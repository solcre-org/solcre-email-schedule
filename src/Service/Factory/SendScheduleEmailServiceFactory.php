<?php

namespace Solcre\SolcreEmailSchedule\Service\Factory;

use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use Solcre\SolcreEmailSchedule\Service\EmailService;
use Solcre\SolcreEmailSchedule\Service\ScheduleEmailService;
use Solcre\SolcreEmailSchedule\Service\SendScheduleEmailService;
use Zend\ServiceManager\Factory\FactoryInterface;

class SendScheduleEmailServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $doctrineService = $container->get(EntityManager::class);
        $scheduleEmailService = $container->get(ScheduleEmailService::class);
        $emailService = $container->get(EmailService::class);
        $logger = null; // TODO Soportar Loggger
        return new SendScheduleEmailService($doctrineService, $scheduleEmailService, $emailService, $logger);
    }
}
