<?php

declare(strict_types=1);

use Th\Core\View;

?>
<h1>Reports</h1>

<section class="card">
    <h2>Filters</h2>
    <form method="get" action="/reports">
        <div class="grid">
            <div>
                <label for="client_id">Client</label>
                <select id="client_id" name="client_id">
                    <option value="">All clients</option>
                    <?php foreach ($clients as $client): ?>
                        <option value="<?= View::escape((string) $client['id']) ?>" <?= (string) $filters['client_id'] === (string) $client['id'] ? 'selected' : '' ?>>
                            <?= View::escape((string) $client['full_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (($errors['client_id'] ?? null) !== null): ?>
                    <div class="field-error"><?= View::escape((string) $errors['client_id']) ?></div>
                <?php endif; ?>
            </div>

            <div>
                <label for="from">From date</label>
                <input id="from" type="date" name="from" value="<?= View::escape((string) $filters['from']) ?>">
                <?php if (($errors['from'] ?? null) !== null): ?>
                    <div class="field-error"><?= View::escape((string) $errors['from']) ?></div>
                <?php endif; ?>
            </div>

            <div>
                <label for="to">To date</label>
                <input id="to" type="date" name="to" value="<?= View::escape((string) $filters['to']) ?>">
                <?php if (($errors['to'] ?? null) !== null): ?>
                    <div class="field-error"><?= View::escape((string) $errors['to']) ?></div>
                <?php endif; ?>
            </div>
        </div>

        <?php if (($errors['range'] ?? null) !== null): ?>
            <div class="field-error" style="margin-top: 0.75rem;"><?= View::escape((string) $errors['range']) ?></div>
        <?php endif; ?>

        <div class="actions" style="margin-top: 1rem;">
            <button class="btn" type="submit">Apply Filters</button>
            <a class="btn btn-secondary" href="/reports">Reset</a>
        </div>
    </form>
</section>

<div class="grid">
    <section class="card">
        <h2>Earnings</h2>
        <p class="text-earning" style="font-size: 1.6rem; margin: 0;">+<?= View::escape((string) $totals['earnings']) ?></p>
    </section>

    <section class="card">
        <h2>Expenses</h2>
        <p class="text-expense" style="font-size: 1.6rem; margin: 0;">-<?= View::escape((string) $totals['expenses']) ?></p>
    </section>

    <section class="card">
        <h2>Balance</h2>
        <?php $isPositive = (float) $totals['balance'] >= 0; ?>
        <p class="<?= $isPositive ? 'text-earning' : 'text-expense' ?>" style="font-size: 1.6rem; margin: 0;">
            <?= View::escape((string) $totals['balance']) ?>
        </p>
    </section>
</div>

<section class="card">
    <h2>Movements</h2>

    <?php if ($movements === []): ?>
        <p class="text-muted">No movements for selected filters.</p>
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
            <?php foreach ($movements as $movement): ?>
                <tr>
                    <td><?= View::escape((string) $movement['moved_at']) ?></td>
                    <td><a href="/clients/<?= View::escape((string) $movement['client_id']) ?>"><?= View::escape((string) $movement['client_name']) ?></a></td>
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
