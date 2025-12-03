<?php

declare(strict_types=1);

namespace EmailScheduleTest\Service;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Solcre\EmailSchedule\Entity\EmailAddress;
use Solcre\EmailSchedule\Entity\ScheduleEmail;
use Solcre\EmailSchedule\Exception\BaseException;
use Solcre\EmailSchedule\Interfaces\TemplateInterface;
use Solcre\EmailSchedule\Interfaces\TransportInterface;
use Solcre\EmailSchedule\Module;
use Solcre\EmailSchedule\Service\EmailService;
use Solcre\EmailSchedule\Service\ScheduleEmailService;

class EmailServiceTest extends TestCase
{
    private TransportInterface $transport;
    private ScheduleEmailService $scheduleEmailService;
    private TemplateInterface $template;
    private EmailService $emailService;

    protected function setUp(): void
    {
        $this->transport = $this->createMock(TransportInterface::class);
        $this->scheduleEmailService = $this->createMock(ScheduleEmailService::class);
        $this->template = $this->createMock(TemplateInterface::class);
        $logger = $this->createMock(LoggerInterface::class);

        $configuration = [
            Module::CONFIG_KEY => [
                'DEFAULT_FROM_EMAIL'      => 'noreply@example.com',
                'DEFAULT_FROM_NAME_EMAIL' => 'No Reply',
                'DEFAULT_VARIABLES'       => [],
            ],
        ];

        $this->emailService = new EmailService(
            $this->transport,
            $configuration,
            $this->scheduleEmailService,
            $this->template,
            $logger
        );
    }

    public function testGetFromEmailFallsBackToDefaults(): void
    {
        $from = $this->emailService->getFromEmail('invalid');

        self::assertSame('noreply@example.com', $from->getEmail());
        self::assertSame('No Reply', $from->getName());
    }

    public function testBuildAddressesFiltersInvalidItems(): void
    {
        $addresses = [
            ['email' => 'valid@example.com', 'type' => EmailService::TYPE_TO],
            ['email' => 'invalid', 'type' => EmailService::TYPE_TO],
        ];

        $result = $this->emailService->buildAddresses($addresses);

        self::assertCount(1, $result);
        self::assertInstanceOf(EmailAddress::class, $result[0]);
        self::assertSame('valid@example.com', $result[0]->getEmail());
    }

    public function testSendEmailQueuesAndSends(): void
    {
        $this->template
            ->method('render')
            ->willReturn('rendered');

        $this->transport
            ->expects(self::once())
            ->method('send')
            ->with(self::isInstanceOf(ScheduleEmail::class))
            ->willReturn(true);

        $this->scheduleEmailService
            ->method('add')
            ->willReturnCallback(function (array $data): ScheduleEmail {
                $email = new ScheduleEmail();
                $email->setEmailFrom($data['from']);
                $email->setAddresses($data['addresses']);
                $email->setSubject($data['subject']);
                $email->setContent($data['content']);
                return $email;
            });

        $address = ['email' => 'to@example.com', 'type' => EmailService::TYPE_TO];

        $result = $this->emailService->sendEmail([], 'welcome', [$address], 'Hi');

        self::assertInstanceOf(ScheduleEmail::class, $result);
    }

    public function testSendEmailThrowsWhenNoAddresses(): void
    {
        $this->expectException(BaseException::class);
        $this->emailService->sendEmail([], 'tpl', [], 'subject');
    }
}
