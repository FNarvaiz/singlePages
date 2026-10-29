<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/api.php';

$sitio = ($_GET['sitio'] ?? 'lugra') === 'inmobiliaria' ? 'inmobiliaria' : 'lugra';

try {
    $pdo = db();

    $salida = ['escritorio' => [], 'movil' => []];

    $stmt = $pdo->prepare(
        'SELECT dispositivo, ruta, alt, url
         FROM banners
         WHERE sitio = ? AND activo = 1
         ORDER BY orden, id'
    );
    $stmt->execute([$sitio]);

    foreach ($stmt->fetchAll() as $banner) {
        $entrada = ['ruta' => (string) $banner['ruta']];
        if ($banner['alt'] !== null && $banner['alt'] !== '') {
            $entrada['alt'] = (string) $banner['alt'];
        }
        if ($banner['url'] !== null && $banner['url'] !== '') {
            $entrada['url'] = (string) $banner['url'];
        }
        $salida[$banner['dispositivo']][] = $entrada;
    }

    api_json($salida);
} catch (Throwable $e) {
    api_error('Error interno: ' . $e->getMessage());
}
