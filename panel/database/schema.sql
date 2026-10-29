-- Panel Lugra — Esquema de base de datos (MySQL 8 / MariaDB)
-- Se ejecuta desde instalar.php

CREATE TABLE IF NOT EXISTS usuarios (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    usuario VARCHAR(50) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_usuarios_usuario (usuario)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS tarifas_habitaciones (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nombre VARCHAR(60) NOT NULL,
    orden INT NOT NULL DEFAULT 0,
    PRIMARY KEY (id)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS tarifas_promociones (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    titulo VARCHAR(200) NOT NULL,
    desde DATE NOT NULL,
    hasta DATE NOT NULL,
    servicios TEXT NULL,
    info TEXT NULL,
    imagenes JSON NULL,
    sub_headers JSON NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    orden INT NOT NULL DEFAULT 0,
    PRIMARY KEY (id)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS tarifas_promos (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    promocion_id INT UNSIGNED NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    precios JSON NOT NULL,
    orden INT NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    KEY idx_promos_promocion (promocion_id),
    CONSTRAINT fk_promos_promocion
        FOREIGN KEY (promocion_id)
        REFERENCES tarifas_promociones (id)
        ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ultimo_minuto (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    sitio ENUM ('hotel', 'inmobiliaria') NOT NULL DEFAULT 'hotel',
    tipo VARCHAR(100) NOT NULL,
    check_in DATE NOT NULL,
    check_out DATE NOT NULL,
    precio INT UNSIGNED NOT NULL,
    info VARCHAR(255) NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    orden INT NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    KEY idx_ultimo_minuto_sitio (sitio)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS banners (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    sitio ENUM ('lugra', 'inmobiliaria') NOT NULL,
    dispositivo ENUM ('escritorio', 'movil') NOT NULL,
    ruta VARCHAR(255) NOT NULL,
    alt VARCHAR(255) NULL,
    url VARCHAR(255) NULL,
    orden INT NOT NULL DEFAULT 0,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (id),
    KEY idx_banners_sitio (sitio)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS promos_agencias (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    imagen VARCHAR(255) NOT NULL,
    nombre VARCHAR(150) NOT NULL,
    descripcion TEXT NULL,
    link VARCHAR(255) NULL,
    orden INT NOT NULL DEFAULT 0,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (id)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS formas_pago (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    sitio ENUM ('hotel', 'inmobiliaria') NOT NULL,
    nombre VARCHAR(100) NOT NULL DEFAULT 'Formas de Pago',
    PRIMARY KEY (id),
    UNIQUE KEY uq_formas_pago_sitio (sitio)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS formas_pago_promos (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    forma_pago_id INT UNSIGNED NOT NULL,
    icono VARCHAR(60) NOT NULL,
    titulo VARCHAR(150) NOT NULL,
    descripcion TEXT NULL,
    orden INT NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    KEY idx_formas_pago_promos (forma_pago_id),
    CONSTRAINT fk_formas_pago_promos
        FOREIGN KEY (forma_pago_id)
        REFERENCES formas_pago (id)
        ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS inmuebles (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    sitio ENUM ('alquiler', 'venta') NOT NULL,
    nombre VARCHAR(120) NOT NULL,
    categoria VARCHAR(60) NOT NULL,
    camas INT NOT NULL DEFAULT 0,
    banios INT NOT NULL DEFAULT 0,
    carpeta VARCHAR(120) NOT NULL,
    imagen VARCHAR(120) NOT NULL,
    direccion VARCHAR(200) NOT NULL,
    x VARCHAR(30) NOT NULL,
    y VARCHAR(30) NOT NULL,
    orden INT NOT NULL DEFAULT 0,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (id),
    KEY idx_inmuebles_sitio (sitio)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS unidades (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    carpeta VARCHAR(120) NOT NULL,
    nombre VARCHAR(120) NOT NULL,
    imagen1 VARCHAR(255) NOT NULL DEFAULT '',
    imagen2 VARCHAR(255) NOT NULL DEFAULT '',
    descripcion TEXT NULL,
    banios INT NOT NULL DEFAULT 0,
    camas INT NOT NULL DEFAULT 0,
    categoria VARCHAR(60) NOT NULL DEFAULT '',
    direccion VARCHAR(200) NOT NULL DEFAULT '',
    venta TINYINT(1) NOT NULL DEFAULT 0,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (id),
    UNIQUE KEY uq_unidades_carpeta (carpeta)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS unidad_tarifas (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    unidad_id INT UNSIGNED NOT NULL,
    titulo VARCHAR(200) NOT NULL,
    desde DATE NOT NULL,
    hasta DATE NOT NULL,
    servicios TEXT NULL,
    info TEXT NULL,
    orden INT NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    KEY idx_unidad_tarifas (unidad_id),
    CONSTRAINT fk_unidad_tarifas
        FOREIGN KEY (unidad_id)
        REFERENCES unidades (id)
        ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS unidad_tarifa_promos (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    tarifa_id INT UNSIGNED NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    precio INT UNSIGNED NOT NULL DEFAULT 0,
    orden INT NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    KEY idx_unidad_tarifa_promos (tarifa_id),
    CONSTRAINT fk_unidad_tarifa_promos
        FOREIGN KEY (tarifa_id)
        REFERENCES unidad_tarifas (id)
        ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS unidad_imagenes (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    unidad_id INT UNSIGNED NOT NULL,
    ruta VARCHAR(255) NOT NULL,
    alt VARCHAR(255) NULL,
    orden INT NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    KEY idx_unidad_imagenes (unidad_id),
    CONSTRAINT fk_unidad_imagenes
        FOREIGN KEY (unidad_id)
        REFERENCES unidades (id)
        ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS resenas_cache (
    sitio VARCHAR(20) NOT NULL,
    payload LONGTEXT NOT NULL,
    actualizado_en DATETIME NOT NULL,
    PRIMARY KEY (sitio)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;
