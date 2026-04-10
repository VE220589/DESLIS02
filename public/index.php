<?php
// Ubicación: /public/index.php
session_start();

// 1. Detectar qué página o acción está pidiendo el usuario
// Si no piden nada, por defecto cargamos 'home'
$page = $_GET['page'] ?? 'home';
$action = $_GET['action'] ?? null;

// ==========================================
// 2. GESTIÓN DE PETICIONES AJAX (CONTROLADORES)
// ==========================================
if ($action !== null) {
    // Si viene una 'action', significa que es AJAX. 
    // Requerimos el controlador que unificamos en el paso anterior.
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
    }
    
    // Detenemos el script aquí porque AJAX solo espera un JSON, no HTML.
    exit; 
}

// ==========================================
// 3. GESTIÓN DE VISTAS (PÁGINAS HTML)
// ==========================================
switch ($page) {
    case 'home':
        // Cargamos tu landing page
        require_once '../app/views/home.php'; 
        break;
        
    case 'services':
        // Cargamos el catálogo
        require_once '../app/views/services/catalog.php'; 
        break;
        
    case 'quotes':
        // Cargamos el historial
        require_once '../app/views/quotes/history.php'; 
        break;

    case 'login': // Ruta para que carguemos el login
        require_once '../app/views/auth/login.php'; 
        break;
        
    default:
        // Si escriben una URL rara, mostramos error
        echo "<h1>404 - Página no encontrada</h1>";
        break;
}
?>