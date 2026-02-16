<?php

declare(strict_types=1);

use Th\Controller\AuthController;
use Th\Controller\ClientController;
use Th\Controller\DashboardController;
use Th\Controller\MovementController;
use Th\Controller\ReportController;
use Th\Core\Auth;
use Th\Core\Database;
use Th\Core\Env;
use Th\Core\Router;
use Th\Core\Session;
use Th\Core\View;
use Th\Repository\AdminRepository;
use Th\Repository\ClientRepository;
use Th\Repository\MovementRepository;

$projectRoot = dirname(__DIR__);
$vendorAutoload = $projectRoot . '/vendor/autoload.php';

if (!is_file($vendorAutoload)) {
    http_response_code(500);
    echo 'Dependencies are missing. Run composer install.';
    exit;
}

require $vendorAutoload;

Env::load($projectRoot . '/.env');
Session::start();

header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: no-referrer');
header("Content-Security-Policy: default-src 'self'; style-src 'self' 'unsafe-inline'; form-action 'self'; base-uri 'self'; frame-ancestors 'none'");

try {
    $pdo = Database::connectFromEnv();

    $adminRepository = new AdminRepository($pdo);
    $clientRepository = new ClientRepository($pdo);
    $movementRepository = new MovementRepository($pdo);

    $auth = new Auth($adminRepository);
    $view = new View($projectRoot . '/src/View', $auth);

    $authController = new AuthController($view, $auth, $adminRepository);
    $dashboardController = new DashboardController($view, $auth, $clientRepository, $movementRepository);
    $clientController = new ClientController($view, $auth, $clientRepository, $movementRepository);
    $movementController = new MovementController($view, $auth, $clientRepository, $movementRepository);
    $reportController = new ReportController($view, $auth, $clientRepository, $movementRepository);

    $router = new Router();

    $router->get('/setup', static fn () => $authController->showSetup());
    $router->post('/setup', static fn () => $authController->setup());

    $router->get('/login', static fn () => $authController->showLogin());
    $router->post('/login', static fn () => $authController->login());
    $router->post('/logout', static fn () => $authController->logout());

    $router->get('/', static fn () => $dashboardController->index());

    $router->get('/clients', static fn () => $clientController->index());
    $router->get('/clients/create', static fn () => $clientController->create());
    $router->post('/clients', static fn () => $clientController->store());
    $router->get('/clients/{id}', static fn (array $params) => $clientController->show($params));
    $router->post('/clients/{id}/movements', static fn (array $params) => $movementController->store($params));

    $router->get('/reports', static fn () => $reportController->index());

    $requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $requestUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

    if (!is_string($requestUri)) {
        $requestUri = '/';
    }

    if (!$router->dispatch($requestMethod, $requestUri)) {
        http_response_code(404);
        $view->render('errors/not-found', ['title' => 'Not Found']);
    }
} catch (Throwable $exception) {
    http_response_code(500);
    echo 'An internal server error occurred.';
}
