<?php

class Database
{
    private static $host = 'localhost';
    private static $db_name = 'cotizador_mvc';
    private static $username = 'root';
    private static $password = '';

    private static $conn = null;

    /**
     * Obtiene la conexión a la base de datos usando el patrón Singleton
     */
    public static function connect()
    {
        // Si la conexión aún no existe, la creamos
        if (self::$conn === null) {
            try {
                // Instanciamos PDO
                self::$conn = new PDO(
                    "mysql:host=" . self::$host . ";dbname=" . self::$db_name . ";charset=utf8mb4",
                    self::$username,
                    self::$password
                );

                // Configuramos PDO para que lance excepciones cuando haya errores SQL
                self::$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                // Evitamos que PDO emule las sentencias preparadas (mayor seguridad)
                self::$conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

            } catch (PDOException $e) {
                // En un entorno de producción, aquí guardaríamos el error en un log (error_log)
                // y mostraríamos un mensaje genérico. Por ahora, mostramos el error para debug.
                die("Error crítico de Conexión a la Base de Datos: " . $e->getMessage());
            }
        }

        // Retornamos la conexión activa
        return self::$conn;
    }
}
?>