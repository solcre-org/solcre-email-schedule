<?php

namespace Solcre\EmailSchedule\TemplateService;

use Smarty;
use SmartyException;
use Solcre\EmailSchedule\Interfaces\TemplateInterface;

class SmartyService implements TemplateInterface
{
    private Smarty $smarty;
    private array $templatePaths;

    public function __construct(Smarty $smarty, array $templatePaths)
    {
        $this->smarty = $smarty;
        $this->templatePaths = $templatePaths;
    }

    public function render(string $templateName, array $data = []): string
    {
        foreach ($this->templatePaths as $path) {
            $fullName = $path . $templateName;
            if (\file_exists($fullName)) {
                try {
                    return $this->smarty->fetch($fullName, $data);
                } catch (SmartyException $e) {
                    throw $e;
                }
            }
        }

        return '';
    }
}
