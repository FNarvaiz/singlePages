<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';

requiereLogin();

$bloques = db()->query(
    'SELECT f.id, f.sitio, f.nombre, COUNT(p.id) AS cantidad_promos
     FROM formas_pago f
     LEFT JOIN formas_pago_promos p ON p.forma_pago_id = f.id
     GROUP BY f.id, f.sitio, f.nombre
     ORDER BY f.sitio'
)->fetchAll();
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Panel — Formas de pago</title>
    <link rel="stylesheet" href="assets/panel.css">
</head>
<body>
    <?php include __DIR__ . '/partials/nav.php'; ?>
    <main class="contenedor">
        <?php mostrar_flash(); ?>

        <div class="panel-acciones">
            <h2>Formas de pago</h2>
        </div>

        <div class="panel-card">
            <p class="ayuda" style="margin: 0 0 14px;">
                Hay un bloque por sitio: Hotel (tarifas del hotel) e Inmobiliaria (detalle de unidades).
            </p>
            <table class="tabla">
                <thead>
                    <tr>
                        <th>Sitio</th>
                        <th>Nombre del bloque</th>
                        <th>Ítems</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bloques as $b): ?>
                    <tr>
                        <td><?= $b['sitio'] === 'hotel' ? 'Hotel' : 'Inmobiliaria' ?></td>
                        <td><?= e($b['nombre']) ?></td>
                        <td><?= (int) $b['cantidad_promos'] ?></td>
                        <td>
                            <a class="btn btn-chico" href="formas-pago-editar.php?sitio=<?= e($b['sitio']) ?>">Editar</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>
