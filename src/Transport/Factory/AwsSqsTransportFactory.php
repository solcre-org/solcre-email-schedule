<?php

namespace Solcre\EmailSchedule\Transport\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Solcre\EmailSchedule\Module;
use Solcre\EmailSchedule\Transport\AwsSqsTransport;

class AwsSqsTransportFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): AwsSqsTransport
    {
        $credentials = $container->get('config')[Module::CONFIG_KEY]['transport']['aws-sqs'];

        return new AwsSqsTransport(
            $credentials['url'],
            $credentials['region'] ?? 'us-east-1');
    }
}
