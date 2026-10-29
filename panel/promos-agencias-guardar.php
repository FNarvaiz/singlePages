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

$pdo = db();
$id = (int) ($_POST['id'] ?? 0);

$nombre = trim((string) ($_POST['nombre'] ?? ''));
$imagen = trim((string) ($_POST['imagen'] ?? ''));
$descripcion = trim((string) ($_POST['descripcion'] ?? ''));
$link = trim((string) ($_POST['link'] ?? ''));
$activo = isset($_POST['activo']) ? 1 : 0;

$errores = [];

if ($nombre === '') {
    $errores[] = 'El nombre es obligatorio.';
}
if ($imagen === '') {
    $errores[] = 'La imagen es obligatoria.';
} elseif (preg_match('#^https?://#i', $imagen)) {
    if (!str_starts_with($imagen, PANEL_BASE_URL . '/uploads/') && !filter_var($imagen, FILTER_VALIDATE_URL)) {
        $errores[] = 'La URL de la imagen no es válida.';
    }
} elseif (!preg_match('#^/[\w./\-]+\.(svg|png|jpe?g)$#i', $imagen)) {
    $errores[] = 'La ruta de la imagen no es válida.';
}
if ($link !== '' && !preg_match('#^https?://#i', $link)) {
    $errores[] = 'El link debe ser una URL completa (https://...).';
}

if ($errores) {
    flash(implode(' ', $errores), 'error');
    redirect($id > 0 ? 'promos-agencias-editar.php?id=' . $id : 'promos-agencias-editar.php');
}

if ($id > 0) {
    $stmt = $pdo->prepare(
        'UPDATE promos_agencias SET imagen = ?, nombre = ?, descripcion = ?, link = ?, activo = ? WHERE id = ?'
    );
    $stmt->execute([
        $imagen,
        $nombre,
        $descripcion !== '' ? $descripcion : null,
        $link !== '' ? $link : null,
        $activo,
        $id,
    ]);
} else {
    $stmt = $pdo->prepare(
        'INSERT INTO promos_agencias (imagen, nombre, descripcion, link, orden, activo)
         SELECT ?, ?, ?, ?, COALESCE(MAX(orden) + 1, 0), ?
         FROM promos_agencias'
    );
    $stmt->execute([
        $imagen,
        $nombre,
        $descripcion !== '' ? $descripcion : null,
        $link !== '' ? $link : null,
        $activo,
    ]);
}

flash('Agencia guardada.');
redirect('promos-agencias.php');
