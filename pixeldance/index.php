<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pixel Shapes - Interactive 3D</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            overflow: hidden;
            background: #000;
            font-family: Arial, sans-serif;
        }
        
        #container {
            width: 100vw;
            height: 100vh;
        }
        
        #info {
            position: absolute;
            top: 20px;
            left: 20px;
            color: white;
            z-index: 100;
            font-size: 14px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.8);
        }
        
        #controls {
            position: absolute;
            top: 20px;
            right: 20px;
            color: white;
            z-index: 100;
            background: rgba(0,0,0,0.7);
            padding: 15px;
            border-radius: 10px;
            backdrop-filter: blur(10px);
            min-width: 220px;
            max-height: 90vh;
            overflow-y: auto;
        }
        
        .control-group {
            margin-bottom: 12px;
        }
        
        .control-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            font-size: 11px;
        }
        
        .control-group select, .control-group button, .control-group input {
            width: 100%;
            padding: 6px;
            border: none;
            border-radius: 5px;
            background: rgba(255,255,255,0.9);
            color: #333;
            cursor: pointer;
            font-size: 11px;
            box-sizing: border-box;
        }
        
        .control-group input[type="range"] {
            cursor: pointer;
            padding: 4px;
        }
        
        .control-group input[type="checkbox"] {
            width: auto;
            margin-right: 8px;
        }
        
        .checkbox-container {
            display: flex;
            align-items: center;
            color: white;
            font-size: 11px;
        }
        
        .control-group button {
            background: linear-gradient(45deg, #ff6b6b, #4ecdc4);
            color: white;
            font-weight: bold;
            transition: transform 0.2s;
            margin-bottom: 3px;
        }
        
        .control-group button:hover {
            transform: scale(1.05);
        }
        
        .control-group button.pause {
            background: linear-gradient(45deg, #ffa726, #ff7043);
        }
        
        .control-group button.pause.paused {
            background: linear-gradient(45deg, #66bb6a, #43a047);
        }
        
        .slider-value {
            font-size: 9px;
            color: #ccc;
            text-align: center;
            margin-top: 2px;
        }
        
        #footer {
            position: absolute;
            bottom: 20px;
            left: 20px;
            color: white;
            z-index: 100;
            font-size: 12px;
            opacity: 0.7;
        }
        
        .divider {
            border-top: 1px solid rgba(255,255,255,0.2);
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div id="info">
        <h2>🎮 Pixel Shapes</h2>
        <p>Drag to rotate • Scroll to zoom</p>
    </div>
    
    <div id="controls">
        <div class="control-group">
            <label>🔷 Shape</label>
            <select id="shapeSelect">
                <option value="cube">Cube</option>
                <option value="pyramid">Pyramid</option>
                <option value="sphere">Sphere</option>
                <option value="diamond">Diamond</option>
                <option value="torus">Torus</option>
                <option value="helix">Helix</option>
                <option value="tower">Tower</option>
            </select>
        </div>
        
        <div class="control-group">
            <label>✨ Light Pattern</label>
            <select id="patternSelect">
                <option value="rainbow">Rainbow Wave</option>
                <option value="pulse">Color Pulse</option>
                <option value="spiral">Spiral</option>
                <option value="wave">Wave Motion</option>
                <option value="strobe">Strobe</option>
                <option value="fire">Fire Effect</option>
                <option value="matrix">Matrix Rain</option>
                <option value="ripple">Ripple Effect</option>
                <option value="lightning">⚡ Lightning</option>
                <option value="heartbeat">💓 Heartbeat</option>
                <option value="aurora">🌌 Aurora</option>
                <option value="kaleidoscope">🔮 Kaleidoscope</option>
            </select>
        </div>
        
        <div class="divider"></div>
        
        <div class="control-group">
            <label>🌍 Background Scene</label>
            <select id="backgroundSelect">
                <option value="space">🌌 Space</option>
                <option value="mountain">🏔️ Mountain</option>
                <option value="desert">🏜️ Desert</option>
                <option value="ocean">🌊 Ocean</option>
                <option value="forest">🌲 Forest</option>
                <option value="black">⚫ Black</option>
            </select>
        </div>
        
        <div class="divider"></div>
        
        <div class="control-group">
            <div class="checkbox-container">
                <input type="checkbox" id="glowEffect">
                <label for="glowEffect">✨ Glow Effects</label>
            </div>
        </div>
        
        <div class="control-group">
            <div class="checkbox-container">
                <input type="checkbox" id="symmetryMode">
                <label for="symmetryMode">🔄 Symmetry Mode</label>
            </div>
        </div>
        
        <div class="divider"></div>
        
        <div class="control-group">
            <label>📏 Resolution: <span id="densityValue">10</span></label>
            <input type="range" id="densitySlider" min="6" max="24" value="10" step="2">
            <div class="slider-value">Low ← → High resolution</div>
        </div>
        
        <div class="control-group">
            <button id="pauseButton" class="pause">⏸️ Pause</button>
            <button onclick="randomizePattern()">🎲 Random Pattern</button>
        </div>
    </div>
    
    <div id="footer">
        <p>💫 Interactive 3D pixel art with dynamic lighting</p>
    </div>
    
    <div id="container"></div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    <script>
        // Scene setup
        const scene = new THREE.Scene();
        const camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 0.1, 1000);
        const renderer = new THREE.WebGLRenderer({ antialias: true });
        renderer.setSize(window.innerWidth, window.innerHeight);
        renderer.setClearColor(0x000000);
        document.getElementById('container').appendChild(renderer.domElement);

        // Global variables
        let pixels = [];
        let pixelGroup = new THREE.Group();
        let currentShape = 'cube';
        let currentPattern = 'rainbow';
        let currentDensity = 10;
        let isPaused = false;
        let glowEnabled = false;
        let symmetryEnabled = false;
        let currentBackground = 'space';
        
        // Lightning effect variables
        let lightningBolts = [];
        let nextLightningTime = 0;
        
        // Background
        let backgroundSphere;
        
        // Fixed shape size and dynamic pixel properties
        const totalShapeSize = 1.0;
        let pixelSize = 0.08;
        let spacing = 0.1;

        scene.add(pixelGroup);
        camera.position.z = 3;

        // Create background sphere
        function createBackground(type) {
            if (backgroundSphere) {
                scene.remove(backgroundSphere);
            }
            
            if (type === 'black') return;
            
            const sphereGeometry = new THREE.SphereGeometry(50, 32, 32);
            const loader = new THREE.TextureLoader();
            
            // Create procedural texture based on type
            const canvas = document.createElement('canvas');
            canvas.width = 1024;
            canvas.height = 512;
            const ctx = canvas.getContext('2d');
            
            switch(type) {
                case 'space':
                    // Starfield
                    const gradient = ctx.createLinearGradient(0, 0, 0, 512);
                    gradient.addColorStop(0, '#000011');
                    gradient.addColorStop(0.5, '#001122');
                    gradient.addColorStop(1, '#000011');
                    ctx.fillStyle = gradient;
                    ctx.fillRect(0, 0, 1024, 512);
                    
                    // Add stars
                    ctx.fillStyle = 'white';
                    for (let i = 0; i < 1000; i++) {
                        const x = Math.random() * 1024;
                        const y = Math.random() * 512;
                        const size = Math.random() * 2;
                        ctx.fillRect(x, y, size, size);
                    }
                    break;
                    
                case 'mountain':
                    // Mountain silhouette
                    const mountainGrad = ctx.createLinearGradient(0, 0, 0, 512);
                    mountainGrad.addColorStop(0, '#87CEEB');
                    mountainGrad.addColorStop(0.7, '#FFA500');
                    mountainGrad.addColorStop(1, '#FF6347');
                    ctx.fillStyle = mountainGrad;
                    ctx.fillRect(0, 0, 1024, 512);
                    
                    // Mountains
                    ctx.fillStyle = '#2F4F4F';
                    ctx.beginPath();
                    ctx.moveTo(0, 512);
                    for (let i = 0; i <= 1024; i += 50) {
                        ctx.lineTo(i, 300 + Math.sin(i * 0.01) * 100);
                    }
                    ctx.lineTo(1024, 512);
                    ctx.fill();
                    break;
                    
                case 'desert':
                    // Desert dunes
                    const desertGrad = ctx.createLinearGradient(0, 0, 0, 512);
                    desertGrad.addColorStop(0, '#FFE4B5');
                    desertGrad.addColorStop(0.5, '#DEB887');
                    desertGrad.addColorStop(1, '#F4A460');
                    ctx.fillStyle = desertGrad;
                    ctx.fillRect(0, 0, 1024, 512);
                    
                    // Dunes
                    ctx.fillStyle = '#CD853F';
                    for (let i = 0; i < 5; i++) {
                        ctx.beginPath();
                        ctx.ellipse(200 * i, 400, 150, 50, 0, 0, Math.PI * 2);
                        ctx.fill();
                    }
                    break;
                    
                case 'ocean':
                    // Ocean waves
                    const oceanGrad = ctx.createLinearGradient(0, 0, 0, 512);
                    oceanGrad.addColorStop(0, '#87CEEB');
                    oceanGrad.addColorStop(0.5, '#4682B4');
                    oceanGrad.addColorStop(1, '#191970');
                    ctx.fillStyle = oceanGrad;
                    ctx.fillRect(0, 0, 1024, 512);
                    
                    // Waves
                    ctx.strokeStyle = '#B0C4DE';
                    ctx.lineWidth = 2;
                    for (let y = 200; y < 512; y += 40) {
                        ctx.beginPath();
                        ctx.moveTo(0, y);
                        for (let x = 0; x <= 1024; x += 20) {
                            ctx.lineTo(x, y + Math.sin(x * 0.02) * 10);
                        }
                        ctx.stroke();
                    }
                    break;
                    
                case 'forest':
                    // Forest scene
                    const forestGrad = ctx.createLinearGradient(0, 0, 0, 512);
                    forestGrad.addColorStop(0, '#228B22');
                    forestGrad.addColorStop(0.7, '#006400');
                    forestGrad.addColorStop(1, '#013220');
                    ctx.fillStyle = forestGrad;
                    ctx.fillRect(0, 0, 1024, 512);
                    
                    // Trees
                    ctx.fillStyle = '#8B4513';
                    for (let i = 0; i < 20; i++) {
                        const x = Math.random() * 1024;
                        ctx.fillRect(x, 350, 20, 162);
                        ctx.fillStyle = '#228B22';
                        ctx.beginPath();
                        ctx.ellipse(x + 10, 350, 40, 60, 0, 0, Math.PI * 2);
                        ctx.fill();
                        ctx.fillStyle = '#8B4513';
                    }
                    break;
            }
            
            const texture = new THREE.CanvasTexture(canvas);
            const material = new THREE.MeshBasicMaterial({ 
                map: texture, 
                side: THREE.BackSide,
                transparent: true,
                opacity: 0.8
            });
            
            backgroundSphere = new THREE.Mesh(sphereGeometry, material);
            scene.add(backgroundSphere);
        }

        // Calculate pixel size and spacing based on density
        function calculatePixelProperties(density) {
            const scaleFactor = totalShapeSize / density;
            return {
                pixelSize: scaleFactor * 0.9,
                spacing: scaleFactor
            };
        }

        // Shape generators (same as before - keeping for brevity)
        function generateCubePixels(size) {
            const positions = [];
            for (let x = 0; x < size; x++) {
                for (let y = 0; y < size; y++) {
                    for (let z = 0; z < size; z++) {
                        if (x === 0 || x === size - 1 || 
                            y === 0 || y === size - 1 || 
                            z === 0 || z === size - 1) {
                            positions.push({
                                x: (x - (size - 1) / 2) * spacing,
                                y: (y - (size - 1) / 2) * spacing,
                                z: (z - (size - 1) / 2) * spacing,
                                gridX: x, gridY: y, gridZ: z
                            });
                        }
                    }
                }
            }
            return positions;
        }

        function generatePyramidPixels(baseSize) {
            const positions = [];
            const height = Math.floor(baseSize * 0.8);
            
            for (let level = 0; level < height; level++) {
                const levelSize = baseSize - Math.floor(level * 2);
                if (levelSize <= 0) break;
                
                const yPos = level - (height - 1) / 2;
                
                for (let x = 0; x < levelSize; x++) {
                    for (let z = 0; z < levelSize; z++) {
                        if (levelSize === 1 || x === 0 || x === levelSize - 1 || z === 0 || z === levelSize - 1) {
                            positions.push({
                                x: (x - (levelSize - 1) / 2) * spacing,
                                y: yPos * spacing,
                                z: (z - (levelSize - 1) / 2) * spacing,
                                gridX: x, gridY: level, gridZ: z, level: level
                            });
                        }
                    }
                }
            }
            return positions;
        }

        function generateSpherePixels(size) {
            const positions = [];
            const radius = (size - 1) / 2;
            const center = (size - 1) / 2;
            
            for (let x = 0; x < size; x++) {
                for (let y = 0; y < size; y++) {
                    for (let z = 0; z < size; z++) {
                        const distance = Math.sqrt(
                            Math.pow(x - center, 2) + 
                            Math.pow(y - center, 2) + 
                            Math.pow(z - center, 2)
                        );
                        
                        const thickness = Math.max(1, size / 12);
                        if (distance >= radius - thickness && distance <= radius + thickness * 0.5) {
                            positions.push({
                                x: (x - center) * spacing,
                                y: (y - center) * spacing,
                                z: (z - center) * spacing,
                                gridX: x, gridY: y, gridZ: z
                            });
                        }
                    }
                }
            }
            return positions;
        }

        function generateDiamondPixels(size) {
            const positions = [];
            const center = (size - 1) / 2;
            
            for (let x = 0; x < size; x++) {
                for (let y = 0; y < size; y++) {
                    for (let z = 0; z < size; z++) {
                        const distance = Math.abs(x - center) + Math.abs(y - center) + Math.abs(z - center);
                        const targetDistance = center;
                        
                        if (Math.abs(distance - targetDistance) < 1.5) {
                            positions.push({
                                x: (x - center) * spacing,
                                y: (y - center) * spacing,
                                z: (z - center) * spacing,
                                gridX: x, gridY: y, gridZ: z
                            });
                        }
                    }
                }
            }
            return positions;
        }

        function generateTorusPixels(size) {
            const positions = [];
            const majorRadius = size * 0.35;
            const minorRadius = size * 0.15;
            const resolution = Math.max(24, size * 4);
            const tubeResolution = Math.max(12, size * 2);
            
            for (let i = 0; i < resolution; i++) {
                for (let j = 0; j < tubeResolution; j++) {
                    const u = (i / resolution) * Math.PI * 2;
                    const v = (j / tubeResolution) * Math.PI * 2;
                    
                    const x = (majorRadius + minorRadius * Math.cos(v)) * Math.cos(u);
                    const y = minorRadius * Math.sin(v);
                    const z = (majorRadius + minorRadius * Math.cos(v)) * Math.sin(u);
                    
                    positions.push({
                        x: x * spacing,
                        y: y * spacing,
                        z: z * spacing,
                        gridX: i, gridY: j, gridZ: 0
                    });
                }
            }
            return positions;
        }

        function generateHelixPixels(size) {
            const positions = [];
            const height = size * 1.2;
            const radius = size * 0.25;
            const turns = 3;
            const pointsPerTurn = Math.max(12, size * 3);
            const totalPoints = turns * pointsPerTurn;
            
            for (let i = 0; i < totalPoints; i++) {
                const t = i / totalPoints;
                const angle = t * turns * Math.PI * 2;
                const y = (t - 0.5) * height;
                
                const x = radius * Math.cos(angle);
                const z = radius * Math.sin(angle);
                
                positions.push({
                    x: x * spacing,
                    y: y * spacing,
                    z: z * spacing,
                    gridX: Math.floor(i / pointsPerTurn), gridY: i % pointsPerTurn, gridZ: 0
                });
            }
            return positions;
        }

        function generateTowerPixels(size) {
            const positions = [];
            const levels = size;
            
            for (let level = 0; level < levels; level++) {
                const levelSize = Math.max(2, size - Math.floor(level / 2));
                const yPos = level - (levels - 1) / 2;
                
                for (let x = 0; x < levelSize; x++) {
                    for (let z = 0; z < levelSize; z++) {
                        if ((x === 0 || x === levelSize - 1) || (z === 0 || z === levelSize - 1)) {
                            positions.push({
                                x: (x - (levelSize - 1) / 2) * spacing,
                                y: yPos * spacing,
                                z: (z - (levelSize - 1) / 2) * spacing,
                                gridX: x, gridY: level, gridZ: z, level: level
                            });
                        }
                    }
                }
            }
            return positions;
        }

        const shapeGenerators = {
            cube: generateCubePixels,
            pyramid: generatePyramidPixels,
            sphere: generateSpherePixels,
            diamond: generateDiamondPixels,
            torus: generateTorusPixels,
            helix: generateHelixPixels,
            tower: generateTowerPixels
        };

        function createShape(shapeType, density) {
            pixels.forEach(pixel => pixelGroup.remove(pixel));
            pixels = [];

            const pixelProps = calculatePixelProperties(density);
            pixelSize = pixelProps.pixelSize;
            spacing = pixelProps.spacing;

            const positions = shapeGenerators[shapeType](density);
            const pixelGeometry = new THREE.BoxGeometry(pixelSize, pixelSize, pixelSize);

            positions.forEach((pos, index) => {
                const pixelMaterial = new THREE.MeshBasicMaterial({
                    color: 0xffffff,
                    transparent: true,
                    opacity: 0.8
                });
                
                const pixel = new THREE.Mesh(pixelGeometry, pixelMaterial);
                pixel.position.set(pos.x, pos.y, pos.z);
                
                pixel.userData = {
                    originalPos: pixel.position.clone(),
                    phase: Math.random() * Math.PI * 2,
                    speed: 0.5 + Math.random() * 1.5,
                    gridPos: { x: pos.gridX || 0, y: pos.gridY || 0, z: pos.gridZ || 0 },
                    level: pos.level || 0,
                    index: index,
                    baseIntensity: 1
                };
                
                pixels.push(pixel);
                pixelGroup.add(pixel);
            });

            console.log(`Created ${currentShape} with ${pixels.length} pixels at resolution ${density}`);
        }

        // Enhanced light pattern functions with new effects
        const lightPatterns = {
            rainbow: (pixel, time) => {
                const userData = pixel.userData;
                const hue = (time * userData.speed + userData.phase + userData.index * 0.1) % (Math.PI * 2);
                const normalizedHue = hue / (Math.PI * 2);
                const [r, g, b] = hslToRgb(normalizedHue, 1, 0.6);
                pixel.material.color.setRGB(r/255, g/255, b/255);
                pixel.material.opacity = Math.sin(time * userData.speed * 2 + userData.phase) * 0.3 + 0.7;
            },
            
            pulse: (pixel, time) => {
                const pulse = Math.sin(time * 3) * 0.5 + 0.5;
                const colors = [
                    [255, 0, 100], [0, 255, 100], [100, 0, 255], [255, 255, 0]
                ];
                const colorIndex = Math.floor(time * 0.5) % colors.length;
                const [r, g, b] = colors[colorIndex];
                pixel.material.color.setRGB(r/255 * pulse, g/255 * pulse, b/255 * pulse);
                pixel.material.opacity = pulse;
            },
            
            spiral: (pixel, time) => {
                const userData = pixel.userData;
                const distance = Math.sqrt(userData.gridPos.x * userData.gridPos.x + userData.gridPos.z * userData.gridPos.z);
                const angle = Math.atan2(userData.gridPos.z, userData.gridPos.x);
                const wave = Math.sin(time * 2 + distance * 0.5 + angle * 2) * 0.5 + 0.5;
                const hue = (time + distance * 0.1 + angle * 0.3) % (Math.PI * 2);
                const [r, g, b] = hslToRgb(hue / (Math.PI * 2), 1, wave * 0.8);
                pixel.material.color.setRGB(r/255, g/255, b/255);
                pixel.material.opacity = wave;
            },
            
            wave: (pixel, time) => {
                const userData = pixel.userData;
                const wave = Math.sin(time * 2 + userData.gridPos.y * 0.8 + userData.gridPos.x * 0.3) * 0.5 + 0.5;
                const hue = (userData.gridPos.y * 0.1 + time * 0.5) % (Math.PI * 2);
                const [r, g, b] = hslToRgb(hue / (Math.PI * 2), 1, 0.7);
                pixel.material.color.setRGB(r/255 * wave, g/255 * wave, b/255 * wave);
                pixel.material.opacity = wave * 0.5 + 0.5;
            },
            
            strobe: (pixel, time) => {
                const strobe = Math.floor(time * 8) % 2;
                const colors = [[255, 255, 255], [255, 0, 0], [0, 255, 0], [0, 0, 255]];
                const colorIndex = Math.floor(time * 2) % colors.length;
                const [r, g, b] = colors[colorIndex];
                pixel.material.color.setRGB(r/255 * strobe, g/255 * strobe, b/255 * strobe);
                pixel.material.opacity = strobe * 0.8 + 0.2;
            },
            
            fire: (pixel, time) => {
                const userData = pixel.userData;
                const heat = Math.max(0, 1 - userData.gridPos.y * 0.2 + Math.sin(time * 3 + userData.phase) * 0.3);
                const flame = Math.sin(time * 4 + userData.phase + userData.gridPos.x * 0.5) * 0.3 + 0.7;
                const r = Math.min(1, heat * flame);
                const g = Math.min(1, heat * flame * 0.5);
                const b = Math.max(0, heat * flame * 0.1);
                pixel.material.color.setRGB(r, g, b);
                pixel.material.opacity = heat * flame;
                userData.baseIntensity = heat * flame;
            },
            
            matrix: (pixel, time) => {
                const userData = pixel.userData;
                const rain = Math.sin(time * 2 + userData.gridPos.x * 2 + userData.gridPos.z * 1.5 - userData.gridPos.y * 0.5) * 0.5 + 0.5;
                const intensity = rain > 0.7 ? 1 : rain * 0.3;
                pixel.material.color.setRGB(0, intensity, 0);
                pixel.material.opacity = intensity;
                userData.baseIntensity = intensity;
            },
            
            ripple: (pixel, time) => {
                const userData = pixel.userData;
                const center = { x: 0, y: 0, z: 0 };
                const distance = Math.sqrt(
                    Math.pow(userData.originalPos.x - center.x, 2) +
                    Math.pow(userData.originalPos.y - center.y, 2) +
                    Math.pow(userData.originalPos.z - center.z, 2)
                );
                const ripple = Math.sin(time * 3 - distance * 8) * 0.5 + 0.5;
                const hue = (distance * 2 + time * 0.5) % (Math.PI * 2);
                const [r, g, b] = hslToRgb(hue / (Math.PI * 2), 1, ripple * 0.8);
                pixel.material.color.setRGB(r/255, g/255, b/255);
                pixel.material.opacity = ripple * 0.7 + 0.3;
                userData.baseIntensity = ripple;
            },
            
            // NEW PATTERNS
            lightning: (pixel, time) => {
                const userData = pixel.userData;
                
                // Create lightning bolts occasionally
                if (time > nextLightningTime) {
                    nextLightningTime = time + 1 + Math.random() * 2;
                    createLightningBolt();
                }
                
                // Check if pixel is part of any lightning bolt
                let isLit = false;
                let lightningIntensity = 0;
                
                lightningBolts.forEach(bolt => {
                    if (bolt.endTime > time) {
                        bolt.path.forEach(point => {
                            const distance = Math.sqrt(
                                Math.pow(userData.originalPos.x - point.x, 2) +
                                Math.pow(userData.originalPos.y - point.y, 2) +
                                Math.pow(userData.originalPos.z - point.z, 2)
                            );
                            if (distance < 0.2) {
                                isLit = true;
                                lightningIntensity = Math.max(lightningIntensity, 1 - distance / 0.2);
                            }
                        });
                    }
                });
                
                if (isLit) {
                    const flicker = Math.random() * 0.3 + 0.7;
                    pixel.material.color.setRGB(1 * flicker, 1 * flicker, 1);
                    pixel.material.opacity = lightningIntensity * flicker;
                    userData.baseIntensity = lightningIntensity * 2;
                } else {
                    // Dim background
                    const base = Math.sin(time + userData.phase) * 0.1 + 0.1;
                    pixel.material.color.setRGB(0.2, 0.2, 0.4);
                    pixel.material.opacity = base;
                    userData.baseIntensity = base;
                }
            },
            
            heartbeat: (pixel, time) => {
                const userData = pixel.userData;
                const center = { x: 0, y: 0, z: 0 };
                const distance = Math.sqrt(
                    Math.pow(userData.originalPos.x - center.x, 2) +
                    Math.pow(userData.originalPos.y - center.y, 2) +
                    Math.pow(userData.originalPos.z - center.z, 2)
                );
                
                // Heartbeat pattern: lub-dub, lub-dub
                const beatTime = time * 2;
                const beatPhase = beatTime % 3;
                let pulse = 0;
                
                if (beatPhase < 0.2) {
                    pulse = Math.sin(beatPhase * Math.PI / 0.2);
                } else if (beatPhase > 0.4 && beatPhase < 0.6) {
                    pulse = Math.sin((beatPhase - 0.4) * Math.PI / 0.2) * 0.7;
                }
                
                const wave = Math.max(0, pulse - distance * 3 + 0.5);
                const intensity = wave * (1 - distance * 0.3);
                
                pixel.material.color.setRGB(1, intensity * 0.3, intensity * 0.3);
                pixel.material.opacity = Math.max(0.1, intensity);
                userData.baseIntensity = intensity;
            },
            
            aurora: (pixel, time) => {
                const userData = pixel.userData;
                const flow1 = Math.sin(time * 0.5 + userData.originalPos.x * 2 + userData.originalPos.z) * 0.5 + 0.5;
                const flow2 = Math.sin(time * 0.7 + userData.originalPos.y * 1.5 + userData.originalPos.z * 2) * 0.5 + 0.5;
                const flow3 = Math.sin(time * 0.3 + userData.originalPos.x * 1.2 + userData.originalPos.y * 0.8) * 0.5 + 0.5;
                
                const hue1 = (time * 0.1 + userData.originalPos.y * 0.5) % (Math.PI * 2);
                const hue2 = (time * 0.15 + userData.originalPos.x * 0.3) % (Math.PI * 2);
                
                const [r1, g1, b1] = hslToRgb(hue1 / (Math.PI * 2), 1, 0.6);
                const [r2, g2, b2] = hslToRgb(hue2 / (Math.PI * 2), 1, 0.6);
                
                const r = (r1 * flow1 + r2 * flow2) / 2;
                const g = (g1 * flow1 + g2 * flow2) / 2;
                const b = (b1 * flow1 + b2 * flow2) / 2;
                
                const intensity = (flow1 + flow2 + flow3) / 3;
                pixel.material.color.setRGB(r/255 * intensity, g/255 * intensity, b/255 * intensity);
                pixel.material.opacity = intensity * 0.8 + 0.2;
                userData.baseIntensity = intensity;
            },
            
            kaleidoscope: (pixel, time) => {
                const userData = pixel.userData;
                let pos = userData.originalPos.clone();
                
                if (symmetryEnabled) {
                    // Create kaleidoscope symmetry
                    pos.x = Math.abs(pos.x);
                    pos.z = Math.abs(pos.z);
                    
                    // Mirror diagonal
                    if (pos.x > pos.z) {
                        const temp = pos.x;
                        pos.x = pos.z;
                        pos.z = temp;
                    }
                }
                
                const angle = Math.atan2(pos.z, pos.x);
                const distance = Math.sqrt(pos.x * pos.x + pos.z * pos.z);
                const pattern = Math.sin(time + angle * 6 + distance * 4) * 0.5 + 0.5;
                const pattern2 = Math.sin(time * 1.3 + angle * 4 - distance * 3) * 0.5 + 0.5;
                
                const hue = (angle / (Math.PI * 2) + time * 0.1) % 1;
                const [r, g, b] = hslToRgb(hue, 1, pattern * 0.8);
                
                pixel.material.color.setRGB(r/255, g/255, b/255);
                pixel.material.opacity = (pattern + pattern2) / 2 * 0.8 + 0.2;
                userData.baseIntensity = (pattern + pattern2) / 2;
            }
        };

        function createLightningBolt() {
            const start = new THREE.Vector3(
                (Math.random() - 0.5) * 2,
                (Math.random() - 0.5) * 2,
                (Math.random() - 0.5) * 2
            );
            
            const end = new THREE.Vector3(
                (Math.random() - 0.5) * 2,
                (Math.random() - 0.5) * 2,
                (Math.random() - 0.5) * 2
            );
            
            const path = [];
            const segments = 10;
            
            for (let i = 0; i <= segments; i++) {
                const t = i / segments;
                const point = start.clone().lerp(end, t);
                
                // Add jaggedness
                if (i > 0 && i < segments) {
                    point.add(new THREE.Vector3(
                        (Math.random() - 0.5) * 0.3,
                        (Math.random() - 0.5) * 0.3,
                        (Math.random() - 0.5) * 0.3
                    ));
                }
                
                path.push(point);
            }
            
            lightningBolts.push({
                path: path,
                endTime: Date.now() / 1000 + 0.5
            });
            
            // Clean up old bolts
            lightningBolts = lightningBolts.filter(bolt => bolt.endTime > Date.now() / 1000);
        }

        // Apply glow effect
        function applyGlowEffect(pixel) {
            if (!glowEnabled) return;
            
            const intensity = pixel.userData.baseIntensity || 1;
            if (intensity > 0.7) {
                // Scale up bright pixels
                const scale = 1 + (intensity - 0.7) * 0.5;
                pixel.scale.setScalar(scale);
            } else {
                pixel.scale.setScalar(1);
            }
        }

        // Utility functions
        function hslToRgb(h, s, l) {
            let r, g, b;
            if (s === 0) {
                r = g = b = l;
            } else {
                const hue2rgb = (p, q, t) => {
                    if (t < 0) t += 1;
                    if (t > 1) t -= 1;
                    if (t < 1/6) return p + (q - p) * 6 * t;
                    if (t < 1/2) return q;
                    if (t < 2/3) return p + (q - p) * (2/3 - t) * 6;
                    return p;
                };
                const q = l < 0.5 ? l * (1 + s) : l + s - l * s;
                const p = 2 * l - q;
                r = hue2rgb(p, q, h + 1/3);
                g = hue2rgb(p, q, h);
                b = hue2rgb(p, q, h - 1/3);
            }
            return [Math.round(r * 255), Math.round(g * 255), Math.round(b * 255)];
        }

        function randomizePattern() {
            const patterns = Object.keys(lightPatterns);
            const randomPattern = patterns[Math.floor(Math.random() * patterns.length)];
            document.getElementById('patternSelect').value = randomPattern;
            currentPattern = randomPattern;
        }

        // Mouse controls (same as before)
        let mouseDown = false;
        let mouseX = 0;
        let mouseY = 0;
        let targetRotationX = 0;
        let targetRotationY = 0;
        let currentRotationX = 0;
        let currentRotationY = 0;

        // Event listeners
        renderer.domElement.addEventListener('mousedown', onMouseDown, false);
        renderer.domElement.addEventListener('mousemove', onMouseMove, false);
        renderer.domElement.addEventListener('mouseup', onMouseUp, false);
        renderer.domElement.addEventListener('wheel', onMouseWheel, false);
        renderer.domElement.addEventListener('touchstart', onTouchStart, false);
        renderer.domElement.addEventListener('touchmove', onTouchMove, false);
        renderer.domElement.addEventListener('touchend', onTouchEnd, false);

        document.getElementById('shapeSelect').addEventListener('change', (e) => {
            currentShape = e.target.value;
            createShape(currentShape, currentDensity);
        });

        document.getElementById('patternSelect').addEventListener('change', (e) => {
            currentPattern = e.target.value;
        });

        document.getElementById('backgroundSelect').addEventListener('change', (e) => {
            currentBackground = e.target.value;
            createBackground(currentBackground);
        });

        document.getElementById('glowEffect').addEventListener('change', (e) => {
            glowEnabled = e.target.checked;
        });

        document.getElementById('symmetryMode').addEventListener('change', (e) => {
            symmetryEnabled = e.target.checked;
        });

        document.getElementById('densitySlider').addEventListener('input', (e) => {
            currentDensity = parseInt(e.target.value);
            document.getElementById('densityValue').textContent = currentDensity;
            createShape(currentShape, currentDensity);
        });

        document.getElementById('pauseButton').addEventListener('click', () => {
            isPaused = !isPaused;
            const button = document.getElementById('pauseButton');
            if (isPaused) {
                button.textContent = '▶️ Play';
                button.classList.add('paused');
            } else {
                button.textContent = '⏸️ Pause';
                button.classList.remove('paused');
            }
        });

        function onMouseDown(event) {
            mouseDown = true;
            mouseX = event.clientX;
            mouseY = event.clientY;
        }

        function onMouseMove(event) {
            if (!mouseDown) return;
            const deltaX = event.clientX - mouseX;
            const deltaY = event.clientY - mouseY;
            targetRotationY += deltaX * 0.01;
            targetRotationX += deltaY * 0.01;
            mouseX = event.clientX;
            mouseY = event.clientY;
        }

        function onMouseUp() {
            mouseDown = false;
        }

        function onMouseWheel(event) {
            camera.position.z += event.deltaY * 0.001;
            camera.position.z = Math.max(1, Math.min(10, camera.position.z));
        }

        function onTouchStart(event) {
            if (event.touches.length === 1) {
                mouseDown = true;
                mouseX = event.touches[0].clientX;
                mouseY = event.touches[0].clientY;
            }
        }

        function onTouchMove(event) {
            if (event.touches.length === 1 && mouseDown) {
                event.preventDefault();
                const deltaX = event.touches[0].clientX - mouseX;
                const deltaY = event.touches[0].clientY - mouseY;
                targetRotationY += deltaX * 0.01;
                targetRotationX += deltaY * 0.01;
                mouseX = event.touches[0].clientX;
                mouseY = event.touches[0].clientY;
            }
        }

        function onTouchEnd() {
            mouseDown = false;
        }

        // Animation loop
        let time = 0;
        function animate() {
            requestAnimationFrame(animate);
            
            if (!isPaused) {
                time += 0.01;
                
                // Smooth rotation
                currentRotationX += (targetRotationX - currentRotationX) * 0.05;
                currentRotationY += (targetRotationY - currentRotationY) * 0.05;
                pixelGroup.rotation.x = currentRotationX;
                pixelGroup.rotation.y = currentRotationY;

                // Auto-rotation when not controlled
                if (!mouseDown) {
                    targetRotationY += 0.003;
                }

                // Apply current light pattern
                if (lightPatterns[currentPattern]) {
                    pixels.forEach(pixel => {
                        lightPatterns[currentPattern](pixel, time);
                        
                        // Apply glow effect
                        applyGlowEffect(pixel);
                        
                        // Add subtle position animation
                        const wobble = Math.sin(time * pixel.userData.speed + pixel.userData.phase) * 0.01;
                        pixel.position.copy(pixel.userData.originalPos);
                        pixel.position.multiplyScalar(1 + wobble);
                    });
                }
            }

            renderer.render(scene, camera);
        }

        // Window resize handler
        window.addEventListener('resize', () => {
            camera.aspect = window.innerWidth / window.innerHeight;
            camera.updateProjectionMatrix();
            renderer.setSize(window.innerWidth, window.innerHeight);
        });

        // Initialize
        createBackground(currentBackground);
        createShape(currentShape, currentDensity);
        animate();
    </script>
</body>
</html>