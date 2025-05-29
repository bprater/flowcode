<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sand Cannon Simulator</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background: #1a1a1a;
            font-family: Arial, sans-serif;
            overflow: hidden;
        }
        
        canvas {
            display: block;
            cursor: crosshair;
        }
        
        .controls {
            position: absolute;
            top: 10px;
            left: 10px;
            background: rgba(0, 0, 0, 0.85);
            padding: 15px;
            border-radius: 10px;
            color: white;
            min-width: 220px;
            max-height: 90vh;
            overflow-y: auto;
            backdrop-filter: blur(5px);
        }
        
        .control-group {
            margin-bottom: 10px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            font-size: 12px;
        }
        
        input[type="range"] {
            width: 100%;
            margin-bottom: 5px;
        }
        
        input[type="color"] {
            width: 60px;
            height: 25px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
        }
        
        select {
            width: 100%;
            padding: 5px;
            background: #333;
            color: white;
            border: 1px solid #555;
            border-radius: 3px;
        }
        
        .value {
            font-size: 11px;
            color: #ccc;
        }
        
        .color-controls {
            display: flex;
            gap: 10px;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .toggle-group {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 10px;
        }
        
        input[type="checkbox"] {
            width: 16px;
            height: 16px;
        }
        
        .button-group {
            display: flex;
            gap: 5px;
            margin-bottom: 10px;
        }
        
        button {
            padding: 8px 12px;
            background: #ff6b35;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 11px;
            flex: 1;
        }
        
        button:hover {
            background: #ff8c69;
        }
        
        button.active {
            background: #00ff00;
        }
        
        button.recording {
            background: #ff0000;
            animation: pulse 1s infinite;
        }
        
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }
        
        .cannon-info {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(0, 0, 0, 0.85);
            padding: 10px;
            border-radius: 10px;
            color: white;
            font-size: 12px;
            backdrop-filter: blur(5px);
        }
        
        .cannon {
            position: absolute;
            width: 30px;
            height: 30px;
            background: #ff6b35;
            border-radius: 50%;
            border: 3px solid #ff8c69;
            cursor: move;
            z-index: 1000;
            box-shadow: 0 0 15px rgba(255, 107, 53, 0.5);
        }
    </style>
</head>
<body>
    <canvas id="canvas"></canvas>
    
    <div class="cannon" id="cannon"></div>
    
    <div class="controls">
        <div class="toggle-group">
            <input type="checkbox" id="autoFire">
            <label for="autoFire">Auto-Fire</label>
        </div>
        
        <div class="toggle-group">
            <input type="checkbox" id="drawMode">
            <label for="drawMode">Draw Mode</label>
        </div>
        
        <div class="toggle-group">
            <input type="checkbox" id="magnetMode">
            <label for="magnetMode">Magnet Mode</label>
        </div>
        
        <div class="control-group">
            <label>Background</label>
            <select id="backgroundSelect">
                <option value="dark">Dark</option>
                <option value="stars">Starfield</option>
                <option value="grid">Grid</option>
                <option value="gradient">Gradient</option>
                <option value="noise">Noise</option>
            </select>
        </div>
        
        <div class="control-group">
            <label>Sand Colors</label>
            <div class="color-controls">
                <input type="color" id="color1" value="#D2691E">
                <span>to</span>
                <input type="color" id="color2" value="#F4A460">
            </div>
        </div>
        
        <div class="control-group">
            <label>Volume (particles/frame)</label>
            <input type="range" id="volume" min="1" max="50" value="10">
            <div class="value" id="volumeValue">10</div>
        </div>
        
        <div class="control-group">
            <label>Velocity</label>
            <input type="range" id="velocity" min="1" max="20" value="8">
            <div class="value" id="velocityValue">8</div>
        </div>
        
        <div class="control-group">
            <label>Fan Angle</label>
            <input type="range" id="fanAngle" min="0" max="180" value="30">
            <div class="value" id="fanAngleValue">30°</div>
        </div>
        
        <div class="control-group">
            <label>Gravity</label>
            <input type="range" id="gravity" min="0.1" max="2" step="0.1" value="0.5">
            <div class="value" id="gravityValue">0.5</div>
        </div>
        
        <div class="control-group">
            <label>Bounce Damping</label>
            <input type="range" id="damping" min="0.1" max="1" step="0.05" value="0.7">
            <div class="value" id="dampingValue">0.7</div>
        </div>
        
        <div class="control-group">
            <label>Particle Decay</label>
            <input type="range" id="decay" min="0" max="1000" value="0">
            <div class="value" id="decayValue">Off</div>
        </div>
        
        <div class="control-group">
            <label>Brush Size</label>
            <input type="range" id="brushSize" min="2" max="20" value="8">
            <div class="value" id="brushSizeValue">8</div>
        </div>
        
        <div class="button-group">
            <button onclick="randomizeSettings()">Random</button>
            <button onclick="clearSand()">Clear Sand</button>
        </div>
        
        <div class="button-group">
            <button onclick="clearObstacles()">Clear Draw</button>
            <button onclick="windBurst()">Wind Burst</button>
        </div>
        
        <div class="button-group">
            <button id="recordBtn" onclick="toggleRecording()">Record</button>
            <button id="playBtn" onclick="togglePlayback()" disabled>Play</button>
        </div>
    </div>
    
    <div class="cannon-info">
        <div>Particles: <span id="particleCount">0</span></div>
        <div id="modeInfo">Click and hold to fire!</div>
        <div>Drag orange circle to move cannon</div>
        <div>Enable Draw Mode to create obstacles</div>
        <div>Magnet Mode: Click to attract particles</div>
    </div>

    <script>
        const canvas = document.getElementById('canvas');
        const ctx = canvas.getContext('2d');
        const cannon = document.getElementById('cannon');
        
        // Background variables - DECLARE THESE FIRST
        let stars = [];
        let noiseData = null;
        
        // Particles array and obstacles
        let particles = [];
        let settled = new Set();
        let obstacles = new Set();
        let drawingObstacles = [];
        let magnets = [];
        
        // Cannon state
        let cannonX = window.innerWidth / 2;
        let cannonY = window.innerHeight / 2;
        let firing = false;
        let mouseX = 0;
        let mouseY = 0;
        let nozzleAngle = 0;
        
        // Drawing state
        let drawing = false;
        let currentStroke = [];
        let lastDrawPoint = null;
        
        // Recording/playback state
        let recording = false;
        let playing = false;
        let recordedPath = [];
        let playbackIndex = 0;
        let playbackStartTime = 0;
        
        // Wind effect
        let windForce = { x: 0, y: 0 };
        let windDecay = 0.95;
        
        // Generate background elements
        function generateBackground() {
            // Generate stars
            stars = [];
            for (let i = 0; i < 100; i++) {
                stars.push({
                    x: Math.random() * canvas.width,
                    y: Math.random() * canvas.height,
                    brightness: Math.random(),
                    twinkle: Math.random() * Math.PI * 2
                });
            }
            
            // Generate noise pattern
            const imageData = ctx.createImageData(canvas.width, canvas.height);
            for (let i = 0; i < imageData.data.length; i += 4) {
                const noise = Math.random() * 30;
                imageData.data[i] = noise;     // R
                imageData.data[i + 1] = noise; // G
                imageData.data[i + 2] = noise; // B
                imageData.data[i + 3] = 255;   // A
            }
            noiseData = imageData;
        }
        
        // Resize canvas to fill viewport
        function resizeCanvas() {
            canvas.width = window.innerWidth;
            canvas.height = window.innerHeight;
            generateBackground();
        }
        resizeCanvas();
        window.addEventListener('resize', resizeCanvas);
        
        // Controls
        const autoFireCheckbox = document.getElementById('autoFire');
        const drawModeCheckbox = document.getElementById('drawMode');
        const magnetModeCheckbox = document.getElementById('magnetMode');
        const backgroundSelect = document.getElementById('backgroundSelect');
        const brushSizeSlider = document.getElementById('brushSize');
        const color1Input = document.getElementById('color1');
        const color2Input = document.getElementById('color2');
        const volumeSlider = document.getElementById('volume');
        const velocitySlider = document.getElementById('velocity');
        const fanAngleSlider = document.getElementById('fanAngle');
        const gravitySlider = document.getElementById('gravity');
        const dampingSlider = document.getElementById('damping');
        const decaySlider = document.getElementById('decay');
        const recordBtn = document.getElementById('recordBtn');
        const playBtn = document.getElementById('playBtn');
        const modeInfo = document.getElementById('modeInfo');
        
        // Background selection
        backgroundSelect.addEventListener('change', generateBackground);
        
        // Color interpolation function
        function hexToRgb(hex) {
            const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
            return result ? {
                r: parseInt(result[1], 16),
                g: parseInt(result[2], 16),
                b: parseInt(result[3], 16)
            } : null;
        }
        
        function interpolateColor(color1, color2, factor) {
            const c1 = hexToRgb(color1);
            const c2 = hexToRgb(color2);
            
            const r = Math.round(c1.r + (c2.r - c1.r) * factor);
            const g = Math.round(c1.g + (c2.g - c1.g) * factor);
            const b = Math.round(c1.b + (c2.b - c1.b) * factor);
            
            return `rgb(${r}, ${g}, ${b})`;
        }
        
        function getRandomSandColor() {
            const factor = Math.random();
            return interpolateColor(color1Input.value, color2Input.value, factor);
        }
        
        // Update slider values display
        function updateSliderValues() {
            document.getElementById('volumeValue').textContent = volumeSlider.value;
            document.getElementById('velocityValue').textContent = velocitySlider.value;
            document.getElementById('fanAngleValue').textContent = fanAngleSlider.value + '°';
            document.getElementById('gravityValue').textContent = gravitySlider.value;
            document.getElementById('dampingValue').textContent = dampingSlider.value;
            document.getElementById('brushSizeValue').textContent = brushSizeSlider.value;
            
            const decayValue = parseInt(decaySlider.value);
            document.getElementById('decayValue').textContent = decayValue === 0 ? 'Off' : decayValue + ' frames';
        }
        
        [volumeSlider, velocitySlider, fanAngleSlider, gravitySlider, dampingSlider, decaySlider, brushSizeSlider].forEach(slider => {
            slider.addEventListener('input', updateSliderValues);
        });
        updateSliderValues();
        
        // Mode checkboxes
        [autoFireCheckbox, drawModeCheckbox, magnetModeCheckbox].forEach(checkbox => {
            checkbox.addEventListener('change', updateModeInfo);
        });
        
        function updateModeInfo() {
            if (magnetModeCheckbox.checked) {
                modeInfo.textContent = 'Magnet Mode: Click to attract particles';
            } else if (drawModeCheckbox.checked) {
                modeInfo.textContent = 'Draw Mode: Click-drag to draw obstacles';
            } else if (autoFireCheckbox.checked) {
                modeInfo.textContent = 'Auto-firing enabled!';
            } else {
                modeInfo.textContent = 'Click and hold to fire!';
            }
        }
        
        // Special effects
        function windBurst() {
            const angle = Math.random() * Math.PI * 2;
            const force = 5 + Math.random() * 10;
            windForce.x = Math.cos(angle) * force;
            windForce.y = Math.sin(angle) * force;
        }
        
        // Randomize settings
        function randomizeSettings() {
            volumeSlider.value = Math.floor(Math.random() * 49) + 1;
            velocitySlider.value = Math.floor(Math.random() * 19) + 1;
            fanAngleSlider.value = Math.floor(Math.random() * 180);
            gravitySlider.value = (Math.random() * 1.9 + 0.1).toFixed(1);
            dampingSlider.value = (Math.random() * 0.9 + 0.1).toFixed(2);
            decaySlider.value = Math.floor(Math.random() * 1000);
            brushSizeSlider.value = Math.floor(Math.random() * 18) + 2;
            
            const randomColor1 = '#' + Math.floor(Math.random()*16777215).toString(16).padStart(6, '0');
            const randomColor2 = '#' + Math.floor(Math.random()*16777215).toString(16).padStart(6, '0');
            color1Input.value = randomColor1;
            color2Input.value = randomColor2;
            
            const backgrounds = ['dark', 'stars', 'grid', 'gradient', 'noise'];
            backgroundSelect.value = backgrounds[Math.floor(Math.random() * backgrounds.length)];
            generateBackground();
            
            updateSliderValues();
        }
        
        // Clear functions
        function clearSand() {
            particles = [];
            settled.clear();
        }
        
        function clearObstacles() {
            obstacles.clear();
            drawingObstacles = [];
        }
        
        // Recording functions
        function toggleRecording() {
            if (recording) {
                stopRecording();
            } else {
                startRecording();
            }
        }
        
        function startRecording() {
            recording = true;
            playing = false;
            recordedPath = [];
            recordBtn.textContent = 'Stop';
            recordBtn.classList.add('recording');
            playBtn.disabled = true;
            updateModeInfo();
        }
        
        function stopRecording() {
            recording = false;
            recordBtn.textContent = 'Record';
            recordBtn.classList.remove('recording');
            playBtn.disabled = recordedPath.length === 0;
            updateModeInfo();
        }
        
        function togglePlayback() {
            if (playing) {
                stopPlayback();
            } else {
                startPlayback();
            }
        }
        
        function startPlayback() {
            if (recordedPath.length === 0) return;
            
            playing = true;
            playbackIndex = 0;
            playbackStartTime = Date.now();
            playBtn.textContent = 'Stop';
            playBtn.classList.add('active');
            recordBtn.disabled = true;
            modeInfo.textContent = 'Playing back recorded path...';
        }
        
        function stopPlayback() {
            playing = false;
            playBtn.textContent = 'Play';
            playBtn.classList.remove('active');
            recordBtn.disabled = false;
            updateModeInfo();
        }
        
        // Draw background
        function drawBackground() {
            const bg = backgroundSelect.value;
            
            switch(bg) {
                case 'dark':
                    ctx.fillStyle = '#1a1a1a';
                    ctx.fillRect(0, 0, canvas.width, canvas.height);
                    break;
                    
                case 'stars':
                    ctx.fillStyle = '#0a0a0a';
                    ctx.fillRect(0, 0, canvas.width, canvas.height);
                    for (let star of stars) {
                        star.twinkle += 0.1;
                        const brightness = star.brightness * (0.5 + 0.5 * Math.sin(star.twinkle));
                        ctx.fillStyle = `rgba(255, 255, 255, ${brightness})`;
                        ctx.fillRect(star.x, star.y, 1, 1);
                    }
                    break;
                    
                case 'grid':
                    ctx.fillStyle = '#1a1a1a';
                    ctx.fillRect(0, 0, canvas.width, canvas.height);
                    ctx.strokeStyle = 'rgba(50, 50, 50, 0.5)';
                    ctx.lineWidth = 1;
                    for (let x = 0; x < canvas.width; x += 50) {
                        ctx.beginPath();
                        ctx.moveTo(x, 0);
                        ctx.lineTo(x, canvas.height);
                        ctx.stroke();
                    }
                    for (let y = 0; y < canvas.height; y += 50) {
                        ctx.beginPath();
                        ctx.moveTo(0, y);
                        ctx.lineTo(canvas.width, y);
                        ctx.stroke();
                    }
                    break;
                    
                case 'gradient':
                    const gradient = ctx.createLinearGradient(0, 0, canvas.width, canvas.height);
                    gradient.addColorStop(0, '#1a1a2e');
                    gradient.addColorStop(0.5, '#16213e');
                    gradient.addColorStop(1, '#0f3460');
                    ctx.fillStyle = gradient;
                    ctx.fillRect(0, 0, canvas.width, canvas.height);
                    break;
                    
                case 'noise':
                    if (noiseData) {
                        ctx.putImageData(noiseData, 0, 0);
                    }
                    break;
            }
        }
        
        // Cannon dragging
        let dragging = false;
        let dragOffset = { x: 0, y: 0 };
        
        cannon.addEventListener('mousedown', (e) => {
            if (!playing && !drawModeCheckbox.checked && !magnetModeCheckbox.checked) {
                dragging = true;
                const rect = cannon.getBoundingClientRect();
                dragOffset.x = e.clientX - rect.left;
                dragOffset.y = e.clientY - rect.top;
                e.preventDefault();
            }
        });
        
        // Distance function
        function distance(x1, y1, x2, y2) {
            return Math.sqrt((x2 - x1) * (x2 - x1) + (y2 - y1) * (y2 - y1));
        }
        
        // Add thick line to obstacles using circles along the line
        function addThickLineToObstacles(x0, y0, x1, y1, thickness) {
            const dist = distance(x0, y0, x1, y1);
            const steps = Math.max(1, Math.floor(dist / 2));
            
            for (let i = 0; i <= steps; i++) {
                const t = i / steps;
                const x = x0 + (x1 - x0) * t;
                const y = y0 + (y1 - y0) * t;
                
                const radius = Math.floor(thickness / 2);
                for (let dx = -radius; dx <= radius; dx++) {
                    for (let dy = -radius; dy <= radius; dy++) {
                        if (dx * dx + dy * dy <= radius * radius) {
                            const obstacleX = Math.floor(x + dx);
                            const obstacleY = Math.floor(y + dy);
                            if (obstacleX >= 0 && obstacleX < canvas.width && obstacleY >= 0 && obstacleY < canvas.height) {
                                const key = obstacleX + ',' + obstacleY;
                                obstacles.add(key);
                            }
                        }
                    }
                }
            }
        }
        
        // Mouse events for drawing and cannon control
        canvas.addEventListener('mousedown', (e) => {
            const rect = canvas.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            
            if (magnetModeCheckbox.checked) {
                magnets.push({ x: x, y: y, strength: 50, life: 300 });
            } else if (drawModeCheckbox.checked) {
                drawing = true;
                lastDrawPoint = {x: x, y: y};
                currentStroke = [{x: x, y: y}];
                const brushSize = parseInt(brushSizeSlider.value);
                addThickLineToObstacles(x, y, x, y, brushSize);
            } else if (!dragging && !playing) {
                firing = true;
            }
        });
        
        document.addEventListener('mousemove', (e) => {
            if (!playing) {
                mouseX = e.clientX;
                mouseY = e.clientY;
                
                if (dragging) {
                    cannonX = e.clientX - dragOffset.x;
                    cannonY = e.clientY - dragOffset.y;
                    
                    cannonX = Math.max(0, Math.min(window.innerWidth - 30, cannonX));
                    cannonY = Math.max(0, Math.min(window.innerHeight - 30, cannonY));
                    
                    updateCannonPosition();
                }
                
                if (drawing && drawModeCheckbox.checked) {
                    const rect = canvas.getBoundingClientRect();
                    const x = e.clientX - rect.left;
                    const y = e.clientY - rect.top;
                    
                    if (lastDrawPoint) {
                        const brushSize = parseInt(brushSizeSlider.value);
                        addThickLineToObstacles(lastDrawPoint.x, lastDrawPoint.y, x, y, brushSize);
                    }
                    
                    lastDrawPoint = {x: x, y: y};
                    currentStroke.push({x: x, y: y});
                }
                
                if (recording) {
                    recordedPath.push({
                        mouseX: mouseX,
                        mouseY: mouseY,
                        cannonX: cannonX,
                        cannonY: cannonY,
                        timestamp: Date.now()
                    });
                }
            }
            
            updateNozzleDirection();
        });
        
        document.addEventListener('mouseup', () => {
            dragging = false;
            firing = false;
            
            if (drawing) {
                drawing = false;
                if (currentStroke.length > 1) {
                    drawingObstacles.push([...currentStroke]);
                }
                currentStroke = [];
                lastDrawPoint = null;
            }
        });
        
        // Update cannon position
        function updateCannonPosition() {
            cannon.style.left = cannonX + 'px';
            cannon.style.top = cannonY + 'px';
        }
        
        // Update nozzle direction
        function updateNozzleDirection() {
            const centerX = cannonX + 15;
            const centerY = cannonY + 15;
            const dx = mouseX - centerX;
            const dy = mouseY - centerY;
            nozzleAngle = Math.atan2(dy, dx);
        }
        
        // Enhanced cannon drawing with volume and velocity visualization
        function drawCannon() {
            const centerX = cannonX + 15;
            const centerY = cannonY + 15;
            const fanAngle = parseFloat(fanAngleSlider.value) * Math.PI / 180;
            const volume = parseInt(volumeSlider.value);
            const velocity = parseFloat(velocitySlider.value);
            
            // Draw volume as arc thickness/opacity
            const volumeIntensity = volume / 50;
            ctx.strokeStyle = `rgba(255, 107, 53, ${0.2 + volumeIntensity * 0.6})`;
            ctx.lineWidth = 1 + volumeIntensity * 4;
            
            ctx.beginPath();
            const fanLength = 60;
            const angle1 = nozzleAngle - fanAngle / 2;
            const angle2 = nozzleAngle + fanAngle / 2;
            
            ctx.arc(centerX, centerY, fanLength, angle1, angle2);
            ctx.stroke();
            
            // Draw velocity as filled portion of nozzle
            const velocityPercent = velocity / 20;
            const nozzleLength = 25;
            const filledLength = nozzleLength * velocityPercent;
            
            // Base nozzle
            ctx.strokeStyle = '#333';
            ctx.lineWidth = 4;
            ctx.beginPath();
            ctx.moveTo(centerX, centerY);
            ctx.lineTo(centerX + Math.cos(nozzleAngle) * nozzleLength, centerY + Math.sin(nozzleAngle) * nozzleLength);
            ctx.stroke();
            
            // Filled portion showing velocity
            ctx.strokeStyle = '#ff6b35';
            ctx.lineWidth = 3;
            ctx.beginPath();
            ctx.moveTo(centerX, centerY);
            ctx.lineTo(centerX + Math.cos(nozzleAngle) * filledLength, centerY + Math.sin(nozzleAngle) * filledLength);
            ctx.stroke();
        }
        
        // Handle playback
        function updatePlayback() {
            if (!playing || recordedPath.length === 0) return;
            
            const currentTime = Date.now();
            const elapsed = currentTime - playbackStartTime;
            
            let targetFrame = null;
            for (let i = playbackIndex; i < recordedPath.length; i++) {
                const frameTime = recordedPath[i].timestamp - recordedPath[0].timestamp;
                if (frameTime >= elapsed) {
                    targetFrame = recordedPath[i];
                    playbackIndex = i;
                    break;
                }
            }
            
            if (targetFrame) {
                mouseX = targetFrame.mouseX;
                mouseY = targetFrame.mouseY;
                cannonX = targetFrame.cannonX;
                cannonY = targetFrame.cannonY;
                updateCannonPosition();
                updateNozzleDirection();
            } else {
                playbackStartTime = currentTime;
                playbackIndex = 0;
            }
        }
        
        // Particle class with decay and magnet attraction
        class Particle {
            constructor(x, y, vx, vy) {
                this.x = x;
                this.y = y;
                this.vx = vx;
                this.vy = vy;
                this.settled = false;
                this.color = getRandomSandColor();
                this.life = parseInt(decaySlider.value) || Infinity;
                this.maxLife = this.life;
                this.alpha = 1;
            }
            
            update() {
                if (this.settled) {
                    if (this.life !== Infinity) {
                        this.life--;
                        this.alpha = this.life / this.maxLife;
                        if (this.life <= 0) {
                            const key = this.x + ',' + this.y;
                            settled.delete(key);
                            return 'dead';
                        }
                    }
                    return;
                }
                
                if (this.life !== Infinity) {
                    this.life--;
                    this.alpha = this.life / this.maxLife;
                    if (this.life <= 0) return 'dead';
                }
                
                this.vy += parseFloat(gravitySlider.value);
                
                this.vx += windForce.x * 0.1;
                this.vy += windForce.y * 0.1;
                
                for (let magnet of magnets) {
                    const dx = magnet.x - this.x;
                    const dy = magnet.y - this.y;
                    const dist = Math.sqrt(dx * dx + dy * dy);
                    if (dist < 100 && dist > 0) {
                        const force = magnet.strength / (dist * dist);
                        this.vx += (dx / dist) * force * 0.1;
                        this.vy += (dy / dist) * force * 0.1;
                    }
                }
                
                const oldX = this.x;
                const oldY = this.y;
                const newX = this.x + this.vx;
                const newY = this.y + this.vy;
                
                let collided = false;
                
                const checkRadius = 3;
                for (let testX = Math.floor(Math.min(oldX, newX)) - checkRadius; testX <= Math.ceil(Math.max(oldX, newX)) + checkRadius; testX++) {
                    for (let testY = Math.floor(Math.min(oldY, newY)) - checkRadius; testY <= Math.ceil(Math.max(oldY, newY)) + checkRadius; testY++) {
                        const key = testX + ',' + testY;
                        if (obstacles.has(key)) {
                            if (this.lineCircleCollision(oldX, oldY, newX, newY, testX + 0.5, testY + 0.5, 1.5)) {
                                const obstacleNormalX = this.x - (testX + 0.5);
                                const obstacleNormalY = this.y - (testY + 0.5);
                                const normalLength = Math.sqrt(obstacleNormalX * obstacleNormalX + obstacleNormalY * obstacleNormalY);
                                
                                if (normalLength > 0) {
                                    const normalX = obstacleNormalX / normalLength;
                                    const normalY = obstacleNormalY / normalLength;
                                    
                                    const dotProduct = this.vx * normalX + this.vy * normalY;
                                    this.vx = (this.vx - 2 * dotProduct * normalX) * parseFloat(dampingSlider.value);
                                    this.vy = (this.vy - 2 * dotProduct * normalY) * parseFloat(dampingSlider.value);
                                    
                                    this.x = oldX + normalX * 2;
                                    this.y = oldY + normalY * 2;
                                    collided = true;
                                    break;
                                }
                            }
                        }
                    }
                    if (collided) break;
                }
                
                if (!collided) {
                    this.x = newX;
                    this.y = newY;
                }
                
                if (this.y >= canvas.height - 1) {
                    this.y = canvas.height - 1;
                    this.vy *= -parseFloat(dampingSlider.value);
                    this.vx *= parseFloat(dampingSlider.value);
                    collided = true;
                }
                
                if (this.x <= 0 || this.x >= canvas.width - 1) {
                    this.vx *= -parseFloat(dampingSlider.value);
                    this.x = Math.max(0, Math.min(canvas.width - 1, this.x));
                    collided = true;
                }
                
                const key = Math.floor(this.x) + ',' + Math.floor(this.y);
                const keyBelow = Math.floor(this.x) + ',' + (Math.floor(this.y) + 1);
                
                if (settled.has(keyBelow) || this.y >= canvas.height - 1) {
                    if (Math.abs(this.vx) < 0.5 && Math.abs(this.vy) < 0.5) {
                        this.settle();
                        return;
                    }
                }
                
                if (collided && Math.abs(this.vx) < 0.1 && Math.abs(this.vy) < 0.1) {
                    this.settle();
                }
            }
            
            lineCircleCollision(x1, y1, x2, y2, cx, cy, r) {
                const dx = x2 - x1;
                const dy = y2 - y1;
                const fx = x1 - cx;
                const fy = y1 - cy;
                
                const a = dx * dx + dy * dy;
                const b = 2 * (fx * dx + fy * dy);
                const c = (fx * fx + fy * fy) - r * r;
                
                const discriminant = b * b - 4 * a * c;
                
                if (discriminant < 0) return false;
                
                const discriminantSqrt = Math.sqrt(discriminant);
                const t1 = (-b - discriminantSqrt) / (2 * a);
                const t2 = (-b + discriminantSqrt) / (2 * a);
                
                return (t1 >= 0 && t1 <= 1) || (t2 >= 0 && t2 <= 1);
            }
            
            settle() {
                this.settled = true;
                this.vx = 0;
                this.vy = 0;
                this.x = Math.floor(this.x);
                this.y = Math.floor(this.y);
                const key = this.x + ',' + this.y;
                settled.add(key);
            }
            
            draw() {
                if (this.alpha < 0.1) return;
                
                const rgb = hexToRgb(this.color) || {r: 210, g: 105, b: 30};
                ctx.fillStyle = `rgba(${rgb.r}, ${rgb.g}, ${rgb.b}, ${this.alpha})`;
                ctx.fillRect(Math.floor(this.x), Math.floor(this.y), 1, 1);
            }
        }
        
        // Create particles
        function createParticles() {
            const shouldFire = autoFireCheckbox.checked || firing || playing;
            if (!shouldFire) return;
            
            const volume = parseInt(volumeSlider.value);
            const velocity = parseFloat(velocitySlider.value);
            const fanAngle = parseFloat(fanAngleSlider.value) * Math.PI / 180;
            
            const centerX = cannonX + 15;
            const centerY = cannonY + 15;
            
            for (let i = 0; i < volume; i++) {
                const spread = (Math.random() - 0.5) * fanAngle;
                const angle = nozzleAngle + spread;
                
                const speed = velocity * (0.8 + Math.random() * 0.4);
                const vx = Math.cos(angle) * speed;
                const vy = Math.sin(angle) * speed;
                
                particles.push(new Particle(centerX, centerY, vx, vy));
            }
            
            if (particles.length > 10000) {
                particles.splice(0, particles.length - 10000);
            }
        }
        
        // Animation loop
        function animate() {
            drawBackground();
            
            windForce.x *= windDecay;
            windForce.y *= windDecay;
            
            magnets = magnets.filter(magnet => {
                magnet.life--;
                magnet.strength *= 0.99;
                
                const alpha = magnet.life / 300;
                ctx.strokeStyle = `rgba(0, 255, 255, ${alpha * 0.5})`;
                ctx.lineWidth = 2;
                ctx.beginPath();
                ctx.arc(magnet.x, magnet.y, 50, 0, Math.PI * 2);
                ctx.stroke();
                
                return magnet.life > 0;
            });
            
            const brushSize = parseInt(brushSizeSlider.value);
            for (let obstacle of drawingObstacles) {
                if (obstacle.length > 1) {
                    ctx.strokeStyle = '#666';
                    ctx.lineWidth = brushSize;
                    ctx.lineCap = 'round';
                    ctx.lineJoin = 'round';
                    ctx.beginPath();
                    ctx.moveTo(obstacle[0].x, obstacle[0].y);
                    for (let i = 1; i < obstacle.length; i++) {
                        ctx.lineTo(obstacle[i].x, obstacle[i].y);
                    }
                    ctx.stroke();
                }
            }
            
            if (drawing && currentStroke.length > 1) {
                ctx.strokeStyle = '#999';
                ctx.lineWidth = brushSize;
                ctx.lineCap = 'round';
                ctx.lineJoin = 'round';
                ctx.beginPath();
                ctx.moveTo(currentStroke[0].x, currentStroke[0].y);
                for (let i = 1; i < currentStroke.length; i++) {
                    ctx.lineTo(currentStroke[i].x, currentStroke[i].y);
                }
                ctx.stroke();
            }
            
            updatePlayback();
            createParticles();
            
            particles = particles.filter(particle => {
                const result = particle.update();
                if (result === 'dead') return false;
                particle.draw();
                return true;
            });
            
            drawCannon();
            
            document.getElementById('particleCount').textContent = particles.length;
            
            requestAnimationFrame(animate);
        }
        
        // Initialize
        updateCannonPosition();
        animate();
    </script>
</body>
</html>