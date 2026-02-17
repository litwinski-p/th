<?php

declare(strict_types=1);

namespace Th\Controller;

use Th\Core\Auth;
use Th\Core\Flash;
use Th\Core\View;
use Th\Form\AdminLoginForm;
use Th\Form\AdminSetupForm;
use Th\Repository\Contracts\AdminRepositoryInterface;

final class AuthController extends Controller
{
    public function __construct(
        View $view,
        Auth $auth,
        private AdminRepositoryInterface $adminRepository
    ) {
        parent::__construct($view, $auth);
    }

    public function showLogin(): void
    {
        if (!$this->auth->hasAnyAdmin()) {
            $this->redirect('/setup');
        }

        if ($this->auth->check()) {
            $this->redirect('/');
        }

        $this->render('auth/login', [
            'title' => 'Admin Login',
            'lockSeconds' => $this->auth->lockSecondsRemaining($this->throttleKey()),
            'old' => ['email' => ''],
            'errors' => [],
        ]);
    }

    public function login(): void
    {
        if (!$this->auth->hasAnyAdmin()) {
            $this->redirect('/setup');
        }

        $this->verifyCsrfOrFail();

        $form = AdminLoginForm::fromArray($_POST);

        if (!$form->isValid()) {
            $this->render('auth/login', [
                'title' => 'Admin Login',
                'lockSeconds' => $this->auth->lockSecondsRemaining($this->throttleKey()),
                'old' => $form->old(),
                'errors' => $form->errors(),
            ], 422);

            return;
        }

        if (!$this->auth->attempt($form->email(), $form->password(), $this->throttleKey())) {
            $lockSeconds = $this->auth->lockSecondsRemaining($this->throttleKey());
            $errors = $form->errors();
            $errors['credentials'] = $lockSeconds > 0
                ? sprintf('Too many failed attempts. Try again in %d seconds.', $lockSeconds)
                : 'Invalid credentials.';

            $this->render('auth/login', [
                'title' => 'Admin Login',
                'lockSeconds' => $lockSeconds,
                'old' => $form->old(),
                'errors' => $errors,
            ], 401);

            return;
        }

        Flash::set('success', 'Signed in successfully.');

        $this->redirect('/');
    }

    public function showSetup(): void
    {
        if ($this->auth->hasAnyAdmin()) {
            $this->redirect('/login');
        }

        $setupToken = $this->resolveSetupTokenFromGet();
        $tokenError = $this->validateSetupToken($setupToken);

        $this->render('auth/setup', [
            'title' => 'Initial Admin Setup',
            'setupToken' => $setupToken ?? '',
            'canSetup' => $tokenError === null,
            'old' => [
                'name' => '',
                'email' => '',
            ],
            'errors' => $tokenError === null ? [] : ['setup_token' => $tokenError],
        ], $tokenError === null ? 200 : 403);
    }

    public function setup(): void
    {
        if ($this->auth->hasAnyAdmin()) {
            $this->redirect('/login');
        }

        $this->verifyCsrfOrFail();

        $submittedSetupToken = $_POST['setup_token'] ?? null;
        $tokenError = $this->validateSetupToken(is_string($submittedSetupToken) ? $submittedSetupToken : null);

        if ($tokenError !== null) {
            $this->render('auth/setup', [
                'title' => 'Initial Admin Setup',
                'setupToken' => '',
                'canSetup' => false,
                'old' => [
                    'name' => '',
                    'email' => '',
                ],
                'errors' => ['setup_token' => $tokenError],
            ], 403);

            return;
        }

        $form = AdminSetupForm::fromArray($_POST);

        if (!$form->isValid()) {
            $this->render('auth/setup', [
                'title' => 'Initial Admin Setup',
                'setupToken' => $submittedSetupToken,
                'canSetup' => true,
                'old' => $form->old(),
                'errors' => $form->errors(),
            ], 422);

            return;
        }

        $adminId = $this->adminRepository->create(
            $form->name(),
            $form->email(),
            password_hash($form->password(), PASSWORD_DEFAULT)
        );
        $this->auth->loginById($adminId);

        Flash::set('success', 'Administrator account created.');

        $this->redirect('/');
    }

    public function logout(): void
    {
        $this->requireAuth();
        $this->verifyCsrfOrFail();

        $this->auth->logout();
        Flash::set('success', 'Signed out successfully.');

        $this->redirect('/login');
    }

    private function throttleKey(): string
    {
        $remoteAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

        if (!is_string($remoteAddress) || $remoteAddress === '') {
            $remoteAddress = 'unknown';
        }

        return 'ip:' . hash('sha256', $remoteAddress);
    }

    private function resolveSetupTokenFromGet(): ?string
    {
        $token = $_GET['token'] ?? null;

        if (!is_string($token)) {
            return null;
        }

        $token = trim($token);

        return $token === '' ? null : $token;
    }

    private function validateSetupToken(?string $providedToken): ?string
    {
        $expectedToken = trim((string) (getenv('APP_SETUP_TOKEN') ?: ''));

        if ($expectedToken === '') {
            return 'Setup is disabled. Set APP_SETUP_TOKEN in .env to enable initial bootstrap.';
        }

        if ($providedToken === null || $providedToken === '') {
            return 'Setup token is required. Open /setup?token=YOUR_APP_SETUP_TOKEN';
        }

        if (!hash_equals($expectedToken, $providedToken)) {
            return 'Setup token is invalid.';
        }

        return null;
    }
}
