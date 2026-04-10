<?php
// Ubicación: /app/controllers/QuoteController.php

require_once __DIR__ . '/../models/Service.php';
require_once __DIR__ . '/../models/Quote.php';

class QuoteController
{

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Procesa la petición AJAX para generar y guardar una cotización
     */
    public function generate()
    {
        header('Content-Type: application/json');

        try {
            // 1. Validaciones iniciales
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception("Método no permitido.");
            }

            // Validar que el usuario haya iniciado sesión
            if (!isset($_SESSION['user_id'])) {
                throw new Exception("Debes iniciar sesión para generar una cotización.");
            }

            // Validar que el carrito no esté vacío
            if (empty($_SESSION['cart'])) {
                throw new Exception("No se puede generar una cotización con el carrito vacío.");
            }

            // 2. Recibir y limpiar los datos del cliente
            $cliente = [
                'nombre' => trim($_POST['nombre'] ?? ''),
                'empresa' => trim($_POST['empresa'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'telefono' => trim($_POST['telefono'] ?? '')
            ];

            // 3. Instanciar el Modelo Quote
            $quote = new Quote($cliente);

            // 4. Leer el carrito y agregar los servicios reales desde la BD
            foreach ($_SESSION['cart'] as $id => $cantidad) {
                // Consultamos el servicio a la base de datos
                $service = Service::getById($id);

                if (!$service) {
                    throw new Exception("El servicio con ID {$id} ya no está disponible o no existe.");
                }

                // Agregamos el ítem al objeto cotización
                $quote->agregarItem($service, $cantidad);
            }

            // 5. Generar cálculos y Guardar en la Base de Datos
            // Le pasamos el ID del usuario en sesión para que quede registrado a su nombre
            $quote->save($_SESSION['user_id']);

            // 6. Limpiar el carrito después del éxito
            unset($_SESSION['cart']);

            // 7. Retornar éxito al Frontend
            echo json_encode([
                'success' => true,
                'codigo' => $quote->getCodigo(),
                'subtotal' => $quote->getSubtotal(),
                'descuento' => $quote->getDescuento(),
                'iva' => $quote->getIVA(),
                'total' => $quote->getTotal(),
                'fechaGeneracion' => $quote->getFechaGeneracion(),
                'fechaValidez' => $quote->getFechaValidez(),
                'message' => 'Cotización generada y guardada con éxito.'
            ]);

        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}
?>