<?php

declare(strict_types=1);

use Th\Core\View;
?>
<div class="actions" style="justify-content: space-between; margin-bottom: 1rem;">
    <h1 style="margin: 0;">Clients</h1>
    <a class="btn" href="/clients/create">Add Client</a>
</div>

<section class="card">
    <?php if ($clients === []): ?>
        <p class="text-muted">No clients yet.</p>
    <?php else: ?>
        <table>
            <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Balance</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($clients as $client): ?>
                <tr>
                    <td><?= View::escape((string) $client['full_name']) ?></td>
                    <td><?= View::escape((string) ($client['email'] ?? '-')) ?></td>
                    <td><?= View::escape((string) ($client['phone'] ?? '-')) ?></td>
                    <?php $isPositive = (float) $client['balance'] >= 0; ?>
                    <td class="<?= $isPositive ? 'text-earning' : 'text-expense' ?>">
                        <?= View::escape((string) $client['balance']) ?>
                    </td>
                    <td><a class="btn btn-secondary" href="/clients/<?= View::escape((string) $client['id']) ?>">Open</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>
