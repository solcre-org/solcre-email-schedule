<?php

namespace Solcre\EmailSchedule\Service\Factory;

use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use Solcre\EmailSchedule\Service\ScheduleEmailService;
use Zend\ServiceManager\Factory\FactoryInterface;

class ScheduleEmailServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $doctrineService = $container->get(EntityManager::class);
        
        return new ScheduleEmailService($doctrineService);
    }
}
