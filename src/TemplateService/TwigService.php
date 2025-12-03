<?php

declare(strict_types=1);

namespace Solcre\EmailSchedule\TemplateService;

use Solcre\EmailSchedule\Interfaces\TemplateInterface;
use Twig\Environment;

class TwigService implements TemplateInterface
{
    private Environment $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    public function render(string $templateName, array $data = []): string
    {
        return $this->twig->render($templateName . '.twig', $data);
    }
}
