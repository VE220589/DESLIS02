<?php
require_once '../app/config/database.php';

try {
    $db = Database::connect();
    
    // Encriptamos 'password123' nativamente en tu servidor
    $nueva_password_segura = password_hash('password123', PASSWORD_DEFAULT);
    
    // Actualizamos a todos los usuarios para que tengan esta contraseña
    $stmt = $db->prepare("UPDATE usuarios SET password = :hash");
    $stmt->execute(['hash' => $nueva_password_segura]);
    
    echo "<h2 style='color: green;'>✅ ¡Contraseñas actualizadas con éxito!</h2>";
    echo "<p>Todos los usuarios ahora tienen la contraseña: <strong>password123</strong></p>";
    echo "<p>Ya puedes borrar este archivo y probar el login de nuevo.</p>";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>