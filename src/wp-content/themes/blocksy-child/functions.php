<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

// BEGIN ENQUEUE PARENT ACTION
// AUTO GENERATED - Do not modify or remove comment markers above or below:

if ( !function_exists( 'chld_thm_cfg_locale_css' ) ):
    function chld_thm_cfg_locale_css( $uri ){
        if ( empty( $uri ) && is_rtl() && file_exists( get_template_directory() . '/rtl.css' ) )
            $uri = get_template_directory_uri() . '/rtl.css';
        return $uri;
    }
endif;
add_filter( 'locale_stylesheet_uri', 'chld_thm_cfg_locale_css' );
         
if ( !function_exists( 'child_theme_configurator_css' ) ):
    function child_theme_configurator_css() {
        wp_enqueue_style( 'chld_thm_cfg_child', trailingslashit( get_stylesheet_directory_uri() ) . 'style.css', array( 'ct-main-styles','ct-woocommerce-styles','ct-admin-frontend-styles','ct-flexy-styles','ct-stackable-styles' ) );
    }
endif;

add_action( 'wp_enqueue_scripts', 'child_theme_configurator_css', 10 );
// END ENQUEUE PARENT ACTION


// [[[[wp_enqueue_scripts]]]]
// Es un hook (acción) de WordPress que se usa para agregar o “encolar” archivos CSS y JavaScript en el frontend (parte pública) de un sitio.
// Cargar Fancybox solo en tienda, categorías y etiquetas
add_action('wp_enqueue_scripts', 'cargar_fancybox_tienda');

// ======================================================
// Función que carga las librerias para iniciar fancybox.
// ======================================================
function cargar_fancybox_tienda() {
	
	// Cargar jQuery (ya viene en WordPress)
    wp_enqueue_script('jquery');
	
    if (is_shop() || is_product_category() || is_product_tag()) {
        wp_enqueue_style('fancybox-css', 'https://cdn.jsdelivr.net/npm/@fancyapps/ui/dist/fancybox.css', [], '4.0', 'all');
        wp_enqueue_script('fancybox-js', 'https://cdn.jsdelivr.net/npm/@fancyapps/ui/dist/fancybox.umd.js', [], '4.0', true);
    }
}

// =========================================
// Cargar el sistema de selección de facción
// =========================================
if (file_exists( get_stylesheet_directory() . '/faction-selection.php')) {
    require_once get_stylesheet_directory() . '/faction-selection.php';
}

// ======================================
// Cargar las animaciones de presentación
// ======================================
if (file_exists( get_stylesheet_directory() . '/animations.php')) {
    require_once get_stylesheet_directory() . '/animations.php';
}

// [[[hud_intro]]]
add_action('wp_head', 'hud_intro');

// ================
// Cargar hud-intro
// ================
function hud_intro() {
    // Verificar si estamos en la página "intro"
    $hud_intro = get_page_by_path('intro');
    if (!$hud_intro || !is_page($hud_intro->ID)) return;
		
	require_once get_stylesheet_directory() . '/hud-intro.php'; 
	if (function_exists('nod_gdi_hud_intro')) {
		nod_gdi_hud_intro(); 
    }
}

/* ==========================================================================================
 * CARGA DE CSS SEGÚN FACCION (PRODUCCIÓN)
 * ------------------------------------------------------------------------------------------
 * Este bloque encola el CSS correspondiente a la facción guardada en la cookie "faction".
 * 
 * Si la cookie existe y su valor es "gdi" → se carga gdi-faction-min.css
 * Si la cookie existe y su valor es "nod" → se carga nod-faction-min.css
 * Si la cookie NO existe → cargar CSS de GDI. Si no se carga nada da un pequeño error de 
 * estetica al seleccionar por primera vez una facción, por eso se carga GDI.
 * OPTIMIZADO PARA PRODUCCIÓN:
 * - Usa `filemtime()` para generar una versión basada en la fecha de modificación del archivo.
 *   Así, el navegador y el CDN solo descargan una nueva versión cuando el CSS realmente cambia.
 * - Elimina (dequeue) estilos previos antes de encolar los nuevos, evitando conflictos.
 * - No genera fallback, respetando el flujo de selección inicial de facción.
 * ========================================================================================== */
add_action('wp_enqueue_scripts', function() {
    // ============================
    // Detectar facción según cookie
    // ============================
    $faction = $_COOKIE['faction'] ?? 'gdi';

    // Si no existe la cookie, no cargamos ningún CSS
    if (empty($faction)) return;

    // ==================================================
    // Eliminar estilos previos (si existen)
    // ==================================================
    wp_dequeue_style('gdi-faction');
    wp_dequeue_style('nod-faction');

    // ==================================================
    // Encolar el CSS correcto según la facción
    // ==================================================
    if ($faction === 'gdi') {
        $css_file = get_stylesheet_directory() . '/gdi-faction-min.css';
        $version  = file_exists($css_file) ? filemtime($css_file) : '1.0';

        wp_enqueue_style(
            'gdi-faction',
            get_stylesheet_directory_uri() . '/gdi-faction-min.css',
            [],
            $version,
            'all'
        );

    } elseif ($faction === 'nod') {
        $css_file = get_stylesheet_directory() . '/nod-faction-min.css';
        $version  = file_exists($css_file) ? filemtime($css_file) : '1.0';

        wp_enqueue_style(
            'nod-faction',
            get_stylesheet_directory_uri() . '/nod-faction-min.css',
            [],
            $version,
            'all'
        );
    }
}, 999);

/* ==========================================================================================
 * 🧩 CARGA DE CSS SEGÚN FACCION (VERSIÓN DE DESARROLLO)
 * ------------------------------------------------------------------------------------------
 * Este bloque encola dinámicamente el CSS correspondiente a la facción definida
 * en la cookie "faction". Está pensado exclusivamente para entorno de desarrollo.
 * 
 * 🔹 Si la cookie tiene valor "gdi" → carga gdi-faction-min.css
 * 🔹 Si la cookie tiene valor "nod" → carga nod-faction-min.css
 * 🔹 Si la cookie NO existe o está vacía → no carga ningún CSS.
 * 
 * 🧠 OBJETIVO:
 * Forzar la recarga del CSS en cada actualización de página para evitar que el
 * navegador o el CDN sirvan versiones cacheadas. Esto garantiza que los cambios
 * en los estilos se reflejen de inmediato durante el desarrollo.
 * 
 * ⚙️ MECANISMO:
 * - Se obtiene la facción desde la cookie.
 * - Si no existe, se detiene la ejecución (return).
 * - Si existe, se genera un parámetro dinámico `?v=[timestamp]` con `time()`,
 *   haciendo que cada carga tenga una URL distinta para invalidar la caché.
 * 
 * 🚫 NO USAR EN PRODUCCIÓN:
 * En producción se debe usar `filemtime()` en lugar de `time()` para que
 * la versión del CSS solo cambie cuando el archivo se modifica realmente.
 * ========================================================================================== */
add_action('wp_enqueue_scripts', function() {
    // Detectar facción según cookie
    $faction = $_COOKIE['faction'] ?? '';

    // Si no existe la cookie o está vacía, no cargamos ningún CSS
    if (empty($faction)) return;

    // Directorios del tema
    $theme_dir = get_stylesheet_directory();
    $theme_uri = get_stylesheet_directory_uri();

    // Generar parámetro dinámico basado en la hora actual
    // Esto fuerza la recarga del archivo en cada refresh
    $timestamp = time();

    // Cargar el CSS correspondiente a la facción
    if ($faction === 'gdi') {
        $url = $theme_uri . '/gdi-faction-min.css?v=' . $timestamp;
        wp_enqueue_style('gdi-faction', $url, [], null, 'all');

    } elseif ($faction === 'nod') {
        $url = $theme_uri . '/nod-faction-min.css?v=' . $timestamp;
        wp_enqueue_style('nod-faction', $url, [], null, 'all');
    }
}, 999);

/* ==================================================================================================================
	Función que bloquea distintas cosas aplicado al sitio en general
	Bloqueo de selección de texto en toda la web, incluso arrastrando o haciendo doble clic.
	Bloqueo de clic derecho.
	Bloqueo de atajos comunes: Ctrl+U, Ctrl+C, Ctrl+S, Ctrl+A, Ctrl+P.
	Bloqueo de atajos avanzados de desarrollador:
	Ctrl+Shift+I → Dev Tools
	Ctrl+Shift+J → Consola
	Ctrl+Shift+C → Inspeccionar elemento
	Ctrl+Shift+K → Consola en Firefox
	Ctrl+Shift+M → Vista responsive
	Bloqueo de F12.
	Bloqueo de Ctrl+Alt+I (algunos navegadores).
==================================================================================================================== */
/*
add_action('wp_head','fxd_load_9382');function fxd_load_9382(){?><style>body{user-select:none!important;-webkit-user-select:none!important;-moz-user-select:none!important;-ms-user-select:none!important;}</style><script>document.addEventListener('contextmenu',function(e){e.preventDefault();});document.addEventListener('keydown',function(e){if(e.ctrlKey){const blockedKeys=['u','c','s','a','p','i'];if(blockedKeys.includes(e.key.toLowerCase())){e.preventDefault();}}if(e.ctrlKey&&e.shiftKey){const blockedShiftKeys=['i','j','c','k','m'];if(blockedShiftKeys.includes(e.key.toLowerCase())){e.preventDefault();}}if(e.key==='F12'){e.preventDefault();}if(e.ctrlKey&&e.altKey&&e.key.toLowerCase()==='i'){e.preventDefault();}});document.addEventListener('selectstart',function(e){e.preventDefault();});document.addEventListener('mousedown',function(e){if(e.detail>1)e.preventDefault();});</script><?php }
*/

/* ==================================================================================================================
Bloqueo de selección de texto (excepto en our-services)
Bloqueo de arrastre / drag
Bloqueo de doble clic para seleccionar
Bloqueo de clic derecho
/* ================================================================================================================== */
add_action('wp_head','fxd_load_9382'); function fxd_load_9382(){ if(is_page('our-services')){ echo '<script>document.addEventListener("contextmenu",e=>e.preventDefault());document.addEventListener("mousedown",e=>{if(e.detail>1)e.preventDefault();});document.addEventListener("dragstart",e=>e.preventDefault());</script>'; } else { echo '<style>body{user-select:none!important;-webkit-user-select:none!important;-moz-user-select:none!important;-ms-user-select:none!important;}</style><script>document.addEventListener("selectstart",e=>e.preventDefault());document.addEventListener("mousedown",e=>{if(e.detail>1)e.preventDefault();});document.addEventListener("dragstart",e=>e.preventDefault());document.addEventListener("contextmenu",e=>e.preventDefault());</script>'; } }

// [[[[template_redirect]]]]
// El hook template_redirect se ejecuta justo antes de que WordPress cargue la plantilla final (el archivo PHP del tema que muestra la página).
// En otras palabras es el último momento donde podés detener la carga normal de la página y hacer algo distinto, como:
// 		- Redirigir al usuario con wp_redirect().
// 		- Cargar otro contenido.
// 		- Bloquear el acceso.
// 		- Aplicar condiciones (por ejemplo, si no hay cookie, si no está logueado, etc.).
add_action('template_redirect', 'insertar_presentacion');

/* =========================================================================================================================================
 * Función que inserta una animación introductoria (GDI o NOD) y redirige a la pagina de intro la primera vez que el usuario entra al sitio.  
 * - Si no existe la cookie 'visited_intro':
 *   1. Se crea una cookie válida por 1 año.
 *   2. Se decide entre GDI o NOD para mostrar la animación de facción (50/50).
 *   3. Dentro de esa facción, se elige 1 de 4 animaciones al azar (25% cada una).
 *   4. Se ejecuta la animación y luego el overlay general.
 * ========================================================================================================================================= */
function insertar_presentacion() {

    // Evitar que la animación se muestre en la página "intro" o la pagina "faction-selection"
	if (is_page(['intro', 'faction-selection'])) return;

    // Si no existe la cookie, ejecutar la animación y dirigirse a la pagina intro
    if (empty($_COOKIE['faction'])) {

        // Determinar si estamos en un tipo de página donde queremos mostrar la animación
        // Cubre:
        // - páginas normales (is_page)
        // - posts (is_single)
        // - archivos (is_archive)
        // - WooCommerce (tienda, productos, categorías, tags)
        if (is_page() || is_single() || is_archive() ||
            (function_exists('is_woocommerce') && is_woocommerce()) ||
            is_shop() || is_product() || is_product_category() || is_product_tag()
        ) {
            // Paso 1: elegir facción con probabilidad 50/50
            // rand(0,1) devuelve 0 o 1 al azar
            // Si es 1 → GDI, si es 0 → NOD
            $faccion = rand(0,1) ? 'GDI' : 'NOD';

            // Paso 2: definir animaciones disponibles por facción
            $animaciones = [
                'GDI' => [
                    'insertar_animacion_apocalipsis_GDI_UNO',
                    'insertar_animacion_apocalipsis_GDI_DOS',
                    'insertar_animacion_apocalipsis_GDI_TRES',
                    'insertar_animacion_apocalipsis_GDI_CUATRO',
                ],
                'NOD' => [
                    'insertar_animacion_apocalipsis_NOD_UNO',
                    'insertar_animacion_apocalipsis_NOD_DOS',
                    'insertar_animacion_apocalipsis_NOD_TRES',
                    'insertar_animacion_apocalipsis_NOD_CUATRO',
                ]
            ];

            // Paso 3: elegir una animación al azar dentro de la facción seleccionada
            // array_rand devuelve un índice aleatorio del array
            $funcion = $animaciones[$faccion][array_rand($animaciones[$faccion])];

            // Paso 4: ejecutar la animación elegida, solo si la función existe
            if (function_exists($funcion)) {
                $funcion();
            }
			
			// === Overlay con imagen a pantalla completa ===
            $uploads_dir = wp_get_upload_dir();
            $image_path = trailingslashit($uploads_dir['baseurl']) . 'images/image7.jpg';
			
			// === Overlay con imagen a pantalla completa ===
            ?>
            <style>
                #transition-cover {
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100vw;
                    height: 100vh;                    
					background-image: url('<?php echo esc_url($image_path); ?>');
                    background-size: cover;
                    background-position: center;
                    opacity: 0;
                    z-index: 9999;
                    transition: opacity 1s ease;
                }
                #transition-cover.show {
                    opacity: 1;
                }
            </style>

            <div id="transition-cover"></div>

            <script>
                // Mostrar la imagen de cobertura después de la animación
                setTimeout(function() {
                    const cover = document.getElementById('transition-cover');
                    cover.classList.add('show');
                }, 4000); // cuando termina tu animación (~4 seg)

                // Redirigir después de mostrar la imagen
                setTimeout(function() {
                    window.location.href = "<?php echo home_url('/intro'); ?>";
                }, 6000); // 2s después de mostrar la imagen
            </script>
            <?php
			
			// Redirigir a la página "intro"
        	wp_redirect(home_url('/intro'));
        	exit;
        }
    }
}

// ################################################################################################################################################
// ################################################################################################################################################
// ################################################################################################################################################
// 											XOOTIX
// ################################################################################################################################################
// ################################################################################################################################################
// ################################################################################################################################################

// [[[[wp_footer]]]]
// Hook para inyectar el script al final del body
add_action('wp_footer', 'eliminar_boton_xootix_checkout');

/* ===================================================================================================
 * Función que elimina el botón "Checkout" del mini-carrito de Xootix.  
 * Comportamiento:
 * - Utiliza un MutationObserver para detectar dinámicamente cuando el botón aparece en el DOM.
 * - Una vez detectado, el botón se elimina.  
 * - Esto es útil si el mini-carrito se carga dinámicamente vía AJAX y el botón aparece después de que 
 * la página ya se cargó.
 * =================================================================================================== */
function eliminar_boton_xootix_checkout() {
	
    // Evitar que la función se ejecute en la página 'gallery' o en la página de inicio
    if (is_page('gallery') || is_page('our-services') || is_front_page()) return;
    ?>
    <script>
        // Esperar a que todo el contenido del DOM esté cargado
        document.addEventListener('DOMContentLoaded', () => {

            // Crear un observador para detectar cambios en el DOM
            const observer = new MutationObserver(() => {
                
                // Buscar el botón "Checkout" del mini-cart Xootix
                const checkoutBtn = document.querySelector('.xoo-wsc-ft-btn-checkout');

                // Si el botón existe, eliminarlo del DOM
                if (checkoutBtn) checkoutBtn.remove();
            });

            // Iniciar la observación sobre todo el body, incluyendo nodos hijos
            observer.observe(document.body, { childList: true, subtree: true });
        });
    </script>
    <?php
}

// ################################################################################################################################################
// ################################################################################################################################################
// ################################################################################################################################################
// 											HOME
// ################################################################################################################################################
// ################################################################################################################################################
// ################################################################################################################################################

// [[[[wp_head]]]]
add_action('wp_head', 'home');

/* =========================================================================
 * Función que personaliza el HOME  
 * - Cambia entre GDI y NOD según la cookie 'faction'
 * - Video principal en formato HLS (.m3u8) con precarga del primer segmento
 * - Modal fullscreen con el mismo video
 * - Efecto de apertura tipo KlingAI con óvalo brillante expansivo
 * - Si la facción es NOD, muestra una imagen temporal al abrir el modal
 * ========================================================================= */ 
function home() {

  if (!is_front_page() && !is_home()) return;

  // Detectar facción (puede venir de cookie o forzado)
  $faction = $_COOKIE['faction'] ?? 'gdi';
  
  // Variables dinámicas
  $title_text = ($faction === 'gdi') ? 'GDI' : 'NOD';
  $video_src  = ($faction === 'gdi') ? '/videos/gdi-home/gdi-home.m3u8' : '/videos/nod-home/nod-home.m3u8';
  ?>

  <!-- === SECCIÓN HERO PRINCIPAL === -->
  <section class="hero-video-section">
    <div class="oval-video-wrapper">
      <div class="home-loading-text">
        <span class="lt-line lt-initializing">INITIALIZING</span>
        <span class="lt-line lt-<?php echo esc_attr($faction); ?>"><?php echo esc_html($title_text); ?></span>
        <span class="lt-line lt-interface">INTERFACE...</span>
      </div>
      <video autoplay muted loop playsinline id="header-video">
        <source type="video/mp4">
      </video>
    </div>
    <div class="hero-content" id="hero-content">
      <h1 class="hero-hud-title"><?php echo esc_html($title_text); ?> COMMAND ONLINE</h1>
      <p class="hero-hud-subtitle">
        Welcome to MC Artworks STUDIO<br>
        <span>Design, creativity and visual style</span>
      </p>
      <a href="#" class="hero-hud-btn">ENTER INTERFACE</a>
    </div>
    <div class="hud-grid"></div>
  </section>

  <!-- === MODAL FULLSCREEN VIDEO === -->
  <div class="gdi-modal" id="gdiModal">
    <div class="gdi-modal-content">
      <button class="gdi-close" id="closeModal"><span>×</span></button>
	  <video id="fullVideo" autoplay muted playsinline preload="auto"></video>
    </div>
  </div>

  <!-- === SCRIPT HLS + EFECTOS === -->
  <script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>
  <script>
	  document.addEventListener('DOMContentLoaded', function() {
		const faction = '<?php echo esc_js($faction); ?>';
		const hlsSource = '<?php echo esc_js($video_src); ?>';	  
		const video = document.getElementById('header-video');
		const loadingText = document.querySelector('.home-loading-text');
		const heroContent = document.getElementById('hero-content');

		// === Precarga manual del primer segmento ===
		fetch(hlsSource)
		  .then(response => response.text())
		  .then(manifest => {
			const firstSegment = manifest.match(/^(?!#).*\.ts/m);
			if (firstSegment && firstSegment[0]) {
			  const basePath = hlsSource.substring(0, hlsSource.lastIndexOf('/') + 1);
			  fetch(basePath + firstSegment[0]);
			}
		  })
		  .catch(() => {});

		// === Inicialización HLS ===
		function initHLS(videoElement) {
		  if (!videoElement) return;
		  if (Hls.isSupported()) {
			const hls = new Hls({ maxBufferLength: 10, startLevel: 0, autoStartLoad: true });
			hls.loadSource(hlsSource);
			hls.attachMedia(videoElement);
			hls.on(Hls.Events.MANIFEST_PARSED, () => videoElement.play());
		  } else if (videoElement.canPlayType('application/vnd.apple.mpegurl')) {
			videoElement.src = hlsSource;
			videoElement.addEventListener('loadedmetadata', () => videoElement.play());
		  }
		}

		initHLS(video);
		initHLS(document.getElementById('fullVideo'));

		// === EFECTO DE APERTURA ===
		function startIntroSequence() {
		  if (video.classList.contains('loaded')) return; // Evitar repetición
		  video.classList.add('loaded');

		  // Fade-out del texto
		  loadingText.classList.add('hide');

		  // Remover después del fade (sin corte)
		  setTimeout(() => {
			if (loadingText && loadingText.parentNode) {
			  loadingText.parentNode.removeChild(loadingText);
			}
		  }, 1000); // 1s = transición + margen
		}

		// === CUANDO EL VIDEO ESTÁ LISTO PARA MOSTRAR ===
		video.addEventListener('canplay', () => {
		  // Esperar unos milisegundos para asegurar que el frame se dibuje en pantalla
		  setTimeout(() => {
			startIntroSequence();

			// Ahora sí, expandimos el óvalo y mostramos contenido
			document.querySelector('.oval-video-wrapper').classList.add('expand');
			setTimeout(() => heroContent.classList.add('visible'), 1000);
		  }, 120); // ~1 frame y medio de retraso, imperceptible pero clave
		});

		// --- OPCIONAL: respaldo si el evento canplay falla (muy raro) ---
		setTimeout(() => {
		  if (!video.classList.contains('loaded')) startIntroSequence();
		}, 8000);

		// --- IMPORTANTE ---
		// Cuando el video tiene su primer frame listo, recién ahí expandimos el óvalo
		video.addEventListener('loadeddata', () => {
		  if (video.readyState >= 2) {
			// Llamamos a la secuencia (texto fuera, etc.)
			startIntroSequence();
			// Y ahora sí, expandimos el óvalo y mostramos contenido
			document.querySelector('.oval-video-wrapper').classList.add('expand');
			setTimeout(() => heroContent.classList.add('visible'), 1000);
		  }
		});

		// Ejecutar secuencia cuando el video cargue o empiece
		video.addEventListener('loadeddata', startIntroSequence);
		video.addEventListener('play', startIntroSequence);

		// === CONTROL DEL MODAL ===
		const openBtn = document.querySelector('.hero-hud-btn');
		const modal = document.getElementById('gdiModal');
		const closeBtn = document.getElementById('closeModal');
		const fullVideo = document.getElementById('fullVideo');
		const menuBtn = document.getElementById('zajMenu');

		// Variables globales para manejar los timeouts
		let nodShowTimeout, nodHideTimeout, nodRemoveTimeout;
		let lastTime = 0; // Para detectar reinicio manual
		  
		if (faction === 'gdi') {
		  		fullVideo.addEventListener('ended', () => {
				if (modal.classList.contains('active')) {
					  fullVideo.currentTime = 0;
					  fullVideo.play();
					}
			});
		}
		  
		if (openBtn && modal && closeBtn) {
		  openBtn.addEventListener('click', (e) => {
			e.preventDefault();
			modal.classList.add('active');
			document.body.classList.add('no-interaction');
			if (menuBtn) {
			  menuBtn.disabled = true;
			  menuBtn.classList.add('zaj-menu--disabled');
			}

			fullVideo.currentTime = 0;
			fullVideo.play();

			// === EFECTO IMAGEN TEMPORAL SOLO PARA NOD ===
			if (faction === 'nod') {

			  const showNodImage = () => {
					// Limpiar imágenes anteriores
					document.querySelectorAll('.nod-fade-image').forEach(img => img.remove());

					// Crear imagen
					const nodImage = document.createElement('img');
					nodImage.src = '/images/wuhrer-kucan.jpg';
					nodImage.className = 'nod-fade-image';
					document.body.appendChild(nodImage);

					// Mostrar después de 16.5s, mantener 6.5s, luego fade out
					clearTimeout(nodShowTimeout);
					clearTimeout(nodHideTimeout);
					clearTimeout(nodRemoveTimeout);

					nodShowTimeout = setTimeout(() => {
					  nodImage.classList.add('visible');
					  nodHideTimeout = setTimeout(() => {
						nodImage.classList.remove('visible');
						nodRemoveTimeout = setTimeout(() => nodImage.remove(), 1500);
					  }, 6000);
					}, 16500);
				  };

				  // Primera ejecución
				  showNodImage();

				  // Detectar reinicio de video aunque esté en loop
				  fullVideo.addEventListener('timeupdate', () => {
					if (fullVideo.currentTime < lastTime && modal.classList.contains('active')) {
					  // Video reinició
					  showNodImage();
					}
					lastTime = fullVideo.currentTime;
				  });

				  // Loop manual (por compatibilidad)
				  fullVideo.addEventListener('ended', () => {
					if (modal.classList.contains('active')) {
					  fullVideo.currentTime = 0;
					  fullVideo.play();
					  showNodImage();
					}
				  });
				}		
		  });

		  closeBtn.addEventListener('click', () => {
			modal.classList.remove('active');
			document.body.classList.remove('no-interaction');
			fullVideo.pause();

			if (menuBtn) {
			  menuBtn.disabled = false;
			  menuBtn.classList.remove('zaj-menu--disabled');
			}

			// Limpiar imagen y timeouts SOLO cuando se cierra el modal
			clearTimeout(nodShowTimeout);
			clearTimeout(nodHideTimeout);
			clearTimeout(nodRemoveTimeout);
			document.querySelectorAll('.nod-fade-image').forEach(img => img.remove());
		  });
		}
	  });
  </script>

  <style>	  
	  .home-loading-text {
	  	opacity: 1;
		transition: opacity 1s cubic-bezier(0.4, 0, 0.2, 1);
	  }	  
	  
	  /* === EFECTO DE DESVANECIMIENTO DEL TEXTO === */
	  .home-loading-text.hide {
	  	opacity: 0;
	  	transition: opacity 1.2s ease-in-out;
	  	pointer-events: none;
	 }

	/* === IMAGEN TEMPORAL NOD (VERSIÓN GRANDE) === */
	.nod-fade-image {
	  position: fixed;
	  bottom: 40px;
	  right: 40px;
	  width: 520px;          /* antes 220px — ajustá este valor según gusto */
	  max-width: 35vw;       /* limita ancho en pantallas chicas */
	  height: auto;
	  opacity: 0;
	  pointer-events: none;
	  transition: opacity 1.5s ease-in-out;
	  z-index: 9999;
	  border-radius: 14px;
	  box-shadow:
		0 0 35px rgba(255, 0, 0, 0.35),
		0 0 65px rgba(255, 100, 0, 0.25); /* brillo rojizo NOD */
	  border: 2px solid rgba(255, 40, 0, 0.8);
	}

	.nod-fade-image.visible {
	  opacity: 1;
	}

  </style>

  <?php
}

// [[[[wp_head]]]]
add_action('wp_head', 'mostrar_overlay_faccion_inicio');

/* ==================================================================================================================
 * Agrega un overlay de carga según la facción seleccionada, pero solamente si se viene desde la selección de facción
 * SOLO en la página de inicio o home.  
 * Funcionamiento:
 * 1. Se dispara en 'wp_head' para que el overlay aparezca antes de que se renderice la página.
 * 2. Verifica que estemos en la página de inicio/home.
 * 3. Comprueba si el usuario viene de la selección de facción mediante la cookie 'from_faction_selection'.
 * 4. Según la facción (GDI o NOD), muestra el overlay correspondiente.
 * 5. Elimina la cookie temporal para que el overlay no se repita al refrescar la página.
 * =================================================================================================================== */
function mostrar_overlay_faccion_inicio() {
    // Solo ejecutar en front page o home
    if (!is_front_page() && !is_home()) return;

    // Verifica si el usuario viene de la selección de facción
    if (isset($_COOKIE['from_faction_selection'])) {
        // Obtiene la facción desde cookie, por defecto 'gdi'
        $faction = $_COOKIE['faction'] ?? 'gdi';

        // Muestra el overlay según la facción
        if ($faction === 'gdi') {
            mostrar_loading_GDI_overlay();
        } elseif ($faction === 'nod') {
            mostrar_loading_NOD_overlay();
        }

        // Borra la cookie temporal para que no se repita el overlay al refrescar
        setcookie('from_faction_selection', '', time() - 3600, '/');
    }
}

// ==========================================================================================
// Pantalla de carga GDI con “ACCESS GRANTED” (azul-dorado estilo C&C3) y apertura automática
// ==========================================================================================
function mostrar_loading_GDI_overlay() {    
    ?>
    <style>		
    /* === Overlay GDI === */
    #loading-overlay {
        display: flex;
        align-items: center;
        justify-content: center;
        position: fixed;
        inset: 0;
        background: linear-gradient(-45deg, #0a0f1a, #00224e, #0a437a, #001f3f);
        background-size: 400% 400%;
        animation: gradientBG 6s ease infinite;
        z-index: 999999999 !important;
        font-family: 'Share Tech Mono', monospace;
        color: #ffe38f;
        flex-direction: column;
        overflow: hidden;
        opacity: 1;
        visibility: visible;
        transition: opacity 0.8s ease, visibility 0.8s ease;
    }

    @keyframes gradientBG {
        0% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
        100% { background-position: 0% 50%; }
    }

    #loading-overlay.hidden {
        opacity: 0;
        visibility: hidden;
    }

    #loading-overlay::before {
        content: "";
        position: absolute;
        inset: 0;
        background: repeating-linear-gradient(
            to bottom,
            rgba(255, 217, 89, 0.08) 0px,
            rgba(255, 217, 89, 0.08) 2px,
            transparent 2px,
            transparent 4px
        );
        pointer-events: none;
        z-index: 1;
    }

    .hud-container {
        position: relative;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        z-index: 2;
        height: 100%;
        width: 100%;
        min-height: 100vh;
    }

    /* === Radar === */
    .radar {
        position: relative;
        width: 220px;
        height: 220px;
        border-radius: 50%;
        border: 2px solid #ffd24c;
        background: radial-gradient(circle, rgba(255,223,89,0.15) 0%, transparent 70%);
        box-shadow: 0 0 20px #ffd24c, 0 0 40px #ffb300 inset;
        overflow: hidden;
        transition: opacity 0.5s ease;
    }

    .radar.fade-out {
        opacity: 0;
    }

    .radar::before, .radar::after {
        content: "";
        position: absolute;
        background: #ffd24c;
    }

    .radar::before {
        width: 2px;
        height: 100%;
        left: 50%;
        top: 0;
        transform: translateX(-50%);
        opacity: 0.5;
    }

    .radar::after {
        width: 100%;
        height: 2px;
        top: 50%;
        left: 0;
        transform: translateY(-50%);
        opacity: 0.5;
    }

    .radar-sweep {
        position: absolute;
        width: 100%;
        height: 100%;
        border-radius: 50%;
        background: conic-gradient(
            from 0deg,
            rgba(255, 223, 89, 0.35) 0deg,
            rgba(255, 223, 89, 0.0) 60deg
        );
        animation: rotate 3s linear infinite;
    }

    @keyframes rotate {
        from { transform: rotate(0deg); }
        to   { transform: rotate(360deg); }
    }

    .blip {
        position: absolute;
        width: 8px;
        height: 8px;
        background: #fff2a6;
        border-radius: 50%;
        box-shadow: 0 0 8px #ffe38f, 0 0 18px #ffb300;
        animation: blinkBlip 1.5s infinite;
    }

    @keyframes blinkBlip {
        0%, 100% { opacity: 0; transform: scale(0.5); }
        50% { opacity: 1; transform: scale(1); }
    }

    .loading-text {
        font-size: 20px;
        letter-spacing: 0.15em;
        text-transform: uppercase;
        color: #ffe38f;
        text-shadow: 0 0 8px #ffdb5c, 0 0 18px #ffd24c;
        animation: blink 1.5s infinite;
        text-align: center;
        position: relative;
        z-index: 2;
        margin-top: 35px;
        margin-left: 40px;
    }

    @keyframes blink {
        0%, 49% { opacity: 1; }
        50%, 100% { opacity: 0.3; }
    }

	/* === ACCESS GRANTED === */
    .access-granted {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%) scale(0.9);
        font-size: 60px;
        letter-spacing: 0.25em;
        text-transform: uppercase;
        color: #ffffff;
        text-shadow:
            0 0 10px #00baff,
            0 0 25px #00baff,
            0 0 45px #0094ff,
            0 0 70px #005bbb;
        opacity: 0;
        animation: accessIn 1s ease forwards, pulseGlow 1.5s infinite 1s;
        z-index: 3;
    }

    @keyframes accessIn {
        0% { opacity: 0; transform: translate(-50%, -50%) scale(0.9); }
        60% { opacity: 1; transform: translate(-50%, -50%) scale(1.05); }
        100% { opacity: 1; transform: translate(-50%, -50%) scale(1); }
    }

    @keyframes pulseGlow {
        0%, 100% {
            text-shadow: 0 0 15px #00c8ff, 0 0 35px #00baff, 0 0 70px #0077ff;
        }
        50% {
            text-shadow: 0 0 30px #33d6ff, 0 0 60px #00c8ff, 0 0 100px #005bbb;
        }
    }
		
    html.loading-active, body.loading-active {
        overflow: hidden !important;
        height: 100vh !important;
        touch-action: none;
    }
    </style>

    <div id="loading-overlay">
        <div class="hud-container">
            <div class="radar" id="radar">
                <div class="radar-sweep"></div>
            </div>
            <div class="loading-text" id="loading-text">Initializing GDI Interface...</div>
        </div>
    </div>

    <link href="https://fonts.googleapis.com/css2?family=Share+Tech+Mono&display=swap" rel="stylesheet">

    <script>
    document.documentElement.classList.add('loading-active');
    document.body.classList.add('loading-active');

    const radar = document.getElementById('radar');
    const overlay = document.getElementById('loading-overlay');
    const loadingText = document.getElementById('loading-text');

    function createBlip() {
        const blip = document.createElement('div');
        blip.classList.add('blip');
        const r = Math.random() * 100;
        const angle = Math.random() * Math.PI * 2;
        const x = 110 + r * Math.cos(angle);
        const y = 110 + r * Math.sin(angle);
        blip.style.left = `${x}px`;
        blip.style.top = `${y}px`;
        radar.appendChild(blip);
        setTimeout(() => blip.remove(), 2000);
    }
    setInterval(createBlip, 1000);

    function hideLoadingOverlay() {
        overlay.classList.add('hidden');
        document.documentElement.classList.remove('loading-active');
        document.body.classList.remove('loading-active');
        setTimeout(() => overlay.remove(), 800);
    }

    document.addEventListener('DOMContentLoaded', () => {
        setTimeout(() => {
            radar.classList.add('fade-out');
            loadingText.style.visibility = 'hidden';

            setTimeout(() => {
                const granted = document.createElement('div');
                granted.className = 'access-granted';
                granted.textContent = 'ACCESS GRANTED';
                overlay.querySelector('.hud-container').appendChild(granted);

                setTimeout(() => {
                    const btn = document.getElementById('zajMenu');
                    const zajOverlay = document.getElementById('zajOverlay');									
					window.menuOpenedAutomatically = true;
                    if (btn && zajOverlay) btn.click();
                    hideLoadingOverlay();
                }, 1000);
            }, 500);
        }, 2000);
    });
    </script>
    <?php
}

// =========================================================================================
// Pantalla de carga NOD con “ACCESS GRANTED” (rojo épico estilo C&C3) y apertura automática
// =========================================================================================
function mostrar_loading_NOD_overlay() {    
    ?>
    <style>
    /* === Overlay NOD === */
    #loading-overlay {
        display: flex;
        align-items: center;
        justify-content: center;
        position: fixed;
        inset: 0;
        background: #050000;		
        z-index: 999999999 !important;
        font-family: 'Share Tech Mono', monospace;
        color: #ff2a2a;
        flex-direction: column;
        overflow: hidden;
        opacity: 1;
        visibility: visible;
        transition: opacity 0.8s ease, visibility 0.8s ease;
    }

    #loading-overlay.hidden {
        opacity: 0;
        visibility: hidden;
    }

    #loading-overlay::before {
        content: "";
        position: absolute;
        inset: 0;
        background: repeating-linear-gradient(
            to bottom,
            rgba(255, 0, 0, 0.08) 0px,
            rgba(255, 0, 0, 0.08) 2px,
            transparent 2px,
            transparent 4px
        );
        pointer-events: none;
        z-index: 1;
    }

    .hud-container {
        position: relative;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        z-index: 2;
        height: 100%;
        width: 100%;
        /* 🔥 Fijamos el espacio vertical para que nada se mueva */
        min-height: 100vh;
    }

    /* === Radar === */
    .radar {
        position: relative;
        width: 220px;
        height: 220px;
        border-radius: 50%;
        border: 2px solid #ff2a2a;
        background: radial-gradient(circle, rgba(255,42,42,0.15) 0%, transparent 70%);
        box-shadow: 0 0 20px #ff2a2a;
        overflow: hidden;
        transition: opacity 0.5s ease;
    }

    .radar.fade-out {
        opacity: 0;
    }

    .radar::before, .radar::after {
        content: "";
        position: absolute;
        background: #ff2a2a;
    }

    .radar::before {
        width: 2px;
        height: 100%;
        left: 50%;
        top: 0;
        transform: translateX(-50%);
        opacity: 0.4;
    }

    .radar::after {
        width: 100%;
        height: 2px;
        top: 50%;
        left: 0;
        transform: translateY(-50%);
        opacity: 0.4;
    }

    .radar-sweep {
        position: absolute;
        width: 100%;
        height: 100%;
        border-radius: 50%;
        background: conic-gradient(
            from 0deg,
            rgba(255,42,42,0.35) 0deg,
            rgba(255,42,42,0.0) 60deg
        );
        animation: rotate 3s linear infinite;
    }

    @keyframes rotate {
        from { transform: rotate(0deg); }
        to   { transform: rotate(360deg); }
    }

    .blip {
        position: absolute;
        width: 8px;
        height: 8px;
        background: #ff2a2a;
        border-radius: 50%;
        box-shadow: 0 0 8px #ff2a2a, 0 0 15px #ff2a2a;
        animation: blinkBlip 1.5s infinite;
    }

    @keyframes blinkBlip {
        0%, 100% { opacity: 0; transform: scale(0.5); }
        50% { opacity: 1; transform: scale(1); }
    }

    .loading-text {
        font-size: 20px;
        letter-spacing: 0.15em;
        text-transform: uppercase;
        color: #ff2a2a;
        text-shadow: 0 0 5px #ff2a2a, 0 0 15px #ff2a2a;
        animation: blink 1.5s infinite;
        text-align: center;
        position: relative;
        z-index: 2;
        margin-top: 35px;
        margin-left: 40px;
    }

    @keyframes blink {
        0%, 49% { opacity: 1; }
        50%, 100% { opacity: 0.3; }
    }

    /* === ACCESS GRANTED === */
    .access-granted {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%) scale(0.9);
        font-size: 60px;
        letter-spacing: 0.25em;
        text-transform: uppercase;
        color: #ff1a1a;
        text-shadow:
            0 0 10px #ff1a1a,
            0 0 25px #ff1a1a,
            0 0 45px #ff1a1a,
            0 0 70px #b00000;
        opacity: 0;
        animation: accessIn 1s ease forwards, pulseGlow 1.5s infinite 1s;
        z-index: 3;
    }

    @keyframes accessIn {
        0% { opacity: 0; transform: translate(-50%, -50%) scale(0.9); }
        60% { opacity: 1; transform: translate(-50%, -50%) scale(1.05); }
        100% { opacity: 1; transform: translate(-50%, -50%) scale(1); }
    }

    @keyframes pulseGlow {
        0%, 100% {
            text-shadow: 0 0 15px #ff2a2a, 0 0 35px #ff2a2a, 0 0 70px #b00000;
        }
        50% {
            text-shadow: 0 0 30px #ff4d4d, 0 0 60px #ff1a1a, 0 0 100px #e60000;
        }
    }

    html.loading-active, body.loading-active {
        overflow: hidden !important;
        height: 100vh !important;
        touch-action: none;
    }
    </style>

    <div id="loading-overlay">
        <div class="hud-container">
            <div class="radar" id="radar">
                <div class="radar-sweep"></div>
            </div>
            <div class="loading-text" id="loading-text">Initializing NOD Interface...</div>
        </div>
    </div>

    <link href="https://fonts.googleapis.com/css2?family=Share+Tech+Mono&display=swap" rel="stylesheet">

    <script>
    document.documentElement.classList.add('loading-active');
    document.body.classList.add('loading-active');

    const radar = document.getElementById('radar');
    const overlay = document.getElementById('loading-overlay');
    const loadingText = document.getElementById('loading-text');

    // Crear blips
    function createBlip() {
        const blip = document.createElement('div');
        blip.classList.add('blip');
        const r = Math.random() * 100;
        const angle = Math.random() * Math.PI * 2;
        const x = 110 + r * Math.cos(angle);
        const y = 110 + r * Math.sin(angle);
        blip.style.left = `${x}px`;
        blip.style.top = `${y}px`;
        radar.appendChild(blip);
        setTimeout(() => blip.remove(), 2000);
    }
    setInterval(createBlip, 1000);

    function hideLoadingOverlay() {
        overlay.classList.add('hidden');
        document.documentElement.classList.remove('loading-active');
        document.body.classList.remove('loading-active');
        setTimeout(() => overlay.remove(), 800);
    }

    // Secuencia NOD
    document.addEventListener('DOMContentLoaded', () => {
        setTimeout(() => {
            radar.classList.add('fade-out');
            loadingText.style.visibility = 'hidden'; // no elimina, evita que mueva nada

            // Mostrar texto ACCESS GRANTED justo DESPUÉS del radar
            setTimeout(() => {
                const granted = document.createElement('div');
                granted.className = 'access-granted';
                granted.textContent = 'ACCESS GRANTED';
                overlay.querySelector('.hud-container').appendChild(granted);

                // Esperar 1s y abrir menú
                setTimeout(() => {
                    const btn = document.getElementById('zajMenu');
                    const zajOverlay = document.getElementById('zajOverlay');
					window.menuOpenedAutomatically = true;
                    if (btn && zajOverlay) btn.click();
                    hideLoadingOverlay();
                }, 1000);
            }, 500); // radar fade (0.5s)
        }, 2000); // radar activo 2s
    });
    </script>
    <?php
}

// [[[[wp_footer]]]]
add_action('wp_footer', 'custom_hide_home_cart_and_scroll');

/* ==================================================================================================
 * Función que oculta el botón del carrito Xootix y bloquea scroll permanentemente en la página Home.  
 * - Evita que se vea la barra de desplazamiento.
 * - Oculta el botón del carrito incluso si Xootix lo carga dinámicamente.
 * - Se ejecuta solo en la página de inicio (front page o home).
 * Esta función resuelve el tema de que cuando se abria de forma automática el menu y se cerraba
 * aparecia el scrollbar, el boton de compra de xootix y el espacio vacio grande inferior
 * ================================================================================================== */
function custom_hide_home_cart_and_scroll() {
    if (!is_front_page() && !is_home()) return;
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', () => {

        // ===============================
        // Bloquear scroll permanentemente
        // ===============================
        document.body.style.overflow = 'hidden';
        document.body.style.height = '100vh';
        document.documentElement.style.overflow = 'hidden';
        document.documentElement.style.height = '100vh';

        // =======================================
        // Función para ocultar el botón del carrito
        // =======================================
        function hideCart() {
            const cartBtn = document.querySelector('.xoo-wsc-basket');
            if (cartBtn) cartBtn.style.display = 'none';
        }

        // Intento inicial
        hideCart();

        // =========================================
        // Observar cambios en body para detectar
        // carrito cargado dinámicamente por Xootix
        // =========================================
        const observer = new MutationObserver(() => hideCart());
        observer.observe(document.body, { childList: true, subtree: true });

    });
    </script>
    <?php
}

// ################################################################################################################################################
// ################################################################################################################################################
// ################################################################################################################################################
// 											FILTRO HUSKY
// ################################################################################################################################################
// ################################################################################################################################################
// ################################################################################################################################################

// [[[[wp_footer]]]]
add_action('wp_footer', 'husky_cambiar_titulo_categorias');

/* ==============================================================
 * Función que cambia el título del filtro de categorías de Husky 
 * de "Categorías del producto" a "Categories".
 * ============================================================== */
function husky_cambiar_titulo_categorias() {
    if (!is_shop() && !is_product_category()) return; // Solo Shop o categorías
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Selecciona el <h4> del contenedor de categorías
        const catHeader = document.querySelector('.woof_container_product_cat h4');
        if(catHeader) {
            catHeader.textContent = 'Categories';
            // Estilo de fuente Orbitron
            catHeader.style.fontFamily = '"Orbitron", sans-serif';
            catHeader.style.fontSize = '18px';
            catHeader.style.fontWeight = '300';
            catHeader.style.letterSpacing = '1px';
        }

        // Actualiza el input hidden interno que Husky usa para el filtro
        const hiddenInput = document.querySelector('input[name="woof_t_product_cat"]');
        if(hiddenInput) hiddenInput.value = 'Product Categories';
    });
    </script>
    <?php
}

// [[[[wp_footer]]]]
add_action('wp_footer', 'husky_busqueda_sin_dropdown');

/* ================================================================================
 * Función que desactiva el dropdown del input de búsqueda Husky en la página Shop.
 * - Evita que aparezca el combo de sugerencias al hacer focus o escribir.
 * - Se ejecuta solo en la página principal de la tienda.
 * ================================================================================ */
function husky_busqueda_sin_dropdown() {
    if (!is_shop()) return; // Solo en página Shop
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const input = document.querySelector('.woof_husky_txt-input');
        if(input) {
            // Nunca mostrar dropdown al hacer focus
            input.addEventListener('focus', () => {
                const container = input.closest('.woof_text_search_container');
                if(container) {
                    const drop = container.querySelector('.woof_husky_txt');
                    if(drop) drop.style.display = 'none';
                }
            });

            // Nunca mostrar dropdown al escribir
            input.addEventListener('input', () => {
                const container = input.closest('.woof_text_search_container');
                if(container) {
                    const drop = container.querySelector('.woof_husky_txt');
                    if(drop) drop.style.display = 'none';
                }
            });
        }
    });
    </script>
    <?php
}

// [[[[wp_footer]]]]
add_action('wp_footer', 'husky_filtrar_categorias');

/* ================================================================
 * Función que Oculta categorías no deseadas en el filtro Husky.
 * - Remueve "Uncategorized" y "Bundles" de la lista de checkboxes.
 * ================================================================ */
function husky_filtrar_categorias() {
    if (!is_shop()) return; // Solo Shop
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Lista de categorías a ocultar
        const categoriasOcultas = ['Uncategorized', 'Bundles'];

        // Selecciona todos los <li> de la lista de categorías Husky
        const items = document.querySelectorAll('.woof_container_product_cat .woof_list_checkbox li');

        items.forEach(li => {
            const label = li.querySelector('label.woof_checkbox_label');
            if(label && categoriasOcultas.includes(label.textContent.trim())) {
                li.remove(); // elimina el <li> no deseado
            }
        });
    });
    </script>
    <?php
}

// [[[[wp_head]]]]
add_action('wp_head', 'va2_ocultar_filtro_precio_woof');

/* ====================================================================================================
 * Función que agrega CSS para ocultar inicialmente el filtro de precio de Woof
 * Esto evita que el contenedor del filtro de precio se vea mal antes de que Woof cargue completamente.
 * ==================================================================================================== */
function va2_ocultar_filtro_precio_woof() {
    ?>
    <style>
        /* Oculta el contenedor del filtro de precio Woof: transparente y sin visibilidad */
        .woof_price3_search_container {
            visibility: hidden; /* Oculto para que no se vea */
            opacity: 0;         /* Transparente */
            height: 70px;       /* Ajustar según la altura real del filtro */
            transition: opacity 0.3s ease; /* Transición suave al mostrar */
        }
        /* Cuando tenga clase visible, se muestra */
        .woof_price3_search_container.visible {
            visibility: visible;
            opacity: 1;
            height: 70px;
        }
		/* Ocultar números y líneas debajo del ionRangeSlider */
        .irs-grid, .irs-min, .irs-max {
            display: none !important;
        }
    </style>
    <?php
}

// [[[[wp_footer]]]]
add_action('wp_footer', 'va2_mostrar_filtro_precio_woof_despues_delay');

/* ============================================================================================
   Función que agrega JS para mostrar el filtro de precio de Woof después de un pequeño retraso
   Esto evita el "flash" o parpadeo visual antes de que Woof cargue el filtro correctamente.
 ============================================================================================== */
function va2_mostrar_filtro_precio_woof_despues_delay() {
    ?>
    <script>
    (function($){
        $(function() {
            // Esperamos 300ms para asegurarnos que Woof cargó el filtro de precio correctamente
            setTimeout(function() {
                // Agregamos clase visible para mostrar el filtro con transición suave
                $('.woof_price3_search_container').addClass('visible');
            }, 300);
        });
    })(jQuery);
    </script>
    <?php
}

// [[[[wp_footer]]]]
// Hook personalizado que inyecta un script en el footer de la página de la tienda para traducir una etiqueta específica
add_action('wp_footer', 'traducir_label_tiene_globo_con_observer');

/* ===================================================================================
 * Función que imprime un script JavaScript en el footer solo en la página de tienda,
 * que reemplaza el texto del label "¿Tiene globo?" por "Comic Balloon Text".
 * Observa el DOM en tiempo real y traduce el label "¿Tiene globo?" en cuanto aparece.
 * Evita parpadeo o retraso, sin depender de eventos AJAX ni de DOMContentLoaded.
 * Busca el label “¿Tiene globo?” apenas se inyecta en el DOM.
Lo cambia a “Comic Balloon Text” inmediatamente al aparecer.
No espera DOMContentLoaded ni eventos AJAX.
Evita que se ejecute múltiples veces con observer.disconnect().
 * =================================================================================== */
function traducir_label_tiene_globo_con_observer() {
    if (is_shop()) {
        ?>
        <script>
        (function() {
            const traducir = () => {
                const label = document.querySelector('label[for="woof_meta_checkbox_tiene_globo"]');
                if (label && label.textContent.trim() !== 'Comic Balloon Text') {
                    label.textContent = 'Comic Balloon Text';
                    return true;
                }
                return false;
            };

            // Si ya está disponible, traducir inmediatamente
            if (traducir()) return;

            // Si aún no está, observar el DOM
            const observer = new MutationObserver(() => {
                if (traducir()) {
                    observer.disconnect(); // Detener el observador una vez traducido
                }
            });

            observer.observe(document.body, {
                childList: true,
                subtree: true
            });
        })();
        </script>
        <?php
    }
}

// [[[[wp_footer]]]]
add_action('wp_footer', 'agregar_boton_lupa_buscador');

/* ==========================================================================================================
 * Función que agrega botón lupa y alerta personalizada para búsquedas de texto con menos de 3 caracteres.
 * La validación solo se activa cuando el usuario presiona ENTER en el input de texto o hace clic en la lupa.
 * Evita que la alerta aparezca al usar otros filtros (checkbox, select, radio).
 * Usa MutationObserver para esperar a que el input de WOOF cargue su placeholder correctamente,
 * ya que WOOF lo reemplaza dinámicamente después de cargar la página o tras aplicar filtros AJAX.
 * ========================================================================================================== */
function agregar_boton_lupa_buscador() {
    if (!is_shop()) return;
    ?>

    <script>
    (function(){
        // bandera que usás para diferenciar búsquedas por input vs otros filtros
        let modoTextoActivo = false;
		
        // Buscar input (primero disponible)
        function findInput() {
            return document.querySelector('.woof_husky_txt-input');
        }

        // Función que agrega la lupa a un input dado (no jQuery, funciona con el nodo)
        function agregarLupaSiInputListoEl(inputEl) {
            if (!inputEl) return;
            // No duplicar
            if (inputEl.parentElement && inputEl.parentElement.querySelector('#btn_search')) return;

            // Crear botón
            const btn = document.createElement('button');
            btn.id = 'btn_search';
            btn.type = 'button';
            btn.textContent = '🔍';
            // Estilos inline (podés mover a CSS)
            Object.assign(btn.style, {
                cursor: 'pointer',
                position: 'absolute',
                right: '6px',
                top: '50%',
                transform: 'translateY(-50%)',
                background: 'transparent',
                border: 'none',
                fontSize: '18px',
                color: '#333',
                zIndex: 10
            });

            const wrapper = inputEl.parentElement || inputEl.closest('div') || document.body;
            if (window.getComputedStyle(wrapper).position === 'static') {
                wrapper.style.position = 'relative';
            }

            wrapper.appendChild(btn);
            
			btn.addEventListener('click', function (e) {
				e.preventDefault();

				// ✅ 👉 Buscar SIEMPRE el input actual
				const inp = findInput();
				const val = inp ? (inp.value || '').trim() : '';
				
				if (val.length >= 0 && val.length < 3) {					
					if (typeof window.mostrarAlertaPersonalizada === 'function') {
						window.mostrarAlertaPersonalizada('Please enter at least 3 characters.');
					} else {
						alert('Please enter at least 3 characters.');
					}					
					modoTextoActivo = false;
					return;
				}

				modoTextoActivo = true;

				if (inp) {
					// ✅ 👉 Disparar eventos como si hubieras presionado ENTER
					inp.focus();
					inp.dispatchEvent(new Event('input', { bubbles: true }));
					inp.dispatchEvent(new Event('change', { bubbles: true }));

					const kd = new KeyboardEvent('keydown', {
						key: 'Enter', code: 'Enter', which: 13, keyCode: 13, bubbles: true
					});
					inp.dispatchEvent(kd);

					setTimeout(() => {
						const ku = new KeyboardEvent('keyup', {
							key: 'Enter', code: 'Enter', which: 13, keyCode: 13, bubbles: true
						});
						inp.dispatchEvent(ku);
					}, 15);

					// ✅ 👉 Fallback: llama directamente a la función de Husky
					setTimeout(() => {
						if (typeof window.woof_submit_link === 'function') {
							window.woof_submit_link();
						}
					}, 40);
				}
			});
        }

        // Escaneo inicial (por si ya existe al cargar)
        (function initialScan() {
            const input = findInput();
            if (input) agregarLupaSiInputListoEl(input);
        })();

        // Observer: detectar cuando aparece el input y agregar la lupa inmediatamente
        const observer = new MutationObserver((mutations) => {
            for (const m of mutations) {
                // Revisar nodes añadidos
                for (const node of m.addedNodes) {
                    if (!(node instanceof HTMLElement)) continue;
                    if (node.matches && node.matches('.woof_husky_txt-input')) {
                        agregarLupaSiInputListoEl(node);
                        return;
                    }
                    const found = node.querySelector && node.querySelector('.woof_husky_txt-input');
                    if (found) {
                        agregarLupaSiInputListoEl(found);
                        return;
                    }
                }
                // Opcional: si detecta que #btn_search fue removido, reintenta re-agregar
                for (const node of m.removedNodes) {
                    if (!(node instanceof HTMLElement)) continue;
                    if (node.id === 'btn_search') {
                        // pequeño timeout para dar tiempo a que Husky re-renderice
                        setTimeout(() => {
                            const input = findInput();
                            if (input) agregarLupaSiInputListoEl(input);
                        }, 20);
                    }
                }
            }
        });

        observer.observe(document.body, { childList: true, subtree: true });
		
		// Delegación: ENTER en input activa modoTextoActivo y valida longitud
		document.body.addEventListener('keydown', function(e){
			const input = e.target;
			if (e.key === 'Enter' && input && input.classList && input.classList.contains('woof_husky_txt-input')) {
				const val = (input.value || '').trim();

				// Si hay menos de 3 caracteres (incluye 0)
				if (val.length < 3) {
					e.preventDefault();
					e.stopImmediatePropagation();

					if (typeof window.mostrarAlertaPersonalizada === 'function') {
						window.mostrarAlertaPersonalizada('Please enter at least 3 characters.');
					} else {
						alert('Please enter at least 3 characters.');
					}

					// Prevenir submit del formulario Husky
					const form = input.closest('form');
					if (form) {
						form.addEventListener('submit', function(ev){
							ev.preventDefault();
							ev.stopImmediatePropagation();
						}, { once: true, capture: true });
					}

					return false;
				}

				// Si pasa validación
				modoTextoActivo = true;
			}
		}, true);

        // Delegación: si cambia un checkbox de Husky, desactivar modoTextoActivo
        document.body.addEventListener('change', function(e){
            if (e.target && e.target.classList && e.target.classList.contains('woof_husky_checkbox')) {
                modoTextoActivo = false;
            }
        });

        // Monkey patch en woof_submit_link para validar solo si modoTextoActivo está activo
		// Interceptamos la función original woof_submit_link para aplicar la validación
        // El plugin Husky (WOOF) no usa eventos comunes como submit, input, change, etc. para activar el filtro. En cambio:
		// Llama directamente a una función JavaScript global interna llamada woof_submit_link(link) o woof_ajax_submit(...) cuando quiere ejecutar el filtro.
		// Esta función se dispara incluso si vos bloqueás todos los eventos normales, porque Husky la llama manualmente en su propio flujo.
		// Se reemplazo (“monkey patch”) la función window.woof_submit_link para meter la validación personalizada antes de que Husky ejecute su lógica
		// Esto se llama "Monkey patching":
		// Es una técnica avanzada donde sobrescribís funciones ya definidas por otro plugin o librería.
		// Se usa cuando no tenés control directo sobre el código original (como en un plugin cerrado).
		// Te permite insertar condiciones antes de que el código original se ejecute.
		// Si alguna vez Husky cambia el nombre de esa función, este parche dejará de funcionar.	
        function tryPatchWoof() {
            if (typeof window.woof_submit_link === 'function') {
                const original = window.woof_submit_link;
                window.woof_submit_link = function(link) {
                    const inp = findInput();
                    const val = inp ? (inp.value || '').trim() : '';
					// Si el texto es menor a 3						
                    if (val.length > 0 && val.length < 3) {												
						if (typeof window.mostrarAlertaPersonalizada === 'function') {
						 	window.mostrarAlertaPersonalizada('Please enter at least 3 characters.');
						 } else {
						 	alert('Please enter at least 3 characters.');
						 }																			
                        modoTextoActivo = false;
                        return false;
                    }
					
                    modoTextoActivo = false;
                    return original.call(this, link);
                };
				
                return true;
            }
            return false;
        }
        if (!tryPatchWoof()) {
            const t = setInterval(function(){
                if (tryPatchWoof()) clearInterval(t);
            }, 200);
        }

    })();
		
	// --- Evitar que Husky (WOOF) haga submit con Enter vacío solo en buscador ---   
	(function interceptarWoof(){
		function getInput(){
			return document.querySelector('.woof_husky_txt-input');
		}

		function parchear(){
			if (typeof window.woof_submit_link === 'function') {
				const original = window.woof_submit_link;

				// ⚙️ Flag temporal para detectar si realmente se presionó ENTER en el input
				let enterPresionado = false;

				// Detectar ENTER exclusivamente (no solo foco)
				document.addEventListener('keydown', function(e){
					if (e.key === 'Enter') {
						const input = getInput();
						if (input && document.activeElement === input) {
							enterPresionado = true;
							// Se limpia automáticamente después de un pequeño intervalo
							setTimeout(() => enterPresionado = false, 300);
						}
					}
				}, true);

				window.woof_submit_link = function(link){
					const input = getInput();
					// Si el usuario presionó Enter en input vacío → bloquear solo eso
					if (enterPresionado && input && document.activeElement === input) {
						const val = (input.value || '').trim();
						if (val.length === 0) {
							input.focus();
							enterPresionado = false;
							return false; // bloquea solo si estaba en el input y presionó Enter vacío
						}
					}
					// Para todo lo demás (checkboxes, selects...) funciona normal
					return original.call(this, link);
				};
				return true;
			}
			return false;
		}

		// Esperar hasta que Husky cargue
		if (!parchear()) {
			const t = setInterval(() => {
				if (parchear()) clearInterval(t);
			}, 300);
		}
	})();
		
	// Al cerrar la alerta personalizada → enfocar input de texto
    window.addEventListener('customAlertClosed', function() {
    	const input = document.querySelector('.woof_husky_txt-input');
        if (input) {
			input.focus();
        }	
    });	
				
    </script>
		
    <?php
}

// [[[[wp_footer]]]]
add_action('wp_footer', 'bloquear_interfaz_husky');

/* ==================================================================
 * Función que bloquea la interfaz cuando se usan filtros Husky.
 * Para la paginación también funciona
 * - Muestra overlay y activa clase "loading-active" en <body>.
 * - Se ejecuta en shop y categorías.
 * - Escucha clicks, cambios y submits en elementos Husky relevantes.
 * =================================================================== */
function bloquear_interfaz_husky() {
    if (!is_shop()) return;
    ?>
    <script>
    (function(){        

        const blocker = document.createElement('div');
        blocker.className = 'screen-blocker';
        document.body.appendChild(blocker);

        function activarBloqueo() {
            if (!document.body.classList.contains('loading-active')) {
                document.body.classList.add('loading-active');                
            }
        }

        // 🎯 Solo elementos válidos que disparan acción
        const validSelectors = [
            '.woof_checkbox',          // checkboxes de taxonomías
            '.woof_radio',             // radios
            '.woof_price_filter input',// rangos de precio                        
            '.woof_submit_search_form',// botón aplicar filtros            
        ].join(',');

        // Click o cambio solo si coincide con selector válido
        document.addEventListener('click', e => {
            if (e.target.closest(validSelectors)) activarBloqueo();
        });

        document.addEventListener('change', e => {
            if (e.target.closest(validSelectors)) activarBloqueo();
        });

        // Si hay formulario Husky, también cubrir submit manual
        const huskyForm = document.querySelector('.woof_submit_search_form')?.closest('form') 
                       || document.querySelector('form.woof');
        if (huskyForm) {
            huskyForm.addEventListener('submit', activarBloqueo);
        }

        // En caso de recarga directa
        // El beforeunload se ejecuta cada vez que la página se va a recargar, sin importar si es por un clic en paginación, 
        // un enlace normal o incluso un submit de formulario que recargue la página.
        window.addEventListener('beforeunload', () => {
            document.body.classList.add('loading-active');
        });
    })();
    </script>

    <?php
}

// [[[[wp_footer]]]]
// Añade la función eliminar_panel_husky para que se ejecute en el hook wp_footer, es decir, justo antes del cierre de la etiqueta </body> en el frontend de WordPress.
add_action('wp_footer', 'eliminar_panel_husky');

// ===========================================================================================
// Función que busca en el DOM un elemento con la clase .woof_products_top_panel y, si existe, 
// lo elimina del DOM (quita ese panel de filtros de la página).
// ===========================================================================================
function eliminar_panel_husky() {
    if (!is_shop()) return;
    ?>
    <script>
    function eliminarPanelFiltrosHusky() {
        const panel = document.querySelector('.woof_products_top_panel');
        if (panel) panel.remove();
    }

    // Ejecutar al cargar la página
    document.addEventListener('DOMContentLoaded', eliminarPanelFiltrosHusky);

    // Ejecutar luego de aplicar un filtro Husky por AJAX
    document.addEventListener('woof_ajax_done', eliminarPanelFiltrosHusky);
    </script>
    <?php
}

// [[[[wp_footer]]]]
add_action('wp_footer', 'forzar_rango_woof_14_20');

// ===================================================================================================
// Función que fuerza el rango mínimo y máximo del control de precios del plugin Husky (WOOF)
// para que siempre esté dentro de $14–$20, incluso cuando se use una URL tipo /swoof/price-14-to-18/.
// Además, re-aplica el rango tras cada actualización AJAX que realiza Husky al filtrar productos.
// Se ejecuta en el hook 'wp_footer' solo en la página de tienda (is_shop()).
// ===================================================================================================
function forzar_rango_woof_14_20() {
    if (is_shop()) : ?>
        <script>
        document.addEventListener('DOMContentLoaded', function() {

            /**
             * Obtiene el rango desde la URL, compatible con estructuras tipo:
             * https://tusitio.com/shop/swoof/price-14-to-18/
             * Retorna un objeto con los valores min y max detectados,
             * o el rango por defecto (14–20) si no hay coincidencia.
             */
            function obtenerRangoDesdeUrl() {
                const url = window.location.href;
                const match = url.match(/price-(\d+)-to-(\d+)/);
                if (match) {
                    return {
                        min: parseFloat(match[1]),
                        max: parseFloat(match[2])
                    };
                }
                return { min: 14, max: 20 }; // rango por defecto
            }

            /**
             * Fuerza el rango del control deslizante WOOF (ionRangeSlider)
             * a los límites globales 14–20, aplicando además el rango
             * activo detectado desde la URL si existe.
             */
            function forzarRangoWoof() {
                const slider = document.querySelector('.woof_range_slider');
                if (!slider) return;

                // Detectar los valores min y max actuales desde la URL
                const rango = obtenerRangoDesdeUrl();

                // Forzar límites absolutos del control
                slider.setAttribute('data-min', '14');
                slider.setAttribute('data-max', '20');

                // Si el slider ya fue inicializado por ionRangeSlider, actualizarlo directamente
                if (typeof jQuery !== 'undefined' && jQuery(slider).data('ionRangeSlider')) {
                    const instancia = jQuery(slider).data('ionRangeSlider');
                    instancia.update({
                        min: 14,
                        max: 20,
                        from: rango.min,
                        to: rango.max
                    });
                } else {
                    // Fallback visual si el slider aún no está listo
                    const fromLabel = document.querySelector('.irs-from');
                    const toLabel = document.querySelector('.irs-to');
                    if (fromLabel) fromLabel.textContent = '$' + rango.min;
                    if (toLabel) toLabel.textContent = '$' + rango.max;
                }
            }

            // === Inicialización: espera a que el slider esté disponible ===
            const initInterval = setInterval(() => {
                if (document.querySelector('.woof_range_slider')) {
                    clearInterval(initInterval);
                    forzarRangoWoof();
                }
            }, 400);

            // === Reaplicación automática tras cada actualización AJAX de WOOF ===
            if (typeof jQuery !== 'undefined') {
                jQuery(document).on('woof_ajax_done', function() {
                    setTimeout(forzarRangoWoof, 300);
                });
            }

        });
        </script>
    <?php endif;
}

// ################################################################################################################################################
// ################################################################################################################################################
// ################################################################################################################################################
// 											SHOP
// ################################################################################################################################################
// ################################################################################################################################################
// ################################################################################################################################################

// [[[[wp_head]]]]
add_action('wp_head', 'estilo_personalizado_shop');

/* ================================================================================
 * Función que agrega estilos CSS personalizados para el body de la página de shop.  
 * ================================================================================ */ 
function estilo_personalizado_shop() {		
    if (!is_shop()) return;
	
    $faction = $_COOKIE['faction'] ?? 'gdi';
	
    if ($faction === 'gdi') {  				
		$body_bg = 'linear-gradient(270deg, #001f3f, #003f7f, #005bbb, #001f3f)'; // azul profundo eléctrico			
    } else if ($faction === 'nod') {
        // $body_bg = 'linear-gradient(270deg, #2a0000, #550000, #2a0000)';
		// $body_bg = 'linear-gradient(135deg, rgba(10, 0, 0, 0.95) 0%, rgba(40, 0, 0, 0.9) 50%, rgba(120, 0, 0, 0.4) 100% )';
		$body_bg = 'linear-gradient(135deg, rgba(5, 0, 0, 0.97) 0%, rgba(20, 0, 0, 0.94) 40%, rgba(60, 0, 0, 0.85) 70%, rgba(90, 0, 0, 0.6) 100%)';
    }	
    ?>

	<style>		
		/* ===========================
           BACKGROUND ANIMADO 
           ========================== */
        body {
          background: <?= $body_bg ?>;
          background-size: 200% 200%;
          animation: gradientShift 20s ease infinite;
          overflow-x: hidden;
          font-family: Orbitron, sans-serif;
        }
        @keyframes gradientShift {
          0% { background-position: 0% 50%; }
          50% { background-position: 100% 50%; }
          100% { background-position: 0% 50%; }
        }		
	</style>

	<div class="grid-3d"></div>    
	<!-- <div class="hud-circle"></div> -->
	<div class="custom-shop-title">
        SHOP
    </div>
    <div class="scan-line"></div>    

    <?php 
}

// [[[[woocommerce_shop_loop_item_title]]]]
// Renderiza (muestra) el título del producto dentro del loop de la tienda,
add_action( 'woocommerce_shop_loop_item_title', 'custom_loop_product_title', 10 );

/* ========================================================================
 * Muestra el título del producto en el loop de la tienda. 
 * Esta función reemplaza el comportamiento estándar de WooCommerce
 * para imprimir el título de cada producto dentro del listado (shop loop),
 * mostrando solo el texto del título, sin enlace al producto. 
 * ======================================================================== */
function custom_loop_product_title() {
    global $product;

    // Solo muestra el título sin enlace
    echo '<h2 class="woocommerce-loop-product__title">' . esc_html( $product->get_title() ) . '</h2>';
}

// [[[[wp_footer]]]]
// Hook para ejecutar la función al final del footer
add_action('wp_footer', 'animacion_random_shop');

/* =============================================================================================
 * Función que elige una animación de entre cinco para mostrarse cuando hay productos en el shop
 * Propósito:
 *  - Ejecutar al azar una de las tres animaciones definidas para el shop.
 *  - Solo se ejecuta en la página de la tienda WooCommerce.
 *  - Se valida que haya productos visibles en el loop actual antes de ejecutar la animación.
 * Flujo:
 * 1. Comprobar que estamos en la página de la tienda con is_shop().
 * 2. Comprobar que existen productos visibles usando wc_get_loop_prop('total').
 * 3. Seleccionar al azar una función de animación.
 * 4. Ejecutar la función de animación si existe.
 * ============================================================================================= */
function animacion_random_shop() {    
    // Validar página de shop    
    if (!is_shop()) return;
    
    // Validar que existan productos visibles en el loop    
    if (wc_get_loop_prop('total') < 1) return;
	    
    // Array con los nombres de las animaciones    
    $animaciones = ['animacion_shop_uno', 'animacion_shop_dos', 'animacion_shop_tres', 'animacion_shop_cuatro', 'animacion_shop_cinco'];	
    
    // Elegir una animación al azar    
    $seleccion = $animaciones[array_rand($animaciones)];
    
    // Ejecutar la función seleccionada si existe   
    if ( function_exists($seleccion) ) {
    	$seleccion();
    }
}

/* =======================================================================================
 * Función que agrega estilos CSS al footer de la tienda WooCommerce.
 * Propósitos:
 *   - Sidebar: deslizamiento desde la izquierda con un rebote rápido.
 *   - Productos: rebote horizontal al aparecer.
 *   - Más “clásica” y directa, menos exagerada en los rebotes.
 * 1. Sidebar Husky: aparece desde la izquierda con un rebote suave.
 * 2. Productos: animación de desplazamiento horizontal con rebotes sucesivos al aparecer.
 *    - Cada producto inicia desplazado a la derecha fuera de su posición final.
 *    - Se mueve hacia la izquierda más allá de la posición final (primer rebote grande).
 *    - Retrocede ligeramente a la derecha (rebote menor).
 *    - Ajustes finales con rebotes pequeños hasta quedar en su posición estable.
 *    - Cada producto aparece escalonadamente, generando efecto de cascada.
 * 3. Define layout de grilla, hover en productos y estilos responsive.
 *======================================================================================== */
function animacion_shop_uno() { ?>
    <style>
    /* ======================
       Sidebar animado
       ====================== */
    body.woocommerce-shop .ct-container[data-sidebar="left"] > aside#sidebar {
      position: sticky; /* se mantiene visible al hacer scroll */
      top: 130px; /* separación desde la parte superior */
      align-self: start; /* alineación al inicio del contenedor flex */
      height: calc(100vh - 140px); /* altura total menos márgenes */
      overflow-y: auto; /* scroll vertical si excede altura */
      max-height: calc(100vh - 140px);
      z-index: 9999; /* por encima de otros elementos */

      /* Estado inicial invisible y desplazado fuera de pantalla */
      opacity: 0;
      transform: translateX(-150px);
      animation: slideInBounce 0.8s forwards; /* deslizamiento con rebote */
    }

    /* ======================
       Keyframes del sidebar
       ====================== */
    @keyframes slideInBounce {
      0% { opacity: 0; transform: translateX(-150px); } /* fuera de pantalla */
      60% { opacity: 1; transform: translateX(30px); }  /* rebote hacia la derecha */
      80% { transform: translateX(-10px); }             /* rebote menor hacia la izquierda */
      100% { opacity: 1; transform: translateX(0); }   /* posición final estable */
    }

    /* ======================
       Animación de la galería de productos
       ====================== */
    body.woocommerce-shop ul.products {
        transform: translateX(150px); /* inicia desplazada a la derecha */
        animation: productsBounce 1s forwards; /* animación de rebotes horizontales */
    }

    /* ======================
       Keyframes de los productos
       ====================== */
    @keyframes productsBounce {
      0%   { transform: translateX(150px); }  /* posición inicial fuera de pantalla */
      50%  { transform: translateX(-20px); }  /* primer rebote grande hacia la izquierda */
      65%  { transform: translateX(10px); }   /* rebote menor hacia la derecha */
      80%  { transform: translateX(-5px); }   /* último rebote pequeño hacia la izquierda */
      90%  { transform: translateX(2px); }    /* ajuste final hacia la derecha */
      100% { transform: translateX(0); }      /* posición final estable */
    }

    /* ======================
       Sidebar interno (contenedor del contenido)
       ====================== */
    body.woocommerce-shop .ct-container[data-sidebar="left"] > aside#sidebar .ct-sidebar {
      background: transparent;
      border-right: 1px solid rgba(255,255,255,0.08); /* línea sutil a la derecha */
      padding: 20px 15px;
      border-radius: 14px;
      height: 100%;
      box-sizing: border-box;
    }

    /* ======================
       Layout de productos en grilla
       ====================== */
    body.woocommerce-shop .ct-container[data-sidebar="left"] ul.products {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); /* columnas adaptativas */
      gap: 20px;
      margin: 0;
      padding: 0;
      list-style: none;
    }

    /* ======================
       Estilo de cada producto
       ====================== */
    body.woocommerce-shop ul.products li.product {
      border-radius: 14px;
      overflow: hidden;
      transition: transform 0.25s ease, box-shadow 0.25s ease; /* transición suave al hover */
    }

    /* Efecto hover: levanta y agrega sombra */
    body.woocommerce-shop ul.products li.product:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 20px rgba(255,215,0,0.25);
    }

    /* ======================
       Responsive: adaptaciones para pantallas pequeñas
       ====================== */
    @media (max-width: 900px) {
      body.woocommerce-shop ul.products {
        grid-template-columns: 1fr; /* una columna en móviles */
        animation: none !important; /* desactiva animación en móviles */
        transform: none !important; /* posición normal */
      }
      body.woocommerce-shop .ct-container[data-sidebar="left"] > aside#sidebar {
        position: static; /* sidebar dentro del flujo normal */
        max-height: none;
        transform: none !important;
        opacity: 1 !important; /* siempre visible */
        animation: none !important; /* sin animación en móviles */
      }
    }
    </style>
<?php
}

/* ============================================================================================
 * Función que anima el shop al cargarse y se ejecuta únicamente en la página del shop.   
 * Propósitos:
 * 		- Sidebar: muy similar al Uno, pero algunos detalles de timing o suavizado pueden variar.
 *		- Productos: utiliza cubic-bezier para un rebote más natural y “suave”.
 * 		- Cada producto aparece escalonadamente con un efecto de cascada más evidente.
 * Agrega estilos CSS al footer de la tienda WooCommerce.
 * Funciones principales:
 * 1. Animar el sidebar Husky desde la izquierda con rebote suave.
 * 2. Animar los productos al aparecer con rebote horizontal escalonado (cascada).
 * 3. Definir layout de grilla, hover en productos y estilos responsive.
 * ============================================================================================ */
function animacion_shop_dos() { ?>
        <style>
        /* ======================
           Sidebar animado
           ====================== */
        body.woocommerce-shop .ct-container[data-sidebar="left"] > aside#sidebar {
          position: sticky; /* sidebar se mantiene visible al hacer scroll */
          top: 130px; /* separación superior */
          align-self: start; /* alineación al inicio del contenedor flex */
          height: calc(100vh - 140px); /* altura total menos márgenes */
          overflow-y: auto; /* scroll vertical si contenido excede altura */
          max-height: calc(100vh - 140px);
          z-index: 9999; /* por encima de otros elementos */

          /* Estado inicial: invisible y desplazado fuera de pantalla */
          opacity: 0;
          transform: translateX(-150px);
          animation: slideInBounce 0.8s forwards; /* animación de deslizamiento con rebote */
        }

        /* ======================
           Keyframes del sidebar
           ====================== */
        @keyframes slideInBounce {
          0% { opacity: 0; transform: translateX(-150px); } /* fuera de pantalla */
          60% { opacity: 1; transform: translateX(30px); }  /* rebote hacia la derecha */
          80% { transform: translateX(-10px); }             /* rebote menor hacia la izquierda */
          100% { opacity: 1; transform: translateX(0); }   /* posición final estable */
        }

        /* ======================
           Galería: cada producto rebota en cascada
           ====================== */
        body.woocommerce-shop ul.products li.product {
          transform: translateX(150px); /* empieza desplazado a la derecha */
          animation: productBounce 1s forwards cubic-bezier(0.68, -0.55, 0.27, 1.55);
          /* cubic-bezier para suavizar el rebote y dar sensación de caída natural */
        }

        /* ======================
           Keyframes de rebote de productos
           ====================== */
        @keyframes productBounce {
          0%   { transform: translateX(150px); }   /* fuera de pantalla a la derecha */
          50%  { transform: translateX(-20px); }  /* primer rebote grande hacia la izquierda */
          65%  { transform: translateX(10px); }   /* rebote secundario menor hacia la derecha */
          80%  { transform: translateX(-5px); }   /* último rebote menor hacia la izquierda */
          90%  { transform: translateX(2px); }    /* ajuste final hacia la derecha */
          100% { transform: translateX(0); }      /* posición final estable */
        }

        /* ======================
           Retardo escalonado para efecto cascada
           ====================== */
        <?php
        for ($i = 0; $i < 20; $i++) {
            // Cada producto se anima 0.1s después del anterior
            echo "body.woocommerce-shop ul.products li.product:nth-child(".($i+1).") { animation-delay: ".($i*0.1)."s; }\n";
        }
        ?>

        /* ======================
           Sidebar interno
           ====================== */
        body.woocommerce-shop .ct-container[data-sidebar="left"] > aside#sidebar .ct-sidebar {
          background: transparent;
          border-right: 1px solid rgba(255,255,255,0.08); /* línea sutil a la derecha */
          padding: 20px 15px;
          border-radius: 14px;
          height: 100%;
          box-sizing: border-box;
        }

        /* ======================
           Layout de productos en grilla
           ====================== */
        body.woocommerce-shop .ct-container[data-sidebar="left"] ul.products {
          display: grid;
          grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); /* columnas adaptativas */
          gap: 20px;
          margin: 0;
          padding: 0;
          list-style: none;
        }

        /* ======================
           Hover en productos
           ====================== */
        body.woocommerce-shop ul.products li.product:hover {
          transform: translateY(-5px); /* eleva ligeramente el producto */
          box-shadow: 0 8px 20px rgba(255,215,0,0.25); /* sombra dorada */
        }

        /* ======================
           Responsive: adaptación para pantallas pequeñas
           ====================== */
        @media (max-width: 900px) {
          body.woocommerce-shop ul.products {
            grid-template-columns: 1fr; /* una columna en móviles */
            animation: none !important; /* desactiva animación en móviles */
            transform: none !important; /* posición normal */
          }
          body.woocommerce-shop .ct-container[data-sidebar="left"] > aside#sidebar {
            position: static; /* sidebar dentro del flujo normal */
            max-height: none;
            transform: none !important;
            opacity: 1 !important; /* siempre visible */
            animation: none !important; /* sin animación en móviles */
          }
        }

        </style>
    <?php    
}

/* =====================================================================================
 * Función que anima el shop al cargarse y se ejecuta únicamente en la página del shop.  
 * Propósitos:
 * 1. Sidebar Husky: Igual que el uno. Necesario porque sino no se alineaba el husky con 
 * la galeria.
 * 2. Posters (productos):
*    - Animación de caída con rebote vertical y desplazamiento horizontal.
*    - Cada poster aparece escalonadamente con delays crecientes para efecto de cascada.
* ====================================================================================== */
function animacion_shop_tres() { ?>     
        <style>					
			/* ------------------ Sidebar ------------------ */
			body.woocommerce-shop .ct-container[data-sidebar="left"] > aside#sidebar {
				position: sticky;
				top: 130px;
				align-self: start;
				height: calc(100vh - 140px);
				overflow-y: auto;
				max-height: calc(100vh - 140px);
				z-index: 9999;

				/* Estado inicial */
				opacity: 0;
				transform: translateX(-150px);
			}

			/* Keyframes del sidebar: igual que animacion 5 */
			@keyframes slideInBounce {
				0% { opacity: 0; transform: translateX(-150px); }
				60% { opacity: 1; transform: translateX(30px); }
				80% { transform: translateX(-10px); }
				100% { opacity: 1; transform: translateX(0); }
			}

			/* Sidebar interno */
			body.woocommerce-shop .ct-container[data-sidebar="left"] > aside#sidebar .ct-sidebar {
				background: transparent;
				border-right: 1px solid rgba(255,255,255,0.08);
				padding: 20px 15px;
				border-radius: 14px;
				height: 100%;
				box-sizing: border-box;
			}

        /* ------------------ Animación de los productos (posters) ------------------ */
        body.woocommerce-shop ul.products li.product {
          transform: translateX(150px) translateY(-100px); 
          /* Comienza desplazado a la derecha y arriba */
          animation: productDropBounce 1.2s forwards cubic-bezier(0.68, -0.55, 0.27, 1.55);
        }

        @keyframes productDropBounce {
          0%   { transform: translateX(150px) translateY(-100px); } /* fuera de pantalla */
          40%  { transform: translateX(-20px) translateY(20px); }   /* primer rebote hacia abajo y ligeramente a la izquierda */
          55%  { transform: translateX(10px) translateY(-10px); }   /* rebote contrario */
          70%  { transform: translateX(-5px) translateY(5px); }     /* rebote pequeño */
          85%  { transform: translateX(2px) translateY(-2px); }     /* rebote final muy pequeño */
          100% { transform: translateX(0) translateY(0); }          /* posición final estable */
        }

        /* ------------------ Retardo escalonado de los posters para efecto cascada ------------------ */
        <?php
        for ($i = 0; $i < 20; $i++) {
            // Cada producto aparece 0.1s después que el anterior
            echo "body.woocommerce-shop ul.products li.product:nth-child(".($i+1).") { animation-delay: ".($i*0.1)."s; }\n";
        }
        ?>

        /* ------------------ Layout de la grilla de productos ------------------ */
        body.woocommerce-shop .ct-container[data-sidebar="left"] ul.products {
          display: grid;
          grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
          gap: 20px;
          margin: 0;
          padding: 0;
          list-style: none;
        }

        /* ------------------ Hover en productos ------------------ */
        body.woocommerce-shop ul.products li.product:hover {
          transform: translateY(-5px); /* levanta ligeramente el poster */
          box-shadow: 0 8px 20px rgba(255,215,0,0.25); /* sombra dorada */
        }

        /* ------------------ Responsive ------------------ */
        @media (max-width: 900px) {
          body.woocommerce-shop ul.products {
            grid-template-columns: 1fr;
            animation: none !important;
            transform: none !important;
          }
          body.woocommerce-shop .ct-container[data-sidebar="left"] > aside#sidebar {
            position: static;
            max-height: none;
            transform: none !important;
            opacity: 1 !important;
            transition: none !important;
          }
        }
        </style>
		<script>
		document.addEventListener('DOMContentLoaded', function() {
			const sidebar = document.querySelector('body.woocommerce-shop .ct-container[data-sidebar="left"] > aside#sidebar');
			if (sidebar) {
				// Forzamos la animación con keyframes igual que en animacion 5
				sidebar.style.animation = 'slideInBounce 0.8s forwards';
			}
		});
		</script>
    <?php    
}

/* =====================================================================================
 * Función que anima el shop al cargarse y se ejecuta únicamente en la página del shop.  
 * Propósitos:
 * 1. Sidebar Husky: Igual que el uno. Necesario porque sino no se alineaba el husky con 
 * la galeria.
 * 2. Posters (productos):
*    - Animación de caída con rebote vertical y desplazamiento horizontal.
*    - Cada poster aparece escalonadamente con delays crecientes para efecto de cascada.
* ====================================================================================== */
function animacion_shop_cuatro() { ?>     
<style>	
	/* ------------------ Sidebar ------------------ */
body.woocommerce-shop .ct-container[data-sidebar="left"] > aside#sidebar {
    position: sticky;
    top: 130px;
    align-self: start;
    height: calc(100vh - 140px);
    overflow-y: auto;
    max-height: calc(100vh - 140px);
    z-index: 9999;

    /* Estado inicial */
    opacity: 0;
    transform: translateX(-150px);
}

/* Keyframes del sidebar: igual que animacion 5 */
@keyframes slideInBounce {
    0% { opacity: 0; transform: translateX(-150px); }
    60% { opacity: 1; transform: translateX(30px); }
    80% { transform: translateX(-10px); }
    100% { opacity: 1; transform: translateX(0); }
}

/* Sidebar interno */
body.woocommerce-shop .ct-container[data-sidebar="left"] > aside#sidebar .ct-sidebar {
    background: transparent;
    border-right: 1px solid rgba(255,255,255,0.08);
    padding: 20px 15px;
    border-radius: 14px;
    height: 100%;
    box-sizing: border-box;
}
		
/* ------------------ Animación de los productos ------------------ */
body.woocommerce-shop ul.products li.product {
    transform: translateX(150px) translateY(-100px);
    animation: productDropBounceSmooth 1.4s forwards cubic-bezier(0.25, 0.46, 0.45, 0.94);
}

@keyframes productDropBounceSmooth {
    0%   { transform: translateX(150px) translateY(-100px); }
    30%  { transform: translateX(-20px) translateY(25px); }
    50%  { transform: translateX(10px) translateY(-15px); }
    65%  { transform: translateX(-5px) translateY(7px); }
    80%  { transform: translateX(2px) translateY(-3px); }
    90%  { transform: translateX(-1px) translateY(1px); }
    100% { transform: translateX(0) translateY(0); }
}

/* ------------------ Retardo escalonado ------------------ */
<?php
for ($i = 0; $i < 20; $i++) {
    echo "body.woocommerce-shop ul.products li.product:nth-child(".($i+1).") { animation-delay: ".($i*0.08)."s; }\n";
}
?>

/* ------------------ Layout de la grilla ------------------ */
body.woocommerce-shop .ct-container[data-sidebar="left"] ul.products {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 20px;
    margin: 0;
    padding: 0;
    list-style: none;
}

/* ------------------ Hover en productos ------------------ */
body.woocommerce-shop ul.products li.product:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(255,215,0,0.25);
}

/* ------------------ Responsive ------------------ */
@media (max-width: 900px) {
    body.woocommerce-shop ul.products {
        grid-template-columns: 1fr;
        animation: none !important;
        transform: none !important;
    }
    body.woocommerce-shop .ct-container[data-sidebar="left"] > aside#sidebar {
        position: static;
        max-height: none;
        transform: none !important;
        opacity: 1 !important;
        transition: none !important;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.querySelector('body.woocommerce-shop .ct-container[data-sidebar="left"] > aside#sidebar');
    if (sidebar) {
        // Forzamos la animación con keyframes igual que en animacion 5
        sidebar.style.animation = 'slideInBounce 0.8s forwards';
    }
});
</script>

<?php    
}

/* =====================================================================================
 * Función que anima el shop al cargarse con efecto cinemático
 * Propósitos:
 * Sidebar Husky: Igual que el uno. Necesario porque sino no se alineaba el husky con 
 * la galeria.
 *   - Productos: caída tipo pelota real, rebotes progresivamente más pequeños
 *   - Delay escalonado para efecto cascada
 * ====================================================================================== */
function animacion_shop_cinco() { ?>
    <style>
		/* ------------------ Sidebar ------------------ */
		body.woocommerce-shop .ct-container[data-sidebar="left"] > aside#sidebar {
			position: sticky;
			top: 130px;
			align-self: start;
			height: calc(100vh - 140px);
			overflow-y: auto;
			max-height: calc(100vh - 140px);
			z-index: 9999;

			/* Estado inicial */
			opacity: 0;
			transform: translateX(-150px);
		}

		/* Keyframes del sidebar: igual que animacion 5 */
		@keyframes slideInBounce {
			0% { opacity: 0; transform: translateX(-150px); }
			60% { opacity: 1; transform: translateX(30px); }
			80% { transform: translateX(-10px); }
			100% { opacity: 1; transform: translateX(0); }
		}

		/* Sidebar interno */
		body.woocommerce-shop .ct-container[data-sidebar="left"] > aside#sidebar .ct-sidebar {
			background: transparent;
			border-right: 1px solid rgba(255,255,255,0.08);
			padding: 20px 15px;
			border-radius: 14px;
			height: 100%;
			box-sizing: border-box;
		}

    /* ------------------ Productos: caída cinemática ------------------ */
    body.woocommerce-shop ul.products li.product {
        transform: translateY(-200px);
        animation: productBounceCinematic 1.6s forwards cubic-bezier(0.25, 0.46, 0.45, 0.94);
    }

    @keyframes productBounceCinematic {
        0%   { transform: translateY(-200px); }
        30%  { transform: translateY(0); }      /* primer “golpe” al suelo */
        45%  { transform: translateY(-60px); }  /* rebote alto */
        60%  { transform: translateY(0); }      /* rebote hacia abajo */
        70%  { transform: translateY(-30px); }  /* rebote medio */
        80%  { transform: translateY(0); }      /* rebote menor */
        88%  { transform: translateY(-15px); }  /* último rebote pequeño */
        95%  { transform: translateY(0); }
        100% { transform: translateY(0); }
    }

    /* ------------------ Retardo escalonado para cascada ------------------ */
    <?php
    for ($i = 0; $i < 20; $i++) {
        echo "body.woocommerce-shop ul.products li.product:nth-child(".($i+1).") { animation-delay: ".($i*0.12)."s; }\n";
    }
    ?>

    /* ------------------ Layout de grilla ------------------ */
    body.woocommerce-shop .ct-container[data-sidebar="left"] ul.products {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: 20px;
        margin: 0;
        padding: 0;
        list-style: none;
    }

    /* ------------------ Hover suave ------------------ */
    body.woocommerce-shop ul.products li.product:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 20px rgba(255,215,0,0.25);
    }

    /* ------------------ Responsive ------------------ */
    @media (max-width: 900px) {
        body.woocommerce-shop ul.products {
            grid-template-columns: 1fr;
            animation: none !important;
            transform: none !important;
        }
        body.woocommerce-shop .ct-container[data-sidebar="left"] > aside#sidebar {
            position: static;
            max-height: none;
            transform: none !important;
            opacity: 1 !important;
            transition: none !important;
        }
    }
    </style>
	<script>
	document.addEventListener('DOMContentLoaded', function() {
		const sidebar = document.querySelector('body.woocommerce-shop .ct-container[data-sidebar="left"] > aside#sidebar');
		if (sidebar) {
			// Forzamos la animación con keyframes igual que en animacion 5
			sidebar.style.animation = 'slideInBounce 0.8s forwards';
		}
	});
	</script>
<?php
}

// [[[[init]]]]
// init es una acción (action hook) que WordPress ejecuta muy temprano en la carga de la página, justo después de haber cargado 
// la mayoría de los archivos esenciales del núcleo y antes de que se procese cualquier contenido o plantilla.
add_action('init', 'mc_reemplazar_mensaje_sin_productos');

/* ===========================================================================================
// Función que muestra un mensaje personalizado sólo en shop o bundles cuando no hay productos
/* =========================================================================================== */
function mc_mostrar_mensaje_sin_productos() {
    // Verificar que estamos en la página de la tienda o en bundles
    if (is_shop()) {
		
		$faction = $_COOKIE['faction'] ?? 'gdi';
		// Obtener el array con las URLs y rutas del directorio de uploads de WordPress
        $uploads_dir = wp_upload_dir();

		// Obtener la URL completa de la imagen 'notfound.png' dentro de la carpeta uploads
		if ($faction == 'gdi')
			$notfound_path = trailingslashit($uploads_dir['baseurl']) . 'images/irina_notfound.jpg';
		else
			$notfound_path = trailingslashit($uploads_dir['baseurl']) . 'images/notfound.jpg';
		
        ?>
        <div class="woocommerce-no-products-found">
            <div class="no-products-box">
                <img src="<?php echo esc_url($notfound_path); ?>" alt="No artworks available">
                <p>Oops! it looks like your search didn’t find any artwork. Want to try another magic word?</p>
            </div>
        </div>
        <?php
    }
}

/* ===================================================================================
// Función que reemplaza el mensaje por defecto de WooCommerce cuando no hay productos
/* =================================================================================== */
function mc_reemplazar_mensaje_sin_productos() {
    remove_action('woocommerce_no_products_found', 'wc_no_products_found');
    add_action('woocommerce_no_products_found', 'mc_mostrar_mensaje_sin_productos');
}

// [[[[wp_footer]]]]
add_action('wp_footer', 'deshabilitar_boton_add_to_cart_mientras_procesa');

/* ==============================================================================
// Función que deshabilita el boton "Add to Cart" mientras se procesa el producto
/* ============================================================================== */
add_action('wp_footer', 'deshabilitar_boton_add_to_cart_mientras_procesa');
function deshabilitar_boton_add_to_cart_mientras_procesa() {
	// Lo aplicamos solo en tienda y bundles
	if ( !is_shop() && !is_page('bundles') ) return;
	?>
	<script>
	(function($){
	  $(document).ready(function(){
	    // Cuando se hace clic en un botón ajax add to cart
	    $('body').on('click', '.ajax_add_to_cart', function(e) {
	      var $btn = $(this);

	      // Si ya está deshabilitado, bloqueamos
	      if ($btn.hasClass('disabled')) {
	        e.preventDefault();
	        return false;
	      }

	      // Deshabilitar y mostrar spinner WooCommerce
	      $btn
	        .addClass('disabled loading')   // 👈 "loading" activa el spinner11
	        .attr('aria-disabled', 'true')
	        .css('pointer-events', 'none');
	    });

	    // Cuando WooCommerce confirma que se agregó al carrito
	    $(document.body).on('added_to_cart', function(event, fragments, cart_hash, $button) {
	      if ($button) {
	        $button
	          .removeClass('disabled loading')  // 👈 quitamos el spinner
	          .removeAttr('aria-disabled')
	          .css('pointer-events', '');
	      }
	    });
	  });
	})(jQuery);
	</script>
	<?php
}

// [[[[woocommerce_product_add_to_cart_text]]]]
// es un filtro que WooCommerce usa para el texto del botón “Añadir al carrito” en listados de productos o páginas de tienda.
// Siempre mostrar "Add to cart" en vez de "Añadir al carrito"
add_filter('woocommerce_product_add_to_cart_text', function() {
    return 'Add to cart';
});

// [[[[woocommerce_product_single_add_to_cart_text]]]]
// es el filtro que WooCommerce usa para el botón de la página individual de cada producto.
add_filter('woocommerce_product_single_add_to_cart_text', function() {
	// devuelve la cadena "Add to cart", reemplazando cualquier traducción automática que WooCommerce pudiera aplicar.
    return 'Add to cart';
});

// [[[[wp_footer]]]]
// Hook que agrega el script en el footer, pero solo en la página de tienda
add_action('wp_footer', 'cambiar_texto_ver_carrito_en_shop');

/* =========================================================================
 * Función que inyecta JavaScript para cambiar "Add to Cart" por "View Cart"
 * únicamente en la página de tienda. Usa MutationObserver para detectar
 * los nuevos botones añadidos dinámicamente.
 * ========================================================================= */
function cambiar_texto_ver_carrito_en_shop() {
    // Verificamos que estamos en la página de tienda
    if (!is_shop()) return;
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        // Función que revisa y cambia el texto de los botones "Ver carrito"
        function reemplazarTextoBoton(boton) {
            if (
                boton &&
                boton.textContent.trim() === 'Ver carrito'
            ) {
                boton.textContent = 'View Cart';
            }
        }

        // 1. Cambiar los botones que ya están presentes en la página al cargar
        document.querySelectorAll('a.added_to_cart.wc-forward').forEach(reemplazarTextoBoton);

        // 2. Usar MutationObserver para detectar cambios cuando se agregan botones nuevos al DOM
       	// Un MutationObserver es una API de JavaScript que te permite observar cambios en el DOM (Document Object Model) de una página web. Es muy útil cuando el contenido 
        // se agrega dinámicamente (como con AJAX), porque podés detectar cuando un nuevo nodo aparece o se modifica, y actuar en consecuencia.
        const observer = new MutationObserver(function (mutaciones) {
            mutaciones.forEach(function (mutacion) {
                mutacion.addedNodes.forEach(function (nodo) {
                    // Verificamos que el nodo agregado sea un elemento <a> con las clases deseadas
                    if (
                        nodo.nodeType === 1 &&
                        nodo.matches('a.added_to_cart.wc-forward')
                    ) {
                        reemplazarTextoBoton(nodo);
                    }
                });
            });
        });

        // Iniciamos la observación del DOM
        observer.observe(document.body, {
            childList: true, // observar hijos directos
            subtree: true    // observar todo el subárbol
        });
    });
    </script>
    <?php
}

// [[[[init]]]]
// Añadimos la función al hook 'init' para que se ejecute al inicializar WordPress
add_action('init', 'eliminar_selector_orden_tienda');

// ================================================================================================
// Función que elimina el selector de ordenamiento (dropdown) en la página de tienda de WooCommerce
// ================================================================================================
function eliminar_selector_orden_tienda() {
    // Elimina la acción que muestra el dropdown de ordenación de productos en la tienda
    // woocommerce_before_shop_loop: hook que ejecuta funciones antes del listado de productos en tienda
    // woocommerce_catalog_ordering: función que genera el dropdown para ordenar productos
    // 30: prioridad con la que se ejecuta la función originalmente (importante para quitarla correctamente)
    remove_action('woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30);
}

// [[[[wp_footer]]]]
// Hook para insertar JavaScript en el footer que oculta la sección hero en la página tienda WooCommerce
add_action('wp_footer', 'ocultar_hero_section_shop');

// =================================================================================
// Función que oculta la sección hero que contiene el contenedor que dice SHOP en la 
// página principal de la tienda WooCommerce (shop).
// =================================================================================
function ocultar_hero_section_shop() {
    // Solo ejecutar si estamos en la página archivo de productos (shop)
    if (!is_shop()) {
        return;
    }
    ?>
    <script>
    // Espera que el DOM esté completamente cargado para manipular elementos
    document.addEventListener('DOMContentLoaded', function() {
        // Selecciona la sección hero específica mediante el atributo data-type
        var heroSection = document.querySelector('.hero-section[data-type="type-2"]');
        if (heroSection) {
            // Oculta la sección hero estableciendo display a 'none' lo que evita que la sección aparezca visible en la página shop.
            heroSection.style.display = 'none';
        }
    });
    </script>
    <?php
}

// [[[[pre_get_posts]]]]
// Este hook se ejecuta antes de que WordPress ejecute una consulta principal
add_action('pre_get_posts', 'ocultar_bundles_de_shop');

/* ============================================================
 * Función para ocultar los productos bundles de la tienda shop
 * ============================================================ */
function ocultar_bundles_de_shop($query) {
    // Asegura que:
    // - NO estamos en el panel de administración
    // - Es la consulta principal de WordPress (la principal del loop)
    // - Estamos en la página de la tienda (Shop)
    if (!is_admin() && $query->is_main_query() && is_shop()) {

        // Obtiene las condiciones de taxonomía ya existentes en la consulta, si las hay
        $tax_query = (array) $query->get('tax_query');

        // Agrega una nueva condición para excluir productos que pertenezcan a la categoría con slug 'bundles'
        $tax_query[] = array(
            'taxonomy' => 'product_cat', // Taxonomía usada por WooCommerce para categorías de productos
            'field'    => 'slug',        // Vamos a usar el slug de la categoría como referencia
            'terms'    => array('bundles'), // Categoría a excluir (importante: el slug debe ser exacto)
            'operator' => 'NOT IN',      // Indica que queremos excluir productos que estén en esa categoría
        );

        // Aplica la nueva tax_query modificada a la consulta principal
        $query->set('tax_query', $tax_query);
    }
}

// [[[[woocommerce_before_shop_loop_item_title]]]]
// Es un hook (acción) de WooCommerce que se ejecuta justo antes del título del producto cuando WooCommerce muestra la lista de productos 
// (por ejemplo, en la tienda, categorías o resultados de búsqueda).
 // mostrar_videos_shop() se ejecuta una vez por producto que se muestra en la página.
// Por lo tanto la función se invoca 28 veces, uno por cada producto.
// Eso está bien, porque cada producto necesita su HTML de video independiente.
add_action('woocommerce_before_shop_loop_item_title', 'mostrar_videos_shop', 10);

/* ==============================================================================================================================
 * Función que se encarga de mostrar los videos destacados de cada producto dentro del loop de la tienda WooCommerce. 
 * - Solo se ejecuta en la página principal de la tienda (is_shop()).
 * - Se Obtiene el URL del video desde un campo personalizado 'video_destacado' (ACF).
 * - Se muestra el video con un poster fijo para evitar espacio vacío mientras carga.
 * - Usa preload="metadata" para no cargar el video completo al cargar la página (solo metadatos).
 * Importante:
 * 	- El atributo preload="metadata" en la etiqueta <video> indica que sólo se cargan los metadatos (duración, dimensiones),
 *    pero no se descarga el video completo automáticamente, ayudando a optimizar el rendimiento y evitar cargar videos completos
 *    en la página de la tienda.
 * - El video se abre en un fancybox al hacer clic en el contenedor.
 * - Se muestra el video con un póster fijo o loader encima si no carga.
 * - El CSS limita el tamaño del contenedor del video sin afectar otros elementos como botones.
 * - Usa `data-src` para lazy loading (el JS carga el video cuando es visible).
 * - Aplica autoplay, muted, playsinline y loop manual vía JS.
 * - Muestra el precio como overlay. 
 * =============================================================================================================================== */
function mostrar_videos_shop() {
    if (!is_shop()) return;

    global $product;

    $faction = $_COOKIE['faction'] ?? 'gdi';
    $uploads_dir = wp_upload_dir();	 	
	
	if ($faction == 'gdi')
		$poster_path = trailingslashit($uploads_dir['baseurl']) . 'images/irina_poster.jpg';
	else
		$poster_path = trailingslashit($uploads_dir['baseurl']) . 'images/poster.jpg';
	
    $video_url = get_field('video_destacado', $product->get_id());
    if (!$video_url) return;
	
    ?>

    <a href="<?php echo esc_url($video_url); ?>" 
       data-fancybox="videos-tienda" 
       class="video-loop-link video-loop-wrapper">
        <div class="video-container" style="position:relative;">
            <video 
              width="630" 
              height="870"
              poster="<?php echo esc_url($poster_path); ?>"
              data-src="<?php echo esc_url($video_url); ?>"
              preload="metadata"
              muted 
              autoplay 
              playsinline
              class="video-loop"
              controlslist="nodownload"
              oncontextmenu="return false"
            ></video>
            <span class="price precio-video"><?php echo wc_price($product->get_price()); ?></span>
        </div>
    </a>

    <?php
}

// [[[[wp_footer]]]]
add_action('wp_footer', 'video_lazy_loop_shop');

/* =============================================================================================================
 * Función que:
 * 1. Aplica lazy loading a los videos que tienen el atributo `data-src`.
 *    - Esto evita que todos los videos se descarguen al mismo tiempo, optimizando el rendimiento.
 * 2. Diferencia entre videos visibles al cargar la página y los que aparecen al hacer scroll:
 *    - Los primeros videos visibles reciben un delay de 850ms antes de reproducir,
 *      para mostrar el poster un breve momento y evitar parpadeo inicial.
 *    - Los videos que aparecen al hacer scroll se reproducen inmediatamente sin delay.
 * 3. Control manual del loop:
 *    - Cuando el video termina, se reinicia al primer frame para evitar el spinner de Chrome.
 * 4. Fade-in suave:
 *    - Se aplica una clase `.active` que aumenta la opacidad cuando empieza la reproducción,
 *      creando una transición visual agradable.
 * 5. Ocultación del poster o placeholder:
 *    - Si existe un elemento anterior (por ejemplo, un div de placeholder), se oculta automáticamente
 *      cuando el primer frame del video está disponible (`loadeddata`).
 * 6. IntersectionObserver:
 *    - Detecta cuando los videos entran en la zona visible del viewport (o 200px antes),
 *      y carga/reproduce solo esos videos para no saturar la página. 
 * Beneficios:
 * - Mejora la experiencia de usuario y rendimiento.
 * - Evita spinners y parpadeos al final del loop.
 * - Funciona de manera elegante tanto al cargar como al hacer scroll.
 * ============================================================================================================= */
function video_lazy_loop_shop() {
    if (!is_shop()) return;
    ?>
    <style>
    .video-fade {
        opacity: 0;
        transition: opacity 0.35s ease-in-out;
    }
    .video-fade.active {
        opacity: 1;
    }
    </style>
    <script>
    document.addEventListener("DOMContentLoaded", function () {
        const videos = document.querySelectorAll('video[data-src]');

        const reproducirVideo = (video, delay=false) => {
            if (!video.src) video.src = video.getAttribute('data-src');
            video.preload = "auto";
            video.autoplay = false;
            video.pause();

            const playVideo = () => {
                video.currentTime = 0.01;
                video.play().then(() => video.classList.add('active')).catch(() => {});
            }

            if(delay) {
                setTimeout(playVideo, 850); // solo primeros visibles
            } else {
                playVideo(); // scroll: inmediato
            }

            // Loop manual
            video.addEventListener('timeupdate', () => {
                if(video.currentTime >= video.duration - 0.05){
                    video.pause();
                    video.currentTime = 0.01;
                    requestAnimationFrame(() => video.play());
                }
            });

            // Ocultar poster si existe placeholder
            video.addEventListener('loadeddata', () => {
                const placeholder = video.previousElementSibling;
                if(placeholder) placeholder.style.display = 'none';
            });
        }

        const observer = new IntersectionObserver((entries, obs) => {
            entries.forEach(entry => {
                if(entry.isIntersecting){
                    reproducirVideo(entry.target, false); // scroll: sin delay
                    obs.unobserve(entry.target);
                }
            });
        }, { rootMargin: "200px" });

        videos.forEach(video => {
            const rect = video.getBoundingClientRect();
            const inViewport = rect.top < window.innerHeight && rect.bottom > 0;
            if(inViewport){
                reproducirVideo(video, true); // primeros visibles: 850ms
            } else {
                observer.observe(video); // scroll: inmediato
            }
        });
    });
    </script>
    <?php
}

// [[[[wp_footer]]]]
add_action('wp_footer', 'fix_videos_chrome_shop_universal');

/* ============================================================================================================
 * Función: fix_videos_chrome_shop_universal()
 * Objetivo:
 * - Evitar los cuadros negros de los <video> en Chrome/Opera al salir de la página shop.
 * - Se ejecuta solo en la tienda (is_shop()). 
 * Cubre tres casos:
 * 1. Navegación por enlaces internos (View Cart, paginación, etc.).
 * 2. Envío de formularios (filtros Husky / WOOF o cualquier submit).
 * 3. Cambios manuales en la barra de direcciones (detectados con beforeunload / pagehide). 
 * Lógica:
 * - Toma un snapshot (canvas o poster fallback) sobre cada <video> visible antes de la salida.
 * - Solo para Chrome y Opera (evita afectar Firefox, Safari, Edge, etc.).
 * ============================================================================================================ */
function fix_videos_chrome_shop_universal() {
    if (!is_shop()) return;
    ?>
    <script>
    (function(){
        const ua = navigator.userAgent;
        const isChrome = /Chrome/.test(ua) && /Google Inc/.test(navigator.vendor);
        const isOpera = /OPR\//.test(ua);
        if (!(isChrome || isOpera)) return;

        // === Snapshot general (para galería principal) ===        
        function snapshotVideo(video){
            try {
				// Ignorar los videos del menú principal
				if (video.classList.contains('zaj-card-video')) return;				
				
                const w = video.offsetWidth || video.clientWidth;
                const h = video.offsetHeight || video.clientHeight;
                const canvas = document.createElement('canvas');
                canvas.width = video.videoWidth || w;
                canvas.height = video.videoHeight || h;
                canvas.style.width = w + 'px';
                canvas.style.height = h + 'px';
                canvas.style.position = 'absolute';
                canvas.style.top = video.offsetTop + 'px';
                canvas.style.left = video.offsetLeft + 'px';
                canvas.style.zIndex = '9999';
                canvas.style.pointerEvents = 'none';
                const ctx = canvas.getContext('2d');
                ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
                const parent = video.parentElement;
                if (getComputedStyle(parent).position === 'static') {
                    parent.style.position = 'relative';
                }
                parent.insertBefore(canvas, video);
                return true;
            } catch(e){
                const poster = video.getAttribute('poster');
                if (poster) {
                    const img = document.createElement('img');
                    img.src = poster;
                    img.style.width = (video.offsetWidth || video.clientWidth) + 'px';
                    img.style.height = (video.offsetHeight || video.clientHeight) + 'px';
                    img.style.position = 'absolute';
                    img.style.top = video.offsetTop + 'px';
                    img.style.left = video.offsetLeft + 'px';
                    img.style.zIndex = '9999';
                    img.style.pointerEvents = 'none';
                    const parent = video.parentElement;
                    if (getComputedStyle(parent).position === 'static') {
                        parent.style.position = 'relative';
                    }
                    parent.insertBefore(img, video);
                }
            }
        }
		
        // === Snapshot más liviano solo para videos del carrito Xootix ===
		function snapshotCartVideos(){
			const cartContainer = document.querySelector('.xoo-wsc-container');
			if (!cartContainer || getComputedStyle(cartContainer).display === 'none') return;

				cartContainer.querySelectorAll('video').forEach(video => {
					try {
						const rect = video.getBoundingClientRect();
						const w = rect.width || video.offsetWidth;
						const h = rect.height || video.offsetHeight;
						if (w === 0 || h === 0) return;

						const canvas = document.createElement('canvas');
						const ctx = canvas.getContext('2d');
						canvas.width = video.videoWidth || w;
						canvas.height = video.videoHeight || h;
						ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

						// Creamos la imagen snapshot
						const snapshot = new Image();
						snapshot.src = canvas.toDataURL('image/jpeg', 0.85);
						snapshot.style.position = 'absolute';
						snapshot.style.width = w + 'px';
						snapshot.style.height = h + 'px';
						snapshot.style.top = rect.top + window.scrollY + 'px';
						snapshot.style.left = rect.left + window.scrollX + 'px';
						snapshot.style.objectFit = 'cover';
						snapshot.style.zIndex = 2147483647;
						snapshot.style.pointerEvents = 'none';
						snapshot.className = 'xoo-wsc-video-snapshot';

						document.body.appendChild(snapshot);
						video.style.opacity = '0';
					} catch(e) {						
					}
			});
		}

        // === Snapshot general ===
        function snapshotAllVideos(){
            document.querySelectorAll('video').forEach(v => {
                // Ignorar los videos del carrito (se tratan aparte)
                if (v.closest('.xoo-wsc-container')) return;
                const rect = v.getBoundingClientRect();
                if (rect.width > 0 && rect.height > 0) snapshotVideo(v);
            });
        }

        // 1️⃣ Enlaces internos (paginación, View Cart, etc.)
        document.addEventListener('click', function(e){
            const link = e.target.closest && e.target.closest('a[href]');
            if (!link) return;
			
		    // Ignorar enlaces que abren videos con Fancybox o clics dentro de <video>
    		if (link.hasAttribute('data-fancybox') || e.target.closest('.video-loop-link') || e.target.tagName === 'VIDEO') return;
			
            // Ignorar enlaces de "Add to Cart" o "View Cart"
            if (link.classList.contains('added_to_cart') || link.classList.contains('add_to_cart_button')) return;
            if (link.target === '_blank') return;
			
			const href = link.getAttribute('href');
            if (!href || href.startsWith('#')) return;

            e.preventDefault();

            // Tomar snapshot de todo
            snapshotAllVideos();
            snapshotCartVideos();

            setTimeout(() => window.location.href = href, 80);
        }, true);

        // 2️⃣ Formularios (Husky / WOOF / AJAX)
        document.addEventListener('submit', function(){
            snapshotAllVideos();
            snapshotCartVideos();
        }, true);

        // 3️⃣ Cambios de dirección manual (enter en barra, reload, etc.)
        window.addEventListener('beforeunload', function(){
            snapshotAllVideos();
            snapshotCartVideos();
        });
        window.addEventListener('pagehide', function(){
            snapshotAllVideos();
            snapshotCartVideos();
        });
    })();
    </script>
    <?php
}

// Añade un filtro para modificar el HTML del botón "Agregar al carrito" en el loop de productos de WooCommerce
add_filter('woocommerce_loop_add_to_cart_link', 'insertar_categoria_en_boton', 10, 2);

// ==================================================================================
// Función que inserta el nombre de la primera categoría del producto antes del botón
// ==================================================================================
function insertar_categoria_en_boton($html, $product) {	
	
	// Obtiene las categorías (taxonomía 'product_cat') asociadas al producto
	$terms = get_the_terms($product->get_id(), 'product_cat');

	// Si el producto tiene categorías y no hubo error al obtenerlas
	if ($terms && !is_wp_error($terms)) {
						
		if (is_shop()) {				
			// Toma el nombre de la primera categoría y lo convierte a mayúsculas, escapando HTML para seguridad
			$cat_name = strtoupper(esc_html($terms[0]->name));
		}
		elseif (is_page('bundles')) {
			$cat_name = strtoupper(esc_html($terms[1]->name));			
		}		

		// Crea un span con una clase CSS para mostrar la categoría junto al botón
		$categoria_html = '<span class="categoria-junto-boton">' . $cat_name . '</span>';

		// Inserta ese span justo antes del HTML original del botón "Agregar al carrito"
		$html = $categoria_html . $html;
			
	}
	
    // Devuelve el HTML modificado (o el original si no tenía categorías)
    return $html;
}

// [[[[wp_head]]]]
// Hook para insertar los estilos en el head solo en la página tienda WooCommerce
add_action('wp_head', 'insertar_estilos_videos_tienda');

// ==================================================================================
// Función para insertar CSS específico solo en la página tienda WooCommerce
// Esto asegura que los estilos solo afecten a la tienda sin romper el layout general
// El CSS incluido limita el tamaño del video y evita que la galería se agrande o se
//  rompan otros estilos.
// ==================================================================================
function insertar_estilos_videos_tienda() {
    // Solo cargar el CSS si estamos en la página principal de la tienda WooCommerce
    if (!is_shop()) return;
    ?>
    <style>
    /* Estilos para el enlace que envuelve cada video en el loop de productos */
    body.woocommerce.archive ul.products.columns-4 a.video-loop-link {
        max-width: 300px;   /* Máximo ancho para que el contenedor no crezca */
        height: 400px;      /* Altura fija para mantener proporción visual */
        background: black;  /* Fondo negro para evitar espacios vacíos */
        border-radius: 12px; /* Bordes redondeados para estética */
        overflow: hidden;   /* Oculta cualquier contenido que sobresalga */
        display: block;     /* Mostrar como bloque para control de tamaño */
        position: relative; /* Posicionamiento relativo para hijos absolutos si hay */
        box-sizing: border-box; /* Incluir padding y borde en tamaño total */
        margin: 0 auto;     /* Centrar horizontalmente */
        flex-shrink: 0;     /* Evitar que flexbox reduzca el tamaño */
    }

    /* Estilos para el video dentro del contenedor */
    body.woocommerce.archive ul.products.columns-4 video.video-loop {
        width: 100%;         /* Ancho completo del contenedor padre */
        height: 100%;        /* Alto completo del contenedor padre */
        object-fit: cover;   /* Cubrir el área sin distorsionar */
        background: black;   /* Fondo negro para evitar huecos */
        border-radius: 12px; /* Bordes redondeados que coinciden con el contenedor */
        display: block;      /* Bloque para eliminar espacios inline */
        max-width: 100%;     /* No superar el ancho del contenedor */
        max-height: 100%;    /* No superar la altura del contenedor */
    }

    /* No aplicamos estilos a li.product ni otros enlaces para no romper botones ni layout */
    /* Puedes agregar estilos adicionales a li.product si quieres, pero sin cambiar tamaños */
    </style>
    <?php
}

// [[[[post_class()]]]]
// Es una función de WordPress que genera una lista de clases CSS para el contenedor HTML de un post o producto (o cualquier tipo de contenido), facilitando así el estilizado y la identificación del elemento en la página. La función post_class() imprime un atributo class con varias clases útiles, que describen el tipo, estado y características del post.
add_filter('post_class', 'agregar_clase_si_tiene_video', 20, 3);

// ===============================================================
// Añadir clase CSS 'tiene-video' a los productos que tienen video
// ===============================================================
// De esta forma los productos que tienen la clase 'tiene-video' no muestran la imagen vacia en la tienda. 
// Es decir, se oculta la imagen vacia si el producto tiene un video. En el .css seria:
// .woocommerce ul.products li.product.tiene-video img {
//    display: none !important;
// }
function agregar_clase_si_tiene_video($classes, $class, $post_id) {
    if (get_post_type($post_id) === 'product') {
        $video_url = get_field('video_destacado', $post_id);
        if (!empty($video_url)) {
            $classes[] = 'tiene-video';
        }
    }
    return $classes;
}

// [[[[loop_shop_per_page]]]]
// Es un filtro de WooCommerce que permite definir cuántos productos se muestran por página en las páginas de la tienda (catálogo, categoría, etc.).
// Cuando WooCommerce va a mostrar los productos de la tienda, ejecuta internamente un "loop" (bucle) para cargarlos. El filtro loop_shop_per_page le dice a WooCommerce cuántos productos mostrar antes de paginar. 
//  ¿Por qué usar 999?
// Prioridad más alta = se ejecuta más tarde
// WordPress ejecuta los filtros en orden ascendente de prioridad.
// El valor por defecto es 10, pero si se quiere que la función sobrescriba otras posibles modificaciones se le da una prioridad mayor, como 999.
// 999 significa: “asegúrate de que esta función se ejecute después que cualquier otra que modifique la cantidad de productos por página”.
// Mostrar 28 productos por página en WooCommerce
add_filter('loop_shop_per_page', 'productos_por_pagina', 999);

// ============================================================
// Función para mostrar 28 productos por página en WooCommerce
// ============================================================
function productos_por_pagina($cols) {
  return 28;
}

// [[[[woocommerce_loop_add_to_cart_link]]]]
// Aplica un filtro para modificar el HTML del botón "Añadir al carrito" en el loop de productos de WooCommerce (por ejemplo, en la tienda o bundles).
add_filter( 'woocommerce_loop_add_to_cart_link', 'modificar_boton_añadir_al_carrito_en_loop', 10, 2 );

/* ===========================================================================================================
 * Función para modificar el botón "Añadir al carrito" en la tienda para productos que ya están en el carrito.
 * 
 * Si el producto ya está en el carrito:
 * - Oculta el botón "Añadir al carrito" con CSS inline.
 * - Muestra un botón adicional "Ver carrito" que redirige al usuario al carrito.
 * 
 * @param string   $button  El HTML original del botón "Añadir al carrito".
 * @param WC_Product $product El objeto del producto actual en el loop.
 * @return string  HTML modificado con el botón "Ver carrito" si corresponde.
 * =========================================================================================================== */
function modificar_boton_añadir_al_carrito_en_loop($button, $product) {
    // Obtiene el ID del producto actual
    $product_id = $product->get_id();

    // Obtiene el objeto del carrito de WooCommerce
    $cart = WC()->cart;

    // Si no hay carrito disponible (por ejemplo, no se ha inicializado aún), devuelve el botón original
    if ( ! $cart ) {
        return $button;
    }

    // Obtiene todos los ítems del carrito
    $cart_items = $cart->get_cart();
    $producto_en_carrito = false;

    // Recorre los ítems del carrito para ver si el producto actual ya fue añadido
    foreach ( $cart_items as $cart_item ) {
        if ( $cart_item['product_id'] == $product_id ) {
            $producto_en_carrito = true;
            break; // Detiene el loop en cuanto lo encuentra (mejor rendimiento)
        }
    }

    // Si el producto ya está en el carrito
    if ( $producto_en_carrito ) {
        // Obtiene la URL de la página del carrito
        $cart_url = wc_get_cart_url();

		// Oculta el botón original de "Añadir al carrito" con CSS inline
        $button_oculto = str_replace('<a ', '<a style="display:none;" ', $button);
		
        // Crea un nuevo botón "Ver carrito" con clases de WooCommerce
		/* Se incluye la clase "added_to_cart" y el atributo "data-product_id" para que el boton ver carrito vaya a la ventana sidecart del plugin */
		$btn_ver_carrito = sprintf(
    		'<a href="%s" class="added_to_cart wc-forward" data-product_id="%d" aria-label="%s">%s</a>',			
    		esc_url( $cart_url ),	// Limpia (escapa) la URL para asegurarse de que sea segura antes de imprimirla en el HTML para evitar inyecciones de código o URLs malformadas.
    		esc_attr( $product_id ), // Evita que un valor numérico o string contenga caracteres que rompan el HTML o permitan XSS
    		esc_attr__( 'View Cart', 'woocommerce' ), // Usa __() para traducir el texto “View Cart” si hay traducción disponible en WooCommerce.
    		esc_html__( 'View Cart', 'woocommerce' ) // Usa __() para traducir el texto.
		);
		
        // Devuelve el botón "Añadir" oculto más el nuevo botón "Ver carrito"
        return $button_oculto . $btn_ver_carrito;
    }

    // Si el producto no está en el carrito, se deja el botón original sin cambios
    return $button;
}

// [[[[woocommerce_add_to_cart_validation]]]]
// Validación en PHP como respaldo
add_filter('woocommerce_add_to_cart_validation', 'limitar_cantidad_total_carrito', 10, 3);

/* ============================================================================
 * Función que limita la cantidad total de productos en el carrito a un máximo.
 * @param bool $passed  Indica si se permite agregar el producto.
 * @param int $product_id ID del producto.
 * @param int $quantity   Cantidad del producto.
 * @return bool
 * ============================================================================ */
function limitar_cantidad_total_carrito($passed, $product_id, $quantity) {
    $cantidad_actual = WC()->cart->get_cart_contents_count();
    $nueva_cantidad_total = $cantidad_actual + $quantity;
    $limite = 10;

    if ($nueva_cantidad_total > $limite) {
        wc_add_notice(sprintf('Only up to %d artworks are allowed per order.', $limite), 'error');
        return false; // Bloquea el agregado al carrito
    }

    return $passed;
}

// [[[[wp_footer]]]]
// Insertar en el footer HTML fijo para la alerta personalizada
// Esta estructura se agrega solo UNA vez para evitar duplicados y se mantiene oculta inicialmente.
add_action('wp_footer', 'insertar_div_alerta_personalizada');

// =========================================================================================
// Función para insertar en el footer un modal de alerta oculto, usado para mostrar mensajes
//  en shop o bundles
// =========================================================================================
function insertar_div_alerta_personalizada() {
	// Solo ejecutar este bloque en la página de tienda (shop) o bundles
    if (!is_shop() && !is_page('bundles')) return;
    ?>
    <!-- Alerta personalizada fija (solo se agrega UNA vez en el footer) -->
	<!-- Capa semitransparente que oscurece el fondo cuando la alerta está visible -->
    <div id="custom-alert-overlay" style="display:none;"></div>
    <div id="custom-alert" role="alert" aria-live="assertive" aria-atomic="true" style="display:none;">
		 <!-- Barra superior de la alerta con botón de cierre -->
        <div id="custom-alert-header">
			<!-- Botón para cerrar la alerta, accesible con etiqueta aria-label -->
            <button id="custom-alert-close" aria-label="Cerrar alerta">&times;</button>
        </div>		
        <!-- Contenedor donde se insertará el mensaje dinámicamente vía JS -->
        <div id="custom-alert-content"></div> <!-- contenido vacío -->
		<!-- Botón visible para cerrar la alerta -->
        <button id="custom-alert-button">Close</button>
    </div>
    <?php
}

// Hook para agregar el script de la alerta personalizada en el footer
add_action('wp_footer', 'mostrar_alerta_personalizada');

/* ====================================================================
 * Función que se ejecuta para mostrar/ocultar una alerta personalizada
 * que también deshabilita el icono del carrito de Xootix
 * ==================================================================== */
function mostrar_alerta_personalizada() {   
    if (!is_shop() && !is_page('bundles')) return;
    ?>
    <script>
    jQuery(document).ready(function($) {
        const $overlay = $('#custom-alert-overlay');
        const $alertBox = $('#custom-alert');
        const $btnClose = $('#custom-alert-close');
        const $btnClose2 = $('#custom-alert-button');
        const $alertContent = $('#custom-alert-content');

        // Asegurarnos que overlay y alerta estén en body (evita stacking context raro)
        if ($overlay.length && !$overlay.parent().is('body')) $overlay.appendTo('body');
        if ($alertBox.length && !$alertBox.parent().is('body')) $alertBox.appendTo('body');

        function mostrarAlerta(mensaje) {
            $alertContent.text(mensaje);

            // Clase para bloquear scroll y clicks en elementos externos
            $('body').addClass('alert-open no-scroll');

            // Mostrar overlay y alerta
            $overlay.fadeIn(200);
            $alertBox.fadeIn(200);

            // Bloquear clic derecho
            $(document).on('contextmenu.customAlert', function(e){ e.preventDefault(); });

            // Bloquear clics en el carrito (refuerzo JS)
            $('.xoo-wsc-basket, .xoo-wsc').css('pointer-events', 'none');
        }

        function ocultarAlerta() {
            $alertBox.fadeOut(200);
            $overlay.fadeOut(200);
            $('body').removeClass('alert-open no-scroll');

            // Habilitar clic derecho
            $(document).off('contextmenu.customAlert');

            // Restaurar clics en el carrito
            $('.xoo-wsc-basket, .xoo-wsc').css('pointer-events', 'auto');
			
			const event = new CustomEvent('customAlertClosed');
			window.dispatchEvent(event);			
        }

        $btnClose.on('click', ocultarAlerta);
        $btnClose2.on('click', ocultarAlerta);

        // Hacer la función accesible globalmente
        window.mostrarAlertaPersonalizada = mostrarAlerta;
    });
    </script>
    <?php
}

// [[[[wp_footer]]]]
// Añade un script en el footer que limita la cantidad máxima de productos/artworks
// que un usuario puede agregar al carrito desde la tienda o categorías de producto.
add_action('wp_footer', 'verificar_limite_productos');

/* ============================================================================================
 * Función que verifica los productos del carrito y si sobrepasan los diez aparece una alerta 
 * en caso de que el usuario haga click en un botón "Add to Cart"
 * Muestra una alerta personalizada si la función `mostrarAlertaPersonalizada` está disponible.
 * Si no, muestra un alert() simple.
 * Esta validación se aplica a la página shop y bundles. 
 * ============================================================================================ */
function verificar_limite_productos() {
    if (!is_shop() && !is_page('bundles')) return;
    ?>
    <script>
    jQuery(document).ready(function($) {
        const limite = 10;

        // Función para mostrar alerta y prevenir añadir
        function verificarLimite(e) {			
            const cantidadActual = parseInt($('.xoo-wsc-items-count').text().trim() || '0');
            if (cantidadActual >= limite) {
                e.preventDefault();
                e.stopImmediatePropagation();

                if (typeof window.mostrarAlertaPersonalizada === 'function') {
                    window.mostrarAlertaPersonalizada('Only up to 10 artworks are allowed per order.');
                } else {
                    alert('Only up to 10 artworks are allowed per order.');
                }
				
				const $btn = jQuery(e.currentTarget);
				// Restaurar botón manualmente
                setTimeout(() => {
                    $btn.removeClass('loading disabled').removeAttr('aria-disabled').css('pointer-events', 'auto');
                }, 150); // pequeño retardo visual opcional
				
                return false;
            }
        }

        // === Tienda principal ===
        <?php if (is_shop()) : ?>
        $('body').on('click', '.add_to_cart_button', verificarLimite);
        <?php endif; ?>

        // === Página Bundles ===
        <?php if (is_page('bundles')) : ?>
        $('body').on('click', '.bundle-container .add_to_cart_button', verificarLimite);
        <?php endif; ?>
    });
    </script>
    <?php
}

// [[[[wp_enqueue_scripts]]]]
// Agrega la URL del carrito disponible para JavaScript
/*
add_action('wp_enqueue_scripts', function() {
    wp_localize_script('jquery', 'wc_cart_url', wc_get_cart_url());
});
*/

add_action('wp_enqueue_scripts', function() {
    wp_localize_script('jquery', 'wc_cart_url', array(
        'url' => wc_get_cart_url()
    ));
});

// [[[[actualizar_boton_producto_eliminado_desde_el_sidecart]]]]
// Inserta el script en el pie de página solo en la tienda (shop)
add_action('wp_footer', 'actualizar_boton_producto_eliminado_desde_el_sidecart');

/* ==========================================================================================================================================================================================
 * Función que inserta un script en la tienda para sincronizar los botones "Añadir al carrito" y "Ver carrito".
 * - Cuando se elimina un producto desde el sidecart: muestra el botón "Añadir al carrito" y oculta "Ver carrito" solo para ese producto.
 * - Cuando se vuelve a añadir un producto que fue eliminado del sidecart: oculta el botón "Añadir al carrito" y crea un único botón "Ver carrito" para ese producto, evitando duplicados. 
 * - Funciona correctamente al navegar a otras secciones y regresar al Shop, manteniendo la sincronización del estado de los botones.  
 * Al eliminar un producto desde el sidecart, detecta cuál fue el producto eliminado y en la página Shop sólo para ese producto muestra el botón "Añadir al carrito" (que estaba oculto) y oculta el botón "Ver carrito".
Cuando el usuario vuelve a añadir ese mismo producto (por ejemplo, después de haberlo eliminado), el script oculta el botón "Añadir al carrito" y crea un único botón "Ver carrito" para ese producto, evitando que haya botones duplicados.
 * ========================================================================================================================================================================================== */
function actualizar_boton_producto_eliminado_desde_el_sidecart() {
     if (!is_shop()) return; // Ejecutar solo en la página del shop	    	
    ?>
    <script>
    jQuery(function($) {
				
		/* 1. Cuando se elimina un producto desde el sidecart: muestra el botón 'Añadir al carrito' y oculta 'Ver carrito' solo para ese producto."		
			El código detecta el click en el botón de eliminar dentro del sidecart (.xoo-wsc-smr-del).
			Obtiene el slug del producto eliminado, pide al servidor su ID vía AJAX.
			Luego recorre los productos visibles en el shop buscando el producto con ese ID.
			Para ese producto, muestra el botón "Añadir al carrito" (btnAdd.css('display', 'inline-block');) y oculta el botón "Ver carrito" (btnView.hide();).
			Solo afecta a ese producto específico gracias a la comparación de IDs. */		
		
        // Detecta clic en el botón de eliminar producto dentro del sidecart
        $(document).on('click', '.xoo-wsc-smr-del', function() {

            // Busca el contenedor del producto eliminado en el sidecart
            const $productoSidecart = $(this).closest('.xoo-wsc-product');

            // Obtiene el enlace al producto para extraer el slug
            const href = $productoSidecart.find('a').attr('href');
            if (!href) return;

            // Extrae el slug del producto desde la URL
            let slug = href.split('/product/')[1];
            if (!slug) return;
            slug = slug.replace(/\/$/, '');

            // Hace una petición AJAX para obtener el ID del producto a partir del slug
            $.post('<?php echo admin_url('admin-ajax.php'); ?>', {
                action: 'obtener_id_desde_slug',
                slug: slug
            }, function(response) {
                if (!response.success) return;

                // ID del producto eliminado
                const eliminadoID = response.data.id.toString();

                // Recorre todos los productos visibles en la tienda
                $('.product').each(function() {
                    const $prod = $(this);

                    // Busca el ID del producto mostrado en la tienda
                    const pid = $prod.find('a.add_to_cart_button').data('product_id');

                    // Compara con el producto eliminado
                    if (pid && pid.toString() === eliminadoID) {

                        // Muestra nuevamente el botón 'Añadir al carrito'
                        const btnAdd = $prod.find('a.add_to_cart_button[data-product_id="' + eliminadoID + '"]');
                        if (btnAdd.length) {
                            btnAdd.css('display', 'inline-block');
                        }

                        // Elimina cualquier botón 'Ver carrito' presente en ese producto
                        const btnView = $prod.find('a.button.wc-forward, a.added_to_cart.wc-forward');
                        if (btnView.length) {
							btnView.remove();                             
                        }
						
                        return false; // Salir del each una vez que se actualizó el producto correspondiente
                    }
                });
            });
        });
				
		/* 2. Cuando se vuelve a añadir un producto que fue eliminado del sidecart: oculta el botón 'Añadir al carrito' y crea un único botón 'Ver carrito' para ese producto, evitando duplicados."
			Escucha el evento de WooCommerce added_to_cart que se dispara cuando se añade un producto al carrito.
			Obtiene el botón "Añadir al carrito" que generó el evento.
			Oculta ese botón ($button.hide();).
			Elimina cualquier botón "Ver carrito" duplicado que pudiera haber junto al botón oculto.
			Luego agrega un solo botón "Ver carrito" con la clase y atributos adecuados, justo después del botón oculto.
			Esto evita que se acumulen botones "Ver carrito" para el mismo producto. */		
		
        // Cuando se añade un producto al carrito desde la tienda
        $(document.body).on('added_to_cart', function(event, fragments, cart_hash, $button) {
            if (!$button || !$button.length) return;

            const pid = $button.data('product_id');
            if (!pid) return;

            // Oculta el botón 'Añadir al carrito' que se acaba de usar
            $button.hide();

            // Elimina instancias duplicadas de 'Ver carrito'
            $button.siblings('a.added_to_cart.wc-forward, a.button.wc-forward').remove();

            // Inserta solo un botón 'Ver carrito' limpio
            const verCarrito = $('<a>', {
                href: wc_cart_url,
                class: 'added_to_cart wc-forward',
                'data-product_id': pid,
                text: 'Ver carrito'
            });

            $button.after(verCarrito);
        });
    });
    </script>
    <?php
}

// Registra la acción AJAX para usuarios logueados y no logueados,
// que devolverá el ID del producto dado su slug (parte final de la URL)
add_action('wp_ajax_obtener_id_desde_slug', 'obtener_id_desde_slug_callback');
add_action('wp_ajax_nopriv_obtener_id_desde_slug', 'obtener_id_desde_slug_callback');

/* ==================================================================================
 * Función que responde a la petición AJAX para obtener el ID de un producto
 * dado su slug (parte final de la URL).  
 * Esta función está registrada para usuarios logueados y no logueados.  
 * Proceso:
 * - Verifica que se envió el parámetro 'slug' vía POST.
 * - Sanitiza el slug recibido para evitar inyección o caracteres inválidos.
 * - Busca el producto en la base de datos usando el slug.
 * - Si encuentra el producto, devuelve un JSON con el ID.
 * - Si no encuentra el producto o falta el slug, termina la ejecución sin respuesta.
 * ================================================================================== */
function obtener_id_desde_slug_callback() {
    // Verifica que se haya recibido el parámetro 'slug' vía POST
    if (!isset($_POST['slug'])) {
        wp_die(); // Termina sin respuesta si falta slug
    }

    // Limpia y sanitiza el valor recibido para evitar inyecciones o caracteres inválidos
    $slug = sanitize_title($_POST['slug']);

    // Busca el producto en la base de datos usando su slug (nombre amigable en la URL)
    $producto = get_page_by_path($slug, OBJECT, 'product');

    if ($producto) {
        // Si el producto existe, responde con éxito y devuelve un array con el ID del producto
        wp_send_json_success(['id' => $producto->ID]);
    } else {
        wp_die(); // Termina sin respuesta si no encuentra el producto
    }
}

// [[[[wp_footer]]]]
add_action('wp_footer', 'forzar_desactivar_retorno_shop');

/* ============================================================================================================ */
// Función que sirve para evitar que el enlace "Return to Shop" del carrito de xootix funcione cuando el
// usuario ya está en la página de la tienda principal (shop). Sirve para mejorar la experiencia de usuario 
// y evitar redirecciones innecesarias.
// Incluye:
// - Desactivación visual y funcional del botón.
// - Prevención de clics y navegación.
// - Detección automática mediante observer y eventos del plugin.
// - Reintentos para garantizar que el botón quede bloqueado.
// Se ejecuta únicamente en la página "shop".
// ============================================================================================================ */
function forzar_desactivar_retorno_shop() {
    // Solo ejecutar en shop
    if (!is_shop()) return;
    ?>
    <script>
    (function(){
        const SELECTOR = '.xoo-wsc-empty-cart a.xoo-wsc-btn';
        const DISABLED_MARK = 'data-mcart-disabled';

        // -----------------------------------------------------------------------
        // Aplica los estilos y atributos necesarios para desactivar el botón
        // -----------------------------------------------------------------------
        function estiloDesactivado(el){
            el.setAttribute(DISABLED_MARK, '1');
            el.setAttribute('aria-disabled', 'true');
            el.setAttribute('tabindex', '-1');
            el.style.pointerEvents = 'none';
            el.style.opacity = '0.5';
            el.style.cursor = 'default';
            try { el.textContent = "You're already in the shop"; } catch(e){}
            try {
                el.dataset.hrefBackup = el.getAttribute('href') || '';
                el.removeAttribute('href');
            } catch(e){}
        }

        // -----------------------------------------------------------------------
        // Busca el botón dentro del nodo indicado y lo desactiva si aparece
        // -----------------------------------------------------------------------
        function desactivarSiExiste(node){
            if (!node) return;

            if (node.matches && node.matches(SELECTOR) && !node.hasAttribute(DISABLED_MARK)) {
                estiloDesactivado(node);
            }

            const encontrados = node.querySelectorAll ? node.querySelectorAll(SELECTOR) : [];
            encontrados.forEach(function(el){
                if (!el.hasAttribute(DISABLED_MARK)) estiloDesactivado(el);
            });
        }

        // 1. Intento inmediato al cargar
        desactivarSiExiste(document);

        // 2. Escucha eventos del plugin que puedan reinyectar el botón
        ['xoo_wsc_loaded','xoo_wsc_cart_updated','xoo_wsc_open','xoo_wsc_after_load'].forEach(function(ev){
            document.addEventListener(ev, function(e){
                if (e && e.target) desactivarSiExiste(e.target);
                desactivarSiExiste(document);
            }, {passive:true});
        });

        // 3. Observa cambios en el DOM para aplicar el fix si el botón aparece luego
        const observer = new MutationObserver(function(mutations){
            for (let m of mutations) {
                if (m.addedNodes && m.addedNodes.length) {
                    m.addedNodes.forEach(function(node){
                        if (node.nodeType === 1) desactivarSiExiste(node);
                    });
                }
            }
        });

        observer.observe(document.documentElement || document.body, {
            childList: true,
            subtree: true
        });

        // 4. Captura global de clics para prevenir navegación accidental
        document.addEventListener('click', function(e){
            const a = e.target.closest ? e.target.closest(SELECTOR) : null;
            if (a && a.hasAttribute(DISABLED_MARK)) {
                e.preventDefault();
                e.stopImmediatePropagation();
                try {
                    a.style.transition = 'opacity .15s';
                    a.style.opacity = '0.5';
                } catch(err){}
                return false;
            }
        }, true);

        // 5. Fallback: reintentos cortos para asegurar la desactivación
        let intentos = 0;
        const intervalo = setInterval(function(){
            desactivarSiExiste(document);
            intentos++;
            if (intentos > 10) clearInterval(intervalo);
        }, 300);

    })();
    </script>
    <?php
}

// [[[[wp_footer]]]]
add_action('wp_footer', 'mostrar_mensaje_fallback_tienda');

/* ============================================================================================================
 * Función: fallback_shop_box_centered()
 * ------------------------------------------------------------------------------------------------------------
 * Objetivo:
 *  - Mostrar un mensaje de fallback en la página de SHOP cuando WooCommerce no devuelve productos.
 *  - Evita una apariencia vacía o rota al usuario final.  
 * Beneficios:
 *  - Previene interacciones inconsistentes cuando la tienda no muestra productos.
 *  - Mejora la UX mostrando un estado de "revisión temporal" en lugar de una pantalla vacía.
 *  - Evita errores de interacción con el carrito o filtros cuando no hay datos cargados. 
 * Contexto de uso:
 *  - Ideal para catálogos que dependen de filtros AJAX (Husky) y del modal de carrito (Xootix Side Cart).
 * ============================================================================================================ */
function  mostrar_mensaje_fallback_tienda() {
    if (!is_shop()) return;
    ?>
    <script>
    (function(){
      function showFallbackBox(){
        const productsList = document.querySelector('ul.products[data-products]');
        if (!productsList) return;

        const hasProducts = productsList.querySelectorAll('li.product').length > 0;
        if (!hasProducts) {

          // === 🔒 Desactivar sidebar (Husky Filters) ===
          const sidebar = document.querySelector('.ct-sidebar');
          if (sidebar) {
            sidebar.style.opacity = '0.5';
            sidebar.style.pointerEvents = 'none';
            sidebar.style.filter = 'grayscale(0.7) brightness(0.8)';
          }

          // === 🧱 Ocultar título "HUSKY Filter" ===
          const huskyTitle = document.querySelector('.ct-sidebar .widget-title');
          if (huskyTitle) huskyTitle.style.display = 'none';

          // === 🛒 Bloquear completamente el botón del carrito (Xootix) ===
          const cartBtn = document.querySelector('.xoo-wsc-basket');
          if (cartBtn) {
            cartBtn.style.opacity = '0.4';
            cartBtn.style.filter = 'grayscale(1)';
            cartBtn.style.cursor = 'not-allowed';
            
            // Remover cualquier listener del plugin
            const clone = cartBtn.cloneNode(true);
            cartBtn.parentNode.replaceChild(clone, cartBtn);

            // Bloquear burbujas de evento globales
            clone.addEventListener('click', function(e) {
              e.stopImmediatePropagation();
              e.preventDefault();
              return false;
            }, true);
          }

          // También bloquear cualquier click global que dispare el sidecart
          document.addEventListener('click', function(e){
            if (e.target.closest('.xoo-wsc-basket') || e.target.closest('.xoo-wsc-container')) {
              e.stopImmediatePropagation();
              e.preventDefault();
              return false;
            }
          }, true);

          // === 📦 Crear caja de mensaje central ===
          const box = document.createElement('div');
          box.className = 'shop-fallback-box-inner';
          box.innerHTML = `
            <h3>Temporary issue detected</h3>
            <p>We're currently reviewing this.<br>Please try again shortly.</p>
          `;

          const container = document.querySelector('div.ct-container > section');
          if (container) {
            const wrapper = document.createElement('div');
            wrapper.className = 'shop-fallback-wrapper';
            wrapper.appendChild(box);
            container.insertBefore(wrapper, container.querySelector('ul.products'));
          }
        }
      }

      // Esperar un poco por AJAX de WooCommerce
      setTimeout(showFallbackBox, 2000);
    })();

    // === Styles ===
    const style = document.createElement('style');
    style.textContent = `
      .shop-fallback-wrapper {
        display: flex;
        justify-content: center;
        margin: 30px 0;
      }
      .shop-fallback-box-inner {
        background: rgba(20, 20, 40, 0.85);
        border: 2px solid rgba(255, 255, 255, 0.15);
        border-radius: 16px;
        padding: 40px 60px;
        text-align: center;
        color: #fff;
        font-family: 'Poppins', sans-serif;
        box-shadow: 0 0 20px rgba(0,0,0,0.4);
      }
      .shop-fallback-box-inner h3 {
        font-size: 1.6em;
        margin-bottom: 10px;
        color: #ffd300;
        text-shadow: 0 0 6px rgba(255, 211, 0, 0.6);
      }
      .shop-fallback-box-inner p {
        font-size: 1em;
        line-height: 1.5em;
        opacity: 0.9;
      }
    `;
    document.head.appendChild(style);
    </script>
    <?php
}

// ################################################################################################################################################
// ################################################################################################################################################
// ################################################################################################################################################
// 											FANCYBOX
// ################################################################################################################################################
// ################################################################################################################################################
// ################################################################################################################################################
	
// [[[[wp_footer]]]]
// Inserta un script en el footer para configurar Fancybox con comportamiento personalizado
add_action('wp_footer', 'custom_fancybox_video_script');

/* =====================================================================================================
   Función que reemplaza el popup con un video sin controles, centrado y con el botón cerrar
   Script JS personalizado para personalizar la ventana Fancybox
   - El script sólo se carga en la página de tienda para no afectar otras páginas.
   - Inicializa Fancybox para los enlaces con data-fancybox="videos-tienda".
   - Cuando se abre un video mp4 en el popup, reemplaza el contenido por un video HTML5 centrado, 
     con tamaño controlado y sin controles visibles.
   - El popup muestra solo el botón cerrar (X).
   - Los videos miniatura se reproducen automáticamente y en loop, pero sin sonido para evitar bloqueos. 
// ===================================================================================================== */
function custom_fancybox_video_script() {
    if (is_shop()) {
        ?>
        <style>
        .fancybox__content > :not(.fancybox-video-wrapper) {
            display: none !important;
        }
        </style>

        <script>
        document.addEventListener('DOMContentLoaded', function () {
			
			// Listener global para bloquear clic derecho fuera del fancybox mientras blocker exista
            document.addEventListener('contextmenu', function(e) {
                if (document.getElementById('fancybox-overlay-blocker')) {
                    e.preventDefault();                    
                }
            });
						
			if (typeof Fancybox !== 'undefined') {
                // Vincula fancybox a los elementos con data-fancybox="videos-tienda"
                Fancybox.bind('[data-fancybox="videos-tienda"]', {
					dragToClose: false,	// Deshabilitar el drag de fancybox completamente
                    width: "90vw",
                    height: "80vh",
                    showClass: "fancybox-fadeIn",
                    hideClass: "fancybox-fadeOut",
                    animated: true,
                    closeButton: "inside",
					click: false,
                    Toolbar: {
                        display: ['close']
                    },
                    on: {
						// Evento que se ejecuta cuando se muestra el fancybox
                        reveal: async (fancybox, slide) => {
							
							// Bloquea interacción del fondo
        					document.body.classList.add('fancybox-lock');
							
                            const urlOri = slide.src;

                            // Agrega capa bloqueadora sólo una vez
                            if (!document.getElementById('fancybox-overlay-blocker')) {
                                const blocker = document.createElement('div');
                                blocker.id = 'fancybox-overlay-blocker';
                                blocker.style.cssText = `
                                    position: fixed;
                                    top: 0;
                                    left: 0;
                                    width: 100vw;
                                    height: 100vh;
                                    background: transparent;
                                    z-index: 99998;
                                    pointer-events: all;
									cursor: not-allowed !important;
        							user-select: none;
        							touch-action: none;
                                `;
                                document.body.appendChild(blocker);
								
								// Deshabilitar clic derecho sólo en el overlay bloqueador
    							blocker.addEventListener('contextmenu', function(e) {
        							e.preventDefault();
    							});
                            }
							
                            if (urlOri.endsWith('.mp4')) {
                                const videoHTML = `
                                  <div class="fancybox-video-wrapper" style="cursor: default;">
                                    <div class="fancybox-video-container" style="position: relative; cursor: default;">
                                        <button data-fancybox-close class="f-button is-close">
                                            <svg viewBox="0 0 24 24" width="20" height="20" stroke="white" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M18 6 L6 18" />
                                                <path d="M6 6 L18 18" />
                                            </svg>
                                        </button>

                                        <button class="custom-nav-prev" style="position:absolute; top:50%; left:-48px; background:black; border:none; border-radius:50%; width:36px; height:36px; display:flex; align-items:center; justify-content:center; cursor:pointer;">
                                            <svg viewBox="0 0 24 24" width="20" height="20" stroke="white" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M15 18 L9 12 L15 6" />
                                            </svg>
                                        </button>

                                        <button class="custom-nav-next" style="position:absolute; top:50%; right:-48px; background:black; border:none; border-radius:50%; width:36px; height:36px; display:flex; align-items:center; justify-content:center; cursor:pointer;">
                                            <svg viewBox="0 0 24 24" width="20" height="20" stroke="white" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M9 6 L15 12 L9 18" />
                                            </svg>
                                        </button>

                                        <video autoplay muted loop playsinline tabindex="-1" oncontextmenu="return false" style="
                                            max-width: 100%;
                                            max-height: 100%;
                                            user-select: none;
                                            -webkit-user-select: none;
                                        ">
                                            <source src="${urlOri}" type="video/mp4" />
                                        </video>
                                    </div>
                                  </div>`;

                                slide.$content.innerHTML = videoHTML;

                                const video = slide.$content.querySelector("video");
                                const videoContainer = slide.$content.querySelector(".fancybox-video-container");

                                if (video) {
                                    video.addEventListener("contextmenu", e => e.preventDefault());
                                    video.addEventListener("dragstart", e => e.preventDefault());
                                    video.addEventListener("selectstart", e => e.preventDefault());
                                    video.setAttribute("draggable", "false");
                                }

                                if (videoContainer) {
                                    videoContainer.addEventListener("contextmenu", e => e.preventDefault());
                                    videoContainer.style.cursor = "default";
                                }

                                // Navegación
                                slide.$content.querySelector(".custom-nav-prev")?.addEventListener("click", () => fancybox.prev());
                                slide.$content.querySelector(".custom-nav-next")?.addEventListener("click", () => fancybox.next());

                                document.addEventListener('keydown', function (event) {
                                    const instance = Fancybox.getInstance();
                                    if (instance) {
                                        if (event.key === "ArrowLeft") {
                                            event.preventDefault();
                                            instance.prev();
                                        }
                                        if (event.key === "ArrowRight") {
                                            event.preventDefault();
                                            instance.next();
                                        }
                                    }
                                });
                            }
                        },
						// Evento que se ejcuta cuando el Fancybox se está cerrando
						closing: () => {
        					// Desbloquea al cerrar
        					document.body.classList.remove('fancybox-lock');									
							// Elimina capa bloqueadora si existe
							const blocker = document.getElementById('fancybox-overlay-blocker');
							if (blocker) {
								blocker.remove();
							 }
    					}
                    }
                });
            }
        });
        </script>
        <?php
    }
}

// [[[[wp_footer]]]]
// Esto inserta código justo antes del cierre de la etiqueta </body> en el frontend
add_action('wp_footer', 'cerrar_fancybox_con_jquery');

// =========================================================================================================================================================================
// Función que agrega un pequeño script JavaScript al final de la página de WooCommerce (la tienda)
// El script detecta cuando haces click en un botón personalizado para cerrar Fancybox (button[data-fancybox-close]) y llama manualmente al método Fancybox.close() para cerrar el modal.
// Este código asegura que el botón de cerrar personalizado (data-fancybox-close) funcione incluso si el contenido fue inyectado dinámicamente (como en los popups de video)
// =========================================================================================================================================================================
function cerrar_fancybox_con_jquery() {
	// Este código solo se ejecuta en:
    // - La página principal de la tienda (is_shop)    
    if (is_shop()) {
        ?>
        <script>
		// Espera a que el DOM esté completamente cargado
        jQuery(document).ready(function($) {
		  // Delegación de evento: escucha clics en botones con data-fancybox-close
          $(document).on('click', 'button[data-fancybox-close]', function() {
			// Verifica que la librería Fancybox esté cargada correctamente
            if (typeof Fancybox !== 'undefined' && typeof Fancybox.close === 'function') {
			  // Cierra manualmente el popup de Fancybox
              Fancybox.close();
            }
          });
        });
        </script>
        <?php
    }
}

// [[[[wp_footer]]]]
// Hook para inyectar CSS/JS en el footer únicamente en la página de tienda
add_action('wp_footer', 'cf_fix_cart_over_fancybox');

/* ============================================================================================================================
 * Función que permite que el modal del carrito (Xoo) funcione y sea interactivo **por encima**
 * de Fancybox cuando éste está abierto. No reinicializa ni rompe Fancybox:
 * - Usa una clase (.xoo-cart-open) para neutralizar el ::after pseudo-overlay de Fancybox (que no puede seleccionarse desde JS)
 * - Desactiva temporalmente el blocker real (#fancybox-overlay-blocker) si existe (pointer-events toggling)
 * - Eleva el z-index del modal del carrito para que quede visible e interactivo
 * Solo se inyecta en la página de tienda (is_shop()) para no afectar otras páginas.
 * ============================================================================================================================ */
function cf_fix_cart_over_fancybox() {
    // Evitar ejecutar fuera de la tienda
    if (!is_shop()) return;
    ?>
    <style>
    /*
     * ===============================
     * CSS: ajustar cursor y z-index
     * ===============================
     *
     * 1) Forzamos cursor pointer (la "manito") en todos los elementos
     *    que habitualmente son interactivos dentro del modal del carrito.
     *    Muchos elementos del plugin Xoo son <div>/<span> (no botones),
     *    por eso listamos clases y atributos comunes para garantizar el cursor.
     *
     * 2) Mientras el body tenga la clase .xoo-cart-open, anulamos la
     *    captura del ::after de Fancybox (el pseudo-overlay) usando
     *    pointer-events: none. No manipulamos ::after desde JS — solo
     *    modificamos su comportamiento vía selector combinado.
     *
     * 3) Elevamos el z-index del contenedor del carrito para que quede
     *    por encima de Fancybox, y garantizamos pointer-events en él.
     */
	.xoo-wsc-container button,
	.xoo-wsc-container a,
	.xoo-wsc-container [role="button"],
	.xoo-wsc-container .xoo-wsc-ft-btn,
	.xoo-wsc-container .xoo-wsc-ft-btn-continue,
	.xoo-wsc-container .xoo-wsc-icon-close,
	.xoo-wsc-basket,
	.xoo-wsc-basket * {
		cursor: pointer !important; /* fuerza la manito */
	}

    /* Cuando abrimos el carrito permitimos pasar clicks a través del ::after de Fancybox */
	body.fancybox-lock.xoo-cart-open::after {
    	pointer-events: none !important; /* deja pasar clicks al modal del carrito */
    	cursor: pointer !important;      /* mostrar manito sobre el overlay */
	}
		
	/* Permitir pointer-events en todo el body cuando el carrito está abierto, anulando pointer-events:none del .fancybox-lock  */
	body.fancybox-lock.xoo-cart-open {
    	pointer-events: auto !important;
	}
		
	/* Forzar cursor manito en todo cuando el carrito abierto esta encima de Fancybox */
	body.fancybox-lock.xoo-cart-open *,
	body.fancybox-lock.xoo-cart-open *::before,
	body.fancybox-lock.xoo-cart-open *::after {
    	cursor: pointer !important;
	}
		
    /* Aseguramos que el modal del carrito quede fijo y por encima de todo */
    .xoo-wsc-container {
        position: fixed !important;
        z-index: 2147483647 !important; /* valor muy alto para garantizar prioridad */
        pointer-events: auto !important; /* permitir interacción dentro del modal */
    }

    /* El botón del carrito (icono) debe quedar accesible siempre */
    .xoo-wsc-basket {
        z-index: 2147483648 !important;  /* un poquito más arriba que el contenedor */
        pointer-events: auto !important;
    }
    </style>

    <script>
    (function () {
        // Inicialización única para evitar dobles bindings si el script se carga más de una vez.
        if (window.cfCartFixInitialized) return;
        window.cfCartFixInitialized = true;

        /**
         * Helpers simples para obtener nodos que usamos repetidamente.
         * - getCartContainer(): devuelve el nodo .xoo-wsc-container (modal del carrito).
         * - getBlocker(): devuelve #fancybox-overlay-blocker si existe (blocker real que creamos para fancybox).
         */
        function getCartContainer() {
            return document.querySelector('.xoo-wsc-container');
        }
        function getBlocker() {
            return document.getElementById('fancybox-overlay-blocker');
        }

        /**
         * openCart()
         * - Muestra el modal del carrito (forzando display y estilos si el plugin no lo hiciera)
         * - Añade la clase body.xoo-cart-open para desactivar el ::after pseudo-overlay de Fancybox
         * - Desactiva temporalmente pointer-events en el blocker real (si existe) para que los clicks lleguen al carrito
         * - Sube z-index y pointer-events del modal para garantizar interactividad
         */
        function openCart() {
            const carrito = getCartContainer();
            if (!carrito) return;

            // Mostrar el modal: el plugin Xoo puede tener su propio método
            carrito.style.display = 'block';
            carrito.style.position = 'fixed';
            carrito.style.zIndex = '2147483647';
            carrito.style.pointerEvents = 'auto';

            // Clase que, mediante CSS, permite que ::after de fancy no capture eventos
            document.body.classList.add('xoo-cart-open');

            // Si existe el blocker real (div#fancybox-overlay-blocker), guardamos su estado y lo desactivamos
            const blocker = getBlocker();
            if (blocker) {
                blocker.dataset._prevPointer = blocker.style.pointerEvents || '';
                blocker.style.pointerEvents = 'none';
            }
        }

        /**
         * closeCart()
         * - Oculta el modal del carrito
         * - Quita la clase .xoo-cart-open y restaura pointer-events del blocker real
         * - Restaura estilos inline que agregamos
         */
		function closeCart() {
			const carrito = getCartContainer();
			if (!carrito) return;

			carrito.style.display = '';
			carrito.style.position = '';
			carrito.style.zIndex = '';
			carrito.style.pointerEvents = '';

			document.body.classList.remove('xoo-cart-open');

			const blocker = getBlocker();
			if (blocker) {
				setTimeout(() => {
					blocker.style.pointerEvents = blocker.dataset._prevPointer || 'all';
					delete blocker.dataset._prevPointer;
				}, 100);  
			}
		}

        /**
         * Delegación de eventos (captura temprana) para:
         * - Detectar clicks en el botón del carrito (.xoo-wsc-basket / .xoo-wsch-basket) y abrir el modal.
         * - Detectar clicks en los selectores de cierre del plugin Xoo y cerrar el modal.
         * - Detectar clicks fuera del modal para cerrarlo (opcional, depende de UX).
         *
         * Usamos capture=true para interceptar antes que otros handlers y evitar conflictos.
         */
        document.addEventListener('click', function (e) {
            // Botón que abre el carrito (icono)
            const openBtn = e.target.closest('.xoo-wsc-basket, .xoo-wsch-basket');
            if (openBtn) {
                e.preventDefault();
                openCart();
                return;
            }

            // Selectores que cierran el modal (Xoo suele usar estas clases)
            const closeBtn = e.target.closest('.xoo-wsch-close, .xoo-wsc-cart-close, .xoo-wsc-ft-btn-continue');
            if (closeBtn) {
                e.preventDefault();
                closeCart();
                return;
            }

            // Si el carrito está abierto y clickeamos fuera de él (y no es el botón open), lo cerramos
            const carrito = getCartContainer();
            if (carrito && carrito.style.display === 'block' && !carrito.contains(e.target) && !e.target.closest('.xoo-wsc-basket')) {
                closeCart();
            }
        }, true); // capture: true

        /**
         * Escape key handler: si el carrito está abierto, lo cerramos con ESC.
         * Esto evita que el foco quede atascado o que el usuario no tenga forma de cerrar.
         */
        document.addEventListener('keydown', function (ev) {
            if (ev.key === 'Escape' && document.body.classList.contains('xoo-cart-open')) {
                closeCart();
            }
        });

        /**
         * Nota de seguridad y compatibilidad:
         * - No re-bindear Fancybox ni quitar/add la clase 'fancybox-lock' desde JS.
         *   Tal manipulación directa puede romper la inicialización de Fancybox (por eso lo evitamos).
         * - En su lugar actuar con:
         *     a) una clase body.xoo-cart-open que neutraliza el pseudo-overlay (CSS)
         *     b) toggling de pointer-events en #fancybox-overlay-blocker (DOM real)
         * - De este modo Fancybox sigue funcionando normalmente y el modal del carrito queda interactivo
         *   cuando se abre, sin cerrar ni reinicializar Fancybox.
         */
    })();
    </script>
    <?php
}

// ################################################################################################################################################
// ################################################################################################################################################
// ################################################################################################################################################
// 											BUNDLES
// ################################################################################################################################################
// ################################################################################################################################################
// ################################################################################################################################################

// [[[[wp_head]]]]
add_action('wp_head', 'estilo_personalizado_bundles');

/* ============================================================================================
 * Función que agrega estilos CSS personalizados para el titulo y body de la página de bundles.  
 * ============================================================================================ */ 
function estilo_personalizado_bundles() {  
   // Validar página de bundles    
   if (!is_page('bundles')) return;
    	 
    // Detectar facción desde la cookie
    $faction = $_COOKIE['faction'] ?? 'gdi';
	
    if ($faction === 'gdi') {				
		$body_bg = 'linear-gradient(270deg, #001f3f, #003f7f, #005bbb, #001f3f)'; // azul profundo eléctrico						
	} else if ($faction === 'nod') {
		// $body_bg = 'linear-gradient(135deg, rgba(10, 0, 0, 0.95) 0%, rgba(40, 0, 0, 0.9) 50%, rgba(120, 0, 0, 0.4) 100% )';
		$body_bg = 'linear-gradient(135deg, rgba(5, 0, 0, 0.97) 0%, rgba(20, 0, 0, 0.94) 40%, rgba(60, 0, 0, 0.85) 70%, rgba(90, 0, 0, 0.6) 100%)';
    }
	/*
	else if ($faction === 'scrin') {		
	    $body_bg = 'linear-gradient(270deg, #1a0033, #4a148c, #7b2ff7, #9d4edd, #1a0033)';
        $title_bg = 'linear-gradient(45deg, #ff00ff, #b5179e, #7209b7, #7b2ff7)';
        $title_shadow = '0 0 10px rgba(255,0,255,0.6), 0 0 20px rgba(155,0,255,0.4), 0 0 30px rgba(241,7,163,0.3)';
        $hud_color = 'rgba(155,0,255,0.7)';
	}
	*/
		
    ?>

    <style>		
		/* ===========================
           BACKGROUND ANIMADO 
           =========================== */
        body {
          background: <?= $body_bg ?>;
          background-size: 600% 600%;
          animation: gradientShift 20s ease infinite;
          overflow-x: hidden;
          font-family: Orbitron, sans-serif;
        }

        @keyframes gradientShift {
          0% { background-position: 0% 50%; }
          50% { background-position: 100% 50%; }
          100% { background-position: 0% 50%; }
        }						
	</style>

<div class="grid-3d"></div>    
<!-- <div class="hud-circle"></div> -->
<div class="custom-shop-title">BUNDLES</div>
<div class="scan-line"></div>
<div class="particles">
	<div class="particle" style="left:10%; animation-delay:0s;"></div>
    <div class="particle" style="left:30%; animation-delay:2s;"></div>
    <div class="particle" style="left:60%; animation-delay:4s;"></div>
    <div class="particle" style="left:80%; animation-delay:1s;"></div>
</div>

 <?php
}

// [[[[the_content]]]]
// the_content es un filtro que se aplica al texto principal de una entrada o página — es decir, el contenido que el editor de WordPress 
// guarda en la base de datos (lo que pondrías en el editor visual o en el bloque de texto). 
// Le estamos diciendo que después de generar el contenido normal de la página, ejecute la función mostrar_bundles_filtrados_rapido() 
// y devuelva el resultado como contenido final.
add_filter('the_content', 'mostrar_bundles_filtrados_rapido', 21);

/* ==============================================================================================
 * Función que sirve para renderizar la página "Bundles" de forma rápida y con videos responsivos 
 * Esta función reemplaza el contenido de la página "Bundles" con:
 *	- Un sidebar de filtros (búsqueda por texto, categorías y rango de precios)
 * 	- Una lista de bundles filtrados usando wc_get_products en PHP
 * 	- Cada bundle muestra sus productos con videos destacados y títulos
 * 	- Los videos y títulos se escalan automáticamente según el ancho del contenedor
 * 	- Optimizado para velocidad, filtrando bundles en PHP y evitando consultas SQL pesadas
 *  - Se implementa paginación, 12 bundles por página
 * =============================================================================================== */
function mostrar_bundles_filtrados_rapido($content) {
    // Solo aplicamos en la página con slug 'bundles'
    if (!is_page('bundles')) return $content;

    // Capturamos los parámetros GET para filtros
    $q         = isset($_GET['q']) ? sanitize_text_field(wp_unslash($_GET['q'])) : '';
    $min_price = isset($_GET['min_price']) ? floatval($_GET['min_price']) : 35;
    $max_price = isset($_GET['max_price']) ? floatval($_GET['max_price']) : 50;

    // Capturamos categorías seleccionadas
    $cats_raw  = isset($_GET['cat']) ? (array) $_GET['cat'] : [];
    $all_terms = get_terms(['taxonomy' => 'product_cat', 'hide_empty' => false, 'fields' => 'all']);
    $all_slugs = $all_terms && !is_wp_error($all_terms) ? wp_list_pluck($all_terms, 'slug') : [];
    $cats      = array_values(array_filter(array_map(function($cat) use ($all_slugs) {
        $cat_slug = sanitize_title($cat);
        return in_array($cat_slug, $all_slugs, true) ? $cat_slug : null;
    }, $cats_raw)));

    // Siempre incluir 'bundles'
    $cats_final = array_unique(array_merge(['bundles'], $cats));

    // Query taxonómica
    $tax_query = [
        'relation' => 'AND',
        [
            'taxonomy' => 'product_cat',
            'field'    => 'slug',
            'terms'    => ['bundles'],
        ]
    ];
    if (!empty($cats)) {
        $tax_query[] = [
            'taxonomy' => 'product_cat',
            'field'    => 'slug',
            'terms'    => $cats,
        ];
    }

    $all_bundles = wc_get_products([
        'status'     => 'publish',
        'limit'      => -1,
        'orderby'    => 'title',
        'order'      => 'ASC',
        'tax_query'  => $tax_query,
    ]);

    // === PAGINACIÓN ===
    $paged = max(1, absint(get_query_var('paged') ?: get_query_var('page') ?: (isset($_GET['paged']) ? intval($_GET['paged']) : 1)));
    $per_page = 12;
    $offset = ($paged - 1) * $per_page;

    // === FILTRADO MANUAL ===
    $filtered_products = [];
    foreach ($all_bundles as $bundle) {
        $price = floatval($bundle->get_price());
        $name = $bundle->get_name();
        if ($price >= $min_price && $price <= $max_price && ($q === '' || stripos($name, $q) !== false)) {
            $filtered_products[] = $bundle;
        }
    }

    $total_products = count($filtered_products);
    $total_pages    = max(1, ceil($total_products / $per_page));
    $filtered_products = array_slice($filtered_products, $offset, $per_page);

    // === ANIMACIÓN ===
    if (!empty($filtered_products)) {
        add_action('wp_footer', function() use ($filtered_products) {
            $animaciones = [
                'animacion_bundles_uno',
                'animacion_bundles_dos',
                'animacion_bundles_tres',
                'animacion_bundles_cuatro',
                'animacion_bundles_cinco'
            ];
            $seleccion = $animaciones[array_rand($animaciones)];
            if (function_exists($seleccion)) $seleccion();
        });
    }

    // === SETUP ===
    $bundles_url = get_permalink(get_page_by_path('bundles')) ?: home_url('/bundles/');
    $bundles_term = get_term_by('slug', 'bundles', 'product_cat');
    $exclude_id = $bundles_term ? [$bundles_term->term_id] : [];
    $cats_list = get_terms(['taxonomy' => 'product_cat', 'hide_empty' => true, 'exclude' => $exclude_id]);

    ob_start();
    ?>

    <div class="mc-bundles-wrap" style="display:grid;grid-template-columns:280px 1fr;gap:24px;">
        <!-- ===== SIDEBAR FILTROS ===== -->
        <aside class="mc-bundles-filter" style="background:#1c1230;color:#fff;padding:16px;border-radius:12px;">
            <form method="get" action="<?php echo esc_url($bundles_url); ?>">

                <!-- Buscar -->
                <div style="margin-bottom:14px;">
                    <label for="q" style="display:block;font-weight:600;margin-bottom:6px;">Search</label>
                    <input id="q" name="q" type="text" value="<?php echo esc_attr($q); ?>" style="width:100%;border-radius:8px;padding:8px;">
                </div>

                <!-- Categorías -->
                <div style="margin-bottom:14px;">
                    <label style="display:block;font-weight:600;margin-bottom:6px;">Categories</label>
					<div class="bundles-checkbox-container" style="border-radius:8px;background:rgba(255,255,255,.06);padding:8px;">
                        <?php if (!is_wp_error($cats_list) && $cats_list): ?>
                            <?php foreach ($cats_list as $term): ?>
                                <label>
                                    <input type="checkbox" name="cat[]" value="<?php echo esc_attr($term->slug); ?>"
                                    <?php checked(in_array($term->slug, $cats, true)); ?>>
                                    <span><?php echo esc_html($term->name); ?></span>
                                </label>
                            <?php endforeach; ?>
                        <?php else: ?><em>There are no additional categories.</em><?php endif; ?>
                    </div>
                </div>

                <!-- Precio -->
                <div style="margin-bottom:14px;">
                    <label style="display:block;font-weight:600;margin-bottom:6px;">Price</label>
                    <div class="price-slider-container" style="position:relative;height:50px;">
                        <div class="slider-track" style="position:absolute;top:50%;left:0;right:0;height:4px;background:#555;transform:translateY(-50%);border-radius:2px;"></div>
                        <div class="slider-range" style="position:absolute;top:50%;height:4px;background:#9c69e2;transform:translateY(-50%);border-radius:2px;"></div>
                        <div class="slider-thumb min-thumb" style="position:absolute;top:50%;width:20px;height:20px;border-radius:50%;background:#fff;border:2px solid #9c69e2;transform:translate(-50%,-50%);cursor:pointer;"></div>
                        <div class="price-label min-label" style="position:absolute;top:60%;text-align:center;width:60px;margin-left:-20px;color:#fff;font-size:0.9em;"><?php echo esc_html($min_price); ?></div>
                        <div class="slider-thumb max-thumb" style="position:absolute;top:50%;width:20px;height:20px;border-radius:50%;background:#fff;border:2px solid #9c69e2;transform:translate(-50%,-50%);cursor:pointer;"></div>
                        <div class="price-label max-label" style="position:absolute;top:60%;text-align:center;width:60px;margin-left:-20px;color:#fff;font-size:0.9em;"><?php echo esc_html($max_price); ?></div>
                        <input type="hidden" id="absolute_min_price" value="35">
                        <input type="hidden" id="absolute_max_price" value="50">
                        <input type="hidden" name="min_price" id="min_price_input" value="<?php echo esc_attr($min_price); ?>">
                        <input type="hidden" name="max_price" id="max_price_input" value="<?php echo esc_attr($max_price); ?>">
                    </div>
                </div>

                <div style="display:flex;gap:8px;align-items:center;">
                    <button type="submit" class="button" style="border-radius:10px;padding:8px 12px;">Apply Filters</button>
					<button type="button" class="button" onclick="window.location.href='https://mcartworksstudio.com/bundles/';">Reset</button>
                </div>
            </form>
        </aside>

        <!-- ===== RESULTADOS ===== -->
        <main class="mc-bundles-results">
            <?php
			// Si no hay resultados, mostramos mensaje
            if (!$filtered_products) {
				
    			$faction = $_COOKIE['faction'] ?? 'gdi';
				$uploads_dir = wp_upload_dir();
	
				if ($faction == 'gdi')
					$notfound_path = trailingslashit($uploads_dir['baseurl']) . 'images/irina_notfound.jpg';
				else
					$notfound_path = trailingslashit($uploads_dir['baseurl']) . 'images/notfound.jpg';
				
                echo '<div class="woocommerce-no-products-found"><div class="no-products-box">';
                echo '<img src="' . esc_url($notfound_path) . '" alt="No bundles available">';
                echo '<p>Oops! it looks like your search didn’t find any bundle.</p>';
                echo '</div></div>';
				
            } else {
				
                // <-- AQUÍ abrimos el contenedor GRID que ahora mostrará 2 por fila -->
                echo '<div class="bundles-grid">';

                // Iteramos cada bundle filtrado
                foreach ($filtered_products as $bundle) {
                    $bundle_id = $bundle->get_id();
										
					echo '<div id="bundle-' . esc_attr($bundle_id) . '" 
          				class="bundle-container" 
          				data-id="' . esc_attr($bundle_id) . '" 
          				data-type="' . esc_attr($bundle->get_type()) . '" 
          				data-add-to-cart-url="' . esc_url($bundle->add_to_cart_url()) . '" 
          				data-aria-label="' . esc_attr($bundle->add_to_cart_description()) . '" 
          				data-add-to-cart-text="' . esc_html($bundle->add_to_cart_text()) . '">';
									
                    echo '<h2 style="margin-bottom:15px;">' . esc_html($bundle->get_name()) . '</h2>';

                    // Obtenemos los productos asignados al bundle
                    $productos_in_bundle = get_field('productos_del_bundle', $bundle_id);
                    if (!$productos_in_bundle) {
                        echo '<p><em>Este bundle no tiene productos asignados.</em></p></div>';
                        continue;
                    }
									
    				$faction = $_COOKIE['faction'] ?? 'gdi';
					// Ruta del póster
					$uploads_dir = wp_upload_dir();
	
					if ($faction == 'gdi')
						$poster_url = trailingslashit($uploads_dir['baseurl']) . 'images/poster_bundle.png';
					else
						$poster_url = trailingslashit($uploads_dir['baseurl']) . 'images/poster_bundle_nod.jpg';
					
					echo '<div class="bundle-fila">'; // sin estilos inline
					
                    foreach ($productos_in_bundle as $prod) {
                        $prod_id = is_array($prod) && isset($prod['ID']) ? $prod['ID'] : $prod;
                        $video_url = get_field('video_destacado', $prod_id);
                        $producto = wc_get_product($prod_id);
                        if (!$producto) continue;
						
						// Contenedor de video y título
						echo '<div class="video-contenedor">';
						if ($video_url) {
							// Se agrega la clase lazy-video y el atributo data-src en lugar de src
							// loading="lazy": Le dice al navegador que intente cargar el video solo cuando sea necesario (cuando se acerque al viewport). 
							// Si el navegador soporta este atributo (la mayoría lo hace), entonces el video no se cargará inmediatamente.
							// data-src: Al usar data-src, la URL del video o imagen se mantiene "oculta" hasta que se activen ciertas condiciones. 
							// data-src guarda la URL del video de manera que no se cargue automáticamente al cargar la página.
							// Esto permite que el navegador no comience a cargar los videos hasta que realmente estén cerca de ser visibles.
							// En este caso, el atributo data-src permite que un script (como el IntersectionObserver) tome el control de cuándo la URL del video 
							// se asigna al src de la etiqueta <video>. Cuando el video entra en el área visible del viewport (o dentro de un rango como los 200px),
							// el script toma la URL almacenada en data-src y la asigna al atributo src, lo que hace que el video comience a cargarse.
							// Sin data-src, el navegador intentaría cargar todos los videos cuando la página se cargue, lo cual no es lo que queremos para optimizar el rendimiento.					
							echo '<video 
								  	class="lazy-video" 
								  	autoplay 
								  	muted 
								  	loop 
								  	playsinline 
									preload="none"
								  	loading="lazy" 
									controlslist="nodownload" 
									 oncontextmenu="return false"
								  	poster="' . esc_url($poster_url) . '">
								  	<source data-src="' . esc_url($video_url) . '" type="video/mp4">
							      </video>';
						} else {
							echo '<p><em>Sin video destacado.</em></p>';
						}

                        //echo '<h4 style="margin-top:8px;font-weight:600;">' . esc_html($producto->get_name()) . '</h4>';						
                        echo '</div>';
                    }
                    echo '</div>';

                    // Información adicional del bundle y botón de añadir al carrito
                    echo '<div class="bundle-info" style="display:flex;gap:20px;align-items:center;margin-top:10px;flex-wrap:wrap;">';										
                    /* $product_cats = wc_get_product_category_list($bundle_id);
                    if ($product_cats) {
                        echo '<span class="bundle-category" style="color:#fff;font-weight:600;">' . wp_kses_post($product_cats) . '</span>';
                    } */					
                    // echo '<span class="bundle-price" style="color:#fff;font-weight:bold;font-size:1.2em;">' . wp_kses_post($bundle->get_price_html()) . '</span>';
					echo '<span class="bundle-price">' . wp_kses_post($bundle->get_price_html()) . '</span>';
					
                    echo apply_filters(
                        'woocommerce_loop_add_to_cart_link',
                        sprintf(
                            '<a href="%s" data-quantity="1" class="button add_to_cart_button ajax_add_to_cart product_type_%s" data-product_id="%d" aria-label="%s">%s</a>',
                            esc_url($bundle->add_to_cart_url()),
                            esc_attr($bundle->get_type()),
                            esc_attr($bundle->get_id()),
                            esc_attr($bundle->add_to_cart_description()),
                            esc_html($bundle->add_to_cart_text())
                        ),
                        $bundle
                    );
                    echo '</div></div>';
                }

                echo '</div>'; // cierre .bundles-grid
            }
		
			// === PAGINACIÓN CON FILTROS MANTENIDOS ===
			if ($total_pages > 1) {
				echo '<div class="bundle-pagination" style="text-align:center;margin-top:30px;">';
				$pretty = (bool) get_option('permalink_structure');

				// Botón "Anterior"
				if ($paged > 1) {
					$prev_page = $paged - 1;
					$query_args = $_GET;
					if ($pretty) {
						$prev_url = trailingslashit(untrailingslashit($bundles_url)) . 'page/' . $prev_page . '/';
						if (!empty($query_args)) $prev_url = add_query_arg($query_args, $prev_url);
					} else {
						$prev_url = esc_url(add_query_arg(array_merge($query_args, ['paged' => $prev_page]), $bundles_url));
					}
					echo '<a href="' . esc_url($prev_url) . '" class="page-link prev">Previous</a>';
				}

				// === Números (solo 5 visibles a la vez) ===
				$range = 2; // cantidad de botones antes y después de la página actual
				$start = max(1, $paged - $range);
				$end = min($total_pages, $paged + $range);

				// Mostrar primera página si no está visible
				if ($start > 1) {
					$query_args = $_GET;
					if ($pretty) {
						$first_url = trailingslashit(untrailingslashit($bundles_url)) . 'page/1/';
						if (!empty($query_args)) $first_url = add_query_arg($query_args, $first_url);
					} else {
						$first_url = esc_url(add_query_arg(array_merge($query_args, ['paged' => 1]), $bundles_url));
					}
					echo '<a href="' . esc_url($first_url) . '" class="page-link">1</a>';
					if ($start > 2) echo '<span class="dots">...</span>';
				}

				// Números intermedios
				for ($i = $start; $i <= $end; $i++) {
					$query_args = $_GET;
					if ($pretty) {
						$page_url = trailingslashit(untrailingslashit($bundles_url)) . 'page/' . $i . '/';
						if (!empty($query_args)) $page_url = add_query_arg($query_args, $page_url);
					} else {
						$page_url = esc_url(add_query_arg(array_merge($query_args, ['paged' => $i]), $bundles_url));
					}
					echo '<a href="' . esc_url($page_url) . '" class="page-link ' . ($paged == $i ? 'active' : '') . '">' . $i . '</a>';
				}

				// Mostrar última página si no está visible
				if ($end < $total_pages) {
					if ($end < $total_pages - 1) echo '<span class="dots">...</span>';
					$query_args = $_GET;
					if ($pretty) {
						$last_url = trailingslashit(untrailingslashit($bundles_url)) . 'page/' . $total_pages . '/';
						if (!empty($query_args)) $last_url = add_query_arg($query_args, $last_url);
					} else {
						$last_url = esc_url(add_query_arg(array_merge($query_args, ['paged' => $total_pages]), $bundles_url));
					}
					echo '<a href="' . esc_url($last_url) . '" class="page-link">' . $total_pages . '</a>';
				}

				// Botón "Siguiente"
				if ($paged < $total_pages) {
					$next_page = $paged + 1;
					$query_args = $_GET;
					if ($pretty) {
						$next_url = trailingslashit(untrailingslashit($bundles_url)) . 'page/' . $next_page . '/';
						if (!empty($query_args)) $next_url = add_query_arg($query_args, $next_url);
					} else {
						$next_url = esc_url(add_query_arg(array_merge($query_args, ['paged' => $next_page]), $bundles_url));
					}
					echo '<a href="' . esc_url($next_url) . '" class="page-link next">Next</a>';
				}

				echo '</div>';
			}
            ?>
        </main>
    </div>

    <script>
    document.addEventListener("DOMContentLoaded", function() {
        const min = document.querySelector('.min-label');
        const max = document.querySelector('.max-label');
        if(min && max) {
            min.textContent = document.getElementById('min_price_input').value;
            max.textContent = document.getElementById('max_price_input').value;
        }
    });
    </script>

    <?php
    return $content . ob_get_clean();
}


// [[[[wp_footer]]]]
// Enganchamos la función a 'wp_footer' para que el script se imprima al final del <body>
add_action('wp_footer', 'agregar_lazy_loading_videos');

/* =============================================================================================================
 * Función que aplica Lazy Loading a los videos en la página "Bundles" y muestra un póster hasta que se carguen.
 * 
 * Funcionamiento:
 * 1. Los videos se insertan con <source data-src="..."> en lugar de src, y con preload="none".
 * 2. Se utiliza IntersectionObserver para detectar cuándo un video está a punto de entrar al viewport.
 * 3. Cuando entra, el script copia data-src → src, llama a video.load() y lo reproduce automáticamente.
 * 4. El póster (poster="...") se mantiene visible hasta que el navegador tenga datos suficientes para mostrar 
 * el primer frame.
 * 
 * Beneficios:
 * - Se reducen cargas innecesarias al no descargar todos los videos de golpe.
 * - Se optimiza la experiencia visual mostrando siempre una imagen de póster antes de que el video esté listo.
 * ============================================================================================================= */
function agregar_lazy_loading_videos() {
    // Evita que el script se ejecute en páginas que no sean "Bundles"
    if (!is_page('bundles')) return;
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        
        /**
         * Carga un video a partir de su atributo data-src.
         * @param {HTMLVideoElement} video - Elemento <video> que se va a cargar.
         */
        const loadVideo = (video) => {
            const source = video.querySelector('source');
            if (!source) {
                console.error("No se encontró <source> en el video:", video);
                return;
            }

            const src = source.getAttribute('data-src');
            if (!src) {
                console.error("El <source> no tiene data-src:", source);
                return;
            }

            // Pasar data-src → src para iniciar la descarga real del video
            source.src = src;

            // Recargar el elemento <video> para que detecte la nueva fuente
            video.load();

            // Cuando el navegador tenga suficientes datos para mostrar un frame,
            // se inicia la reproducción y el póster desaparece automáticamente.
            video.addEventListener('loadeddata', () => {
                video.play().catch(err => {
                    console.warn("Autoplay bloqueado por el navegador:", err);
                });
            }, { once: true });          
        };

        /**
         * IntersectionObserver:
         * Observa los videos y dispara loadVideo() cuando entren en el área visible
         * o estén a menos de 200px del viewport.
         */
        const observer = new IntersectionObserver((entries, obs) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    loadVideo(entry.target); // Cargar el video
                    obs.unobserve(entry.target); // Dejar de observar este video (ya no hace falta)
                }
            });
        }, {
            rootMargin: '200px', // Empieza a cargar antes de que sea visible
            threshold: 0.25      // Al menos 25% del video visible para activarlo
        });

        /**
         * Busca todos los videos con la clase .lazy-video y los observa.
         */
        const observarVideos = () => {
            const videos = document.querySelectorAll('.lazy-video');            
            videos.forEach(video => observer.observe(video));
        };

        // Observar los videos que ya estén en el DOM al cargar la página
        observarVideos();

        /**
         * MutationObserver:
         * Detecta si se añaden videos nuevos al DOM de forma dinámica
         * y los añade al IntersectionObserver.
         */
        const mutationObserver = new MutationObserver(mutations => {
            mutations.forEach(mutation => {
                mutation.addedNodes.forEach(node => {
                    // Solo trabajamos con nodos tipo ELEMENTO
                    if (node.nodeType === 1) {
                        if (node.matches('.lazy-video')) {
                            observer.observe(node);
                        } else {
                            node.querySelectorAll &&
                            node.querySelectorAll('.lazy-video').forEach(v => observer.observe(v));
                        }
                    }
                });
            });
        });

        // Observar cambios en todo el body (nodos añadidos en cualquier parte)
        mutationObserver.observe(document.body, {
            childList: true,  // Observar hijos directos añadidos/eliminados
            subtree: true,    // Observar también nodos dentro de nodos
        });
    });
    </script>
    <?php
}

/* ==========================================================================================
 * Función para agregar animaciones CSS al Sidebar y los Bundles (Productos)
 * Esta función se usa para aplicar las siguientes animaciones:
 * 1. Animación del Sidebar al aparecer (desliza desde la izquierda con un rebote suave).
 * 2. Animación de los Bundles (productos) con desplazamiento horizontal y rebotes sucesivos.
 * 3. Efecto hover en los productos (eleva ligeramente y agrega sombra).
 * 4. Adaptación responsive para pantallas pequeñas (sin animaciones en móviles).
 * ========================================================================================== */
function animacion_bundles_uno() {
    ?>
    <style>
        /* ====================== 
           Animación del Sidebar 
           ====================== */
        .mc-bundles-filter {
            position: sticky; /* El sidebar permanece fijo al hacer scroll */
            top: 130px; /* Separación desde la parte superior */
            opacity: 0; /* Inicialmente invisible */
            transform: translateX(-150px); /* Comienza desplazado hacia la izquierda fuera de la pantalla */
            animation: slideInBounce 0.8s forwards; /* Animación de deslizamiento con rebote */
            z-index: 9999; /* Asegura que el sidebar se muestre por encima de otros elementos */
        }

        /* Keyframes de la animación del Sidebar */
        @keyframes slideInBounce {
            0% { opacity: 0; transform: translateX(-150px); } /* Comienza fuera de pantalla */
            60% { opacity: 1; transform: translateX(30px); }  /* Rebote hacia la derecha */
            80% { transform: translateX(-10px); }             /* Rebote menor hacia la izquierda */
            100% { opacity: 1; transform: translateX(0); }    /* Posición final estable */
        }

        /* ====================== 
           Animación de los Bundles (Productos) 
           ====================== */
        .mc-bundles-results .bundles-grid .bundle-container {
            transform: translateX(150px); /* Inicia desplazado a la derecha fuera de la pantalla */
            animation: productsBounce 1s forwards; /* Animación de rebotes horizontales */
        }

        /* Keyframes de la animación de los Bundles */
        @keyframes productsBounce {
            0% { transform: translateX(150px); }  /* Comienza fuera de pantalla hacia la derecha */
            50% { transform: translateX(-20px); } /* Primer rebote grande hacia la izquierda */
            65% { transform: translateX(10px); }  /* Rebote menor hacia la derecha */
            80% { transform: translateX(-5px); }  /* Último rebote pequeño hacia la izquierda */
            90% { transform: translateX(2px); }   /* Ajuste final hacia la derecha */
            100% { transform: translateX(0); }    /* Posición final estable */
        }

        /* ====================== 
           Responsive: adaptaciones para pantallas pequeñas 
           ====================== */
        @media (max-width: 980px) {
            /* Desactiva las animaciones en pantallas pequeñas (móviles y tabletas) */
            .mc-bundles-filter {
                transform: none !important; /* El sidebar no tiene desplazamiento */
                opacity: 1 !important; /* El sidebar es completamente visible */
                animation: none !important; /* Sin animación en dispositivos pequeños */
            }

            .mc-bundles-results .bundles-grid .bundle-container {
                transform: none !important; /* Los productos no tienen desplazamiento inicial */
                animation: none !important; /* Sin animación en dispositivos pequeños */
            }
        }

        /* ====================== 
           Efectos de Hover en los productos 
           ====================== */
        .mc-bundles-results .bundles-grid .bundle-container:hover {
            transform: translateY(-5px); /* Levanta ligeramente el producto */
            box-shadow: 0 8px 20px rgba(255, 215, 0, 0.25); /* Agrega una sombra de elevación */
        }
    </style>
    <?php
}

/* ===========================================================================================
 * Función para agregar animaciones CSS al Sidebar y los Bundles (Productos):
 * 1. Animación del Sidebar al aparecer, deslizando desde la izquierda con un rebote suave.
 * 2. Animación de los Bundles (productos) en la galería, desplazándose desde la derecha con 
 * rebotes sucesivos.
 * 3. Retrasos escalonados en cada bundle para crear un efecto cascada al cargar los productos.
 * 4. Efecto hover en los productos (eleva ligeramente y agrega sombra dorada).
 * 5. Adaptación responsive para pantallas pequeñas (desactiva animaciones y posiciona 
 * elementos de forma estática).
 * ============================================================================================ */
function animacion_bundles_dos() { ?>
    <style>
    /* ======================
       Sidebar animado
       ====================== */
    .mc-bundles-wrap .mc-bundles-filter {
        position: sticky;
        top: 130px;
        align-self: start;
        height: calc(100vh - 140px);
        overflow-y: auto;
        opacity: 0;
        transform: translateX(-150px);
        animation: slideInBounceSidebar 0.8s forwards;
    }

    /* ======================
       Keyframes del sidebar
       ====================== */
    @keyframes slideInBounceSidebar {
        0% { opacity: 0; transform: translateX(-150px); }
        60% { opacity: 1; transform: translateX(30px); }
        80% { transform: translateX(-10px); }
        100% { opacity: 1; transform: translateX(0); }
    }

    /* ======================
       Animación para los bundles en la galería
       ====================== */
    .mc-bundles-wrap .mc-bundles-results .bundles-grid .bundle-container {
        transform: translateX(150px);
        animation: slideInBounceBundle 1s forwards cubic-bezier(0.68, -0.55, 0.27, 1.55);
    }

    /* ======================
       Keyframes de rebote para los bundles
       ====================== */
    @keyframes slideInBounceBundle {
        0%   { transform: translateX(150px); }
        50%  { transform: translateX(-20px); }
        65%  { transform: translateX(10px); }
        80%  { transform: translateX(-5px); }
        90%  { transform: translateX(2px); }
        100% { transform: translateX(0); }
    }

    /* ======================
       Retraso escalonado para efecto cascada
       ====================== */
    <?php
    for ($i = 0; $i < 20; $i++) {
        echo ".mc-bundles-wrap .mc-bundles-results .bundles-grid .bundle-container:nth-child(".($i+1).") { animation-delay: ".($i*0.1)."s; }\n";
    }
    ?>

    /* ======================
       Hover sobre los bundles
       ====================== */
    .mc-bundles-wrap .mc-bundles-results .bundles-grid .bundle-container:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 20px rgba(255,215,0,0.25);
    }

    /* ======================
       Layout responsive para dispositivos pequeños
       ====================== */
    @media (max-width: 980px) {
        .mc-bundles-wrap .mc-bundles-results .bundles-grid {
            grid-template-columns: 1fr;
            animation: none !important;
            transform: none !important;
        }

        .mc-bundles-wrap .mc-bundles-filter {
            position: static;
            max-height: none;
            transform: none !important;
            opacity: 1 !important;
            animation: none !important;
        }
    }
    </style>
<?php }

/* ==============================================================================================
 * Función que anima los Bundles al cargarse
 * Propósitos:
 *    - Sidebar: se desliza desde la izquierda con rebote suave.
 *    - Bundles (productos): caen desde arriba con rebotes verticales y desplazamiento horizontal.
 *    - Cada bundle aparece escalonadamente para efecto cascada.
 *    - Hover: eleva ligeramente y agrega sombra dorada.
 *    - Adaptación responsive para móviles sin animaciones.
 * ============================================================================================== */
function animacion_bundles_tres() { ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.querySelector('.mc-bundles-wrap .mc-bundles-filter');

        if (sidebar && !sidebar.querySelector('.sidebar-inner')) {
            sidebar.style.opacity = '0';
            sidebar.style.transform = 'translateX(-150px)';

            const wrapper = document.createElement('div');
            wrapper.className = 'sidebar-inner';

            while (sidebar.firstChild) {
                wrapper.appendChild(sidebar.firstChild);
            }
            sidebar.appendChild(wrapper);

            requestAnimationFrame(() => {
                sidebar.style.transition = 'transform 0.9s cubic-bezier(0.68, -0.55, 0.27, 1.55), opacity 0.9s';
                sidebar.style.transform = 'translateX(0)';
                sidebar.style.opacity = '1';
            });
        }
    });
    </script>

    <style>
    /* ------------------ Sidebar Bundles ------------------ */
    .mc-bundles-wrap .mc-bundles-filter {
        position: sticky;
        top: 130px;
        align-self: flex-start;
        height: calc(100vh - 140px);
        overflow-y: auto;
        max-height: calc(100vh - 140px);
        opacity: 0;
        transform: translateX(-150px);
        z-index: 9999;
    }

    /* ------------------ Animación de los bundles ------------------ */
    .mc-bundles-wrap .mc-bundles-results .bundles-grid .bundle-container {
        transform: translateX(50px) translateY(-100px);
        animation: bundleDropBounce 1.2s forwards cubic-bezier(0.68, -0.55, 0.27, 1.55);        
    }

    @keyframes bundleDropBounce {
        0%   { transform: translateX(50px) translateY(-100px); }
        40%  { transform: translateX(-20px) translateY(20px); }
        55%  { transform: translateX(10px) translateY(-10px); }
        70%  { transform: translateX(-5px) translateY(5px); }
        85%  { transform: translateX(2px) translateY(-2px); }
        100% { transform: translateX(0) translateY(0); }
    }

    /* ------------------ Retraso escalonado de los bundles ------------------ */
    <?php
    for ($i = 0; $i < 20; $i++) {
        echo ".mc-bundles-wrap .mc-bundles-results .bundles-grid .bundle-container:nth-child(".($i+1).") { animation-delay: ".($i*0.1)."s; }\n";
    }
    ?>

    /* ------------------ Hover sobre los bundles ------------------ */
    .mc-bundles-wrap .mc-bundles-results .bundles-grid .bundle-container:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 20px rgba(255,215,0,0.25);
    }

    /* ------------------ Layout responsive ------------------ */
    @media (max-width: 900px) {
        .mc-bundles-wrap .mc-bundles-results .bundles-grid {
            grid-template-columns: 1fr;
            animation: none !important;
            transform: none !important;
        }

        .mc-bundles-wrap .mc-bundles-filter {
            position: static;
            max-height: none;
            transform: none !important;
            opacity: 1 !important;
            transition: none !important;
        }
    }
    </style>
<?php }

/* =====================================================================================
 * Función que anima los Bundles al cargarse (versión suave)
 * Propósitos:
 *    - Sidebar: se desliza desde la izquierda con rebote suave y transición más fluida.
 *    - Bundles: rebote vertical y horizontal más suave, simulando caída con rebote.
 *    - Cada bundle aparece escalonadamente para efecto cascada más fluido.
 * ===================================================================================== */
function animacion_bundles_cuatro() { ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.querySelector('.mc-bundles-wrap .mc-bundles-filter');

    if (sidebar && !sidebar.querySelector('.sidebar-inner')) {
        sidebar.style.opacity = '0';
        sidebar.style.transform = 'translateX(-150px)';

        const wrapper = document.createElement('div');
        wrapper.className = 'sidebar-inner';

        while (sidebar.firstChild) {
            wrapper.appendChild(sidebar.firstChild);
        }
        sidebar.appendChild(wrapper);

        requestAnimationFrame(() => {
            sidebar.style.transition = 'transform 1s cubic-bezier(0.25, 0.46, 0.45, 0.94), opacity 1s';
            sidebar.style.transform = 'translateX(0)';
            sidebar.style.opacity = '1';
        });
    }
});
</script>

<style>
/* ------------------ Sidebar Bundles ------------------ */
.mc-bundles-wrap .mc-bundles-filter {
    position: sticky;
    top: 130px;
    align-self: flex-start;
    height: calc(100vh - 140px);
    overflow-y: auto;
    max-height: calc(100vh - 140px);
    z-index: 9999;

    opacity: 0;
    transform: translateX(-150px);
}

/* ------------------ Animación de los bundles ------------------ */
.mc-bundles-wrap .mc-bundles-results .bundles-grid .bundle-container {
    transform: translateX(50px) translateY(-100px);
    animation: bundleDropBounceSmooth 1.4s forwards cubic-bezier(0.25, 0.46, 0.45, 0.94);
}

@keyframes bundleDropBounceSmooth {
    0%   { transform: translateX(50px) translateY(-100px); }
    30%  { transform: translateX(-20px) translateY(25px); }
    50%  { transform: translateX(10px) translateY(-15px); }
    65%  { transform: translateX(-5px) translateY(7px); }
    80%  { transform: translateX(2px) translateY(-3px); }
    90%  { transform: translateX(-1px) translateY(1px); }
    100% { transform: translateX(0) translateY(0); }
}

/* ------------------ Retraso escalonado ------------------ */
<?php
for ($i = 0; $i < 20; $i++) {
    echo ".mc-bundles-wrap .mc-bundles-results .bundles-grid .bundle-container:nth-child(".($i+1).") { animation-delay: ".($i*0.07)."s; }\n";
}
?>

/* ------------------ Hover sobre los bundles ------------------ */
.mc-bundles-wrap .mc-bundles-results .bundles-grid .bundle-container:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(255,215,0,0.25);
}

/* ------------------ Layout responsive ------------------ */
@media (max-width: 900px) {
    .mc-bundles-wrap .mc-bundles-results .bundles-grid {
        grid-template-columns: 1fr;
        animation: none !important;
        transform: none !important;
    }

    .mc-bundles-wrap .mc-bundles-filter {
        position: static;
        max-height: none;
        transform: none !important;
        opacity: 1 !important;
        transition: none !important;
    }
}
</style>
<?php }

/* =====================================================================================
 * Función que anima los Bundles al cargarse con efecto cinemático
 * Propósitos:
 *   - Sidebar: se desliza suavemente desde la izquierda
 *   - Bundles: caída tipo pelota real, rebotes progresivamente más pequeños
 *   - Delay escalonado para efecto cascada
 * ===================================================================================== */
function animacion_bundles_cinco() { ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.querySelector('.mc-bundles-wrap .mc-bundles-filter');

    if (sidebar && !sidebar.querySelector('.sidebar-inner')) {
        sidebar.style.opacity = '0';
        sidebar.style.transform = 'translateX(-150px)';

        const wrapper = document.createElement('div');
        wrapper.className = 'sidebar-inner';
        while (sidebar.firstChild) wrapper.appendChild(sidebar.firstChild);
        sidebar.appendChild(wrapper);

        requestAnimationFrame(() => {
            sidebar.style.transition = 'transform 0.9s cubic-bezier(0.68, -0.55, 0.27, 1.55), opacity 0.9s';
            sidebar.style.transform = 'translateX(0)';
            sidebar.style.opacity = '1';
        });
    }
});
</script>

<style>
/* ------------------ Sidebar Bundles ------------------ */
.mc-bundles-wrap .mc-bundles-filter {
    position: sticky;
    top: 130px;
    align-self: flex-start;
    height: calc(100vh - 140px);
    overflow-y: auto;
    max-height: calc(100vh - 140px);
    z-index: 9999;
    opacity: 0;
    transform: translateX(-150px);
}

/* ------------------ Bundles: caída cinemática ------------------ */
.mc-bundles-wrap .mc-bundles-results .bundles-grid .bundle-container {
    transform: translateY(-200px);
    animation: bundleBounceCinematic 1.6s forwards cubic-bezier(0.25, 0.46, 0.45, 0.94);    
}

@keyframes bundleBounceCinematic {
    0%   { transform: translateY(-200px); }
    30%  { transform: translateY(0); }
    45%  { transform: translateY(-60px); }
    60%  { transform: translateY(0); }
    70%  { transform: translateY(-30px); }
    80%  { transform: translateY(0); }
    88%  { transform: translateY(-15px); }
    95%  { transform: translateY(0); }
    100% { transform: translateY(0); }
}

/* ------------------ Retardo escalonado para cascada ------------------ */
<?php
for ($i = 0; $i < 20; $i++) {
    echo ".mc-bundles-wrap .mc-bundles-results .bundles-grid .bundle-container:nth-child(".($i+1).") { animation-delay: ".($i*0.12)."s; }\n";
}
?>

/* ------------------ Hover suave ------------------ */
.mc-bundles-wrap .mc-bundles-results .bundles-grid .bundle-container:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(255,215,0,0.25);
}

/* ------------------ Layout responsive ------------------ */
@media (max-width: 900px) {
    .mc-bundles-wrap .mc-bundles-results .bundles-grid {
        grid-template-columns: 1fr;
        animation: none !important;
        transform: none !important;
    }

    .mc-bundles-wrap .mc-bundles-filter {
        position: static;
        max-height: none;
        transform: none !important;
        opacity: 1 !important;
        transition: none !important;
    }
}
</style>
<?php
}

// [[[[wp_footer]]]]
add_action('wp_footer', 'actualizar_boton_view_to_add_cart_bundles');

// ===============================================================================================================================
// Función que se ejecuta al final de la página, sólo en la página de "bundles".
// Esta función gestiona los botones "Añadir al carrito" y "Ver carrito" para los productos en la página de bundles.
// Cuando un producto es eliminado del sidecart, el botón "Añadir al carrito" se reestablece y el botón "Ver carrito" se oculta.
// Si el usuario agrega el producto nuevamente al carrito, el botón "Añadir al carrito" se oculta y el botón "Ver carrito" aparece.
// =============================================================================================================================== 
function actualizar_boton_view_to_add_cart_bundles() {
    if (!is_page('bundles')) return; // Ejecutar solo en la página de bundles
    ?>
    <script>
    jQuery(function($) {
          
         /* 1. Cuando se elimina un producto desde el sidecart:
         *    - Muestra el botón 'Añadir al carrito' y oculta el botón 'Ver carrito' solo para ese producto específico.
         *    - Detecta el slug del producto, realiza una solicitud AJAX para obtener el ID del producto y luego busca el producto en la página de bundles.
         *    - Si el ID coincide, inserta el botón "Añadir al carrito" y oculta el botón "Ver carrito". */         				
        $(document).on('click', '.xoo-wsc-smr-del', function() {
            // Busca el contenedor del producto eliminado en el sidecart
            const $productoSidecart = $(this).closest('.xoo-wsc-product');
            const href = $productoSidecart.find('a').attr('href');
            if (!href) return;

            // Extrae el slug del producto desde la URL
            let slug = href.split('/product/')[1];
            if (!slug) return;
            slug = slug.replace(/\/$/, '');

            // Hace una petición AJAX para obtener el ID del producto a partir del slug
            $.post('<?php echo admin_url('admin-ajax.php'); ?>', {
                action: 'obtener_id_desde_slug',  // Este es el AJAX que debes crear para obtener el ID desde el slug
                slug: slug
            }, function(response) {
                if (!response.success) return;

                // ID del producto eliminado
                const eliminadoID = response.data.id.toString();

                // Recorre todos los productos visibles en la página de bundles
                $('.bundle-container').each(function() {
                    const $contenedor = $(this);
                    const idProducto = $contenedor.data('id');

                    // Si el ID coincide, actualiza el botón
                    if (idProducto && idProducto.toString() === eliminadoID) {
                        // Muestra el botón "Añadir al carrito"
                        var $btnAdd = $('<a/>', {
                            'href': $contenedor.data('add-to-cart-url'),
                            'class': 'button add_to_cart_button ajax_add_to_cart product_type_' + $contenedor.data('type'),
                            'data-product_id': idProducto,  // Usar el ID correcto
                            'data-quantity': 1,
                            'aria-label': $contenedor.data('aria-label'),
                            text: $contenedor.data('add-to-cart-text')
                        });
                        // Añadir el botón "Añadir al carrito" dentro de .bundle-info
                        $contenedor.find('.bundle-info').append($btnAdd);
						
                        // Oculta el botón "Ver carrito"
                        const btnView = $contenedor.find('a.added_to_cart.wc-forward');
                        if (btnView.length) {
                            btnView.hide();
                        }

                        // Sale del ciclo una vez que se encuentra y actualiza el producto correspondiente
                        return false; // Termina el ciclo una vez que se actualizó el producto
                    }
                });
            });
        });

        // 2. Cuando se añade un producto al carrito desde el bundle, actualizar el botón
        $(document.body).on('added_to_cart', function(event, fragments, cart_hash, $button) {
            // Verifica que el botón y su producto estén correctamente definidos
            if (!$button || !$button.length) return;

            const pid = $button.data('product_id');
            if (!pid) return;

            // Busca el contenedor del producto en los bundles
            $('.bundle-container').each(function() {
                const $contenedor = $(this);
                const idProducto = $contenedor.data('id');

                // Si el ID coincide, oculta el botón "Añadir al carrito" y muestra "Ver carrito"
                if (idProducto && idProducto.toString() === pid.toString()) {
                    // Oculta el botón "Añadir al carrito"
                    $contenedor.find('a.add_to_cart_button').hide();

                    // Elimina cualquier botón "Ver carrito" duplicado
                    $contenedor.find('a.added_to_cart.wc-forward').remove();

                    // Crea un único botón "Ver carrito" y lo agrega
                    const verCarrito = $('<a>', {
                        href: wc_cart_url,
                        class: 'added_to_cart wc-forward',
                        'data-product_id': idProducto,
                        text: 'View Cart'
                    });

                    // Agregar el botón "Ver carrito" después de "Añadir al carrito"
                    $contenedor.find('.bundle-info').append(verCarrito);
                }
            });
        });

    });
    </script>
    <?php
}

// [[[[wp_footer]]]]
add_action('wp_footer', 'mostrar_botones_bundles_correctos', 99);

// =========================================================================================
// Función que se ejecuta al final de la página, sólo en la página de "bundles".
// Esta función gestiona la visualización de los botones "Añadir al carrito" y "Ver carrito" 
// para los productos en la página de bundles.
// - Limpia el botón "View Cart" inicial si el producto no está en el carrito.
// - Muestra el botón "View Cart" cuando un producto es añadido al carrito.
// =========================================================================================
function mostrar_botones_bundles_correctos() {
    // Verifica que estamos en la página de bundles y que WooCommerce está cargado
    if (!function_exists('is_page') || !is_page('bundles')) return;
    if (!function_exists('wc_get_cart_url') || !function_exists('WC')) return;

    // Array para almacenar los IDs de los productos que están en el carrito
    $ids_en_carrito = [];
    foreach (WC()->cart->get_cart() as $item) {
        $ids_en_carrito[] = (int) $item['product_id']; // Almacena el ID del producto
    }

    // Obtiene la URL del carrito de compras
    $url_carrito = wc_get_cart_url();
    ?>
    <script>
    (function($){
        // Asigna las variables JS con los datos obtenidos desde PHP
        var urlCarrito = <?php echo wp_json_encode(esc_url($url_carrito)); ?>;
        var idsEnCarrito = <?php echo wp_json_encode($ids_en_carrito); ?>;
        var idsAntesDeActualizar = [];

        // Función que limpia el botón "View Cart" inicial si el producto no está en el carrito
        function limpiarViewCartInicial() {
            $('.bundle-container').each(function() {
                var $contenedor = $(this); // Contenedor del producto bundle
                var idProducto = $contenedor.data('id'); // ID del producto del bundle
                if (!idProducto) return;

                // Si el producto no está en el carrito, elimina el botón "View Cart"
                if (idsEnCarrito.indexOf(parseInt(idProducto)) === -1) {
                    $contenedor.find('a.added_to_cart.wc-forward').remove();
                }
            });
        }

        // Función que muestra el botón "View Cart" cuando un producto es añadido al carrito
        function mostrarViewCart($contenedor) {
            if (!$contenedor || !$contenedor.length) return;
            var idProducto = $contenedor.data('id');

            // Elimina el botón "Añadir al carrito" antes de agregar el botón "View Cart"
            $contenedor.find('a.add_to_cart_button').remove();

            // Si ya existe el botón "View Cart", no hace nada
            if ($contenedor.find('a.added_to_cart.wc-forward').length) return;

            // Crea el botón "View Cart"
            var $botonViewCart = $('<a/>', {
                href: urlCarrito, // URL del carrito
                'class': 'added_to_cart wc-forward',
                'aria-label': 'View Cart', // Etiqueta de accesibilidad
                text: 'View Cart' // Texto del botón
            });

            // Si el ID del producto existe, lo asigna como atributo 'data-id'
            if (idProducto) $botonViewCart.attr('data-id', idProducto);

            // Añade el botón "View Cart" dentro de la sección .bundle-info
            $contenedor.find('.bundle-info').append($botonViewCart);
        }

        // Cuando la página se carga, limpia los botones "View Cart" no deseados
        $(document).ready(function() {
            limpiarViewCartInicial();
        });

        // Cuando se añade un producto al carrito, muestra el botón "View Cart"
        $(document.body).on('added_to_cart', function(event, fragments, cart_hash, $boton) {
            var $contenedor = $boton ? $boton.closest('.bundle-container') : null;
            if ($contenedor && $contenedor.length) {
                mostrarViewCart($contenedor); // Llama a la función para mostrar "View Cart"
            }
        });

    })(jQuery);
    </script>
    <?php
}
		
// [[[[wp_footer]]]]
add_action('wp_footer', 'rangoprecios_bundles');

// =======================================================
// Función que imprime el JS del slider de rango de precio
// - Dos thumbs: mínimo y máximo
// - Actualiza barra de rango, etiquetas y inputs ocultos
// - Evita que los thumbs se crucen
// - Se adapta al tamaño de la ventana
// =======================================================
function rangoprecios_bundles() {
	if (!is_page('bundles')) return;
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', ()=>{

        const slider = document.querySelector('.price-slider-container');
        if(!slider) return;

        const minThumb = slider.querySelector('.min-thumb');
        const maxThumb = slider.querySelector('.max-thumb');
        const rangeBar = slider.querySelector('.slider-range');
        const minInput = slider.querySelector('#min_price_input');
        const maxInput = slider.querySelector('#max_price_input');
        const minLabel = slider.querySelector('.min-label');
        const maxLabel = slider.querySelector('.max-label');

        const absoluteMin = parseFloat(document.querySelector('#absolute_min_price').value);
        const absoluteMax = parseFloat(document.querySelector('#absolute_max_price').value);

        let currentMin = parseFloat(minInput.value);
        let currentMax = parseFloat(maxInput.value);
        const minGap = 1;

        function updateUI(){
            const minPercent = ((currentMin - absoluteMin) / (absoluteMax - absoluteMin)) * 100;
            const maxPercent = ((currentMax - absoluteMin) / (absoluteMax - absoluteMin)) * 100;

            minThumb.style.left = minPercent + '%';
            maxThumb.style.left = maxPercent + '%';
            rangeBar.style.left = minPercent + '%';
            rangeBar.style.width = (maxPercent - minPercent) + '%';

            minLabel.style.left = minPercent + '%';
            minLabel.textContent = Math.round(currentMin);
            maxLabel.style.left = maxPercent + '%';
            maxLabel.textContent = Math.round(currentMax);

            minInput.value = Math.round(currentMin);
            maxInput.value = Math.round(currentMax);
        }

        function dragThumb(thumb,onMove){
            let dragging = false;
            thumb.addEventListener('mousedown',e=>{ dragging=true; e.preventDefault(); });
            document.addEventListener('mousemove', e=>{ 
                if(!dragging) return;
                let rect = slider.getBoundingClientRect();
                let percent = (e.clientX - rect.left) / rect.width;
                percent = Math.max(0, Math.min(1, percent));
                onMove(percent);
                updateUI();
            });
            document.addEventListener('mouseup', ()=>{ dragging=false; });
        }

        dragThumb(minThumb, percent=>{
            let newMin = absoluteMin + percent * (absoluteMax - absoluteMin);
            if (newMin > currentMax - minGap) newMin = currentMax - minGap;
            currentMin = Math.max(absoluteMin, newMin);
        });

        dragThumb(maxThumb, percent=>{
            let newMax = absoluteMin + percent * (absoluteMax - absoluteMin);
            if (newMax < currentMin + minGap) newMax = currentMin + minGap;
            currentMax = Math.min(absoluteMax, newMax);
        });

        updateUI();
        window.addEventListener('resize', updateUI);

    });
    </script>
    <?php
}
	
// [[[[wp_footer]]]]
// Enganchamos la función al footer para que el script se imprima al final de la página
add_action('wp_footer', 'cambiar_boton_bundle_al_carrito');

/* ================================================================================
 * Función que añade un script en el footer que cambia el botón "Añadir al carrito"
 * por un botón "Ver carrito" tras agregar un bundle al carrito vía AJAX,
 * pero solo en la página 'bundles'.
 ================================================================================== */
function cambiar_boton_bundle_al_carrito() {
    // Solo cargar el script si estamos en la página 'bundles'
    if (is_page('bundles')) {
        // Obtenemos URL segura de la página del carrito
        $cart_url = esc_url(wc_get_cart_url());
        ?>
        <script>
        jQuery(function($){
            // Escuchar el evento cuando se agrega un producto al carrito vía AJAX
            $(document.body).on('added_to_cart', function(event, fragments, cart_hash, $button){
                if ($button && $button.length) {
                    // Reemplazar el botón "Añadir al carrito" por un enlace "Ver carrito"
                    $button.replaceWith(
                        '<a href="<?php echo $cart_url; ?>" class="button ver-carrito-button">Ver carrito</a>'
                    );
                }
            });
        });
        </script>
        <?php
    }
}

// [[[[wp_footer]]]]
add_action('wp_footer', 'agregar_resaltado_bundle_al_footer');

// =====================================================================================================================================
// Función que agrega un script en el footer para resaltar visualmente el bundle correspondiente cuando se visita /bundles/#bundle-{ID}.
// Esto sólo se ejecuta en la página 'bundles' para evitar cargar código innecesario en otras páginas.
// =====================================================================================================================================
function agregar_resaltado_bundle_al_footer() {
    if (!is_page('bundles')) {
        return; // Solo ejecuta si estamos en la página bundles
    }
    ?>
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        const hash = window.location.hash;

        // Verifica que el hash empiece con "#bundle-" para aplicar el resaltado solo al bundle correcto
        if (hash && hash.startsWith("#bundle-")) {
            const target = document.querySelector(hash);

            if (target) {
                // Aplica una animación visual para llamar la atención al bundle correspondiente
                target.style.transition = "box-shadow 0.5s ease, transform 0.5s ease";
                target.style.boxShadow = "0 0 20px 5px gold";
                target.style.transform = "scale(1.02)";

                // Después de 3 segundos, remueve el efecto para volver a su estado original
                setTimeout(() => {
                    target.style.boxShadow = "";
                    target.style.transform = "";
                }, 3000);
            }
        }
    });
    </script>
    <?php
}

// [[[[wp_footer]]]]
add_action('wp_footer', 'bloquear_interfaz_bundles');

// =============================================================================
// Función que muestra un overlay de bloqueo de pantalla en la página de bundles
// - Se activa al hacer click en los botones de filtro:
//     - "Apply Filters"
//     - "Reset Filters"
// - También se activa al recargar la página (beforeunload)
// - Añade la clase "loading-active" al <body> y muestra un overlay
// =============================================================================
function bloquear_interfaz_bundles() {
    if (!is_page('bundles')) return; // solo en la página bundles
    ?>
    <script>
    (function(){        
        // Crear overlay
        const blocker = document.createElement('div');
        blocker.className = 'screen-blocker';
        document.body.appendChild(blocker);

        function activarBloqueo() {
            document.body.classList.add('loading-active');
        }

        // Selector de botones
        const buttons = document.querySelectorAll('.mc-bundles-filter button[type="submit"], .mc-bundles-filter a.button');
        buttons.forEach(btn => btn.addEventListener('click', activarBloqueo));

        // En caso de recarga directa
        window.addEventListener('beforeunload', activarBloqueo);

    })();
    </script>
    <?php
}

// [[[[wp_footer]]]]
add_action('wp_footer', 'fix_videos_chrome_bundles_universal');

/* ============================================================================================================
 * Función: fix_videos_chrome_bundles_universal()
 * ------------------------------------------------------------------------------------------------------------
 * OBJETIVO:
 * - Corrige el bug visual de Chrome/Opera donde los <video> desaparecen (se ponen en negro)
 *   justo antes de salir de la página /bundles/ o cuando se aplica un filtro Husky (AJAX, URL interna, etc).
 * - Captura un frame estático del video (snapshot) y lo mantiene visible mientras el navegador redirige.
 * - De esta forma, el contenido se mantiene visible sin parpadeos ni glitches.
 *
 * CONTEXTO:
 * - Este bug ocurre principalmente en Chrome (y derivados) debido a cómo el motor de render libera
 *   la textura de video en GPU al iniciar una nueva navegación, dejando un cuadro negro temporal.
 * - En Firefox y Safari no ocurre, por eso el script los ignora completamente.
 *
 * ALCANCE:
 * - Solo se ejecuta en la página “bundles” (verificado por is_page('bundles')).
 * - Afecta cualquier <video> presente en la página.
 * - No interfiere con los botones “Add to cart” o “View cart” (se excluyen explícitamente).
 *
 * MECÁNICA GENERAL:
 * 1. Detecta si el navegador es Chrome u Opera (usa userAgent).
 * 2. Cuando el usuario hace click en un enlace interno, envía un formulario o cambia de dirección:
 *    → Captura todos los <video> visibles y crea un <canvas> o <img> (fallback) con la última imagen visible.
 * 3. Inserta ese snapshot dentro del mismo contenedor que el video, perfectamente alineado.
 * 4. Así, mientras Chrome destruye el contexto de video, el usuario sigue viendo el último frame.
 * 5. Luego la navegación continúa con una leve demora (~80 ms).
 *
 * SEGURIDAD / RENDIMIENTO:
 * - Usa try/catch para evitar errores en drawImage.
 * - No modifica la estructura del DOM más allá del overlay temporal.
 * - Cada snapshot se elimina automáticamente al recargar la nueva página.
 * ============================================================================================================ */
function fix_videos_chrome_bundles_universal() {
	if (!is_page('bundles')) return;
	?>
	<script>
	(function(){
		const ua = navigator.userAgent;
		const isChrome = /Chrome/.test(ua) && /Google Inc/.test(navigator.vendor);
		const isOpera = /OPR\//.test(ua);
		if (!(isChrome || isOpera)) return;

		function snapshotVideo(video) {
    		try {
				// Ignorar los videos del menú principal
				if (video.classList.contains('zaj-card-video')) return;
				
				// rect del video relativo al viewport
				const rect = video.getBoundingClientRect();

				// contenedor directo donde insertaremos el canvas
				const parent = video.parentElement;
				if (!parent) return;

				// forzamos que el padre sea relativo para que el canvas absolute se posicione dentro
				const parentStyle = getComputedStyle(parent);
				if (parentStyle.position === 'static') {
					parent.style.position = 'relative';
				}
				// asegurar overflow visible para que el canvas no se recorte
				if (parentStyle.overflow !== 'visible') {
					parent.style.overflow = 'visible';
				}

				// rect del padre (viewport)
				const parentRect = parent.getBoundingClientRect();

				// posición del video **dentro** del padre
				const topInsideParent = rect.top - parentRect.top;
				const leftInsideParent = rect.left - parentRect.left;

				// tamaño en px (float -> integer para evitar subpixel glitches)
				const w = Math.max(1, Math.round(rect.width));
				const h = Math.max(1, Math.round(rect.height));

				// eliminar snapshot previo si existe
				const previous = parent.querySelector('.snapshot-overlay');
				if (previous) previous.remove();

				// crear canvas
				const canvas = document.createElement('canvas');
				canvas.className = 'snapshot-overlay';
				canvas.width = Math.max(1, w);   // tamaño lógico del canvas
				canvas.height = Math.max(1, h);
				canvas.style.width = w + 'px';   // tamaño CSS
				canvas.style.height = h + 'px';
				canvas.style.position = 'absolute';
				canvas.style.top = leftInsideParent === leftInsideParent ? topInsideParent + 'px' : topInsideParent + 'px'; // seguro
				canvas.style.left = leftInsideParent + 'px';
				canvas.style.zIndex = '9999';
				canvas.style.pointerEvents = 'none';
				canvas.style.transform = 'none';
				canvas.style.willChange = 'transform, opacity';
				canvas.style.borderRadius = getComputedStyle(video).borderRadius || '0';
				// objectFit for visual match if CSS uses it
				if (getComputedStyle(video).objectFit) {
					canvas.style.objectFit = getComputedStyle(video).objectFit;
				}

				// dibujar frame (puede fallar si video no tiene frame disponible)
				const ctx = canvas.getContext('2d');
				try {
					// drawImage usando el video y el tamaño CSS para que coincide visualmente
					ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
					parent.appendChild(canvas);
					return;
				} catch (drawErr) {
					// si drawImage falla, fallback al poster abajo
					canvas.remove();
					throw drawErr;
				}
			} catch (e) {
				// fallback: imagen con poster colocada exactamente igual
				const poster = video.getAttribute('poster');
				if (!poster) return;
				const parent = video.parentElement;
				if (!parent) return;

				const rect = video.getBoundingClientRect();
				const parentRect = parent.getBoundingClientRect();
				const topInsideParent = rect.top - parentRect.top;
				const leftInsideParent = rect.left - parentRect.left;
				const w = Math.max(1, Math.round(rect.width));
				const h = Math.max(1, Math.round(rect.height));

				// limpiar previos
				const previous = parent.querySelector('.snapshot-overlay');
				if (previous) previous.remove();

				const img = document.createElement('img');
				img.className = 'snapshot-overlay';
				img.src = poster;
				img.style.width = w + 'px';
				img.style.height = h + 'px';
				img.style.position = 'absolute';
				img.style.top = topInsideParent + 'px';
				img.style.left = leftInsideParent + 'px';
				img.style.zIndex = '9999';
				img.style.pointerEvents = 'none';
				img.style.borderRadius = getComputedStyle(video).borderRadius || '0';
				if (getComputedStyle(video).objectFit) {
					img.style.objectFit = getComputedStyle(video).objectFit;
				}

				const parentStyle = getComputedStyle(parent);
				if (parentStyle.position === 'static') parent.style.position = 'relative';
				if (parentStyle.overflow !== 'visible') parent.style.overflow = 'visible';

				parent.appendChild(img);
			}
		}

		function snapshotAllVideos(){
			document.querySelectorAll('video').forEach(v => {
				const rect = v.getBoundingClientRect();
				if (rect.width > 0 && rect.height > 0) snapshotVideo(v);
			});
		}

		// Enlaces internos — excluye Add to Cart / View Cart
		document.addEventListener('click', function(e){
			const link = e.target.closest && e.target.closest('a[href]');
			if (!link) return;
			if (link.classList.contains('added_to_cart') || link.classList.contains('add_to_cart_button')) return;
			const href = link.getAttribute('href');
			if (!href || href.startsWith('#') || link.hasAttribute('data-fancybox')) return;
			if (link.target === '_blank') return;
			e.preventDefault();
			snapshotAllVideos();
			setTimeout(() => window.location.href = href, 80);
		}, true);

		// Formularios
		document.addEventListener('submit', function(){
			snapshotAllVideos();
		}, true);

		// Cambios manuales de dirección
		window.addEventListener('beforeunload', snapshotAllVideos);
		window.addEventListener('pagehide', snapshotAllVideos);
	})();
	</script>
	<?php
}

// ################################################################################################################################################
// ################################################################################################################################################
// ################################################################################################################################################
// 											CARRITO DE COMPRAS
// ################################################################################################################################################
// ################################################################################################################################################
// ################################################################################################################################################

// [[[[wp_head]]]]
add_action('wp_head', 'estilo_personalizado_cart');

/* ================================================================================
 * Función que agrega estilos CSS personalizados para el body de la página de cart.  
 * ================================================================================ */ 
function estilo_personalizado_cart() {
    if (!is_page('cart') && !is_cart()) return; // solo en página del carrito (Woo o página 'cart')

    $faction = $_COOKIE['faction'] ?? 'gdi';
	
    if ($faction === 'gdi') {
        $body_bg = 'linear-gradient(270deg, #001f3f, #003f7f, #005bbb, #001f3f)'; // azul profundo eléctrico			
    } else if ($faction === 'nod') {
		// $body_bg = 'linear-gradient(135deg, rgba(10, 0, 0, 0.95) 0%, rgba(40, 0, 0, 0.9) 50%, rgba(120, 0, 0, 0.4) 100% )';
		$body_bg = 'linear-gradient(135deg, rgba(5, 0, 0, 0.97) 0%, rgba(20, 0, 0, 0.94) 40%, rgba(60, 0, 0, 0.85) 70%, rgba(90, 0, 0, 0.6) 100%)';
    }	
    ?>
    <style>
      /* ===========================
         BACKGROUND ANIMADO 
         ========================== */
      body {
        background: <?= $body_bg ?>;
        background-size: 200% 200%;
        animation: gradientShift 20s ease infinite;
        overflow-x: hidden;
        font-family: Orbitron, sans-serif;
      }
      @keyframes gradientShift {
        0% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
        100% { background-position: 0% 50%; }
      }     
    </style>

	<div class="grid-3d"></div>    	
	<!-- <div class="hud-circle"></div> -->
    <div class="custom-shop-title">CART</div>
    <div class="scan-line"></div>    

    <?php
}

// [[[[woocommerce_cart_item_name]]]]
// woocommerce_cart_item_name es un hook (filtro) de WooCommerce que permite modificar o filtrar el nombre que se muestra para cada producto en el carrito de compras.
// // Reemplaza el enlace del nombre del producto en el carrito si es un bundle
//add_filter('woocommerce_cart_item_name', 'cambiar_link_bundle_en_carrito', 10, 3);

/*
// =====================================================================================================================
// Función que cambia el enlace del nombre del producto en el carrito si el producto pertenece a la categoría 'bundles'.
// =====================================================================================================================
// El enlace apunta a la sección correspondiente del bundle en la página /bundles/#bundle-{ID}.
function cambiar_link_bundle_en_carrito($nombre_original, $cart_item, $cart_item_key) {
    $producto = $cart_item['data'];

    // Verificamos si el producto pertenece a la categoría 'bundles'
    if (has_term('bundles', 'product_cat', $producto->get_id())) {
        $bundle_id = $producto->get_id();

        // Construimos el enlace que apunta a la sección específica del bundle
        $nuevo_link = '<a href="' . site_url('/bundles/#bundle-' . $bundle_id) . '">' . esc_html($producto->get_name()) . '</a>';

        return $nuevo_link;
    }

    // Si no es bundle, devuelve el nombre original
    return $nombre_original;
}
*/

// // [[[[woocommerce_is_sold_individually]]]]
// Este filtro le dice a WooCommerce que ciertos productos solo pueden comprarse de a uno (sin selector de cantidad)
add_filter('woocommerce_is_sold_individually', 'forzar_artworks_individuales', 10, 2);

/** ==============================================================================================================
 * Función que fuerza la compra de productos como "individuales", es decir, sin permitir cambiar la cantidad.
 *
 * @param bool $individually Valor original que indica si el producto es vendido individualmente.
 * @param WC_Product $product El objeto del producto que se está procesando.
 * @return bool true si queremos que WooCommerce fuerce cantidad = 1 (sin selector), false para dejarlo como está.
 * =============================================================================================================== */
function forzar_artworks_individuales($individually, $product) {
    // OPCIÓN GLOBAL: esto aplica a TODOS los productos de la tienda.
    return true;

    /**
     * OPCIÓN FILTRADA (solo a productos específicos):
     *
     * // Verificamos si el producto pertenece a la categoría "artworks"
     * if (has_term('artworks', 'product_cat', $product->get_id())) {
     *     return true; // fuerza cantidad 1
     * }
     * return $individually; // mantiene el comportamiento original para el resto
     */
}

// [[[[woocommerce_cart_item_thumbnail]]]] 
// woocommerce_cart_item_thumbnail es un filtro de WooCommerce que permite modificar la miniatura (thumbnail) 
// que se muestra en la tabla del carrito para cada producto. 
add_filter('woocommerce_cart_item_thumbnail', 'mc_cart_thumbnail_media', 10, 3);

/* =========================================================================================== 
 * Función que reemplaza la miniatura en el carrito por:
 *  - un poster .png si el producto es un "bundle" (detecta por nombre),
 *  - o por el video ACF 'video_destacado' (sin atributos width/height, dentro de un wrapper). 
 * Esto garantiza que tanto imagen como video se ajusten exactamente con CSS.
 ============================================================================================= */
function mc_cart_thumbnail_media($thumbnail, $cart_item, $cart_item_key) {
    if ( empty($cart_item) || empty($cart_item['data']) ) {
        return $thumbnail;
    }

    $product = $cart_item['data'];
    $product_name = (string) $product->get_name();

    // 1) Si es bundle (por nombre)
    if ( stripos($product_name, 'bundle') !== false ) {
        $faction = $_COOKIE['faction'] ?? 'gdi';
        $uploads_dir = wp_upload_dir();
        
        if ($faction == 'gdi') {
            $poster_url = trailingslashit($uploads_dir['baseurl']) . 'images/poster_bundle.png';
        } else {
            $poster_url = trailingslashit($uploads_dir['baseurl']) . 'images/poster_bundle_nod.jpg';
        }

        $html = sprintf(
            '<div class="bundle-fila"><img src="%s" alt="%s" class="bundle-custom-thumb" /></div>',
            esc_url($poster_url),
            esc_attr($product_name)
        );

        return $html;
    }

    // 2) Si tiene video ACF, devolver video sin width/height (CSS manejará tamaño)
    $video_url = get_field('video_destacado', $product->get_id());
    if ( $video_url ) {
        $permalink = get_permalink($product->get_id());
        $html  = '<div class="xoo-wsc-media-wrapper">';
        $html .= '<a href="' . esc_url($permalink) . '">';
        $html .= '<video class="xoo-wsc-thumb-video" autoplay muted loop playsinline src="' . esc_url($video_url) . '" controlslist="nodownload" oncontextmenu="return false"></video>';
        $html .= '</a>';
        $html .= '</div>';

        return $html;
    }

    // 3) Si no aplica, devolver thumbnail original
    return $thumbnail;
}

// [[[[wp_footer]]]]
add_action('wp_footer', 'agregar_loading_eliminar_producto_modal');

/* ================================================================================================
 * Función que agrega un overlay con spinner y texto "Loading..." al eliminar un producto del modal
 * del carrito del plugin Xootix (sidecart).
 *  - Evita que aparezca un recuadro blanco vacío mientras Xootix procesa la eliminación.
 *  - Spinner negro centrado y overlay transparente para no bloquear la interacción del usuario.
 * ============================================================================================== */
function agregar_loading_eliminar_producto_modal() {
	
	// Solo ejecutar en shop, bundles y cart
    if(!is_shop() && !is_cart() && !is_page('bundles')) {
        return; // salir si no estamos en esas páginas
    }
	
    ?>
    <style>
    /* Overlay transparente (no oscurece el recuadro blanco), spinner negro centrado */
    .xoo-wsc-loading-overlay{
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
        background: transparent; /* si quisieras un leve tono blanco: rgba(255,255,255,0.6) */
        pointer-events: none; /* no bloquea clicks al plugin; pon 'auto' si querés bloquear */
    }
    .xoo-wsc-loading-inner{
        display:flex;
        flex-direction:column;
        align-items:center;
        gap:8px;
    }
    .xoo-wsc-spinner{
        width:28px;
        height:28px;
        border-radius:50%;
        border:3px solid rgba(0,0,0,0.15);
        border-top-color:#000; /* spinning color (negro) */
        animation: xoo-spin 0.8s linear infinite;
    }
    @keyframes xoo-spin{ to{ transform: rotate(360deg);} }
    .xoo-wsc-loading-text{
        font-size:12px;
        color:#000;
        text-transform:uppercase;
        letter-spacing:0.3px;
    }
    </style>

    <script>
    document.addEventListener('DOMContentLoaded', function(){
      document.addEventListener('click', function(e){
        // detecta clicks en el botón eliminar (añadí variantes por si el markup cambia)
        const btn = e.target.closest('.xoo-wsc-smr-del, .xoo-wsc-remove, .xoo-wsc-smr .remove, .xoo-wsc-smr .xoo-wsc-smr-del');
        if(!btn) return;

        // intentamos localizar el "recuadro blanco" subiendo por varios selectores comunes
        const trySelectors = ['.xoo-wsc-smr', '.xoo-wsc-product', '.xoo-wsc-cart-item', '.xoo-wsc-item', '.xoo-wsc-smr-row', '.xoo-wsc-smr-inner'];
        let productRow = null;
        for (let s of trySelectors) {
          productRow = btn.closest(s);
          if (productRow) break;
        }
        // fallback genérico (el primer ancestro li o div)
        if(!productRow) productRow = btn.closest('li, div');
        if(!productRow) return;

        // asegurar posicionamiento para que el overlay absoluto funcione
        const cs = window.getComputedStyle(productRow);
        if(cs.position === 'static') productRow.style.position = 'relative';

        // prevenir overlays duplicados
        if(productRow.querySelector('.xoo-wsc-loading-overlay')) return;

        // crear overlay con spinner negro + texto "Loading..."
        const overlay = document.createElement('div');
        overlay.className = 'xoo-wsc-loading-overlay';
        overlay.innerHTML = '<div class="xoo-wsc-loading-inner"><div class="xoo-wsc-spinner"></div><div class="xoo-wsc-loading-text">Loading...</div></div>';
        productRow.appendChild(overlay);

        // fallback: si por alguna razón nunca se elimina, lo quitamos a los X ms
        const fallback = setTimeout(()=> {
          if(overlay.parentNode) overlay.remove();
        }, 6000);

        // cuando Xootix actualice/elimine, quitamos overlays (evento que dispara Xootix)
        function limpiar() {
          clearTimeout(fallback);
          if(overlay.parentNode) overlay.remove();
        }
        document.addEventListener('xoo_wsc_removed_item', limpiar, { once: true });
        document.addEventListener('xoo_wsc_cart_updated', limpiar, { once: true });
      });
    });
    </script>
    <?php
}

// [[[[woocommerce_after_cart_item_name]]]]
// Hook para mostrar campos personalizados debajo del nombre del producto en el carrito
// Este hook se ejecuta después del nombre del producto en la tabla del carrito
add_action('woocommerce_after_cart_item_name', 'mostrar_campos_personalizados_en_carrito', 10, 2);

/* ==========================================================================
 * Función para mostrar campos personalizados en cada ítem del carrito. 
 * Se muestra un cuadro de texto para agregar el texto que tendrá el 
 * globo de cómic en caso de que el artwork tenga un globo de diálogo, 
 * y un combobox para elegir el color del marco si se desea. 
 * NUEVO:
 * - Se agregó un límite de 50 caracteres para el campo "Comic Balloon Text". 
 * - Este límite visual evita desbordes o textos ilegibles dentro del artwork. 
 * @param array $cart_item Los datos del producto en el carrito.
 * @param string $cart_item_key La clave única del ítem en el carrito.
 * =========================================================================== */
function mostrar_campos_personalizados_en_carrito($cart_item, $cart_item_key) {
    // Nos aseguramos de que solo se ejecute en la página del carrito
    if (!is_cart()) return;

    // Obtenemos el ID del producto
    $product_id = $cart_item['product_id'];

    // Revisamos si el producto tiene habilitado el campo ACF 'tiene_globo'
    $tiene_globo = get_field('tiene_globo', $product_id);

    // Obtenemos el texto del globo, si existe en los datos del carrito
    $valor_globo = isset($cart_item['comic_balloon_text']) && !empty($cart_item['comic_balloon_text']) 
        ? esc_attr($cart_item['comic_balloon_text']) // Sanitizamos el valor
        : 'default'; // Valor por defecto si no se ha definido

    // Obtenemos el color del marco seleccionado
    $valor_color = isset($cart_item['color_frame']) 
        ? esc_attr($cart_item['color_frame']) // Sanitizamos el valor
        : 'default'; // Valor por defecto si no se ha definido

    // Lista de opciones disponibles para el color del marco
    $opciones = ['default', 'blue', 'green', 'pink', 'red', 'violet', 'white', 'yellow'];

    // Contenedor general para los campos personalizados, con estilos para alinear y espaciar elementos
    echo '<div class="campos-personalizados-contenedor" data-cart-key="' . esc_attr($cart_item_key) . '" 
            style="margin-top:8px; display:flex; flex-direction:column; gap:10px; max-width:100%;">';

    // ===============================================================
    // ========== CAMPO DE TEXTO (si el producto tiene globo) ==========
    // ===============================================================
    if ($tiene_globo) {
        // Contenedor horizontal para el campo de texto
        echo '<div class="campo-personalizado-fila" style="display:flex; align-items:center; gap:10px;">';

        // Etiqueta del input de texto
        echo '<label for="comic_balloon_text_' . esc_attr($cart_item_key) . '" 
                style="margin:0; width:160px; font-weight:600;">Comic Balloon Text:</label>';

        // Campo de entrada de texto para el globo, con límite y placeholder
        echo '<div style="display:flex; flex-direction:column; flex:1; max-width:380px;">';
        echo '<input 
                type="text" 
                name="comic_balloon_text[' . esc_attr($cart_item_key) . ']" 
                id="comic_balloon_text_' . esc_attr($cart_item_key) . '" 
                value="' . $valor_globo . '" 
                maxlength="50" 
				placeholder="Max. 50 characters"
                style="width:100%; padding:6px;" 
                oninput="this.value = this.value.slice(0, 50)" />';
        
        // Contador de caracteres dinámico (inicializado con la longitud actual)
        $contador_inicial = strlen($valor_globo);
        echo '<small class="contador-caracteres" 
                  style="font-size:12px; color:#777; text-align:right; margin-top:2px;">' 
                  . $contador_inicial . '/50 caracteres</small>';
        echo '</div>';

        echo '</div>'; // Cierra el contenedor del campo de texto
    }

    // ===============================================================
    // ========== CAMPO SELECT (combo de colores del marco) ==========
    // ===============================================================

    // Contenedor horizontal para el combobox
    echo '<div class="campo-personalizado-fila" style="display:flex; align-items:center; gap:10px;">';

    // Etiqueta del combobox
    echo '<label for="color_frame_' . esc_attr($cart_item_key) . '" 
            style="margin:0; width:160px; font-weight:600;">Frame Color:</label>';

    // Combobox para seleccionar el color del marco
    echo '<select name="color_frame[' . esc_attr($cart_item_key) . ']" 
                  id="color_frame_' . esc_attr($cart_item_key) . '" 
                  style="flex:1; max-width:200px;">';

    // Se genera cada opción del select
    foreach ($opciones as $opcion) {
        // Verificamos si esta opción es la seleccionada
        $selected = ($valor_color === $opcion) ? 'selected' : '';
        // Se muestra la opción en el combo
        echo '<option value="' . esc_attr($opcion) . '" ' . $selected . '>' . ucfirst($opcion) . '</option>';
    }

    echo '</select>';
    echo '</div>'; // Cierra el contenedor del select

    echo '</div>'; // Cierra el contenedor general de todos los campos personalizados
}

// [[[[woocommerce_cart_item_name]]]]
add_filter( 'woocommerce_cart_item_name', 'mca_cart_item_name_no_link', 10, 3 );

/* ==========================================================================
/* Función que evita que el nombre del producto en el carrito sea un enlace.
/* Reemplaza <a>...</a> por su contenido (mantiene variaciones/HTML interno).
/* ========================================================================== */
function mca_cart_item_name_no_link( $product_name_html, $cart_item, $cart_item_key ) {
    // Si ya no hay <a>, devolvemos tal cual
    if ( false === stripos( $product_name_html, '<a' ) ) {
        return $product_name_html;
    }

    // Quita la etiqueta <a> pero conserva el contenido interno (texto, etiquetas, etc.)
    $without_link = preg_replace( '#<a[^>]*>(.*?)</a>#is', '$1', $product_name_html );

    // Envolvemos el texto con una clase para poder aplicar los estilos originales
    $without_link = '<span class="product-title">' . trim( $without_link ) . '</span>';

    return $without_link;
}

// Desactivar completamente los cupones en WooCommerce
add_filter( 'woocommerce_coupons_enabled', '__return_false' );
/* Desactiva el mensaje de WooCommerce al eliminar un producto del carrito. */
add_filter('wc_add_to_cart_message_html', '__return_empty_string'); // quita mensaje de "producto agregado"
add_filter('woocommerce_cart_item_removed_notice_type', '__return_empty_string'); // desactiva tipo de mensaje
add_filter('woocommerce_cart_item_removed_notice', '__return_empty_string'); // desactiva mensaje al eliminar
// También elimina el aviso de "producto restaurado" si el usuario presiona "Deshacer"
add_filter('woocommerce_cart_undo_item_notice', '__return_empty_string');
// [[[[woocommerce_update_cart_action_cart_updated]]]]
// hook para guardar campos personalizados al actualizar el carrito
add_action( 'woocommerce_update_cart_action_cart_updated', 'mcart_guardar_campos_personalizados_en_carrito' );

/* =============================================================================================================
 * Función para guardar los campos personalizados 'comic_balloon_text' y 'color_frame' en los datos del carrito.
 * - Solo guarda si al menos uno de los campos fue enviado y es un array válido.
 * - Sanitiza los valores para evitar código malicioso o datos inválidos.
 * - Limita el texto del globo a 160 caracteres.
 * ============================================================================================================= */
function mcart_guardar_campos_personalizados_en_carrito() {

    if (
        ( isset($_POST['comic_balloon_text']) && is_array($_POST['comic_balloon_text']) ) ||
        ( isset($_POST['color_frame']) && is_array($_POST['color_frame']) )
    ) {
        foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
			
			/*
			La función sanitize_text_field() es una función de WordPress que se usa para limpiar (sanitizar) un texto antes de guardarlo o procesarlo. Sirve para asegurarse de que el contenido
			no contenga código malicioso y que sea seguro. elimina etiquetas HTML, quita espacios innecesarios, elimina caracteres peligrosos, convierte caracteres especiales en una forma
			segura.
			
			Ejemplo: */
			/*
				$nombre_crudo = "<script>alert('Hola')</script> Ana ";
				$nombre_limpio = sanitize_text_field($nombre_crudo);
				echo $nombre_limpio;  // Resultado: Ana
			*/			
			/*
			 - isset para verificar que el campo exista.
			 - sanitize_text_field para evitar scripts maliciosos o datos inesperados.
			 - mb_substr para limitar a 160 caracteres, incluso si alguien manipula el formulario.
			 - Guarda el valor directamente en cart_contents.
			*/
			
            // Texto del globo
            if ( isset($_POST['comic_balloon_text'][$cart_item_key]) ) {
                $texto = sanitize_text_field( $_POST['comic_balloon_text'][$cart_item_key] );
                $texto = mb_substr( $texto, 0, 160 );
                WC()->cart->cart_contents[$cart_item_key]['comic_balloon_text'] = $texto;
            }

            // Color del marco
            if ( isset($_POST['color_frame'][$cart_item_key]) ) {
                $color = sanitize_text_field( $_POST['color_frame'][$cart_item_key] );
                WC()->cart->cart_contents[$cart_item_key]['color_frame'] = $color;
            }
        }

        // Guarda correctamente la sesión
        WC()->cart->set_session();
    }
}

// [[[[woocommerce_get_cart_item_from_session]]]]
// Filtro para restaurar campos personalizados desde la sesión del carrito 
add_filter( 'woocommerce_get_cart_item_from_session', 'mcart_restaurar_campos_personalizados_desde_sesion', 10, 3 );

/* ====================================================================================
 * Restaura los campos personalizados 'comic_balloon_text' y 'color_frame'
 * desde la sesión de WooCommerce cuando el carrito se recarga. 
 * Este filtro se ejecuta cada vez que WooCommerce reconstruye el carrito
 * desde la sesión almacenada en la base de datos (por ejemplo, al recargar la página). 
 * @param array $cart_item  Datos actuales del ítem en el carrito.
 * @param array $values     Datos almacenados previamente en la sesión.
 * @param string $key       Clave única del ítem del carrito.
 * @return array            Datos actualizados del ítem del carrito.
 * ==================================================================================== */
function mcart_restaurar_campos_personalizados_desde_sesion( $cart_item, $values, $key ) {

    // Restaura el texto del globo si existe
    if ( isset( $values['comic_balloon_text'] ) ) {
        $cart_item['comic_balloon_text'] = $values['comic_balloon_text'];
    }

    // Restaura el color del marco si existe
    if ( isset( $values['color_frame'] ) ) {
        $cart_item['color_frame'] = $values['color_frame'];
    }

    return $cart_item;
}

// [[[[woocommerce_before_calculate_totals]]]]
// Se dispara cada vez que WooCommerce necesita calcular los totales del carrito, incluyendo:
// Cuando el usuario carga la página del carrito.
// Cuando actualiza cantidades en el carrito.
// Durante el checkout, antes de mostrar totales.
// En peticiones AJAX relacionadas con el carrito (si no se bloquea con is_admin()).
add_action('woocommerce_before_calculate_totals', 'forzar_valores_por_defecto_en_carrito', 5);

/* ======================================================================================
* Función que sirve para forzar valores por defecto en todos los ítems del carrito
 * incluso si el usuario nunca presionó "Update Cart". 
 * Esta función se engancha al hook 'woocommerce_before_calculate_totals', que se ejecuta
 * justo antes de calcular los totales del carrito, permitiendo modificar los ítems. 
 * ====================================================================================== */
function forzar_valores_por_defecto_en_carrito( $cart ) {
    // Evitar ejecución en el backend o cuando no hay carrito
    if ( is_admin() && ! defined('DOING_AJAX') ) return;
    if ( ! did_action('woocommerce_before_calculate_totals') ) return;

    // Recorremos todos los ítems del carrito
    foreach ( $cart->get_cart() as $cart_item_key => $cart_item ) {

        // === Campo Comic Balloon Text ===
        if ( ! isset( $cart_item['comic_balloon_text'] ) || $cart_item['comic_balloon_text'] === '' ) {
            $cart->cart_contents[$cart_item_key]['comic_balloon_text'] = 'default';
        }

        // === Campo Frame Color ===
        if ( ! isset( $cart_item['color_frame'] ) || $cart_item['color_frame'] === '' ) {
            $cart->cart_contents[$cart_item_key]['color_frame'] = 'default';
        }
    }

    // Guardar los cambios en la sesión
    $cart->set_session();
}

// [[[[wp_head]]]]
add_action('wp_head', 'ocultar_variaciones_checkout_y_cart');

/* ==============================================================================================
 * Función que oculta los bloques de variaciones duplicadas en las páginas de carrito y checkout. 
 * Problema: WooCommerce, al usar productos variables o añadir campos personalizados,
 * genera automáticamente un bloque de variaciones (<dl class="variation">) debajo del
 * nombre del producto o en la tabla de revisión de pedido. Esto puede generar duplicación
 * visual si ya mostramos los campos de forma personalizada. 
 * Solución: Insertar CSS en el <head> de la página para ocultar estos bloques de variaciones.
 * ============================================================================================== */
function ocultar_variaciones_checkout_y_cart() {
	/* Ocultar duplicados del bloque de variaciones */
	if (is_cart() || is_checkout()) {
    	echo '<style>
            /* Ocultar duplicados del bloque de variaciones y etiquetas small */
            .woocommerce-cart-form .variation,
            .woocommerce-checkout-review-order-table .variation,
            .woocommerce-cart-form .product-name small {
                display: none !important;
            }
        </style>';
    }	
}

// [[[[gettext]]]]
// gettext es un filtro global de WordPress que se dispara cada vez que WordPress está a punto de mostrar un texto traducible 
// (es decir, cada vez que se llama a la función __(), _e(), _n(), etc.).
add_filter('gettext', 'traducciones_carrito', 20, 3);

/* =============================================================================
 * Intercepta los textos traducibles de WordPress y reemplaza algunos
 * valores en la página del carrito para mostrarlos en inglés. 
 * @param string $translated_text Texto ya traducido que WordPress va a mostrar.
 * @param string $text            Texto original sin traducir.
 * @param string $domain          Dominio de traducción (por ej. 'woocommerce').
 * @return string Texto final (modificado o no) que se mostrará en pantalla.
 * ============================================================================= */
function traducciones_carrito($translated_text, $text, $domain) {

    // Solo aplicar si la función is_cart() existe (WooCommerce está activo)
    if (function_exists('is_cart') && is_cart()) {
        switch ($translated_text) {
            case 'Actualizar el carrito':
                $translated_text = 'Update Cart';
                break;			
            case 'Finalizar compra':
                $translated_text = 'Proceed to Checkout';
                break;			
            case 'Producto':
                $translated_text = 'Product';
                break;
            case 'Cantidad':
                $translated_text = 'Quantity';
                break;			
            case 'Totales del carrito':
                $translated_text = 'Cart Totals';
                break;			
        }
    }

    return $translated_text;
}

// =====================================================================
// Función que muestra un overlay de carga dinámico al vaciar el carrito
// según la facción seleccionada por el jugador (cookie 'faction').
// Se ejecuta en el footer de WordPress, solo en la página de carrito.
// Lógica:
// 1. Verifica que estemos en la página del carrito.
// 2. Lee la cookie 'faction' para determinar la facción del jugador.
// 3. Muestra el overlay correspondiente:
//      - GDI → mostrar_loading_CART_GDI_overlay()
//      - Nod → mostrar_loading_CART_NOD_overlay()
// 4. Cada overlay tiene un mensaje aleatorio y animación de carga.
// =====================================================================
add_action('wp_footer', 'mostrar_overlay_carga_cart_por_faccion');

function mostrar_overlay_carga_cart_por_faccion() {   
    // Solo ejecutar en la página de carrito
    if (!is_cart()) return;
	
    if (isset($_COOKIE['faction'])) {
        $faction = $_COOKIE['faction'] ?? 'gdi';
		
        // Muestra el overlay según la facción
        if ($faction === 'gdi') {
            mostrar_loading_CART_GDI_overlay();
        } elseif ($faction === 'nod') {
            mostrar_loading_CART_NOD_overlay();
        }
    }
}

// ===========================================================
// Función que muestra el overlay de carga para la facción GDI
// con mensaje aleatorio y animación, al vaciar el carrito.
// ===========================================================
function mostrar_loading_CART_GDI_overlay()
{	
    // URL de la página de tienda para redirección
    $shop_url = esc_url(wc_get_page_permalink('shop'));

    // Frases GDI aleatorias (PHP) que se mostrarán en el overlay
    $messages = array(
        "Cart empty — returning to base...",
        "All units redeployed.",
        "Resource transport complete.",
        "Sector cleared. Redirecting..."
    );
    $chosen_message = $messages[array_rand($messages)]; // seleccionar una frase al azar
    ?>
    <style>
    /* === Overlay de carga GDI === */
    #gdi-loading-overlay {
        position: fixed;             /* Fijo en toda la pantalla */
        inset: 0;                    /* Ocupa todo el viewport */
        background: radial-gradient(circle at center, #005bbb 0%, #001f3f 80%); /* Fondo degradado GDI */
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        z-index: 999999;             /* Por encima de todo */
        opacity: 0;                  /* Inicialmente invisible */
        pointer-events: none;        /* No interactuable hasta activarse */
        transition: opacity 0.3s ease-in;
        font-family: 'Share Tech Mono', monospace;
        color: #f5d76e;
        text-transform: uppercase;
        letter-spacing: 2px;
    }

    /* Overlay activo */
    #gdi-loading-overlay.active {
        opacity: 1;
        pointer-events: all;
    }

    /* Spinner de carga */
    #gdi-loading-overlay .pulse {
        width: 80px;
        height: 80px;
        border: 4px solid #f5d76e;
        border-top-color: transparent;
        border-radius: 50%;
        animation: spin 1s linear infinite;
        box-shadow: 0 0 20px #f5d76e;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    /* Estilo del mensaje */
    #gdi-loading-overlay h2 {
        margin-top: 20px;
        font-size: 1.2rem;
        text-shadow: 0 0 10px #f5d76e;
        text-align: center;
    }
    </style>

    <!-- Overlay HTML -->
    <div id="gdi-loading-overlay">
        <div class="pulse"></div>
        <!-- Mensaje aleatorio seleccionado en PHP -->
        <h2><?php echo esc_html( $chosen_message ); ?></h2>
    </div>

    <script>
    (function(){
      const shopURL = '<?php echo $shop_url; ?>';
      const overlay = document.getElementById('gdi-loading-overlay');

      // Función para activar el overlay
      function showGdiOverlay() {
        overlay.classList.add('active');
      }

      // Función que detecta si el carrito está vacío e inicia la redirección
      function instantRedirectIfEmpty(){
        try {
          // Detecta si hay mensaje de carrito vacío o no hay items
          const cartEmpty = document.querySelector('.cart-empty, .woocommerce-info')?.innerText.includes('vacío');
          const hasItems = document.querySelectorAll('tr.cart_item').length > 0;

          if (cartEmpty || !hasItems) {
            // Oculta el contenido principal
            const content = document.querySelector('#content, .site-content, .woocommerce');
            if (content) content.style.display = 'none';
            document.body.style.background = '#000';
            showGdiOverlay();

            // Redirige a la tienda tras breve pausa para mostrar la animación
            setTimeout(() => {
              window.location.replace(shopURL);
            }, 500);
          }
        } catch(e){}
      }

      // Ejecutar al cargar la página
      if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', instantRedirectIfEmpty);
      } else {
        instantRedirectIfEmpty();
      }

      // Observa cambios dinámicos en el DOM para detectar carrito vacío
      if (window.MutationObserver) {
        const obs = new MutationObserver(instantRedirectIfEmpty);
        obs.observe(document.body, {childList:true, subtree:true});
      }
    })();
    </script>
    <?php		
}

// ==================================================================
// Función que muestra el overlay de carga para la facción Nod (C&C3)
// con mensaje aleatorio y animación, al vaciar el carrito.
// ==================================================================
function mostrar_loading_CART_NOD_overlay()
{
    // URL de la página de tienda para redirección
    $shop_url = esc_url(wc_get_page_permalink('shop'));

    // Frases Nod C&C3 aleatorias que se mostrarán en el overlay
    $messages = array(
        "Cart empty — redeploying Black Hand units...",
        "All harvesters accounted for. Resource flow stable.",
        "Obedience achieved. Sector under control.",
        "Kane commands — returning to base for redeployment..."
    );
    $chosen_message = $messages[array_rand($messages)]; // seleccionar una frase al azar
    ?>
    <style>
    /* === Overlay de carga NOD === */
    #nod-loading-overlay {
        position: fixed;             /* Fijo en toda la pantalla */
        inset: 0;                    /* Ocupa todo el viewport */
        background: radial-gradient(circle at center, #3b0000 0%, #000000 80%); /* Fondo rojo/negro */
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        z-index: 999999;             /* Por encima de todo */
        opacity: 0;                  /* Inicialmente invisible */
        pointer-events: none;        /* No interactuable hasta activarse */
        transition: opacity 0.3s ease-in;
        font-family: 'Share Tech Mono', monospace;
        color: #ff3333;              /* Color del texto y del spinner */
        text-transform: uppercase;
        letter-spacing: 2px;
    }

    /* Overlay activo */
    #nod-loading-overlay.active {
        opacity: 1;
        pointer-events: all;
    }

    /* Spinner de carga */
    #nod-loading-overlay .pulse {
        width: 80px;
        height: 80px;
        border: 4px solid #ff3333;
        border-top-color: transparent;
        border-radius: 50%;
        animation: spin 1s linear infinite;
        box-shadow: 0 0 20px #ff3333;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    /* Estilo del mensaje */
    #nod-loading-overlay h2 {
        margin-top: 20px;
        font-size: 1.2rem;
        text-shadow: 0 0 10px #ff3333;
        text-align: center;
    }
    </style>

    <!-- Overlay HTML -->
    <div id="nod-loading-overlay">
        <div class="pulse"></div>
        <!-- Mensaje aleatorio seleccionado en PHP -->
        <h2><?php echo esc_html( $chosen_message ); ?></h2>
    </div>

    <script>
    (function(){
      const shopURL = '<?php echo $shop_url; ?>';
      const overlay = document.getElementById('nod-loading-overlay');

      // Función para activar el overlay
      function showNodOverlay() {
        overlay.classList.add('active');
      }

      // Función que detecta si el carrito está vacío e inicia la redirección
      function instantRedirectIfEmpty(){
        try {
          // Detecta si hay mensaje de carrito vacío o no hay items
          const cartEmpty = document.querySelector('.cart-empty, .woocommerce-info')?.innerText.includes('vacío');
          const hasItems = document.querySelectorAll('tr.cart_item').length > 0;

          if (cartEmpty || !hasItems) {
            // Oculta el contenido principal
            const content = document.querySelector('#content, .site-content, .woocommerce');
            if (content) content.style.display = 'none';
            document.body.style.background = '#000';
            showNodOverlay();

            // Redirige a la tienda tras breve pausa para mostrar la animación
            setTimeout(() => {
              window.location.replace(shopURL);
            }, 500);
          }
        } catch(e){}
      }

      // Ejecutar al cargar la página
      if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', instantRedirectIfEmpty);
      } else {
        instantRedirectIfEmpty();
      }

      // Observa cambios dinámicos en el DOM para detectar carrito vacío
      if (window.MutationObserver) {
        const obs = new MutationObserver(instantRedirectIfEmpty);
        obs.observe(document.body, {childList:true, subtree:true});
      }
    })();
    </script>
    <?php
}

// ===================================================================
// BLOQUE LATERAL IZQUIERDO DE PERSONALIZACIÓN EN LA PÁGINA DE CARRITO
// ===================================================================

// [[[[woocommerce_after_cart]]]]
// El hook woocommerce_after_cart en WooCommerce es un hook de acción que se dispara justo después del contenido principal del carrito, es decir, 
// después de que WooCommerce genera la tabla con los productos del carrito y los totales.
add_action('woocommerce_after_cart', 'bloque_lateral_personalizacion_carrito');

// ========================================================================
// Función que agrega un recuadro lateral izquierdo en la página de carrito
// con instrucciones para que el cliente pueda personalizar sus artworks.
// Incluye estilos CSS para mantener el recuadro fijo en pantallas grandes
// y que se adapte en pantallas pequeñas.
// ========================================================================
function bloque_lateral_personalizacion_carrito() {
    
    // Validación: solo mostrar en la página de carrito
    if (!is_cart()) return; 

    ?>

    <div class="mcartworks-side-info-left">
        <h3>Customize your artworks easily:</h3>

        <p><strong>Comic balloon:</strong> Write your text in <em>Comic Balloon Text</em>, or leave it on <em>default</em> to keep the preset text.</p>

        <p><strong>Frame color:</strong> Choose a <em>Frame Color</em> or keep <em>default</em> to use the original frame.</p>

        <p><strong>Artwork name:</strong> Enter the name on the <em>checkout page</em>.  
        If you add multiple artworks or bundles, the same name will apply to all.</p>

        <p><strong>Bundles:</strong> You can only change the <em>Frame Color</em> for all artworks in the bundle.  
        For individual balloon text or specific artwork changes, please <strong>contact us</strong>.</p>
		
        <hr>

        <p><strong>Delivery format (per artwork):</strong></p>
        <ul style="margin: 0 0 0 15px; padding: 0;">
            <li><strong>Frame:</strong> 2 files, each under 5 MB.</li>
            <li><strong>Workshop:</strong> 5 files, each under 2 MB.</li>
        </ul>

        <p style="margin-top:10px;">Each artwork includes both versions.</p>
		
		<p><strong>Stamps removal:</strong> All default stamps are removed — only your name remains on the artwork.</p>

		<p><strong>Steam upload:</strong> Instructions on how to upload the artworks to Steam are included.</p>
		
		If you're from Argentina, you can pay via CBU with Argentine pesos. For purchases from Argentina, please contact us first.
		
    </div>
    <?php
}

// [[[[wp_footer]]]]
add_action('wp_footer', 'fix_videos_chrome_cart_only');

/* ===================================================================================================
 * Función que soluciona el corte abrupto de videos en Chrome/Opera al salir de la página del carrito.
 * Genera un "snapshot" (imagen estática del frame actual) antes de cambiar de página,
 * evitando que el video desaparezca antes de la transición.
 * Se aplica la misma logica que para bundles
 * =================================================================================================== */
function fix_videos_chrome_cart_only() {
	if ( !is_cart() ) return;
	?>
	<script>
	(function(){
		const ua = navigator.userAgent;
		const isChrome = /Chrome/.test(ua) && /Google Inc/.test(navigator.vendor);
		const isOpera = /OPR\//.test(ua);
		if (!(isChrome || isOpera)) return;
		
		// === Captura individual ===
		function snapshotVideo(video) {
			try {
				const rect = video.getBoundingClientRect();
				const parent = video.parentElement;
				if (!parent) return;

				const parentStyle = getComputedStyle(parent);
				if (parentStyle.position === 'static') parent.style.position = 'relative';
				if (parentStyle.overflow !== 'visible') parent.style.overflow = 'visible';

				const parentRect = parent.getBoundingClientRect();
				const topInsideParent = rect.top - parentRect.top;
				const leftInsideParent = rect.left - parentRect.left;
				const w = Math.max(1, Math.round(rect.width));
				const h = Math.max(1, Math.round(rect.height));

				const prev = parent.querySelector('.snapshot-overlay');
				if (prev) prev.remove();

				const canvas = document.createElement('canvas');
				canvas.className = 'snapshot-overlay';
				canvas.width = w;
				canvas.height = h;
				Object.assign(canvas.style, {
					width: w + 'px',
					height: h + 'px',
					position: 'absolute',
					top: topInsideParent + 'px',
					left: leftInsideParent + 'px',
					zIndex: '9999',
					pointerEvents: 'none',
					borderRadius: getComputedStyle(video).borderRadius || '0'
				});

				const ctx = canvas.getContext('2d');
				ctx.drawImage(video, 0, 0, w, h);
				parent.appendChild(canvas);
			} catch (err) {
				const poster = video.getAttribute('poster');
				if (!poster) return;
				const parent = video.parentElement;
				if (!parent) return;

				const rect = video.getBoundingClientRect();
				const parentRect = parent.getBoundingClientRect();
				const topInsideParent = rect.top - parentRect.top;
				const leftInsideParent = rect.left - parentRect.left;
				const w = Math.max(1, Math.round(rect.width));
				const h = Math.max(1, Math.round(rect.height));

				const prev = parent.querySelector('.snapshot-overlay');
				if (prev) prev.remove();

				const img = document.createElement('img');
				img.className = 'snapshot-overlay';
				img.src = poster;
				Object.assign(img.style, {
					width: w + 'px',
					height: h + 'px',
					position: 'absolute',
					top: topInsideParent + 'px',
					left: leftInsideParent + 'px',
					zIndex: '9999',
					pointerEvents: 'none',
					borderRadius: getComputedStyle(video).borderRadius || '0'
				});

				parent.appendChild(img);
			}
		}

		// === Captura solo videos del carrito ===
		function snapshotCartVideos(){
			const cartArea = document.querySelector('.woocommerce-cart-form, .cart');
			if (!cartArea) return;
			cartArea.querySelectorAll('video').forEach(v => {
				const rect = v.getBoundingClientRect();
				if (rect.width > 0 && rect.height > 0) snapshotVideo(v);
			});
		}

		// === Clics en enlaces normales (navegación) ===
		document.addEventListener('click', function(e){
			const link = e.target.closest && e.target.closest('a[href]');
			if (!link) return;

			// Ignorar si el menú está activo (cubre toda la pantalla)
			if (
				document.body.classList.contains('menu-open') ||
				document.body.classList.contains('zaj-menu-active') ||
				document.querySelector('.zaj-menu.active, .zaj-overlay.active')
			) {
				return; // no hacemos snapshot mientras el menú está visible
			}

			// Ignorar el tacho de eliminar
			if (link.classList.contains('remove')) return;

			// Ignorar enlaces internos sin navegación
			const href = link.getAttribute('href');
			if (!href || href.startsWith('#')) return;
			if (link.target === '_blank') return;

			// Ignorar botón de update cart
			if (link.name === 'update_cart' || link.classList.contains('update_cart')) return;

			// Ignorar enlaces del menú o footer del menú (por seguridad extra)
			if (
				link.closest('.zaj-menu') ||
				link.closest('.zaj-overlay') ||
				link.closest('.zaj-card') ||
				link.closest('.zaj-footer-links')
			) {
				return;
			}

			e.preventDefault();
			snapshotCartVideos();
			setTimeout(() => window.location.href = href, 80);
		}, false);

		// === Al salir o cambiar de página ===
		window.addEventListener('beforeunload', snapshotCartVideos);
		window.addEventListener('pagehide', snapshotCartVideos);
	})();
	</script>
	<?php
}

// [[[[wp_footer]]]]
add_action('wp_footer', 'forzar_desactivar_boton_view_cart');

// =========================================================================================================================
// Función que evita que el enlace "View Cart" del carrito xootix funcione cuando el usuario ya está en la página
// del carrito (/cart/). Así se previenen redirecciones innecesarias y mejora la experiencia.
// Se ejecuta únicamente en la página del carrito (cart).
// =========================================================================================================================
function forzar_desactivar_boton_view_cart() {
    // Solo ejecutar en la página del carrito
    if (!is_cart()) return;
    ?>
    <script>
    (function(){
        const SELECTOR = '.xoo-wsc-ft-btn-cart';
        const DISABLED_MARK = 'data-cart-disabled';

        function estiloDesactivado(el){
            el.setAttribute(DISABLED_MARK, '1');
            el.setAttribute('aria-disabled', 'true');
            el.setAttribute('tabindex', '-1');
            el.style.pointerEvents = 'none';
            el.style.opacity = '0.5';
            el.style.cursor = 'default';
            try { el.textContent = "You're already in the cart"; } catch(e){}
            try {
                el.dataset.hrefBackup = el.getAttribute('href') || '';
                el.removeAttribute('href');
            } catch(e){}
        }

        function desactivarSiExiste(node){
            if (!node) return;

            if (node.matches && node.matches(SELECTOR) && !node.hasAttribute(DISABLED_MARK)) {
                estiloDesactivado(node);
            }

            const encontrados = node.querySelectorAll ? node.querySelectorAll(SELECTOR) : [];
            encontrados.forEach(function(el){
                if (!el.hasAttribute(DISABLED_MARK)) estiloDesactivado(el);
            });
        }

        // 1. Intento inmediato al cargar
        desactivarSiExiste(document);

        // 2. Escucha eventos del plugin que puedan reinyectar el botón
        ['xoo_wsc_loaded','xoo_wsc_cart_updated','xoo_wsc_open','xoo_wsc_after_load'].forEach(function(ev){
            document.addEventListener(ev, function(e){
                if (e && e.target) desactivarSiExiste(e.target);
                desactivarSiExiste(document);
            }, {passive:true});
        });

        // 3. Observa cambios en el DOM por si el botón se vuelve a insertar
        const observer = new MutationObserver(function(mutations){
            for (let m of mutations) {
                if (m.addedNodes && m.addedNodes.length) {
                    m.addedNodes.forEach(function(node){
                        if (node.nodeType === 1) desactivarSiExiste(node);
                    });
                }
            }
        });

        observer.observe(document.documentElement || document.body, {
            childList: true,
            subtree: true
        });

        // 4. Captura global de clics para evitar navegación accidental
        if (window.location.pathname.includes('/cart')) {
    		document.addEventListener('click', function(e){
				const a = e.target.closest ? e.target.closest(SELECTOR) : null;
				if (a && a.hasAttribute(DISABLED_MARK)) {
					e.preventDefault();
					e.stopImmediatePropagation();
					try {
						a.style.transition = 'opacity .15s';
						a.style.opacity = '0.5';
					} catch(err){}
					return false;
				}
        	}, true);
		}

        // 5. Fallback de reintentos
        let intentos = 0;
        const intervalo = setInterval(function(){
            desactivarSiExiste(document);
            intentos++;
            if (intentos > 10) clearInterval(intervalo);
        }, 300);

    })();
    </script>
    <?php
}

// ################################################################################################################################################
// ################################################################################################################################################
// ################################################################################################################################################
// 											CHECKOUT
// ################################################################################################################################################
// ################################################################################################################################################
// ################################################################################################################################################

// [[[[wp_head]]]]
add_action('wp_head', 'estilo_personalizado_checkout');

/* ===================================================================================
 * Función que agrega estilos CSS personalizados para el body de la página de checkout  
 * =================================================================================== */ 
function estilo_personalizado_checkout() {	
	if(!is_checkout()) return; 
	
    $faction = $_COOKIE['faction'] ?? 'gdi';
	
    if ($faction === 'gdi') {  				
        $body_bg = 'linear-gradient(270deg, #001f3f, #003f7f, #005bbb, #001f3f)'; // azul profundo eléctrico			
    } else if ($faction === 'nod') {
		// $body_bg = 'linear-gradient(135deg, rgba(10, 0, 0, 0.95) 0%, rgba(40, 0, 0, 0.9) 50%, rgba(120, 0, 0, 0.4) 100% )';
		$body_bg = 'linear-gradient(135deg, rgba(5, 0, 0, 0.97) 0%, rgba(20, 0, 0, 0.94) 40%, rgba(60, 0, 0, 0.85) 70%, rgba(90, 0, 0, 0.6) 100%)';
    }	
    ?>
    <style>
      /* ===========================
         BACKGROUND ANIMADO 
         ========================== */
      body {
        background: <?= $body_bg ?>;
        background-size: 200% 200%;
        animation: gradientShift 20s ease infinite;
        overflow-x: hidden;      
      }
      @keyframes gradientShift {
        0% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
        100% { background-position: 0% 50%; }
      }		
    </style>

	<div class="grid-3d"></div>    	
	<!-- <div class="hud-circle"></div> -->
    <div class="custom-shop-title">CHECKOUT</div>
    <div class="scan-line"></div>    

    <?php
}

// [[[[woocommerce_checkout_fields]]]] 
// El hook woocommerce_checkout_fields te deja modificar completamente los campos del formulario de checkout antes de que se muestren al usuario.
// Sirve para agregar, quitar o cambiar los campos del checkout: los de facturación (billing), envío (shipping) y adicionales (order).
add_filter('woocommerce_checkout_fields', 'simplificar_checkout_nombre_y_correo');

// =================================================================================
// Función que modifica el formulario de checkout de WooCommerce para que solo tenga
// los campos de nombre y correo electrónico en la sección de facturación.
// - Reemplaza todos los campos de facturación por dos campos: nombre y email.
// - Ambos campos son obligatorios.
// - Los campos se muestran en ancho completo ('form-row-wide').
// - Se controla la prioridad para que el nombre aparezca primero y el email después.
// ==================================================================================
function simplificar_checkout_nombre_y_correo($fields) {
    $fields['billing'] = [
        'billing_first_name' => [
            'label'       => 'First Name',
            'required'    => true,
            'class'       => ['form-row-wide'],
            'priority'    => 10,
        ],
        'billing_email' => [
            'label'       => 'Email',
            'required'    => true,
            'class'       => ['form-row-wide'],
            'priority'    => 20,
        ],
    ];
    return $fields;
}

// [[[[woocommerce_checkout_get_value]]]]
// El hook woocommerce_checkout_get_value sirve para controlar o modificar el valor que WooCommerce muestra en cada campo del formulario de checkout antes de renderizarlo.
// En otras palabras: cada vez que WooCommerce llena un campo (nombre, email, dirección, etc.), pasa por este filtro para ver si debe mostrar algo diferente.
add_filter('woocommerce_checkout_get_value', 'limpiar_campos_checkout', 10, 2);

// ================================================================================================================================
// Función que vacia los valores de "nombre" y "correo" en el checkout. Esta función fuerza a que los campos 'First Name' y 'Email'
// aparezcan siempre vacíos al cargar la página de checkout. Evita que WooCommerce los rellene con datos previos del navegador,
// sesiones o cookies, asegurando un formulario limpio cada vez.
// - Hook utilizado: woocommerce_checkout_get_value
// - Parámetros:
//   $input: el valor actual del campo (si lo hubiera)
//   $key: el nombre del campo de checkout
// ================================================================================================================================
function limpiar_campos_checkout($input, $key) {
    // Campos que queremos forzar a vacíos
    $campos_a_limpiar = ['billing_first_name', 'billing_email'];

    // Si el campo actual está en la lista, devolvemos vacío
    if (in_array($key, $campos_a_limpiar)) {
        return ''; // siempre vacío
    }

    // En cualquier otro caso, dejamos el valor original
    return $input;
}

// [[[[init]]]]
// init es uno de los primeros hooks del ciclo de carga de WordPress.
// En ese momento ya se puede registrar taxonomías, tipos de post, o métodos de pago (en WooCommerce).
// En WordPress, una taxonomía es un sistema de clasificación que permite agrupar contenido.
// El ejemplo más claro es cómo categorías y etiquetas agrupan posts.
add_action('init', 'registrar_metodo_pago_manual');

/* ==============================================================================
 * MÉTODO DE PAGO MANUAL: PayPal (Manual)
 * - Envía email al CLIENTE con instrucciones PayPal en HTML elegante y ordenado.
 * - WooCommerce marca automáticamente el pedido como "on-hold".
 * - Crea un log detallado del pedido en /wp-content/uploads/manual-pay.log.
 * - Pedido válido 48 horas; entrega dentro de 24 horas tras confirmación.
 * ============================================================================== */
function registrar_metodo_pago_manual() {

    if (!class_exists('WC_Payment_Gateway')) return;

    class WC_Gateway_Manual_Pay extends WC_Payment_Gateway {

        public function __construct() {
            $this->id                 = 'manual_pay';
            $this->icon               = '';
            $this->has_fields         = false;
            $this->method_title       = 'Manual Payment (Offline)';
            $this->method_description = 'The user will receive an email with PayPal payment details.';
            $this->title              = 'Pay with PayPal (manual)';
            $this->enabled            = 'yes';
            $this->supports           = ['products'];

            add_filter('woocommerce_available_payment_gateways', [$this, 'solo_en_checkout']);
        }

        public function solo_en_checkout($gateways) {
            if (is_admin()) return $gateways;
            if (!is_checkout() && !(defined('DOING_AJAX') && DOING_AJAX)) {
                unset($gateways[$this->id]);
            }
            return $gateways;
        }

		private function get_log_path() {
			// Directorio existente donde se guardan los logs
			$uploads_dir = WP_CONTENT_DIR . '/uploads/logs';

			// Nombre del archivo con formato argentino: día-mes-año_orders.log
			$date_str = date('d-m-Y');
			$log_filename = "{$date_str}_orders.log";

			// Retorna el path completo del archivo del día
			return $uploads_dir . '/' . $log_filename;
		}
		
		private function log_manual_pay($message) {
			$log_file = $this->get_log_path();

			// Bloque visual para separar cada registro
			$entry = trim($message) . "\n";

			$written = @file_put_contents($log_file, $entry, FILE_APPEND | LOCK_EX);
			if ($written === false) {
				error_log("manual-pay log write FAILED: $entry");
			}
		}

        public function process_payment($order_id) {
            $order = wc_get_order($order_id);
			
		// xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
		// Log detallado del pedido
		// xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

		$log  = "============================================================\n";
		$log .= "🕒  " . date('d-m-Y H:i:s', current_time('timestamp')) . "\n";
		$log .= "============================================================\n";			
		$log .= "🛒 NEW MANUAL PAYPAL ORDER\n";			
		$log .= "------------------------------------------------------------\n";
		$log .= "Order #: " . $order->get_order_number() . "\n";
		$log .= "Customer: " . $order->get_billing_first_name() . " " . $order->get_billing_last_name() . "\n";
		$log .= "Email: " . $order->get_billing_email() . "\n";

		// Agregamos Artworks Name si existe
		$artwork_name = $order->get_meta('Artworks Name');
		if ($artwork_name) {
			$log .= "Name for the artworks: " . $artwork_name . "\n";
		}

		$log .= "------------------------------------------------------------\n";

		foreach ($order->get_items() as $item) {
			$product_name = $item->get_name();
			$total = number_format($item->get_total(), 2);
			$log .= "• {$product_name} - \${$total}\n";

			$product_id = $item->get_product_id();
			$tiene_globo = get_field('tiene_globo', $product_id); // solo muestra Comic Balloon Text si tiene_globo

			$meta_lines = [];
			foreach ($item->get_meta_data() as $meta) {
				$key = strtolower($meta->key);
				$value = $meta->value;

				if ($key === 'frame color') {
					$meta_lines[] = "• Frame Color: {$value}";
				}
				if ($tiene_globo && $key === 'comic balloon text') {
					$meta_lines[] = "• Comic Balloon Text: {$value}";
				}
			}

			if (!empty($meta_lines)) {
				foreach ($meta_lines as $line) {
					$log .= "   {$line}\n";
				}
			}
		}

		$log .= "------------------------------------------------------------\n";
		$log .= "Total: $" . number_format($order->get_total(), 2) . "\n";

		$this->log_manual_pay($log);
			
		// xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
		// Enviar mail al cliente
		// xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
			
        // === Construir resumen HTML ===
        $order_date = $order->get_date_created() ? $order->get_date_created()->date('F j, Y') : '';
        $items_html = '';
			
		// Se arma el listado de productos
		foreach ($order->get_items() as $item) {
				$product_name = esc_html($item->get_name());
				$total = number_format($item->get_total(), 2);
				$meta_html = '';

				$product_id = $item->get_product_id();
				$tiene_globo = get_field('tiene_globo', $product_id); // true/false

				foreach ($item->get_meta_data() as $meta) {
					$key = esc_html($meta->key);
					$value = esc_html($meta->value);

					// Solo mostramos "Comic Balloon Text" si tiene_globo es true
					if ($tiene_globo && strtolower($key) === 'comic balloon text') {
						$meta_html .= "<br><span style='color:#777;'>• <strong>{$key}:</strong> {$value}</span>";
					}

					// Frame Color siempre se muestra
					if (strtolower($key) === 'frame color') {
						$meta_html .= "<br><span style='color:#777;'>• <strong>{$key}:</strong> {$value}</span>";
					}
				}

				$items_html .= "
				<tr>
					<td style='padding:6px 0;border-bottom:1px solid #eee;'>{$product_name}{$meta_html}</td>
					<td style='padding:6px 0;border-bottom:1px solid #eee;text-align:right;'>\${$total}</td>
				</tr>";
			}

            $artwork_html = '';
            if ($artwork_name) {
                $artwork_html = '<p style="color:#555;margin:5px 0 15px;"><strong>Name for the artworks:</strong> ' . esc_html($artwork_name) . '</p>';
            }
			
			// Path al logo
			$uploads_dir = wp_upload_dir();			
			$logo_url = trailingslashit($uploads_dir['baseurl']) . 'images/logo.png';			
			// $logo_url = home_url('/images/logo.png');

			// === Plantilla HTML elegante ===
            $message = '
            <html>
            <body style="background:#f6f6f6;padding:40px 0;margin:0;font-family:Helvetica,Arial,sans-serif;">
            <table align="center" width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:12px;overflow:hidden;">
			<td style="background:#4B0082;color:#ffffff;padding:25px 30px;text-align:center;">
			<img src="' . esc_url($logo_url) . '" 
				 alt="MCArtworks Studio" 
				 width="110" 
				 style="display:block;margin:0 auto 6px auto;">
			<div style="font-size:22px;font-weight:bold;letter-spacing:0.5px;">MCArtworks Studio</div>
			</td>
                <tr>
                    <td style="padding:30px;">
                        <p style="font-size:16px;color:#333;">Hi <strong>' . esc_html($order->get_billing_first_name()) . '</strong>,</p>
                        <p style="font-size:15px;color:#555;">Thank you for your order at <strong>MCArtworks Studio</strong>!</p>

                        <div style="background:#fafafa;border-left:4px solid #0070ba;padding:15px 20px;margin:20px 0;">
                            <p style="margin:0;color:#333;font-size:15px;">
                                <strong>To complete your purchase:</strong><br>
                                Please send the total amount to our PayPal account:<br>
								<span style="color:#0070ba;font-weight:bold;">mcartworksstudio@gmail.com</span>
								<br><span style="font-size:13px;color:#555;">(copy and paste this email directly in PayPal → Send Money)</span>
                            </p>
							<p style="margin:10px 0 0;color:#333;font-size:14px;">
								<strong>Important:</strong> When sending the payment, please include the following details in your PayPal note:
							</p>
							<ul style="margin:8px 0 0 20px;padding:0;color:#555;font-size:13px;line-height:1.5;">
								<li>Your order number: <strong>#' . $order->get_order_number() . '</strong></li>
								<li>Your name: <strong>' . esc_html($order->get_billing_first_name()) . ' ' . esc_html($order->get_billing_last_name()) . '</strong> (must be the same as you entered on our website)</li>
								<li>Your email: <strong>' . esc_html($order->get_billing_email()) . '</strong> (must be the same as you entered on our website)</li>
							</ul>
							<p style="margin:10px 0 0;color:#777;font-size:13px;">
								This ensures we can match your payment to your order quickly and correctly.
							</p>
						    <p style="margin:6px 0 0;color:#a00;font-size:13px;font-style:italic;">
        						(In PayPal choose “Paying for an item or service” / “Goods & Services” — this enables buyer & seller protection. Do not choose “Friends & Family.”)
    						</p>
                        </div>

                        <h3 style="border-bottom:2px solid #eee;padding-bottom:8px;color:#111;">Order Summary</h3>
						<p style="color:#777;margin:0 0 10px;">Order #' . $order->get_order_number() . ' (' . $order_date . ')</p>
                        ' . $artwork_html . ' 
                        <table width="100%" cellpadding="0" cellspacing="0" style="font-size:14px;color:#555;border-collapse:collapse;">
                            ' . $items_html . '
                            <tr>
                                <td style="padding-top:10px;font-weight:bold;">Total to Pay:</td>
                                <td align="right" style="padding-top:10px;font-weight:bold;color:#111;">$' . number_format($order->get_total(), 2) . '</td>
                            </tr>
                        </table>

                        <p style="margin:25px 0 5px;color:#777;font-size:13px;">
                            Once we receive your payment, we’ll confirm it via email and deliver your artworks within 24 hours.
                        </p>
                        <p style="margin:0;color:#999;font-size:12px;">
                            Order will remain valid for 48 hours.
                        </p>

                        <p style="margin-top:30px;font-size:14px;color:#555;">
                            Thank you for your trust!<br>
                            <strong>— MCArtworks Studio</strong>
                        </p>
                    </td>
                </tr>
                <tr>
					<td style="background:#4B0082;color:#ffffff;font-size:12px;text-align:center;padding:15px;font-weight:600;">
						© ' . date('Y') . ' MCArtworks Studio — All rights reserved
					</td>
                </tr>
            </table>
            </body>
            </html>';

            // === Enviar correo ===
            $headers = [
                'Content-Type: text/html; charset=UTF-8',
                'From: MCArtworks Studio <shop@mcartworksstudio.com>'
            ];

			$subject = '🧾 Order #' . $order->get_order_number() . ' — Payment Details from MCArtworks Studio';
			$result  = wp_mail($order->get_billing_email(), $subject, $message, $headers);

            if (!$result) {
                $this->log_manual_pay("⚠️ ERROR sending email to {$order->get_billing_email()} for order #{$order->get_order_number()}");
            } else {
                $this->log_manual_pay("✅ Email sent successfully to {$order->get_billing_email()} for order #{$order->get_order_number()}");
            }
			
			// xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
			// Enviar mail al administrador
			// xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

			$admin_email = 'mcartworksstudio@gmail.com'; 
			$admin_subject = '🛒 New Order Received — #' . $order->get_order_number();

			$order_date = $order->get_date_created() ? $order->get_date_created()->date('F j, Y') : '';

			$admin_message = '
			<html>
			<body style="font-family: Arial, sans-serif; background-color: #f7f7f7; padding: 20px; color: #222;">
			  <table style="max-width:600px; margin:auto; background:#fff; border-radius:8px; overflow:hidden;">
				<tr>
				  <td style="background:#4B0082; color:#fff; text-align:center; padding:15px; font-size:18px;">
					<strong>New Order Received</strong>
				  </td>
				</tr>
				<tr>
				  <td style="padding:20px;">
					<p style="font-size:15px; margin:0 0 10px 0;">Hi Admin,</p>
					<p style="font-size:15px; margin:0 0 15px 0;">A new manual PayPal order has been placed.</p>

					<h3 style="color:#4B0082; margin-top:20px;">Order Summary</h3>
					<p style="color:#777;margin:0 0 10px;">Order #' . $order->get_order_number() . ' (' . $order_date . ')</p>
					' . $artwork_html . '
					
					<table width="100%" cellpadding="0" cellspacing="0" style="font-size:14px;color:#555;border-collapse:collapse;">
						' . $items_html . '
						<tr>
							<td style="padding-top:10px;font-weight:bold;">Total to Pay:</td>
							<td align="right" style="padding-top:10px;font-weight:bold;color:#111;">$' . number_format($order->get_total(), 2) . '</td>
						</tr>
					</table>

					<h3 style="color:#4B0082; margin-top:25px;">Customer Information</h3>
					<p style="font-size:14px; line-height:1.6em;">
					  <strong>Name:</strong> ' . esc_html($order->get_billing_first_name()) . ' ' . esc_html($order->get_billing_last_name()) . '<br>
					  <strong>Email:</strong> ' . esc_html($order->get_billing_email()) . '<br>					 
					</p>

					<p style="margin-top:25px; font-size:14px;">
					  You can view and manage this order here:<br>
					  <a href="' . admin_url('post.php?post=' . $order_id . '&action=edit') . '" 
						 style="color:#4B0082; font-weight:bold; text-decoration:none;">
						 View Order in Dashboard
					  </a>
					</p>
				  </td>
				</tr>
				<tr>
				  <td style="background:#4B0082; color:#fff; text-align:center; padding:10px; font-size:12px;">
					© ' . date('Y') . ' MCArtworks Studio — Internal Order Notification
				  </td>
				</tr>
			  </table>
			</body>
			</html>
			';

			$result = wp_mail($admin_email, $admin_subject, $admin_message, $headers);

			if (!$result) {
				$this->log_manual_pay("⚠️ ERROR sending email to the admin for order #{$order->get_order_number()}");
			} else {
				$this->log_manual_pay("✅ Email sent successfully to the admin for order #{$order->get_order_number()}");
			}
			
			$this->log_manual_pay("============================================================\n\n");

            return [
                'result'   => 'success',
                'redirect' => $this->get_return_url($order),
            ];
        }
    }

    add_filter('woocommerce_payment_gateways', function($methods) {
        $methods[] = 'WC_Gateway_Manual_Pay';
        return $methods;
    });
}

// Eliminar fila "Acciones" del resumen de pedido
add_filter('woocommerce_my_account_my_orders_actions', function($actions, $order) {
    return []; // Vacía las acciones del pedido
}, 999, 2);
// Eliminar la fila "Acciones" en la página de agradecimiento (order-received)
add_filter('woocommerce_order_actions', '__return_empty_array', 999);
// Ocultar términos y política de privacidad del checkout
add_filter('woocommerce_checkout_show_terms', '__return_false');
add_filter('woocommerce_checkout_show_privacy_policy_text', '__return_false');

/* ===================================================================
 * CONFIGURACIÓN DEL MAIL AUTOMÁTICO
 * =================================================================== */

// Quita las imágenes de los productos en los correos
add_filter('woocommerce_email_order_items_args', function($args) {
    $args['show_image'] = false;
    return $args;
});

// Cambiar el texto del pie de los correos WooCommerce
add_filter('woocommerce_email_footer_text', function($text) {
    return 'Thanks again! If you need any help with your order, please contact us at <a href="mailto:shop@mcartworksstudio.com">shop@mcartworksstudio.com</a>.';
});

// [[[[woocommerce_review_order_before_submit]]]]
// Es un hook de acción de WooCommerce que se ejecuta justo antes del botón “Place order” (o “Realizar pedido”) en la página de checkout.
// En otras palabras: WooCommerce arma la sección de revisión de pedido (order review) con la lista de productos, total, impuestos, envío, etc.
// Antes de mostrar el botón de enviar pedido, dispara este hook. Te permite insertar contenido HTML, texto, botones o scripts justo ahí.
add_action('woocommerce_review_order_before_submit', 'boton_manual_estilo_paypal');

// =====================================================================================================
// // BOTÓN DE PAGO MANUAL ESTILO PAYPAL
// Esta función agrega un botón personalizado en el checkout de WooCommerce con estilo similar a PayPal.  
// - Inserta un <input hidden> para forzar que el método de pago sea 'manual_pay'.
// - Inserta un <button> visible para que el usuario haga submit.
// - Incluye CSS en línea para que el botón se vea grande, azul
//   y con efecto hover.
// - No reemplaza el botón original de WooCommerce, solo agrega uno extra.
// ======================================================================================================
function boton_manual_estilo_paypal() {
    ?>   
    <!-- Campo oculto que indica el método de pago a WooCommerce -->
    <input type="hidden" name="payment_method" value="manual_pay">
    <!-- Botón visible que envía el checkout -->
    <button type="submit" class="manual-pay-btn">Pay with PayPal</button>
    <?php
}

/* ===================================================================
 * GUARDAR CAMPOS PERSONALIZADOS DEL CARRITO EN EL PEDIDO
 * =================================================================== */
add_action('woocommerce_checkout_create_order_line_item', function($item, $cart_item_key, $values, $order) {
    if (isset($values['comic_balloon_text']) && !empty($values['comic_balloon_text'])) {
        $item->add_meta_data('Comic Balloon Text', $values['comic_balloon_text']);
    }

    if (isset($values['color_frame']) && !empty($values['color_frame'])) {
        $item->add_meta_data('Frame Color', ucfirst($values['color_frame']));
    }
}, 10, 4);

/* ===================================================================
 * GUARDAR LOS DATOS PERSONALIZADOS CUANDO SE ACTUALIZA EL CARRITO
 * =================================================================== */
add_action('woocommerce_cart_item_name', function($name, $cart_item, $cart_item_key) {
    if (!empty($cart_item['comic_balloon_text'])) {
        $name .= '<br><small><strong>Comic Balloon Text:</strong> ' . esc_html($cart_item['comic_balloon_text']) . '</small>';
    }
    if (!empty($cart_item['color_frame'])) {
        $name .= '<br><small><strong>Frame Color:</strong> ' . esc_html($cart_item['color_frame']) . '</small>';
    }
    return $name;
}, 10, 3);

// [[[[woocommerce_get_item_data]]]]
// woocommerce_get_item_data es un filtro (hook) que WooCommerce usa para modificar o añadir información adicional que se muestra en el resumen de los productos dentro del carrito y en las páginas de // checkout.
add_filter('woocommerce_get_item_data', 'mostrar_campos_personalizados_en_resumen_pedido', 10, 2 );

/* ============================================================================================================
 * Función que muestra los campos personalizados 'comic_balloon_text' y 'color_frame' en el resumen del pedido.
 * Agrega o elimina campos personalizados en el resumen del pedido (checkout, emails, etc.) 
 * - Detecta si el producto tiene globo o es un bundle
 * - Elimina el campo "Comic Balloon Text" en bundles
 * - Agrega el campo "Comic Balloon Text" si el producto tiene globo
 * - Siempre agrega el campo "Frame Color"
 * ============================================================================================================ */
function mostrar_campos_personalizados_en_resumen_pedido($item_data, $cart_item){
	
    $product = $cart_item['data'] ?? null;
    // obtener id real (variación -> parent)
    $product_id = 0;
    if($product){
        $product_id = $product->get_id();
        if( method_exists($product, 'is_type') && $product->is_type('variation') ){
            $parent = $product->get_parent_id();
            if($parent) $product_id = $parent;
        }
    } else {
        $product_id = $cart_item['product_id'] ?? 0;
    }

    $product_name = $product ? $product->get_name() : '';
    $tiene_globo = (bool) get_field('tiene_globo', $product_id);
    $es_bundle = stripos(strtolower($product_name), 'bundle') !== false;

    // Eliminar Comic Balloon Text en bundles
    if($es_bundle){
        $item_data = array_values(array_filter($item_data, function($i){
            return strtolower(trim($i['key'])) !== 'comic balloon text';
        }));
    }

    // Agregar Comic Balloon Text solo si tiene globo y no es bundle
    if(!$es_bundle && $tiene_globo){
        $valor = $cart_item['comic_balloon_text'] ?? 'default';
        $item_data[] = [
            'key' => 'Comic Balloon Text',
            'value' => wc_clean($valor),
        ];
    }

    // Frame Color siempre
    $frame_color = $cart_item['color_frame'] ?? 'default';
    $item_data[] = [
        'key' => 'Frame Color',
        'value' => wc_clean($frame_color),
    ];

    return $item_data;
}

// [[[[woocommerce_cart_item_class]]]]
// El filtro woocommerce_cart_item_class sirve para modificar o agregar clases CSS al elemento HTML <tr class="cart_item"> que WooCommerce genera automáticamente 
// en las tablas del carrito y del checkout. En otras palabras:
// Cada producto del carrito se renderiza como una fila <tr class="cart_item ...">.
// El filtro te permite inyectar clases personalizadas a esa fila, usando la información del producto antes de que WooCommerce la imprima en el HTML.
add_filter('woocommerce_cart_item_class', 'wpo_agregar_clases_cart_item', 10, 3);

// ============================================================================================
// Función que agrega clases personalizadas al <tr class="cart_item"> en el checkout y carrito.
// Sirve para que el JS pueda identificar fácilmente los productos con globo o los bundles.
// - Añade la clase "tiene-globo" si el producto tiene el campo ACF "tiene_globo" activado.
// - Añade la clase "is-bundle" si el nombre del producto contiene la palabra "bundle".
// ============================================================================================
function wpo_agregar_clases_cart_item($classes, $cart_item, $cart_item_key){
    $product = $cart_item['data'] ?? null;
    $product_id = 0;

    // Obtener ID correcto, incluso si es una variación
    if($product){
        $product_id = $product->get_id();
        if(method_exists($product, 'is_type') && $product->is_type('variation')){
            $parent = $product->get_parent_id();
            if($parent) $product_id = $parent;
        }
    } else {
        $product_id = $cart_item['product_id'] ?? 0;
    }

    $product_name = $product ? $product->get_name() : '';
    $tiene_globo = (bool) get_field('tiene_globo', $product_id);

    // Agregar clases según condiciones
    if($tiene_globo){
        $classes .= ' tiene-globo';
    }
    if(stripos($product_name, 'bundle') !== false){
        $classes .= ' is-bundle';
    }

    return $classes;
}

// [[[[wp_footer]]]]
add_action('wp_footer', 'wpo_footer_limpieza_checkout');

/* ====================================================================================================================================
 * Función que inserta un script en el pie de página del checkout para limpiar y reordenar elementos en la tabla de resumen del pedido. 
 * Propósito:
 * - Mejorar la presentación de los productos durante el checkout.
 * - Ocultar información innecesaria en los productos tipo *bundle* o que no tienen globo.
 * - Mover el texto de cantidad junto al título del producto
 * - Limpiar los nombres de productos en el checkout: 
 * - Ocultar "Comic Balloon Text" en bundles o productos sin globo
 * - Eliminar espacios o saltos de línea innecesarios	 
 * ==================================================================================================================================== */
function wpo_footer_limpieza_checkout(){
    if(!is_checkout() ) return; ?>
    
    <script>
    function wpo_limpiarBundlesYMoverCantidad(){
        document.querySelectorAll('.woocommerce-checkout-review-order-table .cart_item').forEach(function(row, idx){
            const nameCell = row.querySelector('.product-name');
            if(!nameCell) return;

            // 1️⃣ Detectar si es bundle (por clase o texto)
            const isBundle = row.classList.contains('is-bundle') || /bundle/i.test(nameCell.innerText);

            // 2️⃣ Detectar si tiene globo (por clase)
            const tieneGlobo = row.classList.contains('tiene-globo');

            // 3️⃣ Log para depuración
            // console.log(`Fila ${idx + 1}: isBundle=${isBundle}, tieneGlobo=${tieneGlobo}, nombre=${nameCell.innerText.trim()}`);

            // 4️⃣ Eliminar Comic Balloon Text si es bundle o no tiene globo
            nameCell.querySelectorAll('small').forEach(function(small){
                if (/Comic Balloon Text:/i.test(small.innerText) && (isBundle || !tieneGlobo)) {
                    let prev = small.previousElementSibling;
                    if (prev && prev.tagName.toLowerCase() === 'br') prev.remove();
                    small.remove();
                }
            });

            // 5️⃣ Mover cantidad al lado del título
            const quantity = nameCell.querySelector('.product-quantity');
            if (quantity) {
                let firstTextNode = Array.from(nameCell.childNodes)
                    .find(n => n.nodeType === Node.TEXT_NODE && n.textContent.trim());
                if (firstTextNode) {
                    nameCell.insertBefore(quantity, firstTextNode.nextSibling);
                }
            }

            // 6️⃣ Limpiar nodos de texto vacíos sobrantes
            Array.from(nameCell.childNodes).forEach(function(node){
                if (node.nodeType === Node.TEXT_NODE && !node.textContent.trim()) node.remove();
            });
        });
    }

    document.addEventListener('DOMContentLoaded', wpo_limpiarBundlesYMoverCantidad);
    jQuery(document.body).on('updated_checkout', wpo_limpiarBundlesYMoverCantidad);
    </script>
<?php }

// [[[[woocommerce_order_item_get_formatted_meta_data]]]]
/* Filtro que permite modificar o eliminar los metadatos formateados (los “atributos” que aparecen debajo del nombre del producto)
antes de que WooCommerce los muestre en:
	- la página de pedido recibido / gracias (/checkout/order-received/...),
	- la página de "Ver pedido" del usuario (en “Mi cuenta”),
	- y los correos de confirmación (tanto al cliente como al administrador).
En otras palabras, controla qué información aparece debajo del producto en esos contextos. */
add_filter('woocommerce_order_item_get_formatted_meta_data', 'wpo_filtrar_meta_en_pedido', 10, 2);

/* =================================================================================================================
 * Función que filtra los metadatos visibles en la tabla de detalles del pedido (página "order received" y correos). 
 * Objetivo:
 * - Evitar que aparezca el campo "Comic Balloon Text" en productos que no deberían mostrarlo.
 * - Se aplica tanto en la página de confirmación del pedido (/checkout/order-received/)
 *   como en los correos automáticos de WooCommerce (cliente y administrador). 
 * Lógica:
 * - Obtiene el producto asociado a cada línea del pedido.
 * - Comprueba los campos ACF personalizados:
 *     - 'es_bundle' → indica si el producto pertenece a un paquete (bundle).
 *     - 'tiene_globo' → indica si el producto utiliza texto de globo.
 * - Si el producto es un bundle o no tiene globo, elimina del arreglo de metadatos
 *   cualquier línea cuya clave sea "Comic Balloon Text". 
 * Dependencias:
 * - Requiere que los campos ACF 'es_bundle' y 'tiene_globo' estén correctamente configurados
 *   para cada producto en el administrador de WordPress.
 * ================================================================================================================= */
function wpo_filtrar_meta_en_pedido($formatted_meta, $item) {
    $product = $item->get_product();
    if (!$product) return $formatted_meta;

    $product_id  = $product->get_id();
    $is_bundle   = get_field('es_bundle', $product_id); // tu campo ACF que marca si es bundle
    $tiene_globo = get_field('tiene_globo', $product_id);

    // Si el producto es bundle o no tiene globo, filtramos el metadato
    if ($is_bundle || !$tiene_globo) {
        $formatted_meta = array_filter($formatted_meta, function($meta) {
            return stripos($meta->display_key, 'Comic Balloon Text') === false;
        });
    }

    return $formatted_meta;
}

// [[[[wp_footer]]]]
add_action('wp_footer', 'wpo_checkout_gradiente_titulos');

/* =====================================================================================
* Función que recorre todos los nombres de producto en la tabla del resumen del checkout
* para insertarles un <span> con clase .product-title-gradient. 
* Esto permite aplicar estilos CSS con gradiente (por ejemplo, color degradado)
* sin afectar los elementos hijos como la cantidad o variaciones.
* ===================================================================================== */
function wpo_checkout_gradiente_titulos() {
    if (!is_checkout()) return; // Solo en checkout ?>
    
    <script>
    function aplicarGradienteTitulos(){
        document.querySelectorAll('.woocommerce-checkout-review-order-table .cart_item td.product-name').forEach(function(td){
            // Evitar duplicar
            if(td.querySelector('.product-title-gradient')) return;

            let firstTextNode = Array.from(td.childNodes)
                .find(n => n.nodeType === Node.TEXT_NODE && n.textContent.trim());
            
            if(firstTextNode){
                // Crear el span que tendrá gradiente
                const span = document.createElement('span');
                span.className = 'product-title-gradient';
                span.textContent = firstTextNode.textContent.trim();

                // Reemplazar el nodo de texto original por el span
                td.replaceChild(span, firstTextNode);

                // Mover la cantidad dentro del span
                const quantity = td.querySelector('.product-quantity');
                if(quantity){
                    span.appendChild(document.createTextNode(' ')); // separador
                    span.appendChild(quantity);
                }
            }
        });
    }

    document.addEventListener('DOMContentLoaded', aplicarGradienteTitulos);
    jQuery(document.body).on('updated_checkout', aplicarGradienteTitulos);
    </script>

<?php }

// [[[[wp_footer]]]]
add_action('wp_footer', 'wpo_checkout_gradiente_labels');

/* ====================================================================================
* Función que recorre las etiquetas (strong) dentro de los elementos <small> del nombre
* de cada producto en el resumen del pedido del checkout para agregar la clase CSS 
* "field-label" a esos <strong> permitiendo aplicar estilos con gradiente 
* ===================================================================================== */
function wpo_checkout_gradiente_labels() {
    if (!is_checkout()) return; ?>
    
    <script>
    function aplicarGradienteLabels(){
        document.querySelectorAll('.woocommerce-checkout-review-order-table .cart_item td.product-name small strong').forEach(function(strong){
            if(strong.classList.contains('field-label')) return;
            strong.classList.add('field-label');
        });
    }

    document.addEventListener('DOMContentLoaded', aplicarGradienteLabels);
    jQuery(document.body).on('updated_checkout', aplicarGradienteLabels);
    </script>

<?php }

// [[[[woocommerce_before_checkout_billing_form]]]]
// Se ejecuta justo antes de que WooCommerce renderice los campos de facturación (nombre, apellido, dirección, email, etc.) 
// en el formulario de pago (checkout/form-billing.php).
add_action('woocommerce_before_checkout_billing_form', 'wpo_mover_titulo_facturacion_y_agregar_artwork_field');

// ====================================================================================
// Función que agrega un campo personalizado "Nombre para tus artworks" en el checkout.
// - Mueve el título "Detalles de facturación" justo debajo de ese campo.
// - Se ejecuta automáticamente antes del formulario de facturación de WooCommerce.
// - NO requiere validar con is_checkout(), ya que el hook solo corre en checkout.
// ====================================================================================
function wpo_mover_titulo_facturacion_y_agregar_artwork_field($checkout) {
    // === Agregar el nuevo campo de texto ===
    echo '<div id="artwork_name_field" style="margin-bottom:18px;">';
    echo '<h3>Name for your artworks</h3>'; // Traducido al inglés
	
    woocommerce_form_field('artwork_name', array(
        'type'        => 'text',
        'class'       => array('artwork-name form-row-wide'),
        'label'       => 'Enter the name that will appear on your artworks', // Traducido al inglés
        /*'placeholder' => 'e.g.: My Awesome Artwork', // Traducido al inglés */
        'required'    => true		 
    ), $checkout->get_value('artwork_name'));
	
    echo '</div>';
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var artworkDiv = document.getElementById('artwork_name_field');
        if (!artworkDiv) return;

        // Buscar el primer título dentro de billing fields
        var billingHeading = document.querySelector('.woocommerce-billing-fields h3');
        if (!billingHeading) return;

        // Mover el título justo después del campo de artwork
        artworkDiv.parentNode.insertBefore(billingHeading, artworkDiv.nextSibling);
    });
    </script>
    <?php
}

// Quita todo el área de notas del pedido del checkout.
add_filter( 'woocommerce_enable_order_notes_field', '__return_false' );

// ====================================================================================
// TRADUCCIONES
// ====================================================================================

/* ===================================================================================== 
 * Función que traduce automáticamente los mensajes de error del checkout de WooCommerce
 * del español al inglés, tanto en el bloque principal de errores como en los
 * mensajes inline que aparecen debajo de los campos de facturación.
 * FUNCIONALIDAD:
 * - Detecta dinámicamente cuando WooCommerce inserta mensajes de error.
 * - Reemplaza los textos en español por sus equivalentes en inglés.
 * - Soporta errores del tipo:
 *     - <ul class="woocommerce-error">...</ul>
 *     - <p class="checkout-inline-error-message">...</p>
 * - También ejecuta la traducción luego de presionar el botón "Pay with PayPal".
 * ===================================================================================== */

add_action('wp_footer', 'traducir_errores_checkout');
function traducir_errores_checkout() {
    if (!is_checkout()) return;
    ?>
    <script>
    jQuery(function($){

        // Lista de reemplazos: cada objeto contiene el texto a buscar y su traducción
        const replacements = [
            { find: 'Facturación First Name', replace: 'Billing First Name' },
            { find: 'Facturación Email',      replace: 'Billing Email' },
            { find: 'es un campo requerido.', replace: 'is a required field.' }
        ];

        /**
         * Traducir todos los mensajes de error visibles en el checkout.
         * Afecta tanto los del bloque superior (.woocommerce-error)
         * como los mensajes inline debajo de los inputs (.checkout-inline-error-message).
         */
        function traducirTodosLosErrores() {
            // --- Bloque principal de errores ---
            $('.woocommerce-error li').each(function(){
                let html = $(this).html();
                replacements.forEach(r => {
                    const regex = new RegExp(r.find, 'gi');
                    html = html.replace(regex, r.replace);
                });
                $(this).html(html.trim());
            });

            // --- Mensajes inline bajo los campos ---
            $('.checkout-inline-error-message').each(function(){
                let html = $(this).html();
                replacements.forEach(r => {
                    const regex = new RegExp(r.find, 'gi');
                    html = html.replace(regex, r.replace);
                });
                $(this).html(html.trim());
            });
        }

        // Ejecutar al cargar la página (por si ya existen errores visibles)
        traducirTodosLosErrores();

        // Observar el DOM para detectar si WooCommerce agrega nuevos errores
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(m => {
                m.addedNodes.forEach(node => {
                    if (node.nodeType === 1 && (
                        $(node).find('.woocommerce-error, .checkout-inline-error-message').length ||
                        $(node).is('.woocommerce-error, .checkout-inline-error-message')
                    )) {
                        setTimeout(traducirTodosLosErrores, 50);
                    }
                });
            });
        });

        // Activar el observador sobre todo el cuerpo del documento
        observer.observe(document.body, { childList: true, subtree: true });

        // Forzar traducción también luego de presionar el botón "Pay with PayPal"
        $(document).on('click', '.manual-pay-btn', function() {
            setTimeout(traducirTodosLosErrores, 500);
        });
    });
    </script>
    <?php
}

// [[[[wp_footer]]]]
add_action('wp_footer', 'traducirTextosCheckout');

// =========================================================================================
// Función que traduce textos visibles del checkout de WooCommerce al inglés.
// Se ejecuta al cargar la página y cada vez que WooCommerce actualiza el checkout vía AJAX.
// =========================================================================================
function traducirTextosCheckout() {
    if (is_checkout()) : ?>
        <script>
        document.addEventListener('DOMContentLoaded', function() {

            // Función interna que traduce los textos del checkout
            function translateCheckoutTexts() {
                // Cambiar "Tu pedido"
                const orderHeading = document.getElementById('order_review_heading');
                if (orderHeading) orderHeading.textContent = 'Your Order';

                // Cambiar "Producto"
                const productTh = document.querySelector('th.product-name');
                if (productTh) productTh.textContent = 'Product';

                // Cambiar "Detalles de facturación"
                const billingHeadings = document.querySelectorAll('.woocommerce-billing-fields h3');
                billingHeadings.forEach(function(h3) {
                    if (h3.textContent.trim() === 'Detalles de facturación') {
                        h3.textContent = 'Billing Details';
                    }
                });
            }

            // Ejecutar la traducción al cargar la página
            translateCheckoutTexts();

            // Ejecutar la traducción cada vez que WooCommerce actualiza el checkout vía AJAX
            jQuery(document.body).on('updated_checkout', function() {
                translateCheckoutTexts();
            });

        });
        </script>
    <?php endif;
}

// [[[[wp_footer]]]]
add_action('wp_footer', 'traducir_textos_pedido_recibido');

/* ============================================================================================
 * Función que traduce dinámicamente los textos de la página "Pedido recibido" (order-received)
 * para mantener consistencia bilingüe (ES/EN) sin modificar plantillas WooCommerce.
 * ============================================================================================ */
function traducir_textos_pedido_recibido() {
    if (!is_order_received_page()) return;
    ?>
    <script>
    jQuery(function($){

        const traducciones = [
            { find: 'Gracias. Tu pedido ha sido recibido.', replace: 'THANK YOU. YOUR ORDER HAS BEEN RECEIVED.' },
            { find: 'Número de pedido:', replace: 'ORDER NUMBER:' },
            { find: 'Fecha:', replace: 'DATE:' },
            { find: 'Correo electrónico:', replace: 'EMAIL:' },
            { find: 'Método de pago:', replace: 'PAYMENT METHOD:' },
            { find: 'Detalles del pedido', replace: 'ORDER DETAILS' },
            { find: 'Dirección de facturación', replace: 'BILLING ADDRESS' },
            { find: 'Subtotal:', replace: 'SUBTOTAL:' },
            { find: 'Total:', replace: 'TOTAL:' },
            { find: 'Producto', replace: 'PRODUCT' },
            { find: 'Total', replace: 'TOTAL' },
        ];

        function traducirCheckoutRecibido() {
            traducciones.forEach(t => {
                const regex = new RegExp(t.find, 'gi');
                $('body').find('*').contents().filter(function(){
                    return this.nodeType === 3 && regex.test(this.nodeValue);
                }).each(function(){
                    this.nodeValue = this.nodeValue.replace(regex, t.replace);
                });
            });
        }

        traducirCheckoutRecibido();

        // En caso de que WooCommerce cargue algo dinámico después
        // const observer = new MutationObserver(() => traducirCheckoutRecibido());
        // observer.observe(document.body, { childList: true, subtree: true });
    });
    </script>
    <?php
}

// ====================================================================================
// VALIDACIONES
// ====================================================================================

// Límite de caracteres para artwork_name
add_action('woocommerce_checkout_process', function() {
    if ( empty($_POST['artwork_name']) ) {
        wc_add_notice('Please enter a name for your artworks.', 'error'); // Traducido
    } elseif (strlen($_POST['artwork_name']) > 32) {
        wc_add_notice('The name for your artworks cannot exceed 32 characters.', 'error'); // Traducido
    }
});

// Límite de caracteres para nombre para la facturación
add_action('woocommerce_checkout_process', function() {
    if (!empty($_POST['billing_first_name']) && strlen($_POST['billing_first_name']) > 50) {
        wc_add_notice('Billing first name cannot exceed 50 characters.', 'error'); // Traducido
    }
});

// Validación del mail
add_action('woocommerce_checkout_process', function() {
    $email = '';

    // Capturar el email del checkout
    if (!empty($_POST['billing_email'])) {
        $email = sanitize_text_field($_POST['billing_email']);
    } elseif (!empty($_POST['billing']['email'])) {
        $email = sanitize_text_field($_POST['billing']['email']);
    }

    // 1) Longitud máxima
    if (!empty($email) && strlen($email) > 100) {
        wc_add_notice('Email cannot exceed 100 characters.', 'error'); // Traducido
        return;
    }

    // 2) Formato básico con filter_var
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        wc_add_notice('Please enter a valid email address.', 'error'); // Traducido
        return;
    }

    // 3) Extra: asegurar que haya al menos un punto en la parte de dominio (ej: example.com)
    $domain = substr(strrchr($email, "@"), 1);
    if ($domain === false || strpos($domain, '.') === false) {
        wc_add_notice('The email domain is not valid.', 'error'); // Traducido
        return;
    }

    // 4) Verificar existencia DNS del dominio (MX o A). Esto filtra 'hotmail.comweq'
    $has_dns = false;

    // preferir getmxrr si está disponible (MX records)
    if (function_exists('getmxrr')) {
        $mxhosts = [];
        if (@getmxrr($domain, $mxhosts) && !empty($mxhosts)) {
            $has_dns = true;
        }
    }

    // fallback a checkdnsrr (MX o A)
    if (! $has_dns && function_exists('checkdnsrr')) {
        if (@checkdnsrr($domain, 'MX') || @checkdnsrr($domain, 'A')) {
            $has_dns = true;
        }
    }

    // último recurso: intentar resolver con gethostbyname (no perfecto, pero ayuda)
    if (! $has_dns) {
        $resolved = @gethostbyname($domain);
        if ($resolved !== $domain && $resolved !== false) {
            $has_dns = true;
        }
    }

    if (! $has_dns) {
        wc_add_notice('The email domain does not appear to exist (check that it is correctly typed).', 'error'); // Traducido
        return;
    }

    // si llega acá, el email pasa las validaciones
});

// Limite de caracteres desde JS
add_action('wp_footer', function() {
    if (is_checkout()) {
        ?>
        <script>
        document.addEventListener('DOMContentLoaded', function(){
          const art = document.querySelector('#artwork_name');
          if(art) art.setAttribute('maxlength', '32');

          const name = document.querySelector('#billing_first_name');
          if(name) name.setAttribute('maxlength', '50');

          const mail = document.querySelector('#billing_email');
          if(mail) mail.setAttribute('maxlength', '100');
			
		  const orderComments = document.querySelector('#order_comments');
          if (orderComments) {
                orderComments.setAttribute('maxlength', '200');
                
                // Crear contador de caracteres
                const counter = document.createElement('small');
                counter.style.display = 'block';
                counter.style.marginTop = '4px';                
                counter.style.color = '#fff'; // Cambialo si necesitas otro color
                counter.textContent = '0 / 200 characters';
                orderComments.parentNode.appendChild(counter);

                // Actualizar contador en tiempo real
                orderComments.addEventListener('input', () => {
                    counter.textContent = `${orderComments.value.length} / 200 characters`;
                });
          }
        });
        </script>
        <?php
    }
});

// Guardar en el pedido 
add_action('woocommerce_checkout_create_order', function($order, $data) {
    if ( isset($_POST['artwork_name']) && $_POST['artwork_name'] !== '' ) {
        $order->update_meta_data('Artworks Name', sanitize_text_field($_POST['artwork_name'])); // Traducido
    }
}, 10, 2);

// Mostrar en admin 
add_action('woocommerce_admin_order_data_after_billing_address', function($order){
    $artwork_name = $order->get_meta('Artworks Name'); // Asegurarse que coincide con el meta guardado
    if($artwork_name){
        echo '<p><strong>Name for the artworks:</strong> ' . esc_html($artwork_name) . '</p>'; // Traducido
    }
});

// Asegura que el campo de email sea tratado correctamente como un input de tipo email y tenga la validación nativa del navegador
add_filter('woocommerce_checkout_fields', function($fields) {
    $fields['billing']['billing_email']['type'] = 'email';
    return $fields;
});

// [[[[wp_footer]]]]
add_action('wp_footer', 'mostrar_mensaje_y_traducir_meses', 100);

/* ==============================================================================================
 * Función que agrega un mensaje adicional dentro del párrafo de confirmación de pedido
 * en la página "thank you" de WooCommerce y convierte nombres de meses
 * de español a inglés únicamente en esa página.
 * Comportamiento:
 * 1) Verifica que estamos en la página de "order-received".
 * 2) Inserta un <span> con el mensaje dentro de <p class="woocommerce-thankyou-order-received">.
 * 3) Reemplaza todos los nombres de meses en español por su equivalente en inglés
 *    dentro del contenedor principal de la orden para que las fechas se muestren en inglés.
 * 4) Aplica estilos sencillos al mensaje para que se integre en el recuadro.
 * 5) No genera ningún log en la consola para evitar errores visibles al usuario.
 * ============================================================================================== */
function mostrar_mensaje_y_traducir_meses() {
    // Verificamos que existe la función de WooCommerce y que estamos en la página correcta
    if (!function_exists('is_wc_endpoint_url') || ! is_wc_endpoint_url('order-received') ) {
        return;
    }

    // Mensaje que se añadirá al párrafo de confirmación
    $extra_text = "We have sent you an email with our PayPal details.<br>
                   Once your payment is received and verified, you’ll receive a confirmation email. Your artworks will then be delivered within 24 hours of payment confirmation.<br><br>
                   If you don’t receive our email, please check your spam/junk folder or contact us using the contact information in the <strong>Our Services</strong> section.";
	
    // Insertamos un pequeño script y estilos en el footer
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        try {
            // 1) Insertar mensaje dentro del <p class="woocommerce-thankyou-order-received">
            var p = document.querySelector('.woocommerce-thankyou-order-received');
            if (p) {
                var span = document.createElement('span');
                span.className = 'woocommerce-thankyou-extra';
                span.style.display = 'block';
                span.style.marginTop = '8px';
                span.style.fontSize = '0.95rem';
                span.innerHTML = <?php echo wp_json_encode($extra_text); ?>;
                p.appendChild(span);
            }

            // 2) Reemplazar nombres de meses en español por inglés dentro del contenedor de la orden
            var monthsMap = {
                'enero':'January','febrero':'February','marzo':'March','abril':'April',
                'mayo':'May','junio':'June','julio':'July','agosto':'August',
                'septiembre':'September','octubre':'October','noviembre':'November','diciembre':'December'
            };

            var container = document.querySelector('.woocommerce-order-overview') 
                         || document.querySelector('.woocommerce-order') 
                         || document.querySelector('.woocommerce-order-details')
                         || document.querySelector('body');

            if (container) {
                var html = container.innerHTML;
                Object.keys(monthsMap).forEach(function(spanish) {
                    var english = monthsMap[spanish];
                    var re = new RegExp('\\b' + spanish + '\\b', 'ig'); // palabra completa, case-insensitive
                    html = html.replace(re, english);
                });
                container.innerHTML = html;
            }
        } catch (e) {
            // No hacer nada si ocurre un error
        }
    });
    </script>

    <style>
    /* Estilo sencillo para que el mensaje quede integrado dentro del recuadro de thank you */
    .woocommerce-thankyou-order-received .woocommerce-thankyou-extra {
        color: #ffffff;
        text-align: center;
        opacity: .95;
        line-height: 1.2;
        max-width: 1000px;
        margin: 10px auto 0;
    }
    </style>
    <?php
}

// ################################################################################################################################################
// ################################################################################################################################################
// ################################################################################################################################################
// 											GALLERY
// ################################################################################################################################################
// ################################################################################################################################################
// ################################################################################################################################################

// [[[[wp_head]]]]
add_action('wp_head', 'cnc_gallery_parallax');

/* ========================================================================================================
 * Galería de videos GDI con main y alternativos:
 * - Solo un video en toda la galería puede sonar a la vez
 * - Los demás siguen corriendo en loop pero muteados
 * - Los íconos 🔊/🔇 se sincronizan globalmente
 * ======================================================================================================== */
function cnc_gallery_parallax() {
    if (!is_page('gallery')) return;

	$faction = $_COOKIE['faction'] ?? 'gdi';
	
    if ($faction === 'gdi') {
			$body_bg = 'linear-gradient(270deg, #001f3f, #003f7f, #005bbb, #001f3f)'; // azul profundo eléctrico			
			$loader_class = 'video-loader gdi';		
			$videos = [
			['main'=>'/videos/gdi-gallery/video1_main.mp4','alt'=>['/videos/gdi-gallery/video1_alt_one.mp4','/videos/gdi-gallery/video1_alt_two.mp4','/videos/gdi-gallery/video1_alt_three.mp4','/videos/gdi-gallery/video1_alt_four.mp4','/videos/gdi-gallery/video1_alt_five.mp4'],'sound'=>true],
			['main'=>'/videos/gdi-gallery/video2_main.mp4','alt'=>['/videos/gdi-gallery/video2_alt_one.mp4','/videos/gdi-gallery/video2_alt_two.mp4'],'sound'=>true],
			['main'=>'/videos/gdi-gallery/video3_main.mp4','alt'=>['/videos/gdi-gallery/video3_alt_one.mp4'],'sound'=>false],
			['main'=>'/videos/gdi-gallery/video4_main.mp4','alt'=>['/videos/gdi-gallery/video4_alt_one.mp4','/videos/gdi-gallery/video4_alt_two.mp4'],'sound'=>false],
			['main'=>'/videos/gdi-gallery/video5_main.mp4','alt'=>['/videos/gdi-gallery/video5_alt_one.mp4','/videos/gdi-gallery/video5_alt_two.mp4','/videos/gdi-gallery/video5_alt_three.mp4'],'sound'=>false],
			['main'=>'/videos/gdi-gallery/video6_main.mp4','alt'=>['/videos/gdi-gallery/video6_alt_one.mp4'],'sound'=>false],
			['main'=>'/videos/gdi-gallery/video7_main.mp4','alt'=>['/videos/gdi-gallery/video7_alt_one.mp4','/videos/gdi-gallery/video7_alt_two.mp4','/videos/gdi-gallery/video7_alt_three.mp4'],'sound'=>false],		
			['main'=>'/videos/gdi-gallery/video8_main.mp4','alt'=>['/videos/gdi-gallery/video8_alt_one.mp4'],'sound'=>true],		
			['main'=>'/videos/gdi-gallery/video9_main.mp4','alt'=>['/videos/gdi-gallery/video9_alt_one.mp4','/videos/gdi-gallery/video9_alt_two.mp4','/videos/gdi-gallery/video9_alt_three.mp4','/videos/gdi-gallery/video9_alt_four.mp4'],'sound'=>true],
			['main'=>'/videos/gdi-gallery/video10_main.mp4','alt'=>['/videos/gdi-gallery/video10_alt_one.mp4','/videos/gdi-gallery/video10_alt_two.mp4'],'sound'=>true],
			['main'=>'/videos/gdi-gallery/video11_main.mp4','alt'=>['/videos/gdi-gallery/video11_alt_one.mp4'],'sound'=>true],
			['main'=>'/videos/gdi-gallery/video12_main.mp4','alt'=>['/videos/gdi-gallery/video12_alt_one.mp4'],'sound'=>false]
		];
    } else if ($faction === 'nod') {
        $body_bg = 'linear-gradient(270deg, #2a0000, #550000, #2a0000)';						
		$loader_class = 'video-loader nod';
		$videos = [
            ['main'=>'/videos/nod-gallery/video1_main.mp4','alt'=>['/videos/nod-gallery/video1_alt_one.mp4','/videos/nod-gallery/video1_alt_two.mp4','/videos/nod-gallery/video1_alt_three.mp4','/videos/nod-gallery/video1_alt_four.mp4','/videos/nod-gallery/video1_alt_five.mp4','/videos/nod-gallery/video1_alt_six.mp4'],'sound'=>false],
            ['main'=>'/videos/nod-gallery/video2_main.mp4','alt'=>['/videos/nod-gallery/video2_alt_one.mp4','/videos/nod-gallery/video2_alt_two.mp4'],'sound'=>true],
            ['main'=>'/videos/nod-gallery/video3_main.mp4','alt'=>['/videos/nod-gallery/video3_alt_one.mp4','/videos/nod-gallery/video3_alt_two.mp4'],'sound'=>true],
            ['main'=>'/videos/nod-gallery/video4_main.mp4','alt'=>['/videos/nod-gallery/video4_alt_one.mp4'],'sound'=>true],
			['main'=>'/videos/nod-gallery/video5_main.mp4','alt'=>['/videos/nod-gallery/video5_alt_one.mp4','/videos/nod-gallery/video5_alt_two.mp4'],'sound'=>true],										['main'=>'/videos/nod-gallery/video6_main.mp4','alt'=>['/videos/nod-gallery/video6_alt_one.mp4'],'sound'=>false],			
			['main'=>'/videos/nod-gallery/video7_main.mp4','alt'=>['/videos/nod-gallery/video7_alt_one.mp4','/videos/nod-gallery/video7_alt_two.mp4'],'sound'=>true],							
			['main'=>'/videos/nod-gallery/video8_main.mp4','alt'=>['/videos/nod-gallery/video8_alt_one.mp4','/videos/nod-gallery/video8_alt_two.mp4'],'sound'=>true],										['main'=>'/videos/nod-gallery/video9_main.mp4','alt'=>['/videos/nod-gallery/video9_alt_one.mp4','/videos/nod-gallery/video9_alt_two.mp4','/videos/nod-gallery/video9_alt_three.mp4','/videos/nod-gallery/video9_alt_four.mp4'],'sound'=>true],																		
			['main'=>'/videos/nod-gallery/video10_main.mp4','alt'=>['/videos/nod-gallery/video10_alt_one.mp4','/videos/nod-gallery/video10_alt_two.mp4'],'sound'=>true],						
            ['main'=>'/videos/nod-gallery/video11_main.mp4','alt'=>['/videos/nod-gallery/video11_alt_one.mp4'],'sound'=>true],						
            ['main'=>'/videos/nod-gallery/video12_main.mp4','alt'=>['/videos/nod-gallery/video12_alt_one.mp4'],'sound'=>false],
        ];
    }	
	
    ?>

	<style>		
		/* ===========================
           BACKGROUND ANIMADO 
           ========================== */
        body {
          background: <?= $body_bg ?>;
          background-size: 200% 200%;
          animation: gradientShift 20s ease infinite;
          overflow-x: hidden;
          font-family: Orbitron, sans-serif;
        }
        @keyframes gradientShift {
          0% { background-position: 0% 50%; }
          50% { background-position: 100% 50%; }
          100% { background-position: 0% 50%; }
        }		
		#main-container {
			min-height: auto !important;
			height: auto !important;
	  	}		
  		main.site-main {
    		padding-bottom: 0 !important;
    		margin-bottom: 0 !important;
  		}		
  		#footer {
    		margin-top: 0 !important;
  		}
	</style>

	<div class="gdi-gallery-wrapper">		
		<!--<div class="grid-3d"></div>    -->
		<!-- <div class="hud-circle"></div> -->
		<div class="gallery-title">GALLERY</div>
		<div class="scan-line"></div>    
		<div class="cnc-gallery">
			<?php foreach($videos as $v): ?>
			<div class="frame-container" data-alt='<?= json_encode($v['alt']) ?>' data-sound='<?= $v['sound'] ? 1 : 0 ?>'>
				<div class="video-wrap">
					<video class="vid-main" src="<?= $v['main'] ?>" autoplay muted loop playsinline preload="auto"></video>
				</div>
				<div class="frame"></div>
				<div class="video-controls">
					<span class="icon-eye" title="Ver principal">👁️</span>
					<?php foreach($v['alt'] as $index => $alt): ?>
						<span class="icon-video" data-alt="<?= $index ?>" title="Ver alternativo <?= $index+1 ?>">🎥</span>
					<?php endforeach; ?>
					<?php if ($v['sound']): ?>
						<span class="icon-sound" title="Activar/Desactivar sonido">🔊</span>
					<?php endif; ?>
				</div>
			</div>
			<?php endforeach; ?>
		</div>
	</div>
	
    <script>
		
    /* ================================
       GALERÍA DE VIDEOS — LÓGICA
       ================================ */

    function updateAllSoundIcons(){
        document.querySelectorAll('.frame-container').forEach(container=>{
            const ctrl = container.querySelector('.video-controls');
            const soundBtn = ctrl?.querySelector('.icon-sound');
            if(!soundBtn) return;
            const visibleVideo = container.querySelector('video.visible');
            if(visibleVideo){
                soundBtn.textContent = visibleVideo.muted ? '🔇' : '🔊';
            }
        });
    }
		
    function muteAllGlobal(exceptVideo){
        document.querySelectorAll('.frame-container video').forEach(v=>{
            if(v !== exceptVideo){
                v.muted = true;
            }
        });
        updateAllSoundIcons();
    }

    document.querySelectorAll('.frame-container').forEach(container=>{
        const angle=(Math.random()*12-6).toFixed(2)+'deg';
        container.style.setProperty('--rot-angle',angle);
    });

    // Loader inicial
    document.querySelectorAll('.frame-container').forEach(container=>{
        const wrap = container.querySelector('.video-wrap');
        const main = wrap.querySelector('.vid-main');
        if (!main) return;

        const loader = document.createElement('div');        
		loader.className = '<?php echo $loader_class; ?>';
        loader.innerHTML = '<div>Loading...</div><div class="spinner"></div>';
        wrap.appendChild(loader);

        function showMain(){
            loader.style.opacity = 0;
            setTimeout(()=>loader.remove(), 500);
            main.classList.add('visible');
        }

        if (main.readyState >= 2) {
            showMain();
        } else {
            main.addEventListener('loadeddata', showMain, { once: true });
        }
    });

    function showVideo(wrap, video){
        wrap.querySelectorAll('video').forEach(v=>{
            v.classList.remove('visible');
            v.pause();
        });
        video.classList.add('visible');
        video.currentTime = 0;
        video.play();
    }

    document.querySelectorAll('.video-controls').forEach(ctrl=>{
        const wrap = ctrl.closest('.frame-container').querySelector('.video-wrap');
        const main = wrap.querySelector('.vid-main');
        const altSources = JSON.parse(ctrl.closest('.frame-container').dataset.alt);
        const altHasSound = parseInt(ctrl.closest('.frame-container').dataset.sound);

        const altCache = {};
        let currentAlt = null;

        function setCurrent(video){
            currentAlt = (video === main) ? null : video;
            if (currentAlt) muteAllGlobal(video);
            updateAllSoundIcons();
        }

        ctrl.querySelector('.icon-eye').addEventListener('click', ()=>{
            if(currentAlt){ currentAlt.pause(); currentAlt.classList.remove('visible'); }
            showVideo(wrap, main);
            setCurrent(main);
        });

        ctrl.querySelectorAll('.icon-video').forEach(icon=>{
            const idx = parseInt(icon.dataset.alt);
            icon.addEventListener('click', ()=>{
                if(currentAlt){
                    currentAlt.pause();
                    currentAlt.classList.remove('visible');
                }

                let v = altCache[idx];
                if(!v){
                    v = document.createElement('video');
                    v.src = altSources[idx];
                    v.loop = true;
                    v.playsInline = true;
                    v.muted = !altHasSound;
                    v.classList.add('vid-alt');
                    wrap.appendChild(v);
                    altCache[idx] = v;
                }

                function finalize(){
                    v.muted = false;
                    showVideo(wrap, v);
                    setCurrent(v);
                    updateAllSoundIcons();
                }

                if (v.readyState >= 2) {
                    finalize();
                } else {
                    let loader = document.createElement('div');
					loader.className = '<?php echo $loader_class; ?>';
                    loader.innerHTML = '<div>Loading...</div><div class="spinner"></div>';
                    wrap.appendChild(loader);

                    v.addEventListener('loadeddata', ()=>{
                        loader.style.opacity = 0;
                        setTimeout(()=>loader.remove(),500);
                        finalize();
                    }, { once:true });
                }
            });
        });

        const soundBtn = ctrl.querySelector('.icon-sound');
        if(soundBtn){
            soundBtn.addEventListener('click', ()=>{
                const video = currentAlt || main;
                video.muted = !video.muted;
                if(!video.muted) muteAllGlobal(video);
                updateAllSoundIcons();
            });
        }
    });

    // Parallax
    document.querySelectorAll('.frame-container').forEach(container => {
        const videoWrap = container.querySelector('.video-wrap');
        const main = videoWrap.querySelector('.vid-main');
        if (!main) return;

        const maxX = 18, maxY = 12, ease = 0.12;
        let tX=0,tY=0,cX=0,cY=0,raf=null,inSide=false;

        const onMove = e => {
            const r = videoWrap.getBoundingClientRect();
            tX = -(((e.clientX-r.left)/r.width)-0.5)*maxX;
            tY = -(((e.clientY-r.top)/r.height)-0.5)*maxY;
        };

        function step(){
            cX += (tX-cX)*ease; 
            cY += (tY-cY)*ease;
            main.style.transform = `translate3d(calc(-50% + ${cX}px), calc(-50% + ${cY}px),0)`;
            if(!inSide && Math.abs(cX)<0.25 && Math.abs(cY)<0.25){
                main.style.transform='translate3d(-50%,-50%,0)';
                cancelAnimationFrame(raf); 
                raf=null; 
                cX=0;cY=0; 
                return;
            }
            raf=requestAnimationFrame(step);
        }

        videoWrap.addEventListener('mouseenter',()=>{
            inSide=true;
            videoWrap.addEventListener('mousemove',onMove);
            if(!raf) step();
        });

        videoWrap.addEventListener('mouseleave',()=>{
            inSide=false;
            videoWrap.removeEventListener('mousemove',onMove);
            tX=0;tY=0;
        });
    });

    document.addEventListener('contextmenu', e=>e.preventDefault());
		
    </script>
    <?php
}

// ################################################################################################################################################
// ################################################################################################################################################
// ################################################################################################################################################
// 											OUR SERVICES
// ################################################################################################################################################
// ################################################################################################################################################
// ################################################################################################################################################

// [[[[wp_head]]]]
add_action('wp_head', 'mostrar_servicios_personalizados');

// ===========================================================================================
// Función que agrega contenido HTML personalizado en el <head> para la página "Our Services".
// ===========================================================================================
function mostrar_servicios_personalizados() {

    if (is_page('our-services')) : ?>

		<style>			
			#main-container {
				min-height: auto !important;
				height: auto !important;
			}		
			main.site-main {
				padding-bottom: 0 !important;
				margin-bottom: 0 !important;
			}		
			#footer {
				margin-top: 0 !important;
			}
		</style>

        <div class="custom-shop-title">OUR SERVICES</div>

        <div class="payment-info-panel">
          <p>
            💳 <span class="highlight">Payment Methods:</span> We accept <b>PayPal</b> worldwide and 
            <b>CBU bank transfers in Argentine pesos</b> for local clients.
            <br>
            📧 <span class="highlight">Email:</span>
            <a href="mailto:contact@mcartworksstudio.com">contact@mcartworksstudio.com</a>
            <br>
            💬 <span class="highlight">Discord:</span>
            <a href="https://discordapp.com/users/1432397605471649905" target="_blank">mcartworks_studio</a>
          </p>
        </div>

        <section class="your-services">
            <div class="your-services-card">
                <h3>IMAGE EDITING</h3>
                <p>You give us your image, and we enhance or modify it.</p>		
                <div class="price-box">$10 – $50 USD / image</div>		
                <p><span class="highlight-delivery">Delivery: 1–3 days</span></p>
                <p class="highlight-upfront">✦ Full payment upfront.</p>
                <p class="highlight-makes">We offer all kinds of edits, such as:</p>
                <p>- Removal or addition of backgrounds, objects, people, tattoos, scars.</p>
                <p>- Skin and detail retouching.</p>
                <p>- Portrait restoration.</p>
                <p>- Image enhancement including color, detail, sharpness.</p>
				<p>- Gender, face, hair, or clothing changes.</p>
                <p>🎬 Want your edited image turned into a video?</p>
				<p>We can create up to three unique short videos from it.</p>
            </div>

            <div class="your-services-card">
                <h3>ARTWORK CREATION</h3>
                <p>You give us an existing artwork and we improve it, or we create one from scratch.</p>
                <div class="price-box">$25 – $50 USD / artwork</div>		
                <p><span class="highlight-delivery">Delivery: 2–5 days</span></p>		
                <p class="highlight-upfront">✦ Upfront payment of <b>$10 USD</b> for samples (deducted from the total).</p>
                <p>- After you approve the sample and complete the remaining payment, we create the final version of your artwork — either static or animated, according to your choice.</p>
                <p>- Artworks can be made for posters, social media, wallpapers, Steam, and more.</p>
                <p>- If you need us to make any adjustments or corrections, we will do so.</p>
                <p>- If it’s for Steam, we deliver in your preferred format: Full, Frame, or Workshop.</p>
            </div>

            <div class="your-services-card">
                <h3>REALISTIC PHOTOMONTAGES</h3>
                <p>You express your vision. We bring it to life in realism.</p>
                <div class="price-box">$30 – $60 USD / photomontage</div>
                <p><span class="highlight-delivery">Delivery: 2–5 days</span></p>
                <p class="highlight-upfront">✦ Full payment upfront.</p>
                <p class="highlight-makes">We create all types of realistic compositions:</p>
                <p>- Place you with models, celebrities or inside a cinematic battle.</p>
                <p>- Generate realistic characters and scenes.</p>
                <p>- Transform comic or anime characters into real-life characters.</p>
                <p>- The possibilities are endless.</p>
                <p>🎬 We can generate up to <b>3 optional videos</b> if you want.</p>
                <p>💥 Perfect for intros, presentations, or cinematic profiles.</p>
                <p>If it’s for Steam, we deliver it in Workshop format.</p>
            </div>
        </section>

        <div class="section-divider"></div>

        <div class="info-panel">
          <h4>Important Information</h4>
          <p>⚠️ We do not work with explicit content (nudity or extreme gore).</p>
          <p>💰 Prices may vary depending on project complexity.</p>
          <p>⏱️ Delivery times may vary according to complexity and workload.</p>
        </div>

        <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&display=swap" rel="stylesheet">

    <?php endif;
}