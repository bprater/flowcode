<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pixel Realms Defense - Lively Mobs</title>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: #282c34;
            color: #abb2bf;
            margin: 0;
            overflow: hidden;
        }
        #game-wrapper {
            display: flex;
            flex-direction: column;
            align-items: center;
            border: 3px solid #4b5263;
            padding: 15px;
            background-color: #1e222a;
            box-shadow: 0 0 15px rgba(0,0,0,0.5);
        }
        #gameCanvas {
            border: 2px solid #61afef;
            /* background-color: #3b4048; /* Will be drawn dynamically */
            image-rendering: pixelated;
            image-rendering: -moz-crisp-edges;
            image-rendering: crisp-edges;
            margin-bottom: 15px;
            cursor: pointer;
        }
        #ui-panel {
            width: 100%;
            min-height: 150px;
            padding: 10px;
            background-color: #2c313a;
            border-radius: 5px;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        #stats {
            display: flex;
            justify-content: space-around;
            margin-bottom: 10px;
            font-size: 1.1em;
        }
        #stats span {
            padding: 5px 10px;
            background-color: #3b4048;
            border-radius: 3px;
        }
        #tower-selection, #selected-tower-actions {
            display: flex;
            justify-content: center;
            margin-bottom: 10px;
            flex-wrap: wrap;
        }
        .tower-button, #start-wave-button, .action-button {
            padding: 8px 12px;
            margin: 5px;
            background-color: #61afef;
            color: #1e222a;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-family: inherit;
            font-weight: bold;
            transition: background-color 0.2s ease;
            font-size: 0.9em;
        }
        .tower-button:hover, #start-wave-button:hover, .action-button:hover {
            background-color: #528bce;
        }
        .tower-button.selected {
            background-color: #c678dd;
            box-shadow: 0 0 8px #c678dd;
        }
        #start-wave-button {
            background-color: #98c379;
            width: calc(100% - 10px);
            margin-top: auto;
        }
        #start-wave-button:hover {
            background-color: #80a863;
        }
        #selected-tower-info {
            font-size: 0.9em;
            text-align: center;
            padding: 5px;
            background-color: #3b4048;
            border-radius: 3px;
            margin-bottom: 5px;
            min-height: 40px;
        }
        #selected-tower-info p {
            margin: 3px 0;
        }
        .action-button.upgrade { background-color: #e5c07b; }
        .action-button.upgrade:hover { background-color: #d8b368; }
        .action-button.sell { background-color: #e06c75; }
        .action-button.sell:hover { background-color: #d35f69; }
        #message-area {
            margin-top: 10px;
            min-height: 20px;
            text-align: center;
            font-weight: bold;
            color: #98c379;
            font-size: 0.9em;
        }
    </style>
</head>
<body>
    <div id="game-wrapper">
        <canvas id="gameCanvas"></canvas>
        <div id="ui-panel">
            <div>
                <div id="stats">
                    <span>Gold: <span id="goldDisplay">100</span></span>
                    <span>Lives: <span id="livesDisplay">20</span></span>
                    <span>Wave: <span id="waveDisplay">0</span> / <span id="totalWavesDisplay">7</span></span>
                </div>
                <div id="tower-selection">
                    <button class="tower-button" data-tower-type="archer" data-cost="50">Archer (50G)</button>
                    <button class="tower-button" data-tower-type="cannon" data-cost="100">Cannon (100G)</button>
                    <button class="tower-button" data-tower-type="magic" data-cost="75">Magic (75G)</button>
                </div>
                <div id="selected-tower-info" style="display:none;"></div>
                <div id="selected-tower-actions" style="display:none;"></div>
                <div id="message-area">Game Initialized!</div>
            </div>
            <button id="start-wave-button">Start Next Wave</button>
        </div>
    </div>

    <script>
        // --- Global Variables & Constants ---
        const canvas = document.getElementById('gameCanvas');
        const ctx = canvas.getContext('2d');

        const TILE_SIZE = 32;
        const GRID_COLS = 20;
        const GRID_ROWS = 15;

        canvas.width = TILE_SIZE * GRID_COLS;
        canvas.height = TILE_SIZE * GRID_ROWS;
        document.getElementById('ui-panel').style.width = canvas.width + 'px';

        const goldDisplay = document.getElementById('goldDisplay');
        const livesDisplay = document.getElementById('livesDisplay');
        const waveDisplay = document.getElementById('waveDisplay');
        const totalWavesDisplay = document.getElementById('totalWavesDisplay');
        const messageArea = document.getElementById('message-area');
        const towerButtons = document.querySelectorAll('.tower-button');
        const startWaveButton = document.getElementById('start-wave-button');
        const selectedTowerInfoDiv = document.getElementById('selected-tower-info');
        const selectedTowerActionsDiv = document.getElementById('selected-tower-actions');

        let gold = 100;
        let lives = 20;
        let selectedTowerType = null;
        let selectedTowerCost = 0;
        let currentlySelectedPlacedTower = null;

        let towers = [];
        let enemies = [];
        let projectiles = [];
        let particles = [];

        let path = [];
        let grid = []; // Will store { type: 'grass'/'path', tower: null, grassPattern: [...] }

        let isWaveActive = false;
        let gameTime = 0; // in milliseconds
        let lastTime = 0;

        // Define grass colors for texture
        const grassColors = ['#34A853', '#2E8B57', '#3CB371', '#228B22'];


        const waveDefinitions = [ // Added 'mobType' for specific drawing logic
            { mobType: 'goblin', subWave: [ { count: 10, spawnInterval: 1000, health: 50, speed: 60, goldValue: 5, color: '#e06c75', radius: TILE_SIZE * 0.25 } ]},
            { mobType: 'orc', subWave: [ { count: 5, spawnInterval: 1500, health: 150, speed: 40, goldValue: 10, color: '#d19a66', radius: TILE_SIZE * 0.35 } ]},
            { mobType: 'mixed_goblin_orc', subWave: [
                { mobTypeInternal: 'goblin', count: 8, spawnInterval: 800, health: 50, speed: 60, goldValue: 5, color: '#e06c75', radius: TILE_SIZE * 0.25 },
                { mobTypeInternal: 'orc', count: 4, spawnInterval: 2000, health: 150, speed: 40, goldValue: 10, color: '#d19a66', radius: TILE_SIZE * 0.35 }
            ]},
            { mobType: 'fast_goblin', subWave: [ { count: 15, spawnInterval: 600, health: 40, speed: 80, goldValue: 4, color: '#ef5966', radius: TILE_SIZE * 0.20 } ]},
            { mobType: 'tough_orc', subWave: [ { count: 7, spawnInterval: 1800, health: 250, speed: 35, goldValue: 15, color: '#c08050', radius: TILE_SIZE * 0.40 } ]},
            { mobType: 'swarm', subWave: [ { count: 25, spawnInterval: 400, health: 30, speed: 90, goldValue: 3, color: '#ff7f7f', radius: TILE_SIZE * 0.18 } ]},
            { mobType: 'mini_boss_ogre', subWave: [
                { mobTypeInternal: 'goblin', count: 10, spawnInterval: 900, health: 50, speed: 60, goldValue: 5, color: '#e06c75', radius: TILE_SIZE * 0.25 },
                { mobTypeInternal: 'ogre', count: 1, spawnInterval: 3000, health: 800, speed: 30, goldValue: 50, color: '#8B0000', radius: TILE_SIZE * 0.50 },
                { mobTypeInternal: 'orc', count: 5, spawnInterval: 1000, health: 150, speed: 40, goldValue: 10, color: '#d19a66', radius: TILE_SIZE * 0.35 }
            ]},
        ];
        let currentWaveIndex = -1;
        let currentSubWaveIndex = -1;
        let currentWaveEnemiesToSpawn = 0;
        let currentWaveSpawnTimer = 0;
        let currentEnemyConfig = null; // This will hold the specific sub-wave enemy config

        // --- Web Audio API Sound System ---
        let audioCtx; function initAudio() { if (!audioCtx) audioCtx = new (window.AudioContext || window.webkitAudioContext)(); }
        let audioInitialized = false;
        function playWebAudioSound(type, options = {}) { /* ... (same as before, ensure it's here) ... */
            if (!audioCtx || !audioInitialized) return;
            const oscillator = audioCtx.createOscillator(); const gainNode = audioCtx.createGain();
            oscillator.connect(gainNode); gainNode.connect(audioCtx.destination);
            gainNode.gain.setValueAtTime(options.volume || 0.1, audioCtx.currentTime);
            switch (type) {
                case 'archerFire': oscillator.type = 'triangle'; oscillator.frequency.setValueAtTime(800, audioCtx.currentTime); gainNode.gain.exponentialRampToValueAtTime(0.0001, audioCtx.currentTime + 0.15); oscillator.start(audioCtx.currentTime); oscillator.stop(audioCtx.currentTime + 0.15); break;
                case 'cannonFire': oscillator.type = 'sawtooth'; oscillator.frequency.setValueAtTime(100, audioCtx.currentTime); oscillator.frequency.exponentialRampToValueAtTime(50, audioCtx.currentTime + 0.2); gainNode.gain.setValueAtTime(options.volume || 0.3, audioCtx.currentTime); gainNode.gain.exponentialRampToValueAtTime(0.0001, audioCtx.currentTime + 0.3); oscillator.start(audioCtx.currentTime); oscillator.stop(audioCtx.currentTime + 0.3); const noiseSource = audioCtx.createBufferSource(); const bufferSize = audioCtx.sampleRate * 0.2; const buffer = audioCtx.createBuffer(1, bufferSize, audioCtx.sampleRate); const output = buffer.getChannelData(0); for (let i = 0; i < bufferSize; i++) output[i] = Math.random() * 2 - 1; noiseSource.buffer = buffer; const noiseGain = audioCtx.createGain(); noiseSource.connect(noiseGain); noiseGain.connect(audioCtx.destination); noiseGain.gain.setValueAtTime(options.volume * 0.5 || 0.1, audioCtx.currentTime); noiseGain.gain.exponentialRampToValueAtTime(0.0001, audioCtx.currentTime + 0.2); noiseSource.start(audioCtx.currentTime); noiseSource.stop(audioCtx.currentTime + 0.2); break;
                case 'magicFire': oscillator.type = 'sine'; oscillator.frequency.setValueAtTime(600, audioCtx.currentTime); oscillator.frequency.exponentialRampToValueAtTime(1200, audioCtx.currentTime + 0.2); gainNode.gain.setValueAtTime(options.volume || 0.08, audioCtx.currentTime); gainNode.gain.exponentialRampToValueAtTime(0.0001, audioCtx.currentTime + 0.25); oscillator.start(audioCtx.currentTime); oscillator.stop(audioCtx.currentTime + 0.25); break;
                case 'hitMob': oscillator.type = 'square'; oscillator.frequency.setValueAtTime(400, audioCtx.currentTime); gainNode.gain.setValueAtTime(options.volume || 0.05, audioCtx.currentTime); gainNode.gain.exponentialRampToValueAtTime(0.0001, audioCtx.currentTime + 0.1); oscillator.start(audioCtx.currentTime); oscillator.stop(audioCtx.currentTime + 0.1); break;
            }
        }

        // --- Helper Functions ---
        function distance(x1, y1, x2, y2) { const dx = x2 - x1; const dy = y2 - y1; return Math.sqrt(dx * dx + dy * dy); }
        function createParticleBurst(x, y, count, config) { for (let i = 0; i < count; i++) particles.push(new Particle(x, y, config)); }

        // --- Entity Classes ---
        function Particle(x, y, config) { /* ... (same as before, ensure it's here) ... */
            this.x = x; this.y = y; this.size = config.size || Math.random() * 3 + 1;
            this.speed = Math.random() * (config.maxSpeed || 20) + (config.minSpeed || 10);
            this.angle = config.angle !== undefined ? config.angle : Math.random() * Math.PI * 2;
            this.vx = Math.cos(this.angle) * this.speed; this.vy = Math.sin(this.angle) * this.speed;
            this.color = config.color || 'rgba(255,255,255,0.8)'; this.life = config.life || Math.random() * 0.3 + 0.2;
            this.lifeSpan = this.life; this.gravity = config.gravity || 0; this.drag = config.drag || 0;
            this.isParticle = true; this.isAoEVisual = config.isAoEVisual || false; this.type = config.type || 'spark';
            this.update = function(deltaTime) { this.life -= deltaTime; if (this.life <= 0) return; this.x += this.vx * deltaTime; this.y += this.vy * deltaTime; this.vy += this.gravity * deltaTime; if (this.drag > 0) { this.vx *= (1 - this.drag * deltaTime); this.vy *= (1 - this.drag * deltaTime); } };
            this.draw = function(ctx) { if (this.life <= 0) return; const alpha = Math.max(0, this.life / this.lifeSpan); let effectiveColor = this.color; if (this.color.startsWith('rgba')) effectiveColor = this.color.replace(/[\d\.]+\)$/, alpha.toFixed(2) + ')'); else { let r = parseInt(this.color.substring(1, 3), 16); let g = parseInt(this.color.substring(3, 5), 16); let b = parseInt(this.color.substring(5, 7), 16); effectiveColor = `rgba(${r},${g},${b},${alpha.toFixed(2)})`; } ctx.fillStyle = effectiveColor; if (this.isAoEVisual) { const currentRadius = (config.radius || TILE_SIZE) * (1 - alpha); ctx.beginPath(); ctx.arc(this.x, this.y, currentRadius, 0, Math.PI * 2); ctx.fill(); } else if (this.type === 'smoke') { ctx.beginPath(); ctx.arc(this.x, this.y, this.size * (1 + (this.lifeSpan - this.life)), 0, Math.PI * 2); ctx.fill(); } else ctx.fillRect(this.x - this.size / 2, this.y - this.size / 2, this.size, this.size); };
        }

        function Enemy(mobType, config, startX, startY) { // Added mobType from main wave definition
            this.x = startX; this.y = startY;
            this.mobType = mobType; // e.g., 'goblin', 'orc', 'swarm'
            if (config.mobTypeInternal) this.mobType = config.mobTypeInternal; // Override if subwave has specific type

            this.maxHealth = config.health; this.health = config.health;
            this.speed = config.speed; this.goldValue = config.goldValue;
            this.color = config.color; this.baseRadius = config.radius; // Store base radius
            this.radius = config.radius; // Current radius, can change for pulsing
            this.pathIndex = 0; this.isAlive = true;
            this.hitFlashTimer = 0; this.hitFlashDuration = 150;
            this.glowIntensity = 0;
            this.pulseOffset = Math.random() * Math.PI * 2; // For unique pulse cycle
            this.wobbleAngle = 0;
            this.wobbleDirection = 1;
            this.rotation = 0; // For spinning mobs

            this.takeDamage = function(amount, impactX, impactY) { /* ... (same as before) ... */
                this.health -= amount; this.hitFlashTimer = this.hitFlashDuration; this.glowIntensity = 1.0;
                createParticleBurst(impactX !== undefined ? impactX : this.x, impactY !== undefined ? impactY : this.y, 5, { color: this.color === '#8B0000' ? '#FF0000' : '#A52A2A', size: 3, life: 0.4, maxSpeed: 40, minSpeed: 10, gravity: 50, type: 'blood' });
                if (this.health <= 0) { this.health = 0; this.isAlive = false; gold += this.goldValue; updateUIDisplays(); createParticleBurst(this.x, this.y, 15, { color: this.color, size: 3, life: 0.6, maxSpeed: 50, minSpeed: 10, type: 'smoke' }); }
            };
            this.update = function(deltaTime) {
                if (!this.isAlive) return;
                // Hit effects
                if (this.hitFlashTimer > 0) this.hitFlashTimer -= deltaTime * 1000;
                if (this.glowIntensity > 0) { this.glowIntensity -= deltaTime * 2.0; this.glowIntensity = Math.max(0, this.glowIntensity); }

                // Mob-specific animations
                if (this.mobType === 'goblin' || this.mobType === 'fast_goblin') {
                    this.radius = this.baseRadius + Math.sin(gameTime / 200 + this.pulseOffset) * 1; // Subtle pulse
                    this.wobbleAngle += (Math.random() - 0.5) * 0.1 * this.wobbleDirection;
                    if (Math.abs(this.wobbleAngle) > 0.2) this.wobbleDirection *= -1;
                } else if (this.mobType === 'swarm') {
                    this.radius = this.baseRadius + Math.sin(gameTime / 100 + this.pulseOffset) * 2; // Faster pulse
                    this.rotation += deltaTime * 5; // Spin
                } else if (this.mobType === 'orc' || this.mobType === 'tough_orc' || this.mobType === 'ogre') {
                    // Less pulsing, more rigid
                }

                // Movement
                if (this.pathIndex >= path.length) { lives--; this.isAlive = false; if (lives <= 0) { showMessage("GAME OVER!", "error"); isWaveActive = false; } updateUIDisplays(); return; }
                const targetWaypoint = path[this.pathIndex]; const targetX = targetWaypoint.c * TILE_SIZE + TILE_SIZE / 2; const targetY = targetWaypoint.r * TILE_SIZE + TILE_SIZE / 2;
                const dx = targetX - this.x; const dy = targetY - this.y; const distToWaypoint = Math.sqrt(dx * dx + dy * dy);
                if (distToWaypoint < this.speed * deltaTime * 1.1) { this.x = targetX; this.y = targetY; this.pathIndex++; }
                else { const angle = Math.atan2(dy, dx); this.x += Math.cos(angle) * this.speed * deltaTime; this.y += Math.sin(angle) * this.speed * deltaTime; }
            };
            this.draw = function(ctx) {
                if (!this.isAlive) return;
                ctx.save();
                ctx.translate(this.x, this.y);
                ctx.rotate(this.rotation + this.wobbleAngle); // Apply rotation and wobble

                // Main body color
                let bodyColor = this.hitFlashTimer > 0 ? 'rgba(255, 255, 255, 0.8)' : this.color;
                ctx.fillStyle = bodyColor;

                // Draw based on mobType
                if (this.mobType === 'goblin' || this.mobType === 'fast_goblin') {
                    ctx.beginPath(); // Slightly more triangular/hunched
                    ctx.moveTo(0, -this.radius);
                    ctx.lineTo(this.radius * 0.8, this.radius * 0.6);
                    ctx.lineTo(-this.radius * 0.8, this.radius * 0.6);
                    ctx.closePath();
                    ctx.fill();
                    // Simple eyes
                    ctx.fillStyle = 'black';
                    ctx.fillRect(-this.radius * 0.3, -this.radius * 0.4, 2, 2);
                    ctx.fillRect(this.radius * 0.1, -this.radius * 0.4, 2, 2);
                } else if (this.mobType === 'orc' || this.mobType === 'tough_orc') {
                    ctx.fillRect(-this.radius, -this.radius, this.radius * 2, this.radius * 2); // Blocky
                     // Angry eyes (simple V shape or two slanted lines)
                    ctx.fillStyle = 'black';
                    ctx.beginPath();
                    ctx.moveTo(-this.radius*0.4, -this.radius*0.3); ctx.lineTo(-this.radius*0.1, -this.radius*0.5);
                    ctx.moveTo(this.radius*0.4, -this.radius*0.3); ctx.lineTo(this.radius*0.1, -this.radius*0.5);
                    ctx.lineWidth = 1; ctx.strokeStyle = 'black'; ctx.stroke(); // Use stroke for thin lines
                } else if (this.mobType === 'ogre') {
                    ctx.beginPath(); ctx.arc(0, 0, this.radius, 0, 2 * Math.PI); ctx.fill(); // Large circle
                    // Big angry eyes
                    ctx.fillStyle = 'white';
                    ctx.beginPath(); ctx.arc(-this.radius*0.3, -this.radius*0.2, this.radius*0.2, 0, Math.PI*2); ctx.fill();
                    ctx.beginPath(); ctx.arc(this.radius*0.3, -this.radius*0.2, this.radius*0.2, 0, Math.PI*2); ctx.fill();
                    ctx.fillStyle = 'red'; // Pupils
                    ctx.beginPath(); ctx.arc(-this.radius*0.3, -this.radius*0.2, this.radius*0.1, 0, Math.PI*2); ctx.fill();
                    ctx.beginPath(); ctx.arc(this.radius*0.3, -this.radius*0.2, this.radius*0.1, 0, Math.PI*2); ctx.fill();
                } else if (this.mobType === 'swarm') {
                    // Main body
                    ctx.beginPath(); ctx.arc(0, 0, this.radius, 0, 2 * Math.PI); ctx.fill();
                    // Simple "tentacles" or spikes
                    ctx.fillStyle = this.hitFlashTimer > 0 ? 'white' : this.color; // Tentacles also flash
                    for (let i = 0; i < 5; i++) {
                        const angle = (i / 5) * Math.PI * 2 + this.rotation * 0.5; // Rotate tentacles slower
                        const length = this.radius * (0.5 + Math.sin(gameTime / 150 + i) * 0.2);
                        ctx.fillRect(Math.cos(angle) * this.radius * 0.8, Math.sin(angle) * this.radius * 0.8, 2, length);
                    }
                } else { // Default: Circle
                    ctx.beginPath(); ctx.arc(0, 0, this.radius, 0, 2 * Math.PI); ctx.fill();
                }
                ctx.restore(); // Restore transform

                // Glow effect (applied after transform restore, at original this.x, this.y)
                if (this.glowIntensity > 0) {
                    ctx.globalAlpha = this.glowIntensity * 0.5; ctx.fillStyle = 'white'; ctx.beginPath();
                    ctx.arc(this.x, this.y, this.radius + 2 * this.glowIntensity, 0, 2 * Math.PI); ctx.fill();
                    ctx.globalAlpha = 1.0;
                }
                // Health bar
                if (this.health < this.maxHealth) {
                    const barWidth = this.radius * 1.5; const barHeight = 4; const barX = this.x - barWidth / 2; const barY = this.y - this.baseRadius - barHeight - 2; // Use baseRadius for health bar positioning
                    ctx.fillStyle = '#5c6370'; ctx.fillRect(barX, barY, barWidth, barHeight);
                    ctx.fillStyle = '#98c379'; ctx.fillRect(barX, barY, barWidth * (this.health / this.maxHealth), barHeight);
                }
            };
        }

        function Tower(r, c, type, cost) { /* ... (same as before, ensure it's here) ... */
            this.r = r; this.c = c; this.x = c * TILE_SIZE + TILE_SIZE / 2; this.y = r * TILE_SIZE + TILE_SIZE / 2;
            this.type = type; this.baseCost = cost; this.level = 1; this.maxLevel = 2;
            this.lastShotTime = 0; this.target = null;
            this.barrelAngle = -Math.PI / 2; this.targetAngle = -Math.PI / 2;
            this.barrelLength = TILE_SIZE * 0.5; this.barrelWidth = TILE_SIZE * 0.2;
            this.recoilAmount = 0; this.recoilDuration = 100; this.recoilTimer = 0;
            this.upgradeCosts = { 'archer': [75], 'cannon': [150], 'magic': [100] };
            this.applyStats = function() { this.aoeRadius = 0; this.aoeDamageFactor = 0.3; if (this.type === 'archer') { this.fireRate = 2 + (this.level - 1) * 0.5; this.projectileDamage = 15 + (this.level - 1) * 5; this.range = TILE_SIZE * (3.5 + (this.level - 1) * 0.25); this.color = this.level === 1 ? '#98c379' : '#7a9a61'; this.projectileColor = this.color; this.projectileSpeed = 300; this.projectileRadius = TILE_SIZE * 0.1; this.barrelLength = TILE_SIZE * 0.5; this.barrelWidth = TILE_SIZE * 0.15;} else if (this.type === 'cannon') { this.fireRate = 0.5 + (this.level -1) * 0.1; this.projectileDamage = 50 + (this.level - 1) * 30; this.range = TILE_SIZE * (2.5 + (this.level - 1) * 0.25); this.color = this.level === 1 ? '#abb2bf' : '#808080'; this.projectileColor = this.color; this.projectileSpeed = 200; this.projectileRadius = TILE_SIZE * (0.15 + (this.level - 1) * 0.03); if (this.level === 2) { this.aoeRadius = TILE_SIZE * 0.75; } this.barrelLength = TILE_SIZE * 0.6; this.barrelWidth = TILE_SIZE * 0.3;} else if (this.type === 'magic') { this.fireRate = 1.25 + (this.level -1) * 0.25; this.projectileDamage = 25 + (this.level - 1) * 10; this.range = TILE_SIZE * (3 + (this.level - 1) * 0.5); this.color = this.level === 1 ? '#61afef' : '#4b8bbe'; this.projectileColor = this.color; this.projectileSpeed = 250; this.projectileRadius = TILE_SIZE * 0.1; } }; this.applyStats();
            this.getUpgradeCost = function() { if (this.level < this.maxLevel) return this.upgradeCosts[this.type][this.level -1]; return Infinity; };
            this.upgrade = function() { if (this.level < this.maxLevel) { const cost = this.getUpgradeCost(); if (gold >= cost) { gold -= cost; this.level++; this.applyStats(); updateUIDisplays(); displaySelectedTowerInfo(this); showMessage(`${this.type.toUpperCase()} upgraded to Level ${this.level}!`, "success"); } else { showMessage("Not enough gold to upgrade!", "error"); } } };
            this.findTarget = function() { this.target = null; let closestDist = this.range + 1; for (let enemy of enemies) { if (enemy.isAlive) { const d = distance(this.x, this.y, enemy.x, enemy.y); if (d <= this.range && d < closestDist) { closestDist = d; this.target = enemy; } } } };
            this.update = function(gameTime, deltaTime) { this.findTarget(); if (this.target) this.targetAngle = Math.atan2(this.target.y - this.y, this.target.x - this.x); else this.targetAngle = -Math.PI / 2; let angleDiff = this.targetAngle - this.barrelAngle; while (angleDiff < -Math.PI) angleDiff += Math.PI * 2; while (angleDiff > Math.PI) angleDiff -= Math.PI * 2; this.barrelAngle += angleDiff * 0.2; if (this.recoilTimer > 0) { this.recoilTimer -= deltaTime * 1000; const recoilProgress = Math.max(0, this.recoilTimer / this.recoilDuration); if (recoilProgress > 0.5) this.recoilAmount = TILE_SIZE * 0.1 * ((recoilProgress - 0.5) * 2); else this.recoilAmount = TILE_SIZE * 0.1 * (recoilProgress * 2); } else this.recoilAmount = 0; if (this.target && (gameTime - this.lastShotTime) >= (1000 / this.fireRate)) { this.lastShotTime = gameTime; const barrelEndX = this.x + Math.cos(this.barrelAngle) * (this.barrelLength - this.recoilAmount); const barrelEndY = this.y + Math.sin(this.barrelAngle) * (this.barrelLength - this.recoilAmount); projectiles.push(new Projectile(barrelEndX, barrelEndY, this.target, this.projectileDamage, this.projectileSpeed, this.projectileRadius, this.projectileColor, this.aoeRadius, this.aoeDamageFactor)); this.recoilTimer = this.recoilDuration; createParticleBurst(barrelEndX, barrelEndY, 5, { color: this.type === 'cannon' ? '#FFA500' : (this.type === 'archer' ? '#FFFFE0' : '#ADD8E6'), size: this.type === 'cannon' ? 3 : 2, life: 0.1, maxSpeed: 50, minSpeed: 20, angle: this.barrelAngle + (Math.random() - 0.5) * 0.3, type: 'spark' }); if (this.type === 'archer') playWebAudioSound('archerFire'); else if (this.type === 'cannon') playWebAudioSound('cannonFire'); else if (this.type === 'magic') playWebAudioSound('magicFire'); } };
            this.draw = function(ctx) { ctx.fillStyle = this.color; ctx.beginPath(); const visualRadius = TILE_SIZE * (0.4 + (this.level - 1) * 0.05); ctx.arc(this.x, this.y, visualRadius, 0, 2 * Math.PI); ctx.fill(); ctx.save(); ctx.translate(this.x, this.y); ctx.rotate(this.barrelAngle); let barrelColor = this.level === 1 ? '#A9A9A9' : '#696969'; if (this.type === 'cannon') barrelColor = this.level === 1 ? '#606060' : '#404040'; else if (this.type === 'archer') barrelColor = this.level === 1 ? '#CD853F' : '#8B4513'; ctx.fillStyle = barrelColor; ctx.fillRect(0 - this.recoilAmount, -this.barrelWidth / 2, this.barrelLength, this.barrelWidth); ctx.restore(); ctx.fillStyle = '#FFF'; ctx.font = 'bold 10px Courier New'; ctx.textAlign = 'center'; ctx.textBaseline = 'middle'; ctx.fillText(this.level, this.x, this.y + visualRadius * 0.7); if (currentlySelectedPlacedTower === this) { ctx.strokeStyle = '#FFF'; ctx.lineWidth = 2; ctx.beginPath(); ctx.arc(this.x, this.y, visualRadius + 2, 0, 2 * Math.PI); ctx.stroke(); ctx.strokeStyle = 'rgba(255, 255, 255, 0.3)'; ctx.lineWidth = 1; ctx.beginPath(); ctx.arc(this.x, this.y, this.range, 0, 2 * Math.PI); ctx.stroke(); } };
        }

        function Projectile(startX, startY, target, damage, speed, radius, color, aoeRadius = 0, aoeDamageFactor = 0) { /* ... (same as before, ensure it's here) ... */
            this.x = startX; this.y = startY; this.target = target; this.damage = damage; this.speed = speed; this.radius = radius;
            this.color = color; this.isActive = true; this.aoeRadius = aoeRadius; this.aoeDamageFactor = aoeDamageFactor;
            this.trailTimer = 0; this.trailInterval = 50;
            this.update = function(deltaTime) { if (!this.isActive || !this.target || !this.target.isAlive) { this.isActive = false; return; } this.trailTimer += deltaTime * 1000; if (this.trailTimer >= this.trailInterval) { this.trailTimer = 0; createParticleBurst(this.x, this.y, 1, { color: this.color, size: this.radius * 0.5, life: 0.2, maxSpeed: 5, minSpeed: 1, angle: Math.random() * Math.PI * 2, type: 'trail' }); } const dx = this.target.x - this.x; const dy = this.target.y - this.y; const distToTarget = Math.sqrt(dx * dx + dy * dy); if (distToTarget < this.speed * deltaTime || distToTarget < this.target.radius * 0.8) { this.target.takeDamage(this.damage, this.x, this.y); playWebAudioSound('hitMob'); createParticleBurst(this.x, this.y, 8, { color: '#FFFFFF', size: 2, life: 0.3, maxSpeed: 60, minSpeed: 30, type: 'spark' }); if (this.aoeRadius > 0) { const impactX = this.target.x; const impactY = this.target.y; enemies.forEach(enemy => { if (enemy.isAlive && enemy !== this.target) { const distToAoETarget = distance(impactX, impactY, enemy.x, enemy.y); if (distToAoETarget <= this.aoeRadius) enemy.takeDamage(this.damage * this.aoeDamageFactor, enemy.x, enemy.y); } }); particles.push(new Particle(impactX, impactY, { isAoEVisual: true, radius: this.aoeRadius, color: 'rgba(255,165,0,0.7)', life: 0.2 })); } this.isActive = false; } else { const angle = Math.atan2(dy, dx); this.x += Math.cos(angle) * this.speed * deltaTime; this.y += Math.sin(angle) * this.speed * deltaTime; } };
            this.draw = function(ctx) { if (!this.isActive) return; ctx.fillStyle = this.color; ctx.beginPath(); ctx.arc(this.x, this.y, this.radius, 0, 2 * Math.PI); ctx.fill(); };
        }

        // --- Game Initialization ---
        function initializeGame() {
            console.log("Initializing game...");
            grid = []; // Clear grid for regeneration
            for (let r = 0; r < GRID_ROWS; r++) {
                grid[r] = [];
                for (let c = 0; c < GRID_COLS; c++) {
                    // Generate a simple grass pattern for each grass tile
                    let grassPattern = [];
                    if (Math.random() > 0.3) { // Not every tile needs dense grass
                        for(let i=0; i < TILE_SIZE / 4; i++) { // Density of grass blades
                            grassPattern.push({
                                x: Math.random() * TILE_SIZE,
                                y: Math.random() * TILE_SIZE,
                                color: grassColors[Math.floor(Math.random() * grassColors.length)]
                            });
                        }
                    }
                    grid[r][c] = { type: 'grass', tower: null, grassPattern: grassPattern };
                }
            }
            path = [ { r: 7, c: 0 }, { r: 7, c: 1 }, { r: 7, c: 2 }, { r: 6, c: 2 }, { r: 5, c: 2 }, { r: 5, c: 3 }, { r: 5, c: 4 }, { r: 5, c: 5 }, { r: 5, c: 6 }, { r: 4, c: 6 }, { r: 3, c: 6 }, { r: 3, c: 7 }, { r: 3, c: 8 }, { r: 3, c: 9 }, { r: 3, c: 10 }, { r: 4, c: 10 }, { r: 5, c: 10 }, { r: 6, c: 10 }, { r: 7, c: 10 }, { r: 7, c: 11 }, { r: 7, c: 12 }, { r: 7, c: 13 }, { r: 7, c: 14 }, { r: 7, c: 15 }, { r: 7, c: 16 }, { r: 7, c: 17 }, { r: 7, c: 18 }, { r: 7, c: 19 } ];
            path.forEach(segment => { if (segment.r >= 0 && segment.r < GRID_ROWS && segment.c >= 0 && segment.c < GRID_COLS) { grid[segment.r][segment.c].type = 'path'; grid[segment.r][segment.c].grassPattern = []; } }); // Path tiles don't have grass pattern
            gold = 100; lives = 20; currentWaveIndex = -1; currentSubWaveIndex = -1;
            enemies = []; projectiles = []; particles = []; towers = []; grid.forEach(row => row.forEach(cell => { if(cell.tower) cell.tower = null; } )); // Clear towers from grid objects, but keep grassPattern
            isWaveActive = false; currentlySelectedPlacedTower = null;
            selectedTowerType = null; towerButtons.forEach(btn => btn.classList.remove('selected'));
            hideSelectedTowerUI(); updateUIDisplays(); showMessage("Place your towers! Press Start Wave.");
            startWaveButton.textContent = "Start Next Wave";
        }

        // --- UI Update & Interaction ---
        function updateUIDisplays() { /* ... (same as before) ... */ goldDisplay.textContent = gold; livesDisplay.textContent = lives; waveDisplay.textContent = currentWaveIndex + 1; totalWavesDisplay.textContent = waveDefinitions.length; }
        function showMessage(msg, type = 'info') { /* ... (same as before) ... */ messageArea.textContent = msg; if (type === 'error') messageArea.style.color = '#e06c75'; else if (type === 'success') messageArea.style.color = '#98c379'; else messageArea.style.color = '#61afef';}
        function displaySelectedTowerInfo(tower) { /* ... (same as before) ... */ selectedTowerInfoDiv.style.display = 'block'; selectedTowerActionsDiv.style.display = 'flex'; let infoHTML = `<p>Type: ${tower.type.toUpperCase()} | Level: ${tower.level}</p>`; infoHTML += `<p>Damage: ${tower.projectileDamage.toFixed(0)} | Range: ${(tower.range / TILE_SIZE).toFixed(1)}t | RoF: ${tower.fireRate.toFixed(1)}/s</p>`; if (tower.aoeRadius > 0) infoHTML += `<p>AoE Radius: ${(tower.aoeRadius / TILE_SIZE).toFixed(1)}t | AoE Dmg: ${(tower.projectileDamage * tower.aoeDamageFactor).toFixed(0)}</p>`; selectedTowerInfoDiv.innerHTML = infoHTML; selectedTowerActionsDiv.innerHTML = ''; if (tower.level < tower.maxLevel) { const upgradeCost = tower.getUpgradeCost(); const upgradeButton = document.createElement('button'); upgradeButton.classList.add('action-button', 'upgrade'); upgradeButton.textContent = `Upgrade (${upgradeCost}G)`; upgradeButton.onclick = () => tower.upgrade(); selectedTowerActionsDiv.appendChild(upgradeButton); } }
        function hideSelectedTowerUI() { /* ... (same as before) ... */ selectedTowerInfoDiv.style.display = 'none'; selectedTowerActionsDiv.style.display = 'none'; currentlySelectedPlacedTower = null; }

        // --- Event Listeners ---
        towerButtons.forEach(button => { /* ... (same, including audio init) ... */ button.addEventListener('click', () => { if (lives <= 0) return; if (!audioInitialized) { initAudio(); audioInitialized = true; } hideSelectedTowerUI(); if (selectedTowerType === button.dataset.towerType) { selectedTowerType = null; selectedTowerCost = 0; button.classList.remove('selected'); showMessage("Tower selection cleared."); } else { selectedTowerType = button.dataset.towerType; selectedTowerCost = parseInt(button.dataset.cost); towerButtons.forEach(btn => btn.classList.remove('selected')); button.classList.add('selected'); showMessage(`${selectedTowerType.charAt(0).toUpperCase() + selectedTowerType.slice(1)} Tower selected. Cost: ${selectedTowerCost}G`); } }); });
        function startNextSubWave() { currentSubWaveIndex++; if (currentSubWaveIndex < waveDefinitions[currentWaveIndex].subWave.length) { currentEnemyConfig = waveDefinitions[currentWaveIndex].subWave[currentSubWaveIndex]; currentWaveEnemiesToSpawn = currentEnemyConfig.count; currentWaveSpawnTimer = currentEnemyConfig.spawnInterval; } else { currentEnemyConfig = null; } }
        startWaveButton.addEventListener('click', () => { /* ... (same, including audio init) ... */ if (!audioInitialized) { initAudio(); audioInitialized = true; } if (lives <= 0) { initializeGame(); return; } if (isWaveActive) { showMessage("Wave already in progress!", "error"); return; } if (currentWaveIndex + 1 >= waveDefinitions.length && enemies.length === 0) { showMessage("All waves cleared! YOU WIN! Click to Play Again.", "success"); return; } currentWaveIndex++; if (currentWaveIndex >= waveDefinitions.length) { showMessage("All waves cleared! YOU WIN! Click to Play Again.", "success"); return; } isWaveActive = true; currentSubWaveIndex = -1; startNextSubWave(); updateUIDisplays(); showMessage(`Wave ${currentWaveIndex + 1} of ${waveDefinitions.length} (${waveDefinitions[currentWaveIndex].type}) started!`, "info"); hideSelectedTowerUI(); selectedTowerType = null; towerButtons.forEach(btn => btn.classList.remove('selected')); });
        canvas.addEventListener('click', (event) => { /* ... (same, including audio init) ... */ if (!audioInitialized) { initAudio(); audioInitialized = true; } if (lives <= 0) return; const rect = canvas.getBoundingClientRect(); const x = event.clientX - rect.left; const y = event.clientY - rect.top; const col = Math.floor(x / TILE_SIZE); const row = Math.floor(y / TILE_SIZE); if (row < 0 || row >= GRID_ROWS || col < 0 || col >= GRID_COLS) return; if (selectedTowerType) { if (grid[row][col].type === 'path') { showMessage("Cannot place tower on the path!", "error"); return; } if (grid[row][col].tower) { showMessage("Cell already occupied by a tower!", "error"); return; } if (gold < selectedTowerCost) { showMessage("Not enough gold!", "error"); return; } gold -= selectedTowerCost; const newTower = new Tower(row, col, selectedTowerType, selectedTowerCost); towers.push(newTower); grid[row][col].tower = newTower; showMessage(`${selectedTowerType.charAt(0).toUpperCase() + selectedTowerType.slice(1)} tower placed.`, "success"); updateUIDisplays(); selectedTowerType = null; towerButtons.forEach(btn => btn.classList.remove('selected')); hideSelectedTowerUI(); } else { const clickedTower = grid[row][col].tower; if (clickedTower) { if (currentlySelectedPlacedTower === clickedTower) hideSelectedTowerUI(); else { currentlySelectedPlacedTower = clickedTower; displaySelectedTowerInfo(clickedTower); } } else hideSelectedTowerUI(); } });

        // --- Game Loop ---
        function update(deltaTime) { /* ... (same as before, ensure particle update is there) ... */
            if (lives <= 0 && isWaveActive) { isWaveActive = false; showMessage(`GAME OVER! Final Wave: ${currentWaveIndex + 1}. Click Start Wave to Play Again.`, "error"); startWaveButton.textContent = "Play Again?"; return; }
            if (!isWaveActive && currentWaveIndex + 1 >= waveDefinitions.length && enemies.length === 0 && lives > 0) { startWaveButton.textContent = "Play Again?"; return; }
            gameTime += deltaTime * 1000; // Keep gameTime in ms
            if (isWaveActive && currentEnemyConfig) { if (currentWaveEnemiesToSpawn > 0) { currentWaveSpawnTimer -= deltaTime * 1000; if (currentWaveSpawnTimer <= 0) { const startTile = path[0]; const startX = startTile.c * TILE_SIZE + TILE_SIZE / 2; const startY = startTile.r * TILE_SIZE + TILE_SIZE / 2; enemies.push(new Enemy(waveDefinitions[currentWaveIndex].mobType, currentEnemyConfig, startX, startY)); currentWaveEnemiesToSpawn--; currentWaveSpawnTimer = currentEnemyConfig.spawnInterval; } } else if (currentSubWaveIndex < waveDefinitions[currentWaveIndex].subWave.length - 1) startNextSubWave(); else if (enemies.length === 0) { isWaveActive = false; if (currentWaveIndex + 1 >= waveDefinitions.length) { showMessage(`All waves cleared! YOU WIN! Final Score: ${gold}`, "success"); startWaveButton.textContent = "Play Again?"; } else showMessage(`Wave ${currentWaveIndex + 1} cleared! Prepare for the next!`, "success"); } }
            towers.forEach(tower => tower.update(gameTime, deltaTime));
            for (let i = enemies.length - 1; i >= 0; i--) { enemies[i].update(deltaTime); if (!enemies[i].isAlive) enemies.splice(i, 1); }
            for (let i = projectiles.length - 1; i >= 0; i--) { projectiles[i].update(deltaTime); if (!projectiles[i].isActive) projectiles.splice(i, 1); }
            for (let i = particles.length - 1; i >= 0; i--) { particles[i].update(deltaTime); if (particles[i].life <= 0) particles.splice(i, 1); }
        }
        function draw() {
            // Draw Background (Grass and Path)
            for (let r = 0; r < GRID_ROWS; r++) {
                for (let c = 0; c < GRID_COLS; c++) {
                    const tileX = c * TILE_SIZE;
                    const tileY = r * TILE_SIZE;
                    if (grid[r][c].type === 'path') {
                        ctx.fillStyle = '#565c64'; // Path color
                        ctx.fillRect(tileX, tileY, TILE_SIZE, TILE_SIZE);
                    } else { // Grass tile
                        ctx.fillStyle = grassColors[0]; // Base grass color
                        ctx.fillRect(tileX, tileY, TILE_SIZE, TILE_SIZE);
                        // Draw grass texture pixels
                        grid[r][c].grassPattern.forEach(pixel => {
                            ctx.fillStyle = pixel.color;
                            ctx.fillRect(tileX + pixel.x, tileY + pixel.y, 1, 1); // Small 1x1 pixel "blades"
                        });
                    }
                }
            }

            particles.forEach(p => { if (p.isParticle && p.type === 'trail') p.draw(ctx); });
            projectiles.forEach(projectile => projectile.draw(ctx));
            towers.forEach(tower => tower.draw(ctx));
            particles.forEach(p => { if (p.isParticle && p.type !== 'trail' && !p.isAoEVisual) p.draw(ctx); });
            particles.forEach(p => { if (p.isAoEVisual) p.draw(ctx); });
            enemies.forEach(enemy => enemy.draw(ctx));
        }
        function gameLoop(timestamp) { /* ... (same as before) ... */ if (!lastTime) lastTime = timestamp; const deltaTime = (timestamp - lastTime) / 1000; lastTime = timestamp; update(Math.min(deltaTime, 0.1)); draw(); requestAnimationFrame(gameLoop); }
        document.addEventListener('DOMContentLoaded', () => { initializeGame(); requestAnimationFrame(gameLoop); });
    </script>
</body>
</html>