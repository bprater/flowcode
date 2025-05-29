<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>3D Bouncing Ball Physics</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            overflow: hidden;
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        #controls {
            position: absolute;
            top: 10px;
            left: 10px;
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 15px;
            border-radius: 10px;
            max-width: 300px;
            z-index: 100;
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
        
        .value-display {
            font-size: 10px;
            color: #ccc;
        }
        
        button {
            background: #667eea;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 5px;
            cursor: pointer;
            margin: 2px;
        }
        
        button:hover {
            background: #5a67d8;
        }
        
        #randomizeAll {
            background: #f093fb;
            font-weight: bold;
            width: 100%;
            margin: 10px 0;
        }
        
        #randomizeAll:hover {
            background: #f568a0;
        }
        
        select {
            width: 100%;
            padding: 5px;
            border-radius: 3px;
            border: none;
            margin-bottom: 10px;
        }
        
        #instructions {
            position: absolute;
            bottom: 10px;
            left: 10px;
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 10px;
            border-radius: 5px;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div id="controls">
        <div class="control-group">
            <label>Ball Type:</label>
            <select id="ballType">
                <option value="soccer">Soccer Ball</option>
                <option value="basketball">Basketball</option>
                <option value="tennis">Tennis Ball</option>
                <option value="bowling">Bowling Ball</option>
                <option value="volleyball">Volleyball</option>
            </select>
        </div>
        
        <button id="randomizeAll" onclick="randomizeAllSettings()">🎲 RANDOMIZE ALL SETTINGS</button>
        
        <div class="control-group">
            <label>Gravity: <span class="value-display" id="gravityValue">9.8</span></label>
            <input type="range" id="gravity" min="0" max="20" value="9.8" step="0.1">
        </div>
        
        <div class="control-group">
            <label>Weight: <span class="value-display" id="weightValue">1.0</span></label>
            <input type="range" id="weight" min="0.1" max="5" value="1.0" step="0.1">
        </div>
        
        <div class="control-group">
            <label>Bounce Damping: <span class="value-display" id="dampingValue">0.8</span></label>
            <input type="range" id="damping" min="0.1" max="1" value="0.8" step="0.05">
        </div>
        
        <div class="control-group">
            <label>Air Resistance: <span class="value-display" id="airResistanceValue">0.99</span></label>
            <input type="range" id="airResistance" min="0.9" max="1" value="0.99" step="0.01">
        </div>
        
        <div class="control-group">
            <label>Ball Size: <span class="value-display" id="ballSizeValue">0.5</span></label>
            <input type="range" id="ballSize" min="0.2" max="1.5" value="0.5" step="0.1">
        </div>
        
        <div class="control-group">
            <label>Spin Factor: <span class="value-display" id="spinFactorValue">1.0</span></label>
            <input type="range" id="spinFactor" min="0" max="3" value="1.0" step="0.1">
        </div>
        
        <button onclick="resetBall()">Reset Ball</button>
        <button onclick="randomKick()">Random Kick</button>
    </div>
    
    <div id="instructions">
        Click to kick the ball!<br>
        Hold and drag to grab and fling!
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    <script>
        // Scene setup
        let scene, camera, renderer;
        let ball, ballMaterial;
        let ballRadius = 0.5;
        let ballPosition = { x: 0, y: 2, z: 0 };
        let ballVelocity = { x: 0, y: 0, z: 0 };
        let ballAngularVelocity = { x: 0, y: 0, z: 0 };
        
        // Physics parameters
        let gravity = 9.8;
        let weight = 1.0;
        let damping = 0.8;
        let airResistance = 0.99;
        let spinFactor = 1.0;
        
        // Room boundaries
        const roomWidth = 10;
        const roomHeight = 6;
        const roomDepth = 3;
        
        // Mouse interaction
        let isMouseDown = false;
        let mouseStart = { x: 0, y: 0 };
        let mouseCurrent = { x: 0, y: 0 };
        
        function init() {
            // Scene
            scene = new THREE.Scene();
            
            // Camera (fixed 2D scroller style)
            camera = new THREE.OrthographicCamera(
                -roomWidth/2, roomWidth/2,
                roomHeight/2, -roomHeight/2,
                0.1, 100
            );
            camera.position.set(0, 0, 10);
            camera.lookAt(0, 0, 0);
            
            // Renderer
            renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
            renderer.setSize(window.innerWidth, window.innerHeight);
            renderer.shadowMap.enabled = true;
            renderer.shadowMap.type = THREE.PCFSoftShadowMap;
            document.body.appendChild(renderer.domElement);
            
            // Lighting
            const ambientLight = new THREE.AmbientLight(0x404040, 0.6);
            scene.add(ambientLight);
            
            const directionalLight = new THREE.DirectionalLight(0xffffff, 0.8);
            directionalLight.position.set(5, 5, 5);
            directionalLight.castShadow = true;
            directionalLight.shadow.mapSize.width = 2048;
            directionalLight.shadow.mapSize.height = 2048;
            scene.add(directionalLight);
            
            // Create ball
            createBall('soccer');
            
            // Initial random movement
            randomKick();
            
            // Event listeners
            setupControls();
            setupMouseEvents();
            
            // Start animation
            animate();
        }
        
        function createBall(type) {
            if (ball) {
                scene.remove(ball);
            }
            
            const geometry = new THREE.SphereGeometry(ballRadius, 32, 32);
            
            switch(type) {
                case 'soccer':
                    ballMaterial = createSoccerBallMaterial();
                    break;
                case 'basketball':
                    ballMaterial = createBasketballMaterial();
                    break;
                case 'tennis':
                    ballMaterial = createTennisBallMaterial();
                    break;
                case 'bowling':
                    ballMaterial = createBowlingBallMaterial();
                    break;
                case 'volleyball':
                    ballMaterial = createVolleyballMaterial();
                    break;
            }
            
            ball = new THREE.Mesh(geometry, ballMaterial);
            ball.position.set(ballPosition.x, ballPosition.y, ballPosition.z);
            ball.castShadow = true;
            scene.add(ball);
        }
        
        function createSoccerBallMaterial() {
            const canvas = document.createElement('canvas');
            canvas.width = 1024;
            canvas.height = 512;
            const ctx = canvas.getContext('2d');
            
            // White base
            ctx.fillStyle = '#ffffff';
            ctx.fillRect(0, 0, 1024, 512);
            
            // Create classic soccer ball pattern
            ctx.fillStyle = '#000000';
            
            // Draw pentagons in a more realistic pattern
            const pentagons = [
                {x: 512, y: 100, size: 40},    // top
                {x: 200, y: 180, size: 35},    // upper left
                {x: 824, y: 180, size: 35},    // upper right
                {x: 100, y: 300, size: 30},    // mid left
                {x: 924, y: 300, size: 30},    // mid right
                {x: 300, y: 380, size: 35},    // lower left
                {x: 724, y: 380, size: 35},    // lower right
                {x: 512, y: 450, size: 40},    // bottom
            ];
            
            pentagons.forEach(pent => {
                ctx.beginPath();
                for (let i = 0; i < 5; i++) {
                    const angle = (i / 5) * Math.PI * 2 - Math.PI / 2;
                    const x = pent.x + Math.cos(angle) * pent.size;
                    const y = pent.y + Math.sin(angle) * pent.size;
                    if (i === 0) ctx.moveTo(x, y);
                    else ctx.lineTo(x, y);
                }
                ctx.closePath();
                ctx.fill();
            });
            
            // Add some hexagonal white spaces between
            ctx.strokeStyle = '#cccccc';
            ctx.lineWidth = 2;
            for (let i = 0; i < 15; i++) {
                const x = Math.random() * 1024;
                const y = Math.random() * 512;
                ctx.beginPath();
                for (let j = 0; j < 6; j++) {
                    const angle = (j / 6) * Math.PI * 2;
                    const px = x + Math.cos(angle) * 25;
                    const py = y + Math.sin(angle) * 25;
                    if (j === 0) ctx.moveTo(px, py);
                    else ctx.lineTo(px, py);
                }
                ctx.closePath();
                ctx.stroke();
            }
            
            const texture = new THREE.CanvasTexture(canvas);
            texture.wrapS = THREE.RepeatWrapping;
            texture.wrapT = THREE.RepeatWrapping;
            return new THREE.MeshLambertMaterial({ map: texture });
        }
        
        function createBasketballMaterial() {
            const canvas = document.createElement('canvas');
            canvas.width = 1024;
            canvas.height = 512;
            const ctx = canvas.getContext('2d');
            
            // Create realistic basketball orange with texture
            const gradient = ctx.createRadialGradient(512, 256, 0, 512, 256, 400);
            gradient.addColorStop(0, '#ff8833');
            gradient.addColorStop(0.7, '#ee6611');
            gradient.addColorStop(1, '#cc4400');
            
            ctx.fillStyle = gradient;
            ctx.fillRect(0, 0, 1024, 512);
            
            // Add pebbled texture
            ctx.fillStyle = '#ff9944';
            for (let i = 0; i < 3000; i++) {
                const x = Math.random() * 1024;
                const y = Math.random() * 512;
                const radius = Math.random() * 2 + 1;
                ctx.beginPath();
                ctx.arc(x, y, radius, 0, Math.PI * 2);
                ctx.fill();
            }
            
            // Black seam lines
            ctx.strokeStyle = '#000000';
            ctx.lineWidth = 8;
            ctx.lineCap = 'round';
            
            // Vertical seams
            ctx.beginPath();
            ctx.moveTo(256, 20);
            ctx.quadraticCurveTo(200, 256, 256, 492);
            ctx.stroke();
            
            ctx.beginPath();
            ctx.moveTo(768, 20);
            ctx.quadraticCurveTo(824, 256, 768, 492);
            ctx.stroke();
            
            // Horizontal seam
            ctx.beginPath();
            ctx.moveTo(20, 256);
            ctx.quadraticCurveTo(256, 200, 512, 256);
            ctx.quadraticCurveTo(768, 312, 1004, 256);
            ctx.stroke();
            
            const texture = new THREE.CanvasTexture(canvas);
            texture.wrapS = THREE.RepeatWrapping;
            texture.wrapT = THREE.RepeatWrapping;
            return new THREE.MeshLambertMaterial({ map: texture });
        }
        
        function createTennisBallMaterial() {
            const canvas = document.createElement('canvas');
            canvas.width = 1024;
            canvas.height = 512;
            const ctx = canvas.getContext('2d');
            
            // Tennis ball yellow-green with fuzzy texture
            ctx.fillStyle = '#ccff33';
            ctx.fillRect(0, 0, 1024, 512);
            
            // Add fuzzy texture
            ctx.fillStyle = '#ddff55';
            for (let i = 0; i < 2000; i++) {
                const x = Math.random() * 1024;
                const y = Math.random() * 512;
                ctx.fillRect(x, y, 1, 1);
            }
            
            ctx.fillStyle = '#aabb22';
            for (let i = 0; i < 1000; i++) {
                const x = Math.random() * 1024;
                const y = Math.random() * 512;
                ctx.fillRect(x, y, 1, 1);
            }
            
            // Classic tennis ball curved seam
            ctx.strokeStyle = '#ffffff';
            ctx.lineWidth = 6;
            ctx.lineCap = 'round';
            
            // Main seam curve
            ctx.beginPath();
            ctx.moveTo(0, 256);
            ctx.quadraticCurveTo(256, 100, 512, 256);
            ctx.quadraticCurveTo(768, 412, 1024, 256);
            ctx.stroke();
            
            // Mirror seam
            ctx.beginPath();
            ctx.moveTo(0, 256);
            ctx.quadraticCurveTo(256, 412, 512, 256);
            ctx.quadraticCurveTo(768, 100, 1024, 256);
            ctx.stroke();
            
            const texture = new THREE.CanvasTexture(canvas);
            texture.wrapS = THREE.RepeatWrapping;
            texture.wrapT = THREE.RepeatWrapping;
            return new THREE.MeshLambertMaterial({ map: texture });
        }
        
        function createBowlingBallMaterial() {
            const canvas = document.createElement('canvas');
            canvas.width = 1024;
            canvas.height = 512;
            const ctx = canvas.getContext('2d');
            
            // Dark bowling ball with marble effect
            const gradient = ctx.createRadialGradient(512, 256, 0, 512, 256, 400);
            gradient.addColorStop(0, '#2a2a4a');
            gradient.addColorStop(0.5, '#1a1a3a');
            gradient.addColorStop(1, '#0a0a2a');
            
            ctx.fillStyle = gradient;
            ctx.fillRect(0, 0, 1024, 512);
            
            // Add marble swirls
            ctx.strokeStyle = '#3a3a6a';
            ctx.lineWidth = 3;
            for (let i = 0; i < 20; i++) {
                ctx.beginPath();
                ctx.moveTo(Math.random() * 1024, Math.random() * 512);
                ctx.quadraticCurveTo(
                    Math.random() * 1024, Math.random() * 512,
                    Math.random() * 1024, Math.random() * 512
                );
                ctx.stroke();
            }
            
            // Finger holes positioned correctly
            ctx.fillStyle = '#000000';
            
            // Two finger holes
            ctx.beginPath();
            ctx.arc(450, 220, 20, 0, Math.PI * 2);
            ctx.fill();
            
            ctx.beginPath();
            ctx.arc(574, 220, 20, 0, Math.PI * 2);
            ctx.fill();
            
            // Thumb hole (larger)
            ctx.beginPath();
            ctx.arc(512, 320, 25, 0, Math.PI * 2);
            ctx.fill();
            
            // Add highlights to holes
            ctx.fillStyle = '#111111';
            ctx.beginPath();
            ctx.arc(445, 215, 8, 0, Math.PI * 2);
            ctx.fill();
            
            ctx.beginPath();
            ctx.arc(569, 215, 8, 0, Math.PI * 2);
            ctx.fill();
            
            ctx.beginPath();
            ctx.arc(507, 315, 10, 0, Math.PI * 2);
            ctx.fill();
            
            const texture = new THREE.CanvasTexture(canvas);
            texture.wrapS = THREE.RepeatWrapping;
            texture.wrapT = THREE.RepeatWrapping;
            return new THREE.MeshLambertMaterial({ map: texture });
        }
        
        function createVolleyballMaterial() {
            const canvas = document.createElement('canvas');
            canvas.width = 1024;
            canvas.height = 512;
            const ctx = canvas.getContext('2d');
            
            // White and blue volleyball
            ctx.fillStyle = '#ffffff';
            ctx.fillRect(0, 0, 1024, 512);
            
            // Blue sections
            ctx.fillStyle = '#0066cc';
            
            // Create volleyball panel pattern
            const panels = [
                {x: 0, y: 0, w: 342, h: 256},
                {x: 682, y: 0, w: 342, h: 256},
                {x: 341, y: 256, w: 342, h: 256},
            ];
            
            panels.forEach(panel => {
                ctx.fillRect(panel.x, panel.y, panel.w, panel.h);
            });
            
            // Add panel lines
            ctx.strokeStyle = '#003388';
            ctx.lineWidth = 4;
            
            // Vertical lines
            ctx.beginPath();
            ctx.moveTo(341, 0);
            ctx.lineTo(341, 512);
            ctx.stroke();
            
            ctx.beginPath();
            ctx.moveTo(683, 0);
            ctx.lineTo(683, 512);
            ctx.stroke();
            
            // Horizontal line
            ctx.beginPath();
            ctx.moveTo(0, 256);
            ctx.lineTo(1024, 256);
            ctx.stroke();
            
            // Add some curved seam details
            ctx.strokeStyle = '#cccccc';
            ctx.lineWidth = 2;
            
            for (let i = 0; i < 3; i++) {
                const x = i * 342 + 171;
                ctx.beginPath();
                ctx.arc(x, 128, 80, 0, Math.PI);
                ctx.stroke();
                
                ctx.beginPath();
                ctx.arc(x, 384, 80, Math.PI, Math.PI * 2);
                ctx.stroke();
            }
            
            const texture = new THREE.CanvasTexture(canvas);
            texture.wrapS = THREE.RepeatWrapping;
            texture.wrapT = THREE.RepeatWrapping;
            return new THREE.MeshLambertMaterial({ map: texture });
        }
        
        function randomizeAllSettings() {
            // Randomize ball type
            const ballTypes = ['soccer', 'basketball', 'tennis', 'bowling', 'volleyball'];
            const randomBallType = ballTypes[Math.floor(Math.random() * ballTypes.length)];
            document.getElementById('ballType').value = randomBallType;
            createBall(randomBallType);
            
            // Randomize and set all sliders
            const settings = [
                {id: 'gravity', min: 0, max: 20, value: Math.random() * 20},
                {id: 'weight', min: 0.1, max: 5, value: 0.1 + Math.random() * 4.9},
                {id: 'damping', min: 0.1, max: 1, value: 0.1 + Math.random() * 0.9},
                {id: 'airResistance', min: 0.9, max: 1, value: 0.9 + Math.random() * 0.1},
                {id: 'ballSize', min: 0.2, max: 1.5, value: 0.2 + Math.random() * 1.3},
                {id: 'spinFactor', min: 0, max: 3, value: Math.random() * 3}
            ];
            
            settings.forEach(setting => {
                const slider = document.getElementById(setting.id);
                const valueDisplay = document.getElementById(setting.id + 'Value');
                
                slider.value = setting.value;
                valueDisplay.textContent = setting.value.toFixed(2);
                
                // Apply the values immediately
                switch(setting.id) {
                    case 'gravity': gravity = setting.value; break;
                    case 'weight': weight = setting.value; break;
                    case 'damping': damping = setting.value; break;
                    case 'airResistance': airResistance = setting.value; break;
                    case 'spinFactor': spinFactor = setting.value; break;
                    case 'ballSize':
                        ballRadius = setting.value;
                        if (ball) ball.scale.setScalar(setting.value / 0.5);
                        break;
                }
            });
            
            // Give the ball a random kick to show off the new settings
            randomKick();
        }
        
        function setupControls() {
            const controls = {
                gravity: document.getElementById('gravity'),
                weight: document.getElementById('weight'),
                damping: document.getElementById('damping'),
                airResistance: document.getElementById('airResistance'),
                ballSize: document.getElementById('ballSize'),
                spinFactor: document.getElementById('spinFactor'),
                ballType: document.getElementById('ballType')
            };
            
            // Update displays and values
            Object.keys(controls).forEach(key => {
                if (key === 'ballType') {
                    controls[key].addEventListener('change', (e) => {
                        createBall(e.target.value);
                    });
                } else {
                    const valueDisplay = document.getElementById(key + 'Value');
                    controls[key].addEventListener('input', (e) => {
                        const value = parseFloat(e.target.value);
                        valueDisplay.textContent = value.toFixed(2);
                        
                        switch(key) {
                            case 'gravity': gravity = value; break;
                            case 'weight': weight = value; break;
                            case 'damping': damping = value; break;
                            case 'airResistance': airResistance = value; break;
                            case 'spinFactor': spinFactor = value; break;
                            case 'ballSize':
                                ballRadius = value;
                                ball.scale.setScalar(value / 0.5);
                                break;
                        }
                    });
                }
            });
        }
        
        function setupMouseEvents() {
            renderer.domElement.addEventListener('mousedown', onMouseDown);
            renderer.domElement.addEventListener('mousemove', onMouseMove);
            renderer.domElement.addEventListener('mouseup', onMouseUp);
        }
        
        function onMouseDown(event) {
            isMouseDown = true;
            mouseStart.x = (event.clientX / window.innerWidth) * 2 - 1;
            mouseStart.y = -(event.clientY / window.innerHeight) * 2 + 1;
            mouseCurrent.x = mouseStart.x;
            mouseCurrent.y = mouseStart.y;
        }
        
        function onMouseMove(event) {
            if (isMouseDown) {
                mouseCurrent.x = (event.clientX / window.innerWidth) * 2 - 1;
                mouseCurrent.y = -(event.clientY / window.innerHeight) * 2 + 1;
                
                // Move ball to mouse position while dragging
                ballPosition.x = mouseCurrent.x * roomWidth / 2;
                ballPosition.y = mouseCurrent.y * roomHeight / 2;
                ballVelocity.x = 0;
                ballVelocity.y = 0;
                ballVelocity.z = 0;
            }
        }
        
        function onMouseUp(event) {
            if (isMouseDown) {
                const deltaX = mouseCurrent.x - mouseStart.x;
                const deltaY = mouseCurrent.y - mouseStart.y;
                
                // Apply velocity based on drag
                ballVelocity.x = deltaX * 20;
                ballVelocity.y = deltaY * 20;
                ballVelocity.z = (Math.random() - 0.5) * 5;
                
                // Add some spin
                ballAngularVelocity.x = deltaY * spinFactor * 10;
                ballAngularVelocity.y = deltaX * spinFactor * 10;
                ballAngularVelocity.z = (Math.random() - 0.5) * spinFactor * 5;
            }
            isMouseDown = false;
        }
        
        function resetBall() {
            ballPosition.x = 0;
            ballPosition.y = 2;
            ballPosition.z = 0;
            ballVelocity.x = 0;
            ballVelocity.y = 0;
            ballVelocity.z = 0;
            ballAngularVelocity.x = 0;
            ballAngularVelocity.y = 0;
            ballAngularVelocity.z = 0;
        }
        
        function randomKick() {
            ballVelocity.x = (Math.random() - 0.5) * 10;
            ballVelocity.y = Math.random() * 8 + 2;
            ballVelocity.z = (Math.random() - 0.5) * 6;
            
            ballAngularVelocity.x = (Math.random() - 0.5) * spinFactor * 10;
            ballAngularVelocity.y = (Math.random() - 0.5) * spinFactor * 10;
            ballAngularVelocity.z = (Math.random() - 0.5) * spinFactor * 10;
        }
        
        function updatePhysics(deltaTime) {
            if (isMouseDown) return; // Don't update physics while dragging
            
            // Apply gravity
            ballVelocity.y -= gravity * weight * deltaTime;
            
            // Apply air resistance
            ballVelocity.x *= Math.pow(airResistance, deltaTime * 60);
            ballVelocity.y *= Math.pow(airResistance, deltaTime * 60);
            ballVelocity.z *= Math.pow(airResistance, deltaTime * 60);
            
            // Update position
            ballPosition.x += ballVelocity.x * deltaTime;
            ballPosition.y += ballVelocity.y * deltaTime;
            ballPosition.z += ballVelocity.z * deltaTime;
            
            // Collision detection and response
            const halfWidth = roomWidth / 2 - ballRadius;
            const halfDepth = roomDepth / 2 - ballRadius;
            const floorY = -roomHeight / 2 + ballRadius;
            const ceilingY = roomHeight / 2 - ballRadius;
            
            // Floor and ceiling
            if (ballPosition.y <= floorY) {
                ballPosition.y = floorY;
                ballVelocity.y = -ballVelocity.y * damping;
                ballAngularVelocity.x += ballVelocity.z * spinFactor * 0.1;
                ballAngularVelocity.z -= ballVelocity.x * spinFactor * 0.1;
            }
            if (ballPosition.y >= ceilingY) {
                ballPosition.y = ceilingY;
                ballVelocity.y = -ballVelocity.y * damping;
            }
            
            // Left and right walls
            if (ballPosition.x <= -halfWidth) {
                ballPosition.x = -halfWidth;
                ballVelocity.x = -ballVelocity.x * damping;
                ballAngularVelocity.y += ballVelocity.z * spinFactor * 0.1;
                ballAngularVelocity.z -= ballVelocity.y * spinFactor * 0.1;
            }
            if (ballPosition.x >= halfWidth) {
                ballPosition.x = halfWidth;
                ballVelocity.x = -ballVelocity.x * damping;
                ballAngularVelocity.y -= ballVelocity.z * spinFactor * 0.1;
                ballAngularVelocity.z += ballVelocity.y * spinFactor * 0.1;
            }
            
            // Front and back walls
            if (ballPosition.z <= -halfDepth) {
                ballPosition.z = -halfDepth;
                ballVelocity.z = -ballVelocity.z * damping;
                ballAngularVelocity.x += ballVelocity.y * spinFactor * 0.1;
                ballAngularVelocity.y -= ballVelocity.x * spinFactor * 0.1;
            }
            if (ballPosition.z >= halfDepth) {
                ballPosition.z = halfDepth;
                ballVelocity.z = -ballVelocity.z * damping;
                ballAngularVelocity.x -= ballVelocity.y * spinFactor * 0.1;
                ballAngularVelocity.y += ballVelocity.x * spinFactor * 0.1;
            }
            
            // Update ball rotation based on movement
            ball.rotation.x += ballAngularVelocity.x * deltaTime;
            ball.rotation.y += ballAngularVelocity.y * deltaTime;
            ball.rotation.z += ballAngularVelocity.z * deltaTime;
            
            // Dampen angular velocity
            ballAngularVelocity.x *= Math.pow(0.98, deltaTime * 60);
            ballAngularVelocity.y *= Math.pow(0.98, deltaTime * 60);
            ballAngularVelocity.z *= Math.pow(0.98, deltaTime * 60);
        }
        
        let lastTime = 0;
        function animate(currentTime) {
            const deltaTime = (currentTime - lastTime) / 1000;
            lastTime = currentTime;
            
            if (deltaTime < 0.1) { // Prevent large time jumps
                updatePhysics(deltaTime);
                
                // Update ball position
                ball.position.set(ballPosition.x, ballPosition.y, ballPosition.z);
            }
            
            renderer.render(scene, camera);
            requestAnimationFrame(animate);
        }
        
        // Handle window resize
        window.addEventListener('resize', () => {
            camera.left = -roomWidth / 2;
            camera.right = roomWidth / 2;
            camera.top = roomHeight / 2;
            camera.bottom = -roomHeight / 2;
            camera.updateProjectionMatrix();
            renderer.setSize(window.innerWidth, window.innerHeight);
        });
        
        // Initialize the app
        init();
    </script>
</body>
</html>