<?php

declare(strict_types=1);

namespace Th\Core;

use RuntimeException;

final class View
{
    public function __construct(
        private string $basePath,
        private Auth $auth
    ) {
    }

    public static function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    /**
     * @param array<string, mixed> $data
     */
    public function render(string $template, array $data = [], string $layout = 'layouts/main'): void
    {
        $templatePath = $this->basePath . '/' . $template . '.php';
        $layoutPath = $this->basePath . '/' . $layout . '.php';

        if (!is_file($templatePath)) {
            throw new RuntimeException(sprintf('View template not found: %s', $template));
        }

        if (!is_file($layoutPath)) {
            throw new RuntimeException(sprintf('View layout not found: %s', $layout));
        }

        $currentAdmin = $this->auth->user();
        $flashSuccess = Flash::pull('success');
        $flashError = Flash::pull('error');
        $title = $data['title'] ?? 'Application';

        extract($data, EXTR_SKIP);

        ob_start();
        require $templatePath;
        $content = (string) ob_get_clean();

        require $layoutPath;
    }
}
