<?php

declare(strict_types=1);

namespace Th\Repository;

use PDO;

final class AdminRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function count(): int
    {
        $result = $this->pdo->query('SELECT COUNT(*) FROM administrators');

        return (int) $result->fetchColumn();
    }

    public function findByEmail(string $email): ?array
    {
        $statement = $this->pdo->prepare('SELECT id, name, email, password_hash FROM administrators WHERE email = :email LIMIT 1');
        $statement->execute(['email' => mb_strtolower($email)]);

        $admin = $statement->fetch();

        return is_array($admin) ? $admin : null;
    }

    public function findById(int $id): ?array
    {
        $statement = $this->pdo->prepare('SELECT id, name, email FROM administrators WHERE id = :id LIMIT 1');
        $statement->execute(['id' => $id]);

        $admin = $statement->fetch();

        return is_array($admin) ? $admin : null;
    }

    public function create(string $name, string $email, string $passwordHash): int
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO administrators (name, email, password_hash, created_at, updated_at) VALUES (:name, :email, :password_hash, NOW(), NOW())'
        );

        $statement->execute([
            'name' => $name,
            'email' => mb_strtolower($email),
            'password_hash' => $passwordHash,
        ]);

        return (int) $this->pdo->lastInsertId();
    }
}
