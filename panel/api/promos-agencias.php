<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/api.php';

try {
    $agencias = db()->query(
        'SELECT imagen, nombre, descripcion, link
         FROM promos_agencias
         WHERE activo = 1
         ORDER BY orden, id'
    )->fetchAll();

    $salida = [];
    foreach ($agencias as $agencia) {
        $entrada = [
            'imagen' => (string) $agencia['imagen'],
            'nombre' => (string) $agencia['nombre'],
        ];
        if ($agencia['descripcion'] !== null && $agencia['descripcion'] !== '') {
            $entrada['descripcion'] = (string) $agencia['descripcion'];
        }
        if ($agencia['link'] !== null && $agencia['link'] !== '') {
            $entrada['link'] = (string) $agencia['link'];
        }
        $salida[] = $entrada;
    }

    api_json($salida);
} catch (Throwable $e) {
    api_error('Error interno: ' . $e->getMessage());
}
