<?php

declare(strict_types=1);

namespace Th\Repository;

use DateTimeImmutable;
use PDO;
use Th\Repository\Contracts\LoginAttemptRepositoryInterface;

final class LoginAttemptRepository implements LoginAttemptRepositoryInterface
{
    public function __construct(private PDO $pdo)
    {
    }

    public function lockSecondsRemaining(string $key): int
    {
        $statement = $this->pdo->prepare(
            'SELECT lock_until FROM login_attempts WHERE login_key = :login_key LIMIT 1'
        );
        $statement->execute(['login_key' => $key]);

        $row = $statement->fetch();

        if (!is_array($row) || !is_string($row['lock_until'] ?? null)) {
            return 0;
        }

        $lockUntil = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $row['lock_until']);

        if ($lockUntil === false) {
            return 0;
        }

        $remaining = $lockUntil->getTimestamp() - time();

        if ($remaining <= 0) {
            $this->clear($key);

            return 0;
        }

        return $remaining;
    }

    public function recordFailure(string $key, int $maxAttempts, int $lockSeconds): void
    {
        $statement = $this->pdo->prepare(
            'SELECT attempts, lock_until FROM login_attempts WHERE login_key = :login_key LIMIT 1'
        );
        $statement->execute(['login_key' => $key]);
        $row = $statement->fetch();

        $attempts = 0;

        if (is_array($row) && is_numeric($row['attempts'] ?? null)) {
            $attempts = (int) $row['attempts'];

            if (is_string($row['lock_until'] ?? null)) {
                $lockUntil = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $row['lock_until']);

                if ($lockUntil !== false && $lockUntil->getTimestamp() > time()) {
                    return;
                }
            }
        }

        $attempts++;
        $lockUntil = null;

        if ($attempts >= $maxAttempts) {
            $attempts = 0;
            $lockUntil = (new DateTimeImmutable())->modify(sprintf('+%d seconds', $lockSeconds))->format('Y-m-d H:i:s');
        }

        $upsert = $this->pdo->prepare(
            'INSERT INTO login_attempts (login_key, attempts, lock_until, updated_at)
             VALUES (:login_key, :attempts, :lock_until, NOW())
             ON DUPLICATE KEY UPDATE attempts = VALUES(attempts), lock_until = VALUES(lock_until), updated_at = NOW()'
        );

        $upsert->bindValue(':login_key', $key);
        $upsert->bindValue(':attempts', $attempts, PDO::PARAM_INT);
        $upsert->bindValue(':lock_until', $lockUntil, $lockUntil === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $upsert->execute();
    }

    public function clear(string $key): void
    {
        $statement = $this->pdo->prepare('DELETE FROM login_attempts WHERE login_key = :login_key');
        $statement->execute(['login_key' => $key]);
    }
}
