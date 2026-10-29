<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';

requiereLogin();

$sitio = ($_GET['sitio'] ?? 'alquiler') === 'venta' ? 'venta' : 'alquiler';

$stmt = db()->prepare('SELECT * FROM inmuebles WHERE sitio = ? ORDER BY orden, id');
$stmt->execute([$sitio]);
$inmuebles = $stmt->fetchAll();
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Panel — Inmuebles</title>
    <link rel="stylesheet" href="assets/panel.css">
</head>
<body>
    <?php include __DIR__ . '/partials/nav.php'; ?>
    <main class="contenedor">
        <?php mostrar_flash(); ?>

        <div class="panel-acciones">
            <h2>Inmuebles</h2>
            <a class="btn" href="inmuebles-editar.php?sitio=<?= e($sitio) ?>">+ Nuevo inmueble</a>
        </div>

        <div class="panel-card" style="margin-bottom: 12px;">
            <div class="fila-botones">
                <a class="btn <?= $sitio === 'alquiler' ? '' : 'btn-secundario' ?> btn-chico" href="inmuebles.php?sitio=alquiler">Alquileres</a>
                <a class="btn <?= $sitio === 'venta' ? '' : 'btn-secundario' ?> btn-chico" href="inmuebles.php?sitio=venta">Ventas</a>
            </div>
        </div>

        <div class="panel-card">
            <p class="ayuda" style="margin: 0 0 14px;">
                La carpeta del inmueble y su <code>info_unidad.json</code> se cargan en el repo del sitio
                (<code>src/assets/inmo/&lt;carpeta&gt;/</code>). Acá se administra lo que se muestra en los listados.
            </p>

            <?php if (!$inmuebles): ?>
                <p>Sin inmuebles cargados.</p>
            <?php else: ?>
            <table class="tabla">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Categoría</th>
                        <th>Camas / Baños</th>
                        <th>Imagen</th>
                        <th>Dirección</th>
                        <th>Estado</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($inmuebles as $i => $inm): ?>
                    <tr>
                        <td><?= e($inm['nombre']) ?></td>
                        <td><?= e($inm['categoria']) ?></td>
                        <td><?= (int) $inm['camas'] ?> / <?= (int) $inm['banios'] ?></td>
                        <td><span class="ayuda"><?= e($inm['carpeta'] . '/' . $inm['imagen']) ?></span></td>
                        <td><?= e($inm['direccion']) ?></td>
                        <td>
                            <?php if ($inm['activo']): ?>
                                <span class="pill pill-ok">Visible</span>
                            <?php else: ?>
                                <span class="pill pill-no">Oculta</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="fila-botones">
                                <form method="post" action="inmuebles-accion.php">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="accion" value="orden">
                                    <input type="hidden" name="id" value="<?= (int) $inm['id'] ?>">
                                    <input type="hidden" name="sitio" value="<?= e($sitio) ?>">
                                    <input type="hidden" name="dir" value="-1">
                                    <button class="btn btn-secundario btn-chico" type="submit" <?= $i === 0 ? 'disabled' : '' ?>>&uarr;</button>
                                </form>
                                <form method="post" action="inmuebles-accion.php">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="accion" value="orden">
                                    <input type="hidden" name="id" value="<?= (int) $inm['id'] ?>">
                                    <input type="hidden" name="sitio" value="<?= e($sitio) ?>">
                                    <input type="hidden" name="dir" value="1">
                                    <button class="btn btn-secundario btn-chico" type="submit">&darr;</button>
                                </form>
                                <a class="btn btn-chico" href="inmuebles-editar.php?id=<?= (int) $inm['id'] ?>&sitio=<?= e($sitio) ?>">Editar</a>
                                <form method="post" action="inmuebles-accion.php" onsubmit="return confirm('¿Eliminar este inmueble?');">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="accion" value="eliminar">
                                    <input type="hidden" name="id" value="<?= (int) $inm['id'] ?>">
                                    <input type="hidden" name="sitio" value="<?= e($sitio) ?>">
                                    <button class="btn btn-peligro btn-chico" type="submit">Borrar</button>
                                </form>
                                <form method="post" action="inmuebles-accion.php">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="accion" value="<?= $inm['activo'] ? 'desactivar' : 'activar' ?>">
                                    <input type="hidden" name="id" value="<?= (int) $inm['id'] ?>">
                                    <input type="hidden" name="sitio" value="<?= e($sitio) ?>">
                                    <button class="btn-inline" type="submit"><?= $inm['activo'] ? 'Ocultar' : 'Mostrar' ?></button>
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
