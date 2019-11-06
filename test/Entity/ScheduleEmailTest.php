<?php

use PHPUnit\Framework\TestCase;
use Solcre\EmailSchedule\Entity\ScheduleEmail;

class ScheduleEmailTest extends TestCase
{
    public function testGettersAndSetters()
    {
        $scheduleEmail = new ScheduleEmail();

        $id          = 1;
        $emailFrom   = ["addressee 1", "addressee 2"];
        $addresses   = ["addresses"];
        $subject     = "a subject of email";
        $charset     = "charset";
        $altText     = "altext";
        $content     = "a content";
        $sendAt      = new \DateTime('2019-07-04T18:00');
        $createdAt   = new \DateTime('2019-07-04T19:00');
        $retried     = 1;
        $sendingDate = new \DateTime('2019-07-04T19:00');

        $scheduleEmail->setId($id);
        $scheduleEmail->setEmailFrom($emailFrom);
        $scheduleEmail->setAddresses($addresses);
        $scheduleEmail->setSubject($subject);
        $scheduleEmail->setCharset($charset);
        $scheduleEmail->setAltText($altText);
        $scheduleEmail->setContent($content);
        $scheduleEmail->setSendAt($sendAt);
        $scheduleEmail->setCreatedAt($createdAt);
        $scheduleEmail->setRetried($retried);
        $scheduleEmail->setSendingDate($sendingDate);

        $this->assertEquals($id, $scheduleEmail->getId());
        $this->assertEquals($emailFrom, $scheduleEmail->getEmailFrom());
        $this->assertEquals($addresses, $scheduleEmail->getAddresses());
        $this->assertEquals($subject, $scheduleEmail->getSubject());
        $this->assertEquals($charset, $scheduleEmail->getCharset());
        $this->assertEquals($altText, $scheduleEmail->getAltText());
        $this->assertEquals($content, $scheduleEmail->getContent());
        $this->assertEquals($sendAt, $scheduleEmail->getSendAt());
        $this->assertEquals($createdAt, $scheduleEmail->getCreatedAt());
        $this->assertEquals($retried, $scheduleEmail->getRetried());
        $this->assertEquals($sendingDate, $scheduleEmail->getSendingDate());
    }
}
