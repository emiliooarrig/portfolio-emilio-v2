-- ============================================================
--  emiguzman — Datos de ejemplo
--  TODO: reemplazar por información real desde el panel de admin.
-- ============================================================

USE `emiguzman`;

SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE `contact_messages`;
TRUNCATE TABLE `experience_highlights`;
TRUNCATE TABLE `experiences`;
TRUNCATE TABLE `certifications`;
TRUNCATE TABLE `services`;
TRUNCATE TABLE `project_pipeline_steps`;
TRUNCATE TABLE `project_metrics`;
TRUNCATE TABLE `project_technologies`;
TRUNCATE TABLE `projects`;
TRUNCATE TABLE `technologies`;
TRUNCATE TABLE `home_metrics`;
TRUNCATE TABLE `profile`;
TRUNCATE TABLE `admin_users`;
SET FOREIGN_KEY_CHECKS = 1;

-- ------------------------------------------------------------
--  admin_users — usuario: admin · contraseña: admin123
--  CAMBIAR LA CONTRASEÑA ANTES DE PUBLICAR.
-- ------------------------------------------------------------
INSERT INTO `admin_users` (`username`, `email`, `password_hash`, `display_name`) VALUES
('admin', 'emilioag2703@gmail.com', '$2y$12$6tvEhto/0QAoL9/Zed00PeVqg.J/IP2aX1Tz7ZdsNj0Qk4j1uxziK', 'Emilio Guzmán');

-- ------------------------------------------------------------
--  profile
-- ------------------------------------------------------------
INSERT INTO `profile`
(`id`, `full_name`, `role_title`, `headline`, `bio_short`, `bio_long`, `email`, `phone`, `location`,
 `avatar_path`, `cv_path`, `github_url`, `linkedin_url`, `website_url`, `years_experience`, `available_for_work`)
VALUES
(1,
 'Emilio Guzmán',
 'Ingeniero de TI',
 'Sistemas, datos e infraestructura que funcionan, y que el resto del equipo puede entender.',
 'Ingeniero de TI. Hago dos cosas: construyo el software que un negocio necesita para trabajar mejor, y pongo en orden la información que ya tiene para que sirva de algo.',
 'Trabajo en la parte que casi nadie ve: el camino que recorre un dato desde que se genera hasta que alguien toma una decisión con él. En la práctica eso significa sistemas que funcionan sin que nadie los esté empujando, y números en los que todos confían porque salen del mismo lugar.\n\nVengo del lado de la infraestructura, así que me importa tanto que el sistema no se caiga como que el reporte final se entienda. Si algo se rompe un domingo, lo levanto; si un número no cuadra, sé dónde buscarlo. Prefiero explicarte en tus palabras qué voy a hacer antes de escribir una sola línea de código.',
 'emilioag2703@gmail.com',
 '+52 000 000 0000',
 'Guadalajara, México',
 NULL,
 '/assets/docs/cv-emilio-guzman.pdf',
 'https://github.com/emilioguzman',
 'https://www.linkedin.com/in/emilioguzman',
 NULL,
 5,
 1);

-- ------------------------------------------------------------
--  technologies
-- ------------------------------------------------------------
INSERT INTO `technologies` (`id`, `name`, `slug`, `category`, `is_featured`) VALUES
(1,  'Python',        'python',        'lenguaje',      1),
(2,  'SQL',           'sql',           'lenguaje',      1),
(3,  'PHP',           'php',           'lenguaje',      0),
(4,  'PostgreSQL',    'postgresql',    'datos',         1),
(5,  'MySQL',         'mysql',         'datos',         1),
(6,  'BigQuery',      'bigquery',      'datos',         1),
(7,  'Apache Airflow','airflow',       'datos',         1),
(8,  'dbt',           'dbt',           'datos',         1),
(9,  'Apache Spark',  'spark',         'datos',         0),
(10, 'Kafka',         'kafka',         'datos',         0),
(11, 'Google Cloud',  'gcp',           'cloud',         1),
(12, 'AWS',           'aws',           'cloud',         0),
(13, 'Docker',        'docker',        'herramienta',   1),
(14, 'Terraform',     'terraform',     'herramienta',   0),
(15, 'Power BI',      'power-bi',      'datos',         1),
(16, 'Looker Studio', 'looker-studio', 'datos',         0),
(17, 'Git',           'git',           'herramienta',   0),
(18, 'Pandas',        'pandas',        'herramienta',   0);

-- ------------------------------------------------------------
--  home_metrics
-- ------------------------------------------------------------
INSERT INTO `home_metrics` (`label`, `value`, `unit`, `caption`) VALUES
('Años en datos',        '5',    '+',    'Del ETL nocturno al streaming'),
('Pipelines en producción','24',  '',     'Orquestados y monitoreados'),
('Registros procesados', '180',  'M/mes','Batch + near real-time'),
('Tiempo de reporte',    '-72',  '%',    'De 8 h a 2 h en cierre mensual');

-- ------------------------------------------------------------
--  projects
-- ------------------------------------------------------------
INSERT INTO `projects`
(`id`, `slug`, `title`, `subtitle`, `summary`, `context`, `solution`, `outcome`, `role`, `client`,
 `cover_image`, `repo_url`, `demo_url`, `is_featured`, `is_published`, `has_pipeline`,
 `started_on`, `ended_on`)
VALUES
(1, 'plataforma-datos-retail', 'La venta de 120 tiendas en un solo lugar',
 'De la caja registradora al reporte de dirección, en 15 minutos',
 'Junté lo que vendían 120 tiendas en un solo lugar, para que dirección viera el día anterior sin esperar a que alguien lo armara a mano.',
 'Cada tienda mandaba su venta por correo en una hoja de cálculo. El reporte del día anterior llegaba a media mañana y cada área tenía su propia versión del mismo número.',
 'Construí ingesta incremental desde los POS hacia BigQuery con Airflow, modelado dimensional en dbt (ventas, inventario, mermas) y pruebas de calidad automáticas en cada corrida. La capa semántica quedó como fuente única para Power BI.',
 'El reporte diario pasó de tardar 10 horas a estar listo en 15 minutos, y las tres áreas que discutían cifras distintas hoy leen la misma.',
 'Data Engineer (líder técnico)', 'Cadena retail nacional',
 NULL, 'https://github.com/emilioguzman/retail-data-platform', NULL,
 1, 1, 1, '2024-02-01', '2024-11-30'),

(2, 'pipeline-streaming-iot', 'Avisar antes de que se pare la máquina',
 '4,500 sensores de planta vigilados al mismo tiempo',
 'Puse a la planta a avisar sola cuando una máquina empieza a calentarse, en vez de enterarse cuando ya se detuvo.',
 'Los sensores guardaban todo, pero nadie los miraba hasta que algo ya se había roto. El diagnóstico siempre llegaba tarde.',
 'Diseñé el flujo Kafka → Spark Structured Streaming → almacenamiento columnar, con ventanas móviles para detección de desviaciones y alertas al equipo de mantenimiento.',
 'Las alertas se adelantaron un promedio de 40 minutos a la falla; dos paros de línea evitados en el primer trimestre.',
 'Data Engineer', 'Manufactura industrial',
 NULL, NULL, NULL,
 1, 1, 1, '2023-05-01', '2023-12-15'),

(3, 'observabilidad-datos', 'Detectar el error antes que el cliente',
 'Un vigilante que no se distrae',
 'Puse a vigilar solas las 30 tablas más importantes: si un número deja de llegar o llega raro, el equipo se entera en minutos y no en días.',
 'Los errores se descubrían cuando alguien notaba un reporte vacío. No había forma de saber cuándo se rompió ni cuánto tiempo llevaba así.',
 'Implementé chequeos de frescura, volumen esperado y deriva de esquema como DAGs de Airflow, con severidad por tabla y una bitácora consultable de cada incidente.',
 'Enterarse de un problema pasó de tardar 2 días a tardar 20 minutos.',
 'Data Engineer', 'Proyecto interno',
 NULL, 'https://github.com/emilioguzman/data-observability', NULL,
 1, 1, 0, '2024-03-01', NULL),

(4, 'migracion-onprem-cloud', 'Mudanza a la nube sin cerrar un solo día',
 '11 años de información movidos sin parar la operación',
 'Cambié el servidor viejo de la empresa por uno en la nube, con toda la historia intacta y sin pedirle a nadie que dejara de trabajar.',
 'El servidor de la empresa ya no tenía soporte y cada mantenimiento obligaba a detener la operación un fin de semana completo.',
 'Planeé la migración por dominios, con carga histórica en paralelo, doble escritura durante la transición y scripts de reconciliación por conteo y checksum.',
 'Cuatro horas de corte un domingo, ni un registro perdido y 38% menos de costo cada mes.',
 'Ingeniero de TI', 'Sector financiero',
 NULL, NULL, NULL,
 0, 1, 0, '2022-08-01', '2023-03-31'),

(5, 'capa-semantica-bi', 'Un solo significado para cada número',
 'Un solo lugar donde vive la definición de "cliente activo"',
 'Puse por escrito qué significa cada uno de los 60 indicadores del negocio, para que finanzas, comercial y operaciones dejaran de calcularlos distinto.',
 'Cada área calculaba sus propios indicadores con reglas distintas. La misma junta terminaba con tres cifras de ingreso para el mismo mes.',
 'Documenté y modelé cada métrica en dbt con pruebas y linaje visible, más un diccionario navegable para usuarios de negocio.',
 'Las juntas de resultados dejaron de empezar con una discusión sobre de dónde salió el número.',
 'Analytics Engineer', 'Retail y servicios',
 NULL, NULL, NULL,
 0, 1, 0, '2023-01-10', '2023-06-30');

-- ------------------------------------------------------------
--  project_technologies
-- ------------------------------------------------------------
INSERT INTO `project_technologies` (`project_id`, `technology_id`) VALUES
(1, 6), (1, 7), (1, 8), (1, 1), (1, 15),
(2, 10), (2, 9), (2, 1), (2, 11),
(3, 7), (3, 1), (3, 2), (3, 13),
(4, 4), (4, 2), (4, 14), (4, 12),
(5, 8), (5, 2), (5, 16);

-- ------------------------------------------------------------
--  project_metrics
-- ------------------------------------------------------------
INSERT INTO `project_metrics` (`project_id`, `label`, `value`, `unit`) VALUES
(1, 'Tiendas integradas',    '120',  ''),
(1, 'Latencia de reporte',   '15',   'min'),
(1, 'Modelos dbt',           '64',   ''),
(2, 'Sensores en línea',     '4,500',''),
(2, 'Eventos por minuto',    '90',   'K'),
(2, 'Adelanto de alerta',    '40',   'min'),
(3, 'Tablas monitoreadas',   '32',   ''),
(3, 'Detección media',       '20',   'min'),
(4, 'Histórico migrado',     '11',   'años'),
(4, 'Ventana de corte',      '4',    'h'),
(4, 'Ahorro en infra',       '-38',  '%'),
(5, 'Métricas estandarizadas','60',  '');

-- ------------------------------------------------------------
--  project_pipeline_steps
--  project_id NULL = pipeline genérico mostrado en el Inicio
-- ------------------------------------------------------------
INSERT INTO `project_pipeline_steps` (`project_id`, `label`, `description`, `stage`) VALUES
(NULL, 'Ingesta',      'APIs, POS, sensores, archivos planos',       'raw'),
(NULL, 'Staging',      'Datos crudos versionados e inmutables',      'raw'),
(NULL, 'Transformación','Modelado dimensional y pruebas de calidad', 'transform'),
(NULL, 'Capa semántica','Métricas con una sola definición',          'transform'),
(NULL, 'Decisión',     'Tableros y alertas que alguien usa',         'refined'),

(1, 'POS de tienda',      'Extracción incremental cada 15 min',      'raw'),
(1, 'Landing BigQuery',   'Particionado por fecha de operación',     'raw'),
(1, 'dbt · staging',      'Normalización y deduplicado',             'transform'),
(1, 'dbt · marts',        'Ventas, inventario y mermas',             'transform'),
(1, 'Power BI',           'Tablero directivo de cierre diario',      'refined'),

(2, 'Sensores',           '4,500 dispositivos, 90K eventos/min',     'raw'),
(2, 'Kafka',              'Tópicos por línea de producción',         'raw'),
(2, 'Spark Streaming',    'Ventanas móviles de 5 minutos',           'transform'),
(2, 'Detección',          'Umbrales dinámicos por sensor',           'transform'),
(2, 'Alerta a planta',    'Notificación al equipo de mantenimiento', 'refined');

-- ------------------------------------------------------------
--  services — carrusel de "Servicios"
--  Regla de escritura: lo lee alguien que no sabe de sistemas. Cero jerga,
--  cero nombres de herramientas; el resultado antes que el método.
-- ------------------------------------------------------------
INSERT INTO `services`
(`slug`, `title`, `tagline`, `description`, `deliverables`, `outcome`, `timeframe`, `icon`, `is_featured`)
VALUES
('paginas-web', 'Páginas web que sí traen clientes',
 'Tu negocio explicado en diez segundos',
 'Diseño y programo tu sitio desde cero: rápido, que se vea bien en el celular y que le diga a quien entra qué haces y por qué vale la pena buscarte. Nada de plantillas que se ven igual a las de todos.',
 JSON_ARRAY('Diseño hecho a tu medida', 'Se ve bien en celular y computadora', 'Formulario de contacto que sí te llega'),
 'Que quien te busque en internet te encuentre y te escriba.',
 '2 a 4 semanas', 'browser', 1),

('sistemas-a-medida', 'Sistemas para tu día a día',
 'Cuando la hoja de cálculo ya no da para más',
 'Programo el sistema que tu equipo usa todos los días: registrar clientes, controlar inventario, dar seguimiento a pedidos. Hecho a la medida de cómo trabajas tú, no al revés.',
 JSON_ARRAY('Un usuario y permisos por persona', 'Pantallas simples, sin manual de 40 hojas', 'Acompañamiento a tu equipo las primeras semanas'),
 'Todos trabajan sobre la misma información, sin archivos sueltos.',
 '1 a 3 meses', 'layers', 1),

('automatizacion', 'Automatizar lo repetitivo',
 'Lo que hoy toma horas, hecho solo',
 'El reporte que alguien arma a mano cada lunes, los correos que se mandan uno por uno, la información que se copia de un lado a otro: todo eso puede hacerse solo y sin errores de dedo.',
 JSON_ARRAY('Reviso contigo cómo se hace hoy', 'Lo dejo corriendo solo y a tiempo', 'Te aviso si algún día falla'),
 'Tu equipo deja de copiar y pegar y vuelve a lo suyo.',
 '1 a 3 semanas', 'bolt', 1),

('orden-en-tu-informacion', 'Orden en tu información',
 'Tus números en un solo lugar',
 'Junto lo que hoy vive disperso —el sistema de ventas, las hojas de cálculo, lo que sigue en papel— en un solo lugar confiable, para que todos lean la misma cifra.',
 JSON_ARRAY('Reviso dónde está hoy cada dato', 'Un solo lugar donde consultarlo', 'Limpieza de duplicados y errores viejos'),
 'Se acaban las juntas que empiezan discutiendo de dónde salió el número.',
 '3 a 6 semanas', 'boxes', 0),

('reportes-y-tableros', 'Reportes que se entienden solos',
 'Saber cómo va el negocio sin pedirle nada a nadie',
 'Armo la pantalla donde ves ventas, gastos o lo que necesites medir: se actualiza sola, se lee desde el celular y está escrita en palabras normales.',
 JSON_ARRAY('Tú eliges qué se mide', 'Se actualiza sin que nadie lo toque', 'Se lee desde el celular'),
 'Decides con los números de hoy, no con el reporte del mes pasado.',
 '2 a 4 semanas', 'chart', 0),

('soporte', 'Soporte y acompañamiento',
 'Alguien que conteste cuando algo se cae',
 'Me hago cargo de que lo que ya tienes siga funcionando: respaldos, actualizaciones, seguridad y una persona a quien llamarle cuando algo no prende.',
 JSON_ARRAY('Respaldos automáticos de tu información', 'Revisiones periódicas antes de que falle', 'Atención directa cuando algo se rompe'),
 'Si algo falla, ya hay quien lo levante — y no eres tú.',
 'Mensual', 'shield', 0);

-- ------------------------------------------------------------
--  certifications
-- ------------------------------------------------------------
INSERT INTO `certifications`
(`title`, `issuer`, `credential_id`, `credential_url`, `badge_image`, `description`, `issued_on`, `expires_on`)
VALUES
('Professional Data Engineer', 'Google Cloud', 'GCP-PDE-000000', 'https://www.credential.net/', NULL,
 'Diseño de sistemas de datos, pipelines y modelos operables en Google Cloud.', '2024-06-18', '2027-06-18'),
('Azure Data Fundamentals (DP-900)', 'Microsoft', 'MS-DP900-000000', 'https://learn.microsoft.com/', NULL,
 'Fundamentos de datos relacionales, no relacionales y analítica en Azure.', '2023-11-02', NULL),
('dbt Analytics Engineering', 'dbt Labs', 'DBT-AE-000000', 'https://www.getdbt.com/', NULL,
 'Modelado, pruebas y documentación de transformaciones con dbt.', '2023-07-14', NULL),
('AWS Certified Cloud Practitioner', 'Amazon Web Services', 'AWS-CCP-000000', 'https://aws.amazon.com/certification/', NULL,
 'Fundamentos de arquitectura, costos y seguridad en AWS.', '2022-09-30', '2025-09-30'),
('Scrum Foundation Professional', 'CertiProf', 'SFPC-000000', NULL, NULL,
 'Marco de trabajo ágil aplicado a equipos de datos.', '2022-03-11', NULL),
('Databricks Lakehouse Fundamentals', 'Databricks', 'DB-LF-000000', NULL, NULL,
 'Arquitectura lakehouse, Delta Lake y gobierno de datos.', '2024-01-25', NULL);

-- ------------------------------------------------------------
--  experiences
-- ------------------------------------------------------------
INSERT INTO `experiences`
(`id`, `company`, `role`, `location`, `employment_type`, `company_url`, `summary`, `started_on`, `ended_on`)
VALUES
(1, 'Grupo Datalab', 'Data Engineer Senior', 'Guadalajara, MX', 'tiempo_completo', NULL,
 'Responsable de la plataforma de datos: ingesta, modelado, orquestación y calidad para las áreas comercial y de operaciones.',
 '2024-01-15', NULL),
(2, 'Sistemas Norte', 'Data Engineer', 'Remoto', 'tiempo_completo', NULL,
 'Construcción de pipelines batch y streaming para clientes de manufactura y retail.',
 '2022-03-01', '2024-01-10'),
(3, 'Consultoría TI Vega', 'Ingeniero de TI', 'Guadalajara, MX', 'tiempo_completo', NULL,
 'Administración de infraestructura, bases de datos y automatización de procesos internos.',
 '2020-06-01', '2022-02-25'),
(4, 'Freelance', 'Desarrollador y analista de datos', 'Remoto', 'freelance', NULL,
 'Automatización de reportes y desarrollo web a la medida para pequeñas empresas.',
 '2019-02-01', '2020-05-30');

INSERT INTO `experience_highlights` (`experience_id`, `description`) VALUES
(1, 'Diseñé el data warehouse en BigQuery que hoy consumen 5 áreas de negocio.'),
(1, 'Reduje el tiempo de cierre diario de 10 horas a 15 minutos con ingesta incremental.'),
(1, 'Implementé observabilidad de datos sobre 32 tablas críticas con alertas a Slack.'),
(1, 'Mentoreo a dos analistas en modelado dimensional y buenas prácticas de SQL.'),

(2, 'Desarrollé el pipeline de telemetría IoT para 4,500 sensores industriales.'),
(2, 'Migré 11 años de histórico de SQL Server on-premise a PostgreSQL gestionado.'),
(2, 'Estandaricé 60 métricas de negocio en una capa semántica con dbt.'),

(3, 'Administré la infraestructura de servidores y respaldos de 3 sedes.'),
(3, 'Automaticé reportes operativos que antes tomaban 12 horas-persona al mes.'),
(3, 'Implementé monitoreo de disponibilidad con alertas y bitácora de incidentes.'),

(4, 'Construí tableros de venta y cobranza para 6 clientes pequeños.'),
(4, 'Desarrollé integraciones entre sistemas de facturación y hojas de cálculo.');

-- ------------------------------------------------------------
--  contact_messages — ejemplos para la bandeja del panel
-- ------------------------------------------------------------
INSERT INTO `contact_messages` (`name`, `email`, `subject`, `message`, `ip_address`, `is_read`) VALUES
('María Fernanda Ruiz', 'mf.ruiz@example.com', 'Consultoría de data warehouse',
 'Hola Emilio, estamos evaluando migrar nuestro reporteo a BigQuery y vi tu proyecto de retail. ¿Podemos agendar una llamada esta semana?', '127.0.0.1', 0),
('Carlos Medina', 'cmedina@example.com', 'Vacante Data Engineer',
 'Buen día, tenemos una posición senior de ingeniería de datos y tu perfil encaja. Te comparto detalles si te interesa.', '127.0.0.1', 1);
