# AGENTS.md

## Project

Angular 20 standalone SPA (no NgModules, no backend in this repo). Two sites in one app:
- `src/app/lugra/` — Hotel Lugra (routes under `/hotel/...`)
- `src/app/inmobiliaria/` — Inmobiliaria Luis Protti (routes under `/inmobiliaria/...`)
- `src/app/home/` — landing page at `/`

All routing is defined in `src/app/app.routes.ts`. Shared reusable components live directly under `src/app/` (carousel, formulario, navbar, etc.).

## Commands

- `npm start` — dev server on http://localhost:4200
- `npm run build` — production build **with SSG prerender**; deployable site is `dist/single-pages/browser/` (17 routes baked to HTML)
- `npm run lint` — Angular ESLint (includes template accessibility rules); currently has ~100 pre-existing errors that are NOT blocking
- `npm test` — Karma + Jasmine in real Chrome (Chrome must be installed); only a handful of `.spec.ts` files exist

## SEO / Prerender (SSG)

- Production build prerenders all routes to static HTML via the application builder (`outputMode: "static"` + `server: "src/main.server.ts"` in angular.json).
- The full route list lives in `serverRouteConfig` at the bottom of `src/app/app.routes.ts` (used via `withRoutes()` in `app.config.server.ts`). **When adding a new route, add it there too, or the build fails.**
- Unit pages: route is `unidad/:tipo/:nombre`; the 5 `getPrerenderParams` entries in `serverRouteConfig` list every carpeta (`inmuebles/{name}`). **Adding a new inmueble requires adding its entry there** and a folder in `src/assets/inmo/inmuebles/` with `info_unidad.json`.
- `provideHttpClient(withFetch())` is required: prerender fetches JSON via `fetch` only (XHR fails during prerender).
- Browser-only code must be guarded with `isPlatformBrowser()` (see mapa-inmuebles, swiper-cabecera, aviso-fraude, opiniones) or prerender crashes.
- `public/.htaccess`, `public/sitemap.xml`, `public/robots.txt` are copied to the build output root via angular.json assets. Sitemap host is `www.lugrahotel.com.ar` — update it if the domain changes.

## Deployment

- Deploy the **contents of `dist/single-pages/browser/`** (NOT the outer `dist/single-pages/` folder) to the hosting root.
- Apache/cPanel: `.htaccess` already included (SPA fallback to index.html + caching). Prerendered routes are real folders, so most URLs work without rewrite.
- Old `/#/...` URLs (pre-migration) fall back to the landing page: fragments never reach the server; let Google reindex.

## Gotchas

- Content is data-driven from JSON in `src/assets/` (galeria, tarifas, inmuebles_*.json, banners_*.json, home.json, ...). README.md documents the required schema of every file in detail — check it before touching any JSON or code that reads it.
- All asset paths in code and JSON are root-relative `/assets/...` (works on nested routes like `/hotel/tarifas`); keep them that way.
- Inmueble images and detail data resolve from `/assets/inmo/{carpeta}/`; each unidad folder must contain `info_unidad.json` plus images.
- `src/environments/environment.prod.ts` is dead code: `angular.json` has no `fileReplacements`, so only `environment.ts` is ever bundled. Edit that file for reCAPTCHA/Google Maps keys.
- Contact forms POST to `${window.location.origin}/enviar.php`, an external PHP backend not in this repo. Setup guide: `src/environments/RECAPTCHA_SETUP.md`.
- Every route declares `title` and `data.description` in `app.routes.ts`; `AppTitleStrategy` (`src/app/seo/app-title-strategy.ts`) sets `<title>` and meta description from them. Add both when creating routes.
- Components are written in Spanish; selectors must use the `app-` prefix (enforced by ESLint).
- npm installs need `--legacy-peer-deps` (ng-recaptcha pins Angular 17).
- Build budgets: initial bundle 2mb error, component styles 8kb error (several components already warn).
- `docs/` holds business reference material (rates, promos) in Spanish — useful for content tasks, not code.
- Future phase (deferred): PHP panel at `panel.lugrahotel.com.ar` will feed the runtime JSONs (tarifas, ultimo-minuto, promos, banners, formas-pago) — those stay SEO-irrelevant by design (tarifas explicitly excluded from SEO).
