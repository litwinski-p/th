<?php

declare(strict_types=1);

namespace Th\Controller;

use Th\Core\Auth;
use Th\Core\Csrf;
use Th\Core\Flash;
use Th\Core\View;

abstract class Controller
{
    public function __construct(
        protected View $view,
        protected Auth $auth
    ) {
    }

    protected function render(string $template, array $data = [], int $statusCode = 200): void
    {
        http_response_code($statusCode);
        $this->view->render($template, $data);
    }

    protected function redirect(string $path): never
    {
        header('Location: ' . $path);
        exit;
    }

    protected function requireAuth(): void
    {
        if (!$this->auth->check()) {
            Flash::set('error', 'Please sign in to continue.');
            $this->redirect('/login');
        }
    }

    protected function verifyCsrfOrFail(): void
    {
        if (!Csrf::verify($_POST['_token'] ?? null)) {
            http_response_code(419);
            echo 'CSRF token mismatch. Refresh the page and try again.';
            exit;
        }
    }
}
