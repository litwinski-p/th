<?php

declare(strict_types=1);

namespace Th\Controller;

use Th\Core\Auth;
use Th\Core\Flash;
use Th\Core\View;
use Th\Repository\ClientRepository;
use Th\Repository\MovementRepository;

final class ClientController extends Controller
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

        $this->render('clients/index', [
            'title' => 'Clients',
            'clients' => $this->clientRepository->allWithBalance(),
        ]);
    }

    public function create(): void
    {
        $this->requireAuth();

        $this->render('clients/create', [
            'title' => 'Create Client',
            'old' => [
                'full_name' => '',
                'email' => '',
                'phone' => '',
                'note' => '',
            ],
            'errors' => [],
        ]);
    }

    public function store(): void
    {
        $this->requireAuth();
        $this->verifyCsrfOrFail();

        $fullName = trim((string) ($_POST['full_name'] ?? ''));
        $email = $this->normalizeText($_POST['email'] ?? null);
        $phone = $this->normalizeText($_POST['phone'] ?? null);
        $note = $this->normalizeText($_POST['note'] ?? null);

        $errors = [];

        if (mb_strlen($fullName) < 2 || mb_strlen($fullName) > 150) {
            $errors['full_name'] = 'Full name must be between 2 and 150 characters.';
        }

        if ($email !== null && filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $errors['email'] = 'Email is invalid.';
        }

        if ($phone !== null && mb_strlen($phone) > 50) {
            $errors['phone'] = 'Phone cannot be longer than 50 characters.';
        }

        if ($note !== null && mb_strlen($note) > 2000) {
            $errors['note'] = 'Note cannot be longer than 2000 characters.';
        }

        if ($errors !== []) {
            $this->render('clients/create', [
                'title' => 'Create Client',
                'old' => [
                    'full_name' => $fullName,
                    'email' => $email ?? '',
                    'phone' => $phone ?? '',
                    'note' => $note ?? '',
                ],
                'errors' => $errors,
            ], 422);

            return;
        }

        $clientId = $this->clientRepository->create($fullName, $email, $phone, $note);
        Flash::set('success', 'Client created successfully.');

        $this->redirect('/clients/' . $clientId);
    }

    /**
     * @param array<string, string> $params
     */
    public function show(array $params): void
    {
        $this->requireAuth();

        $clientId = (int) ($params['id'] ?? 0);

        if ($clientId < 1) {
            $this->render('errors/not-found', ['title' => 'Not Found'], 404);

            return;
        }

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
            'errors' => [],
            'oldMovement' => [
                'movement_type' => 'earning',
                'amount' => '',
                'description' => '',
                'moved_at' => date('Y-m-d'),
            ],
        ]);
    }
}
