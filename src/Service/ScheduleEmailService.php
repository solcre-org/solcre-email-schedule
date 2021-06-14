<?php

namespace Solcre\EmailSchedule\Service;

use DateTime;
use Doctrine\ORM\EntityManager;
use Exception;
use InvalidArgumentException;
use Solcre\EmailSchedule\Entity\ScheduleEmail;
use Solcre\EmailSchedule\Exception\BaseException;
use function array_key_exists;
use function count;
use function sprintf;

class ScheduleEmailService
{
    private const MAX_RETRIED        = 3;
    private const DELAYED_EMAIL_HOUR = 1;
    private EntityManager $entityManager;
    private $repository;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository    = $this->entityManager->getRepository(ScheduleEmail::class);
    }

    /**
     * @throws \Solcre\EmailSchedule\Exception\BaseException
     */
    public function add($data): ScheduleEmail
    {
        try {
            $this->validateData($data);

            $scheduleEmail = new ScheduleEmail();
            $scheduleEmail->setCharset($data['charset'] ?? 'UTF-8');
            $scheduleEmail->setAddresses($data['addresses']);
            $scheduleEmail->setAltText($data['altText']);
            $scheduleEmail->setContent($data['content']);
            $scheduleEmail->setCreatedAt(new DateTime());
            $scheduleEmail->setEmailFrom($data['from']);
            $scheduleEmail->setRetried(0);
            $scheduleEmail->setSendingDate(null);
            $scheduleEmail->setSubject($data['subject']);

            $this->entityManager->persist($scheduleEmail);
            $this->entityManager->flush($scheduleEmail);

            return $scheduleEmail;
        } catch (Exception $exception) {
            throw new BaseException('Error creating schedule email', $exception->getCode());
        }
    }

    private function validateData(array $data): void
    {
        $required = ['content', 'subject', 'altText', 'addresses', 'from'];

        if (! $this->arrayKeysExists($required, $data)) {
            throw new InvalidArgumentException('Invalid data provided', 422);
        }
    }

    private function arrayKeysExists(array $keys, array $arr): bool
    {
        return ! array_diff_key(array_flip($keys), $arr);
    }

    public function anyArrayKeyExist(array $keys, array $data): bool
    {
        $keysReceived = array_keys($data);

        return ! ((! count(array_intersect($keys, $keysReceived))) > 0);
    }

    /**
     * @throws \Solcre\EmailSchedule\Exception\BaseException
     */
    public function patchScheduleEmail(ScheduleEmail $scheduleEmailEntity, array $data): ScheduleEmail
    {
        try {
            if (! $this->anyArraykeyExist(['sendAt', 'isSending', 'retried'], $data)) {
                return $scheduleEmailEntity;
            }

            if (array_key_exists('sendAt', $data)) {
                $scheduleEmailEntity->setSendAt(new DateTime());
            }

            if (array_key_exists('isSending', $data)) {
                $date = null;

                if ($data['isSending']) {
                    $date = new DateTime();
                }
                $scheduleEmailEntity->setSendingDate($date);
            }

            if (array_key_exists('retried', $data)) {
                $scheduleEmailEntity->setRetried($data['retried']);
            }

            $this->entityManager->flush($scheduleEmailEntity);

            return $scheduleEmailEntity;
        } catch (Exception $exception) {
            throw new BaseException('Error patching schedule email', $exception->getCode());
        }
    }

    /**
     * @throws \Exception
     */
    public function markEmailAsSending($emailsToSend)
    {
        try {
            return $this->repository->markEmailAsSending($emailsToSend);
        } catch (Exception $e) {
            throw  $e;
        }
    }

    /**
     * @throws \Exception
     */
    public function fetchAvailableScheduledEmails()
    {
        try {
            return $this->repository->fetchAvailableScheduledEmails(self::MAX_RETRIED);
        } catch (Exception $e) {
            throw  $e;
        }
    }

    private function calculateOffset(int $page, int $size): int
    {
        return $size * ($page - 1);
    }

    /**
     * @throws \Exception
     */
    public function fetchScheduledEmailsAsArray(?int $page = null, ?int $size = null): array
    {
        try {
            if ($page === null) {
                $page = 1;
            }

            if ($size === null) {
                $size = 100;
            }

            $offset = $this->calculateOffset($page, $size);
            return $this->repository->fetchScheduledEmailsAsArray($offset, $size);
        } catch (Exception $e) {
            throw  $e;
        }
    }

    /**
     * @throws \Exception
     */
    public function processDelayedEmails()
    {
        try {
            $delayedTime = new DateTime();
            $hourPast    = sprintf('- %s hour', self::DELAYED_EMAIL_HOUR);
            $delayedTime->modify($hourPast);
            $delayedMinutes = self::DELAYED_EMAIL_HOUR * 60;

            return $this->repository->processDelayedEmails($delayedTime->format('Y-m-d H:i:s'), $delayedMinutes);
        } catch (Exception $e) {
            throw  $e;
        }
    }
}
