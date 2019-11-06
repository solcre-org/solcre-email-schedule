<?php

namespace Solcre\SolcreEmailSchedule\Service\Factory;

use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use Solcre\SolcreEmailSchedule\Service\ScheduleEmailService;
use Zend\ServiceManager\Factory\FactoryInterface;

class ScheduleEmailServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $doctrineService = $container->get(EntityManager::class);
        return new ScheduleEmailService($doctrineService);
    }
}
