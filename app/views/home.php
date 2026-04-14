<?php
$isLoggedIn = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Sistema de Cotización Digital</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <!-- HERO -->
    <section class="bg-light py-5">
        <div class="container text-center">
            <h1 class="display-5 fw-bold">
                Sistema de Cotización de Servicios Digitales
            </h1>
            <p class="lead mt-3">
                Plataforma web desarrollada en PHP orientada a la gestión y generación
                automatizada de cotizaciones para servicios tecnológicos.
            </p>

            <div class="d-flex gap-2 justify-content-center flex-wrap mt-3">
                <a href="index.php?page=services" class="btn btn-primary btn-lg">
                    Explorar Servicios
                </a>
                <?php if ($isLoggedIn): ?>
                    <a href="index.php?page=quotes" class="btn btn-outline-dark btn-lg">
                        Ver Cotizaciones
                    </a>
                <?php else: ?>
                    <a href="index.php?page=login" class="btn btn-dark btn-lg">
                        Iniciar Sesión
                    </a>
                    <a href="index.php?page=register" class="btn btn-outline-primary btn-lg">
                        Crear Cuenta
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- SECCIÓN INFORMATIVA -->
    <section class="py-5">
        <div class="container">

            <div class="row text-center mb-4">
                <h2>¿Qué permite el sistema?</h2>
            </div>

            <div class="row text-center">

                <div class="col-md-4">
                    <div class="card shadow-sm p-3">
                        <h5>Gestión de Servicios</h5>
                        <p>Catálogo organizado por categorías con precios definidos.</p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card shadow-sm p-3">
                        <h5>Carrito Dinámico</h5>
                        <p>Selección de múltiples servicios con cálculo automático.</p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card shadow-sm p-3">
                        <h5>Generación de Cotizaciones</h5>
                        <p>Aplicación de descuentos, IVA y almacenamiento en historial.</p>
                    </div>
                </div>

            </div>

        </div>
    </section>

    <!-- CÓMO FUNCIONA -->
    <section class="bg-light py-5">
        <div class="container text-center">
            <h2>¿Cómo funciona?</h2>

            <div class="row mt-4">

                <div class="col-md-3">
                    <h4>1️⃣</h4>
                    <p>Selecciona los servicios.</p>
                </div>

                <div class="col-md-3">
                    <h4>2️⃣</h4>
                    <p>Agrega al carrito.</p>
                </div>

                <div class="col-md-3">
                    <h4>3️⃣</h4>
                    <p>Genera tu cotización.</p>
                </div>

                <div class="col-md-3">
                    <h4>4️⃣</h4>
                    <p>Consulta el historial.</p>
                </div>

            </div>

        </div>
    </section>

    <!-- FOOTER -->
    <footer class="bg-dark text-white text-center py-3">
        <p class="mb-0">
            DESAFÍO 1 LIS - Sistema de Cotización | PHP + Bootstrap + AJAX
        </p>
    </footer>

</body>

</html>
