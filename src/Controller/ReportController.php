<?php

declare(strict_types=1);

namespace Th\Controller;

use DateTimeImmutable;
use Th\Core\Auth;
use Th\Core\View;
use Th\Repository\ClientRepository;
use Th\Repository\MovementRepository;

final class ReportController extends Controller
{
    public function __construct(
        View $view,
        Auth $auth,
        private ClientRepository $clientRepository,
        private MovementRepository $movementRepository
    ) {
        parent::__construct($view, $auth);
    }

    public function index(): void
    {
        $this->requireAuth();

        $clientIdInput = trim((string) ($_GET['client_id'] ?? ''));
        $from = trim((string) ($_GET['from'] ?? ''));
        $to = trim((string) ($_GET['to'] ?? ''));

        $clientId = null;
        $errors = [];

        if ($clientIdInput !== '') {
            if (!ctype_digit($clientIdInput)) {
                $errors['client_id'] = 'Client selection is invalid.';
            } else {
                $clientId = (int) $clientIdInput;
                if (!$this->clientRepository->exists($clientId)) {
                    $errors['client_id'] = 'Selected client was not found.';
                }
            }
        }

        if ($from !== '' && !$this->isValidDate($from)) {
            $errors['from'] = 'From date is invalid.';
        }

        if ($to !== '' && !$this->isValidDate($to)) {
            $errors['to'] = 'To date is invalid.';
        }

        if ($from !== '' && $to !== '' && $from > $to) {
            $errors['range'] = 'From date cannot be greater than to date.';
        }

        $normalizedFrom = $from === '' ? null : $from;
        $normalizedTo = $to === '' ? null : $to;

        $movements = [];
        $totals = [
            'earnings' => '0.00',
            'expenses' => '0.00',
            'balance' => '0.00',
        ];

        if ($errors === []) {
            $movements = $this->movementRepository->report($clientId, $normalizedFrom, $normalizedTo);
            $totals = $this->movementRepository->totals($clientId, $normalizedFrom, $normalizedTo);
        }

        $this->render('reports/index', [
            'title' => 'Reports',
            'clients' => $this->clientRepository->allForSelect(),
            'filters' => [
                'client_id' => $clientIdInput,
                'from' => $from,
                'to' => $to,
            ],
            'errors' => $errors,
            'movements' => $movements,
            'totals' => $totals,
        ]);
    }

    private function isValidDate(string $value): bool
    {
        $date = DateTimeImmutable::createFromFormat('Y-m-d', $value);

        return $date !== false && $date->format('Y-m-d') === $value;
    }
}
