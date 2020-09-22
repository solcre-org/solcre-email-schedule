<?php

namespace Solcre\EmailSchedule\Entity;

class EmailAddress
{
    /* @var string */
    private string $email;
    /* @var string|null */
    private ?string $name;
    /* @var int */
    private int $type;

    /**
     * EmailAddress constructor.
     * @param string $email
     * @param string|null $name
     * @param int $type
     */

    public function __construct(string $email, ?string $name, int $type)
    {
        $this->email = $email;
        $this->name = $name;
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     */
    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return int
     */
    public function getType(): int
    {
        return $this->type;
    }

    /**
     * @param int $type
     */
    public function setType(int $type): void
    {
        $this->type = $type;
    }
}
