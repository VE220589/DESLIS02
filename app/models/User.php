<?php
// Ubicación: /app/models/User.php

require_once __DIR__ . '/../config/database.php';

class User {
    
    /**
     * Busca un usuario por su correo electrónico.
     * Ideal para el proceso de Login.
     */
    public static function findByEmail(string $email) {
        $db = Database::connect();
        
        $stmt = $db->prepare("SELECT * FROM usuarios WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        
        // Retornamos el registro como un arreglo asociativo, o false si no existe
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Busca un usuario por su ID (sin devolver la contraseña).
     * Útil para recuperar datos de la sesión actual.
     */
    public static function getById(int $id) {
        $db = Database::connect();
        
        $stmt = $db->prepare("SELECT id, nombre, email, rol, fecha_registro FROM usuarios WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Registra un nuevo usuario con rol cliente.
     */
    public static function create(array $data): array
    {
        $nombre = trim($data['nombre'] ?? '');
        $email = filter_var(trim($data['email'] ?? ''), FILTER_SANITIZE_EMAIL);
        $password = (string) ($data['password'] ?? '');

        if (strlen($nombre) < 3) {
            throw new InvalidArgumentException('El nombre debe tener al menos 3 caracteres.');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('El correo electrónico no es válido.');
        }

        if (strlen($password) < 8) {
            throw new InvalidArgumentException('La contraseña debe tener al menos 8 caracteres.');
        }

        if (self::findByEmail($email)) {
            throw new InvalidArgumentException('Ya existe un usuario registrado con ese correo.');
        }

        $db = Database::connect();
        $stmt = $db->prepare(
            "INSERT INTO usuarios (nombre, email, password, rol) VALUES (:nombre, :email, :password, 'cliente')"
        );

        $stmt->execute([
            'nombre' => htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8'),
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
        ]);

        return self::findByEmail($email);
    }
}
?>
