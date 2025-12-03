<?php

declare(strict_types=1);

namespace Solcre\EmailSchedule\Entity;

class EmailAddress
{
    private string $email;
    private ?string $name;
    private int $type;

    public function __construct(string $email, ?string $name, int $type)
    {
        $this->email = $email;
        $this->name = $name;
        $this->type = $type;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function setType(int $type): void
    {
        $this->type = $type;
    }
}
