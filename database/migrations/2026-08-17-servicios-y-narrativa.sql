-- ============================================================
--  2026-08-17 · Sección "Servicios" + narrativa para no técnicos
--
--  Para una base ya poblada: crea la tabla `services`, la llena y reescribe
--  el texto que lee un visitante sin conocimientos de sistemas.
--
--  En una instalación nueva no hace falta: schema.sql y seeds.sql ya
--  incluyen todo esto. Es idempotente, se puede correr más de una vez.
--
--  Aplicar (el charset del cliente NO es opcional: sin él los acentos se
--  guardan mal y se leen como "PÃ¡ginas"):
--
--    mysql --default-character-set=utf8mb4 -u root -p emiguzman \
--      < database/migrations/2026-08-17-servicios-y-narrativa.sql
--
--  Lo que NO toca, a propósito: experiencias, logros, certificaciones,
--  tecnologías y el bloque "Qué construí" de cada proyecto. Ahí el lenguaje
--  técnico es el correcto y se queda igual.
-- ============================================================

USE `emiguzman`;

-- ------------------------------------------------------------
--  1. Tabla de servicios
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `services` (
  `id`           SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `slug`         VARCHAR(80)  NOT NULL,
  `title`        VARCHAR(120) NOT NULL,
  `tagline`      VARCHAR(160) NULL,
  `description`  VARCHAR(500) NOT NULL,
  `deliverables` JSON         NULL,
  `outcome`      VARCHAR(200) NULL,
  `timeframe`    VARCHAR(60)  NULL,
  `icon`         VARCHAR(40)  NOT NULL DEFAULT 'spark',
  `is_featured`  TINYINT(1)   NOT NULL DEFAULT 0,
  `is_published` TINYINT(1)   NOT NULL DEFAULT 1,
  `sort_order`   SMALLINT     NOT NULL DEFAULT 0,
  `created_at`   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_service_slug` (`slug`),
  KEY `idx_service_published` (`is_published`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
--  2. Contenido del carrusel
-- ------------------------------------------------------------
INSERT INTO `services`
(`slug`, `title`, `tagline`, `description`, `deliverables`, `outcome`, `timeframe`, `icon`, `is_featured`, `sort_order`)
VALUES
('paginas-web', 'Páginas web que sí traen clientes',
 'Tu negocio explicado en diez segundos',
 'Diseño y programo tu sitio desde cero: rápido, que se vea bien en el celular y que le diga a quien entra qué haces y por qué vale la pena buscarte. Nada de plantillas que se ven igual a las de todos.',
 JSON_ARRAY('Diseño hecho a tu medida', 'Se ve bien en celular y computadora', 'Formulario de contacto que sí te llega'),
 'Que quien te busque en internet te encuentre y te escriba.',
 '2 a 4 semanas', 'browser', 1, 10),

('sistemas-a-medida', 'Sistemas para tu día a día',
 'Cuando la hoja de cálculo ya no da para más',
 'Programo el sistema que tu equipo usa todos los días: registrar clientes, controlar inventario, dar seguimiento a pedidos. Hecho a la medida de cómo trabajas tú, no al revés.',
 JSON_ARRAY('Un usuario y permisos por persona', 'Pantallas simples, sin manual de 40 hojas', 'Acompañamiento a tu equipo las primeras semanas'),
 'Todos trabajan sobre la misma información, sin archivos sueltos.',
 '1 a 3 meses', 'layers', 1, 20),

('automatizacion', 'Automatizar lo repetitivo',
 'Lo que hoy toma horas, hecho solo',
 'El reporte que alguien arma a mano cada lunes, los correos que se mandan uno por uno, la información que se copia de un lado a otro: todo eso puede hacerse solo y sin errores de dedo.',
 JSON_ARRAY('Reviso contigo cómo se hace hoy', 'Lo dejo corriendo solo y a tiempo', 'Te aviso si algún día falla'),
 'Tu equipo deja de copiar y pegar y vuelve a lo suyo.',
 '1 a 3 semanas', 'bolt', 1, 30),

('orden-en-tu-informacion', 'Orden en tu información',
 'Tus números en un solo lugar',
 'Junto lo que hoy vive disperso —el sistema de ventas, las hojas de cálculo, lo que sigue en papel— en un solo lugar confiable, para que todos lean la misma cifra.',
 JSON_ARRAY('Reviso dónde está hoy cada dato', 'Un solo lugar donde consultarlo', 'Limpieza de duplicados y errores viejos'),
 'Se acaban las juntas que empiezan discutiendo de dónde salió el número.',
 '3 a 6 semanas', 'boxes', 0, 40),

('reportes-y-tableros', 'Reportes que se entienden solos',
 'Saber cómo va el negocio sin pedirle nada a nadie',
 'Armo la pantalla donde ves ventas, gastos o lo que necesites medir: se actualiza sola, se lee desde el celular y está escrita en palabras normales.',
 JSON_ARRAY('Tú eliges qué se mide', 'Se actualiza sin que nadie lo toque', 'Se lee desde el celular'),
 'Decides con los números de hoy, no con el reporte del mes pasado.',
 '2 a 4 semanas', 'chart', 0, 50),

('soporte', 'Soporte y acompañamiento',
 'Alguien que conteste cuando algo se cae',
 'Me hago cargo de que lo que ya tienes siga funcionando: respaldos, actualizaciones, seguridad y una persona a quien llamarle cuando algo no prende.',
 JSON_ARRAY('Respaldos automáticos de tu información', 'Revisiones periódicas antes de que falle', 'Atención directa cuando algo se rompe'),
 'Si algo falla, ya hay quien lo levante — y no eres tú.',
 'Mensual', 'shield', 0, 60)
ON DUPLICATE KEY UPDATE
  `title`        = VALUES(`title`),
  `tagline`      = VALUES(`tagline`),
  `description`  = VALUES(`description`),
  `deliverables` = VALUES(`deliverables`),
  `outcome`      = VALUES(`outcome`),
  `timeframe`    = VALUES(`timeframe`),
  `icon`         = VALUES(`icon`),
  `is_featured`  = VALUES(`is_featured`),
  `sort_order`   = VALUES(`sort_order`);

-- ------------------------------------------------------------
--  3. Perfil — la presentación deja de hablar de pipelines
-- ------------------------------------------------------------
UPDATE `profile` SET
  `headline`  = 'Construyo software y pongo orden en la información para que tu negocio deje de perder tiempo.',
  `bio_short` = 'Ingeniero de TI. Hago dos cosas: construyo el software que un negocio necesita para trabajar mejor, y pongo en orden la información que ya tiene para que sirva de algo.',
  `bio_long`  = 'Trabajo en la parte que casi nadie ve: el camino que recorre un dato desde que se genera hasta que alguien toma una decisión con él. En la práctica eso significa sistemas que funcionan sin que nadie los esté empujando, y números en los que todos confían porque salen del mismo lugar.\n\nVengo del lado de la infraestructura, así que me importa tanto que el sistema no se caiga como que el reporte final se entienda. Si algo se rompe un domingo, lo levanto; si un número no cuadra, sé dónde buscarlo. Prefiero explicarte en tus palabras qué voy a hacer antes de escribir una sola línea de código.'
WHERE `id` = 1;

-- ------------------------------------------------------------
--  4. Proyectos — la tarjeta y la historia se cuentan sin jerga.
--     `solution` ("Qué construí") no se toca: ahí el detalle técnico
--     es justamente la prueba de que sé hacerlo.
-- ------------------------------------------------------------
UPDATE `projects` SET
  `title`    = 'La venta de 120 tiendas en un solo lugar',
  `subtitle` = 'De la caja registradora al reporte de dirección, en 15 minutos',
  `summary`  = 'Junté lo que vendían 120 tiendas en un solo lugar, para que dirección viera el día anterior sin esperar a que alguien lo armara a mano.',
  `context`  = 'Cada tienda mandaba su venta por correo en una hoja de cálculo. El reporte del día anterior llegaba a media mañana y cada área tenía su propia versión del mismo número.',
  `outcome`  = 'El reporte diario pasó de tardar 10 horas a estar listo en 15 minutos, y las tres áreas que discutían cifras distintas hoy leen la misma.'
WHERE `slug` = 'plataforma-datos-retail';

UPDATE `projects` SET
  `title`    = 'Avisar antes de que se pare la máquina',
  `subtitle` = '4,500 sensores de planta vigilados al mismo tiempo',
  `summary`  = 'Puse a la planta a avisar sola cuando una máquina empieza a calentarse, en vez de enterarse cuando ya se detuvo.',
  `context`  = 'Los sensores guardaban todo, pero nadie los miraba hasta que algo ya se había roto. El diagnóstico siempre llegaba tarde.'
WHERE `slug` = 'pipeline-streaming-iot';

UPDATE `projects` SET
  `title`    = 'Detectar el error antes que el cliente',
  `subtitle` = 'Un vigilante que no se distrae',
  `summary`  = 'Puse a vigilar solas las 30 tablas más importantes: si un número deja de llegar o llega raro, el equipo se entera en minutos y no en días.',
  `context`  = 'Los errores se descubrían cuando alguien notaba un reporte vacío. No había forma de saber cuándo se rompió ni cuánto tiempo llevaba así.',
  `outcome`  = 'Enterarse de un problema pasó de tardar 2 días a tardar 20 minutos.'
WHERE `slug` = 'observabilidad-datos';

UPDATE `projects` SET
  `title`    = 'Mudanza a la nube sin cerrar un solo día',
  `subtitle` = '11 años de información movidos sin parar la operación',
  `summary`  = 'Cambié el servidor viejo de la empresa por uno en la nube, con toda la historia intacta y sin pedirle a nadie que dejara de trabajar.',
  `context`  = 'El servidor de la empresa ya no tenía soporte y cada mantenimiento obligaba a detener la operación un fin de semana completo.',
  `outcome`  = 'Cuatro horas de corte un domingo, ni un registro perdido y 38% menos de costo cada mes.'
WHERE `slug` = 'migracion-onprem-cloud';

UPDATE `projects` SET
  `title`    = 'Un solo significado para cada número',
  `summary`  = 'Puse por escrito qué significa cada uno de los 60 indicadores del negocio, para que finanzas, comercial y operaciones dejaran de calcularlos distinto.',
  `context`  = 'Cada área calculaba sus propios indicadores con reglas distintas. La misma junta terminaba con tres cifras de ingreso para el mismo mes.'
WHERE `slug` = 'capa-semantica-bi';
