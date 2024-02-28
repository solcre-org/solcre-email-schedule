<?php

namespace Solcre\EmailSchedule\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Solcre\EmailSchedule\Repository\ScheduleEmailRepository")
 * @ORM\Table(name="schedule_emails")
 */
class ScheduleEmail
{

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="json", name="email_from")
     */
    private array $emailFrom;

    /**
     * @ORM\Column(type="json")
     */
    private array $addresses;

    /**
     * @ORM\Column(type="string")
     */
    private string $subject;

    /**
     * @ORM\Column(type="string")
     */
    private string $charset;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private string $altText;

    /**
     * @ORM\Column(type="text")
     */
    private string $content;

    /**
     * @ORM\Column(type="datetime", name="send_at", nullable=true)
     */
    private ?DateTime $sendAt;
    /**
     * @ORM\Column(type="datetime", name="created_at")
     */
    private DateTime $createdAt;

    /**
     * @ORM\Column(type="integer")
     */
    private int $retried;

    /**
     * @ORM\Column(type="datetime", name="sending_date", nullable=true)
     */
    private ?DateTime $sendingDate;

    /**
     * @return integer
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param integer $id
     */
    public function setId($id): void
    {
        $this->id = $id;
    }

    /**
     * @return EmailAddress[]
     */
    public function getAddresses(): array
    {
        foreach ($this->addresses as $address) {
            $addresses[] = new EmailAddress($address['email'], $address['name'], $address['type']);
        }

        return $addresses;
    }

    /**
     * @param array $addresses
     */
    public function setAddresses($addresses): void
    {
        $this->addresses = $addresses;
    }


    /**
     * @return string
     */
    public function getCharset(): string
    {
        return $this->charset;
    }

    /**
     * @param string $charset
     */
    public function setCharset($charset): void
    {
        $this->charset = $charset;
    }

    /**
     * @return string
     */
    public function getAltText(): string
    {
        return $this->altText;
    }

    /**
     * @param string $altText
     */
    public function setAltText($altText): void
    {
        $this->altText = $altText;
    }

    /**
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * @param string $content
     */
    public function setContent($content): void
    {
        $this->content = $content;
    }

    /**
     * @return DateTime|null
     */
    public function getSendAt(): ?DateTime
    {
        return $this->sendAt;
    }

    /**
     * @param DateTime $sendAt
     */
    public function setSendAt(DateTime $sendAt): void
    {
        $this->sendAt = $sendAt;
    }

    /**
     * @return array
     */
    public function getEmailFrom(): array
    {
        return $this->emailFrom;
    }

    /**
     * @param array $emailFrom
     */
    public function setEmailFrom($emailFrom): void
    {
        $this->emailFrom = $emailFrom;
    }

    /**
     * @return string
     */
    public function getSubject(): string
    {
        return $this->subject;
    }

    /**
     * @param string $subject
     */
    public function setSubject($subject): void
    {
        $this->subject = $subject;
    }

    /**
     * @return int
     */
    public function getRetried(): int
    {
        return $this->retried;
    }

    /**
     * @param int $retried
     */
    public function setRetried($retried): void
    {
        $this->retried = $retried;
    }

    /**
     * @return DateTime|null
     */
    public function getSendingDate(): ?DateTime
    {
        return $this->sendingDate;
    }

    /**
     * @param DateTime|null $sendingDate
     */
    public function setSendingDate(?DateTime $sendingDate): void
    {
        $this->sendingDate = $sendingDate;
    }

    /**
     * @return DateTime
     */
    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    /**
     * @param DateTime $createdAt
     */
    public function setCreatedAt(DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }
}
