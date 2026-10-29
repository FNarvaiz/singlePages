<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';

requiereLogin();

$agencias = db()->query('SELECT * FROM promos_agencias ORDER BY orden, id')->fetchAll();
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Panel — Promociones de agencias</title>
    <link rel="stylesheet" href="assets/panel.css">
</head>
<body>
    <?php include __DIR__ . '/partials/nav.php'; ?>
    <main class="contenedor">
        <?php mostrar_flash(); ?>

        <div class="panel-acciones">
            <h2>Promociones de agencias</h2>
            <a class="btn" href="promos-agencias-editar.php">+ Nueva agencia</a>
        </div>

        <div class="panel-card">
            <?php if (!$agencias): ?>
                <p>Sin promociones de agencias cargadas.</p>
            <?php else: ?>
            <table class="tabla">
                <thead>
                    <tr>
                        <th>Imagen</th>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th>Link</th>
                        <th>Estado</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($agencias as $i => $a): ?>
                    <tr>
                        <td>
                            <?php if (preg_match('#^https?://#i', $a['imagen'])): ?>
                                <img src="<?= e($a['imagen']) ?>" alt="" style="width: 70px; border-radius: 6px;">
                            <?php else: ?>
                                <span class="ayuda"><?= e($a['imagen']) ?></span>
                            <?php endif; ?>
                        </td>
                        <td><?= e($a['nombre']) ?></td>
                        <td><?= e($a['descripcion'] ?? '') ?></td>
                        <td><?= e($a['link'] ?? '') ?></td>
                        <td>
                            <?php if ($a['activo']): ?>
                                <span class="pill pill-ok">Visible</span>
                            <?php else: ?>
                                <span class="pill pill-no">Oculta</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="fila-botones">
                                <form method="post" action="promos-agencias-accion.php">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="accion" value="orden">
                                    <input type="hidden" name="id" value="<?= (int) $a['id'] ?>">
                                    <input type="hidden" name="dir" value="-1">
                                    <button class="btn btn-secundario btn-chico" type="submit" <?= $i === 0 ? 'disabled' : '' ?>>&uarr;</button>
                                </form>
                                <form method="post" action="promos-agencias-accion.php">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="accion" value="orden">
                                    <input type="hidden" name="id" value="<?= (int) $a['id'] ?>">
                                    <input type="hidden" name="dir" value="1">
                                    <button class="btn btn-secundario btn-chico" type="submit">&darr;</button>
                                </form>
                                <a class="btn btn-chico" href="promos-agencias-editar.php?id=<?= (int) $a['id'] ?>">Editar</a>
                                <form method="post" action="promos-agencias-accion.php" onsubmit="return confirm('¿Eliminar esta agencia?');">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="accion" value="eliminar">
                                    <input type="hidden" name="id" value="<?= (int) $a['id'] ?>">
                                    <button class="btn btn-peligro btn-chico" type="submit">Borrar</button>
                                </form>
                                <form method="post" action="promos-agencias-accion.php">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="accion" value="<?= $a['activo'] ? 'desactivar' : 'activar' ?>">
                                    <input type="hidden" name="id" value="<?= (int) $a['id'] ?>">
                                    <button class="btn-inline" type="submit"><?= $a['activo'] ? 'Ocultar' : 'Mostrar' ?></button>
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
