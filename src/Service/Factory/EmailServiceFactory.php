<?php

namespace Solcre\EmailSchedule\Service\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Solcre\EmailSchedule\Interfaces\TemplateInterface;
use Solcre\EmailSchedule\Interfaces\TransportInterface;
use Solcre\EmailSchedule\Module;
use Solcre\EmailSchedule\Service\EmailService;
use Solcre\EmailSchedule\Service\ScheduleEmailService;
use Solcre\EmailSchedule\TemplateService\SmartyService;
use Solcre\EmailSchedule\TemplateService\TwigService;
use Solcre\EmailSchedule\Transport\AwsSqsTransport;
use Solcre\EmailSchedule\Transport\SmtpTransport;

class EmailServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): EmailService
    {
        $configuration = $container->get('config');

        if (!isset($configuration[Module::CONFIG_KEY])) {
            throw new RuntimeException(
                'No config was found for Solcre Email Schedule. Did you copy the `solcre_email_schedule.local.php` file to your autoload folder?'
            );
        }

        return new EmailService(
            $this->getTransport($container),
            $configuration,
            $container->get(ScheduleEmailService::class),
            $this->getTemplateService($container),
            $this->getLoggerClass($container)
        );
    }

    private function getTransport(ContainerInterface $container): TransportInterface
    {
        $config = $container->get('config')[Module::CONFIG_KEY]['transport'];

        if ($config['driver'] === 'aws-sqs') {
            return $container->get(AwsSqsTransport::class);
        } elseif ($config['driver'] === 'smtp') {
            return $container->get(SmtpTransport::class);
        } else {
            throw new \RuntimeException('Invalid email driver specified');
        }
    }

    private function getTemplateService(ContainerInterface $container): TemplateInterface
    {
        $templateEngine = $configuration[Module::CONFIG_KEY]['TEMPLATE_ENGINE'] ?? null;
        $templateService = $container->get(TwigService::class);
        if ($templateEngine === EmailService::TEMPLATE_ENGINE_SMARTY) {
            $templateService = $container->get(SmartyService::class);
        }

        return $templateService;
    }

    private function getLoggerClass(ContainerInterface $container): ?LoggerInterface
    {
        $logger = $configuration[Module::CONFIG_KEY]['LOGGER_CLASS'] ?? null;

        if ($logger !== null && $container->has($logger)) {
            return $container->get($logger);
        }

        return null;
    }
}
