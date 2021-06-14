<?php

namespace Solcre\EmailSchedule\Service\Factory;

use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use Solcre\EmailSchedule\Service\EmailService;
use Solcre\EmailSchedule\Service\ScheduleEmailService;
use Solcre\EmailSchedule\Service\SendScheduleEmailService;
use Laminas\ServiceManager\Factory\FactoryInterface;

class SendScheduleEmailServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): SendScheduleEmailService
    {
        $doctrineService = $container->get(EntityManager::class);
        $scheduleEmailService = $container->get(ScheduleEmailService::class);
        $emailService = $container->get(EmailService::class);
        $logger = null; // TODO Soportar Loggger

        return new SendScheduleEmailService($doctrineService, $scheduleEmailService, $emailService, $logger);
    }
}
