<?php
session_start();

$page = $_GET['page'] ?? 'home';
$action = $_GET['action'] ?? null;

if ($action !== null) {
    require_once '../app/controllers/CartController.php';
    require_once '../app/controllers/AuthController.php';
    require_once '../app/controllers/QuoteController.php';
    require_once '../app/controllers/ServiceController.php';

    $cartController = new CartController();
    $authController = new AuthController();
    $quoteController = new QuoteController();
    $serviceController = new ServiceController();

    switch ($action) {
        case 'add_to_cart':
            $cartController->add();
            break;
        case 'get_cart':
            $cartController->get();
            break;
        case 'update_cart':
            $cartController->update();
            break;
        case 'remove_from_cart':
            $cartController->remove();
            break;
        case 'login':
            $authController->login();
            break;
        case 'register':
            $authController->register();
            break;
        case 'logout':
            $authController->logout();
            break;
        case 'generate_quote':
            $quoteController->generate();
            break;
        case 'create_service':
            $serviceController->create();
            break;
        case 'delete_service':
            $serviceController->delete();
            break;
        default:
            http_response_code(404);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'La acción solicitada no existe.'
            ]);
            break;
    }

    exit;
}

switch ($page) {
    case 'home':
        require_once '../app/views/home.php';
        break;

    case 'services':
        require_once '../app/views/services/catalog.php';
        break;

    case 'quotes':
        require_once '../app/views/quotes/history.php';
        break;

    case 'login':
        require_once '../app/views/auth/login.php';
        break;

    case 'register':
        require_once '../app/views/auth/register.php';
        break;

    case 'forbidden':
        http_response_code(403);
        $errorMessage = 'No cuentas con permisos para acceder a esta página.';
        require_once '../app/views/errors/403.php';
        break;

    default:
        http_response_code(404);
        require_once '../app/views/errors/404.php';
        break;
}
?>
