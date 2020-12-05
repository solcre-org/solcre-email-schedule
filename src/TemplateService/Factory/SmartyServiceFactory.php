<?php

namespace Solcre\EmailSchedule\TemplateService\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Solcre\EmailSchedule\Module;
use Solcre\EmailSchedule\TemplateService\SmartyService;

class SmartyServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): SmartyService
    {
        return new SmartyService(
            new \Smarty(),
            $container->get('config')[Module::CONFIG_KEY]['TEMPLATES_PATH']['EMAILS']
        );
    }
}
