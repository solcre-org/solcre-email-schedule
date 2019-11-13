<?php

namespace SolcreFrameworkTest;

use PHPUnit\Framework\TestCase;
use Solcre\EmailSchedule\Entity\EmailAddress;

class EmailAddressTest extends TestCase
{
    public function testCreateWithParams(): void
    {
        $email = 'jhon.doe@solcre.com';
        $name  = 'Jon Doe';
        $type  = 1;

        $emailAddress = new EmailAddress($email, $name, $type);

        $this->assertEquals('jhon.doe@solcre.com', $emailAddress->getEmail());
        $this->assertEquals('Jon Doe', $emailAddress->getName());
        $this->assertSame(1, $emailAddress->getType());
    }

    public function testGetters(): void
    {
        $email = 'jhon.doe@solcre.com';
        $name  = 'Jon Doe';
        $type  = 1;

        $emailAddress = new EmailAddress($email, $name, $type);

        $this->assertEquals($email, $emailAddress->getEmail());
        $this->assertEquals($name, $emailAddress->getName());
        $this->assertEquals($type, $emailAddress->getType());
    }

    public function testSetters(): void
    {
        $emailAddress = new EmailAddress('any mail', 'any name', 123);

        $email = 'jhon.doe@solcre.com';
        $name  = 'Jon Doe';
        $type  = 1;

        $emailAddress->setEmail($email);
        $emailAddress->setName($name);
        $emailAddress->setType($type);

        $this->assertEquals($email, $emailAddress->getEmail());
        $this->assertEquals($name, $emailAddress->getName());
        $this->assertEquals($type, $emailAddress->getType());
    }
}
