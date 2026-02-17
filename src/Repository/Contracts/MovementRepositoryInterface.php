<?php

declare(strict_types=1);

namespace Th\Repository\Contracts;

interface MovementRepositoryInterface
{
    public function create(int $clientId, string $type, string $amount, string $description, string $movedAt): void;

    /**
     * @return array<int, array<string, mixed>>
     */
    public function forClient(int $clientId): array;

    /**
     * @return array{earnings: string, expenses: string, balance: string}
     */
    public function totals(?int $clientId = null, ?string $from = null, ?string $to = null): array;

    /**
     * @return array<int, array<string, mixed>>
     */
    public function report(?int $clientId = null, ?string $from = null, ?string $to = null): array;

    /**
     * @return array<int, array<string, mixed>>
     */
    public function recent(int $limit = 10): array;
}
