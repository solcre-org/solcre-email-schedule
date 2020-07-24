<?php

namespace Solcre\EmailSchedule\TemplateService\Factory;

use Interop\Container\ContainerInterface;
use RuntimeException;
use Solcre\EmailSchedule\Module;
use Solcre\EmailSchedule\TemplateService\TwigService;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Laminas\ServiceManager\Factory\FactoryInterface;

class TwigServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $configPaths = $container->get('config');
        if (! isset($configPaths[Module::CONFIG_KEY])) {
            throw new RuntimeException(
                'No config was found for Solcre Email Schedule. Did you copy the `solcre_email_schedule.local.php` file to your autoload folder?'
            );
        }
        $loader = new FilesystemLoader($configPaths[Module::CONFIG_KEY]['TEMPLATES_PATH']);
        $twig = new Environment($loader);

        return new TwigService($twig);
    }
}
