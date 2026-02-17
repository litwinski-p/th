<?php

declare(strict_types=1);

namespace Th\Repository;

use PDO;
use Th\Repository\Contracts\ClientRepositoryInterface;

final class ClientRepository implements ClientRepositoryInterface
{
    public function __construct(private PDO $pdo)
    {
    }

    public function count(): int
    {
        $result = $this->pdo->query('SELECT COUNT(*) FROM clients');

        return (int) $result->fetchColumn();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function allWithBalance(): array
    {
        $statement = $this->pdo->query(
            "SELECT
                c.id,
                c.full_name,
                c.email,
                c.phone,
                c.created_at,
                COALESCE(SUM(CASE WHEN m.movement_type = 'earning' THEN m.amount ELSE -m.amount END), 0) AS balance
             FROM clients c
             LEFT JOIN movements m ON m.client_id = c.id
             GROUP BY c.id
             ORDER BY c.full_name ASC"
        );

        return $statement->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT id, full_name, email, phone, note, created_at, updated_at FROM clients WHERE id = :id LIMIT 1'
        );
        $statement->execute(['id' => $id]);

        $client = $statement->fetch();

        return is_array($client) ? $client : null;
    }

    public function exists(int $id): bool
    {
        $statement = $this->pdo->prepare('SELECT 1 FROM clients WHERE id = :id LIMIT 1');
        $statement->execute(['id' => $id]);

        return (bool) $statement->fetchColumn();
    }

    public function create(string $fullName, ?string $email, ?string $phone, ?string $note): int
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO clients (full_name, email, phone, note, created_at, updated_at) VALUES (:full_name, :email, :phone, :note, NOW(), NOW())'
        );

        $statement->execute([
            'full_name' => $fullName,
            'email' => $email,
            'phone' => $phone,
            'note' => $note,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function allForSelect(): array
    {
        $statement = $this->pdo->query('SELECT id, full_name FROM clients ORDER BY full_name ASC');

        return $statement->fetchAll();
    }
}
