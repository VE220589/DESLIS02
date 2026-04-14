<?php
if (isset($_SESSION['user_id'])) {
    header("Location: index.php?page=services");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Registro - Cotizador Digital</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/services-catalog.css">
</head>

<body class="bg-light d-flex align-items-center justify-content-center" style="min-height: 100vh;">

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">

                <div class="text-center mb-4">
                    <h2 class="fw-bold">Crear cuenta</h2>
                    <p class="text-muted">Regístrate para generar y consultar tus cotizaciones</p>
                </div>

                <div class="form-card p-4 shadow">
                    <div id="register-alert" class="alert d-none" role="alert"></div>

                    <form id="register-form" novalidate>
                        <div class="mb-3 form-group">
                            <label for="nombre" class="form-label">Nombre completo <span class="required">*</span></label>
                            <input type="text" class="form-control form-input" id="nombre" name="nombre" required
                                minlength="3" placeholder="Tu nombre completo">
                            <small class="text-danger d-none" data-error-for="nombre"></small>
                        </div>

                        <div class="mb-3 form-group">
                            <label for="email" class="form-label">Correo electrónico <span class="required">*</span></label>
                            <input type="email" class="form-control form-input" id="email" name="email" required
                                placeholder="ejemplo@correo.com">
                            <small class="text-danger d-none" data-error-for="email"></small>
                        </div>

                        <div class="mb-3 form-group">
                            <label for="password" class="form-label">Contraseña <span class="required">*</span></label>
                            <input type="password" class="form-control form-input" id="password" name="password" required
                                minlength="8" placeholder="Minimo 8 caracteres">
                            <small class="text-danger d-none" data-error-for="password"></small>
                        </div>

                        <div class="mb-4 form-group">
                            <label for="confirm_password" class="form-label">Confirmar contraseña <span
                                    class="required">*</span></label>
                            <input type="password" class="form-control form-input" id="confirm_password"
                                name="confirm_password" required placeholder="Repite tu contraseña">
                            <small class="text-danger d-none" data-error-for="confirm_password"></small>
                        </div>

                        <button type="submit" class="btn btn-primary w-100" id="btn-register">
                            Crear cuenta
                        </button>
                    </form>

                    <div class="text-center mt-4">
                        <p class="mb-2 text-muted">
                            ¿Ya tienes cuenta?
                            <a href="index.php?page=login" class="text-decoration-none">Inicia sesión</a>
                        </p>
                        <a href="index.php?page=home" class="text-decoration-none text-muted">Volver a la páguina
                            principal</a>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script src="assets/js/register.js"></script>
</body>

</html>
