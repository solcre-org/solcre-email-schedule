<?php

use PHPUnit\Framework\TestCase;
use Solcre\EmailSchedule\Entity\EmailAddress;

class EmailAddressTest extends TestCase
{
    public function testCreateWithParams()
    {
      $email = "jhon.doe@solcre.com";
      $name  = "Jon Doe";
      $type  = 1;

      $emailAdress = new EmailAddress($email, $name, $type);

      $this->assertEquals("jhon.doe@solcre.com", $emailAdress->getEmail());
      $this->assertEquals("Jon Doe", $emailAdress->getName());
      $this->assertSame(1, $emailAdress->getType());
    }

    public function testGetters()
    {
        $email = "jhon.doe@solcre.com";
        $name  = "Jon Doe";
        $type  = 1;

        $emailAdress = new EmailAddress($email, $name, $type); 

        $this->assertEquals($email, $emailAdress->getEmail());
        $this->assertEquals($name, $emailAdress->getName());
        $this->assertEquals($type, $emailAdress->getType());      
    }

    public function testSetters()
    {
        $emailAddress = new EmailAddress("any mail", "any name", 123);

        $email = "jhon.doe@solcre.com";
        $name  = "Jon Doe";
        $type  = 1;

        $emailAddress->setEmail($email);
        $emailAddress->setName($name);
        $emailAddress->setType($type);

        $this->assertEquals($email, $emailAddress->getEmail());
        $this->assertEquals($name, $emailAddress->getName());
        $this->assertEquals($type, $emailAddress->getType());      
    }
}
