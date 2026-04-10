<?php
// Ubicación: /public/test_db.php

// 1. Incluimos tu archivo de conexión
require_once '../app/config/database.php';

echo "<h1>Prueba de Conexión PDO</h1>";

try {
    // 2. Intentamos establecer la conexión usando tu método Singleton
    $db = Database::connect();

    echo "<p style='color: green;'>✅ ¡Conexión exitosa a la base de datos 'cotizador_mvc'!</p>";

    // 3. Ejecutamos una consulta sencilla a la tabla de usuarios
    $stmt = $db->query("SELECT nombre, email, rol FROM usuarios");
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<h3>Usuarios de prueba encontrados:</h3>";
    echo "<ul>";
    foreach ($usuarios as $user) {
        echo "<li><strong>{$user['nombre']}</strong> - {$user['email']} <em>(Rol: {$user['rol']})</em></li>";
    }
    echo "</ul>";

    // 4. Verificamos la tabla de servicios
    $stmtServicios = $db->query("SELECT COUNT(*) as total FROM servicios");
    $resultado = $stmtServicios->fetch(PDO::FETCH_ASSOC);

    echo "<p>Total de servicios listos en el catálogo: <strong>{$resultado['total']}</strong></p>";

} catch (Exception $e) {
    // Si hay un error de contraseña, base de datos no encontrada, etc., caerá aquí.
    echo "<p style='color: red;'>❌ Error de Conexión: " . $e->getMessage() . "</p>";
    echo "<p><em>Sugerencia: Verifica que MySQL esté corriendo y que las credenciales en /app/config/database.php sean correctas.</em></p>";
}
?>