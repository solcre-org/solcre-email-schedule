<?php

declare(strict_types=1);

namespace Solcre\EmailSchedule;

class Module
{
    public const CONFIG_KEY = 'solcre_email_schedule';

    public function getConfig(): array
    {
        return include __DIR__ . '/../config/module.config.php';
    }
}
