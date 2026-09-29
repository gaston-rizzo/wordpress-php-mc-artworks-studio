# MC Artworks Studio

MC Artworks Studio is a website developed with WordPress and WooCommerce for selling digital artworks and offering image editing, artwork creation, and realistic photomontage services.

The project uses a multimedia interface inspired by video game aesthetics. During the first visit, the user sees a multimedia introduction, selects either the NOD or GDI faction, and the site adapts its appearance to that choice.

## Main technologies

- WordPress
- PHP
- WooCommerce
- MariaDB
- Blocksy + `blocksy-child` child theme
- Advanced Custom Fields (ACF)
- HUSKY / WOOF
- Xootix Side Cart
- WP Mail SMTP
- JavaScript / jQuery
- HLS.js
- Fancybox 4
- HTML5 / CSS3

## What the site includes

- Multimedia introduction with video, music, and animations.
- NOD / GDI faction selection.
- Visual appearance adapted to the selected faction.
- Digital artwork store managed with WooCommerce.
- Product previews using video.
- AJAX catalog filters.
- Artwork bundles.
- Side cart.
- Customization through `Frame Color` and `Comic Balloon Text`.
- Simplified checkout.
- Manual payment method.
- Order logging and HTML email delivery.
- Multimedia gallery.
- Image editing and creation services section.

## Repository structure

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

`src/wp-content/themes/blocksy-child/` contains the project's custom code.

The repository also includes the database, technical documentation and visual guides in Spanish and English, a selection of multimedia resources, and two demonstration videos of the site.

## Multimedia resources

The original installation uses images, videos, music, and sound effects stored inside `wp-content/uploads`.

Only some multimedia resources are included in the repository:

```text
src/wp-content/uploads/images/
src/wp-content/uploads/songs/
src/wp-content/uploads/sounds/
```

> [!IMPORTANT]
> Videos used by the site and other large multimedia files are deliberately excluded to avoid storing large files on GitHub.
>
> For this reason, some sections that depend on videos and other multimedia resources cannot be reproduced in full using only the files published in the repository.

## Quick installation

1. Install WordPress.
2. Install and activate the **Blocksy** parent theme.
3. Copy `src/wp-content/themes/blocksy-child/` into:

```text
wp-content/themes/blocksy-child/
```

4. Install:

```text
WooCommerce
Advanced Custom Fields
HUSKY / WOOF
Xootix Side Cart
WP Mail SMTP
```

5. Import the database included in `database/`.
6. Copy the contents of `src/wp-content/uploads/` into `wp-content/uploads/`.
7. Update the URLs and environment configuration when necessary.
8. Verify the pages, ACF fields, HUSKY / WOOF and Xootix Side Cart configuration, the cart, checkout, and SMTP.

The payment method included in the project is manual and does not process payments automatically through the PayPal API.

> Before publishing the repository, the SQL dump should use a sanitized version with no account data, hashes, email addresses, or sensitive configuration.

## Documentation

The project includes technical documentation and a visual guide in Spanish and English.

### Spanish

- [`docs/es/MC_Artworks_Studio_Documentacion_Tecnica.pdf`](docs/es/MC_Artworks_Studio_Documentacion_Tecnica.pdf)
- [`docs/es/MC_Artworks_Studio_Guia_Visual.pdf`](docs/es/MC_Artworks_Studio_Guia_Visual.pdf)

### English

- [`docs/en/MC_Artworks_Studio_Technical_Documentation.pdf`](docs/en/MC_Artworks_Studio_Technical_Documentation.pdf)
- [`docs/en/MC_Artworks_Studio_Website_Visual_Guide.pdf`](docs/en/MC_Artworks_Studio_Website_Visual_Guide.pdf)

The technical documentation covers the architecture, WordPress, Blocksy, WooCommerce, ACF, multimedia, NOD / GDI factions, store, bundles, cart, checkout, orders, emails, database, security, installation, and maintenance in depth.

## Demonstration

The repository includes two videos of the site in operation, each showing a different faction.

```text
video-sitio-faccion-A.mp4
video-sitio-faccion-B.mp4
```
