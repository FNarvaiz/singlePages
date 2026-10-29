<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';

requiereLogin();

// Acciones: activar | desactivar | eliminar | orden
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('tarifas.php');
}

csrf_check();

$accion = (string) ($_POST['accion'] ?? '');
$id = (int) ($_POST['id'] ?? 0);
$pdo = db();

if ($accion === 'eliminar') {
    $stmt = $pdo->prepare('DELETE FROM tarifas_promociones WHERE id = ?');
    $stmt->execute([$id]);
    flash('Promoción eliminada.');
    redirect('tarifas.php');
}

if ($accion === 'activar' || $accion === 'desactivar') {
    $valor = $accion === 'activar' ? 1 : 0;
    $stmt = $pdo->prepare('UPDATE tarifas_promociones SET activo = ? WHERE id = ?');
    $stmt->execute([$valor, $id]);
    flash($valor ? 'Promoción visible.' : 'Promoción oculta.');
    redirect('tarifas.php');
}

if ($accion === 'orden') {
    $dir = (int) ($_POST['dir'] ?? 0);
    if ($dir !== 1 && $dir !== -1) {
        redirect('tarifas.php');
    }

    $stmt = $pdo->prepare('SELECT id, orden FROM tarifas_promociones WHERE id = ?');
    $stmt->execute([$id]);
    $actual = $stmt->fetch();
    if (!$actual) {
        redirect('tarifas.php');
    }

    $actualOrden = (int) $actual['orden'];
    $otroOrden = $actualOrden + $dir;

    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare('SELECT id, orden FROM tarifas_promociones WHERE orden = ? AND id <> ? LIMIT 1');
        $stmt->execute([$otroOrden, $id]);
        $otro = $stmt->fetch();
        if ($otro) {
            $stmt = $pdo->prepare('UPDATE tarifas_promociones SET orden = ? WHERE id = ?');
            $stmt->execute([$actualOrden, (int) $otro['id']]);
        }
        $stmt = $pdo->prepare('UPDATE tarifas_promociones SET orden = ? WHERE id = ?');
        $stmt->execute([$otroOrden, $id]);
        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        flash('No se pudo reordenar.', 'error');
        redirect('tarifas.php');
    }

    redirect('tarifas.php');
}

redirect('tarifas.php');
