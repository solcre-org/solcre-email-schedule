<?php

namespace Solcre\EmailSchedule\Entity;

class EmailAddress
{
    /* @var string */
    private $email;
    /* @var string */
    private $name;
    /* @var int */
    private $type;

    /**
     * EmailAddress constructor.
     * @param string $email
     * @param string $name
     * @param int $type
     */
    
    public function __construct(string $email, string $name, int $type)
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
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
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
