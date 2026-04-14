<?php
require_once '../app/models/Service.php';

$services = Service::getAll();
$isLoggedIn = isset($_SESSION['user_id']);
$userNombre = $_SESSION['user_nombre'] ?? 'Invitado';
$userRol = $_SESSION['user_rol'] ?? '';
$isAdmin = $userRol === 'admin';
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

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container py-2">
            <a class="navbar-brand fw-bold" href="index.php?page=home">Cotizador MVC</a>
            <div class="d-flex align-items-center gap-2 flex-wrap justify-content-end">
                <?php if ($isLoggedIn): ?>
                    <span class="text-white">Hola, <strong><?= htmlspecialchars($userNombre) ?></strong></span>
                    <?php if ($isAdmin): ?>
                        <span class="badge text-bg-warning">Administrador</span>
                    <?php endif; ?>
                    <a href="index.php?page=quotes" class="btn btn-outline-light btn-sm">Ver Cotizaciones</a>
                    <button class="btn btn-danger btn-sm" id="btn-logout">Cerrar Sesión</button>
                <?php else: ?>
                    <a href="index.php?page=login" class="btn btn-outline-light btn-sm">Iniciar Sesión</a>
                    <a href="index.php?page=register" class="btn btn-primary btn-sm">Registrarme</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <section class="bg-white border-bottom">
        <div class="container py-4">
            <div class="row align-items-center g-3">
                <div class="col-lg-8">
                    <h1 class="h3 mb-2">Catalogo de Servicios</h1>
                    <p class="text-muted mb-0">
                        Explora mas de 12 servicios organizados en distintas categorias, agrega los que necesites al
                        carrito y genera tu cotizacion con calculos automaticos.
                    </p>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <?php if ($isAdmin): ?>
                        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#serviceModal">
                            Nuevo servicio
                        </button>
                    <?php else: ?>
                        <span class="badge text-bg-secondary p-2">3 categorías disponibles</span>
                    <?php endif; ?>
                </div>
            </div>

            <div id="catalog-feedback" class="alert d-none mt-3 mb-0" role="alert"></div>
        </div>
    </section>

    <div class="container py-4">
        <div class="row g-4">
            <div class="<?= $isLoggedIn ? 'col-lg-8' : 'col-12' ?>">
                <div class="row g-4">
                    <?php foreach ($services as $service): ?>
                        <div class="col-md-6">
                            <article class="card h-100 shadow-sm border-0 service-card">
                                <div class="card-body d-flex flex-column">
                                    <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                        <span class="badge rounded-pill text-bg-light border">
                                            <?= htmlspecialchars($service->getCategoria()); ?>
                                        </span>
                                        <strong class="text-primary">$<?= number_format($service->getPrecio(), 2); ?></strong>
                                    </div>

                                    <h2 class="h5 card-title mb-2"><?= htmlspecialchars($service->getNombre()); ?></h2>
                                    <p class="card-text text-muted flex-grow-1">
                                        <?= htmlspecialchars($service->getDescripcion()); ?>
                                    </p>

                                    <div class="d-flex flex-wrap gap-2 mt-3">
                                        <?php if ($isLoggedIn): ?>
                                            <button class="btn btn-primary flex-grow-1 add-to-cart"
                                                data-id="<?= $service->getId(); ?>">
                                                Agregar al carrito
                                            </button>
                                            <?php if ($isAdmin): ?>
                                                <button class="btn btn-outline-danger delete-service"
                                                    data-id="<?= $service->getId(); ?>"
                                                    data-name="<?= htmlspecialchars($service->getNombre()); ?>">
                                                    Eliminar
                                                </button>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <a href="index.php?page=login" class="btn btn-outline-primary w-100">
                                                Inicia sesión para cotizar
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </article>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <?php if ($isLoggedIn): ?>
                <div class="col-lg-4">
                    <aside class="card shadow-sm border-0 sticky-top" style="top: 20px;">
                        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                            <span>Mi CotizaciÃ³n</span>
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
                    </aside>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($isLoggedIn): ?>
        <div class="modal fade" id="quoteModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">Datos del Cliente</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div id="modal-summary" class="mb-4 p-3 bg-light rounded border"></div>

                        <div id="quote-alert" class="alert d-none" role="alert"></div>

                        <form id="quote-form" novalidate>
                            <div class="mb-3">
                                <label class="form-label text-muted small mb-1">Nombre Completo <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="nombre" id="quote-nombre"
                                    value="<?= htmlspecialchars($userNombre) ?>" required>
                                <small class="text-danger d-none" data-error-for="quote-nombre"></small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted small mb-1">Empresa <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="empresa" id="quote-empresa"
                                    placeholder="Nombre de la empresa" required>
                                <small class="text-danger d-none" data-error-for="quote-empresa"></small>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label text-muted small mb-1">Correo <span
                                            class="text-danger">*</span></label>
                                    <input type="email" class="form-control" name="email" id="quote-email"
                                        placeholder="correo@empresa.com" required>
                                    <small class="text-danger d-none" data-error-for="quote-email"></small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label text-muted small mb-1">Telefono <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="telefono" id="quote-telefono"
                                        placeholder="0000-0000" required>
                                    <small class="text-danger d-none" data-error-for="quote-telefono"></small>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-success w-100 py-2 fw-bold">Confirmar y Generar
                                Cotización</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="successModal" tabindex="-1" data-bs-backdrop="static" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content text-center">
                    <div class="modal-body p-5">
                        <div class="mb-4">
                            <i class="text-success" style="font-size: 4rem;">&#10003;</i>
                        </div>
                        <h4 class="mb-3">¡Cotización Generada!</h4>

                        <div class="card bg-light border-0 mb-4">
                            <div class="card-body">
                                <h2 class="text-primary fw-bold" id="confirm-codigo">COT-2026-0000</h2>
                                <hr>
                                <p class="mb-1"><strong>Generada:</strong> <span id="confirm-fecha">--</span></p>
                                <p class="mb-1 text-danger"><strong>VÃ¡lida hasta:</strong> <span
                                        id="confirm-validez">--</span></p>
                                <h4 class="mt-3 text-success fw-bold" id="confirm-total">$0.00</h4>
                            </div>
                        </div>

                        <button type="button" class="btn btn-primary px-4" data-bs-dismiss="modal">Entendido</button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($isAdmin): ?>
        <div class="modal fade" id="serviceModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title">Agregar Servicio</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div id="service-alert" class="alert d-none" role="alert"></div>

                        <form id="service-form" novalidate>
                            <div class="mb-3">
                                <label class="form-label">Nombre del servicio</label>
                                <input type="text" class="form-control" name="nombre" id="service-nombre" required>
                                <small class="text-danger d-none" data-error-for="service-nombre"></small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">DescripciÃ³n</label>
                                <textarea class="form-control" name="descripcion" id="service-descripcion" rows="3"
                                    required></textarea>
                                <small class="text-danger d-none" data-error-for="service-descripcion"></small>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Precio base</label>
                                    <input type="number" step="0.01" min="100" max="10000" class="form-control"
                                        name="precio" id="service-precio" required>
                                    <small class="text-danger d-none" data-error-for="service-precio"></small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Categoría</label>
                                    <select class="form-select" name="categoria" id="service-categoria" required>
                                        <option value="">Selecciona una categoría</option>
                                        <?php foreach (Service::CATEGORIAS_VALIDAS as $categoria): ?>
                                            <option value="<?= htmlspecialchars($categoria); ?>">
                                                <?= htmlspecialchars($categoria); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <small class="text-danger d-none" data-error-for="service-categoria"></small>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-success w-100" id="btn-save-service">
                                Guardar servicio
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <script>
        window.appConfig = {
            isAdmin: <?= $isAdmin ? 'true' : 'false' ?>,
            isLoggedIn: <?= $isLoggedIn ? 'true' : 'false' ?>
        };
    </script>
    <script src="assets/js/services-catalog.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
