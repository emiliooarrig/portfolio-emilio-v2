# Portafolio · Emilio Guzmán

Portafolio personal de un ingeniero de TI, pensado para que cualquier reclutador entienda en segundos quién es, qué ha hecho y cómo trabaja. **Una sola página**: scroll continuo con secciones ancladas, PHP vanilla con MVC propio, SCSS a mano y MySQL.

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

La conexión a la base de datos se configura con variables de entorno. Copia la
plantilla y ajusta los valores:

```bash
cp .env.example .env
```

```dotenv
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=emiguzman
DB_USER=root
DB_PASS=
DB_CHARSET=utf8mb4
```

`.env` está en `.gitignore`, así que las credenciales nunca viajan al repositorio.
`config/config.php` las lee con `App\Core\Env` y mantiene valores por defecto de
entorno local para cada llave, de modo que el sitio arranca aunque el archivo no
exista todavía.

En producción no hace falta subir el `.env`: si el hosting (o el `SetEnv` de
Apache) define las variables, ésas ganan sobre el archivo. El mismo `.env`
acepta además `APP_NAME`, `APP_BASE_PATH`, `APP_DEBUG`, `APP_TIMEZONE` y
`APP_LOCALE`.

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
  /Core           Router, Controller, Model, Database (mysqli), Env, Request, Auth, Csrf, helpers
  /Controllers    Home (la landing completa), Project (detalle del modal), Contact, Error, Auth
                  Admin/ (uno por sección del panel, sobre Admin\AdminController)
  /Models         Profile, Project, Service, Certification, Experience, Technology, ContactMessage, AdminUser
  /Views          layouts/ (main · auth · admin) · home/index.php · partials/ (una por sección) · admin/ (una por pantalla) · errors/
/config           config.php · routes.php
/database         schema.sql · seeds.sql · migrations/
/public           index.php (front controller) · .htaccess · assets/
```

Todo el contenido vive en `home/index.php`, que sólo apila los partials de sección
en el orden del scroll: `section-inicio` → `section-proyectos` → `section-metodo`
(Cómo trabajo) → `section-experiencia` → `section-sobre-mi` →
`section-certificaciones` → `section-contacto`. La prueba (proyectos) va primero;
"Sobre mí" baja porque sólo interesa una vez que el trabajo convenció. Servicios
salió de la landing, pero su tabla, su modelo y su CRUD del panel se conservan.

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
- Todo POST lleva token CSRF. **Borrar siempre pregunta antes**: el ícono de la
  papelera abre la alerta del sitio con el nombre de la fila y lo que se lleva por
  delante, sin salir del listado. El borrado es duro (`DELETE FROM`, con las
  filas hijas cayendo por `ON DELETE CASCADE`): no hay papelera ni deshacer, y por
  eso la pregunta. Sin JavaScript ese mismo enlace lleva a la pantalla de
  confirmación de siempre, que sigue ahí y recuerda que despublicar suele bastar.
- Las acciones de cada fila son íconos —lápiz y papelera—, no texto: en una tabla
  se reconocen antes de leerse. El nombre de la fila viaja en el `aria-label`, que
  es lo que anuncia un lector de pantalla y lo que sale al pasar por encima.
- **La foto del perfil y el CV se suben desde el formulario**, no se escriben como
  ruta (`Core/Upload.php`). El tipo se decide leyendo el archivo, no por su
  extensión; se guarda en `public/assets/img` o `public/assets/docs` con un nombre
  propio (slug + sufijo aleatorio) y, cuando se reemplaza o se quita, el anterior
  se borra del disco — sólo dentro de la carpeta que la propia regla declara.
  Nada se escribe en disco hasta que el resto del formulario valida, así que un
  error no deja archivos sueltos. Formatos: JPG, PNG, WEBP o AVIF hasta 4 MB para
  la foto; PDF hasta 8 MB para el CV.

Funciona sin JS: la lateral se vuelve una tira de pestañas deslizable en pantallas
estrechas por CSS, y las tablas anchas hacen scroll dentro de su contenedor. El
único script que carga es `alerts.js`, y sólo mejora la alerta de la última
acción — sin él se ve igual y se cierra igual. Las
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
| `main.js` | menú móvil, anclas, regla del nav al hacer scroll, el asentamiento del nombre del hero y arranque del resto |
| `scroll-spy.js` | resalta en el nav la sección visible (`IntersectionObserver`) |
| `project-modal.js` | fetch del detalle, apertura/cierre con View Transitions (fundido sin la API), trampa de foco e `history.pushState` |
| `alerts.js` | comportamiento de las alertas: modal, Escape, clic fuera y cierre automático |

### Alertas

Todo lo que el backend contesta —guardado, borrado, error o aviso— sale por la
misma pieza: `partials/alert.php`. Una sola línea la lanza desde cualquier vista:

```php
<?= partial('alert', ['type' => 'success', 'text' => 'Perfil guardado.']) ?>
```

`type` es `success`, `error`, `warning` o `info`, y decide el tono y el ícono;
`title` y `confirm` se pueden pasar si el texto por defecto del tipo no encaja.
La usan el layout público (resultado del formulario de contacto), el del panel
(el aviso de la última acción) y el login.

Con `cancel` la alerta **pregunta** en vez de avisar: dos botones, el destructivo
en rojo. Así montada y con `template`, el layout del panel deja un molde que
`alerts.js` clona cada vez que alguien pulsa una papelera — el diálogo se dibuja
una sola vez y en PHP, y el JavaScript sólo lo rellena con lo que trae el
disparador (`data-confirm-title`, `data-confirm-text`). Al aceptar, envía el
borrado por POST con el token del panel.

Es un `<dialog>` que llega abierto en el HTML: **se ve y se cierra sin
JavaScript**, porque su botón usa `method="dialog"`. `alerts.js` sólo añade lo
que el HTML no da — la asciende a modal (foco atrapado, Escape, resto de la
página inerte), la cierra al hacer clic fuera del panel, y cierra sola las de
éxito a los 4,2 s, con una barra que se detiene si el puntero o el teclado
entran en la alerta.

## Contenido dinámico

Todo el contenido visible sale de la base de datos `emiguzman`; no hay texto de perfil incrustado en las vistas.

| Tabla | Alimenta |
|---|---|
| `profile` | nombre, rol, headline, bio, contacto, CV, redes (fila única, `id = 1`) |
| `projects` + `project_technologies` + `project_metrics` + `project_pipeline_steps` | índice de proyectos (con la primera métrica de los destacados), detalle, resultados y pasos del flujo |
| `certifications` | filas de credenciales: título, emisor, emisión, vencimiento y enlace de verificación (el ID va en su `aria-label`) |
| `experiences` + `experience_highlights` | timeline de trayectoria |
| `technologies` | stack en texto plano agrupado por categoría en "Sobre mí" (el orden de las categorías es el del ENUM: Datos y Sistemas primero) |
| `contact_messages` | bandeja del formulario |
| `admin_users` | acceso al panel de administración |

Campos de control pensados para el panel: `is_published`, `is_featured` (los destacados van primero, ampliados y con su primera métrica en grande) y `has_pipeline`. `bento_size` se retiró en la migración `2026-10-02-narrativa-generalista.sql`.

### Cómo se ordena

No hay columna de orden. Se retiró de todas las tablas (migración
`2026-08-18-sin-orden-manual.sql`) porque era un dato que había que mantener a
mano y que repetía lo que ya decían otros:

- **Proyectos y experiencia** se ordenan por su intervalo de fechas, del más
  reciente al más antiguo. Lo que sigue abierto no tiene `ended_on`, así que
  cuenta como que termina hoy y encabeza la lista; después manda la fecha de
  fin y, en empate, la de inicio. Un proyecto sin ninguna fecha cae al final,
  donde se nota que le falta el dato.
- **Stack, certificaciones y servicios** no tienen un orden que mostrar: salen
  por su `id`, que es el orden en que se dieron de alta. El stack, además, se
  agrupa por categoría y dentro va por nombre, que es como se lee en "Sobre mí".
- **Las filas hijas** (stack de un proyecto, métricas, pasos del flujo, logros
  de un puesto) salen por su `id`. Al guardar se borran y se vuelven a insertar
  en el orden del formulario, así que el `id` *es* ese orden: guardarlo aparte
  era repetirlo.

Por lo mismo desapareció `experiences.is_current`: decía lo que ya decía la
fecha de fin y podía contradecirla. Ahora se deriva al leer
(`(ended_on IS NULL) AS is_current`), así que la fecha es la única versión del
hecho — dejar el "Fin" vacío es lo que marca el puesto actual.

El `id` que genera la base se ve en las tablas del panel, para poder nombrar una
fila sin ambigüedad. No se escribe desde ningún formulario: lo asigna el
`AUTO_INCREMENT`, que es el único que puede garantizar que no se repita.

> La tabla `home_metrics` quedó sin uso al retirarse la celda "lectura rápida" del hero.
> Se conserva en el esquema por si el panel de administración la retoma.

**Usuario semilla:** `admin` / `admin123` — cambiar antes de publicar.

## Sistema visual

Línea **modern minimal**. Las decisiones viven en `CLAUDE.md` y en `public/assets/css/tokens.scss`:

- **Concepto:** "Que funcione, y que se entienda." La inclinación hacia datos y hacia el cuidado de usuarios no se dice en el copy: se expresa con la jerarquía de proyectos (`is_featured`), la métrica visible, la sección "Cómo trabajo", el orden del stack y el color de señal.
- **La tipografía es la interfaz:** una sola familia variable, **Mona Sans** (pesos 200–900, ancho 75–125 %), autoalojada en `public/assets/fonts/mona-sans.woff2`. El ancho (`font-stretch`) es parte del diseño: cuerpo 100 %, títulos 108 %, nombre del hero 118 %. Sin iconos decorativos ni ilustraciones. Lo utilitario usa `.meta` (tamaño, tono y números tabulares; nunca mayúsculas).
- **Espacio antes que cajas:** no hay tarjetas. Cada sección usa un riel izquierdo con el título (sticky en escritorio) y una columna ancha con el contenido (`_section.scss`). Una línea de 1 px sólo separa filas de una lista.
- **Papel y tinta:** tema claro por defecto y oscuro automático con `prefers-color-scheme`. Tokens `--color-paper`, `--color-sheet`, `--color-ink*`, `--color-rule*`. Contraste AA en ambos temas.
- **Un solo color con significado:** `--color-signal` (verde de "estado operativo"). Sólo aparece en el punto de disponibilidad, las métricas, el último paso del método, el bloque "Resultado", el anillo de foco y las alertas de éxito. Nunca en botones (el primario es tinta).
- **Un solo momento de movimiento:** al cargar, cada palabra del nombre se "asienta" de `font-stretch: 75%` a `118%`. Es una excepción consciente a "sólo `transform` y `opacity`": ocurre una vez, en un único elemento y con cada palabra en su propia línea. Se espera a la fuente; sin ella o con movimiento reducido se muestra el estado final. Lo demás sólo responde a acciones del usuario (hover de filas, View Transitions del modal). Nada se anima al hacer scroll.
- **Sin JS** toda la landing se ve y navega, y el nombre aparece en su estado final.

## Pendiente

- Elegir qué proyectos se marcan como destacados: son la palanca principal de la jerarquía de la landing.
- Decidir si la tabla y el CRUD de servicios se eliminan definitivamente.
- Panel de administración: falta el alta de cuentas desde dentro (hoy sólo existe el usuario semilla) y subir las portadas de proyecto (`projects.cover_image`) en vez de escribir su ruta a mano — la foto del perfil y el CV ya se suben.
- Sustituir los datos de ejemplo de `database/seeds.sql` por la información real.
- Imágenes de portada de proyectos (`projects.cover_image`) en `public/assets/img/`. El CV y la foto ya se suben desde el panel.
