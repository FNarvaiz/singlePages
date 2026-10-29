<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';

requiereLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('banners.php');
}

csrf_check();

$accion = (string) ($_POST['accion'] ?? '');
$id = (int) ($_POST['id'] ?? 0);
$sitio = (string) ($_POST['sitio'] ?? 'lugra');
if (!in_array($sitio, ['lugra', 'inmobiliaria'], true)) {
    $sitio = 'lugra';
}
$pdo = db();

if ($accion === 'eliminar') {
    $stmt = $pdo->prepare('DELETE FROM banners WHERE id = ?');
    $stmt->execute([$id]);
    flash('Banner eliminado.');
    redirect('banners.php?sitio=' . $sitio);
}

if ($accion === 'activar' || $accion === 'desactivar') {
    $valor = $accion === 'activar' ? 1 : 0;
    $stmt = $pdo->prepare('UPDATE banners SET activo = ? WHERE id = ?');
    $stmt->execute([$valor, $id]);
    flash($valor ? 'Banner visible.' : 'Banner oculto.');
    redirect('banners.php?sitio=' . $sitio);
}

if ($accion === 'orden') {
    $dir = (int) ($_POST['dir'] ?? 0);
    if ($dir !== 1 && $dir !== -1) {
        redirect('banners.php?sitio=' . $sitio);
    }

    $stmt = $pdo->prepare('SELECT id, dispositivo, orden FROM banners WHERE id = ?');
    $stmt->execute([$id]);
    $actual = $stmt->fetch();
    if (!$actual) {
        redirect('banners.php?sitio=' . $sitio);
    }

    $actualOrden = (int) $actual['orden'];
    $otroOrden = $actualOrden + $dir;
    $dispositivo = (string) $actual['dispositivo'];

    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare(
            'SELECT id FROM banners WHERE sitio = ? AND dispositivo = ? AND orden = ? AND id <> ? LIMIT 1'
        );
        $stmt->execute([$sitio, $dispositivo, $otroOrden, $id]);
        $otro = $stmt->fetch();
        if ($otro) {
            $stmt = $pdo->prepare('UPDATE banners SET orden = ? WHERE id = ?');
            $stmt->execute([$actualOrden, (int) $otro['id']]);
        }
        $stmt = $pdo->prepare('UPDATE banners SET orden = ? WHERE id = ?');
        $stmt->execute([$otroOrden, $id]);
        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        flash('No se pudo reordenar.', 'error');
    }
}

redirect('banners.php?sitio=' . $sitio);
