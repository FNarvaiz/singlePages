<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';

requiereLogin();

$id = (int) ($_GET['id'] ?? 0);
$sitio = sitio_um_validar((string) ($_GET['sitio'] ?? 'hotel'));

$oferta = [
    'tipo' => '',
    'check_in' => date('Y-m-d'),
    'check_out' => date('Y-m-d', strtotime('+7 days')),
    'precio' => '',
    'info' => '',
    'activo' => 1,
];

if ($id > 0) {
    $stmt = db()->prepare('SELECT * FROM ultimo_minuto WHERE id = ?');
    $stmt->execute([$id]);
    $fila = $stmt->fetch();
    if (!$fila) {
        flash('La oferta no existe.', 'error');
        redirect('ultimo-minuto.php?sitio=' . $sitio);
    }
    $sitio = $fila['sitio'];
    $oferta = [
        'tipo' => (string) $fila['tipo'],
        'check_in' => (string) $fila['check_in'],
        'check_out' => (string) $fila['check_out'],
        'precio' => (string) $fila['precio'],
        'info' => (string) ($fila['info'] ?? ''),
        'activo' => (int) $fila['activo'],
    ];
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Panel — <?= $id ? 'Editar' : 'Nueva' ?> oferta</title>
    <link rel="stylesheet" href="assets/panel.css">
</head>
<body>
    <?php include __DIR__ . '/partials/nav.php'; ?>
    <main class="contenedor">
        <p class="migas">
            <a href="ultimo-minuto.php?sitio=<?= e($sitio) ?>">Último minuto</a> &rarr;
            <?= $id ? 'Editar oferta' : 'Nueva oferta' ?>
        </p>

        <?php mostrar_flash(); ?>

        <form method="post" action="ultimo-minuto-guardar.php">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= $id ?>">
            <input type="hidden" name="sitio" value="<?= e($sitio) ?>">

            <div class="panel-card">
                <label>
                    Tipo (ej. Doble Standard, Depto 2 Ambientes)
                    <input type="text" name="tipo" value="<?= e($oferta['tipo']) ?>" required>
                </label>
                <div class="fila-form">
                    <label>
                        Check-in
                        <input type="date" name="check_in" value="<?= e($oferta['check_in']) ?>" required>
                    </label>
                    <label>
                        Check-out
                        <input type="date" name="check_out" value="<?= e($oferta['check_out']) ?>" required>
                    </label>
                </div>
                <div class="fila-form">
                    <label>
                        Precio total de la estadía ($)
                        <input type="number" name="precio" min="0" value="<?= e($oferta['precio']) ?>" required>
                    </label>
                    <label>
                        Info (opcional)
                        <span class="ayuda">Ej. &quot;Desayuno incluido&quot;. Se oculta en pantallas chicas.</span>
                        <input type="text" name="info" maxlength="255" value="<?= e($oferta['info']) ?>">
                    </label>
                </div>
                <label class="checkbox-linea">
                    <input type="checkbox" name="activo" value="1" <?= $oferta['activo'] ? 'checked' : '' ?>>
                    Visible en el sitio
                </label>
            </div>

            <div class="fila-botones">
                <button type="submit" class="btn">Guardar</button>
                <a class="btn btn-secundario" href="ultimo-minuto.php?sitio=<?= e($sitio) ?>">Cancelar</a>
            </div>
        </form>
    </main>
</body>
</html>
