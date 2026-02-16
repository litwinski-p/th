<?php

declare(strict_types=1);

use Th\Core\View;
?>
<h1>Dashboard</h1>

<div class="grid">
    <section class="card">
        <h2>Total Clients</h2>
        <p style="font-size: 2rem; margin: 0;"><?= View::escape((string) $clientCount) ?></p>
    </section>

    <section class="card">
        <h2>Total Earnings</h2>
        <p class="text-earning" style="font-size: 2rem; margin: 0;">+<?= View::escape((string) $totals['earnings']) ?></p>
    </section>

    <section class="card">
        <h2>Total Expenses</h2>
        <p class="text-expense" style="font-size: 2rem; margin: 0;">-<?= View::escape((string) $totals['expenses']) ?></p>
    </section>

    <section class="card">
        <h2>Balance</h2>
        <?php $isPositive = (float) $totals['balance'] >= 0; ?>
        <p class="<?= $isPositive ? 'text-earning' : 'text-expense' ?>" style="font-size: 2rem; margin: 0;">
            <?= View::escape((string) $totals['balance']) ?>
        </p>
    </section>
</div>

<section class="card">
    <div class="actions" style="justify-content: space-between; margin-bottom: 0.8rem;">
        <h2 style="margin: 0;">Recent Movements</h2>
        <a class="btn btn-secondary" href="/reports">View full report</a>
    </div>

    <?php if ($recentMovements === []): ?>
        <p class="text-muted">No movements yet.</p>
    <?php else: ?>
        <table>
            <thead>
            <tr>
                <th>Date</th>
                <th>Client</th>
                <th>Type</th>
                <th>Description</th>
                <th>Amount</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($recentMovements as $movement): ?>
                <tr>
                    <td><?= View::escape((string) $movement['moved_at']) ?></td>
                    <td><?= View::escape((string) $movement['client_name']) ?></td>
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
