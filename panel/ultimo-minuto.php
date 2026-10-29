<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';

requiereLogin();

$sitio = sitio_um_validar((string) ($_GET['sitio'] ?? 'hotel'));

$stmt = db()->prepare('SELECT id, tipo, check_in, check_out, precio, info, activo, orden
     FROM ultimo_minuto
     WHERE sitio = ?
     ORDER BY orden, id');
$stmt->execute([$sitio]);
$ofertas = $stmt->fetchAll();
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Panel — Último minuto (<?= $sitio === 'hotel' ? 'Hotel' : 'Inmobiliaria' ?>)</title>
    <link rel="stylesheet" href="assets/panel.css">
</head>
<body>
    <?php include __DIR__ . '/partials/nav.php'; ?>
    <main class="contenedor">
        <?php mostrar_flash(); ?>

        <div class="panel-acciones">
            <h2>Último minuto — <?= $sitio === 'hotel' ? 'Hotel' : 'Inmobiliaria' ?></h2>
            <a class="btn" href="ultimo-minuto-editar.php?sitio=<?= e($sitio) ?>">+ Nueva oferta</a>
        </div>

        <div class="panel-card" style="margin-bottom: 12px;">
            <div class="fila-botones">
                <a class="btn <?= $sitio === 'hotel' ? '' : 'btn-secundario' ?> btn-chico" href="ultimo-minuto.php?sitio=hotel">Hotel</a>
                <a class="btn <?= $sitio === 'inmobiliaria' ? '' : 'btn-secundario' ?> btn-chico" href="ultimo-minuto.php?sitio=inmobiliaria">Inmobiliaria</a>
            </div>
        </div>

        <div class="panel-card">
            <?php if (!$ofertas): ?>
                <p>Todavía no hay ofertas de último minuto.</p>
            <?php else: ?>
            <table class="tabla">
                <thead>
                    <tr>
                        <th>Tipo</th>
                        <th>Check-in</th>
                        <th>Check-out</th>
                        <th>Precio</th>
                        <th>Info</th>
                        <th>Estado</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ofertas as $o): ?>
                    <tr>
                        <td><?= e($o['tipo']) ?></td>
                        <td><?= e(fecha_legible($o['check_in'])) ?></td>
                        <td><?= e(fecha_legible($o['check_out'])) ?></td>
                        <td>$ <?= number_format((int) $o['precio'], 0, ',', '.') ?></td>
                        <td><?= e($o['info'] ?? '') ?></td>
                        <td>
                            <?php if ($o['activo']): ?>
                                <span class="pill pill-ok">Visible</span>
                            <?php else: ?>
                                <span class="pill pill-no">Oculta</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="fila-botones">
                                <form method="post" action="ultimo-minuto-accion.php">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="accion" value="orden">
                                    <input type="hidden" name="id" value="<?= (int) $o['id'] ?>">
                                    <input type="hidden" name="sitio" value="<?= e($sitio) ?>">
                                    <input type="hidden" name="dir" value="-1">
                                    <button class="btn btn-secundario btn-chico" type="submit" <?= (int) $o['orden'] === 0 ? 'disabled' : '' ?>>&uarr;</button>
                                </form>
                                <form method="post" action="ultimo-minuto-accion.php">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="accion" value="orden">
                                    <input type="hidden" name="id" value="<?= (int) $o['id'] ?>">
                                    <input type="hidden" name="sitio" value="<?= e($sitio) ?>">
                                    <input type="hidden" name="dir" value="1">
                                    <button class="btn btn-secundario btn-chico" type="submit">&darr;</button>
                                </form>
                                <a class="btn btn-chico" href="ultimo-minuto-editar.php?id=<?= (int) $o['id'] ?>&sitio=<?= e($sitio) ?>">Editar</a>
                                <form method="post" action="ultimo-minuto-accion.php" onsubmit="return confirm('¿Eliminar esta oferta?');">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="accion" value="eliminar">
                                    <input type="hidden" name="id" value="<?= (int) $o['id'] ?>">
                                    <input type="hidden" name="sitio" value="<?= e($sitio) ?>">
                                    <button class="btn btn-peligro btn-chico" type="submit">Borrar</button>
                                </form>
                                <form method="post" action="ultimo-minuto-accion.php">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="accion" value="<?= $o['activo'] ? 'desactivar' : 'activar' ?>">
                                    <input type="hidden" name="id" value="<?= (int) $o['id'] ?>">
                                    <input type="hidden" name="sitio" value="<?= e($sitio) ?>">
                                    <button class="btn-inline" type="submit"><?= $o['activo'] ? 'Ocultar' : 'Mostrar' ?></button>
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
