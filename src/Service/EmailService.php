<?php

namespace Solcre\EmailSchedule\Service;

use Psr\Log\LoggerInterface;
use Solcre\EmailSchedule\Entity\EmailAddress;
use Solcre\EmailSchedule\Entity\ScheduleEmail;
use Solcre\EmailSchedule\Exception\BaseException;
use Solcre\EmailSchedule\Interfaces\TemplateInterface;
use Solcre\EmailSchedule\Interfaces\TransportInterface;
use Solcre\EmailSchedule\Module;

class EmailService extends LoggerService
{
    public const TEMPLATE_ENGINE_SMARTY = 'Twig';

    public const TYPE_FROM = 1;
    public const TYPE_TO = 2;
    public const TYPE_CC = 3;
    public const TYPE_BCC = 4;
    public const TYPE_REPLAY_TO = 5;
    private $configuration;
    private ScheduleEmailService $scheduleEmailService;
    private TemplateInterface $templateService;
    private TransportInterface $mailer;

    public function __construct(
        TransportInterface   $mailer,
                             $configuration,
        ScheduleEmailService $scheduleEmailService,
        TemplateInterface    $templateService,
        ?LoggerInterface     $logger)
    {
        parent::__construct($logger);
        $this->mailer = $mailer;
        $this->configuration = $configuration;
        $this->scheduleEmailService = $scheduleEmailService;
        $this->templateService = $templateService;
    }

    public function sendEmail(array $data, string $templateName, array $addresses, string $subject, string $from = null): void
    {
        $from = $this->getFromEmail($from);
        $scheduleEmail = $this->saveEmail($data, $templateName, $addresses, $subject, $from);

        $this->mailer->send($scheduleEmail);
    }

    private function saveEmail(
        array        $data,
        string       $templateName,
        array        $addresses,
        string       $subject,
        EmailAddress $from): ScheduleEmail
    {
        if (empty($addresses)) {
            throw new BaseException('Addresses must not be empty', 422);
        }

        $content = $this->getRenderTemplate($data, $templateName);

        $data = [];
        $data['from'] = [
            'name'  => $from->getName(),
            'email' => $from->getEmail(),
            'type'  => $from->getType()
        ];
        $data['content'] = $content;
        $data['addresses'] = $addresses;
        $data['charset'] = 'utf-8';
        $data['subject'] = $subject;
        $data['altText'] = $altText ?? 'To view the message, please use an HTML compatible email viewer!';

        return $this->scheduleEmailService->add($data);
    }

    private function getFromEmail($from = null, $fromName = null): EmailAddress
    {
        if (empty($from) || !$this->validateEmail($from)) {
            $from = $this->configuration[Module::CONFIG_KEY]['DEFAULT_FROM_EMAIL'];
            $fromName = $this->configuration[Module::CONFIG_KEY]['DEFAULT_FROM_NAME_EMAIL'] ?? null;
        }

        return new EmailAddress($from, $fromName, self::TYPE_FROM);
    }

    private function validateEmail($email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    private function generateAddresses(array $addresses): array
    {
        $emailAddresses = [];
        foreach ($addresses as $address) {
            if ($address instanceof EmailAddress) {
                $emailAddresses[] = $address;
                continue;
            }

            if (is_array($address)) {
                $email = $address['email'] ?? null;
                $type = $address['type'] ?? null;
                $name = $address['name'] ?? null;

                if ($this->validateEmail($email) && $this->validateEmailType($type)) {
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
            self::TYPE_REPLAY_TO
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
        return $this->configuration[Module::CONFIG_KEY]['DEFAULT_VARIABLES'];
    }

}
