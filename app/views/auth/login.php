<?php
// Si el usuario ya está logueado, lo mandamos al catálogo para que no vea el login de nuevo
if (isset($_SESSION['user_id'])) {
    header("Location: index.php?page=services");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Iniciar Sesión - Cotizador Digital</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/services-catalog.css">
</head>

<body class="bg-light d-flex align-items-center justify-content-center" style="min-height: 100vh;">

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">

                <div class="text-center mb-4">
                    <h2 class="fw-bold">Bienvenido</h2>
                    <p class="text-muted">Ingresa tus credenciales para continuar</p>
                </div>

                <div class="form-card p-4 shadow">

                    <div id="login-alert" class="alert d-none" role="alert"></div>

                    <form id="login-form">
                        <div class="mb-3 form-group">
                            <label for="email" class="form-label">Correo Electrónico <span
                                    class="required">*</span></label>
                            <input type="email" class="form-control form-input" id="email" name="email" required
                                placeholder="ejemplo@correo.com">
                        </div>

                        <div class="mb-4 form-group">
                            <label for="password" class="form-label">Contraseña <span class="required">*</span></label>
                            <input type="password" class="form-control form-input" id="password" name="password"
                                required placeholder="••••••••">
                        </div>

                        <button type="submit" class="btn btn-primary w-100" id="btn-login">
                            Iniciar Sesión
                        </button>
                    </form>

                    <div class="text-center mt-4">
                        <a href="index.php?page=home" class="text-decoration-none text-muted">Volver a la página
                            principal</a>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script src="assets/js/login.js"></script>
</body>

</html>