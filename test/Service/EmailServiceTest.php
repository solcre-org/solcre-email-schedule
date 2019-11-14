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
use function InvalidPhpDoc\variadicNumbers;

class EmailServiceTest extends TestCase
{
    public const TYPE_FROM = 1;

    private $mailer;
    private $configuration;
    private $mockedEntityManager;
    private $scheduleEmailService;
    private $mockedTemplateInterface;
    private $mockedLogger;
    private $emailService;

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

   public function setUp(): void
   {
       $this->mailer = new PHPMailer();

       $this->configuration           = [
           'solcre_email_schedule' => [
               'DEFAULT_FROM_EMAIL' => '',
               'ASSETS_PATH'        => '',
               'TEMPLATES_PATH'        => '',
               'SMTP_CREDENTIALS'   => [
                   'ACTIVE'     => 1,
                   'HOST'     => '',
                   'USERNAME' => '',
                   'PASSWORD' => '',
                   'PORT'     => 999,
               ]
           ]
       ];

       $this->mockedEntityManager     = $this->createMock(EntityManager::class);
       $this->scheduleEmailService    = new ScheduleEmailService($this->mockedEntityManager);
       $this->mockedTemplateInterface = $this->createMock(TemplateInterface::class);
       $this->mockedLogger            = $this->createMock(LoggerInterface::class);

       $this->emailService = new EmailService($this->mailer, $this->configuration, $this->scheduleEmailService,
           $this->mockedTemplateInterface, $this->mockedLogger);
   }

    /*
    public function testSendTpl()
    {
        $vars = [];
        $templateName = 'templateName';
        $addresses = [];
        $subject = 'subject';

        $address_1 = [
            'email' => 'jon_doe@solcre.com',
            'name'  => 'Jon Doe',
            'type'  => 2
        ];

        $address_2 = [
            'email' => 'jon_doe2@solcre.com',
            'name'  => 'Jon Doe2',
            'type'  => 2
        ];

        $address_3 = [
            'email' => 'jon_doe3@solcre.com',
            'name'  => 'Jon Doe3',
            'type'  => 2
        ];

        $addresses = $this->emailService->generateAddresses([$address_1, $address_2, $address_3]);
        $from      = $this->emailService->getFromEmail(null);
        //$content   = $content = $this->emailService->getRenderTemplate($vars, $templateName);
        // no permite llamar metodo privado getRenderTemplate, por tanto contetn queda indefinido.

        //$expectedRet = $this->emailService->sendTpl($from, $addresses, $content, $charset, $subject, $altText);
        //var_dump($expectedRet);


        //$this->emailService->sendTpl($vars, $templateName, $addresses)
    }*/

    public function testGetFromEmail(): void
    {
        $from = 'jhon_doe@solcre.com';
        $expectedEmailAddress = new EmailAddress($from, '', self::TYPE_FROM);

        $this->assertEquals($expectedEmailAddress, $this->emailService->getFromEmail($from));
    }

    public function testGetFromEmailWhenFromIsNull(): void
    {
        $from = null;

        $expectedFrom = $this->configuration['solcre_email_schedule']['DEFAULT_FROM_EMAIL'];
        $expectedEmailAddress = new EmailAddress($expectedFrom, '', self::TYPE_FROM);

        $this->assertEquals($expectedEmailAddress, $this->emailService->getFromEmail($from));
    }

    public function testGetFromEmailWithInvalidFrom(): void
    {
        $from = 'jonDoe.solcre';

        $expectedFrom = $this->configuration['solcre_email_schedule']['DEFAULT_FROM_EMAIL'];
        $expectedEmailAddress = new EmailAddress($expectedFrom, '', self::TYPE_FROM);

        $this->assertEquals($expectedEmailAddress, $this->emailService->getFromEmail($from));
    }

    public function testGenerateAddresses(): void
    {
        $address_1 = [
            'email' => 'jon_doe@solcre.com',
            'name'  => 'Jon Doe',
            'type'  => 2
        ];

        $address_2 = [
            'email' => 'jon_doe2@solcre.com',
            'name'  => 'Jon Doe2',
            'type'  => 2
        ];

        $address_3 = [
            'email' => 'jon_doe3@solcre.com',
            'name'  => 'Jon Doe3',
            'type'  => 2
        ];

        $expectedAddress_1 = new EmailAddress($address_1['email'], $address_1['name'], $address_1['type']);
        $expectedAddress_2 = new EmailAddress($address_2['email'], $address_2['name'], $address_2['type']);
        $expectedAddress_3 = new EmailAddress($address_3['email'], $address_3['name'], $address_3['type']);

        $expectedAddresses  = [$expectedAddress_1, $expectedAddress_2, $expectedAddress_3];

        $addresses = [$address_1, $address_2, $address_3];

        $this->assertEquals($this->emailService->generateAddresses($addresses), $expectedAddresses);
    }

    public function testGenerateAddressesWithInvalidAddresses(): void
    {
        $address_1 = [
            'email' => 'jon_doe@solcre.com',
            'name'  => 'Jon Doe',
            'type'  => 1
        ];

        $address_2 = [
            'email' => 'jon_doe2@solcre.com',
            'name'  => 'Jon Doe2',
            'type'  => 1
        ];

        $address_3 = [
            'email' => 'jon_doe3@solcre.com',
            'name'  => 'Jon Doe3',
            'type'  => 1
        ];

        $expectedAddresses  = [];

        $addresses = [$address_1, $address_2, $address_3];

        $this->assertEquals($this->emailService->generateAddresses($addresses), $expectedAddresses);
    }

    public function testSend()
    {
        /*(EmailAddress $from,
        array $addresses, string $subject, string $content,
        string $charset = PHPMailer::CHARSET_UTF8,
        $altText = 'To view the message, please use an HTML compatible email viewer!')*/

        $from = new EmailAddress('jon_doe@solcre.com', 'Jhon Doe', 2);

        $data_1 = [
            'email' => 'jon_doe@solcre.com',
            'name'  => 'Jon Doe',
            'type'  => 1
                ];

        $data_2 = [
            'email' => 'jon_doe2@solcre.com',
            'name'  => 'Jon Doe2',
            'type'  => 1
        ];

        $data_3 = [
            'email' => 'jon_doe3@solcre.com',
            'name'  => 'Jon Doe3',
            'type'  => 1
        ];

        $address_1 = new EmailAddress($data_1['email'], $data_1['name'], $data_1['type']);
        $address_2 = new EmailAddress($data_2['email'], $data_2['name'], $data_2['type']);
        $address_3 = new EmailAddress($data_3['email'], $data_3['name'], $data_3['type']);

        $addresses = [$address_1, $address_2, $address_3];

        $subject = 'any subject';
        $content = 'a content';
        $charset = PHPMailer::CHARSET_UTF8;

        $this->AssertTrue($this->emailService->send($from, $addresses, $subject, $content ));
        /*
         *  if (! $this->mailer->send()) {
                throw new BaseException($this->mailer->ErrorInfo, 400);
            }
            Este fragmento da false porque no envia el mail. no conecta host y demas
         */

    }



}
