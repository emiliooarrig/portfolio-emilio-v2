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
 'Ingeniero de TI · Ingeniería de Datos',
 'Diseño y opero pipelines de datos que convierten información cruda en decisiones medibles.',
 'Ingeniero de TI enfocado en ingeniería de datos: modelado, orquestación de pipelines y capas analíticas listas para negocio.',
 'Trabajo en la parte del sistema que casi nadie ve: el camino que recorre un dato desde que se genera hasta que alguien toma una decisión con él. Diseño modelos dimensionales, orquesto pipelines idempotentes y construyo capas semánticas para que los equipos dejen de discutir de dónde salió un número y empiecen a discutir qué hacer con él.\n\nVengo del lado de infraestructura y sistemas, así que me importa tanto el SLA del pipeline como el gráfico final: monitoreo, costos, reprocesos y calidad del dato son parte del entregable, no un extra.',
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
INSERT INTO `technologies` (`id`, `name`, `slug`, `category`, `is_featured`, `sort_order`) VALUES
(1,  'Python',        'python',        'lenguaje',      1, 10),
(2,  'SQL',           'sql',           'lenguaje',      1, 20),
(3,  'PHP',           'php',           'lenguaje',      0, 30),
(4,  'PostgreSQL',    'postgresql',    'base_datos',    1, 40),
(5,  'MySQL',         'mysql',         'base_datos',    1, 50),
(6,  'BigQuery',      'bigquery',      'base_datos',    1, 60),
(7,  'Apache Airflow','airflow',       'orquestacion',  1, 70),
(8,  'dbt',           'dbt',           'orquestacion',  1, 80),
(9,  'Apache Spark',  'spark',         'orquestacion',  0, 90),
(10, 'Kafka',         'kafka',         'orquestacion',  0, 100),
(11, 'Google Cloud',  'gcp',           'cloud',         1, 110),
(12, 'AWS',           'aws',           'cloud',         0, 120),
(13, 'Docker',        'docker',        'herramienta',   1, 130),
(14, 'Terraform',     'terraform',     'herramienta',   0, 140),
(15, 'Power BI',      'power-bi',      'bi',            1, 150),
(16, 'Looker Studio', 'looker-studio', 'bi',            0, 160),
(17, 'Git',           'git',           'herramienta',   0, 170),
(18, 'Pandas',        'pandas',        'herramienta',   0, 180);

-- ------------------------------------------------------------
--  home_metrics
-- ------------------------------------------------------------
INSERT INTO `home_metrics` (`label`, `value`, `unit`, `caption`, `sort_order`) VALUES
('Años en datos',        '5',    '+',    'Del ETL nocturno al streaming', 10),
('Pipelines en producción','24',  '',     'Orquestados y monitoreados',    20),
('Registros procesados', '180',  'M/mes','Batch + near real-time',        30),
('Tiempo de reporte',    '-72',  '%',    'De 8 h a 2 h en cierre mensual',40);

-- ------------------------------------------------------------
--  projects
-- ------------------------------------------------------------
INSERT INTO `projects`
(`id`, `slug`, `title`, `subtitle`, `summary`, `context`, `solution`, `outcome`, `role`, `client`,
 `cover_image`, `repo_url`, `demo_url`, `bento_size`, `is_featured`, `is_published`, `has_pipeline`,
 `started_on`, `ended_on`, `sort_order`)
VALUES
(1, 'plataforma-datos-retail', 'Plataforma de datos para retail',
 'Del punto de venta al tablero directivo en 15 minutos',
 'Data warehouse en BigQuery con ingesta incremental desde 120 tiendas y capa semántica en dbt para reportes de venta casi en tiempo real.',
 'La operación consolidaba ventas en hojas de cálculo enviadas por correo cada noche. El cierre diario llegaba a media mañana y cada área tenía su propia versión del mismo número.',
 'Construí ingesta incremental desde los POS hacia BigQuery con Airflow, modelado dimensional en dbt (ventas, inventario, mermas) y pruebas de calidad automáticas en cada corrida. La capa semántica quedó como fuente única para Power BI.',
 'El cierre diario pasó de 10 h a 15 min, y las tres áreas que discutían cifras distintas hoy leen el mismo modelo.',
 'Data Engineer (líder técnico)', 'Cadena retail nacional',
 NULL, 'https://github.com/emilioguzman/retail-data-platform', NULL,
 'xl', 1, 1, 1, '2024-02-01', '2024-11-30', 10),

(2, 'pipeline-streaming-iot', 'Pipeline de telemetría IoT',
 'Ingesta continua de 4,500 sensores industriales',
 'Streaming con Kafka y Spark Structured Streaming para detectar anomalías de temperatura en planta antes de que se conviertan en paro de línea.',
 'Los sensores de planta escribían a un histórico que sólo se revisaba después de una falla. El diagnóstico era siempre forense, nunca preventivo.',
 'Diseñé el flujo Kafka → Spark Structured Streaming → almacenamiento columnar, con ventanas móviles para detección de desviaciones y alertas al equipo de mantenimiento.',
 'Las alertas se adelantaron un promedio de 40 minutos a la falla; dos paros de línea evitados en el primer trimestre.',
 'Data Engineer', 'Manufactura industrial',
 NULL, NULL, NULL,
 'lg', 1, 1, 1, '2023-05-01', '2023-12-15', 20),

(3, 'observabilidad-datos', 'Observabilidad de pipelines',
 'Saber que un dato se rompió antes que el negocio',
 'Sistema de monitoreo de frescura, volumen y esquema sobre 30+ tablas críticas, con alertas a Slack y bitácora histórica de incidentes.',
 'Los errores de datos se descubrían cuando alguien notaba un tablero vacío. No había forma de saber cuándo se rompió ni por cuánto tiempo.',
 'Implementé chequeos de frescura, volumen esperado y deriva de esquema como DAGs de Airflow, con severidad por tabla y una bitácora consultable de cada incidente.',
 'El tiempo medio de detección bajó de 2 días a 20 minutos.',
 'Data Engineer', 'Proyecto interno',
 NULL, 'https://github.com/emilioguzman/data-observability', NULL,
 'md', 1, 1, 0, '2024-03-01', NULL, 30),

(4, 'migracion-onprem-cloud', 'Migración on-premise a la nube',
 '11 años de histórico movidos sin parar la operación',
 'Migración de un data warehouse SQL Server on-premise hacia PostgreSQL gestionado en la nube, con validación fila a fila y ventana de corte de 4 horas.',
 'El servidor on-premise estaba sin soporte y cada mantenimiento requería detener la operación durante el fin de semana.',
 'Planeé la migración por dominios, con carga histórica en paralelo, doble escritura durante la transición y scripts de reconciliación por conteo y checksum.',
 'Corte final de 4 horas en domingo, cero pérdida de registros y 38% menos de costo de infraestructura.',
 'Ingeniero de TI', 'Sector financiero',
 NULL, NULL, NULL,
 'lg', 0, 1, 0, '2022-08-01', '2023-03-31', 40),

(5, 'capa-semantica-bi', 'Capa semántica de negocio',
 'Un solo lugar donde vive la definición de "cliente activo"',
 'Diccionario de métricas y modelo semántico en dbt que estandariza 60 indicadores usados por finanzas, comercial y operaciones.',
 'Cada área calculaba sus propios KPIs con reglas distintas. La misma junta terminaba con tres cifras de ingreso para el mismo mes.',
 'Documenté y modelé cada métrica en dbt con pruebas y linaje visible, más un diccionario navegable para usuarios de negocio.',
 'Las juntas de resultados dejaron de empezar con una discusión sobre de dónde salió el número.',
 'Analytics Engineer', 'Retail y servicios',
 NULL, NULL, NULL,
 'md', 0, 1, 0, '2023-01-10', '2023-06-30', 50);

-- ------------------------------------------------------------
--  project_technologies
-- ------------------------------------------------------------
INSERT INTO `project_technologies` (`project_id`, `technology_id`, `sort_order`) VALUES
(1, 6, 10), (1, 7, 20), (1, 8, 30), (1, 1, 40), (1, 15, 50),
(2, 10, 10), (2, 9, 20), (2, 1, 30), (2, 11, 40),
(3, 7, 10), (3, 1, 20), (3, 2, 30), (3, 13, 40),
(4, 4, 10), (4, 2, 20), (4, 14, 30), (4, 12, 40),
(5, 8, 10), (5, 2, 20), (5, 16, 30);

-- ------------------------------------------------------------
--  project_metrics
-- ------------------------------------------------------------
INSERT INTO `project_metrics` (`project_id`, `label`, `value`, `unit`, `sort_order`) VALUES
(1, 'Tiendas integradas',    '120',  '',      10),
(1, 'Latencia de reporte',   '15',   'min',   20),
(1, 'Modelos dbt',           '64',   '',      30),
(2, 'Sensores en línea',     '4,500','',      10),
(2, 'Eventos por minuto',    '90',   'K',     20),
(2, 'Adelanto de alerta',    '40',   'min',   30),
(3, 'Tablas monitoreadas',   '32',   '',      10),
(3, 'Detección media',       '20',   'min',   20),
(4, 'Histórico migrado',     '11',   'años',  10),
(4, 'Ventana de corte',      '4',    'h',     20),
(4, 'Ahorro en infra',       '-38',  '%',     30),
(5, 'Métricas estandarizadas','60',  '',      10);

-- ------------------------------------------------------------
--  project_pipeline_steps
--  project_id NULL = pipeline genérico mostrado en el Inicio
-- ------------------------------------------------------------
INSERT INTO `project_pipeline_steps` (`project_id`, `label`, `description`, `stage`, `sort_order`) VALUES
(NULL, 'Ingesta',      'APIs, POS, sensores, archivos planos',       'raw',       10),
(NULL, 'Staging',      'Datos crudos versionados e inmutables',      'raw',       20),
(NULL, 'Transformación','Modelado dimensional y pruebas de calidad', 'transform', 30),
(NULL, 'Capa semántica','Métricas con una sola definición',          'transform', 40),
(NULL, 'Decisión',     'Tableros y alertas que alguien usa',         'refined',   50),

(1, 'POS de tienda',      'Extracción incremental cada 15 min',      'raw',       10),
(1, 'Landing BigQuery',   'Particionado por fecha de operación',     'raw',       20),
(1, 'dbt · staging',      'Normalización y deduplicado',             'transform', 30),
(1, 'dbt · marts',        'Ventas, inventario y mermas',             'transform', 40),
(1, 'Power BI',           'Tablero directivo de cierre diario',      'refined',   50),

(2, 'Sensores',           '4,500 dispositivos, 90K eventos/min',     'raw',       10),
(2, 'Kafka',              'Tópicos por línea de producción',         'raw',       20),
(2, 'Spark Streaming',    'Ventanas móviles de 5 minutos',           'transform', 30),
(2, 'Detección',          'Umbrales dinámicos por sensor',           'transform', 40),
(2, 'Alerta a planta',    'Notificación al equipo de mantenimiento', 'refined',   50);

-- ------------------------------------------------------------
--  certifications
-- ------------------------------------------------------------
INSERT INTO `certifications`
(`title`, `issuer`, `credential_id`, `credential_url`, `badge_image`, `description`, `issued_on`, `expires_on`, `sort_order`)
VALUES
('Professional Data Engineer', 'Google Cloud', 'GCP-PDE-000000', 'https://www.credential.net/', NULL,
 'Diseño de sistemas de datos, pipelines y modelos operables en Google Cloud.', '2024-06-18', '2027-06-18', 10),
('Azure Data Fundamentals (DP-900)', 'Microsoft', 'MS-DP900-000000', 'https://learn.microsoft.com/', NULL,
 'Fundamentos de datos relacionales, no relacionales y analítica en Azure.', '2023-11-02', NULL, 20),
('dbt Analytics Engineering', 'dbt Labs', 'DBT-AE-000000', 'https://www.getdbt.com/', NULL,
 'Modelado, pruebas y documentación de transformaciones con dbt.', '2023-07-14', NULL, 30),
('AWS Certified Cloud Practitioner', 'Amazon Web Services', 'AWS-CCP-000000', 'https://aws.amazon.com/certification/', NULL,
 'Fundamentos de arquitectura, costos y seguridad en AWS.', '2022-09-30', '2025-09-30', 40),
('Scrum Foundation Professional', 'CertiProf', 'SFPC-000000', NULL, NULL,
 'Marco de trabajo ágil aplicado a equipos de datos.', '2022-03-11', NULL, 50),
('Databricks Lakehouse Fundamentals', 'Databricks', 'DB-LF-000000', NULL, NULL,
 'Arquitectura lakehouse, Delta Lake y gobierno de datos.', '2024-01-25', NULL, 60);

-- ------------------------------------------------------------
--  experiences
-- ------------------------------------------------------------
INSERT INTO `experiences`
(`id`, `company`, `role`, `location`, `employment_type`, `company_url`, `summary`, `started_on`, `ended_on`, `is_current`, `sort_order`)
VALUES
(1, 'Grupo Datalab', 'Data Engineer Senior', 'Guadalajara, MX', 'tiempo_completo', NULL,
 'Responsable de la plataforma de datos: ingesta, modelado, orquestación y calidad para las áreas comercial y de operaciones.',
 '2024-01-15', NULL, 1, 10),
(2, 'Sistemas Norte', 'Data Engineer', 'Remoto', 'tiempo_completo', NULL,
 'Construcción de pipelines batch y streaming para clientes de manufactura y retail.',
 '2022-03-01', '2024-01-10', 0, 20),
(3, 'Consultoría TI Vega', 'Ingeniero de TI', 'Guadalajara, MX', 'tiempo_completo', NULL,
 'Administración de infraestructura, bases de datos y automatización de procesos internos.',
 '2020-06-01', '2022-02-25', 0, 30),
(4, 'Freelance', 'Desarrollador y analista de datos', 'Remoto', 'freelance', NULL,
 'Automatización de reportes y desarrollo web a la medida para pequeñas empresas.',
 '2019-02-01', '2020-05-30', 0, 40);

INSERT INTO `experience_highlights` (`experience_id`, `description`, `sort_order`) VALUES
(1, 'Diseñé el data warehouse en BigQuery que hoy consumen 5 áreas de negocio.', 10),
(1, 'Reduje el tiempo de cierre diario de 10 horas a 15 minutos con ingesta incremental.', 20),
(1, 'Implementé observabilidad de datos sobre 32 tablas críticas con alertas a Slack.', 30),
(1, 'Mentoreo a dos analistas en modelado dimensional y buenas prácticas de SQL.', 40),

(2, 'Desarrollé el pipeline de telemetría IoT para 4,500 sensores industriales.', 10),
(2, 'Migré 11 años de histórico de SQL Server on-premise a PostgreSQL gestionado.', 20),
(2, 'Estandaricé 60 métricas de negocio en una capa semántica con dbt.', 30),

(3, 'Administré la infraestructura de servidores y respaldos de 3 sedes.', 10),
(3, 'Automaticé reportes operativos que antes tomaban 12 horas-persona al mes.', 20),
(3, 'Implementé monitoreo de disponibilidad con alertas y bitácora de incidentes.', 30),

(4, 'Construí tableros de venta y cobranza para 6 clientes pequeños.', 10),
(4, 'Desarrollé integraciones entre sistemas de facturación y hojas de cálculo.', 20);

-- ------------------------------------------------------------
--  contact_messages — ejemplos para la bandeja del panel
-- ------------------------------------------------------------
INSERT INTO `contact_messages` (`name`, `email`, `subject`, `message`, `ip_address`, `is_read`) VALUES
('María Fernanda Ruiz', 'mf.ruiz@example.com', 'Consultoría de data warehouse',
 'Hola Emilio, estamos evaluando migrar nuestro reporteo a BigQuery y vi tu proyecto de retail. ¿Podemos agendar una llamada esta semana?', '127.0.0.1', 0),
('Carlos Medina', 'cmedina@example.com', 'Vacante Data Engineer',
 'Buen día, tenemos una posición senior de ingeniería de datos y tu perfil encaja. Te comparto detalles si te interesa.', '127.0.0.1', 1);
