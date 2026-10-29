<?php
declare(strict_types=1);

/*
 * Actualización del panel (para instalaciones existentes).
 * 1) Subí los archivos nuevos al servidor.
 * 2) Entrá a migrar.php?token=<INSTALL_TOKEN>
 * 3) Borrá este archivo al terminar.
 *
 * Ejecuta el schema.sql (idempotente) y carga las semillas solo en las tablas
 * que estén vacías. Nunca pisa datos existentes.
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/database/seed-functions.php';

$tokenOk = isset($_GET['token']) && hash_equals(INSTALL_TOKEN, (string) $_GET['token']);
$mensajes = [];
$error = '';
$migrado = false;

if ($tokenOk && $_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo = db();

        $sql = file_get_contents(__DIR__ . '/database/schema.sql');
        if ($sql === false) {
            throw new RuntimeException('No se pudo leer schema.sql.');
        }
        $pdo->exec($sql);
        $mensajes[] = 'Esquema actualizado.';

        seed_tarifas($pdo);
        seed_ultimo_minuto($pdo, 'hotel');
        seed_ultimo_minuto($pdo, 'inmobiliaria');
        seed_banners($pdo, 'lugra');
        seed_banners($pdo, 'inmobiliaria');
        seed_promos_agencias($pdo);
        seed_formas_pago($pdo, 'hotel');
        seed_formas_pago($pdo, 'inmobiliaria');
        seed_inmuebles($pdo, 'alquiler');
        seed_inmuebles($pdo, 'venta');
        seed_unidades($pdo);
        $mensajes[] = 'Semillas cargadas (solo en tablas vacías).';

        $migrado = true;
    } catch (Throwable $ex) {
        $error = 'Error al migrar: ' . $ex->getMessage();
    }
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Migrar panel</title>
    <link rel="stylesheet" href="assets/panel.css">
</head>
<body class="login-body">
    <main class="login-card">
        <h1>Actualizar panel</h1>

        <?php if (!$tokenOk): ?>
            <p>Ingresá el token en la URL: <code>migrar.php?token=...</code></p>
        <?php elseif ($migrado): ?>
            <div class="flash flash-ok">Panel actualizado.</div>
            <?php foreach ($mensajes as $m): ?>
                <p><?= e($m) ?></p>
            <?php endforeach; ?>
            <p><a href="login.php">Ir al panel</a>. <strong>BORRÁ migrar.php del servidor.</strong></p>
        <?php else: ?>
            <?php if ($error): ?>
                <div class="flash flash-error"><?= e($error) ?></div>
            <?php endif; ?>
            <p>Esto crea las tablas nuevas y carga semillas solo donde no hay datos. No borra ni pisa nada existente.</p>
            <form method="post" action="migrar.php?token=<?= e(INSTALL_TOKEN) ?>">
                <button type="submit" class="btn btn-primario">Actualizar</button>
            </form>
        <?php endif; ?>
    </main>
</body>
</html>
