<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';

requiereLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('inmuebles.php');
}

csrf_check();

$pdo = db();
$id = (int) ($_POST['id'] ?? 0);

$carpetaAnterior = null;
if ($id > 0) {
    $stmt = $pdo->prepare('SELECT carpeta FROM inmuebles WHERE id = ?');
    $stmt->execute([$id]);
    $filaActual = $stmt->fetch();
    if (!$filaActual) {
        flash('El inmueble no existe.', 'error');
        redirect('inmuebles.php');
    }
    $carpetaAnterior = (string) $filaActual['carpeta'];
}

$unidadVinculadaId = null;
if ($carpetaAnterior !== null) {
    $stmt = $pdo->prepare('SELECT id FROM unidades WHERE carpeta = ?');
    $stmt->execute([$carpetaAnterior]);
    $filaUnidad = $stmt->fetchColumn();
    $unidadVinculadaId = $filaUnidad !== false ? (int) $filaUnidad : null;
}

$sitio = (string) ($_POST['sitio'] ?? 'alquiler');
if (!in_array($sitio, ['alquiler', 'venta'], true)) {
    $sitio = 'alquiler';
}

$nombre = trim((string) ($_POST['nombre'] ?? ''));
$categoria = trim((string) ($_POST['categoria'] ?? ''));
$camas = (int) ($_POST['camas'] ?? 0);
$banios = (int) ($_POST['banios'] ?? 0);
$carpeta = trim((string) ($_POST['carpeta'] ?? ''));
$imagen = trim((string) ($_POST['imagen'] ?? ''));
$direccion = trim((string) ($_POST['direccion'] ?? ''));
$x = trim((string) ($_POST['x'] ?? ''));
$y = trim((string) ($_POST['y'] ?? ''));
$activo = isset($_POST['activo']) ? 1 : 0;

$errores = [];

if ($nombre === '') {
    $errores[] = 'El nombre es obligatorio.';
}
if ($categoria === '') {
    $errores[] = 'La categoría es obligatoria.';
}
if ($carpeta === '') {
    $errores[] = 'La carpeta es obligatoria.';
} elseif (!preg_match('#^[a-zA-Z0-9_/\-]+$#', $carpeta)) {
    $errores[] = 'La carpeta solo puede contener letras, números, guiones, guiones bajos y barras.';
}
if ($imagen === '') {
    $errores[] = 'La imagen principal es obligatoria.';
} elseif (!preg_match('/^[\w.\-]+\.(svg|png|jpe?g)$/i', $imagen)) {
    $errores[] = 'El nombre de la imagen no es válido.';
}
if ($camas < 0 || $banios < 0) {
    $errores[] = 'Camas y baños deben ser 0 o más.';
}

if ($errores) {
    flash(implode(' ', $errores), 'error');
    redirect($id > 0 ? 'inmuebles-editar.php?id=' . $id . '&sitio=' . $sitio : 'inmuebles-editar.php?sitio=' . $sitio);
}

$renombrada = $id > 0 && $carpetaAnterior !== null && $carpetaAnterior !== $carpeta;
$inmueblesActualizados = 0;
$unidadesActualizadas = 0;

$pdo->beginTransaction();
try {
    if ($renombrada) {
        $resultado = renombrar_carpeta($pdo, $carpetaAnterior, $carpeta, $unidadVinculadaId);
        if ($resultado['errores']) {
            $pdo->rollBack();
            flash(implode(' ', $resultado['errores']), 'error');
            redirect('inmuebles-editar.php?id=' . $id . '&sitio=' . $sitio);
        }
        $inmueblesActualizados = (int) $resultado['inmuebles'];
        $unidadesActualizadas = (int) $resultado['unidades'];
    }

    if ($id > 0) {
        $stmt = $pdo->prepare(
            'UPDATE inmuebles
             SET sitio = ?, nombre = ?, categoria = ?, camas = ?, banios = ?, carpeta = ?,
                 imagen = ?, direccion = ?, x = ?, y = ?, activo = ?
             WHERE id = ?'
        );
        $stmt->execute([$sitio, $nombre, $categoria, $camas, $banios, $carpeta, $imagen, $direccion, $x, $y, $activo, $id]);
    } else {
        $stmt = $pdo->prepare(
            'INSERT INTO inmuebles (sitio, nombre, categoria, camas, banios, carpeta, imagen, direccion, x, y, orden, activo)
             SELECT ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, COALESCE(MAX(orden) + 1, 0), ?
             FROM inmuebles
             WHERE sitio = ?'
        );
        $stmt->execute([$sitio, $nombre, $categoria, $camas, $banios, $carpeta, $imagen, $direccion, $x, $y, $activo, $sitio]);
    }

    $pdo->commit();
} catch (Throwable $e) {
    $pdo->rollBack();
    flash('Error al guardar: ' . $e->getMessage(), 'error');
    redirect($id > 0 ? 'inmuebles-editar.php?id=' . $id . '&sitio=' . $sitio : 'inmuebles-editar.php?sitio=' . $sitio);
}

if ($renombrada) {
    flash(
        'Inmueble guardado. Carpeta renombrada: se actualizaron ' . $inmueblesActualizados .
        ' listado(s) y ' . $unidadesActualizadas . ' unidad(es). ' .
        'Renombrá la carpeta en src/assets/inmo, actualizá el prerender/sitemap y redeployá el sitio.',
        'aviso'
    );
} else {
    flash('Inmueble guardado.');
}
redirect('inmuebles.php?sitio=' . $sitio);
