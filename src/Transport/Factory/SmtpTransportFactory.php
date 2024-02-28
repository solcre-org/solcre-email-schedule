<?php

namespace Solcre\EmailSchedule\Transport\Factory;

use Laminas\ServiceManager\Factory\FactoryInterface;
use PHPMailer\PHPMailer\PHPMailer;
use Psr\Container\ContainerInterface;
use Solcre\EmailSchedule\Transport\SmtpTransport;

class SmtpTransportFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): SmtpTransport
    {
        return new SmtpTransport(
            new PHPMailer(),
            $container->get('config')
        );
    }
}
