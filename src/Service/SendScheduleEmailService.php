<?php

namespace Solcre\EmailSchedule\Service;

use Doctrine\DBAL\Driver\PDO\MySQL\Driver;
use Doctrine\ORM\EntityManager;
use Exception;
use Psr\Log\LoggerInterface;
use Solcre\EmailSchedule\Entity\EmailAddress;
use Solcre\EmailSchedule\Entity\ScheduleEmail;
use function array_map;
use function is_array;

class SendScheduleEmailService extends LoggerService
{
    private ScheduleEmailService $scheduleEmailService;
    private EmailService $emailService;
    private EntityManager $entityManager;

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
     * @throws \Doctrine\DBAL\Exception
     */
    private function lockTable(): void
    {
        if ($this->isMysqlDriver()) {
            try {
                $this->entityManager->getConnection()->executeStatement('LOCK TABLES schedule_emails as se WRITE;');
            } catch (\Doctrine\DBAL\Exception $e) {
                throw $e;
            }
        }
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    private function unlockTable(): void
    {
        if ($this->isMysqlDriver()) {
            try {
                $this->entityManager->getConnection()->executeStatement('UNLOCK TABLES;');
            } catch (\Doctrine\DBAL\Exception $e) {
                throw $e;
            }
        }
    }

    /**
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Solcre\EmailSchedule\Exception\BaseException
     * @throws \Doctrine\DBAL\Exception
     */
    /**
     */
    public function sendScheduledEmails(): bool
    {
        try {
            $this->lockTable();
            $scheduledEmailsToSend = $this->scheduleEmailService->fetchAvailableScheduledEmails();
            $result                = false;

            if (! empty($scheduledEmailsToSend) && is_array($scheduledEmailsToSend)) {
                $result = $this->markEmailAsSending($scheduledEmailsToSend);

                if ($result) {
                    $this->entityManager->beginTransaction();
                    $result = $this->processEmails($scheduledEmailsToSend);
                    $this->entityManager->flush();
                    $this->entityManager->commit();
                }
            }

            $this->unlockTable();
            return $result;
        } catch (Exception $e) {
            if ($this->entityManager->isOpen()) {
                $this->entityManager->flush();
                $this->entityManager->commit();
            }
            throw $e;
        }
    }

    /**
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\DBAL\Exception
     */
    private function markEmailAsSending(array $emailsToSend): ?bool
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

        try {
            $result = $this->scheduleEmailService->markEmailAsSending($emailsToSendIds);
            $this->unlockTable();

            if (! $result) {
                return false;
            }

            foreach ($emailsToSend as $email) {
                $this->entityManager->refresh($email);
            }

            return true;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * @throws \Solcre\EmailSchedule\Exception\BaseException
     */
    private function processEmails(array $emailsToSend): ?bool
    {
        $resultSend = false;
        /* @var $scheduleEmail ScheduleEmail */
        foreach ($emailsToSend as $scheduleEmail) {
            try {
                $addressesToEmail = $this->createAddresses($scheduleEmail->getAddresses());

                if (empty($addressesToEmail)) {
                    continue;
                }

                $from       = $this->createEmailFrom($scheduleEmail->getEmailFrom());
                $resultSend = $this->sendEmail($from, $addressesToEmail, $scheduleEmail);

                $dataToPatch = [];
                if ($resultSend) {
                    $dataToPatch = [
                        'sendAt' => true
                    ];
                }

                if (! $resultSend) {
                    $message = 'Email Schedule ID: ' . $scheduleEmail->getId();
                    $this->logToFile($message);
                    $dataToPatch = [
                        'retried'   => $scheduleEmail->getRetried() + 1,
                        'isSending' => false,
                    ];
                }

                $this->scheduleEmailService->patchScheduleEmail($scheduleEmail, $dataToPatch);
            } catch (Exception $e) {
                $dataToPatch = [
                    'retried'   => $scheduleEmail->getRetried() + 1,
                    'isSending' => false,
                ];
                $this->scheduleEmailService->patchScheduleEmail($scheduleEmail, $dataToPatch);
                unset($e);
            }
        }

        return $resultSend;
    }

    private function createAddresses(array $addresses): array
    {
        $addressesToEmail = [];
        if (\count($addresses) > 0) {
            foreach ($addresses as $emailsAddress) {
                $addressesToEmail[] = new EmailAddress($emailsAddress['email'], $emailsAddress['name'], $emailsAddress['type']);
            }
        }

        return $addressesToEmail;
    }

    private function createEmailFrom(array $fromEmail): EmailAddress
    {
        return new EmailAddress($fromEmail['email'], $fromEmail['name'] ?? null, $fromEmail['type']);
    }

    private function sendEmail(EmailAddress $from, array $addressesToEmail, ScheduleEmail $scheduleEmail): ?bool
    {
        try {
            return $this->emailService->send(
                $from,
                $addressesToEmail,
                $scheduleEmail->getSubject(),
                $scheduleEmail->getContent(),
                $scheduleEmail->getCharset(),
                $scheduleEmail->getAltText()
            );
        } catch (Exception $e) {
            return false;
        }
    }

    private function logToFile($msg): void
    {
        // open file
        $fd = fopen(__DIR__ . '/../Log/email_error.txt', 'ab');
        if ($fd !== false) {
            // append date/time to message
            $str = '[' . date('Y/m/d h:i:s') . ']' . $msg;
            // write string
            fwrite($fd, $str . "\n");
            // close file
            fclose($fd);
        }
    }
}
