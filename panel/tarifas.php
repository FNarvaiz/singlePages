<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';

requiereLogin();

$promociones = db()->query(
    'SELECT p.id, p.titulo, p.desde, p.hasta, p.activo, p.orden,
            COUNT(pr.id) AS cantidad_promos
     FROM tarifas_promociones p
     LEFT JOIN tarifas_promos pr ON pr.promocion_id = p.id
     GROUP BY p.id, p.titulo, p.desde, p.hasta, p.activo, p.orden
     ORDER BY p.orden, p.id'
)->fetchAll();
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Panel — Tarifas y promociones</title>
    <link rel="stylesheet" href="assets/panel.css">
</head>
<body>
    <?php include __DIR__ . '/partials/nav.php'; ?>
    <main class="contenedor">
        <?php mostrar_flash(); ?>

        <div class="panel-acciones">
            <h2>Tarifas y promociones</h2>
            <a class="btn" href="tarifas-editar.php">+ Nueva promoción</a>
        </div>

        <div class="panel-card">
            <?php if (!$promociones): ?>
                <p>Todavía no hay promociones. Creá la primera con el botón de arriba.</p>
            <?php else: ?>
            <table class="tabla">
                <thead>
                    <tr>
                        <th>Título</th>
                        <th>Desde</th>
                        <th>Hasta</th>
                        <th>Promos</th>
                        <th>Estado</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($promociones as $p): ?>
                    <tr>
                        <td><?= e($p['titulo']) ?></td>
                        <td><?= e(fecha_legible($p['desde'])) ?></td>
                        <td><?= e(fecha_legible($p['hasta'])) ?></td>
                        <td><?= (int) $p['cantidad_promos'] ?></td>
                        <td>
                            <?php if ($p['activo']): ?>
                                <span class="pill pill-ok">Visible</span>
                            <?php else: ?>
                                <span class="pill pill-no">Oculta</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="fila-botones">
                                <form method="post" action="tarifas-accion.php">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="accion" value="orden">
                                    <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                                    <input type="hidden" name="dir" value="-1">
                                    <button class="btn btn-secundario btn-chico" type="submit" <?= (int) $p['orden'] === 0 ? 'disabled' : '' ?>>&uarr;</button>
                                </form>
                                <form method="post" action="tarifas-accion.php">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="accion" value="orden">
                                    <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                                    <input type="hidden" name="dir" value="1">
                                    <button class="btn btn-secundario btn-chico" type="submit">&darr;</button>
                                </form>
                                <a class="btn btn-chico" href="tarifas-editar.php?id=<?= (int) $p['id'] ?>">Editar</a>
                                <form method="post" action="tarifas-accion.php" onsubmit="return confirm('¿Eliminar esta promoción y sus precios?');">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="accion" value="eliminar">
                                    <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                                    <button class="btn btn-peligro btn-chico" type="submit">Borrar</button>
                                </form>
                                <form method="post" action="tarifas-accion.php">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="accion" value="<?= $p['activo'] ? 'desactivar' : 'activar' ?>">
                                    <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                                    <button class="btn-inline" type="submit"><?= $p['activo'] ? 'Ocultar' : 'Mostrar' ?></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>
