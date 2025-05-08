<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>3D Planet Generator - Three.js with Presets</title>
    <style>
        body {
            margin: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
            background-color: #000000; /* Black space background */
            color: #e0e0e0;
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 100vh;
            overflow: hidden; /* Prevent scrollbars */
            padding-bottom: 20px; /* Space for controls */
            box-sizing: border-box;
        }

        #planetCanvasContainer {
            width: 100%; 
            max-width: 800px; 
            height: 55vh; /* Slightly less to ensure controls fit */
            margin-bottom: 15px;
            touch-action: none; 
        }

        .controls {
            background-color: rgba(20, 20, 30, 0.9);
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.5);
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); /* Adjusted minmax */
            gap: 12px;
            max-width: 800px;
            width: 90%;
            z-index: 10; 
        }

        .control-group {
            display: flex;
            flex-direction: column;
        }

        .control-group label {
            margin-bottom: 4px;
            font-size: 0.85em;
        }

        .control-group input[type="range"],
        .control-group input[type="color"],
        .control-group button,
        .control-group select {
            padding: 7px;
            border-radius: 4px;
            border: 1px solid #303050;
            background-color: #1a1a2e;
            color: #e0e0e0;
            font-size: 0.9em;
        }
        .control-group input[type="color"] {
            padding: 2px; 
            height: 30px;
        }
        .control-group button, .control-group select {
            cursor: pointer;
            background-color: #3a3a6a;
            transition: background-color 0.2s;
        }
        .control-group button:hover, .control-group select:hover {
            background-color: #5a5a90;
        }
    </style>
</head>
<body>

    <div id="planetCanvasContainer"></div>

    <div class="controls">
        <div class="control-group">
            <label for="planetPreset">Planet Type Preset:</label>
            <select id="planetPreset">
                <option value="custom">Custom (Use Controls)</option>
                <!-- Options will be populated by JS -->
            </select>
        </div>
        <div class="control-group">
            <label for="planetSize">Planet Size (Radius):</label>
            <input type="range" id="planetSize" min="0.5" max="3" value="1.5" step="0.1">
        </div>
        <div class="control-group">
            <label for="baseColor">Base Color:</label>
            <input type="color" id="baseColor" value="#3A6EA5">
        </div>
        <div class="control-group">
            <label for="featureColor1">Feature Color 1:</label>
            <input type="color" id="featureColor1" value="#89B0AE">
        </div>
        <div class="control-group">
            <label for="featureColor2">Feature Color 2:</label>
            <input type="color" id="featureColor2" value="#FAF3DD">
        </div>
        <div class="control-group">
            <label for="atmosphereColor">Atmosphere Color:</label>
            <input type="color" id="atmosphereColor" value="#BEE9E8">
        </div>
        <div class="control-group">
            <label for="atmosphereDensity">Atmosphere Density:</label>
            <input type="range" id="atmosphereDensity" min="0" max="1" value="0.3" step="0.01">
        </div>
        <div class="control-group">
            <label for="rotationSpeed">Auto-Rotation Speed:</label>
            <input type="range" id="rotationSpeed" min="0" max="100" value="10">
        </div>
        <div class="control-group" style="grid-column: 1 / -1;">
            <button id="randomizePlanet">Generate Random Planet</button>
        </div>
    </div>

    <script type="importmap">
        {
            "imports": {
                "three": "https://unpkg.com/three@0.157.0/build/three.module.js",
                "three/addons/": "https://unpkg.com/three@0.157.0/examples/jsm/"
            }
        }
    </script>
    <script type="module">
        import * as THREE from 'three';
        import { OrbitControls } from 'three/addons/controls/OrbitControls.js';

        let scene, camera, renderer, planet, atmosphereMesh, stars;
        let planetTextureCanvas, planetTextureCtx, planetTexture;
        let orbitControls;

        const canvasContainer = document.getElementById('planetCanvasContainer');

        const planetPresetSelect = document.getElementById('planetPreset');
        const planetSizeInput = document.getElementById('planetSize');
        const baseColorInput = document.getElementById('baseColor');
        const featureColor1Input = document.getElementById('featureColor1');
        const featureColor2Input = document.getElementById('featureColor2');
        const atmosphereColorInput = document.getElementById('atmosphereColor');
        const atmosphereDensityInput = document.getElementById('atmosphereDensity');
        const rotationSpeedInput = document.getElementById('rotationSpeed');
        const randomizeButton = document.getElementById('randomizePlanet');

        let currentPlanetType = 'custom';

        const planetPresets = {
            "Earth-like": {
                size: 1.5, baseColor: '#2E5A88', featureColor1: '#5A8B4C', featureColor2: '#FFFFFF',
                atmosphereColor: '#A0C0E0', atmosphereDensity: 0.35, rotationSpeed: 10, textureStyle: 'terran'
            },
            "Moon": {
                size: 0.8, baseColor: '#808080', featureColor1: '#505050', featureColor2: '#A0A0A0',
                atmosphereColor: '#C0C0C0', atmosphereDensity: 0.02, rotationSpeed: 5, textureStyle: 'lunar'
            },
            "Ice Planet": {
                size: 1.3, baseColor: '#E0F0FF', featureColor1: '#A0D0F0', featureColor2: '#FFFFFF',
                atmosphereColor: '#D0E8FF', atmosphereDensity: 0.2, rotationSpeed: 8, textureStyle: 'icy'
            },
            "Gas Giant": {
                size: 2.5, baseColor: '#D8A078', featureColor1: '#F0E0B0', featureColor2: '#A07050',
                atmosphereColor: '#E0C0A0', atmosphereDensity: 0.5, rotationSpeed: 25, textureStyle: 'gas_giant'
            },
            "Lava Planet": {
                size: 1.2, baseColor: '#301008', featureColor1: '#FF4500', featureColor2: '#FFD700',
                atmosphereColor: '#FF6347', atmosphereDensity: 0.15, rotationSpeed: 12, textureStyle: 'lava'
            },
            "Desert Planet": {
                size: 1.4, baseColor: '#D2B48C', featureColor1: '#8B4513', featureColor2: '#F0E68C',
                atmosphereColor: '#F4A460', atmosphereDensity: 0.1, rotationSpeed: 9, textureStyle: 'desert'
            }
        };

        let currentPlanetParameters = {
            size: 1.5, baseColor: '#3A6EA5', featureColor1: '#89B0AE', featureColor2: '#FAF3DD',
            atmosphereColor: '#BEE9E8', atmosphereDensity: 0.3, rotationSpeed: 0.001, textureStyle: 'default_procedural'
        };

        function init() {
            scene = new THREE.Scene();
            const aspect = canvasContainer.clientWidth / canvasContainer.clientHeight;
            camera = new THREE.PerspectiveCamera(75, aspect, 0.1, 1000);
            camera.position.z = 4;

            renderer = new THREE.WebGLRenderer({ antialias: true, alpha: false }); // Opaque background for performance
            renderer.setSize(canvasContainer.clientWidth, canvasContainer.clientHeight);
            renderer.setPixelRatio(window.devicePixelRatio);
            canvasContainer.appendChild(renderer.domElement);

            orbitControls = new OrbitControls(camera, renderer.domElement);
            orbitControls.enableDamping = true;
            orbitControls.dampingFactor = 0.05;
            orbitControls.minDistance = 2;
            orbitControls.maxDistance = 20;

            planetTextureCanvas = document.createElement('canvas');
            planetTextureCanvas.width = 1024;
            planetTextureCanvas.height = 512;
            planetTextureCtx = planetTextureCanvas.getContext('2d');
            planetTexture = new THREE.CanvasTexture(planetTextureCanvas);
            planetTexture.wrapS = THREE.RepeatWrapping;
            planetTexture.wrapT = THREE.ClampToEdgeWrapping;
            planetTexture.colorSpace = THREE.SRGBColorSpace; // Important for correct color

            const planetGeometry = new THREE.SphereGeometry(1, 64, 32);
            const planetMaterial = new THREE.MeshStandardMaterial({
                map: planetTexture, roughness: 0.7, metalness: 0.1,
            });
            planet = new THREE.Mesh(planetGeometry, planetMaterial);
            scene.add(planet);

            const atmosphereGeometry = new THREE.SphereGeometry(1.05, 64, 32);
            const atmosphereMaterial = new THREE.MeshStandardMaterial({
                transparent: true, side: THREE.BackSide, blending: THREE.AdditiveBlending, depthWrite: false
            });
            atmosphereMesh = new THREE.Mesh(atmosphereGeometry, atmosphereMaterial);
            planet.add(atmosphereMesh);

            const ambientLight = new THREE.AmbientLight(0xffffff, 0.5);
            scene.add(ambientLight);
            const directionalLight = new THREE.DirectionalLight(0xffffff, 1.8);
            directionalLight.position.set(5, 3, 5);
            scene.add(directionalLight);

            const starVertices = [];
            for (let i = 0; i < 10000; i++) {
                starVertices.push(THREE.MathUtils.randFloatSpread(200));
                starVertices.push(THREE.MathUtils.randFloatSpread(200));
                starVertices.push(THREE.MathUtils.randFloatSpread(200));
            }
            const starGeometry = new THREE.BufferGeometry();
            starGeometry.setAttribute('position', new THREE.Float32BufferAttribute(starVertices, 3));
            const starMaterial = new THREE.PointsMaterial({ color: 0xffffff, size: 0.05, sizeAttenuation: true });
            stars = new THREE.Points(starGeometry, starMaterial);
            scene.add(stars);

            for (const presetName in planetPresets) {
                const option = document.createElement('option');
                option.value = presetName;
                option.textContent = presetName;
                planetPresetSelect.appendChild(option);
            }
            planetPresetSelect.addEventListener('change', onPresetChange);

            [planetSizeInput, baseColorInput, featureColor1Input, featureColor2Input, atmosphereColorInput, atmosphereDensityInput, rotationSpeedInput].forEach(input => {
                input.addEventListener('input', () => {
                    if (planetPresetSelect.value !== 'custom') {
                        planetPresetSelect.value = 'custom';
                        currentPlanetType = 'custom';
                        currentPlanetParameters.textureStyle = 'default_procedural';
                    }
                    readCurrentControlValues();
                    updatePlanetSystems();
                });
            });

            randomizeButton.addEventListener('click', randomizePlanetValues);
            window.addEventListener('resize', onWindowResize, false);

            readInitialControlValues();
            updatePlanetSystems();
            animate();
        }

        function readCurrentControlValues() {
            currentPlanetParameters.size = parseFloat(planetSizeInput.value);
            currentPlanetParameters.baseColor = baseColorInput.value;
            currentPlanetParameters.featureColor1 = featureColor1Input.value;
            currentPlanetParameters.featureColor2 = featureColor2Input.value;
            currentPlanetParameters.atmosphereColor = atmosphereColorInput.value;
            currentPlanetParameters.atmosphereDensity = parseFloat(atmosphereDensityInput.value);
            currentPlanetParameters.rotationSpeed = parseFloat(rotationSpeedInput.value) / 10000;
            // currentPlanetParameters.textureStyle is set if 'custom' is chosen or preset changes
        }
        
        function readInitialControlValues() {
            currentPlanetParameters.size = parseFloat(planetSizeInput.value);
            currentPlanetParameters.baseColor = baseColorInput.value;
            currentPlanetParameters.featureColor1 = featureColor1Input.value;
            currentPlanetParameters.featureColor2 = featureColor2Input.value;
            currentPlanetParameters.atmosphereColor = atmosphereColorInput.value;
            currentPlanetParameters.atmosphereDensity = parseFloat(atmosphereDensityInput.value);
            currentPlanetParameters.rotationSpeed = parseFloat(rotationSpeedInput.value) / 10000;

            const selectedPresetName = planetPresetSelect.value;
            if (selectedPresetName !== 'custom' && planetPresets[selectedPresetName]) {
                currentPlanetType = selectedPresetName;
                currentPlanetParameters.textureStyle = planetPresets[selectedPresetName].textureStyle;
                 // Apply initial preset values if one is somehow selected by default (less common)
                const preset = planetPresets[selectedPresetName];
                Object.assign(currentPlanetParameters, preset); // shallow copy preset values
                currentPlanetParameters.rotationSpeed = preset.rotationSpeed / 10000; // Rescale
            } else {
                currentPlanetType = 'custom';
                planetPresetSelect.value = 'custom';
                currentPlanetParameters.textureStyle = 'default_procedural';
            }
        }


        function updatePlanetSystems() {
            updatePlanetMesh();
            updateAtmosphereMesh();
            generateAndApplyPlanetTexture();
        }

        function onPresetChange(e) {
            const selectedPresetName = e.target.value;
            currentPlanetType = selectedPresetName;

            if (selectedPresetName === 'custom') {
                currentPlanetParameters.textureStyle = 'default_procedural';
                 // When 'custom' is selected from dropdown, values remain as they are from sliders
                readCurrentControlValues(); // ensure current params are from sliders
                updatePlanetSystems();
                return;
            }

            const preset = planetPresets[selectedPresetName];
            if (preset) {
                planetSizeInput.value = preset.size;
                baseColorInput.value = preset.baseColor;
                featureColor1Input.value = preset.featureColor1;
                featureColor2Input.value = preset.featureColor2;
                atmosphereColorInput.value = preset.atmosphereColor;
                atmosphereDensityInput.value = preset.atmosphereDensity;
                rotationSpeedInput.value = preset.rotationSpeed;

                Object.assign(currentPlanetParameters, preset); // This is a shallow copy
                currentPlanetParameters.rotationSpeed = preset.rotationSpeed / 10000; // Rescale for internal use
                // textureStyle is already part of preset: currentPlanetParameters.textureStyle = preset.textureStyle;
                updatePlanetSystems();
            }
        }

        function updatePlanetMesh() {
            if (!planet) return;
            planet.scale.set(currentPlanetParameters.size, currentPlanetParameters.size, currentPlanetParameters.size);
        }

        function updateAtmosphereMesh() {
            if (!atmosphereMesh) return;
            atmosphereMesh.material.color.set(currentPlanetParameters.atmosphereColor);
            atmosphereMesh.material.opacity = currentPlanetParameters.atmosphereDensity;
            atmosphereMesh.material.needsUpdate = true;
        }
        
        function generateAndApplyPlanetTexture() {
            if (!planetTextureCtx || !planetTexture || !planet) return;
            const W = planetTextureCanvas.width;
            const H = planetTextureCanvas.height;
            planetTextureCtx.fillStyle = currentPlanetParameters.baseColor;
            planetTextureCtx.fillRect(0, 0, W, H);

            const style = currentPlanetParameters.textureStyle || 'default_procedural';

            switch (style) {
                case 'terran': generateTerranTexture(planetTextureCtx, W, H, currentPlanetParameters); break;
                case 'lunar': generateLunarTexture(planetTextureCtx, W, H, currentPlanetParameters); break;
                case 'icy': generateIcyTexture(planetTextureCtx, W, H, currentPlanetParameters); break;
                case 'gas_giant': generateGasGiantTexture(planetTextureCtx, W, H, currentPlanetParameters); break;
                case 'lava': generateLavaTexture(planetTextureCtx, W, H, currentPlanetParameters); break;
                case 'desert': generateDesertTexture(planetTextureCtx, W, H, currentPlanetParameters); break;
                default: generateDefaultProceduralTexture(planetTextureCtx, W, H, currentPlanetParameters); break;
            }
            planetTexture.needsUpdate = true;
            if (planet.material.map) planet.material.map.needsUpdate = true; // ensure map itself knows it's updated
            planet.material.needsUpdate = true;
        }

        // --- Specific Texture Generation Functions ---
        function generateDefaultProceduralTexture(ctx, W, H, params) {
            ctx.fillStyle = params.baseColor;
            ctx.fillRect(0, 0, W, H);
            const numFeatures = 20 + Math.random() * 25;
            for (let i = 0; i < numFeatures; i++) {
                const x = Math.random() * W; const y = Math.random() * H;
                const radius = (Math.random() * W / 12) + (W / 35);
                let featureColor = Math.random() > 0.5 ? params.featureColor1 : params.featureColor2;
                if (Math.random() > 0.9) featureColor = params.baseColor;
                const grad = ctx.createRadialGradient(x, y, radius * 0.15, x, y, radius);
                grad.addColorStop(0, hexToRgba(featureColor, Math.random() * 0.3 + 0.6));
                grad.addColorStop(0.7, hexToRgba(featureColor, Math.random() * 0.2 + 0.2));
                grad.addColorStop(1, hexToRgba(featureColor, 0));
                ctx.fillStyle = grad; ctx.beginPath(); ctx.arc(x, y, radius, 0, Math.PI * 2); ctx.fill();
            }
            const numStreaks = 8 + Math.random() * 10;
            ctx.globalAlpha = 0.15 + Math.random() * 0.25;
            for (let i = 0; i < numStreaks; i++) {
                const yPos = Math.random() * H; const height = Math.random() * (H / 25) + (H / 55);
                ctx.fillStyle = hexToRgba(params.featureColor2, 0.4 + Math.random() * 0.3);
                ctx.fillRect(0, yPos, W, height);
                ctx.beginPath(); ctx.moveTo(0, yPos + Math.sin(0) * height * 0.4);
                for(let j=0; j < W; j+=25) {
                    ctx.lineTo(j, yPos + Math.sin(j * 0.015 + i*0.6) * height * (0.4 + Math.random()*0.4) );
                }
                ctx.lineTo(W, yPos + Math.sin(W * 0.015 + i*0.6) * height * 0.4);
                ctx.lineTo(W, yPos - height*0.15); ctx.lineTo(0, yPos - height*0.15); ctx.closePath();
                ctx.fillStyle = hexToRgba(params.featureColor2, 0.15 + Math.random() * 0.15); ctx.fill();
            }
            ctx.globalAlpha = 1.0;
        }

        function generateTerranTexture(ctx, W, H, params) {
            ctx.fillStyle = params.baseColor; ctx.fillRect(0, 0, W, H);
            const numContinents = 4 + Math.floor(Math.random() * 4);
            for (let i = 0; i < numContinents; i++) {
                let x = Math.random() * W; let y = Math.random() * H;
                let continentSize = W / (2.5 + Math.random() * 2.5);
                for (let j = 0; j < 8 + Math.random() * 12; j++) {
                    const offsetX = (Math.random() - 0.5) * continentSize * 0.7;
                    const offsetY = (Math.random() - 0.5) * continentSize * 0.45;
                    const radius = (Math.random() * continentSize / 3.5) + (continentSize / 5.5);
                    const grad = ctx.createRadialGradient(x + offsetX, y + offsetY, radius * 0.25, x + offsetX, y + offsetY, radius);
                    grad.addColorStop(0, hexToRgba(params.featureColor1, 0.95));
                    grad.addColorStop(0.75, hexToRgba(params.featureColor1, 0.75));
                    grad.addColorStop(1, hexToRgba(params.featureColor1, 0));
                    ctx.fillStyle = grad; ctx.beginPath(); ctx.arc(x + offsetX, y + offsetY, radius, 0, Math.PI * 2); ctx.fill();
                }
            }
            ctx.globalAlpha = 0.65;
            const numCloudPatches = 18 + Math.random() * 18;
            for (let i = 0; i < numCloudPatches; i++) {
                const x = Math.random() * W; const y = Math.random() * H;
                const length = W / (4 + Math.random() * 4);
                const thickness = H / (18 + Math.random() * 8);
                ctx.beginPath(); ctx.moveTo(x, y); const segments = 12;
                for (let k = 0; k <= segments; k++) {
                    const t = k / segments;
                    const currentX = x + Math.sin(t * Math.PI * (1+Math.random()*1.5)) * length * t  + (Math.random()-0.5)*length*0.15;
                    const currentY = y + Math.cos(t * Math.PI + (Math.random()-0.5)*1.5) * thickness * (1-t) + (Math.random()-0.5)*thickness*1.5;
                    ctx.lineTo(currentX, currentY);
                }
                ctx.lineWidth = thickness * (0.4 + Math.random()*0.8);
                ctx.strokeStyle = hexToRgba(params.featureColor2, 0.25 + Math.random() * 0.35); ctx.stroke();
            }
            ctx.globalAlpha = 1.0;
        }

        function generateLunarTexture(ctx, W, H, params) {
            ctx.fillStyle = params.baseColor; ctx.fillRect(0, 0, W, H);
            const numCraters = 80 + Math.floor(Math.random() * 80);
            for (let i = 0; i < numCraters; i++) {
                const x = Math.random() * W; const y = Math.random() * H;
                const radius = (Math.random() * W / 30) + (W / 120);
                ctx.fillStyle = hexToRgba(params.featureColor1, 0.5 + Math.random() * 0.25);
                ctx.beginPath(); ctx.arc(x, y, radius, 0, Math.PI * 2); ctx.fill();
                const angle = Math.random() * Math.PI * 2;
                const rimX = x + Math.cos(angle) * radius * 0.6; const rimY = y + Math.sin(angle) * radius * 0.6;
                const rimGrad = ctx.createRadialGradient(rimX, rimY, radius * 0.05, rimX, rimY, radius * 0.4);
                rimGrad.addColorStop(0, hexToRgba(params.featureColor2, 0.45));
                rimGrad.addColorStop(1, hexToRgba(params.featureColor2, 0));
                ctx.fillStyle = rimGrad; ctx.beginPath(); ctx.arc(rimX, rimY, radius * 0.5, 0, Math.PI * 2); ctx.fill();
            }
        }

        function generateIcyTexture(ctx, W, H, params) {
            ctx.fillStyle = params.baseColor; ctx.fillRect(0, 0, W, H);
            const numPatches = 4 + Math.floor(Math.random() * 4);
            for (let i = 0; i < numPatches; i++) {
                const x = Math.random() * W; const y = Math.random() * H;
                const radius = (Math.random() * W / 3.5) + (W / 5.5);
                const grad = ctx.createRadialGradient(x, y, radius * 0.35, x, y, radius);
                grad.addColorStop(0, hexToRgba(params.featureColor1, 0.08 + Math.random()*0.08));
                grad.addColorStop(1, hexToRgba(params.featureColor1, 0));
                ctx.fillStyle = grad; ctx.beginPath(); ctx.arc(x, y, radius, 0, Math.PI * 2); ctx.fill();
            }
            ctx.strokeStyle = hexToRgba(params.featureColor1, 0.25 + Math.random()*0.15);
            ctx.lineWidth = 0.8 + Math.random() * (H/250);
            const numCracks = 8 + Math.floor(Math.random() * 12);
            for (let i = 0; i < numCracks; i++) {
                let x = Math.random() * W; let y = Math.random() * H;
                ctx.beginPath(); ctx.moveTo(x, y);
                const segments = 2 + Math.floor(Math.random() * 4);
                for (let j = 0; j < segments; j++) {
                    x += (Math.random() - 0.5) * (W / 5.5); y += (Math.random() - 0.5) * (H / 3.5);
                    ctx.lineTo(x, y);
                }
                ctx.stroke();
            }
            ctx.globalAlpha = 0.45;
            const numFrost = 25 + Math.random() * 25;
            for (let i = 0; i < numFrost; i++) {
                const x = Math.random() * W; const y = Math.random() * H;
                const radius = (Math.random() * W / 35) + (W / 85);
                const grad = ctx.createRadialGradient(x, y, 0, x, y, radius);
                grad.addColorStop(0, hexToRgba(params.featureColor2, 0.55));
                grad.addColorStop(1, hexToRgba(params.featureColor2, 0));
                ctx.fillStyle = grad; ctx.beginPath(); ctx.arc(x, y, radius, 0, Math.PI * 2); ctx.fill();
            }
            ctx.globalAlpha = 1.0;
        }

        function generateGasGiantTexture(ctx, W, H, params) {
            ctx.fillStyle = params.baseColor; ctx.fillRect(0, 0, W, H);
            const numBands = 6 + Math.floor(Math.random() * 7);
            let currentY = 0;
            for (let i = 0; i < numBands; i++) {
                const bandHeight = (H / numBands) * (0.6 + Math.random()*0.8);
                const bandColor = (i % 2 === 0) ? params.featureColor1 : params.featureColor2;
                const prevBandColor = (i===0) ? params.baseColor : ( (i-1) % 2 === 0 ? params.featureColor1 : params.featureColor2);
                const grad = ctx.createLinearGradient(0, currentY, 0, currentY + bandHeight);
                grad.addColorStop(0, hexToRgba(prevBandColor, 0.6 + Math.random()*0.25));
                grad.addColorStop(0.5, hexToRgba(bandColor, 0.7 + Math.random()*0.15));
                grad.addColorStop(1, hexToRgba(bandColor, 0.6 + Math.random()*0.25));
                ctx.fillStyle = grad; ctx.fillRect(0, currentY, W, bandHeight);
                currentY += bandHeight;
            }
            if (currentY < H) {
                ctx.fillStyle = (numBands % 2 === 0) ? params.featureColor1 : params.featureColor2;
                ctx.fillRect(0, currentY, W, H - currentY);
            }
            const numStorms = 1 + Math.floor(Math.random() * 2);
            for (let i = 0; i < numStorms; i++) {
                const x = Math.random() * W; const y = Math.random() * H;
                const radiusX = (W / 9) * (0.6 + Math.random()); const radiusY = (H / 14) * (0.6 + Math.random());
                const stormColor = Math.random() > 0.5 ? darkenColor(params.featureColor1, 0.15) : darkenColor(params.featureColor2, 0.25);
                const grad = ctx.createRadialGradient(x, y, 0, x, y, Math.max(radiusX, radiusY));
                grad.addColorStop(0, hexToRgba(lightenColor(stormColor, 0.25), 0.95));
                grad.addColorStop(0.55, hexToRgba(stormColor, 0.85));
                grad.addColorStop(1, hexToRgba(darkenColor(stormColor,0.15), 0.35));
                ctx.fillStyle = grad; ctx.beginPath();
                ctx.ellipse(x, y, radiusX, radiusY, Math.random() * Math.PI, 0, Math.PI * 2); ctx.fill();
                ctx.strokeStyle = hexToRgba(lightenColor(stormColor, 0.4), 0.35);
                ctx.lineWidth = 1.5 + Math.random()*1.5;
                for(let s=0; s<2; s++) {
                    ctx.beginPath();
                    ctx.arc(x,y, Math.max(radiusX,radiusY) * (0.25 + s*0.2 + Math.random()*0.08), Math.random()*Math.PI*2, Math.random()*Math.PI*2 + Math.PI*1.5);
                    ctx.stroke();
                }
            }
        }
        
        function generateLavaTexture(ctx, W, H, params) {
            ctx.fillStyle = params.baseColor; ctx.fillRect(0, 0, W, H);
            const numLavaFeatures = 12 + Math.floor(Math.random() * 15);
            for (let i = 0; i < numLavaFeatures; i++) {
                let x = Math.random() * W; let y = Math.random() * H;
                const type = Math.random();
                if (type < 0.65) { // Rivers
                    ctx.beginPath(); ctx.moveTo(x, y);
                    const segments = 4 + Math.floor(Math.random() * 8);
                    const riverWidth = (W / 120) * (1 + Math.random() * 2.5);
                    for (let j = 0; j < segments; j++) {
                        x += (Math.random() - 0.5) * (W / 9); y += (Math.random() - 0.5) * (H / 5.5);
                        x = Math.max(0, Math.min(W, x)); y = Math.max(0, Math.min(H, y)); ctx.lineTo(x, y);
                    }
                    const grad = ctx.createLinearGradient(x-riverWidth, y, x+riverWidth, y);
                    grad.addColorStop(0, hexToRgba(params.featureColor2, 0.65));
                    grad.addColorStop(0.5, hexToRgba(params.featureColor1, 0.9));
                    grad.addColorStop(1, hexToRgba(params.featureColor2, 0.65));
                    ctx.strokeStyle = grad; ctx.lineWidth = riverWidth; ctx.lineCap = 'round'; ctx.stroke();
                } else { // Pools
                    const radius = (Math.random() * W / 18) + (W / 55);
                    const grad = ctx.createRadialGradient(x, y, radius * 0.25, x, y, radius);
                    grad.addColorStop(0, hexToRgba(params.featureColor2, 0.9));
                    grad.addColorStop(0.65, hexToRgba(params.featureColor1, 0.85));
                    grad.addColorStop(1, hexToRgba(darkenColor(params.baseColor, -0.15), 0.55));
                    ctx.fillStyle = grad; ctx.beginPath(); ctx.arc(x, y, radius, 0, Math.PI * 2); ctx.fill();
                }
            }
        }

        function generateDesertTexture(ctx, W, H, params) {
            ctx.fillStyle = params.baseColor; ctx.fillRect(0, 0, W, H);
            const numDuneSets = 4 + Math.floor(Math.random() * 4);
            for (let i = 0; i < numDuneSets; i++) {
                const duneColor = Math.random() > 0.5 ? params.featureColor1 : params.featureColor2;
                const startY = Math.random() * H;
                const amplitude = H / (12 + Math.random() * 12);
                const frequency = 0.006 + Math.random() * 0.012;
                const thickness = H / (18 + Math.random() * 18);
                ctx.beginPath(); ctx.moveTo(0, startY);
                for (let x_coord = 0; x_coord < W; x_coord += 12) {
                    const yOffset = Math.sin(x_coord * frequency + i) * amplitude;
                    ctx.lineTo(x_coord, startY + yOffset);
                }
                ctx.lineTo(W, startY + H * 0.2); ctx.lineTo(0, startY + H * 0.2); ctx.closePath();
                const grad = ctx.createLinearGradient(0, startY - amplitude, 0, startY + amplitude + thickness);
                grad.addColorStop(0, hexToRgba(lightenColor(duneColor, 0.08), 0.65));
                grad.addColorStop(0.5, hexToRgba(duneColor, 0.85));
                grad.addColorStop(1, hexToRgba(darkenColor(duneColor, 0.08), 0.75));
                ctx.fillStyle = grad; ctx.fill();
            }
            const numRocks = 8 + Math.floor(Math.random() * 12);
            for (let i = 0; i < numRocks; i++) {
                const x = Math.random() * W; const y = Math.random() * H;
                const radius = (Math.random() * W / 25) + (W / 65);
                ctx.beginPath(); ctx.moveTo(x + radius, y);
                for(let a = 0; a < Math.PI * 2; a += Math.PI / (2.5 + Math.random()*2.5) ) {
                    const r = radius * (0.75 + Math.random() * 0.5);
                    ctx.lineTo(x + Math.cos(a) * r, y + Math.sin(a) * r);
                }
                ctx.closePath();
                ctx.fillStyle = hexToRgba(params.featureColor1, 0.55 + Math.random()*0.15); ctx.fill();
            }
        }

        // --- Helper color functions ---
        function hexToRgba(hex, alpha = 1) {
            const r = parseInt(hex.slice(1, 3), 16);
            const g = parseInt(hex.slice(3, 5), 16);
            const b = parseInt(hex.slice(5, 7), 16);
            return `rgba(${r}, ${g}, ${b}, ${alpha})`;
        }

        function darkenColor(hex, percent) {
            let r = parseInt(hex.slice(1, 3), 16); let g = parseInt(hex.slice(3, 5), 16); let b = parseInt(hex.slice(5, 7), 16);
            r = Math.max(0, Math.floor(r * (1 - percent))); g = Math.max(0, Math.floor(g * (1 - percent))); b = Math.max(0, Math.floor(b * (1 - percent)));
            return `#${r.toString(16).padStart(2, '0')}${g.toString(16).padStart(2, '0')}${b.toString(16).padStart(2, '0')}`;
        }

        function lightenColor(hex, percent) {
            let r = parseInt(hex.slice(1, 3), 16); let g = parseInt(hex.slice(3, 5), 16); let b = parseInt(hex.slice(5, 7), 16);
            r = Math.min(255, Math.floor(r * (1 + percent))); g = Math.min(255, Math.floor(g * (1 + percent))); b = Math.min(255, Math.floor(b * (1 + percent)));
            return `#${r.toString(16).padStart(2, '0')}${g.toString(16).padStart(2, '0')}${b.toString(16).padStart(2, '0')}`;
        }

        function getRandomColor() {
            return '#' + Math.floor(Math.random()*16777215).toString(16).padStart(6, '0');
        }

        function randomizePlanetValues() {
            if (Math.random() < 0.4) { // Chance to pick a random preset
                const presetKeys = Object.keys(planetPresets);
                const randomPresetName = presetKeys[Math.floor(Math.random() * presetKeys.length)];
                planetPresetSelect.value = randomPresetName;
                const event = new Event('change'); // Create a new change event
                planetPresetSelect.dispatchEvent(event); // Dispatch it
            } else {
                planetPresetSelect.value = 'custom';
                currentPlanetType = 'custom';
                currentPlanetParameters.textureStyle = 'default_procedural';

                planetSizeInput.value = (Math.random() * 2.5 + 0.5).toFixed(1);
                baseColorInput.value = getRandomColor();
                featureColor1Input.value = getRandomColor();
                featureColor2Input.value = getRandomColor();
                atmosphereColorInput.value = getRandomColor();
                atmosphereDensityInput.value = (Math.random() * 0.8 + 0.1).toFixed(2);
                rotationSpeedInput.value = Math.floor(Math.random() * 90) + 10;

                readCurrentControlValues();
                updatePlanetSystems();
            }
        }

        function onWindowResize() {
            const newWidth = canvasContainer.clientWidth;
            const newHeight = canvasContainer.clientHeight;
            camera.aspect = newWidth / newHeight;
            camera.updateProjectionMatrix();
            renderer.setSize(newWidth, newHeight);
        }

        function animate() {
            requestAnimationFrame(animate);
            if (planet) {
                planet.rotation.y += currentPlanetParameters.rotationSpeed;
            }
            orbitControls.update();
            renderer.render(scene, camera);
        }

        init();
    </script>
</body>
</html>