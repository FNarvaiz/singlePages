<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/api.php';

$sitio = ($_GET['sitio'] ?? 'hotel') === 'inmobiliaria' ? 'inmobiliaria' : 'hotel';

try {
    $pdo = db();

    $stmt = $pdo->prepare('SELECT id, nombre FROM formas_pago WHERE sitio = ?');
    $stmt->execute([$sitio]);
    $bloque = $stmt->fetch();

    $salida = [];
    if ($bloque) {
        $stmt = $pdo->prepare(
            'SELECT icono, titulo, descripcion
             FROM formas_pago_promos
             WHERE forma_pago_id = ?
             ORDER BY orden, id'
        );
        $stmt->execute([(int) $bloque['id']]);

        $promos = [];
        foreach ($stmt->fetchAll() as $p) {
            $promos[] = [
                'icono' => (string) $p['icono'],
                'titulo' => (string) $p['titulo'],
                'descripcion' => (string) ($p['descripcion'] ?? ''),
            ];
        }

        $salida[] = [
            'nombre' => (string) $bloque['nombre'],
            'promos' => $promos,
        ];
    }

    api_json($salida);
} catch (Throwable $e) {
    api_error('Error interno: ' . $e->getMessage());
}
