<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';

function e(?string $valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function csrf_check(): void
{
    $token = $_POST['csrf_token'] ?? '';
    if (!is_string($token) || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(403);
        exit('Sesión inválida o vencida. Volvé atrás y reintentá.');
    }
}

function redirect(string $url): never
{
    header('Location: ' . $url);
    exit;
}

function flash(string $mensaje, string $tipo = 'ok'): void
{
    $_SESSION['flash'] = ['mensaje' => $mensaje, 'tipo' => $tipo];
}

function mostrar_flash(): void
{
    if (!empty($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        echo '<div class="flash flash-' . e($f['tipo']) . '">' . e($f['mensaje']) . '</div>';
    }
}

function fecha_legible(string $fechaYmd): string
{
    $f = DateTime::createFromFormat('Y-m-d', $fechaYmd);
    if (!$f) {
        return $fechaYmd;
    }

    return $f->format('d/m/Y');
}

function sitio_um_validar(string $valor): string
{
    return $valor === 'inmobiliaria' ? 'inmobiliaria' : 'hotel';
}

function renombrar_carpeta(PDO $pdo, string $anterior, string $nueva, ?int $unidadId = null): array
{
    $resultado = ['errores' => [], 'unidades' => 0, 'inmuebles' => 0];

    if ($anterior === $nueva) {
        return $resultado;
    }

    $stmt = $pdo->prepare('SELECT id FROM unidades WHERE carpeta = ? AND id <> ?');
    $stmt->execute([$nueva, $unidadId ?? 0]);
    if ($stmt->fetch()) {
        $resultado['errores'][] = 'Ya existe una unidad con esa carpeta.';
        return $resultado;
    }

    $stmt = $pdo->prepare('SELECT COUNT(*) FROM inmuebles WHERE carpeta = ? AND carpeta <> ?');
    $stmt->execute([$nueva, $anterior]);
    if ((int) $stmt->fetchColumn() > 0) {
        $resultado['errores'][] = 'Ya existe un inmueble con esa carpeta.';
        return $resultado;
    }

    $stmt = $pdo->prepare('UPDATE unidades SET carpeta = ? WHERE carpeta = ?');
    $stmt->execute([$nueva, $anterior]);
    $resultado['unidades'] = $stmt->rowCount();

    $stmt = $pdo->prepare('UPDATE inmuebles SET carpeta = ? WHERE carpeta = ?');
    $stmt->execute([$nueva, $anterior]);
    $resultado['inmuebles'] = $stmt->rowCount();

    return $resultado;
}
