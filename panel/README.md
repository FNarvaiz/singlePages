# Panel PHP — Hotel Lugra / Luis Protti

Panel de control PHP 8.5 + MySQL para editar los datos que consume la web (Angular).

## Requisitos

- PHP 8.1+ (probado en 8.5), extensión PDO MySQL habilitada.
- Base de datos MySQL (misma cuenta cPanel que la web).
- Subdominio con SSL (AutoSSL de cPanel alcanza).

## Instalación

> Copiá `config.example.php` a `config.php` y completá tus credenciales. `config.php` no se versiona.

1. **Base de datos**: en cPanel → *MySQL Databases*, creá una base (ej. `usuario_panel`) y un usuario con todos los privilegios sobre esa base. Anotá nombre/usuario/contraseña.
2. **Configurá `config.php`**: completá `DB_NAME`, `DB_USER`, `DB_PASS` y cambiá `INSTALL_TOKEN` por algo secreto.
3. **Subí la carpeta `panel/`** al docroot del subdominio (todo su contenido, sin la carpeta contenedora).
4. **Instalá**: entrá a `https://panel.lugrahotel.com.ar/instalar.php?token=<INSTALL_TOKEN>`, creá el usuario administrador. El instalador crea las tablas y carga los datos actuales desde `database/seed/`.
5. **BORRÁ `instalar.php`** del servidor.
6. Entrá a `https://panel.lugrahotel.com.ar/login.php` y listo.

## Actualizar una instalación existente

1. Subí los archivos nuevos (incluido `migrar.php`).
2. Entrá a `https://panel.lugrahotel.com.ar/migrar.php?token=<INSTALL_TOKEN>` y confirmá.
3. Crea las tablas nuevas (idempotente) y carga semillas **solo en tablas vacías**; nunca pisa datos existentes.
4. **BORRÁ `migrar.php`** del servidor.

## Estructura

| Ruta | Qué es |
| --- | --- |
| `login.php` / `logout.php` / `index.php` | Acceso y panel principal |
| `tarifas.php` + `tarifas-editar.php` + `tarifas-guardar.php` + `tarifas-accion.php` | CRUD de promociones y precios |
| `habitaciones.php` | Columnas de la grilla de tarifas |
| `ultimo-minuto*.php` | Ofertas último minuto (hotel e inmobiliaria, con `?sitio=`) |
| `banners*.php` | Banners de cabecera (hotel/inmobiliaria, escritorio/móvil) |
| `promos-agencias*.php` | Descuentos de agencias (ACA, etc.) |
| `formas-pago*.php` | Bloques de formas de pago (hotel/inmobiliaria) |
| `inmuebles*.php` | Listados de alquileres y ventas |
| `unidades.php` + `unidad-editar.php` + `unidad-guardar.php` + `unidad-accion.php` | Detalle de cada unidad (info_unidad.json): descripción, fotos, tarifas por temporada |
| `subir-icono.php` | Sube imágenes PNG/JPG a `uploads/` |
| `api/*.php` | JSON público que consume la web |
| `includes/` | Conexión, sesión, helpers, helpers de API |
| `database/schema.sql` + `database/seed/` | Esquema y datos semilla |

## Cómo consume la web

- La web fetchea los endpoints de `https://panel.lugrahotel.com.ar/api/` (CORS habilitado en `config.php`).
- Endpoints: `tarifas.php`, `ultimo-minuto-hotel.php`, `ultimo-minuto-inmobiliaria.php`, `banners.php?sitio=lugra|inmobiliaria`, `promos-agencias.php`, `formas-pago.php?sitio=hotel|inmobiliaria`, `inmuebles.php?sitio=alquiler|venta`, `unidad.php?carpeta=inmuebles/<nombre>`, `resenas.php?sitio=hotel|inmobiliaria`.
- Si el panel no responde (o durante el build con prerender), usa los JSON de `src/assets/` como respaldo.

## Reseñas de Google

- `api/resenas.php` devuelve `rating`, `userRatingCount` y hasta 5 `reviews` de la ficha de Google indicada en `GOOGLE_PLACES_PLACE_IDS` (`config.php`).
- La respuesta se cachea 12 horas en la tabla `resenas_cache` (la crea `database/schema.sql`; en instalaciones existentes hay que correr `migrar.php?token=...` y borrarlo después).
- Configurar en `config.php` una **API key de servidor** (`GOOGLE_PLACES_API_KEY`), distinta de la clave pública del navegador. Restringirla por IP del hosting y habilitarle solo `Places API (New)`.
- Si no podés fijar la IP de salida del hosting, usá una key sin restricción de aplicación pero solo con `Places API (New)` habilitada y un tope de cuota.
- Si Google falla, el endpoint devuelve la última caché disponible; si no hay caché, responde 502 y la web usa `src/assets/resenas-hotel.json` o `resenas-inmobiliaria.json` como respaldo.
- Google Places API (New) devuelve como máximo 5 reseñas por ficha; no hay paginación.
- Los íconos subidos se devuelven como URL absoluta del panel; los existentes como ruta del sitio.

## Seguridad

- Login con `password_hash`, sesión con cookie httponly + SameSite Lax.
- CSRF en todos los formularios. Consultas con PDO preparado.
- `uploads/` bloquea la ejecución de PHP (`.htaccess`). Solo PNG/JPG, máx. 1 MB, nombres aleatorios.
- El instalador y el migrador exigen token y hay que borrarlos después de usarlos.

## Recuperar la contraseña del admin

Si perdés la contraseña: subí `recuperar-clave.php`, entrá a
`recuperar-clave.php?token=<INSTALL_TOKEN>`, elegí el usuario y la nueva clave, y **borrá el archivo**.
(Alternativa: en phpMyAdmin, `UPDATE usuarios SET password_hash = '<hash bcrypt>' WHERE usuario = '...'`.)

## Renombrar la carpeta de una propiedad

El campo **Carpeta** se puede editar tanto en *Unidades* como en *Inmuebles*. Al guardar, el panel actualiza en una transacción `unidades.carpeta` y todos los `inmuebles.carpeta` que usaban el valor anterior (por ejemplo, el listado de alquiler y el de venta de la misma propiedad).

El panel **no** puede renombrar archivos ni regenerar el sitio. Después de cambiar la carpeta:

1. Renombrá `src/assets/inmo/<carpeta-vieja>` a `<carpeta-nueva>` en el repo del sitio.
2. Actualizá `src/assets/inmuebles_alquiler.json` y `src/assets/inmuebles_venta.json` (los usa el prerender).
3. Actualizá `getPrerenderParams` en `src/app/app.routes.ts` y `public/sitemap.xml`.
4. Opcional: agregá un 301 de la URL vieja a la nueva en `public/.htaccess`.
5. Ejecutá `npm run build` y desplegá `dist/single-pages/browser/`.
6. Si querés que las instalaciones nuevas nazcan con el nombre nuevo, actualizá también `panel/database/seed/`.

Para evitar imágenes rotas durante la transición, mantené una copia de la carpeta en la ruta vieja hasta completar el deploy.

## Mantenimiento

- Al agregar un inmueble/temporada, se edita todo desde el panel; no hay que tocar JSON.
- Al agregar un inmueble, cargar latitud/longitud exactas: el listado y el mapa usan esos valores.
- Al cambiar el esquema o agregar la caché de reseñas, correr `migrar.php?token=...` y borrarlo después.
- Las semillas de `database/seed/` solo se usan en la instalación inicial.
