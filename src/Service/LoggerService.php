<?php

namespace Solcre\EmailSchedule\Service;

use Exception;
use Psr\Log\LoggerInterface;
use Solcre\EmailSchedule\Exception\BaseException;
use function in_array;
use function strtolower;

class LoggerService
{
    public const LEVELS = ['emergency', 'alert', 'critical', 'error', 'warning', 'notice', 'info', 'debug', 'log'];
    private ?LoggerInterface $logger;

    /**
     * @param LoggerInterface|null $logger
     */
    public function __construct(?LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @throws \Solcre\EmailSchedule\Exception\BaseException
     */
    public function logMessage(Exception $exception, array $context, string $level = 'error'): void
    {
        if ($this->logger instanceof LoggerInterface) {
            $this->validateLevel($level);
            $this->logger->$level($exception->getMessage(), $context);
        }
    }

    /**
     * @throws \Solcre\EmailSchedule\Exception\BaseException
     */
    private function validateLevel(string $level): void
    {
        $level = strtolower($level);

        if (in_array($level, self::LEVELS, true)) {
            throw new BaseException('Log level does not exists', 404);
        }
    }
}
