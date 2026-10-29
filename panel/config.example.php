<?php
declare(strict_types=1);

/*
 * Ejemplo de configuración del panel.
 * Copiar este archivo como config.php y completar los valores reales.
 * config.php NO se versiona: contiene credenciales y claves.
 */

// Credenciales de la base de datos MySQL (crearlas desde cPanel → Bases de datos MySQL)
const DB_HOST = 'localhost';
const DB_NAME = 'nombre_bd';
const DB_USER = 'usuario_bd';
const DB_PASS = 'contraseña_bd';

// Token secreto del instalador (solo se usa para correr instalar.php)
const INSTALL_TOKEN = 'cambiar-por-un-token-secreto';

// URL pública base del panel (para generar URLs absolutas de los íconos subidos)
const PANEL_BASE_URL = 'https://panel.lugrahotel.com.ar';

// Google Places API (New) — clave de servidor restringida por IP del hosting.
// No usar acá la clave pública del navegador (environment.ts): esa está restringida por referer.
const GOOGLE_PLACES_API_KEY = '';

// Place IDs de las fichas de Google que se muestran en el sitio.
const GOOGLE_PLACES_PLACE_IDS = [
    'hotel' => 'ChIJJ0IfTHwQhZURwapGFsTJqLU',
    'inmobiliaria' => 'ChIJ21LSOXwQhZUR6drd0pVUTRQ',
];

// Tiempo de vida de la caché de reseñas (en segundos). 43200 = 12 horas.
const RESENAS_CACHE_TTL = 43200;

// Orígenes permitidos por CORS en la API ('*' o 'https://www.lugrahotel.com.ar,https://www.luisprotti.com.ar')
const CORS_ALLOW_ORIGIN = '*';

// Límite de tamaño para íconos subidos (PNG/JPG)
const UPLOAD_MAX_BYTES = 1048576; // 1 MB

// Nombre de la sesión
const SESSION_NAME = 'panel_lugra';

session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_name(SESSION_NAME);
session_start();
