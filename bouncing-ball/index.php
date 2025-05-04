<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Bouncing Ball Game</title>
    <style>
        body { margin: 0; overflow: hidden; background: #111; }
        canvas { display: block; background: #222; }
        #controls {
            position: absolute;
            top: 10px;
            left: 10px;
            z-index: 1;
            color: #fff;
            font-family: sans-serif;
            font-size: 12px;
        }
        #controls label { margin-right: 5px; display: inline-block; width: 60px; }
        #controls input, #controls button {
            margin-bottom: 8px;
        }
        #launchBtn {
            background: #ff5722;
            color: #fff;
            border: none;
            padding: 4px 8px;
            cursor: pointer;
        }
        #launchBtn:hover {
            background: #e64a19;
        }
    </style>
</head>
<body>
    <div id="controls">
        <div><label for="speedSlider">Speed:</label><input id="speedSlider" type="range" min="1" max="10" step="1" value="5"></div>
        <div><label for="gravitySlider">Gravity:</label><input id="gravitySlider" type="range" min="0" max="2" step="0.05" value="0.2"></div>
        <div><label for="angleSlider">Angle Var:</label><input id="angleSlider" type="range" min="0" max="1" step="0.01" value="0.1"></div>
        <div><button id="launchBtn">Launch Up</button></div>
    </div>
    <canvas id="gameCanvas"></canvas>
    <script>
        const canvas = document.getElementById('gameCanvas');
        const ctx = canvas.getContext('2d');

        function resize() {
            canvas.width = window.innerWidth;
            canvas.height = window.innerHeight;
        }
        window.addEventListener('resize', resize);
        resize();

        const normalColor = '#ff5722';
        const invincibleColor = '#ffff00';
        const ball = { x: canvas.width/2, y: canvas.height/2, radius: 20, vx: 4, vy: 3, speed: Math.hypot(4,3), color: normalColor, invincible: false };

        // Control sliders
        const speedSlider = document.getElementById('speedSlider');
        const gravitySlider = document.getElementById('gravitySlider');
        const angleSlider = document.getElementById('angleSlider');
        const launchBtn = document.getElementById('launchBtn');
        let gravity = parseFloat(gravitySlider.value);
        let angleRandomness = parseFloat(angleSlider.value);
        speedSlider.addEventListener('input', () => { ball.speed = Number(speedSlider.value); });
        gravitySlider.addEventListener('input', () => { gravity = parseFloat(gravitySlider.value); });
        angleSlider.addEventListener('input', () => { angleRandomness = parseFloat(angleSlider.value); });

        // Launch button: move ball to top middle and drop invincibly
        launchBtn.addEventListener('click', () => {
            ball.invincible = true;
            ball.color = invincibleColor;
            ball.x = canvas.width / 2;
            ball.y = ball.radius;
            ball.vx = 0;
            ball.vy = 0;
            setTimeout(() => {
                ball.invincible = false;
                ball.color = normalColor;
            }, 2000);
        });

        // Particle sparkles
        const particles = [];
        function spawnSparkles() {
            for (let i = 0; i < 5; i++) {
                const angle = Math.random() * 2 * Math.PI;
                const speed = 1 + Math.random() * 2;
                particles.push({ x: ball.x, y: ball.y, vx: Math.cos(angle) * speed, vy: Math.sin(angle) * speed, life: 30 });
            }
        }
        function updateParticles() {
            for (let i = particles.length - 1; i >= 0; i--) {
                const p = particles[i];
                p.x += p.vx;
                p.y += p.vy;
                p.life--;
                if (p.life <= 0) particles.splice(i, 1);
            }
        }
        function drawParticles() {
            particles.forEach(p => {
                ctx.save();
                ctx.globalAlpha = p.life / 30;
                ctx.fillStyle = invincibleColor;
                ctx.beginPath();
                ctx.arc(p.x, p.y, 3, 0, 2 * Math.PI);
                ctx.fill();
                ctx.restore();
            });
        }

        // Bricks dynamic fill
        const brickPadding = 10, origW = 75, origH = 20;
        const maxCols = Math.floor((canvas.width + brickPadding) / (origW + brickPadding));
        const brickColumnCount = maxCols * 2;
        const brickWidth = (canvas.width - (brickColumnCount - 1) * brickPadding) / brickColumnCount;
        const brickHeight = origH;
        const brickRowCount = Math.floor((canvas.height + brickPadding) / (brickHeight + brickPadding));
        const gridHeight = brickRowCount * brickHeight + (brickRowCount - 1) * brickPadding;
        const brickOffsetTop = (canvas.height - gridHeight) / 2;
        const bricks = [];
        for (let c = 0; c < brickColumnCount; c++) {
            bricks[c] = [];
            for (let r = 0; r < brickRowCount; r++) {
                const x = c * (brickWidth + brickPadding);
                const y = brickOffsetTop + r * (brickHeight + brickPadding);
                bricks[c][r] = { x, y, status: 1, destroyedTime: null, regenDelay: 5000 };
            }
        }

        // Bounce helper with angle randomness
        function applyBounce(axis) {
            const sp = Math.hypot(ball.vx, ball.vy);
            let ang = Math.atan2(ball.vy, ball.vx);
            if (axis === 'x') ang = Math.PI - ang;
            if (axis === 'y') ang = -ang;
            ang += (Math.random() - 0.5) * angleRandomness;
            ball.vx = sp * Math.cos(ang);
            ball.vy = sp * Math.sin(ang);
        }

        // Draw functions
        function drawBall() {
            ctx.save();
            ctx.shadowColor = 'rgba(0,0,0,0.6)';
            ctx.shadowBlur = 10;
            const grad = ctx.createRadialGradient(
                ball.x - ball.radius * 0.3, ball.y - ball.radius * 0.3, ball.radius * 0.1,
                ball.x, ball.y, ball.radius
            );
            grad.addColorStop(0, '#fff');
            grad.addColorStop(1, ball.color);
            ctx.fillStyle = grad;
            ctx.beginPath();
            ctx.arc(ball.x, ball.y, ball.radius, 0, 2 * Math.PI);
            ctx.fill();
            ctx.restore();
        }
        function drawBricks() {
            for (let c = 0; c < brickColumnCount; c++) {
                for (let r = 0; r < brickRowCount; r++) {
                    const b = bricks[c][r];
                    if (b.status === 1) {
                        ctx.save();
                        ctx.shadowColor = 'rgba(0,0,0,0.4)';
                        ctx.shadowBlur = 4;
                        const gr = ctx.createLinearGradient(b.x, b.y, b.x + brickWidth, b.y + brickHeight);
                        gr.addColorStop(0, '#aaa');
                        gr.addColorStop(1, '#555');
                        ctx.fillStyle = gr;
                        ctx.fillRect(b.x, b.y, brickWidth, brickHeight);
                        ctx.restore();
                    }
                }
            }
        }

        // Update physics
        function update() {
            ball.vy += gravity;
            ball.x += ball.vx;
            ball.y += ball.vy;
            if (ball.x + ball.radius > canvas.width) { ball.x = canvas.width - ball.radius; applyBounce('x'); }
            else if (ball.x - ball.radius < 0) { ball.x = ball.radius; applyBounce('x'); }
            if (ball.y + ball.radius > canvas.height) { ball.y = canvas.height - ball.radius; applyBounce('y'); }
            else if (ball.y - ball.radius < 0) { ball.y = ball.radius; applyBounce('y'); }
            const now = Date.now();
            for (let c = 0; c < brickColumnCount; c++) {
                for (let r = 0; r < brickRowCount; r++) {
                    const b = bricks[c][r];
                    if (b.status === 0 && b.destroyedTime && now - b.destroyedTime > b.regenDelay) {
                        b.status = 1; b.destroyedTime = null;
                    }
                }
            }
            for (let c = 0; c < brickColumnCount; c++) {
                for (let r = 0; r < brickRowCount; r++) {
                    const b = bricks[c][r];
                    if (b.status === 1) {
                        const nx = Math.max(b.x, Math.min(ball.x, b.x + brickWidth));
                        const ny = Math.max(b.y, Math.min(ball.y, b.y + brickHeight));
                        const dx = ball.x - nx;
                        const dy = ball.y - ny;
                        if (dx * dx + dy * dy < ball.radius * ball.radius) {
                            b.status = 0; b.destroyedTime = now; b.regenDelay = 5000;
                            if (!ball.invincible) {
                                if (Math.abs(dx) > Math.abs(dy)) applyBounce('x');
                                else applyBounce('y');
                            }
                        }
                    }
                }
            }
        }

        function loop() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            drawBricks();
            if (ball.invincible) spawnSparkles();
            updateParticles();
            drawParticles();
            drawBall();
            update();
            requestAnimationFrame(loop);
        }
        loop();

        function scheduleInvincibility() {
            const d = Math.random() * 5000;
            setTimeout(() => {
                ball.invincible = true; ball.color = invincibleColor;
                setTimeout(() => {
                    ball.invincible = false; ball.color = normalColor; scheduleInvincibility();
                }, 1000);
            }, d);
        }
        scheduleInvincibility();

        canvas.addEventListener('click', (e) => {
            const rect = canvas.getBoundingClientRect();
            const cx = e.clientX - rect.left;
            const cy = e.clientY - rect.top;
            const now = Date.now();
            for (let c = 0; c < brickColumnCount; c++) {
                for (let r = 0; r < brickRowCount; r++) {
                    const b = bricks[c][r];
                    if (b.status === 1 && cx >= b.x && cx <= b.x + brickWidth && cy >= b.y && cy <= b.y + brickHeight) {
                        const cc = c, cr = r, er = 2;
                        for (let i = cc - er; i <= cc + er; i++) {
                            for (let j = cr - er; j <= cr + er; j++) {
                                const t = bricks[i] && bricks[i][j];
                                if (t && t.status === 1) {
                                    const dist = Math.hypot(i - cc, j - cr);
                                    if (dist <= er * (0.7 + Math.random() * 0.6)) {
                                        t.status = 0; t.destroyedTime = now; t.regenDelay = 8000;
                                    }
                                }
                            }
                        }
                        break;
                    }
                }
            }
            applyBounce('x'); applyBounce('y');
        });
    </script>
</body>
</html>
