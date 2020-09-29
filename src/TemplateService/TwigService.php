<?php

namespace Solcre\EmailSchedule\TemplateService;

use Solcre\EmailSchedule\Interfaces\TemplateInterface;
use Twig\Environment;
use Twig\Error\Error;

class TwigService implements TemplateInterface
{
    private Environment $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    public function render(string $templateName, array $data = []): string
    {
        try {
            return $this->twig->render($templateName . '.twig', $data);
        } catch (Error $e) {
            throw $e;
        }
    }
}
