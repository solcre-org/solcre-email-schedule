<?php

namespace Solcre\SolcreEmailSchedule\TemplateService;

use Solcre\SolcreEmailSchedule\Interfaces\TemplateInterface;
use Twig\Environment;

class TwigService implements TemplateInterface
{
    private $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    public function render(string $templateName, array $data = []): string
    {
        return $this->twig->render($templateName . '.twig', $data);
    }
}
