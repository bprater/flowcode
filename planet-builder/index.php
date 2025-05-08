<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Earth-like Planet Generator - Three.js</title>
    <style>
        body {
            margin: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
            background-color: #000000;
            color: #e0e0e0;
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 100vh;
            overflow: hidden;
            padding-bottom: 20px;
            box-sizing: border-box;
        }

        #planetCanvasContainer {
            width: 100%;
            max-width: 800px;
            height: 55vh; /* Adjusted height slightly */
            margin-bottom: 15px;
            touch-action: none;
        }

        .controls {
            background-color: rgba(20, 20, 30, 0.9);
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.5);
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); /* Adjusted min width */
            gap: 10px; /* Reduced gap */
            max-width: 900px; /* Slightly wider to accommodate more controls */
            width: 95%;
            z-index: 10;
        }

        .control-group {
            display: flex;
            flex-direction: column;
        }

        .control-group label {
            margin-bottom: 4px;
            font-size: 0.8em; /* Slightly smaller labels */
        }

        .control-group input[type="range"],
        .control-group input[type="color"],
        .control-group button {
            padding: 6px; /* Slightly reduced padding */
            border-radius: 4px;
            border: 1px solid #303050;
            background-color: #1a1a2e;
            color: #e0e0e0;
            font-size: 0.85em; /* Slightly smaller inputs */
        }
        .control-group input[type="color"] {
            padding: 1px;
            height: 28px;
        }
        .control-group button {
            cursor: pointer;
            background-color: #3a3a6a;
            transition: background-color 0.2s;
        }
        .control-group button:hover {
            background-color: #5a5a90;
        }
    </style>
</head>
<body>

    <div id="planetCanvasContainer"></div>

    <div class="controls">
        <div class="control-group">
            <label for="planetSize">Planet Size (Radius):</label>
            <input type="range" id="planetSize" min="0.5" max="3" value="1.5" step="0.1">
        </div>
        <div class="control-group">
            <label for="oceanColor">Ocean Color:</label>
            <input type="color" id="oceanColor" value="#2E5A88">
        </div>
        <div class="control-group">
            <label for="landColorLow">Land Color (Low/Green):</label>
            <input type="color" id="landColorLow" value="#5A8B4C">
        </div>
        <div class="control-group">
            <label for="landColorHigh">Land Color (High/Brown):</label>
            <input type="color" id="landColorHigh" value="#A08060">
        </div>
        <div class="control-group">
            <label for="cloudColor">Cloud & Ice Cap Color:</label>
            <input type="color" id="cloudColor" value="#FFFFFF">
        </div>
        <div class="control-group">
            <label for="atmosphereColor">Atmosphere Base Color:</label>
            <input type="color" id="atmosphereColor" value="#A0C0E0">
        </div>
        <div class="control-group">
            <label for="atmosphereDensity">Atmosphere Density:</label>
            <input type="range" id="atmosphereDensity" min="0" max="1" value="0.35" step="0.01">
        </div>
        <div class="control-group">
            <label for="rotationSpeed">Auto-Rotation Speed:</label>
            <input type="range" id="rotationSpeed" min="0" max="100" value="10">
        </div>
        <div class="control-group">
            <label for="landMassScale">Landmass Scale:</label>
            <input type="range" id="landMassScale" min="1" max="20" value="6" step="0.5">
        </div>
        <div class="control-group">
            <label for="landThreshold">Sea Level Threshold:</label>
            <input type="range" id="landThreshold" min="-0.5" max="0.5" value="0.05" step="0.01">
        </div>
         <div class="control-group">
            <label for="mountainThreshold">(Legacy - No Effect):</label>
            <input type="range" id="mountainThreshold" min="0.1" max="0.8" value="0.35" step="0.01" disabled>
        </div>
        <div class="control-group">
            <label for="iceCapSize">Polar Ice Cap Size:</label>
            <input type="range" id="iceCapSize" min="0" max="0.5" value="0.15" step="0.01">
        </div>
        <div class="control-group">
            <label for="mountainHeightScale">Land Height Scale:</label>
            <input type="range" id="mountainHeightScale" min="0" max="0.5" value="0.1" step="0.01">
        </div>
         <div class="control-group">
            <label for="atmospherePulseSpeed">Atmosphere Pulse Speed:</label>
            <input type="range" id="atmospherePulseSpeed" min="0.1" max="5" value="1" step="0.1">
        </div>
        <div class="control-group">
            <label for="atmospherePulseColor1">Atmosphere Pulse Color 1:</label>
            <input type="color" id="atmospherePulseColor1" value="#4A90E2">
        </div>
        <div class="control-group">
            <label for="atmospherePulseColor2">Atmosphere Pulse Color 2:</label>
            <input type="color" id="atmospherePulseColor2" value="#AF52DE">
        </div>
        <!-- Lighting Controls -->
        <div class="control-group">
            <label for="ambientIntensity">Ambient Light Intensity:</label>
            <input type="range" id="ambientIntensity" min="0" max="1" value="0.15" step="0.01">
        </div>
        <div class="control-group">
            <label for="sunIntensity">Sun Intensity:</label>
            <input type="range" id="sunIntensity" min="0" max="4" value="1.8" step="0.1">
        </div>
        <div class="control-group">
            <label for="sunAngleY">Sun Vertical Angle (Y Pos):</label>
            <input type="range" id="sunAngleY" min="-10" max="15" value="2.5" step="0.5">
        </div>
        <div class="control-group">
            <label for="sunAngleX">Sun Side Position (X Pos):</label>
            <input type="range" id="sunAngleX" min="-15" max="15" value="12" step="0.5">
        </div>
        <!-- Regenerate Button -->
        <div class="control-group" style="grid-column: 1 / -1; margin-top: 10px;">
            <button id="regeneratePlanet">Regenerate Planet Surface</button>
        </div>
    </div>

    <script type="importmap">
        {
            "imports": {
                "three": "https://unpkg.com/three@0.157.0/build/three.module.js",
                "three/addons/": "https://unpkg.com/three@0.157.0/examples/jsm/",
                "simplex-noise": "https://esm.sh/simplex-noise@3.0.0"
            }
        }
    </script>
    <script type="module">
        import *   as THREE from 'three';
        import { OrbitControls } from 'three/addons/controls/OrbitControls.js';
        import SimplexNoise from 'simplex-noise';

        let scene, camera, renderer, planet, atmosphereMesh, stars;
        let planetTextureCanvas, planetTextureCtx, planetTexture;
        let displacementMapCanvas, displacementMapCtx, displacementMapTexture;
        let orbitControls;
        let simplex;
        let clock; 
        let ambientLight; // Module scope
        let directionalLight; // Module scope

        const canvasContainer = document.getElementById('planetCanvasContainer');
        const planetSizeInput = document.getElementById('planetSize');
        const oceanColorInput = document.getElementById('oceanColor');
        const landColorLowInput = document.getElementById('landColorLow');
        const landColorHighInput = document.getElementById('landColorHigh');
        const cloudColorInput = document.getElementById('cloudColor');
        const atmosphereColorInput = document.getElementById('atmosphereColor'); 
        const atmosphereDensityInput = document.getElementById('atmosphereDensity');
        const rotationSpeedInput = document.getElementById('rotationSpeed');
        const landMassScaleInput = document.getElementById('landMassScale');
        const landThresholdInput = document.getElementById('landThreshold');
        const mountainThresholdInput = document.getElementById('mountainThreshold'); 
        const iceCapSizeInput = document.getElementById('iceCapSize');
        const mountainHeightScaleInput = document.getElementById('mountainHeightScale');
        const atmospherePulseSpeedInput = document.getElementById('atmospherePulseSpeed');
        const atmospherePulseColor1Input = document.getElementById('atmospherePulseColor1');
        const atmospherePulseColor2Input = document.getElementById('atmospherePulseColor2');
        const ambientIntensityInput = document.getElementById('ambientIntensity'); // Lighting
        const sunIntensityInput = document.getElementById('sunIntensity'); // Lighting
        const sunAngleYInput = document.getElementById('sunAngleY'); // Lighting
        const sunAngleXInput = document.getElementById('sunAngleX'); // Lighting
        const regenerateButton = document.getElementById('regeneratePlanet');

        let currentPlanetParameters = {
            size: 1.5, oceanColor: '#2E5A88', landColorLow: '#5A8B4C', landColorHigh: '#A08060',
            cloudColor: '#FFFFFF', atmosphereColor: '#A0C0E0', atmosphereDensity: 0.35,
            rotationSpeed: 0.001, landMassScale: 6, landThreshold: 0.05,
            iceCapSize: 0.15, cloudScale: 10, cloudOpacity: 0.65,
            mountainHeightScale: 0.1,
            atmospherePulseSpeed: 1.0, atmospherePulseColor1: '#4A90E2', atmospherePulseColor2: '#AF52DE',
            ambientIntensity: 0.15, sunIntensity: 1.8, sunAngleY: 2.5, sunAngleX: 12, sunPosZ: 8 
        };

        function init() {
            clock = new THREE.Clock();

            if (typeof SimplexNoise === 'function' && SimplexNoise.prototype && typeof SimplexNoise.prototype.noise2D === 'function') {
                 simplex = new SimplexNoise();
            } else {
                alert("Critical Error: Failed to initialize SimplexNoise. Check console.");
                regenerateButton.disabled = true; 
                return; 
            }

            scene = new THREE.Scene();
            const aspect = canvasContainer.clientWidth / canvasContainer.clientHeight;
            camera = new THREE.PerspectiveCamera(75, aspect, 0.1, 1000);
            camera.position.z = 4;

            renderer = new THREE.WebGLRenderer({ antialias: true, alpha: false });
            renderer.shadowMap.enabled = true;
            renderer.shadowMap.type = THREE.PCFSoftShadowMap; 
            renderer.setSize(canvasContainer.clientWidth, canvasContainer.clientHeight);
            renderer.setPixelRatio(window.devicePixelRatio);
            canvasContainer.appendChild(renderer.domElement);

            orbitControls = new OrbitControls(camera, renderer.domElement);
            orbitControls.enableDamping = true;
            orbitControls.dampingFactor = 0.05;
            orbitControls.minDistance = 2;
            orbitControls.maxDistance = 20;

            planetTextureCanvas = document.createElement('canvas');
            planetTextureCanvas.width = 1024; planetTextureCanvas.height = 512;
            planetTextureCtx = planetTextureCanvas.getContext('2d');
            planetTexture = new THREE.CanvasTexture(planetTextureCanvas);
            planetTexture.wrapS = THREE.RepeatWrapping; planetTexture.wrapT = THREE.ClampToEdgeWrapping;
            planetTexture.colorSpace = THREE.SRGBColorSpace;

            displacementMapCanvas = document.createElement('canvas');
            displacementMapCanvas.width = 1024; displacementMapCanvas.height = 512;
            displacementMapCtx = displacementMapCanvas.getContext('2d');
            displacementMapTexture = new THREE.CanvasTexture(displacementMapCanvas);
            displacementMapTexture.wrapS = THREE.RepeatWrapping; displacementMapTexture.wrapT = THREE.ClampToEdgeWrapping;

            const planetGeometry = new THREE.SphereGeometry(1, 128, 64); 
            const planetMaterial = new THREE.MeshStandardMaterial({
                map: planetTexture,
                displacementMap: displacementMapTexture,
                displacementScale: currentPlanetParameters.mountainHeightScale * currentPlanetParameters.size,
                roughness: 0.8, metalness: 0.05,
            });
            planet = new THREE.Mesh(planetGeometry, planetMaterial);
            planet.castShadow = true; 
            planet.receiveShadow = true;
            scene.add(planet);

            const atmosphereGeometry = new THREE.SphereGeometry(1.05, 64, 32);
            const atmosphereMaterial = new THREE.MeshBasicMaterial({
                transparent: true,
                opacity: currentPlanetParameters.atmosphereDensity,
                side: THREE.BackSide,
                blending: THREE.AdditiveBlending,
                depthWrite: false
            });
            atmosphereMesh = new THREE.Mesh(atmosphereGeometry, atmosphereMaterial);
            planet.add(atmosphereMesh);

            // Initialize Lights using parameters
            ambientLight = new THREE.AmbientLight(0xffffff, currentPlanetParameters.ambientIntensity); 
            scene.add(ambientLight);
            
            directionalLight = new THREE.DirectionalLight(0xffffff, currentPlanetParameters.sunIntensity);
            directionalLight.position.set(
                currentPlanetParameters.sunAngleX, 
                currentPlanetParameters.sunAngleY, 
                currentPlanetParameters.sunPosZ
            ); 
            directionalLight.castShadow = true;
            directionalLight.shadow.mapSize.width = 2048;
            directionalLight.shadow.mapSize.height = 2048;
            directionalLight.shadow.bias = -0.001;    
            directionalLight.shadow.normalBias = 0.04; 
            updateDirectionalLightShadowCamera(directionalLight, currentPlanetParameters.size); // Initial setup
            scene.add(directionalLight);
            // const shadowHelper = new THREE.CameraHelper(directionalLight.shadow.camera); scene.add(shadowHelper);

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

            // Combine all inputs for listeners
            const allInputs = [
                planetSizeInput, oceanColorInput, landColorLowInput, landColorHighInput, cloudColorInput,
                atmosphereColorInput, atmosphereDensityInput, rotationSpeedInput,
                landMassScaleInput, landThresholdInput, /*mountainThresholdInput,*/ // Not listening to disabled input
                iceCapSizeInput, mountainHeightScaleInput,
                atmospherePulseSpeedInput, atmospherePulseColor1Input, atmospherePulseColor2Input,
                ambientIntensityInput, sunIntensityInput, sunAngleYInput, sunAngleXInput // Added lighting inputs
            ];

            allInputs.forEach(input => {
                if (!input) return; // Skip if any input wasn't found (e.g., mountainThreshold)
                input.addEventListener('input', () => {
                    readCurrentControlValues();
                    if (input === ambientIntensityInput || input === sunIntensityInput || input === sunAngleYInput || input === sunAngleXInput) {
                        updateLighting(); 
                    } else if (input === atmospherePulseSpeedInput || input === atmospherePulseColor1Input || input === atmospherePulseColor2Input || input === atmosphereDensityInput ) {
                        updateAtmosphereMesh();
                    } else if (input === mountainHeightScaleInput || input === planetSizeInput) {
                        updatePlanetMesh(); 
                    } else {
                        updatePlanetSystems(); // Regenerate textures for other changes
                    }
                });
            });

            regenerateButton.addEventListener('click', () => {
                if (typeof SimplexNoise === 'function' && SimplexNoise.prototype && typeof SimplexNoise.prototype.noise2D === 'function') {
                    simplex = new SimplexNoise();
                } else {
                    console.error("Cannot re-initialize SimplexNoise on regenerate.");
                }
                readCurrentControlValues();
                updatePlanetSystems();
            });
            window.addEventListener('resize', onWindowResize, false);

            readInitialControlValues();
            updateLighting(); // Apply initial lighting state
            updatePlanetSystems();
            animate();
        }

        function updateDirectionalLightShadowCamera(light, planetSize) {
            if (!light || !light.shadow || !light.shadow.camera) return; // Add check for shadow camera existence

            const lightDistance = light.position.length();
            const planetRadius = planetSize;
            const shadowCamSize = planetRadius * 1.15; 
            light.shadow.camera.left = -shadowCamSize;
            light.shadow.camera.right = shadowCamSize;
            light.shadow.camera.top = shadowCamSize;
            light.shadow.camera.bottom = -shadowCamSize;
            light.shadow.camera.near = Math.max(0.1, lightDistance - planetRadius * 1.5);
            light.shadow.camera.far = lightDistance + planetRadius * 1.5;
            light.shadow.camera.updateProjectionMatrix(); // IMPORTANT
        }
        
        function readCurrentControlValues() {
            currentPlanetParameters.size = parseFloat(planetSizeInput.value);
            currentPlanetParameters.oceanColor = oceanColorInput.value;
            currentPlanetParameters.landColorLow = landColorLowInput.value;
            currentPlanetParameters.landColorHigh = landColorHighInput.value;
            currentPlanetParameters.cloudColor = cloudColorInput.value;
            currentPlanetParameters.atmosphereColor = atmosphereColorInput.value;
            currentPlanetParameters.atmosphereDensity = parseFloat(atmosphereDensityInput.value);
            currentPlanetParameters.rotationSpeed = parseFloat(rotationSpeedInput.value) / 10000;
            currentPlanetParameters.landMassScale = parseFloat(landMassScaleInput.value);
            currentPlanetParameters.landThreshold = parseFloat(landThresholdInput.value);
            // currentPlanetParameters.mountainThreshold = parseFloat(mountainThresholdInput.value); // No longer used in logic
            currentPlanetParameters.iceCapSize = parseFloat(iceCapSizeInput.value);
            currentPlanetParameters.mountainHeightScale = parseFloat(mountainHeightScaleInput.value);
            currentPlanetParameters.atmospherePulseSpeed = parseFloat(atmospherePulseSpeedInput.value);
            currentPlanetParameters.atmospherePulseColor1 = atmospherePulseColor1Input.value;
            currentPlanetParameters.atmospherePulseColor2 = atmospherePulseColor2Input.value;
            currentPlanetParameters.ambientIntensity = parseFloat(ambientIntensityInput.value); // Read lighting
            currentPlanetParameters.sunIntensity = parseFloat(sunIntensityInput.value); // Read lighting
            currentPlanetParameters.sunAngleY = parseFloat(sunAngleYInput.value); // Read lighting
            currentPlanetParameters.sunAngleX = parseFloat(sunAngleXInput.value); // Read lighting
        }

        function readInitialControlValues() {
            readCurrentControlValues();
        }

        function updateLighting() {
            if (ambientLight) {
                ambientLight.intensity = currentPlanetParameters.ambientIntensity;
            }
            if (directionalLight) {
                directionalLight.intensity = currentPlanetParameters.sunIntensity;
                directionalLight.position.set(
                    currentPlanetParameters.sunAngleX, 
                    currentPlanetParameters.sunAngleY, 
                    currentPlanetParameters.sunPosZ 
                );
                updateDirectionalLightShadowCamera(directionalLight, currentPlanetParameters.size); // Update shadow cam when light moves
            }
        }

        function updatePlanetSystems() {
            updatePlanetMesh();
            updateAtmosphereMesh();
            generateEarthLikeTextureAndDisplacementMap();
        }

        function updatePlanetMesh() {
            if (!planet || !directionalLight) return; // Ensure directionalLight exists
            
            const oldSize = planet.scale.x; 
            planet.scale.set(currentPlanetParameters.size, currentPlanetParameters.size, currentPlanetParameters.size);
            
            if (planet.material.displacementMap) {
                planet.material.displacementScale = currentPlanetParameters.mountainHeightScale * currentPlanetParameters.size;
                planet.material.needsUpdate = true;
            }

            // Update shadow camera if size changed
            if (oldSize !== currentPlanetParameters.size) {
                 updateDirectionalLightShadowCamera(directionalLight, currentPlanetParameters.size);
            }
        }

        function updateAtmosphereMesh() {
            if (!atmosphereMesh || !atmosphereMesh.material) return;
            atmosphereMesh.material.opacity = currentPlanetParameters.atmosphereDensity;
            atmosphereMesh.material.needsUpdate = true;
        }
        
        function generateEarthLikeTextureAndDisplacementMap() {
            if (!planetTextureCtx || !displacementMapCtx || !simplex) {
                console.warn("Texture/Displacement generation skipped: Contexts or SimplexNoise not initialized.");
                return;
            }

            const W = planetTextureCanvas.width;
            const H = planetTextureCanvas.height;

            const colorImageData = planetTextureCtx.createImageData(W, H);
            const colorData = colorImageData.data;
            const displacementImageData = displacementMapCtx.createImageData(W, H);
            const displacementData = displacementImageData.data;

            const params = currentPlanetParameters;
            const landScale = params.landMassScale / W;
            const cloudScale = params.cloudScale / W;

            const oceanRGB = hexToRgbArray(params.oceanColor);
            const landLowRGB = hexToRgbArray(params.landColorLow);
            const landHighRGB = hexToRgbArray(params.landColorHigh);
            const cloudRGB = hexToRgbArray(params.cloudColor);

            const oceanFloorElevation = 10; 
            const seaLevelElevation = 40;  
            const maxLowlandElevation = 100; 
            const maxMountainElevation = 230; 

            const approxMaxIntensity = 0.8;

            for (let y = 0; y < H; y++) {
                for (let x = 0; x < W; x++) {
                    const i = (y * W + x) * 4;

                    let noiseVal = 0;
                    let frequency = 1.0; let amplitude = 1.0; let maxAmplitude = 0;
                    for (let octave = 0; octave < 6; octave++) {
                        noiseVal += simplex.noise2D(x * landScale * frequency, y * landScale * frequency) * amplitude;
                        maxAmplitude += amplitude; amplitude *= 0.5; frequency *= 2.0;
                    }
                    noiseVal /= maxAmplitude;

                    let r, g, b;
                    let currentElevationValue;

                    if (noiseVal <= params.landThreshold) { // Ocean
                        const oceanDepthFactor = (noiseVal + 1) / (params.landThreshold + 1); 
                        currentElevationValue = lerp(oceanFloorElevation, seaLevelElevation, oceanDepthFactor);
                        r = oceanRGB[0]; g = oceanRGB[1]; b = oceanRGB[2];

                    } else { // Land
                        const landHeightFactor = Math.max(0, Math.min(1, (noiseVal - params.landThreshold) / (approxMaxIntensity - params.landThreshold)));
                        currentElevationValue = lerp(seaLevelElevation, maxMountainElevation, landHeightFactor);

                        const greenDominanceEnd = 0.4; 
                        let colorBlendFactor = 0;
                        if (landHeightFactor <= greenDominanceEnd) {
                            colorBlendFactor = (landHeightFactor / greenDominanceEnd) * 0.3; 
                        } else {
                            const factorInBrownZone = (landHeightFactor - greenDominanceEnd) / (1.0 - greenDominanceEnd);
                            colorBlendFactor = 0.3 + factorInBrownZone * 0.7;
                        }
                        colorBlendFactor = Math.max(0, Math.min(1, colorBlendFactor));

                        r = lerp(landLowRGB[0], landHighRGB[0], colorBlendFactor);
                        g = lerp(landLowRGB[1], landHighRGB[1], colorBlendFactor);
                        b = lerp(landLowRGB[2], landHighRGB[2], colorBlendFactor);
                    }

                    const normalizedY = y / H;
                    const iceCapBoundary = params.iceCapSize;
                    if (normalizedY < iceCapBoundary || normalizedY > (1 - iceCapBoundary)) {
                        let iceFactor = 0;
                        if (normalizedY < iceCapBoundary) iceFactor = 1 - (normalizedY / iceCapBoundary);
                        else iceFactor = (normalizedY - (1 - iceCapBoundary)) / iceCapBoundary;
                        iceFactor = Math.pow(iceFactor, 1.5);
                        
                        r = lerp(r, cloudRGB[0], iceFactor);
                        g = lerp(g, cloudRGB[1], iceFactor);
                        b = lerp(b, cloudRGB[2], iceFactor);
                        
                        const iceElevation = lerp(seaLevelElevation, maxLowlandElevation * 0.6, 0.5);
                        currentElevationValue = lerp(currentElevationValue, iceElevation, iceFactor);
                    }

                    currentElevationValue = Math.min(255, Math.max(0, currentElevationValue));
                    displacementData[i]     = currentElevationValue;
                    displacementData[i + 1] = currentElevationValue;
                    displacementData[i + 2] = currentElevationValue;
                    displacementData[i + 3] = 255;

                    let cloudNoise = 0;
                    let cloudFreq = 1.0; let cloudAmp = 1.0; let cloudMaxAmp = 0;
                    for (let octave = 0; octave < 5; octave++) {
                        cloudNoise += simplex.noise3D(x * cloudScale * cloudFreq, y * cloudScale * cloudFreq * 0.7, octave * 10 + (planet.rotation.y * 0.1) ) * cloudAmp;
                        cloudMaxAmp += cloudAmp; cloudAmp *= 0.5; cloudFreq *= 2.0;
                    }
                    cloudNoise /= cloudMaxAmp;

                    if (cloudNoise > 0.3) {
                        const cloudAlpha = Math.min(1.0, (cloudNoise - 0.3) / (0.7)) * params.cloudOpacity;
                        r = lerp(r, cloudRGB[0], cloudAlpha);
                        g = lerp(g, cloudRGB[1], cloudAlpha);
                        b = lerp(b, cloudRGB[2], cloudAlpha);
                    }

                    colorData[i]     = r;
                    colorData[i + 1] = g;
                    colorData[i + 2] = b;
                    colorData[i + 3] = 255;
                }
            }
            planetTextureCtx.putImageData(colorImageData, 0, 0);
            planetTexture.needsUpdate = true;
            if (planet.material.map) planet.material.map.needsUpdate = true;

            displacementMapCtx.putImageData(displacementImageData, 0, 0);
            displacementMapTexture.needsUpdate = true;
            if (planet.material.displacementMap) planet.material.displacementMap.needsUpdate = true;
            
            planet.material.needsUpdate = true;
        }

        function hexToRgbArray(hex) {
            const r = parseInt(hex.slice(1, 3), 16);
            const g = parseInt(hex.slice(3, 5), 16);
            const b = parseInt(hex.slice(5, 7), 16);
            return [r, g, b];
        }

        function lerp(a, b, t) {
            return a * (1 - t) + b * t;
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
            const elapsedTime = clock.getElapsedTime();

            if (planet && simplex) { 
                planet.rotation.y += currentPlanetParameters.rotationSpeed;
            }

            if (atmosphereMesh && atmosphereMesh.material) {
                const pulseFactor = (Math.sin(elapsedTime * currentPlanetParameters.atmospherePulseSpeed) + 1) / 2; 
                const color1 = new THREE.Color(currentPlanetParameters.atmospherePulseColor1);
                const color2 = new THREE.Color(currentPlanetParameters.atmospherePulseColor2);
                atmosphereMesh.material.color.lerpColors(color1, color2, pulseFactor);
            }

            orbitControls.update();
            renderer.render(scene, camera);
        }

        init();
    </script>
</body>
</html>