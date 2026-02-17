<?php

declare(strict_types=1);

use Th\Core\Csrf;
use Th\Core\View;

?>
<div class="card" style="max-width: 560px; margin: 2rem auto;">
    <h1>Initial Administrator Setup</h1>
    <p class="text-muted">Create the first administrator account. This page is available only when no administrator exists.</p>

    <form method="post" action="/setup" autocomplete="off" novalidate>
        <input type="hidden" name="_token" value="<?= View::escape(Csrf::token()) ?>">

        <div style="margin-bottom: 0.75rem;">
            <label for="name">Name</label>
            <input id="name" type="text" name="name" value="<?= View::escape((string) ($old['name'] ?? '')) ?>" required>
            <?php if (($errors['name'] ?? null) !== null): ?>
                <div class="field-error"><?= View::escape((string) $errors['name']) ?></div>
            <?php endif; ?>
        </div>

        <div style="margin-bottom: 0.75rem;">
            <label for="email">Email</label>
            <input id="email" type="email" name="email" value="<?= View::escape((string) ($old['email'] ?? '')) ?>" required>
            <?php if (($errors['email'] ?? null) !== null): ?>
                <div class="field-error"><?= View::escape((string) $errors['email']) ?></div>
            <?php endif; ?>
        </div>

        <div style="margin-bottom: 0.75rem;">
            <label for="password">Password</label>
            <input id="password" type="password" name="password" required>
            <?php if (($errors['password'] ?? null) !== null): ?>
                <div class="field-error"><?= View::escape((string) $errors['password']) ?></div>
            <?php endif; ?>
        </div>

        <div style="margin-bottom: 1rem;">
            <label for="password_confirmation">Confirm Password</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required>
            <?php if (($errors['password_confirmation'] ?? null) !== null): ?>
                <div class="field-error"><?= View::escape((string) $errors['password_confirmation']) ?></div>
            <?php endif; ?>
        </div>

        <button class="btn" type="submit">Create Administrator</button>
    </form>
</div>
