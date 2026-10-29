<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';

if (estaLogueado()) {
    redirect('index.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !hash_equals(csrf_token(), (string) $_POST['csrf_token'])) {
        $error = 'Sesión inválida. Reintentá.';
    } else {
        bloquearIntentoFallido();

        $usuario = trim((string) ($_POST['usuario'] ?? ''));
        $clave = (string) ($_POST['clave'] ?? '');

        if ($usuario === '' || $clave === '') {
            $error = 'Completá usuario y contraseña.';
            registrarIntentoFallido();
        } else {
            $stmt = db()->prepare('SELECT id, password_hash FROM usuarios WHERE usuario = ? LIMIT 1');
            $stmt->execute([$usuario]);
            $fila = $stmt->fetch();

            if ($fila && password_verify($clave, $fila['password_hash'])) {
                loguear((int) $fila['id']);
                redirect('index.php');
            }

            $error = 'Usuario o contraseña incorrectos.';
            registrarIntentoFallido();
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
    <title>Panel — Ingresar</title>
    <link rel="stylesheet" href="assets/panel.css">
</head>
<body class="login-body">
    <main class="login-card">
        <h1>Panel Lugra</h1>
        <?php if ($error): ?>
            <div class="flash flash-error"><?= e($error) ?></div>
        <?php endif; ?>
        <form method="post" action="login.php">
            <?= csrf_field() ?>
            <label>
                Usuario
                <input type="text" name="usuario" autocomplete="username" required autofocus>
            </label>
            <label>
                Contraseña
                <input type="password" name="clave" autocomplete="current-password" required>
            </label>
            <button type="submit" class="btn btn-primario">Ingresar</button>
        </form>
    </main>
</body>
</html>
