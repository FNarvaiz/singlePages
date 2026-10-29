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

$accion = (string) ($_POST['accion'] ?? '');
$id = (int) ($_POST['id'] ?? 0);
$sitio = (string) ($_POST['sitio'] ?? 'alquiler');
if (!in_array($sitio, ['alquiler', 'venta'], true)) {
    $sitio = 'alquiler';
}
$pdo = db();

if ($accion === 'eliminar') {
    $stmt = $pdo->prepare('DELETE FROM inmuebles WHERE id = ?');
    $stmt->execute([$id]);
    flash('Inmueble eliminado.');
    redirect('inmuebles.php?sitio=' . $sitio);
}

if ($accion === 'activar' || $accion === 'desactivar') {
    $valor = $accion === 'activar' ? 1 : 0;
    $stmt = $pdo->prepare('UPDATE inmuebles SET activo = ? WHERE id = ?');
    $stmt->execute([$valor, $id]);
    flash($valor ? 'Inmueble visible.' : 'Inmueble oculto.');
    redirect('inmuebles.php?sitio=' . $sitio);
}

if ($accion === 'orden') {
    $dir = (int) ($_POST['dir'] ?? 0);
    if ($dir !== 1 && $dir !== -1) {
        redirect('inmuebles.php?sitio=' . $sitio);
    }

    $stmt = $pdo->prepare('SELECT id, orden FROM inmuebles WHERE id = ?');
    $stmt->execute([$id]);
    $actual = $stmt->fetch();
    if (!$actual) {
        redirect('inmuebles.php?sitio=' . $sitio);
    }

    $actualOrden = (int) $actual['orden'];
    $otroOrden = $actualOrden + $dir;

    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare('SELECT id FROM inmuebles WHERE sitio = ? AND orden = ? AND id <> ? LIMIT 1');
        $stmt->execute([$sitio, $otroOrden, $id]);
        $otro = $stmt->fetch();
        if ($otro) {
            $stmt = $pdo->prepare('UPDATE inmuebles SET orden = ? WHERE id = ?');
            $stmt->execute([$actualOrden, (int) $otro['id']]);
        }
        $stmt = $pdo->prepare('UPDATE inmuebles SET orden = ? WHERE id = ?');
        $stmt->execute([$otroOrden, $id]);
        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        flash('No se pudo reordenar.', 'error');
    }
}

redirect('inmuebles.php?sitio=' . $sitio);
