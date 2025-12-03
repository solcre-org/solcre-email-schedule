<?php

declare(strict_types=1);

namespace EmailScheduleTest\Service;

use DateTime;
use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\TestCase;
use Solcre\EmailSchedule\Entity\EmailAddress;
use Solcre\EmailSchedule\Entity\ScheduleEmail;
use Solcre\EmailSchedule\Exception\BaseException;
use Solcre\EmailSchedule\Repository\ScheduleEmailRepository;
use Solcre\EmailSchedule\Service\ScheduleEmailService;

class ScheduleEmailServiceTest extends TestCase
{
    private EntityManager $entityManager;
    private ScheduleEmailRepository $repository;
    private ScheduleEmailService $service;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(ScheduleEmailRepository::class);
        $this->entityManager = $this->createMock(EntityManager::class);
        $this->entityManager->method('getRepository')->willReturn($this->repository);

        $this->service = new ScheduleEmailService($this->entityManager);
    }

    public function testAddPersistsScheduleEmail(): void
    {
        $data = [
            'charset'   => 'utf-8',
            'addresses' => [new EmailAddress('a@example.com', null, 2)],
            'altText'   => 'alt',
            'content'   => 'content',
            'from'      => ['email' => 'from@example.com', 'name' => null, 'type' => 1],
            'subject'   => 'subject',
        ];

        $this->entityManager
            ->expects(self::once())
            ->method('persist')
            ->with(self::isInstanceOf(ScheduleEmail::class));

        $this->entityManager
            ->expects(self::once())
            ->method('flush')
            ->with(self::isInstanceOf(ScheduleEmail::class));

        $result = $this->service->add($data);

        self::assertInstanceOf(ScheduleEmail::class, $result);
        self::assertSame('subject', $result->getSubject());
    }

    public function testAddWithMissingDataThrows(): void
    {
        $data = [
            'addresses' => [],
            'from'      => [],
        ];

        $this->expectException(\InvalidArgumentException::class);
        $this->service->add($data);
    }

    public function testPatchScheduleEmailUpdatesFields(): void
    {
        $email = new ScheduleEmail();
        $email->setRetried(0);

        $data = ['sendAt' => true, 'isSending' => false, 'retried' => 2];

        $this->entityManager
            ->expects(self::once())
            ->method('flush')
            ->with($email);

        $updated = $this->service->patchScheduleEmail($email, $data);

        self::assertInstanceOf(DateTime::class, $updated->getSendAt());
        self::assertSame(2, $updated->getRetried());
        self::assertNull($updated->getSendingDate());
    }

    public function testPatchScheduleEmailCatchesExceptions(): void
    {
        $email = new ScheduleEmail();

        $this->entityManager
            ->method('flush')
            ->willThrowException(new \RuntimeException('fail'));

        $this->expectException(BaseException::class);
        $this->service->patchScheduleEmail($email, ['sendAt' => true]);
    }

    public function testAnyArrayKeyExist(): void
    {
        $keys = ['a', 'b'];
        self::assertTrue($this->service->anyArrayKeyExist($keys, ['a' => 1]));
        self::assertFalse($this->service->anyArrayKeyExist($keys, ['c' => 1]));
    }
}
