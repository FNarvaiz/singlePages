<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';

requiereLogin();

$id = (int) ($_GET['id'] ?? 0);

$agencia = [
    'imagen' => '',
    'nombre' => '',
    'descripcion' => '',
    'link' => '',
    'activo' => 1,
];

if ($id > 0) {
    $stmt = db()->prepare('SELECT * FROM promos_agencias WHERE id = ?');
    $stmt->execute([$id]);
    $fila = $stmt->fetch();
    if (!$fila) {
        flash('La agencia no existe.', 'error');
        redirect('promos-agencias.php');
    }
    $agencia = [
        'imagen' => (string) $fila['imagen'],
        'nombre' => (string) $fila['nombre'],
        'descripcion' => (string) ($fila['descripcion'] ?? ''),
        'link' => (string) ($fila['link'] ?? ''),
        'activo' => (int) $fila['activo'],
    ];
}

$rutasExistentes = listar_uploads();
$dirPromos = dirname(__DIR__) . '/assets/galeria/Promociones';
if (is_dir($dirPromos)) {
    $archivos = scandir($dirPromos) ?: [];
    foreach ($archivos as $archivo) {
        if (preg_match('/\.(svg|png|jpe?g|webp)$/i', $archivo)) {
            $rutasExistentes[] = '/assets/galeria/Promociones/' . $archivo;
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
    <title>Panel — <?= $id ? 'Editar' : 'Nueva' ?> agencia</title>
    <link rel="stylesheet" href="assets/panel.css">
</head>
<body>
    <?php include __DIR__ . '/partials/nav.php'; ?>
    <main class="contenedor">
        <p class="migas">
            <a href="promos-agencias.php">Promociones de agencias</a> &rarr;
            <?= $id ? 'Editar agencia' : 'Nueva agencia' ?>
        </p>

        <?php mostrar_flash(); ?>

        <form method="post" action="promos-agencias-guardar.php">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= $id ?>">

            <div class="panel-card">
                <label>
                    Nombre
                    <input type="text" name="nombre" maxlength="150" value="<?= e($agencia['nombre']) ?>" required>
                </label>
                <label>
                    Imagen (ruta o URL)
                    <span class="ayuda">Ruta del sitio o URL de una imagen subida desde el panel.</span>
                    <input id="promo-imagen" type="text" name="imagen" list="promos-existente" value="<?= e($agencia['imagen']) ?>" required>
                    <datalist id="promos-existente">
                        <?php foreach ($rutasExistentes as $ruta): ?>
                            <option value="<?= e($ruta) ?>">
                        <?php endforeach; ?>
                    </datalist>
                </label>
                <label>
                    Descripción
                    <textarea name="descripcion" rows="3"><?= e($agencia['descripcion']) ?></textarea>
                </label>
                <label>
                    Link (opcional)
                    <input type="text" name="link" maxlength="255" value="<?= e($agencia['link']) ?>">
                </label>
                <label class="checkbox-linea">
                    <input type="checkbox" name="activo" value="1" <?= $agencia['activo'] ? 'checked' : '' ?>>
                    Visible en el sitio
                </label>
            </div>

            <div class="fila-botones">
                <button type="submit" class="btn">Guardar</button>
                <a class="btn btn-secundario" href="promos-agencias.php">Cancelar</a>
            </div>
        </form>

        <div class="panel-card" style="margin-top: 20px;">
            <h2>Subir imagen (PNG / JPG / WebP)</h2>
            <p class="ayuda" style="margin: 0 0 10px;">
                Al subirla, la URL se completa automáticamente en el campo Imagen. Máx. 5 MB.
            </p>
            <form method="post" action="subir-icono.php" enctype="multipart/form-data" data-subida
                data-destino="#promo-imagen">
                <?= csrf_field() ?>
                <input type="hidden" name="retorno" value="promos-agencias-editar.php<?= $id ? '?id=' . $id : '' ?>">
                <div class="fila-subida">
                    <input type="file" name="icono" accept=".png,.jpg,.jpeg,.webp,image/png,image/jpeg,image/webp" required>
                    <button type="submit" class="btn">Subir</button>
                </div>
                <div data-aviso-subida></div>
                <div class="preview-subida" data-preview-subida>
                    <?php if (preg_match('#^https?://#i', $agencia['imagen'])): ?>
                        <a href="<?= e($agencia['imagen']) ?>" target="_blank" rel="noopener">
                            <img src="<?= e($agencia['imagen']) ?>" alt="">
                        </a>
                        <code class="url-subida"><?= e($agencia['imagen']) ?></code>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </main>

    <script src="assets/panel-upload.js"></script>
</body>
</html>
