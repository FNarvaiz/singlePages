<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/api.php';

try {
    $pdo = db();

    $habitaciones = $pdo->query('SELECT nombre FROM tarifas_habitaciones ORDER BY orden, id')
        ->fetchAll(PDO::FETCH_COLUMN);

    $promociones = $pdo->query(
        'SELECT id, titulo, desde, hasta, servicios, info, imagenes, sub_headers
         FROM tarifas_promociones
         WHERE activo = 1
         ORDER BY orden, id'
    )->fetchAll();

    $stmtPromos = $pdo->prepare('SELECT nombre, precios FROM tarifas_promos WHERE promocion_id = ? ORDER BY orden, id');

    $cronograma = [];
    foreach ($promociones as $promo) {
        $stmtPromos->execute([(int) $promo['id']]);
        $promos = [];
        foreach ($stmtPromos->fetchAll() as $p) {
            $promos[] = [
                'nombre' => (string) $p['nombre'],
                'precios' => json_decode((string) $p['precios'], true) ?: [],
            ];
        }

        $entrada = [
            'titulo' => (string) $promo['titulo'],
            'Desde' => (string) $promo['desde'],
            'Hasta' => (string) $promo['hasta'],
            'Servicios' => (string) ($promo['servicios'] ?? ''),
            'Info' => (string) ($promo['info'] ?? ''),
            'Imagenes' => json_decode((string) ($promo['imagenes'] ?? '[]'), true) ?: [],
            'promos' => $promos,
        ];

        $subHeaders = json_decode((string) ($promo['sub_headers'] ?? 'null'), true);
        if (is_array($subHeaders) && $subHeaders) {
            $entrada['subHeaders'] = array_values($subHeaders);
        }

        $cronograma[] = $entrada;
    }

    api_json([
        'habitaciones' => array_values($habitaciones),
        'cronograma' => $cronograma,
    ]);
} catch (Throwable $e) {
    api_error('Error interno: ' . $e->getMessage());
}
