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
            min-height: 100vh; margin: 0; padding-top: 20px;
            overflow-x: hidden;
        }
        #main-content { display: flex; flex-direction: column; align-items: center; }
        #controls-container {
            background-color: #303030; padding: 10px; border-radius: 5px; margin-bottom: 15px;
            display: flex; flex-wrap: wrap; gap: 15px; justify-content: center; max-width: 700px;
        }
        .slider-group {
            display: flex; flex-direction: column; align-items: center; min-width: 150px;
        }
        .slider-group label { font-size: 12px; margin-bottom: 3px; }
        .slider-group input[type="range"] { width: 130px; }
        .slider-group span { font-size: 11px; min-width: 25px; text-align: center; }

        #game-container {
            width: 640px; height: 400px; background-color: #404040;
            border: 4px solid #707070; position: relative; overflow: hidden; margin-bottom: 10px;
        }
        #player {
            width: 20px; height: 30px; background-color: #FF5555;
            position: absolute; bottom: 0; left: 50px; transform-origin: bottom center;
        }
        #player::before {
            content: ''; display: block; width: 16px; height: 16px; background-color: #FFFF77;
            border-radius: 3px; position: absolute; bottom: 100%; left: 50%;
            transform: translateX(-50%); box-shadow: inset 0 0 0 1px #000000;
        }
        #player.is-walking-left { transform: skewX(8deg); }
        #player.is-walking-right { transform: skewX(-8deg); }
        #player.walk-frame-1::after {
            content: ''; display: block; position: absolute; width: 6px; height: 10px;
            background-color: #CC4444; bottom: -8px; left: 2px;
        }
        #player.walk-frame-2::after {
            content: ''; display: block; position: absolute; width: 6px; height: 10px;
            background-color: #CC4444; bottom: -8px; right: 2px;
        }
        #player.is-jumping { transform: rotate(0deg); }
        .platform { background-color: #707070; position: absolute; }
        #platform1 { width: 640px; height: 40px; bottom: 0px; left: 0px; background-color: #505050; }
        #platform2 { width: 200px; height: 16px; bottom: 100px; left: 150px; }
        #platform3 { width: 150px; height: 16px; bottom: 200px; left: 350px; }
        #platform4 { width: 180px; height: 16px; bottom: 300px; left: 50px; }
        .collectible {
            width: 12px; height: 12px; background-color: gold; border: 1px solid darkgoldenrod;
            border-radius: 50%; position: absolute; box-shadow: 0 0 5px yellow;
        }
        #score-display { font-size: 20px; color: #FFFF77; margin-bottom: 5px; text-align: center; }
        #log-display-container {
             width: 640px; height: 60px; background-color: rgba(20,20,20,0.8); color: #0f0;
             font-family: monospace; font-size: 10px; overflow-y: scroll; padding: 5px;
             border: 1px solid #0f0; z-index: 1001; box-sizing: border-box; margin-top: 5px;
        }
    </style>
</head>
<body>
    <div id="controls-container">
        <div class="slider-group">
            <label for="speed-slider">Player Speed</label>
            <input type="range" id="speed-slider" min="1" max="15" value="5">
            <span id="speed-value">5</span>
        </div>
        <div class="slider-group">
            <label for="gravity-slider">Gravity</label>
            <input type="range" id="gravity-slider" min="0.1" max="2.5" step="0.1" value="0.8">
            <span id="gravity-value">0.8</span>
        </div>
        <div class="slider-group">
            <label for="jump-slider">Jump Strength</label>
            <input type="range" id="jump-slider" min="5" max="25" value="16">
            <span id="jump-value">16</span>
        </div>
         <div class="slider-group">
            <label for="friction-slider">Air Friction (0=none)</label>
            <input type="range" id="friction-slider" min="0" max="0.2" step="0.01" value="0">
            <span id="friction-value">0.0</span>
        </div>
    </div>

    <div id="main-content">
        <h1>Impossible Mission - Agent X</h1>
        <div id="game-container">
            <div id="player"></div>
            <div class="platform" id="platform1"></div>
            <div class="platform" id="platform2"></div>
            <div class="platform" id="platform3"></div>
            <div class="platform" id="platform4"></div>
        </div>
        <div id="score-display">Score: 0</div>
        <div id="log-display-container"></div>
    </div>

    <script>
        const player = document.getElementById('player');
        const gameContainer = document.getElementById('game-container');
        const platforms = Array.from(document.querySelectorAll('.platform'));
        const logDisplayContainer = document.getElementById('log-display-container');
        const scoreDisplay = document.getElementById('score-display');

        let gameLog = [];
        const MAX_LOG_LINES = 10;
        function logToDisplay(message, type = "info") {
            if (type === "error") console.error(`[${new Date().toLocaleTimeString()}] ${message}`);
            gameLog.push(`[${type.toUpperCase()}] ${message}`);
            if (gameLog.length > MAX_LOG_LINES) gameLog.shift();
            logDisplayContainer.innerHTML = gameLog.join('<br>');
            logDisplayContainer.scrollTop = logDisplayContainer.scrollHeight;
        }

        function safeParseFloat(value, defaultValue = 0) {
            const num = parseFloat(value);
            return isNaN(num) ? defaultValue : num;
        }

        // --- Game Parameters (Make sure these are defined BEFORE setupSliders is called) ---
        let moveSpeed = 5;
        let gravity = 0.8;
        let jumpStrength = 16;
        let airFriction = 0.0;

        // --- Player State ---
        let playerX = 50;
        let playerBodyHeight = safeParseFloat(getComputedStyle(player).height);
        let playerWidth = safeParseFloat(getComputedStyle(player).width);
        let playerY;
        let velocityX = 0;
        let velocityY = 0;
        let isJumping = false;
        let onPlatformState = true;
        let score = 0;

        const collectibles = [];
        const COLLECTIBLE_VALUE = 10;

        function createCollectible(x, y, id) {
            const item = document.createElement('div');
            item.classList.add('collectible');
            item.id = `collectible-${id}`;
            item.style.left = x + 'px'; item.style.bottom = y + 'px';
            gameContainer.appendChild(item);
            collectibles.push({ element: item, x, y, width: 12, height: 12, id: item.id, collected: false });
        }

        function setupCollectibles() {
            collectibles.forEach(c => c.element.remove());
            collectibles.length = 0;
            createCollectible(200, 120, 1); createCollectible(400, 220, 2);
            createCollectible(100, 320, 3); createCollectible(300, 60, 4);
            createCollectible(550, 120, 5);
        }

        const initialGroundPlatform = platforms.find(p => p.id === 'platform1');
        if (initialGroundPlatform) {
            playerY = safeParseFloat(getComputedStyle(initialGroundPlatform).height);
        } else {
            playerY = 0; logToDisplay("CRITICAL: Platform1 not found!", "error");
        }

        const gameContainerWidth = safeParseFloat(getComputedStyle(gameContainer).width);
        const keysPressed = { ArrowLeft: false, ArrowRight: false, ArrowUp: false, Space: false };
        let walkFrame = 1, walkFrameCounter = 0;
        const walkFrameDelay = 5;

        player.style.left = playerX + 'px'; player.style.bottom = playerY + 'px';

        function updatePlayerVisualState() {
            player.classList.remove('is-walking-left', 'is-walking-right', 'walk-frame-1', 'walk-frame-2', 'is-jumping');
            if (isJumping && velocityY !== 0) {
                player.classList.add('is-jumping');
            } else if (velocityX !== 0 && onPlatformState) {
                 if (velocityX < 0) player.classList.add('is-walking-left');
                 if (velocityX > 0) player.classList.add('is-walking-right');
                 player.classList.add(walkFrame === 1 ? 'walk-frame-1' : 'walk-frame-2');
            }
        }

        // --- Slider Setup ---
        function setupSliders() {
            // Define an object to map slider variable names to the actual variables
            const gameParams = {
                moveSpeed: moveSpeed,
                gravity: gravity,
                jumpStrength: jumpStrength,
                airFriction: airFriction
            };

            const slidersConfig = [
                { id: 'speed-slider', valueId: 'speed-value', variableName: 'moveSpeed', decimals: 0 },
                { id: 'gravity-slider', valueId: 'gravity-value', variableName: 'gravity', decimals: 1 },
                { id: 'jump-slider', valueId: 'jump-value', variableName: 'jumpStrength', decimals: 0 },
                { id: 'friction-slider', valueId: 'friction-value', variableName: 'airFriction', decimals: 2}
            ];

            slidersConfig.forEach(s => {
                const sliderElement = document.getElementById(s.id);
                const valueElement = document.getElementById(s.valueId);

                if (!sliderElement) {
                    logToDisplay(`Slider element ${s.id} not found!`, "error");
                    return;
                }
                if (!valueElement) {
                    logToDisplay(`Value element ${s.valueId} not found!`, "error");
                    return;
                }

                // Get initial value from our gameParams object (which holds references to the let variables)
                // Or use the slider's default HTML value if the JS variable is somehow undefined
                let initialValue = gameParams[s.variableName];
                if (typeof initialValue !== 'number' || isNaN(initialValue)) {
                    logToDisplay(`Warning: Initial value for ${s.variableName} is not a number. Using slider default.`, "info");
                    initialValue = parseFloat(sliderElement.value) || 0; // Fallback to slider's HTML value or 0
                }
                
                // Update the global variable with the slider's actual initial value (if different from hardcoded JS)
                // This ensures consistency if HTML 'value' attributes are set.
                // For this example, we'll prioritize the JS variable's initial values.
                // So, we set the slider's value from the JS variable.
                sliderElement.value = initialValue;
                valueElement.textContent = initialValue.toFixed(s.decimals);


                sliderElement.addEventListener('input', (e) => {
                    const val = parseFloat(e.target.value);
                    if (isNaN(val)) return; // Don't process if not a number

                    // Update the correct global variable directly
                    // This requires the variable names to match exactly.
                    if (s.variableName === 'moveSpeed') moveSpeed = val;
                    else if (s.variableName === 'gravity') gravity = val;
                    else if (s.variableName === 'jumpStrength') jumpStrength = val;
                    else if (s.variableName === 'airFriction') airFriction = val;
                    
                    valueElement.textContent = val.toFixed(s.decimals);
                });
            });
        }


        function gameLoop() {
            if (isNaN(playerY)) {
                logToDisplay("NaN ERROR: playerY in gameLoop!", "error");
                playerY = 0; velocityY = 0;
            }
            const prevPlayerY = playerY;

            if (onPlatformState) {
                velocityX = 0;
                if (keysPressed.ArrowLeft) velocityX = -moveSpeed;
                if (keysPressed.ArrowRight) velocityX = moveSpeed;
            } else {
                if (keysPressed.ArrowLeft) velocityX -= moveSpeed * 0.1;
                if (keysPressed.ArrowRight) velocityX += moveSpeed * 0.1;
                velocityX *= (1 - airFriction);
                if (Math.abs(velocityX) < 0.1) velocityX = 0;
            }
            playerX += velocityX;

            if (playerX < 0) { playerX = 0; velocityX = 0; }
            if (playerX + playerWidth > gameContainerWidth) { playerX = gameContainerWidth - playerWidth; velocityX = 0; }

            let jumpKeyPressed = keysPressed.ArrowUp || keysPressed.Space;
            if (jumpKeyPressed && !isJumping && onPlatformState) {
                velocityY = jumpStrength; isJumping = true; onPlatformState = false;
            }

            velocityY -= gravity; playerY += velocityY;

            let landedThisFrame = false;
            for (const platform of platforms) {
                if (platform.id === 'platform1') continue;
                const pfStyle = getComputedStyle(platform);
                const pfTop = safeParseFloat(pfStyle.bottom) + safeParseFloat(pfStyle.height);
                const pfBottom = safeParseFloat(pfStyle.bottom);
                const pfLeft = safeParseFloat(pfStyle.left);
                const pfRight = safeParseFloat(pfStyle.left) + safeParseFloat(pfStyle.width);
                const horizAlign = (playerX + playerWidth) > pfLeft && playerX < pfRight;

                if (horizAlign && velocityY <= 0 && playerY <= pfTop && prevPlayerY >= pfTop) {
                    playerY = pfTop; velocityY = 0; landedThisFrame = true; break;
                }
                const playerTopY = playerY + playerBodyHeight;
                const prevPlayerTopY = prevPlayerY + playerBodyHeight;
                if (horizAlign && velocityY > 0 && playerTopY >= pfBottom && prevPlayerTopY <= pfBottom) {
                    playerY = pfBottom - playerBodyHeight; velocityY = -0.1 * velocityY;
                }
            }

            if (landedThisFrame) { isJumping = false; onPlatformState = true; }
            else { if (velocityY <= 0) onPlatformState = false; }

            const groundPlatformElement = platforms.find(p => p.id === 'platform1');
            const groundPfStyle = getComputedStyle(groundPlatformElement);
            const groundLevel = safeParseFloat(groundPfStyle.height);
            if (playerY <= groundLevel && !landedThisFrame) {
                if (prevPlayerY >= groundLevel) {
                    playerY = groundLevel; if (velocityY < 0) velocityY = 0;
                    isJumping = false; onPlatformState = true;
                }
            }
            if (playerY < 0) {
                playerY = 0; if (velocityY < 0) velocityY = 0;
                isJumping = false; onPlatformState = true;
            }

            for (const item of collectibles) {
                if (item.collected) continue;
                if (playerX < item.x + item.width && playerX + playerWidth > item.x &&
                    playerY < item.y + item.height && playerY + playerBodyHeight > item.y) {
                    item.collected = true; item.element.style.display = 'none';
                    score += COLLECTIBLE_VALUE; scoreDisplay.textContent = `Score: ${score}`;
                    logToDisplay(`Collected item ${item.id}! +${COLLECTIBLE_VALUE}pts`, "event");
                }
            }

            player.style.left = playerX + 'px'; player.style.bottom = playerY + 'px';

            if (velocityX !== 0 && onPlatformState && !isJumping) {
                walkFrameCounter++;
                if (walkFrameCounter >= walkFrameDelay) {
                    walkFrame = walkFrame === 1 ? 2 : 1; walkFrameCounter = 0;
                }
            } else { walkFrameCounter = 0; }
            updatePlayerVisualState();

            requestAnimationFrame(gameLoop);
        }

        document.addEventListener('keydown', (event) => {
            if (event.code === 'Space') event.preventDefault();
            if (keysPressed.hasOwnProperty(event.key) || keysPressed.hasOwnProperty(event.code)) {
                keysPressed[event.key] = true; keysPressed[event.code] = true;
            }
        });
        document.addEventListener('keyup', (event) => {
            if (keysPressed.hasOwnProperty(event.key) || keysPressed.hasOwnProperty(event.code)) {
                keysPressed[event.key] = false; keysPressed[event.code] = false;
            }
        });

        // --- Initialization ---
        // Ensure game parameters are defined before setupSliders
        setupSliders(); // Call this after game parameters are defined.
        setupCollectibles();
        if (isNaN(playerY)) { logToDisplay("ULTIMATE FAILSAFE: playerY NaN before loop.", "error"); playerY = 0; }
        player.style.bottom = playerY + 'px';
        scoreDisplay.textContent = `Score: ${score}`;
        updatePlayerVisualState();
        logToDisplay("Game Initialized. PlayerY: " + playerY.toFixed(1), "info");
        requestAnimationFrame(gameLoop);
    </script>
</body>
</html>