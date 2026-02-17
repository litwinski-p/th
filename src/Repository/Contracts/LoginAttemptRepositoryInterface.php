<?php

declare(strict_types=1);

namespace Th\Repository\Contracts;

interface LoginAttemptRepositoryInterface
{
    public function lockSecondsRemaining(string $key): int;

    public function recordFailure(string $key, int $maxAttempts, int $lockSeconds): void;

    public function clear(string $key): void;
}
