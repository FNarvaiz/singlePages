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

$accion = (string) ($_POST['accion'] ?? '');
$id = (int) ($_POST['id'] ?? 0);
$pdo = db();

if ($accion === 'eliminar') {
    $stmt = $pdo->prepare('DELETE FROM unidades WHERE id = ?');
    $stmt->execute([$id]);
    flash('Unidad eliminada.');
    redirect('unidades.php');
}

if ($accion === 'activar' || $accion === 'desactivar') {
    $valor = $accion === 'activar' ? 1 : 0;
    $stmt = $pdo->prepare('UPDATE unidades SET activo = ? WHERE id = ?');
    $stmt->execute([$valor, $id]);
    flash($valor ? 'Unidad visible.' : 'Unidad oculta.');
}

redirect('unidades.php');
