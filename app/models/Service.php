<?php
// Ubicación: /app/models/Service.php

require_once __DIR__ . '/../config/database.php';

class Service
{
    // ==========================================
    // CONSTANTES
    // ==========================================

    public const CATEGORIAS_VALIDAS = [
        'Desarrollo Web',
        'Marketing Digital',
        'Soporte y Consultoría'
    ];

    public const PRECIO_MINIMO = 100;
    public const PRECIO_MAXIMO = 10000;

    // ==========================================
    // PROPIEDADES
    // ==========================================

    private int $id;
    private string $nombre;
    private string $descripcion;
    private float $precio;
    private string $categoria;

    // ==========================================
    // CONSTRUCTOR
    // ==========================================

    public function __construct(
        int $id,
        string $nombre,
        string $descripcion,
        float $precio,
        string $categoria
    ) {
        // Ejecutamos validaciones antes de asignar
        $this->validarId($id);
        $this->validarNombre($nombre);
        $this->validarDescripcion($descripcion);
        $this->validarPrecio($precio);
        $this->validarCategoria($categoria);

        $this->id = $id;
        $this->nombre = trim($nombre);
        $this->descripcion = trim($descripcion);
        $this->precio = $precio;
        $this->categoria = $categoria;
    }

    // ==========================================
    // MÉTODOS DE VALIDACIÓN INTERNA
    // ==========================================

    private function validarId(int $id): void
    {
        if ($id < 0) {
            throw new InvalidArgumentException("El ID del servicio no puede ser negativo.");
        }
    }

    private function validarNombre(string $nombre): void
    {
        $nombre = trim($nombre);

        if (empty($nombre)) {
            throw new InvalidArgumentException("El nombre del servicio no puede estar vacío.");
        }

        if (strlen($nombre) < 3) {
            throw new InvalidArgumentException("El nombre del servicio debe tener al menos 3 caracteres.");
        }
    }

    private function validarDescripcion(string $descripcion): void
    {
        $descripcion = trim($descripcion);

        if (empty($descripcion)) {
            throw new InvalidArgumentException("La descripción del servicio no puede estar vacía.");
        }

        if (strlen($descripcion) < 10) {
            throw new InvalidArgumentException("La descripción debe tener al menos 10 caracteres.");
        }
    }

    private function validarPrecio(float $precio): void
    {
        if (!is_numeric($precio)) {
            throw new InvalidArgumentException("El precio debe ser numérico.");
        }

        if ($precio < self::PRECIO_MINIMO || $precio > self::PRECIO_MAXIMO) {
            throw new InvalidArgumentException(
                "El precio debe estar entre $" . self::PRECIO_MINIMO .
                " y $" . self::PRECIO_MAXIMO . "."
            );
        }
    }

    private function validarCategoria(string $categoria): void
    {
        if (!in_array(trim($categoria), self::CATEGORIAS_VALIDAS, true)) {
            throw new InvalidArgumentException("Categoría no válida para el servicio.");
        }
    }

    // ==========================================
    // MÉTODOS DE INTERACCIÓN CON LA BDD (PDO)
    // ==========================================

    /**
     * Obtiene TODOS los servicios de la base de datos.
     * Retorna un arreglo de objetos Service instanciados.
     */
    public static function getAll(): array
    {
        $db = Database::connect();
        $stmt = $db->query("SELECT id, nombre, descripcion, precio, categoria FROM servicios");

        $servicios = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $servicios[] = new self(
                (int) $row['id'],
                $row['nombre'],
                $row['descripcion'],
                (float) $row['precio'],
                $row['categoria']
            );
        }

        return $servicios;
    }

    /**
     * Busca un servicio específico por su ID.
     * Retorna un objeto Service o null si no existe.
     */
    public static function getById(int $id): ?self
    {
        $db = Database::connect();
        $stmt = $db->prepare("SELECT id, nombre, descripcion, precio, categoria FROM servicios WHERE id = :id");
        $stmt->execute(['id' => $id]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            return new self(
                (int) $row['id'],
                $row['nombre'],
                $row['descripcion'],
                (float) $row['precio'],
                $row['categoria']
            );
        }

        return null;
    }

    /**
     * Guarda el servicio actual en la base de datos (INSERT)
     */
    public function save(): bool
    {
        $db = Database::connect();
        $stmt = $db->prepare("INSERT INTO servicios (nombre, descripcion, precio, categoria) VALUES (:nombre, :descripcion, :precio, :categoria)");
        
        return $stmt->execute([
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
            'precio' => $this->precio,
            'categoria' => $this->categoria
        ]);
    }

    /**
     * Elimina un servicio de la base de datos por su ID (DELETE)
     */
    public static function deleteById(int $id): bool
    {
        $db = Database::connect();
        $stmt = $db->prepare("DELETE FROM servicios WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    // ==========================================
    // GETTERS
    // ==========================================

    public function getId(): int
    {
        return $this->id;
    }

    public function getNombre(): string
    {
        return $this->nombre;
    }

    public function getDescripcion(): string
    {
        return $this->descripcion;
    }

    public function getPrecio(): float
    {
        return $this->precio;
    }

    public function getCategoria(): string
    {
        return $this->categoria;
    }
}
?>
