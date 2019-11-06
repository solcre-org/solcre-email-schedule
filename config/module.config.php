<?php

namespace ZendSkeletonModule;

use Solcre\EmailSchedule\Service;
use Solcre\EmailSchedule\Service\Factory;
use Solcre\EmailSchedule\TemplateService;
use Solcre\EmailSchedule\TemplateService\Factory as TemplateServiceFactory;

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
