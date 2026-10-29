<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/auth.php';

requiereLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php');
}

csrf_check();

$retorno = (string) ($_POST['retorno'] ?? '');
$permitidos = ['tarifas-editar.php', 'ultimo-minuto-editar.php', 'banners-editar.php', 'promos-agencias-editar.php', 'unidad-editar.php'];
$rutaRetorno = preg_split('/\?/', $retorno, 2)[0];
if (!in_array($rutaRetorno, $permitidos, true)) {
    flash('Destino de retorno no válido.', 'error');
    redirect('index.php');
}

$esAjax = ($_POST['ajax'] ?? '') === '1';
$responder = function (bool $ok, string $mensaje, array $extra = []) use ($esAjax, $retorno): never {
    if ($esAjax) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => $ok, 'mensaje' => $mensaje] + $extra, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    flash($mensaje, $ok ? 'ok' : 'error');
    redirect($retorno);
};

if (empty($_FILES['icono']) || !is_uploaded_file($_FILES['icono']['tmp_name'])) {
    $responder(false, 'No se recibió ningún archivo.');
}

$archivo = $_FILES['icono'];

if (($archivo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
    $responder(false, 'Error al subir el archivo.');
}

if ((int) $archivo['size'] > UPLOAD_MAX_BYTES) {
    $responder(false, 'El archivo supera el tamaño máximo de 1 MB.');
}

$extension = strtolower(pathinfo((string) $archivo['name'], PATHINFO_EXTENSION));
if (!in_array($extension, ['png', 'jpg', 'jpeg'], true)) {
    $responder(false, 'Formato no permitido: solo PNG o JPG.');
}

$info = @getimagesize($archivo['tmp_name']);
if ($info === false || !in_array($info[2], [IMAGETYPE_PNG, IMAGETYPE_JPEG], true)) {
    $responder(false, 'El archivo no es una imagen PNG/JPG válida.');
}

$dirUploads = __DIR__ . '/uploads';
if (!is_dir($dirUploads)) {
    mkdir($dirUploads, 0755, true);
}

$nombreFinal = date('Ymd') . '-' . bin2hex(random_bytes(8)) . '.' . $extension;
$destino = $dirUploads . '/' . $nombreFinal;

if (!move_uploaded_file($archivo['tmp_name'], $destino)) {
    $responder(false, 'No se pudo guardar el archivo subido.');
}

$responder(true, 'Imagen subida: ' . $nombreFinal, [
    'url' => PANEL_BASE_URL . '/uploads/' . $nombreFinal,
    'nombre' => $nombreFinal,
]);
