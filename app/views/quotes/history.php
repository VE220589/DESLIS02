<?php
// Ubicación: /app/views/quotes/history.php

// 1. Cargamos la base de datos y el modelo de detalles
require_once '../app/config/database.php';
require_once '../app/models/QuoteDetail.php';

// 2. Seguridad: Si no hay sesión, lo mandamos al login
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php?page=login");
    exit;
}

$userId = $_SESSION['user_id'];
$userRol = $_SESSION['user_rol'];

$db = Database::connect();

// 3. Lógica de Roles: El Admin ve todo, el Cliente solo lo suyo
if ($userRol === 'admin') {
    $stmt = $db->query("SELECT * FROM cotizaciones ORDER BY fecha_generacion DESC, id DESC");
} else {
    $stmt = $db->prepare("SELECT * FROM cotizaciones WHERE usuario_id = :uid ORDER BY fecha_generacion DESC, id DESC");
    $stmt->execute(['uid' => $userId]);
}

$quotes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Historial de Cotizaciones - Cotizador MVC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="index.php?page=home">Cotizador MVC</a>
            <div class="d-flex align-items-center">
                <span class="text-white me-3">Hola,
                    <strong><?= htmlspecialchars($_SESSION['user_nombre']) ?></strong></span>
                <a href="index.php?page=services" class="btn btn-outline-light me-2">Ir al Catálogo</a>
            </div>
        </div>
    </nav>

    <div class="container my-5">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Historial de Cotizaciones</h2>
            <?php if ($userRol === 'admin'): ?>
                <span class="badge bg-primary fs-6">Vista de Administrador</span>
            <?php endif; ?>
        </div>

        <?php if (empty($quotes)): ?>

            <div class="alert alert-info shadow-sm">
                No hay cotizaciones registradas en el sistema.
            </div>

        <?php else: ?>

            <?php foreach ($quotes as $quote): ?>

                <div class="card mb-4 shadow-sm">
                    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                        <span>Código: <strong><?= htmlspecialchars($quote['codigo']); ?></strong></span>
                        <span class="badge bg-success"><?= htmlspecialchars($quote['estado']); ?></span>
                    </div>

                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <h5 class="text-primary mb-3">Datos del Cliente</h5>
                                <p class="mb-1"><strong>Nombre:</strong> <?= htmlspecialchars($quote['cliente_nombre']); ?></p>
                                <p class="mb-1"><strong>Empresa:</strong> <?= htmlspecialchars($quote['cliente_empresa']); ?>
                                </p>
                                <p class="mb-1"><strong>Email:</strong> <?= htmlspecialchars($quote['cliente_email']); ?></p>
                                <p class="mb-1"><strong>Teléfono:</strong> <?= htmlspecialchars($quote['cliente_telefono']); ?>
                                </p>
                            </div>

                            <div class="col-md-6">
                                <h5 class="text-primary mb-3">Detalles de Validez</h5>
                                <p class="mb-1"><strong>Fecha de Emisión:</strong>
                                    <?= htmlspecialchars($quote['fecha_generacion']); ?></p>
                                <p class="mb-1 text-danger"><strong>Válido hasta:</strong>
                                    <?= htmlspecialchars($quote['fecha_validez']); ?></p>
                            </div>
                        </div>

                        <h5 class="text-primary mb-3">Servicios Cotizados</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead class="table-light">
                                    <tr>
                                        <th>Servicio</th>
                                        <th class="text-center">Cantidad</th>
                                        <th class="text-end">Precio Unitario</th>
                                        <th class="text-end">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    // ¡Aquí usamos el modelo QuoteDetail que creamos!
                                    $detalles = QuoteDetail::getByQuoteId($quote['id']);
                                    foreach ($detalles as $item):
                                        ?>
                                        <tr>
                                            <td><?= htmlspecialchars($item->getServicioNombre()); ?></td>
                                            <td class="text-center"><?= $item->getCantidad(); ?></td>
                                            <td class="text-end">$<?= number_format($item->getPrecioUnitario(), 2); ?></td>
                                            <td class="text-end fw-bold">$<?= number_format($item->getSubtotal(), 2); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="row justify-content-end mt-3">
                            <div class="col-md-4 text-end">
                                <p class="mb-1"><strong>Subtotal:</strong> $<?= number_format($quote['subtotal'], 2); ?></p>
                                <p class="mb-1 text-success"><strong>Descuento:</strong>
                                    -$<?= number_format($quote['descuento'], 2); ?></p>
                                <p class="mb-1"><strong>IVA (13%):</strong> $<?= number_format($quote['iva'], 2); ?></p>
                                <hr>
                                <h4 class="text-primary fw-bold">
                                    Total Final: $<?= number_format($quote['total'], 2); ?>
                                </h4>
                            </div>
                        </div>

                    </div>
                </div>

            <?php endforeach; ?>

        <?php endif; ?>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>