<?php

namespace SolcreFrameworkTest;

use Doctrine\ORM\EntityManager;
use PHPMailer\PHPMailer\PHPMailer;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Solcre\EmailSchedule\Entity\EmailAddress;
use Solcre\EmailSchedule\Exception\BaseException;
use Solcre\EmailSchedule\Interfaces\TemplateInterface;
use Solcre\EmailSchedule\Service\EmailService;
use Solcre\EmailSchedule\Service\ScheduleEmailService;

class EmailServiceTest extends TestCase
{
    public const TYPE_FROM = 1;
    public const TYPE_TO = 2;
    public const TYPE_CC = 3;
    public const TYPE_BCC = 4;
    public const TYPE_REPLAY_TO = 5;

    private $mailer;
    private $configuration;
    private $mockedEntityManager;
    private $scheduleEmailService;
    private $mockedTemplateInterface;
    private $mockedLogger;
    private $emailService;

    public function setUp(): void
    {
        $this->mailer = $this->getMockBuilder(PHPMailer::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['send'])
            ->getMock();

        $this->mailer->method('send')->willReturn(true);

        $this->configuration = [
            'solcre_email_schedule' => [
                'DEFAULT_FROM_EMAIL' => '',
                'TEMPLATES_PATH'     => '',
                'SMTP_CREDENTIALS'   => [
                    'ACTIVE'   => 1,
                    'HOST'     => '',
                    'USERNAME' => '',
                    'PASSWORD' => '',
                    'PORT'     => 999,
                ],
                'DEFAULT_VARAIBLES'  => [
                    'ASSETS_PATH' => ''
                ]
            ]
        ];

        $this->mockedEntityManager = $this->createMock(EntityManager::class);
        $this->scheduleEmailService = new ScheduleEmailService($this->mockedEntityManager);
        $this->mockedTemplateInterface = $this->createMock(TemplateInterface::class);
        $this->mockedLogger = $this->createMock(LoggerInterface::class);

        $this->emailService = new EmailService(
            $this->mailer,
            $this->configuration,
            $this->scheduleEmailService,
            $this->mockedTemplateInterface,
            $this->mockedLogger
        );
    }

    public function testSendTplWithEmptyAddresses(): void
    {
        $vars = [];
        $templateName = 'templateName';
        $addresses = [];
        $subject = 'subject';

        $this->expectException(BaseException::class);

        $this->emailService->sendTpl($vars, $templateName, $addresses, $subject);
    }

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
            'type'  => self::TYPE_TO
        ];

        $address_2 = [
            'email' => 'jon_doe2@solcre.com',
            'name'  => 'Jon Doe2',
            'type'  => self::TYPE_TO
        ];

        $address_3 = [
            'email' => 'jon_doe3@solcre.com',
            'name'  => 'Jon Doe3',
            'type'  => self::TYPE_TO
        ];

        $expectedAddress_1 = new EmailAddress($address_1['email'], $address_1['name'], $address_1['type']);
        $expectedAddress_2 = new EmailAddress($address_2['email'], $address_2['name'], $address_2['type']);
        $expectedAddress_3 = new EmailAddress($address_3['email'], $address_3['name'], $address_3['type']);

        $expectedAddresses = [$expectedAddress_1, $expectedAddress_2, $expectedAddress_3];

        $addresses = [$address_1, $address_2, $address_3];

        $this->assertEquals($this->emailService->generateAddresses($addresses), $expectedAddresses);
    }

    public function testGenerateAddressesWithInvalidAddresses(): void
    {
        $address_1 = [
            'email' => 'jon_doe@solcre.com',
            'name'  => 'Jon Doe',
            'type'  => self::TYPE_FROM
        ];

        $address_2 = [
            'email' => 'jon_doe2@solcre.com',
            'name'  => 'Jon Doe2',
            'type'  => self::TYPE_FROM
        ];

        $address_3 = [
            'email' => 'jon_doe3@solcre.com',
            'name'  => 'Jon Doe3',
            'type'  => self::TYPE_FROM
        ];

        $expectedAddresses = [];

        $addresses = [$address_1, $address_2, $address_3];

        $this->assertEquals($this->emailService->generateAddresses($addresses), $expectedAddresses);
    }

    public function testSend(): void
    {
        $data = $this->sendSetup();

        $this->AssertTrue($this->emailService->send($data['from'], $data['addresses'], $data['subject'], $data['content']));
    }

    public function sendSetup(): array
    {
        $from = new EmailAddress('jon_doe@solcre.com', 'Jhon Doe', 2);

        $data_1 = [
            'email' => 'jon_doe@solcre.com',
            'name'  => 'Jon Doe',
            'type'  => self::TYPE_FROM
        ];

        $data_2 = [
            'email' => 'jon_doe2@solcre.com',
            'name'  => 'Jon Doe2',
            'type'  => self::TYPE_FROM
        ];

        $data_3 = [
            'email' => 'jon_doe3@solcre.com',
            'name'  => 'Jon Doe3',
            'type'  => self::TYPE_CC
        ];

        $data_4 = [
            'email' => 'jon_doe4@solcre.com',
            'name'  => 'Jon Doe4',
            'type'  => self::TYPE_BCC
        ];

        $data_5 = [
            'email' => 'jon_doe5@solcre.com',
            'name'  => 'Jon Doe5',
            'type'  => self::TYPE_REPLAY_TO
        ];

        $address_1 = new EmailAddress($data_1['email'], $data_1['name'], $data_1['type']);
        $address_2 = new EmailAddress($data_2['email'], $data_2['name'], $data_2['type']);
        $address_3 = new EmailAddress($data_3['email'], $data_3['name'], $data_3['type']);
        $address_4 = new EmailAddress($data_4['email'], $data_4['name'], $data_4['type']);
        $address_5 = new EmailAddress($data_5['email'], $data_5['name'], $data_5['type']);

        $addresses = [$address_1, $address_2, $address_3, $address_4, $address_5];

        $subject = 'any subject';
        $content = 'a content';

        return [
            'from'      => $from,
            'addresses' => $addresses,
            'subject'   => $subject,
            'content'   => $content
        ];
    }

    public function testSendWithException(): void
    {
        $data = $this->sendSetup();
        $emailService = $this->sendSetupWithException();

        $this->expectException(BaseException::class);

        $emailService->send($data['from'], $data['addresses'], $data['subject'], $data['content']);
    }

    public function sendSetupWithException(): EmailService
    {
        $mockMailer = $this->getMockBuilder(PHPMailer::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['send'])
            ->getMock();

        $mockMailer->method('send')->willReturn(false);

        $emailService = new EmailService(
            $mockMailer,
            $this->configuration,
            $this->scheduleEmailService,
            $this->mockedTemplateInterface,
            $this->mockedLogger
        );

        return $emailService;
    }
}
