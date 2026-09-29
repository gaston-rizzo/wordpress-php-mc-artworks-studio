<?php
/**
 * Archivo: hud-intro.php
 * Contiene toda la pantalla intro 
 */

/* ========================================================================================
*  Función que muestra el HUD de Intro:
*  - Bloquea scroll y selección.
*  - Muestra contenedor fijo con animaciones y grilla.
*  - Simula barra de carga (0% → 100%) con sonido.
*  - Al 100%: abre compuertas GDI/NOD, aplica shake a videos, habilita botón Continue.
*  - Botón Continue: al hover y click reproduce sonidos y cierra HUD desbloqueando la página.
 ============================================================================================ */
function nod_gdi_hud_intro() {  
  ?>
  <style>
  :root{
    --gdi-blue:#1ea7ff;
    --gdi-gold:#ffd400;
    --nod-red:#ff2a2a;
    --bg-dark:#06060a;
    --hud-width:clamp(640px,42vw,880px);

    /* colores del efecto ojo de pez */
    /* --fisheye-color: #20ff9a; */
    --fisheye-color: #ff2a2a; /* rojo intenso */
    --fisheye-scan-opacity: .35;  
  }

  html.hud-lock, body.hud-lock{overflow:hidden!important;height:100vh!important;touch-action:none;}
  .hud-lock [class*="xoo-wsc"], .hud-lock [id*="xoo-wsc"]{ display:none!important; }
  html, body {user-select:none;-webkit-user-select:none;-moz-user-select:none;-ms-user-select:none;}

  /* === Fondo === */

  #hud-intro{position:fixed; inset:0; z-index:2147483647;
    font-family:"Orbitron", system-ui, sans-serif;
    background:var(--bg-dark); overflow:hidden;}
	  
    /* === Fondo común === */

	/*
	#hud-intro .hud-bg {
	  position: absolute;
	  inset: 0;
	  z-index: 0;
	  background-size: cover;
	  background-repeat: no-repeat;
	  background-position: center;
	  filter: contrast(1.05) brightness(.9) saturate(1.1);
	  opacity: 0;
	  transition: opacity 1s ease-in-out;
	}
	*/
	  
	#hud-intro .hud-bg {
	  position: absolute;
	  inset: 0;
	  z-index: 0;
	  opacity: 0;
	  transition: opacity 1s ease-in-out;
	  overflow: hidden; /* asegura que la imagen no se salga */
	}
	  
	/* cada imagen ocupa toda la pantalla */
	#hud-intro .hud-bg img {
	  width: 100%;
	  height: 100%;
	  object-fit: cover;  
	  /*object-fit: contain;*/
	  filter: contrast(1.05) brightness(.9) saturate(1.1);
	}
	  
	 /* El primero arranca visible */
	 #hud-intro .hud-bg:first-child {
  	 	opacity: 1;
	 }
	  
	/* Animaciones distintas */
	#hud-intro .hud-bg:nth-child(1) { animation: bgPan1 60s linear infinite; }
	#hud-intro .hud-bg:nth-child(2) { animation: bgPan2 65s linear infinite; }
	#hud-intro .hud-bg:nth-child(3) {
  		animation: bgPan3 70s ease-in-out infinite alternate;
	}	  
	#hud-intro .hud-bg:nth-child(4) {
  		animation: bgPan4 75s ease-in-out infinite alternate;
	}
	  	
	#hud-intro .hud-bg:nth-child(5) {
  		animation: bgPan5 70s ease-in-out infinite alternate;
	}			  	  
	/* Imagen 5: arranca completa y hace zoom suave cuando se activa */
	/*
	#hud-intro .hud-bg#bg5 {
    	transform: scale(1);               
    	transition: transform 20s linear;  
    	transform-origin: center center;   
	}
	*/	  
	#hud-intro .hud-bg:nth-child(6) {
  		animation: bgPan6 70s ease-in-out infinite alternate;
	}	  
	#hud-intro .hud-bg:nth-child(7) {
  		animation: bgPan7 75s ease-in-out infinite alternate;
	}
	  
	/* Keyframes variados para mover fondos de manera diferente */
	@keyframes bgPan1 { /* Diagonal ↘ */ from {background-position: 0% 0%;}   to {background-position: 150% 150%;} }
	@keyframes bgPan2 { /* Diagonal ↗ */ from {background-position: 0% 100%;} to {background-position: 150% -50%;} }
		  
	@keyframes bgPan3 {
	  from { transform: translateX(0); }
	  to   { transform: translateX(-5%); }
	} /* se mueve de der a izq */
	  	  
	@keyframes bgPan4 {
  	  from { transform: translateX(0); }
  	  to   { transform: translateX(5%); } /* se mueve de izq a der */
	}	  
	
	/*
	@keyframes bgZoomIn5 {
	  from { transform: scale(1); }   
	  to   { transform: scale(1.3); } 
	}
	*/	  	  
	@keyframes bgPan5 {
  	  	from { transform: translateX(0%); }
  		to   { transform: translateX(-5%); }
	} /* se mueve de der a izq */
	  
	@keyframes bgPan6 {
	  from { transform: translateX(0); }
	  to   { transform: translateX(5%); }
	} /* se mueve de der a izq */
	  
	@keyframes bgPan7 {
	  from { transform: translateX(0); }
	  to   { transform: translateX(-5%); }
	} /* se mueve de der a izq */
	  	
	/* === Cada imagen === */
    #bg1 { background-image: url("/images/image1.jpg"); opacity: 1; }
    #bg2 { background-image: url("/images/image2.jpg"); }
	#bg3 { 
    	background-image: url("/images/image3.jpg"); 
    	background-size: 300% auto;  /* más ancho para que el pan lateral se note */
    	background-position: 0% 50%; 
	}
    #bg4 { background-image: url("/images/image4.jpg"); }
    #bg5 { background-image: url("/images/image5.jpg"); }
	#bg6 { background-image: url("/images/image6.jpg"); }	  
	#bg7 { background-image: url("/images/image7.jpg"); }
	  	  
  #hud-intro::before{
    content:""; position:absolute; inset:0; z-index:1; pointer-events:none;
    background:repeating-linear-gradient(0deg, rgba(255,255,255,.06) 0 2px, transparent 4px);
    mix-blend-mode:overlay; animation:scanline 1.2s linear infinite;
  }
  @keyframes scanline{0%{background-position-y:0}100%{background-position-y:4px}}

  #hud-intro::after{
    content:""; position:absolute; inset:0; z-index:1; pointer-events:none;
    background-image:
      linear-gradient(rgba(255,255,255,.03) 1px, transparent 1px),
      linear-gradient(90deg, rgba(255,255,255,.03) 1px, transparent 1px);
    background-size:80px 80px; mix-blend-mode:overlay;
  }

  /* ========= Lente ojo de pez ========= */
  .bg-fisheye{ position:absolute; inset:0; z-index:2; pointer-events:none; display:grid; place-items:center; }
  .bg-fisheye .lens{
    position:relative; width:min(132vmin, 1600px); aspect-ratio:1/1; border-radius:50%; overflow:hidden;
    box-shadow:0 0 120px rgba(0,0,0,.35) inset, 0 0 100px rgba(0,0,0,.18);
    mix-blend-mode:screen;
  }
  .bg-fisheye .fisheye-grid{ position:absolute; inset:0; color: var(--fisheye-color); opacity:.22; filter: drop-shadow(0 0 4px currentColor); animation:feSpin 80s linear infinite; }
  .bg-fisheye .fisheye-grid path{ fill:none; stroke:currentColor; stroke-linecap:round; vector-effect:non-scaling-stroke; stroke-width:2; }
  @keyframes feSpin{to{transform:rotate(360deg)}}
  .bg-fisheye .lens::before{
    content:""; position:absolute; inset:-2px; border-radius:50%;
    background:conic-gradient(from 0deg, color-mix(in oklab, var(--fisheye-color) 40%, transparent) 0deg, transparent 60deg);
    animation:feSweep 4.2s linear infinite; opacity:var(--fisheye-scan-opacity); mix-blend-mode:screen;
  }
  @keyframes feSweep{to{transform:rotate(360deg)}}
  .bg-fisheye .lens::after{
    content:""; position:absolute; inset:0; border-radius:50%;
    background:radial-gradient(circle at center, transparent 60%, rgba(0,0,0,.3) 78%, rgba(0,0,0,.6) 100%);
    mix-blend-mode:multiply;
  }

  /* Diagonales en movimiento */
  .super-lines{ position:absolute; inset:0; z-index:3; pointer-events:none; }
  .super-lines .beam{
    position:absolute; left:-60%; width:220%; height:30vh;
    transform:rotate(-25deg); filter:blur(12px); opacity:.9; mix-blend-mode:screen;
  }
  .beam.b1{top:-8%;background:linear-gradient(90deg,transparent 10%,rgba(30,167,255,.38) 40%,rgba(30,167,255,.18) 60%,transparent 85%);animation:beamMove1 18s linear infinite;}
  .beam.b2{top:18%; height:26vh;background:linear-gradient(90deg,transparent 10%,rgba(255,160,60,.28) 40%,rgba(255,80,40,.18) 60%,transparent 85%);animation:beamMove2 22s linear infinite reverse;}
  .beam.b3{top:38%; height:28vh;background:linear-gradient(90deg,transparent 10%,rgba(255,42,42,.36) 40%,rgba(255,30,30,.2) 60%,transparent 85%);animation:beamMove3 26s linear infinite;}
  @keyframes beamMove1{0%{transform:translateX(-60%) rotate(-25deg)}100%{transform:translateX(60%) rotate(-25deg)}}
  @keyframes beamMove2{0%{transform:translateX(60%) rotate(-25deg)}100%{transform:translateX(-60%) rotate(-25deg)}}
  @keyframes beamMove3{0%{transform:translateX(-50%) rotate(-25deg)}100%{transform:translateX(50%) rotate(-25deg)}}

  /* === Cajas de video con marco C&C === */
  .hud-box{
    position:absolute; width:var(--hud-width); aspect-ratio:16/9; z-index:40;
    box-shadow:0 28px 72px rgba(0,0,0,.65);
  }
  #hud-top-left{top:4vh; left:3vw; box-shadow:0 0 120px rgba(30,167,255,.15);}
  #hud-bottom-right{bottom:20vh; right:3vw; box-shadow:0 0 120px rgba(255,34,34,.12);}

  .mc-panel-video{ position:relative; width:100%; height:100%; border-radius:18px; overflow:visible; }
  .mc-panel-video__shape{
    position:absolute; inset:0;
    clip-path: polygon(0 0, 94% 0, 100% 6%, 100% 100%, 8% 100%, 0 88%);
  }
  .mc-panel-video__shape video{ width:100%; height:100%; object-fit:cover; display:block; border-radius:0; }
  .mc-panel-video__edges{ position:absolute; inset:0; pointer-events:none; z-index:3; }
  .mc-panel-video__edges svg{ width:100%; height:100%; display:block; }
  .mc-panel-video__edges path{
    stroke-width:2.6px; stroke-linejoin:round; stroke-linecap:round; vector-effect:non-scaling-stroke; fill:none; opacity:.96;
    filter: drop-shadow(0 0 18px rgba(255,255,255,.02));
    animation:mc-edge-pulse 3.6s ease-in-out infinite;
  }
  @keyframes mc-edge-pulse{ 0%{opacity:.85} 50%{opacity:1} 100%{opacity:.85} }
  #hud-top-left .mc-panel-video__edges path{ filter: drop-shadow(0 0 28px rgba(30,167,255,.35)); }
  #hud-bottom-right .mc-panel-video__edges path{ filter: drop-shadow(0 0 28px rgba(255,34,34,.35)); stroke:#ff0000; }
	  
  /* Badges + radares */
  .hud-badge{position:absolute; top:12px; left:14px; z-index:45; padding:8px 12px; font-size:13px; letter-spacing:.12em; text-transform:uppercase; border-radius:10px; font-weight:900;}
  .hud-badge.gdi{color:#051225;background:linear-gradient(90deg,var(--gdi-gold),#fff8da);}
  .hud-badge.nod{color:#fff;background:linear-gradient(90deg,#8b0000,var(--nod-red));}
	  
  .hud-radar{
    --radar-color:#0f0;
    position:absolute; top:20px; right:24px; width:84px; height:84px; border-radius:50%; overflow:hidden; z-index:45;
    border:2px solid var(--radar-color); box-shadow:0 0 18px rgba(0,0,0,.5); color: var(--radar-color);
    background: radial-gradient(circle at center, rgba(0,0,0,.65) 0 70%, rgba(0,0,0,.88) 100%);
  }
  .hud-radar.gdi{ --radar-color: var(--gdi-blue); }
  .hud-radar.nod{ --radar-color: var(--nod-red); }
  .hud-radar .radar-grid{ position:absolute; inset:0; z-index:2; pointer-events:none; opacity:.28; filter: drop-shadow(0 0 2px currentColor); }
  .hud-radar .radar-grid path{ stroke:currentColor; vector-effect:non-scaling-stroke; stroke-width:2; opacity:0.45; }
  .hud-radar::before{
    content:""; position:absolute; inset:-2px; border-radius:50%;
    background:conic-gradient(from 0deg, color-mix(in oklab,var(--radar-color) 35%, transparent) 0deg, transparent 60deg);
    animation:radarSweep 2.6s linear infinite; mix-blend-mode:screen; z-index:3;
  }
	  
/* === BADGE + RADAR + videofallback ocultos hasta que se abra la compuerta === */
.mc-panel-video__shape .hud-badge,
.mc-panel-video__shape .hud-radar,
.mc-panel-video__shape .video-fallback {
  opacity: 0;
  visibility: hidden;
  transform: translateY(6px) scale(.96);
  transition:
    opacity .28s ease,
    transform .28s ease,
    visibility 0s linear .28s; /* mantiene hidden mientras anima */
}

/* Al abrir la compuerta (badge + radar aparecen, y fallback si corresponde) */	  
/* Al abrir la compuerta (clase .open en .mc-door) los mostramos */	  
.mc-panel-video__shape .mc-door.open ~ .hud-badge,
.mc-panel-video__shape .mc-door.open ~ .hud-radar,
.mc-panel-video__shape .mc-door.open ~ .video-fallback {
  opacity: 1;
  visibility: visible;
  transform: none;
  transition-delay: .55s; /* ajustá este delay a gusto (la puerta tarda 900ms) */
}
	  
/* Cuando el real cargó → ocultamos fallback */
.video-fallback.hidden {
  opacity: 0 !important;
  visibility: hidden !important;
  transition-delay: 0s !important;
}
	  
  @keyframes radarSweep{0%{transform:rotate(0)}100%{transform:rotate(360deg)}}
  .blip{position:absolute; width:7px; height:7px; border-radius:50%; background:var(--radar-color); box-shadow:0 0 10px var(--radar-color),0 0 18px var(--radar-color); opacity:0; transform:scale(.6); animation:blip 2.4s infinite; z-index:4;}
  @keyframes blip{0%{opacity:0; transform:scale(.4)}20%{opacity:1; transform:scale(1)}60%{opacity:.3}100%{opacity:0; transform:scale(1.6)}}

  /* ======== PANEL INIT + CONTROLES ======== */
  #hud-init-panel{position:absolute; left:50%; top:50%; transform:translate(-50%,-50%); z-index:80; width:min(92vw,560px); padding:28px 32px; color:#fff; text-align:center; background:linear-gradient(180deg, rgba(10,12,18,.72), rgba(6,6,10,.58)); border:1px solid rgba(255,255,255,.15); border-radius:18px; backdrop-filter: blur(8px) saturate(1.1); box-shadow:0 24px 60px rgba(0,0,0,.55),0 0 60px rgba(255,42,42,.12),0 0 60px rgba(30,167,255,.12);}
  #hud-init-panel .title{font-weight:900; letter-spacing:.18em; text-transform:uppercase; font-size:38px; text-shadow:0 0 28px rgba(255,255,255,.2);}
  #hud-init-panel .percent{display:block;font-size:22px;margin-top:10px;}
  #hud-init-panel .hud-progress{width:100%;height:12px;background:rgba(255,255,255,.08);border-radius:9px;margin-top:10px;overflow:hidden;}
  #hud-init-panel .hud-progress-bar{width:0;height:100%;background:linear-gradient(90deg,#ff7a3b,#ff2a2a);transition:width .08s linear;}
  #hud-init-panel.vanish{animation:fadeOutPanel .35s ease forwards;}
  @keyframes fadeOutPanel{to{opacity:0;visibility:hidden}}

  #hud-controls{position:absolute; left:50%; transform:translateX(-50%); bottom:5.2vh; z-index:20; display:flex; flex-direction:column; align-items:center; gap:12px;}
  #hud-ticker{position:relative; overflow:hidden; width:min(92vw,720px); height:36px; border:2px solid rgba(255,255,255,.2); border-radius:12px; background:rgba(0,0,0,.55); color:#eee; font-size:14px; letter-spacing:.14em; text-transform:uppercase; font-weight:600;}
  #hud-ticker .marquee{display:inline-block; white-space:nowrap; will-change:transform; padding:0 16px; position:absolute; top:50%; transform:translateY(-50%);}
	  
/* === Botón estilo C&C futurista === */
#hud-continue {
  position: relative;
  min-width: 360px;
  padding: 20px 56px;
  font-size: 22px;
  letter-spacing: .24em;
  text-transform: uppercase;
  font-weight: 900;
  color: #fff;
  border: none;
  border-radius: 18px;
  overflow: hidden;
  cursor: pointer;

  /* 🎮 Fondo rojo-oscuro gaming */
  background: linear-gradient(135deg,
    #0b0b0f 0%,
    #1a0000 20%,
    #ff1a1a 50%,
    #8b0000 80%,
    #0b0b0f 100%
  );

  /* Glow exterior base */
  box-shadow:
    0 0 24px rgba(255, 40, 40, .55),
    0 0 64px rgba(255, 10, 10, .45),
    inset 0 0 22px rgba(255, 10, 10, .35);

  pointer-events: none;
  opacity: .48;
  transition: all .28s ease-in-out;
}

#hud-continue.ready {
  pointer-events: auto;
  opacity: 1;
}

/* === Borde metálico biselado === */
#hud-continue::before {
  content: "";
  position: absolute;
  inset: 0;
  border-radius: 18px;
  padding: 2px;
  background: linear-gradient(135deg,
    #ff4040,
    #ff0000,
    #330000,
    #990000,
    #ff2020
  );
  -webkit-mask: linear-gradient(#fff 0 0) content-box,
                 linear-gradient(#fff 0 0);
  -webkit-mask-composite: xor;
          mask-composite: exclude;
  z-index: 2;
  pointer-events: none;
}

/* === Scanlines en el botón === */
#hud-continue::after {
  content: "";
  position: absolute;
  inset: 0;
  border-radius: 18px;
  background: repeating-linear-gradient(
    0deg,
    rgba(255,255,255,.06) 0 2px,
    transparent 4px
  );
  mix-blend-mode: overlay;
  animation: btnScan 1.2s linear infinite;
  pointer-events: none;
  z-index: 3;
}
@keyframes btnScan {
  0% { background-position-y: 0; }
  100% { background-position-y: 4px; }
}
	  	
/* ===== HUD TOP LEFT ===== */
#hud-top-left {
  position: relative; /* necesario para que los hijos absolutos se posicionen respecto a este contenedor */
}
	  	  
/* ===== PANEL LISTBOX ===== */	  	 
#hud-music-panel {
  position: absolute;
  top: 100%;           /* justo debajo del contenedor */
  left: 0;             /* alineado a la izquierda del contenedor */
  margin-top: 2rem;  /* espacio entre el top-left y el panel */
  width: 500px;        /* ancho del panel */
  background: rgba(0, 0, 0, 0.85);
  border: 2px solid #00ff66;
  border-radius: 8px;
  padding: 0.8rem 1rem;
  display: flex;
  flex-direction: column; /* columnas para las tracks */
  font-family: "Share Tech Mono", monospace;
  color: #00ff66;
  font-size: 1.1rem;
  z-index: 9999;
}
	  	  
/* Cada línea de texto (canción) */
#hud-music-panel .track {
  cursor: pointer;
  padding: 0.2rem 0; /* solo espacio vertical */
  transition: all 0.2s;
}

/* Hover */
#hud-music-panel .track:hover {
  background: #003300;
  color: #00ff99;
  box-shadow: 0 0 10px #00ff66;
}

/* Canción activa */
#hud-music-panel .track.active {
  background: #00ff66;
  color: #000;
  font-weight: bold;
  box-shadow: 0 0 15px #00ff66;
}
	 	 
/* === Overlay con grilla digital HUD === */
#hud-continue .grid-overlay {
  position: absolute;
  inset: 0;
  border-radius: 18px;
  background: 
    linear-gradient(90deg, rgba(255,0,0,.08) 1px, transparent 1px),
    linear-gradient(0deg,  rgba(255,0,0,.08) 1px, transparent 1px);
  background-size: 16px 16px;
  z-index: 1;
  pointer-events: none;
}

/* === Pulso expansivo desde el centro === */
#hud-continue .pulse {
  position: absolute;
  top: 50%; left: 50%;
  width: 12px; height: 12px;
  background: rgba(255,0,0,.7);
  border-radius: 50%;
  transform: translate(-50%, -50%);
  z-index: 0;
  animation: pulseExpand 2.8s ease-out infinite;
}
@keyframes pulseExpand {
  0%   { transform: translate(-50%, -50%) scale(0.4); opacity: .8; }
  70%  { transform: translate(-50%, -50%) scale(8);   opacity: 0; }
  100% { opacity: 0; }
}

/* === Texto épico (brillo solo al hover) === */
#hud-continue span {
  position: relative;
  z-index: 4;
  color: #fff;
  text-shadow:
    0 0 8px rgba(255,60,60,.8),
    0 0 18px rgba(255,20,20,.9);
}
#hud-continue.ready:hover span {
  background: linear-gradient(90deg,#fff,#ff4040,#fff);
  background-size: 200%;
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  animation: txtShine 2s linear infinite;
}
@keyframes txtShine {
  0%   { background-position: 0%; }
  100% { background-position: 200%; }
}

/* === Hover épico con glow dinámico === */	  
/*
#hud-continue.ready:hover {
  animation: hoverPulse 1s ease-in-out infinite;
}
*/
	  
/* Latido SOLO si NO estoy en click */
#hud-continue.ready:hover:not(.mega-effect):not(.jump-effect) {
  animation: hoverPulse 1s ease-in-out infinite;
}	 
	  
@keyframes hoverPulse {
  0%, 100% {
    transform: scale(1.05);
    box-shadow:
      0 0 34px rgba(255, 60, 60, .9),
      0 0 88px rgba(255, 20, 20, .7),
      inset 0 0 28px rgba(255, 20, 20, .55);
  }
  50% {
    transform: scale(1.1);
    box-shadow:
      0 0 54px rgba(255, 80, 80, 1),
      0 0 108px rgba(255, 30, 30, .9),
      inset 0 0 38px rgba(255, 40, 40, .7);
  }
}
	/* efecto pulsante/futurista al click */	  
	#hud-continue.mega-effect {
	  animation: megaPulse 0.6s ease-out forwards;
	  box-shadow:
		0 0 16px #ff0000,
		0 0 32px #ff4040,
		0 0 64px #ff8080,
		inset 0 0 16px #ff1a1a;
	  transform: scale(1.15) rotateZ(2deg);
	}
	  
/* Efecto para el click */
#hud-continue.jump-effect {
  animation: jumpPulse 0.6s ease-out forwards;
}

@keyframes jumpPulse {
  0%   { transform: scale(1) rotateZ(0deg); }
  50%  { transform: scale(1.2) rotateZ(2deg); }
  100% { transform: scale(1) rotateZ(0deg); }
}

	@keyframes megaPulse {
	  0% { transform: scale(1) rotateZ(0deg); }
	  50% { transform: scale(1.2) rotateZ(2deg); }
	  100% { transform: scale(1) rotateZ(0deg); }
	}  	  
	  
  #hud-continue.ready{pointer-events:auto; opacity:1;}
  #hud-intro.closing{animation:fadeOut .4s ease forwards;}
  @keyframes fadeOut{ to{ opacity:0; visibility:hidden; } }

/* =========================================================
   🔐 COMPUERTAS ESCALABLES GDI & NOD
   ========================================================= */

/* ===== CONTENEDOR GENERAL ===== */
.mc-door {
  position: absolute;
  inset: 0;
  display: flex;
  z-index: 2;
  pointer-events: none;
}
.mc-door::before { display: none !important; }

/* ===== ANIMACIÓN DE APERTURA ===== */
.mc-door.open .half.left  { transform: translateX(-102%); }
.mc-door.open .half.right { transform: translateX(102%); }

/* ===== PUERTA NOD (abajo derecha) ===== */
#hud-bottom-right .mc-door .half.left,
#hud-bottom-right .mc-door .half.right {
  flex: 1;
  will-change: transform;
  transition: transform 900ms cubic-bezier(.22,1,.36,1);
  background: repeating-linear-gradient(135deg, var(--nod-red) 0 2%, #0b0b0f 2% 4%);
  background-size: 200% 100%;
  border-top: 0.25% solid rgba(255,255,255,.08);
  border-bottom: 0.25% solid rgba(0,0,0,.45);
  box-shadow: inset 0 0 2.5% rgba(0,0,0,.55);
  position: relative;
}
#hud-bottom-right .mc-door .half.left { background-position: left top; }
#hud-bottom-right .mc-door .half.right { background-position: right top; }

/* ===== PUERTA GDI (arriba izquierda) ===== */
#hud-top-left .mc-door .half.left,
#hud-top-left .mc-door .half.right {
  flex: 1;
  will-change: transform;
  transition: transform 900ms cubic-bezier(.22,1,.36,1);
  background: repeating-linear-gradient(135deg, var(--gdi-gold) 0 2%, #0b2038 2% 4%);
  background-size: 200% 100%;
  border-top: 0.25% solid rgba(255,255,255,.08);
  border-bottom: 0.25% solid rgba(0,0,0,.45);
  box-shadow: inset 0 0 2.5% rgba(0,0,0,.55);
  position: relative;
}
#hud-top-left .mc-door .half.left { background-position: left top; }
#hud-top-left .mc-door .half.right { background-position: right top; }

/* ===== Glow central láser NOD estable con pulso ===== */
#hud-bottom-right .mc-door::after {
  content: "";
  position: absolute;
  top: 0; bottom: 0;
  left: 50%;
  transform: translateX(-50%);
  width: 6px; /* barra fina */
  border-radius: 3px;
  background: linear-gradient(180deg,
    rgba(255,100,100,1) 0%,
    rgba(255,0,0,0.6) 40%,
    rgba(255,0,0,0) 100%);
  box-shadow:
    0 0 20px 4px rgba(255,50,50,0.9),
    0 0 50px 15px rgba(255,0,0,0.6),
    0 0 80px 30px rgba(255,0,0,0.3);
  animation: nodBeamGlow 1.5s infinite alternate ease-in-out;
  z-index: 4;
  pointer-events: none;
}

/* ===== Solo pulso sutil de brillo ===== */
@keyframes nodBeamGlow {
  0%   { opacity:0.5; box-shadow: 0 0 15px 3px rgba(255,50,50,0.7), 0 0 40px 10px rgba(255,0,0,0.5), 0 0 70px 25px rgba(255,0,0,0.2); }
  50%  { opacity:1;   box-shadow: 0 0 25px 6px rgba(255,50,50,0.9), 0 0 50px 15px rgba(255,0,0,0.6), 0 0 80px 30px rgba(255,0,0,0.3); }
  100% { opacity:0.6; box-shadow: 0 0 18px 4px rgba(255,50,50,0.8), 0 0 45px 12px rgba(255,0,0,0.55), 0 0 75px 28px rgba(255,0,0,0.25); }
}
	  	  
/* ===== Glow central láser GDI estable con pulso ===== */
#hud-top-left .mc-door::after {
  content: "";
  position: absolute;
  top: 0; bottom: 0;
  left: 50%;
  transform: translateX(-50%);
  width: 6px; /* barra fina */
  border-radius: 3px;
  background: linear-gradient(180deg,
    rgba(255,215,0,1) 0%,       /* dorado intenso */
    rgba(255,215,0,0.6) 40%,
    rgba(255,215,0,0) 100%);
  box-shadow:
    0 0 20px 4px rgba(255,215,0,0.9),
    0 0 50px 15px rgba(255,215,0,0.6),
    0 0 80px 30px rgba(255,215,0,0.3);
  animation: gdiBeamGlow 1.5s infinite alternate ease-in-out;
  z-index: 4;
  pointer-events: none;
}

/* ===== Pulso suave del láser GDI ===== */
@keyframes gdiBeamGlow {
  0%   { opacity:0.5; box-shadow: 0 0 15px 3px rgba(255,215,0,0.7), 0 0 40px 10px rgba(255,215,0,0.5), 0 0 70px 25px rgba(255,215,0,0.2); }
  50%  { opacity:1;   box-shadow: 0 0 25px 6px rgba(255,215,0,0.9), 0 0 50px 15px rgba(255,215,0,0.6), 0 0 80px 30px rgba(255,215,0,0.3); }
  100% { opacity:0.6; box-shadow: 0 0 18px 4px rgba(255,215,0,0.8), 0 0 45px 12px rgba(255,215,0,0.55), 0 0 75px 28px rgba(255,215,0,0.25); }
}
	  	  
/* ===== Luces laterales rojas ===== */
#hud-bottom-right .mc-door::before {
  content:"";
  position:absolute;
  top:0; bottom:0;
  left:0; right:0;
  background:
    radial-gradient(circle at 25% 50%, rgba(255,0,0,0.25), transparent 70%),
    radial-gradient(circle at 75% 50%, rgba(255,0,0,0.25), transparent 70%);
  mix-blend-mode: screen;
  animation: sirenGlow 1.5s infinite alternate;
  z-index:3;
  pointer-events:none;
}

/* ===== Sirena rotatoria (simulación giro) ===== */
@keyframes sirenGlow {
  0%   { opacity:0.2; filter:blur(2px) brightness(1); }
  50%  { opacity:0.8; filter:blur(4px) brightness(1.3); }
  100% { opacity:0.4; filter:blur(3px) brightness(1); }
}

/* ===== Scanners láser diagonales ===== */
/*
#hud-bottom-right .mc-door .laser,
#hud-top-left .mc-door .laser {
  position: absolute;
  width: 2px;
  height: 120%;
  background: rgba(255,0,0,0.9);
  box-shadow: 0 0 15px rgba(255,0,0,0.9), 0 0 35px rgba(255,0,0,0.6);
  transform: rotate(45deg);
  top: -10%;
  left: -20%;
  animation: laserMove 2.5s linear infinite;
  z-index: 99; 
  pointer-events: none;
}
*/

/*
#hud-bottom-right .mc-door .laser:nth-child(2),
#hud-top-left .mc-door .laser:nth-child(2) {
  transform: rotate(-45deg);
  left: auto;
  right: -20%;
  animation-delay: 1.25s;
}
*/

/* Animación movimiento láser */
@keyframes laserMove {
  0%   { transform: translateX(0) rotate(var(--angle,45deg)); opacity:0.2; }
  25%  { opacity:1; }
  50%  { transform: translateX(120%) rotate(var(--angle,45deg)); opacity:0.6; }
  75%  { opacity:1; }
  100% { transform: translateX(0) rotate(var(--angle,45deg)); opacity:0.2; }
}

/* ===== Cierre suave de la linea del medio cuando se abre la puerta NOD ===== */
#hud-bottom-right .mc-door.open::after,
#hud-bottom-right .mc-door.open::before {
  opacity: 0 !important;
  transform: scaleY(0.3) !important;
  animation: none !important; /* 🚨 detiene la animación */
  transition: all 0.9s ease;
}

/* ===== Cierre suave de la linea del medio cuando se abre la puerta GDI ===== */
#hud-top-left .mc-door.open::after,
#hud-top-left .mc-door.open::before {
  opacity: 0 !important;
  transform: scaleY(0.3) !important;
  animation: none !important; /* detiene la animación de la puerta */
  transition: all 0.9s ease;
}

/* ===== Shake metálico ===== */
@keyframes metalShake {
  0%   { transform:translateX(0); }
  20%  { transform:translateX(-6px); }
  40%  { transform:translateX(5px); }
  60%  { transform:translateX(-4px); }
  80%  { transform:translateX(3px); }
  100% { transform:translateX(0); }
}
.mc-panel-video.shake { animation: metalShake .48s ease-in-out; }

  .mc-panel-video.impact { animation: metalImpact .32s ease-in-out; }
	  
	/* Pulso expansivo extra para el radar */
	.bg-fisheye .lens::after {
	  content:"";
	  position:absolute;
	  inset:0;
	  border-radius:50%;
	  pointer-events:none;
	  background: radial-gradient(circle,
		rgba(255,42,42,.35) 0%,
		transparent 70%);
	  animation: radarPulse 3s ease-out infinite;
	  mix-blend-mode: screen;
	}

	@keyframes radarPulse {
	  0%   { transform: scale(0.2); opacity:.6; }
	  70%  { transform: scale(1); opacity:.15; }
	  100% { transform: scale(1.2); opacity:0; }
	}
  </style>
  <?php
};

add_action('wp_body_open', function () {  
  ?>
  <div id="hud-intro">	  	  	  
	<div id="bg1" class="hud-bg"><img src="/images/image1.jpg" alt=""></div>
	<div id="bg2" class="hud-bg"><img src="/images/image2.jpg" alt=""></div>
	<div id="bg3" class="hud-bg"><img src="/images/image3.jpg" alt=""></div>
	<div id="bg4" class="hud-bg"><img src="/images/image4.jpg" alt=""></div>
	<div id="bg5" class="hud-bg"><img src="/images/image5.jpg" alt=""></div>
	<div id="bg6" class="hud-bg"><img src="/images/image6.jpg" alt=""></div>
	<div id="bg7" class="hud-bg"><img src="/images/image7.jpg" alt=""></div>
	
    <!-- Lente con grilla curvada -->
    <div class="bg-fisheye"><div class="lens">		
		 <div class="pulse"></div>		
		</div></div>

    <div class="super-lines">
      <div class="beam b1"></div>
      <div class="beam b2"></div>
      <div class="beam b3"></div>
    </div>
	  
	<script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>
	  	  
    <!-- === GDI FEED === -->
    <div class="hud-box" id="hud-top-left">
      <div class="mc-panel-video" aria-hidden="true">
        <div class="mc-panel-video__shape">
          <!-- 🔐 Compuerta GDI (dentro del clip-path) -->
          <div class="mc-door">
            <div class="half left"></div>
            <div class="half right"></div>
			<!--<div class="laser"></div>
  			<!--<div class="laser"></div>-->
          </div>

          <!-- Badge + Radar DETRÁS de la compuerta (están dentro del shape) -->
          <span class="hud-badge gdi">GDI FEED</span>
          <div class="hud-radar gdi">
            <span class="blip" style="top:22%;left:38%;animation-delay:.25s"></span>
            <span class="blip" style="top:58%;left:68%;animation-delay:1.1s"></span>
            <span class="blip" style="top:36%;left:16%;animation-delay:2.0s"></span>
          </div>
						
		  <div class="video-fallback" id="GDI-fallback">
    	  	<video src="/videos/gdi-gallery/video8_main.mp4" autoplay muted loop playsinline></video>			  			  
  		  </div>						
          <video id="GDIvideo" muted loop playsinline></video>
								  			
        </div>

        <div class="mc-panel-video__edges" aria-hidden="true">
          <svg viewBox="0 0 100 100" preserveAspectRatio="none" aria-hidden="true">
            <defs>
              <linearGradient id="mc-edge-grad-gdi" x1="0%" y1="0%" x2="100%" y2="100%">
                <stop offset="0%"   stop-color="#1ea7ff"/>
                <stop offset="40%"  stop-color="#0066ff"/>
                <stop offset="70%"  stop-color="#00ffcc"/>
                <stop offset="100%" stop-color="#0099ff"/>
              </linearGradient>
            </defs>
            <path d="M 1.5 0 L 94 0 L 100 6 L 100 97.333 A 1.5 2.667 0 0 1 98.5 100 L 8 100 L 0 88 L 0 2.667 A 1.5 2.667 0 0 1 1.5 0 Z"
                  stroke="url(#mc-edge-grad-gdi)"/>
          </svg>
        </div>
      </div>		
	  <div id="hud-music-panel">
		<div class="track" data-src="/songs/Main_Theme">MAIN THEME</div>
	  	<div class="track" data-src="/songs/The_Girl_Who_Loved_Joseph_Kane">THE GIRL WHO LOVED JOSEPH KANE</div>						
		<div class="track" data-src="/songs/Irina_a_Name_Of_Steel_And_Might">IRINA, A NAME OF STEEL AND MIGHT</div>
		<div class="track" data-src="/songs/Maya_the_Living_Machine_Gun">MAYA, THE LIVING MACHINE GUN</div>
		<div class="track" data-src="/songs/La_chica_que_amaba_a_Joseph_Kane_VERSION_1">LA CHICA QUE AMABA A JOSEPH KANE VERSION 1</div>
		<div class="track" data-src="/songs/La_chica_que_amaba_a_Joseph_Kane_VERSION_2">LA CHICA QUE AMABA A JOSEPH KANE VERSION 2</div>
		<div class="track" data-src="/songs/Me_Voy_con_Vos_Kane_VERSION_1">ME VOY CON VOS KANE VERSION 1</div>
		<div class="track" data-src="/songs/Me_Voy_con_Vos_Kane_VERSION_2">ME VOY CON VOS KANE VERSION 1</div>
	  </div>
	  <audio id="hud-audio" preload="none" loop playsinline></audio>		
	</div>
	  
	<script>
		document.addEventListener("DOMContentLoaded", function () {
		  const backgrounds = document.querySelectorAll("#hud-intro .hud-bg");
		  let index = 0;
		  setInterval(() => {
			  const prev = index;
			  index = (index + 1) % backgrounds.length;
			  // fade
			  backgrounds[prev].style.opacity = "0";
			  backgrounds[index].style.opacity = "1";
		}, 6000);
		});
	</script>
	  
	<script>
		document.addEventListener("DOMContentLoaded", () => {
		  const audio = document.getElementById("hud-audio");
		  const tracks = document.querySelectorAll("#hud-music-panel .track");

		  // Guarda la posición de cada canción
		  const trackPositions = {};

		  function fadeIn(audioElement, duration = 3000) {
			  let volumeTarget = 1;
			  audioElement.volume = 0;
			  audioElement.play().catch(() => {});
			  const stepTime = 50;
			  const step = stepTime / duration;
			  const fadeInterval = setInterval(() => {
				if (audioElement.volume >= volumeTarget || audioElement.paused) {
				  clearInterval(fadeInterval);
				  return;
				}
				audioElement.volume = Math.min(audioElement.volume + step, volumeTarget);
			  }, stepTime);
		  }

		  // Guarda el tiempo actual del track activo
		  audio.addEventListener("timeupdate", () => {
				if (audio.dataset.currentTrack) {
				  trackPositions[audio.dataset.currentTrack] = audio.currentTime;
				}
			  });

			  // Evento click en las pistas
			  tracks.forEach(track => {
				track.addEventListener("click", () => {
				  const newSrc = track.dataset.src;

				  // Si ya está activa, no reiniciar nada
				  if (audio.dataset.currentTrack === newSrc) return;

				  // Guarda tiempo del track anterior
				  if (audio.dataset.currentTrack) {
					trackPositions[audio.dataset.currentTrack] = audio.currentTime;
				  }

				  // Cambia visual
				  tracks.forEach(t => t.classList.remove("active"));
				  track.classList.add("active");

				  // Cambiar pista SIN reiniciar tiempo
				  audio.pause();

				  // Si es la misma fuente, simplemente seguir desde ahí
				  if (audio.src.includes(newSrc)) {
					const resume = trackPositions[newSrc] || 0;
					audio.currentTime = resume;
					fadeIn(audio, 2000);
					return;
				  }

				  // Cambiar a nueva fuente
				  audio.dataset.currentTrack = newSrc;
				  audio.src = newSrc;

				  // Esperar a que cargue metadata antes de setear tiempo
				  audio.addEventListener(
					"loadedmetadata",
					() => {
					  const resumeTime = trackPositions[newSrc] || 0;
					  audio.currentTime = resumeTime;
					  if (!audio.muted) {
						fadeIn(audio, 2000);
					  } else {
						audio.play().catch(() => {});
					  }
					},
					{ once: true }
				  );

				  audio.load();
				});
			  });

			// Prepara MAIN THEME pero no lo reproduce todavía
			const mainTheme = Array.from(tracks).find(t =>
			  t.textContent.includes("MAIN THEME")
			);
			if (mainTheme) {
			  mainTheme.classList.add("active");
			  audio.src = mainTheme.dataset.src;
			  audio.dataset.currentTrack = mainTheme.dataset.src;
			  audio.loop = true;
			  audio.muted = true;
			  audio.load(); // solo carga
			  // 👇 Forzar decodificación inicial
			  audio.play().then(() => {
				audio.pause();
				audio.currentTime = 0;
			  }).catch(() => {});
			}
		});
	</script>

    <!-- === NOD FEED === -->
    <div class="hud-box" id="hud-bottom-right">
      <div class="mc-panel-video" aria-hidden="true">
        <div class="mc-panel-video__shape">
          <!-- 🔐 Compuerta NOD (dentro del clip-path) -->
          <div class="mc-door">
            <div class="half left"></div>
            <div class="half right"></div>
			<!--<div class="laser"></div>
  			<div class="laser"></div>-->
          </div>

          <!-- Badge + Radar DETRÁS -->
          <span class="hud-badge nod">NOD FEED</span>
          <div class="hud-radar nod">
            <span class="blip" style="top:26%;left:52%;animation-delay:.6s"></span>
            <span class="blip" style="top:68%;left:24%;animation-delay:1.3s"></span>
            <span class="blip" style="top:42%;left:82%;animation-delay:2.2s"></span>
          </div>
			
		  <div class="video-fallback" id="NOD-fallback">
    	  	<video src="/videos/nod-gallery/video8_main.mp4" autoplay muted loop playsinline></video>				
  		  </div>			
		  <video id="NODvideo" muted loop playsinline></video>

        </div>
        <div class="mc-panel-video__edges" aria-hidden="true">
          <svg viewBox="0 0 100 100" preserveAspectRatio="none" aria-hidden="true">
            <defs>
              <linearGradient id="mc-edge-grad-nod" x1="0%" y1="0%" x2="100%" y2="100%">
                <stop offset="0%"   stop-color="#ff2a2a"/>
                <stop offset="40%"  stop-color="#ff6a4a"/>
                <stop offset="70%"  stop-color="#ffcc6a"/>
                <stop offset="100%" stop-color="#ff2a2a"/>
              </linearGradient>
            </defs>
            <path d="M 1.5 0 L 94 0 L 100 6 L 100 97.333 A 1.5 2.667 0 0 1 98.5 100 L 8 100 L 0 88 L 0 2.667 A 1.5 2.667 0 0 1 1.5 0 Z"
                  stroke="url(#mc-edge-grad-nod)"/>
          </svg>
        </div>
      </div>
    </div>

    <div id="hud-init-panel">
      <div class="title">INITIALIZING</div>
      <span id="hud-percent" class="percent">0%</span>
      <div class="hud-progress"><div class="hud-progress-bar"></div></div>
    </div>

    <div id="hud-controls">
      <div id="hud-ticker">
        <span class="marquee">NOD PRIORITY CHANNEL // GDI UPLINK ESTABLISHED // SECURE HANDSHAKE: OK // VIDEO FEEDS ONLINE // PRESS CONTINUE TO DEPLOY &nbsp;&nbsp;•&nbsp;&nbsp;</span>
      </div>
      <button id="hud-continue">		  
		  <span>▶ CONTINUE</span>
	      <div class="grid-overlay"></div>
          <div class="pulse"></div>
		   <audio id="hud-hover-sound" src="/sounds/button-hover-one" preload="auto"></audio>
  		   <audio id="hud-click-sound" src="/sounds/button-click-one" preload="auto"></audio>
	  </button>
    </div>
    <!-- Sonidos: coloca los mp3 en /sounds/ o ajusta rutas -->
    <audio id="door-open" preload="auto"><source src="/sounds/sliding-hatch-door" type="audio/mpeg"></audio>	  	  
    <!--<audio id="door-impact" preload="auto"><source src="/sounds/door_impact_metal.mp3" type="audio/mpeg"></audio>-->
	<audio id="loading" preload="auto"><source src="/sounds/loading" type="audio/mpeg"></audio>
  </div>

	<!-- === Botón de sonido estilo C&C === -->
	<div id="hud-sound-btn" class="hud-sound muted">
	  <svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
		<!-- Icono parlante -->
		<path fill="currentColor" d="M5 9v6h4l5 5V4L9 9H5z"/>
		<!-- Línea muteado (oculta por defecto, visible en mute) -->
		<line id="muteLine" x1="3" y1="3" x2="21" y2="21"/>
	  </svg>
	</div>

<script>document.addEventListener('DOMContentLoaded',async function(){const isFirefox=typeof InstallTrigger!=='undefined';const isChromium=!!window.chrome;const videos=isFirefox?{GDIvideo:"/videos/gdi-intro-firefox/GDI-INTRO.m3u8",NODvideo:"/videos/nod-intro-firefox/NOD-INTRO.m3u8"}:{GDIvideo:"/wp-content/uploads/videos/GDI-INTRO/GDI-INTRO.m3u8",NODvideo:"/wp-content/uploads/videos/NOD-INTRO/NOD-INTRO.m3u8"};const elements={};async function loadVideo(id,src){return new Promise(resolve=>{const vid=document.getElementById(id);elements[id]=vid;function ready(){vid.removeEventListener('canplaythrough',ready);resolve();}if(isFirefox){if(window.Hls&&Hls.isSupported()){const hls=new Hls({enableWorker:true,lowLatencyMode:true,backBufferLength:60});hls.loadSource(src);hls.attachMedia(vid);hls.on(Hls.Events.MANIFEST_PARSED,()=>resolve());hls.on(Hls.Events.ERROR,()=>resolve());}else if(vid.canPlayType('application/vnd.apple.mpegurl')){vid.src=src;vid.addEventListener('canplaythrough',ready);}else resolve();}else if(isChromium){if(window.Hls&&Hls.isSupported()){const hls=new Hls({enableWorker:false,lowLatencyMode:false,backBufferLength:1,liveDurationInfinity:true,maxBufferLength:10,maxMaxBufferLength:20,fragLoadingTimeOut:20000,manifestLoadingTimeOut:15000,appendErrorMaxRetry:2});hls.loadSource(src);hls.attachMedia(vid);hls.on(Hls.Events.MANIFEST_PARSED,()=>resolve());hls.on(Hls.Events.ERROR,(e,data)=>{console.warn(`[HLS Error] ${id}`,data);resolve();});}else if(vid.canPlayType('application/vnd.apple.mpegurl')){vid.src=src;vid.addEventListener('canplaythrough',ready);}else resolve();}else{vid.src=src;vid.addEventListener('canplaythrough',ready);}});}await Promise.all(Object.entries(videos).map(([id,src])=>loadVideo(id,src)));Object.values(elements).forEach(v=>{v.currentTime=0;v.pause();});await new Promise(r=>requestAnimationFrame(r));Object.values(elements).forEach(v=>v.play().catch(()=>{}));const syncInterval=isChromium?1500:2000;setInterval(()=>{const[v1,v2]=[elements["GDIvideo"],elements["NODvideo"]];if(!v1||!v2||v1.paused||v2.paused)return;const diff=v1.currentTime-v2.currentTime;if(Math.abs(diff)>0.05){const target=Math.min(v1.currentTime,v2.currentTime);v1.currentTime=target;v2.currentTime=target;}if(isChromium){if(v1.readyState<2||v1.paused)v1.play().catch(()=>{});if(v2.readyState<2||v2.paused)v2.play().catch(()=>{});}},syncInterval);});</script>

	<script>
		document.addEventListener("DOMContentLoaded", () => {
		  const soundBtn = document.getElementById("hud-sound-btn");
		  const audios = document.querySelectorAll("audio");
		  const mainAudio = document.getElementById("hud-audio"); // este es el main theme
		  const loadingBeep = document.getElementById("loading"); // beep de carga
		  let isMuted = true;
		  let mainReady = false; // ya terminó la carga o no

		  // Todos arrancan muteados
		  audios.forEach(a => a.muted = true);

		  // Mute global (visual + lógico)
		  function setGlobalMute(muted) {
			isMuted = muted;
			audios.forEach(a => a.muted = muted);
			soundBtn.classList.toggle("muted", muted);

			// Si desmutea después del 100%, sonar main theme
			if (!muted && mainReady) {
			  if (mainAudio.paused) mainAudio.play().catch(() => {});
			}

			// Si mutea mientras suena, pausar main theme
			if (muted && !mainAudio.paused) mainAudio.pause();
		  }

		  // Click en el botón de sonido
		  soundBtn.addEventListener("click", () => {
			setGlobalMute(!isMuted);

			// Si aún no llegó al 100%, solo manejar el beep
			if (!mainReady && !isMuted) {
			  loadingBeep.currentTime = 0;
			  loadingBeep.play().catch(() => {});
			} else if (!mainReady && isMuted && !loadingBeep.paused) {
			  loadingBeep.pause();
			}
		  });

		  // Exponer funciones para el loader
		  window.setGlobalMute = setGlobalMute;
		  window.setMainReady = () => { mainReady = true; };
		});
	</script>

	<style>
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
				
	.video-fallback {
	  position: absolute;
	  inset: 0;
	  z-index: 2; 
	}

	.video-fallback video {
	  width: 100%;
	  height: 100%;
	  object-fit: cover;
	}

	#NODvideo, #GDIvideo {
	  position: absolute;
	  inset: 0;
	  width: 100%;
	  height: 100%;
	  object-fit: cover;
	  z-index: 1; 
	  background: black; /* evita transparencias raras */
	}

	</style>

  <script>
  (function(){
	  const hud = document.getElementById('hud-intro');
	  const btn = document.getElementById('hud-continue');
	  const panel = document.getElementById('hud-init-panel');
	  const percent = document.getElementById('hud-percent');
	  const bar = panel.querySelector('.hud-progress-bar');

	  const hoverSound = document.getElementById('hud-hover-sound');
	  const clickSound = document.getElementById('hud-click-sound');
	  
	  // Detecta si viene desde el menú
	  const fromMenu = sessionStorage.getItem('fromMenu') === 'true';
	  // Limpia el flag para que no quede activo después
	  sessionStorage.removeItem('fromMenu');
	  
	  document.documentElement.classList.add('hud-lock');
	  document.body.classList.add('hud-lock');

      // Bloqueos
  	  document.addEventListener('contextmenu', e => e.preventDefault());
  	  document.addEventListener('selectstart', e => e.preventDefault());
	  
		// Deshabilitar el panel de música al inicio
		const musicPanel = document.getElementById('hud-music-panel');
		if (musicPanel) {
  			musicPanel.style.pointerEvents = 'none';
  			musicPanel.style.opacity = '0.4'; // visualmente grisado opcional
		}

  // ===== Inicialización simulada + apertura de compuertas
  (function init(ms = 3000) {
	  const start = Date.now();
	  const loading = document.getElementById('loading');
	  loading.loop = true;	  	  	  
	  loading.load();
	  loading.play().catch(() => {});	  
	  	    
	  // Solo reproducir si el audio NO está muteado
  		if (!loading.muted) {
    		loading.currentTime = 0;
    		loading.play().catch(() => {});
  		}
	  	  	  
	  const tick = setInterval(() => {
		const elapsed = Date.now() - start;
		const pct = Math.min(100, Math.round((elapsed / ms) * 100));
		percent.textContent = pct + '%';
		bar.style.width = pct + '%';

		if (pct >= 100) {
			
		  if (window.setMainReady) window.setMainReady();
			
  		  const mainAudio = document.getElementById("hud-audio");
  		  if (mainAudio.muted === false) {
    			mainAudio.play().catch(() => {});
  		  } else {
    	  		mainAudio.pause();
    			mainAudio.currentTime = 0;
  		  }

		  clearInterval(tick);

		  // ===== Detener y liberar el beep
		  if (!loading.paused) {
			loading.pause();
			loading.currentTime = 0;
		  }

		  // ===== Apertura de compuertas
		  
		  // Abrir la compuerta NOD
		  const nodDoor = document.querySelector('#hud-bottom-right .mc-door');
		  if(nodDoor) nodDoor.classList.add('open');

		  // Abrir la compuerta GDI
		  const gdiDoor = document.querySelector('#hud-top-left .mc-door');
		  if(gdiDoor) gdiDoor.classList.add('open');

		  const doorSound = document.getElementById('door-open');		  
		  doorSound.pause();
		  doorSound.currentTime = 0;

		  // Aseguramos que se cargue antes de reproducir
		  const playDoorSound = () => {
			  doorSound.playbackRate = 2; // velocidad ajustada
			  doorSound.play().catch(() => {});
		  };

		  if (doorSound.readyState >= 4) { // ya cargado
			playDoorSound();
		  } else {
			doorSound.addEventListener('canplaythrough', function once() {
			  doorSound.removeEventListener('canplaythrough', once);
			  playDoorSound();
			});
			doorSound.load(); // carga forzada si no estaba lista
		  }

		  // ===== Shake visual
		  document.querySelectorAll('.mc-panel-video').forEach(el => {
			el.classList.add('shake');
			setTimeout(() => el.classList.remove('shake'), 480);
		  });

		  // ===== Finalizamos HUD
		  btn.classList.add('ready');
		  panel.classList.add('vanish');
		  setTimeout(() => { panel.remove(); }, 380);
			
		  // Habilitar el panel de música una vez cargado todo
		  if (musicPanel) {
  		  		setTimeout(() => {
    			musicPanel.style.pointerEvents = 'auto';
    			musicPanel.style.opacity = '1';
  			}, 400); // tras el fade del panel
		  }

		  // ===== Se reproduce el MAIN THEME =====
		  const audio = document.getElementById("hud-audio");

		  // Nunca desmutear automáticamente: el mute sigue el estado del botón
		  audio.muted = isMuted;  // asegura consistencia con el botón
		  const playMainTheme = () => audio.play().catch(() => {});

		  // Solo reproducir seguro, independientemente de mute
		  if (audio.readyState >= 2) {
			playMainTheme();
		  } else {
			const onCanPlay = () => {
				audio.removeEventListener("canplay", onCanPlay);
				playMainTheme();
			};
			audio.addEventListener("canplay", onCanPlay);
			audio.load();
		  }			
		}
	  }, 40);
 	})();

	btn.addEventListener('mouseenter', () => {
		hoverSound.currentTime = 0;
		hoverSound.play().catch(()=>{});
	});
	  
	const isChromium = !!window.chrome;

	function freezeVideo(video){
	  if(!isChromium) return null;

	  const rect = video.getBoundingClientRect();
	  const parentRect = video.parentNode.getBoundingClientRect();

	  const topInsideParent = rect.top - parentRect.top;
	  const leftInsideParent = rect.left - parentRect.left;

	  const w = Math.round(rect.width);
	  const h = Math.round(rect.height);

	  const canvas = document.createElement('canvas');
	  canvas.width = w;
	  canvas.height = h;
	  canvas.style.width = w + 'px';
	  canvas.style.height = h + 'px';
	  canvas.style.position = 'absolute';
	  canvas.style.top = topInsideParent + 'px';
	  canvas.style.left = leftInsideParent + 'px';
	  canvas.style.zIndex = '99';
	  canvas.style.pointerEvents = 'none';

	  const ctx = canvas.getContext('2d');

	  // calcular proporcional real evitando distorsión
	  const vidW = video.videoWidth;
	  const vidH = video.videoHeight;
	  const videoAspect = vidW / vidH;
	  const rectAspect = w / h;

	  let sx=0, sy=0, sw=vidW, sh=vidH;

	  if (videoAspect > rectAspect) {
		// recortar horizontal
		const newWidth = vidH * rectAspect;
		sx = (vidW - newWidth) / 2;
		sw = newWidth;
	  } else {
		// recortar vertical
		const newHeight = vidW / rectAspect;
		sy = (vidH - newHeight) / 2;
		sh = newHeight;
	  }

	  ctx.drawImage(video, sx, sy, sw, sh, 0, 0, w, h);

	  video.style.opacity = '0';

	  const parent = video.parentNode;
	  if(getComputedStyle(parent).position === 'static'){
		parent.style.position = 'relative';
	  }

	  parent.appendChild(canvas);

	  return canvas;
	}

	btn.addEventListener('click', () => {
		  if (!btn.classList.contains('ready')) return;

		  clickSound.currentTime = 0;
		  clickSound.play().catch(() => {});

		  // Reinicia animaciones para que se repitan siempre
		  btn.classList.remove('mega-effect', 'jump-effect');
		  void btn.offsetWidth; // fuerza reflow

		  // Aplica efectos del click
		  btn.classList.add('mega-effect', 'jump-effect');

		  if(isChromium){
			freezeVideo(document.getElementById('GDIvideo'));
			freezeVideo(document.getElementById('NODvideo'));
		  }

		  // Redirección según origen
		  if (fromMenu) {
			window.location.href = 'https://mcartworksstudio.com';
		  } else {
			window.location.href = 'https://mcartworksstudio.com/faction-selection';
		  }
	});

	// Marquee
    const marquee=document.querySelector('#hud-ticker .marquee');
    let pos = marquee.parentElement.offsetWidth;
    const speed = 2;
    function loopTicker(){
      pos -= speed;
      if(pos < -marquee.offsetWidth) pos = marquee.parentElement.offsetWidth;
      marquee.style.transform = `translateX(${pos}px) translateY(-50%)`;
      requestAnimationFrame(loopTicker);
    }
    loopTicker();

    /* ======= Grillas curvas (radar y lente) ======= */
    function makeCurvedGrid(el, className='radar-grid', targetSpacingPx=14){
      const size = Math.min(el.clientWidth, el.clientHeight);
      let lines = Math.max(6, Math.round(size / targetSpacingPx));
      if (lines % 2 !== 0) lines++;

      const svgNS = "http://www.w3.org/2000/svg";
      const svg = document.createElementNS(svgNS,'svg');
      svg.setAttribute('class', className);
      svg.setAttribute('viewBox','-1 -1 2 2');
      svg.setAttribute('preserveAspectRatio','xMidYMid meet');

      const defs = document.createElementNS(svgNS,'defs');
      const clip = document.createElementNS(svgNS,'clipPath');
      clip.setAttribute('id','rgClip'+Math.random().toString(36).slice(2));
      const circle = document.createElementNS(svgNS,'circle');
      circle.setAttribute('cx','0'); circle.setAttribute('cy','0'); circle.setAttribute('r','1');
      clip.appendChild(circle);
      defs.appendChild(clip);
      svg.appendChild(defs);

      const g = document.createElementNS(svgNS,'g');
      g.setAttribute('clip-path',`url(#${clip.getAttribute('id')})`);
      svg.appendChild(g);

      function mapSquareToDisk(x, y){
        const x2 = x*x, y2 = y*y;
        const u = x * Math.sqrt(1 - y2/2);
        const v = y * Math.sqrt(1 - x2/2);
        return [u, v];
      }
      function addPath(points){
        const p = document.createElementNS(svgNS,'path');
        let d = '';
        for (let i=0;i<points.length;i++){
          const [u,v] = points[i];
          d += (i===0?'M ':'L ')+u.toFixed(4)+' '+v.toFixed(4)+' ';
        }
        p.setAttribute('d', d.trim());
        g.appendChild(p);
      }

      for (let i=-lines;i<=lines;i++){
        const c = i/lines;
        const pts = [];
        for (let y=-1; y<=1; y+=0.02){ pts.push(mapSquareToDisk(c, y)); }
        addPath(pts);
      }
      for (let j=-lines;j<=lines;j++){
        const c = j/lines;
        const pts = [];
        for (let x=-1; x<=1; x+=0.02){ pts.push(mapSquareToDisk(x, c)); }
        addPath(pts);
      }

      el.querySelectorAll('svg.'+className).forEach(n=>n.remove());
      el.prepend(svg);
    }

    function buildRadars(){
      document.querySelectorAll('.hud-radar').forEach(el => makeCurvedGrid(el, 'radar-grid', 14));
    }
    function buildFisheye(){
      const lens = document.querySelector('.bg-fisheye .lens');
      if(!lens) return;
      makeCurvedGrid(lens, 'fisheye-grid', 34);
    }
    buildRadars();
    buildFisheye();

    let to;
    window.addEventListener('resize', ()=>{
      clearTimeout(to);
      to = setTimeout(()=>{ buildRadars(); buildFisheye(); }, 80);
    });
  })();
	  	  
  </script>

	<script>
	document.addEventListener("DOMContentLoaded", () => {
  const feeds = [
    { video: document.getElementById("GDIvideo"), fallback: document.getElementById("GDI-fallback") },
    { video: document.getElementById("NODvideo"), fallback: document.getElementById("NOD-fallback") }
  ];

  feeds.forEach(feed => {
    const { video, fallback } = feed;

    if (!video) {
      // Video no existe → fallback visible
      fallback.style.display = "block";
      return;
    }

    // Ocultar fallback cuando el video pueda reproducirse
    video.addEventListener("canplay", () => {
      fallback.style.display = "none";
    });

    // Si la compuerta se abre antes de que el video esté listo, mostramos fallback
    const door = video.closest(".mc-panel-video__shape")?.querySelector(".mc-door");
    if (door) {
      const observer = new MutationObserver(muts => {
        muts.forEach(m => {
          if (m.type === "attributes" && m.attributeName === "class") {
            if (m.target.classList.contains("open") && video.readyState < 3) {
              fallback.style.display = "block";
            }
          }
        });
      });
      observer.observe(door, { attributes: true });
    }
  });
});

	</script>

  <?php
});