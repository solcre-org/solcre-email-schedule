<?php

namespace Solcre\EmailSchedule\Service\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use PHPMailer\PHPMailer\PHPMailer;
use Solcre\EmailSchedule\Service\EmailService;
use Solcre\EmailSchedule\Service\ScheduleEmailService;
use Solcre\EmailSchedule\TemplateService\TwigService;

class EmailServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $mailer = new PHPMailer();
        $configuration = $container->get('config');
        $scheduleEmailService = $container->get(ScheduleEmailService::class);
        $twigService = $container->get(TwigService::class);
        $logger = null; // TODO Soportar Loggger
        return new EmailService($mailer, $configuration, $scheduleEmailService, $twigService, $logger);
    }
}
