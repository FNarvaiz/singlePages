<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';

function api_json(mixed $datos): never
{
    header('Content-Type: application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: ' . CORS_ALLOW_ORIGIN);
    header('Cache-Control: public, max-age=60');
    echo json_encode($datos, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function api_error(string $mensaje, int $codigo = 500): never
{
    http_response_code($codigo);
    api_json(['error' => $mensaje]);
}
