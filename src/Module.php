<?php

namespace Solcre\EmailSchedule;

class Module
{
    public const CONFIG_KEY = 'solcre_email_schedule';

    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }
}
