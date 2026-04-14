<?php
// Ubicación: /app/controllers/AuthController.php

require_once __DIR__ . '/../models/User.php';

class AuthController {

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Procesa la petición AJAX de inicio de sesión
     */
    public function login() {
        header('Content-Type: application/json');
        
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception("Método no permitido.");
            }

            $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
            $password = trim($_POST['password'] ?? '');

            if (empty($email) || empty($password)) {
                throw new Exception("El correo y la contraseña son obligatorios.");
            }

            // 1. Buscamos al usuario en la base de datos usando el Modelo
            $user = User::findByEmail($email);

            if (!$user) {
                // Por seguridad, no especificamos si falló el correo o la contraseña
                throw new Exception("Credenciales incorrectas."); 
            }

            // 2. Verificamos que el hash de la contraseña coincida
            if (!password_verify($password, $user['password'])) {
                throw new Exception("Credenciales incorrectas.");
            }

            // 3. ¡Éxito! Creamos las variables de sesión seguras
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_nombre'] = $user['nombre'];
            $_SESSION['user_rol'] = $user['rol']; // 'admin' o 'cliente'

            echo json_encode([
                'success' => true,
                'message' => 'Inicio de sesión exitoso.',
                'rol' => $user['rol'] // Enviamos el rol al frontend para saber a dónde redirigir
            ]);

        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Procesa el registro de nuevos usuarios.
     */
    public function register() {
        header('Content-Type: application/json');

        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception("MÃ©todo no permitido.");
            }

            $nombre = trim(strip_tags($_POST['nombre'] ?? ''));
            $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
            $password = trim($_POST['password'] ?? '');
            $confirmPassword = trim($_POST['confirm_password'] ?? '');

            if ($nombre === '' || $email === '' || $password === '' || $confirmPassword === '') {
                throw new Exception("Todos los campos son obligatorios.");
            }

            if ($password !== $confirmPassword) {
                throw new Exception("Las contraseÃ±as no coinciden.");
            }

            $user = User::create([
                'nombre' => $nombre,
                'email' => $email,
                'password' => $password,
            ]);

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_nombre'] = $user['nombre'];
            $_SESSION['user_rol'] = $user['rol'];

            echo json_encode([
                'success' => true,
                'message' => 'Cuenta creada correctamente.',
            ]);

        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Cierra la sesión del usuario vía AJAX
     */
    public function logout() {
        header('Content-Type: application/json');
        
        // Vaciamos el arreglo de sesión
        $_SESSION = [];
        
        // Destruimos la cookie de sesión si existe
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        session_destroy();

        echo json_encode([
            'success' => true,
            'message' => 'Sesión cerrada correctamente.'
        ]);
    }
}
?>
