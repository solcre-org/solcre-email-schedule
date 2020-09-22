<?php

namespace Solcre\EmailSchedule\TemplateService\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use RuntimeException;
use Solcre\EmailSchedule\Module;
use Solcre\EmailSchedule\TemplateService\TwigService;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class TwigServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $paths = $container->get('config')[Module::CONFIG_KEY]['TEMPLATES_PATH']['EMAILS'];
        $pathsConfirmed = [];
        foreach ($paths as $path) {
            if (\is_dir($path)) {
                $pathsConfirmed[] = $path;
            }
        }

        if (empty($pathsConfirmed)) {
            throw new RuntimeException(
                'You must add directories paths'
            );
        }

        $loader = new FilesystemLoader($pathsConfirmed);
        $twig = new Environment($loader);

        return new TwigService($twig);
    }
}
