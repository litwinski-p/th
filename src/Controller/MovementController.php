<?php

declare(strict_types=1);

namespace Th\Controller;

use Th\Core\Auth;
use Th\Core\Flash;
use Th\Core\View;
use Th\Form\MovementCreateForm;
use Th\Repository\Contracts\ClientRepositoryInterface;
use Th\Repository\Contracts\MovementRepositoryInterface;

final class MovementController extends Controller
{
    public function __construct(
        View $view,
        Auth $auth,
        private ClientRepositoryInterface $clientRepository,
        private MovementRepositoryInterface $movementRepository
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

        $clientId = $this->positiveIntParam($params, 'id');

        if ($clientId === null || !$this->clientRepository->exists($clientId)) {
            $this->render('errors/not-found', ['title' => 'Not Found'], 404);

            return;
        }

        $form = MovementCreateForm::fromArray($_POST);

        if (!$form->isValid()) {
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
                'errors' => $form->errors(),
                'oldMovement' => $form->old(),
            ], 422);

            return;
        }

        $this->movementRepository->create(
            $clientId,
            $form->movementType(),
            $form->normalizedAmount(),
            $form->description(),
            $form->movedAt()
        );
        Flash::set('success', 'Movement added successfully.');

        $this->redirect('/clients/' . $clientId);
    }
}
