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

$pdo = db();
$id = (int) ($_POST['id'] ?? 0);

$sitio = (string) ($_POST['sitio'] ?? 'lugra');
if (!in_array($sitio, ['lugra', 'inmobiliaria'], true)) {
    $sitio = 'lugra';
}

$dispositivo = (string) ($_POST['dispositivo'] ?? 'escritorio');
if (!in_array($dispositivo, ['escritorio', 'movil'], true)) {
    $dispositivo = 'escritorio';
}

$ruta = trim((string) ($_POST['ruta'] ?? ''));
$alt = trim((string) ($_POST['alt'] ?? ''));
$url = trim((string) ($_POST['url'] ?? ''));
$activo = isset($_POST['activo']) ? 1 : 0;

$errores = [];

if ($ruta === '') {
    $errores[] = 'La imagen es obligatoria.';
} elseif (preg_match('#^https?://#i', $ruta)) {
    if (!str_starts_with($ruta, PANEL_BASE_URL . '/uploads/') && !filter_var($ruta, FILTER_VALIDATE_URL)) {
        $errores[] = 'La URL de la imagen no es válida.';
    }
} elseif (!preg_match('#^/[\w./\-]+\.(svg|png|jpe?g)$#i', $ruta)) {
    $errores[] = 'La ruta de la imagen no es válida. Usá una ruta del sitio o una URL.';
}

if ($url !== '' && !preg_match('#^https?://#i', $url)) {
    $errores[] = 'El link debe ser una URL completa (https://...).';
}

if ($errores) {
    flash(implode(' ', $errores), 'error');
    redirect($id > 0 ? 'banners-editar.php?id=' . $id . '&sitio=' . $sitio : 'banners-editar.php?sitio=' . $sitio);
}

if ($id > 0) {
    $stmt = $pdo->prepare(
        'UPDATE banners SET sitio = ?, dispositivo = ?, ruta = ?, alt = ?, url = ?, activo = ? WHERE id = ?'
    );
    $stmt->execute([
        $sitio,
        $dispositivo,
        $ruta,
        $alt !== '' ? $alt : null,
        $url !== '' ? $url : null,
        $activo,
        $id,
    ]);
} else {
    $stmt = $pdo->prepare(
        'INSERT INTO banners (sitio, dispositivo, ruta, alt, url, orden, activo)
         SELECT ?, ?, ?, ?, ?, COALESCE(MAX(orden) + 1, 0), ?
         FROM banners
         WHERE sitio = ? AND dispositivo = ?'
    );
    $stmt->execute([
        $sitio,
        $dispositivo,
        $ruta,
        $alt !== '' ? $alt : null,
        $url !== '' ? $url : null,
        $activo,
        $sitio,
        $dispositivo,
    ]);
}

flash('Banner guardado.');
redirect('banners.php?sitio=' . $sitio);
