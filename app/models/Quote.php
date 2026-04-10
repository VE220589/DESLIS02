<?php

require_once __DIR__ . '/../config/database.php';
require_once 'Service.php';

class Quote
{
    // ==========================================
    // CONSTANTES Y REGLAS DE NEGOCIO
    // ==========================================
    public const IVA = 0.13;
    public const MONTO_MINIMO = 100;

    // ==========================================
    // PROPIEDADES
    // ==========================================
    private string $codigo;
    private array $cliente;
    private array $items = [];

    private float $subtotal = 0.0;
    private float $descuento = 0.0;
    private float $iva = 0.0;
    private float $total = 0.0;

    private string $fechaGeneracion;
    private string $fechaValidez;

    // ==========================================
    // CONSTRUCTOR Y VALIDACIONES
    // ==========================================
    public function __construct(array $cliente)
    {
        $this->validarCliente($cliente);
        $this->cliente = $cliente;
    }

    private function validarCliente(array $cliente): void
    {
        $camposRequeridos = ['nombre', 'empresa', 'email', 'telefono'];

        foreach ($camposRequeridos as $campo) {
            if (empty(trim($cliente[$campo] ?? ''))) {
                throw new InvalidArgumentException("El campo {$campo} es obligatorio.");
            }
        }

        if (!filter_var($cliente['email'], FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("El email no tiene formato válido.");
        }
    }

    // ==========================================
    // GESTIÓN DE ÍTEMS Y CÁLCULOS (Opción B)
    // ==========================================
    public function agregarItem(Service $service, int $cantidad): void
    {
        if ($cantidad < 1 || $cantidad > 10) {
            throw new InvalidArgumentException("La cantidad debe estar entre 1 y 10.");
        }

        $this->items[] = [
            'service' => $service,
            'cantidad' => $cantidad
        ];
    }

    public function calcularSubtotal(): void
    {
        $this->subtotal = 0;
        foreach ($this->items as $item) {
            $this->subtotal += $item['service']->getPrecio() * $item['cantidad'];
        }
    }

    public function calcularDescuento(): void
    {
        $totalUnidades = 0;
        foreach ($this->items as $item) {
            $totalUnidades += $item['cantidad'];
        }

        $porcentaje = 0;
        if ($totalUnidades >= 3 && $totalUnidades <= 5) {
            $porcentaje = 0.08;
        } elseif ($totalUnidades >= 6 && $totalUnidades <= 9) {
            $porcentaje = 0.12;
        } elseif ($totalUnidades >= 10) {
            $porcentaje = 0.18;
        }

        $this->descuento = $this->subtotal * $porcentaje;
    }

    public function calcularIVA(): void
    {
        $baseImponible = $this->subtotal - $this->descuento;
        $this->iva = $baseImponible * self::IVA;
    }

    public function calcularTotal(): void
    {
        $this->total = ($this->subtotal - $this->descuento) + $this->iva;
    }

    public static function validarMonto(float $subtotal): void
    {
        if ($subtotal < self::MONTO_MINIMO) {
            throw new InvalidArgumentException(
                "El monto mínimo para generar una cotización es $" . self::MONTO_MINIMO
            );
        }
    }

    // ==========================================
    // GENERACIÓN Y BASE DE DATOS (NUEVO)
    // ==========================================
    
    /**
     * Genera un código único basado en el año actual y la cantidad de registros en la BD.
     */
    private function generarCodigoDB(): string
    {
        $db = Database::connect();
        $anio = date('Y');
        
        $stmt = $db->query("SELECT COUNT(*) FROM cotizaciones WHERE YEAR(fecha_generacion) = {$anio}");
        $conteo = $stmt->fetchColumn();
        
        $consecutivo = str_pad($conteo + 1, 4, '0', STR_PAD_LEFT);
        return "COT-{$anio}-{$consecutivo}";
    }

    public function generar(): void
    {
        if (empty($this->items)) {
            throw new RuntimeException("No se puede generar una cotización con el carrito vacío.");
        }

        $this->calcularSubtotal();
        self::validarMonto($this->subtotal);
        $this->calcularDescuento();
        $this->calcularIVA();
        $this->calcularTotal();

        // Generamos el código conectando a la base de datos
        $this->codigo = $this->generarCodigoDB();

        $this->fechaGeneracion = date('Y-m-d');
        $this->fechaValidez = date('Y-m-d', strtotime('+7 days'));
    }

    /**
     * Guarda la cotización completa en la Base de Datos usando Transacciones
     */
    public function save(int $usuarioId): bool
    {
        // 1. Nos aseguramos de que los cálculos y el código estén generados
        $this->generar();

        $db = Database::connect();

        try {
            // 2. Iniciamos la transacción
            $db->beginTransaction();

            // 3. Insertamos la Cabecera de la cotización
            $stmt = $db->prepare("INSERT INTO cotizaciones (usuario_id, codigo, cliente_nombre, cliente_empresa, cliente_email, cliente_telefono, subtotal, descuento, iva, total, fecha_generacion, fecha_validez) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            $stmt->execute([
                $usuarioId,
                $this->codigo,
                $this->cliente['nombre'],
                $this->cliente['empresa'],
                $this->cliente['email'],
                $this->cliente['telefono'],
                $this->subtotal,
                $this->descuento,
                $this->iva,
                $this->total,
                $this->fechaGeneracion,
                $this->fechaValidez
            ]);

            // Obtenemos el ID que MySQL le acaba de asignar a esta cotización
            $cotizacionId = $db->lastInsertId();

            // 4. Insertamos los Detalles (los ítems del carrito)
            $stmtDetalle = $db->prepare("INSERT INTO cotizacion_detalles (cotizacion_id, servicio_id, cantidad, precio_unitario, subtotal) VALUES (?, ?, ?, ?, ?)");

            foreach ($this->items as $item) {
                $service = $item['service'];
                $cantidad = $item['cantidad'];
                $precioUnitario = $service->getPrecio();
                $subtotalLinea = $precioUnitario * $cantidad;

                $stmtDetalle->execute([
                    $cotizacionId,
                    $service->getId(),
                    $cantidad,
                    $precioUnitario,
                    $subtotalLinea
                ]);
            }

            // 5. Si todo salió bien, confirmamos los cambios en la BD
            $db->commit();
            return true;

        } catch (Exception $e) {
            // Si algo falló (ej. un servicio no existe), deshacemos todo
            $db->rollBack();
            throw new Exception("Error al guardar en la base de datos: " . $e->getMessage());
        }
    }

    // ==========================================
    // GETTERS
    // ==========================================
    public function getCodigo(): string { return $this->codigo ?? ''; }
    public function getCliente(): array { return $this->cliente; }
    public function getSubtotal(): float { return $this->subtotal; }
    public function getDescuento(): float { return $this->descuento; }
    public function getIVA(): float { return $this->iva; }
    public function getTotal(): float { return $this->total; }
    public function getFechaGeneracion(): string { return $this->fechaGeneracion ?? ''; }
    public function getFechaValidez(): string { return $this->fechaValidez ?? ''; }

    public function getItems(): array
    {
        $itemsFormateados = [];
        foreach ($this->items as $item) {
            $service = $item['service'];
            $itemsFormateados[] = [
                'id' => $service->getId(),
                'nombre' => $service->getNombre(),
                'precio' => $service->getPrecio(),
                'cantidad' => $item['cantidad']
            ];
        }
        return $itemsFormateados;
    }
}
?>