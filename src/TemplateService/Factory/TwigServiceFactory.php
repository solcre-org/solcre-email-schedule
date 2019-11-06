<?php

namespace Solcre\SolcreEmailSchedule\TemplateService\Factory;

use Interop\Container\ContainerInterface;
use Solcre\SolcreEmailSchedule\Module;
use Solcre\SolcreEmailSchedule\TemplateService\TwigService;
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
