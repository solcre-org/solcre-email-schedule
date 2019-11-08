<?php

use PHPUnit\Framework\TestCase;
use Doctrine\ORM\EntityManager;
use Solcre\EmailSchedule\Entity\ScheduleEmail;
use Solcre\EmailSchedule\Exception\BaseException;
use Solcre\EmailSchedule\Service\ScheduleEmailService;
use Solcre\EmailSchedule\Repository\ScheduleEmailRepository;

class ScheduleEmailServiceTest extends TestCase
{
    public function testAdd()
    {
        $data = [
            'charset'   => "charset",
            'addresses' => ["addresses"],
            'altText'   => "altext",
            'content'   => "a content",
            'from'      => ["addressee 1", "addressee 2"],
            'subject'   => "a subject of email"
        ];

        $mockedEntityManager = $this->createMock(EntityManager::class);
        $mockedEntityManager->method('persist')->willReturn(true);
        $mockedEntityManager->method('flush')->willReturn(true);
        $mockedEntityManager->method('getRepository')->willReturn(true); 

        $scheduleEmailService = new scheduleEmailService($mockedEntityManager);

        $expectedScheduleEmail = new ScheduleEmail();
        $expectedScheduleEmail->setCharset($data['charset'] ?? 'UTF-8');
        $expectedScheduleEmail->setAddresses($data['addresses']);
        $expectedScheduleEmail->setAltText($data['altText']);
        $expectedScheduleEmail->setContent($data['content']);
        $expectedScheduleEmail->setCreatedAt(new DateTime());
        $expectedScheduleEmail->setEmailFrom($data['from']);
        $expectedScheduleEmail->setRetried(0);
        $expectedScheduleEmail->setSendingDate(null);
        $expectedScheduleEmail->setSubject($data['subject']);

        $mockedEntityManager->expects($this->once())
        ->method('persist')
        ->with($this->isInstanceOf(ScheduleEmail::class));

        $mockedEntityManager->expects($this->once())
        ->method('flush')
        ->with($this->isInstanceOf(ScheduleEmail::class));

        $scheduleEmailService->add($data);     
    }

    public function testAddWithException()
    {
        // data without 'content' key
        $data = [
            'charset'   => "charset",
            'addresses' => ["addresses"],
            'altText'   => "altext",
            'from'      => ["addressee 1", "addressee 2"],
            'subject'   => "a subject of email"
        ];

        $mockedEntityManager = $this->createMock(EntityManager::class);
        $scheduleEmailService = new scheduleEmailService($mockedEntityManager);

        $this->expectException(BaseException::class);

        $scheduleEmailService->add($data);     
    }

    public function testPatchScheduleEmailWithAllKeys()
    {
        $mockedEntityManager = $this->createMock(EntityManager::class);
        $mockedEntityManager->method('flush')->willReturn(true);

        $scheduleEmailService = new scheduleEmailService($mockedEntityManager);

        $mockedScheduleEmailEntity = $this->getMockBuilder(ScheduleEmail::class)
                                     ->disableOriginalConstructor()
                                     ->setMethods(['setSendAt', 'setSendingDate', 'setRetried'])
                                     ->getMock();

        $data = [
            'sendAt'    => null,
            'isSending' => ["addresses"],
            'retried'   => "altext",
            'content'   => "a content",
            'from'      => ["addressee 1", "addressee 2"],
            'subject'   => "a subject of email"
        ];

        $mockedScheduleEmailEntity->expects($this->once())
        ->method('setSendAt');

        $mockedScheduleEmailEntity->expects($this->once())
        ->method('setSendingDate');

        $mockedScheduleEmailEntity->expects($this->once())
        ->method('setRetried')
        ->with($data['retried']);

        $mockedEntityManager->expects($this->once())
        ->method('flush')
        ->with($this->isInstanceOf(ScheduleEmail::class));

        $scheduleEmailService->patchScheduleEmail($mockedScheduleEmailEntity, $data);   
    }

    public function testPatchScheduleEmailWithoutKeys()
    {
        $mockedEntityManager  = $this->createMock(EntityManager::class);
        $scheduleEmailService = new scheduleEmailService($mockedEntityManager);

        $mockedScheduleEmailEntity =  $this->createMock(ScheduleEmail::class);
        $data                      = [];

        $this->assertEquals($mockedScheduleEmailEntity, $scheduleEmailService->patchScheduleEmail($mockedScheduleEmailEntity, $data));

        $scheduleEmailService->patchScheduleEmail($mockedScheduleEmailEntity, $data);   
    }

    public function testPatchScheduleEmailWithException()
    {
        $mockedEntityManager = $this->createMock(EntityManager::class);
        $mockedEntityManager->method('flush')->will($this->throwException(
          new \Exception()));

        $scheduleEmailService = new scheduleEmailService($mockedEntityManager);

        $mockedScheduleEmailEntity = $this->getMockBuilder(ScheduleEmail::class)
                                     ->disableOriginalConstructor()
                                     ->setMethods(['setSendAt', 'setSendingDate', 'setRetried'])
                                     ->getMock();

        $data = [
            'sendAt'    => null,
            'isSending' => ["addresses"],
            'retried'   => "altext",
            'content'   => "a content",
            'from'      => ["addressee 1", "addressee 2"],
            'subject'   => "a subject of email"
        ];

        $this->expectException(BaseException::class);

        $scheduleEmailService->patchScheduleEmail($mockedScheduleEmailEntity, $data);   
    }

    public function testMarkEmailAsSendingTrue() 
    {
        $mockedEntityManager = $this->createMock(EntityManager::class);
        $mockedRepository    = $this->createMock(ScheduleEmailRepository::class);
        $mockedRepository->method('markEmailAsSending')->willReturn(true);
        $mockedEntityManager->method('getRepository')->willReturn($mockedRepository);

        $scheduleEmailService = new scheduleEmailService($mockedEntityManager);

        $emailToSend = "email to send";
        $this->assertTrue($scheduleEmailService->markEmailAsSending($emailToSend));
    }

    public function testMarkEmailAsSendingFalse() 
    {
        $mockedEntityManager = $this->createMock(EntityManager::class);
        $mockedRepository    = $this->createMock(ScheduleEmailRepository::class);
        $mockedRepository->method('markEmailAsSending')->willReturn(false);
        $mockedEntityManager->method('getRepository')->willReturn($mockedRepository);

        $scheduleEmailService = new scheduleEmailService($mockedEntityManager);

        $emailToSend = "email to send";
        $this->assertFalse($scheduleEmailService->markEmailAsSending($emailToSend));
    }

    public function testMarkEmailAsSendingWithException() 
    {
        $mockedEntityManager = $this->createMock(EntityManager::class);
        $mockedRepository    = $this->createMock(ScheduleEmailRepository::class);
        $mockedRepository->method('markEmailAsSending')->will($this->throwException(
          new Exception())
        );
        $mockedEntityManager->method('getRepository')->willReturn($mockedRepository);

        $scheduleEmailService = new scheduleEmailService($mockedEntityManager);

        $emailToSend = "email to send";

        $this->expectException(Exception::class);

        $scheduleEmailService->markEmailAsSending($emailToSend);
    }

    public function testKeysToPatchExist()
    {
        $data = [
            'key1' => "value1",
            'key2' => "value2",
            'key3' => "value3"
        ];

        $data1 = [
            'sendAt'          => "value1",
            'senisSendingdAt' => "value2",
            'retried'         => "value3"
        ];

        $data2 = [
            'key1'    => "value1",
            'key2'    => "value2",
            'retried' => "value3"
        ];


        $data3 = [
            'key1'    => "value1",
            'key2'    => "value2",
            'key3'    => "value3",
            'retried' => "value4"
        ];

        $data4 = [
            'sendAt'          => "value1",
            'senisSendingdAt' => "value2",
            'retried'         => "value3",
            'key1'            => "value4",
            'key2'            => "value5"
        ];

        $data5 = [
            'key1' => "value1",
            'key2' => "value2",
            'key3' => "value3",
            'key4' => "value4",
            'key5' => "value5"
        ];


        $mockedEntityManager = $this->createMock(EntityManager::class);
        $scheduleEmailService = new scheduleEmailService($mockedEntityManager);

        // without matches
        $this->assertFalse($scheduleEmailService->keysToPatchExist($data));

        // all keys match
        $this->assertTrue($scheduleEmailService->keysToPatchExist($data1));

        // one key match
        $this->assertTrue($scheduleEmailService->keysToPatchExist($data2));

        // one key match and array are of different size
        $this->assertTrue($scheduleEmailService->keysToPatchExist($data3));

        // all keys match and array are of different size
        $this->assertTrue($scheduleEmailService->keysToPatchExist($data4));

        // without matches and array are of different size
        $this->assertFalse($scheduleEmailService->keysToPatchExist($data5));
    }
}
