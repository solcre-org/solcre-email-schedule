<?php

declare(strict_types=1);

namespace Solcre\EmailSchedule\Transport;

use Aws\Exception\AwsException;
use Aws\Result;
use Aws\Ses\SesClient;
use Aws\Sqs\SqsClient;
use Solcre\EmailSchedule\Entity\ScheduleEmail;
use Solcre\EmailSchedule\Exception\BaseException;
use Solcre\EmailSchedule\Interfaces\TransportInterface;
use Solcre\EmailSchedule\Service\EmailService;
use function json_encode;
use function in_array;

class AwsSqsTransport implements TransportInterface
{
    private string $queueUrl;
    private string $region;

    public function __construct(string $queueUrl, string $region)
    {
        $this->queueUrl = $queueUrl;
        $this->region = $region;
    }

    private function isEmailVerified(string $email): bool
    {
        $sesClient = new SesClient([
            'region' => $this->region,
            'version' => '2010-12-01',
        ]);

        $list = $sesClient->listVerifiedEmailAddresses();
        if ($list instanceof Result) {
            return in_array($email, $list->get('VerifiedEmailAddresses'), true);
        }

        return false;
    }

    /**
     * @throws AwsException
     * @throws BaseException
     */
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

        foreach ($scheduleEmail->getAddresses() as $address) {
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
            throw new BaseException('Recipient required', 400);
        }

        $data = [
            'toAddresses' => $toAddresses,
            'ccAddresses' => $ccAddresses,
            'bccAddresses' => $bccAddresses,
            'replyToAddresses' => $replyToAddresses,
            'subject' => $scheduleEmail->getSubject(),
            'body' => $scheduleEmail->getContent(),
        ];

        $fromEmail = $scheduleEmail->getEmailFrom()['email'] ?? null;
        if ($fromEmail !== null && $this->isEmailVerified($fromEmail)) {
            $data['from'] = $fromEmail;
        }

        $params = [
            'MessageBody' => json_encode($data),
            'QueueUrl' => $this->queueUrl,
        ];

        $result = $client->sendMessage($params);

        $statusCode = $result['@metadata']['statusCode'] ?? null;
        return $statusCode === 200;
    }
}
