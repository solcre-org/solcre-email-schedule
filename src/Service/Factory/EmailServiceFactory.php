<?php

namespace Solcre\SolcreEmailSchedule\Service\Factory;

use Interop\Container\ContainerInterface;
use PHPMailer\PHPMailer\PHPMailer;
use Solcre\SolcreEmailSchedule\Service\EmailService;
use Solcre\SolcreEmailSchedule\Service\ScheduleEmailService;
use Solcre\SolcreEmailSchedule\TemplateService\TwigService;
use Zend\ServiceManager\Factory\FactoryInterface;

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
