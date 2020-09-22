<?php

namespace Solcre\EmailSchedule;

use Solcre\EmailSchedule\Service;
use Solcre\EmailSchedule\Service\Factory;
use Solcre\EmailSchedule\TemplateService;

return [
    'service_manager' => [
        'factories' => [
            Service\EmailService::class             => Factory\EmailServiceFactory::class,
            Service\ScheduleEmailService::class     => Factory\ScheduleEmailServiceFactory::class,
            Service\SendScheduleEmailService::class => Factory\SendScheduleEmailServiceFactory::class,
            TemplateService\TwigService::class      => TemplateService\Factory\TwigServiceFactory::class,
            TemplateService\SmartyService::class    => TemplateService\Factory\SmartyServiceFactory::class
        ],
    ],
    'doctrine'        => [
        'driver' => [
            'my_annotation_driver' => [
                'paths' => [
                    __DIR__ . '/../src/Entity',
                ],
            ],
            'orm_default'          => [
                'drivers' => [
                    'Solcre\\EmailSchedule\\Entity' => 'my_annotation_driver',
                ],
            ],
        ]
    ],
];
