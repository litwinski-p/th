<?php

declare(strict_types=1);

use Th\Core\Csrf;
use Th\Core\View;
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= View::escape((string) $title) ?></title>
    <style>
        :root {
            --bg: #f5f7fb;
            --surface: #ffffff;
            --text: #1f2937;
            --muted: #6b7280;
            --border: #d1d5db;
            --accent: #0369a1;
            --success: #166534;
            --error: #b91c1c;
            --earning: #166534;
            --expense: #b91c1c;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: var(--bg);
            color: var(--text);
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.5;
        }

        .container {
            max-width: 1080px;
            margin: 0 auto;
            padding: 1rem;
        }

        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1rem;
        }

        .nav {
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            margin-bottom: 1rem;
        }

        .nav .container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
        }

        .nav a {
            color: var(--text);
            text-decoration: none;
            margin-right: 0.75rem;
            font-weight: 600;
        }

        .nav a:hover {
            color: var(--accent);
        }

        h1,
        h2,
        h3 {
            margin-top: 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.95rem;
        }

        th,
        td {
            border-bottom: 1px solid var(--border);
            padding: 0.65rem;
            text-align: left;
            vertical-align: top;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1rem;
        }

        .badge {
            display: inline-block;
            border-radius: 999px;
            padding: 0.2rem 0.6rem;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .badge-earning {
            background: #dcfce7;
            color: var(--earning);
        }

        .badge-expense {
            background: #fee2e2;
            color: var(--expense);
        }

        .text-earning {
            color: var(--earning);
            font-weight: 600;
        }

        .text-expense {
            color: var(--expense);
            font-weight: 600;
        }

        .text-muted {
            color: var(--muted);
        }

        .flash {
            border-radius: 8px;
            padding: 0.75rem 1rem;
            margin-bottom: 1rem;
            border: 1px solid transparent;
        }

        .flash-success {
            border-color: #bbf7d0;
            background: #f0fdf4;
            color: var(--success);
        }

        .flash-error {
            border-color: #fecaca;
            background: #fef2f2;
            color: var(--error);
        }

        label {
            display: block;
            font-weight: 600;
            margin-bottom: 0.3rem;
        }

        input,
        select,
        textarea,
        button {
            width: 100%;
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 0.55rem 0.65rem;
            font: inherit;
            background: #fff;
            color: var(--text);
        }

        textarea {
            min-height: 120px;
            resize: vertical;
        }

        .actions {
            display: flex;
            gap: 0.65rem;
            align-items: center;
        }

        .btn {
            display: inline-block;
            width: auto;
            cursor: pointer;
            background: var(--accent);
            color: #fff;
            border: 1px solid var(--accent);
            text-decoration: none;
            font-weight: 600;
            padding: 0.55rem 0.9rem;
            border-radius: 8px;
        }

        .btn-secondary {
            background: #fff;
            color: var(--text);
            border-color: var(--border);
        }

        .field-error {
            color: var(--error);
            margin-top: 0.25rem;
            font-size: 0.9rem;
        }

        .inline-form {
            display: inline;
        }

        @media (max-width: 768px) {
            .nav .container {
                flex-direction: column;
                align-items: flex-start;
            }

            th,
            td {
                font-size: 0.88rem;
                padding: 0.5rem;
            }
        }
    </style>
</head>
<body>
<?php if ($currentAdmin !== null): ?>
    <nav class="nav">
        <div class="container">
            <div>
                <a href="/">Dashboard</a>
                <a href="/clients">Clients</a>
                <a href="/reports">Reports</a>
            </div>
            <div class="actions">
                <span class="text-muted">Signed in as <?= View::escape((string) $currentAdmin['email']) ?></span>
                <form class="inline-form" method="post" action="/logout">
                    <input type="hidden" name="_token" value="<?= View::escape(Csrf::token()) ?>">
                    <button class="btn btn-secondary" type="submit">Logout</button>
                </form>
            </div>
        </div>
    </nav>
<?php endif; ?>

<main class="container">
    <?php if ($flashSuccess !== null): ?>
        <div class="flash flash-success"><?= View::escape($flashSuccess) ?></div>
    <?php endif; ?>

    <?php if ($flashError !== null): ?>
        <div class="flash flash-error"><?= View::escape($flashError) ?></div>
    <?php endif; ?>

    <?= $content ?>
</main>
</body>
</html>
