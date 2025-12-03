<?php

declare(strict_types=1);

namespace EmailScheduleTest\Service;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver;
use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\TestCase;
use Solcre\EmailSchedule\Entity\ScheduleEmail;
use Solcre\EmailSchedule\Service\EmailService;
use Solcre\EmailSchedule\Service\ScheduleEmailService;
use Solcre\EmailSchedule\Service\SendScheduleEmailService;

class SendScheduleEmailServiceTest extends TestCase
{
    public function testSendScheduledEmailsWithoutWork(): void
    {
        $service = $this->buildService([], false);
        self::assertFalse($service->sendScheduledEmails());
    }

    public function testSendScheduledEmailsWithWork(): void
    {
        $email = new ScheduleEmail();
        $email->setSubject('hello');
        $email->setEmailFrom(['email' => 'from@example.com', 'type' => 1]);
        $email->setAddresses([['email' => 'to@example.com', 'type' => 2]]);

        $service = $this->buildService([$email], true);
        self::assertTrue($service->sendScheduledEmails());
    }

    /**
     * @param ScheduleEmail[] $emails
     */
    private function buildService(array $emails, bool $markSuccess): SendScheduleEmailService
    {
        $connection = $this->createMock(Connection::class);
        $connection->method('getDriver')->willReturn($this->createMock(Driver::class));
        $connection->method('isTransactionActive')->willReturn(false);

        $entityManager = $this->createMock(EntityManager::class);
        $entityManager->method('getConnection')->willReturn($connection);

        $scheduleEmailService = $this->createMock(ScheduleEmailService::class);
        $scheduleEmailService->method('fetchAvailableScheduledEmails')->willReturn($emails);
        $scheduleEmailService->method('markEmailAsSending')->willReturn($markSuccess);

        $emailService = $this->createMock(EmailService::class);
        $emailService->method('sendScheduledEmail')->willReturn(true);

        return new SendScheduleEmailService($entityManager, $scheduleEmailService, $emailService, null);
    }
}
