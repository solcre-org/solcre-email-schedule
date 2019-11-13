<?php

namespace SolcreFrameworkTest;

use PHPUnit\Framework\TestCase;
use Solcre\EmailSchedule\Service\EmailService;
use Solcre\EmailSchedule\Service\ScheduleEmailService;
use PHPMailer\PHPMailer\PHPMailer;
use Psr\Log\LoggerInterface;
use Solcre\EmailSchedule\Entity\EmailAddress;
use Solcre\EmailSchedule\Entity\ScheduleEmail;
use Solcre\EmailSchedule\Exception\BaseException;
use Solcre\EmailSchedule\Interfaces\TemplateInterface;
use Solcre\EmailSchedule\Module;
use Doctrine\ORM\EntityManager;

class EmailServiceTest extends TestCase
{
   /* public function testCreateWithParams(): void
    {
        // how test?
        $mailer               = new PHPMailer();
        $configuration        = 'the configuration';
        $mockedEntityManager  = $this->createMock(EntityManager::class);
        $scheduleEmailService = new ScheduleEmailService($mockedEntityManager);
        $templateInterface    = $this->createMock(TemplateInterface::class);
        $logger               = $this->createMock(LoggerInterface::class);

        $emailService = new EmailService($mailer, $configuration, $scheduleEmailService, $templateInterface, $logger);

        $this->assertEquals('mailer', $emailService->getEmail());
    }*/

    /*
    public function testSendTpl()
    {

    }*/
    /*
    public function testGetFromEmail()
    {

    }*/

    /*
    public function validateEmail()
    {

    }

    public function generateAddresses()
    {

    }

    public function validateEmailType()
    {

    }

    public function getRenderTemplate()
    {

    }

    public function mergeDefaultVariables()
    {

    }

    public function getDefaultVariables()
    {

    }

    public function getEmailAssetsPath()
    {

    }

    public function sendOrSaveEmail()
    {

    }

    public function saveEmail()
    {

    }

    public function send()
    {

    }*/

}
