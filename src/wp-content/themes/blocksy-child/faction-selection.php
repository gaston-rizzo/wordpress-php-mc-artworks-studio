<?php
/**
 * Archivo: faction-selection.php
 * Contiene toda la pantalla de selección de facción
 */

/* ========================================================================================
 *  Función que muestra la pantalla de selección de facción:
 *  - Desactiva scroll y muestra una pantalla a pantalla completa con fondo animado.
 *  - Presenta las dos facciones (GDI y NOD) con sus respectivos videos y overlays.
 *  - Cada facción reproduce su video "default" en loop al estar en reposo.
 *  - Al presionar "JOIN GDI" o "JOIN NOD" se ejecuta el video "after-selection" una sola vez,
 *    quedando pausado en el último frame.
 *  - El usuario puede confirmar (YES) o cancelar (NO) la selección:
 *      · YES: guarda la facción elegida en una cookie "faction" válida por 1 año.
 *      · NO: reinicia la animación y vuelve al estado inicial de selección.
 *  - Incluye control de sonido global (HUD sound button) que sincroniza música, efectos y videos.
 *  - Carga y muestra un overlay de "loading" general antes de desplegar la pantalla.
 ============================================================================================ */

 /* ======================================================================================
 * Inyecta estilos específicos en el <head> para la página de selección de facción.  
 * - Solo se ejecuta en la página con slug 'faction-selection'.
 * - Añade estilos para cuando el body o html tengan la clase 'loading-active'.
 * - Sirve para mostrar un fondo negro y bloquear el scroll durante la pantalla de carga.
 * ====================================================================================== */
add_action('wp_head', function() {
	if (is_page('faction-selection')) {
		echo '
		<style>
		html.loading-active, body.loading-active {
			background: #000 !important;
			margin: 0;
			padding: 0;
			height: 100%;
			overflow: hidden;
		}
		</style>
		';
	}
});

/* =======================================================================================
 * Inserta la pantallas de carga y de selección de facción.  
 * - Solo se ejecuta en la página con slug 'faction-selection'.
 * - Primero muestra la pantalla de carga general (mostrar_loading_general_overlay()).
 * - Luego carga la pantalla principal de selección de facción (pantalla_faccion_epica()).
 * - El orden es importante: primero el loading, después la facción.
 * ======================================================================================= */
add_action('wp_body_open', function() {
    if (is_page('faction-selection')) {
        mostrar_loading_general_overlay();
        pantalla_faccion_epica();
    }
});

/* =====================================================================================
 * Muestra una pantalla de carga (overlay) con animación visual y sonora
 * para la página de selección de facción. 
 * - Cubre toda la pantalla con un fondo animado (gradiente dinámico y scanlines).
 * - Muestra una barra de carga animada, texto glitch y logos genéricos. 
 * - Una vez finalizada la animación, oculta el overlay y muestra la pantalla de facción.
 * ====================================================================================== */
function mostrar_loading_general_overlay() {
    ?>
    <style>
		
    /* === Overlay general facciones === */
    #loading-overlay {
        display: flex !important;
        align-items: center;
        justify-content: center;
        position: fixed !important;
        inset: 0 !important;
         z-index: 999999 !important;
        font-family: 'Share Tech Mono', monospace;
        color: #fff;
        flex-direction: column;
        overflow: hidden;
        background: linear-gradient(135deg, #0d0d0d 0%, #1a0000 45%, #250029 55%, #0d0d0d 100%);
        background-size: 400% 400%;
        animation: bgShift 12s ease-in-out infinite;
    }

    @keyframes bgShift {
        0% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
        100% { background-position: 0% 50%; }
    }

    /* === Scanlines === */
    #loading-overlay::after {
        content: "";
        position: absolute;
        inset: 0;
        background: repeating-linear-gradient(
            to bottom,
            rgba(255,255,255,0.05) 0px,
            rgba(255,255,255,0.05) 2px,
            transparent 2px,
            transparent 4px
        );
        pointer-events: none;
        mix-blend-mode: overlay;
        animation: flicker 3s infinite;
    }

    @keyframes flicker {
        0%, 19%, 21%, 23%, 100% { opacity: 1; }
        20%, 22% { opacity: 0.5; }
    }

    /* === HUD container === */
    .hud-container {
        position: relative;
        z-index: 3;
        text-align: center;
    }

    /* === Loading Text === */
    .loading-text {
        font-size: 28px;
        letter-spacing: 0.25em;
        text-transform: uppercase;
        margin-top: 24px;
        text-shadow: 0 0 6px #ff2a2a, 0 0 14px #9b00ff;
        animation: glowPulse 2s infinite alternate;
        position: relative;
    }

    @keyframes glowPulse {
        from { text-shadow: 0 0 6px #ff2a2a, 0 0 14px #9b00ff; opacity: 0.7; }
        to   { text-shadow: 0 0 18px #ff4d4d, 0 0 28px #ff66ff; opacity: 1; }
    }

    /* === Glitch effect text overlay === */
    .loading-text::before,
    .loading-text::after {
        content: "Loading Interface...";
        position: absolute;
        left: 50%;
        transform: translateX(-50%);
        opacity: 0.6;
    }
    .loading-text::before {
        color: #ff2a2a;
        animation: glitchLeft 1.5s infinite;
    }
    .loading-text::after {
        color: #9b00ff;
        animation: glitchRight 1.5s infinite;
    }
    @keyframes glitchLeft {
        0% { transform: translate(-50%, 0); }
        20% { transform: translate(-52%, -2px); }
        40% { transform: translate(-48%, 2px); }
        60% { transform: translate(-50%, 0); }
        100% { transform: translate(-50%, 0); }
    }
    @keyframes glitchRight {
        0% { transform: translate(-50%, 0); }
        20% { transform: translate(-49%, 2px); }
        40% { transform: translate(-51%, -2px); }
        60% { transform: translate(-50%, 0); }
        100% { transform: translate(-50%, 0); }
    }

    /* === Loading bar === */
    .loading-bar {
        width: 360px;
        height: 14px;
        border-radius: 8px;
        background: rgba(255,255,255,0.08);
        overflow: hidden;
        margin: 16px auto;
        box-shadow: 0 0 12px rgba(255,0,0,0.4), 0 0 12px rgba(155,0,255,0.4);
    }
    .loading-bar-inner {
        width: 0;
        height: 100%;
        background: linear-gradient(90deg, #ff2a2a, #9b00ff, #ff66ff, #ff2a2a);
        background-size: 300% 100%;
		animation: loadingBar 3s ease-in-out forwards;
    }

    @keyframes loadingBar {
        0% { width: 0%; background-position: 0% 50%; }
        50% { width: 85%; background-position: 100% 50%; }
        100% { width: 100%; background-position: 0% 50%; }
    }

    /* === Logos genéricos === */
    .logo-nod, .logo-gdi {
        position: absolute;
        width: 40%;
        height: auto;
        opacity: 0.12;
        z-index: 1;
        filter: drop-shadow(0 0 12px currentColor);
    }

    .logo-nod {
        left: 5%;
        top: 50%;
        transform: translateY(-50%);
        color: #ff2a2a;
        animation: nodGlow 3s infinite alternate;
    }

    .logo-gdi {
        right: 5%;
        top: 50%;
        transform: translateY(-50%);
        color: #9b00ff;
        animation: gdiGlow 3s infinite alternate;
    }

    @keyframes nodGlow {
        from { filter: drop-shadow(0 0 6px #ff2a2a); }
        to   { filter: drop-shadow(0 0 18px #ff4d4d); }
    }
    @keyframes gdiGlow {
        from { filter: drop-shadow(0 0 6px #9b00ff); }
        to   { filter: drop-shadow(0 0 18px #ff66ff); }
    }

    /* Bloquear scroll */
    html.loading-active, body.loading-active {
        overflow: hidden !important;
        height: 100vh !important;
        touch-action: none;
    }
		
	/* Oculta el botón del carrito mientras carga */
	html.loading-active body .xoo-wsc-basket {
    	display: none !important;
	}

    </style>

    <div id="loading-overlay">
        <!-- Logo NOD genérico -->
        <svg class="logo-nod" viewBox="0 0 100 100">
          <polygon points="50,10 90,90 10,90" fill="none" stroke="currentColor" stroke-width="6"/>
        </svg>

        <!-- Logo GDI genérico -->
        <svg class="logo-gdi" viewBox="0 0 100 100">
          <circle cx="50" cy="50" r="40" fill="none" stroke="currentColor" stroke-width="6"/>
          <polygon points="50,15 85,35 85,65 50,85 15,65 15,35" fill="none" stroke="#ff66ff" stroke-width="4"/>
        </svg>

        <!-- HUD central -->
        <div class="hud-container">
            <div class="loading-bar">
                <div class="loading-bar-inner"></div>
            </div>
            <div class="loading-text"></div>
        </div>
    </div>

	<script>
		document.documentElement.classList.add('loading-active');
		document.body.classList.add('loading-active');

		window.addEventListener('load', () => {
		  const overlay = document.getElementById('loading-overlay');
		  const factionScreen = document.getElementById('faccion-screen');
		  const loadingBar = document.querySelector('.loading-bar-inner');

		  if (!overlay || !loadingBar) return;

		  // Ocultar la pantalla de facción hasta que termine la carga
		  if (factionScreen) factionScreen.style.display = 'none';

		  // Esperar a que termine la animación de la barra de carga
		  loadingBar.addEventListener('animationend', () => {
			// Fade out del overlay
			overlay.style.transition = 'opacity 0.8s ease';
			overlay.style.opacity = '0';

			setTimeout(() => {
			  overlay.style.display = 'none';
			  document.documentElement.classList.remove('loading-active');
			  document.body.classList.remove('loading-active');
			  if (factionScreen) factionScreen.style.display = 'block';
			}, 800);
		  });

		  // Seguridad: si algo falla, forzar cierre tras 3.5s
		  setTimeout(() => {
			const isVisible = window.getComputedStyle(overlay).display !== 'none';
			if (isVisible) {
			  loadingBar.dispatchEvent(new Event('animationend'));
			}
		  }, 3500);
		});
		</script>

    <!-- Fuente futurista -->
    <link href="https://fonts.googleapis.com/css2?family=Share+Tech+Mono&display=swap" rel="stylesheet">
    <?php
}

/* ==================================================================
 * Genera la pantalla de selección de facción (NOD / GDI). 
 * - Se muestra después del overlay de carga.
 * - Contiene videos de fondo dinámicos para cada facción.
 * - Incluye botones de selección con confirmación “ARE YOU SURE?”.
 * - Controla sonidos de hover, click y ambientación.
 * - Agrega efectos visuales (partículas, glitch, HUD, fade, etc.).
 * - Guarda la facción elegida en una cookie y redirige a /nod o /gdi.
 * =================================================================== */
function pantalla_faccion_epica() { ?>
<section id="faccion-screen">
    
    <!-- Canvas de partículas dinámicas -->
    <canvas id="bg-canvas"></canvas>

    <!-- Sonido ambiental -->
    <audio id="ambient-sound">
        <source src="/songs/selection-theme" type="audio/mpeg">
    </audio>
	
    <!-- Título central -->
    <h1 class="titulo-faccion">Choose Your Faction</h1>

    <!-- Contenedor de facciones -->
    <div class="contenedor-facciones">

        <!-- Panel NOD -->
        <div class="faccion nod" data-faccion="nod">
            <video class="video-default" autoplay muted loop playsinline>
                <source src="/videos/nod-selection/nod-before-selection.mp4" type="video/mp4">
            </video>
            <video class="video-confirm" playsinline style="display:none;">
                <source src="/videos/nod-selection/nod-after-selection.mp4" type="video/mp4">
            </video>			
            <div class="overlay overlay-default">
                <p class="desc">Brotherhood of Nod: Shadows, Fire, Faith</p>
                <a href="#" class="btn-nod select-btn">Join Nod</a>
            </div>

            <div class="overlay overlay-confirm" style="display:none;">
                <h2 class="confirm-text">ARE YOU SURE?</h2>
                <div class="confirm-buttons">
                    <button class="btn-yes">YES</button>
                    <button class="btn-no">NO</button>										
                </div>
            </div>
        </div>

        <!-- Panel GDI -->
        <div class="faccion gdi" data-faccion="gdi">
            <video class="video-default" autoplay muted loop playsinline>
                <source src="/videos/gdi-selection/gdi-before-selection.mp4" type="video/mp4">								
            </video>
            <video class="video-confirm" playsinline style="display:none;">
                <source src="/videos/gdi-selection/gdi-after-selection.mp4" type="video/mp4">
            </video>
            <div class="overlay overlay-default">
                <p class="desc">GDI: Strength, Order, Technology</p>
                <a href="#" class="btn-gdi select-btn">Join GDI</a>
            </div>
            <div class="overlay overlay-confirm" style="display:none;">
                <h2 class="confirm-text">ARE YOU SURE?</h2>
                <div class="confirm-buttons">
                    <button class="btn-yes">YES</button>
                    <button class="btn-no">NO</button>
                </div>
            </div>
        </div>
		
		<audio id="sound-hover" src="/sounds/button-hover-one" preload="auto"></audio>					
		<audio id="sound-yes-click" src="/sounds/button-click-one" preload="auto"></audio>										
		<audio id="sound-no-click" src="/sounds/button-click-two" preload="auto"></audio>
		<audio id="sound-btn-select-click" src="/sounds/btn-select-click" preload="auto"></audio>
		<audio id="sound-btn-select-hover" src="/sounds/btn-select-hover" preload="auto"></audio>
		
    </div>

    <!-- HUD futurista -->
    <div class="hud-overlay"></div>
	
	<!-- === Botón de sonido estilo C&C === -->
	<div id="hud-sound-btn" class="hud-sound muted">
	  <svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
		<!-- Icono parlante -->
		<path fill="currentColor" d="M5 9v6h4l5 5V4L9 9H5z"/>
		<!-- Línea muteado (oculta por defecto, visible en mute) -->
		<line id="muteLine" x1="3" y1="3" x2="21" y2="21"/>
	  </svg>
	</div>
	
	<script>
		
		  document.addEventListener("DOMContentLoaded", () => {
		  const soundBtn = document.getElementById("hud-sound-btn");		  
		  const soundHover = document.getElementById("sound-hover");
		  const soundYesClick = document.getElementById("sound-yes-click");
		  const soundNoClick = document.getElementById("sound-no-click");		  
		  const soundBtnSelectClick = document.getElementById("sound-btn-select-click");
		  const soundBtnSelectHover = document.getElementById("sound-btn-select-hover");
			  
		  const audios = document.querySelectorAll("audio");
		  let isMuted = true;
		  let firstInteraction = true; // para detectar el primer click		  		  
		  const ambient = document.getElementById("ambient-sound");

		  // Todos arrancan muteados	   
		  audios.forEach(a => a.muted = true);
			  
		  // --- Forzar mute en los videos de confirmación según el estado global ---
		  document.querySelectorAll(".video-confirm").forEach(video => {
		  		// Siempre arranca con el estado actual del botón de sonido
		  		video.muted = isMuted;
		  });

		  soundBtn.addEventListener("click", () => {		  
				isMuted = !isMuted;    
				ambient.muted = isMuted;

				if (firstInteraction && !isMuted) {
					ambient.play();
				}

				// Después del primer click ya no hace falta forzar play
				if (firstInteraction) firstInteraction = false;
				soundBtn.classList.toggle("muted", isMuted);

				// Desmutear los audios de botones para que suenen luego con interacción
				soundHover.muted = isMuted;
				soundYesClick.muted = isMuted;
				soundNoClick.muted = isMuted;	
			    soundBtnSelectClick.muted = isMuted;
			    soundBtnSelectHover.muted = isMuted;
			  
			    // También mutear/desmutear los videos de fondo y confirmación
				document.querySelectorAll("video").forEach(v => {
					v.muted = isMuted;
				});
		  });

		  const btnYes = document.querySelectorAll(".btn-yes");
		  const btnNo = document.querySelectorAll(".btn-no");			  
		  const btnSelect = document.querySelectorAll('.select-btn');

		  function playSound(audioEl) {
			if (!isMuted) {  // solo suena si el usuario activó el sonido
				audioEl.currentTime = 0;
				audioEl.play();
			}
		  }

		  // Hover en YES y NO
		  [...btnYes, ...btnNo].forEach(btn => {
			btn.addEventListener("mouseenter", () => playSound(soundHover));
		  });
			  
		  // Click en YES
		  btnYes.forEach(btn => {
			btn.addEventListener("click", () => playSound(soundYesClick));
		  });

		  // Click en NO
		  btnNo.forEach(btn => {
			btn.addEventListener("click", () => playSound(soundNoClick));
		  });
					  
		  // Hover en Seleccion facción
		  btnSelect.forEach(btn => {
			btn.addEventListener("mouseenter", () => playSound(soundBtnSelectHover));
		  });
			  
		  // Click en Seleccion facción
		  btnSelect.forEach(btn => {
			btn.addEventListener("click", () => playSound(soundBtnSelectClick));
		  });
		});

		document.documentElement.classList.add('loading-active');
		document.body.classList.add('loading-active');

		window.addEventListener('load', () => {
		  const overlay = document.getElementById('loading-overlay');
		  const factionScreen = document.getElementById('faccion-screen');
		  const loadingBar = document.querySelector('.loading-bar-inner');

		  if (!overlay || !loadingBar || !factionScreen) return;

		  // Aseguramos que la pantalla de facción esté oculta inicialmente
		  factionScreen.style.opacity = '0';
		  factionScreen.style.visibility = 'hidden';

		  // Esperar a que la animación de la barra termine
		  loadingBar.addEventListener('animationend', () => {
			// Fade out del overlay
			overlay.style.transition = 'opacity 0.8s ease';
			overlay.style.opacity = '0';
			overlay.style.visibility = 'hidden';

			// Fade in de la pantalla de facción
			factionScreen.style.visibility = 'visible';
			factionScreen.style.opacity = '1';

			// Eliminar bloqueo de scroll al finalizar
			setTimeout(() => {
			  overlay.style.display = 'none';
			  document.documentElement.classList.remove('loading-active');
			  document.body.classList.remove('loading-active');
			}, 900);
		  });

		  // Fallback si algo falla
		  setTimeout(() => {
			const isVisible = window.getComputedStyle(overlay).visibility !== 'hidden';
			if (isVisible) {
			  loadingBar.dispatchEvent(new Event('animationend'));
			}
		  }, 3500);
		});
	</script>
	
</section>

<style>
	
/* Evitar selección de texto */
body {
  overflow: hidden !important; /* Ocultar scrollbar */
  user-select: none;
  -webkit-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
}
	
/* === Botón de sonido estilo C&C === */
	#hud-sound-btn {
	  position: fixed;
	  bottom: 22px;
	  right: 22px;
	  z-index: 2147483647;
	  width: 52px;
	  height: 52px;
	  border-radius: 50%;
	  background: radial-gradient(circle at 30% 30%, #222, #000);
	  border: 2px solid rgba(255,255,255,.25);
	  box-shadow: 0 0 12px rgba(255,0,0,.45), inset 0 0 8px rgba(255,0,0,.35);
	  cursor: pointer;
	  display: flex;
	  align-items: center;
	  justify-content: center;
	  transition: all .25s ease;
	}

	#hud-sound-btn:hover {
	  box-shadow: 0 0 20px rgba(255,0,0,.75), inset 0 0 10px rgba(255,0,0,.55);
	  transform: scale(1.1);
	}

	#hud-sound-btn svg {
	  width: 28px;
	  height: 28px;
	  fill: #ff3a3a;
	}

	/* Línea mute */
	#muteLine {
	  stroke: red;
	  stroke-width: 2.5;
	  opacity: 0;
	  transition: opacity .25s ease;
	}

	#hud-sound-btn.muted #muteLine {
	  opacity: 1;
	}
	
/* ===== Reset ===== */
#faccion-screen, #faccion-screen * {
  margin: 0; padding: 0; box-sizing: border-box; 
}

/* ===== Pantalla full ===== */
#faccion-screen {
  position: fixed;
  inset: 0;
  background: #000;
  z-index: 2147483647;
  overflow: hidden;
  font-family: 'Orbitron', sans-serif;
}

/* ===== Canvas de partículas ===== */
#bg-canvas {
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
  background: radial-gradient(circle at center, #111 0%, #000 100%);
  z-index: 0;
}

/* ===== HUD flotante ===== */
.hud-overlay {
  position: absolute;
  inset: 0;
  background: repeating-linear-gradient(
    to bottom, rgba(255,255,255,0.05) 0px, rgba(255,255,255,0.05) 2px, transparent 2px, transparent 4px
  );
  background-size: 100% 4px;
  pointer-events: none;
  z-index: 5;
}

/* ===== Título ===== */
.titulo-faccion {
  position: absolute;
  top: 6%;
  width: 100%;
  text-align: center;
  font-size: 3.5rem;
  color: #fff;
  text-transform: uppercase;
  letter-spacing: 3px;
  text-shadow: 0 0 25px #ff0000, 0 0 35px #ffd700;
  z-index: 20;
}

/* ===== Contenedor principal ===== */
.contenedor-facciones {
  display: flex;
  width: 100%;
  height: 100%;
  z-index: 10;
  position: relative;
}

/* ===== Panel base ===== */
.faccion {
  flex: 1;
  position: relative;
  overflow: hidden;
  cursor: pointer;
  transition: transform 0.5s ease;
}
.faccion:hover { transform: scale(1.02); }

.faccion video {
  width: 100%;
  height: 100%;
  object-fit: cover;
  filter: brightness(0.4);
  transition: filter 0.6s ease;
}
.faccion:hover video { filter: brightness(1); }

/* ===== Overlay con logo + texto ===== */
.overlay {
  position: absolute;
  bottom: 8%;
  left: 50%;
  transform: translateX(-50%);
  width: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  padding: 20px;
  z-index: 15;
}
.overlay-default { background: rgba(0,0,0,0.45); }
.overlay-confirm { background: rgba(0,0,0,0.65); flex-direction: column; }

/* ===== Texto descriptivo ===== */
.desc {
  font-size: 1.2rem;
  margin-bottom: 30px; /* espacio entre texto y botón */
  color: #fff;
  text-shadow: 0 0 10px #000;
}

/* ===== Botones selección más pequeños y proporcionales ===== */
#faccion-screen .btn-nod,
#faccion-screen .btn-gdi {
  margin-top: 5px; 
  display: inline-flex !important;
  align-items: center !important;
  justify-content: center !important;
  padding: 12px 28px !important;
  min-height: 40px !important;
  height: auto !important;
  font-size: 1rem !important;
  font-weight: 900;
  text-transform: uppercase;
  border-radius: 12px;
  text-decoration: none;
  letter-spacing: 1.5px;
  line-height: normal !important;
  box-sizing: border-box !important;
  transition: all 0.3s ease;
  cursor: pointer;
  position: relative;
  overflow: visible !important;
  z-index: 2 !important;
}

/* Glow dinámico */
#faccion-screen .btn-nod::before,
#faccion-screen .btn-gdi::before {
  top: -40%;
  left: -40%;
  width: 180%;
  height: 180%;
  background: linear-gradient(120deg, transparent, rgba(255,255,255,0.12), transparent);
  transform: rotate(25deg);
  animation: shine 3s infinite;
  pointer-events: none;
  z-index: 0;
}

@keyframes shine {
  0% { left: -200%; }
  100% { left: 200%; }
}

/* NOD */
#faccion-screen .btn-nod {
  background: #111;
  color: #fff;
  border: 3px solid #ff1a1a;
  box-shadow: 0 0 25px #ff1a1a, 0 0 50px #900 inset;
}
#faccion-screen .btn-nod:hover {
  background: #ff1a1a;
  color: #fff;
  box-shadow: 0 0 35px #ff1a1a, 0 0 80px #f00;
}

/* GDI */
#faccion-screen .btn-gdi {
  background: #111;
  color: #ffd700;
  border: 3px solid #ffd700;
  box-shadow: 0 0 25px #ffd700, 0 0 50px #bfa100 inset;
}
#faccion-screen .btn-gdi:hover {
  background: #ffd700;
  color: #000;
  box-shadow: 0 0 35px #ffd700, 0 0 80px #ffd700;
}

/* ===== Confirmación ===== */
.confirm-text {
  font-size: 2rem;
  margin-bottom: 20px;
  letter-spacing: 3px;
  text-transform: uppercase;
  color: #fff;
  text-shadow: 0 0 10px #000;
}
.confirm-buttons {
  display: flex;
  justify-content: center;
  gap: 30px; /* separación clara entre botones */
}
.confirm-buttons button {	  
  margin: 0 10px;
  padding: 14px 40px;  	  
  font-size: 1.2rem;
  font-weight: bold;
  text-transform: uppercase;
  border: 2px solid #fff;
  background: transparent;
  color: #fff;
  cursor: pointer;
  transition: all 0.3s ease;  
}
.confirm-buttons button:hover {
  background: #fff;
  color: #000;
}
	
</style>

<script>	
// ==========================
// Canvas partículas dinámicas
// ==========================
const canvas = document.getElementById('bg-canvas');
const ctx = canvas.getContext('2d');
let particles = [];

function resizeCanvas() {
  canvas.width = window.innerWidth;
  canvas.height = window.innerHeight;
}
resizeCanvas();
window.addEventListener('resize', resizeCanvas);

function createParticles() {
  particles = [];
  for (let i = 0; i < 80; i++) {
    particles.push({
      x: Math.random() * canvas.width,
      y: Math.random() * canvas.height,
      r: Math.random() * 2 + 1,
      dx: (Math.random() - 0.5) * 0.5,
      dy: Math.random() * -1 - 0.2,
      color: Math.random() > 0.5 ? 'rgba(255,50,50,0.7)' : 'rgba(255,215,0,0.7)'
    });
  }
}
createParticles();

function animateParticles() {
  ctx.clearRect(0,0,canvas.width,canvas.height);
  particles.forEach(p => {
    ctx.beginPath();
    ctx.arc(p.x, p.y, p.r, 0, Math.PI*2);
    ctx.fillStyle = p.color;
    ctx.fill();
    p.x += p.dx;
    p.y += p.dy;
    if (p.y < 0) { p.y = canvas.height; p.x = Math.random() * canvas.width; }
  });
  requestAnimationFrame(animateParticles);
}
animateParticles();
			
// ==========================
// Selección + Confirmación
// ==========================
document.querySelectorAll('.faccion').forEach(faccion => {
  const btnSelect = faccion.querySelector('.select-btn');
  const videoDefault = faccion.querySelector('.video-default');
  const videoConfirm = faccion.querySelector('.video-confirm');
  const overlayDefault = faccion.querySelector('.overlay-default');
  const overlayConfirm = faccion.querySelector('.overlay-confirm');

  // Función para resetear solo esta facción
  function resetThisFaction() {
    videoConfirm.pause();	  
	videoConfirm.currentTime = 0; // reinicia el video	  	  
    videoConfirm.style.display = "none";
    overlayConfirm.style.display = "none";
    videoDefault.style.display = "block";
    overlayDefault.style.display = "flex";
  }
	
  function isChromeOrOpera() {
	const ua = navigator.userAgent;
	const vendor = navigator.vendor;
	const isChrome = /Chrome/.test(ua) && /Google Inc/.test(vendor);
	const isOpera = /OPR|Opera/.test(ua);
	return isChrome || isOpera;
  }

  // Click en el botón de selección
  if (btnSelect) {
    btnSelect.addEventListener('click', e => {
      e.preventDefault();

      // Si hay otra facción en confirmación, reseteamos solo esa
      const activeConfirm = document.querySelector('.video-confirm[style*="block"]');
      if (activeConfirm && !faccion.contains(activeConfirm)) {
        const parentFaction = activeConfirm.closest('.faccion');
        const parentOverlay = parentFaction.querySelector('.overlay-confirm');
        const parentVideoDefault = parentFaction.querySelector('.video-default');
        const parentVideoConfirm = parentFaction.querySelector('.video-confirm');

        parentVideoConfirm.pause();
        parentVideoConfirm.style.display = "none";
        parentOverlay.style.display = "none";
        parentVideoDefault.style.display = "block";
        parentFaction.querySelector('.overlay-default').style.display = "flex";
      }

      // Activar confirmación solo para esta facción
      videoDefault.style.display = "none";
      overlayDefault.style.display = "none";
      videoConfirm.style.display = "block";
      overlayConfirm.style.display = "flex";
      videoConfirm.play();
		
	  // Cuando el video termina, lo dejamos pausado en el último frame
	 videoConfirm.addEventListener("ended", () => {
		 videoConfirm.pause();
		 videoConfirm.currentTime = videoConfirm.duration; // queda en último frame
	 });
    });
  }

  // Botón YES
  
	const btnYes = document.querySelectorAll(".btn-yes");

if (btnYes) {
  btnYes.forEach(btn => {
    btn.addEventListener("click", e => {
      const faccion = btn.closest('.faccion');
      const videoConfirm = faccion.querySelector('.video-confirm');

      if (videoConfirm) videoConfirm.muted = true;

      // Overlay rojo común
      const overlayFull = document.createElement('div');
      Object.assign(overlayFull.style, {
        position: 'fixed',
        inset: '0',
        background: 'rgba(255,0,0,0.8)',
        zIndex: '9999',
        opacity: '0',
        transition: 'opacity 0.8s ease, transform 0.8s ease',
        transform: 'scale(0.8)'
      });
      document.body.appendChild(overlayFull);

      if (isChromeOrOpera()) {
        // === Chrome/Opera: captura frame en canvas -> img y animación sobre la img ===
        const canvas = document.createElement('canvas');
        canvas.width = videoConfirm.videoWidth || 1920;
        canvas.height = videoConfirm.videoHeight || 1080;
        const ctx = canvas.getContext('2d');
        try {
          ctx.drawImage(videoConfirm, 0, 0, canvas.width, canvas.height);
        } catch (err) {
          // si falla drawImage, caemos al fallback (animar video directamente)
        }

        const img = document.createElement('img');
        img.src = canvas.toDataURL('image/jpeg', 0.9);
        Object.assign(img.style, {
          position: 'absolute',
          inset: '0',
          width: '100%',
          height: '100%',
          objectFit: 'cover',
          zIndex: '4000',
          transform: 'scale(1)',
          filter: 'brightness(1) blur(0px)',
          transition: 'transform 0.8s ease, filter 0.8s ease'
        });
        faccion.appendChild(img);

        // animar la img + overlay con un rAF (da tiempo al navegador a pintar)
        requestAnimationFrame(() => {
          // pequeño timeout para asegurar paint (0-30ms funciona; 0 suele bastar en Chrome)
          setTimeout(() => {
            img.style.transform = 'scale(1.25)';
            img.style.filter = 'brightness(1.6) blur(6px)';
            overlayFull.style.opacity = '1';
            overlayFull.style.transform = 'scale(1.2)';
          }, 0);
        });
      } else {
			// Zoom y blur al video confirm			
			videoConfirm.style.transition = 'all 0.8s ease';
			videoConfirm.style.filter = 'brightness(2) blur(6px)';
			videoConfirm.style.transform = 'scale(1.2)';

			// Trigger animación overlay
			requestAnimationFrame(() => {
				overlayFull.style.opacity = '1';
				overlayFull.style.transform = 'scale(1.2)';
			});
      }

      // Cookies
      document.cookie = "faction=" + faccion.dataset.faccion + "; path=/; max-age=" + (60 * 60 * 24 * 365);
      document.cookie = "from_faction_selection=1; path=/; max-age=10";

      // Redirección después del efecto
      setTimeout(() => {
        window.location.href = "/";
      }, 1000);
    });
  });
}


  // Botón NO → volver al estado inicial de esta facción
  const btnNo = faccion.querySelector('.btn-no');
  if (btnNo) {
    btnNo.addEventListener('click', resetThisFaction);
  }

  // Si el mouse entra a esta facción mientras otra tiene confirmación, reseteamos solo la que tiene confirmación
  faccion.addEventListener('mouseenter', () => {
    const activeConfirm = document.querySelector('.video-confirm[style*="block"]');
    if (activeConfirm && !faccion.contains(activeConfirm)) {
      const parentFaction = activeConfirm.closest('.faccion');
      const parentOverlay = parentFaction.querySelector('.overlay-confirm');
      const parentVideoDefault = parentFaction.querySelector('.video-default');
      const parentVideoConfirm = parentFaction.querySelector('.video-confirm');

      parentVideoConfirm.pause();
	  parentVideoConfirm.currentTime = 0; // reinicia el video al cambiar de facción				
      parentVideoConfirm.style.display = "none";
      parentOverlay.style.display = "none";
      parentVideoDefault.style.display = "block";
      parentFaction.querySelector('.overlay-default').style.display = "flex";
    }
  });
});
	
// ==========================
// Evitar click derecho
// ==========================
document.addEventListener('contextmenu', e => e.preventDefault());
		
</script>
<?php } 