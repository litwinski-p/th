<?php

declare(strict_types=1);

use Th\Core\Csrf;
use Th\Core\View;
?>
<div class="card" style="max-width: 500px; margin: 2rem auto;">
    <h1>Administrator Login</h1>
    <p class="text-muted">Only administrators can access this application.</p>

    <?php if (($errors['credentials'] ?? null) !== null): ?>
        <div class="flash flash-error"><?= View::escape((string) $errors['credentials']) ?></div>
    <?php endif; ?>

    <form method="post" action="/login" autocomplete="off" novalidate>
        <input type="hidden" name="_token" value="<?= View::escape(Csrf::token()) ?>">

        <div style="margin-bottom: 0.75rem;">
            <label for="email">Email</label>
            <input id="email" type="email" name="email" value="<?= View::escape((string) ($old['email'] ?? '')) ?>" required>
            <?php if (($errors['email'] ?? null) !== null): ?>
                <div class="field-error"><?= View::escape((string) $errors['email']) ?></div>
            <?php endif; ?>
        </div>

        <div style="margin-bottom: 1rem;">
            <label for="password">Password</label>
            <input id="password" type="password" name="password" required>
            <?php if (($errors['password'] ?? null) !== null): ?>
                <div class="field-error"><?= View::escape((string) $errors['password']) ?></div>
            <?php endif; ?>
        </div>

        <?php if (($lockSeconds ?? 0) > 0): ?>
            <p class="text-muted">Login is temporarily locked for <?= View::escape((string) $lockSeconds) ?> seconds.</p>
        <?php endif; ?>

        <button class="btn" type="submit" <?= ($lockSeconds ?? 0) > 0 ? 'disabled' : '' ?>>Sign in</button>
    </form>
</div>
