<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/api.php';

/*
 * Reseñas de Google para el sitio.
 *
 * Google Places API (New) devuelve como máximo 5 reseñas por ficha. Las respuestas
 * se cachean en `resenas_cache` para no consumir la API en cada visita.
 *
 * La clave usada acá es una clave de servidor (GOOGLE_PLACES_API_KEY), distinta
 * de la clave pública del navegador que usa Google Maps.
 */

$sitio = (string) ($_GET['sitio'] ?? 'hotel');
if (!isset(GOOGLE_PLACES_PLACE_IDS[$sitio])) {
    api_error('Sitio inválido.', 400);
}

$placeId = GOOGLE_PLACES_PLACE_IDS[$sitio];

/**
 * Devuelve la caché guardada para un sitio o null si no existe.
 *
 * @return array{payload: string, timestamp: int}|null
 */
function resenas_cache_leer(PDO $pdo, string $sitio): ?array
{
    try {
        $stmt = $pdo->prepare(
            'SELECT payload, UNIX_TIMESTAMP(actualizado_en) AS ts
             FROM resenas_cache
             WHERE sitio = ?'
        );
        $stmt->execute([$sitio]);
        $fila = $stmt->fetch();
        if (!$fila) {
            return null;
        }

        return [
            'payload' => (string) $fila['payload'],
            'timestamp' => (int) $fila['ts'],
        ];
    } catch (Throwable $e) {
        return null;
    }
}

function resenas_cache_guardar(PDO $pdo, string $sitio, string $payload): void
{
    try {
        $stmt = $pdo->prepare(
            'INSERT INTO resenas_cache (sitio, payload, actualizado_en)
             VALUES (?, ?, NOW())
             ON DUPLICATE KEY UPDATE payload = VALUES(payload), actualizado_en = VALUES(actualizado_en)'
        );
        $stmt->execute([$sitio, $payload]);
    } catch (Throwable $e) {
        error_log('resenas: no se pudo guardar la caché de ' . $sitio . ': ' . $e->getMessage());
    }
}

/**
 * Consulta Google Places API (New) y devuelve el payload normalizado.
 *
 * @return array{rating: float|int, userRatingCount: int, reviews: array<int, mixed>}
 */
function resenas_consultar_google(string $placeId, string $apiKey): array
{
    $url = 'https://places.googleapis.com/v1/places/' . rawurlencode($placeId)
        . '?languageCode=es&regionCode=AR';
    $headers = [
        'X-Goog-Api-Key: ' . $apiKey,
        'X-Goog-FieldMask: rating,userRatingCount,reviews',
    ];

    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        if ($ch === false) {
            throw new RuntimeException('No se pudo iniciar cURL.');
        }
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_CONNECTTIMEOUT => 4,
            CURLOPT_TIMEOUT => 8,
        ]);
        $crudo = curl_exec($ch);
        $estado = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($crudo === false || $estado < 200 || $estado >= 300) {
            throw new RuntimeException('Google Places respondió HTTP ' . $estado . ($error !== '' ? ': ' . $error : ''));
        }
    } else {
        $contexto = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => implode("\r\n", $headers) . "\r\n",
                'timeout' => 8,
                'ignore_errors' => true,
            ],
        ]);
        $crudo = @file_get_contents($url, false, $contexto);
        if ($crudo === false) {
            throw new RuntimeException('No se pudo consultar Google Places.');
        }
    }

    $datos = json_decode((string) $crudo, true);
    if (!is_array($datos)) {
        throw new RuntimeException('Respuesta inválida de Google Places.');
    }
    if (isset($datos['error'])) {
        $mensaje = (string) ($datos['error']['message'] ?? 'Error de Google Places.');
        throw new RuntimeException($mensaje);
    }

    $reviews = [];
    $reviewsGoogle = is_array($datos['reviews'] ?? null) ? $datos['reviews'] : [];
    foreach ($reviewsGoogle as $review) {
        if (is_array($review)) {
            $reviews[] = $review;
        }
        if (count($reviews) >= 5) {
            break;
        }
    }

    return [
        'rating' => $datos['rating'] ?? 0,
        'userRatingCount' => (int) ($datos['userRatingCount'] ?? 0),
        'reviews' => $reviews,
    ];
}

$cache = resenas_cache_leer(db(), $sitio);
if ($cache !== null && (time() - $cache['timestamp']) < RESENAS_CACHE_TTL) {
    $payload = json_decode($cache['payload'], true);
    if (is_array($payload)) {
        api_json($payload);
    }
}

if (GOOGLE_PLACES_API_KEY === '') {
    if ($cache !== null) {
        $payload = json_decode($cache['payload'], true);
        if (is_array($payload)) {
            api_json($payload);
        }
    }
    api_error('Reseñas no configuradas.', 502);
}

try {
    $payload = resenas_consultar_google($placeId, GOOGLE_PLACES_API_KEY);
    $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($json !== false) {
        resenas_cache_guardar(db(), $sitio, $json);
    }
    api_json($payload);
} catch (Throwable $e) {
    error_log('resenas: error al consultar ' . $sitio . ': ' . $e->getMessage());

    if ($cache !== null) {
        $payload = json_decode($cache['payload'], true);
        if (is_array($payload)) {
            api_json($payload);
        }
    }

    api_error('No se pudieron obtener las reseñas.', 502);
}
