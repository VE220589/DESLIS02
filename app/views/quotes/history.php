<?php
require_once '../app/config/database.php';
require_once '../app/models/QuoteDetail.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php?page=login");
    exit;
}

$userId = $_SESSION['user_id'];
$userRol = $_SESSION['user_rol'];
$db = Database::connect();

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

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container py-2">
            <a class="navbar-brand fw-bold" href="index.php?page=home">Cotizador MVC</a>
            <div class="d-flex align-items-center gap-2 flex-wrap justify-content-end">
                <span class="text-white">Hola, <strong><?= htmlspecialchars($_SESSION['user_nombre']) ?></strong></span>
                <?php if ($userRol === 'admin'): ?>
                    <span class="badge text-bg-warning">Administrador</span>
                <?php endif; ?>
                <a href="index.php?page=services" class="btn btn-outline-light btn-sm">Ir al Catalogo</a>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
            <div>
                <h1 class="h3 mb-1">Historial de Cotizaciones</h1>
                <p class="text-muted mb-0">
                    <?= $userRol === 'admin'
                        ? 'Visualizas todas las cotizaciones registradas en el sistema.'
                        : 'AquÃ­ puedes revisar las cotizaciones generadas con tu cuenta.'; ?>
                </p>
            </div>
        </div>

        <?php if (empty($quotes)): ?>
            <div class="alert alert-info shadow-sm">
                No hay cotizaciones registradas en el sistema.
            </div>
        <?php else: ?>
            <?php foreach ($quotes as $quote): ?>
                <?php
                $detalles = QuoteDetail::getByQuoteId((int) $quote['id']);
                $cantidadServicios = 0;
                foreach ($detalles as $detalle) {
                    $cantidadServicios += $detalle->getCantidad();
                }
                ?>
                <article class="card mb-4 shadow-sm border-0">
                    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <span class="text-white-50 small">CÃ³digo</span><br>
                            <strong><?= htmlspecialchars($quote['codigo']); ?></strong>
                        </div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <span class="badge bg-info text-dark"><?= $cantidadServicios; ?> servicio(s)</span>
                            <span class="badge bg-success"><?= htmlspecialchars($quote['estado']); ?></span>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <div class="border rounded p-3 h-100 bg-light-subtle">
                                    <h2 class="h6 text-primary">Cliente</h2>
                                    <p class="mb-1"><strong>Nombre:</strong> <?= htmlspecialchars($quote['cliente_nombre']); ?></p>
                                    <p class="mb-1"><strong>Empresa:</strong> <?= htmlspecialchars($quote['cliente_empresa']); ?></p>
                                    <p class="mb-0"><strong>Correo:</strong> <?= htmlspecialchars($quote['cliente_email']); ?></p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="border rounded p-3 h-100 bg-light-subtle">
                                    <h2 class="h6 text-primary">Fechas</h2>
                                    <p class="mb-1"><strong>GeneraciÃ³n:</strong> <?= htmlspecialchars($quote['fecha_generacion']); ?></p>
                                    <p class="mb-0"><strong>VÃ¡lida hasta:</strong> <?= htmlspecialchars($quote['fecha_validez']); ?></p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="border rounded p-3 h-100 bg-light-subtle">
                                    <h2 class="h6 text-primary">Resumen</h2>
                                    <p class="mb-1"><strong>Total:</strong> $<?= number_format((float) $quote['total'], 2); ?></p>
                                    <p class="mb-1"><strong>IVA:</strong> $<?= number_format((float) $quote['iva'], 2); ?></p>
                                    <p class="mb-0"><strong>TelÃ©fono:</strong> <?= htmlspecialchars($quote['cliente_telefono']); ?></p>
                                </div>
                            </div>
                        </div>

                        <div class="d-none d-md-block">
                            <div class="table-responsive">
                                <table class="table table-bordered align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Servicio</th>
                                            <th class="text-center">Cantidad</th>
                                            <th class="text-end">Precio Unitario</th>
                                            <th class="text-end">Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($detalles as $item): ?>
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
                        </div>

                        <div class="d-md-none">
                            <div class="row g-3">
                                <?php foreach ($detalles as $item): ?>
                                    <div class="col-12">
                                        <div class="border rounded p-3">
                                            <h3 class="h6 mb-2"><?= htmlspecialchars($item->getServicioNombre()); ?></h3>
                                            <p class="mb-1"><strong>Cantidad:</strong> <?= $item->getCantidad(); ?></p>
                                            <p class="mb-1"><strong>Precio Unitario:</strong> $<?= number_format($item->getPrecioUnitario(), 2); ?></p>
                                            <p class="mb-0"><strong>Subtotal:</strong> $<?= number_format($item->getSubtotal(), 2); ?></p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="row justify-content-end mt-4">
                            <div class="col-md-4">
                                <div class="border rounded p-3 bg-white shadow-sm">
                                    <p class="mb-1"><strong>Subtotal:</strong> $<?= number_format((float) $quote['subtotal'], 2); ?></p>
                                    <p class="mb-1 text-success"><strong>Descuento:</strong> -$<?= number_format((float) $quote['descuento'], 2); ?></p>
                                    <p class="mb-1"><strong>IVA (13%):</strong> $<?= number_format((float) $quote['iva'], 2); ?></p>
                                    <hr>
                                    <h4 class="text-primary fw-bold mb-0">
                                        Total Final: $<?= number_format((float) $quote['total'], 2); ?>
                                    </h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
