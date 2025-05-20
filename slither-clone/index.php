<!DOCTYPE html>
<html>
<head>
    <title>Slitherish - Single File</title>
    <meta charset="UTF-8">
    <style>
        body {
            margin: 0;
            display: flex;
            flex-direction: column; /* Stack canvas and controls */
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #1e1e1e; /* Darker background */
            font-family: Arial, sans-serif;
            color: white;
        }
        canvas {
            border: 2px solid #555;
            background-color: #282828; /* Slightly lighter game area */
            display: block; /* Remove extra space below canvas */
        }
        #ui-container {
            margin-top: 15px;
            text-align: center;
        }
        #score {
            font-size: 24px;
            margin-bottom: 10px;
        }
        #restartButton {
            padding: 10px 20px;
            font-size: 16px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            display: none; /* Hidden initially */
        }
        #restartButton:hover {
            background-color: #45a049;
        }
        #instructions {
            font-size: 14px;
            color: #aaa;
            margin-top: 5px;
        }
    </style>
</head>
<body>

    <canvas id="gameCanvas"></canvas>
    <div id="ui-container">
        <div id="score">Score: 0</div>
        <div id="instructions">Move mouse to control. Eat pellets to grow. Avoid walls and yourself!</div>
        <button id="restartButton">Restart Game</button>
    </div>

    <script>
        const canvas = document.getElementById('gameCanvas');
        const ctx = canvas.getContext('2d');
        const scoreDisplay = document.getElementById('score');
        const restartButton = document.getElementById('restartButton');
        const instructionsDiv = document.getElementById('instructions');

        // --- Game Settings ---
        const CANVAS_WIDTH = 800;
        const CANVAS_HEIGHT = 600;
        const SNAKE_SPEED = 2.5; // Pixels per frame
        const SEGMENT_RADIUS = 8;
        const PELLET_RADIUS = 5;
        const INITIAL_SNAKE_LENGTH = 5;
        const MAX_PELLETS = 20;
        const SELF_COLLISION_OFFSET = 5; // Corrected value

        canvas.width = CANVAS_WIDTH;
        canvas.height = CANVAS_HEIGHT;

        // --- Game State ---
        let snake;
        let pellets;
        let score;
        let mouseX = CANVAS_WIDTH / 2;
        let mouseY = CANVAS_HEIGHT / 2;
        let gameRunning = true;
        let animationFrameId;

        // --- Event Listeners ---
        canvas.addEventListener('mousemove', (e) => {
            const rect = canvas.getBoundingClientRect();
            mouseX = e.clientX - rect.left;
            mouseY = e.clientY - rect.top;
        });

        restartButton.addEventListener('click', () => {
            startGame();
        });

        // --- Helper Functions ---
        function distance(x1, y1, x2, y2) {
            const dx = x2 - x1;
            const dy = y2 - y1;
            return Math.sqrt(dx * dx + dy * dy);
        }

        function getRandomColor() {
            return `hsl(${Math.random() * 360}, 100%, 70%)`;
        }

        // --- Game Initialization ---
        function initSnake() {
            snake = [];
            const startX = CANVAS_WIDTH / 2;
            const startY = CANVAS_HEIGHT / 2;
            for (let i = 0; i < INITIAL_SNAKE_LENGTH; i++) {
                snake.push({
                    x: startX - i * SEGMENT_RADIUS,
                    y: startY,
                    color: i === 0 ? 'white' : getRandomColor()
                });
            }
        }

        function spawnPellet() {
            if (pellets.length < MAX_PELLETS) {
                pellets.push({
                    x: Math.random() * (CANVAS_WIDTH - PELLET_RADIUS * 2) + PELLET_RADIUS,
                    y: Math.random() * (CANVAS_HEIGHT - PELLET_RADIUS * 2) + PELLET_RADIUS,
                    radius: PELLET_RADIUS,
                    color: getRandomColor()
                });
            }
        }

        function initPellets() {
            pellets = [];
            for (let i = 0; i < MAX_PELLETS / 2; i++) {
                spawnPellet();
            }
        }

        // --- Game Logic (Update) ---
        function updateSnakeMovement() {
            if (!snake.length) return;

            const head = snake[0];

            let dx = mouseX - head.x;
            let dy = mouseY - head.y;
            const distToMouse = Math.sqrt(dx * dx + dy * dy);

            if (distToMouse > SNAKE_SPEED / 2) { // Move only if mouse is a bit away
                const normalizedDx = dx / distToMouse;
                const normalizedDy = dy / distToMouse;

                const nextHeadX = head.x + normalizedDx * SNAKE_SPEED;
                const nextHeadY = head.y + normalizedDy * SNAKE_SPEED;

                for (let i = snake.length - 1; i > 0; i--) {
                    snake[i].x = snake[i - 1].x;
                    snake[i].y = snake[i - 1].y;
                }
                head.x = nextHeadX;
                head.y = nextHeadY;
            }
        }

        function checkPelletCollisions() {
            if (!snake.length) return;
            const head = snake[0];

            for (let i = pellets.length - 1; i >= 0; i--) {
                const pellet = pellets[i];
                if (distance(head.x, head.y, pellet.x, pellet.y) < SEGMENT_RADIUS + pellet.radius) {
                    pellets.splice(i, 1);
                    spawnPellet();
                    score++;
                    scoreDisplay.textContent = `Score: ${score}`;

                    // GROW SNAKE
                    const tail = snake[snake.length - 1];
                    snake.push({ x: tail.x, y: tail.y, color: getRandomColor() });
                }
            }
        }

        function checkBoundaryCollisions() {
            if (!snake.length) return;
            const head = snake[0];
            if (head.x - SEGMENT_RADIUS < 0 || head.x + SEGMENT_RADIUS > CANVAS_WIDTH ||
                head.y - SEGMENT_RADIUS < 0 || head.y + SEGMENT_RADIUS > CANVAS_HEIGHT) {
                gameOver();
            }
        }

        function checkSelfCollisions() {
            if (!snake.length) return;
            const head = snake[0];
            for (let i = SELF_COLLISION_OFFSET; i < snake.length; i++) {
                const segment = snake[i];
                const dist = distance(head.x, head.y, segment.x, segment.y);
                const collisionThreshold = SEGMENT_RADIUS * 1.5;
                if (dist < collisionThreshold) {
                    gameOver();
                    return;
                }
            }
        }

        function gameOver() {
            gameRunning = false;
            ctx.fillStyle = 'rgba(0, 0, 0, 0.7)';
            ctx.fillRect(0, 0, CANVAS_WIDTH, CANVAS_HEIGHT);
            ctx.fillStyle = 'white';
            ctx.font = '48px Arial';
            ctx.textAlign = 'center';
            ctx.fillText('Game Over!', CANVAS_WIDTH / 2, CANVAS_HEIGHT / 2 - 30);
            ctx.font = '24px Arial';
            ctx.fillText(`Final Score: ${score}`, CANVAS_WIDTH / 2, CANVAS_HEIGHT / 2 + 20);
            restartButton.style.display = 'block';
            instructionsDiv.style.display = 'none';
        }

        // --- Drawing ---
        function drawSnake() {
            for (let i = 0; i < snake.length; i++) {
                const segment = snake[i];
                ctx.beginPath();
                ctx.arc(segment.x, segment.y, SEGMENT_RADIUS, 0, Math.PI * 2);
                ctx.fillStyle = segment.color;
                ctx.fill();

                if (i > 0) {
                    ctx.strokeStyle = segment.color;
                    ctx.lineWidth = 1;
                    ctx.stroke();
                } else {
                     ctx.strokeStyle = '#333';
                     ctx.lineWidth = 2;
                     ctx.stroke();
                }

                if (i === 0) { // Eyes for the head
                    const eyeRadius = SEGMENT_RADIUS / 2.5;
                    const eyeDist = SEGMENT_RADIUS / 2;

                    let angleToMouse = Math.atan2(mouseY - segment.y, mouseX - segment.x);
                    const pupilRadius = eyeRadius / 2;

                    // Eye 1
                    const eye1X = segment.x + eyeDist * Math.cos(angleToMouse + Math.PI / 5);
                    const eye1Y = segment.y + eyeDist * Math.sin(angleToMouse + Math.PI / 5);
                    ctx.beginPath();
                    ctx.arc(eye1X, eye1Y, eyeRadius, 0, Math.PI * 2);
                    ctx.fillStyle = 'white';
                    ctx.fill();
                    ctx.beginPath(); // Pupil 1
                    ctx.arc(eye1X + pupilRadius/2 * Math.cos(angleToMouse), eye1Y + pupilRadius/2 * Math.sin(angleToMouse), pupilRadius, 0, Math.PI * 2);
                    ctx.fillStyle = 'black';
                    ctx.fill();

                    // Eye 2
                    const eye2X = segment.x + eyeDist * Math.cos(angleToMouse - Math.PI / 5);
                    const eye2Y = segment.y + eyeDist * Math.sin(angleToMouse - Math.PI / 5);
                    ctx.beginPath();
                    ctx.arc(eye2X, eye2Y, eyeRadius, 0, Math.PI * 2);
                    ctx.fillStyle = 'white';
                    ctx.fill();
                    ctx.beginPath(); // Pupil 2
                    ctx.arc(eye2X + pupilRadius/2 * Math.cos(angleToMouse), eye2Y + pupilRadius/2 * Math.sin(angleToMouse), pupilRadius, 0, Math.PI * 2);
                    ctx.fillStyle = 'black';
                    ctx.fill();
                }
            }
        }

        function drawPellets() {
            pellets.forEach(pellet => {
                ctx.beginPath();
                ctx.arc(pellet.x, pellet.y, pellet.radius, 0, Math.PI * 2);
                ctx.fillStyle = pellet.color;
                ctx.fill();
            });
        }

        // --- Main Game Loop ---
        function gameLoop() {
            if (!gameRunning) {
                if (animationFrameId) cancelAnimationFrame(animationFrameId);
                return;
            }

            updateSnakeMovement();
            checkPelletCollisions();
            checkBoundaryCollisions();
            if (gameRunning) { // Re-check gameRunning as previous checks might change it
                 checkSelfCollisions();
            }

            ctx.fillStyle = '#282828';
            ctx.fillRect(0, 0, CANVAS_WIDTH, CANVAS_HEIGHT);

            drawPellets();
            drawSnake();

            animationFrameId = requestAnimationFrame(gameLoop);
        }

        function startGame() {
            gameRunning = true;
            score = 0;
            scoreDisplay.textContent = `Score: ${score}`;
            restartButton.style.display = 'none';
            instructionsDiv.style.display = 'block';

            initSnake();
            initPellets();

            if (animationFrameId) {
                cancelAnimationFrame(animationFrameId);
            }
            animationFrameId = requestAnimationFrame(gameLoop);
        }

        // --- Start the game ---
        startGame();

    </script>
</body>
</html>