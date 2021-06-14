<?php

namespace Solcre\EmailSchedule\Service\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use PHPMailer\PHPMailer\PHPMailer;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Solcre\EmailSchedule\Module;
use Solcre\EmailSchedule\Service\EmailService;
use Solcre\EmailSchedule\Service\ScheduleEmailService;
use Solcre\EmailSchedule\TemplateService\SmartyService;
use Solcre\EmailSchedule\TemplateService\TwigService;

class EmailServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): EmailService
    {
        $mailer = new PHPMailer();
        $configuration = $container->get('config');
        $scheduleEmailService = $container->get(ScheduleEmailService::class);

        if (! isset($configuration[Module::CONFIG_KEY])) {
            throw new RuntimeException(
                'No config was found for Solcre Email Schedule. Did you copy the `solcre_email_schedule.local.php` file to your autoload folder?'
            );
        }

        $templateEngine = $configuration[Module::CONFIG_KEY]['TEMPLATE_ENGINE'] ?? null;
        $templateService = $container->get(TwigService::class);
        if ($templateEngine === EmailService::TEMPLATE_ENGINE_SMARTY) {
            $templateService = $container->get(SmartyService::class);
        }

        $loggerClass = null;
        $logger = $configuration[Module::CONFIG_KEY]['LOGGER_CLASS'] ?? null;

        if ($logger !== null && $container->has($logger)) {
            $logger = $container->get($logger);
            if (! $logger instanceof LoggerInterface) {
                $loggerClass = null;
            }
        }

        return new EmailService($mailer, $configuration, $scheduleEmailService, $templateService, $loggerClass);
    }
}
