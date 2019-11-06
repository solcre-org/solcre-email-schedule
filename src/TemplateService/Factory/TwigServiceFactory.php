<?php

namespace Solcre\EmailSchedule\TemplateService\Factory;

use Interop\Container\ContainerInterface;
use Solcre\EmailSchedule\Module;
use Solcre\EmailSchedule\TemplateService\TwigService;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Zend\ServiceManager\Factory\FactoryInterface;

class TwigServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $configPaths = $container->get('config');
        $loader = new FilesystemLoader($configPaths[Module::CONFIG_KEY]['TEMPLATES_PATH']);
        $twig = new Environment($loader);

        return new TwigService($twig);
    }
}
