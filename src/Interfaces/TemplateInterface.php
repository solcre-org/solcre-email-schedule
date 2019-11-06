<?php

namespace Solcre\SolcreEmailSchedule\Interfaces;

interface TemplateInterface
{
    public function render(string $templateName, array $data = []);
}
