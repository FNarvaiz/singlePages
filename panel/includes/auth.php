<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/helpers.php';

function estaLogueado(): bool
{
    return !empty($_SESSION['usuario_id']);
}

function requiereLogin(): void
{
    if (!estaLogueado()) {
        redirect('login.php');
    }
}

function loguear(int $usuarioId): void
{
    session_regenerate_id(true);
    $_SESSION['usuario_id'] = $usuarioId;
    unset($_SESSION['login_intentos']);
}

function desloguear(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

function registrarIntentoFallido(): void
{
    $_SESSION['login_intentos'] = ($_SESSION['login_intentos'] ?? 0) + 1;
}

function bloquearIntentoFallido(): void
{
    if (($_SESSION['login_intentos'] ?? 0) >= 3) {
        sleep(2);
    }
}
