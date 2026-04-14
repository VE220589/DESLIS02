<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Acceso Denegado</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/services-catalog.css">
</head>

<body class="bg-light d-flex align-items-center" style="min-height: 100vh;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-7">
                <div class="card border-0 shadow-lg">
                    <div class="card-body p-5 text-center">
                        <span class="badge text-bg-warning mb-3 px-3 py-2">Error 403</span>
                        <h1 class="display-6 fw-bold mb-3">Acceso denegado</h1>
                        <p class="text-muted mb-4">
                            No tienes permisos suficientes para acceder a esta sección del sistema.
                        </p>
                        <div class="bg-light rounded p-3 mb-4">
                            <p class="mb-0">
                                <?= htmlspecialchars($errorMessage ?? 'Esta acción requiere permisos adicionales.'); ?>
                            </p>
                        </div>
                        <div class="d-flex gap-2 justify-content-center flex-wrap">
                            <a href="index.php?page=home" class="btn btn-primary">Volver al inicio</a>
                            <a href="index.php?page=services" class="btn btn-outline-primary">Ir al catálogo</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
