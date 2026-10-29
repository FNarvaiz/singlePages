<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';

requiereLogin();

$id = (int) ($_GET['id'] ?? 0);
$sitio = ($_GET['sitio'] ?? 'alquiler') === 'venta' ? 'venta' : 'alquiler';

$inmueble = [
    'nombre' => '',
    'categoria' => '',
    'camas' => 1,
    'banios' => 1,
    'carpeta' => 'inmuebles/',
    'imagen' => '',
    'direccion' => '',
    'x' => '',
    'y' => '',
    'activo' => 1,
];

if ($id > 0) {
    $stmt = db()->prepare('SELECT * FROM inmuebles WHERE id = ?');
    $stmt->execute([$id]);
    $fila = $stmt->fetch();
    if (!$fila) {
        flash('El inmueble no existe.', 'error');
        redirect('inmuebles.php?sitio=' . $sitio);
    }
    $sitio = $fila['sitio'];
    $inmueble = [
        'nombre' => (string) $fila['nombre'],
        'categoria' => (string) $fila['categoria'],
        'camas' => (int) $fila['camas'],
        'banios' => (int) $fila['banios'],
        'carpeta' => (string) $fila['carpeta'],
        'imagen' => (string) $fila['imagen'],
        'direccion' => (string) $fila['direccion'],
        'x' => (string) $fila['x'],
        'y' => (string) $fila['y'],
        'activo' => (int) $fila['activo'],
    ];
}

$categorias = db()->query('SELECT DISTINCT categoria FROM inmuebles ORDER BY categoria')->fetchAll(PDO::FETCH_COLUMN);
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Panel — <?= $id ? 'Editar' : 'Nuevo' ?> inmueble</title>
    <link rel="stylesheet" href="assets/panel.css">
</head>
<body>
    <?php include __DIR__ . '/partials/nav.php'; ?>
    <main class="contenedor">
        <p class="migas">
            <a href="inmuebles.php?sitio=<?= e($sitio) ?>">Inmuebles</a> &rarr;
            <?= $id ? 'Editar inmueble' : 'Nuevo inmueble' ?>
        </p>

        <?php mostrar_flash(); ?>

        <form method="post" action="inmuebles-guardar.php">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= $id ?>">

            <div class="panel-card">
                <label>
                    Sitio
                    <select name="sitio">
                        <option value="alquiler" <?= $sitio === 'alquiler' ? 'selected' : '' ?>>Alquileres</option>
                        <option value="venta" <?= $sitio === 'venta' ? 'selected' : '' ?>>Ventas</option>
                    </select>
                </label>
                <div class="fila-form">
                    <label>
                        Nombre
                        <input type="text" name="nombre" maxlength="120" value="<?= e($inmueble['nombre']) ?>" required>
                    </label>
                    <label>
                        Categoría
                        <input type="text" name="categoria" list="categorias" maxlength="60" value="<?= e($inmueble['categoria']) ?>" required>
                        <datalist id="categorias">
                            <?php foreach ($categorias as $cat): ?>
                                <option value="<?= e($cat) ?>">
                            <?php endforeach; ?>
                        </datalist>
                    </label>
                </div>
                <div class="fila-form">
                    <label>
                        Camas
                        <input type="number" name="camas" min="0" value="<?= (int) $inmueble['camas'] ?>">
                    </label>
                    <label>
                        Baños
                        <input type="number" name="banios" min="0" value="<?= (int) $inmueble['banios'] ?>">
                    </label>
                </div>
                <div class="fila-form">
                    <label>
                        Carpeta
                        <span class="ayuda">
                            Ej. inmuebles/montecarlo1c (debe existir en src/assets/inmo/).
                            Si la cambiás, también se actualizan la unidad y los demás listados que usan esa carpeta;
                            después hay que renombrar la carpeta real, actualizar el prerender/sitemap y redeployar el sitio.
                        </span>
                        <input type="text" name="carpeta" maxlength="120" value="<?= e($inmueble['carpeta']) ?>"
                            data-original="<?= $id ? e($inmueble['carpeta']) : '' ?>" required>
                    </label>
                    <label>
                        Imagen principal (nombre de archivo)
                        <span class="ayuda">Archivo dentro de la carpeta, ej. monte.jpeg.</span>
                        <input type="text" name="imagen" maxlength="120" value="<?= e($inmueble['imagen']) ?>" required>
                    </label>
                </div>
                <label>
                    Dirección
                    <input type="text" name="direccion" maxlength="200" value="<?= e($inmueble['direccion']) ?>">
                </label>
                <div class="fila-form">
                    <label>
                        Latitud (x)
                        <input type="text" name="x" maxlength="30" value="<?= e($inmueble['x']) ?>">
                    </label>
                    <label>
                        Longitud (y)
                        <input type="text" name="y" maxlength="30" value="<?= e($inmueble['y']) ?>">
                    </label>
                </div>
                <label class="checkbox-linea">
                    <input type="checkbox" name="activo" value="1" <?= $inmueble['activo'] ? 'checked' : '' ?>>
                    Visible en el sitio
                </label>
            </div>

            <div class="fila-botones">
                <button type="submit" class="btn">Guardar</button>
                <a class="btn btn-secundario" href="inmuebles.php?sitio=<?= e($sitio) ?>">Cancelar</a>
            </div>
        </form>
    </main>

    <script src="assets/panel-inmuebles.js"></script>
</body>
</html>
