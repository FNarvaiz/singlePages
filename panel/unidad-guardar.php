<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';

requiereLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('unidades.php');
}

csrf_check();

$pdo = db();
$id = (int) ($_POST['id'] ?? 0);

$carpetaAnterior = null;
if ($id > 0) {
    $stmt = $pdo->prepare('SELECT carpeta FROM unidades WHERE id = ?');
    $stmt->execute([$id]);
    $filaActual = $stmt->fetch();
    if (!$filaActual) {
        flash('La unidad no existe.', 'error');
        redirect('unidades.php');
    }
    $carpetaAnterior = (string) $filaActual['carpeta'];
}

$carpeta = trim((string) ($_POST['carpeta'] ?? ''));
$nombre = trim((string) ($_POST['nombre'] ?? ''));
$imagen1 = trim((string) ($_POST['imagen1'] ?? ''));
$imagen2 = trim((string) ($_POST['imagen2'] ?? ''));
$descripcion = trim((string) ($_POST['descripcion'] ?? ''));
$banios = (int) ($_POST['banios'] ?? 0);
$camas = (int) ($_POST['camas'] ?? 0);
$categoria = trim((string) ($_POST['categoria'] ?? ''));
$direccion = trim((string) ($_POST['direccion'] ?? ''));
$venta = isset($_POST['venta']) ? 1 : 0;
$activo = isset($_POST['activo']) ? 1 : 0;

$errores = [];

if ($carpeta === '') {
    $errores[] = 'La carpeta es obligatoria.';
} elseif (!preg_match('#^[a-zA-Z0-9_/\-]+$#', $carpeta)) {
    $errores[] = 'La carpeta solo puede contener letras, números, guiones, guiones bajos y barras.';
} else {
    $stmt = $pdo->prepare('SELECT id FROM unidades WHERE carpeta = ? AND id <> ?');
    $stmt->execute([$carpeta, $id]);
    if ($stmt->fetch()) {
        $errores[] = 'Ya existe una unidad con esa carpeta.';
    }
}

if ($nombre === '') {
    $errores[] = 'El nombre es obligatorio.';
}
if ($imagen1 === '') {
    $errores[] = 'La imagen principal es obligatoria.';
}

foreach ([$imagen1, $imagen2] as $imagen) {
    if ($imagen === '') {
        continue;
    }
    if (preg_match('#^https?://#i', $imagen)) {
        if (!str_starts_with($imagen, PANEL_BASE_URL . '/uploads/') && !filter_var($imagen, FILTER_VALIDATE_URL)) {
            $errores[] = 'URL de imagen no válida: ' . $imagen;
        }
    } elseif (!preg_match('/^[\w.\- ]+\.(svg|png|jpe?g)$/i', $imagen)) {
        $errores[] = 'Nombre de imagen no válido: ' . $imagen;
    }
}

if ($banios < 0 || $camas < 0) {
    $errores[] = 'Camas y baños deben ser 0 o más.';
}

// Galería
$imagenes = json_decode((string) ($_POST['imagenes_json'] ?? '[]'), true);
if (!is_array($imagenes)) {
    $imagenes = [];
}
$imagenesLimpios = [];
foreach ($imagenes as $img) {
    $ruta = trim((string) ($img['ruta'] ?? ''));
    $alt = trim((string) ($img['alt'] ?? ''));
    if ($ruta === '') {
        continue;
    }
    if (preg_match('#^https?://#i', $ruta)) {
        if (!str_starts_with($ruta, PANEL_BASE_URL . '/uploads/') && !filter_var($ruta, FILTER_VALIDATE_URL)) {
            $errores[] = 'URL de foto no válida: ' . $ruta;
        }
    } elseif (!preg_match('/^[\w.\- ]+\.(svg|png|jpe?g)$/i', $ruta)) {
        $errores[] = 'Nombre de foto no válido: ' . $ruta;
    }
    $imagenesLimpios[] = ['ruta' => $ruta, 'alt' => $alt];
}

// Tarifas
$tarifas = json_decode((string) ($_POST['tarifas_json'] ?? '[]'), true);
if (!is_array($tarifas)) {
    $tarifas = [];
}
$tarifasLimpios = [];
foreach ($tarifas as $i => $tarifa) {
    $titulo = trim((string) ($tarifa['titulo'] ?? ''));
    $desde = (string) ($tarifa['desde'] ?? '');
    $hasta = (string) ($tarifa['hasta'] ?? '');
    $servicios = trim((string) ($tarifa['servicios'] ?? ''));
    $info = trim((string) ($tarifa['info'] ?? ''));

    if ($titulo === '' && $desde === '' && $hasta === '' && !($tarifa['promos'] ?? [])) {
        continue;
    }
    if ($titulo === '') {
        $errores[] = 'Hay una temporada sin título.';
    }

    $fechaDesde = DateTime::createFromFormat('Y-m-d', $desde);
    $fechaHasta = DateTime::createFromFormat('Y-m-d', $hasta);
    if (!$fechaDesde || $fechaDesde->format('Y-m-d') !== $desde) {
        $errores[] = 'Fecha "Desde" no válida en la temporada "' . $titulo . '".';
    }
    if (!$fechaHasta || $fechaHasta->format('Y-m-d') !== $hasta) {
        $errores[] = 'Fecha "Hasta" no válida en la temporada "' . $titulo . '".';
    }
    if ($fechaDesde && $fechaHasta && $fechaDesde > $fechaHasta) {
        $errores[] = 'La fecha "Desde" es posterior a "Hasta" en la temporada "' . $titulo . '".';
    }

    $promos = [];
    foreach ((array) ($tarifa['promos'] ?? []) as $promo) {
        $nombrePromo = trim((string) ($promo['nombre'] ?? ''));
        $precio = $promo['precio'] ?? null;
        if ($nombrePromo === '' && $precio === null) {
            continue;
        }
        if ($nombrePromo === '') {
            $errores[] = 'Hay una promo sin nombre en la temporada "' . $titulo . '".';
        }
        if (!is_int($precio) || $precio < 0) {
            $errores[] = 'Precio no válido en la promo "' . $nombrePromo . '".';
        }
        $promos[] = ['nombre' => $nombrePromo, 'precio' => $precio ?? 0];
    }

    $tarifasLimpios[] = [
        'titulo' => $titulo,
        'desde' => $desde,
        'hasta' => $hasta,
        'servicios' => $servicios,
        'info' => $info,
        'promos' => $promos,
    ];
}

if ($errores) {
    flash(implode(' ', $errores), 'error');
    redirect($id > 0 ? 'unidad-editar.php?id=' . $id : 'unidad-editar.php');
}

$renombrada = $id > 0 && $carpetaAnterior !== null && $carpetaAnterior !== $carpeta;
$inmueblesActualizados = 0;

$pdo->beginTransaction();
try {
    if ($renombrada) {
        $resultado = renombrar_carpeta($pdo, $carpetaAnterior, $carpeta, $id);
        if ($resultado['errores']) {
            $pdo->rollBack();
            flash(implode(' ', $resultado['errores']), 'error');
            redirect('unidad-editar.php?id=' . $id);
        }
        $inmueblesActualizados = (int) $resultado['inmuebles'];
    }

    if ($id > 0) {
        $stmt = $pdo->prepare(
            'UPDATE unidades
             SET carpeta = ?, nombre = ?, imagen1 = ?, imagen2 = ?, descripcion = ?, banios = ?, camas = ?,
                 categoria = ?, direccion = ?, venta = ?, activo = ?
             WHERE id = ?'
        );
        $stmt->execute([
            $carpeta,
            $nombre,
            $imagen1,
            $imagen2,
            $descripcion !== '' ? $descripcion : null,
            $banios,
            $camas,
            $categoria,
            $direccion,
            $venta,
            $activo,
            $id,
        ]);
        $unidadId = $id;
    } else {
        $stmt = $pdo->prepare(
            'INSERT INTO unidades (carpeta, nombre, imagen1, imagen2, descripcion, banios, camas, categoria, direccion, venta, activo)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $carpeta,
            $nombre,
            $imagen1,
            $imagen2,
            $descripcion !== '' ? $descripcion : null,
            $banios,
            $camas,
            $categoria,
            $direccion,
            $venta,
            $activo,
        ]);
        $unidadId = (int) $pdo->lastInsertId();
    }

    // Reemplazar galería y tarifas
    $stmt = $pdo->prepare('DELETE FROM unidad_imagenes WHERE unidad_id = ?');
    $stmt->execute([$unidadId]);
    $stmt = $pdo->prepare('DELETE FROM unidad_tarifas WHERE unidad_id = ?');
    $stmt->execute([$unidadId]);

    $stmt = $pdo->prepare('INSERT INTO unidad_imagenes (unidad_id, ruta, alt, orden) VALUES (?, ?, ?, ?)');
    foreach ($imagenesLimpios as $i => $img) {
        $stmt->execute([$unidadId, $img['ruta'], $img['alt'] !== '' ? $img['alt'] : null, $i]);
    }

    $stmtTarifa = $pdo->prepare(
        'INSERT INTO unidad_tarifas (unidad_id, titulo, desde, hasta, servicios, info, orden) VALUES (?, ?, ?, ?, ?, ?, ?)'
    );
    $stmtPromo = $pdo->prepare(
        'INSERT INTO unidad_tarifa_promos (tarifa_id, nombre, precio, orden) VALUES (?, ?, ?, ?)'
    );
    foreach ($tarifasLimpios as $i => $tarifa) {
        $stmtTarifa->execute([
            $unidadId,
            $tarifa['titulo'],
            $tarifa['desde'],
            $tarifa['hasta'],
            $tarifa['servicios'] !== '' ? $tarifa['servicios'] : null,
            $tarifa['info'] !== '' ? $tarifa['info'] : null,
            $i,
        ]);
        $tarifaId = (int) $pdo->lastInsertId();
        foreach ($tarifa['promos'] as $j => $promo) {
            $stmtPromo->execute([$tarifaId, $promo['nombre'], $promo['precio'], $j]);
        }
    }

    $pdo->commit();
} catch (Throwable $e) {
    $pdo->rollBack();
    flash('Error al guardar: ' . $e->getMessage(), 'error');
    redirect($id > 0 ? 'unidad-editar.php?id=' . $id : 'unidad-editar.php');
}

if ($renombrada) {
    flash(
        'Unidad guardada. Carpeta renombrada: se actualizaron ' . $inmueblesActualizados . ' inmueble(s). ' .
        'Renombrá la carpeta en src/assets/inmo, actualizá el prerender/sitemap y redeployá el sitio.',
        'aviso'
    );
} else {
    flash('Unidad guardada.');
}
redirect('unidades.php');
