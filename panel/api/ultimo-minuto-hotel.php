<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/api.php';

function fechaDma(string $fechaYmd): string
{
    $f = DateTime::createFromFormat('Y-m-d', $fechaYmd);
    return $f ? $f->format('d/m/Y') : $fechaYmd;
}

try {
    $pdo = db();

    $ofertas = $pdo->query(
        "SELECT tipo, check_in, check_out, precio, info
         FROM ultimo_minuto
         WHERE sitio = 'hotel' AND activo = 1
         ORDER BY orden, id"
    )->fetchAll();

    $salida = [];
    foreach ($ofertas as $oferta) {
        $entrada = [
            'tipo' => (string) $oferta['tipo'],
            'checkIn' => fechaDma((string) $oferta['check_in']),
            'checkOut' => fechaDma((string) $oferta['check_out']),
            'precio' => (int) $oferta['precio'],
        ];
        if ($oferta['info'] !== null && $oferta['info'] !== '') {
            $entrada['info'] = (string) $oferta['info'];
        }
        $salida[] = $entrada;
    }

    api_json($salida);
} catch (Throwable $e) {
    api_error('Error interno: ' . $e->getMessage());
}
