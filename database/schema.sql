-- ============================================================
--  emiguzman — Esquema de base de datos
--  Portafolio personal · Ingeniería de TI / Ingeniería de Datos
--  Motor: MySQL 8 / MariaDB 10.4+ (InnoDB, utf8mb4)
-- ============================================================

CREATE DATABASE IF NOT EXISTS `emiguzman`
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `emiguzman`;

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `contact_messages`;
DROP TABLE IF EXISTS `experience_highlights`;
DROP TABLE IF EXISTS `experiences`;
DROP TABLE IF EXISTS `certifications`;
DROP TABLE IF EXISTS `services`;
DROP TABLE IF EXISTS `project_pipeline_steps`;
DROP TABLE IF EXISTS `project_metrics`;
DROP TABLE IF EXISTS `project_technologies`;
DROP TABLE IF EXISTS `projects`;
DROP TABLE IF EXISTS `technologies`;
DROP TABLE IF EXISTS `home_metrics`;
DROP TABLE IF EXISTS `profile`;
DROP TABLE IF EXISTS `admin_users`;

SET FOREIGN_KEY_CHECKS = 1;

-- ------------------------------------------------------------
--  admin_users — acceso al panel de administración
-- ------------------------------------------------------------
CREATE TABLE `admin_users` (
  `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `username`       VARCHAR(60)  NOT NULL,
  `email`          VARCHAR(150) NOT NULL,
  `password_hash`  VARCHAR(255) NOT NULL,
  `display_name`   VARCHAR(120) NOT NULL,
  `last_login_at`  DATETIME     NULL,
  `is_active`      TINYINT(1)   NOT NULL DEFAULT 1,
  `created_at`     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_admin_username` (`username`),
  UNIQUE KEY `uq_admin_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
--  profile — datos del sitio (fila única, id = 1)
-- ------------------------------------------------------------
CREATE TABLE `profile` (
  `id`               TINYINT UNSIGNED NOT NULL DEFAULT 1,
  `full_name`        VARCHAR(120) NOT NULL,
  `role_title`       VARCHAR(160) NOT NULL,
  `headline`         VARCHAR(255) NOT NULL,
  `bio_short`        TEXT         NULL,
  `bio_long`         MEDIUMTEXT   NULL,
  `email`            VARCHAR(150) NOT NULL,
  `phone`            VARCHAR(40)  NULL,
  `location`         VARCHAR(120) NULL,
  `avatar_path`      VARCHAR(255) NULL,
  `cv_path`          VARCHAR(255) NULL,
  `github_url`       VARCHAR(255) NULL,
  `linkedin_url`     VARCHAR(255) NULL,
  `website_url`      VARCHAR(255) NULL,
  `years_experience` TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `available_for_work` TINYINT(1) NOT NULL DEFAULT 1,
  `updated_at`       TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
--  technologies — stack; alimenta los chips de "Sobre mí" y los tags de proyecto
-- ------------------------------------------------------------
CREATE TABLE `technologies` (
  `id`          SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`        VARCHAR(60)  NOT NULL,
  `slug`        VARCHAR(60)  NOT NULL,
  -- El orden del ENUM es el orden en que se ven las categorías en "Sobre mí".
  `category`    ENUM('datos','sistemas','redes','seguridad','cloud','lenguaje','herramienta') NOT NULL DEFAULT 'herramienta',
  `is_featured` TINYINT(1)   NOT NULL DEFAULT 0,  -- reservado: hoy el stack se muestra completo por categoría
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_tech_slug` (`slug`),
  KEY `idx_tech_featured` (`is_featured`, `category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
--  projects — proyectos destacados
-- ------------------------------------------------------------
CREATE TABLE `projects` (
  `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `slug`           VARCHAR(120) NOT NULL,
  `title`          VARCHAR(160) NOT NULL,
  `subtitle`       VARCHAR(200) NULL,
  `summary`        VARCHAR(400) NOT NULL,          -- descripción al compartir el enlace
  `context`        TEXT         NULL,              -- detalle: contexto / problema
  `solution`       TEXT         NULL,              -- detalle: qué construí
  `outcome`        TEXT         NULL,              -- detalle: resultado
  `role`           VARCHAR(120) NULL,
  `client`         VARCHAR(120) NULL,
  `cover_image`    VARCHAR(255) NULL,
  `repo_url`       VARCHAR(255) NULL,
  `demo_url`       VARCHAR(255) NULL,
  `is_featured`    TINYINT(1)   NOT NULL DEFAULT 0,
  `is_published`   TINYINT(1)   NOT NULL DEFAULT 1,
  `has_pipeline`   TINYINT(1)   NOT NULL DEFAULT 0, -- muestra el riel de flujo en el detalle
  `started_on`     DATE         NULL,
  `ended_on`       DATE         NULL,
  `created_at`     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_project_slug` (`slug`),
  KEY `idx_project_published` (`is_published`, `started_on`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
--  project_technologies — N:M proyecto ↔ stack
-- ------------------------------------------------------------
CREATE TABLE `project_technologies` (
  `project_id`    INT UNSIGNED      NOT NULL,
  `technology_id` SMALLINT UNSIGNED NOT NULL,
  PRIMARY KEY (`project_id`, `technology_id`),
  KEY `idx_pt_tech` (`technology_id`),
  CONSTRAINT `fk_pt_project` FOREIGN KEY (`project_id`)    REFERENCES `projects` (`id`)     ON DELETE CASCADE,
  CONSTRAINT `fk_pt_tech`    FOREIGN KEY (`technology_id`) REFERENCES `technologies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
--  project_metrics — números duros del detalle de proyecto
-- ------------------------------------------------------------
CREATE TABLE `project_metrics` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `project_id` INT UNSIGNED NOT NULL,
  `label`      VARCHAR(80)  NOT NULL,
  `value`      VARCHAR(40)  NOT NULL,
  `unit`       VARCHAR(20)  NULL,
  PRIMARY KEY (`id`),
  KEY `idx_pm_project` (`project_id`),
  CONSTRAINT `fk_pm_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
--  project_pipeline_steps — etapas del flujo de datos
--  stage: raw = azul estructural · transform = intermedio · refined = ámbar
-- ------------------------------------------------------------
CREATE TABLE `project_pipeline_steps` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `project_id`  INT UNSIGNED NULL,   -- NULL = pipeline genérico del Inicio
  `label`       VARCHAR(60)  NOT NULL,
  `description` VARCHAR(200) NULL,
  `stage`       ENUM('raw','transform','refined') NOT NULL DEFAULT 'raw',
  PRIMARY KEY (`id`),
  KEY `idx_pps_project` (`project_id`),
  CONSTRAINT `fk_pps_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
--  services — lo que ofrezco, contado para quien no es de sistemas
--
--  Es la fuente del carrusel de "Servicios". El texto de esta tabla lo lee
--  un cliente potencial, no un colega: nada de jerga técnica aquí (el stack
--  vive en `technologies` y el detalle duro, en `projects`).
--
--  `deliverables` va en JSON en vez de una tabla hija: son 2-4 viñetas
--  cortas que sólo existen dentro de su tarjeta, nunca se consultan ni se
--  ordenan por separado.
-- ------------------------------------------------------------
CREATE TABLE `services` (
  `id`           SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `slug`         VARCHAR(80)  NOT NULL,
  `title`        VARCHAR(120) NOT NULL,
  `tagline`      VARCHAR(160) NULL,          -- frase corta de apoyo
  `description`  VARCHAR(500) NOT NULL,      -- qué hago, en palabras del cliente
  `deliverables` JSON         NULL,          -- ["Entregable 1", "Entregable 2"]
  `outcome`      VARCHAR(200) NULL,          -- el "para qué te sirve"
  `timeframe`    VARCHAR(60)  NULL,          -- tiempo típico: "2 a 4 semanas"
  `icon`         VARCHAR(40)  NOT NULL DEFAULT 'spark', -- clave del SVG en la vista
  `is_featured`  TINYINT(1)   NOT NULL DEFAULT 0,
  `is_published` TINYINT(1)   NOT NULL DEFAULT 1,
  `created_at`   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_service_slug` (`slug`),
  KEY `idx_service_published` (`is_published`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
--  certifications
-- ------------------------------------------------------------
CREATE TABLE `certifications` (
  `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title`           VARCHAR(180) NOT NULL,
  `issuer`          VARCHAR(120) NOT NULL,
  `credential_id`   VARCHAR(120) NULL,
  `credential_url`  VARCHAR(255) NULL,
  `badge_image`     VARCHAR(255) NULL,
  `description`     VARCHAR(400) NULL,
  `issued_on`       DATE         NOT NULL,
  `expires_on`      DATE         NULL,
  `is_published`    TINYINT(1)   NOT NULL DEFAULT 1,
  `created_at`      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_cert_published` (`is_published`, `issued_on`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
--  experiences — timeline laboral
-- ------------------------------------------------------------
CREATE TABLE `experiences` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `company`       VARCHAR(140) NOT NULL,
  `role`          VARCHAR(140) NOT NULL,
  `location`      VARCHAR(120) NULL,
  `employment_type` ENUM('tiempo_completo','medio_tiempo','freelance','practicas','contrato') NOT NULL DEFAULT 'tiempo_completo',
  `company_url`   VARCHAR(255) NULL,
  `summary`       TEXT         NULL,
  `started_on`    DATE         NOT NULL,
  `ended_on`      DATE         NULL,          -- NULL = sigue en marcha (no se guarda aparte)
  `is_published`  TINYINT(1)   NOT NULL DEFAULT 1,
  `created_at`    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_exp_published` (`is_published`, `started_on`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
--  experience_highlights — bullets de logros por puesto
-- ------------------------------------------------------------
CREATE TABLE `experience_highlights` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `experience_id` INT UNSIGNED NOT NULL,
  `description`   VARCHAR(400) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_eh_experience` (`experience_id`),
  CONSTRAINT `fk_eh_experience` FOREIGN KEY (`experience_id`) REFERENCES `experiences` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
--  home_metrics — sin uso en la landing actual; se conserva
-- ------------------------------------------------------------
CREATE TABLE `home_metrics` (
  `id`         SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `label`      VARCHAR(80) NOT NULL,
  `value`      VARCHAR(20) NOT NULL,
  `unit`       VARCHAR(20) NULL,
  `caption`    VARCHAR(120) NULL,
  `is_published` TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
--  contact_messages — bandeja del formulario de contacto
-- ------------------------------------------------------------
CREATE TABLE `contact_messages` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`       VARCHAR(120) NOT NULL,
  `email`      VARCHAR(150) NOT NULL,
  `subject`    VARCHAR(180) NULL,
  `message`    TEXT         NOT NULL,
  `ip_address` VARCHAR(45)  NULL,
  `is_read`    TINYINT(1)   NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_cm_unread` (`is_read`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
