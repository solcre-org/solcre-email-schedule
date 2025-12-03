<?php

declare(strict_types=1);

namespace Solcre\EmailSchedule\Entity;

class SmtpAccount
{
    private string $host;
    private string $username;
    private string $password;
    private int $port;
    private bool $isTls;

    public function __construct(string $host, string $username, string $password, int $port, bool $isTls = true)
    {
        $this->host = $host;
        $this->username = $username;
        $this->password = $password;
        $this->port = $port;
        $this->isTls = $isTls;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getPort(): int
    {
        return $this->port;
    }

    public function getIsTls(): bool
    {
        return $this->isTls;
    }

    public function setHost(string $host): void
    {
        $this->host = $host;
    }

    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    public function setPort(int $port): void
    {
        $this->port = $port;
    }

    public function setIsTls(bool $isTls): void
    {
        $this->isTls = $isTls;
    }
}
