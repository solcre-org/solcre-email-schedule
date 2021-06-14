<?php

namespace Solcre\EmailSchedule\TemplateService;

use Exception;
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

    public function assign($data, $key = null): void
    {
        if ($key === null) {
            $key = 'data';
        }

        $this->smarty->assign($key, $data);
    }

    /**
     * @throws \SmartyException
     */
    public function render(string $templateName, array $data = []): string
    {
        foreach ($this->templatePaths as $path) {
            $fullName = $path . $templateName;
            if (file_exists($fullName)) {
                try {
                    $this->assign($data);
                    return $this->smarty->fetch($fullName);
                } catch (Exception $e) {
                    throw $e;
                }
            }
        }

        return '';
    }
}
