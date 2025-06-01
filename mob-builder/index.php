<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TD Enemy Simulator - Polished Towers</title>
    <style>
        body { font-family: sans-serif; display: flex; flex-direction: column; align-items: center; margin: 0; background-color: #f0f0f0; }
        #game-container { position: relative; width: 800px; height: 350px; background-color: #ddd; border: 2px solid #333; margin-top: 20px; overflow: hidden; }
        #path { position: absolute; top: 150px; left: 0; width: 100%; height: 60px; background-color: #b08d57; border-top: 1px dashed #8a6d40; border-bottom: 1px dashed #8a6d40; }

        .tower-base { position: absolute; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 5px rgba(0,0,0,0.3); }
        #tower-rocket-base { background-color: #saddlebrown; top: 60px; left: 100px; }
        #tower-ice-base { background-color: #steelblue; top: 60px; left: 380px; }
        #tower-lightning-base { background-color: #darkgoldenrod; top: 60px; left: 660px; }

        .turret-pivot { position: absolute; width: 100%; height: 100%; top: 0; left: 0; transform-origin: center center; }
        .turret-model {
            position: absolute;
            background-color: #666;
            border: 1px solid #444;
            transform-origin: 50% 100%; /* Pivot at the base of the barrel model for recoil */
        }
        .turret-glow-indicator { /* For pre-fire glow */
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            border-radius: inherit; /* Match parent model's shape */
            box-shadow: 0 0 0px 0px transparent;
            opacity: 0;
            transition: opacity 0.1s, box-shadow 0.1s; /* Smooth visual updates */
            pointer-events: none;
            z-index: 1; /* Above model, below other elements if any */
        }


        .turret-rocket { width: 18px; height: 30px; background-color: #d2691e; border-radius: 4px 4px 0 0; left: calc(50% - 9px); bottom: 50%; }
        .turret-ice {
            width: 12px; height: 28px; background-color: #87CEEB; border-radius: 6px 6px 2px 2px;
            box-shadow: inset 0 0 3px rgba(255,255,255,0.5); left: calc(50% - 6px); bottom: 50%;
        }
        .turret-ice::before { content: ''; position: absolute; top: -5px; left: -4px; width: 20px; height: 10px; background: radial-gradient(circle, rgba(224,255,255,0.5) 20%, transparent 70%); border-radius: 50%; opacity: 0; transition: opacity 0.1s; }
        .turret-ice.firing::before { opacity: 1; }

        .turret-lightning {
            width: 25px; height: 25px; background: radial-gradient(circle, #ffffcc, #ffd700);
            border-radius: 50%; border: 2px solid #ffae42; box-shadow: 0 0 8px gold;
            left: calc(50% - 12.5px); top: calc(50% - 12.5px); transform-origin: center center;
        }

        .enemy { position: absolute; width: 30px; height: 30px; background-color: #2ecc71; border: 1px solid #27ae60; border-radius: 5px; display: flex; flex-direction: column; align-items: center; justify-content: center; box-sizing: border-box; font-size: 10px; color: white; }
        .enemy-health-bar { width: 90%; height: 5px; background-color: #e74c3c; border: 1px solid #c0392b; margin-top: 2px; }
        .enemy-health-fill { width: 100%; height: 100%; background-color: #2ecc71; }

        .projectile { position: absolute; box-sizing: border-box; }
        .rocket { width: 12px; height: 6px; background-color: #808080; border: 1px solid #505050; border-radius: 3px 0 0 3px; transform-origin: 25% 50%; }
        .rocket-flame { position: absolute; bottom: -2px; right: -8px; width: 10px; height: 10px; background: radial-gradient(circle, yellow 20%, orangered 50%, transparent 70%); border-radius: 50%; animation: flicker 0.1s infinite alternate; }
        @keyframes flicker { 0% { transform: scale(0.8); opacity: 0.7; } 100% { transform: scale(1.2); opacity: 1; } }

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
        .ice-shard { position: absolute; background-color: #afeeee; opacity: 0.8; }
        @keyframes ice-shatter-anim { 0% { opacity: 1; transform: translate(-50%, -50%) scale(0.5); } 100% { opacity: 0; transform: translate(-50%, -50%) scale(1.2) rotate(90deg); } }
        .lightning-strike-segment { position: absolute; background-color: yellow; height: 3px; transform-origin: 0 50%; box-shadow: 0 0 5px gold, 0 0 10px yellow; animation: lightning-flash 0.2s forwards; }
        .lightning-impact-flash { width: 30px; height: 30px; background: radial-gradient(circle, white 20%, yellow 60%, transparent 80%); border-radius: 50%; animation: explosion-anim 0.2s forwards; transform: translate(-50%, -50%); }
        @keyframes lightning-flash { 0% { opacity: 0; } 50% { opacity: 1; } 100% { opacity: 0; } }

        .muzzle-puff {
            width: 25px; /* Start size */
            height: 25px;
            background: radial-gradient(circle, rgba(220,220,220,0.6) 10%, rgba(180,180,180,0.3) 40%, transparent 60%);
            border-radius: 50%;
            animation: puff-anim 0.4s ease-out forwards;
            transform: translate(-50%, -50%); /* Center on spawn point */
        }
        @keyframes puff-anim {
            0% { transform: translate(-50%, -50%) scale(0.2); opacity: 0.8; }
            100% { transform: translate(-50%, -50%) scale(1.5); opacity: 0; }
        }


        /* Recoil Animations - Corrected Direction */
        .recoil-rocket > .turret-model { animation: recoil-pushback 0.15s ease-out; }
        .recoil-ice > .turret-model { animation: recoil-pushback-light 0.1s ease-out; }
        .recoil-lightning > .turret-model { animation: recoil-pulse 0.2s ease-in-out; }

        @keyframes recoil-pushback {
            0% { transform: translateY(0); }
            50% { transform: translateY(-4px); } /* Negative value for "backwards" */
            100% { transform: translateY(0); }
        }
        @keyframes recoil-pushback-light {
            0% { transform: translateY(0); }
            50% { transform: translateY(-3px); } /* Negative value for "backwards" */
            100% { transform: translateY(0); }
        }
        @keyframes recoil-pulse {
            0% { transform: scale(1); box-shadow: 0 0 8px gold; }
            50% { transform: scale(1.15); box-shadow: 0 0 15px yellow, 0 0 5px white; }
            100% { transform: scale(1); box-shadow: 0 0 8px gold; }
        }

        #controls { display: flex; justify-content: space-around; width: 800px; margin-top: 20px; padding: 10px; background-color: #e0e0e0; border-radius: 5px; }
        /* ... other control styles ... */
        .control-group { border: 1px solid #ccc; padding: 10px; border-radius: 5px; background-color: #f9f9f9; }
        .control-group h3 { margin-top: 0; text-align: center; }
        .control-group label { display: block; margin: 5px 0 2px; font-size: 0.9em; }
        .control-group input[type="range"] { width: 150px; }
        .control-group span { font-size: 0.8em; color: #555; }

    </style>
</head>
<body>
    <h1>TD Enemy Simulator - Polished Towers</h1>
    <div id="game-container">
        <div id="path"></div>
        <div id="tower-rocket-base" class="tower-base">
            <div class="turret-pivot">
                <div class="turret-model turret-rocket">
                    <div class="turret-glow-indicator"></div>
                </div>
            </div>
        </div>
        <div id="tower-ice-base" class="tower-base">
            <div class="turret-pivot">
                <div class="turret-model turret-ice">
                    <div class="turret-glow-indicator"></div>
                </div>
            </div>
        </div>
        <div id="tower-lightning-base" class="tower-base">
            <div class="turret-pivot">
                <div class="turret-model turret-lightning">
                    <div class="turret-glow-indicator"></div>
                </div>
            </div>
        </div>
    </div>
    <div id="controls">
        <!-- Controls remain the same -->
        <div class="control-group" id="rocket-controls">
            <h3>Rocket Tower 🚀</h3>
            <label for="rocket-firerate">Fire Rate (ms): <span id="rocket-firerate-val">1500</span></label>
            <input type="range" id="rocket-firerate" min="500" max="5000" value="1500" step="100">
            <label for="rocket-damage">Damage: <span id="rocket-damage-val">70</span></label>
            <input type="range" id="rocket-damage" min="10" max="200" value="70" step="5">
            <label for="rocket-projspeed">Proj. Speed: <span id="rocket-projspeed-val">2</span></label>
            <input type="range" id="rocket-projspeed" min="0.5" max="5" value="2" step="0.1">
            <label for="rocket-range">Range (px): <span id="rocket-range-val">200</span></label>
            <input type="range" id="rocket-range" min="50" max="350" value="200" step="10">
        </div>
        <div class="control-group"  id="ice-controls">
            <h3>Ice Tower ❄️</h3>
            <label for="ice-firerate">Fire Rate (ms): <span id="ice-firerate-val">900</span></label>
            <input type="range" id="ice-firerate" min="200" max="2500" value="900" step="50">
            <label for="ice-damage">Damage: <span id="ice-damage-val">20</span></label>
            <input type="range" id="ice-damage" min="5" max="100" value="20" step="5">
            <label for="ice-projspeed">Proj. Speed: <span id="ice-projspeed-val">5</span></label>
            <input type="range" id="ice-projspeed" min="1" max="12" value="5" step="0.5">
            <label for="ice-range">Range (px): <span id="ice-range-val">140</span></label>
            <input type="range" id="ice-range" min="50" max="250" value="140" step="10">
        </div>
        <div class="control-group"  id="lightning-controls">
            <h3>Lightning Tower ⚡</h3>
            <label for="lightning-firerate">Fire Rate (ms): <span id="lightning-firerate-val">700</span></label>
            <input type="range" id="lightning-firerate" min="100" max="1500" value="700" step="50">
            <label for="lightning-damage">Damage: <span id="lightning-damage-val">35</span></label>
            <input type="range" id="lightning-damage" min="10" max="150" value="35" step="5">
            <label for="lightning-range">Range (px): <span id="lightning-range-val">180</span></label>
            <input type="range" id="lightning-range" min="50" max="300" value="180" step="10">
        </div>
    </div>

    <script>
        const gameContainer = document.getElementById('game-container');
        const path = document.getElementById('path');
        const pathTop = path.offsetTop;
        const pathHeight = path.offsetHeight;
        const gameWidth = gameContainer.offsetWidth;

        let enemies = [];
        let projectiles = [];
        let enemyIdCounter = 0;
        let projectileIdCounter = 0;
        let effectIdCounter = 0;

        const ENEMY_HEALTH_MAX = 280;
        const ENEMY_SPEED = 0.75;
        const ENEMY_WIDTH = 30;
        const ENEMY_HEIGHT = 30;
        const ENEMY_SPAWN_INTERVAL = 1900;
        let lastEnemySpawnTime = 0;

        const ROCKET_TURN_RATE = 0.05;
        const ROCKET_WOBBLE_AMPLITUDE = 4;
        const ROCKET_WOBBLE_FREQUENCY = 0.2;
        const DEFAULT_TURRET_ANGLE_RAD = Math.PI / 2; // Pointing down path (positive Y) initially

        const towers = [
            {
                id: 'tower-rocket', baseEl: document.getElementById('tower-rocket-base'),
                pivotEl: document.querySelector('#tower-rocket-base .turret-pivot'),
                modelEl: document.querySelector('#tower-rocket-base .turret-model'),
                glowEl: document.querySelector('#tower-rocket-base .turret-glow-indicator'),
                type: 'rocket', barrelLength: 28, /* Adjusted slightly to be closer to visual tip */
                recoilClass: 'recoil-rocket',
                fireRate: 1500, damage: 70, projectileSpeed: 2, range: 200, lastShotTime: 0, currentAngleRad: DEFAULT_TURRET_ANGLE_RAD,
                chargeLevel: 0, projectileClass: 'rocket'
            },
            {
                id: 'tower-ice', baseEl: document.getElementById('tower-ice-base'),
                pivotEl: document.querySelector('#tower-ice-base .turret-pivot'),
                modelEl: document.querySelector('#tower-ice-base .turret-model'),
                glowEl: document.querySelector('#tower-ice-base .turret-glow-indicator'),
                type: 'ice', barrelLength: 26, /* Adjusted slightly */
                recoilClass: 'recoil-ice',
                fireRate: 900, damage: 20, projectileSpeed: 5, range: 140, lastShotTime: 0, currentAngleRad: DEFAULT_TURRET_ANGLE_RAD,
                chargeLevel: 0, projectileClass: 'ice-crystal-projectile'
            },
            {
                id: 'tower-lightning', baseEl: document.getElementById('tower-lightning-base'),
                pivotEl: document.querySelector('#tower-lightning-base .turret-pivot'),
                modelEl: document.querySelector('#tower-lightning-base .turret-model'),
                glowEl: document.querySelector('#tower-lightning-base .turret-glow-indicator'),
                type: 'lightning', barrelLength: 0, // Lightning originates from center of orb
                recoilClass: 'recoil-lightning',
                fireRate: 700, damage: 35, projectileSpeed: 0, range: 180, lastShotTime: 0, currentAngleRad: DEFAULT_TURRET_ANGLE_RAD,
                chargeLevel: 0
            }
        ];
        towers.forEach(tower => {
            tower.x = tower.baseEl.offsetLeft + tower.baseEl.offsetWidth / 2;
            tower.y = tower.baseEl.offsetTop + tower.baseEl.offsetHeight / 2;
            tower.pivotEl.style.transform = `rotate(${tower.currentAngleRad - Math.PI/2}rad)`;
        });


        function updateSliderValue(sliderId, displayId) { /* ... (same) ... */
            const slider = document.getElementById(sliderId); const display = document.getElementById(displayId);
            display.textContent = slider.value; return parseFloat(slider.value);
        }
        function setupControls() { /* ... (same) ... */
             towers.forEach(tower => {
                const type = tower.type;
                document.getElementById(`${type}-firerate`).addEventListener('input', () => { tower.fireRate = updateSliderValue(`${type}-firerate`, `${type}-firerate-val`); });
                document.getElementById(`${type}-damage`).addEventListener('input', () => { tower.damage = updateSliderValue(`${type}-damage`, `${type}-damage-val`); });
                 if (document.getElementById(`${type}-projspeed`)) { document.getElementById(`${type}-projspeed`).addEventListener('input', () => { tower.projectileSpeed = updateSliderValue(`${type}-projspeed`, `${type}-projspeed-val`); }); }
                document.getElementById(`${type}-range`).addEventListener('input', () => { tower.range = updateSliderValue(`${type}-range`, `${type}-range-val`); });
                tower.fireRate = parseFloat(document.getElementById(`${type}-firerate`).value); tower.damage = parseFloat(document.getElementById(`${type}-damage`).value);
                if (document.getElementById(`${type}-projspeed`)) { tower.projectileSpeed = parseFloat(document.getElementById(`${type}-projspeed`).value); }
                tower.range = parseFloat(document.getElementById(`${type}-range`).value);
            });
        }
        function spawnEnemy() { /* ... (same) ... */
            enemyIdCounter++; const enemyEl = document.createElement('div'); enemyEl.classList.add('enemy'); enemyEl.id = `enemy-${enemyIdCounter}`;
            enemyEl.style.left = `${gameWidth - ENEMY_WIDTH}px`; enemyEl.style.top = `${pathTop + (pathHeight / 2) - (ENEMY_HEIGHT / 2)}px`;
            const healthBar = document.createElement('div'); healthBar.classList.add('enemy-health-bar'); const healthFill = document.createElement('div'); healthFill.classList.add('enemy-health-fill'); healthBar.appendChild(healthFill); enemyEl.appendChild(healthBar);
            gameContainer.appendChild(enemyEl); enemies.push({ id: enemyIdCounter, el: enemyEl, x: gameWidth - ENEMY_WIDTH, y: pathTop + (pathHeight / 2) - (ENEMY_HEIGHT / 2), health: ENEMY_HEALTH_MAX, maxHealth: ENEMY_HEALTH_MAX, speed: ENEMY_SPEED, healthFillEl: healthFill, age: 0 });
        }
        function initialEnemyFill() { /* ... (same) ... */
            const numToFill = Math.floor(gameWidth / (ENEMY_WIDTH + 10));
            for (let i = 0; i < numToFill; i++) {
                const xPos = gameWidth - ENEMY_WIDTH - i * (ENEMY_WIDTH + 10); if (xPos + ENEMY_WIDTH < 0) break;
                enemyIdCounter++; const enemyEl = document.createElement('div'); enemyEl.classList.add('enemy'); enemyEl.id = `enemy-${enemyIdCounter}`;
                enemyEl.style.left = `${xPos}px`; enemyEl.style.top = `${pathTop + (pathHeight / 2) - (ENEMY_HEIGHT / 2)}px`;
                const healthBar = document.createElement('div'); healthBar.classList.add('enemy-health-bar'); const healthFill = document.createElement('div'); healthFill.classList.add('enemy-health-fill'); healthBar.appendChild(healthFill); enemyEl.appendChild(healthBar);
                gameContainer.appendChild(enemyEl); enemies.push({ id: enemyIdCounter, el: enemyEl, x: xPos, y: pathTop + (pathHeight / 2) - (ENEMY_HEIGHT / 2), health: ENEMY_HEALTH_MAX, maxHealth: ENEMY_HEALTH_MAX, speed: ENEMY_SPEED, healthFillEl: healthFill, age: 0 });
            }
        }
        function updateEnemies(currentTime) { /* ... (same) ... */
            if (currentTime - lastEnemySpawnTime > ENEMY_SPAWN_INTERVAL) { spawnEnemy(); lastEnemySpawnTime = currentTime; }
            for (let i = enemies.length - 1; i >= 0; i--) {
                const enemy = enemies[i]; enemy.x -= enemy.speed; enemy.age++; enemy.el.style.left = `${enemy.x}px`;
                const healthPercentage = (enemy.health / enemy.maxHealth) * 100; enemy.healthFillEl.style.width = `${Math.max(0, healthPercentage)}%`;
                if (enemy.health <= 0) { enemy.el.remove(); enemies.splice(i, 1); continue; }
                if (enemy.x + ENEMY_WIDTH < 0) { enemy.el.remove(); enemies.splice(i, 1); }
            }
        }
        function findTarget(tower) { /* ... (same) ... */
            let closestEnemy = null; let minDistance = tower.range;
            for (const enemy of enemies) {
                const enemyCenterX = enemy.x + ENEMY_WIDTH / 2; const enemyCenterY = enemy.y + ENEMY_HEIGHT / 2;
                if (enemyCenterY > pathTop - pathHeight && enemyCenterY < pathTop + pathHeight * 2) {
                     const distance = Math.sqrt(Math.pow(tower.x - enemyCenterX, 2) + Math.pow(tower.y - enemyCenterY, 2));
                    if (distance < minDistance) { minDistance = distance; closestEnemy = enemy; }
                }
            }
            return closestEnemy;
        }

        function createVisualEffect(x, y, type) {
            effectIdCounter++;
            const effectEl = document.createElement('div');
            effectEl.id = `effect-${effectIdCounter}`;
            effectEl.classList.add('visual-effect');
            let duration = 400;

            if (type === 'explosion') { effectEl.classList.add('explosion'); duration = 400; }
            else if (type === 'ice_shatter') {
                effectEl.classList.add('ice-shatter');
                for (let i = 0; i < 5; i++) { /* ... shards ... */
                    const shard = document.createElement('div'); shard.classList.add('ice-shard');
                    shard.style.width = `${2 + Math.random() * 3}px`; shard.style.height = `${5 + Math.random() * 5}px`;
                    shard.style.transform = `rotate(${Math.random() * 360}deg) translate(${Math.random()*5}px, ${Math.random()*5}px)`;
                    shard.style.left = `calc(50% - ${parseFloat(shard.style.width)/2}px)`; shard.style.top = `calc(50% - ${parseFloat(shard.style.height)/2}px)`;
                    effectEl.appendChild(shard);
                }
                duration = 300;
            }
            else if (type === 'lightning_impact') { effectEl.classList.add('lightning-impact-flash'); duration = 200;}
            else if (type === 'muzzle_puff') { effectEl.classList.add('muzzle-puff'); duration = 400; }

            effectEl.style.left = `${x}px`;
            effectEl.style.top = `${y}px`;
            gameContainer.appendChild(effectEl);
            setTimeout(() => effectEl.remove(), duration);
        }

        function createLightningBoltVisual(tower, target) { /* ... (same, but use tower.x, tower.y as origin, as barrelLength is 0 for lightning) ... */
            const numSegments = 5;
            const originX = tower.x; // Lightning orb center
            const originY = tower.y;
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
                gameContainer.appendChild(segmentEl); setTimeout(() => segmentEl.remove(), 150);
                currentX = nextX; currentY = nextY;
            }
            createVisualEffect(targetX, targetY, 'lightning_impact');
        }
        function createIceTrailParticle(x, y) { /* ... (same) ... */
            const particleEl = document.createElement('div'); particleEl.classList.add('ice-trail-particle');
            const driftX = (Math.random() - 0.5) * 15; const driftY = (Math.random() - 0.5) * 15;
            particleEl.style.setProperty('--drift-x', `${driftX}px`); particleEl.style.setProperty('--drift-y', `${driftY}px`);
            particleEl.style.left = `${x + (Math.random() - 0.5) * 5 - 2}px`; particleEl.style.top = `${y + (Math.random() - 0.5) * 5 - 2}px`;
            gameContainer.appendChild(particleEl); setTimeout(() => particleEl.remove(), 500 + Math.random() * 200);
        }

        function fireRecoil(tower) {
            if (tower.modelEl && tower.recoilClass) {
                tower.pivotEl.classList.add(tower.recoilClass);
                if(tower.type === 'ice' && tower.modelEl) tower.modelEl.classList.add('firing');

                // Muzzle Puff for Rocket and Ice
                if (tower.type === 'rocket' || tower.type === 'ice') {
                    const muzzleX = tower.x + tower.barrelLength * Math.cos(tower.currentAngleRad);
                    const muzzleY = tower.y + tower.barrelLength * Math.sin(tower.currentAngleRad);
                    createVisualEffect(muzzleX, muzzleY, 'muzzle_puff');
                }

                setTimeout(() => {
                    tower.pivotEl.classList.remove(tower.recoilClass);
                    if(tower.type === 'ice' && tower.modelEl) tower.modelEl.classList.remove('firing');
                }, 200);
            }
        }

        function launchProjectile(tower, target) {
            projectileIdCounter++;
            const projectileEl = document.createElement('div');
            projectileEl.classList.add('projectile', tower.projectileClass);
            projectileEl.id = `proj-${projectileIdCounter}`;

            const startX = tower.x + tower.barrelLength * Math.cos(tower.currentAngleRad);
            const startY = tower.y + tower.barrelLength * Math.sin(tower.currentAngleRad);
            
            // Set initial position for offsetWidth/Height calculation
            projectileEl.style.left = `0px`; projectileEl.style.top = `0px`;
            projectileEl.style.visibility = 'hidden'; // Keep hidden until correctly placed
            
            if (tower.type === 'rocket') { const flameEl = document.createElement('div'); flameEl.classList.add('rocket-flame'); projectileEl.appendChild(flameEl); }
            else if (tower.type === 'ice') { const crystalInnerDiv = document.createElement('div'); projectileEl.appendChild(crystalInnerDiv); }
            
            gameContainer.appendChild(projectileEl);
            
            // Now correctly position it
            projectileEl.style.left = `${startX - projectileEl.offsetWidth / 2}px`;
            projectileEl.style.top = `${startY - projectileEl.offsetHeight / 2}px`;
            projectileEl.style.visibility = 'visible';

            projectiles.push({
                id: projectileIdCounter, el: projectileEl, x: startX, y: startY,
                damage: tower.damage, speed: tower.projectileSpeed, target: target, towerType: tower.type,
                currentAngle: (tower.type === 'rocket' ? tower.currentAngleRad : Math.atan2(target.y + ENEMY_HEIGHT/2 - startY, target.x + ENEMY_WIDTH/2 - startX)),
                age: 0
            });
            tower.lastShotTime = performance.now();
            tower.chargeLevel = 0; // Reset charge
            if(tower.glowEl) { tower.glowEl.style.opacity = 0; tower.glowEl.style.boxShadow = '0 0 0px 0px transparent'; }
            fireRecoil(tower);
        }

        function updateTowers(currentTime) {
            towers.forEach(tower => {
                // Update charge level
                if (tower.lastShotTime > 0) { // Don't charge if never fired
                     tower.chargeLevel = Math.min(1, (currentTime - tower.lastShotTime) / tower.fireRate);
                } else if (tower.fireRate > 0){ // Initial charge up before first shot
                     tower.chargeLevel = Math.min(1, currentTime / tower.fireRate); // Simplified for initial
                } else {
                    tower.chargeLevel = 1; // Always ready if firerate is 0
                }


                if (tower.glowEl) {
                    tower.glowEl.style.opacity = tower.chargeLevel * 0.8; // Max opacity 0.8 for glow
                    const hue = 60 - (tower.chargeLevel * 60); // Yellow (60) to Red (0)
                    const spread = tower.chargeLevel * 6;   // Spread 0 to 6px
                    const blur = tower.chargeLevel * 12;    // Blur 0 to 12px
                    tower.glowEl.style.boxShadow = `0 0 ${blur}px ${spread}px hsla(${hue}, 100%, 50%, 0.7)`;
                }


                const target = findTarget(tower);
                if (target) {
                    const targetX = target.x + ENEMY_WIDTH / 2;
                    const targetY = target.y + ENEMY_HEIGHT / 2;
                    const angleToTargetRad = Math.atan2(targetY - tower.y, targetX - tower.x);
                    tower.currentAngleRad = angleToTargetRad;
                    tower.pivotEl.style.transform = `rotate(${angleToTargetRad - Math.PI/2}rad)`;

                    if (tower.chargeLevel >= 1.0) { // Fire when fully charged
                        if (tower.type === 'lightning') {
                            target.health -= tower.damage;
                            createLightningBoltVisual(tower, target);
                            tower.lastShotTime = currentTime;
                            tower.chargeLevel = 0; // Reset charge
                             if(tower.glowEl) { tower.glowEl.style.opacity = 0; tower.glowEl.style.boxShadow = '0 0 0px 0px transparent'; }
                            fireRecoil(tower);
                        } else {
                            launchProjectile(tower, target); // This also resets chargeLevel & lastShotTime
                        }
                    }
                }
            });
        }

        function updateProjectiles() { /* ... (same projectile update logic as before) ... */
             for (let i = projectiles.length - 1; i >= 0; i--) {
                const proj = projectiles[i]; proj.age++; const targetEnemy = proj.target;
                if (!targetEnemy || targetEnemy.health <= 0 || !enemies.includes(targetEnemy)) { proj.el.remove(); projectiles.splice(i, 1); continue; }
                const targetX = targetEnemy.x + ENEMY_WIDTH / 2; const targetY = targetEnemy.y + ENEMY_HEIGHT / 2;
                let dx = targetX - proj.x; let dy = targetY - proj.y; const distance = Math.sqrt(dx * dx + dy * dy);
                if (distance < Math.max(proj.speed, ENEMY_WIDTH / 2.5)) { // Slightly adjusted hit radius
                    targetEnemy.health -= proj.damage;
                    if (proj.towerType === 'rocket') createVisualEffect(targetX, targetY, 'explosion');
                    if (proj.towerType === 'ice') createVisualEffect(targetX, targetY, 'ice_shatter');
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
                    if (proj.towerType === 'ice' && proj.age % 4 === 0) { createIceTrailParticle(proj.x, proj.y); }
                }
                proj.x += moveX; proj.y += moveY;
                proj.el.style.left = `${proj.x - proj.el.offsetWidth/2}px`; proj.el.style.top = `${proj.y - proj.el.offsetHeight/2}px`;
                if (proj.x < -50 || proj.x > gameWidth + 50 || proj.y < -50 || proj.y > gameContainer.offsetHeight + 100) { proj.el.remove(); projectiles.splice(i, 1); }
            }
        }

        function gameLoop(timestamp) {
            updateEnemies(timestamp);
            updateTowers(timestamp);
            updateProjectiles();
            requestAnimationFrame(gameLoop);
        }

        // Initialize
        setupControls();
        initialEnemyFill();
        lastEnemySpawnTime = performance.now();
        // Set initial lastShotTime to allow initial charge-up visual
        const initialTime = performance.now();
        towers.forEach(tower => tower.lastShotTime = initialTime - tower.fireRate); // Start as if just fired to begin charge
        
        requestAnimationFrame(gameLoop);
    </script>
</body>
</html>