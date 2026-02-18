<?php

declare(strict_types=1);

namespace Th\Core;

use Th\Repository\Contracts\AdminRepositoryInterface;
use Th\Repository\Contracts\LoginAttemptRepositoryInterface;

final class Auth
{
    private const int MAX_LOGIN_ATTEMPTS = 5;

    private const int LOCK_SECONDS = 300;

    public function __construct(
        private AdminRepositoryInterface $adminRepository,
        private LoginAttemptRepositoryInterface $loginAttemptRepository
    ) {
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

    public function attempt(string $email, string $password, string $throttleKey): bool
    {
        if ($this->lockSecondsRemaining($throttleKey) > 0) {
            return false;
        }

        $admin = $this->adminRepository->findByEmail($email);

        if ($admin === null || !password_verify($password, (string) $admin['password_hash'])) {
            $this->loginAttemptRepository->recordFailure($throttleKey, self::MAX_LOGIN_ATTEMPTS, self::LOCK_SECONDS);

            return false;
        }

        $this->loginAttemptRepository->clear($throttleKey);
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

    public function lockSecondsRemaining(string $throttleKey): int
    {
        return $this->loginAttemptRepository->lockSecondsRemaining($throttleKey);
    }
}
