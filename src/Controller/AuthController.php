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

        $form = AdminLoginForm::fromArray($_POST);

        if (!$form->isValid()) {
            $this->render('auth/login', [
                'title' => 'Admin Login',
                'lockSeconds' => $this->auth->lockSecondsRemaining(),
                'old' => $form->old(),
                'errors' => $form->errors(),
            ], 422);

            return;
        }

        if (!$this->auth->attempt($form->email(), $form->password())) {
            $lockSeconds = $this->auth->lockSecondsRemaining();
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

        $form = AdminSetupForm::fromArray($_POST);

        if (!$form->isValid()) {
            $this->render('auth/setup', [
                'title' => 'Initial Admin Setup',
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
}
