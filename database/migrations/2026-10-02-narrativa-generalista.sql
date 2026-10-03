-- ============================================================
--  2026-10-02 · Narrativa generalista (portafolio v3, modern minimal)
--
--  Para una base ya poblada:
--    1. el perfil pasa a "Ingeniero de TI" con la nueva promesa;
--    2. las categorías del stack pasan de un perfil de datos a uno de TI
--       general (Datos y Sistemas primero);
--    3. se retira `projects.bento_size`: la jerarquía la decide is_featured.
--
--  En una instalación nueva no hace falta: schema.sql y seeds.sql ya
--  incluyen todo esto.
--
--  NO es idempotente: el DROP COLUMN sólo puede correr una vez. Ejecutar
--  una sola vez sobre una base creada con el esquema anterior.
--
--  Aplicar (el charset del cliente NO es opcional: sin él los acentos se
--  guardan mal y se leen como "PÃ¡ginas"):
--
--    mysql --default-character-set=utf8mb4 -u root -p emiguzman \
--      < database/migrations/2026-10-02-narrativa-generalista.sql
-- ============================================================

USE `emiguzman`;

-- 1. Perfil: rol general y nueva promesa
UPDATE `profile`
   SET `role_title` = 'Ingeniero de TI',
       `headline`   = 'Sistemas, datos e infraestructura que funcionan, y que el resto del equipo puede entender.'
 WHERE `id` = 1;

-- 2. Categorías del stack: de un perfil de datos a uno de TI general.
--    MySQL ordena un ENUM por la posición en que se declaró cada valor,
--    así que este orden es el orden en que se ven en "Sobre mí".
ALTER TABLE `technologies` MODIFY `category`
  ENUM('lenguaje','base_datos','orquestacion','cloud','bi','herramienta',
       'datos','sistemas','redes','seguridad')
  NOT NULL DEFAULT 'herramienta';

UPDATE `technologies`
   SET `category` = 'datos'
 WHERE `category` IN ('base_datos', 'orquestacion', 'bi');

ALTER TABLE `technologies` MODIFY `category`
  ENUM('datos','sistemas','redes','seguridad','cloud','lenguaje','herramienta')
  NOT NULL DEFAULT 'herramienta';

-- 3. bento_size ya no tiene uso: la jerarquía la decide is_featured.
ALTER TABLE `projects` DROP COLUMN `bento_size`;
