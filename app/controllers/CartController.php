<?php
// UbicaciÃ³n: /app/controllers/CartController.php

require_once __DIR__ . '/../models/Service.php';

class CartController
{
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
    }

    public function add()
    {
        header('Content-Type: application/json');

        try {
            $this->ensureAuthenticated();

            $id = (int) ($_POST['id'] ?? 0);
            if ($id <= 0) {
                throw new Exception("ID de servicio inválido.");
            }

            $service = Service::getById($id);
            if (!$service) {
                throw new Exception("El servicio seleccionado no existe.");
            }

            $currentQuantity = (int) ($_SESSION['cart'][$id] ?? 0);
            if ($currentQuantity >= 10) {
                throw new Exception("Límite máximo de 10 unidades alcanzado.");
            }

            $_SESSION['cart'][$id] = $currentQuantity + 1;

            echo json_encode($this->buildCartResponse("Servicio agregado al carrito."));
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function update()
    {
        header('Content-Type: application/json');

        try {
            $this->ensureAuthenticated();

            $id = (int) ($_POST['id'] ?? 0);
            if (!isset($_SESSION['cart'][$id])) {
                throw new Exception("El servicio no existe dentro del carrito.");
            }

            $quantity = isset($_POST['quantity']) ? (int) $_POST['quantity'] : null;
            if ($quantity !== null) {
                if ($quantity < 1 || $quantity > 10) {
                    throw new Exception("La cantidad debe estar entre 1 y 10.");
                }
                $_SESSION['cart'][$id] = $quantity;
            } else {
                $action = $_POST['action_type'] ?? '';

                if ($action === 'increase') {
                    if ($_SESSION['cart'][$id] >= 10) {
                        throw new Exception("No puedes agregar mas de 10 unidades.");
                    }
                    $_SESSION['cart'][$id]++;
                } elseif ($action === 'decrease') {
                    $_SESSION['cart'][$id]--;
                    if ($_SESSION['cart'][$id] <= 0) {
                        unset($_SESSION['cart'][$id]);
                    }
                } else {
                    throw new Exception("Acción de actualización inválida.");
                }
            }

            echo json_encode($this->buildCartResponse("Carrito actualizado."));
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function remove()
    {
        header('Content-Type: application/json');

        try {
            $this->ensureAuthenticated();

            if (($this->getPostValue('clear')) === 'true') {
                $_SESSION['cart'] = [];
            } else {
                $id = (int) ($_POST['id'] ?? 0);
                if ($id > 0 && isset($_SESSION['cart'][$id])) {
                    unset($_SESSION['cart'][$id]);
                }
            }

            echo json_encode($this->buildCartResponse("Carrito actualizado."));
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function get()
    {
        header('Content-Type: application/json');

        try {
            echo json_encode($this->buildCartResponse());
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    private function buildCartResponse(string $message = ''): array
    {
        $items = [];
        $subtotal = 0.0;
        $totalItems = 0;

        foreach ($_SESSION['cart'] as $id => $cantidad) {
            $service = Service::getById((int) $id);
            if (!$service) {
                unset($_SESSION['cart'][$id]);
                continue;
            }

            $lineTotal = $service->getPrecio() * $cantidad;
            $items[] = [
                'id' => $service->getId(),
                'nombre' => $service->getNombre(),
                'precio' => $service->getPrecio(),
                'cantidad' => $cantidad,
                'total' => $lineTotal,
                'categoria' => $service->getCategoria(),
            ];

            $subtotal += $lineTotal;
            $totalItems += $cantidad;
        }

        $descuento = $this->calculateDiscount($subtotal, $totalItems);
        $iva = ($subtotal - $descuento) * 0.13;
        $totalFinal = $subtotal - $descuento + $iva;

        return [
            'success' => true,
            'message' => $message,
            'items' => $items,
            'subtotal' => round($subtotal, 2),
            'descuento' => round($descuento, 2),
            'iva' => round($iva, 2),
            'total' => round($totalFinal, 2),
            'totalItems' => $totalItems,
        ];
    }

    private function calculateDiscount(float $subtotal, int $totalItems): float
    {
        if ($totalItems >= 10) {
            return $subtotal * 0.18;
        }

        if ($totalItems >= 6) {
            return $subtotal * 0.12;
        }

        if ($totalItems >= 3) {
            return $subtotal * 0.08;
        }

        return 0.0;
    }

    private function ensureAuthenticated(): void
    {
        if (!isset($_SESSION['user_id'])) {
            throw new Exception("Debes iniciar sesión para gestionar el carrito.");
        }
    }

    private function getPostValue(string $key): ?string
    {
        return isset($_POST[$key]) ? trim((string) $_POST[$key]) : null;
    }
}
?>
