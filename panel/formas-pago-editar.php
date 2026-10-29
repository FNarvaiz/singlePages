<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';

requiereLogin();

$sitio = ($_GET['sitio'] ?? 'hotel') === 'inmobiliaria' ? 'inmobiliaria' : 'hotel';

$pdo = db();

$stmt = $pdo->prepare('SELECT id, nombre FROM formas_pago WHERE sitio = ?');
$stmt->execute([$sitio]);
$bloque = $stmt->fetch();

$nombre = $bloque ? (string) $bloque['nombre'] : 'Formas de Pago';
$promos = [];

if ($bloque) {
    $stmt = $pdo->prepare('SELECT icono, titulo, descripcion FROM formas_pago_promos WHERE forma_pago_id = ? ORDER BY orden, id');
    $stmt->execute([(int) $bloque['id']]);
    foreach ($stmt->fetchAll() as $p) {
        $promos[] = [
            'icono' => (string) $p['icono'],
            'titulo' => (string) $p['titulo'],
            'descripcion' => (string) ($p['descripcion'] ?? ''),
        ];
    }
}

$iconosComunes = [
    'fa-building-columns', 'fa-credit-card', 'fa-calendar-check',
    'fa-hand-holding-dollar', 'fa-money-bill-transfer', 'fa-wallet',
    'fa-percent', 'fa-coins', 'fa-bank', 'fa-clock',
];
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Panel — Formas de pago (<?= $sitio === 'hotel' ? 'Hotel' : 'Inmobiliaria' ?>)</title>
    <link rel="stylesheet" href="assets/panel.css">
</head>
<body>
    <?php include __DIR__ . '/partials/nav.php'; ?>
    <main class="contenedor">
        <p class="migas">
            <a href="formas-pago.php">Formas de pago</a> &rarr;
            <?= $sitio === 'hotel' ? 'Hotel' : 'Inmobiliaria' ?>
        </p>

        <?php mostrar_flash(); ?>

        <form method="post" action="formas-pago-guardar.php">
            <?= csrf_field() ?>
            <input type="hidden" name="sitio" value="<?= e($sitio) ?>">

            <div class="panel-card">
                <label>
                    Nombre del bloque
                    <input type="text" name="nombre" maxlength="100" value="<?= e($nombre) ?>">
                </label>

                <p class="ayuda" style="margin: 0 0 8px;">
                    Ítems de la sección. El ícono es una clase de Font Awesome (ej. fa-credit-card).
                </p>

                <div id="filas-promos">
                    <?php foreach ($promos as $p): ?>
                    <div class="panel-card" style="padding: 14px; margin-bottom: 10px;">
                        <div class="fila-form">
                            <label>
                                Ícono
                                <input type="text" name="promos_icono[]" list="iconos-fa" value="<?= e($p['icono']) ?>">
                            </label>
                            <label>
                                Título
                                <input type="text" name="promos_titulo[]" maxlength="150" value="<?= e($p['titulo']) ?>">
                            </label>
                        </div>
                        <label style="margin-bottom: 4px;">
                            Descripción
                            <textarea name="promos_descripcion[]" rows="2"><?= e($p['descripcion']) ?></textarea>
                        </label>
                        <button type="button" class="btn-inline peligro" onclick="quitarFila(this)">Quitar ítem</button>
                    </div>
                    <?php endforeach; ?>
                </div>

                <template id="plantilla-promo">
                    <div class="panel-card" style="padding: 14px; margin-bottom: 10px;">
                        <div class="fila-form">
                            <label>
                                Ícono
                                <input type="text" name="promos_icono[]" list="iconos-fa" value="">
                            </label>
                            <label>
                                Título
                                <input type="text" name="promos_titulo[]" maxlength="150" value="">
                            </label>
                        </div>
                        <label style="margin-bottom: 4px;">
                            Descripción
                            <textarea name="promos_descripcion[]" rows="2"></textarea>
                        </label>
                        <button type="button" class="btn-inline peligro" onclick="quitarFila(this)">Quitar ítem</button>
                    </div>
                </template>

                <datalist id="iconos-fa">
                    <?php foreach ($iconosComunes as $icono): ?>
                        <option value="<?= e($icono) ?>">
                    <?php endforeach; ?>
                </datalist>

                <button type="button" class="btn btn-secundario btn-chico" onclick="agregarFila()">+ Agregar ítem</button>
            </div>

            <div class="fila-botones">
                <button type="submit" class="btn">Guardar</button>
                <a class="btn btn-secundario" href="formas-pago.php">Cancelar</a>
            </div>
        </form>
    </main>

    <script>
        function agregarFila() {
            const plantilla = document.getElementById('plantilla-promo');
            const clon = plantilla.content.cloneNode(true);
            document.getElementById('filas-promos').appendChild(clon);
        }
        function quitarFila(boton) {
            boton.closest('.panel-card').remove();
        }
    </script>
</body>
</html>
