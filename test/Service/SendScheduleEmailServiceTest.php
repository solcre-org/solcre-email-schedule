<?php

namespace EmailScheduleTest\Service;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\AbstractMySQLDriver;
use PHPUnit\Framework\TestCase;
use Doctrine\ORM\EntityManager;
use Exception;
use Psr\Log\LoggerInterface;
use Solcre\EmailSchedule\Entity\EmailAddress;
use Solcre\EmailSchedule\Entity\ScheduleEmail;
use Solcre\EmailSchedule\Service\EmailService;
use Solcre\EmailSchedule\Service\ScheduleEmailService;
use Solcre\EmailSchedule\Service\SendScheduleEmailService;

class SendScheduleEmailServiceTest extends TestCase
{
    private $scheduleEmailService;
    private $emailService;
    private $mockedEntityManager;
    private $sendScheduleEmailService;

    public function setup(): void
    {
        $this->mockedEntityManager  = $this->createMock(EntityManager::class);
        $this->emailService         = $this->createMock(EmailService::class);

        $this->scheduleEmailService = $this->getMockBuilder(ScheduleEmailService::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['fetchAvailableScheduledEmails'])
            ->getMock();

        $listOfMails = ['mail 1', 'mail 2'];
        $this->scheduleEmailService->method('fetchAvailableScheduledEmails')->willReturn($listOfMails);

        $this->sendScheduleEmailService = new SendScheduleEmailService(
            $this->mockedEntityManager,
            $this->scheduleEmailService,
            $this->emailService,
            null
        );
    }

    public function setupWithoutEmailsToSend(): SendScheduleEmailService
    {
        $mockedEntityManager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getConnection'])
            ->getMock();

//        $returnTrue = new class() {
//            public function exec()
//            {
//                return true;
//            }
//        };

        $mockedConnection = $this->getMockBuilder(Connection::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getDriver'])
            ->getMock();


        $mockedDriver = $this->getMockBuilder(AbstractMySQLDriver::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockedEntityManager->method('getConnection')->willReturn($mockedConnection);
        $mockedConnection->method('getDriver')->willReturn($mockedDriver);


        $scheduleEmailService = $this->getMockBuilder(ScheduleEmailService::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['fetchAvailableScheduledEmails'])
            ->getMock();

        $listOfMails = [];
        $scheduleEmailService->method('fetchAvailableScheduledEmails')->willReturn($listOfMails);

        $sendScheduleEmailService = new SendScheduleEmailService(
            $mockedEntityManager,
            $scheduleEmailService,
            $this->emailService,
            null
        );

        return $sendScheduleEmailService;
    }

    public function testSendScheduledEmailsWithoutEmails(): void
    {
        $sendScheduleEmailService = $this->setupWithoutEmailsToSend();

        $this->assertEquals($sendScheduleEmailService->sendScheduledEmails(), false);
    }
}
