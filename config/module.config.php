<?php

namespace ZendSkeletonModule;

use Solcre\SolcreEmailSchedule\Service;
use Solcre\SolcreEmailSchedule\Service\Factory;
use Solcre\SolcreEmailSchedule\TemplateService;
use Solcre\SolcreEmailSchedule\TemplateService\Factory as TemplateServiceFactory;

return [
    'controllers' => [
        'factories' => [
            Service\EmailService::class             => Factory\EmailServiceFactory::class,
            Service\ScheduleEmailService::class     => Factory\ScheduleEmailServiceFactory::class,
            Service\SendScheduleEmailService::class => Factory\SendScheduleEmailServiceFactory::class,
            TemplateService\TwigService::class      => TemplateServiceFactory\TwigServiceFactory::class
        ],
    ],
];
