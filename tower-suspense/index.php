<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pixel Realms Defense - Prototype</title>
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
            background-color: #3b4048;
            image-rendering: pixelated;
            image-rendering: -moz-crisp-edges;
            image-rendering: crisp-edges;
            margin-bottom: 15px;
        }
        #ui-panel {
            width: 100%;
            padding: 10px;
            background-color: #2c313a;
            border-radius: 5px;
            box-sizing: border-box;
        }
        #stats {
            display: flex;
            justify-content: space-around;
            margin-bottom: 15px;
            font-size: 1.1em;
        }
        #stats span {
            padding: 5px 10px;
            background-color: #3b4048;
            border-radius: 3px;
        }
        #tower-selection {
            display: flex;
            justify-content: center;
            margin-bottom: 15px;
        }
        .tower-button, #start-wave-button {
            padding: 10px 15px;
            margin: 0 8px;
            background-color: #61afef;
            color: #1e222a;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-family: inherit;
            font-weight: bold;
            transition: background-color 0.2s ease;
        }
        .tower-button:hover, #start-wave-button:hover {
            background-color: #528bce;
        }
        .tower-button.selected {
            background-color: #c678dd;
            box-shadow: 0 0 8px #c678dd;
        }
        #start-wave-button {
            background-color: #98c379;
        }
        #start-wave-button:hover {
            background-color: #80a863;
        }
        #message-area {
            margin-top: 10px;
            min-height: 20px;
            text-align: center;
            font-weight: bold;
            color: #98c379;
        }
    </style>
</head>
<body>
    <div id="game-wrapper">
        <canvas id="gameCanvas"></canvas>
        <div id="ui-panel">
            <div id="stats">
                <span>Gold: <span id="goldDisplay">100</span></span>
                <span>Lives: <span id="livesDisplay">20</span></span>
                <span>Wave: <span id="waveDisplay">0</span> / <span id="totalWavesDisplay">7</span></span> <!-- Updated total -->
            </div>
            <div id="tower-selection">
                <button class="tower-button" data-tower-type="archer" data-cost="50">Archer (50G)</button>
                <button class="tower-button" data-tower-type="cannon" data-cost="100">Cannon (100G)</button>
                <button class="tower-button" data-tower-type="magic" data-cost="75">Magic (75G)</button>
            </div>
            <button id="start-wave-button">Start Next Wave</button>
            <div id="message-area">Game Initialized!</div>
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

        const goldDisplay = document.getElementById('goldDisplay');
        const livesDisplay = document.getElementById('livesDisplay');
        const waveDisplay = document.getElementById('waveDisplay');
        const totalWavesDisplay = document.getElementById('totalWavesDisplay');
        const messageArea = document.getElementById('message-area');
        const towerButtons = document.querySelectorAll('.tower-button');
        const startWaveButton = document.getElementById('start-wave-button');

        let gold = 100;
        let lives = 20;
        let currentWaveNumber = 0;
        // const totalWaves = 10; // This will be determined by waveDefinitions.length
        let selectedTowerType = null;
        let selectedTowerCost = 0;

        let towers = [];
        let enemies = [];
        let projectiles = [];

        let path = [];
        let grid = [];

        let isWaveActive = false;
        let gameTime = 0;
        let lastTime = 0;

        // Wave definitions: [ { type, count, spawnInterval (ms), health, speed, goldValue, color, radius }, ... ]
        const waveDefinitions = [
            // Wave 1
            { type: 'goblin', subWave: [
                { count: 10, spawnInterval: 1000, health: 50, speed: 60, goldValue: 5, color: '#e06c75', radius: TILE_SIZE * 0.25 }
            ]},
            // Wave 2
            { type: 'orc', subWave: [
                { count: 5, spawnInterval: 1500, health: 150, speed: 40, goldValue: 10, color: '#d19a66', radius: TILE_SIZE * 0.35 }
            ]},
            // Wave 3 - Mix of Goblins and Orcs
            { type: 'mixed1', subWave: [
                { count: 8, spawnInterval: 800, health: 50, speed: 60, goldValue: 5, color: '#e06c75', radius: TILE_SIZE * 0.25 }, // Goblins
                { count: 4, spawnInterval: 2000, health: 150, speed: 40, goldValue: 10, color: '#d19a66', radius: TILE_SIZE * 0.35 }  // Orcs after goblins
            ]},
            // Wave 4 - Faster Goblins
            { type: 'fast_goblins', subWave: [
                { count: 15, spawnInterval: 600, health: 40, speed: 80, goldValue: 4, color: '#ef5966', radius: TILE_SIZE * 0.20 } // Slightly different color for faster
            ]},
            // Wave 5 - Tougher Orcs
            { type: 'tough_orcs', subWave: [
                { count: 7, spawnInterval: 1800, health: 250, speed: 35, goldValue: 15, color: '#c08050', radius: TILE_SIZE * 0.40 } // Slightly different color for tougher
            ]},
            // Wave 6 - "Swarm" of weak, fast enemies
            { type: 'swarm', subWave: [
                { count: 25, spawnInterval: 400, health: 30, speed: 90, goldValue: 3, color: '#ff7f7f', radius: TILE_SIZE * 0.18 }
            ]},
            // Wave 7 - Mini-Boss wave with escorts
            { type: 'mini_boss', subWave: [
                { count: 10, spawnInterval: 900, health: 50, speed: 60, goldValue: 5, color: '#e06c75', radius: TILE_SIZE * 0.25 }, // Goblin escorts
                { count: 1, spawnInterval: 3000, health: 800, speed: 30, goldValue: 50, color: '#8B0000', radius: TILE_SIZE * 0.50 }, // "Mini-Boss" Ogre
                { count: 5, spawnInterval: 1000, health: 150, speed: 40, goldValue: 10, color: '#d19a66', radius: TILE_SIZE * 0.35 }  // Orc escorts after boss
            ]},
        ];
        let currentWaveIndex = -1; // Index for waveDefinitions
        let currentSubWaveIndex = -1; // Index for subWave array
        let currentWaveEnemiesToSpawn = 0;
        let currentWaveSpawnTimer = 0;
        let currentEnemyConfig = null;


        // --- Helper Functions ---
        function distance(x1, y1, x2, y2) {
            const dx = x2 - x1;
            const dy = y2 - y1;
            return Math.sqrt(dx * dx + dy * dy);
        }

        // --- Entity Classes (Constructors) ---
        function Enemy(config, startX, startY) {
            this.x = startX;
            this.y = startY;
            // this.type = config.type; // Type is now more general in waveDefinition
            this.maxHealth = config.health;
            this.health = config.health;
            this.speed = config.speed; // pixels per second
            this.goldValue = config.goldValue;
            this.color = config.color;
            this.radius = config.radius;
            this.pathIndex = 0; // Current target waypoint in the path
            this.isAlive = true;

            this.update = function(deltaTime) {
                if (!this.isAlive) return;

                if (this.pathIndex >= path.length) {
                    lives--;
                    this.isAlive = false;
                    if (lives <= 0) {
                        showMessage("GAME OVER!", "error");
                        isWaveActive = false;
                    }
                    updateUIDisplays();
                    return;
                }

                const targetWaypoint = path[this.pathIndex];
                const targetX = targetWaypoint.c * TILE_SIZE + TILE_SIZE / 2;
                const targetY = targetWaypoint.r * TILE_SIZE + TILE_SIZE / 2;

                const dx = targetX - this.x;
                const dy = targetY - this.y;
                const distToWaypoint = Math.sqrt(dx * dx + dy * dy);

                if (distToWaypoint < this.speed * deltaTime * 1.1) { // Add a small buffer to prevent overshooting
                    this.x = targetX;
                    this.y = targetY;
                    this.pathIndex++;
                } else {
                    const angle = Math.atan2(dy, dx);
                    this.x += Math.cos(angle) * this.speed * deltaTime;
                    this.y += Math.sin(angle) * this.speed * deltaTime;
                }
            };

            this.takeDamage = function(amount) {
                this.health -= amount;
                if (this.health <= 0) {
                    this.health = 0;
                    this.isAlive = false;
                    gold += this.goldValue;
                    updateUIDisplays();
                }
            };

            this.draw = function(ctx) {
                if (!this.isAlive) return;
                ctx.fillStyle = this.color;
                ctx.beginPath();
                ctx.arc(this.x, this.y, this.radius, 0, 2 * Math.PI);
                ctx.fill();

                if (this.health < this.maxHealth) {
                    const barWidth = this.radius * 1.5;
                    const barHeight = 4;
                    const barX = this.x - barWidth / 2;
                    const barY = this.y - this.radius - barHeight - 2;
                    ctx.fillStyle = '#5c6370';
                    ctx.fillRect(barX, barY, barWidth, barHeight);
                    ctx.fillStyle = '#98c379';
                    ctx.fillRect(barX, barY, barWidth * (this.health / this.maxHealth), barHeight);
                }
            };
        }

        function Tower(r, c, type, cost) {
            this.r = r;
            this.c = c;
            this.x = c * TILE_SIZE + TILE_SIZE / 2;
            this.y = r * TILE_SIZE + TILE_SIZE / 2;
            this.type = type;
            this.cost = cost;
            this.lastShotTime = 0;
            this.target = null;

            this.range = TILE_SIZE * 3;
            this.fireRate = 1;
            this.projectileSpeed = 250;
            this.projectileDamage = 10;
            this.projectileRadius = TILE_SIZE * 0.1;
            this.color = '#61afef';
            this.projectileColor = '#c678dd';

            if (type === 'archer') {
                this.range = TILE_SIZE * 3.5;
                this.fireRate = 2; // shots per sec
                this.projectileDamage = 15;
                this.color = '#98c379';
                this.projectileColor = '#98c379';
            } else if (type === 'cannon') {
                this.range = TILE_SIZE * 2.5;
                this.fireRate = 0.5; // shots per sec
                this.projectileDamage = 50;
                this.projectileSpeed = 200;
                this.projectileRadius = TILE_SIZE * 0.15;
                this.color = '#abb2bf';
                this.projectileColor = '#abb2bf';
            } else if (type === 'magic') {
                this.range = TILE_SIZE * 3;
                this.fireRate = 1.25; // shots per sec
                this.projectileDamage = 25;
                this.color = '#61afef';
                this.projectileColor = '#c678dd';
            }

            this.findTarget = function() {
                this.target = null;
                let closestDist = this.range + 1;

                for (let enemy of enemies) {
                    if (enemy.isAlive) {
                        const d = distance(this.x, this.y, enemy.x, enemy.y);
                        if (d <= this.range && d < closestDist) {
                            closestDist = d;
                            this.target = enemy;
                        }
                    }
                }
            };

            this.update = function(gameTime, deltaTime) {
                this.findTarget();

                if (this.target && (gameTime - this.lastShotTime) >= (1000 / this.fireRate)) {
                    this.lastShotTime = gameTime;
                    projectiles.push(new Projectile(this.x, this.y, this.target, this.projectileDamage, this.projectileSpeed, this.projectileRadius, this.projectileColor));
                }
            };

            this.draw = function(ctx) {
                ctx.fillStyle = this.color;
                ctx.beginPath();
                ctx.arc(this.x, this.y, TILE_SIZE * 0.4, 0, 2 * Math.PI);
                ctx.fill();
            };
        }

        function Projectile(startX, startY, target, damage, speed, radius, color) {
            this.x = startX;
            this.y = startY;
            this.target = target;
            this.damage = damage;
            this.speed = speed;
            this.radius = radius;
            this.color = color;
            this.isActive = true;

            this.update = function(deltaTime) {
                if (!this.isActive || !this.target || !this.target.isAlive) {
                    this.isActive = false;
                    return;
                }

                const dx = this.target.x - this.x;
                const dy = this.target.y - this.y;
                const distToTarget = Math.sqrt(dx * dx + dy * dy);

                if (distToTarget < this.speed * deltaTime || distToTarget < this.target.radius * 0.8) { // Hit closer to center
                    this.target.takeDamage(this.damage);
                    this.isActive = false;
                } else {
                    const angle = Math.atan2(dy, dx);
                    this.x += Math.cos(angle) * this.speed * deltaTime;
                    this.y += Math.sin(angle) * this.speed * deltaTime;
                }
            };

            this.draw = function(ctx) {
                if (!this.isActive) return;
                ctx.fillStyle = this.color;
                ctx.beginPath();
                ctx.arc(this.x, this.y, this.radius, 0, 2 * Math.PI);
                ctx.fill();
            };
        }

        // --- Game Initialization ---
        function initializeGame() {
            console.log("Initializing game...");
            for (let r = 0; r < GRID_ROWS; r++) {
                grid[r] = [];
                for (let c = 0; c < GRID_COLS; c++) {
                    grid[r][c] = { type: 'grass', tower: null };
                }
            }
            path = [
                { r: 7, c: 0 }, { r: 7, c: 1 }, { r: 7, c: 2 }, { r: 6, c: 2 }, { r: 5, c: 2 },
                { r: 5, c: 3 }, { r: 5, c: 4 }, { r: 5, c: 5 }, { r: 5, c: 6 }, { r: 4, c: 6 },
                { r: 3, c: 6 }, { r: 3, c: 7 }, { r: 3, c: 8 }, { r: 3, c: 9 }, { r: 3, c: 10 },
                { r: 4, c: 10 }, { r: 5, c: 10 }, { r: 6, c: 10 }, { r: 7, c: 10 }, { r: 7, c: 11 },
                { r: 7, c: 12 }, { r: 7, c: 13 }, { r: 7, c: 14 }, { r: 7, c: 15 }, { r: 7, c: 16 },
                { r: 7, c: 17 }, { r: 7, c: 18 }, { r: 7, c: 19 }
            ];
            path.forEach(segment => {
                if (segment.r >= 0 && segment.r < GRID_ROWS && segment.c >= 0 && segment.c < GRID_COLS) {
                    grid[segment.r][segment.c].type = 'path';
                }
            });

            gold = 100;
            lives = 20;
            currentWaveIndex = -1; // Start before the first wave
            currentSubWaveIndex = -1;
            enemies = [];
            projectiles = [];
            towers = [];
            grid.forEach(row => row.forEach(cell => cell.tower = null)); // Clear towers from grid
            isWaveActive = false;

            updateUIDisplays();
            showMessage("Place your towers! Press Start Wave.");
        }

        // --- UI Update Functions ---
        function updateUIDisplays() {
            goldDisplay.textContent = gold;
            livesDisplay.textContent = lives;
            waveDisplay.textContent = currentWaveIndex + 1; // Display 1-based wave number
            totalWavesDisplay.textContent = waveDefinitions.length;
        }

        function showMessage(msg, type = 'info') {
            messageArea.textContent = msg;
            if (type === 'error') messageArea.style.color = '#e06c75';
            else if (type === 'success') messageArea.style.color = '#98c379';
            else messageArea.style.color = '#61afef';
        }

        // --- Event Listeners ---
        towerButtons.forEach(button => {
            button.addEventListener('click', () => {
                if (lives <= 0) return;
                if (selectedTowerType === button.dataset.towerType) {
                    selectedTowerType = null;
                    selectedTowerCost = 0;
                    button.classList.remove('selected');
                    showMessage("Tower selection cleared.");
                } else {
                    selectedTowerType = button.dataset.towerType;
                    selectedTowerCost = parseInt(button.dataset.cost);
                    towerButtons.forEach(btn => btn.classList.remove('selected'));
                    button.classList.add('selected');
                    showMessage(`${selectedTowerType.charAt(0).toUpperCase() + selectedTowerType.slice(1)} Tower selected. Cost: ${selectedTowerCost}G`);
                }
            });
        });

        function startNextSubWave() {
            currentSubWaveIndex++;
            if (currentSubWaveIndex < waveDefinitions[currentWaveIndex].subWave.length) {
                currentEnemyConfig = waveDefinitions[currentWaveIndex].subWave[currentSubWaveIndex];
                currentWaveEnemiesToSpawn = currentEnemyConfig.count;
                currentWaveSpawnTimer = 0; // Start spawning immediately or after a short delay if needed
            } else {
                // All subwaves for the current main wave are done
                // The check for wave completion (all enemies defeated) is in the update loop
            }
        }

        startWaveButton.addEventListener('click', () => {
            if (lives <= 0) {
                initializeGame(); // Allow restarting if game over
                return;
            }
            if (isWaveActive) {
                showMessage("Wave already in progress!", "error");
                return;
            }
            if (currentWaveIndex + 1 >= waveDefinitions.length && enemies.length === 0) {
                 showMessage("All waves cleared! YOU WIN! Click to Play Again.", "success");
                 // currentWaveIndex = -1; // Ready for restart
                 // initializeGame(); // Or provide a separate restart button
                 return;
            }

            currentWaveIndex++;
            if (currentWaveIndex >= waveDefinitions.length) {
                // This case should ideally be caught by the one above if all enemies are cleared
                showMessage("All waves cleared! YOU WIN! Click to Play Again.", "success");
                return;
            }

            isWaveActive = true;
            currentSubWaveIndex = -1; // Reset for the new main wave
            startNextSubWave(); // Start the first sub-wave

            updateUIDisplays();
            showMessage(`Wave ${currentWaveIndex + 1} of ${waveDefinitions.length} (${waveDefinitions[currentWaveIndex].type}) started!`, "info");
        });

        canvas.addEventListener('click', (event) => {
            if (lives <= 0) return;
            if (!selectedTowerType) {
                showMessage("Select a tower type first!", "info");
                return;
            }

            const rect = canvas.getBoundingClientRect();
            const x = event.clientX - rect.left;
            const y = event.clientY - rect.top;
            const col = Math.floor(x / TILE_SIZE);
            const row = Math.floor(y / TILE_SIZE);

            if (row >= 0 && row < GRID_ROWS && col >= 0 && col < GRID_COLS) {
                if (grid[row][col].type === 'path') {
                    showMessage("Cannot place tower on the path!", "error"); return;
                }
                if (grid[row][col].tower) {
                    showMessage("Cell already occupied by a tower!", "error"); return;
                }
                if (gold < selectedTowerCost) {
                    showMessage("Not enough gold!", "error"); return;
                }

                gold -= selectedTowerCost;
                const newTower = new Tower(row, col, selectedTowerType, selectedTowerCost);
                towers.push(newTower);
                grid[row][col].tower = newTower;

                showMessage(`${selectedTowerType} tower placed.`, "success");
                updateUIDisplays();
            }
        });

        // --- Game Loop Functions ---
        function update(deltaTime) {
            if (lives <= 0 && isWaveActive) { // Added isWaveActive check to ensure game truly stops
                isWaveActive = false; // Explicitly stop wave activity
                showMessage(`GAME OVER! Final Wave: ${currentWaveIndex + 1}. Click Start Wave to Play Again.`, "error");
                startWaveButton.textContent = "Play Again?"; // Change button text
                return; // Stop updates if game over
            }
            if (!isWaveActive && currentWaveIndex + 1 >= waveDefinitions.length && enemies.length === 0 && lives > 0) {
                // Win condition already handled in startWaveButton, this is a safeguard
                startWaveButton.textContent = "Play Again?";
                return;
            }


            gameTime += deltaTime * 1000;

            // Spawn Enemies
            if (isWaveActive && currentEnemyConfig) {
                if (currentWaveEnemiesToSpawn > 0) {
                    currentWaveSpawnTimer -= deltaTime * 1000;
                    if (currentWaveSpawnTimer <= 0) {
                        const startTile = path[0];
                        const startX = startTile.c * TILE_SIZE + TILE_SIZE / 2;
                        const startY = startTile.r * TILE_SIZE + TILE_SIZE / 2;
                        enemies.push(new Enemy(currentEnemyConfig, startX, startY));
                        currentWaveEnemiesToSpawn--;
                        currentWaveSpawnTimer = currentEnemyConfig.spawnInterval;
                    }
                } else if (currentSubWaveIndex < waveDefinitions[currentWaveIndex].subWave.length - 1) {
                    // Current sub-wave finished spawning, try to start next sub-wave
                    startNextSubWave();
                } else if (enemies.length === 0) {
                    // All sub-waves done spawning for this main wave, and all enemies defeated
                    isWaveActive = false;
                    if (currentWaveIndex + 1 >= waveDefinitions.length) {
                        showMessage(`All waves cleared! YOU WIN! Final Score: ${gold}`, "success");
                        startWaveButton.textContent = "Play Again?";
                    } else {
                        showMessage(`Wave ${currentWaveIndex + 1} cleared! Prepare for the next!`, "success");
                    }
                }
            }

            towers.forEach(tower => tower.update(gameTime, deltaTime));

            for (let i = enemies.length - 1; i >= 0; i--) {
                enemies[i].update(deltaTime);
                if (!enemies[i].isAlive) {
                    enemies.splice(i, 1);
                }
            }

            for (let i = projectiles.length - 1; i >= 0; i--) {
                projectiles[i].update(deltaTime);
                if (!projectiles[i].isActive) {
                    projectiles.splice(i, 1);
                }
            }
        }

        function draw() {
            ctx.fillStyle = '#3b4048';
            ctx.fillRect(0, 0, canvas.width, canvas.height);

            ctx.fillStyle = '#565c64';
            path.forEach(segment => {
                ctx.fillRect(segment.c * TILE_SIZE, segment.r * TILE_SIZE, TILE_SIZE, TILE_SIZE);
            });

            towers.forEach(tower => tower.draw(ctx));
            projectiles.forEach(projectile => projectile.draw(ctx)); // Draw projectiles before enemies
            enemies.forEach(enemy => enemy.draw(ctx));


            // Tower Placement Preview / Range (Optional)
            // if (selectedTowerType && mouseOverCanvas) {
            //    const col = Math.floor(mouseX / TILE_SIZE);
            //    const row = Math.floor(mouseY / TILE_SIZE);
            //    if (isValidPlacement(row, col)) {
            //        ctx.fillStyle = 'rgba(0, 255, 0, 0.3)';
            //        ctx.fillRect(col * TILE_SIZE, row * TILE_SIZE, TILE_SIZE, TILE_SIZE);
            //        // Draw potential range
            //    } else {
            //        ctx.fillStyle = 'rgba(255, 0, 0, 0.3)';
            //        ctx.fillRect(col * TILE_SIZE, row * TILE_SIZE, TILE_SIZE, TILE_SIZE);
            //    }
            // }
        }

        function gameLoop(timestamp) {
            if (!lastTime) lastTime = timestamp;
            const deltaTime = (timestamp - lastTime) / 1000;
            lastTime = timestamp;

            if (deltaTime > 0.1) { // Max delta time to prevent large jumps if tab loses focus
                // console.warn("Large deltaTime detected, capping to 0.1s:", deltaTime);
                // update(0.1); // Cap delta time
            } else {
                // update(deltaTime);
            }
            update(Math.min(deltaTime, 0.1)); // Cap delta time

            draw();

            requestAnimationFrame(gameLoop);
        }

        document.addEventListener('DOMContentLoaded', () => {
            initializeGame();
            requestAnimationFrame(gameLoop);
        });
    </script>
</body>
</html>