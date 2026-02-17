<?php

declare(strict_types=1);

use Th\Core\Csrf;
use Th\Core\View;

?>
<div class="actions" style="justify-content: space-between; margin-bottom: 1rem;">
    <h1 style="margin: 0;">Create Client</h1>
    <a class="btn btn-secondary" href="/clients">Back</a>
</div>

<section class="card" style="max-width: 700px;">
    <form method="post" action="/clients" novalidate>
        <input type="hidden" name="_token" value="<?= View::escape(Csrf::token()) ?>">

        <div style="margin-bottom: 0.75rem;">
            <label for="full_name">Full name</label>
            <input id="full_name" type="text" name="full_name" value="<?= View::escape((string) $old['full_name']) ?>" required>
            <?php if (($errors['full_name'] ?? null) !== null): ?>
                <div class="field-error"><?= View::escape((string) $errors['full_name']) ?></div>
            <?php endif; ?>
        </div>

        <div style="margin-bottom: 0.75rem;">
            <label for="email">Email (optional)</label>
            <input id="email" type="email" name="email" value="<?= View::escape((string) $old['email']) ?>">
            <?php if (($errors['email'] ?? null) !== null): ?>
                <div class="field-error"><?= View::escape((string) $errors['email']) ?></div>
            <?php endif; ?>
        </div>

        <div style="margin-bottom: 0.75rem;">
            <label for="phone">Phone (optional)</label>
            <input id="phone" type="text" name="phone" value="<?= View::escape((string) $old['phone']) ?>">
            <?php if (($errors['phone'] ?? null) !== null): ?>
                <div class="field-error"><?= View::escape((string) $errors['phone']) ?></div>
            <?php endif; ?>
        </div>

        <div style="margin-bottom: 1rem;">
            <label for="note">Note (optional)</label>
            <textarea id="note" name="note"><?= View::escape((string) $old['note']) ?></textarea>
            <?php if (($errors['note'] ?? null) !== null): ?>
                <div class="field-error"><?= View::escape((string) $errors['note']) ?></div>
            <?php endif; ?>
        </div>

        <button class="btn" type="submit">Create Client</button>
    </form>
</section>
