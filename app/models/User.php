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
}
?>