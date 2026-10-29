<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';

requiereLogin();

$pdo = db();

function habitacionesActuales(PDO $pdo): array
{
    return $pdo->query('SELECT id, nombre, orden FROM tarifas_habitaciones ORDER BY orden, id')->fetchAll();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $accion = (string) ($_POST['accion'] ?? '');

    if ($accion === 'agregar') {
        $nombre = trim((string) ($_POST['nombre'] ?? ''));
        if ($nombre === '') {
            flash('Ingresá el nombre de la habitación.', 'error');
        } else {
            $stmt = $pdo->prepare('INSERT INTO tarifas_habitaciones (nombre, orden) SELECT ?, COALESCE(MAX(orden) + 1, 0) FROM tarifas_habitaciones');
            $stmt->execute([$nombre]);
            flash('Habitación agregada.');
        }
    }

    if ($accion === 'renombrar') {
        $id = (int) ($_POST['id'] ?? 0);
        $nombre = trim((string) ($_POST['nombre'] ?? ''));
        if ($nombre === '') {
            flash('El nombre no puede quedar vacío.', 'error');
        } else {
            $stmt = $pdo->prepare('UPDATE tarifas_habitaciones SET nombre = ? WHERE id = ?');
            $stmt->execute([$nombre, $id]);
            flash('Habitación renombrada.');
        }
    }

    if ($accion === 'orden') {
        $id = (int) ($_POST['id'] ?? 0);
        $dir = (int) ($_POST['dir'] ?? 0);
        if ($dir !== 1 && $dir !== -1) {
            redirect('habitaciones.php');
        }

        $stmt = $pdo->prepare('SELECT id, orden FROM tarifas_habitaciones WHERE id = ?');
        $stmt->execute([$id]);
        $actual = $stmt->fetch();
        if (!$actual) {
            redirect('habitaciones.php');
        }

        $actualOrden = (int) $actual['orden'];
        $otroOrden = $actualOrden + $dir;

        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare('SELECT id FROM tarifas_habitaciones WHERE orden = ? AND id <> ? LIMIT 1');
            $stmt->execute([$otroOrden, $id]);
            $otro = $stmt->fetch();
            if ($otro) {
                $stmt = $pdo->prepare('UPDATE tarifas_habitaciones SET orden = ? WHERE id = ?');
                $stmt->execute([$actualOrden, (int) $otro['id']]);
            }
            $stmt = $pdo->prepare('UPDATE tarifas_habitaciones SET orden = ? WHERE id = ?');
            $stmt->execute([$otroOrden, $id]);
            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            flash('No se pudo reordenar.', 'error');
        }
    }

    if ($accion === 'eliminar') {
        $id = (int) ($_POST['id'] ?? 0);

        $stmt = $pdo->prepare('SELECT id, orden FROM tarifas_habitaciones WHERE id = ?');
        $stmt->execute([$id]);
        $hab = $stmt->fetch();
        if (!$hab) {
            redirect('habitaciones.php');
        }

        $columnas = $pdo->query('SELECT id, orden FROM tarifas_habitaciones ORDER BY orden, id')->fetchAll();
        $indiceColumna = null;
        foreach ($columnas as $i => $col) {
            if ((int) $col['id'] === (int) $hab['id']) {
                $indiceColumna = $i;
                break;
            }
        }

        $pdo->beginTransaction();
        try {
            if ($indiceColumna !== null) {
                // Podar la celda de esta columna en cada promo de cada promoción
                $promos = $pdo->query('SELECT id, precios FROM tarifas_promos')->fetchAll();
                $update = $pdo->prepare('UPDATE tarifas_promos SET precios = ? WHERE id = ?');
                foreach ($promos as $promo) {
                    $matriz = json_decode((string) $promo['precios'], true) ?: [];
                    if (isset($matriz[$indiceColumna])) {
                        array_splice($matriz, $indiceColumna, 1);
                        $update->execute([json_encode($matriz), (int) $promo['id']]);
                    }
                }
            }

            $stmt = $pdo->prepare('DELETE FROM tarifas_habitaciones WHERE id = ?');
            $stmt->execute([$id]);

            // Compactar órdenes
            $restantes = $pdo->query('SELECT id FROM tarifas_habitaciones ORDER BY orden, id')->fetchAll();
            $stmt = $pdo->prepare('UPDATE tarifas_habitaciones SET orden = ? WHERE id = ?');
            foreach ($restantes as $i => $col) {
                $stmt->execute([$i, (int) $col['id']]);
            }

            $pdo->commit();
            flash('Habitación eliminada. Revisá las promos afectadas.');
        } catch (Throwable $e) {
            $pdo->rollBack();
            flash('Error al eliminar: ' . $e->getMessage(), 'error');
        }
    }

    redirect('habitaciones.php');
}

$habitaciones = habitacionesActuales($pdo);
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Panel — Habitaciones</title>
    <link rel="stylesheet" href="assets/panel.css">
</head>
<body>
    <?php include __DIR__ . '/partials/nav.php'; ?>
    <main class="contenedor">
        <?php mostrar_flash(); ?>

        <div class="panel-acciones">
            <h2>Habitaciones</h2>
        </div>

        <div class="panel-card">
            <p class="ayuda" style="margin: 0 0 14px;">
                Son las columnas de la grilla de precios. Al borrar una, se quita su columna de todas las promos.
            </p>

            <table class="tabla">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Orden</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($habitaciones as $h): ?>
                    <tr>
                        <td>
                            <form method="post" action="habitaciones.php" style="display: flex; gap: 8px; align-items: center;">
                                <?= csrf_field() ?>
                                <input type="hidden" name="accion" value="renombrar">
                                <input type="hidden" name="id" value="<?= (int) $h['id'] ?>">
                                <input type="text" name="nombre" value="<?= e($h['nombre']) ?>">
                                <button type="submit" class="btn btn-chico">Guardar</button>
                            </form>
                        </td>
                        <td><?= (int) $h['orden'] ?></td>
                        <td>
                            <div class="fila-botones">
                                <form method="post" action="habitaciones.php">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="accion" value="orden">
                                    <input type="hidden" name="id" value="<?= (int) $h['id'] ?>">
                                    <input type="hidden" name="dir" value="-1">
                                    <button class="btn btn-secundario btn-chico" type="submit" <?= (int) $h['orden'] === 0 ? 'disabled' : '' ?>>&uarr;</button>
                                </form>
                                <form method="post" action="habitaciones.php">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="accion" value="orden">
                                    <input type="hidden" name="id" value="<?= (int) $h['id'] ?>">
                                    <input type="hidden" name="dir" value="1">
                                    <button class="btn btn-secundario btn-chico" type="submit">&darr;</button>
                                </form>
                                <form method="post" action="habitaciones.php" onsubmit="return confirm('¿Eliminar la habitación y su columna de precios en todas las promos?');">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="accion" value="eliminar">
                                    <input type="hidden" name="id" value="<?= (int) $h['id'] ?>">
                                    <button class="btn btn-peligro btn-chico" type="submit">Borrar</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <form method="post" action="habitaciones.php" style="display: flex; gap: 8px; margin-top: 16px;">
                <?= csrf_field() ?>
                <input type="hidden" name="accion" value="agregar">
                <input type="text" name="nombre" placeholder="Nueva habitación (ej. Deptox4/5)">
                <button type="submit" class="btn">Agregar</button>
            </form>
        </div>
    </main>
</body>
</html>
