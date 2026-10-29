<?php
declare(strict_types=1);

/*
 * Funciones de carga de datos semilla desde panel/database/seed/.
 * Todas verifican que la tabla esté vacía antes de cargar, para poder
 * reutilizarlas desde instalar.php (instalación nueva) y migrar.php (upgrade).
 */

function seed_tarifas(PDO $pdo): void
{
    $existe = $pdo->query('SELECT COUNT(*) FROM tarifas_habitaciones')->fetchColumn();
    if ((int) $existe > 0) {
        return;
    }

    $archivo = __DIR__ . '/seed/tarifas.json';
    if (!is_file($archivo)) {
        return;
    }
    $datos = json_decode((string) file_get_contents($archivo), true);
    if (!is_array($datos)) {
        return;
    }

    foreach (($datos['habitaciones'] ?? []) as $i => $nombre) {
        $stmt = $pdo->prepare('INSERT INTO tarifas_habitaciones (nombre, orden) VALUES (?, ?)');
        $stmt->execute([(string) $nombre, $i]);
    }

    foreach (($datos['cronograma'] ?? []) as $i => $promo) {
        $stmt = $pdo->prepare(
            'INSERT INTO tarifas_promociones (titulo, desde, hasta, servicios, info, imagenes, sub_headers, activo, orden)
             VALUES (?, ?, ?, ?, ?, ?, ?, 1, ?)'
        );
        $stmt->execute([
            (string) ($promo['titulo'] ?? ''),
            (string) ($promo['Desde'] ?? ''),
            (string) ($promo['Hasta'] ?? ''),
            (string) ($promo['Servicios'] ?? ''),
            (string) ($promo['Info'] ?? ''),
            json_encode($promo['Imagenes'] ?? []),
            isset($promo['subHeaders']) ? json_encode($promo['subHeaders']) : null,
            $i,
        ]);
        $promocionId = (int) $pdo->lastInsertId();

        foreach (($promo['promos'] ?? []) as $j => $p) {
            $stmt = $pdo->prepare('INSERT INTO tarifas_promos (promocion_id, nombre, precios, orden) VALUES (?, ?, ?, ?)');
            $stmt->execute([
                $promocionId,
                (string) ($p['nombre'] ?? ''),
                json_encode($p['precios'] ?? []),
                $j,
            ]);
        }
    }
}

function seed_ultimo_minuto(PDO $pdo, string $sitio): void
{
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM ultimo_minuto WHERE sitio = ?');
    $stmt->execute([$sitio]);
    if ((int) $stmt->fetchColumn() > 0) {
        return;
    }

    $archivo = $sitio === 'hotel'
        ? __DIR__ . '/seed/ultimo-minuto-hotel.json'
        : __DIR__ . '/seed/ultimo-minuto-inmobiliaria.json';
    if (!is_file($archivo)) {
        return;
    }
    $datos = json_decode((string) file_get_contents($archivo), true);
    if (!is_array($datos)) {
        return;
    }

    foreach ($datos as $i => $oferta) {
        $stmt = $pdo->prepare(
            'INSERT INTO ultimo_minuto (sitio, tipo, check_in, check_out, precio, info, activo, orden)
             VALUES (?, ?, ?, ?, ?, ?, 1, ?)'
        );
        $stmt->execute([
            $sitio,
            (string) ($oferta['tipo'] ?? ''),
            fecha_dma_a_ymd((string) ($oferta['checkIn'] ?? '')),
            fecha_dma_a_ymd((string) ($oferta['checkOut'] ?? '')),
            (int) ($oferta['precio'] ?? 0),
            isset($oferta['info']) && $oferta['info'] !== '' ? (string) $oferta['info'] : null,
            $i,
        ]);
    }
}

function seed_banners(PDO $pdo, string $sitio): void
{
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM banners WHERE sitio = ?');
    $stmt->execute([$sitio]);
    if ((int) $stmt->fetchColumn() > 0) {
        return;
    }

    $archivo = $sitio === 'lugra'
        ? __DIR__ . '/seed/banners_lugra.json'
        : __DIR__ . '/seed/banners_inmo.json';
    if (!is_file($archivo)) {
        return;
    }
    $datos = json_decode((string) file_get_contents($archivo), true);
    if (!is_array($datos)) {
        return;
    }

    foreach (['escritorio', 'movil'] as $dispositivo) {
        foreach (($datos[$dispositivo] ?? []) as $i => $banner) {
            $stmt = $pdo->prepare(
                'INSERT INTO banners (sitio, dispositivo, ruta, alt, url, orden, activo) VALUES (?, ?, ?, ?, ?, ?, 1)'
            );
            $stmt->execute([
                $sitio,
                $dispositivo,
                (string) ($banner['ruta'] ?? ''),
                isset($banner['alt']) && $banner['alt'] !== '' ? (string) $banner['alt'] : null,
                isset($banner['url']) && $banner['url'] !== '' ? (string) $banner['url'] : null,
                $i,
            ]);
        }
    }
}

function seed_promos_agencias(PDO $pdo): void
{
    $existe = $pdo->query('SELECT COUNT(*) FROM promos_agencias')->fetchColumn();
    if ((int) $existe > 0) {
        return;
    }

    $archivo = __DIR__ . '/seed/promociones-agencias.json';
    if (!is_file($archivo)) {
        return;
    }
    $datos = json_decode((string) file_get_contents($archivo), true);
    if (!is_array($datos)) {
        return;
    }

    foreach ($datos as $i => $agencia) {
        $stmt = $pdo->prepare(
            'INSERT INTO promos_agencias (imagen, nombre, descripcion, link, orden, activo) VALUES (?, ?, ?, ?, ?, 1)'
        );
        $stmt->execute([
            (string) ($agencia['imagen'] ?? ''),
            (string) ($agencia['nombre'] ?? ''),
            isset($agencia['descripcion']) ? (string) $agencia['descripcion'] : null,
            isset($agencia['link']) && $agencia['link'] !== '' ? (string) $agencia['link'] : null,
            $i,
        ]);
    }
}

function seed_formas_pago(PDO $pdo, string $sitio): void
{
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM formas_pago WHERE sitio = ?');
    $stmt->execute([$sitio]);
    if ((int) $stmt->fetchColumn() > 0) {
        return;
    }

    $archivo = $sitio === 'hotel'
        ? __DIR__ . '/seed/formas-pago.json'
        : __DIR__ . '/seed/formas-pago-inmo.json';
    if (!is_file($archivo)) {
        return;
    }
    $datos = json_decode((string) file_get_contents($archivo), true);
    if (!is_array($datos)) {
        return;
    }

    $bloque = $datos[0] ?? [];
    $stmt = $pdo->prepare('INSERT INTO formas_pago (sitio, nombre) VALUES (?, ?)');
    $stmt->execute([$sitio, (string) ($bloque['nombre'] ?? 'Formas de Pago')]);
    $bloqueId = (int) $pdo->lastInsertId();

    foreach (($bloque['promos'] ?? []) as $i => $promo) {
        $stmt = $pdo->prepare(
            'INSERT INTO formas_pago_promos (forma_pago_id, icono, titulo, descripcion, orden) VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $bloqueId,
            (string) ($promo['icono'] ?? ''),
            (string) ($promo['titulo'] ?? ''),
            isset($promo['descripcion']) ? (string) $promo['descripcion'] : null,
            $i,
        ]);
    }
}

function seed_inmuebles(PDO $pdo, string $sitio): void
{
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM inmuebles WHERE sitio = ?');
    $stmt->execute([$sitio]);
    if ((int) $stmt->fetchColumn() > 0) {
        return;
    }

    $archivo = $sitio === 'alquiler'
        ? __DIR__ . '/seed/inmuebles_alquiler.json'
        : __DIR__ . '/seed/inmuebles_venta.json';
    if (!is_file($archivo)) {
        return;
    }
    $datos = json_decode((string) file_get_contents($archivo), true);
    if (!is_array($datos)) {
        return;
    }

    $categorias = $datos['categorias'] ?? [];

    foreach (($datos['inmuebles'] ?? []) as $i => $inmueble) {
        $indiceCategoria = (int) ($inmueble['categoria'] ?? 1) - 1;
        $nombreCategoria = (string) ($categorias[$indiceCategoria] ?? '1 AMBIENTE');

        $stmt = $pdo->prepare(
            'INSERT INTO inmuebles (sitio, nombre, categoria, camas, banios, carpeta, imagen, direccion, x, y, orden, activo)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)'
        );
        $stmt->execute([
            $sitio,
            (string) ($inmueble['nombre'] ?? ''),
            $nombreCategoria,
            (int) ($inmueble['camas'] ?? 0),
            (int) ($inmueble['banios'] ?? 0),
            (string) ($inmueble['carpeta'] ?? ''),
            (string) ($inmueble['imagen'] ?? ''),
            (string) ($inmueble['localizacion']['direccion'] ?? ''),
            (string) ($inmueble['localizacion']['x'] ?? ''),
            (string) ($inmueble['localizacion']['y'] ?? ''),
            $i,
        ]);
    }
}

function fecha_dma_a_ymd(string $fecha): string
{
    $f = DateTime::createFromFormat('d/m/Y', $fecha);
    return $f ? $f->format('Y-m-d') : '1970-01-01';
}

function seed_unidades(PDO $pdo): void
{
    $base = __DIR__ . '/seed/unidades';
    if (!is_dir($base)) {
        return;
    }

    $archivos = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($base, FilesystemIterator::SKIP_DOTS)
    );
    foreach ($archivos as $archivo) {
        if ($archivo->getFilename() !== 'info_unidad.json') {
            continue;
        }

        $rutaRelativa = $archivo->getPathname();
        $relativo = substr($rutaRelativa, strlen(rtrim($base, '/\\')));
        $relativo = str_replace('\\', '/', $relativo);
        $relativo = preg_replace('#^/+#', '', $relativo);
        $carpeta = preg_replace('#/?info_unidad\.json$#', '', $relativo);

        $stmt = $pdo->prepare('SELECT id FROM unidades WHERE carpeta = ?');
        $stmt->execute([$carpeta]);
        if ($stmt->fetch()) {
            continue;
        }

        $datos = json_decode((string) file_get_contents($archivo->getPathname()), true);
        if (!is_array($datos)) {
            continue;
        }

        $stmt = $pdo->prepare(
            'INSERT INTO unidades (carpeta, nombre, imagen1, imagen2, descripcion, banios, camas, categoria, direccion, venta, activo)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)'
        );
        $stmt->execute([
            $carpeta,
            (string) ($datos['nombre'] ?? ''),
            (string) ($datos['imagen1'] ?? ''),
            (string) ($datos['imagen2'] ?? ''),
            isset($datos['descripcion']) ? (string) $datos['descripcion'] : null,
            (int) ($datos['banios'] ?? 0),
            (int) ($datos['camas'] ?? 0),
            (string) ($datos['categoria'] ?? ''),
            (string) ($datos['direccion'] ?? ''),
            !empty($datos['venta']) ? 1 : 0,
        ]);
        $unidadId = (int) $pdo->lastInsertId();

        foreach (($datos['tarifas'] ?? []) as $i => $tarifa) {
            $stmt = $pdo->prepare(
                'INSERT INTO unidad_tarifas (unidad_id, titulo, desde, hasta, servicios, info, orden)
                 VALUES (?, ?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                $unidadId,
                (string) ($tarifa['titulo'] ?? ''),
                (string) ($tarifa['Desde'] ?? date('Y-m-d')),
                (string) ($tarifa['Hasta'] ?? date('Y-m-d')),
                isset($tarifa['Servicios']) ? (string) $tarifa['Servicios'] : null,
                isset($tarifa['Info']) ? (string) $tarifa['Info'] : null,
                $i,
            ]);
            $tarifaId = (int) $pdo->lastInsertId();

            foreach (($tarifa['promos'] ?? []) as $j => $promo) {
                $precio = 0;
                $matriz = (array) ($promo['precios'] ?? []);
                if (isset($matriz[0][0])) {
                    $precio = (int) $matriz[0][0];
                }
                $stmt = $pdo->prepare(
                    'INSERT INTO unidad_tarifa_promos (tarifa_id, nombre, precio, orden) VALUES (?, ?, ?, ?)'
                );
                $stmt->execute([
                    $tarifaId,
                    (string) ($promo['nombre'] ?? ''),
                    $precio,
                    $j,
                ]);
            }
        }

        foreach (($datos['imagenes'] ?? []) as $i => $imagen) {
            $stmt = $pdo->prepare(
                'INSERT INTO unidad_imagenes (unidad_id, ruta, alt, orden) VALUES (?, ?, ?, ?)'
            );
            $stmt->execute([
                $unidadId,
                (string) ($imagen['ruta'] ?? ''),
                isset($imagen['alt']) && $imagen['alt'] !== '' ? (string) $imagen['alt'] : null,
                $i,
            ]);
        }
    }
}
