<?php

declare(strict_types=1);

namespace Solcre\EmailSchedule\Exception;

use Exception;

class BaseException extends Exception
{
    protected array $additional = [];

    public function __construct(string $message = '', int $code = 0, array $additional = [])
    {
        $this->additional = $additional;
        parent::__construct($message, $code);
    }

    public function getAdditional(): array
    {
        return $this->additional;
    }
}
