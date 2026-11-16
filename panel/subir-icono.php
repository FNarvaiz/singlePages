<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/auth.php';

requiereLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php');
}

$retorno = (string) ($_POST['retorno'] ?? '');
$permitidos = ['tarifas-editar.php', 'banners-editar.php', 'promos-agencias-editar.php', 'unidad-editar.php'];
$rutaRetorno = preg_split('/\?/', $retorno, 2)[0];
$retornoValido = in_array($rutaRetorno, $permitidos, true);
$esAjax = ($_POST['ajax'] ?? '') === '1'
    || ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';

$responder = function (bool $ok, string $mensaje, array $extra = []) use ($esAjax, $retorno, $retornoValido): never {
    if ($esAjax) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($ok ? 200 : 400);
        echo json_encode(['ok' => $ok, 'mensaje' => $mensaje] + $extra, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    flash($mensaje, $ok ? 'ok' : 'error');
    redirect($retornoValido ? $retorno : 'index.php');
};

if (!$esAjax && !$retornoValido) {
    flash('Destino de retorno no válido.', 'error');
    redirect('index.php');
}

// Un POST vacío con contenido suele significar que post_max_size cortó la subida.
if (empty($_POST) && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
    $responder(false, 'La imagen supera el tamaño máximo permitido por el servidor.');
}

csrf_check();

if (!isset($_FILES['icono']) || !is_array($_FILES['icono'])) {
    $responder(false, 'No se recibió ningún archivo.');
}

$archivo = $_FILES['icono'];

$erroresSubida = [
    UPLOAD_ERR_INI_SIZE => 'La imagen supera el tamaño máximo permitido por el servidor.',
    UPLOAD_ERR_FORM_SIZE => 'La imagen supera el tamaño máximo permitido.',
    UPLOAD_ERR_PARTIAL => 'La subida se interrumpió. Reintentá.',
    UPLOAD_ERR_NO_FILE => 'No se recibió ningún archivo.',
    UPLOAD_ERR_NO_TMP_DIR => 'El servidor no tiene una carpeta temporal configurada.',
    UPLOAD_ERR_CANT_WRITE => 'No se pudo guardar el archivo en el servidor.',
    UPLOAD_ERR_EXTENSION => 'Una extensión de PHP interrumpió la subida.',
];
$codigoError = (int) ($archivo['error'] ?? UPLOAD_ERR_NO_FILE);
if ($codigoError !== UPLOAD_ERR_OK) {
    $responder(false, $erroresSubida[$codigoError] ?? 'Error al subir el archivo.');
}

if (empty($archivo['tmp_name']) || !is_uploaded_file($archivo['tmp_name'])) {
    $responder(false, 'No se recibió ningún archivo válido.');
}

if ((int) $archivo['size'] > UPLOAD_MAX_BYTES) {
    $responder(false, 'La imagen supera el tamaño máximo de 5 MB.');
}

$tipos = [
    'png' => IMAGETYPE_PNG,
    'jpg' => IMAGETYPE_JPEG,
    'jpeg' => IMAGETYPE_JPEG,
    'webp' => IMAGETYPE_WEBP,
];
$extension = strtolower(pathinfo((string) $archivo['name'], PATHINFO_EXTENSION));
if (!isset($tipos[$extension])) {
    $responder(false, 'Formato no permitido: solo PNG, JPG o WebP.');
}

$info = @getimagesize($archivo['tmp_name']);
if ($info === false || ($info[2] ?? null) !== $tipos[$extension]) {
    $responder(false, 'El archivo no es una imagen PNG, JPG o WebP válida.');
}

$dirUploads = __DIR__ . '/uploads';
if (!is_dir($dirUploads) && !mkdir($dirUploads, 0755, true) && !is_dir($dirUploads)) {
    $responder(false, 'No se pudo crear la carpeta de subidas.');
}

$nombreFinal = date('Ymd') . '-' . bin2hex(random_bytes(8)) . '.' . $extension;
$destino = $dirUploads . '/' . $nombreFinal;

if (!move_uploaded_file($archivo['tmp_name'], $destino)) {
    $responder(false, 'No se pudo guardar el archivo subido.');
}

$url = PANEL_BASE_URL . '/uploads/' . $nombreFinal;

$responder(true, 'Imagen subida correctamente.', [
    'url' => $url,
    'nombre' => $nombreFinal,
]);
