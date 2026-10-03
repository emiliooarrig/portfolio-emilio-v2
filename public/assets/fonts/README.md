# Fuentes del proyecto

El sitio usa **una sola familia**, variable y autoalojada:

| Archivo           | Familia     | Ejes                          | Token CSS     |
| ----------------- | ----------- | ----------------------------- | ------------- |
| `mona-sans.woff2` | `Mona Sans` | `wght` 200–900 · `wdth` 75–125 | `--font-sans` |

Mona Sans es de GitHub, con licencia SIL Open Font License. Se sirve desde aquí
(no se usa Google Fonts ni ningún CDN). El `@font-face` vive en
`public/assets/css/fonts.scss` y los tres layouts la precargan con
`<link rel="preload">`.

## De dónde sale

Release oficial: <https://github.com/github/mona-sans/releases> (se tomó la
v2.0.27). Del zip `mona-sans-webfonts-*.zip` se copia el archivo **variable de
estilo normal** con ancho y peso, y se renombra:

```
fonts/webfonts/variable/MonaSansVF[wdth,opsz,wght].woff2  →  mona-sans.woff2
```

El nombre tiene que ser exactamente `mona-sans.woff2`.

## Cómo se usa el ancho

Con `font-stretch: 75% 125%` declarado como rango en el `@font-face`, la
propiedad CSS `font-stretch` mueve el eje `wdth` directamente. Se usa
`font-stretch` y no `font-variation-settings` porque degrada bien con la
fuente de respaldo. Cuerpo 100 %, títulos 108 %, nombre del hero 118 % (y su
animación de entrada parte de 75 %).

Para comprobar los ejes del archivo:

```bash
pip install fonttools brotli
fonttools ttx -t fvar public/assets/fonts/mona-sans.woff2
```

## Si el archivo no está

La página **no se rompe**: cae en `system-ui` (y después `-apple-system`,
`Segoe UI`, `Roboto`, `sans-serif`) y sigue legible. El nombre del hero no se
anima con la fuente de respaldo: `main.js` espera a Mona Sans y, si no llega
en 1,2 s, muestra el estado final directamente. El `preload` del layout dará un
404 hasta que el archivo exista.
