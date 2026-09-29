<?php
/**
 * The header for our theme (Blocksy) – dinámico según facción
 */
?><!doctype html>
<html <?php language_attributes(); ?><?php echo blocksy_html_attr() ?>>
<head>
    <?php do_action('blocksy:head:start') ?>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5, viewport-fit=cover">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    <?php wp_head(); ?>
    <?php do_action('blocksy:head:end') ?>
</head>

<?php
ob_start();
blocksy_output_header();
$global_header = ob_get_clean();

// Detecta facción
$faction = $_COOKIE['faction'] ?? 'gdi';

// videos por facción
$videos_by_faction = [
    'gdi' => [		
		'/videos/GDI-MENU/gdi_home.mp4',
    	'/videos/GDI-MENU/gdi_shop.mp4',
	    '/videos/GDI-MENU/gdi_bundles.mp4',								
		'/videos/GDI-MENU/gdi_gallery.mp4',
		'/videos/GDI-MENU/gdi_your_services.mp4',
    ],	
	'nod' => [				
		'/videos/NOD-MENU/nod_home.mp4',
    	'/videos/NOD-MENU/nod_shop.mp4',
	    '/videos/NOD-MENU/nod_bundles.mp4',								
		'/videos/NOD-MENU/nod_gallery.mp4',
		'/videos/NOD-MENU/nod_your_services.mp4',
	],			
];

$videos = $videos_by_faction[$faction];
	
?>

<body <?php body_class(); ?> <?php echo blocksy_body_attr() ?>>

<?php if (function_exists('wp_body_open')) wp_body_open(); ?>
				
<button id="zajMenu" class="zaj-menu" aria-expanded="false" aria-controls="zajOverlay">
  <div class="toggle-icon-wrap">
    <div class="toggle-span is-top"></div>
    <div class="toggle-span is-bottom"></div>
  </div>
  <span class="zaj-menu__label zaj-menu__label--open">menu</span>
  <span class="zaj-menu__label zaj-menu__label--close">close</span>
</button>
	
<!-- Overlay + carril de tarjetas -->
<div id="zajOverlay" class="zaj-overlay" aria-hidden="true">
	<h1 class="zaj-menu-title">MENU</h1>
	<!--<div class="gold-scan"></div> <!-- Escáner diagonal dorado -->		
	<!-- Capa de fondo animada: transmisión de datos -->
    <div class="data-stream"></div>
    <!-- Radar giratorio (capa intermedia) -->
	   <div class="zaj-card-wrapper">
	   		<div class="zaj-track" role="list">				
				<?php
					// Lista de enlaces según el índice
					$links = [
						'https://mcartworksstudio.com/',              
						'https://mcartworksstudio.com/shop',          
						'https://mcartworksstudio.com/bundles',       
						'https://mcartworksstudio.com/gallery',       
						'https://mcartworksstudio.com/our-services', 
					];

					// Lista de etiquetas según el índice
					$labels = [
						'HOME',
						'SHOP',
						'BUNDLES',
						'GALLERY',
						'OUR SERVICES',
					];
				
				    $current_url = home_url( add_query_arg( array(), $wp->request ) ); // URL actual				
				?>
				
				<?php foreach($videos as $index => $video_url): 
					$link_url = $links[$index];
					$is_current = ( rtrim($link_url, '/') === rtrim($current_url, '/') );
				?>
					<div class="zaj-card <?php echo esc_attr($faction); ?> <?php echo $is_current ? 'is-current' : ''; ?>" role="listitem" style="--i:<?php echo esc_attr($index); ?>">												
						<video class="zaj-card-video" autoplay muted loop playsinline>
							<source src="<?php echo esc_url($video_url); ?>" type="video/mp4">
						</video>						
						<!-- Enlace que cubre toda la tarjeta -->
						<a class="zaj-card-link"
						   href="<?php echo esc_url($link_url); ?>"						
						   tabindex="0"
						   <?php echo $is_current ? 'aria-disabled="true"' : ''; ?>>
						</a>
						<!-- Línea decorativa -->
						<div class="zaj-card-line" aria-hidden="true"></div>
						<!-- Etiqueta visible -->
						<div class="zaj-card-label"><?php echo esc_html($labels[$index]); ?></div>
					</div>
				<?php endforeach; ?>
			</div>		
		</div>		
		<div class="scanlines"></div>	
		<div class="sweep-beam"></div>	
	    <div class="radar-pulse"></div>
    	<div class="hud-info">
			<div class="hud-title">
				<?php 
					// Cambia el título según la facción
					echo ($faction === 'nod') ? 'NOD Command Uplink' : 'GDI Command Uplink'; 
				?>
			</div>			
	    	<div class="hud-data">Status: Online</div>
	    	<div>Signal Strength<span class="signal-bar"></span></div> 
		</div>	
		<!-- Indicador de barras diagonales -->
		<div class="hud-bars">
		  <div class="bar"></div>
		  <div class="bar"></div>
		  <div class="bar"></div>
		  <div class="bar"></div>
		</div>
		<div class="terminal-feed top-right">
		  <div>> SYSTEM: AUTH LINK ESTABLISHED</div>
		  <div>> VERIFYING NODE CHANNELS...</div>
		  <div>> CONNECTION STATUS: STABLE</div>
		</div>				
		
	<div class="zaj-footer-links">
    	<a href="/intro" class="footer-link" id="viewIntro">VIEW INTRO</a>
    	<a href="/change-faction" class="footer-link" id="changeFaction">CHANGE FACTION</a>
	</div>
	<!-- === Botón de sonido estilo C&C === -->
	<div id="menu-sound-btn" class="menu-sound muted" style="display:none;">
	  <svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
		<!-- Icono parlante -->
		<path d="M5 9v6h4l5 5V4L9 9H5z"/>
		<!-- Línea mute -->
		<line id="muteLine" x1="3" y1="3" x2="21" y2="21"/>
	  </svg>
	</div>
</div>
<!-- LOADING SCREEN -->
<div id="zajLoading" class="zaj-loading" aria-hidden="true">
  <div class="loading-content">
    <div class="loading-title">ESTABLISHING UPLINK...</div>
    <div class="loading-bar">
      <div class="loading-progress"></div>
    </div>
  </div>
</div>
<div id="main-container">
    <?php
        do_action('blocksy:header:before');
        echo $global_header;
        do_action('blocksy:header:after');
    ?>
<?php do_action('blocksy:content:before'); ?>
<main <?php echo blocksy_main_attr() ?>>

<?php
    do_action('blocksy:content:top');
    blocksy_before_current_template();
?>

<script>
document.addEventListener('DOMContentLoaded', () => {
	const btn = document.getElementById('zajMenu');
	const overlay = document.getElementById('zajOverlay');
	const cards = overlay.querySelectorAll('.zaj-card');

	/* ==============================
	   SISTEMA DE SONIDO GENERAL
	   ============================== */
	const hoverSound = new Audio('/sounds/hover_card_click');
	const clickSound = new Audio('/sounds/card_click');
	const currentClickSound = new Audio('/sounds/current_card_click');

	let isMuted = false; // 🔇 estado global de silencio

	/* ===========================================
	   EFECTOS DE SONIDO DE TARJETAS (condicional)
	   =========================================== */
	cards.forEach(card => {
		const link = card.querySelector('.zaj-card-link');

		card.addEventListener('mouseenter', () => {
			if (!card.classList.contains('is-current') && !isMuted) {
				hoverSound.currentTime = 0;
				hoverSound.play().catch(()=>{});
			}
		});

		card.addEventListener('click', () => {
			if (isMuted) return; // si está muteado, no hace nada

			if (card.classList.contains('is-current')) {
				currentClickSound.currentTime = 0;
				currentClickSound.play().catch(()=>{});
			} else {
				clickSound.currentTime = 0;
				clickSound.play().catch(()=>{});
			}
		});

		link.addEventListener('click', e => {
			e.preventDefault();
			const href = link.getAttribute('href');
			showLoadingAndRedirect(href);
		});
	});

	/* ===========================
	   OPEN / CLOSE OVERLAY
	   =========================== */
	function openOverlay() {
		btn.classList.add('is-open');
		overlay.classList.add('is-open');
		btn.setAttribute('aria-expanded', 'true');
		overlay.setAttribute('aria-hidden', 'false');
		document.documentElement.style.overflow = 'hidden';
		document.body.classList.add('menu-open');

		cards.forEach((card, index) => {
			card.style.opacity = '0';
			card.classList.remove('animate-in');
			void card.offsetWidth; // reflow
			card.style.animationDelay = `${index * 0.07}s`;
			card.classList.add('animate-in');
			card.style.opacity = '1';
		});

		startGlitch();
	}

	function closeOverlay() {
		btn.classList.remove('is-open');
		overlay.classList.remove('is-open');
		btn.setAttribute('aria-expanded', 'false');
		overlay.setAttribute('aria-hidden', 'true');
		document.documentElement.style.overflow = '';
		document.body.classList.remove('menu-open');

		cards.forEach(card => {
			card.classList.remove('animate-in');
			card.style.opacity = '0';
		});
		
		stopGlitch();
	}

	btn.addEventListener('click', () => {
		overlay.classList.contains('is-open') ? closeOverlay() : openOverlay();
	});

	document.addEventListener('keydown', (e) => {
		if (e.key === 'Escape' && overlay.classList.contains('is-open')) closeOverlay();
	});

	overlay.addEventListener('click', (e) => {
		if (e.target === overlay) closeOverlay();
	});

	/* ===========================
	   GLITCH EFFECT (sin cambios)
	   =========================== */
	let glitchActive = false;
	const glitchLayers = [];
	function startGlitch() { /* ... igual que tu versión ... */ }
	function stopGlitch() { /* ... igual que tu versión ... */ }

	/* ==================================
	   AUDIO DEL MENÚ (con cookie + fade)
	   ================================== */
	let menuAudio;
	let menuAudioTime = 0;
	let fadeInterval = null;

	const faction = '<?php echo esc_js($faction); ?>';
	const cookieName = `${faction}_menu_audio_time`;

	function setCookie(name, value, days = 1) {
		const expires = new Date(Date.now() + days * 864e5).toUTCString();
		document.cookie = `${name}=${encodeURIComponent(value)}; expires=${expires}; path=/`;
	}
	
	function getCookie(name) {
		const value = document.cookie.split('; ').find(row => row.startsWith(name + '='));
		return value ? decodeURIComponent(value.split('=')[1]) : null;
	}
	
	function initMenuAudio() {
		if (!menuAudio) {
			const audioPath = `/songs/${faction === 'nod' ? 'nod-menu-theme' : 'gdi-menu-theme'}`;
			menuAudio = new Audio(audioPath);
			menuAudio.loop = true;
			menuAudio.preload = "auto";			
			menuAudio.volume = 0;
			const savedTime = parseFloat(getCookie(cookieName));
			if (!isNaN(savedTime)) {
				menuAudioTime = savedTime;
				menuAudio.currentTime = savedTime;
			}
			setInterval(() => {
				if (menuAudio && !menuAudio.paused) {
					setCookie(cookieName, menuAudio.currentTime);
				}
			}, 2000);
		}
	}
	
	// Si el menú se abre automáticamente, empieza muteado
	if (window.menuOpenedAutomatically) {
		initMenuAudio(); // aseguramos que menuAudio exista
		menuAudio.pause();
		menuAudio.currentTime = 0;
		menuAudio.muted = true;
		isMuted = true;
	}
	
	function fadeAudio(targetVolume, duration = 600) {
		if (!menuAudio) return;
		clearInterval(fadeInterval);
		const steps = 20;
		const stepTime = duration / steps;
		const volumeStep = (targetVolume - menuAudio.volume) / steps;
		fadeInterval = setInterval(() => {
			let newVol = menuAudio.volume + volumeStep;
			if ((volumeStep > 0 && newVol >= targetVolume) || (volumeStep < 0 && newVol <= targetVolume)) {
				newVol = targetVolume;
				clearInterval(fadeInterval);
			}
			menuAudio.volume = Math.max(0, Math.min(1, newVol));
		}, stepTime);
	}
		
	function playMenuAudio() {
		initMenuAudio();

		if (!menuAudio) return;

		menuAudio.currentTime = menuAudioTime;

		if (isMuted) {
			// 🚫 No reproducimos nada si está muteado
			menuAudio.pause();
			menuAudio.muted = true;
			menuAudio.volume = 0;
			return;
		}

		menuAudio.muted = false;
		menuAudio.volume = 0;
		menuAudio
			.play()
			.then(() => fadeAudio(0.4, 800))
			.catch(() => {});
	}
	
	function pauseMenuAudio() {
		if (menuAudio) {
			menuAudioTime = menuAudio.currentTime;
			setCookie(cookieName, menuAudioTime);
			fadeAudio(0, 500);
			setTimeout(() => menuAudio.pause(), 550);
		}
	}
		
	// Redefinimos openOverlay y closeOverlay con control de autoplay
	const originalOpenOverlay = openOverlay;
	const originalCloseOverlay = closeOverlay;

	openOverlay = function() {
	  originalOpenOverlay();

	  // 🚫 Si el menú se abre automáticamente, no reproducimos el audio
	  if (window.menuOpenedAutomatically) {
		showMenuSoundButton(); // mostramos el botón en estado muteado
		isMuted = true;

		if (menuAudio) {
		  menuAudio.pause();
		  menuAudio.currentTime = 0;
		  menuAudio.muted = true;
		}

		return; // salimos antes de que se llame a playMenuAudio()
	  }

	  // 🔊 Si no fue automático, reproducimos normalmente
	  playMenuAudio();
	  showMenuSoundButton();
	};

	closeOverlay = function() {
	  originalCloseOverlay();
	  pauseMenuAudio();
	  hideMenuSoundButton();
	};
	
	/* ================================== 
	 * BOTÓN CAMBIO FACTION / INTRO
	 * ================================== */
	const changeFactionBtn = document.getElementById('changeFaction');
	if (changeFactionBtn) {
		changeFactionBtn.addEventListener('click', e => {
			e.preventDefault();
			showLoadingAndRedirect('https://mcartworksstudio.com/faction-selection');
		});
	}
	const viewIntroBtn = document.getElementById('viewIntro');
	if (viewIntroBtn) {
		viewIntroBtn.addEventListener('click', e => {
			e.preventDefault();
			sessionStorage.setItem('fromMenu', 'true');
			showLoadingAndRedirect('https://mcartworksstudio.com/intro');
		});
	}

	/* =====================================
	 * FUNCIÓN GLOBAL: LOADING + REDIRECCIÓN
	 * ===================================== */
	function showLoadingAndRedirect(href, delay = 800) {
	  // Congela el último frame de los videos 
	  document.querySelectorAll('.zaj-card-video').forEach(v => {
		try {
		  const canvas = document.createElement('canvas');
		  canvas.width = v.videoWidth;
		  canvas.height = v.videoHeight;
		  const ctx = canvas.getContext('2d');
		  ctx.drawImage(v, 0, 0, canvas.width, canvas.height);
		  const snapshot = document.createElement('div');
		  snapshot.className = 'video-freeze-frame';
		  snapshot.style.backgroundImage = `url(${canvas.toDataURL('image/jpeg')})`;
		  snapshot.style.position = 'absolute';
		  snapshot.style.top = 0;
		  snapshot.style.left = 0;
		  snapshot.style.width = '100%';
		  snapshot.style.height = '100%';
		  snapshot.style.backgroundSize = 'cover';
		  snapshot.style.backgroundPosition = 'center';
		  snapshot.style.zIndex = '5'; // ✅ por debajo del label (10), por encima del video (1)
		  /*snapshot.style.zIndex = 1;*/
		  v.parentNode.appendChild(snapshot);
		} catch (e) {}
	  });

	  // Activa el loader normalmente
	  const loader = document.getElementById('zajLoading');
	  const progress = loader.querySelector('.loading-progress');
	  loader.classList.add('active');
	  progress.style.width = '0%';
	  void progress.offsetWidth;
	  progress.style.width = '100%';
	  document.body.style.cursor = 'wait';
	  setTimeout(() => window.location.href = href, delay);
	}

	/* ============================================
	   BOTÓN DE SONIDO ESTILO C&C (sincroniza todo)
	   ============================================ */
	const menuSoundBtn = document.getElementById('menu-sound-btn');

	function showMenuSoundButton() {
		if (window.menuOpenedAutomatically && menuSoundBtn) {
			menuSoundBtn.style.display = 'flex';
			menuSoundBtn.classList.add('muted');
			isMuted = true; // 🔇 arranca muteado
		}
	}
	function hideMenuSoundButton() {
		if (menuSoundBtn) menuSoundBtn.style.display = 'none';
	}

	if (menuSoundBtn) {
		menuSoundBtn.addEventListener('click', () => {
			if (!menuAudio) initMenuAudio();
			const isBtnMuted = menuSoundBtn.classList.contains('muted');

			// Si el usuario toca el botón de sonido, ya no es automático
			if (typeof window.menuOpenedAutomatically !== 'undefined') {
				window.menuOpenedAutomatically = false;
			}

			if (isBtnMuted) {
				menuAudio.muted = false;
				menuAudio.play().then(() => fadeAudio(0.4, 800)).catch(()=>{});
				menuSoundBtn.classList.remove('muted');
				isMuted = false; // 🔊 habilita sonidos hover/click
			} else {
				fadeAudio(0, 400);
				setTimeout(() => menuAudio.muted = true, 400);
				menuSoundBtn.classList.add('muted');
				isMuted = true; // 🔇 deshabilita hover/click
			}
		});
	}
					
	// Si estamos en la pagina gallery
	if (window.location.pathname.includes('/gallery')) {

		/* =====================================
		   DETENER Y RECORDAR ESTADO DE SONIDOS
		   ===================================== */
		function stopAllOtherAudio() {
			document.querySelectorAll('audio').forEach(a => {
				if (!a.closest('#zajOverlay')) {
					a.dataset.wasPlaying = !a.paused ? 'true' : 'false';
					a.pause();
				}
			});

			document.querySelectorAll('video').forEach(v => {
				if (!v.closest('#zajOverlay')) {
					v.dataset.wasUnmuted = !v.muted ? 'true' : 'false';
					v.muted = true;
				}
			});
		}

		/* =====================================
		   RESTAURAR SONIDOS PREVIOS
		   ===================================== */
		function restorePreviousAudio() {
			document.querySelectorAll('audio').forEach(a => {
				if (!a.closest('#zajOverlay') && a.dataset.wasPlaying === 'true') {
					a.play().catch(() => {});
				}
			});

			document.querySelectorAll('video').forEach(v => {
				if (!v.closest('#zajOverlay') && v.dataset.wasUnmuted === 'true') {
					v.muted = false;
				}
			});
		}

		/* =====================================
		   INTEGRACIÓN CON EL MENÚ
		   ===================================== */
		const _originalOpenOverlay = openOverlay;
		openOverlay = function() {
			stopAllOtherAudio();
			_originalOpenOverlay();
			playMenuAudio();
			showMenuSoundButton();
		};

		const _originalCloseOverlay = closeOverlay;
		closeOverlay = function() {
			_originalCloseOverlay();
			restorePreviousAudio();
		};
	}
});
</script>