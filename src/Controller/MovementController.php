<?php

declare(strict_types=1);

namespace Th\Controller;

use Th\Core\Auth;
use Th\Core\Flash;
use Th\Core\View;
use Th\Repository\ClientRepository;
use Th\Repository\MovementRepository;

final class MovementController extends Controller
{
    public function __construct(
        View $view,
        Auth $auth,
        private ClientRepository $clientRepository,
        private MovementRepository $movementRepository
    ) {
        parent::__construct($view, $auth);
    }

    /**
     * @param array<string, string> $params
     */
    public function store(array $params): void
    {
        $this->requireAuth();
        $this->verifyCsrfOrFail();

        $clientId = (int) ($params['id'] ?? 0);

        if (!$this->clientRepository->exists($clientId)) {
            $this->render('errors/not-found', ['title' => 'Not Found'], 404);

            return;
        }

        $movementType = trim((string) ($_POST['movement_type'] ?? ''));
        $amountInput = trim((string) ($_POST['amount'] ?? ''));
        $description = trim((string) ($_POST['description'] ?? ''));
        $movedAt = trim((string) ($_POST['moved_at'] ?? ''));

        $errors = [];

        if (!in_array($movementType, ['earning', 'expense'], true)) {
            $errors['movement_type'] = 'Choose a valid movement type.';
        }

        if (!is_numeric($amountInput) || (float) $amountInput <= 0) {
            $errors['amount'] = 'Amount must be a number greater than zero.';
        }

        if (mb_strlen($description) < 3 || mb_strlen($description) > 255) {
            $errors['description'] = 'Description must be between 3 and 255 characters.';
        }

        if (!$this->isValidDate($movedAt)) {
            $errors['moved_at'] = 'Date must be in YYYY-MM-DD format.';
        }

        if ($errors !== []) {
            $client = $this->clientRepository->findById($clientId);

            if ($client === null) {
                $this->render('errors/not-found', ['title' => 'Not Found'], 404);

                return;
            }

            $this->render('clients/show', [
                'title' => 'Client Details',
                'client' => $client,
                'movements' => $this->movementRepository->forClient($clientId),
                'totals' => $this->movementRepository->totals($clientId),
                'errors' => $errors,
                'oldMovement' => [
                    'movement_type' => $movementType,
                    'amount' => $amountInput,
                    'description' => $description,
                    'moved_at' => $movedAt,
                ],
            ], 422);

            return;
        }

        $normalizedAmount = number_format((float) $amountInput, 2, '.', '');

        $this->movementRepository->create($clientId, $movementType, $normalizedAmount, $description, $movedAt);
        Flash::set('success', 'Movement added successfully.');

        $this->redirect('/clients/' . $clientId);
    }

    private function isValidDate(string $value): bool
    {
        $date = \DateTimeImmutable::createFromFormat('Y-m-d', $value);

        return $date !== false && $date->format('Y-m-d') === $value;
    }
}
