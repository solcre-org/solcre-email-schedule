<?php

declare(strict_types=1);

namespace Solcre\EmailSchedule\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Solcre\EmailSchedule\Repository\ScheduleEmailRepository;

#[ORM\Entity(repositoryClass: ScheduleEmailRepository::class)]
#[ORM\Table(name: 'schedule_emails')]
class ScheduleEmail
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'json', name: 'email_from')]
    private array $emailFrom = [];

    #[ORM\Column(type: 'json')]
    private array $addresses = [];

    #[ORM\Column(type: 'string')]
    private string $subject = '';

    #[ORM\Column(type: 'string')]
    private string $charset = 'utf-8';

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $altText = null;

    #[ORM\Column(type: 'text')]
    private string $content = '';

    #[ORM\Column(type: 'datetime', name: 'send_at', nullable: true)]
    private ?DateTime $sendAt = null;

    #[ORM\Column(type: 'datetime', name: 'created_at')]
    private DateTime $createdAt;

    #[ORM\Column(type: 'integer')]
    private int $retried = 0;

    #[ORM\Column(type: 'datetime', name: 'sending_date', nullable: true)]
    private ?DateTime $sendingDate = null;

    public function __construct()
    {
        $this->createdAt = new DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return EmailAddress[]
     */
    public function getAddresses(): array
    {
        $addresses = [];

        foreach ($this->addresses as $address) {
            if ($address instanceof EmailAddress) {
                $addresses[] = $address;
                continue;
            }

            if (isset($address['email'], $address['type'])) {
                $addresses[] = new EmailAddress(
                    $address['email'],
                    $address['name'] ?? null,
                    (int) $address['type']
                );
            }
        }

        return $addresses;
    }

    /**
     * @param EmailAddress[] $addresses
     */
    public function setAddresses(array $addresses): void
    {
        $this->addresses = $addresses;
    }

    public function getCharset(): string
    {
        return $this->charset;
    }

    public function setCharset(string $charset): void
    {
        $this->charset = $charset;
    }

    public function getAltText(): ?string
    {
        return $this->altText;
    }

    public function setAltText(?string $altText): void
    {
        $this->altText = $altText;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    public function getSendAt(): ?DateTime
    {
        return $this->sendAt;
    }

    public function setSendAt(?DateTime $sendAt): void
    {
        $this->sendAt = $sendAt;
    }

    public function getEmailFrom(): array
    {
        return $this->emailFrom;
    }

    public function setEmailFrom(array $emailFrom): void
    {
        $this->emailFrom = $emailFrom;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function setSubject(string $subject): void
    {
        $this->subject = $subject;
    }

    public function getRetried(): int
    {
        return $this->retried;
    }

    public function setRetried(int $retried): void
    {
        $this->retried = $retried;
    }

    public function getSendingDate(): ?DateTime
    {
        return $this->sendingDate;
    }

    public function setSendingDate(?DateTime $sendingDate): void
    {
        $this->sendingDate = $sendingDate;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }
}
