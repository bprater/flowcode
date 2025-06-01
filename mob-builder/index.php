<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TD Enemy Simulator - Robust Web Audio</title>
    <style>
        body { font-family: sans-serif; display: flex; flex-direction: column; align-items: center; margin: 0; background-color: #e0e0e0; }
        #game-container {
            position: relative; width: 800px; height: 450px;
            background-color: #5DAD36;
            background-image: linear-gradient(to bottom, #76C84D 0%, #5DAD36 60%, #488A29 100%);
            border: 2px solid #333; margin-top: 20px; overflow: hidden;
        }
        #path { position: absolute; bottom: 30px; left: 0; width: 100%; height: 60px; background-color: #b08d57; border-top: 1px dashed #8a6d40; border-bottom: 1px dashed #8a6d40; }

        /* ... (All other CSS remains IDENTICAL to the previous "Sounds & Grass" version) ... */
        .tower-base { position: absolute; width: 45px; height: 45px; border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 3px 6px rgba(0,0,0,0.4), inset 0 2px 4px rgba(0,0,0,0.2); top: 50px; }
        #tower-rocket-base { background: linear-gradient(135deg, #A0522D 0%, #8B4513 100%); left: 100px; }
        #tower-rocket-base::before { content: ''; position: absolute; width: 8px; height: 8px; background: #696969; border-radius: 50%; box-shadow: 15px 0 #696969, -15px 0 #696969, 0 15px #696969, 0 -15px #696969; }
        #tower-ice-base { background: linear-gradient(135deg, #4682B4 0%, #5F9EA0 100%); left: 377.5px; }
        #tower-ice-base::before { content: ''; position: absolute; width: 100%; height: 100%; border-radius: 50%; box-shadow: 0 0 10px 3px rgba(200, 240, 255, 0.7); pointer-events: none; }
        #tower-lightning-base { background: linear-gradient(135deg, #B8860B 0%, #800000 100%); left: 655px; }
        #tower-lightning-base::after { content: ''; position: absolute; width: 6px; height: 110%; background: #FFD700; border-radius: 3px; transform: rotate(45deg); box-shadow: 0 0 3px gold; }
        #tower-lightning-base::before { content: ''; position: absolute; width: 6px; height: 110%; background: #FFD700; border-radius: 3px; transform: rotate(-45deg); box-shadow: 0 0 3px gold; }

        .turret-pivot { position: absolute; width: 100%; height: 100%; top: 0; left: 0; transform-origin: center center; }
        .turret-model { position: absolute; border: 1px solid #333; transform-origin: 50% 90%; left: 50%; bottom: 45%; transform: translateX(-50%); box-shadow: 0 1px 3px rgba(0,0,0,0.3); }
        .turret-glow-indicator { position: absolute; top: 0; left: 0; right: 0; bottom: 0; border-radius: inherit; box-shadow: 0 0 0px 0px transparent; opacity: 0; transition: opacity 0.1s, box-shadow 0.1s; pointer-events: none; z-index: 1; }

        .turret-rocket { width: 20px; height: 32px; background: linear-gradient(#A9A9A9, #696969); border-radius: 5px 5px 2px 2px; }
        .turret-rocket::before { content: ''; position: absolute; top: -3px; left: 2px; width: 16px; height: 5px; background: #505050; border-radius: 2px; }
        .turret-ice { width: 14px; height: 30px; background: linear-gradient(to bottom, #E0FFFF, #AFEEEE 70%, #87CEFA); border-radius: 7px 7px 3px 3px / 4px 4px 2px 2px; box-shadow: inset 0 0 4px rgba(255,255,255,0.6), 0 0 5px #AFEEEE; }
        .turret-ice.firing::after { content: ''; position: absolute; top: -10px; left: 50%; transform: translateX(-50%); width: 24px; height: 24px; background: radial-gradient(circle, rgba(224,255,255,0.8) 10%, rgba(175,238,238,0.4) 40%, transparent 70%); border-radius: 50%; opacity: 1; animation: quick-fade 0.2s forwards; }
        .turret-lightning { width: 28px; height: 28px; background: radial-gradient(circle, #FFFFE0 20%, #FFD700 60%, #FFA500 90%); border-radius: 50%; border: 2px solid #DAA520; box-shadow: 0 0 10px gold, 0 0 15px yellow, inset 0 0 5px #FFFACD; }
        .turret-lightning::before, .turret-lightning::after { content: ''; position: absolute; width: 6px; height: 6px; background: #FF8C00; border-radius: 50%; box-shadow: 0 0 4px #FF4500; }
        .turret-lightning::before { top: -4px; left: calc(50% - 3px); }
        .turret-lightning::after { bottom: -4px; left: calc(50% - 3px); }

        .enemy { position: absolute; width: 30px; height: 30px; background-color: #2ecc71; border: 1px solid #27ae60; border-radius: 5px; display: flex; flex-direction: column; align-items: center; justify-content: center; box-sizing: border-box; font-size: 10px; color: white; }
        .enemy-health-bar { width: 90%; height: 5px; background-color: #e74c3c; border: 1px solid #c0392b; margin-top: 2px; }
        .enemy-health-fill { width: 100%; height: 100%; background-color: #2ecc71; }

        .projectile { position: absolute; box-sizing: border-box; }
        .rocket { width: 12px; height: 6px; background-color: #808080; border: 1px solid #505050; border-radius: 3px 0 0 3px; transform-origin: 25% 50%; }
        .rocket-flame { position: absolute; left: 100%; top: 50%; transform: translate(0%, -50%); width: 10px; height: 10px; background: radial-gradient(circle, yellow 20%, orangered 50%, transparent 70%); border-radius: 50%; animation: flicker 0.1s infinite alternate; }
        @keyframes flicker { 0% { transform: scale(0.8) translate(0%, -50%); opacity: 0.7; } 100% { transform: scale(1.2) translate(0%, -50%); opacity: 1; } }

        .ice-crystal-projectile { width: 14px; height: 14px; position: relative; animation: spin 0.7s linear infinite; }
        .ice-crystal-projectile::before, .ice-crystal-projectile::after { content: ''; position: absolute; background-color: #afeeee; box-shadow: 0 0 3px #fff, 0 0 5px #add8e6; border-radius: 1px; }
        .ice-crystal-projectile::before { width: 100%; height: 20%; top: 40%; left: 0; }
        .ice-crystal-projectile::after { width: 20%; height: 100%; top: 0; left: 40%; }
        .ice-crystal-projectile > div { position: absolute; width: 100%; height: 20%; top: 40%; left: 0; background-color: #afeeee; box-shadow: 0 0 3px #fff, 0 0 5px #add8e6; border-radius: 1px; transform: rotate(45deg); }
        @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
        .ice-trail-particle { position: absolute; width: 4px; height: 4px; background-color: rgba(200, 240, 255, 0.6); border-radius: 50%; opacity: 1; animation: fadeOutAndDrift 0.6s forwards; pointer-events: none; }
        @keyframes fadeOutAndDrift { 0% { opacity: 0.7; transform: scale(1) translate(0,0); } 100% { opacity: 0; transform: scale(0.3) translate(var(--drift-x, 0px), var(--drift-y, 0px)); } }

        .visual-effect { position: absolute; pointer-events: none; z-index: 100; }
        .explosion { width: 50px; height: 50px; background: radial-gradient(circle, #fff700 0%, #ff8c00 30%, #ff4500 60%, transparent 70%); border-radius: 50%; animation: explosion-anim 0.4s forwards; transform: translate(-50%, -50%); }
        @keyframes explosion-anim { 0% { transform: translate(-50%, -50%) scale(0.2); opacity: 1; } 100% { transform: translate(-50%, -50%) scale(1.5); opacity: 0; } }
        .ice-shatter { width: 30px; height: 30px; transform: translate(-50%, -50%); animation: ice-shatter-anim 0.3s forwards; }
        @keyframes ice-shatter-anim { 0% { opacity: 1; transform: translate(-50%, -50%) scale(0.5); } 100% { opacity: 0; transform: translate(-50%, -50%) scale(1.2) rotate(90deg); } }
        .lightning-strike-segment { position: absolute; background-color: yellow; height: 3px; transform-origin: 0 50%; box-shadow: 0 0 5px gold, 0 0 10px yellow; animation: lightning-flash 0.2s forwards; }
        .lightning-impact-flash { width: 30px; height: 30px; background: radial-gradient(circle, white 20%, yellow 60%, transparent 80%); border-radius: 50%; animation: explosion-anim 0.2s forwards; transform: translate(-50%, -50%); }
        @keyframes lightning-flash { 0% { opacity: 0; } 50% { opacity: 1; } 100% { opacity: 0; } }

        .muzzle-puff { width: 25px; height: 25px; background: radial-gradient(circle, rgba(180,180,180,0.7) 10%, rgba(150,150,150,0.4) 40%, transparent 60%); border-radius: 50%; animation: puff-anim 0.4s ease-out forwards; transform: translate(-50%, -50%); }
        @keyframes puff-anim { 0% { transform: translate(-50%, -50%) scale(0.3); opacity: 0.9; } 100% { transform: translate(-50%, -50%) scale(1.8); opacity: 0; } }
        .muzzle-flash { width: 30px; height: 30px; background: radial-gradient(circle, white 20%, yellow 50%, orangered 80%, transparent 90%); border-radius: 50%; animation: quick-fade 0.15s forwards; transform: translate(-50%, -70%); }
        @keyframes quick-fade { 0% { transform: translate(-50%, -70%) scale(1.2); opacity: 1; } 100% { transform: translate(-50%, -70%) scale(0.5); opacity: 0; } }

        .recoil-active > .turret-model { animation-name: recoil-pushback-dynamic; animation-timing-function: ease-out; }
        @keyframes recoil-pushback-dynamic { 0% { transform: translateX(-50%) translateY(0); } 50% { transform: translateX(-50%) translateY(var(--recoil-amount)); } 100% { transform: translateX(-50%) translateY(0); } }
        .recoil-lightning > .turret-model { animation: recoil-pulse 0.2s ease-in-out; }
        @keyframes recoil-pulse { 0% { transform: scale(1); box-shadow: 0 0 8px gold; } 50% { transform: scale(1.15); box-shadow: 0 0 15px yellow, 0 0 5px white; } 100% { transform: scale(1); box-shadow: 0 0 8px gold; } }

        #controls { display: flex; flex-wrap: wrap; justify-content: space-around; width: 800px; margin-top: 20px; padding: 10px; background-color: #e0e0e0; border-radius: 5px; }
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
    <h1>TD Enemy Simulator - Robust Web Audio</h1>
    <div id="game-container">
        <div id="path"></div>
        <div id="tower-rocket-base" class="tower-base">
            <div class="turret-pivot"><div class="turret-model turret-rocket"><div class="turret-glow-indicator"></div></div></div>
        </div>
        <div id="tower-ice-base" class="tower-base">
            <div class="turret-pivot"><div class="turret-model turret-ice"><div class="turret-glow-indicator"></div></div></div>
        </div>
        <div id="tower-lightning-base" class="tower-base">
            <div class="turret-pivot"><div class="turret-model turret-lightning"><div class="turret-glow-indicator"></div></div></div>
        </div>
    </div>
    <div id="controls">
        <div class="global-controls">
            <h3>Global Recoil Settings</h3>
            <label for="recoil-magnitude">Magnitude (px): <span id="recoil-magnitude-val">4</span></label>
            <input type="range" id="recoil-magnitude" min="0" max="15" value="4" step="1">
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

    <script>
        const gameContainer = document.getElementById('game-container');
        const pathEl = document.getElementById('path');
        let pathTop = 0;
        let pathHeight = 0;
        const gameWidth = gameContainer.offsetWidth;

        let enemies = [];
        let projectiles = [];
        let enemyIdCounter = 0;
        let projectileIdCounter = 0;
        let effectIdCounter = 0;

        let globalRecoilMagnitude = 4;
        let globalRecoilDuration = 150;

        const ENEMY_HEALTH_MAX = 300;
        const ENEMY_SPEED = 0.7;
        const ENEMY_WIDTH = 30;
        const ENEMY_HEIGHT = 30;
        const ENEMY_SPAWN_INTERVAL = 2000;
        let lastEnemySpawnTime = 0;

        const ROCKET_TURN_RATE = 0.05;
        const ROCKET_WOBBLE_AMPLITUDE = 4;
        const ROCKET_WOBBLE_FREQUENCY = 0.2;
        const DEFAULT_TURRET_ANGLE_RAD = -Math.PI / 2;

        let audioCtx = null;
        let userHasInteracted = false;

        function ensureAudioContext() {
            if (audioCtx && audioCtx.state === 'running') {
                return true;
            }
            if (!userHasInteracted) {
                // console.log("AudioContext: Waiting for user interaction.");
                return false;
            }
            if (!audioCtx) {
                try {
                    audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                    // console.log("AudioContext created. State:", audioCtx.state);
                } catch (e) {
                    console.error("Failed to create AudioContext:", e);
                    return false;
                }
            }
            if (audioCtx.state === 'suspended') {
                audioCtx.resume().then(() => {
                    // console.log("AudioContext resumed by ensureAudioContext.");
                }).catch(e => {
                    console.error("Failed to resume AudioContext by ensureAudioContext:", e);
                });
                return false; // Resume is async, check next time
            }
            return audioCtx.state === 'running';
        }


        function playSound(type) {
            if (!ensureAudioContext()) {
                // console.warn(`Cannot play sound "${type}", AudioContext not ready.`);
                return;
            }
            // This is the function with the switch statement for sound synthesis
            actuallyPlaySound(type);
        }

        function actuallyPlaySound(type) {
            if (!audioCtx) return; // Should be caught by ensureAudioContext, but safety check

            let oscillator, gainNode, duration, freq, attackTime, decayTime;

            switch (type) {
                case 'rocket_fire':
                    oscillator = audioCtx.createOscillator(); gainNode = audioCtx.createGain();
                    oscillator.connect(gainNode); gainNode.connect(audioCtx.destination);
                    oscillator.type = 'sawtooth'; freq = 80; duration = 0.5; attackTime = 0.05; decayTime = 0.4;
                    oscillator.frequency.setValueAtTime(freq, audioCtx.currentTime);
                    oscillator.frequency.exponentialRampToValueAtTime(freq / 2, audioCtx.currentTime + duration * 0.8);
                    gainNode.gain.setValueAtTime(0, audioCtx.currentTime);
                    gainNode.gain.linearRampToValueAtTime(0.20, audioCtx.currentTime + attackTime); // Quieter
                    gainNode.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + duration);
                    oscillator.start(audioCtx.currentTime); oscillator.stop(audioCtx.currentTime + duration);
                    break;
                case 'rocket_impact':
                    const noiseBuffer = audioCtx.createBuffer(1, audioCtx.sampleRate * 0.4, audioCtx.sampleRate);
                    const output = noiseBuffer.getChannelData(0);
                    for (let i = 0; i < noiseBuffer.length; i++) { output[i] = (Math.random() * 2 - 1) * 0.4; } // Quieter noise
                    const noiseSource = audioCtx.createBufferSource(); noiseSource.buffer = noiseBuffer;
                    gainNode = audioCtx.createGain(); noiseSource.connect(gainNode); gainNode.connect(audioCtx.destination);
                    duration = 0.4; attackTime = 0.01; decayTime = 0.35;
                    gainNode.gain.setValueAtTime(0, audioCtx.currentTime);
                    gainNode.gain.linearRampToValueAtTime(0.35, audioCtx.currentTime + attackTime); // Quieter
                    gainNode.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + duration);
                    noiseSource.start(audioCtx.currentTime); noiseSource.stop(audioCtx.currentTime + duration);
                    oscillator = audioCtx.createOscillator(); const thumpGain = audioCtx.createGain();
                    oscillator.connect(thumpGain); thumpGain.connect(audioCtx.destination);
                    oscillator.type = 'sine'; oscillator.frequency.setValueAtTime(60, audioCtx.currentTime);
                    thumpGain.gain.setValueAtTime(0.5, audioCtx.currentTime); // Quieter
                    thumpGain.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + 0.2);
                    oscillator.start(audioCtx.currentTime); oscillator.stop(audioCtx.currentTime + 0.2);
                    break;
                case 'ice_fire':
                    oscillator = audioCtx.createOscillator(); gainNode = audioCtx.createGain(); const filter = audioCtx.createBiquadFilter();
                    oscillator.connect(filter); filter.connect(gainNode); gainNode.connect(audioCtx.destination);
                    oscillator.type = 'triangle'; freq = 600; duration = 0.3; attackTime = 0.02; decayTime = 0.25;
                    filter.type = 'highpass'; filter.frequency.setValueAtTime(400, audioCtx.currentTime); filter.Q.value = 1;
                    oscillator.frequency.setValueAtTime(freq, audioCtx.currentTime);
                    oscillator.frequency.linearRampToValueAtTime(freq * 1.5, audioCtx.currentTime + duration * 0.7);
                    gainNode.gain.setValueAtTime(0, audioCtx.currentTime);
                    gainNode.gain.linearRampToValueAtTime(0.15, audioCtx.currentTime + attackTime); // Quieter
                    gainNode.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + duration);
                    oscillator.start(audioCtx.currentTime); oscillator.stop(audioCtx.currentTime + duration);
                    break;
                case 'ice_impact':
                    gainNode = audioCtx.createGain(); gainNode.connect(audioCtx.destination);
                    for (let i = 0; i < 5; i++) {
                        const osc = audioCtx.createOscillator(); const g = audioCtx.createGain();
                        osc.connect(g); g.connect(gainNode); osc.type = 'square';
                        const baseFreq = 2000 + Math.random() * 1000;
                        osc.frequency.setValueAtTime(baseFreq, audioCtx.currentTime + i * 0.02);
                        osc.frequency.exponentialRampToValueAtTime(baseFreq * 0.8, audioCtx.currentTime + i * 0.02 + 0.1);
                        g.gain.setValueAtTime(0, audioCtx.currentTime + i * 0.02);
                        g.gain.linearRampToValueAtTime(0.08 / (i + 1), audioCtx.currentTime + i * 0.02 + 0.01); // Quieter
                        g.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + i * 0.02 + 0.1);
                        osc.start(audioCtx.currentTime + i * 0.02); osc.stop(audioCtx.currentTime + i * 0.02 + 0.1);
                    }
                    break;
                case 'lightning_fire':
                    const bandpass = audioCtx.createBiquadFilter(); bandpass.type = 'bandpass';
                    bandpass.frequency.value = 1000 + Math.random() * 1000; bandpass.Q.value = 20;
                    const noiseBufferLight = audioCtx.createBuffer(1, audioCtx.sampleRate * 0.15, audioCtx.sampleRate);
                    const outputLight = noiseBufferLight.getChannelData(0);
                    for (let i = 0; i < noiseBufferLight.length; i++) { outputLight[i] = (Math.random() * 2 - 1); }
                    const noiseSourceLight = audioCtx.createBufferSource(); noiseSourceLight.buffer = noiseBufferLight;
                    gainNode = audioCtx.createGain(); noiseSourceLight.connect(bandpass); bandpass.connect(gainNode); gainNode.connect(audioCtx.destination);
                    duration = 0.15; attackTime = 0.005; decayTime = 0.1;
                    gainNode.gain.setValueAtTime(0, audioCtx.currentTime);
                    gainNode.gain.linearRampToValueAtTime(0.20, audioCtx.currentTime + attackTime); // Quieter
                    gainNode.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + duration);
                    noiseSourceLight.start(audioCtx.currentTime); noiseSourceLight.stop(audioCtx.currentTime + duration);
                    break;
                case 'lightning_impact': playSound('lightning_fire'); break;
                default: console.warn("Unknown sound type:", type);
            }
        }

        const towers = [ /* BARREL LENGTHS NEED YOUR ADJUSTMENT! */
            { id: 'tower-rocket', baseEl: document.getElementById('tower-rocket-base'), pivotEl: document.querySelector('#tower-rocket-base .turret-pivot'), modelEl: document.querySelector('#tower-rocket-base .turret-model'), glowEl: document.querySelector('#tower-rocket-base .turret-glow-indicator'), type: 'rocket', barrelLength: 16, recoilClass: 'recoil-active', fireRate: 1500, damage: 70, projectileSpeed: 2, range: 400, lastShotTime: 0, currentAngleRad: DEFAULT_TURRET_ANGLE_RAD, chargeLevel: 0, projectileClass: 'rocket', initialChargeTime: 0 },
            { id: 'tower-ice', baseEl: document.getElementById('tower-ice-base'), pivotEl: document.querySelector('#tower-ice-base .turret-pivot'), modelEl: document.querySelector('#tower-ice-base .turret-model'), glowEl: document.querySelector('#tower-ice-base .turret-glow-indicator'), type: 'ice', barrelLength: 15, recoilClass: 'recoil-active', fireRate: 900, damage: 20, projectileSpeed: 5, range: 400, lastShotTime: 0, currentAngleRad: DEFAULT_TURRET_ANGLE_RAD, chargeLevel: 0, projectileClass: 'ice-crystal-projectile', initialChargeTime: 0 },
            { id: 'tower-lightning', baseEl: document.getElementById('tower-lightning-base'), pivotEl: document.querySelector('#tower-lightning-base .turret-pivot'), modelEl: document.querySelector('#tower-lightning-base .turret-model'), glowEl: document.querySelector('#tower-lightning-base .turret-glow-indicator'), type: 'lightning', barrelLength: 0, recoilClass: 'recoil-lightning', fireRate: 700, damage: 35, projectileSpeed: 0, range: 400, lastShotTime: 0, currentAngleRad: DEFAULT_TURRET_ANGLE_RAD, chargeLevel: 0, initialChargeTime: 0 }
        ];
        towers.forEach(tower => {
            tower.x = tower.baseEl.offsetLeft + tower.baseEl.offsetWidth / 2;
            tower.y = tower.baseEl.offsetTop + tower.baseEl.offsetHeight / 2;
            if (tower.pivotEl) tower.pivotEl.style.transform = `rotate(${tower.currentAngleRad - Math.PI/2}rad)`;
        });

        function updateSliderValue(sliderId, displayId) {
            const slider = document.getElementById(sliderId); const display = document.getElementById(displayId);
            if (slider && display) { display.textContent = slider.value; return parseFloat(slider.value); }
            return 0;
        }

        function setupControls() { /* ... Same as previous, ensure it's robust ... */
            const recoilMagSlider = document.getElementById('recoil-magnitude');
            const recoilMagVal = document.getElementById('recoil-magnitude-val');
            if (recoilMagSlider && recoilMagVal) {
                recoilMagSlider.addEventListener('input', (e) => { globalRecoilMagnitude = parseFloat(e.target.value); recoilMagVal.textContent = e.target.value; });
                globalRecoilMagnitude = parseFloat(recoilMagSlider.value);
            }
            const recoilDurSlider = document.getElementById('recoil-duration');
            const recoilDurVal = document.getElementById('recoil-duration-val');
            if (recoilDurSlider && recoilDurVal) {
                recoilDurSlider.addEventListener('input', (e) => { globalRecoilDuration = parseFloat(e.target.value); recoilDurVal.textContent = e.target.value; });
                globalRecoilDuration = parseFloat(recoilDurSlider.value);
            }
            towers.forEach(tower => {
                const type = tower.type;
                ['firerate', 'damage', 'projspeed', 'range'].forEach(stat => {
                    const slider = document.getElementById(`${type}-${stat}`);
                    if (slider) {
                        slider.addEventListener('input', () => { tower[stat] = updateSliderValue(`${type}-${stat}`, `${type}-${stat}-val`); });
                        tower[stat] = updateSliderValue(`${type}-${stat}`, `${type}-${stat}-val`);
                    }
                });
            });
        }
        
        function updatePathPosition() { if (pathEl) { pathTop = pathEl.offsetTop; pathHeight = pathEl.offsetHeight; } }

        function spawnEnemy() { /* ... Same ... */
            if (pathHeight === 0 && pathTop === 0 && enemies.length < 1) return;
            enemyIdCounter++; const enemyEl = document.createElement('div'); enemyEl.classList.add('enemy'); enemyEl.id = `enemy-${enemyIdCounter}`;
            enemyEl.style.left = `${gameWidth - ENEMY_WIDTH}px`; enemyEl.style.top = `${pathTop + (pathHeight / 2) - (ENEMY_HEIGHT / 2)}px`;
            const healthBar = document.createElement('div'); healthBar.classList.add('enemy-health-bar'); const healthFill = document.createElement('div'); healthFill.classList.add('enemy-health-fill'); healthBar.appendChild(healthFill); enemyEl.appendChild(healthBar);
            if (gameContainer) gameContainer.appendChild(enemyEl);
            enemies.push({ id: enemyIdCounter, el: enemyEl, x: gameWidth - ENEMY_WIDTH, y: pathTop + (pathHeight / 2) - (ENEMY_HEIGHT / 2), health: ENEMY_HEALTH_MAX, maxHealth: ENEMY_HEALTH_MAX, speed: ENEMY_SPEED, healthFillEl: healthFill, age: 0 });
        }
        function initialEnemyFill() { /* ... Same ... */
            if (pathHeight === 0 && pathTop === 0) return;
            const numToFill = Math.floor(gameWidth / (ENEMY_WIDTH + 10));
            for (let i = 0; i < numToFill; i++) {
                const xPos = gameWidth - ENEMY_WIDTH - i * (ENEMY_WIDTH + 10); if (xPos + ENEMY_WIDTH < 0) break;
                enemyIdCounter++; const enemyEl = document.createElement('div'); enemyEl.classList.add('enemy'); enemyEl.id = `enemy-${enemyIdCounter}`;
                enemyEl.style.left = `${xPos}px`; enemyEl.style.top = `${pathTop + (pathHeight / 2) - (ENEMY_HEIGHT / 2)}px`;
                const healthBar = document.createElement('div'); healthBar.classList.add('enemy-health-bar'); const healthFill = document.createElement('div'); healthFill.classList.add('enemy-health-fill'); healthBar.appendChild(healthFill); enemyEl.appendChild(healthBar);
                if (gameContainer) gameContainer.appendChild(enemyEl);
                enemies.push({ id: enemyIdCounter, el: enemyEl, x: xPos, y: pathTop + (pathHeight / 2) - (ENEMY_HEIGHT / 2), health: ENEMY_HEALTH_MAX, maxHealth: ENEMY_HEALTH_MAX, speed: ENEMY_SPEED, healthFillEl: healthFill, age: 0 });
            }
        }
        function updateEnemies(currentTime) { /* ... Same ... */
            if (currentTime - lastEnemySpawnTime > ENEMY_SPAWN_INTERVAL) { spawnEnemy(); lastEnemySpawnTime = currentTime; }
            for (let i = enemies.length - 1; i >= 0; i--) {
                const enemy = enemies[i]; enemy.x -= enemy.speed; enemy.age++; enemy.el.style.left = `${enemy.x}px`;
                const healthPercentage = (enemy.health / enemy.maxHealth) * 100; enemy.healthFillEl.style.width = `${Math.max(0, healthPercentage)}%`;
                if (enemy.health <= 0) { enemy.el.remove(); enemies.splice(i, 1); continue; }
                if (enemy.x + ENEMY_WIDTH < 0) { enemy.el.remove(); enemies.splice(i, 1); }
            }
        }
        function findTarget(tower) { /* ... Same ... */
            let closestEnemy = null; let minDistance = tower.range;
            if (pathHeight === 0 && pathTop === 0 && enemies.length > 0) return null;
            for (const enemy of enemies) {
                const enemyCenterX = enemy.x + ENEMY_WIDTH / 2; const enemyCenterY = enemy.y + ENEMY_HEIGHT / 2;
                if (enemyCenterY > pathTop - pathHeight / 2 && enemyCenterY < pathTop + pathHeight * 1.5) {
                     const distance = Math.sqrt(Math.pow(tower.x - enemyCenterX, 2) + Math.pow(tower.y - enemyCenterY, 2));
                    if (distance < minDistance) { minDistance = distance; closestEnemy = enemy; }
                }
            }
            return closestEnemy;
        }

        function createVisualEffect(x, y, type, angleRad = 0) { /* ... Same ... */
            effectIdCounter++; const effectEl = document.createElement('div');
            effectEl.id = `effect-${effectIdCounter}`; effectEl.classList.add('visual-effect');
            let duration = 400;
            if (type === 'explosion') { effectEl.classList.add('explosion'); duration = 400; }
            else if (type === 'ice_shatter') { effectEl.classList.add('ice-shatter'); duration = 300; }
            else if (type === 'lightning_impact') { effectEl.classList.add('lightning-impact-flash'); duration = 200;}
            else if (type === 'muzzle_puff') { effectEl.classList.add('muzzle-puff'); duration = 400; }
            else if (type === 'muzzle_flash') { effectEl.classList.add('muzzle-flash'); duration = 150; }
            effectEl.style.left = `${x}px`; effectEl.style.top = `${y}px`;
            if (gameContainer) gameContainer.appendChild(effectEl);
            setTimeout(() => effectEl.remove(), duration);
        }

        function createLightningBoltVisual(tower, target) { /* ... Same ... */
            const numSegments = 5; const originX = tower.x; const originY = tower.y;
            let currentX = originX; let currentY = originY;
            const targetX = target.x + ENEMY_WIDTH / 2; const targetY = target.y + ENEMY_HEIGHT / 2;
            const totalDx = targetX - originX; const totalDy = targetY - originY;
            for (let i = 0; i < numSegments; i++) {
                effectIdCounter++; const segmentEl = document.createElement('div'); segmentEl.id = `effect-${effectIdCounter}`; segmentEl.classList.add('lightning-strike-segment');
                let nextX, nextY;
                if (i === numSegments - 1) { nextX = targetX; nextY = targetY; }
                else { nextX = originX + totalDx * ((i + 1) / numSegments) + (Math.random() - 0.5) * 30; nextY = originY + totalDy * ((i + 1) / numSegments) + (Math.random() - 0.5) * 30; }
                const dx = nextX - currentX; const dy = nextY - currentY; const length = Math.sqrt(dx * dx + dy * dy); const angle = Math.atan2(dy, dx) * (180 / Math.PI);
                segmentEl.style.width = `${length}px`; segmentEl.style.left = `${currentX}px`; segmentEl.style.top = `${currentY - 1.5}px`; segmentEl.style.transform = `rotate(${angle}deg)`;
                if (gameContainer) gameContainer.appendChild(segmentEl); setTimeout(() => segmentEl.remove(), 150);
                currentX = nextX; currentY = nextY;
            }
            createVisualEffect(targetX, targetY, 'lightning_impact');
        }
        function createIceTrailParticle(x, y) { /* ... Same ... */ }

        function fireRecoil(tower) { /* ... Same ... */
            if (tower.modelEl && tower.recoilClass) {
                const muzzleX = tower.x + tower.barrelLength * Math.cos(tower.currentAngleRad);
                const muzzleY = tower.y + tower.barrelLength * Math.sin(tower.currentAngleRad);
                if (tower.recoilClass === 'recoil-active' && tower.pivotEl) {
                    tower.modelEl.style.setProperty('--recoil-amount', `${-globalRecoilMagnitude}px`);
                    tower.modelEl.style.animationDuration = `${globalRecoilDuration}ms`;
                    tower.pivotEl.classList.add(tower.recoilClass);
                } else if (tower.recoilClass === 'recoil-lightning' && tower.pivotEl) {
                     tower.pivotEl.classList.add(tower.recoilClass);
                }
                if (tower.type === 'rocket' || tower.type === 'ice') {
                    createVisualEffect(muzzleX, muzzleY, 'muzzle_flash', tower.currentAngleRad);
                    setTimeout(() => createVisualEffect(muzzleX + Math.cos(tower.currentAngleRad)*2, muzzleY + Math.sin(tower.currentAngleRad)*2, 'muzzle_puff'), 50);
                }
                if(tower.type === 'ice' && tower.modelEl) tower.modelEl.classList.add('firing');
                setTimeout(() => {
                    if(tower.pivotEl) tower.pivotEl.classList.remove(tower.recoilClass);
                    if(tower.type === 'ice' && tower.modelEl) tower.modelEl.classList.remove('firing');
                }, globalRecoilDuration + 50);
            }
        }

        function launchProjectile(tower, target) { /* ... Same, includes playSound ... */
            try {
                projectileIdCounter++; const projectileEl = document.createElement('div');
                projectileEl.classList.add('projectile', tower.projectileClass); projectileEl.id = `proj-${projectileIdCounter}`;
                const startX = tower.x + tower.barrelLength * Math.cos(tower.currentAngleRad);
                const startY = tower.y + tower.barrelLength * Math.sin(tower.currentAngleRad);
                projectileEl.style.left = `0px`; projectileEl.style.top = `0px`; projectileEl.style.visibility = 'hidden';
                if (tower.type === 'rocket') { const flameEl = document.createElement('div'); flameEl.classList.add('rocket-flame'); projectileEl.appendChild(flameEl); }
                else if (tower.type === 'ice') { const crystalInnerDiv = document.createElement('div'); projectileEl.appendChild(crystalInnerDiv); }
                if (gameContainer) gameContainer.appendChild(projectileEl);
                projectileEl.style.left = `${startX - projectileEl.offsetWidth / 2}px`;
                projectileEl.style.top = `${startY - projectileEl.offsetHeight / 2}px`;
                projectileEl.style.visibility = 'visible';
                projectiles.push({ id: projectileIdCounter, el: projectileEl, x: startX, y: startY, damage: tower.damage, speed: tower.projectileSpeed, target: target, towerType: tower.type, currentAngle: (tower.type === 'rocket' ? tower.currentAngleRad : Math.atan2(target.y + ENEMY_HEIGHT/2 - startY, target.x + ENEMY_WIDTH/2 - startX)), age: 0 });
                if (tower.type === 'rocket') playSound('rocket_fire'); else if (tower.type === 'ice') playSound('ice_fire');
                tower.lastShotTime = performance.now(); tower.chargeLevel = 0;
                if(tower.glowEl) { tower.glowEl.style.opacity = 0; tower.glowEl.style.boxShadow = '0 0 0px 0px transparent'; }
                fireRecoil(tower);
            } catch (e) { console.error(`Error in launchProjectile for ${tower.id}:`, e); }
        }

        function updateTowers(currentTime) { /* ... Same, includes playSound for lightning ... */
            towers.forEach(tower => {
                if (tower.fireRate > 0) {
                    if (tower.lastShotTime > 0) { tower.chargeLevel = Math.min(1, (currentTime - tower.lastShotTime) / tower.fireRate); }
                    else if (tower.initialChargeTime > 0) { tower.chargeLevel = Math.min(1, (currentTime - tower.initialChargeTime) / tower.fireRate); }
                    else { tower.chargeLevel = 0; }
                } else { tower.chargeLevel = 1; }
                if (tower.glowEl) {
                    tower.glowEl.style.opacity = tower.chargeLevel * 0.8;
                    const hue = 60 - (tower.chargeLevel * 60);
                    const spread = tower.chargeLevel * 8; const blur = tower.chargeLevel * 15;
                    tower.glowEl.style.boxShadow = `0 0 ${blur}px ${spread}px hsla(${hue}, 100%, 50%, 0.7)`;
                }
                const target = findTarget(tower);
                if (target) {
                    const targetX = target.x + ENEMY_WIDTH / 2; const targetY = target.y + ENEMY_HEIGHT / 2;
                    const angleToTargetRad = Math.atan2(targetY - tower.y, targetX - tower.x);
                    tower.currentAngleRad = angleToTargetRad;
                    if (tower.pivotEl) tower.pivotEl.style.transform = `rotate(${angleToTargetRad - Math.PI/2}rad)`;
                    if (tower.chargeLevel >= 1.0) {
                        if (tower.type === 'lightning') {
                            try {
                                target.health -= tower.damage; createLightningBoltVisual(tower, target); playSound('lightning_fire');
                                tower.lastShotTime = performance.now(); tower.chargeLevel = 0;
                                if(tower.glowEl) { tower.glowEl.style.opacity = 0; tower.glowEl.style.boxShadow = '0 0 0px 0px transparent'; }
                                fireRecoil(tower);
                            } catch (e) { console.error(`Error in lightning fire for ${tower.id}:`, e); }
                        } else { launchProjectile(tower, target); }
                    }
                }
            });
        }

        function updateProjectiles() { /* ... Same, includes playSound for impacts ... */
             for (let i = projectiles.length - 1; i >= 0; i--) {
                const proj = projectiles[i]; proj.age++; const targetEnemy = proj.target;
                if (!proj.el || !targetEnemy || targetEnemy.health <= 0 || !enemies.includes(targetEnemy)) { if(proj.el) proj.el.remove(); projectiles.splice(i, 1); continue; }
                const targetX = targetEnemy.x + ENEMY_WIDTH / 2; const targetY = targetEnemy.y + ENEMY_HEIGHT / 2;
                let dx = targetX - proj.x; let dy = targetY - proj.y; const distance = Math.sqrt(dx * dx + dy * dy);
                if (distance < Math.max(proj.speed, ENEMY_WIDTH / 2.5)) {
                    targetEnemy.health -= proj.damage;
                    if (proj.towerType === 'rocket') { createVisualEffect(targetX, targetY, 'explosion'); playSound('rocket_impact'); }
                    else if (proj.towerType === 'ice') { createVisualEffect(targetX, targetY, 'ice_shatter'); playSound('ice_impact'); }
                    proj.el.remove(); projectiles.splice(i, 1); continue;
                }
                let moveX, moveY;
                if (proj.towerType === 'rocket') {
                    const angleToTarget = Math.atan2(dy, dx); let angleDifference = angleToTarget - proj.currentAngle;
                    while (angleDifference > Math.PI) angleDifference -= 2 * Math.PI; while (angleDifference < -Math.PI) angleDifference += 2 * Math.PI;
                    const turnAmount = Math.sign(angleDifference) * Math.min(Math.abs(angleDifference), ROCKET_TURN_RATE * (proj.speed / 1.5));
                    proj.currentAngle += turnAmount; moveX = Math.cos(proj.currentAngle) * proj.speed; moveY = Math.sin(proj.currentAngle) * proj.speed;
                    const wobbleAngle = proj.currentAngle + Math.PI / 2;
                    moveX += Math.cos(wobbleAngle) * ROCKET_WOBBLE_AMPLITUDE * Math.sin(proj.age * ROCKET_WOBBLE_FREQUENCY) * 0.1;
                    moveY += Math.sin(wobbleAngle) * ROCKET_WOBBLE_AMPLITUDE * Math.sin(proj.age * ROCKET_WOBBLE_FREQUENCY) * 0.1;
                    proj.el.style.transform = `rotate(${proj.currentAngle}rad)`;
                } else {
                    moveX = (dx / distance) * proj.speed; moveY = (dy / distance) * proj.speed;
                    if (proj.towerType === 'ice' && proj.age % 4 === 0) { /* createIceTrailParticle(proj.x, proj.y); */ }
                }
                proj.x += moveX; proj.y += moveY;
                proj.el.style.left = `${proj.x - proj.el.offsetWidth/2}px`;
                proj.el.style.top = `${proj.y - proj.el.offsetHeight/2}px`;
                if (proj.x < -50 || proj.x > gameWidth + 50 || proj.y < -50 || proj.y > gameContainer.offsetHeight + 100) { proj.el.remove(); projectiles.splice(i, 1); }
            }
        }

        function gameLoop(timestamp) {
            updateEnemies(timestamp); updateTowers(timestamp); updateProjectiles(); requestAnimationFrame(gameLoop);
        }

        document.addEventListener('DOMContentLoaded', () => {
            updatePathPosition(); setupControls();

            const userGestureEvents = ['click', 'touchstart', 'keydown'];
            function handleUserGesture() {
                if (!userHasInteracted) {
                    userHasInteracted = true;
                    // console.log("User interaction detected. AudioContext can now be initialized on demand.");
                    // Try to initialize/resume AudioContext immediately after first interaction
                    ensureAudioContext();
                    userGestureEvents.forEach(event => document.body.removeEventListener(event, handleUserGesture));
                }
            }
            userGestureEvents.forEach(event => {
                document.body.addEventListener(event, handleUserGesture, { once: true });
            });

            initialEnemyFill(); lastEnemySpawnTime = performance.now();
            const initialTime = performance.now();
            towers.forEach(tower => { tower.lastShotTime = 0; tower.initialChargeTime = initialTime; });
            requestAnimationFrame(gameLoop);
        });
    </script>
</body>
</html>