<?php

declare(strict_types=1);

namespace Solcre\EmailSchedule\Service;

use Doctrine\DBAL\Driver\PDO\MySQL\Driver;
use Doctrine\DBAL\Exception as DbalException;
use Doctrine\ORM\EntityManager;
use Exception;
use Psr\Log\LoggerInterface;
use Solcre\EmailSchedule\Entity\ScheduleEmail;
use function array_map;
use function count;
use function date;
use function fclose;
use function fopen;
use function fwrite;
use function is_array;

class SendScheduleEmailService extends LoggerService
{
    private EntityManager $entityManager;
    private ScheduleEmailService $scheduleEmailService;
    private EmailService $emailService;

    public function __construct(EntityManager $entityManager, ScheduleEmailService $scheduleEmailService, EmailService $emailService, ?LoggerInterface $logger)
    {
        parent::__construct($logger);
        $this->entityManager        = $entityManager;
        $this->scheduleEmailService = $scheduleEmailService;
        $this->emailService         = $emailService;
    }

    private function isMysqlDriver(): bool
    {
        return $this->entityManager->getConnection()->getDriver() instanceof Driver;
    }

    /**
     * @throws DbalException
     */
    private function lockTable(): void
    {
        if ($this->isMysqlDriver()) {
            $this->entityManager->getConnection()->executeStatement('LOCK TABLES schedule_emails as se WRITE;');
        }
    }

    /**
     * @throws DbalException
     */
    private function unlockTable(): void
    {
        if ($this->isMysqlDriver()) {
            $this->entityManager->getConnection()->executeStatement('UNLOCK TABLES;');
        }
    }

    /**
     * @throws Exception
     */
    public function sendScheduledEmails(): bool
    {
        $result = false;
        $this->lockTable();

        try {
            $scheduledEmailsToSend = $this->scheduleEmailService->fetchAvailableScheduledEmails() ?? [];

            if (!empty($scheduledEmailsToSend) && is_array($scheduledEmailsToSend)) {
                $marked = $this->markEmailAsSending($scheduledEmailsToSend);

                if ($marked) {
                    $this->entityManager->beginTransaction();
                    $result = $this->processEmails($scheduledEmailsToSend);
                    $this->entityManager->flush();
                    $this->entityManager->commit();
                }
            }
        } catch (Exception $e) {
            if ($this->entityManager->getConnection()->isTransactionActive()) {
                $this->entityManager->rollback();
            }
            throw $e;
        } finally {
            $this->unlockTable();
        }

        return $result;
    }

    /**
     * @param ScheduleEmail[] $emailsToSend
     *
     * @throws Exception
     */
    private function markEmailAsSending(array $emailsToSend): bool
    {
        $emailsToSendIds = array_map(
            static function (ScheduleEmail $emailToSend) {
                return $emailToSend->getId();
            },
            $emailsToSend
        );

        if (empty($emailsToSendIds)) {
            return false;
        }

        $result = $this->scheduleEmailService->markEmailAsSending($emailsToSendIds);

        if (!$result) {
            return false;
        }

        foreach ($emailsToSend as $email) {
            $this->entityManager->refresh($email);
        }

        return true;
    }

    /**
     * @param ScheduleEmail[] $emailsToSend
     */
    private function processEmails(array $emailsToSend): bool
    {
        $resultSend = false;

        foreach ($emailsToSend as $scheduleEmail) {
            try {
                $resultSend = $this->emailService->sendScheduledEmail($scheduleEmail);

                $dataToPatch = $resultSend
                    ? ['sendAt' => true]
                    : [
                        'retried'   => $scheduleEmail->getRetried() + 1,
                        'isSending' => false,
                    ];

                if (!$resultSend) {
                    $message = 'Email Schedule ID: ' . $scheduleEmail->getId();
                    $this->logToFile($message);
                }

                $this->scheduleEmailService->patchScheduleEmail($scheduleEmail, $dataToPatch);
            } catch (Exception $e) {
                $dataToPatch = [
                    'retried'   => $scheduleEmail->getRetried() + 1,
                    'isSending' => false,
                ];
                $this->scheduleEmailService->patchScheduleEmail($scheduleEmail, $dataToPatch);
                $resultSend = false;
            }
        }

        return $resultSend;
    }

    private function logToFile(string $msg): void
    {
        $fd = fopen(__DIR__ . '/../Log/email_error.txt', 'ab');
        if ($fd !== false) {
            $str = '[' . date('Y/m/d h:i:s') . ']' . $msg;
            fwrite($fd, $str . "\n");
            fclose($fd);
        }
    }
}
