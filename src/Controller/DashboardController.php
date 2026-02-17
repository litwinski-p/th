<?php

declare(strict_types=1);

namespace Th\Controller;

use Th\Core\Auth;
use Th\Core\View;
use Th\Repository\Contracts\ClientRepositoryInterface;
use Th\Repository\Contracts\MovementRepositoryInterface;

final class DashboardController extends Controller
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

        $totals = $this->movementRepository->totals();

        $this->render('dashboard/index', [
            'title' => 'Dashboard',
            'clientCount' => $this->clientRepository->count(),
            'totals' => $totals,
            'recentMovements' => $this->movementRepository->recent(10),
        ]);
    }
}
