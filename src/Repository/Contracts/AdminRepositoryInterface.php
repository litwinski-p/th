<?php

declare(strict_types=1);

namespace Th\Repository\Contracts;

interface AdminRepositoryInterface
{
    public function count(): int;

    public function findByEmail(string $email): ?array;

    public function findById(int $id): ?array;

    public function create(string $name, string $email, string $passwordHash): int;
}
