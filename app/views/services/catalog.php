<?php
// Ubicación: /app/views/services/catalog.php

// 1. Cargamos el nuevo modelo (La ruta funciona porque este archivo es llamado desde public/index.php)
require_once '../app/models/Service.php';

// 2. Traemos todos los servicios directamente de la Base de Datos
$services = Service::getAll();

// 3. Verificamos si hay alguien logueado
$isLoggedIn = isset($_SESSION['user_id']);
$userNombre = $_SESSION['user_nombre'] ?? 'Invitado';
$userRol = $_SESSION['user_rol'] ?? '';
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Catálogo de Servicios</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/services-catalog.css">
</head>

<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="index.php?page=home">Cotizador MVC</a>
            <div class="d-flex align-items-center">
                <?php if ($isLoggedIn): ?>
                    <span class="text-white me-3">Hola, <strong><?= htmlspecialchars($userNombre) ?></strong></span>
                    <a href="index.php?page=quotes" class="btn btn-outline-light me-2">Mis Cotizaciones</a>
                    <button class="btn btn-danger" id="btn-logout">Cerrar Sesión</button>
                <?php else: ?>
                    <a href="index.php?page=login" class="btn btn-primary">Iniciar Sesión</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="container my-5">
        <div class="row">

            <div class="col-md-8">
                <h2 class="mb-4">Catálogo de Servicios</h2>
                <div class="row">
                    <?php foreach ($services as $service): ?>
                        <div class="col-md-6 mb-4">
                            <div class="card shadow-sm h-100">
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title"><?= htmlspecialchars($service->getNombre()); ?></h5>
                                    <p class="card-text"><?= htmlspecialchars($service->getDescripcion()); ?></p>
                                    <p class="fw-bold text-primary">$<?= number_format($service->getPrecio(), 2); ?></p>
                                    <p class="text-muted small"><?= htmlspecialchars($service->getCategoria()); ?></p>

                                    <?php if ($isLoggedIn): ?>
                                        <button class="btn btn-primary mt-auto add-to-cart" data-id="<?= $service->getId(); ?>">
                                            Agregar al carrito
                                        </button>
                                    <?php else: ?>
                                        <div class="alert alert-warning mt-auto mb-0 text-center py-2">
                                            <small>Inicia sesión para cotizar</small>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <?php if ($isLoggedIn): ?>
                <div class="col-md-4">
                    <div class="card shadow-sm position-sticky" style="top: 20px;">
                        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                            <span>Mi Cotización</span>
                            <span class="badge bg-warning text-dark" id="cart-count">0</span>
                        </div>
                        <div class="card-body">
                            <div id="cart-items">
                                <p class="text-muted text-center my-3">Cargando carrito...</p>
                            </div>
                            <hr>
                            <button class="btn btn-success w-100 mt-2" id="generate-quote">Generar Cotización</button>
                            <button class="btn btn-outline-danger w-100 mt-2" id="clear-cart">Vaciar Carrito</button>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

        </div>
    </div>

    <div class="modal fade" id="quoteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Datos del Cliente</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">

                    <div id="modal-summary" class="mb-4 p-3 bg-light rounded border"></div>

                    <form id="quote-form">
                        <div class="mb-3">
                            <label class="form-label text-muted small mb-1">Nombre Completo <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="nombre"
                                value="<?= htmlspecialchars($userNombre) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted small mb-1">Empresa <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="empresa" placeholder="Nombre de la empresa"
                                required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted small mb-1">Correo <span
                                        class="text-danger">*</span></label>
                                <input type="email" class="form-control" name="email" placeholder="correo@empresa.com"
                                    required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted small mb-1">Teléfono <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="telefono" placeholder="0000-0000"
                                    required>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-success w-100 py-2 fw-bold">Confirmar y Generar
                            Cotización</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="successModal" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content text-center">
                <div class="modal-body p-5">
                    <div class="mb-4">
                        <i class="text-success" style="font-size: 4rem;">✓</i>
                    </div>
                    <h4 class="mb-3">¡Cotización Generada!</h4>

                    <div class="card bg-light border-0 mb-4">
                        <div class="card-body">
                            <h2 class="text-primary fw-bold" id="confirm-codigo">COT-2026-0000</h2>
                            <hr>
                            <p class="mb-1"><strong>Generada:</strong> <span id="confirm-fecha">--</span></p>
                            <p class="mb-1 text-danger"><strong>Válida hasta:</strong> <span
                                    id="confirm-validez">--</span></p>
                            <h4 class="mt-3 text-success fw-bold" id="confirm-total">$0.00</h4>
                        </div>
                    </div>

                    <button type="button" class="btn btn-primary px-4" data-bs-dismiss="modal">Entendido</button>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/services-catalog.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>