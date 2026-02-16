<?php

declare(strict_types=1);

namespace Th\Core;

use Th\Repository\AdminRepository;

final class Auth
{
    private const MAX_LOGIN_ATTEMPTS = 5;

    private const LOCK_SECONDS = 300;

    public function __construct(private AdminRepository $adminRepository)
    {
    }

    public function check(): bool
    {
        $adminId = $_SESSION['admin_id'] ?? null;

        if (!is_int($adminId)) {
            return false;
        }

        if ($this->adminRepository->findById($adminId) === null) {
            unset($_SESSION['admin_id']);

            return false;
        }

        return true;
    }

    public function user(): ?array
    {
        if (!$this->check()) {
            return null;
        }

        return $this->adminRepository->findById($_SESSION['admin_id']);
    }

    public function attempt(string $email, string $password): bool
    {
        if ($this->lockSecondsRemaining() > 0) {
            return false;
        }

        $admin = $this->adminRepository->findByEmail($email);

        if ($admin === null || !password_verify($password, (string) $admin['password_hash'])) {
            $this->recordFailure();

            return false;
        }

        $this->clearFailures();
        session_regenerate_id(true);
        Csrf::regenerate();

        $_SESSION['admin_id'] = (int) $admin['id'];

        return true;
    }

    public function loginById(int $adminId): void
    {
        session_regenerate_id(true);
        Csrf::regenerate();
        $_SESSION['admin_id'] = $adminId;
        $this->clearFailures();
    }

    public function logout(): void
    {
        unset($_SESSION['admin_id']);
        session_regenerate_id(true);
        Csrf::regenerate();
    }

    public function hasAnyAdmin(): bool
    {
        return $this->adminRepository->count() > 0;
    }

    public function lockSecondsRemaining(): int
    {
        $lockUntil = $_SESSION['auth_lock_until'] ?? 0;

        if (!is_int($lockUntil) || $lockUntil <= time()) {
            return 0;
        }

        return $lockUntil - time();
    }

    private function recordFailure(): void
    {
        $attempts = $_SESSION['auth_attempts'] ?? 0;

        if (!is_int($attempts)) {
            $attempts = 0;
        }

        $attempts++;

        $_SESSION['auth_attempts'] = $attempts;

        if ($attempts >= self::MAX_LOGIN_ATTEMPTS) {
            $_SESSION['auth_lock_until'] = time() + self::LOCK_SECONDS;
            $_SESSION['auth_attempts'] = 0;
        }
    }

    private function clearFailures(): void
    {
        unset($_SESSION['auth_attempts'], $_SESSION['auth_lock_until']);
    }
}
