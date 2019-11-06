<?php

namespace Solcre\EmailSchedule\Interfaces;

interface TemplateInterface
{
    public function render(string $templateName, array $data = []);
}
