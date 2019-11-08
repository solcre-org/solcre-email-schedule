<?php

use PHPUnit\Framework\TestCase;
use Solcre\EmailSchedule\Service\EmailService;
use PHPMailer\PHPMailer\PHPMailer;
use Psr\Log\LoggerInterface;
use Solcre\EmailSchedule\Entity\EmailAddress;
use Solcre\EmailSchedule\Entity\ScheduleEmail;
use Solcre\EmailSchedule\Exception\BaseException;
use Solcre\EmailSchedule\Interfaces\TemplateInterface;
use Solcre\EmailSchedule\Module;

class EmailServiceTest extends TestCase
{

}