<?php

declare(strict_types=1);

namespace Th\Repository\Contracts;

interface ClientRepositoryInterface
{
    public function count(): int;

    /**
     * @return array<int, array<string, mixed>>
     */
    public function allWithBalance(): array;

    public function findById(int $id): ?array;

    public function exists(int $id): bool;

    public function create(string $fullName, ?string $email, ?string $phone, ?string $note): int;

    /**
     * @return array<int, array<string, mixed>>
     */
    public function allForSelect(): array;
}
