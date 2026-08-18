# Portafolio · Emilio Guzmán

Landing personal de perfil profesional (Ingeniería de TI · Ingeniería de Datos) en **una sola página**: scroll continuo con secciones ancladas, PHP vanilla con MVC propio, SCSS a mano y MySQL.

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

**Acceso al panel:** los seeds crean `admin` / `admin123` en `admin_users`. Es una
contraseña de ejemplo y está publicada en este repositorio: cámbiala antes de subir
el sitio. Para generar el nuevo hash sin escribirla en ningún archivo:

```bash
php -r 'echo password_hash(readline("Contraseña: "), PASSWORD_BCRYPT, ["cost" => 12]), "\n";'
docker exec -i mysql-local mysql -uroot -proot -e \
  "UPDATE emiguzman.admin_users SET password_hash = '<el hash>' WHERE username = 'admin';"
```

### Apache / XAMPP

El *document root* debe apuntar a `public/`. El `.htaccess` incluido redirige todo al front controller y sirve directo los assets. Si el sitio vive en una subcarpeta, ajusta `app.base_path` en la configuración.

## Estructura

```
/app
  /Core           Router, Controller, Model, Database (mysqli), Request, Auth, Csrf, helpers
  /Controllers    Home (la landing completa), Project (detalle del modal), Contact, Error, Auth
                  Admin/ (uno por sección del panel, sobre Admin\AdminController)
  /Models         Profile, Project, Service, Certification, Experience, Technology, ContactMessage, AdminUser
  /Views          layouts/ (main · auth · admin) · home/index.php · partials/ (una por sección) · admin/ (una por pantalla) · errors/
/config           config.php · routes.php
/database         schema.sql · seeds.sql · migrations/
/public           index.php (front controller) · .htaccess · assets/
```

Todo el contenido vive en `home/index.php`, que sólo apila los partials de sección
en el orden del scroll: `section-inicio` → `section-sobre-mi` → `section-proyectos`
→ `section-servicios` → `section-certificaciones` → `section-experiencia` →
`section-contacto`.

## Rutas

Tres para la landing pública y 47 para el panel: la landing es una sola página
con anclas; el panel es una herramienta, y cada sección editable repite el mismo
juego de siete rutas.

| Ruta | Controlador | Qué devuelve |
|---|---|---|
| `GET /` | `HomeController::index` | la landing completa |
| `GET /proyectos/{slug}` | `ProjectController::detail` | el fragmento del modal si la petición es AJAX; si no, la landing con el modal ya abierto (enlace compartible) |
| `POST /contacto` | `ContactController::store` | redirige a `/#contacto` con el flash en sesión |
| `GET /admin/login` | `AuthController::showLogin` | formulario de acceso (usuario y contraseña); si ya hay sesión, redirige a `/admin` |
| `POST /admin/login` | `AuthController::login` | valida y redirige: a `/admin` si entra, de vuelta al formulario con el motivo si no |
| `POST /admin/logout` | `AuthController::logout` | cierra la sesión y vuelve al login |
| `GET /admin` | `Admin\DashboardController` | resumen: conteos por sección y últimos mensajes |

Y una tabla por sección, cada una con su CRUD completo. Sustituyendo `{sección}`
por `proyectos`, `servicios`, `certificaciones`, `experiencia` o `tecnologias`:

| Ruta | Qué hace |
|---|---|
| `GET /admin/{sección}` | el listado, con todo: publicado y oculto |
| `GET /admin/{sección}/nuevo` | formulario en blanco (`/nueva` en certificaciones y tecnologías) |
| `POST /admin/{sección}` | da de alta |
| `GET /admin/{sección}/{id}/editar` | formulario con la fila cargada |
| `POST /admin/{sección}/{id}` | guarda |
| `GET /admin/{sección}/{id}/eliminar` | pantalla de confirmación |
| `POST /admin/{sección}/{id}/eliminar` | borra |

Perfil y Mensajes no siguen ese patrón, porque su contenido no funciona así:

| Ruta | Qué hace |
|---|---|
| `GET /admin/perfil` · `GET /admin/perfil/editar` · `POST /admin/perfil` | fila única: sólo lectura y edición |
| `GET /admin/mensajes` · `GET /admin/mensajes/{id}` | la bandeja y un mensaje entero (abrirlo lo marca leído) |
| `POST /admin/mensajes/{id}/leido` | lo marca leído o lo devuelve a nuevo |
| `GET`/`POST /admin/mensajes/{id}/eliminar` | confirma y borra |

En la landing la navegación entre secciones es por anclas (`#inicio`, `#sobre-mi`,
`#proyectos`, `#certificaciones`, `#experiencia`, `#contacto`) con scroll suave y
scroll-spy; nunca recarga la página. En el panel es al revés: cada enlace de la
lateral es una petición nueva.

Toda ruta bajo `/admin` (menos el login) empieza con `Auth::requireLogin()`:
sin sesión redirigen al formulario de acceso.

### El panel

Una sección por cada parte de la landing, más el resumen. Se navega por la barra
lateral (`partials/admin-sidebar.php`, la única lista de pantallas que existe) y
cada listado muestra **todo** lo que hay en la base, publicado u oculto — el panel
es el único sitio desde donde se ve lo que la landing esconde.

Desde ahí se crea, se edita y se borra: proyectos (con su stack, sus métricas y
los pasos de su flujo, todo en el mismo envío), servicios, certificaciones,
experiencia y stack. El perfil sólo se edita, porque es una fila única. Los
mensajes sólo se leen, se marcan y se borran, porque los escribe quien visita el
sitio.

Cómo funciona por dentro:

- Cada sección tiene su controlador en `Controllers/Admin/`, sobre una base común
  que reúne el layout, el aviso de la última acción y la lectura del POST.
- La validación vive en el Model, junto a `create()`, `update()` y `delete()`. Si
  algo no valida no se guarda nada: se vuelve al formulario con los errores y con
  lo ya tecleado.
- Guardar siempre redirige (POST-redirect-GET): recargar después de guardar no
  vuelve a guardar.
- Todo POST lleva token CSRF, y **borrar se confirma en su propia pantalla**, que
  dice qué se lleva por delante y recuerda que despublicar suele bastar.

Va sin JS: la lateral se vuelve una tira de pestañas deslizable en pantallas
estrechas por CSS, y las tablas anchas hacen scroll dentro de su contenedor. Las
listas repetibles (métricas, pasos del flujo) son grupos de campos con tres huecos
libres; vaciar el primer campo de una fila es lo que la borra.

### Acceso al panel

Toda la sesión vive en `$_SESSION`, bajo la llave `admin` (ver `Core/Auth.php`) —
sin cookies propias ni "recordarme". El login **no** acepta correo: se entra con
`admin_users.username`, y se compara con `password_verify` contra `password_hash`.

- **No hay registro público**, ni lo habrá: las cuentas se darán de alta desde
  dentro del panel. Esa pantalla está pendiente, así que hoy la única cuenta es
  la que crean los seeds.
- El id de sesión se regenera al entrar y al salir (fijación de sesión).
- Un solo mensaje de error para usuario inexistente y contraseña equivocada, y se
  verifica un hash de descarte cuando el usuario no existe, para que el tiempo de
  respuesta no delate qué usuarios hay.
- Cinco intentos fallidos seguidos bloquean 60 s; el bloqueo se guarda en la sesión,
  así que frena a una persona insistiendo en el navegador, **no** a un script que
  descarte la cookie. Para eso hace falta un límite por IP en el servidor.
- La sesión se cierra sola tras 2 h de inactividad, y `POST /admin/login` y
  `/admin/logout` exigen token CSRF.

## Front-end

| Módulo | Responsabilidad |
|---|---|
| `main.js` | menú móvil, anclas, barra de progreso, botón "volver arriba" y arranque del resto |
| `scroll-reveal.js` | entrada de las celdas al cruzar el viewport, con escalonado (`IntersectionObserver`) |
| `scroll-spy.js` | resalta en el nav la sección visible |
| `count-up.js` | contadores de las métricas (0 → valor real) |
| `project-modal.js` | fetch del detalle, apertura/cierre, trampa de foco e `history.pushState` |
| `pipeline.js` | pausa la animación del riel fuera de pantalla |
| `cursor.js` | cursor propio (punto + anillo). Sólo con puntero fino; en táctil no se construye |

## Contenido dinámico

Todo el contenido visible sale de la base de datos `emiguzman`; no hay texto de perfil incrustado en las vistas.

| Tabla | Alimenta |
|---|---|
| `profile` | nombre, rol, headline, bio, contacto, CV, redes (fila única, `id = 1`) |
| `projects` + `project_technologies` + `project_metrics` + `project_pipeline_steps` | grid bento, detalle, resultados y diagrama de flujo |
| `certifications` | grid de credenciales: emisor, título, fechas de emisión y vencimiento, ID y enlace de verificación (sin etiqueta de estado) |
| `experiences` + `experience_highlights` | timeline de trayectoria |
| `technologies` | chips de stack agrupados por categoría en "Sobre mí" |
| `contact_messages` | bandeja del formulario |
| `admin_users` | acceso al panel de administración |

Campos de control pensados para el panel: `is_published`, `sort_order`, `is_featured`, `bento_size` (`sm`/`md`/`lg`/`xl` = ancho de la celda en el grid de 12 columnas) y `has_pipeline`.

> La tabla `home_metrics` quedó sin uso al retirarse la celda "lectura rápida" del hero.
> Se conserva en el esquema por si el panel de administración la retoma.

**Usuario semilla:** `admin` / `admin123` — cambiar antes de publicar.

## Sistema visual

Las decisiones de diseño están documentadas en `CLAUDE.md` y viven en `public/assets/css/tokens.scss`:

- **Línea única:** Bento Grid. No hay un segundo lenguaje visual: nada de vidrio ni `backdrop-filter` decorativo.
- **Una sola fuente de verdad para el bento:** los mixins `bento-surface`, `bento-span($desktop, $tablet)` y `bento-place($c1, $c2, $r1, $r2)` en `tokens.scss`. Los componentes los invocan; ninguno redeclara fondo, borde, radio ni ancho. Sin frameworks de terceros (lo prohíbe `CLAUDE.md`).
- **Hero:** la única excepción al bento — a sangre completa, `100svh - nav`, con el nombre, la profesión y los CTA. Nada más.
- **Cursor propio:** punto + anillo con retardo, en `_cursor.scss` y `cursor.js`. Se activa sólo con puntero fino y JS; en táctil, sin JS o sobre campos de texto manda el cursor del sistema.
- **Narrativa de color:** azul estructural (`#6EA8FF`) = dato crudo · ámbar (`#FFB13D`) = dato refinado. El ámbar sólo aparece en CTAs primarios, métricas y el final del pipeline.
- **Profundidad:** bordes de baja opacidad, cambio de superficie (`#1A2433` → `#24334A`) y elevación al hover, siempre con movimiento además de sombra.
- **Espaciado:** base 4px, escala `--space-1..9`. Densidad de celda: 24px.
- **Tipografía:** Sekuya (display) · Stack Sans Headline (cuerpo y también datos, fechas, stack). Dos familias, ninguna monoespaciada, autoalojadas en `public/assets/fonts/` — el `<head>` no carga tipografía externa. Los archivos no están en el repo: ver `public/assets/fonts/README.md`. Lo utilitario se marca con la clase `.meta` (números tabulares e interletraje neutro), no con un cambio de familia.
- **Animación:** clases reutilizables en `animations.scss` (`.reveal`, `.reveal--stagger`, `.count-up`, `.bento-card--hoverable`, `.hero-in`). Sólo `transform` y `opacity`. Todo tiene versión reducida bajo `prefers-reduced-motion`, y el estado oculto del scroll-reveal depende de `html.js`: sin JS no se oculta nada.

## Pendiente

- **Archivos de las fuentes:** dejar `sekuya.woff2` y `stack-sans-headline.woff2` en `public/assets/fonts/` (nombres exactos en el README de esa carpeta). Hasta entonces el sitio se ve con `Segoe UI` y los dos `<link rel="preload">` del layout dan 404.
- Panel de administración: falta el alta de cuentas desde dentro (hoy sólo existe el usuario semilla) y subir archivos —imágenes y CV— en vez de escribir su ruta a mano.
- Sustituir los datos de ejemplo de `database/seeds.sql` por la información real.
- Imágenes de portada de proyectos (`projects.cover_image`) y PDF del CV en `public/assets/docs/`.
