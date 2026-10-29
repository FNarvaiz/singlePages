<?php
declare(strict_types=1);

/*
 * Instalador del panel.
 * 1) Subí esta carpeta al docroot del subdominio y configurá config.php.
 * 2) Entrá a instalar.php?token=<INSTALL_TOKEN>
 * 3) Creá el usuario administrador y listo.
 * 4) BORRÁ este archivo del servidor al terminar.
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/database/seed-functions.php';

$tokenOk = isset($_GET['token']) && hash_equals(INSTALL_TOKEN, (string) $_GET['token']);
$error = '';
$instalado = false;

if ($tokenOk && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim((string) ($_POST['usuario'] ?? ''));
    $clave = (string) ($_POST['clave'] ?? '');
    $clave2 = (string) ($_POST['clave2'] ?? '');

    if ($usuario === '' || mb_strlen($usuario) > 50) {
        $error = 'Ingresá un usuario válido (máx. 50 caracteres).';
    } elseif (mb_strlen($clave) < 8) {
        $error = 'La contraseña debe tener al menos 8 caracteres.';
    } elseif ($clave !== $clave2) {
        $error = 'Las contraseñas no coinciden.';
    } else {
        try {
            $pdo = db();

            $sql = file_get_contents(__DIR__ . '/database/schema.sql');
            if ($sql === false) {
                throw new RuntimeException('No se pudo leer schema.sql.');
            }
            $pdo->exec($sql);

            $existe = $pdo->prepare('SELECT COUNT(*) FROM usuarios');
            $existe->execute();
            if ((int) $existe->fetchColumn() > 0) {
                $error = 'La tabla usuarios ya tiene datos. Si querés reinstalar, vaciá las tablas primero.';
            } else {
                $stmt = $pdo->prepare('INSERT INTO usuarios (usuario, password_hash) VALUES (?, ?)');
                $stmt->execute([$usuario, password_hash($clave, PASSWORD_DEFAULT)]);

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

                $instalado = true;
            }
        } catch (Throwable $ex) {
            $error = 'Error al instalar: ' . $ex->getMessage();
        }
    }
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Instalar panel</title>
    <link rel="stylesheet" href="assets/panel.css">
</head>
<body class="login-body">
    <main class="login-card">
        <h1>Instalar panel</h1>

        <?php if (!$tokenOk): ?>
            <p>Ingresá el token de instalación en la URL: <code>instalar.php?token=...</code></p>
        <?php elseif ($instalado): ?>
            <div class="flash flash-ok">Panel instalado correctamente.</div>
            <p>
                Ya podés <a href="login.php">ingresar al panel</a>.
                <strong>BORRÁ el archivo instalar.php del servidor.</strong>
            </p>
        <?php else: ?>
            <?php if ($error): ?>
                <div class="flash flash-error"><?= e($error) ?></div>
            <?php endif; ?>
            <form method="post" action="instalar.php?token=<?= e(INSTALL_TOKEN) ?>">
                <label>
                    Usuario administrador
                    <input type="text" name="usuario" required autofocus>
                </label>
                <label>
                    Contraseña (mín. 8 caracteres)
                    <input type="password" name="clave" required>
                </label>
                <label>
                    Repetir contraseña
                    <input type="password" name="clave2" required>
                </label>
                <button type="submit" class="btn btn-primario">Instalar</button>
            </form>
        <?php endif; ?>
    </main>
</body>
</html>
