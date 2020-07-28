<?php

namespace Solcre\EmailSchedule\Service;

use Exception;
use PHPMailer\PHPMailer\PHPMailer;
use Psr\Log\LoggerInterface;
use Solcre\EmailSchedule\Entity\EmailAddress;
use Solcre\EmailSchedule\Entity\ScheduleEmail;
use Solcre\EmailSchedule\Entity\SmtpAccount;
use Solcre\EmailSchedule\Exception\BaseException;
use Solcre\EmailSchedule\Interfaces\TemplateInterface;
use Solcre\EmailSchedule\Module;

class EmailService extends LoggerService
{
    public const TYPE_FROM = 1;
    public const TYPE_TO = 2;
    public const TYPE_CC = 3;
    public const TYPE_BCC = 4;
    public const TYPE_REPLAY_TO = 5;

    private $mailer;
    protected $configuration;
    protected $scheduleEmailService;
    protected $templateService;
    protected $smtpAccount;

    public function __construct(PHPMailer $mailer, $configuration, ScheduleEmailService $scheduleEmailService, TemplateInterface $templateService, ?LoggerInterface $logger)
    {
        parent::__construct($logger);
        $this->mailer = $mailer;
        $this->configuration = $configuration;
        $this->scheduleEmailService = $scheduleEmailService;
        $this->templateService = $templateService;
        $this->smtpAccount = null;
    }

    public function sendTpl(array $vars, $templateName, array $addresses, string $subject, $charset = 'UTF-8', $altText = '', $from = null): ?bool
    {
        try {
            $from = $this->getFromEmail($from);
            $addresses = $this->generateAddresses($addresses);

            if (empty($addresses)) {
                throw new BaseException('Addresses must not be empty', 422);
            }

            $content = $this->getRenderTemplate($vars, $templateName);

            return $this->sendOrSaveEmail($from, $addresses, $content, $charset, $subject, $altText);
        } catch (Exception $e) {
            $this->logMessage($e, ['EMAIl-SERVICE-SEND-TPL']);
            unset($e);

            return false;
        }
    }

    public function getFromEmail($from = null, $fromName = null): EmailAddress
    {
        if (empty($from) || ! $this->validateEmail($from)) {
            $from = $this->configuration[Module::CONFIG_KEY]['DEFAULT_FROM_EMAIL'];
            $fromName = $this->configuration[Module::CONFIG_KEY]['DEFAULT_FROM_NAME_EMAIL'] ?? null;
        }

        return new EmailAddress($from, $fromName, self::TYPE_FROM);
    }

    private function validateEmail($email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    public function generateAddresses(array $addresses): array
    {
        $emailAddresses = [];
        foreach ($addresses as $address) {
            if (\is_array($address)) {
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

        return \in_array($type, $types, true);
    }

    private function getRenderTemplate(array $data, string $templatePath): string
    {
        return $this->templateService->render($templatePath, $this->mergeDefaultVariables($data));
    }

    private function mergeDefaultVariables(array $data = []): array
    {
        $defaultVariables = $this->getDefaultVariables();
        if (! empty($data)) {
            return \array_merge($defaultVariables, $data);
        }
        return $defaultVariables;
    }

    private function getDefaultVariables(): array
    {
        return $this->configuration[Module::CONFIG_KEY]['DEFAULT_VARIABLES'];
    }

    private function sendOrSaveEmail(EmailAddress $from, array $addresses, string $content, string $charset, string $subject, $altText = ''): ?bool
    {
        try {
            $isSaved = $this->saveEmail($from, $addresses, $subject, $content, $altText, $charset);

            if (! $isSaved) {
                return $this->send($from, $addresses, $subject, $content, $altText, $charset);
            }

            return $isSaved;
        } catch (\Exception $e) {
            $this->logMessage($e, ['EMAIl-SERVICE-SEND-OR-SAVE-EMAIL']);
            unset($e);

            return $this->send($from, $addresses, $subject, $content, $altText, $charset);
        }
    }

    private function saveEmail(EmailAddress $from, $addresses, $subject, $content, $altText, $charset): bool
    {
        $data = [];
        $data['from'] = [
            'name'  => $from->getName() ?? null,
            'email' => $from->getEmail(),
            'type'  => $from->getType()
        ];
        $data['content'] = $content;
        $data['charset'] = $charset;
        $data['subject'] = $subject;
        $data['altText'] = $altText ?? 'To view the message, please use an HTML compatible email viewer!';

        foreach ($addresses as $address) {
            if ($address instanceof EmailAddress) {
                switch ($address->getType()) {
                    case self::TYPE_CC:
                        $data['addresses'][] = [
                            'name'  => $address->getName(),
                            'email' => $address->getEmail(),
                            'type'  => self::TYPE_CC
                        ];
                        break;
                    case self::TYPE_BCC:
                        $data['addresses'][] = [
                            'name'  => $address->getName(),
                            'email' => $address->getEmail(),
                            'type'  => self::TYPE_BCC
                        ];
                        break;
                    case self::TYPE_REPLAY_TO:
                        $data['addresses'][] = [
                            'name'  => $address->getName(),
                            'email' => $address->getEmail(),
                            'type'  => self::TYPE_REPLAY_TO
                        ];
                        break;
                    case self::TYPE_TO:
                    default:
                        $data['addresses'][] = [
                            'name'  => $address->getName(),
                            'email' => $address->getEmail(),
                            'type'  => self::TYPE_TO
                        ];
                        break;
                }
            }
        }

        $scheduleEntity = $this->scheduleEmailService->add($data);

        return $scheduleEntity instanceof ScheduleEmail;
    }

    public function send(EmailAddress $from, array $addresses, string $subject, string $content, string $charset = PHPMailer::CHARSET_UTF8, $altText = 'To view the message, please use an HTML compatible email viewer!'): ?bool
    {
        try {
            $this->mailer->CharSet = $charset;
            $this->mailer->setFrom($from->getEmail(), $from->getName());

            foreach ($addresses as $address) {
                switch ($address->getType()) {
                    case self::TYPE_CC:
                        $this->mailer->addCC($address->getEmail(), $address->getName());
                        break;
                    case self::TYPE_BCC:
                        $this->mailer->addBCC($address->getEmail(), $address->getName());
                        break;
                    case self::TYPE_REPLAY_TO:
                        $this->mailer->addReplyTo($address->getEmail(), $address->getName());
                        break;
                    case self::TYPE_TO:
                    default:
                        $this->mailer->addAddress($address->getEmail(), $address->getName());
                        break;
                }
            }

            $this->mailer->Subject = $subject;
            $this->mailer->AltBody = $altText;
            $this->mailer->msgHTML($content);

            $this->setSmtpCredentials();

            if (! $this->mailer->send()) {
                throw new BaseException($this->mailer->ErrorInfo, 400);
            }

            $this->mailer->clearAddresses();

            return true;
        } catch (Exception $e) {
            throw new BaseException($e->getMessage(), $e->getCode());
        }
    }

    private function setSmtpCredentials(): void
    {
        if($this->smtpAccount instanceof SmtpAccount)
        {
            $this->mailer->isSMTP();
            $this->mailer->SMTPAuth = true;
            $this->mailer->SMTPAutoTLS = $this->smtpAccount->getIsTls();
            $this->mailer->Host = $this->smtpAccount->getHost();
            $this->mailer->Port = $this->smtpAccount->getPort();
            $this->mailer->Username = $this->smtpAccount->getUsername();
            $this->mailer->Password = $this->smtpAccount->getPassword();
        }
        else
        {
            $isSMTP = (boolean) $this->configuration[Module::CONFIG_KEY]['SMTP_CREDENTIALS']['ACTIVE'];
            if ($isSMTP)
            {
                $this->mailer->isSMTP();
                $this->mailer->SMTPAuth = true;
                $this->mailer->SMTPDebug = $this->configuration[Module::CONFIG_KEY]['SMTP_CREDENTIALS']['DEBUG'];
                $this->mailer->Host = $this->configuration[Module::CONFIG_KEY]['SMTP_CREDENTIALS']['HOST'];
                $this->mailer->Username = $this->configuration[Module::CONFIG_KEY]['SMTP_CREDENTIALS']['USERNAME'];
                $this->mailer->Password = $this->configuration[Module::CONFIG_KEY]['SMTP_CREDENTIALS']['PASSWORD'];
                $this->mailer->Port = $this->configuration[Module::CONFIG_KEY]['SMTP_CREDENTIALS']['PORT'];
                $this->mailer->SMTPSecure = $this->configuration[Module::CONFIG_KEY]['SMTP_CREDENTIALS']['SECURE'];
            }
        }
    }

    public function setSmtpAccount(SmtpAccount $smtpAccount): void
    {
        $this->smtpAccount = $smtpAccount;
    }
}
