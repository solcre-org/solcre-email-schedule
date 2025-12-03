<?php

declare(strict_types=1);

namespace Solcre\EmailSchedule\TemplateService;

use Smarty;
use Solcre\EmailSchedule\Interfaces\TemplateInterface;
use function file_exists;

class SmartyService implements TemplateInterface
{
    private Smarty $smarty;
    private array $templatePaths;

    public function __construct(Smarty $smarty, array $templatePaths)
    {
        $this->smarty = $smarty;
        $this->templatePaths = $templatePaths;
    }

    public function assign(array $data, ?string $key = null): void
    {
        $key ??= 'data';
        $this->smarty->assign($key, $data);
    }

    public function render(string $templateName, array $data = []): string
    {
        foreach ($this->templatePaths as $path) {
            $fullName = $path . $templateName;
            if (file_exists($fullName)) {
                $this->assign($data);
                return $this->smarty->fetch($fullName);
            }
        }

        return '';
    }
}
