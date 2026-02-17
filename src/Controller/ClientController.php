<?php

declare(strict_types=1);

namespace Th\Controller;

use Th\Core\Auth;
use Th\Core\Flash;
use Th\Core\View;
use Th\Form\ClientCreateForm;
use Th\Repository\Contracts\ClientRepositoryInterface;
use Th\Repository\Contracts\MovementRepositoryInterface;

final class ClientController extends Controller
{
    public function __construct(
        View $view,
        Auth $auth,
        private ClientRepositoryInterface $clientRepository,
        private MovementRepositoryInterface $movementRepository
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

        $form = ClientCreateForm::fromArray($_POST);

        if (!$form->isValid()) {
            $this->render('clients/create', [
                'title' => 'Create Client',
                'old' => $form->old(),
                'errors' => $form->errors(),
            ], 422);

            return;
        }

        $clientId = $this->clientRepository->create(
            $form->fullName(),
            $form->email(),
            $form->phone(),
            $form->note()
        );
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
