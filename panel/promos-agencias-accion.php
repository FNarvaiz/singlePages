<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';

requiereLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('promos-agencias.php');
}

csrf_check();

$accion = (string) ($_POST['accion'] ?? '');
$id = (int) ($_POST['id'] ?? 0);
$pdo = db();

if ($accion === 'eliminar') {
    $stmt = $pdo->prepare('DELETE FROM promos_agencias WHERE id = ?');
    $stmt->execute([$id]);
    flash('Agencia eliminada.');
    redirect('promos-agencias.php');
}

if ($accion === 'activar' || $accion === 'desactivar') {
    $valor = $accion === 'activar' ? 1 : 0;
    $stmt = $pdo->prepare('UPDATE promos_agencias SET activo = ? WHERE id = ?');
    $stmt->execute([$valor, $id]);
    flash($valor ? 'Agencia visible.' : 'Agencia oculta.');
    redirect('promos-agencias.php');
}

if ($accion === 'orden') {
    $dir = (int) ($_POST['dir'] ?? 0);
    if ($dir !== 1 && $dir !== -1) {
        redirect('promos-agencias.php');
    }

    $stmt = $pdo->prepare('SELECT id, orden FROM promos_agencias WHERE id = ?');
    $stmt->execute([$id]);
    $actual = $stmt->fetch();
    if (!$actual) {
        redirect('promos-agencias.php');
    }

    $actualOrden = (int) $actual['orden'];
    $otroOrden = $actualOrden + $dir;

    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare('SELECT id FROM promos_agencias WHERE orden = ? AND id <> ? LIMIT 1');
        $stmt->execute([$otroOrden, $id]);
        $otro = $stmt->fetch();
        if ($otro) {
            $stmt = $pdo->prepare('UPDATE promos_agencias SET orden = ? WHERE id = ?');
            $stmt->execute([$actualOrden, (int) $otro['id']]);
        }
        $stmt = $pdo->prepare('UPDATE promos_agencias SET orden = ? WHERE id = ?');
        $stmt->execute([$otroOrden, $id]);
        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        flash('No se pudo reordenar.', 'error');
    }
}

redirect('promos-agencias.php');
