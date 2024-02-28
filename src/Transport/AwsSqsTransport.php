<?php

namespace Solcre\EmailSchedule\Transport;

use Aws\Credentials\Credentials;
use Aws\Exception\AwsException;
use Aws\Result;
use Aws\Ses\SesClient;
use Aws\Sqs\SqsClient;
use Solcre\EmailSchedule\Entity\ScheduleEmail;
use Solcre\EmailSchedule\Exception\BaseException;
use Solcre\EmailSchedule\Service\EmailService;

class AwsSqsTransport
{
    private Credentials $credentials;
    private string $QueueUrl;
    private string $region;

    public function __construct(array $credentials, string $QueueUrl, string $region = 'us-east-1')
    {
        $this->credentials = new Credentials($credentials['key'], $credentials['secret']);
        $this->QueueUrl = $QueueUrl;
        $this->region = $region;
    }

    private function isEmailVerified(string $email): bool
    {
        $isVerified = false;

        $sesClient = new SesClient([
            'region'      => $this->region,
            'version'     => '2010-12-01',
            'credentials' => $this->credentials,
        ]);

        $list = $sesClient->listVerifiedEmailAddresses();
        if ($list instanceof Result) {
            $isVerified = \in_array($email, $list->get('VerifiedEmailAddresses'), true);
        }

        return $isVerified;
    }

    public function send(ScheduleEmail $scheduleEmail): bool
    {
        $client = new SqsClient([
            'region'      => $this->region,
            'version'     => '2012-11-05',
            'credentials' => $this->credentials,
        ]);

        $toAddresses = [];
        $ccAddresses = [];
        $bccAddresses = [];
        $replyToAddresses = [];

        $addresses = $scheduleEmail->getAddresses();

        foreach ($addresses as $address) {
            switch ($address['type']) {
                case EmailService::TYPE_CC:
                    $ccAddresses[] = $address['email'];
                    break;
                case EmailService::TYPE_BCC:
                    $bccAddresses[] = $address['email'];
                    break;
                case EmailService::TYPE_REPLAY_TO:
                    $replyToAddresses[] = $address['email'];
                    break;
                case EmailService::TYPE_TO:
                default:
                    $toAddresses[] = $address['email'];
                    break;
            }
        }

        if (empty($toAddresses)) {
            if (!empty($ccAddresses)) {
                $toAddresses = $ccAddresses;
                $ccAddresses = [];
            } elseif (!empty($bccAddresses)) {
                $toAddresses = $bccAddresses;
                $bccAddresses = [];
            }
        }

        if (empty($toAddresses)) {
            throw new BaseException('Debe enviar un destinatario', 400);
        }

        $data = [
            'toAddresses'      => $toAddresses,
            'ccAddresses'      => $ccAddresses,
            'bccAddresses'     => $bccAddresses,
            'replyToAddresses' => $replyToAddresses,
            'subject'          => $scheduleEmail->getSubject(),
            'body'             => $scheduleEmail->getContent(),
        ];


        $fromEmail = $scheduleEmail->getEmailFrom()['email'];
        if ($this->isEmailVerified($fromEmail)) {
            $data['from'] = $fromEmail;
        }

        $params = [
            'MessageBody'               => \json_encode($data),
            'QueueUrl'                  => $this->QueueUrl,
            'MessageGroupId'            => $scheduleEmail->getId(),
            'ContentBasedDeduplication' => true,
        ];

        try {
            $result = $client->sendMessage($params);

            $statusCode = $result['@metadata']['statusCode'] ?? null;
            if ($statusCode === 200) {
                return true;
            }
        } catch (AwsException $e) {
            throw $e;
        }

        return false;
    }

}
