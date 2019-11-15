<?php

namespace SolcreFrameworkTest;

use PHPUnit\Framework\TestCase;
use Doctrine\ORM\EntityManager;
use Solcre\EmailSchedule\Entity\ScheduleEmail;
use Solcre\EmailSchedule\Exception\BaseException;
use Exception;
use DateTime;
use Solcre\EmailSchedule\Service\ScheduleEmailService;
use Solcre\EmailSchedule\Repository\ScheduleEmailRepository;

class ScheduleEmailServiceTest extends TestCase
{
    private $mockedRepository;
    private $mockedEntityManager;
    private $scheduleEmailService;

    protected function setUp(): void
    {
        // mocks used in most tests
        $this->mockedRepository    = $this->createMock(ScheduleEmailRepository::class);
        $this->mockedEntityManager = $this->createMock(EntityManager::class);
        $this->mockedEntityManager->method('persist')->willReturn(true);
        $this->mockedEntityManager->method('flush')->willReturn(true);
        $this->mockedEntityManager->method('getRepository')->willReturn($this->mockedRepository);

        $this->scheduleEmailService = new scheduleEmailService($this->mockedEntityManager);
    }

    public function testAdd(): void
    {
        $data = [
            'charset'   => 'charset',
            'addresses' => ['addresses'],
            'altText'   => 'altText',
            'content'   => 'a content',
            'from'      => ['addressee 1', 'addressee 2'],
            'subject'   => 'a subject of email'
        ];

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

        $this->mockedEntityManager->expects($this->once())
        ->method('persist')
        ->with($this->isInstanceOf(ScheduleEmail::class));

        $this->mockedEntityManager->expects($this->once())
        ->method('flush')
        ->with($this->isInstanceOf(ScheduleEmail::class));

        $this->scheduleEmailService->add($data);
    }

    public function testAddWithoutContentKey(): void
    {
        $data = [
            'charset'   => 'charset',
            'addresses' => ['addresses'],
            'altText'   => 'altText',
            'from'      => ['addressee 1', 'addressee 2'],
            'subject'   => 'a subject of email'
        ];

        $this->expectException(BaseException::class);

        $this->scheduleEmailService->add($data);
    }

    public function testPatchScheduleEmailWithAllKeys(): void
    {
        $mockedScheduleEmailEntity = $this->getMockBuilder(ScheduleEmail::class)
                                     ->disableOriginalConstructor()
                                     ->setMethods(['setSendAt', 'setSendingDate', 'setRetried'])
                                     ->getMock();

        $data = [
            'sendAt'    => null,
            'isSending' => ['addresses'],
            'retried'   => 'retried',
            'content'   => 'a content',
            'from'      => ['addressee 1', 'addressee 2'],
            'subject'   => 'a subject of email'
        ];

        $mockedScheduleEmailEntity->expects($this->once())
        ->method('setSendAt');

        $mockedScheduleEmailEntity->expects($this->once())
        ->method('setSendingDate');

        $mockedScheduleEmailEntity->expects($this->once())
        ->method('setRetried')
        ->with($data['retried']);

        $this->mockedEntityManager->expects($this->once())
        ->method('flush')
        ->with($this->isInstanceOf(ScheduleEmail::class));

        $this->scheduleEmailService->patchScheduleEmail($mockedScheduleEmailEntity, $data);
    }

    public function testPatchScheduleEmailWithoutKeys(): void
    {
        $mockedScheduleEmailEntity =  $this->createMock(ScheduleEmail::class);
        $data                      = [];

        $this->assertEquals($mockedScheduleEmailEntity, $this->scheduleEmailService->patchScheduleEmail($mockedScheduleEmailEntity, $data));
    }

    public function testPatchScheduleEmailWithException(): void
    {
        $mockedEntityManager = $this->createMock(EntityManager::class);
        $mockedEntityManager->method('flush')->will($this->throwException(
            new Exception()
        ));

        $scheduleEmailService = new scheduleEmailService($mockedEntityManager);

        $mockedScheduleEmailEntity = $this->getMockBuilder(ScheduleEmail::class)
                                     ->disableOriginalConstructor()
                                     ->setMethods(['setSendAt', 'setSendingDate', 'setRetried'])
                                     ->getMock();

        $data = [
            'sendAt'    => null,
            'isSending' => ['addresses'],
            'retried'   => 'retried',
            'content'   => 'a content',
            'from'      => ['addressee 1', 'addressee 2'],
            'subject'   => 'a subject of email'
        ];

        $this->expectException(BaseException::class);

        $scheduleEmailService->patchScheduleEmail($mockedScheduleEmailEntity, $data);
    }

    public function setUpServiceForOthersCases($mockedRepository): ScheduleEmailService
    {
        $mockedEntityManager = $this->createMock(EntityManager::class);
        $mockedEntityManager->method('getRepository')->willReturn($mockedRepository);

        $scheduleEmailService = new scheduleEmailService($mockedEntityManager);

        return $scheduleEmailService;
    }

    public function testMarkEmailAsSendingTrue(): void
    {
        $mockedRepository = $this->createMock(ScheduleEmailRepository::class);
        $mockedRepository->method('markEmailAsSending')->willReturn(true);

        $scheduleEmailService = $this->setUpServiceForOthersCases($mockedRepository);

        $emailToSend = 'email to send';
        $this->assertTrue($scheduleEmailService->markEmailAsSending($emailToSend));
    }

    public function testMarkEmailAsSendingFalse(): void
    {
        $mockedRepository = $this->createMock(ScheduleEmailRepository::class);
        $mockedRepository->method('markEmailAsSending')->willReturn(false);

        $scheduleEmailService = $this->setUpServiceForOthersCases($mockedRepository);

        $emailToSend = 'email to send';
        $this->assertFalse($scheduleEmailService->markEmailAsSending($emailToSend));
    }

    public function testMarkEmailAsSendingWithException(): void
    {
        $mockedRepository    = $this->createMock(ScheduleEmailRepository::class);
        $mockedRepository->method('markEmailAsSending')->will($this->throwException(
            new Exception()
        ));

        $scheduleEmailService = $this->setUpServiceForOthersCases($mockedRepository);

        $emailToSend = 'email to send';

        $this->expectException(Exception::class);

        $scheduleEmailService->markEmailAsSending($emailToSend);
    }

    public function testAnyArrayKeyExist(): void
    {
        $data = [
            'key1' => 'value1',
            'key2' => 'value2',
            'key3' => 'value3'
        ];

        $data1 = [
            'sendAt'    => 'value1',
            'SendingAt' => 'value2',
            'retried'   => 'value3'
        ];

        $data2 = [
            'key1'    => 'value1',
            'key2'    => 'value2',
            'retried' => 'value3'
        ];


        $data3 = [
            'key1'    => 'value1',
            'key2'    => 'value2',
            'key3'    => 'value3',
            'retried' => 'value4'
        ];

        $data4 = [
            'sendAt'    => 'value1',
            'SendingAt' => 'value2',
            'retried'   => 'value3',
            'key1'      => 'value4',
            'key2'      => 'value5'
        ];

        $data5 = [
            'key1' => 'value1',
            'key2' => 'value2',
            'key3' => 'value3',
            'key4' => 'value4',
            'key5' => 'value5'
        ];

        $keys = ['sendAt', 'isSending', 'retried'];

        // without matches
        $this->assertFalse($this->scheduleEmailService->anyArrayKeyExist($keys, $data));

        // all keys match
        $this->assertTrue($this->scheduleEmailService->anyArrayKeyExist($keys, $data1));

        // one key match
        $this->assertTrue($this->scheduleEmailService->anyArrayKeyExist($keys, $data2));

        // one key match and array are of different size
        $this->assertTrue($this->scheduleEmailService->anyArrayKeyExist($keys, $data3));

        // all keys match and array are of different size
        $this->assertTrue($this->scheduleEmailService->anyArrayKeyExist($keys, $data4));

        // without matches and array are of different size
        $this->assertFalse($this->scheduleEmailService->anyArrayKeyExist($keys, $data5));
    }

    public function testFetchAvailableScheduledEmails(): void
    {
        $producedInRepository = ['array return by Repository'];
        $mockedRepository     = $this->createMock(ScheduleEmailRepository::class);
        $mockedRepository->method('fetchAvailableScheduledEmails')->willReturn($producedInRepository);
        ;

        $scheduleEmailService = $this->setUpServiceForOthersCases($mockedRepository);

        $this->assertEquals($scheduleEmailService->fetchAvailableScheduledEmails(), $producedInRepository);
    }

    public function testFetchAvailableScheduledEmailsWithException(): void
    {
        $mockedRepository     = $this->createMock(ScheduleEmailRepository::class);
        $producedInRepository = ['array return by Repository'];
        $mockedRepository->method('fetchAvailableScheduledEmails')->will($this->throwException(new Exception()));

        $scheduleEmailService = $this->setUpServiceForOthersCases($mockedRepository);

        $this->expectException(Exception::class);
        $scheduleEmailService->fetchAvailableScheduledEmails();
    }

    public function testProcessDelayedEmails(): void
    {
        $mockedRepository = $this->createMock(ScheduleEmailRepository::class);
        $mockedRepository->method('processDelayedEmails');

        $scheduleEmailService = $this->setUpServiceForOthersCases($mockedRepository);
 
        $mockedRepository->expects($this->once())
        ->method('processDelayedEmails');

        $scheduleEmailService->processDelayedEmails();
    }

    public function testProcessDelayedEmailsWithException(): void
    {
        $mockedRepository = $this->createMock(ScheduleEmailRepository::class);
        $mockedRepository->method('processDelayedEmails')->will($this->throwException(new Exception()));

        $scheduleEmailService = $this->setUpServiceForOthersCases($mockedRepository);
 
        $this->expectException(Exception::class);

        $scheduleEmailService->processDelayedEmails();
    }
}
