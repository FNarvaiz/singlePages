<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';

requiereLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('ultimo-minuto.php');
}

csrf_check();

$accion = (string) ($_POST['accion'] ?? '');
$id = (int) ($_POST['id'] ?? 0);
$sitio = sitio_um_validar((string) ($_POST['sitio'] ?? 'hotel'));
$pdo = db();

if ($accion === 'eliminar') {
    $stmt = $pdo->prepare('DELETE FROM ultimo_minuto WHERE id = ?');
    $stmt->execute([$id]);
    flash('Oferta eliminada.');
    redirect('ultimo-minuto.php?sitio=' . $sitio);
}

if ($accion === 'activar' || $accion === 'desactivar') {
    $valor = $accion === 'activar' ? 1 : 0;
    $stmt = $pdo->prepare('UPDATE ultimo_minuto SET activo = ? WHERE id = ?');
    $stmt->execute([$valor, $id]);
    flash($valor ? 'Oferta visible.' : 'Oferta oculta.');
    redirect('ultimo-minuto.php?sitio=' . $sitio);
}

if ($accion === 'orden') {
    $dir = (int) ($_POST['dir'] ?? 0);
    if ($dir !== 1 && $dir !== -1) {
        redirect('ultimo-minuto.php?sitio=' . $sitio);
    }

    $stmt = $pdo->prepare('SELECT id, orden FROM ultimo_minuto WHERE id = ?');
    $stmt->execute([$id]);
    $actual = $stmt->fetch();
    if (!$actual) {
        redirect('ultimo-minuto.php?sitio=' . $sitio);
    }

    $actualOrden = (int) $actual['orden'];
    $otroOrden = $actualOrden + $dir;

    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare('SELECT id FROM ultimo_minuto WHERE sitio = ? AND orden = ? AND id <> ? LIMIT 1');
        $stmt->execute([$sitio, $otroOrden, $id]);
        $otro = $stmt->fetch();
        if ($otro) {
            $stmt = $pdo->prepare('UPDATE ultimo_minuto SET orden = ? WHERE id = ?');
            $stmt->execute([$actualOrden, (int) $otro['id']]);
        }
        $stmt = $pdo->prepare('UPDATE ultimo_minuto SET orden = ? WHERE id = ?');
        $stmt->execute([$otroOrden, $id]);
        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        flash('No se pudo reordenar.', 'error');
    }
}

redirect('ultimo-minuto.php?sitio=' . $sitio);
