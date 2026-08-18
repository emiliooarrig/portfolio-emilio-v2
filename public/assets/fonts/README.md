# Fuentes del proyecto

El sitio usa dos familias y **ninguna monoespaciada**:

| Rol                                            | Familia               | Token CSS        |
| ---------------------------------------------- | --------------------- | ---------------- |
| Display (nombre del hero, H1–H4, nav, logo)    | `Sekuya`              | `--font-display` |
| Cuerpo, y también fechas / tags / métricas     | `Stack Sans Headline` | `--font-body`    |

Ninguna de las dos está en Google Fonts, así que se sirven desde aquí. Los
`@font-face` viven en `public/assets/css/fonts.scss` y esperan estos archivos
en esta carpeta:

```
sekuya.woff2                 ← preferido
sekuya.ttf                   ← respaldo, si no tienes el woff2
stack-sans-headline.woff2    ← preferido
stack-sans-headline.ttf      ← respaldo
```

Los nombres tienen que ser exactamente esos (minúsculas, con guiones) o el
navegador no los encontrará.

## Mientras los archivos no estén

La página **no se rompe**: cada familia cae en el respaldo declarado en
`tokens.scss` (`Segoe UI` → `system-ui` → `sans-serif`), así que se lee bien
pero pierde su identidad tipográfica. En la consola verás dos 404 de los
`<link rel="preload">` del layout hasta que los archivos existan.

## Si tienes `.ttf` u `.otf` y quieres el `.woff2`

`woff2` pesa alrededor de un 30 % menos y es lo que conviene servir. Con
Python y `fonttools` instalado:

```bash
pip install fonttools brotli
fonttools ttLib.woff2 compress sekuya.ttf
```

## Si algún día tienes más de un peso

`fonts.scss` declara **una sola cara por familia** cubriendo el rango
400–700, para que el navegador no falsifique la negrita deformando la letra.
Si consigues un corte bold real, añade su propia `@font-face` con
`font-weight: 700` y baja la existente a `400`.
