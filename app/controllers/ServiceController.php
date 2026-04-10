<?php
// Ubicación: /app/controllers/ServiceController.php

require_once __DIR__ . '/../models/Service.php';

class ServiceController {

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Middleware de seguridad: Verifica que el usuario sea Administrador
     */
    private function checkAdmin() {
        if (!isset($_SESSION['user_rol']) || $_SESSION['user_rol'] !== 'admin') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Acceso denegado. Permisos de administrador requeridos.']);
            exit; // Detiene la ejecución si no es admin
        }
    }

    /**
     * Crea un nuevo servicio en el catálogo (Solo Admin)
     */
    public function create() {
        $this->checkAdmin();
        header('Content-Type: application/json');

        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception("Método no permitido.");
            }

            // Instanciamos el servicio (pasamos ID 0 porque la BD generará el real)
            // Esto es genial porque aprovecha todas tus validaciones originales
            $service = new Service(
                0, 
                $_POST['nombre'] ?? '',
                $_POST['descripcion'] ?? '',
                (float) ($_POST['precio'] ?? 0),
                $_POST['categoria'] ?? ''
            );
            
            if ($service->save()) {
                echo json_encode(['success' => true, 'message' => 'Servicio agregado al catálogo.']);
            } else {
                throw new Exception("No se pudo guardar el servicio.");
            }

        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Elimina un servicio del catálogo (Solo Admin)
     */
    public function delete() {
        $this->checkAdmin();
        header('Content-Type: application/json');

        try {
            $id = (int) ($_POST['id'] ?? 0);
            if ($id <= 0) throw new Exception("ID de servicio inválido.");

            if (Service::deleteById($id)) {
                echo json_encode(['success' => true, 'message' => 'Servicio eliminado correctamente.']);
            } else {
                throw new Exception("No se pudo eliminar. Es posible que existan cotizaciones ligadas a este servicio.");
            }

        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
?>