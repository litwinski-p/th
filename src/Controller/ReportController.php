<?php

declare(strict_types=1);

namespace Th\Controller;

use Th\Core\Auth;
use Th\Core\View;
use Th\Form\ReportFilterForm;
use Th\Repository\Contracts\ClientRepositoryInterface;
use Th\Repository\Contracts\MovementRepositoryInterface;

final class ReportController extends Controller
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

        $form = ReportFilterForm::fromArray($_GET);
        $clientId = $form->clientId();

        if ($clientId !== null && !$this->clientRepository->exists($clientId)) {
            $form->addError('client_id', 'Selected client was not found.');
        }

        $movements = [];
        $totals = [
            'earnings' => '0.00',
            'expenses' => '0.00',
            'balance' => '0.00',
        ];

        if ($form->isValid()) {
            $movements = $this->movementRepository->report($clientId, $form->fromDate(), $form->toDate());
            $totals = $this->movementRepository->totals($clientId, $form->fromDate(), $form->toDate());
        }

        $this->render('reports/index', [
            'title' => 'Reports',
            'clients' => $this->clientRepository->allForSelect(),
            'filters' => $form->filters(),
            'errors' => $form->errors(),
            'movements' => $movements,
            'totals' => $totals,
        ]);
    }
}
