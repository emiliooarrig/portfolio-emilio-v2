# Portafolio · Emilio Guzmán

Landing personal de perfil profesional (Ingeniería de TI · Ingeniería de Datos), en PHP vanilla con MVC propio, SCSS a mano y MySQL.

## Requisitos

- PHP 8.1+ con `mysqli`
- MySQL 8 / MariaDB 10.4+
- Node 18+ (sólo para compilar el SCSS)

## Puesta en marcha

```bash
# 1. Base de datos
mysql -u root -p < database/schema.sql
mysql -u root -p < database/seeds.sql

# 2. Estilos
npm install
npm run css          # compila una vez
npm run css:watch    # recompila al guardar

# 3. Servidor
npm run serve        # php -S localhost:8000 -t public
```

Con Docker (contenedor `mysql-local`):

```bash
docker exec -i mysql-local mysql -uroot -proot < database/schema.sql
docker exec -i mysql-local mysql -uroot -proot < database/seeds.sql
```

### Credenciales

`config/config.php` trae valores por defecto (`127.0.0.1:3306`, `root`/`root`, base `emiguzman`). Para cambiarlos sin tocar el repositorio, crea `config/config.local.php`:

```php
<?php
return [
    'db'  => ['host' => '127.0.0.1', 'user' => 'root', 'pass' => 'tu_password'],
    'app' => ['debug' => false],
];
```

Ese archivo está en `.gitignore`.

### Apache / XAMPP

El *document root* debe apuntar a `public/`. El `.htaccess` incluido redirige todo al front controller y sirve directo los assets. Si el sitio vive en una subcarpeta, ajusta `app.base_path` en la configuración.

## Estructura

```
/app
  /Core           Router, Controller, Model, Database (mysqli), Request, helpers
  /Controllers    Home, About, Project, Certification, Experience, Contact, Error
  /Models         Profile, Project, Certification, Experience, Technology, HomeMetric, ContactMessage
  /Views          layouts/ · home/ · about/ · projects/ · certifications/ · experience/ · contact/ · partials/ · errors/
/config           config.php · routes.php
/database         schema.sql · seeds.sql
/public           index.php (front controller) · .htaccess · assets/
```

## Rutas

| Ruta | Controlador |
|---|---|
| `/` | `HomeController::index` |
| `/sobre-mi` | `AboutController::index` |
| `/proyectos` | `ProjectController::index` |
| `/proyectos/{slug}` | `ProjectController::show` |
| `/certificaciones` | `CertificationController::index` |
| `/experiencia` | `ExperienceController::index` |
| `/contacto` | `ContactController::index` / `store` (POST) |

## Contenido dinámico

Todo el contenido visible sale de la base de datos `emiguzman`; no hay texto de perfil incrustado en las vistas.

| Tabla | Alimenta |
|---|---|
| `profile` | nombre, rol, headline, bio, contacto, CV, redes (fila única, `id = 1`) |
| `projects` + `project_technologies` + `project_metrics` + `project_pipeline_steps` | grid bento, detalle, resultados y diagrama de flujo |
| `certifications` | grid de credenciales, estado vigente/expirada calculado desde `expires_on` |
| `experiences` + `experience_highlights` | timeline de trayectoria |
| `technologies` | chips de stack (`is_featured` = aparece en Inicio) |
| `home_metrics` | celda "lectura rápida" del Inicio |
| `contact_messages` | bandeja del formulario |
| `admin_users` | acceso al futuro panel de administración |

Campos de control pensados para el panel: `is_published`, `sort_order`, `is_featured`, `bento_size` (`sm`/`md`/`lg`/`xl` = ancho de la celda en el grid de 12 columnas) y `has_pipeline`.

**Usuario semilla:** `admin` / `admin123` — cambiar antes de publicar.

## Sistema visual

Las decisiones de diseño están documentadas en `CLAUDE.md` y viven en `public/assets/css/tokens.scss`:

- **Narrativa de color:** azul estructural (`#5B8DEF`) = dato crudo · ámbar (`#E8A33D`) = dato refinado. El ámbar sólo aparece en CTAs primarios, métricas y el final del pipeline.
- **Profundidad:** bordes de baja opacidad y cambios de superficie. Sombras únicamente en el `glass-panel`.
- **Espaciado:** base 4px, escala `--space-1..9`. Densidad de celda: 24px.
- **Tipografía:** Space Grotesk (display) · IBM Plex Sans (cuerpo) · IBM Plex Mono (datos, fechas, stack).
- **Signature element:** el panel de pipeline con `backdrop-filter` es el único componente con tratamiento de vidrio, y el único con animación fuerte. Se pausa fuera de pantalla y con `prefers-reduced-motion`.

## Pendiente

- Panel de administración (`/admin`) con login sobre `admin_users` y CRUD de proyectos, certificaciones y experiencia.
- Sustituir los datos de ejemplo de `database/seeds.sql` por la información real.
- Imágenes de portada de proyectos (`projects.cover_image`) y PDF del CV en `public/assets/docs/`.
