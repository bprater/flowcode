<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TD Simulator - Rocket Corrected & Sparkles V4</title>
    <style>
        body { font-family: sans-serif; display: flex; flex-direction: column; align-items: center; margin: 0; background-color: #e0e0e0; padding-bottom: 70px; }
        .pause-button-style { padding: 10px 20px; font-size: 16px; cursor: pointer; background-color: #4CAF50; color: white; border: none; border-radius: 5px; margin: 0 5px; }
        .pause-button-style.paused { background-color: #f44336; }
        #bottom-pause-bar { position: fixed; bottom: 0; left: 0; width: 100%; background-color: rgba(200, 200, 200, 0.9); padding: 10px 0; display: flex; justify-content: center; z-index: 200; box-shadow: 0 -2px 5px rgba(0,0,0,0.2); }

        #game-container { position: relative; width: 800px; height: 450px; background-color: #5DAD36; background-image: linear-gradient(to bottom, #76C84D 0%, #5DAD36 60%, #488A29 100%); border: 2px solid #333; overflow: hidden; margin-top:10px; }
        #path { position: absolute; bottom: 30px; left: 0; width: 100%; height: 60px; background-color: #b08d57; border-top: 1px dashed #8a6d40; border-bottom: 1px dashed #8a6d40; }

        .tower-base { position: absolute; width: 45px; height: 45px; border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 3px 6px rgba(0,0,0,0.4), inset 0 2px 4px rgba(0,0,0,0.2); top: 50px; }
        #tower-rocket-base { background: linear-gradient(135deg, #A0522D 0%, #8B4513 100%); left: 100px; }
        #tower-rocket-base::before { content: ''; position: absolute; width: 8px; height: 8px; background: #696969; border-radius: 50%; box-shadow: 15px 0 #696969, -15px 0 #696969, 0 15px #696969, 0 -15px #696969; }
        #tower-ice-base { background: linear-gradient(180deg, #C0E0FF 0%, #A0C0FF 100%); left: 377.5px; box-shadow: 0 0 15px 5px rgba(173, 216, 230, 0.6), inset 0 0 10px rgba(255,255,255,0.5); border: 2px solid #ADD8E6; }
        #tower-ice-base::before, #tower-ice-base::after { content: ''; position: absolute; width: 0; height: 0; border-left: 8px solid transparent; border-right: 8px solid transparent; border-bottom: 15px solid rgba(200, 230, 255, 0.7); opacity: 0.8; }
        #tower-ice-base::before { transform: rotate(30deg) translate(18px, -15px); }
        #tower-ice-base::after { transform: rotate(-40deg) translate(-15px, -18px) scaleX(-1); }
        .frost-mote { position: absolute; width: 3px; height: 3px; background-color: rgba(220, 240, 255, 0.7); border-radius: 50%; pointer-events: none; animation: floatMote 4s linear infinite; opacity: 0; }
        @keyframes floatMote { 0% { transform: translate(0,0) scale(0.5); opacity: 0; } 25% { opacity: 0.7; } 75% { opacity: 0.7; } 100% { transform: translate(calc(var(--mote-dx) * 1px), calc(var(--mote-dy) * 1px)) scale(1.2); opacity: 0; } }
        
        #tower-lightning-base { background: linear-gradient(135deg, #404058 0%, #202030 100%); left: 655px; border: 2px solid #606070; box-shadow: 0 3px 6px rgba(0,0,0,0.5), inset 0 2px 4px rgba(0,0,0,0.3), 0 0 8px #7DF9FF; animation: subtle-pulse-base-lightning 4s infinite alternate ease-in-out; }
        #tower-lightning-base::after, #tower-lightning-base::before { content: ''; position: absolute; width: 5px; height: 110%; background: linear-gradient(to bottom, rgba(125, 249, 255, 0.2), rgba(0, 191, 255, 0.4)); border-radius: 3px; box-shadow: 0 0 2px #00BFFF, 0 0 4px #7DF9FF; opacity: 0.6; }
        #tower-lightning-base::after { transform: rotate(35deg); }
        #tower-lightning-base::before { transform: rotate(-35deg); }
        @keyframes subtle-pulse-base-lightning { 0% { box-shadow: 0 3px 6px rgba(0,0,0,0.5), inset 0 2px 4px rgba(0,0,0,0.3), 0 0 8px #7DF9FF; } 100% { box-shadow: 0 3px 6px rgba(0,0,0,0.5), inset 0 2px 4px rgba(0,0,0,0.3), 0 0 20px #00FFFF; } }

        .turret-pivot { position: absolute; width: 100%; height: 100%; top: 0; left: 0; transform-origin: center center; }
        .turret-model { position: absolute; border: 1px solid #333; transform-origin: 50% 90%; left: 50%; bottom: 45%; transform: translateX(-50%); box-shadow: 0 1px 3px rgba(0,0,0,0.3); }
        .turret-glow-indicator { position: absolute; top: 0; left: 0; right: 0; bottom: 0; border-radius: inherit; box-shadow: 0 0 0px 0px transparent; opacity: 0; transition: opacity 0.1s, box-shadow 0.1s; pointer-events: none; z-index: 1; }
        .turret-rocket { width: 20px; height: 32px; background: linear-gradient(#A9A9A9, #696969); border-radius: 5px 5px 2px 2px; }
        .turret-rocket::before { content: ''; position: absolute; top: -3px; left: 2px; width: 16px; height: 5px; background: #505050; border-radius: 2px; }
        .turret-ice { width: 16px; height: 30px; background: transparent; border: none; box-shadow: none; }
        .turret-ice::before, .turret-ice::after { content: ''; position: absolute; background-color: rgba(173, 216, 230, 0.6); border: 1px solid rgba(200, 240, 255, 0.8); box-shadow: 0 0 5px rgba(220, 250, 255, 0.7); }
        .turret-ice::before { width: 8px; height: 100%; top: 0; left: calc(50% - 4px); border-radius: 3px 3px 0 0; }
        .turret-ice::after { width: 100%; height: 6px; top: 30%; left: 0; border-radius: 2px; transform: rotate(-10deg); }
        .turret-ice.firing::after { content: ''; position: absolute; top: -10px; left: 50%; transform: translateX(-50%); width: 24px; height: 24px; background: radial-gradient(circle, rgba(224,255,255,0.8) 10%, rgba(175,238,238,0.4) 40%, transparent 70%); border-radius: 50%; opacity: 1; animation: quick-fade 0.2s forwards; }
        
        .turret-lightning-barrel { width: 18px; height: 28px; background: linear-gradient(#687A8F, #394653); border-radius: 4px 4px 1px 1px; border: 1px solid #2A343F; box-shadow: 0 1px 2px rgba(0,0,0,0.4); }
        .turret-lightning-barrel::before { content: ''; position: absolute; top: 5px; left: 50%; transform: translateX(-50%); width: 100%; height: 5px; background: #7DF9FF; border-radius: 2px; box-shadow: 0 0 3px #7DF9FF, inset 0 0 2px rgba(255,255,255,0.5); opacity: 0.7; }
        .turret-lightning-barrel::after { content: ''; position: absolute; top: -3px; left: calc(50% - 3px); width: 6px; height: 6px; background: #00FFFF; border-radius: 50%; box-shadow: 0 0 5px #00FFFF, 0 0 10px #7DF9FF; animation: lightning-tip-pulse 1s infinite alternate; }
        @keyframes lightning-tip-pulse { 0% { opacity: 0.6; transform: scale(0.8); } 100% { opacity: 1; transform: scale(1.2); } }

        .enemy { position: absolute; width: 30px; height: 30px; background-color: #2ecc71; border: 1px solid #27ae60; border-radius: 5px; display: flex; flex-direction: column; align-items: center; justify-content: center; box-sizing: border-box; font-size: 10px; color: white; transition: filter 0.1s; }
        .enemy.hit-rocket { filter: brightness(1.8) sepia(0.5) hue-rotate(-20deg); }
        .enemy.hit-ice { filter: brightness(1.5) saturate(2) hue-rotate(180deg); }
        .enemy.hit-lightning { filter: brightness(2.5) saturate(0.5); }
        .enemy-health-bar { width: 90%; height: 5px; background-color: #e74c3c; border: 1px solid #c0392b; margin-top: 2px; }
        .enemy-health-fill { width: 100%; height: 100%; background-color: #2ecc71; }

        .projectile { position: absolute; box-sizing: border-box; }
        
        /* --- ROCKET CSS V4 --- */
        .rocket {
            width: 20px; /* Overall container width */
            height: 10px; /* Overall container height */
            /* background-color: rgba(0, 255, 0, 0.2); /* Debug: shows the main div */
            transform-origin: 70% 50%; /* Pivot point: 70% from left = towards the pointy nose (right) */
            position: relative;
        }
        .rocket-body {
            position: absolute;
            width: 16px;   /* Length of the body */
            height: 8px;   /* Thickness of the body */
            background-color: #606060;
            border: 1px solid #404040;
            /* Flat tail (left), Pointy nose (right) */
            /* border-radius: horizontal-radius / vertical-radius */
            /* For left side (tail): smaller horizontal radius (flatter), full vertical radius */
            /* For right side (nose): larger horizontal radius (pointier), full vertical radius */
            border-radius: 2px 8px 8px 2px / 20% 50% 50% 20%;
            top: 50%;
            left: 0; /* Body starts at the left of the .rocket container */
            transform: translateY(-50%);
        }
        .rocket-fin {
            position: absolute;
            background-color: #B22222;
            border: 1px solid #800000;
            width: 4px;  /* Fin thickness */
            height: 9px; /* Fin protrusion */
            left: 1px; /* Positioned near the tail (left end of rocket-body) */
            z-index: -1; /* Optional: place behind body */
        }
        .rocket-fin-top {
            top: calc(50% - 8px); /* Align with body */
            transform: skewY(35deg); /* Skew opposite for top fin */
        }
        .rocket-fin-bottom {
            bottom: calc(50% - 8px); /* Align with body */
            transform: skewY(-35deg); /* Skew opposite for bottom fin */
        }
        .rocket-flame {
            position: absolute;
            left: -6px; /* Positioned at the very tail (left of rocket-body) */
            top: 50%;
            transform: translateY(-50%); /* Flame visual itself points right by default */
            width: 12px;
            height: 12px;
            background: radial-gradient(circle, #FFFF8C 10%, #FFD700 30%, orangered 60%, transparent 80%);
            border-radius: 50%;
            animation: flicker-strong-rocket 0.08s infinite alternate;
        }
        @keyframes flicker-strong-rocket {
            0% { transform: scale(0.7) translateY(-50%); opacity: 0.8; filter: brightness(1.2); }
            100% { transform: scale(1.3) translateY(-50%); opacity: 1; filter: brightness(1.5); }
        }
        /* --- END ROCKET CSS V4 --- */
        
        .rocket-puff { position: absolute; width: 10px; height: 10px; background: radial-gradient(circle, rgba(220,220,220,0.6) 20%, rgba(180,180,180,0.3) 50%, transparent 70%); border-radius: 50%; pointer-events: none; animation: puff-trail-anim 0.8s ease-out forwards; transform: translate(-50%, -50%); }
        @keyframes puff-trail-anim { 0% { transform: translate(-50%, -50%) scale(0.5); opacity: 0.7; } 100% { transform: translate(-50%, -50%) scale(2.5); opacity: 0; } }

        .ice-crystal-projectile { width: 14px; height: 14px; position: relative; animation: spin 0.7s linear infinite; }
        .ice-crystal-projectile::before, .ice-crystal-projectile::after { content: ''; position: absolute; background-color: #afeeee; box-shadow: 0 0 3px #fff, 0 0 5px #add8e6; border-radius: 1px; }
        .ice-crystal-projectile::before { width: 100%; height: 20%; top: 40%; left: 0; }
        .ice-crystal-projectile::after { width: 20%; height: 100%; top: 0; left: 40%; }
        .ice-crystal-projectile > div { position: absolute; width: 100%; height: 20%; top: 40%; left: 0; background-color: #afeeee; box-shadow: 0 0 3px #fff, 0 0 5px #add8e6; border-radius: 1px; transform: rotate(45deg); }
        @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }

        .visual-effect { position: absolute; pointer-events: none; z-index: 100; }
        .explosion { width: 70px; height: 70px; background: radial-gradient(circle, #fff700 0%, #ff8c00 20%, #ff4500 50%, #400000 70%, transparent 80%); border-radius: 50%; animation: explosion-anim 0.5s forwards; transform: translate(-50%, -50%); }
        @keyframes explosion-anim { 0% { transform: translate(-50%, -50%) scale(0.2); opacity: 1; } 100% { transform: translate(-50%, -50%) scale(1.8); opacity: 0; } }
        .ice-shatter { width: 50px; height: 50px; transform: translate(-50%, -50%); animation: ice-shatter-anim 0.4s forwards; }
        .ice-splash-effect { width: var(--splash-radius, 60px); height: var(--splash-radius, 60px); border: 2px solid rgba(173, 216, 230, 0.5); background-color: rgba(200, 240, 255, 0.2); border-radius: 50%; animation: splash-anim 0.3s ease-out forwards; transform: translate(-50%, -50%); }
        @keyframes splash-anim { 0% { transform: translate(-50%,-50%) scale(0.1); opacity: 0.7; } 100% { transform: translate(-50%,-50%) scale(1); opacity: 0; } }
        @keyframes ice-shatter-anim { 0% { opacity: 1; transform: translate(-50%, -50%) scale(0.5); } 100% { opacity: 0; transform: translate(-50%, -50%) scale(1.5) rotate(120deg); } }
        .lightning-strike-segment, .lightning-tendril-segment { position: absolute; background-color: #ADD8E6; height: 2px; transform-origin: 0 50%; box-shadow: 0 0 4px #7DF9FF, 0 0 7px white; animation: lightning-flash-quick 0.15s forwards; }
        .lightning-tendril-segment { height: 1px; opacity: 0.7; }
        .lightning-impact-flash { width: 45px; height: 45px; background: radial-gradient(circle, white 30%, #7DF9FF 70%, transparent 85%); border-radius: 50%; animation: explosion-anim 0.2s forwards; transform: translate(-50%, -50%); }
        @keyframes lightning-flash-quick { 0% { opacity: 0; } 60% { opacity: 1; } 100% { opacity: 0; } }
        .muzzle-puff { width: 25px; height: 25px; background: radial-gradient(circle, rgba(180,180,180,0.7) 10%, rgba(150,150,150,0.4) 40%, transparent 60%); border-radius: 50%; animation: puff-anim 0.4s ease-out forwards; transform: translate(-50%, -50%); }
        @keyframes puff-anim { 0% { transform: translate(-50%, -50%) scale(0.3); opacity: 0.9; } 100% { transform: translate(-50%, -50%) scale(1.8); opacity: 0; } }
        .muzzle-flash { width: 30px; height: 30px; background: radial-gradient(circle, white 20%, yellow 50%, orangered 80%, transparent 90%); border-radius: 50%; animation: quick-fade 0.15s forwards; transform: translate(-50%, -70%); }
        .muzzle-flash-lightning { width: 35px; height: 20px; background: radial-gradient(ellipse at center, white 10%, #7DF9FF 40%, rgba(0,191,255,0.5) 70%, transparent 90%); border-radius: 40% 40% 50% 50% / 80% 80% 20% 20%; animation: quick-fade-lightning 0.1s forwards; transform: translate(-50%, -80%) rotate(0deg); opacity: 0.8; }
        @keyframes quick-fade-lightning { 0% { transform: translate(-50%, -80%) scale(1.1); opacity: 0.8; } 100% { transform: translate(-50%, -80%) scale(0.4); opacity: 0; } }
        @keyframes quick-fade { 0% { transform: translate(-50%, -70%) scale(1.2); opacity: 1; } 100% { transform: translate(-50%, -70%) scale(0.5); opacity: 0; } }
        .recoil-active > .turret-model { animation-name: recoil-pushback-dynamic; animation-timing-function: ease-out; }
        @keyframes recoil-pushback-dynamic { 0% { transform: translateX(-50%) translateY(0); } 50% { transform: translateX(-50%) translateY(var(--recoil-amount)); } 100% { transform: translateX(-50%) translateY(0); } }

        .enemy-hit-sparkle {
            position: absolute;
            width: 7px; height: 7px;
            background-color: #FFFACD; 
            border-radius: 50%;
            box-shadow: 0 0 4px #FFFFE0, 0 0 7px #FFD700;
            pointer-events: none;
            animation: hit-sparkle-anim 0.4s ease-out forwards;
            transform: translate(-50%, -50%);
            z-index: 101;
        }
        @keyframes hit-sparkle-anim {
            0% { transform: translate(-50%, -50%) scale(1.5); opacity: 1; }
            100% { transform: translate(calc(-50% + var(--sparkle-dx, 0px)), calc(-50% + var(--sparkle-dy, 0px))) scale(0.1); opacity: 0; }
        }

        #controls { display: flex; flex-wrap: wrap; justify-content: space-around; width: 800px; margin-top: 10px; padding: 10px; background-color: #e0e0e0; border-radius: 5px; }
        /* ... other control styles ... */
        .control-group { border: 1px solid #ccc; padding: 10px; border-radius: 5px; background-color: #f9f9f9; margin-bottom: 10px; width: calc(33% - 20px); }
        .control-group h3 { margin-top: 0; text-align: center; }
        .control-group label { display: block; margin: 5px 0 2px; font-size: 0.9em; }
        .control-group input[type="range"] { width: 100%; box-sizing: border-box; }
        .control-group span { font-size: 0.8em; color: #555; }
        .global-controls { width: 100%; margin-bottom: 15px; padding: 10px; background-color: #d0d0d0; border-radius: 5px; text-align: center; }
        .global-controls label { margin: 0 10px; }
    </style>
</head>
<body>
    <!-- HTML structure remains the same as V3 -->
    <h1>TD Simulator - Rocket Corrected & Sparkles V4</h1>
    <div id="game-container">
        <div id="path"></div>
        <div id="tower-rocket-base" class="tower-base">
            <div class="turret-pivot"><div class="turret-model turret-rocket"><div class="turret-glow-indicator"></div></div></div>
        </div>
        <div id="tower-ice-base" class="tower-base">
            <div class="turret-pivot"><div class="turret-model turret-ice"><div class="turret-glow-indicator"></div></div></div>
        </div>
        <div id="tower-lightning-base" class="tower-base">
            <div class="turret-pivot"><div class="turret-model turret-lightning-barrel"><div class="turret-glow-indicator"></div></div></div>
        </div>
    </div>
    <div id="controls">
        <!-- Controls remain the same -->
        <div class="global-controls">
            <h3>Global Recoil Settings</h3>
            <label for="recoil-magnitude">Magnitude (px): <span id="recoil-magnitude-val">10</span></label>
            <input type="range" id="recoil-magnitude" min="0" max="20" value="10" step="1">
            <label for="recoil-duration">Duration (ms): <span id="recoil-duration-val">150</span></label>
            <input type="range" id="recoil-duration" min="50" max="500" value="150" step="10">
        </div>
        <div class="control-group" id="rocket-controls">
            <h3>Rocket Tower 🚀</h3>
            <label for="rocket-firerate">Fire Rate (ms): <span id="rocket-firerate-val">1500</span></label>
            <input type="range" id="rocket-firerate" min="500" max="5000" value="1500" step="100">
            <label for="rocket-damage">Damage: <span id="rocket-damage-val">70</span></label>
            <input type="range" id="rocket-damage" min="10" max="200" value="70" step="5">
            <label for="rocket-projspeed">Proj. Speed: <span id="rocket-projspeed-val">2</span></label>
            <input type="range" id="rocket-projspeed" min="0.5" max="5" value="2" step="0.1">
            <label for="rocket-range">Range (px): <span id="rocket-range-val">400</span></label>
            <input type="range" id="rocket-range" min="50" max="600" value="400" step="10">
        </div>
        <div class="control-group"  id="ice-controls">
            <h3>Ice Tower ❄️</h3>
            <label for="ice-firerate">Fire Rate (ms): <span id="ice-firerate-val">900</span></label>
            <input type="range" id="ice-firerate" min="200" max="2500" value="900" step="50">
            <label for="ice-damage">Damage: <span id="ice-damage-val">20</span></label>
            <input type="range" id="ice-damage" min="5" max="100" value="20" step="5">
            <label for="ice-projspeed">Proj. Speed: <span id="ice-projspeed-val">5</span></label>
            <input type="range" id="ice-projspeed" min="1" max="12" value="5" step="0.5">
            <label for="ice-range">Range (px): <span id="ice-range-val">400</span></label>
            <input type="range" id="ice-range" min="50" max="600" value="400" step="10">
        </div>
        <div class="control-group"  id="lightning-controls">
            <h3>Lightning Tower ⚡</h3>
            <label for="lightning-firerate">Fire Rate (ms): <span id="lightning-firerate-val">700</span></label>
            <input type="range" id="lightning-firerate" min="100" max="1500" value="700" step="50">
            <label for="lightning-damage">Damage: <span id="lightning-damage-val">35</span></label>
            <input type="range" id="lightning-damage" min="10" max="150" value="35" step="5">
            <label for="lightning-range">Range (px): <span id="lightning-range-val">400</span></label>
            <input type="range" id="lightning-range" min="50" max="600" value="400" step="10">
        </div>
    </div>
    <div id="bottom-pause-bar">
        <button id="pause-button-bottom" class="pause-button-style">Pause</button>
    </div>

    <script>
        // JavaScript largely the same as V3, key changes noted below
        const gameContainer = document.getElementById('game-container');
        const pathEl = document.getElementById('path');
        const pauseButtonBottom = document.getElementById('pause-button-bottom');
        let pathTop = 0; let pathHeight = 0;
        const gameWidth = gameContainer.offsetWidth;

        let enemies = []; let projectiles = [];
        let enemyIdCounter = 0; let projectileIdCounter = 0; let effectIdCounter = 0;

        let globalRecoilMagnitude = 10; let globalRecoilDuration = 150;

        const ENEMY_HEALTH_MAX = 300; const ENEMY_SPEED = 0.7;
        const ENEMY_WIDTH = 30; const ENEMY_HEIGHT = 30;
        const ENEMY_SPAWN_INTERVAL = 2000; let lastEnemySpawnTime = 0;

        const ROCKET_TURN_RATE = 0.05; const ROCKET_WOBBLE_AMPLITUDE = 4; const ROCKET_WOBBLE_FREQUENCY = 0.2;
        const ROCKET_PUFF_INTERVAL = 5; 
        const ROCKET_BODY_LENGTH = 16; // Define rocket body length for puff offset
        const DEFAULT_TURRET_ANGLE_RAD = -Math.PI / 2;

        let audioCtx = null; let userHasInteracted = false;
        let isPaused = false; let animationFrameId; let lastTimestamp = 0;

        const ICE_SPLASH_RADIUS = 70;
        const ICE_SPLASH_DAMAGE_PERCENTAGE = 0.5;

        function ensureAudioContext() { /* ... same ... */ 
            if (audioCtx && audioCtx.state === 'running') return true;
            if (!userHasInteracted) return false;
            if (!audioCtx) {
                try { audioCtx = new (window.AudioContext || window.webkitAudioContext)(); }
                catch (e) { console.error("Failed to create AudioContext:", e); return false; }
            }
            if (audioCtx.state === 'suspended') {
                audioCtx.resume().catch(e => console.error("Failed to resume AudioContext by ensureAudioContext:", e));
                return false; 
            }
            return audioCtx.state === 'running';
        }
        function playSound(type) { if (!ensureAudioContext()) return; actuallyPlaySound(type); }
        function actuallyPlaySound(type) { /* ... same ... */ 
            if (!audioCtx) return;
            let osc, gain, dur, freq, attack, decay, noiseSource, buffer, output, filter, env;
            const now = audioCtx.currentTime;
            switch (type) {
                case 'rocket_fire':
                    osc = audioCtx.createOscillator(); gain = audioCtx.createGain(); osc.connect(gain); gain.connect(audioCtx.destination);
                    osc.type = 'sawtooth'; freq = 60; dur = 0.6; attack = 0.05; decay = 0.5;
                    osc.frequency.setValueAtTime(freq, now); osc.frequency.exponentialRampToValueAtTime(freq * 0.7, now + dur);
                    gain.gain.setValueAtTime(0, now); gain.gain.linearRampToValueAtTime(0.15, now + attack); gain.gain.exponentialRampToValueAtTime(0.001, now + dur);
                    osc.start(now); osc.stop(now + dur);
                    noiseSource = audioCtx.createBufferSource(); buffer = audioCtx.createBuffer(1, audioCtx.sampleRate * 0.4, audioCtx.sampleRate); output = buffer.getChannelData(0);
                    for (let i = 0; i < buffer.length; i++) output[i] = Math.random() * 2 - 1;
                    noiseSource.buffer = buffer; filter = audioCtx.createBiquadFilter(); filter.type = "lowpass"; filter.frequency.setValueAtTime(800, now); filter.frequency.linearRampToValueAtTime(200, now + 0.3); filter.Q.value = 5;
                    const whooshGain = audioCtx.createGain(); noiseSource.connect(filter); filter.connect(whooshGain); whooshGain.connect(audioCtx.destination);
                    whooshGain.gain.setValueAtTime(0, now); whooshGain.gain.linearRampToValueAtTime(0.1, now + 0.02); whooshGain.gain.exponentialRampToValueAtTime(0.001, now + 0.4);
                    noiseSource.start(now); noiseSource.stop(now + 0.4);
                    break;
                case 'rocket_impact': 
                    dur = 0.8; attack = 0.01; decay = 0.7;
                    const thumpOsc = audioCtx.createOscillator(); const thumpGain = audioCtx.createGain();
                    thumpOsc.type = 'sine';
                    thumpOsc.frequency.setValueAtTime(100, now);
                    thumpOsc.frequency.exponentialRampToValueAtTime(40, now + 0.3);
                    thumpGain.gain.setValueAtTime(0, now);
                    thumpGain.gain.linearRampToValueAtTime(0.5, now + 0.02); 
                    thumpGain.gain.exponentialRampToValueAtTime(0.01, now + 0.3);
                    thumpOsc.connect(thumpGain); thumpGain.connect(audioCtx.destination);
                    thumpOsc.start(now); thumpOsc.stop(now + 0.3);

                    noiseSource = audioCtx.createBufferSource(); buffer = audioCtx.createBuffer(1, audioCtx.sampleRate * dur, audioCtx.sampleRate); output = buffer.getChannelData(0);
                    for (let i = 0; i < buffer.length; i++) output[i] = (Math.random() * 2 - 1); 
                    noiseSource.buffer = buffer; 
                    filter = audioCtx.createBiquadFilter(); filter.type = "bandpass"; 
                    filter.frequency.setValueAtTime(600, now); filter.frequency.exponentialRampToValueAtTime(150, now + decay);
                    filter.Q.value = 2;
                    gain = audioCtx.createGain();
                    noiseSource.connect(filter); filter.connect(gain); gain.connect(audioCtx.destination);
                    gain.gain.setValueAtTime(0, now); gain.gain.linearRampToValueAtTime(0.6, now + attack); 
                    gain.gain.exponentialRampToValueAtTime(0.001, now + decay);
                    noiseSource.start(now); noiseSource.stop(now + dur);

                    for (let k=0; k<2; k++) { const crackleOsc = audioCtx.createOscillator(); const crackleGain = audioCtx.createGain(); crackleOsc.type = 'square'; crackleOsc.frequency.setValueAtTime(1000 + Math.random()*1000, now + k*0.05 + 0.05); crackleGain.gain.setValueAtTime(0, now + k*0.05 + 0.05); crackleGain.gain.linearRampToValueAtTime(0.03, now + k*0.05 + 0.06); crackleGain.gain.exponentialRampToValueAtTime(0.001, now + k*0.05 + 0.2); crackleOsc.connect(crackleGain); crackleGain.connect(audioCtx.destination); crackleOsc.start(now + k*0.05 + 0.05); crackleOsc.stop(now + k*0.05 + 0.2); }
                    break;
                case 'ice_fire': 
                    osc = audioCtx.createOscillator(); gain = audioCtx.createGain(); osc.type = 'sine'; freq = 1200; dur = 0.3; attack = 0.01; decay = 0.25;
                    osc.frequency.setValueAtTime(freq, now); osc.frequency.exponentialRampToValueAtTime(freq * 1.8, now + dur * 0.3); osc.frequency.exponentialRampToValueAtTime(freq * 0.8, now + dur);
                    gain.gain.setValueAtTime(0, now); gain.gain.linearRampToValueAtTime(0.15, now + attack); gain.gain.exponentialRampToValueAtTime(0.001, now + dur);
                    osc.connect(gain); gain.connect(audioCtx.destination); osc.start(now); osc.stop(now + dur);
                    noiseSource = audioCtx.createBufferSource(); buffer = audioCtx.createBuffer(1, audioCtx.sampleRate * 0.1, audioCtx.sampleRate); output = buffer.getChannelData(0);
                    for (let i = 0; i < buffer.length; i++) output[i] = (Math.random() * 2 - 1) * 0.2;
                    noiseSource.buffer = buffer; filter = audioCtx.createBiquadFilter(); filter.type = "highpass"; filter.frequency.value = 3000; const frostGain = audioCtx.createGain();
                    noiseSource.connect(filter); filter.connect(frostGain); frostGain.connect(audioCtx.destination);
                    frostGain.gain.setValueAtTime(0, now); frostGain.gain.linearRampToValueAtTime(0.05, now + 0.01); frostGain.gain.exponentialRampToValueAtTime(0.001, now + 0.1);
                    noiseSource.start(now); noiseSource.stop(now + 0.1);
                    break;
                case 'ice_impact': 
                    const crackNoise = audioCtx.createBufferSource();
                    buffer = audioCtx.createBuffer(1, audioCtx.sampleRate * 0.1, audioCtx.sampleRate);
                    output = buffer.getChannelData(0);
                    for (let i = 0; i < buffer.length; i++) output[i] = Math.random() * 2 - 1;
                    crackNoise.buffer = buffer;
                    const crackFilter = audioCtx.createBiquadFilter(); crackFilter.type = "highpass"; crackFilter.frequency.value = 2500;
                    const crackGain = audioCtx.createGain();
                    crackGain.gain.setValueAtTime(0, now);
                    crackGain.gain.linearRampToValueAtTime(0.3, now + 0.005); 
                    crackGain.gain.exponentialRampToValueAtTime(0.001, now + 0.08);
                    crackNoise.connect(crackFilter); crackFilter.connect(crackGain); crackGain.connect(audioCtx.destination);
                    crackNoise.start(now); crackNoise.stop(now + 0.1);
                    
                    const crunchOsc = audioCtx.createOscillator(); const crunchGain = audioCtx.createGain();
                    crunchOsc.type = 'sawtooth';
                    crunchOsc.frequency.setValueAtTime(120, now + 0.02); 
                    crunchOsc.frequency.exponentialRampToValueAtTime(60, now + 0.22);
                    crunchGain.gain.setValueAtTime(0, now + 0.02);
                    crunchGain.gain.linearRampToValueAtTime(0.35, now + 0.025); 
                    crunchGain.gain.exponentialRampToValueAtTime(0.01, now + 0.22);
                    crunchOsc.connect(crunchGain); crunchGain.connect(audioCtx.destination);
                    crunchOsc.start(now + 0.02); crunchOsc.stop(now + 0.22);

                    gain = audioCtx.createGain(); gain.connect(audioCtx.destination);
                    for (let i = 0; i < 5; i++) { const oscShard = audioCtx.createOscillator(); const gShard = audioCtx.createGain(); oscShard.connect(gShard); gShard.connect(gain); oscShard.type = 'triangle'; const baseF = 1500 + Math.random() * 2000; oscShard.frequency.setValueAtTime(baseF, now + i * 0.02 + 0.03); oscShard.frequency.exponentialRampToValueAtTime(baseF * 0.7, now + i * 0.02 + 0.15); gShard.gain.setValueAtTime(0, now + i * 0.02 + 0.03); gShard.gain.linearRampToValueAtTime(0.04 / (i*0.5 + 1), now + i * 0.02 + 0.04); gShard.gain.exponentialRampToValueAtTime(0.001, now + i * 0.02 + 0.15); oscShard.start(now + i * 0.02 + 0.03); oscShard.stop(now + i * 0.02 + 0.15); }
                    break;
                case 'lightning_fire': 
                    noiseSource = audioCtx.createBufferSource(); buffer = audioCtx.createBuffer(1, audioCtx.sampleRate * 0.1, audioCtx.sampleRate); output = buffer.getChannelData(0);
                    for(let i=0; i<buffer.length; i++) output[i] = Math.random() * 2 - 1;
                    noiseSource.buffer = buffer; filter = audioCtx.createBiquadFilter(); filter.type = "bandpass"; filter.frequency.setValueAtTime(2000 + Math.random() * 1500, now); filter.Q.value = 30 + Math.random() * 20; gain = audioCtx.createGain();
                    noiseSource.connect(filter); filter.connect(gain); gain.connect(audioCtx.destination); dur = 0.1; attack = 0.002; decay = 0.08;
                    gain.gain.setValueAtTime(0, now); gain.gain.linearRampToValueAtTime(0.2, now + attack); gain.gain.exponentialRampToValueAtTime(0.001, now + dur);
                    noiseSource.start(now); noiseSource.stop(now + dur);
                    break;
                case 'lightning_impact': 
                    const zapNoise = audioCtx.createBufferSource();
                    buffer = audioCtx.createBuffer(1, audioCtx.sampleRate * 0.05, audioCtx.sampleRate); 
                    output = buffer.getChannelData(0);
                    for(let i=0; i<buffer.length; i++) output[i] = (Math.random() * 2 - 1) * 0.8; 
                    zapNoise.buffer = buffer;
                    const zapFilter = audioCtx.createBiquadFilter(); zapFilter.type = "highpass"; zapFilter.frequency.setValueAtTime(3000, now); zapFilter.Q.value = 5;
                    const zapGain = audioCtx.createGain();
                    zapGain.gain.setValueAtTime(0, now);
                    zapGain.gain.linearRampToValueAtTime(0.35, now + 0.002); 
                    zapGain.gain.exponentialRampToValueAtTime(0.001, now + 0.05); 
                    zapNoise.connect(zapFilter); zapFilter.connect(zapGain); zapGain.connect(audioCtx.destination);
                    zapNoise.start(now); zapNoise.stop(now + 0.05);

                    const ltThumpOsc = audioCtx.createOscillator(); const ltThumpGain = audioCtx.createGain();
                    ltThumpOsc.type = 'triangle';
                    ltThumpOsc.frequency.setValueAtTime(100, now + 0.01); 
                    ltThumpOsc.frequency.exponentialRampToValueAtTime(50, now + 0.15);
                    ltThumpGain.gain.setValueAtTime(0, now + 0.01);
                    ltThumpGain.gain.linearRampToValueAtTime(0.25, now + 0.015);
                    ltThumpGain.gain.exponentialRampToValueAtTime(0.001, now + 0.15);
                    ltThumpOsc.connect(ltThumpGain); ltThumpGain.connect(audioCtx.destination);
                    ltThumpOsc.start(now + 0.01); ltThumpOsc.stop(now + 0.15);
                    break;
                default: console.warn("Unknown sound type:", type);
            }
        }

        const towers = [ /* ... same ... */ 
            { id: 'tower-rocket', baseEl: document.getElementById('tower-rocket-base'), pivotEl: document.querySelector('#tower-rocket-base .turret-pivot'), modelEl: document.querySelector('#tower-rocket-base .turret-model'), glowEl: document.querySelector('#tower-rocket-base .turret-glow-indicator'), type: 'rocket', barrelLength: 16, recoilClass: 'recoil-active', fireRate: 1500, damage: 70, projectileSpeed: 2, range: 400, lastShotTime: 0, currentAngleRad: DEFAULT_TURRET_ANGLE_RAD, chargeLevel: 0, projectileClass: 'rocket', initialChargeTime: 0 },
            { id: 'tower-ice', baseEl: document.getElementById('tower-ice-base'), pivotEl: document.querySelector('#tower-ice-base .turret-pivot'), modelEl: document.querySelector('#tower-ice-base .turret-model'), glowEl: document.querySelector('#tower-ice-base .turret-glow-indicator'), type: 'ice', barrelLength: 15, recoilClass: 'recoil-active', fireRate: 900, damage: 20, projectileSpeed: 5, range: 400, lastShotTime: 0, currentAngleRad: DEFAULT_TURRET_ANGLE_RAD, chargeLevel: 0, projectileClass: 'ice-crystal-projectile', initialChargeTime: 0, splashRadius: ICE_SPLASH_RADIUS, splashDamageFactor: ICE_SPLASH_DAMAGE_PERCENTAGE },
            { id: 'tower-lightning', baseEl: document.getElementById('tower-lightning-base'), pivotEl: document.querySelector('#tower-lightning-base .turret-pivot'), modelEl: document.querySelector('#tower-lightning-base .turret-lightning-barrel'), glowEl: document.querySelector('#tower-lightning-base .turret-glow-indicator'), type: 'lightning', barrelLength: 20, recoilClass: 'recoil-active', fireRate: 700, damage: 35, projectileSpeed: 0, range: 400, lastShotTime: 0, currentAngleRad: DEFAULT_TURRET_ANGLE_RAD, chargeLevel: 0, initialChargeTime: 0 }
        ];
        towers.forEach(tower => { /* ... same ... */ 
            tower.x = tower.baseEl.offsetLeft + tower.baseEl.offsetWidth / 2;
            tower.y = tower.baseEl.offsetTop + tower.baseEl.offsetHeight / 2;
            if (tower.pivotEl) tower.pivotEl.style.transform = `rotate(${tower.currentAngleRad - Math.PI/2}rad)`;
            if (tower.type === 'ice' && tower.baseEl) { for (let i=0; i < 5; i++) { const mote = document.createElement('div'); mote.classList.add('frost-mote'); mote.style.left = `${Math.random() * 80 - 40}%`; mote.style.top = `${Math.random() * 80 - 40}%`; mote.style.setProperty('--mote-dx', Math.random() * 20 - 10); mote.style.setProperty('--mote-dy', Math.random() * 20 - 10); mote.style.animationDelay = `${Math.random() * 4}s`; tower.baseEl.appendChild(mote);}}
        });

        function updateSliderValue(sliderId, displayId) { /* ... same ... */ const slider = document.getElementById(sliderId); const display = document.getElementById(displayId); if (slider && display) { display.textContent = slider.value; return parseFloat(slider.value); } return 0; }
        function setupControls() { /* ... same ... */ 
            const recoilMagSlider = document.getElementById('recoil-magnitude'); const recoilMagVal = document.getElementById('recoil-magnitude-val');
            if (recoilMagSlider && recoilMagVal) { recoilMagSlider.addEventListener('input', (e) => { globalRecoilMagnitude = parseFloat(e.target.value); recoilMagVal.textContent = e.target.value; }); globalRecoilMagnitude = parseFloat(recoilMagSlider.value); }
            const recoilDurSlider = document.getElementById('recoil-duration'); const recoilDurVal = document.getElementById('recoil-duration-val');
            if (recoilDurSlider && recoilDurVal) { recoilDurSlider.addEventListener('input', (e) => { globalRecoilDuration = parseFloat(e.target.value); recoilDurVal.textContent = e.target.value; }); globalRecoilDuration = parseFloat(recoilDurSlider.value); }
            towers.forEach(tower => { const type = tower.type;
                ['firerate', 'damage', 'range'].forEach(stat => {
                    const slider = document.getElementById(`${type}-${stat}`); const valDisplay = document.getElementById(`${type}-${stat}-val`);
                    if (slider && valDisplay) { slider.addEventListener('input', () => { tower[stat] = parseFloat(slider.value); valDisplay.textContent = slider.value; }); tower[stat] = parseFloat(slider.value); }
                });
                if (tower.projectileSpeed !== undefined && tower.type !== 'lightning') {
                     const projSpeedSlider = document.getElementById(`${type}-projspeed`); const projSpeedValDisplay = document.getElementById(`${type}-projspeed-val`);
                     if (projSpeedSlider && projSpeedValDisplay) { projSpeedSlider.addEventListener('input', () => { tower.projectileSpeed = parseFloat(projSpeedSlider.value); projSpeedValDisplay.textContent = projSpeedSlider.value; }); tower.projectileSpeed = parseFloat(projSpeedSlider.value); }
                }
            });
        }
        function updatePathPosition() { /* ... same ... */ if (pathEl) { pathTop = pathEl.offsetTop; pathHeight = pathEl.offsetHeight; } }
        function spawnEnemy() { /* ... same ... */ if (pathHeight === 0 && pathTop === 0 && enemies.length < 1) return; enemyIdCounter++; const enemyEl = document.createElement('div'); enemyEl.classList.add('enemy'); enemyEl.id = `enemy-${enemyIdCounter}`; enemyEl.style.left = `${gameWidth - ENEMY_WIDTH}px`; enemyEl.style.top = `${pathTop + (pathHeight / 2) - (ENEMY_HEIGHT / 2)}px`; const healthBar = document.createElement('div'); healthBar.classList.add('enemy-health-bar'); const healthFill = document.createElement('div'); healthFill.classList.add('enemy-health-fill'); healthBar.appendChild(healthFill); enemyEl.appendChild(healthBar); if (gameContainer) gameContainer.appendChild(enemyEl); enemies.push({ id: enemyIdCounter, el: enemyEl, x: gameWidth - ENEMY_WIDTH, y: pathTop + (pathHeight / 2) - (ENEMY_HEIGHT / 2), health: ENEMY_HEALTH_MAX, maxHealth: ENEMY_HEALTH_MAX, speed: ENEMY_SPEED, healthFillEl: healthFill, age: 0 }); }
        function initialEnemyFill() { /* ... same ... */ if (pathHeight === 0 && pathTop === 0) return; const numToFill = Math.floor(gameWidth / (ENEMY_WIDTH + 10)); for (let i = 0; i < numToFill; i++) { const xPos = gameWidth - ENEMY_WIDTH - i * (ENEMY_WIDTH + 10); if (xPos + ENEMY_WIDTH < 0) break; enemyIdCounter++; const enemyEl = document.createElement('div'); enemyEl.classList.add('enemy'); enemyEl.id = `enemy-${enemyIdCounter}`; enemyEl.style.left = `${xPos}px`; enemyEl.style.top = `${pathTop + (pathHeight / 2) - (ENEMY_HEIGHT / 2)}px`; const healthBar = document.createElement('div'); healthBar.classList.add('enemy-health-bar'); const healthFill = document.createElement('div'); healthFill.classList.add('enemy-health-fill'); healthBar.appendChild(healthFill); enemyEl.appendChild(healthBar); if (gameContainer) gameContainer.appendChild(enemyEl); enemies.push({ id: enemyIdCounter, el: enemyEl, x: xPos, y: pathTop + (pathHeight / 2) - (ENEMY_HEIGHT / 2), health: ENEMY_HEALTH_MAX, maxHealth: ENEMY_HEALTH_MAX, speed: ENEMY_SPEED, healthFillEl: healthFill, age: 0 }); } }
        
        function updateEnemies(currentTime, deltaTime) { /* ... same ... */ 
            if (isPaused) return; const actualDeltaFactor = deltaTime / (1000/60);
            if (currentTime - lastEnemySpawnTime > ENEMY_SPAWN_INTERVAL) { spawnEnemy(); lastEnemySpawnTime = currentTime; }
            for (let i = enemies.length - 1; i >= 0; i--) { const enemy = enemies[i]; enemy.x -= enemy.speed * actualDeltaFactor; enemy.age++; enemy.el.style.left = `${enemy.x}px`; const healthPercentage = (enemy.health / enemy.maxHealth) * 100; enemy.healthFillEl.style.width = `${Math.max(0, healthPercentage)}%`; if (enemy.health <= 0) { enemy.el.remove(); enemies.splice(i, 1); continue; } if (enemy.x + ENEMY_WIDTH < 0) { enemy.el.remove(); enemies.splice(i, 1); } }
        }
        function findTarget(tower) { /* ... same ... */ let closestEnemy = null; let minDistance = tower.range; if (pathHeight === 0 && pathTop === 0 && enemies.length > 0 && tower.type !== "lightning") return null; for (const enemy of enemies) { const enemyCenterX = enemy.x + ENEMY_WIDTH / 2; const enemyCenterY = enemy.y + ENEMY_HEIGHT / 2; if (enemyCenterY > pathTop - pathHeight / 2 && enemyCenterY < pathTop + pathHeight * 1.5) { const distance = Math.sqrt(Math.pow(tower.x - enemyCenterX, 2) + Math.pow(tower.y - enemyCenterY, 2)); if (distance < minDistance) { minDistance = distance; closestEnemy = enemy; } } } return closestEnemy; }
        
        function createVisualEffect(x, y, type, options = {}) { /* ... same ... */ 
            let effectEl; 
            let duration = 400; 

            if (type === 'enemy_hit_sparkle') {
                const count = options.count || (3 + Math.floor(Math.random() * 3));
                for (let i = 0; i < count; i++) {
                    effectIdCounter++; 
                    const particleEl = document.createElement('div');
                    particleEl.id = `effect-${effectIdCounter}`; 
                    particleEl.classList.add('visual-effect', 'enemy-hit-sparkle'); 
                    particleEl.style.left = `${x}px`;
                    particleEl.style.top = `${y}px`;
                    const angle = Math.random() * Math.PI * 2;
                    const dist = 15 + Math.random() * 25; 
                    particleEl.style.setProperty('--sparkle-dx', `${Math.cos(angle) * dist}px`);
                    particleEl.style.setProperty('--sparkle-dy', `${Math.sin(angle) * dist}px`);
                    particleEl.style.animationDelay = `${Math.random() * 0.08}s`; 
                    if (gameContainer) gameContainer.appendChild(particleEl);
                    setTimeout(() => particleEl.remove(), 400); 
                }
                return; 
            }
            
            effectIdCounter++; 
            effectEl = document.createElement('div');
            effectEl.id = `effect-${effectIdCounter}`;
            effectEl.classList.add('visual-effect');

            if (type === 'explosion') { effectEl.classList.add('explosion'); duration = 500; }
            else if (type === 'ice_shatter') { effectEl.classList.add('ice-shatter'); duration = 400; }
            else if (type === 'ice_splash') { effectEl.classList.add('ice-splash-effect'); effectEl.style.setProperty('--splash-radius', `${options.radius * 2}px`); duration = 300; }
            else if (type === 'lightning_impact') { effectEl.classList.add('lightning-impact-flash'); duration = 200;}
            else if (type === 'muzzle_puff') { effectEl.classList.add('muzzle-puff'); duration = 400; }
            else if (type === 'muzzle_flash') { effectEl.classList.add('muzzle-flash'); duration = 150; }
            else if (type === 'muzzle_flash_lightning') { effectEl.classList.add('muzzle-flash-lightning'); duration = 100; if(options.angle) effectEl.style.transform = `translate(-50%, -80%) rotate(${options.angle * 180 / Math.PI + 90}deg)`;}
            else if (type === 'rocket_puff_trail') { effectEl.classList.add('rocket-puff'); duration = 800; }
            
            if (effectEl) { 
                effectEl.style.left = `${x}px`;
                effectEl.style.top = `${y}px`;
                if (gameContainer) gameContainer.appendChild(effectEl);
                setTimeout(() => effectEl.remove(), duration);
            }
        }
        
        function createLightningBoltVisual(tower, target) { /* ... same ... */ 
            const originX = tower.x + tower.barrelLength * Math.cos(tower.currentAngleRad);
            const originY = tower.y + tower.barrelLength * Math.sin(tower.currentAngleRad);
            const targetX = target.x + ENEMY_WIDTH / 2;
            const targetY = target.y + ENEMY_HEIGHT / 2;
            function drawSegmentedLine(startX, startY, endX, endY, segments, deviation, className, isMainBolt = false) { let currentX = startX; let currentY = startY; const totalDx = endX - startX; const totalDy = endY - startY; for (let i = 0; i < segments; i++) { effectIdCounter++; const segmentEl = document.createElement('div'); segmentEl.id = `effect-${effectIdCounter}`; segmentEl.classList.add(className); let nextX, nextY; if (i === segments - 1) { nextX = endX; nextY = endY; } else { const progress = (i + 1) / segments; nextX = startX + totalDx * progress + (Math.random() - 0.5) * deviation; nextY = startY + totalDy * progress + (Math.random() - 0.5) * deviation; } const dx = nextX - currentX; const dy = nextY - currentY; const length = Math.sqrt(dx * dx + dy * dy); const angle = Math.atan2(dy, dx) * (180 / Math.PI); const segmentHeight = parseFloat(getComputedStyle(segmentEl).height || '2px'); segmentEl.style.width = `${length}px`; segmentEl.style.left = `${currentX}px`; segmentEl.style.top = `${currentY - segmentHeight / 2}px`; segmentEl.style.transform = `rotate(${angle}deg)`; if (gameContainer) gameContainer.appendChild(segmentEl); setTimeout(() => segmentEl.remove(), isMainBolt ? 150 : 100); currentX = nextX; currentY = nextY; } }
            drawSegmentedLine(originX, originY, targetX, targetY, 5, 30, 'lightning-strike-segment', true);
            const numTendrils = 2 + Math.floor(Math.random() * 2); for (let t = 0; t < numTendrils; t++) { const branchPointRatio = 0.3 + Math.random() * 0.4; const branchOriginX = originX + (targetX - originX) * branchPointRatio; const branchOriginY = originY + (targetY - originY) * branchPointRatio; const tendrilEndX = branchOriginX + (Math.random() - 0.5) * 80; const tendrilEndY = branchOriginY + (Math.random() - 0.5) * 80; drawSegmentedLine(branchOriginX, branchOriginY, tendrilEndX, tendrilEndY, 3, 20, 'lightning-tendril-segment'); }
            createVisualEffect(targetX, targetY, 'lightning_impact');
        }

        function fireRecoil(tower) { /* ... same ... */ 
            if (tower.modelEl && tower.recoilClass) {
                const muzzleX = tower.x + tower.barrelLength * Math.cos(tower.currentAngleRad);
                const muzzleY = tower.y + tower.barrelLength * Math.sin(tower.currentAngleRad);

                if (tower.recoilClass === 'recoil-active' && tower.pivotEl) {
                    tower.modelEl.style.setProperty('--recoil-amount', `${-globalRecoilMagnitude}px`);
                    tower.modelEl.style.animationDuration = `${globalRecoilDuration}ms`;
                    tower.pivotEl.classList.add(tower.recoilClass);
                }
                if (tower.type === 'rocket' || tower.type === 'ice') {
                    createVisualEffect(muzzleX, muzzleY, 'muzzle_flash', { angle: tower.currentAngleRad });
                    setTimeout(() => createVisualEffect(muzzleX + Math.cos(tower.currentAngleRad)*2, muzzleY + Math.sin(tower.currentAngleRad)*2, 'muzzle_puff'), 50);
                } else if (tower.type === 'lightning') { 
                    createVisualEffect(muzzleX, muzzleY, 'muzzle_flash_lightning', { angle: tower.currentAngleRad });
                }
                if(tower.type === 'ice' && tower.modelEl) tower.modelEl.classList.add('firing');
                setTimeout(() => {
                    if(tower.pivotEl) tower.pivotEl.classList.remove(tower.recoilClass);
                    if(tower.type === 'ice' && tower.modelEl) tower.modelEl.classList.remove('firing');
                }, globalRecoilDuration + 50);
            }
        }

        function launchProjectile(tower, target) { 
            try { 
                projectileIdCounter++; 
                const projectileEl = document.createElement('div'); 
                projectileEl.classList.add('projectile', tower.projectileClass); 
                projectileEl.id = `proj-${projectileIdCounter}`; 
                const startX = tower.x + tower.barrelLength * Math.cos(tower.currentAngleRad); 
                const startY = tower.y + tower.barrelLength * Math.sin(tower.currentAngleRad); 
                
                if (tower.type === 'rocket') {
                    const bodyEl = document.createElement('div'); bodyEl.classList.add('rocket-body'); projectileEl.appendChild(bodyEl);
                    const finTop = document.createElement('div'); finTop.classList.add('rocket-fin', 'rocket-fin-top'); projectileEl.appendChild(finTop);
                    const finBottom = document.createElement('div'); finBottom.classList.add('rocket-fin', 'rocket-fin-bottom'); projectileEl.appendChild(finBottom);
                    const flameEl = document.createElement('div'); flameEl.classList.add('rocket-flame'); projectileEl.appendChild(flameEl);
                } else if (tower.type === 'ice') { 
                    const crystalInnerDiv = document.createElement('div'); projectileEl.appendChild(crystalInnerDiv); 
                } 
                
                projectileEl.style.left = `0px`; projectileEl.style.top = `0px`; projectileEl.style.visibility = 'hidden';
                if (gameContainer) gameContainer.appendChild(projectileEl); 
                
                projectileEl.style.left = `${startX - projectileEl.offsetWidth / 2}px`; 
                projectileEl.style.top = `${startY - projectileEl.offsetHeight / 2}px`; 
                projectileEl.style.visibility = 'visible'; 
                
                projectiles.push({ 
                    id: projectileIdCounter, el: projectileEl, 
                    x: startX, y: startY, 
                    damage: tower.damage, speed: tower.projectileSpeed, target: target, 
                    towerType: tower.type, 
                    currentAngle: (tower.type === 'rocket' ? tower.currentAngleRad : Math.atan2(target.y + ENEMY_HEIGHT/2 - startY, target.x + ENEMY_WIDTH/2 - startX)), 
                    age: 0, 
                    splashRadius: tower.splashRadius, splashDamageFactor: tower.splashDamageFactor 
                }); 
                if (tower.type === 'rocket') playSound('rocket_fire'); 
                else if (tower.type === 'ice') playSound('ice_fire'); 
                tower.lastShotTime = performance.now(); tower.chargeLevel = 0; 
                if(tower.glowEl) { tower.glowEl.style.opacity = 0; tower.glowEl.style.boxShadow = '0 0 0px 0px transparent'; } 
                fireRecoil(tower); 
            } catch (e) { console.error(`Error in launchProjectile for ${tower.id}:`, e); } 
        }
        
        function updateTowers(currentTime, deltaTime) { /* ... same, including enemy_hit_sparkle ... */ 
            if (isPaused) return;
            towers.forEach(tower => {
                if (tower.fireRate > 0) { if (tower.lastShotTime > 0) { tower.chargeLevel = Math.min(1, (currentTime - tower.lastShotTime) / tower.fireRate); } else if (tower.initialChargeTime > 0) { tower.chargeLevel = Math.min(1, (currentTime - tower.initialChargeTime) / tower.fireRate); } else { tower.chargeLevel = 0; } } else { tower.chargeLevel = 1; }
                if (tower.glowEl) { tower.glowEl.style.opacity = tower.chargeLevel * 0.8; const hue = 60 - (tower.chargeLevel * 60); const spread = tower.chargeLevel * 8; const blur = tower.chargeLevel * 15; tower.glowEl.style.boxShadow = `0 0 ${blur}px ${spread}px hsla(${hue}, 100%, 50%, 0.7)`; }
                const target = findTarget(tower);
                if (target) {
                    const targetX = target.x + ENEMY_WIDTH / 2; const targetY = target.y + ENEMY_HEIGHT / 2; const angleToTargetRad = Math.atan2(targetY - tower.y, targetX - tower.x); tower.currentAngleRad = angleToTargetRad; if (tower.pivotEl) tower.pivotEl.style.transform = `rotate(${angleToTargetRad - Math.PI/2}rad)`;
                    if (tower.chargeLevel >= 1.0) {
                        if (tower.type === 'lightning') { 
                            try { 
                                target.health -= tower.damage; 
                                createLightningBoltVisual(tower, target); 
                                playSound('lightning_fire'); 
                                setTimeout(() => playSound('lightning_impact'), 50); 
                                if (target.el) { 
                                    target.el.classList.add('hit-lightning'); 
                                    setTimeout(() => target.el.classList.remove('hit-lightning'), 100); 
                                    createVisualEffect(target.x + ENEMY_WIDTH/2, target.y + ENEMY_HEIGHT/2, 'enemy_hit_sparkle');
                                } 
                                tower.lastShotTime = performance.now(); 
                                tower.chargeLevel = 0; 
                                if(tower.glowEl) { tower.glowEl.style.opacity = 0; tower.glowEl.style.boxShadow = '0 0 0px 0px transparent'; } 
                                fireRecoil(tower); 
                            } catch (e) { console.error(`Error in lightning fire for ${tower.id}:`, e); } 
                        }
                        else { launchProjectile(tower, target); }
                    }
                }
            });
        }
        function updateProjectiles(deltaTime) {
            if (isPaused) return; const actualDeltaFactor = deltaTime / (1000/60);
            for (let i = projectiles.length - 1; i >= 0; i--) { 
                const proj = projectiles[i]; proj.age++; 
                const targetEnemy = proj.target; 
                if (!proj.el || !targetEnemy || targetEnemy.health <= 0 || !enemies.includes(targetEnemy)) { 
                    if(proj.el) proj.el.remove(); projectiles.splice(i, 1); continue; 
                } 
                const targetX = targetEnemy.x + ENEMY_WIDTH / 2; 
                const targetY = targetEnemy.y + ENEMY_HEIGHT / 2; 
                let dx = targetX - proj.x; let dy = targetY - proj.y; 
                const distance = Math.sqrt(dx * dx + dy * dy); 
                const moveSpeed = proj.speed * actualDeltaFactor;
                
                if (proj.towerType === 'rocket' && proj.age % ROCKET_PUFF_INTERVAL === 0) {
                    // Spawn puff from the tail of the rocket.
                    // The rocket's (0,0) is its left side. Tail is on the left.
                    // Puff offset should be from proj.x, proj.y (center of .rocket div)
                    // backwards along proj.currentAngle.
                    const puffOffset = (ROCKET_BODY_LENGTH / 2) + 4; // Half body length + a bit to clear flame
                    const puffX = proj.x - puffOffset * Math.cos(proj.currentAngle);
                    const puffY = proj.y - puffOffset * Math.sin(proj.currentAngle);
                    createVisualEffect(puffX, puffY, 'rocket_puff_trail');
                }

                if (distance < moveSpeed + (ENEMY_WIDTH / 3) ) { 
                    targetEnemy.health -= proj.damage; 
                    createVisualEffect(targetX, targetY, 'enemy_hit_sparkle');
                    let hitClass = '';
                    if (proj.towerType === 'rocket') { createVisualEffect(targetX, targetY, 'explosion'); playSound('rocket_impact'); hitClass = 'hit-rocket'; }
                    else if (proj.towerType === 'ice') { createVisualEffect(targetX, targetY, 'ice_shatter'); playSound('ice_impact'); hitClass = 'hit-ice'; if (proj.splashRadius && proj.splashDamageFactor) { createVisualEffect(targetX, targetY, 'ice_splash', { radius: proj.splashRadius }); enemies.forEach(otherEnemy => { if (otherEnemy !== targetEnemy && otherEnemy.health > 0) { const splashDist = Math.sqrt(Math.pow(targetX - (otherEnemy.x + ENEMY_WIDTH/2), 2) + Math.pow(targetY - (otherEnemy.y + ENEMY_HEIGHT/2), 2)); if (splashDist <= proj.splashRadius) { otherEnemy.health -= proj.damage * proj.splashDamageFactor; if (otherEnemy.el) { otherEnemy.el.classList.add('hit-ice'); setTimeout(() => otherEnemy.el.classList.remove('hit-ice'), 100); createVisualEffect(otherEnemy.x + ENEMY_WIDTH/2, otherEnemy.y + ENEMY_HEIGHT/2, 'enemy_hit_sparkle'); } } } }); } }
                    if (targetEnemy.el && hitClass) { targetEnemy.el.classList.add(hitClass); setTimeout(() => targetEnemy.el.classList.remove(hitClass), 100); }
                    proj.el.remove(); projectiles.splice(i, 1); continue;
                }
                let moveX, moveY; 
                if (proj.towerType === 'rocket') { 
                    const angleToTarget = Math.atan2(dy, dx); 
                    let angleDifference = angleToTarget - proj.currentAngle; 
                    while (angleDifference > Math.PI) angleDifference -= 2 * Math.PI; 
                    while (angleDifference < -Math.PI) angleDifference += 2 * Math.PI; 
                    const turnAmount = Math.sign(angleDifference) * Math.min(Math.abs(angleDifference), ROCKET_TURN_RATE * (moveSpeed / 1.5)); 
                    proj.currentAngle += turnAmount; 
                    moveX = Math.cos(proj.currentAngle) * moveSpeed; 
                    moveY = Math.sin(proj.currentAngle) * moveSpeed; 
                    const wobbleAngle = proj.currentAngle + Math.PI / 2; 
                    moveX += Math.cos(wobbleAngle) * ROCKET_WOBBLE_AMPLITUDE * Math.sin(proj.age * ROCKET_WOBBLE_FREQUENCY) * 0.1 * actualDeltaFactor; 
                    moveY += Math.sin(wobbleAngle) * ROCKET_WOBBLE_AMPLITUDE * Math.sin(proj.age * ROCKET_WOBBLE_FREQUENCY) * 0.1 * actualDeltaFactor; 
                    proj.el.style.transform = `rotate(${proj.currentAngle}rad)`; 
                }
                else { 
                    moveX = (dx / distance) * moveSpeed; 
                    moveY = (dy / distance) * moveSpeed; 
                }
                proj.x += moveX; proj.y += moveY; 
                proj.el.style.left = `${proj.x - proj.el.offsetWidth/2}px`; 
                proj.el.style.top = `${proj.y - proj.el.offsetHeight/2}px`; 
                if (proj.x < -50 || proj.x > gameWidth + 50 || proj.y < -50 || proj.y > gameContainer.offsetHeight + 100) { proj.el.remove(); projectiles.splice(i, 1); }
            }
        }

        function gameLoop(timestamp) { /* ... same ... */ 
            if (!lastTimestamp) lastTimestamp = timestamp;
            const deltaTime = timestamp - lastTimestamp;
            lastTimestamp = timestamp;
            if (!isPaused) { updateEnemies(timestamp, deltaTime); updateTowers(timestamp, deltaTime); updateProjectiles(deltaTime); }
            animationFrameId = requestAnimationFrame(gameLoop);
        }
        
        function togglePauseState() { /* ... same ... */ 
            isPaused = !isPaused;
            const buttonText = isPaused ? 'Resume' : 'Pause';
            if (pauseButtonBottom) {
                pauseButtonBottom.textContent = buttonText;
                pauseButtonBottom.classList.toggle('paused', isPaused);
            }
            if (!isPaused) { 
                lastTimestamp = performance.now(); 
                ensureAudioContext(); 
            }
        }

        if(pauseButtonBottom) { pauseButtonBottom.addEventListener('click', togglePauseState); }

        document.addEventListener('DOMContentLoaded', () => { /* ... same ... */ 
            updatePathPosition(); setupControls(); 
            const userGestureEvents = ['click', 'touchstart', 'keydown', 'mousemove']; 
            function handleUserGesture() { if (!userHasInteracted) { userHasInteracted = true; ensureAudioContext(); userGestureEvents.forEach(event => document.body.removeEventListener(event, handleUserGesture)); } } 
            userGestureEvents.forEach(event => { document.body.addEventListener(event, handleUserGesture, { once: true }); }); 
            initialEnemyFill(); lastEnemySpawnTime = performance.now(); 
            const initialTime = performance.now(); 
            towers.forEach(tower => { tower.lastShotTime = 0; tower.initialChargeTime = initialTime; }); 
            animationFrameId = requestAnimationFrame(gameLoop); 
        });
    </script>
</body>
</html>