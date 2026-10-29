<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/api.php';

$carpeta = trim((string) ($_GET['carpeta'] ?? ''));

if ($carpeta === '' || !preg_match('#^[a-zA-Z0-9_/\-]+$#', $carpeta)) {
    api_error('Carpeta inválida.', 400);
}

try {
    $pdo = db();

    $stmt = $pdo->prepare(
        'SELECT nombre, imagen1, imagen2, descripcion, banios, camas, categoria, direccion, venta
         FROM unidades
         WHERE carpeta = ? AND activo = 1'
    );
    $stmt->execute([$carpeta]);
    $unidad = $stmt->fetch();

    if (!$unidad) {
        api_error('Unidad no encontrada.', 404);
    }

    $stmtLoc = $pdo->prepare(
        'SELECT x, y, direccion FROM inmuebles
         WHERE carpeta = ? AND sitio = ?
         LIMIT 1'
    );
    $stmtLoc->execute([$carpeta, $unidad['venta'] ? 'venta' : 'alquiler']);
    $localizacion = $stmtLoc->fetch();

    if (!$localizacion) {
        $stmtLoc = $pdo->prepare(
            'SELECT x, y, direccion FROM inmuebles WHERE carpeta = ? LIMIT 1'
        );
        $stmtLoc->execute([$carpeta]);
        $localizacion = $stmtLoc->fetch();
    }

    $stmt = $pdo->prepare(
        'SELECT id, titulo, desde, hasta, servicios, info
         FROM unidad_tarifas
         WHERE unidad_id = (SELECT id FROM unidades WHERE carpeta = ? AND activo = 1)
         ORDER BY orden, id'
    );
    $stmt->execute([$carpeta]);

    $tarifas = [];
    $stmtPromos = $pdo->prepare(
        'SELECT nombre, precio FROM unidad_tarifa_promos WHERE tarifa_id = ? ORDER BY orden, id'
    );
    foreach ($stmt->fetchAll() as $tarifa) {
        $stmtPromos->execute([(int) $tarifa['id']]);
        $promos = [];
        foreach ($stmtPromos->fetchAll() as $promo) {
            $promos[] = [
                'nombre' => (string) $promo['nombre'],
                'precios' => [[(int) $promo['precio']]],
            ];
        }
        $tarifas[] = [
            'titulo' => (string) $tarifa['titulo'],
            'Desde' => (string) $tarifa['desde'],
            'Hasta' => (string) $tarifa['hasta'],
            'Servicios' => (string) ($tarifa['servicios'] ?? ''),
            'Info' => (string) ($tarifa['info'] ?? ''),
            'promos' => $promos,
        ];
    }

    $stmt = $pdo->prepare(
        'SELECT ruta, alt FROM unidad_imagenes WHERE unidad_id = (SELECT id FROM unidades WHERE carpeta = ? AND activo = 1) ORDER BY orden, id'
    );
    $stmt->execute([$carpeta]);
    $imagenes = [];
    foreach ($stmt->fetchAll() as $imagen) {
        $imagenes[] = [
            'ruta' => (string) $imagen['ruta'],
            'alt' => (string) ($imagen['alt'] ?? ''),
        ];
    }

    api_json([
        'nombre' => (string) $unidad['nombre'],
        'imagen1' => (string) $unidad['imagen1'],
        'imagen2' => (string) $unidad['imagen2'],
        'descripcion' => (string) ($unidad['descripcion'] ?? ''),
        'banios' => (int) $unidad['banios'],
        'camas' => (int) $unidad['camas'],
        'categoria' => (string) $unidad['categoria'],
        'direccion' => (string) $unidad['direccion'],
        'localizacion' => $localizacion ? [
            'x' => (string) $localizacion['x'],
            'y' => (string) $localizacion['y'],
            'direccion' => (string) $localizacion['direccion'],
        ] : null,
        'venta' => (bool) $unidad['venta'],
        'tarifas' => $tarifas,
        'imagenes' => $imagenes,
    ]);
} catch (Throwable $e) {
    api_error('Error interno: ' . $e->getMessage());
}
