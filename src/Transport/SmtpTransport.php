<?php

namespace Solcre\EmailSchedule\Transport;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use Solcre\EmailSchedule\Entity\ScheduleEmail;
use Solcre\EmailSchedule\Exception\BaseException;
use Solcre\EmailSchedule\Interfaces\TransportInterface;
use Solcre\EmailSchedule\Module;
use Solcre\EmailSchedule\Service\EmailService;

class SmtpTransport implements TransportInterface
{
    private PHPMailer $mailer;
    private array $configuration;

    /**
     * @param PHPMailer $mailer
     * @param array $configuration
     */
    public function __construct(PHPMailer $mailer, array $configuration)
    {
        $this->mailer = $mailer;
        $this->configuration = $configuration;
    }

    public function send(ScheduleEmail $scheduleEmail): bool
    {
        try {
            $this->mailer->CharSet = $scheduleEmail->getCharset();

            $from = $scheduleEmail->getEmailFrom();
            $this->mailer->setFrom($from['email'], $from['name']);

            $addresses = $scheduleEmail->getAddresses();
            foreach ($addresses as $address) {
                switch ($address->getType()) {
                    case EmailService::TYPE_CC:
                        $this->mailer->addCC($address->getEmail(), $address->getName());
                        break;
                    case EmailService::TYPE_BCC:
                        $this->mailer->addBCC($address->getEmail(), $address->getName());
                        break;
                    case EmailService::TYPE_REPLAY_TO:
                        $this->mailer->addReplyTo($address->getEmail(), $address->getName());
                        break;
                    case EmailService::TYPE_TO:
                    default:
                        $this->mailer->addAddress($address->getEmail(), $address->getName());
                        break;
                }
            }

            $this->mailer->Subject = $scheduleEmail->getSubject();
            $this->mailer->AltBody = $scheduleEmail->getAltText();
            $this->mailer->msgHTML($scheduleEmail->getContent());

            $this->setSmtpCredentials();

            if (!$this->mailer->send()) {
                throw new BaseException($this->mailer->ErrorInfo, 400);
            }

            $this->mailer->clearAddresses();

            return true;
        } catch (\Exception $e) {
            throw new BaseException($e->getMessage(), $e->getCode());
        }
    }

    private function setSmtpCredentials(): void
    {
        $configuration = $this->configuration[Module::CONFIG_KEY]['transport']['smtp'];
        $isSMTP = (bool)$configuration['ACTIVE'];

        if ($isSMTP) {
            $this->mailer->isSMTP();
            $this->mailer->SMTPAuth = true;
            $this->mailer->SMTPDebug = SMTP::DEBUG_OFF;
            if ((bool)$configuration['DEBUG'] === 1) {
                $this->mailer->SMTPDebug = SMTP::DEBUG_SERVER;
            }
            $this->mailer->Host = $configuration['HOST'];
            $this->mailer->Username = $configuration['USERNAME'];
            $this->mailer->Password = $configuration['PASSWORD'];
            $this->mailer->Port = $configuration['PORT'];
            $this->mailer->SMTPSecure = $configuration['SECURE'];
        }
    }

}
