<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';

requiereLogin();

$id = (int) ($_GET['id'] ?? 0);

$unidad = [
    'carpeta' => 'inmuebles/',
    'nombre' => '',
    'imagen1' => '',
    'imagen2' => '',
    'descripcion' => '',
    'banios' => 1,
    'camas' => 2,
    'categoria' => '',
    'direccion' => '',
    'venta' => 0,
    'activo' => 1,
];
$tarifas = [];
$imagenes = [];

if ($id > 0) {
    $pdo = db();
    $stmt = $pdo->prepare('SELECT * FROM unidades WHERE id = ?');
    $stmt->execute([$id]);
    $fila = $stmt->fetch();
    if (!$fila) {
        flash('La unidad no existe.', 'error');
        redirect('unidades.php');
    }
    $unidad = [
        'carpeta' => (string) $fila['carpeta'],
        'nombre' => (string) $fila['nombre'],
        'imagen1' => (string) $fila['imagen1'],
        'imagen2' => (string) $fila['imagen2'],
        'descripcion' => (string) ($fila['descripcion'] ?? ''),
        'banios' => (int) $fila['banios'],
        'camas' => (int) $fila['camas'],
        'categoria' => (string) $fila['categoria'],
        'direccion' => (string) $fila['direccion'],
        'venta' => (int) $fila['venta'],
        'activo' => (int) $fila['activo'],
    ];

    $stmt = $pdo->prepare('SELECT id, titulo, desde, hasta, servicios, info FROM unidad_tarifas WHERE unidad_id = ? ORDER BY orden, id');
    $stmt->execute([$id]);
    $todas = $stmt->fetchAll();

    $promosPorTarifa = [];
    $stmtP = $pdo->prepare('SELECT tarifa_id, nombre, precio FROM unidad_tarifa_promos WHERE tarifa_id IN (SELECT id FROM unidad_tarifas WHERE unidad_id = ?) ORDER BY tarifa_id, orden, id');
    $stmtP->execute([$id]);
    foreach ($stmtP->fetchAll() as $promo) {
        $promosPorTarifa[(int) $promo['tarifa_id']][] = [
            'nombre' => (string) $promo['nombre'],
            'precio' => (int) $promo['precio'],
        ];
    }
    foreach ($todas as $tar) {
        $tarifas[] = [
            'titulo' => (string) $tar['titulo'],
            'desde' => (string) $tar['desde'],
            'hasta' => (string) $tar['hasta'],
            'servicios' => (string) ($tar['servicios'] ?? ''),
            'info' => (string) ($tar['info'] ?? ''),
            'promos' => $promosPorTarifa[(int) $tar['id']] ?? [],
        ];
    }

    $stmt = $pdo->prepare('SELECT ruta, alt FROM unidad_imagenes WHERE unidad_id = ? ORDER BY orden, id');
    $stmt->execute([$id]);
    foreach ($stmt->fetchAll() as $imagen) {
        $imagenes[] = [
            'ruta' => (string) $imagen['ruta'],
            'alt' => (string) ($imagen['alt'] ?? ''),
        ];
    }
}

$datosEditor = [
    'tarifas' => $tarifas,
    'imagenes' => $imagenes,
];
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Panel — <?= $id ? 'Editar' : 'Nueva' ?> unidad</title>
    <link rel="stylesheet" href="assets/panel.css">
</head>
<body>
    <?php include __DIR__ . '/partials/nav.php'; ?>
    <main class="contenedor">
        <p class="migas">
            <a href="unidades.php">Unidades</a> &rarr;
            <?= $id ? 'Editar unidad' : 'Nueva unidad' ?>
        </p>

        <?php mostrar_flash(); ?>

        <form id="form-unidad" method="post" action="unidad-guardar.php">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= $id ?>">
            <input type="hidden" name="tarifas_json" id="tarifas_json" value="">
            <input type="hidden" name="imagenes_json" id="imagenes_json" value="">

            <div class="panel-card">
                <h2>Datos de la unidad</h2>
                <label>
                    Carpeta
                    <span class="ayuda">
                        Ej. inmuebles/almar7b. Debe existir en src/assets/inmo/ con sus fotos.
                        Si la cambiás, también se actualizan los inmuebles vinculados; después hay que
                        renombrar la carpeta real, actualizar el prerender/sitemap y redeployar el sitio.
                    </span>
                    <input type="text" name="carpeta" maxlength="120" value="<?= e($unidad['carpeta']) ?>"
                        data-original="<?= $id ? e($unidad['carpeta']) : '' ?>" required>
                </label>
                <div class="fila-form">
                    <label>
                        Nombre
                        <input type="text" name="nombre" maxlength="120" value="<?= e($unidad['nombre']) ?>" required>
                    </label>
                    <label>
                        Categoría
                        <input type="text" name="categoria" maxlength="60" value="<?= e($unidad['categoria']) ?>">
                    </label>
                </div>
                <div class="fila-form">
                    <label>
                        Camas
                        <input type="number" name="camas" min="0" value="<?= (int) $unidad['camas'] ?>">
                    </label>
                    <label>
                        Baños
                        <input type="number" name="banios" min="0" value="<?= (int) $unidad['banios'] ?>">
                    </label>
                </div>
                <label>
                    Dirección
                    <input type="text" name="direccion" maxlength="200" value="<?= e($unidad['direccion']) ?>">
                </label>
                <label>
                    Descripción
                    <span class="ayuda">Se permite HTML simple (ej. &lt;br&gt; para saltos de línea).</span>
                    <textarea name="descripcion" rows="6"><?= e($unidad['descripcion']) ?></textarea>
                </label>
                <div class="fila-form">
                    <label>
                        Imagen principal 1 (archivo de la carpeta o URL subida)
                        <input type="text" name="imagen1" maxlength="255" value="<?= e($unidad['imagen1']) ?>" required>
                    </label>
                    <label>
                        Imagen principal 2 (opcional)
                        <input type="text" name="imagen2" maxlength="255" value="<?= e($unidad['imagen2']) ?>">
                    </label>
                </div>
                <label class="checkbox-linea">
                    <input type="checkbox" name="venta" value="1" <?= $unidad['venta'] ? 'checked' : '' ?>>
                    Es venta (oculta formas de pago en la página)
                </label>
                <label class="checkbox-linea">
                    <input type="checkbox" name="activo" value="1" <?= $unidad['activo'] ? 'checked' : '' ?>>
                    Visible en el sitio
                </label>
            </div>

            <div class="panel-card">
                <h2>Galería de fotos</h2>
                <p class="ayuda" style="margin: 0 0 12px;">
                    Cada foto: archivo dentro de la carpeta (ej. Almar7B.JPG) o URL de una imagen subida al panel.
                </p>
                <div id="contenedor-imagenes"></div>
                <button type="button" class="btn btn-secundario btn-chico" id="btn-agregar-imagen">+ Agregar foto</button>
            </div>

            <div class="panel-card">
                <h2>Tarifas de la unidad</h2>
                <p class="ayuda" style="margin: 0 0 12px;">
                    Temporadas con sus promos (nombre y precio). Podés dejar la lista vacía.
                </p>
                <div id="contenedor-tarifas"></div>
                <button type="button" class="btn btn-secundario btn-chico" id="btn-agregar-tarifa">+ Agregar temporada</button>
            </div>

            <div class="fila-botones">
                <button type="submit" class="btn">Guardar</button>
                <a class="btn btn-secundario" href="unidades.php">Cancelar</a>
            </div>
        </form>

        <?php if ($id): ?>
        <div class="panel-card" style="margin-top: 20px;">
            <h2>Subir foto (PNG / JPG)</h2>
            <p class="ayuda" style="margin: 0 0 10px;">
                Al subirla te devolvemos la URL para pegarla en una foto de la galería. Máx. 1 MB.
            </p>
            <form method="post" action="subir-icono.php" enctype="multipart/form-data" class="fila-botones">
                <?= csrf_field() ?>
                <input type="hidden" name="retorno" value="unidad-editar.php?id=<?= $id ?>">
                <input type="file" name="icono" accept=".png,.jpg,.jpeg,image/png,image/jpeg" required>
                <button type="submit" class="btn">Subir</button>
            </form>
        </div>
        <?php endif; ?>
    </main>

    <script id="datos-editor-unidad" type="application/json"><?= json_encode($datosEditor, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?></script>
    <script src="assets/panel-unidad.js"></script>
</body>
</html>
