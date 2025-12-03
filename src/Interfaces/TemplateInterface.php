<?php

declare(strict_types=1);

namespace Solcre\EmailSchedule\Interfaces;

interface TemplateInterface
{
    public function render(string $templateName, array $data = []): string;
}
