-- ------------------------------------------------------------
--  Se retira el orden manual (`sort_order`) de todas las tablas.
--
--  Era un dato que había que mantener a mano y que decía lo mismo que ya
--  decían otros: en proyectos y experiencia, el intervalo de fechas; en las
--  filas hijas (stack de un proyecto, métricas, pasos del flujo, logros de un
--  puesto), el orden en que se insertan al guardar, que es el del formulario.
--  Dos fuentes para el mismo hecho es una que se puede contradecir.
--
--  Por lo mismo se va `experiences.is_current`: decía lo que ya decía la
--  fecha de fin (sin fecha = sigue en marcha) y podía contradecirla. Ahora se
--  deriva al leer, así que no hay dos versiones del mismo hecho.
--
--  Después de esto:
--    · proyectos y experiencia se ordenan por sus fechas, del más reciente al
--      más antiguo, contando lo que sigue abierto como «hoy»;
--    · stack, certificaciones y servicios no llevan orden: salen por su id;
--    · las filas hijas salen por su id, que es el orden en que se guardaron.
--
--  Ejecutar una sola vez sobre una base creada con el esquema anterior.
-- ------------------------------------------------------------

ALTER TABLE `technologies`
  DROP INDEX `idx_tech_featured`,
  DROP COLUMN `sort_order`,
  ADD KEY `idx_tech_featured` (`is_featured`, `category`);

ALTER TABLE `projects`
  DROP INDEX `idx_project_published`,
  DROP COLUMN `sort_order`,
  ADD KEY `idx_project_published` (`is_published`, `started_on`);

ALTER TABLE `project_technologies`
  DROP COLUMN `sort_order`;

ALTER TABLE `project_metrics`
  DROP INDEX `idx_pm_project`,
  DROP COLUMN `sort_order`,
  ADD KEY `idx_pm_project` (`project_id`);

ALTER TABLE `project_pipeline_steps`
  DROP INDEX `idx_pps_project`,
  DROP COLUMN `sort_order`,
  ADD KEY `idx_pps_project` (`project_id`);

ALTER TABLE `services`
  DROP INDEX `idx_service_published`,
  DROP COLUMN `sort_order`,
  ADD KEY `idx_service_published` (`is_published`);

ALTER TABLE `certifications`
  DROP COLUMN `sort_order`;

ALTER TABLE `experiences`
  DROP COLUMN `sort_order`,
  DROP COLUMN `is_current`;

ALTER TABLE `experience_highlights`
  DROP INDEX `idx_eh_experience`,
  DROP COLUMN `sort_order`,
  ADD KEY `idx_eh_experience` (`experience_id`);

ALTER TABLE `home_metrics`
  DROP COLUMN `sort_order`;
