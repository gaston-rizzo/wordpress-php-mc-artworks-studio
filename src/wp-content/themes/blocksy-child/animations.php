<?php
/**
 * Archivo: animations.php
 * Contiene todas las funciones de animaciones GDI y NOD
 */

function insertar_animacion_apocalipsis_GDI_UNO(){ ?>
<!-- Bloque para bloquear HUD y carrito XOATix desde el inicio -->
<script>
	document.documentElement.classList.add("hud-lock");
</script>
<style>
/* ===== BLOQUE HUD LOCK ===== */
.hud-lock, body.hud-lock {
	overflow: hidden !important;
    height: 100vh !important;
    touch-action: none;
}
.hud-lock [class*="xoo-wsc"], .hud-lock [id*="xoo-wsc"] {
	display: none !important;
}		

/* ===== FULLSCREEN APOCALIPSIS CINEMÁTICO ===== */
#apocalipsis-compuerta {
    position: fixed;
    top: -20%; 
    left: -20%;
    width: 140vw; 
    height: 140vh;
    background: radial-gradient(circle at center, #1a0022, #4b0066);
    overflow: hidden;
    display:flex; 
    align-items:center; 
    justify-content:center;
    z-index: 999999;
    transition: opacity 2s ease-out, transform 2s ease-out;
    opacity:1;
}
body { overflow: hidden !important; }

/* Humo dinámico */
.smoke {
    position:absolute; width:120%; height:120%;
    background: radial-gradient(circle, rgba(120,0,120,0.4), transparent 70%);
    top:-10%; left:-10%;
    animation: smokeMove 6s linear infinite;
}
@keyframes smokeMove { 0% {transform:translate(0,0) rotate(0deg); opacity:0.5;} 50% {transform:translate(60px,-30px) rotate(45deg); opacity:0.3;} 100% {transform:translate(-60px,50px) rotate(-45deg); opacity:0.5;} }

/* Chispas explosivas */
.spark { position:absolute; width:4px; height:4px; background:#ffea00; border-radius:50%; opacity:0.8; animation:sparkMove linear infinite;}
@keyframes sparkMove {0%{transform:translate(0,0) scale(1);opacity:0.8;}50%{transform:translate(30px,-60px) scale(2);opacity:0.5;}100%{transform:translate(-30px,60px) scale(1);opacity:0;}}

/* Fragmentos centrífugos */
.fragment { position:absolute; width:6px; height:6px; background:#ff8800; border-radius:50%; top:50%; left:50%; opacity:0.9; transform: translate(-50%,-50%); animation: fragmentFly 2s ease-out forwards; }
@keyframes fragmentFly { 0% {transform:translate(-50%,-50%) scale(1); opacity:1;} 100% {transform:translate(calc(-50% + var(--x)), calc(-50% + var(--y))) scale(0.5); opacity:0;}}

/* Círculo central naranja degradado */
.ultra-circle { width:280px; height:280px; border-radius:50%; background: radial-gradient(circle, #ff8800, #ff3300); box-shadow:0 0 120px #ff8800,0 0 200px #ff3300; display:flex; align-items:center; justify-content:center; z-index:2; animation:pulseUltra 0.8s infinite alternate; position:relative; transition: opacity 2s ease-out, transform 2s ease-out, box-shadow 2s ease-out; }
.ultra-circle img { width:70%; opacity:0; transform:scale(0.3) rotate(-90deg); filter:drop-shadow(0 0 20px #ff8800); transition: all 0.7s ease; }
.ultra-circle.show-logo img { opacity:1; transform:scale(1) rotate(0deg); }
@keyframes pulseUltra { 0%{transform:scale(1); opacity:0.9;}25%{transform:scale(1.5); opacity:1;}50%{transform:scale(1.2); opacity:0.9;}75%{transform:scale(1.6); opacity:1;}100%{transform:scale(1); opacity:0.9;}}

/* Ondas épicas */
.wave { position:absolute; border-radius:50%; background: radial-gradient(circle, rgba(255,136,0,0.3) 0%, rgba(255,51,0,0.1) 60%, transparent 100%); pointer-events:none; transform: translate(-50%, -50%); left:50%; top:50%; opacity:0; animation: waveExpandEpic 2s ease-out forwards; }
@keyframes waveExpandEpic { 0% { width:0; height:0; opacity:0.9; filter:blur(2px);} 50% { opacity:0.6; filter:blur(4px);} 100% { width:2200px; height:2200px; opacity:0; filter:blur(20px);} }

/* Shake extremo */
@keyframes shakeScreen {0%,100%{transform:translate(0,0) rotate(0);}10%{transform:translate(-50px,-25px) rotate(-4deg);}20%{transform:translate(50px,25px) rotate(4deg);}30%{transform:translate(-40px,30px) rotate(-3deg);}40%{transform:translate(40px,-30px) rotate(3deg);}50%{transform:translate(-35px,20px) rotate(-2deg);}60%{transform:translate(35px,-25px) rotate(2deg);}70%{transform:translate(-30px,15px) rotate(-1deg);}80%{transform:translate(30px,-15px) rotate(1deg);}90%{transform:translate(-25px,10px) rotate(0);}}
.shake { animation: shakeScreen 0.7s infinite; transform-origin:center; }

/* Flash final ultra */
.final-flash { position:absolute; top:0; left:0; width:100%; height:100%; background: radial-gradient(circle, rgba(255,136,0,0.9), rgba(255,51,0,0.5), transparent 80%); opacity:0; pointer-events:none; animation: flashFade 2s ease-out forwards;}
@keyframes flashFade {0% {opacity:0;}20% {opacity:1;}100% {opacity:0;}}

/* ===== BOTÓN DE SONIDO ===== */
#soundToggle {
    position: fixed;
    bottom: 25px;
    right: 25px;
    width: 50px;
    height: 50px;
    background: rgba(0,0,0,0.6);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;  /* esto es lo que realmente centra */
    z-index: 1000000;
    cursor: pointer;
    transition: box-shadow 0.3s, transform 0.2s;
}
		
#soundToggle:hover { /*box-shadow: 0 0 25px rgba(255,255,0,0.7); */transform: scale(1.1);}
	
#soundToggle svg {
    width: 24px;
    height: 24px;
    fill: #fff;
    display: block;  /* evita desajuste inline */
}

#soundToggle svg path {
    transform: translate(-2px, 0); /* corrige el offset */
}
		
#soundToggle line {
    stroke-width: 3;
    stroke: red;
    transition: opacity 0.3s;
}		
</style>

<div id="apocalipsis-compuerta">
    <div class="smoke"></div>
    <?php for($i=0;$i<60;$i++): ?>
        <div class="spark" style="top:<?= rand(0,100) ?>%; left:<?= rand(0,100) ?>%; animation-duration: <?= rand(1,4) ?>s;"></div>
    <?php endfor; ?>
    <div class="ultra-circle">
        <img src="/images/logo.webp" alt="Logo">
    </div>
    <audio id="boomSound" src="/sounds/explosion-three"></audio>	
	<audio id="wavesSound" src="/sounds/sci_fi_radar_pings"></audio>   	
</div>

<!-- Botón de sonido -->
<div id="soundToggle">
    <svg viewBox="0 0 24 24" preserveAspectRatio="xMidYMid meet">
        <path d="M9 9v6h4l5 5V4l-5 5H9z"/>
        <line id="muteLine" x1="1" y1="1" x2="23" y2="23"/>
    </svg>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const compuerta = document.getElementById("apocalipsis-compuerta");
    const ultraCircle = document.querySelector(".ultra-circle");
    const boom = document.getElementById("boomSound");    
	const body = document.body;
	body.classList.add("hud-lock");
    document.body.style.overflow = 'hidden';

    // BOTÓN DE SONIDO
    const soundToggle = document.getElementById("soundToggle");
    const muteLine = document.getElementById("muteLine");
    let soundEnabled = false;
    soundToggle.addEventListener("click", () => {
        soundEnabled = !soundEnabled;
        muteLine.style.opacity = soundEnabled ? 0 : 1;		
		if(!soundEnabled){
			// Pausar y reiniciar todos los audios del DOM
        	document.querySelectorAll('audio').forEach(audio => {
            	audio.pause();
            	audio.currentTime = 0;
        	});		
		}
    });
    function playSound(audio){
        if(soundEnabled){
            audio.currentTime = 0;
            audio.play().catch(()=>{});
        }
    }

    // Mostrar logo
    setTimeout(() => ultraCircle.classList.add("show-logo"), 400);

    // Ondas sucesivas
    [1200,1600,2000,2400,2800,3200].forEach(t => {
        setTimeout(() => {
            for(let j=0;j<3;j++){
                const wave = document.createElement("div");
                wave.className = "wave";
                wave.style.animationDuration = `${1.5 + Math.random()*0.5}s`;
                compuerta.appendChild(wave);
                setTimeout(()=>wave.remove(), 2200);				
				playSound(wavesSound); // Reproduce el sonido de las ondas								
            }
        }, t);
    });

    // Fragmentos explosivos
    function spawnFragments(){
        for(let i=0;i<30;i++){
            const frag = document.createElement("div");
            frag.className = "fragment";
            frag.style.setProperty('--x', `${(Math.random()-0.5)*2000}px`);
            frag.style.setProperty('--y', `${(Math.random()-0.5)*1200}px`);
            compuerta.appendChild(frag);
            setTimeout(()=>frag.remove(),2000);
        }
    }

    // Explosión final + shake extremo + sonido + fragmentos
    setTimeout(() => {				
		wavesSound.pause(); // Detenemos el sonido de las ondas
    	wavesSound.currentTime = 0; // Regresar el sonido al inicio	
        compuerta.classList.add("shake");
        playSound(boom);
        spawnFragments();
    }, 4000);

    // Flash final y fade out gradual
    setTimeout(() => {
        const flash = document.createElement("div");
        flash.className = "final-flash";
        compuerta.appendChild(flash);
        compuerta.style.opacity = 0;
        ultraCircle.style.opacity = 0;
        document.querySelectorAll(".spark, .smoke, .wave, .fragment").forEach(el => el.style.opacity = 0);
        compuerta.style.background = "radial-gradient(circle at center, rgba(26,0,34,0) 0%, rgba(75,0,102,0) 100%)";
				
		// Iniciamos sonido de la pantalla de carga
		var loadingSound = document.getElementById('loading-sound');
		if (loadingSound) {						
			playSound(loadingSound);						
		}		
		
        setTimeout(()=> {
            compuerta.remove();
            document.body.style.overflow = '';				
			
			// Mutear y pausar en este momento (no esperar al redirect)
			if (loadingSound) {
				loadingSound.muted = true;
				loadingSound.pause(); 
				loadingSound.currentTime = 0;
				loadingSound.remove();
			}

			// Redirigimos después de 500ms ya con el audio drenado
			setTimeout(()=> {
				window.location.href = "/intro";
			}, 500);	
        }, 2000);
    }, 5800);
});
</script>
<?php 
} 

function insertar_animacion_apocalipsis_GDI_DOS(){ ?>
<!-- Bloque para bloquear HUD y carrito XOATix desde el inicio -->
<script>
	document.documentElement.classList.add("hud-lock");
</script>
<style>
/* ===== BLOQUE HUD LOCK ===== */
.hud-lock, body.hud-lock {
	overflow: hidden !important;
    height: 100vh !important;
    touch-action: none;
}
.hud-lock [class*="xoo-wsc"], .hud-lock [id*="xoo-wsc"] {
	display: none !important;
}		

/* Contenedor principal */
#apocalipsis-compuerta {
    position: fixed; top:0; left:0;
    width:100vw; height:100vh;
    background: radial-gradient(circle at center, #000000, #9b2d91 90%);
    overflow: hidden; display:flex; align-items:center; justify-content:center;
    z-index: 999999;
    transition: opacity 2s ease-out, transform 2s ease-out;
    opacity: 1;
}

/* Shake screen */
.shake-wrapper { width:100%; height:100%; display:flex; align-items:center; justify-content:center; position:relative; }
.shake-wrapper.shake { animation: shakeScreen 0.8s infinite; will-change: transform; }
@keyframes shakeScreen {
  0%,100%{transform:translate(0,0) rotate(0);}
  10%{transform:translate(-10px,-5px) rotate(-1deg);}
  20%{transform:translate(10px,5px) rotate(1deg);}
  30%{transform:translate(-8px,6px) rotate(-1deg);}
  40%{transform:translate(8px,-6px) rotate(1deg);}
  50%{transform:translate(-6px,3px) rotate(-1deg);}
  60%{transform:translate(6px,-3px) rotate(1deg);}
  70%{transform:translate(-4px,2px) rotate(-1deg);}
  80%{transform:translate(4px,-2px) rotate(1deg);}
  90%{transform:translate(-2px,1px) rotate(-1deg);}
}

/* Humo */
.smoke {
    position:absolute; width:120%; height:120%;
    background: radial-gradient(circle, rgba(50,0,50,0.45), transparent 70%);
    top:-10%; left:-10%;
    animation: smokeMove 6s linear infinite;
}
@keyframes smokeMove {
    0% {transform:translate(0,0) rotate(0deg); opacity:0.5;}
    50% {transform:translate(60px,-30px) rotate(45deg); opacity:0.3;}
    100% {transform:translate(-60px,50px) rotate(-45deg); opacity:0.5;}
}

/* Chispas */
.spark { 
    position:absolute; width:5px; height:5px; 
    background: radial-gradient(circle, #ff00ff, #ff80ff);
    border-radius:50%; opacity:0.9; animation:sparkMove linear infinite;
    box-shadow:0 0 8px #ff00ff, 0 0 12px #ff80ff;
}
@keyframes sparkMove { 
    0%{transform:translate(0,0) scale(1); opacity:0.9;}
    50%{transform:translate(30px,-60px) scale(2); opacity:0.6;}
    100%{transform:translate(-30px,60px) scale(1); opacity:0;}
}

/* Círculo central */
.ultra-circle {
    width:280px; height:280px; border-radius:50%;
    background: radial-gradient(circle, #ff00ff, #9b2d91);
    box-shadow:0 0 120px #ff00ff,0 0 200px #9b2d91;
    display:flex; align-items:center; justify-content:center;
    z-index:2; animation:pulseUltra 1s infinite; position:relative;
}
.ultra-circle img {
    width:70%; opacity:0; transform:scale(0.3) rotate(-90deg);
    filter: drop-shadow(0 0 25px #ff00ff);
    transition: all 0.7s ease;
}
.ultra-circle.show-logo img { opacity:1; transform:scale(1) rotate(0deg); }
@keyframes pulseUltra {
    0%{transform:scale(1); opacity:0.9;}
    25%{transform:scale(1.5); opacity:1;}
    50%{transform:scale(1.2); opacity:0.9;}
    75%{transform:scale(1.6); opacity:1;}
    100%{transform:scale(1); opacity:0.9;}
}

/* Ondas expansivas */
.wave {
  position:absolute; border-radius:50%;
  background: radial-gradient(circle, rgba(255,0,255,0.5) 0%, rgba(150,0,150,0.1) 60%, transparent 100%);
  pointer-events:none;
  transform: translate(-50%, -50%);
  left:50%; top:50%; opacity:0;
  animation: waveExpandEpic 2s ease-out forwards;
}
@keyframes waveExpandEpic {
  0% { width:0; height:0; opacity:0.9; filter:blur(2px);}
  100% { width:3000px; height:3000px; opacity:0; filter:blur(40px);}
}

/* Fracturas */
.cracks {
    position:absolute; top:0; left:0; width:100%; height:100%;
    background: url("/images/crackfinal_two.png") center/cover no-repeat;	
    opacity:0; z-index:10000001;
    animation: cracksAppear 0.20s cubic-bezier(0.2, 0.8, 0.4, 1) forwards;
    mix-blend-mode: screen;
    pointer-events:none;
}	
	
@keyframes cracksAppear {
    0%   { opacity: 0; transform: scale(1.2) rotate(2deg); filter: blur(2px);}
    80%  { opacity: 1; transform: scale(0.98) rotate(-1deg); filter: blur(0);}
    100% { opacity: 1; transform: scale(1) rotate(0deg);}
}

/* Relámpagos */
.lightning {
    position:absolute; width:2px; height:100vh;
    background: linear-gradient(to bottom, #fff, rgba(255,0,255,0));
    top:0; opacity:0.8;
    animation: lightningFlash 0.2s ease-in-out forwards;
}
@keyframes lightningFlash { 0% {opacity:0.8;} 100% {opacity:0;} }

/* Explosión final */
#apocalipsis-compuerta.fade-out .ultra-circle {
    animation: explodeUltraEpic 1.5s forwards;
}
@keyframes explodeUltraEpic {
    0%{transform:scale(1); opacity:1;}
    100%{transform:scale(14) rotate(1080deg); opacity:0; filter:blur(80px);}
}

/* Glow final */
.final-glow {
    position:absolute; left:50%; top:50%;
    width:0; height:0;
    background: radial-gradient(circle, rgba(255,0,255,0.6) 0%, rgba(120,0,120,0.2) 60%, transparent 100%);
    border-radius:50%; transform: translate(-50%,-50%);
    pointer-events:none; opacity:0; z-index:999998;
    animation: finalGlow 1.5s ease-out forwards;
}
@keyframes finalGlow {
    0% { width:0; height:0; opacity:0.8;}
    100% { width:3000px; height:3000px; opacity:0;}
}

/* Fade */
#apocalipsis-compuerta.fade-out-gradual { opacity: 0; transform: scale(1.12); }

/* ===== BOTÓN DE SONIDO ===== */
#soundToggle {
    position: fixed; bottom: 25px; right: 25px;
    width: 50px; height: 50px;
    background: rgba(0,0,0,0.6);
    border-radius: 50%; display: flex; align-items: center; justify-content: center;
    z-index: 1000000; cursor: pointer;
}
			
#soundToggle svg {
    width: 24px;
    height: 24px;
    fill: #fff;
    display: block;  /* evita desajuste inline */
}

#soundToggle svg path {
    transform: translate(-2px, 0); /* corrige el offset */
}
		
#soundToggle line {
    stroke-width: 3;
    stroke: red;
    transition: opacity 0.3s;
}			
</style>

<div id="apocalipsis-compuerta">
    <div class="shake-wrapper">
        <div class="smoke"></div>
        <?php for($i=0;$i<80;$i++): ?>
            <div class="spark" style="top:<?= rand(0,100) ?>%; left:<?= rand(0,100) ?>%; animation-duration: <?= rand(1,5) ?>s;"></div>
        <?php endfor; ?>
        <div class="ultra-circle">
            <img src="/images/logo.webp" alt="Logo">
        </div>
    </div>
    <audio id="boomSound" src="/sounds/explosion-two"></audio>	
	<audio id="glassBreaking" src="/sounds/glass-breaking"></audio>	
</div>

<div id="soundToggle">
    <svg viewBox="0 0 24 24">
        <path d="M9 9v6h4l5 5V4l-5 5H9z"/>
        <line id="muteLine" x1="1" y1="1" x2="23" y2="23"/>
    </svg>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const compuerta = document.getElementById("apocalipsis-compuerta");
    const wrapper = compuerta.querySelector(".shake-wrapper");
    const boom = document.getElementById("boomSound");
	const glassBreaking = document.getElementById("glassBreaking");
    const ultraCircle = document.querySelector(".ultra-circle");	
	const soundToggle = document.getElementById("soundToggle");
    const muteLine = document.getElementById("muteLine");
    let soundEnabled = false;

    soundToggle.addEventListener("click", () => {
        soundEnabled = !soundEnabled;
        muteLine.style.opacity = soundEnabled ? 0 : 1;
		if(!soundEnabled){			
			// Pausar y reiniciar todos los audios del DOM
        	document.querySelectorAll('audio').forEach(audio => {
            	audio.pause();
            	audio.currentTime = 0;
        	});						        	
    	}
    });
    function playSound(audio){
        if(soundEnabled){ audio.currentTime = 0; audio.play().catch(()=>{}); }
    }

    setTimeout(() => ultraCircle.classList.add("show-logo"), 400);

    [1200,1700,2200].forEach(t => {
        setTimeout(() => {
            const wave = document.createElement("div");
            wave.className = "wave";
            compuerta.appendChild(wave);
            setTimeout(()=>wave.remove(), 2500);
            playSound(boom);
            const flash = document.createElement("div");
            flash.className = "lightning";
            flash.style.left = Math.random()*100 + "%";
            compuerta.appendChild(flash);
            setTimeout(()=>flash.remove(), 200);
        }, t);
    });

    // Fracturas aparecen con el gran impacto
    setTimeout(() => {
        const cracks = document.createElement("div");
        cracks.className = "cracks";
        compuerta.appendChild(cracks);		
		playSound(glassBreaking);		
    }, 2800);

    // Explosión final
    setTimeout(() => {
        wrapper.classList.add("shake");
        compuerta.classList.add("fade-out");
        playSound(boom);
    }, 3500);

    setTimeout(() => {
        const glow = document.createElement("div");
        glow.className = "final-glow";
        compuerta.appendChild(glow);
    }, 2000);

    setTimeout(() => {
        compuerta.classList.add("fade-out-gradual");
        wrapper.classList.remove("shake");
        ultraCircle.style.opacity = 0;
        document.querySelectorAll(".spark, .smoke, .wave, .lightning").forEach(el => el.remove());
        setTimeout(()=> compuerta.remove(), 1500);		
		
		// Iniciamos sonido de la pantalla de carga
		var loadingSound = document.getElementById('loading-sound');
		if (loadingSound) {						
			playSound(loadingSound);						
		}				
		setTimeout(()=> {
			window.location.href = "/intro";
		}, 500);
    }, 4200);
});
</script>
<?php } 

function insertar_animacion_apocalipsis_GDI_TRES(){ ?>
<!-- Bloque para bloquear HUD y carrito XOATix desde el inicio -->
<script>
	document.documentElement.classList.add("hud-lock");
</script>
<style>	
/* ===== BLOQUE HUD LOCK ===== */
.hud-lock, body.hud-lock {
	overflow: hidden !important;
    height: 100vh !important;
    touch-action: none;
}
.hud-lock [class*="xoo-wsc"], .hud-lock [id*="xoo-wsc"] {
	display: none !important;
}	
			
#apocalipsis-compuerta{
  position:fixed; top:-10%; left:-10%;
  width:120vw; height:120vh;
  background: linear-gradient(135deg, #2e003e, #5a007f);
  overflow:hidden; display:flex; align-items:center; justify-content:center;
  z-index:999999;
  transition: opacity 1s ease-out;
  opacity:1;
}
#apocalipsis-compuerta.fade-out-gradual{ opacity:0; pointer-events:none; }

.smoke{ position:absolute; width:120%; height:120%; background:radial-gradient(circle, rgba(90,0,128,0.35), transparent 70%); top:-10%; left:-10%; animation:smokeMove 6s linear infinite; }
@keyframes smokeMove{ 0%{transform:translate(0,0) rotate(0deg); opacity:0.5;} 50%{transform:translate(60px,-30px) rotate(45deg); opacity:0.3;} 100%{transform:translate(-60px,50px) rotate(-45deg); opacity:0.5;} }

.spark{ position:absolute; width:4px; height:4px; background:#ffea00; border-radius:50%; opacity:0.8; animation:sparkMove linear infinite; }
@keyframes sparkMove{ 0%{transform:translate(0,0) scale(1); opacity:0.8;} 50%{transform:translate(30px,-60px) scale(2); opacity:0.5;} 100%{transform:translate(-30px,60px) scale(1); opacity:0;} }

.ultra-circle{ width:280px; height:280px; border-radius:50%; background:radial-gradient(circle, #7a00a8, #ff3300); box-shadow:0 0 120px #7a00a8, 0 0 200px #ff3300; display:flex; align-items:center; justify-content:center; z-index:2; animation:pulseUltra 1s infinite; position:relative; transition: opacity 1s ease-out; }
.ultra-circle img{ width:70%; opacity:0; transform:scale(0.3) rotate(-90deg); filter:drop-shadow(0 0 20px #7a00a8); transition:all .7s ease; }
.ultra-circle.show-logo img{ opacity:1; transform:scale(1) rotate(0deg); }

@keyframes pulseUltra{ 0%{transform:scale(1); opacity:.9;} 25%{transform:scale(1.5); opacity:1;} 50%{transform:scale(1.2); opacity:.9;} 75%{transform:scale(1.6); opacity:1;} 100%{transform:scale(1); opacity:.9;} }

.wave{ position:absolute; border-radius:50%; pointer-events:none; left:50%; top:50%; transform:translate(-50%,-50%); background:radial-gradient(circle, rgba(255,0,255,0.3) 0%, rgba(150,0,150,0.1) 60%, transparent 100%); opacity:0; animation:waveExpandEpic 2s ease-out forwards; }
@keyframes waveExpandEpic{ 0%{width:0; height:0; opacity:.9; filter:blur(2px);} 50%{opacity:.6; filter:blur(4px);} 100%{width:2200px; height:2200px; opacity:0; filter:blur(20px);} }

#apocalipsis-compuerta.final-explosion .ultra-circle{ animation:explodeUltra 1.2s forwards; }
@keyframes explodeUltra{ 0%{transform:scale(1) rotate(0); opacity:1; filter:blur(0);} 50%{transform:scale(5) rotate(180deg); opacity:.6; filter:blur(10px);} 100%{transform:scale(10) rotate(360deg); opacity:0; filter:blur(40px);} }

.shake-massive{ animation:shakeScreen .6s linear; }
@keyframes shakeScreen{ 0%,100%{transform:translate(0,0) rotate(0);} 10%{transform:translate(-25px,-10px) rotate(-2deg);} 20%{transform:translate(20px,15px) rotate(2deg);} 30%{transform:translate(-30px,20px) rotate(-3deg);} 40%{transform:translate(25px,-15px) rotate(2deg);} 50%{transform:translate(-20px,10px) rotate(-2deg);} 60%{transform:translate(20px,-20px) rotate(3deg);} 70%{transform:translate(-15px,15px) rotate(-2deg);} 80%{transform:translate(10px,-10px) rotate(2deg);} 90%{transform:translate(-5px,5px) rotate(-1deg);} }

.flash{ position:absolute; inset:0; background:radial-gradient(circle, rgba(255,255,0,0.95), rgba(255,77,0,0.6), transparent 80%); opacity:0; z-index:5; animation:flashEpic .45s ease-out forwards; }
@keyframes flashEpic{ 0%{opacity:0;} 20%{opacity:1;} 60%{opacity:.6;} 100%{opacity:0;} }

.full-flash{
  position:fixed; inset:0;
  background: radial-gradient(circle at center, rgba(255,240,0,1) 0%, rgba(255,200,0,0.8) 25%, rgba(255,150,0,0.6) 50%, rgba(255,100,0,0.4) 75%, transparent 100%);
  filter: blur(12px) brightness(1.8) contrast(1.2);
  opacity:0; z-index:9999999; animation:fullFlashAnim 1.8s forwards;
}
@keyframes fullFlashAnim{ 0%{opacity:0;} 10%{opacity:1; filter:blur(6px) brightness(2);} 40%{opacity:1; filter:blur(14px) brightness(1.8);} 80%{opacity:0.9; filter:blur(20px) brightness(1.4);} 100%{opacity:0;} }

.lens-flare { position:fixed; inset:0; background:
  linear-gradient(90deg, transparent, rgba(255,77,182,0.85), transparent),
  linear-gradient(0deg, transparent, rgba(255,215,0,0.55), transparent);
  mix-blend-mode:screen; opacity:0; z-index:99999999; animation:lensFlareAnim 1.2s forwards; }
@keyframes lensFlareAnim{ 0%{opacity:0;} 20%{opacity:1;} 80%{opacity:0.6;} 100%{opacity:0;} }

.burn-particles{ position:fixed; inset:0; background:radial-gradient(circle at center, rgba(255,215,0,0.14) 0%, transparent 70%); pointer-events:none; opacity:0; z-index:9999998; animation:burnAnim 1.5s forwards; }
@keyframes burnAnim{ 0%{opacity:0;} 20%{opacity:0.5;} 80%{opacity:0.3;} 100%{opacity:0;} }

.shockwave{ position:absolute; border:5px solid rgba(122,0,168,0.9); border-radius:50%; width:100px; height:100px; top:50%; left:50%; transform:translate(-50%,-50%) scale(0.2); opacity:.8; z-index:4; animation:shockwaveEpic .8s ease-out forwards; }
@keyframes shockwaveEpic{ 0%{transform:translate(-50%,-50%) scale(.2); opacity:.9;} 100%{transform:translate(-50%,-50%) scale(10); opacity:0;} }
	
/* ===== BOTÓN DE SONIDO ===== */
#soundToggle {
    position: fixed;
    bottom: 25px;
    right: 25px;
    width: 50px;
    height: 50px;
    background: rgba(0,0,0,0.6);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000000;
    cursor: pointer;
    transition: box-shadow 0.3s, transform 0.2s;
}
#soundToggle:hover { transform: scale(1.1);}
#soundToggle svg { width: 24px; height: 24px; fill: #fff; display: block; }
#soundToggle svg path { transform: translate(-2px, 0); }
#soundToggle line { stroke-width: 3; stroke: red; transition: opacity 0.3s; }				
</style>

<div id="apocalipsis-compuerta">
  <div class="smoke"></div>
  <?php for($i=0;$i<70;$i++): ?>
    <div class="spark" style="top:<?= rand(0,100) ?>%; left:<?= rand(0,100) ?>%; animation-duration: <?= rand(1,4) ?>s;"></div>
  <?php endfor; ?>
  <div class="ultra-circle">
    <img src="/images/logo.webp" alt="Logo">
  </div>
  <!--<audio id="boomSound" src="https://www.myinstants.com/media/sounds/explosion.mp3" preload="auto" playsinline></audio>-->
  <audio id="boomSound" src="/sounds/explosion-two"></audio>	
  <audio id="finalBoomSound" src="/sounds/explosion-three"></audio>
</div>

<!-- Botón de sonido -->
<div id="soundToggle">
    <svg viewBox="0 0 24 24" preserveAspectRatio="xMidYMid meet">
        <path d="M9 9v6h4l5 5V4l-5 5H9z"/>
        <line id="muteLine" x1="1" y1="1" x2="23" y2="23"/>
    </svg>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
  const compuerta = document.getElementById("apocalipsis-compuerta");
  const ultra     = document.querySelector(".ultra-circle");
  const boom      = document.getElementById("boomSound");
  const finalBoom = document.getElementById("finalBoomSound");
  const body = document.body;
  body.classList.add("hud-lock");	
	
  // BOTÓN DE SONIDO
  const soundToggle = document.getElementById("soundToggle");
  const muteLine = document.getElementById("muteLine");	
  let soundEnabled = false;	
	
  soundToggle.addEventListener("click", () => {
	  soundEnabled = !soundEnabled;
	  muteLine.style.opacity = soundEnabled ? 0 : 1;	  
	  if (!soundEnabled){			
	  		// Pausar y reiniciar todos los audios del DOM
			document.querySelectorAll('audio').forEach(audio => {
				audio.pause();
            	audio.currentTime = 0;
			});						        	
	  }
   });  	
   function playSound(audio){
   		if(soundEnabled){ audio.currentTime = 0; audio.play().catch(()=>{}); }
   }   
	
  const limpiarDespuesDelAudio = () => {
    compuerta.classList.add("fade-out-gradual");
    setTimeout(() => { compuerta.remove(); document.body.style.overflow = ''; }, 1000);
  };

  document.body.style.overflow = 'hidden';
  setTimeout(() => ultra.classList.add("show-logo"), 400);

  [1200,2000,2800].forEach(t => {
    setTimeout(() => {
      compuerta.classList.add("shake-massive");
      setTimeout(()=>compuerta.classList.remove("shake-massive"), 600);
      const flash = document.createElement("div"); flash.className = "flash"; compuerta.appendChild(flash);
      const shock = document.createElement("div"); shock.className = "shockwave"; compuerta.appendChild(shock);
	  playSound(boom);
    }, t);
  });

  // Explosión final estilo “galería”
  setTimeout(() => {
    compuerta.classList.add("final-explosion");

    document.querySelectorAll("#apocalipsis-compuerta .spark, #apocalipsis-compuerta .smoke").forEach(el => el.remove());

    // Flash final
    const fullFlash = document.createElement("div"); fullFlash.className = "full-flash"; document.body.appendChild(fullFlash); setTimeout(() => fullFlash.remove(), 1800);

    // Lens flare
    const flare = document.createElement("div"); flare.className = "lens-flare"; document.body.appendChild(flare); setTimeout(() => flare.remove(), 1200);

    // Burn particles
    const burn = document.createElement("div"); burn.className = "burn-particles"; document.body.appendChild(burn); setTimeout(() => burn.remove(), 1500);

    // Shockwaves
    for (let i = 0; i < 3; i++) {
      const shock = document.createElement("div");
      shock.className = "shockwave";
      shock.style.animationDelay = `${i * 0.15}s`;
      compuerta.appendChild(shock);
      setTimeout(() => shock.remove(), 1000 + i*150);
    }

    // Logo explota
    ultra.style.transition = 'opacity 1s ease-out, transform 1.2s ease-out';
    ultra.style.opacity = 0;
    ultra.style.transform = 'scale(10) rotate(360deg)';
	  	
    // Sonido final  
	playSound(finalBoom);
	  
    // Fondo transparente    
    // compuerta.style.transition = 'opacity 1s ease-out';
    // compuerta.style.backgroundColor = 'transparent';    	 
	compuerta.style.opacity = '0'; // Remover la pantalla violeta inmediatamente
	compuerta.style.pointerEvents = 'none'; // Desactivar interacciones
	  
    setTimeout(() => { 		
		limpiarDespuesDelAudio(); 
		var loadingSound = document.getElementById('loading-sound');
		if (loadingSound) {						
			playSound(loadingSound);						
		}		
		setTimeout(()=> {
			window.location.href = "/intro";
		}, 500);
	}, 3000);	  	  
  }, 3800);
});
</script>
<?php } 

function insertar_animacion_apocalipsis_GDI_CUATRO(){ ?>
<script>
	document.documentElement.classList.add("hud-lock");
</script>
<style>
/* HUD lock */
.hud-lock, body.hud-lock {
	overflow: hidden !important;
    height: 100vh !important;
    touch-action: none;
}
.hud-lock [class*="xoo-wsc"], .hud-lock [id*="xoo-wsc"] {
	display: none !important;
}		

/* ===== CONTENEDOR ===== */
#apocalipsis-compuerta {
    position: fixed; top:0; left:0;
    width:100vw; height:100vh;
    background: radial-gradient(circle at center, #000000, #9b2d91 90%);
    overflow: hidden; display:flex; align-items:center; justify-content:center;
    z-index: 999999;
    transition: opacity 2s ease-out, transform 2s ease-out;
    opacity: 1;
}
.shake-wrapper {
    width:100%; height:100%;
    display:flex; align-items:center; justify-content:center;
    position:relative;
}
.shake-wrapper.shake-strong { animation: shakeStrong 0.2s infinite; }
@keyframes shakeStrong {
  0%,100%{transform:translate(0,0) rotate(0);}
  25%{transform:translate(-20px,-15px) rotate(-2deg);}
  50%{transform:translate(20px,15px) rotate(2deg);}
  75%{transform:translate(-15px,10px) rotate(-1deg);}
}

/* Humo nuclear */
.smoke {
    position:absolute; width:140%; height:140%;
    background: radial-gradient(circle, rgba(50,0,50,0.5), transparent 70%);
    top:-20%; left:-20%;
    animation: smokeMove 8s linear infinite;
}
@keyframes smokeMove {
    0% {transform:translate(0,0) rotate(0deg); opacity:0.6;}
    50% {transform:translate(80px,-40px) rotate(45deg); opacity:0.3;}
    100% {transform:translate(-80px,60px) rotate(-45deg); opacity:0.6;}
}

/* Sparks */
.spark { 
    position:absolute; width:5px; height:5px; 
    background: radial-gradient(circle, #ff00ff, #ff80ff);
    border-radius:50%; opacity:0.9; animation:sparkMove linear infinite;
    box-shadow:0 0 8px #ff00ff, 0 0 12px #ff80ff;
}
@keyframes sparkMove { 
    0%{transform:translate(0,0) scale(1) rotate(0deg); opacity:0.9;}
    50%{transform:translate(40px,-80px) scale(2) rotate(180deg); opacity:0.6;}
    100%{transform:translate(-40px,80px) scale(1) rotate(360deg); opacity:0;}
}

/* Círculo */
.ultra-circle {
    width:280px; height:280px; border-radius:50%;
    background: radial-gradient(circle, #ff00ff, #9b2d91);
    box-shadow:0 0 120px #ff00ff,0 0 200px #9b2d91;
    display:flex; align-items:center; justify-content:center;
    z-index:2; animation:pulseUltra 1s infinite; position:relative;
}
.ultra-circle img {
    width:70%; opacity:0; transform:scale(0.3) rotate(-90deg);
    filter: drop-shadow(0 0 25px #ff00ff);
    transition: all 0.7s ease;
}
.ultra-circle.show-logo img { opacity:1; transform:scale(1) rotate(0deg); }
@keyframes pulseUltra {
    0%{transform:scale(1); opacity:0.9;}
    25%{transform:scale(1.6); opacity:1;}
    50%{transform:scale(1.3); opacity:0.9;}
    75%{transform:scale(1.7); opacity:1;}
    100%{transform:scale(1); opacity:0.9;}
}

/* Explosión nuclear */
/*
.explosion-nuclear {
    position:absolute; left:50%; top:50%;
    width:400px; height:400px;
    background: radial-gradient(circle, #ff00ff, #ff0000);
    border-radius:50%; transform: translate(-50%,-50%);
    pointer-events:none; opacity:0.9;
    z-index:9999999; animation:explosionFinal 2s ease-out forwards;
}
*/
	
@keyframes explosionFinal {
    0% {transform:scale(1); opacity:1; filter:blur(2px);}
    100% {transform:scale(120); opacity:0; filter:blur(120px);}
}

/* Onda expansiva */
.wave {
  position: absolute;
  left: 50%; top: 50%;
  transform: translate(-50%, -50%);
  border-radius: 50%;
  border: 6px solid rgba(255,0,255,0.6);
  background: rgba(255,0,255,0.2);
  pointer-events: none;
  animation: waveExpandEpic 2.5s ease-out forwards;
  z-index: 9999;
}
@keyframes waveExpandEpic {
  0% { width:0; height:0; opacity:1; filter:blur(2px);}
  50% { width:2500px; height:2500px; opacity:0.8; filter:blur(40px);}
  100% { width:4000px; height:4000px; opacity:0; filter:blur(80px);}
}

/* Rayos */
.rayo {
    position:absolute; width:4px; height:1000px;
    background: linear-gradient(to bottom, #fff, rgba(255,0,255,0));
    left:50%; top:50%;
    transform-origin: top center;
    opacity:0.8; animation: rayoFade 1.2s ease-out forwards;
}
@keyframes rayoFade { 0% {opacity:1;} 100% {opacity:0;} }

/* Flash total */
.flash-total {
    position: absolute; top:0; left:0; width:100%; height:100%;
    background: white; opacity:0;
    animation: flashTotal 1.2s forwards;
    z-index: 10000000;
}
@keyframes flashTotal {
    0%{opacity:0;}
    20%{opacity:1;}
    100%{opacity:0;}
}

/* Fracturas de vidrio */
.cracks {
    position: absolute;
    top: 0; left: 0;
    width: 100%; height: 100%;
    /*background: url("/images/crackfinal.png") center/cover no-repeat;*/		
	background: url("/images/crackfinal_two.png") center/cover no-repeat;
    opacity: 0;
    z-index: 10000001;
    animation: cracksAppear 1.2s ease-out forwards;    
    pointer-events: none; /* no bloquea clicks */
}

@keyframes cracksAppear {
    0%   { opacity: 0; transform: scale(0.9) rotate(0deg); filter: blur(8px); }
    60%  { opacity: 1; transform: scale(1.05) rotate(1deg); filter: blur(0); }
    100% { opacity: 1; transform: scale(1) rotate(0deg); }
}

/* Glow final */
.final-glow {
  position: absolute; top:0; left:0; width:100%; height:100%;
  background: radial-gradient(circle at center, rgba(255,255,255,0.95), transparent 80%);
  opacity:0; animation: glowFade 3s forwards;
  z-index: 99999;
}
@keyframes glowFade {
  0% {opacity: 0;}
  50% {opacity: 1;}
  100% {opacity: 0;}
}

/* Glitch */
.glitch {
    position: absolute; top:0; left:0; width:100%; height:100%;
    background: repeating-linear-gradient(0deg, rgba(255,0,255,0.2), transparent 2px);
    animation: glitchAnim 0.5s infinite;
    z-index: 999999;
}
@keyframes glitchAnim {
    0%{transform:translate(0,0);}
    25%{transform:translate(-10px,5px);}
    50%{transform:translate(5px,-5px);}
    75%{transform:translate(-5px,10px);}
    100%{transform:translate(0,0);}
}

#apocalipsis-compuerta.fade-out-gradual { opacity: 0; transform: scale(1.12); }
		
/* ===== BOTÓN DE SONIDO ===== */
#soundToggle {
    position: fixed;
    bottom: 25px;
    right: 25px;
    width: 50px;
    height: 50px;
    background: rgba(0,0,0,0.6);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000000;
    cursor: pointer;
    transition: box-shadow 0.3s, transform 0.2s;
}
	
#soundToggle:hover { transform: scale(1.1);}
#soundToggle svg { width: 24px; height: 24px; fill: #fff; display: block; }
#soundToggle svg path { transform: translate(-2px, 0); }
#soundToggle line { stroke-width: 3; stroke: red; transition: opacity 0.3s; }			
</style>

<div id="apocalipsis-compuerta">
    <div class="shake-wrapper">
        <div class="smoke"></div>
        <?php for($i=0;$i<120;$i++): ?>
            <div class="spark" style="top:<?= rand(0,100) ?>%; left:<?= rand(0,100) ?>%; animation-duration: <?= rand(1,5) ?>s;"></div>
        <?php endfor; ?>
        <div class="ultra-circle">
            <img src="/images/logo.webp" alt="Logo">
        </div>
    </div>
    <audio id="boomSound" src="/sounds/explosion-two"></audio>
	<audio id="glassBreaking" src="/sounds/glass-breaking"></audio>
</div>

<div id="soundToggle">
    <svg viewBox="0 0 24 24" preserveAspectRatio="xMidYMid meet">
        <path d="M9 9v6h4l5 5V4l-5 5H9z"/>
        <line id="muteLine" x1="1" y1="1" x2="23" y2="23"/>
    </svg>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const compuerta = document.getElementById("apocalipsis-compuerta");
    const wrapper = compuerta.querySelector(".shake-wrapper");
    const boom = document.getElementById("boomSound");
	const glassBreaking = document.getElementById("glassBreaking");
    const ultraCircle = document.querySelector(".ultra-circle");	
    const body = document.body;
    body.classList.add("hud-lock");
    document.body.style.overflow = 'hidden';
	const soundToggle = document.getElementById("soundToggle");
    const muteLine = document.getElementById("muteLine");
    let soundEnabled = false;
	
	soundToggle.addEventListener("click", () => {
    	soundEnabled = !soundEnabled;
    	muteLine.style.opacity = soundEnabled ? 0 : 1;
		if(!soundEnabled){			
			// Pausar y reiniciar todos los audios del DOM
        	document.querySelectorAll('audio').forEach(audio => {
            	audio.pause();
            	audio.currentTime = 0;
        	});						        	
    	}
    });
    function playSound(audio){
        if(soundEnabled){
            audio.currentTime = 0;
            audio.play().catch(()=>{});
        }
    }

    setTimeout(() => ultraCircle.classList.add("show-logo"), 400);

    [1200,1700,2200,2700].forEach(t => {
        setTimeout(() => {
            const wave = document.createElement("div");
            wave.className = "wave";
            compuerta.appendChild(wave);
            setTimeout(()=>wave.remove(), 2500);
            playSound(boom);
            for(let i=0;i<12;i++){
                const rayo=document.createElement("div");
                rayo.className="rayo";
                rayo.style.transform=`translate(-50%,-50%) rotate(${i*30}deg)`;
                compuerta.appendChild(rayo);
                setTimeout(()=>rayo.remove(), 1200);
            }
        }, t);
    });

    // Explosión nuclear + fracturas
    setTimeout(() => {
		
		/*
        const explosion = document.createElement("div");
        explosion.className = "explosion-nuclear";
        compuerta.appendChild(explosion);
        playSound(boom);
		*/

        const flash = document.createElement("div");
        flash.className = "flash-total";
        compuerta.appendChild(flash);
        setTimeout(()=>flash.remove(),1200);

        // fracturas de pantalla
        const cracks = document.createElement("div");
        cracks.className = "cracks";
        compuerta.appendChild(cracks);		
		playSound(glassBreaking);
		
        wrapper.classList.add("shake-strong");
    }, 3200);

    // Glow + glitch
    setTimeout(() => {
        const glow = document.createElement("div");
        glow.className = "final-glow";
        compuerta.appendChild(glow);

        const glitch = document.createElement("div");
        glitch.className = "glitch";
        compuerta.appendChild(glitch);
        setTimeout(()=>glitch.remove(), 2000);
    }, 5200);

    // Fade out
    setTimeout(() => {
        compuerta.classList.add("fade-out-gradual");
        wrapper.classList.remove("shake-strong");
        ultraCircle.style.opacity = 0;
        document.querySelectorAll(".spark, .smoke, .wave, .rayo").forEach(el => el.remove());
        document.body.style.overflow = '';
        setTimeout(()=> compuerta.remove(), 1500);
		
		// Iniciamos sonido de la pantalla de carga
		var loadingSound = document.getElementById('loading-sound');
		if (loadingSound) {	
			playSound(loadingSound);						
		}		
		setTimeout(()=> {
			window.location.href = "/intro";
		}, 500);
    }, 7200);
});
</script>
<?php }  

function insertar_animacion_apocalipsis_NOD_UNO(){ ?>
<!-- Bloque para bloquear HUD y carrito XOATix desde el inicio -->
<script>
	document.documentElement.classList.add("hud-lock");
</script>
<style>
/* ===== BLOQUE HUD LOCK ===== */
.hud-lock, body.hud-lock {
	overflow: hidden !important;
    height: 100vh !important;
    touch-action: none;
}
.hud-lock [class*="xoo-wsc"], .hud-lock [id*="xoo-wsc"] {
	display: none !important;
}		

/* ===== FULLSCREEN APOCALIPSIS CINEMÁTICO ===== */
#apocalipsis-compuerta {
    position: fixed;
    top: -20%; 
    left: -20%;
    width: 140vw; 
    height: 140vh;
    background: radial-gradient(circle at center, #000000, #330011 90%);
    overflow: hidden;
    display:flex; 
    align-items:center; 
    justify-content:center;
    z-index: 999999;
    transition: opacity 2s ease-out, transform 2s ease-out;
    opacity:1;
}
body { overflow: hidden !important; }

/* Humo dinámico */
.smoke {
    position:absolute; width:120%; height:120%;
    background: radial-gradient(circle, rgba(50,0,0,0.45), transparent 70%);
    top:-10%; left:-10%;
    animation: smokeMove 6s linear infinite;
}
@keyframes smokeMove { 
    0% {transform:translate(0,0) rotate(0deg); opacity:0.5;} 
    50% {transform:translate(60px,-30px) rotate(45deg); opacity:0.3;} 
    100% {transform:translate(-60px,50px) rotate(-45deg); opacity:0.5;} 
}

/* Chispas explosivas */
.spark { 
    position:absolute; width:4px; height:4px; 
    background: radial-gradient(circle, #ff0000, #ffcc00); 
    border-radius:50%; opacity:0.8; 
    animation:sparkMove linear infinite;
    box-shadow:0 0 8px #ff0000, 0 0 12px #ffcc00;
}
@keyframes sparkMove {
    0%{transform:translate(0,0) scale(1);opacity:0.8;} 
    50%{transform:translate(30px,-60px) scale(2);opacity:0.5;} 
    100%{transform:translate(-30px,60px) scale(1);opacity:0;}
}

/* Fragmentos centrífugos */
.fragment { 
    position:absolute; width:6px; height:6px; 
    background:#ff1a1a; 
    border-radius:50%; top:50%; left:50%; 
    opacity:0.9; transform: translate(-50%,-50%); 
    animation: fragmentFly 2s ease-out forwards; 
}
@keyframes fragmentFly { 
    0% {transform:translate(-50%,-50%) scale(1); opacity:1;} 
    100% {transform:translate(calc(-50% + var(--x)), calc(-50% + var(--y))) scale(0.5); opacity:0;}
}

/* Círculo central rojo degradado */
.ultra-circle { 
    width:280px; height:280px; border-radius:50%; 
    background: radial-gradient(circle, #ff0000, #330000); 
    box-shadow:0 0 120px #ff0000,0 0 200px #330000; 
    display:flex; align-items:center; justify-content:center; 
    z-index:2; animation:pulseUltra 0.8s infinite alternate; 
    position:relative; 
    transition: opacity 2s ease-out, transform 2s ease-out, box-shadow 2s ease-out; 
}
.ultra-circle img { 
    width:70%; opacity:0; transform:scale(0.3) rotate(-90deg); 
    filter:drop-shadow(0 0 20px #ff0000); 
    transition: all 0.7s ease; 
}
.ultra-circle.show-logo img { opacity:1; transform:scale(1) rotate(0deg); }
@keyframes pulseUltra { 
    0%{transform:scale(1); opacity:0.9;} 
    25%{transform:scale(1.5); opacity:1;} 
    50%{transform:scale(1.2); opacity:0.9;} 
    75%{transform:scale(1.6); opacity:1;} 
    100%{transform:scale(1); opacity:0.9;} 
}

/* Ondas épicas */
.wave { 
    position:absolute; border-radius:50%; 
    background: radial-gradient(circle, rgba(255,0,0,0.3) 0%, rgba(100,0,0,0.1) 60%, transparent 100%); 
    pointer-events:none; transform: translate(-50%, -50%); 
    left:50%; top:50%; opacity:0; 
    animation: waveExpandEpic 2s ease-out forwards; 
}
@keyframes waveExpandEpic { 
    0% { width:0; height:0; opacity:0.9; filter:blur(2px);} 
    50% { opacity:0.6; filter:blur(4px);} 
    100% { width:2200px; height:2200px; opacity:0; filter:blur(20px);} 
}

/* Shake extremo */
@keyframes shakeScreen {
    0%,100%{transform:translate(0,0) rotate(0);}
    10%{transform:translate(-50px,-25px) rotate(-4deg);}
    20%{transform:translate(50px,25px) rotate(4deg);}
    30%{transform:translate(-40px,30px) rotate(-3deg);}
    40%{transform:translate(40px,-30px) rotate(3deg);}
    50%{transform:translate(-35px,20px) rotate(-2deg);}
    60%{transform:translate(35px,-25px) rotate(2deg);}
    70%{transform:translate(-30px,15px) rotate(-1deg);}
    80%{transform:translate(30px,-15px) rotate(1deg);}
    90%{transform:translate(-25px,10px) rotate(0);}
}
.shake { animation: shakeScreen 0.7s infinite; transform-origin:center; }

/* Flash final ultra */
.final-flash { 
    position:absolute; top:0; left:0; width:100%; height:100%; 
    background: radial-gradient(circle, rgba(255,0,0,0.9), rgba(120,0,0,0.5), transparent 80%); 
    opacity:0; pointer-events:none; 
    animation: flashFade 2s ease-out forwards;
}
@keyframes flashFade {0% {opacity:0;}20% {opacity:1;}100% {opacity:0;}}

/* ===== BOTÓN DE SONIDO ===== */
#soundToggle {
    position: fixed;
    bottom: 25px;
    right: 25px;
    width: 50px;
    height: 50px;
    background: rgba(0,0,0,0.6);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000000;
    cursor: pointer;
    transition: box-shadow 0.3s, transform 0.2s;
}
#soundToggle:hover { transform: scale(1.1);}
#soundToggle svg { width: 24px; height: 24px; fill: #fff; display: block; }
#soundToggle svg path { transform: translate(-2px, 0); }
#soundToggle line { stroke-width: 3; stroke: red; transition: opacity 0.3s; }	
</style>

<div id="apocalipsis-compuerta">
    <div class="smoke"></div>
    <?php for($i=0;$i<60;$i++): ?>
        <div class="spark" style="top:<?= rand(0,100) ?>%; left:<?= rand(0,100) ?>%; animation-duration: <?= rand(1,4) ?>s;"></div>
    <?php endfor; ?>
    <div class="ultra-circle">
        <img src="/images/logo.webp" alt="Logo">
    </div>
    <audio id="boomSound" src="/sounds/explosion-three"></audio>	
	<audio id="wavesSound" src="/sounds/sci_fi_radar_pings"></audio>   
</div>

<!-- Botón de sonido -->
<div id="soundToggle">
    <svg viewBox="0 0 24 24" preserveAspectRatio="xMidYMid meet">
        <path d="M9 9v6h4l5 5V4l-5 5H9z"/>
        <line id="muteLine" x1="1" y1="1" x2="23" y2="23"/>
    </svg>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const compuerta = document.getElementById("apocalipsis-compuerta");
    const ultraCircle = document.querySelector(".ultra-circle");
    const boom = document.getElementById("boomSound");    
	const body = document.body;
	body.classList.add("hud-lock");
    document.body.style.overflow = 'hidden';

    // BOTÓN DE SONIDO
    const soundToggle = document.getElementById("soundToggle");
    const muteLine = document.getElementById("muteLine");
    let soundEnabled = false;
	
	soundToggle.addEventListener("click", () => {
    	soundEnabled = !soundEnabled;
    	muteLine.style.opacity = soundEnabled ? 0 : 1;
		if(!soundEnabled){			
			// Pausar y reiniciar todos los audios del DOM
       		document.querySelectorAll('audio').forEach(audio => {
           		audio.pause();
           		audio.currentTime = 0;
       		});						        	
    	}
    });
    function playSound(audio){
        if(soundEnabled){
            audio.currentTime = 0;
            audio.play().catch(()=>{});
        }
    }

    // Mostrar logo
    setTimeout(() => ultraCircle.classList.add("show-logo"), 400);

    // Ondas sucesivas
    [1200,1600,2000,2400,2800,3200].forEach(t => {
        setTimeout(() => {
            for(let j=0;j<3;j++){
                const wave = document.createElement("div");
                wave.className = "wave";
                wave.style.animationDuration = `${1.5 + Math.random()*0.5}s`;
                compuerta.appendChild(wave);
                setTimeout(()=>wave.remove(), 2200);						
             	playSound(wavesSound); // Reproduce el sonido de las ondas												
            }            
        }, t);
    });

    // Fragmentos explosivos
    function spawnFragments(){
        for(let i=0;i<30;i++){
            const frag = document.createElement("div");
            frag.className = "fragment";
            frag.style.setProperty('--x', `${(Math.random()-0.5)*2000}px`);
            frag.style.setProperty('--y', `${(Math.random()-0.5)*1200}px`);
            compuerta.appendChild(frag);
            setTimeout(()=>frag.remove(),2000);
        }
    }

    // Explosión final
    setTimeout(() => {				
    	wavesSound.pause(); // Detenemos el sonido de las ondas
    	wavesSound.currentTime = 0; // Regresar el sonido al inicio		
        compuerta.classList.add("shake"); // Ahora ejecutamos la explosión
        playSound(boom);        
        spawnFragments();
    }, 4000);

    // Flash final y fade out
    setTimeout(() => {
        const flash = document.createElement("div");
        flash.className = "final-flash";
        compuerta.appendChild(flash);
        compuerta.style.opacity = 0;
        ultraCircle.style.opacity = 0;
        document.querySelectorAll(".spark, .smoke, .wave, .fragment").forEach(el => el.style.opacity = 0);
        compuerta.style.background = "radial-gradient(circle at center, rgba(0,0,0,0) 0%, rgba(50,0,0,0) 100%)";
		
		// Iniciamos sonido de la pantalla de carga
		var loadingSound = document.getElementById('loading-sound');
		if (loadingSound) {						
			playSound(loadingSound);						
		}	
				
        setTimeout(()=> {
            compuerta.remove();
            document.body.style.overflow = '';						
			
			// Mutear y pausar en este momento (no esperar al redirect)
			if (loadingSound) {
				loadingSound.muted = true;
				loadingSound.pause(); 
				loadingSound.currentTime = 0;
				loadingSound.remove();
			}

			// Redirigimos después de 500ms ya con el audio drenado
			setTimeout(()=> {
				window.location.href = "/intro";
			}, 500);			
        }, 2000);		
    }, 5800);
});
</script>
<?php 
}  

function insertar_animacion_apocalipsis_NOD_DOS(){ ?>
<!-- Bloque para bloquear HUD y carrito XOATix desde el inicio -->
<script>
	document.documentElement.classList.add("hud-lock");
</script>
<style>
/* ===== BLOQUE HUD LOCK ===== */
.hud-lock, body.hud-lock {
	overflow: hidden !important;
    height: 100vh !important;
    touch-action: none;
}
.hud-lock [class*="xoo-wsc"], .hud-lock [id*="xoo-wsc"] {
	display: none !important;
}		

/* Contenedor principal */
#apocalipsis-compuerta {
    position: fixed; top:0; left:0;
    width:100vw; height:100vh;
	/*background: radial-gradient(circle at center, #000000, #330000 90%);*/	
	background: radial-gradient(circle at center, #000000, #330011 90%);
    overflow: hidden; display:flex; align-items:center; justify-content:center;
    z-index: 999999;
    transition: opacity 2s ease-out, transform 2s ease-out;
    opacity: 1;
}

/* Shake screen */
.shake-wrapper { width:100%; height:100%; display:flex; align-items:center; justify-content:center; position:relative; }
.shake-wrapper.shake { animation: shakeScreen 0.8s infinite; will-change: transform; }
@keyframes shakeScreen {
  0%,100%{transform:translate(0,0) rotate(0);}
  10%{transform:translate(-10px,-5px) rotate(-1deg);}
  20%{transform:translate(10px,5px) rotate(1deg);}
  30%{transform:translate(-8px,6px) rotate(-1deg);}
  40%{transform:translate(8px,-6px) rotate(1deg);}
  50%{transform:translate(-6px,3px) rotate(-1deg);}
  60%{transform:translate(6px,-3px) rotate(1deg);}
  70%{transform:translate(-4px,2px) rotate(-1deg);}
  80%{transform:translate(4px,-2px) rotate(1deg);}
  90%{transform:translate(-2px,1px) rotate(-1deg);}
}

/* Humo */
.smoke {
    position:absolute; width:120%; height:120%;
    /*background: radial-gradient(circle, rgba(0,0,0,0.45), transparent 70%);*/	
	background: radial-gradient(circle, rgba(50,0,0,0.45), transparent 70%);	
    top:-10%; left:-10%;
    animation: smokeMove 6s linear infinite;
}
@keyframes smokeMove {
    0% {transform:translate(0,0) rotate(0deg); opacity:0.5;}
    50% {transform:translate(60px,-30px) rotate(45deg); opacity:0.3;}
    100% {transform:translate(-60px,50px) rotate(-45deg); opacity:0.5;}
}

/* Chispas */
.spark { 
    position:absolute; width:5px; height:5px; 
    /*background: radial-gradient(circle, #ff0000, #ff5050);*/	
	background: radial-gradient(circle, #ff0000, #ffcc00); 	
    border-radius:50%; opacity:0.9; animation:sparkMove linear infinite;
    box-shadow:0 0 8px #ff0000, 0 0 12px #ff5050;
}
@keyframes sparkMove { 
    0%{transform:translate(0,0) scale(1); opacity:0.9;}
    50%{transform:translate(30px,-60px) scale(2); opacity:0.6;}
    100%{transform:translate(-30px,60px) scale(1); opacity:0;}
}

/* Círculo central */
.ultra-circle {
    width:280px; height:280px; border-radius:50%;		
    /*background: radial-gradient(circle, #ff0000, #b30000);*/
	background: radial-gradient(circle, #ff0000, #330000); 		
    box-shadow:0 0 120px #ff0000,0 0 200px #b30000;
    display:flex; align-items:center; justify-content:center;
    z-index:2; animation:pulseUltra 1s infinite; position:relative;
}
.ultra-circle img {
    width:70%; opacity:0; transform:scale(0.3) rotate(-90deg);
    filter: drop-shadow(0 0 25px #ff0000);
    transition: all 0.7s ease;
}
.ultra-circle.show-logo img { opacity:1; transform:scale(1) rotate(0deg); }
@keyframes pulseUltra {
    0%{transform:scale(1); opacity:0.9;}
    25%{transform:scale(1.5); opacity:1;}
    50%{transform:scale(1.2); opacity:0.9;}
    75%{transform:scale(1.6); opacity:1;}
    100%{transform:scale(1); opacity:0.9;}
}

/* Ondas expansivas */
.wave {
  position:absolute; border-radius:50%;
  /*background: radial-gradient(circle, rgba(255,0,0,0.5) 0%, rgba(150,0,0,0.1) 60%, transparent 100%);*/
  background: radial-gradient(circle, rgba(255,0,0,0.3) 0%, rgba(100,0,0,0.1) 60%, transparent 100%); 
  pointer-events:none;
  transform: translate(-50%, -50%);
  left:50%; top:50%; opacity:0;
  animation: waveExpandEpic 2s ease-out forwards;
}
@keyframes waveExpandEpic {
  0% { width:0; height:0; opacity:0.9; filter:blur(2px);}
  100% { width:3000px; height:3000px; opacity:0; filter:blur(40px);}
}

/* Fracturas */
.cracks {
    position:absolute; top:0; left:0; width:100%; height:100%;
    background: url("/images/crackfinal_two.png") center/cover no-repeat;	
    opacity:0; z-index:10000001;
    animation: cracksAppear 0.20s cubic-bezier(0.2, 0.8, 0.4, 1) forwards;
    mix-blend-mode: screen;
    pointer-events:none;
}	
	
@keyframes cracksAppear {
    0%   { opacity: 0; transform: scale(1.2) rotate(2deg); filter: blur(2px);}
    80%  { opacity: 1; transform: scale(0.98) rotate(-1deg); filter: blur(0);}
    100% { opacity: 1; transform: scale(1) rotate(0deg);}
}

/* Relámpagos */
.lightning {
    position:absolute; width:2px; height:100vh;
    background: linear-gradient(to bottom, #fff, rgba(255,0,0,0));
    top:0; opacity:0.8;
    animation: lightningFlash 0.2s ease-in-out forwards;
}
@keyframes lightningFlash { 0% {opacity:0.8;} 100% {opacity:0;} }

/* Explosión final */
#apocalipsis-compuerta.fade-out .ultra-circle {
    animation: explodeUltraEpic 1.5s forwards;
}
@keyframes explodeUltraEpic {
    0%{transform:scale(1); opacity:1;}
    100%{transform:scale(14) rotate(1080deg); opacity:0; filter:blur(80px);}
}

/* Glow final */
.final-glow {
    position:absolute; left:50%; top:50%;
    width:0; height:0;
    background: radial-gradient(circle, rgba(255,0,0,0.6) 0%, rgba(120,0,0,0.2) 60%, transparent 100%);
    border-radius:50%; transform: translate(-50%,-50%);
    pointer-events:none; opacity:0; z-index:999998;
    animation: finalGlow 1.5s ease-out forwards;
}
@keyframes finalGlow {
    0% { width:0; height:0; opacity:0.8;}
    100% { width:3000px; height:3000px; opacity:0;}
}

/* Fade */
#apocalipsis-compuerta.fade-out-gradual { opacity: 0; transform: scale(1.12); }

/* ===== BOTÓN DE SONIDO ===== */
#soundToggle {
    position: fixed; bottom: 25px; right: 25px;
    width: 50px; height: 50px;
    background: rgba(0,0,0,0.6);
    border-radius: 50%; display: flex; align-items: center; justify-content: center;
    z-index: 1000000; cursor: pointer;
}
			
#soundToggle svg {
    width: 24px;
    height: 24px;
    fill: #fff;
    display: block;  /* evita desajuste inline */
}

#soundToggle svg path {
    transform: translate(-2px, 0); /* corrige el offset */
}
		
#soundToggle line {
    stroke-width: 3;
    stroke: red;
    transition: opacity 0.3s ease-in-out;
}

/* Sonidos de fondo y efectos */
audio { display: none; }
</style>
<div id="apocalipsis-compuerta">
    <div class="shake-wrapper">
        <div class="smoke"></div>
        <div class="spark"></div>
        <div class="spark"></div>
        <div class="spark"></div>
        <div class="wave"></div>
        <div class="ultra-circle">
            <img src="/images/logo.webp" alt="Logo">
        </div>
    </div>
    <audio id="boomSound" src="/sounds/explosion-two"></audio>	
	<audio id="glassBreaking" src="/sounds/glass-breaking"></audio>	
</div>

<div id="soundToggle">
    <svg viewBox="0 0 24 24">
        <path d="M9 9v6h4l5 5V4l-5 5H9z"/>
        <line id="muteLine" x1="1" y1="1" x2="23" y2="23"/>
    </svg>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const compuerta = document.getElementById("apocalipsis-compuerta");
    const wrapper = compuerta.querySelector(".shake-wrapper");
    const boom = document.getElementById("boomSound");
	const glassBreaking = document.getElementById("glassBreaking");
    const ultraCircle = document.querySelector(".ultra-circle");	
	const soundToggle = document.getElementById("soundToggle");
    const muteLine = document.getElementById("muteLine");
    let soundEnabled = false;

    soundToggle.addEventListener("click", () => {
        soundEnabled = !soundEnabled;
        muteLine.style.opacity = soundEnabled ? 0 : 1;
		if(!soundEnabled){			
			// Pausar y reiniciar todos los audios del DOM
        	document.querySelectorAll('audio').forEach(audio => {
            	audio.pause();
            	audio.currentTime = 0;
        	});						        	
    	}
    });
    function playSound(audio){
        if(soundEnabled){ audio.currentTime = 0; audio.play().catch(()=>{}); }
    }

    setTimeout(() => ultraCircle.classList.add("show-logo"), 400);

    [1200,1700,2200].forEach(t => {
        setTimeout(() => {
            const wave = document.createElement("div");
            wave.className = "wave";
            compuerta.appendChild(wave);
            setTimeout(()=>wave.remove(), 2500);
            playSound(boom);
            const flash = document.createElement("div");
            flash.className = "lightning";
            flash.style.left = Math.random()*100 + "%";
            compuerta.appendChild(flash);
            setTimeout(()=>flash.remove(), 200);
        }, t);
    });

    // Fracturas aparecen con el gran impacto
    setTimeout(() => {
        const cracks = document.createElement("div");
        cracks.className = "cracks";
        compuerta.appendChild(cracks);		
		playSound(glassBreaking);		
    }, 2800);

    // Explosión final
    setTimeout(() => {
        wrapper.classList.add("shake");
        compuerta.classList.add("fade-out");
        playSound(boom);
    }, 3500);

    setTimeout(() => {
        const glow = document.createElement("div");
        glow.className = "final-glow";
        compuerta.appendChild(glow);
    }, 2000);

    setTimeout(() => {
        compuerta.classList.add("fade-out-gradual");
        wrapper.classList.remove("shake");
        ultraCircle.style.opacity = 0;
        document.querySelectorAll(".spark, .smoke, .wave, .lightning").forEach(el => el.remove());
        setTimeout(()=> compuerta.remove(), 1500);		
		
		// Iniciamos sonido de la pantalla de carga
		var loadingSound = document.getElementById('loading-sound');
		if (loadingSound) {						
			playSound(loadingSound);						
		}				
		setTimeout(()=> {
			window.location.href = "/intro";
		}, 500);
    }, 4200);
});
</script>
<?php }

function insertar_animacion_apocalipsis_NOD_TRES(){ ?>
<!-- Bloque para bloquear HUD y carrito XOATix desde el inicio -->
<script>
	document.documentElement.classList.add("hud-lock");
</script>
<style>	
/* ===== BLOQUE HUD LOCK ===== */
.hud-lock, body.hud-lock {
	overflow: hidden !important;
    height: 100vh !important;
    touch-action: none;
}
.hud-lock [class*="xoo-wsc"], .hud-lock [id*="xoo-wsc"] {
	display: none !important;
}	
			
/* Overlay negro-rojo más oscuro */
#apocalipsis-compuerta{
  position:fixed; top:-10%; left:-10%;
  width:120vw; height:120vh;
  background: linear-gradient(135deg, #000000, #3a0000); /* más negro */
  overflow:hidden; display:flex; align-items:center; justify-content:center;
  z-index:999999; transition: opacity 1s ease-out; opacity:1;
}
#apocalipsis-compuerta.fade-out-gradual{ opacity:0; pointer-events:none; }
	
/* Humo oscuro */
.smoke{
  position:absolute; width:120%; height:120%;
  background:radial-gradient(circle, rgba(50,0,0,0.35), transparent 70%);
  top:-10%; left:-10%; animation:smokeMove 6s linear infinite;
}
@keyframes smokeMove{
  0%{transform:translate(0,0) rotate(0deg); opacity:0.5;}
  50%{transform:translate(60px,-30px) rotate(45deg); opacity:0.3;}
  100%{transform:translate(-60px,50px) rotate(-45deg); opacity:0.5;}
}
				
/* Chispas rojas y amarillas */
.spark{
  position:absolute; width:4px; height:4px;
  background: radial-gradient(circle, #ff0000, #ffea00);
  border-radius:50%; opacity:0.9; animation:sparkMove linear infinite;
  box-shadow:0 0 8px #ff0000, 0 0 12px #ffea00;
}
@keyframes sparkMove{
  0%{transform:translate(0,0) scale(1); opacity:0.9;}
  50%{transform:translate(30px,-60px) scale(2); opacity:0.6;}
  100%{transform:translate(-30px,60px) scale(1); opacity:0;}
}	
			
/* Logo central */
.ultra-circle{
  width:280px; height:280px; border-radius:50%;
  background:radial-gradient(circle, #ff0000, #330000);
  box-shadow:0 0 120px #ff0000, 0 0 200px #330000;
  display:flex; align-items:center; justify-content:center;
  z-index:2; animation:pulseUltra 1s infinite; position:relative;
  transition: opacity 1s ease-out;
}
.ultra-circle img{
  width:70%; opacity:0; transform:scale(0.3) rotate(-90deg);
  filter:drop-shadow(0 0 20px #ff0000); transition:all .7s ease;
}
.ultra-circle.show-logo img{ opacity:1; transform:scale(1) rotate(0deg); }

@keyframes pulseUltra{
  0%{transform:scale(1); opacity:.9;}
  25%{transform:scale(1.5); opacity:1;}
  50%{transform:scale(1.2); opacity:.9;}
  75%{transform:scale(1.6); opacity:1;}
  100%{transform:scale(1); opacity:.9;}
}
	
/* Ondas expansivas rojas */
.wave{
  position:absolute; border-radius:50%; pointer-events:none;
  left:50%; top:50%; transform:translate(-50%,-50%);
  background:radial-gradient(circle, rgba(255,0,0,0.3) 0%, rgba(100,0,0,0.1) 60%, transparent 100%);
  opacity:0; animation:waveExpandEpic 2s ease-out forwards;
}
@keyframes waveExpandEpic{
  0%{width:0; height:0; opacity:.9; filter:blur(2px);}
  50%{opacity:.6; filter:blur(4px);}
  100%{width:2200px; height:2200px; opacity:0; filter:blur(20px);}
}
	
/* Explosión final */
#apocalipsis-compuerta.final-explosion .ultra-circle{
  animation:explodeUltra 1.2s forwards;
}
@keyframes explodeUltra{
  0%{transform:scale(1) rotate(0); opacity:1; filter:blur(0);}
  50%{transform:scale(5) rotate(180deg); opacity:.6; filter:blur(10px);}
  100%{transform:scale(10) rotate(360deg); opacity:0; filter:blur(40px);}
}	
	
/* Temblor de pantalla */
.shake-massive{ animation:shakeScreen .6s linear; }
@keyframes shakeScreen{
  0%,100%{transform:translate(0,0) rotate(0);}
  10%{transform:translate(-25px,-10px) rotate(-2deg);}
  20%{transform:translate(20px,15px) rotate(2deg);}
  30%{transform:translate(-30px,20px) rotate(-3deg);}
  40%{transform:translate(25px,-15px) rotate(2deg);}
  50%{transform:translate(-20px,10px) rotate(-2deg);}
  60%{transform:translate(20px,-20px) rotate(3deg);}
  70%{transform:translate(-15px,15px) rotate(-2deg);}
  80%{transform:translate(10px,-10px) rotate(2deg);}
  90%{transform:translate(-5px,5px) rotate(-1deg);}
}

/* Flash final rojo */
.flash{
  position:absolute; inset:0;
  background:radial-gradient(circle, rgba(255,0,0,0.95), rgba(150,0,0,0.6), transparent 80%);
  opacity:0; z-index:5; animation:flashEpic .45s ease-out forwards;
}
@keyframes flashEpic{ 0%{opacity:0;} 20%{opacity:1;} 60%{opacity:.6;} 100%{opacity:0;} }	
	
/* Full flash rojo */
.full-flash{
  position:fixed; inset:0;
  background: radial-gradient(circle at center, rgba(255,0,0,1) 0%, rgba(150,0,0,0.8) 25%, rgba(100,0,0,0.6) 50%, rgba(50,0,0,0.4) 75%, transparent 100%);
  filter: blur(12px) brightness(1.8) contrast(1.2);
  opacity:0; z-index:9999999; animation:fullFlashAnim 1.8s forwards;
}
@keyframes fullFlashAnim{ 0%{opacity:0;} 10%{opacity:1; filter:blur(6px) brightness(2);} 40%{opacity:1; filter:blur(14px) brightness(1.8);} 80%{opacity:0.9; filter:blur(20px) brightness(1.4);} 100%{opacity:0;} }	
	
/* Lens flare rojo */
.lens-flare{
  position:fixed; inset:0; background:
  linear-gradient(90deg, transparent, rgba(255,0,0,0.85), transparent),
  linear-gradient(0deg, transparent, rgba(100,0,0,0.55), transparent);
  mix-blend-mode:screen; opacity:0; z-index:99999999; animation:lensFlareAnim 1.2s forwards;
}
@keyframes lensFlareAnim{ 0%{opacity:0;} 20%{opacity:1;} 80%{opacity:0.6;} 100%{opacity:0;} }

/* Partículas tipo llamas */
.fire-particles{
  position:fixed; inset:0; pointer-events:none; z-index:9999997;
}
.fire-particles div{
  position:absolute; width:6px; height:6px;
  background: radial-gradient(circle, rgba(255,100,0,0.9), rgba(0,0,0,0.1));
  border-radius:50%; opacity:0; animation:fireParticleAnim linear forwards;
}
@keyframes fireParticleAnim{
  0%{opacity:0; transform:scale(0) translate(0,0);}
  30%{opacity:1; transform:scale(1) translate(var(--x), var(--y));}
  100%{opacity:0; transform:scale(2) translate(calc(var(--x)*2), calc(var(--y)*2));}
}	
	
/* Burn particles */
.burn-particles{
  position:fixed; inset:0;
  background:radial-gradient(circle at center, rgba(255,0,0,0.14) 0%, transparent 70%);
  pointer-events:none; opacity:0; z-index:9999998; animation:burnAnim 1.5s forwards;
}
@keyframes burnAnim{ 0%{opacity:0;} 20%{opacity:0.5;} 80%{opacity:0.3;} 100%{opacity:0;} }	
		
/* Shockwave roja */
.shockwave{
  position:absolute; border:5px solid rgba(255,0,0,0.9); border-radius:50%;
  width:100px; height:100px; top:50%; left:50%; transform:translate(-50%,-50%) scale(0.2);
  opacity:.8; z-index:4; animation:shockwaveEpic .8s ease-out forwards;
}
@keyframes shockwaveEpic{ 0%{transform:translate(-50%,-50%) scale(.2); opacity:.9;} 100%{transform:translate(-50%,-50%) scale(10); opacity:0;} }	
		
/* ===== BOTÓN DE SONIDO ===== */
#soundToggle {
    position: fixed;
    bottom: 25px;
    right: 25px;
    width: 50px;
    height: 50px;
    background: rgba(0,0,0,0.6);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000000;
    cursor: pointer;
    transition: box-shadow 0.3s, transform 0.2s;
}
#soundToggle:hover { transform: scale(1.1);}
#soundToggle svg { width: 24px; height: 24px; fill: #fff; display: block; }
#soundToggle svg path { transform: translate(-2px, 0); }
#soundToggle line { stroke-width: 3; stroke: red; transition: opacity 0.3s; }				
</style>

<div id="apocalipsis-compuerta">
  <div class="smoke"></div>
  <?php for($i=0;$i<70;$i++): ?>
    <div class="spark" style="top:<?= rand(0,100) ?>%; left:<?= rand(0,100) ?>%; animation-duration: <?= rand(1,4) ?>s;"></div>
  <?php endfor; ?>
  <div class="ultra-circle">
    <img src="/images/logo.webp" alt="Logo">
  </div>
  <audio id="boomSound" src="/sounds/explosion-two"></audio>	
  <audio id="finalBoomSound" src="/sounds/explosion-three"></audio>
</div>

<!-- Botón de sonido -->
<div id="soundToggle">
    <svg viewBox="0 0 24 24" preserveAspectRatio="xMidYMid meet">
        <path d="M9 9v6h4l5 5V4l-5 5H9z"/>
        <line id="muteLine" x1="1" y1="1" x2="23" y2="23"/>
    </svg>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
  const compuerta = document.getElementById("apocalipsis-compuerta");
  const ultra     = document.querySelector(".ultra-circle");
  const boom      = document.getElementById("boomSound");
  const finalBoom = document.getElementById("finalBoomSound");
  const body = document.body;
  body.classList.add("hud-lock");	
	
  // BOTÓN DE SONIDO
  const soundToggle = document.getElementById("soundToggle");
  const muteLine = document.getElementById("muteLine");	
  let soundEnabled = false;	
	
  soundToggle.addEventListener("click", () => {
	  soundEnabled = !soundEnabled;
	  muteLine.style.opacity = soundEnabled ? 0 : 1;	  
	  if (!soundEnabled){			
	  		// Pausar y reiniciar todos los audios del DOM
			document.querySelectorAll('audio').forEach(audio => {
				audio.pause();
            	audio.currentTime = 0;
			});						        	
	  }
   });  	
   function playSound(audio){
   		if(soundEnabled){ audio.currentTime = 0; audio.play().catch(()=>{}); }
   }   
	
  const limpiarDespuesDelAudio = () => {
    compuerta.classList.add("fade-out-gradual");
    setTimeout(() => { compuerta.remove(); document.body.style.overflow = ''; }, 1000);
  };

  document.body.style.overflow = 'hidden';
  setTimeout(() => ultra.classList.add("show-logo"), 400);

  [1200,2000,2800].forEach(t => {
    setTimeout(() => {
      compuerta.classList.add("shake-massive");
      setTimeout(()=>compuerta.classList.remove("shake-massive"), 600);
      const flash = document.createElement("div"); flash.className = "flash"; compuerta.appendChild(flash);
      const shock = document.createElement("div"); shock.className = "shockwave"; compuerta.appendChild(shock);
	  playSound(boom);		
    }, t);
  });

  // Explosión final estilo “galería”
  setTimeout(() => {
    compuerta.classList.add("final-explosion");

    document.querySelectorAll("#apocalipsis-compuerta .spark, #apocalipsis-compuerta .smoke").forEach(el => el.remove());

    // Flash final
    const fullFlash = document.createElement("div"); fullFlash.className = "full-flash"; document.body.appendChild(fullFlash); setTimeout(() => fullFlash.remove(), 1800);

    // Lens flare
    const flare = document.createElement("div"); flare.className = "lens-flare"; document.body.appendChild(flare); setTimeout(() => flare.remove(), 1200);

    // Burn particles
    const burn = document.createElement("div"); burn.className = "burn-particles"; document.body.appendChild(burn); setTimeout(() => burn.remove(), 1500);

    // Shockwaves
    for (let i = 0; i < 3; i++) {
      const shock = document.createElement("div");
      shock.className = "shockwave";
      shock.style.animationDelay = `${i * 0.15}s`;
      compuerta.appendChild(shock);
      setTimeout(() => shock.remove(), 1000 + i*150);
    }

    // Logo explota
    ultra.style.transition = 'opacity 1s ease-out, transform 1.2s ease-out';
    ultra.style.opacity = 0;
    ultra.style.transform = 'scale(10) rotate(360deg)';
	  	
    // Sonido final
    // try { finalBoom.currentTime = 0; finalBoom.play().catch(()=>{}); } catch(e){}
	playSound(finalBoom);
	  
    // Fondo transparente    
    // compuerta.style.transition = 'opacity 1s ease-out';
    // compuerta.style.backgroundColor = 'transparent';    	 
	compuerta.style.opacity = '0'; // Remover la pantalla violeta inmediatamente
	compuerta.style.pointerEvents = 'none'; // Desactivar interacciones
	  
    setTimeout(() => { 		
		limpiarDespuesDelAudio(); 
		var loadingSound = document.getElementById('loading-sound');
		if (loadingSound) {						
			playSound(loadingSound);						
		}		
		setTimeout(()=> {
			window.location.href = "/intro";
		}, 500);
	}, 3000);	  	  
  }, 3800);
});
</script>
<?php } 

function insertar_animacion_apocalipsis_NOD_CUATRO(){ ?>
<script>
	document.documentElement.classList.add("hud-lock");
</script>
<style>
/* HUD lock */
.hud-lock, body.hud-lock {
	overflow: hidden !important;
    height: 100vh !important;
    touch-action: none;
}
.hud-lock [class*="xoo-wsc"], .hud-lock [id*="xoo-wsc"] {
	display: none !important;
}		

/* ===== CONTENEDOR ===== */
#apocalipsis-compuerta {
    position: fixed; top:0; left:0;
    width:100vw; height:100vh;
    /*background: radial-gradient(circle at center, #000000, #990000 90%);*/	
	background: radial-gradient(circle at center, #000000, #330000 85%);		
    overflow: hidden; display:flex; align-items:center; justify-content:center;
    z-index: 999999;
    transition: opacity 2s ease-out, transform 2s ease-out;
    opacity: 1;
}
.shake-wrapper {
    width:100%; height:100%;
    display:flex; align-items:center; justify-content:center;
    position:relative;
}
	
.shake-wrapper.shake-strong { animation: shakeStrong 0.2s infinite; }
@keyframes shakeStrong {
  0%,100%{transform:translate(0,0) rotate(0);}
  25%{transform:translate(-20px,-15px) rotate(-2deg);}
  50%{transform:translate(20px,15px) rotate(2deg);}
  75%{transform:translate(-15px,10px) rotate(-1deg);}
}

/* Humo nuclear */
.smoke {
    position:absolute; width:140%; height:140%;
    background: radial-gradient(circle, rgba(80,0,0,0.5), transparent 70%);
    top:-20%; left:-20%;
    animation: smokeMove 8s linear infinite;
}
@keyframes smokeMove {
    0% {transform:translate(0,0) rotate(0deg); opacity:0.6;}
    50% {transform:translate(80px,-40px) rotate(45deg); opacity:0.3;}
    100% {transform:translate(-80px,60px) rotate(-45deg); opacity:0.6;}
}

/* Sparks */
.spark { 
    position:absolute; width:5px; height:5px; 
    background: radial-gradient(circle, #ff0000, #990000);
    border-radius:50%; opacity:0.9; animation:sparkMove linear infinite;
    box-shadow:0 0 8px #ff0000, 0 0 12px #990000;
}
@keyframes sparkMove { 
    0%{transform:translate(0,0) scale(1) rotate(0deg); opacity:0.9;}
    50%{transform:translate(40px,-80px) scale(2) rotate(180deg); opacity:0.6;}
    100%{transform:translate(-40px,80px) scale(1) rotate(360deg); opacity:0;}
}

/* Círculo */
.ultra-circle {
    width:280px; height:280px; border-radius:50%;
    background: radial-gradient(circle, #ff0000, #660000);
    box-shadow:0 0 120px #ff0000,0 0 200px #660000;
    display:flex; align-items:center; justify-content:center;
    z-index:2; animation:pulseUltra 1s infinite; position:relative;
}
.ultra-circle img {
    width:70%; opacity:0; transform:scale(0.3) rotate(-90deg);
    filter: drop-shadow(0 0 25px #ff0000);
    transition: all 0.7s ease;
}
.ultra-circle.show-logo img { opacity:1; transform:scale(1) rotate(0deg); }
@keyframes pulseUltra {
    0%{transform:scale(1); opacity:0.9;}
    25%{transform:scale(1.6); opacity:1;}
    50%{transform:scale(1.3); opacity:0.9;}
    75%{transform:scale(1.7); opacity:1;}
    100%{transform:scale(1); opacity:0.9;}
}

/* Explosión nuclear */
/*
.explosion-nuclear {
    position:absolute; left:50%; top:50%;
    width:400px; height:400px;
    background: radial-gradient(circle, #ff00ff, #ff0000);
    border-radius:50%; transform: translate(-50%,-50%);
    pointer-events:none; opacity:0.9;
    z-index:9999999; animation:explosionFinal 2s ease-out forwards;
}
*/
	
@keyframes explosionFinal {
    0% {transform:scale(1); opacity:1; filter:blur(2px);}
    100% {transform:scale(120); opacity:0; filter:blur(120px);}
}

/* Onda expansiva */
.wave {
  position: absolute;
  left: 50%; top: 50%;
  transform: translate(-50%, -50%);
  border-radius: 50%;
  border: 6px solid rgba(255,0,0,0.6);
  background: rgba(255,0,0,0.2);
  pointer-events: none;
  animation: waveExpandEpic 2.5s ease-out forwards;
  z-index: 9999;
}
@keyframes waveExpandEpic {
  0% { width:0; height:0; opacity:1; filter:blur(2px);}
  50% { width:2500px; height:2500px; opacity:0.8; filter:blur(40px);}
  100% { width:4000px; height:4000px; opacity:0; filter:blur(80px);}
}
	
/* Rayos */
.rayo {
    position:absolute; width:4px; height:1000px;
    background: linear-gradient(to bottom, #fff, rgba(255,0,0,0));
    left:50%; top:50%;
    transform-origin: top center;
    opacity:0.8; animation: rayoFade 1.2s ease-out forwards;
}
@keyframes rayoFade { 0% {opacity:1;} 100% {opacity:0;} }

/* Flash total */
.flash-total {
    position: absolute; top:0; left:0; width:100%; height:100%;
    background: white; opacity:0;
    animation: flashTotal 1.2s forwards;
    z-index: 10000000;
}
@keyframes flashTotal {
    0%{opacity:0;}
    20%{opacity:1;}
    100%{opacity:0;}
}

/* Fracturas de vidrio */
.cracks {
    position: absolute;
    top: 0; left: 0;
    width: 100%; height: 100%;
    /*background: url("/images/crackfinal.png") center/cover no-repeat;*/		
	background: url("/images/crackfinal_two.png") center/cover no-repeat;
    opacity: 0;
    z-index: 10000001;
    animation: cracksAppear 1.2s ease-out forwards;    
    pointer-events: none; /* no bloquea clicks */
}

@keyframes cracksAppear {
    0%   { opacity: 0; transform: scale(0.9) rotate(0deg); filter: blur(8px); }
    60%  { opacity: 1; transform: scale(1.05) rotate(1deg); filter: blur(0); }
    100% { opacity: 1; transform: scale(1) rotate(0deg); }
}

/* Glow final */
.final-glow {
  position: absolute; top:0; left:0; width:100%; height:100%;
  background: radial-gradient(circle at center, rgba(255,255,255,0.95), transparent 80%);
  opacity:0; animation: glowFade 3s forwards;
  z-index: 99999;
}
@keyframes glowFade {
  0% {opacity: 0;}
  50% {opacity: 1;}
  100% {opacity: 0;}
}

/* Glitch */
.glitch {
    position: absolute; top:0; left:0; width:100%; height:100%;
    background: repeating-linear-gradient(0deg, rgba(255,0,0,0.2), transparent 2px);
    animation: glitchAnim 0.5s infinite;
    z-index: 999999;
}
@keyframes glitchAnim {
    0%{transform:translate(0,0);}
    25%{transform:translate(-10px,5px);}
    50%{transform:translate(5px,-5px);}
    75%{transform:translate(-5px,10px);}
    100%{transform:translate(0,0);}
}
	
#apocalipsis-compuerta.fade-out-gradual { opacity: 0; transform: scale(1.12); }
		
/* ===== BOTÓN DE SONIDO ===== */
#soundToggle {
    position: fixed;
    bottom: 25px;
    right: 25px;
    width: 50px;
    height: 50px;
    background: rgba(0,0,0,0.6);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000000;
    cursor: pointer;
    transition: box-shadow 0.3s, transform 0.2s;
}
	
#soundToggle:hover { transform: scale(1.1);}
#soundToggle svg { width: 24px; height: 24px; fill: #fff; display: block; }
#soundToggle svg path { transform: translate(-2px, 0); }
#soundToggle line { stroke-width: 3; stroke: red; transition: opacity 0.3s; }			
</style>

<div id="apocalipsis-compuerta">
    <div class="shake-wrapper">
        <div class="smoke"></div>
        <?php for($i=0;$i<120;$i++): ?>
            <div class="spark" style="top:<?= rand(0,100) ?>%; left:<?= rand(0,100) ?>%; animation-duration: <?= rand(1,5) ?>s;"></div>
        <?php endfor; ?>
        <div class="ultra-circle">
            <img src="/images/logo.webp" alt="Logo">
        </div>
    </div>
    <audio id="boomSound" src="/sounds/explosion-two"></audio>
	<audio id="glassBreaking" src="/sounds/glass-breaking"></audio>
</div>

<div id="soundToggle">
    <svg viewBox="0 0 24 24" preserveAspectRatio="xMidYMid meet">
        <path d="M9 9v6h4l5 5V4l-5 5H9z"/>
        <line id="muteLine" x1="1" y1="1" x2="23" y2="23"/>
    </svg>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const compuerta = document.getElementById("apocalipsis-compuerta");
    const wrapper = compuerta.querySelector(".shake-wrapper");
    const boom = document.getElementById("boomSound");
	const glassBreaking = document.getElementById("glassBreaking");
    const ultraCircle = document.querySelector(".ultra-circle");	
    const body = document.body;
    body.classList.add("hud-lock");
    document.body.style.overflow = 'hidden';
	
	let soundEnabled = false;
	
	setTimeout(() => {
		const soundToggle = document.getElementById("soundToggle");
		const muteLine = document.getElementById("muteLine");
		soundToggle.addEventListener("click", () => {
			soundEnabled = !soundEnabled;
			muteLine.style.opacity = soundEnabled ? 0 : 1;
			if(!soundEnabled){			
				// Pausar y reiniciar todos los audios del DOM
				document.querySelectorAll('audio').forEach(audio => {
					audio.pause();
					audio.currentTime = 0;
				});						        	
			}
		});
	}, 50); // espera 50ms para garantizar que el DOM esté listo
	
    function playSound(audio){
        if(soundEnabled){
            audio.currentTime = 0;
            audio.play().catch(()=>{});
        }
    }

    setTimeout(() => ultraCircle.classList.add("show-logo"), 400);

    [1200,1700,2200,2700].forEach(t => {
        setTimeout(() => {
            const wave = document.createElement("div");
            wave.className = "wave";
            compuerta.appendChild(wave);
            setTimeout(()=>wave.remove(), 2500);
            playSound(boom);
            for(let i=0;i<12;i++){
                const rayo=document.createElement("div");
                rayo.className="rayo";
                rayo.style.transform=`translate(-50%,-50%) rotate(${i*30}deg)`;
                compuerta.appendChild(rayo);
                setTimeout(()=>rayo.remove(), 1200);
            }
        }, t);
    });

    // Explosión nuclear + fracturas
    setTimeout(() => {
		
		/*
        const explosion = document.createElement("div");
        explosion.className = "explosion-nuclear";
        compuerta.appendChild(explosion);
        playSound(boom);
		*/

        const flash = document.createElement("div");
        flash.className = "flash-total";
        compuerta.appendChild(flash);
        setTimeout(()=>flash.remove(),1200);

        // fracturas de pantalla
        const cracks = document.createElement("div");
        cracks.className = "cracks";
        compuerta.appendChild(cracks);		
		playSound(glassBreaking);
		
        wrapper.classList.add("shake-strong");
    }, 3200);

    // Glow + glitch
    setTimeout(() => {
        const glow = document.createElement("div");
        glow.className = "final-glow";
        compuerta.appendChild(glow);

        const glitch = document.createElement("div");
        glitch.className = "glitch";
        compuerta.appendChild(glitch);
        setTimeout(()=>glitch.remove(), 2000);
    }, 5200);

    // Fade out
    setTimeout(() => {
        compuerta.classList.add("fade-out-gradual");
        wrapper.classList.remove("shake-strong");
        ultraCircle.style.opacity = 0;
        document.querySelectorAll(".spark, .smoke, .wave, .rayo").forEach(el => el.remove());
        document.body.style.overflow = '';
        setTimeout(()=> compuerta.remove(), 1500);
		
		// Iniciamos sonido de la pantalla de carga
		var loadingSound = document.getElementById('loading-sound');
		if (loadingSound) {	
			playSound(loadingSound);						
		}		
		setTimeout(()=> {
			window.location.href = "/intro";
		}, 500);
    }, 7200);
});
</script>
<?php }  