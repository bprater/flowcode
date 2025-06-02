<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TD Simulator - Modular Turret System</title>
    <style>
        body { 
            font-family: sans-serif; display: flex; flex-direction: column; 
            align-items: center; margin: 0; background-color: #e0e0e0; 
            padding-bottom: 70px; 
        }
        .pause-button-style { 
            padding: 10px 20px; font-size: 16px; cursor: pointer; 
            background-color: #4CAF50; color: white; border: none; 
            border-radius: 5px; margin: 0 5px; 
        }
        .pause-button-style.paused { background-color: #f44336; }
        
        #bottom-pause-bar { 
            position: fixed; bottom: 0; left: 0; width: 100%; 
            background-color: rgba(200, 200, 200, 0.9); padding: 10px 0; 
            display: flex; justify-content: center; z-index: 200; 
            box-shadow: 0 -2px 5px rgba(0,0,0,0.2); 
        }

        #game-container { 
            position: relative; width: 800px; height: 450px; 
            background-color: #5DAD36; 
            background-image: linear-gradient(to bottom, #76C84D 0%, #5DAD36 60%, #488A29 100%); 
            border: 2px solid #333; overflow: hidden; margin-top:10px; 
        }
        #path { 
            position: absolute; bottom: 30px; left: 0; width: 100%; height: 60px; 
            background-color: #b08d57; border-top: 1px dashed #8a6d40; 
            border-bottom: 1px dashed #8a6d40; 
        }

        .enemy { 
            position: absolute; width: 30px; height: 30px; 
            background-color: #2ecc71; border: 1px solid #27ae60; 
            border-radius: 5px; display: flex; flex-direction: column; 
            align-items: center; justify-content: center; box-sizing: border-box; 
            font-size: 10px; color: white; transition: filter 0.1s; 
        }
        .enemy-health-bar { 
            width: 90%; height: 5px; background-color: #e74c3c; 
            border: 1px solid #c0392b; margin-top: 2px; 
        }
        .enemy-health-fill { width: 100%; height: 100%; background-color: #2ecc71; }

        #controls { 
            display: flex; flex-wrap: wrap; justify-content: space-around; 
            width: 800px; margin-top: 10px; padding: 10px; 
            background-color: #e0e0e0; border-radius: 5px; 
        }
        .control-group { 
            border: 1px solid #ccc; padding: 10px; border-radius: 5px; 
            background-color: #f9f9f9; margin-bottom: 10px; 
            width: calc(25% - 20px); 
        }
        .control-group h3 { margin-top: 0; text-align: center; }
        .control-group label { 
            display: block; margin: 5px 0 2px; font-size: 0.9em; 
        }
        .control-group input[type="range"] { width: 100%; box-sizing: border-box; }
        .control-group span { font-size: 0.8em; color: #555; }
        
        .global-controls { 
            width: 100%; margin-bottom: 15px; padding: 10px; 
            background-color: #d0d0d0; border-radius: 5px; text-align: center; 
        }
        .global-controls label { margin: 0 10px; }
        
        .damage-meter { 
            margin-top: 10px; padding: 8px; background-color: #2c3e50; 
            border-radius: 4px; color: #ecf0f1; text-align: center; 
            border: 1px solid #34495e; box-shadow: inset 0 1px 3px rgba(0,0,0,0.3);
        }
        .damage-meter .damage-label { 
            font-size: 0.7em; color: #bdc3c7; margin-bottom: 2px; 
        }
        .damage-meter .damage-value { 
            font-size: 1.1em; font-weight: bold; color: #e74c3c; 
            text-shadow: 0 0 3px rgba(231, 76, 60, 0.5);
        }

        /* Tower base styles */
        #tower-rocket-base { 
            background: linear-gradient(135deg, #A0522D 0%, #8B4513 100%); 
        }
        #tower-rocket-base::before { 
            content: ''; position: absolute; width: 8px; height: 8px; 
            background: #696969; border-radius: 50%; 
            box-shadow: 15px 0 #696969, -15px 0 #696969, 0 15px #696969, 0 -15px #696969; 
        }
        
        #tower-ice-base { 
            background: linear-gradient(180deg, #C0E0FF 0%, #A0C0FF 100%); 
            box-shadow: 0 0 15px 5px rgba(173, 216, 230, 0.6), inset 0 0 10px rgba(255,255,255,0.5); 
            border: 2px solid #ADD8E6; 
        }
        #tower-ice-base::before, #tower-ice-base::after { 
            content: ''; position: absolute; width: 0; height: 0; 
            border-left: 8px solid transparent; border-right: 8px solid transparent; 
            border-bottom: 15px solid rgba(200, 230, 255, 0.7); opacity: 0.8; 
        }
        #tower-ice-base::before { transform: rotate(30deg) translate(18px, -15px); }
        #tower-ice-base::after { transform: rotate(-40deg) translate(-15px, -18px) scaleX(-1); }
        
        #tower-lightning-base { 
            background: linear-gradient(135deg, #404058 0%, #202030 100%); 
            border: 2px solid #606070; 
            box-shadow: 0 3px 6px rgba(0,0,0,0.5), inset 0 2px 4px rgba(0,0,0,0.3), 0 0 8px #7DF9FF; 
            animation: subtle-pulse-base-lightning 4s infinite alternate ease-in-out; 
        }
        #tower-lightning-base::after, #tower-lightning-base::before { 
            content: ''; position: absolute; width: 5px; height: 110%; 
            background: linear-gradient(to bottom, rgba(125, 249, 255, 0.2), rgba(0, 191, 255, 0.4)); 
            border-radius: 3px; box-shadow: 0 0 2px #00BFFF, 0 0 4px #7DF9FF; opacity: 0.6; 
        }
        #tower-lightning-base::after { transform: rotate(35deg); }
        #tower-lightning-base::before { transform: rotate(-35deg); }
        @keyframes subtle-pulse-base-lightning { 
            0% { box-shadow: 0 3px 6px rgba(0,0,0,0.5), inset 0 2px 4px rgba(0,0,0,0.3), 0 0 8px #7DF9FF; } 
            100% { box-shadow: 0 3px 6px rgba(0,0,0,0.5), inset 0 2px 4px rgba(0,0,0,0.3), 0 0 20px #00FFFF; } 
        }
        
        #tower-machinegun-base { 
            background: linear-gradient(135deg, #556B2F 0%, #2F4F2F 100%); 
            border: 2px solid #1C3A1C; 
        }
        #tower-machinegun-base::before { 
            content: ''; position: absolute; width: 10px; height: 10px; 
            background: #8B4513; border-radius: 50%; 
            box-shadow: 12px 0 #654321, -12px 0 #654321, 0 12px #654321, 0 -12px #654321; 
        }
        
        /* Dynamic turret styles will be injected here */
        <TURRET_STYLES>
    </style>
</head>
<body>
    <h1>TD Simulator - Modular Turret System</h1>
    <div id="game-container">
        <div id="path"></div>
        <!-- Turret bases will be dynamically inserted here -->
        <TURRET_HTML>
    </div>
    
    <div id="controls">
        <div class="global-controls">
            <h3>Global Recoil Settings</h3>
            <label for="recoil-magnitude">Magnitude (px): <span id="recoil-magnitude-val">10</span></label>
            <input type="range" id="recoil-magnitude" min="0" max="20" value="10" step="1">
            <label for="recoil-duration">Duration (ms): <span id="recoil-duration-val">150</span></label>
            <input type="range" id="recoil-duration" min="50" max="500" value="150" step="10">
        </div>
        <!-- Turret controls will be dynamically inserted here -->
        <TURRET_CONTROLS>
    </div>
    
    <div id="bottom-pause-bar">
        <button id="pause-button-bottom" class="pause-button-style">Pause</button>
    </div>

    <!-- Load turret modules -->
    <script src="turrets/base-turret.js"></script>
    <script src="turrets/rocket-turret.js"></script>
    <script src="turrets/ice-turret.js"></script>
    <script src="turrets/lightning-turret.js"></script>
    <script src="turrets/machinegun-turret.js"></script>
    <script src="turrets/turret-manager.js"></script>

    <script>
        // Game initialization
        const gameContainer = document.getElementById('game-container');
        const pathEl = document.getElementById('path');
        const pauseButtonBottom = document.getElementById('pause-button-bottom');
        
        let pathTop = 0; 
        let pathHeight = 0;
        const gameWidth = gameContainer.offsetWidth;
        const gameHeight = gameContainer.offsetHeight;
        
        let enemies = [];
        let enemyIdCounter = 0;
        let globalRecoilMagnitude = 10;
        let globalRecoilDuration = 150;
        
        const ENEMY_HEALTH_MAX = 300;
        const ENEMY_SPEED = 0.7;
        const ENEMY_WIDTH = 30;
        const ENEMY_HEIGHT = 30;
        const ENEMY_SPAWN_INTERVAL = 2000;
        let lastEnemySpawnTime = 0;
        
        let audioCtx = null;
        let userHasInteracted = false;
        let isPaused = false;
        let animationFrameId;
        let lastTimestamp = 0;
        
        // Initialize turret manager
        const turretManager = new TurretManager();
        
        // Register turret types
        turretManager.registerTurretType('rocket', RocketTurret);
        turretManager.registerTurretType('ice', IceTurret);
        turretManager.registerTurretType('lightning', LightningTurret);
        turretManager.registerTurretType('machinegun', MachineGunTurret);
        
        // Create turret instances
        const rocketTurret = turretManager.createTurret('rocket', { id: 'tower-rocket' });
        const iceTurret = turretManager.createTurret('ice', { id: 'tower-ice' });
        const lightningTurret = turretManager.createTurret('lightning', { id: 'tower-lightning' });
        const machinegunTurret = turretManager.createTurret('machinegun', { id: 'tower-machinegun' });
        
        // Game configuration object
        const gameConfig = {
            gameContainer: gameContainer,
            gameWidth: gameWidth,
            gameHeight: gameHeight,
            enemies: enemies,
            isPaused: () => isPaused,
            globalRecoilMagnitude: () => globalRecoilMagnitude,
            globalRecoilDuration: () => globalRecoilDuration,
            getNextProjectileId: () => turretManager.getNextProjectileId(),
            getNextEffectId: () => turretManager.getNextEffectId(),
            addProjectile: (projectile) => turretManager.addProjectile(projectile),
            createVisualEffect: createVisualEffect,
            playSound: playSound
        };
        
        // Audio system
        function ensureAudioContext() {
            if (audioCtx && audioCtx.state === 'running') return true;
            if (!userHasInteracted) return false;
            if (!audioCtx) {
                try { 
                    audioCtx = new (window.AudioContext || window.webkitAudioContext)(); 
                } catch (e) { 
                    console.error("Failed to create AudioContext:", e); 
                    return false; 
                }
            }
            if (audioCtx.state === 'suspended') {
                audioCtx.resume().catch(e => console.error("Failed to resume AudioContext:", e));
                return false;
            }
            return audioCtx.state === 'running';
        }
        
        function playSound(type) {
            if (!ensureAudioContext()) return;
            actuallyPlaySound(type);
        }
        
        function actuallyPlaySound(type) {
            if (!audioCtx) return;
            let osc, gain, dur, freq, attack, decay, noiseSource, buffer, output, filter;
            const now = audioCtx.currentTime;
            
            switch (type) {
                case 'rocket_fire':
                    // Rocket launch sound
                    osc = audioCtx.createOscillator();
                    gain = audioCtx.createGain();
                    osc.connect(gain);
                    gain.connect(audioCtx.destination);
                    osc.type = 'sawtooth';
                    freq = 60;
                    dur = 0.6;
                    attack = 0.05;
                    decay = 0.5;
                    osc.frequency.setValueAtTime(freq, now);
                    osc.frequency.exponentialRampToValueAtTime(freq * 0.7, now + dur);
                    gain.gain.setValueAtTime(0, now);
                    gain.gain.linearRampToValueAtTime(0.15, now + attack);
                    gain.gain.exponentialRampToValueAtTime(0.001, now + dur);
                    osc.start(now);
                    osc.stop(now + dur);
                    break;
                    
                case 'rocket_impact':
                    // Explosion sound
                    dur = 0.8;
                    noiseSource = audioCtx.createBufferSource();
                    buffer = audioCtx.createBuffer(1, audioCtx.sampleRate * dur, audioCtx.sampleRate);
                    output = buffer.getChannelData(0);
                    for (let i = 0; i < buffer.length; i++) {
                        output[i] = Math.random() * 2 - 1;
                    }
                    noiseSource.buffer = buffer;
                    filter = audioCtx.createBiquadFilter();
                    filter.type = "bandpass";
                    filter.frequency.setValueAtTime(600, now);
                    filter.frequency.exponentialRampToValueAtTime(150, now + 0.7);
                    filter.Q.value = 2;
                    gain = audioCtx.createGain();
                    noiseSource.connect(filter);
                    filter.connect(gain);
                    gain.connect(audioCtx.destination);
                    gain.gain.setValueAtTime(0, now);
                    gain.gain.linearRampToValueAtTime(0.6, now + 0.01);
                    gain.gain.exponentialRampToValueAtTime(0.001, now + 0.7);
                    noiseSource.start(now);
                    noiseSource.stop(now + dur);
                    break;
                    
                case 'ice_fire':
                    // Ice crystal sound
                    osc = audioCtx.createOscillator();
                    gain = audioCtx.createGain();
                    osc.type = 'sine';
                    freq = 1200;
                    dur = 0.3;
                    osc.frequency.setValueAtTime(freq, now);
                    osc.frequency.exponentialRampToValueAtTime(freq * 1.8, now + dur * 0.3);
                    osc.frequency.exponentialRampToValueAtTime(freq * 0.8, now + dur);
                    gain.gain.setValueAtTime(0, now);
                    gain.gain.linearRampToValueAtTime(0.15, now + 0.01);
                    gain.gain.exponentialRampToValueAtTime(0.001, now + dur);
                    osc.connect(gain);
                    gain.connect(audioCtx.destination);
                    osc.start(now);
                    osc.stop(now + dur);
                    break;
                    
                case 'ice_impact':
                    // Ice shatter sound
                    noiseSource = audioCtx.createBufferSource();
                    buffer = audioCtx.createBuffer(1, audioCtx.sampleRate * 0.1, audioCtx.sampleRate);
                    output = buffer.getChannelData(0);
                    for (let i = 0; i < buffer.length; i++) {
                        output[i] = Math.random() * 2 - 1;
                    }
                    noiseSource.buffer = buffer;
                    filter = audioCtx.createBiquadFilter();
                    filter.type = "highpass";
                    filter.frequency.value = 2500;
                    gain = audioCtx.createGain();
                    gain.gain.setValueAtTime(0, now);
                    gain.gain.linearRampToValueAtTime(0.3, now + 0.005);
                    gain.gain.exponentialRampToValueAtTime(0.001, now + 0.08);
                    noiseSource.connect(filter);
                    filter.connect(gain);
                    gain.connect(audioCtx.destination);
                    noiseSource.start(now);
                    noiseSource.stop(now + 0.1);
                    break;
                    
                case 'lightning_fire':
                    // Lightning zap
                    noiseSource = audioCtx.createBufferSource();
                    buffer = audioCtx.createBuffer(1, audioCtx.sampleRate * 0.1, audioCtx.sampleRate);
                    output = buffer.getChannelData(0);
                    for (let i = 0; i < buffer.length; i++) {
                        output[i] = Math.random() * 2 - 1;
                    }
                    noiseSource.buffer = buffer;
                    filter = audioCtx.createBiquadFilter();
                    filter.type = "bandpass";
                    filter.frequency.setValueAtTime(2000 + Math.random() * 1500, now);
                    filter.Q.value = 30 + Math.random() * 20;
                    gain = audioCtx.createGain();
                    noiseSource.connect(filter);
                    filter.connect(gain);
                    gain.connect(audioCtx.destination);
                    dur = 0.1;
                    gain.gain.setValueAtTime(0, now);
                    gain.gain.linearRampToValueAtTime(0.2, now + 0.002);
                    gain.gain.exponentialRampToValueAtTime(0.001, now + dur);
                    noiseSource.start(now);
                    noiseSource.stop(now + dur);
                    break;
                    
                case 'lightning_impact':
                    // Lightning impact
                    noiseSource = audioCtx.createBufferSource();
                    buffer = audioCtx.createBuffer(1, audioCtx.sampleRate * 0.05, audioCtx.sampleRate);
                    output = buffer.getChannelData(0);
                    for (let i = 0; i < buffer.length; i++) {
                        output[i] = (Math.random() * 2 - 1) * 0.8;
                    }
                    noiseSource.buffer = buffer;
                    filter = audioCtx.createBiquadFilter();
                    filter.type = "highpass";
                    filter.frequency.setValueAtTime(3000, now);
                    filter.Q.value = 5;
                    gain = audioCtx.createGain();
                    gain.gain.setValueAtTime(0, now);
                    gain.gain.linearRampToValueAtTime(0.35, now + 0.002);
                    gain.gain.exponentialRampToValueAtTime(0.001, now + 0.05);
                    noiseSource.connect(filter);
                    filter.connect(gain);
                    gain.connect(audioCtx.destination);
                    noiseSource.start(now);
                    noiseSource.stop(now + 0.05);
                    break;
                    
                case 'machinegun_fire':
                    // Machine gun bullet
                    osc = audioCtx.createOscillator();
                    gain = audioCtx.createGain();
                    osc.type = 'square';
                    freq = 800 + Math.random() * 400;
                    dur = 0.08;
                    osc.frequency.setValueAtTime(freq, now);
                    osc.frequency.exponentialRampToValueAtTime(freq * 0.5, now + dur);
                    gain.gain.setValueAtTime(0, now);
                    gain.gain.linearRampToValueAtTime(0.1, now + 0.01);
                    gain.gain.exponentialRampToValueAtTime(0.001, now + dur);
                    osc.connect(gain);
                    gain.connect(audioCtx.destination);
                    osc.start(now);
                    osc.stop(now + dur);
                    break;
                    
                case 'bullet_impact':
                    // Small bullet impact
                    osc = audioCtx.createOscillator();
                    gain = audioCtx.createGain();
                    osc.type = 'triangle';
                    freq = 600 + Math.random() * 200;
                    dur = 0.05;
                    osc.frequency.setValueAtTime(freq, now);
                    gain.gain.setValueAtTime(0, now);
                    gain.gain.linearRampToValueAtTime(0.08, now + 0.005);
                    gain.gain.exponentialRampToValueAtTime(0.001, now + dur);
                    osc.connect(gain);
                    gain.connect(audioCtx.destination);
                    osc.start(now);
                    osc.stop(now + dur);
                    break;
                    
                default:
                    console.warn("Unknown sound type:", type);
            }
        }
        
        // Visual effects system
        let effectIdCounter = 0;
        
        function createVisualEffect(x, y, type, options = {}) {
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
                    gameContainer.appendChild(particleEl);
                    setTimeout(() => particleEl.remove(), 400);
                }
                return;
            }
            
            effectIdCounter++;
            effectEl = document.createElement('div');
            effectEl.id = `effect-${effectIdCounter}`;
            effectEl.classList.add('visual-effect');
            
            if (type === 'explosion') { 
                effectEl.classList.add('explosion'); 
                duration = 500; 
            } else if (type === 'ice_shatter') { 
                effectEl.classList.add('ice-shatter'); 
                duration = 400; 
            } else if (type === 'ice_splash') { 
                effectEl.classList.add('ice-splash-effect'); 
                effectEl.style.setProperty('--splash-radius', `${options.radius * 2}px`); 
                duration = 300; 
            } else if (type === 'lightning_impact') { 
                effectEl.classList.add('lightning-impact-flash'); 
                duration = 200;
            } else if (type === 'muzzle_puff') { 
                effectEl.classList.add('muzzle-puff'); 
                duration = 400; 
            } else if (type === 'muzzle_flash') { 
                effectEl.classList.add('muzzle-flash'); 
                duration = 150; 
            } else if (type === 'muzzle_flash_lightning') { 
                effectEl.classList.add('muzzle-flash-lightning'); 
                duration = 100; 
                if (options.angle) {
                    effectEl.style.transform = `translate(-50%, -80%) rotate(${options.angle * 180 / Math.PI + 90}deg)`;
                }
            } else if (type === 'muzzle_flash_rapid') { 
                effectEl.classList.add('muzzle-flash-rapid'); 
                duration = 80; 
                if (options.angle) {
                    effectEl.style.transform = `translate(-50%, -60%) rotate(${options.angle * 180 / Math.PI + 90}deg)`;
                }
            } else if (type === 'rocket_puff_trail') { 
                effectEl.classList.add('rocket-puff'); 
                duration = 800; 
            } else if (type === 'bullet_impact') { 
                effectEl.classList.add('bullet-impact'); 
                duration = 200; 
            }
            
            if (effectEl) {
                effectEl.style.left = `${x}px`;
                effectEl.style.top = `${y}px`;
                gameContainer.appendChild(effectEl);
                setTimeout(() => effectEl.remove(), duration);
            }
        }
        
        // Enemy management
        function updatePathPosition() {
            if (pathEl) {
                pathTop = pathEl.offsetTop;
                pathHeight = pathEl.offsetHeight;
            }
        }
        
        function spawnEnemy() {
            if (pathHeight === 0 && pathTop === 0 && enemies.length < 1) return;
            
            enemyIdCounter++;
            const enemyEl = document.createElement('div');
            enemyEl.classList.add('enemy');
            enemyEl.id = `enemy-${enemyIdCounter}`;
            enemyEl.style.left = `${gameWidth - ENEMY_WIDTH}px`;
            enemyEl.style.top = `${pathTop + (pathHeight / 2) - (ENEMY_HEIGHT / 2)}px`;
            
            const healthBar = document.createElement('div');
            healthBar.classList.add('enemy-health-bar');
            const healthFill = document.createElement('div');
            healthFill.classList.add('enemy-health-fill');
            healthBar.appendChild(healthFill);
            enemyEl.appendChild(healthBar);
            
            gameContainer.appendChild(enemyEl);
            
            enemies.push({
                id: enemyIdCounter,
                el: enemyEl,
                x: gameWidth - ENEMY_WIDTH,
                y: pathTop + (pathHeight / 2) - (ENEMY_HEIGHT / 2),
                health: ENEMY_HEALTH_MAX,
                maxHealth: ENEMY_HEALTH_MAX,
                speed: ENEMY_SPEED,
                healthFillEl: healthFill,
                age: 0,
                width: ENEMY_WIDTH,
                height: ENEMY_HEIGHT
            });
        }
        
        function initialEnemyFill() {
            if (pathHeight === 0 && pathTop === 0) return;
            
            const numToFill = Math.floor(gameWidth / (ENEMY_WIDTH + 10));
            for (let i = 0; i < numToFill; i++) {
                const xPos = gameWidth - ENEMY_WIDTH - i * (ENEMY_WIDTH + 10);
                if (xPos + ENEMY_WIDTH < 0) break;
                
                enemyIdCounter++;
                const enemyEl = document.createElement('div');
                enemyEl.classList.add('enemy');
                enemyEl.id = `enemy-${enemyIdCounter}`;
                enemyEl.style.left = `${xPos}px`;
                enemyEl.style.top = `${pathTop + (pathHeight / 2) - (ENEMY_HEIGHT / 2)}px`;
                
                const healthBar = document.createElement('div');
                healthBar.classList.add('enemy-health-bar');
                const healthFill = document.createElement('div');
                healthFill.classList.add('enemy-health-fill');
                healthBar.appendChild(healthFill);
                enemyEl.appendChild(healthBar);
                
                gameContainer.appendChild(enemyEl);
                
                enemies.push({
                    id: enemyIdCounter,
                    el: enemyEl,
                    x: xPos,
                    y: pathTop + (pathHeight / 2) - (ENEMY_HEIGHT / 2),
                    health: ENEMY_HEALTH_MAX,
                    maxHealth: ENEMY_HEALTH_MAX,
                    speed: ENEMY_SPEED,
                    healthFillEl: healthFill,
                    age: 0,
                    width: ENEMY_WIDTH,
                    height: ENEMY_HEIGHT
                });
            }
        }
        
        function updateEnemies(currentTime, deltaTime) {
            if (isPaused) return;
            
            const actualDeltaFactor = deltaTime / (1000/60);
            
            if (currentTime - lastEnemySpawnTime > ENEMY_SPAWN_INTERVAL) {
                spawnEnemy();
                lastEnemySpawnTime = currentTime;
            }
            
            for (let i = enemies.length - 1; i >= 0; i--) {
                const enemy = enemies[i];
                enemy.x -= enemy.speed * actualDeltaFactor;
                enemy.age++;
                enemy.el.style.left = `${enemy.x}px`;
                
                const healthPercentage = (enemy.health / enemy.maxHealth) * 100;
                enemy.healthFillEl.style.width = `${Math.max(0, healthPercentage)}%`;
                
                if (enemy.health <= 0) {
                    enemy.el.remove();
                    enemies.splice(i, 1);
                    continue;
                }
                
                if (enemy.x + ENEMY_WIDTH < 0) {
                    enemy.el.remove();
                    enemies.splice(i, 1);
                }
            }
        }
        
        // Game loop
        function gameLoop(timestamp) {
            if (!lastTimestamp) lastTimestamp = timestamp;
            const deltaTime = timestamp - lastTimestamp;
            lastTimestamp = timestamp;
            
            if (!isPaused) {
                updateEnemies(timestamp, deltaTime);
                turretManager.updateTurrets(timestamp, deltaTime, enemies, gameConfig);
                turretManager.updateProjectiles(deltaTime, gameConfig);
            }
            
            animationFrameId = requestAnimationFrame(gameLoop);
        }
        
        // Control system
        function setupControls() {
            const recoilMagSlider = document.getElementById('recoil-magnitude');
            const recoilMagVal = document.getElementById('recoil-magnitude-val');
            if (recoilMagSlider && recoilMagVal) {
                recoilMagSlider.addEventListener('input', (e) => {
                    globalRecoilMagnitude = parseFloat(e.target.value);
                    recoilMagVal.textContent = e.target.value;
                });
                globalRecoilMagnitude = parseFloat(recoilMagSlider.value);
            }
            
            const recoilDurSlider = document.getElementById('recoil-duration');
            const recoilDurVal = document.getElementById('recoil-duration-val');
            if (recoilDurSlider && recoilDurVal) {
                recoilDurSlider.addEventListener('input', (e) => {
                    globalRecoilDuration = parseFloat(e.target.value);
                    recoilDurVal.textContent = e.target.value;
                });
                globalRecoilDuration = parseFloat(recoilDurSlider.value);
            }
            
            turretManager.setupControls();
        }
        
        function togglePauseState() {
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
        
        // Initialize game
        document.addEventListener('DOMContentLoaded', () => {
            updatePathPosition();
            turretManager.initializeTurrets(gameContainer);
            setupControls();
            
            const userGestureEvents = ['click', 'touchstart', 'keydown', 'mousemove'];
            function handleUserGesture() {
                if (!userHasInteracted) {
                    userHasInteracted = true;
                    ensureAudioContext();
                    userGestureEvents.forEach(event => 
                        document.body.removeEventListener(event, handleUserGesture)
                    );
                }
            }
            userGestureEvents.forEach(event => {
                document.body.addEventListener(event, handleUserGesture, { once: true });
            });
            
            initialEnemyFill();
            lastEnemySpawnTime = performance.now();
            
            const initialTime = performance.now();
            turretManager.activeTurrets.forEach(turret => {
                turret.lastShotTime = 0;
                turret.initialChargeTime = initialTime;
            });
            
            animationFrameId = requestAnimationFrame(gameLoop);
        });
        
        if (pauseButtonBottom) {
            pauseButtonBottom.addEventListener('click', togglePauseState);
        }
    </script>
</body>
</html>