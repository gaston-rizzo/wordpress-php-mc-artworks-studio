# MC Artworks Studio

MC Artworks Studio fue un sitio web real que estuvo en producción, desarrollado sobre WordPress y WooCommerce para la venta de artworks digitales y la contratación de servicios de edición de imágenes, creación de artworks y fotomontajes realistas.

El proyecto utiliza una interfaz multimedia inspirada en la estética de un videojuego. Durante la primera visita, el usuario visualiza una introducción multimedia, selecciona una facción NOD o GDI y el sitio adapta su apariencia a esa elección.

## Tecnologías principales

- WordPress
- PHP
- WooCommerce
- MariaDB
- Blocksy + tema hijo `blocksy-child`
- Advanced Custom Fields (ACF)
- HUSKY / WOOF
- Xootix Side Cart
- WP Mail SMTP
- JavaScript / jQuery
- HLS.js
- Fancybox 4
- HTML5 / CSS3

## Qué incluye el sitio

- Introducción multimedia con video, música y animaciones.
- Selección de facción NOD / GDI.
- Apariencia visual adaptada a la facción elegida.
- Tienda de artworks digitales gestionada con WooCommerce.
- Previews de productos mediante video.
- Filtros de catálogo con AJAX.
- Bundles de artworks.
- Carrito lateral.
- Personalización mediante `Frame Color` y `Comic Balloon Text`.
- Checkout simplificado.
- Método de pago manual.
- Registro de pedidos mediante logs y envío de correos HTML.
- Galería multimedia.
- Sección de servicios de edición y creación de imágenes.

## Estructura del repositorio

```text
wordpress-php-mc-artworks-studio/
├── README-ES.md
├── README-EN.md
├── database/
│   └── u679645666_vDcAP.sql
├── docs/
│   ├── en/
│   │   ├── MC_Artworks_Studio_Technical_Documentation.pdf
│   │   └── MC_Artworks_Studio_Website_Visual_Guide.pdf
│   └── es/
│       ├── MC_Artworks_Studio_Documentacion_Tecnica.pdf
│       └── MC_Artworks_Studio_Guia_Visual.pdf
├── src/
│   └── wp-content/
│       ├── themes/
│       │   └── blocksy-child/
│       └── uploads/
│           ├── images/
│           ├── songs/
│           └── sounds/
├── video-sitio-faccion-A.mp4
└── video-sitio-faccion-B.mp4
```

`src/wp-content/themes/blocksy-child/` contiene el código propio del proyecto.

El repositorio también incluye la base de datos, documentación técnica y guías visuales en español e inglés, una selección de recursos multimedia y dos videos de demostración del sitio.

## Recursos multimedia

La instalación original utiliza imágenes, videos, música y efectos de sonido dentro de `wp-content/uploads`.

En el repositorio se incluyen únicamente algunos recursos multimedia:

```text
src/wp-content/uploads/images/
src/wp-content/uploads/songs/
src/wp-content/uploads/sounds/
```
> [!IMPORTANT]
> Los videos utilizados por el sitio y otros archivos multimedia pesados se excluyen deliberadamente para evitar almacenar archivos de gran tamaño en GitHub.
>
> Por este motivo, algunas secciones que dependen de videos y otros recursos multimedia no pueden reproducirse de forma completa únicamente con los archivos publicados en el repositorio.

## Instalación rápida

1. Instalar WordPress.
2. Instalar y activar el tema padre **Blocksy**.
3. Copiar `src/wp-content/themes/blocksy-child/` dentro de:

```text
wp-content/themes/blocksy-child/
```

4. Instalar:

```text
WooCommerce
Advanced Custom Fields
HUSKY / WOOF
Xootix Side Cart
WP Mail SMTP
```

5. Importar la base de datos incluida en `database/`.
6. Copiar el contenido de `src/wp-content/uploads/` dentro de `wp-content/uploads/`.
7. Actualizar las URLs y la configuración del entorno cuando corresponda.
8. Verificar las páginas, los campos ACF, la configuración de HUSKY / WOOF y Xootix Side Cart, el carrito, el checkout y SMTP.

El método de pago incluido en el proyecto es manual y no procesa pagos automáticamente mediante la API de PayPal.

> Para publicar el repositorio, el dump SQL debe utilizar una versión sanitizada sin datos de cuentas, hashes, correos ni configuración sensible.

## Documentación

El proyecto incluye documentación técnica y guía visual en español e inglés.

### Español

- [`docs/es/MC_Artworks_Studio_Documentacion_Tecnica.pdf`](docs/es/MC_Artworks_Studio_Documentacion_Tecnica.pdf)
- [`docs/es/MC_Artworks_Studio_Guia_Visual.pdf`](docs/es/MC_Artworks_Studio_Guia_Visual.pdf)

### English

- [`docs/en/MC_Artworks_Studio_Technical_Documentation.pdf`](docs/en/MC_Artworks_Studio_Technical_Documentation.pdf)
- [`docs/en/MC_Artworks_Studio_Website_Visual_Guide.pdf`](docs/en/MC_Artworks_Studio_Website_Visual_Guide.pdf)

La documentación técnica desarrolla en profundidad la arquitectura, WordPress, Blocksy, WooCommerce, ACF, multimedia, facciones NOD / GDI, tienda, bundles, carrito, checkout, pedidos, correos, base de datos, seguridad, instalación y mantenimiento.

## Demostración

El repositorio incluye dos videos del sitio en funcionamiento, cada uno mostrando una facción distinta.

```text
video-sitio-faccion-A.mp4
video-sitio-faccion-B.mp4
```
