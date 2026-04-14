<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Página No Encontrada</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/services-catalog.css">
</head>

<body class="bg-light d-flex align-items-center" style="min-height: 100vh;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-7">
                <div class="card border-0 shadow-lg">
                    <div class="card-body p-5 text-center">
                        <span class="badge text-bg-danger mb-3 px-3 py-2">Error 404</span>
                        <h1 class="display-5 fw-bold mb-3">Ruta no encontrada</h1>
                        <p class="text-muted mb-4">
                            La página que intentaste abrir no existe o la dirección fue escrita incorrectamente.
                        </p>
                        <div class="bg-light rounded p-3 mb-4">
                            <p class="mb-1"><strong>Ruta solicitada:</strong></p>
                            <code><?= htmlspecialchars($_SERVER['REQUEST_URI'] ?? 'Desconocida'); ?></code>
                        </div>
                        <div class="d-flex gap-2 justify-content-center flex-wrap">
                            <a href="index.php?page=home" class="btn btn-primary">Ir al inicio</a>
                            <a href="index.php?page=services" class="btn btn-outline-primary">Ver catálogo</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
