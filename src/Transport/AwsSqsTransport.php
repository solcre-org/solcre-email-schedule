<?php

namespace Solcre\EmailSchedule\Transport;

use Aws\Credentials\Credentials;
use Aws\Exception\AwsException;
use Aws\Result;
use Aws\Ses\SesClient;
use Aws\Sqs\SqsClient;
use Solcre\EmailSchedule\Entity\EmailAddress;
use Solcre\EmailSchedule\Entity\ScheduleEmail;
use Solcre\EmailSchedule\Exception\BaseException;
use Solcre\EmailSchedule\Interfaces\TransportInterface;
use Solcre\EmailSchedule\Service\EmailService;

class AwsSqsTransport implements TransportInterface
{
    private string $QueueUrl;
    private string $region;

    public function __construct(string $QueueUrl, string $region)
    {
        $this->QueueUrl = $QueueUrl;
        $this->region = $region;
    }

    private function isEmailVerified(string $email): bool
    {
        $isVerified = false;

        $sesClient = new SesClient([
            'region' => $this->region,
            'version' => '2010-12-01',
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
            'region' => $this->region,
            'version' => '2012-11-05',
        ]);

        $toAddresses = [];
        $ccAddresses = [];
        $bccAddresses = [];
        $replyToAddresses = [];

        $addresses = $scheduleEmail->getAddresses();

        foreach ($addresses as $address) {
            $email = $address->getEmail();
            switch ($address->getType()) {
                case EmailService::TYPE_CC:
                    $ccAddresses[] = $email;
                    break;
                case EmailService::TYPE_BCC:
                    $bccAddresses[] = $email;
                    break;
                case EmailService::TYPE_REPLAY_TO:
                    $replyToAddresses[] = $email;
                    break;
                case EmailService::TYPE_TO:
                default:
                    $toAddresses[] = $email;
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
            'toAddresses' => $toAddresses,
            'ccAddresses' => $ccAddresses,
            'bccAddresses' => $bccAddresses,
            'replyToAddresses' => $replyToAddresses,
            'subject' => $scheduleEmail->getSubject(),
            'body' => $scheduleEmail->getContent(),
        ];


        $fromEmail = $scheduleEmail->getEmailFrom()['email'];
        if ($this->isEmailVerified($fromEmail)) {
            $data['from'] = $fromEmail;
        }

        $params = [
            'MessageBody' => \json_encode($data),
            'QueueUrl' => $this->QueueUrl,
            'MessageGroupId' => 'Message-' . $scheduleEmail->getId(),
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
