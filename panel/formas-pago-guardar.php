<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';

requiereLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('formas-pago.php');
}

csrf_check();

$pdo = db();
$sitio = ($_POST['sitio'] ?? 'hotel') === 'inmobiliaria' ? 'inmobiliaria' : 'hotel';
$nombre = trim((string) ($_POST['nombre'] ?? 'Formas de Pago'));

$iconos = (array) ($_POST['promos_icono'] ?? []);
$titulos = (array) ($_POST['promos_titulo'] ?? []);
$descripciones = (array) ($_POST['promos_descripcion'] ?? []);

$errores = [];

if ($nombre === '') {
    $errores[] = 'El nombre del bloque es obligatorio.';
}

$promosLimpios = [];
$cantidad = max(count($iconos), count($titulos));
for ($i = 0; $i < $cantidad; $i++) {
    $icono = trim((string) ($iconos[$i] ?? ''));
    $titulo = trim((string) ($titulos[$i] ?? ''));
    $descripcion = trim((string) ($descripciones[$i] ?? ''));

    if ($icono === '' && $titulo === '' && $descripcion === '') {
        continue;
    }
    if ($titulo === '') {
        $errores[] = 'Hay un ítem sin título.';
    }
    if (!preg_match('/^fa-[a-z0-9\-]+$/i', $icono)) {
        $errores[] = 'Ícono no válido: ' . $icono . ' (usá clases fa-...).';
    }
    $promosLimpios[] = [
        'icono' => $icono,
        'titulo' => $titulo,
        'descripcion' => $descripcion,
    ];
}

if ($errores) {
    flash(implode(' ', $errores), 'error');
    redirect('formas-pago-editar.php?sitio=' . $sitio);
}

$pdo->beginTransaction();
try {
    $stmt = $pdo->prepare('SELECT id FROM formas_pago WHERE sitio = ?');
    $stmt->execute([$sitio]);
    $bloque = $stmt->fetch();

    if ($bloque) {
        $bloqueId = (int) $bloque['id'];
        $stmt = $pdo->prepare('UPDATE formas_pago SET nombre = ? WHERE id = ?');
        $stmt->execute([$nombre, $bloqueId]);
        $stmt = $pdo->prepare('DELETE FROM formas_pago_promos WHERE forma_pago_id = ?');
        $stmt->execute([$bloqueId]);
    } else {
        $stmt = $pdo->prepare('INSERT INTO formas_pago (sitio, nombre) VALUES (?, ?)');
        $stmt->execute([$sitio, $nombre]);
        $bloqueId = (int) $pdo->lastInsertId();
    }

    $stmt = $pdo->prepare(
        'INSERT INTO formas_pago_promos (forma_pago_id, icono, titulo, descripcion, orden) VALUES (?, ?, ?, ?, ?)'
    );
    foreach ($promosLimpios as $i => $p) {
        $stmt->execute([
            $bloqueId,
            $p['icono'],
            $p['titulo'],
            $p['descripcion'] !== '' ? $p['descripcion'] : null,
            $i,
        ]);
    }

    $pdo->commit();
} catch (Throwable $e) {
    $pdo->rollBack();
    flash('Error al guardar: ' . $e->getMessage(), 'error');
    redirect('formas-pago-editar.php?sitio=' . $sitio);
}

flash('Formas de pago guardadas.');
redirect('formas-pago.php');
