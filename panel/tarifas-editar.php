<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';

requiereLogin();

$id = (int) ($_GET['id'] ?? 0);

$pdo = db();

$habitaciones = $pdo->query('SELECT nombre FROM tarifas_habitaciones ORDER BY orden, id')
    ->fetchAll(PDO::FETCH_COLUMN);

$promocion = [
    'titulo' => '',
    'desde' => date('Y-m-d'),
    'hasta' => date('Y-m-d', strtotime('+7 days')),
    'servicios' => '',
    'info' => '',
    'imagenes' => [],
    'sub_headers' => [],
    'activo' => 1,
];
$promos = [];

if ($id > 0) {
    $stmt = $pdo->prepare('SELECT * FROM tarifas_promociones WHERE id = ?');
    $stmt->execute([$id]);
    $fila = $stmt->fetch();
    if (!$fila) {
        flash('La promoción no existe.', 'error');
        redirect('tarifas.php');
    }

    $promocion = [
        'titulo' => (string) $fila['titulo'],
        'desde' => (string) $fila['desde'],
        'hasta' => (string) $fila['hasta'],
        'servicios' => (string) ($fila['servicios'] ?? ''),
        'info' => (string) ($fila['info'] ?? ''),
        'imagenes' => json_decode((string) ($fila['imagenes'] ?? '[]'), true) ?: [],
        'sub_headers' => json_decode((string) ($fila['sub_headers'] ?? 'null'), true) ?: [],
        'activo' => (int) $fila['activo'],
    ];

    $stmt = $pdo->prepare('SELECT nombre, precios FROM tarifas_promos WHERE promocion_id = ? ORDER BY orden, id');
    $stmt->execute([$id]);
    foreach ($stmt->fetchAll() as $p) {
        $promos[] = [
            'nombre' => (string) $p['nombre'],
            'precios' => json_decode((string) $p['precios'], true) ?: [],
        ];
    }
}

// Íconos existentes (se sirven desde /assets/ambos del sitio principal)
$directorioAmbos = dirname(__DIR__) . '/assets/ambos';
$iconosExistentes = [];
if (is_dir($directorioAmbos)) {
    $archivos = scandir($directorioAmbos) ?: [];
    foreach ($archivos as $archivo) {
        if (preg_match('/\.(svg|png|jpe?g)$/i', $archivo)) {
            $iconosExistentes[] = $archivo;
        }
    }
}
if (!$iconosExistentes) {
    $iconosExistentes = ['3y6_cuotas.svg', '3_cuotas.svg', '10_desc.svg', '15_desc.svg'];
}
sort($iconosExistentes);

// Íconos subidos desde el panel (URLs absolutas)
$iconosSubidos = [];
$dirUploads = __DIR__ . '/uploads';
if (is_dir($dirUploads)) {
    $archivos = scandir($dirUploads) ?: [];
    usort($archivos, function (string $a, string $b) use ($dirUploads): int {
        return (int) filemtime($dirUploads . '/' . $b) <=> (int) filemtime($dirUploads . '/' . $a);
    });
    foreach ($archivos as $archivo) {
        if (preg_match('/\.(png|jpe?g)$/i', $archivo)) {
            $iconosSubidos[] = PANEL_BASE_URL . '/uploads/' . $archivo;
        }
    }
}

$seleccionados = array_fill_keys(array_map('strval', $promocion['imagenes']), true);

$datosEditor = [
    'habitaciones' => array_values($habitaciones),
    'subHeaders' => array_values($promocion['sub_headers']),
    'promos' => $promos,
];
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Panel — <?= $id ? 'Editar' : 'Nueva' ?> promoción</title>
    <link rel="stylesheet" href="assets/panel.css">
</head>
<body>
    <?php include __DIR__ . '/partials/nav.php'; ?>
    <main class="contenedor">
        <p class="migas">
            <a href="tarifas.php">Tarifas</a> &rarr;
            <?= $id ? 'Editar promoción' : 'Nueva promoción' ?>
        </p>

        <?php mostrar_flash(); ?>

        <form id="form-tarifas" method="post" action="tarifas-guardar.php">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= $id ?>">
            <input type="hidden" name="promos_json" id="promos_json" value="">
            <div id="subheaders_json"></div>

            <div class="panel-card">
                <h2>Datos generales</h2>
                <label>
                    Título
                    <input type="text" name="titulo" value="<?= e($promocion['titulo']) ?>" required>
                </label>
                <div class="fila-form">
                    <label>
                        Desde
                        <input type="date" name="desde" value="<?= e($promocion['desde']) ?>" required>
                    </label>
                    <label>
                        Hasta
                        <input type="date" name="hasta" value="<?= e($promocion['hasta']) ?>" required>
                    </label>
                </div>
                <label>
                    Servicios incluidos
                    <span class="ayuda">Cada servicio separado con punto y espacio (&quot;. &quot;) se muestra como ítem aparte.</span>
                    <textarea name="servicios" rows="3"><?= e($promocion['servicios']) ?></textarea>
                </label>
                <label>
                    Info
                    <span class="ayuda">Texto opcional: descuentos, condiciones, aclaraciones.</span>
                    <textarea name="info" rows="3"><?= e($promocion['info']) ?></textarea>
                </label>
                <label class="checkbox-linea">
                    <input type="checkbox" name="activo" value="1" <?= $promocion['activo'] ? 'checked' : '' ?>>
                    Visible en el sitio
                </label>
            </div>

            <div class="panel-card">
                <h2>Íconos de promos</h2>
                <?php if ($iconosExistentes): ?>
                    <p class="ayuda">Íconos existentes del sitio (se muestran en /assets/ambos/):</p>
                    <div class="fila-botones" style="margin-bottom: 12px;">
                        <?php foreach ($iconosExistentes as $icono): ?>
                        <label class="icono-check">
                            <input type="checkbox" name="imagenes[]" value="<?= e($icono) ?>"
                                <?= isset($seleccionados[$icono]) ? 'checked' : '' ?>>
                            <?= e($icono) ?>
                        </label>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if ($iconosSubidos): ?>
                    <p class="ayuda">Íconos subidos desde el panel:</p>
                    <div class="fila-botones" style="margin-bottom: 12px;">
                        <?php foreach ($iconosSubidos as $icono): ?>
                        <label class="icono-check">
                            <input type="checkbox" name="imagenes[]" value="<?= e($icono) ?>"
                                <?= isset($seleccionados[$icono]) ? 'checked' : '' ?>>
                            <img src="<?= e($icono) ?>" alt="">
                        </label>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="panel-card">
                <h2>Grilla de precios</h2>
                <p class="ayuda" style="margin: 0 0 12px;">
                    Filas = promos (ej. &quot;2 Noches&quot;). Columnas = habitaciones.
                    Si definís sub-columnas (ej. Standard / C/ Aire Acond), cada celda tiene un precio por sub-columna.
                </p>

                <label>
                    Sub-columnas (opcional, máx. 2)
                    <span class="ayuda">Dejar vacío si cada celda tiene un solo precio.</span>
                </label>
                <div class="fila-botones" id="subheaders" style="margin-bottom: 12px;"></div>
                <button type="button" class="btn btn-secundario btn-chico" id="btn-agregar-subheader">+ Agregar sub-columna</button>

                <div style="overflow-x: auto;">
                    <table class="grilla-precios">
                        <thead id="grilla-cabecera"></thead>
                        <tbody id="grilla-filas"></tbody>
                    </table>
                </div>
                <button type="button" class="btn btn-secundario btn-chico" id="btn-agregar-fila"
                    style="margin-top: 12px;">+ Agregar promo</button>
            </div>

            <div class="fila-botones">
                <button type="submit" class="btn">Guardar</button>
                <a class="btn btn-secundario" href="tarifas.php">Cancelar</a>
            </div>
        </form>

        <?php if ($id): ?>
        <div class="panel-card" style="margin-top: 20px;">
            <h2>Subir ícono nuevo (PNG / JPG)</h2>
            <p class="ayuda" style="margin: 0 0 10px;">
                El ícono queda alojado en el panel y podés tildarlo arriba. Máx. 1 MB.
            </p>
            <form method="post" action="subir-icono.php" enctype="multipart/form-data" class="fila-botones">
                <?= csrf_field() ?>
                <input type="hidden" name="retorno" value="tarifas-editar.php?id=<?= $id ?>">
                <input type="file" name="icono" accept=".png,.jpg,.jpeg,image/png,image/jpeg" required>
                <button type="submit" class="btn">Subir</button>
            </form>
        </div>
        <?php endif; ?>
    </main>

    <script id="datos-editor" type="application/json"><?= json_encode($datosEditor, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?></script>
    <script src="assets/panel.js"></script>
</body>
</html>
