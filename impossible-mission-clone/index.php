<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>C64 Style Platformer - Agent X</title>
    <style>
        body {
            background-color: #0000AA; color: #FFFF77; font-family: 'Courier New', Courier, monospace;
            display: flex; flex-direction: column; align-items: center; justify-content: flex-start;
            min-height: 100vh; margin: 0; padding-top: 10px; overflow-x: hidden;
        }
        #main-content { display: flex; flex-direction: column; align-items: center; }
        #controls-container {
            background-color: #303030; padding: 10px; border-radius: 5px; margin-bottom: 10px;
            display: flex; flex-wrap: wrap; gap: 15px; justify-content: center; max-width: 700px;
        }
        .slider-group {
            display: flex; flex-direction: column; align-items: center; min-width: 140px;
        }
        .slider-group label { font-size: 11px; margin-bottom: 2px; }
        .slider-group input[type="range"] { width: 120px; }
        .slider-group span { font-size: 10px; min-width: 20px; text-align: center; }
        #pause-button {
            padding: 8px 15px; background-color: #FFFF77; color: #0000AA; border: 2px solid #CCCC44;
            border-radius: 3px; font-weight: bold; cursor: pointer; font-size: 14px;
        }
        #pause-button:hover { background-color: #FFFFAA; }

        #game-container {
            width: 640px; height: 400px; background-color: #404040;
            border: 4px solid #707070; position: relative; overflow: hidden; margin-bottom: 5px;
        }
        #world {
            position: relative;
            width: 640px;
            height: 400px;
        }

        #player {
            width: 20px; height: 30px; background-color: #FF5555;
            position: absolute; bottom: 0; left: 50px; transform-origin: bottom center;
            z-index: 10;
        }
        #player::before {
            content: ''; display: block; width: 16px; height: 16px; background-color: #FFFF77;
            border-radius: 3px; position: absolute; bottom: 100%; left: 50%;
            transform: translateX(-50%);
            box-shadow: inset 0 0 0 1px #000000;
        }
        #player::after {
            content: ''; display: block; position: absolute;
            width: 10px; height: 6px; background-color: #33DD33;
            border: 1px solid #119911;
            left: 50%; top: -23px;
            z-index: 11;
        }
        #player.facing-right::after { transform: translateX(-20%); }
        #player.facing-left::after { transform: translateX(-80%); }


        #player.is-walking-left { transform: skewX(8deg); }
        #player.is-walking-right { transform: skewX(-8deg); }
        #player.is-jumping { transform: rotate(0deg); }


        .platform { background-color: #707070; position: absolute; z-index: 1;}
        #platform1 { width: 640px; height: 40px; bottom: 0px; left: 0px; background-color: #505050; }
        #platform2 { width: 200px; height: 16px; bottom: 100px; left: 150px; }
        #platform3 { width: 150px; height: 16px; bottom: 200px; left: 350px; }
        #platform4 { width: 180px; height: 16px; bottom: 300px; left: 50px; }

        .collectible {
            width: 12px; height: 12px; background-color: gold; border: 1px solid darkgoldenrod;
            border-radius: 50%; position: absolute; box-shadow: 0 0 5px yellow; z-index: 5;
        }

        .monster { position: absolute; z-index: 8; /* Container for monster and its health bar */ }
        .monster-roller {
            width: 24px; height: 28px; background-color: #8888FF; border-radius: 50%;
            border: 2px solid #5555AA; display: flex; align-items: center; justify-content: center;
        }
        .monster-roller::before { /* Eye */
            content: ''; display: block; width: 6px; height: 6px; background-color: #FFFFFF;
            border-radius: 50%; border: 1px solid black;
        }
        .monster-stomper {
            width: 28px; height: 40px; background-color: #AA6666; border: 2px solid #773333;
        }
        .monster-stomper::before { /* Eye */
            content: ''; display: block; width: 10px; height: 8px; background-color: #DDDD77;
            margin: 3px auto 0; border: 1px solid #AAAA44;
        }
        .monster-stomper.stomp-up { transform: translateY(-2px); }

        /* NEW: Health Bar Styles */
        .monster-health-bar-container {
            position: absolute;
            bottom: -7px; /* Position below the monster visual */
            left: 50%;
            transform: translateX(-50%);
            width: 20px; /* Fixed width for C64 feel */
            height: 3px; /* Slimmer bar */
            background-color: #222; /* Dark background for contrast */
            border: 1px solid #000;
            box-sizing: border-box;
            z-index: 9; 
        }
        .monster-health-bar {
            width: 100%; /* Starts full */
            height: 100%;
            background-color: #DD3333; /* C64-ish Red */
            transition: width 0.1s linear; /* Smooth health loss */
        }


        .projectile {
            width: 8px; height: 3px; background-color: #FFFFA0;
            border: 1px solid #CCCC77;
            position: absolute; z-index: 9;
        }

        #score-display { font-size: 18px; color: #FFFF77; margin-bottom: 3px; text-align: center; }
        #pause-overlay {
            position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            background-color: rgba(0,0,0,0.6); color: #FFFF77;
            display: none;
            justify-content: center; align-items: center;
            font-size: 48px; text-shadow: 2px 2px #0000AA; z-index: 1000;
        }
        #log-display-container {
             width: 640px; height: 50px; background-color: rgba(20,20,20,0.8); color: #0f0;
             font-family: monospace; font-size: 9px; overflow-y: scroll; padding: 3px;
             border: 1px solid #0f0; z-index: 1001; box-sizing: border-box; margin-top: 3px;
        }
    </style>
</head>
<body>
    <div id="main-content">
        <h1>Agent X - Impossible Mission</h1>
        <div id="controls-container">
            <div class="slider-group">
                <label for="speed-slider">Player Speed</label>
                <input type="range" id="speed-slider" min="1" max="15" value="5">
                <span id="speed-value">5</span>
            </div>
            <div class="slider-group">
                <label for="gravity-slider">Gravity</label>
                <input type="range" id="gravity-slider" min="0.1" max="2.5" step="0.1" value="1.1"> <!-- NEW DEFAULT -->
                <span id="gravity-value">1.1</span>
            </div>
            <div class="slider-group">
                <label for="jump-slider">Jump Strength</label>
                <input type="range" id="jump-slider" min="5" max="25" value="16">
                <span id="jump-value">16</span>
            </div>
             <div class="slider-group">
                <label for="friction-slider">Air Friction</label>
                <input type="range" id="friction-slider" min="0" max="0.2" step="0.01" value="0.10"> <!-- NEW DEFAULT -->
                <span id="friction-value">0.10</span>
            </div>
            <button id="pause-button">PAUSE</button>
        </div>

        <div id="game-container">
            <div id="world">
                <div id="player"></div>
                <div class="platform" id="platform1"></div>
                <div class="platform" id="platform2"></div>
                <div class="platform" id="platform3"></div>
                <div class="platform" id="platform4"></div>
            </div>
            <div id="pause-overlay">PAUSED</div>
        </div>
        <div id="score-display">Score: 0</div>
        <div id="log-display-container"></div>
    </div>

    <script>
        const gameContainer = document.getElementById('game-container');
        const world = document.getElementById('world');
        const player = document.getElementById('player');
        const platforms = Array.from(document.querySelectorAll('.platform'));
        const logDisplayContainer = document.getElementById('log-display-container');
        const scoreDisplay = document.getElementById('score-display');
        const pauseButton = document.getElementById('pause-button');
        const pauseOverlay = document.getElementById('pause-overlay');

        let gameLog = [];
        const MAX_LOG_LINES = 10;
        function logToDisplay(message, type = "info") {
            const timestamp = new Date().toLocaleTimeString();
            if (type === "error") console.error(`[${timestamp}] ${message}`);
            gameLog.push(`[${type.toUpperCase()}] ${message}`);
            if (gameLog.length > MAX_LOG_LINES) gameLog.shift();
            logDisplayContainer.innerHTML = gameLog.join('<br>');
            logDisplayContainer.scrollTop = logDisplayContainer.scrollHeight;
        }

        function safeParseFloat(value, defaultValue = 0) {
            const num = parseFloat(value);
            return isNaN(num) ? defaultValue : num;
        }

        let isPaused = false;
        let animationFrameId;

        // --- Game Parameters (NEW DEFAULTS) ---
        let moveSpeed = 5;
        let gravity = 1.1;      // UPDATED
        let jumpStrength = 16;
        let airFriction = 0.10; // UPDATED
        const PLAYER_BOUNCE_STRENGTH_FACTOR = 0.75; // For bouncing on monsters
        const MONSTER_INITIAL_HEALTH = 3;
        const PROJECTILE_DAMAGE = 1;

        // --- Player State ---
        let playerX = 50, playerY, playerStartX = 50, playerStartY;
        const playerBodyHeight = safeParseFloat(getComputedStyle(player).height);
        const playerHeadHeight = 16;
        const playerTotalVisualHeight = playerBodyHeight + playerHeadHeight;
        const playerWidth = safeParseFloat(getComputedStyle(player).width);

        let velocityX = 0, velocityY = 0, isJumping = false, onPlatformState = true, score = 0;
        let playerFacingDirection = 1;
        let isBeingShoved = false;
        let shovingMonsterId = null;

        // --- Player Y Initialization ---
        const initialGroundPlatform = platforms.find(p => p.id === 'platform1');
        if (initialGroundPlatform) {
            playerStartY = safeParseFloat(getComputedStyle(initialGroundPlatform).height); // CORRECTED TYPO
            playerY = playerStartY;
        } else {
            playerStartY = 0; playerY = 0;
            logToDisplay("CRITICAL: Platform1 not found! playerY defaulted to 0.", "error");
        }

        const gameContainerWidth = safeParseFloat(getComputedStyle(gameContainer).width);
        let WORLD_WIDTH = gameContainerWidth;

        const keysPressed = { ArrowLeft: false, ArrowRight: false, ArrowUp: false, Space: false, Shoot: false };

        const collectibles = []; const COLLECTIBLE_VALUE = 10;
        function createCollectible(x, y, id) {
            const item = document.createElement('div');
            item.classList.add('collectible'); item.id = `collectible-${id}`;
            item.style.left = x + 'px'; item.style.bottom = y + 'px';
            world.appendChild(item);
            collectibles.push({ element: item, x, y, width: 12, height: 12, id: item.id, collected: false });
        }
        function setupCollectibles() {
            collectibles.forEach(c => c.element.remove()); collectibles.length = 0;
            createCollectible(200, 120, 1); createCollectible(400, 220, 2);
            createCollectible(100, 320, 3); createCollectible(300, 60, 4); createCollectible(550, 120, 5);
        }

        const monsters = []; let monsterIdCounter = 0;
        function createMonster(type, x, y, patrolMinX, patrolMaxX, speed = 1) {
            const monsterElement = document.createElement('div');
            monsterElement.classList.add('monster', `monster-${type}`); monsterElement.id = `monster-${monsterIdCounter++}`;
            monsterElement.style.left = x + 'px'; monsterElement.style.bottom = y + 'px';
            
            const healthBarContainer = document.createElement('div');
            healthBarContainer.className = 'monster-health-bar-container';
            const healthBar = document.createElement('div');
            healthBar.className = 'monster-health-bar';
            healthBarContainer.appendChild(healthBar);
            monsterElement.appendChild(healthBarContainer);
            
            world.appendChild(monsterElement);

            let monsterWidth, monsterHeight;
            if (type === 'roller') { monsterWidth = 24; monsterHeight = 28; }
            else if (type === 'stomper') { monsterWidth = 28; monsterHeight = 40; }
            else { monsterWidth = 20; monsterHeight = 20; }

            monsters.push({
                element: monsterElement, type, id: monsterElement.id,
                x, y, width: monsterWidth, height: monsterHeight,
                speed, direction: 1, patrolMinX, patrolMaxX,
                stompCounter: 0, stompDelay: 30, justTurned: false,
                health: MONSTER_INITIAL_HEALTH, maxHealth: MONSTER_INITIAL_HEALTH,
                healthBarElement: healthBar
            });
        }
        function updateMonsterHealthBar(monster) {
            if (monster.healthBarElement) {
                const healthPercentage = Math.max(0, (monster.health / monster.maxHealth) * 100);
                monster.healthBarElement.style.width = healthPercentage + '%';
            }
        }
        function setupMonsters() {
            monsters.forEach(m => m.element.remove()); monsters.length = 0; monsterIdCounter = 0;
            createMonster('roller', 160, 100 + 16, 160, 160 + 200 - 24 - 5, 1.5);
            createMonster('stomper', 360, 200 + 16, 360, 360 + 150 - 28 - 5, 0.8);
            createMonster('roller', 450, 40, 450, 600 - 24 - 5, 2);
        }
        function updateMonstersMovement() {
            monsters.forEach(monster => {
                monster.justTurned = false;
                const prevDirection = monster.direction;
                monster.x += monster.speed * monster.direction;

                if (monster.x + monster.width > monster.patrolMaxX) {
                    monster.x = monster.patrolMaxX - monster.width;
                    if (prevDirection === 1) { monster.direction = -1; monster.justTurned = true; }
                } else if (monster.x < monster.patrolMinX) {
                    monster.x = monster.patrolMinX;
                    if (prevDirection === -1) { monster.direction = 1; monster.justTurned = true; }
                }

                if (monster.type === 'stomper') {
                    monster.stompCounter++;
                    if (monster.stompCounter >= monster.stompDelay) {
                        monster.element.classList.toggle('stomp-up'); monster.stompCounter = 0;
                    }
                }
                monster.element.style.left = monster.x + 'px';
            });
        }

        function resetPlayer() {
            playerX = playerStartX; playerY = playerStartY;
            velocityX = 0; velocityY = 0;
            isJumping = false; onPlatformState = true; playerFacingDirection = 1;
            isBeingShoved = false; shovingMonsterId = null;
            updatePlayerVisualState();
            logToDisplay("Player state reset.", "info");
        }

        const projectiles = [];
        let projectileIdCounter = 0;
        const PROJECTILE_SPEED = 10;
        const PROJECTILE_WIDTH = 8;
        const PROJECTILE_HEIGHT = 3;
        const SHOOT_COOLDOWN = 250;
        let lastShotTime = 0;

        function fireProjectile() {
            const currentTime = Date.now();
            if (currentTime - lastShotTime < SHOOT_COOLDOWN) return;
            lastShotTime = currentTime;

            const projectileElement = document.createElement('div');
            projectileElement.classList.add('projectile');
            projectileElement.id = `proj-${projectileIdCounter++}`;
            const projY = playerY + (playerTotalVisualHeight / 2) - (PROJECTILE_HEIGHT / 2);
            let projX;
            if (playerFacingDirection > 0) { projX = playerX + playerWidth; }
            else { projX = playerX - PROJECTILE_WIDTH; }
            projectileElement.style.left = projX + 'px';
            projectileElement.style.bottom = projY + 'px';
            world.appendChild(projectileElement);
            projectiles.push({
                element: projectileElement, x: projX, y: projY,
                vx: PROJECTILE_SPEED * playerFacingDirection, id: projectileElement.id
            });
        }

        function updateProjectiles() {
            for (let i = projectiles.length - 1; i >= 0; i--) {
                const p = projectiles[i];
                p.x += p.vx;
                p.element.style.left = p.x + 'px';
                if (p.x < 0 || p.x > WORLD_WIDTH - PROJECTILE_WIDTH) {
                    p.element.remove();
                    projectiles.splice(i, 1);
                }
            }
        }

        player.style.left = playerX + 'px';
        player.style.bottom = playerY + 'px';

        function updatePlayerVisualState() {
            player.classList.remove('is-walking-left', 'is-walking-right', 'is-jumping', 'facing-left', 'facing-right');
            if (playerFacingDirection === 1) player.classList.add('facing-right');
            else player.classList.add('facing-left');
            if (isJumping && velocityY !== 0 && !onPlatformState) {
                player.classList.add('is-jumping');
            } else if (velocityX !== 0 && onPlatformState && !isBeingShoved) {
                 if (velocityX < 0) player.classList.add('is-walking-left');
                 if (velocityX > 0) player.classList.add('is-walking-right');
            }
        }

        function setupSliders() {
            const slidersConfig = [
                { id: 'speed-slider', valueId: 'speed-value', variableName: 'moveSpeed', defaultValue: 5, decimals: 0 },
                { id: 'gravity-slider', valueId: 'gravity-value', variableName: 'gravity', defaultValue: 1.1, decimals: 1 },
                { id: 'jump-slider', valueId: 'jump-value', variableName: 'jumpStrength', defaultValue: 16, decimals: 0 },
                { id: 'friction-slider', valueId: 'friction-value', variableName: 'airFriction', defaultValue: 0.10, decimals: 2}
            ];
            slidersConfig.forEach(s => {
                const sliderElement = document.getElementById(s.id);
                const valueElement = document.getElementById(s.valueId);
                if (!sliderElement || !valueElement) {logToDisplay(`Slider/Value element for ${s.id} not found!`, "error"); return;}
                
                let initialValue = s.defaultValue; // Use the default from config
                // Set the global variable to the slider's default on init
                if (s.variableName === 'moveSpeed') moveSpeed = initialValue;
                else if (s.variableName === 'gravity') gravity = initialValue;
                else if (s.variableName === 'jumpStrength') jumpStrength = initialValue;
                else if (s.variableName === 'airFriction') airFriction = initialValue;

                sliderElement.value = initialValue;
                valueElement.textContent = initialValue.toFixed(s.decimals);

                sliderElement.addEventListener('input', (e) => {
                    const val = parseFloat(e.target.value); if (isNaN(val)) return;
                    if (s.variableName === 'moveSpeed') moveSpeed = val;
                    else if (s.variableName === 'gravity') gravity = val;
                    else if (s.variableName === 'jumpStrength') jumpStrength = val;
                    else if (s.variableName === 'airFriction') airFriction = val;
                    valueElement.textContent = val.toFixed(s.decimals);
                });
            });
        }

        function gameLoop() {
            if (isPaused) {
                animationFrameId = requestAnimationFrame(gameLoop); return;
            }

            if (isNaN(playerY) || typeof playerY === 'undefined') {
                logToDisplay("NaN/Undefined ERROR for playerY in gameLoop! Resetting.", "error");
                playerY = playerStartY || 0; velocityY = 0; onPlatformState = true;
                isBeingShoved = false; shovingMonsterId = null;
            }
            const prevPlayerY = playerY; 

            // --- Player Horizontal Input & Movement ---
            if (!isBeingShoved) {
                if (onPlatformState) {
                    velocityX = 0;
                    if (keysPressed.ArrowLeft) velocityX = -moveSpeed;
                    if (keysPressed.ArrowRight) velocityX = moveSpeed;
                } else { 
                    if (keysPressed.ArrowLeft) velocityX -= moveSpeed * 0.15;
                    if (keysPressed.ArrowRight) velocityX += moveSpeed * 0.15;
                    velocityX *= (1 - airFriction);
                    if (Math.abs(velocityX) < 0.01) velocityX = 0; 
                }
                if (keysPressed.ArrowLeft && velocityX < 0) playerFacingDirection = -1;
                if (keysPressed.ArrowRight && velocityX > 0) playerFacingDirection = 1;
            } else {
                velocityX = 0; 
            }
            playerX += velocityX;

            if (playerX < 0) { playerX = 0; if (!isBeingShoved) velocityX = 0; }
            if (playerX + playerWidth > WORLD_WIDTH) {
                playerX = WORLD_WIDTH - playerWidth; if (!isBeingShoved) velocityX = 0;
            }

            // --- Player Vertical Movement (Jump, Gravity) ---
            let jumpKeyPressed = keysPressed.ArrowUp || keysPressed.Space;
            if (jumpKeyPressed && !isJumping && onPlatformState && !isBeingShoved) {
                velocityY = jumpStrength; isJumping = true; onPlatformState = false;
            }
            velocityY -= gravity;
            playerY += velocityY;

            // --- Platform Collision Logic (Vertical) ---
            let landedThisFrame = false;
             for (const platform of platforms) {
                if (platform.id === 'platform1') continue;
                const pfStyle = getComputedStyle(platform);
                const pfTop = safeParseFloat(pfStyle.bottom) + safeParseFloat(pfStyle.height);
                const pfBottom = safeParseFloat(pfStyle.bottom);
                const pfLeft = safeParseFloat(pfStyle.left);
                const pfRight = pfLeft + safeParseFloat(pfStyle.width);
                const horizAlign = (playerX + playerWidth) > pfLeft && playerX < pfRight;

                if (horizAlign && velocityY <= 0 && playerY <= pfTop && prevPlayerY >= pfTop) {
                    playerY = pfTop; velocityY = 0; landedThisFrame = true; break;
                }
                const playerTopEdgeY = playerY + playerBodyHeight;
                const prevPlayerTopEdgeY = prevPlayerY + playerBodyHeight;
                if (horizAlign && velocityY > 0 && playerTopEdgeY >= pfBottom && prevPlayerTopEdgeY <= pfBottom) {
                    playerY = pfBottom - playerBodyHeight; velocityY = -0.1 * velocityY; 
                }
            }
            if (landedThisFrame) { isJumping = false; onPlatformState = true; }
            else { if (velocityY <= 0 && playerY > (initialGroundPlatform ? safeParseFloat(getComputedStyle(initialGroundPlatform).height) : 0)) onPlatformState = false; }

            const groundPfElement = initialGroundPlatform;
            const groundLevel = groundPfElement ? safeParseFloat(getComputedStyle(groundPfElement).height) : 0;
            if (playerY <= groundLevel && !landedThisFrame) {
                if (prevPlayerY >= groundLevel || playerY < groundLevel) {
                    playerY = groundLevel; if (velocityY < 0) velocityY = 0;
                    isJumping = false; onPlatformState = true;
                }
            }
            if (playerY < 0 && onPlatformState && groundLevel === 0) {
                playerY = 0; if (velocityY < 0) velocityY = 0;
            }


            // --- Collectible Collision ---
            for (const item of collectibles) {
                if (item.collected) continue;
                if (playerX < item.x + item.width && playerX + playerWidth > item.x &&
                    playerY < item.y + item.height && playerY + playerBodyHeight > item.y) {
                    item.collected = true; item.element.style.display = 'none';
                    score += COLLECTIBLE_VALUE; scoreDisplay.textContent = `Score: ${score}`;
                    logToDisplay(`Collected item ${item.id}! +${COLLECTIBLE_VALUE}pts`, "event");
                }
            }

            // --- Update Game Elements ---
            updateMonstersMovement();
            updateProjectiles();

            // --- Monster Interactions (Projectiles, Head Bop, Shove) ---
            for (let j = monsters.length - 1; j >= 0; j--) {
                const monster = monsters[j];
                if (!monster) continue;

                // 1. Projectile Hits
                for (let i = projectiles.length - 1; i >= 0; i--) {
                    const p = projectiles[i];
                    if (p.x < monster.x + monster.width && p.x + PROJECTILE_WIDTH > monster.x &&
                        p.y < monster.y + monster.height && p.y + PROJECTILE_HEIGHT > monster.y) {
                        
                        logToDisplay(`Projectile ${p.id} hit monster ${monster.id}! -${PROJECTILE_DAMAGE}HP`, "event");
                        monster.health -= PROJECTILE_DAMAGE;
                        updateMonsterHealthBar(monster);
                        p.element.remove();
                        projectiles.splice(i, 1);

                        if (monster.health <= 0) {
                            logToDisplay(`Monster ${monster.id} destroyed by projectile!`, "event");
                            if (isBeingShoved && shovingMonsterId === monster.id) {
                                isBeingShoved = false; shovingMonsterId = null;
                            }
                            monster.element.remove();
                            monsters.splice(j, 1);
                        }
                        break; 
                    }
                }
                if (monster.health <= 0 && !monsters.includes(monster)) continue; // Monster was killed by projectile and removed

                // 2. Player Head Bop
                const playerIsFalling = velocityY < 0;
                const playerCenterX = playerX + playerWidth / 2;
                const monsterBodyLeft = monster.x;
                const monsterBodyRight = monster.x + monster.width;
                const playerHorizontallyAlignedForBop = playerCenterX > monsterBodyLeft && playerCenterX < monsterBodyRight &&
                                                     (playerX + playerWidth * 0.9 > monster.x && playerX + playerWidth * 0.1 < monster.x + monster.width); 

                const playerWasAboveMonsterTop = prevPlayerY >= monster.y + monster.height;
                // Looser check for landing on top, using a small buffer above monster.
                // Check if player's bottom is now intersecting the monster's top zone.
                const playerIsNowOnMonsterTop = playerY <= (monster.y + monster.height) && playerY > (monster.y + monster.height - (playerBodyHeight / 2));


                if (playerIsFalling && playerHorizontallyAlignedForBop && playerWasAboveMonsterTop && playerIsNowOnMonsterTop) {
                    logToDisplay(`Player head-bopped monster ${monster.id}! -1HP`, "event");
                    monster.health--;
                    updateMonsterHealthBar(monster);

                    velocityY = jumpStrength * PLAYER_BOUNCE_STRENGTH_FACTOR; 
                    playerY = monster.y + monster.height + 1; 
                    isJumping = true; 
                    onPlatformState = false; 

                    if (isBeingShoved && shovingMonsterId === monster.id) { 
                        isBeingShoved = false; shovingMonsterId = null;
                    }

                    if (monster.health <= 0) {
                        logToDisplay(`Monster ${monster.id} defeated by head bop!`, "event");
                        monster.element.remove();
                        monsters.splice(j, 1);
                    }
                    continue; // Interaction (bop) happened with this monster, skip shove check for it.
                }
                
                if (monster.health <= 0 && !monsters.includes(monster)) continue; // Monster was killed by bop and removed

                // 3. Shove Logic (Only if no head bop or projectile kill on this monster THIS FRAME)
                if (isBeingShoved && shovingMonsterId === monster.id) { 
                    if (monster.justTurned) {
                        isBeingShoved = false; shovingMonsterId = null;
                        logToDisplay(`Shove ended: Monster ${monster.id} turned.`, "event");
                    } else { 
                        if (monster.direction === 1) { playerX = monster.x + monster.width; }
                        else { playerX = monster.x - playerWidth; }
                        velocityX = 0; 
                    }
                } else if (!isBeingShoved) { 
                    const playerSideCollides = playerX + playerWidth > monster.x && playerX < monster.x + monster.width;
                    const playerVerticallyOverlapsForShove = playerY < monster.y + monster.height && playerY + playerBodyHeight > monster.y;
                    
                    // Make sure it's not a head bop scenario: player's bottom should not be significantly above monster's top
                    const notHeadBopZoneForShove = playerY + playerBodyHeight * 0.5 < monster.y + monster.height;


                    if (playerSideCollides && playerVerticallyOverlapsForShove && notHeadBopZoneForShove && !monster.justTurned) {
                        logToDisplay(`Player starts being shoved by ${monster.type} ${monster.id}!`, "event");
                        isBeingShoved = true;
                        shovingMonsterId = monster.id;
                        if (monster.direction === 1) { playerX = monster.x + monster.width; }
                        else { playerX = monster.x - playerWidth; }
                        velocityX = 0;
                    }
                }
            }
             if (isBeingShoved && !monsters.find(m => m.id === shovingMonsterId)) {
                isBeingShoved = false; shovingMonsterId = null;
                logToDisplay("Shove ended: Monster gone (post-loop check).", "system");
            }


            // --- Final Boundary Checks for Player X (after all movements/shoves) ---
            if (playerX < 0) { playerX = 0; }
            if (playerX + playerWidth > WORLD_WIDTH) {
                playerX = WORLD_WIDTH - playerWidth;
            }

            // --- Update Player Visuals and Position ---
            player.style.left = playerX + 'px';
            player.style.bottom = playerY + 'px';
            updatePlayerVisualState();

            animationFrameId = requestAnimationFrame(gameLoop);
        }

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' || event.key.toLowerCase() === 'p') {
                togglePause(); return;
            }
            if (isPaused) return;
            if (event.code === 'Space' || ['ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight'].includes(event.key) ) {
                event.preventDefault();
            }
            if (keysPressed.hasOwnProperty(event.key) || keysPressed.hasOwnProperty(event.code)) {
                keysPressed[event.key] = true; keysPressed[event.code] = true;
            }
            if ((event.key === 'Control' || event.code === 'ControlLeft' || event.key.toLowerCase() === 'x') && !isBeingShoved) {
                event.preventDefault();
                keysPressed.Shoot = true;
                fireProjectile();
            }
        });
        document.addEventListener('keyup', (event) => {
            if (isPaused && !(event.key === 'Escape' || event.key.toLowerCase() === 'p')) return;
            if (keysPressed.hasOwnProperty(event.key) || keysPressed.hasOwnProperty(event.code)) {
                keysPressed[event.key] = false; keysPressed[event.code] = false;
            }
            if (event.key === 'Control' || event.code === 'ControlLeft' || event.key.toLowerCase() === 'x') {
                 keysPressed.Shoot = false;
            }
        });

        function togglePause() {
            isPaused = !isPaused;
            if (isPaused) {
                pauseOverlay.style.display = 'flex';
                pauseButton.textContent = 'RESUME';
                logToDisplay("Game Paused", "system");
            } else {
                pauseOverlay.style.display = 'none';
                pauseButton.textContent = 'PAUSE';
                logToDisplay("Game Resumed", "system");
            }
        }
        pauseButton.addEventListener('click', togglePause);

        setupSliders(); 
        setupCollectibles();
        setupMonsters();

        if (typeof playerY === 'undefined' || isNaN(playerY)) {
            logToDisplay(`playerY invalid after setups: ${playerY}. Resetting to 0.`, "error");
            playerY = 0; playerStartY = 0;
        }
        
        player.style.bottom = playerY + 'px';
        scoreDisplay.textContent = `Score: ${score}`;
        updatePlayerVisualState();

        logToDisplay("Game Initialized. PlayerY: " + playerY.toFixed(1) +
                     ", Gravity: " + gravity + ", AirFric: " + airFriction, "info");

        animationFrameId = requestAnimationFrame(gameLoop);
    </script>
</body>
</html>