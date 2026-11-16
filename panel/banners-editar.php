<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';

requiereLogin();

$id = (int) ($_GET['id'] ?? 0);
$sitio = ($_GET['sitio'] ?? 'lugra') === 'inmobiliaria' ? 'inmobiliaria' : 'lugra';

$banner = [
    'dispositivo' => 'escritorio',
    'ruta' => '',
    'alt' => '',
    'url' => '',
    'activo' => 1,
];

if ($id > 0) {
    $stmt = db()->prepare('SELECT * FROM banners WHERE id = ?');
    $stmt->execute([$id]);
    $fila = $stmt->fetch();
    if (!$fila) {
        flash('El banner no existe.', 'error');
        redirect('banners.php?sitio=' . $sitio);
    }
    $sitio = $fila['sitio'];
    $banner = [
        'dispositivo' => (string) $fila['dispositivo'],
        'ruta' => (string) $fila['ruta'],
        'alt' => (string) ($fila['alt'] ?? ''),
        'url' => (string) ($fila['url'] ?? ''),
        'activo' => (int) $fila['activo'],
    ];
}

// Rutas existentes de banners del sitio (misma cuenta cPanel) + imágenes subidas al panel
$rutasExistentes = listar_uploads();
$dirBanners = dirname(__DIR__) . '/assets/galeria/Banners';
if (is_dir($dirBanners)) {
    $archivos = scandir($dirBanners) ?: [];
    foreach ($archivos as $archivo) {
        if (preg_match('/\.(svg|png|jpe?g|webp)$/i', $archivo)) {
            $rutasExistentes[] = '/assets/galeria/Banners/' . $archivo;
        }
    }
}
$rutasExistentes = array_values(array_unique($rutasExistentes));
sort($rutasExistentes);
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Panel — <?= $id ? 'Editar' : 'Nuevo' ?> banner</title>
    <link rel="stylesheet" href="assets/panel.css">
</head>
<body>
    <?php include __DIR__ . '/partials/nav.php'; ?>
    <main class="contenedor">
        <p class="migas">
            <a href="banners.php?sitio=<?= e($sitio) ?>">Banners</a> &rarr;
            <?= $id ? 'Editar banner' : 'Nuevo banner' ?>
        </p>

        <?php mostrar_flash(); ?>

        <form method="post" action="banners-guardar.php">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= $id ?>">

            <div class="panel-card">
                <div class="fila-form">
                    <label>
                        Sitio
                        <select name="sitio">
                            <option value="lugra" <?= $sitio === 'lugra' ? 'selected' : '' ?>>Hotel Lugra</option>
                            <option value="inmobiliaria" <?= $sitio === 'inmobiliaria' ? 'selected' : '' ?>>Inmobiliaria</option>
                        </select>
                    </label>
                    <label>
                        Dispositivo
                        <select name="dispositivo">
                            <option value="escritorio" <?= $banner['dispositivo'] === 'escritorio' ? 'selected' : '' ?>>Escritorio</option>
                            <option value="movil" <?= $banner['dispositivo'] === 'movil' ? 'selected' : '' ?>>Móvil</option>
                        </select>
                    </label>
                </div>
                <label>
                    Imagen (ruta)
                    <span class="ayuda">Ruta del sitio (ej. /assets/galeria/Banners/...) o URL de un archivo subido.</span>
                    <input id="banner-ruta" type="text" name="ruta" list="banners-existente" value="<?= e($banner['ruta']) ?>" required>
                    <datalist id="banners-existente">
                        <?php foreach ($rutasExistentes as $ruta): ?>
                            <option value="<?= e($ruta) ?>">
                        <?php endforeach; ?>
                    </datalist>
                </label>
                <label>
                    Texto alternativo (alt)
                    <input type="text" name="alt" maxlength="255" value="<?= e($banner['alt']) ?>">
                </label>
                <label>
                    Link al hacer click (opcional)
                    <input type="text" name="url" maxlength="255" value="<?= e($banner['url']) ?>">
                </label>
                <label class="checkbox-linea">
                    <input type="checkbox" name="activo" value="1" <?= $banner['activo'] ? 'checked' : '' ?>>
                    Visible en el sitio
                </label>
            </div>

            <div class="fila-botones">
                <button type="submit" class="btn">Guardar</button>
                <a class="btn btn-secundario" href="banners.php?sitio=<?= e($sitio) ?>">Cancelar</a>
            </div>
        </form>

        <div class="panel-card" style="margin-top: 20px;">
            <h2>Subir imagen (PNG / JPG / WebP)</h2>
            <p class="ayuda" style="margin: 0 0 10px;">
                La imagen se sube al panel y la URL se completa automáticamente en el campo Imagen.
                Máx. 5 MB. Recomendado: 1920 px de ancho.
            </p>
            <form method="post" action="subir-icono.php" enctype="multipart/form-data" data-subida
                data-destino="#banner-ruta">
                <?= csrf_field() ?>
                <input type="hidden" name="retorno" value="<?= e('banners-editar.php' . ($id ? '?id=' . $id . '&sitio=' . $sitio : '?sitio=' . $sitio)) ?>">
                <div class="fila-subida">
                    <input type="file" name="icono" accept=".png,.jpg,.jpeg,.webp,image/png,image/jpeg,image/webp" required>
                    <button type="submit" class="btn">Subir</button>
                </div>
                <div data-aviso-subida></div>
                <div class="preview-subida" data-preview-subida>
                    <?php if (preg_match('#^https?://#i', $banner['ruta'])): ?>
                        <a href="<?= e($banner['ruta']) ?>" target="_blank" rel="noopener">
                            <img src="<?= e($banner['ruta']) ?>" alt="">
                        </a>
                        <code class="url-subida"><?= e($banner['ruta']) ?></code>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </main>

    <script src="assets/panel-upload.js"></script>
</body>
</html>
