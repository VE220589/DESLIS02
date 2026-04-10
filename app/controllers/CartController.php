<?php
// Ubicación: /app/controllers/CartController.php

require_once __DIR__ . '/../models/Service.php';
require_once __DIR__ . '/../models/Quote.php'; // Lo usaremos para calcular Opción B

class CartController {

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
    }

    // Reemplaza a add-to-cart.php
    public function add() {
        header('Content-Type: application/json');
        try {
            $id = intval($_POST['id'] ?? 0);
            if ($id <= 0) throw new Exception("ID de servicio inválido.");

            // Aquí en lugar del array estático, consultaremos al Modelo
            // $servicio = Service::getById($id);
            // if (!$servicio) throw new Exception("El servicio no existe.");

            if (isset($_SESSION['cart'][$id])) {
                if ($_SESSION['cart'][$id] >= 10) {
                    throw new Exception("Límite máximo de 10 unidades alcanzado.");
                }
                $_SESSION['cart'][$id]++;
            } else {
                $_SESSION['cart'][$id] = 1;
            }

            echo json_encode(['success' => true, 'totalItems' => array_sum($_SESSION['cart'])]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // Reemplaza a update-cart.php
    public function update() {
        header('Content-Type: application/json');
        $id = intval($_POST['id'] ?? 0);
        $action = $_POST['action_type'] ?? ''; // 'increase' o 'decrease'

        if (isset($_SESSION['cart'][$id])) {
            if ($action === 'increase' && $_SESSION['cart'][$id] < 10) {
                $_SESSION['cart'][$id]++;
            } elseif ($action === 'decrease') {
                $_SESSION['cart'][$id]--;
                if ($_SESSION['cart'][$id] <= 0) {
                    unset($_SESSION['cart'][$id]);
                }
            }
        }
        echo json_encode(['success' => true]);
    }

    // Reemplaza a remove-item.php y remove-from-cart.php
    public function remove() {
        header('Content-Type: application/json');
        if (isset($_POST['clear']) && $_POST['clear'] === 'true') {
            $_SESSION['cart'] = [];
        } else {
            $id = intval($_POST['id'] ?? 0);
            if (isset($_SESSION['cart'][$id])) {
                unset($_SESSION['cart'][$id]);
            }
        }
        echo json_encode(['success' => true]);
    }

    // Reemplaza a get-cart.php
    public function get() {
        header('Content-Type: application/json');
        $items = [];
        $subtotal = 0;
        $totalItems = 0;

        foreach ($_SESSION['cart'] as $id => $cantidad) {
            // NOTA: Aquí llamaremos a la base de datos usando el Modelo
            // $service = Service::getById($id); 
            
            // Simulación temporal para que no falle mientras hacemos el Modelo
            $precioFicticio = 100; // Esto se borrará luego
            $nombreFicticio = "Servicio " . $id; 
            
            $totalLinea = $precioFicticio * $cantidad;
            $items[] = [
                'id' => $id,
                'nombre' => $nombreFicticio,
                'precio' => $precioFicticio,
                'cantidad' => $cantidad,
                'total' => $totalLinea
            ];
            $subtotal += $totalLinea;
            $totalItems += $cantidad;
        }

        // Aplicamos Opción B (Descuento por Cantidad)
        $descuento = 0;
        if ($totalItems >= 10) {
            $descuento = $subtotal * 0.18;
        } elseif ($totalItems >= 6) {
            $descuento = $subtotal * 0.12;
        } elseif ($totalItems >= 3) {
            $descuento = $subtotal * 0.08;
        }

        $iva = ($subtotal - $descuento) * 0.13;
        $totalFinal = $subtotal - $descuento + $iva;

        echo json_encode([
            'success' => true,
            'items' => $items,
            'subtotal' => $subtotal,
            'descuento' => $descuento,
            'iva' => $iva,
            'total' => $totalFinal,
            'totalItems' => $totalItems
        ]);
    }
}
?>