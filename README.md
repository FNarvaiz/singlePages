# SinglePages – Hotel Lugra e Inmobiliaria Luis Protti

Aplicación Angular (standalone, Angular 20) con dos sitios dentro del mismo proyecto:
- Hotel Lugra: inicio, galería, tarifas, contacto y restaurant.
- Inmobiliaria Luis Protti: inicio, alquileres, ventas, unidades y contacto.

Usa `HashLocationStrategy` (#/ruta) para facilitar el deploy en hosting estático.

## Requisitos
- Node.js 18+ (recomendado LTS)
- Angular CLI 20 (`npm i -g @angular/cli` opcional)

## Instalación y ejecución
- Instalar dependencias: `npm install`
- Ejecutar en desarrollo: `npm start` y abrir `http://localhost:4200/`
- Compilar producción: `npm run build` (salida en `dist/`)

## Variables de entorno (src/environments)
Editar claves en:
- `src/environments/environment.ts`
- `src/environments/environment.prod.ts`

Claves usadas por la app:
- `recaptcha.siteKey`: clave de sitio de Google reCAPTCHA v2 (checkbox).
- `googleMaps.apiKey`: Google Maps JavaScript API key (para mapa en inmuebles).

Guía completa de reCAPTCHA y backend PHP en: `src/environments/RECAPTCHA_SETUP.md`.

## Backend para formularios (enviar.php)
Los formularios de contacto (Hotel e Inmobiliaria) hacen `POST` a `${window.location.origin}/enviar.php` con:
```
{
  nombre: string,
  email: string,
  mensaje: string,
  recaptchaToken: string
}
```
Debes publicar un `enviar.php` en el root del sitio que:
- Verifique el `recaptchaToken` con tu `secretKey` de Google reCAPTCHA.
- Envíe el correo con el contenido recibido.

En `RECAPTCHA_SETUP.md` hay ejemplo de `enviar.php` y pasos para obtener claves.

## Datos y JSON necesarios (src/assets)
La app consume varios JSON estáticos desde `assets/`. Asegúrate de mantener su estructura.

- `assets/home.json` (array de tarjetas de la home):
  Ejemplo:
  ```json
  [
    {
      "image": "assets/home/hotel.jpg",
      "title": "Lugra Hotel",
      "link": "/#/hotel",
      "social": {
        "instagram": "https://instagram.com/lugrahotel",
        "facebook": "https://facebook.com/lugrahotel"
      }
    }
  ]
  ```

- `assets/banners_lugra.json` y `assets/banners_inmo.json` (cabeceras con sliders):
  ```json
  {
    "escritorio": [{ "ruta": "assets/galeria/Banners/banner_7_descuento.jpg", "alt": "..." }],
    "movil": [{ "ruta": "assets/galeria/Banners/udHistoria_7_Noches.jpg", "alt": "..." }]
  }
  ```

- `assets/galeria.json` (galería Hotel Lugra):
  ```json
  {
    "categorias": ["Hotel", "Balneario", "Habitaciones", "Desayuno"],
    "fotos": [
      { "caption": "Hotel", "src": "assets/galeria/Hotel/Hotel1.jpg", "thumb": "assets/galeria/Hotel/Hotel1_thumb.jpg", "categoria": 1 }
    ]
  }
  ```

- `assets/tarifas.json` (tarifas y promos del Hotel):
  ```json
  {
    "habitaciones": ["Doble", "Triple", "Deptox4/5"],
    "cronograma": [
      {
        "titulo": "Texto de promo",
        "Desde": "YYYY-MM-DD",
        "Hasta": "YYYY-MM-DD",
        "Servicios": "Texto con servicios. Separar con '. ' para ítems",
        "Info": "Texto opcional",
        "Imagenes": ["3_cuotas.svg", "10_desc.svg"],
        "promos": [
          { "nombre": "2 Noches", "precios": [[232900,269000], [286900,329000], [340900,419000]] }
        ]
      }
    ]
  }
  ```
  Notas:
  - `precios` es una matriz por tipo de habitación (en el orden de `habitaciones`).
  - Algunas promos usan dos columnas por habitación (p.ej. Sin Aire / Con Aire).
  - Los íconos de `Imagenes` deben existir en `assets/ambos/`.

- `assets/inmuebles_alquiler.json` y `assets/inmuebles_venta.json`:
  ```json
  {
    "categorias": ["1 AMBIENTE", "2 AMBIENTES"],
    "tipos": ["En Alquiler", "Verano 2026"],
    "inmuebles": [
      {
        "nombre": "MONTECARLO 1°C",
        "categoria": 2,
        "tipo": [1],
        "camas": 3,
        "banios": 1,
        "carpeta": "inmuebles/montecarlo1c",
        "imagen": "monte.jpeg",
        "localizacion": { "direccion": "Calle 13 ...", "x": "-38.27", "y": "-57.83" }
      }
    ]
  }
  ```
  Notas:
  - `carpeta` se concatena a `assets/inmo/` → ejemplo real: `assets/inmo/inmuebles/montecarlo1c/`.
  - Dentro de cada carpeta debe existir `info_unidad.json` e imágenes.

- `assets/inmo/<carpeta>/info_unidad.json` (detalle de la unidad):
  Estructura compatible con `UnidadComponent`:
  ```json
  {
    "nombre": "Depto Frente al Mar",
    "descripcion": "Texto o HTML de la descripción",
    "imagen1": "foto1.jpg",
    "imagen2": "foto2.jpg",
    "venta": false,
    "imagenes": [{ "ruta": "galeria1.jpg", "alt": "Vista" }],
    "direccion": "Calle 13 Nº 707",
    "categoria": "2 AMBIENTES",
    "camas": 3,
    "banios": 1,
    "tarifas": [
      {
        "titulo": "Promo Finde",
        "Desde": "2026-01-10",
        "Hasta": "2026-01-20",
        "Servicios": "Ropa blanca. Limpieza. ",
        "Info": "Opcional",
        "Imagenes": [],
        "promos": [ { "nombre": "2 Noches", "precios": [[120000]] } ]
      }
    ]
  }
  ```

## SEO: títulos y descripciones
- La app usa una `TitleStrategy` que aplica por ruta el `title` y `data.description` definidos en `src/app/app.routes.ts`.
- La página de unidad ajusta dinámicamente el `<title>` y la meta `description` con el nombre/descripcion de la propiedad.

## Scripts útiles
- `npm start`: servidor dev.
- `npm run build`: build producción en `dist/`.
- `npm run lint`: linting (Angular ESLint 20).

## Deploy
- Servir el contenido de `dist/` en tu hosting.
- Al usar `HashLocationStrategy`, no se requieren reglas de rewrite.
- Publica `enviar.php` en el root del sitio para que los formularios funcionen.

## Problemas comunes
- Google Maps no carga: revisa `environment.googleMaps.apiKey` y habilita la API en Google Cloud.
- reCAPTCHA no aparece o rechaza el token: revisa `environment.recaptcha.siteKey`, dominio autorizado y `secretKey` en `enviar.php`.
- Imágenes/JSON 404: verifica rutas relativas en `assets/` y valores `carpeta`/`ruta`.
