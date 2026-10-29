<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/api.php';

$sitio = ($_GET['sitio'] ?? 'alquiler') === 'venta' ? 'venta' : 'alquiler';

try {
    $pdo = db();

    // Categorías: nombres distintos en orden de primera aparición
    $categorias = $pdo->prepare(
        'SELECT categoria
         FROM inmuebles
         WHERE sitio = ? AND activo = 1
         GROUP BY categoria
         ORDER BY MIN(id)'
    );
    $categorias->execute([$sitio]);
    $listaCategorias = array_values(array_map(
        fn($c) => (string) $c,
        $categorias->fetchAll(PDO::FETCH_COLUMN)
    ));

    $inmuebles = $pdo->prepare(
        'SELECT nombre, categoria, camas, banios, carpeta, imagen, direccion, x, y
         FROM inmuebles
         WHERE sitio = ? AND activo = 1
         ORDER BY orden, id'
    );
    $inmuebles->execute([$sitio]);

    $lista = [];
    foreach ($inmuebles->fetchAll() as $inm) {
        $indiceCategoria = array_search($inm['categoria'], $listaCategorias, true);
        $lista[] = [
            'nombre' => (string) $inm['nombre'],
            'categoria' => $indiceCategoria === false ? 1 : $indiceCategoria + 1,
            'tipo' => [1],
            'camas' => (int) $inm['camas'],
            'banios' => (int) $inm['banios'],
            'carpeta' => (string) $inm['carpeta'],
            'imagen' => (string) $inm['imagen'],
            'localizacion' => [
                'direccion' => (string) $inm['direccion'],
                'x' => (string) $inm['x'],
                'y' => (string) $inm['y'],
            ],
        ];
    }

    api_json([
        'categorias' => $listaCategorias,
        'tipos' => $sitio === 'alquiler' ? ['En Alquiler', 'Verano 2027'] : ['En Venta', 'Verano 2027'],
        'inmuebles' => $lista,
    ]);
} catch (Throwable $e) {
    api_error('Error interno: ' . $e->getMessage());
}
