<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';

requiereLogin();

$sitio = ($_GET['sitio'] ?? 'lugra') === 'inmobiliaria' ? 'inmobiliaria' : 'lugra';

$stmt = db()->prepare('SELECT * FROM banners WHERE sitio = ? ORDER BY dispositivo, orden, id');
$stmt->execute([$sitio]);
$banners = $stmt->fetchAll();

$porDispositivo = ['escritorio' => [], 'movil' => []];
foreach ($banners as $b) {
    $porDispositivo[$b['dispositivo']][] = $b;
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Panel — Banners</title>
    <link rel="stylesheet" href="assets/panel.css">
</head>
<body>
    <?php include __DIR__ . '/partials/nav.php'; ?>
    <main class="contenedor">
        <?php mostrar_flash(); ?>

        <div class="panel-acciones">
            <h2>Banners</h2>
            <a class="btn" href="banners-editar.php?sitio=<?= e($sitio) ?>">+ Nuevo banner</a>
        </div>

        <div class="panel-card" style="margin-bottom: 12px;">
            <div class="fila-botones">
                <a class="btn <?= $sitio === 'lugra' ? '' : 'btn-secundario' ?> btn-chico" href="banners.php?sitio=lugra">Hotel Lugra</a>
                <a class="btn <?= $sitio === 'inmobiliaria' ? '' : 'btn-secundario' ?> btn-chico" href="banners.php?sitio=inmobiliaria">Inmobiliaria</a>
            </div>
        </div>

        <?php foreach (['escritorio', 'movil'] as $dispositivo): ?>
        <div class="panel-card">
            <h3><?= $dispositivo === 'escritorio' ? 'Escritorio' : 'Móvil' ?></h3>
            <?php if (!$porDispositivo[$dispositivo]): ?>
                <p>Sin banners.</p>
            <?php else: ?>
            <table class="tabla">
                <thead>
                    <tr>
                        <th>Imagen</th>
                        <th>Alt</th>
                        <th>Link</th>
                        <th>Estado</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($porDispositivo[$dispositivo] as $i => $b): ?>
                    <tr>
                        <td>
                            <?php if (preg_match('#^https?://#i', $b['ruta'])): ?>
                                <img src="<?= e($b['ruta']) ?>" alt="" style="width: 90px; border-radius: 6px;">
                            <?php else: ?>
                                <span class="ayuda"><?= e($b['ruta']) ?></span>
                            <?php endif; ?>
                        </td>
                        <td><?= e($b['alt'] ?? '') ?></td>
                        <td><?= e($b['url'] ?? '') ?></td>
                        <td>
                            <?php if ($b['activo']): ?>
                                <span class="pill pill-ok">Visible</span>
                            <?php else: ?>
                                <span class="pill pill-no">Oculta</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="fila-botones">
                                <form method="post" action="banners-accion.php">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="accion" value="orden">
                                    <input type="hidden" name="id" value="<?= (int) $b['id'] ?>">
                                    <input type="hidden" name="sitio" value="<?= e($sitio) ?>">
                                    <input type="hidden" name="dir" value="-1">
                                    <button class="btn btn-secundario btn-chico" type="submit" <?= $i === 0 ? 'disabled' : '' ?>>&uarr;</button>
                                </form>
                                <form method="post" action="banners-accion.php">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="accion" value="orden">
                                    <input type="hidden" name="id" value="<?= (int) $b['id'] ?>">
                                    <input type="hidden" name="sitio" value="<?= e($sitio) ?>">
                                    <input type="hidden" name="dir" value="1">
                                    <button class="btn btn-secundario btn-chico" type="submit">&darr;</button>
                                </form>
                                <a class="btn btn-chico" href="banners-editar.php?id=<?= (int) $b['id'] ?>&sitio=<?= e($sitio) ?>">Editar</a>
                                <form method="post" action="banners-accion.php" onsubmit="return confirm('¿Eliminar este banner?');">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="accion" value="eliminar">
                                    <input type="hidden" name="id" value="<?= (int) $b['id'] ?>">
                                    <input type="hidden" name="sitio" value="<?= e($sitio) ?>">
                                    <button class="btn btn-peligro btn-chico" type="submit">Borrar</button>
                                </form>
                                <form method="post" action="banners-accion.php">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="accion" value="<?= $b['activo'] ? 'desactivar' : 'activar' ?>">
                                    <input type="hidden" name="id" value="<?= (int) $b['id'] ?>">
                                    <input type="hidden" name="sitio" value="<?= e($sitio) ?>">
                                    <button class="btn-inline" type="submit"><?= $b['activo'] ? 'Ocultar' : 'Mostrar' ?></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </main>
</body>
</html>
