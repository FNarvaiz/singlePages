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

$pdo = db();
$id = (int) ($_POST['id'] ?? 0);
$sitio = sitio_um_validar((string) ($_POST['sitio'] ?? 'hotel'));

$tipo = trim((string) ($_POST['tipo'] ?? ''));
$checkIn = (string) ($_POST['check_in'] ?? '');
$checkOut = (string) ($_POST['check_out'] ?? '');
$precio = (int) ($_POST['precio'] ?? -1);
$info = trim((string) ($_POST['info'] ?? ''));
$activo = isset($_POST['activo']) ? 1 : 0;

$errores = [];

if ($tipo === '') {
    $errores[] = 'El tipo es obligatorio.';
}

$fechaIn = DateTime::createFromFormat('Y-m-d', $checkIn);
$fechaOut = DateTime::createFromFormat('Y-m-d', $checkOut);
if (!$fechaIn || $fechaIn->format('Y-m-d') !== $checkIn) {
    $errores[] = 'La fecha de check-in no es válida.';
}
if (!$fechaOut || $fechaOut->format('Y-m-d') !== $checkOut) {
    $errores[] = 'La fecha de check-out no es válida.';
}
if ($fechaIn && $fechaOut && $fechaIn > $fechaOut) {
    $errores[] = 'El check-in no puede ser posterior al check-out.';
}
if ($precio < 0) {
    $errores[] = 'El precio debe ser un número entero positivo.';
}

if ($errores) {
    flash(implode(' ', $errores), 'error');
    redirect($id > 0 ? 'ultimo-minuto-editar.php?id=' . $id . '&sitio=' . $sitio : 'ultimo-minuto-editar.php?sitio=' . $sitio);
}

if ($id > 0) {
    $stmt = $pdo->prepare(
        'UPDATE ultimo_minuto
         SET sitio = ?, tipo = ?, check_in = ?, check_out = ?, precio = ?, info = ?, activo = ?
         WHERE id = ?'
    );
    $stmt->execute([$sitio, $tipo, $checkIn, $checkOut, $precio, $info !== '' ? $info : null, $activo, $id]);
} else {
    $stmt = $pdo->prepare(
        'INSERT INTO ultimo_minuto (sitio, tipo, check_in, check_out, precio, info, activo, orden)
         SELECT ?, ?, ?, ?, ?, ?, ?, COALESCE(MAX(orden) + 1, 0)
         FROM ultimo_minuto
         WHERE sitio = ?'
    );
    $stmt->execute([$sitio, $tipo, $checkIn, $checkOut, $precio, $info !== '' ? $info : null, $activo, $sitio]);
}

flash('Oferta guardada.');
redirect('ultimo-minuto.php?sitio=' . $sitio);
