<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';

requiereLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('tarifas.php');
}

csrf_check();

$pdo = db();
$id = (int) ($_POST['id'] ?? 0);

$titulo = trim((string) ($_POST['titulo'] ?? ''));
$desde = (string) ($_POST['desde'] ?? '');
$hasta = (string) ($_POST['hasta'] ?? '');
$servicios = trim((string) ($_POST['servicios'] ?? ''));
$info = trim((string) ($_POST['info'] ?? ''));
$activo = isset($_POST['activo']) ? 1 : 0;

$errores = [];

if ($titulo === '') {
    $errores[] = 'El título es obligatorio.';
}

$fechaDesde = DateTime::createFromFormat('Y-m-d', $desde);
$fechaHasta = DateTime::createFromFormat('Y-m-d', $hasta);
if (!$fechaDesde || $fechaDesde->format('Y-m-d') !== $desde) {
    $errores[] = 'La fecha "Desde" no es válida.';
}
if (!$fechaHasta || $fechaHasta->format('Y-m-d') !== $hasta) {
    $errores[] = 'La fecha "Hasta" no es válida.';
}
if ($fechaDesde && $fechaHasta && $fechaDesde > $fechaHasta) {
    $errores[] = 'La fecha "Desde" no puede ser posterior a "Hasta".';
}

$habitaciones = $pdo->query('SELECT nombre FROM tarifas_habitaciones ORDER BY orden, id')
    ->fetchAll(PDO::FETCH_COLUMN);
if (!$habitaciones) {
    $errores[] = 'Primero cargá al menos una habitación (sección Habitaciones).';
}

// Sub-columnas
$subHeaders = array_values(array_filter(array_map(
    fn($v) => trim((string) $v),
    (array) ($_POST['sub_headers'] ?? [])
)));
if (count($subHeaders) > 2) {
    $errores[] = 'Máximo 2 sub-columnas.';
}

// Íconos
$imagenes = array_values(array_filter(array_map(
    fn($v) => trim((string) $v),
    (array) ($_POST['imagenes'] ?? [])
)));
foreach ($imagenes as $img) {
    if (preg_match('#^https?://#i', $img)) {
        if (!str_starts_with($img, PANEL_BASE_URL . '/uploads/')) {
            $errores[] = 'Ícono con URL no permitida: ' . $img;
        }
    } elseif (!preg_match('/^[\w.\-]+\.(svg|png|jpe?g)$/i', $img)) {
        $errores[] = 'Nombre de ícono no válido: ' . $img;
    }
}

// Promos
$promos = json_decode((string) ($_POST['promos_json'] ?? ''), true);
if (!is_array($promos) || !$promos) {
    $errores[] = 'Cargá al menos una promo con su precio.';
} else {
    $columnasEsperadas = count($habitaciones);
    $subCantidad = count($subHeaders) >= 2 ? count($subHeaders) : 1;

    $promosLimpios = [];
    foreach ($promos as $fila) {
        $nombre = trim((string) ($fila['nombre'] ?? ''));
        $precios = (array) ($fila['precios'] ?? []);

        if ($nombre === '') {
            $errores[] = 'Hay una promo sin nombre.';
        }
        if (count($precios) !== $columnasEsperadas) {
            $errores[] = "La promo \"$nombre\" tiene " . count($precios)
                . " columnas de precios, se esperan $columnasEsperadas (una por habitación).";
        }

        $matriz = [];
        foreach ($precios as $celda) {
            $valores = (array) $celda;
            if (count($valores) !== $subCantidad) {
                $errores[] = "La promo \"$nombre\" tiene una celda con " . count($valores)
                    . " precios, se esperan $subCantidad.";
            }
            $numeros = [];
            foreach ($valores as $v) {
                if (!is_int($v) || $v < 0 || $v > 999999999) {
                    $errores[] = "La promo \"$nombre\" tiene un precio no válido. Usá números enteros positivos.";
                    $numeros[] = 0;
                } else {
                    $numeros[] = $v;
                }
            }
            $matriz[] = $numeros;
        }
        $promosLimpios[] = ['nombre' => $nombre, 'precios' => $matriz];
    }

    $promos = $promosLimpios;
}

if ($errores) {
    flash(implode(' ', $errores), 'error');
    redirect($id > 0 ? 'tarifas-editar.php?id=' . $id : 'tarifas-editar.php');
}

$pdo->beginTransaction();
try {
    if ($id > 0) {
        $stmt = $pdo->prepare(
            'UPDATE tarifas_promociones
             SET titulo = ?, desde = ?, hasta = ?, servicios = ?, info = ?,
                 imagenes = ?, sub_headers = ?, activo = ?
             WHERE id = ?'
        );
        $stmt->execute([
            $titulo,
            $desde,
            $hasta,
            $servicios,
            $info,
            json_encode($imagenes),
            $subHeaders ? json_encode($subHeaders) : null,
            $activo,
            $id,
        ]);
        $promocionId = $id;
        $stmt = $pdo->prepare('DELETE FROM tarifas_promos WHERE promocion_id = ?');
        $stmt->execute([$promocionId]);
    } else {
        $stmt = $pdo->prepare(
            'INSERT INTO tarifas_promociones (titulo, desde, hasta, servicios, info, imagenes, sub_headers, activo, orden)
             SELECT ?, ?, ?, ?, ?, ?, ?, ?, COALESCE(MAX(orden) + 1, 0)
             FROM tarifas_promociones'
        );
        $stmt->execute([
            $titulo,
            $desde,
            $hasta,
            $servicios,
            $info,
            json_encode($imagenes),
            $subHeaders ? json_encode($subHeaders) : null,
            $activo,
        ]);
        $promocionId = (int) $pdo->lastInsertId();
    }

    $stmt = $pdo->prepare('INSERT INTO tarifas_promos (promocion_id, nombre, precios, orden) VALUES (?, ?, ?, ?)');
    foreach ($promos as $i => $p) {
        $stmt->execute([$promocionId, $p['nombre'], json_encode($p['precios']), $i]);
    }

    $pdo->commit();
} catch (Throwable $e) {
    $pdo->rollBack();
    flash('Error al guardar: ' . $e->getMessage(), 'error');
    redirect($id > 0 ? 'tarifas-editar.php?id=' . $id : 'tarifas-editar.php');
}

flash('Promoción guardada.');
redirect('tarifas.php');
