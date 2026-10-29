<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';

requiereLogin();

$unidades = db()->query(
    'SELECT u.id, u.carpeta, u.nombre, u.categoria, u.camas, u.banios, u.venta, u.activo,
            (SELECT COUNT(*) FROM unidad_imagenes i WHERE i.unidad_id = u.id) AS cantidad_imagenes,
            (SELECT COUNT(*) FROM unidad_tarifas t WHERE t.unidad_id = u.id) AS cantidad_tarifas
     FROM unidades u
     ORDER BY u.carpeta'
)->fetchAll();
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Panel — Unidades</title>
    <link rel="stylesheet" href="assets/panel.css">
</head>
<body>
    <?php include __DIR__ . '/partials/nav.php'; ?>
    <main class="contenedor">
        <?php mostrar_flash(); ?>

        <div class="panel-acciones">
            <h2>Unidades (detalle de propiedad)</h2>
            <a class="btn" href="unidad-editar.php">+ Nueva unidad</a>
        </div>

        <div class="panel-card">
            <p class="ayuda" style="margin: 0 0 14px;">
                Cada unidad corresponde a una carpeta de <code>src/assets/inmo/</code> con su
                <code>info_unidad.json</code>. Acá se editan descripción, fotos, tarifas y si es venta.
                Las fotos pueden ser archivos de la carpeta o imágenes subidas al panel (URL).
            </p>

            <?php if (!$unidades): ?>
                <p>Sin unidades cargadas.</p>
            <?php else: ?>
            <table class="tabla">
                <thead>
                    <tr>
                        <th>Carpeta</th>
                        <th>Nombre</th>
                        <th>Categoría</th>
                        <th>Camas / Baños</th>
                        <th>Fotos</th>
                        <th>Tarifas</th>
                        <th>Tipo</th>
                        <th>Estado</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($unidades as $u): ?>
                    <tr>
                        <td><span class="ayuda"><?= e($u['carpeta']) ?></span></td>
                        <td><?= e($u['nombre']) ?></td>
                        <td><?= e($u['categoria']) ?></td>
                        <td><?= (int) $u['camas'] ?> / <?= (int) $u['banios'] ?></td>
                        <td><?= (int) $u['cantidad_imagenes'] ?></td>
                        <td><?= (int) $u['cantidad_tarifas'] ?></td>
                        <td><?= $u['venta'] ? 'Venta' : 'Alquiler' ?></td>
                        <td>
                            <?php if ($u['activo']): ?>
                                <span class="pill pill-ok">Visible</span>
                            <?php else: ?>
                                <span class="pill pill-no">Oculta</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="fila-botones">
                                <a class="btn btn-chico" href="unidad-editar.php?id=<?= (int) $u['id'] ?>">Editar</a>
                                <form method="post" action="unidad-accion.php" onsubmit="return confirm('¿Eliminar el detalle de esta unidad? (no borra la carpeta del sitio)');">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="accion" value="eliminar">
                                    <input type="hidden" name="id" value="<?= (int) $u['id'] ?>">
                                    <button class="btn btn-peligro btn-chico" type="submit">Borrar</button>
                                </form>
                                <form method="post" action="unidad-accion.php">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="accion" value="<?= $u['activo'] ? 'desactivar' : 'activar' ?>">
                                    <input type="hidden" name="id" value="<?= (int) $u['id'] ?>">
                                    <button class="btn-inline" type="submit"><?= $u['activo'] ? 'Ocultar' : 'Mostrar' ?></button>
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
