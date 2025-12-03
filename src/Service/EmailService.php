<?php

declare(strict_types=1);

namespace Solcre\EmailSchedule\Service;

use Psr\Log\LoggerInterface;
use Solcre\EmailSchedule\Entity\EmailAddress;
use Solcre\EmailSchedule\Entity\ScheduleEmail;
use Solcre\EmailSchedule\Exception\BaseException;
use Solcre\EmailSchedule\Interfaces\TemplateInterface;
use Solcre\EmailSchedule\Interfaces\TransportInterface;
use Solcre\EmailSchedule\Module;
use function array_merge;
use function filter_var;
use function in_array;

class EmailService extends LoggerService
{
    public const TEMPLATE_ENGINE_SMARTY = 'Smarty';
    public const TEMPLATE_ENGINE_TWIG = 'Twig';

    public const TYPE_FROM = 1;
    public const TYPE_TO = 2;
    public const TYPE_CC = 3;
    public const TYPE_BCC = 4;
    public const TYPE_REPLAY_TO = 5;

    private array $configuration;
    private ScheduleEmailService $scheduleEmailService;
    private TemplateInterface $templateService;
    private TransportInterface $mailer;

    public function __construct(
        TransportInterface $mailer,
        array $configuration,
        ScheduleEmailService $scheduleEmailService,
        TemplateInterface $templateService,
        ?LoggerInterface $logger
    ) {
        parent::__construct($logger);
        $this->configuration = $configuration;
        $this->scheduleEmailService = $scheduleEmailService;
        $this->templateService = $templateService;
        $this->mailer = $mailer;

        $this->assertConfig();
    }

    /**
     * Queue and send an email rendered from a template.
     *
     * @throws BaseException
     */
    public function sendEmail(array $data, string $templateName, array $addresses, string $subject, ?string $from = null, ?string $fromName = null): ScheduleEmail
    {
        $fromAddress = $this->getFromEmail($from, $fromName);
        $addressObjects = $this->buildAddresses($addresses);

        if (empty($addressObjects)) {
            throw new BaseException('Addresses must not be empty', 422);
        }

        $scheduleEmail = $this->saveEmail($data, $templateName, $addressObjects, $subject, $fromAddress);

        $this->mailer->send($scheduleEmail);

        return $scheduleEmail;
    }

    /**
     * Send a scheduled email entity through the configured transport.
     */
    public function sendScheduledEmail(ScheduleEmail $scheduleEmail): bool
    {
        return $this->mailer->send($scheduleEmail);
    }

    /**
     * @param EmailAddress[] $addresses
     */
    private function saveEmail(
        array $data,
        string $templateName,
        array $addresses,
        string $subject,
        EmailAddress $from
    ): ScheduleEmail {
        $content = $this->getRenderTemplate($data, $templateName);

        $payload = [];
        $payload['from'] = [
            'name'  => $from->getName(),
            'email' => $from->getEmail(),
            'type'  => $from->getType(),
        ];
        $payload['content'] = $content;
        $payload['addresses'] = $addresses;
        $payload['charset'] = 'utf-8';
        $payload['subject'] = $subject;
        $payload['altText'] = $data['altText'] ?? 'To view the message, please use an HTML compatible email viewer!';

        return $this->scheduleEmailService->add($payload);
    }

    public function getFromEmail(?string $from = null, ?string $fromName = null): EmailAddress
    {
        $config = $this->configuration[Module::CONFIG_KEY];

        if (empty($from) || !$this->validateEmail($from)) {
            $from = $config['DEFAULT_FROM_EMAIL'];
            $fromName = $config['DEFAULT_FROM_NAME_EMAIL'] ?? null;
        }

        return new EmailAddress($from, $fromName, self::TYPE_FROM);
    }

    private function validateEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * @param array<int, array<string, mixed>|EmailAddress> $addresses
     *
     * @return EmailAddress[]
     */
    public function buildAddresses(array $addresses): array
    {
        $emailAddresses = [];
        foreach ($addresses as $address) {
            if ($address instanceof EmailAddress) {
                $emailAddresses[] = $address;
                continue;
            }

            if (is_array($address)) {
                $email = $address['email'] ?? null;
                $type  = isset($address['type']) ? (int) $address['type'] : null;
                $name  = $address['name'] ?? null;

                if ($email !== null && $type !== null && $this->validateEmail($email) && $this->validateEmailType($type)) {
                    $emailAddresses[] = new EmailAddress($email, $name, $type);
                }
            }
        }

        return $emailAddresses;
    }

    private function validateEmailType(int $type): bool
    {
        $types = [
            self::TYPE_TO,
            self::TYPE_CC,
            self::TYPE_BCC,
            self::TYPE_REPLAY_TO,
        ];

        return in_array($type, $types, true);
    }

    private function getRenderTemplate(array $data, string $templatePath): string
    {
        return $this->templateService->render($templatePath, $this->mergeDefaultVariables($data));
    }

    private function mergeDefaultVariables(array $data = []): array
    {
        $defaultVariables = $this->getDefaultVariables();

        if (!empty($data)) {
            return array_merge($defaultVariables, $data);
        }

        return $defaultVariables;
    }

    private function getDefaultVariables(): array
    {
        return $this->configuration[Module::CONFIG_KEY]['DEFAULT_VARIABLES'] ?? [];
    }

    /**
     * @throws BaseException
     */
    private function assertConfig(): void
    {
        $config = $this->configuration[Module::CONFIG_KEY] ?? null;

        if ($config === null) {
            throw new BaseException('Missing solcre_email_schedule configuration', 500);
        }

        if (empty($config['DEFAULT_FROM_EMAIL'])) {
            throw new BaseException('DEFAULT_FROM_EMAIL must be configured', 500);
        }
    }
}
