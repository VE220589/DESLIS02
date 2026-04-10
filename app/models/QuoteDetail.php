<?php

require_once __DIR__ . '/../config/database.php';
require_once 'Service.php';

class QuoteDetail
{
    // ==========================================
    // PROPIEDADES
    // ==========================================
    private int $id;
    private int $cotizacionId;
    private int $servicioId;
    private int $cantidad;
    private float $precioUnitario;
    private float $subtotal;

    // Propiedad extra para facilitar la vista (viene del JOIN)
    private string $servicioNombre;

    // ==========================================
    // CONSTRUCTOR
    // ==========================================
    public function __construct(
        int $id,
        int $cotizacionId,
        int $servicioId,
        int $cantidad,
        float $precioUnitario,
        float $subtotal,
        string $servicioNombre = ''
    ) {
        $this->id = $id;
        $this->cotizacionId = $cotizacionId;
        $this->servicioId = $servicioId;
        $this->cantidad = $cantidad;
        $this->precioUnitario = $precioUnitario;
        $this->subtotal = $subtotal;
        $this->servicioNombre = $servicioNombre;
    }

    // ==========================================
    // MÉTODOS DE BASE DE DATOS (PDO)
    // ==========================================

    /**
     * Obtiene todos los ítems asociados a una cotización específica.
     * Retorna un arreglo de objetos QuoteDetail.
     */
    public static function getByQuoteId(int $cotizacionId): array
    {
        $db = Database::connect();

        // Usamos INNER JOIN para obtener el nombre del servicio directamente
        $sql = "SELECT cd.id, cd.cotizacion_id, cd.servicio_id, cd.cantidad, 
                       cd.precio_unitario, cd.subtotal, s.nombre AS servicio_nombre
                FROM cotizacion_detalles cd
                INNER JOIN servicios s ON cd.servicio_id = s.id
                WHERE cd.cotizacion_id = :cotizacion_id";

        $stmt = $db->prepare($sql);
        $stmt->execute(['cotizacion_id' => $cotizacionId]);

        $detalles = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $detalles[] = new self(
                (int) $row['id'],
                (int) $row['cotizacion_id'],
                (int) $row['servicio_id'],
                (int) $row['cantidad'],
                (float) $row['precio_unitario'],
                (float) $row['subtotal'],
                $row['servicio_nombre']
            );
        }

        return $detalles;
    }

    // ==========================================
    // GETTERS
    // ==========================================

    public function getId(): int
    {
        return $this->id;
    }
    public function getCotizacionId(): int
    {
        return $this->cotizacionId;
    }
    public function getServicioId(): int
    {
        return $this->servicioId;
    }
    public function getCantidad(): int
    {
        return $this->cantidad;
    }
    public function getPrecioUnitario(): float
    {
        return $this->precioUnitario;
    }
    public function getSubtotal(): float
    {
        return $this->subtotal;
    }

    // Getter para el nombre del servicio (útil para la tabla de la vista)
    public function getServicioNombre(): string
    {
        return $this->servicioNombre;
    }
}
?>