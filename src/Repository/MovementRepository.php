<?php

declare(strict_types=1);

namespace Th\Repository;

use PDO;
use Th\Repository\Contracts\MovementRepositoryInterface;

final class MovementRepository implements MovementRepositoryInterface
{
    public function __construct(private PDO $pdo)
    {
    }

    public function create(int $clientId, string $type, string $amount, string $description, string $movedAt): void
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO movements (client_id, movement_type, amount, description, moved_at, created_at) VALUES (:client_id, :movement_type, :amount, :description, :moved_at, NOW())'
        );

        $statement->execute([
            'client_id' => $clientId,
            'movement_type' => $type,
            'amount' => $amount,
            'description' => $description,
            'moved_at' => $movedAt,
        ]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function forClient(int $clientId): array
    {
        $statement = $this->pdo->prepare(
            "SELECT id, movement_type, amount, description, moved_at, created_at
             FROM movements
             WHERE client_id = :client_id
             ORDER BY moved_at DESC, id DESC"
        );
        $statement->execute(['client_id' => $clientId]);

        return $statement->fetchAll();
    }

    /**
     * @return array{earnings: string, expenses: string, balance: string}
     */
    public function totals(?int $clientId = null, ?string $from = null, ?string $to = null): array
    {
        $params = [];
        $where = $this->buildWhere($params, $clientId, $from, $to);

        $statement = $this->pdo->prepare(
            "SELECT
                COALESCE(SUM(CASE WHEN movement_type = 'earning' THEN amount ELSE 0 END), 0) AS earnings,
                COALESCE(SUM(CASE WHEN movement_type = 'expense' THEN amount ELSE 0 END), 0) AS expenses,
                COALESCE(SUM(CASE WHEN movement_type = 'earning' THEN amount ELSE -amount END), 0) AS balance
             FROM movements m
             {$where}"
        );

        $statement->execute($params);

        $totals = $statement->fetch();

        return [
            'earnings' => (string) ($totals['earnings'] ?? '0.00'),
            'expenses' => (string) ($totals['expenses'] ?? '0.00'),
            'balance' => (string) ($totals['balance'] ?? '0.00'),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function report(?int $clientId = null, ?string $from = null, ?string $to = null): array
    {
        $params = [];
        $where = $this->buildWhere($params, $clientId, $from, $to);

        $statement = $this->pdo->prepare(
            "SELECT
                m.id,
                m.client_id,
                c.full_name AS client_name,
                m.movement_type,
                m.amount,
                m.description,
                m.moved_at,
                m.created_at
             FROM movements m
             INNER JOIN clients c ON c.id = m.client_id
             {$where}
             ORDER BY m.moved_at DESC, m.id DESC"
        );

        $statement->execute($params);

        return $statement->fetchAll();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function recent(int $limit = 10): array
    {
        $statement = $this->pdo->prepare(
            "SELECT
                m.id,
                c.full_name AS client_name,
                m.movement_type,
                m.amount,
                m.description,
                m.moved_at
             FROM movements m
             INNER JOIN clients c ON c.id = m.client_id
             ORDER BY m.moved_at DESC, m.id DESC
             LIMIT :limit"
        );

        $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }

    /**
     * @param array<string, int|string> $params
     */
    private function buildWhere(array &$params, ?int $clientId, ?string $from, ?string $to): string
    {
        $conditions = [];

        if ($clientId !== null) {
            $conditions[] = 'm.client_id = :client_id';
            $params['client_id'] = $clientId;
        }

        if ($from !== null) {
            $conditions[] = 'm.moved_at >= :from_date';
            $params['from_date'] = $from;
        }

        if ($to !== null) {
            $conditions[] = 'm.moved_at <= :to_date';
            $params['to_date'] = $to;
        }

        if ($conditions === []) {
            return '';
        }

        return 'WHERE ' . implode(' AND ', $conditions);
    }
}
