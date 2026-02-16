<?php

declare(strict_types=1);

namespace Th\Controller;

use Th\Core\Auth;
use Th\Core\Csrf;
use Th\Core\Flash;
use Th\Core\View;
use Th\Repository\AdminRepository;

final class AuthController extends Controller
{
    public function __construct(
        View $view,
        Auth $auth,
        private AdminRepository $adminRepository
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
            'lockSeconds' => $this->auth->lockSecondsRemaining(),
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

        $email = mb_strtolower(trim((string) ($_POST['email'] ?? '')));
        $password = (string) ($_POST['password'] ?? '');

        $errors = [];

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Enter a valid email address.';
        }

        if ($password === '') {
            $errors['password'] = 'Password is required.';
        }

        if ($errors !== []) {
            $this->render('auth/login', [
                'title' => 'Admin Login',
                'lockSeconds' => $this->auth->lockSecondsRemaining(),
                'old' => ['email' => $email],
                'errors' => $errors,
            ], 422);

            return;
        }

        if (!$this->auth->attempt($email, $password)) {
            $lockSeconds = $this->auth->lockSecondsRemaining();
            $errors['credentials'] = $lockSeconds > 0
                ? sprintf('Too many failed attempts. Try again in %d seconds.', $lockSeconds)
                : 'Invalid credentials.';

            $this->render('auth/login', [
                'title' => 'Admin Login',
                'lockSeconds' => $lockSeconds,
                'old' => ['email' => $email],
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

        $this->render('auth/setup', [
            'title' => 'Initial Admin Setup',
            'old' => [
                'name' => '',
                'email' => '',
            ],
            'errors' => [],
        ]);
    }

    public function setup(): void
    {
        if ($this->auth->hasAnyAdmin()) {
            $this->redirect('/login');
        }

        $this->verifyCsrfOrFail();

        $name = trim((string) ($_POST['name'] ?? ''));
        $email = mb_strtolower(trim((string) ($_POST['email'] ?? '')));
        $password = (string) ($_POST['password'] ?? '');
        $passwordConfirmation = (string) ($_POST['password_confirmation'] ?? '');

        $errors = [];

        if (mb_strlen($name) < 2 || mb_strlen($name) > 120) {
            $errors['name'] = 'Name must be between 2 and 120 characters.';
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Enter a valid email address.';
        }

        if (!$this->isStrongPassword($password)) {
            $errors['password'] = 'Password must have 12+ chars, upper/lowercase, number and symbol.';
        }

        if ($password !== $passwordConfirmation) {
            $errors['password_confirmation'] = 'Password confirmation does not match.';
        }

        if ($errors !== []) {
            $this->render('auth/setup', [
                'title' => 'Initial Admin Setup',
                'old' => [
                    'name' => $name,
                    'email' => $email,
                ],
                'errors' => $errors,
            ], 422);

            return;
        }

        $adminId = $this->adminRepository->create($name, $email, password_hash($password, PASSWORD_DEFAULT));
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

    private function isStrongPassword(string $password): bool
    {
        if (mb_strlen($password) < 12) {
            return false;
        }

        return preg_match('/[A-Z]/', $password) === 1
            && preg_match('/[a-z]/', $password) === 1
            && preg_match('/\d/', $password) === 1
            && preg_match('/[^a-zA-Z\d]/', $password) === 1;
    }
}
