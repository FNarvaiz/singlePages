<?php
declare(strict_types=1);

/*
 * Recuperación de contraseña de administrador.
 * 1) Subí este archivo al panel.
 * 2) Entrá a recuperar-clave.php?token=<INSTALL_TOKEN>
 * 3) Elegí el usuario y la nueva contraseña.
 * 4) BORRÁ este archivo del servidor al terminar.
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/db.php';

$tokenOk = isset($_GET['token']) && hash_equals(INSTALL_TOKEN, (string) $_GET['token']);
$error = '';
$listo = false;
$usuarioPorDefecto = '';

if ($tokenOk) {
    $usuarioPorDefecto = (string) (db()->query('SELECT usuario FROM usuarios ORDER BY id LIMIT 1')->fetchColumn() ?: '');

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $usuario = trim((string) ($_POST['usuario'] ?? ''));
        $clave = (string) ($_POST['clave'] ?? '');
        $clave2 = (string) ($_POST['clave2'] ?? '');

        if ($clave !== $clave2) {
            $error = 'Las contraseñas no coinciden.';
        } elseif (mb_strlen($clave) < 8) {
            $error = 'La contraseña debe tener al menos 8 caracteres.';
        } else {
            $pdo = db();
            $stmt = $pdo->prepare('SELECT id FROM usuarios WHERE usuario = ?');
            $stmt->execute([$usuario]);
            $fila = $stmt->fetch();
            if (!$fila) {
                $error = 'No existe el usuario "' . $usuario . '".';
            } else {
                $stmt = $pdo->prepare('UPDATE usuarios SET password_hash = ? WHERE id = ?');
                $stmt->execute([password_hash($clave, PASSWORD_DEFAULT), (int) $fila['id']]);
                $listo = true;
            }
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
    <title>Recuperar contraseña</title>
    <link rel="stylesheet" href="assets/panel.css">
</head>
<body class="login-body">
    <main class="login-card">
        <h1>Recuperar contraseña</h1>

        <?php if (!$tokenOk): ?>
            <p>Ingresá el token en la URL: <code>recuperar-clave.php?token=...</code></p>
        <?php elseif ($listo): ?>
            <div class="flash flash-ok">Contraseña actualizada.</div>
            <p>
                Ya podés <a href="login.php">ingresar al panel</a> con la nueva contraseña.
                <strong>BORRÁ el archivo recuperar-clave.php del servidor.</strong>
            </p>
        <?php else: ?>
            <?php if ($error): ?>
                <div class="flash flash-error"><?= e($error) ?></div>
            <?php endif; ?>
            <form method="post" action="recuperar-clave.php?token=<?= e(INSTALL_TOKEN) ?>">
                <label>
                    Usuario
                    <input type="text" name="usuario" value="<?= e($usuarioPorDefecto) ?>" required autofocus>
                </label>
                <label>
                    Nueva contraseña (mín. 8 caracteres)
                    <input type="password" name="clave" required>
                </label>
                <label>
                    Repetir contraseña
                    <input type="password" name="clave2" required>
                </label>
                <button type="submit" class="btn btn-primario">Cambiar contraseña</button>
            </form>
        <?php endif; ?>
    </main>
</body>
</html>
