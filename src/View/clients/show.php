<?php

declare(strict_types=1);

use Th\Core\Csrf;
use Th\Core\View;

?>
<div class="actions" style="justify-content: space-between; margin-bottom: 1rem;">
    <h1 style="margin: 0;"><?= View::escape((string) $client['full_name']) ?></h1>
    <a class="btn btn-secondary" href="/clients">Back to clients</a>
</div>

<div class="grid">
    <section class="card">
        <h2>Client Info</h2>
        <p><strong>Email:</strong> <?= View::escape((string) ($client['email'] ?? '-')) ?></p>
        <p><strong>Phone:</strong> <?= View::escape((string) ($client['phone'] ?? '-')) ?></p>
        <p><strong>Note:</strong> <?= View::escape((string) ($client['note'] ?? '-')) ?></p>
        <p class="text-muted"><small>Created at: <?= View::escape((string) $client['created_at']) ?></small></p>
    </section>

    <section class="card">
        <h2>Balance</h2>
        <p class="text-earning"><strong>Earnings:</strong> +<?= View::escape((string) $totals['earnings']) ?></p>
        <p class="text-expense"><strong>Expenses:</strong> -<?= View::escape((string) $totals['expenses']) ?></p>
        <?php $isPositive = (float) $totals['balance'] >= 0; ?>
        <p class="<?= $isPositive ? 'text-earning' : 'text-expense' ?>"><strong>Balance:</strong> <?= View::escape((string) $totals['balance']) ?></p>
    </section>
</div>

<section class="card">
    <h2>Add Movement</h2>
    <form method="post" action="/clients/<?= View::escape((string) $client['id']) ?>/movements" novalidate>
        <input type="hidden" name="_token" value="<?= View::escape(Csrf::token()) ?>">

        <div class="grid">
            <div>
                <label for="movement_type">Type</label>
                <select id="movement_type" name="movement_type" required>
                    <option value="earning" <?= ($oldMovement['movement_type'] ?? '') === 'earning' ? 'selected' : '' ?>>Earning</option>
                    <option value="expense" <?= ($oldMovement['movement_type'] ?? '') === 'expense' ? 'selected' : '' ?>>Expense</option>
                </select>
                <?php if (($errors['movement_type'] ?? null) !== null): ?>
                    <div class="field-error"><?= View::escape((string) $errors['movement_type']) ?></div>
                <?php endif; ?>
            </div>

            <div>
                <label for="amount">Amount</label>
                <input id="amount" type="number" name="amount" min="0.01" step="0.01" value="<?= View::escape((string) ($oldMovement['amount'] ?? '')) ?>" required>
                <?php if (($errors['amount'] ?? null) !== null): ?>
                    <div class="field-error"><?= View::escape((string) $errors['amount']) ?></div>
                <?php endif; ?>
            </div>

            <div>
                <label for="moved_at">Date</label>
                <input id="moved_at" type="date" name="moved_at" value="<?= View::escape((string) ($oldMovement['moved_at'] ?? date('Y-m-d'))) ?>" required>
                <?php if (($errors['moved_at'] ?? null) !== null): ?>
                    <div class="field-error"><?= View::escape((string) $errors['moved_at']) ?></div>
                <?php endif; ?>
            </div>
        </div>

        <div style="margin: 0.75rem 0;">
            <label for="description">Description</label>
            <input id="description" type="text" name="description" value="<?= View::escape((string) ($oldMovement['description'] ?? '')) ?>" required>
            <?php if (($errors['description'] ?? null) !== null): ?>
                <div class="field-error"><?= View::escape((string) $errors['description']) ?></div>
            <?php endif; ?>
        </div>

        <button class="btn" type="submit">Save Movement</button>
    </form>
</section>

<section class="card">
    <h2>Movement History</h2>
    <?php if ($movements === []): ?>
        <p class="text-muted">No movements yet for this client.</p>
    <?php else: ?>
        <table>
            <thead>
            <tr>
                <th>Date</th>
                <th>Type</th>
                <th>Description</th>
                <th>Amount</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($movements as $movement): ?>
                <tr>
                    <td><?= View::escape((string) $movement['moved_at']) ?></td>
                    <td>
                        <span class="badge <?= $movement['movement_type'] === 'earning' ? 'badge-earning' : 'badge-expense' ?>">
                            <?= View::escape((string) $movement['movement_type']) ?>
                        </span>
                    </td>
                    <td><?= View::escape((string) $movement['description']) ?></td>
                    <td class="<?= $movement['movement_type'] === 'earning' ? 'text-earning' : 'text-expense' ?>">
                        <?= $movement['movement_type'] === 'earning' ? '+' : '-' ?><?= View::escape((string) $movement['amount']) ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>
