<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/auth.php';

requiereLogin();
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Panel — Inicio</title>
    <link rel="stylesheet" href="assets/panel.css">
</head>
<body>
    <?php include __DIR__ . '/partials/nav.php'; ?>
    <main class="contenedor">
        <?php mostrar_flash(); ?>
        <h2>Panel de control</h2>
        <div class="grid-tarjetas">
            <a class="tarjeta" href="tarifas.php">
                <h3>Tarifas y promociones</h3>
                <p>Temporadas, precios por habitación, servicios e íconos de pago.</p>
            </a>
            <a class="tarjeta" href="habitaciones.php">
                <h3>Habitaciones</h3>
                <p>Lista de habitaciones que aparecen como columnas en las tarifas.</p>
            </a>
            <a class="tarjeta" href="ultimo-minuto.php?sitio=hotel">
                <h3>Último minuto — Hotel</h3>
                <p>Ofertas imperdibles de la home del hotel.</p>
            </a>
            <a class="tarjeta" href="ultimo-minuto.php?sitio=inmobiliaria">
                <h3>Último minuto — Inmobiliaria</h3>
                <p>Ofertas imperdibles de la home de la inmobiliaria.</p>
            </a>
            <a class="tarjeta" href="banners.php?sitio=lugra">
                <h3>Banners</h3>
                <p>Sliders de la cabecera del hotel y la inmobiliaria (escritorio y móvil).</p>
            </a>
            <a class="tarjeta" href="promos-agencias.php">
                <h3>Promociones de agencias</h3>
                <p>Descuentos para socios (ACA, etc.) que se muestran en la home del hotel.</p>
            </a>
            <a class="tarjeta" href="formas-pago.php">
                <h3>Formas de pago</h3>
                <p>Bloques de pagos y descuentos para hotel e inmobiliaria.</p>
            </a>
            <a class="tarjeta" href="inmuebles.php?sitio=alquiler">
                <h3>Inmuebles</h3>
                <p>Listados de alquileres y ventas (nombres, categorías, ubicación).</p>
            </a>
            <a class="tarjeta" href="unidades.php">
                <h3>Unidades</h3>
                <p>Detalle de cada propiedad: descripción, fotos y tarifas por temporada.</p>
            </a>
        </div>
    </main>
</body>
</html>
