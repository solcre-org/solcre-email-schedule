<?php

declare(strict_types=1);

namespace Solcre\EmailSchedule\Service;

use DateTime;
use Doctrine\ORM\EntityManager;
use Exception;
use InvalidArgumentException;
use Solcre\EmailSchedule\Entity\EmailAddress;
use Solcre\EmailSchedule\Entity\ScheduleEmail;
use Solcre\EmailSchedule\Exception\BaseException;
use Solcre\EmailSchedule\Repository\ScheduleEmailRepository;
use function array_diff_key;
use function array_flip;
use function array_intersect;
use function array_key_exists;
use function array_keys;
use function count;
use function sprintf;

class ScheduleEmailService
{
    private const MAX_RETRIED = 3;
    private const DELAYED_EMAIL_HOUR = 1;
    private EntityManager $entityManager;
    private ScheduleEmailRepository $repository;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $this->entityManager->getRepository(ScheduleEmail::class);
    }

    public function add(array $data): ScheduleEmail
    {
        $this->validateData($data);

        try {
            $scheduleEmail = new ScheduleEmail();
            $scheduleEmail->setCharset($data['charset'] ?? 'UTF-8');
            $scheduleEmail->setAddresses($this->normalizeAddresses($data['addresses']));
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
            throw new BaseException('Error creating schedule email', $exception->getCode() ?: 500);
        }
    }

    /**
     * @param EmailAddress[] $addresses
     */
    private function normalizeAddresses(array $addresses): array
    {
        $normalized = [];

        foreach ($addresses as $address) {
            $normalized[] = [
                'email' => $address->getEmail(),
                'name'  => $address->getName(),
                'type'  => $address->getType(),
            ];
        }

        return $normalized;
    }

    private function validateData(array $data): void
    {
        $required = ['content', 'subject', 'altText', 'addresses', 'from'];

        if (!$this->arrayKeysExists($required, $data)) {
            throw new InvalidArgumentException('Invalid data provided', 422);
        }
    }

    private function arrayKeysExists(array $keys, array $arr): bool
    {
        return array_diff_key(array_flip($keys), $arr) === [];
    }

    public function anyArrayKeyExist(array $keys, array $data): bool
    {
        return count(array_intersect($keys, array_keys($data))) > 0;
    }

    /**
     * @throws BaseException
     */
    public function patchScheduleEmail(ScheduleEmail $scheduleEmailEntity, array $data): ScheduleEmail
    {
        try {
            if (!$this->anyArrayKeyExist(['sendAt', 'isSending', 'retried'], $data)) {
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
                $scheduleEmailEntity->setRetried((int) $data['retried']);
            }

            $this->entityManager->flush($scheduleEmailEntity);

            return $scheduleEmailEntity;
        } catch (Exception $exception) {
            throw new BaseException('Error patching schedule email', $exception->getCode() ?: 500);
        }
    }

    /**
     * @throws Exception
     */
    public function markEmailAsSending(array $emailsToSend): bool
    {
        return $this->repository->markEmailAsSending($emailsToSend) ?? false;
    }

    /**
     * @return ScheduleEmail[]
     *
     * @throws Exception
     */
    public function fetchAvailableScheduledEmails(): ?array
    {
        return $this->repository->fetchAvailableScheduledEmails(self::MAX_RETRIED);
    }

    private function calculateOffset(int $page, int $size): int
    {
        return $size * ($page - 1);
    }

    /**
     * @throws Exception
     */
    public function fetchScheduledEmailsAsArray(?int $page = null, ?int $size = null): array
    {
        $page = $page ?? 1;
        $size = $size ?? 100;
        $offset = $this->calculateOffset($page, $size);

        return $this->repository->fetchScheduledEmailsAsArray($offset, $size);
    }

    /**
     * @throws Exception
     */
    public function processDelayedEmails(): void
    {
        $delayedTime = new DateTime();
        $hourPast = sprintf('- %s hour', self::DELAYED_EMAIL_HOUR);
        $delayedTime->modify($hourPast);
        $delayedMinutes = self::DELAYED_EMAIL_HOUR * 60;

        $this->repository->processDelayedEmails($delayedTime->format('Y-m-d H:i:s'), $delayedMinutes);
    }
}
