<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ultimate 3D Tree Creation Simulator (No Leaves)</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            display: flex;
            height: 100vh;
            overflow: hidden;
            background-color: #f0f0f0;
        }

        #controls {
            width: 350px;
            padding: 15px;
            background-color: #ffffff;
            border-right: 1px solid #ccc;
            overflow-y: auto;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
        }

        #rendererContainer {
            flex-grow: 1;
            position: relative; /* For potential overlays or stats */
        }

        canvas {
            display: block; /* remove extra space below canvas */
        }

        h3 {
            margin-top: 15px;
            margin-bottom: 10px;
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
            color: #333;
        }
        h3:first-child {
            margin-top: 0;
        }

        .control-group {
            margin-bottom: 12px;
        }

        .control-group label {
            display: block;
            margin-bottom: 4px;
            font-weight: bold;
            font-size: 0.9em;
            color: #555;
        }

        .control-group input[type="range"] {
            width: calc(100% - 60px); /* Adjust width to fit value display */
            margin-right: 10px;
            vertical-align: middle;
        }
        .control-group input[type="number"],
        .control-group input[type="color"],
        .control-group select {
            width: 100%;
            padding: 6px;
            box-sizing: border-box;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .control-group input[type="color"] {
            height: 30px;
            padding: 2px;
        }

        .control-group button {
            width: 100%;
            padding: 10px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1em;
            transition: background-color 0.2s;
        }
        .control-group button:hover {
            background-color: #0056b3;
        }
        .control-group button.secondary {
            background-color: #6c757d;
        }
        .control-group button.secondary:hover {
            background-color: #545b62;
        }

        .control-group .value-display {
            display: inline-block;
            width: 40px; /* Fixed width for value */
            text-align: right;
            font-size: 0.9em;
            color: #333;
            vertical-align: middle;
        }
        
        .checkbox-label {
            display: flex;
            align-items: center;
            font-weight: normal;
            font-size: 0.9em;
        }
        .checkbox-label input[type="checkbox"] {
            margin-right: 8px;
        }

    </style>
    <script type="importmap">
    {
      "imports": {
        "three": "https://unpkg.com/three@0.160.0/build/three.module.js",
        "three/addons/": "https://unpkg.com/three@0.160.0/examples/jsm/"
      }
    }
    </script>
</head>
<body>
    <div id="controls">
        <h3>General</h3>
        <div class="control-group">
            <label for="seedInput">Seed:</label>
            <input type="number" id="seedInput" value="12345">
        </div>
        <div class="control-group">
            <button id="generateButton">Generate Tree</button>
        </div>
        <div class="control-group">
            <button id="randomizeAllButton" class="secondary">Randomize All</button>
        </div>
        <div class="control-group">
            <label class="checkbox-label">
                <input type="checkbox" id="autoRotateCameraCheckbox"> Auto-Rotate Camera
            </label>
        </div>
        <div class="control-group">
            <label for="treePresetSelect">Tree Preset:</label>
            <select id="treePresetSelect">
                <option value="custom">Custom</option>
                <option value="conifer">Conifer (No Leaves)</option>
                <option value="deciduous">Deciduous (No Leaves)</option>
                <option value="fractalSpire">Fractal Spire</option>
            </select>
        </div>

        <h3>Trunk</h3>
        <div class="control-group">
            <label for="trunkHeightSlider">Height: <span id="trunkHeightValue" class="value-display">5</span></label>
            <input type="range" id="trunkHeightSlider" min="1" max="10" step="0.1" value="5">
        </div>
        <div class="control-group">
            <label for="trunkBaseRadiusSlider">Base Radius: <span id="trunkBaseRadiusValue" class="value-display">0.5</span></label>
            <input type="range" id="trunkBaseRadiusSlider" min="0.1" max="2" step="0.05" value="0.5">
        </div>
        <div class="control-group">
            <label for="trunkTaperSlider">Taper (Top/Base Ratio): <span id="trunkTaperValue" class="value-display">0.7</span></label>
            <input type="range" id="trunkTaperSlider" min="0" max="1" step="0.01" value="0.7">
        </div>
        <div class="control-group">
            <label for="trunkColorPicker">Color:</label>
            <input type="color" id="trunkColorPicker" value="#8B4513">
        </div>
        <div class="control-group">
            <label for="trunkRadialSegmentsSlider">Radial Segments: <span id="trunkRadialSegmentsValue" class="value-display">8</span></label>
            <input type="range" id="trunkRadialSegmentsSlider" min="3" max="32" step="1" value="8">
        </div>
        <div class="control-group">
            <label for="trunkHeightSegmentsSlider">Height Segments: <span id="trunkHeightSegmentsValue" class="value-display">3</span></label>
            <input type="range" id="trunkHeightSegmentsSlider" min="1" max="10" step="1" value="3">
        </div>

        <h3>Branches</h3>
        <div class="control-group">
            <label for="maxBranchDepthSlider">Max Depth: <span id="maxBranchDepthValue" class="value-display">4</span></label>
            <input type="range" id="maxBranchDepthSlider" min="1" max="7" step="1" value="4">
        </div>
        <div class="control-group">
            <label for="initialBranchAngleSlider">Initial Pitch (°): <span id="initialBranchAngleValue" class="value-display">45</span></label>
            <input type="range" id="initialBranchAngleSlider" min="0" max="90" step="1" value="45">
        </div>
        <div class="control-group">
            <label for="branchSpreadAngleSlider">Yaw Spread (°): <span id="branchSpreadAngleValue" class="value-display">120</span></label>
            <input type="range" id="branchSpreadAngleSlider" min="0" max="360" step="1" value="120">
        </div>
        <div class="control-group">
            <label for="branchPitchVariationSlider">Pitch Variation (°): <span id="branchPitchVariationValue" class="value-display">15</span></label>
            <input type="range" id="branchPitchVariationSlider" min="0" max="45" step="1" value="15">
        </div>
        <div class="control-group">
            <label for="branchesPerNodeSlider">Branches Per Node: <span id="branchesPerNodeValue" class="value-display">3</span></label>
            <input type="range" id="branchesPerNodeSlider" min="1" max="8" step="1" value="3">
        </div>
        <div class="control-group">
            <label for="branchLengthFactorSlider">Length Factor: <span id="branchLengthFactorValue" class="value-display">0.75</span></label>
            <input type="range" id="branchLengthFactorSlider" min="0.4" max="0.95" step="0.01" value="0.75">
        </div>
        <div class="control-group">
            <label for="branchRadiusFactorSlider">Radius Factor: <span id="branchRadiusFactorValue" class="value-display">0.6</span></label>
            <input type="range" id="branchRadiusFactorSlider" min="0.4" max="0.95" step="0.01" value="0.6">
        </div>
        <div class="control-group">
            <label for="branchColorPicker">Color:</label>
            <input type="color" id="branchColorPicker" value="#A0522D">
        </div>
        <div class="control-group">
            <label for="branchRadialSegmentsSlider">Radial Segments: <span id="branchRadialSegmentsValue" class="value-display">5</span></label>
            <input type="range" id="branchRadialSegmentsSlider" min="3" max="16" step="1" value="5">
        </div>
    </div>
    <div id="rendererContainer"></div>

    <script type="module">
        import * as THREE from 'three';
        import { OrbitControls } from 'three/addons/controls/OrbitControls.js';

        let scene, camera, renderer, controls, treeGroup;
        let currentSeed;

        // Parameters object
        const params = {
            // General
            seed: 12345,
            autoRotateCamera: false,
            // Trunk
            trunkHeight: 5,
            trunkBaseRadius: 0.5,
            trunkTaper: 0.7,
            trunkColor: '#8B4513',
            trunkRadialSegments: 8,
            trunkHeightSegments: 3,
            // Branches
            maxBranchDepth: 4,
            initialBranchAngle: 45, // degrees
            branchSpreadAngle: 120, // degrees
            branchPitchVariation: 15, // degrees
            branchesPerNode: 3,
            branchLengthFactor: 0.75,
            branchRadiusFactor: 0.6,
            branchColor: '#A0522D',
            branchRadialSegments: 5,
        };
        
        const paramConfigs = {
            seedInput: { param: 'seed', type: 'int' },
            autoRotateCameraCheckbox: { param: 'autoRotateCamera', type: 'bool' },
            trunkHeightSlider: { param: 'trunkHeight', type: 'float', valueDisplay: 'trunkHeightValue' },
            trunkBaseRadiusSlider: { param: 'trunkBaseRadius', type: 'float', valueDisplay: 'trunkBaseRadiusValue' },
            trunkTaperSlider: { param: 'trunkTaper', type: 'float', valueDisplay: 'trunkTaperValue' },
            trunkColorPicker: { param: 'trunkColor', type: 'color' },
            trunkRadialSegmentsSlider: { param: 'trunkRadialSegments', type: 'int', valueDisplay: 'trunkRadialSegmentsValue' },
            trunkHeightSegmentsSlider: { param: 'trunkHeightSegments', type: 'int', valueDisplay: 'trunkHeightSegmentsValue' },
            maxBranchDepthSlider: { param: 'maxBranchDepth', type: 'int', valueDisplay: 'maxBranchDepthValue' },
            initialBranchAngleSlider: { param: 'initialBranchAngle', type: 'float', valueDisplay: 'initialBranchAngleValue' },
            branchSpreadAngleSlider: { param: 'branchSpreadAngle', type: 'float', valueDisplay: 'branchSpreadAngleValue' },
            branchPitchVariationSlider: { param: 'branchPitchVariation', type: 'float', valueDisplay: 'branchPitchVariationValue' },
            branchesPerNodeSlider: { param: 'branchesPerNode', type: 'int', valueDisplay: 'branchesPerNodeValue' },
            branchLengthFactorSlider: { param: 'branchLengthFactor', type: 'float', valueDisplay: 'branchLengthFactorValue' },
            branchRadiusFactorSlider: { param: 'branchRadiusFactor', type: 'float', valueDisplay: 'branchRadiusFactorValue' },
            branchColorPicker: { param: 'branchColor', type: 'color' },
            branchRadialSegmentsSlider: { param: 'branchRadialSegments', type: 'int', valueDisplay: 'branchRadialSegmentsValue' },
        };


        // --- PRNG ---
        function setSeed(s) {
            currentSeed = parseInt(s, 10);
            if (isNaN(currentSeed)) currentSeed = Date.now();
        }

        function random() {
            // Basic LCG
            currentSeed = (1103515245 * currentSeed + 12345) % 2147483648; // 2^31
            return currentSeed / 2147483648;
        }
        function randomRange(min, max) { return min + random() * (max - min); }
        function randomInt(min, max) { return Math.floor(randomRange(min, max + 1)); }


        // --- THREE.JS INIT ---
        function init() {
            // Scene
            scene = new THREE.Scene();
            scene.background = new THREE.Color(0xadd8e6); // Light blue background

            // Camera
            camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 0.1, 1000);
            camera.position.set(5, params.trunkHeight*0.75, 10); 

            // Renderer
            renderer = new THREE.WebGLRenderer({ antialias: true });
            renderer.setSize(document.getElementById('rendererContainer').clientWidth, document.getElementById('rendererContainer').clientHeight);
            renderer.shadowMap.enabled = true; 
            renderer.shadowMap.type = THREE.PCFSoftShadowMap;
            document.getElementById('rendererContainer').appendChild(renderer.domElement);

            // Controls
            controls = new OrbitControls(camera, renderer.domElement);
            controls.enableDamping = true;
            controls.target.set(0, params.trunkHeight / 2, 0); 
            
            // Lighting
            const ambientLight = new THREE.AmbientLight(0xffffff, 0.7); 
            scene.add(ambientLight);

            const directionalLight = new THREE.DirectionalLight(0xffffff, 0.8);
            directionalLight.position.set(10, 15, 10);
            directionalLight.castShadow = true;
            directionalLight.shadow.mapSize.width = 1024;
            directionalLight.shadow.mapSize.height = 1024;
            directionalLight.shadow.camera.near = 0.5;
            directionalLight.shadow.camera.far = 50;
            directionalLight.shadow.camera.left = -15;
            directionalLight.shadow.camera.right = 15;
            directionalLight.shadow.camera.top = 15;
            directionalLight.shadow.camera.bottom = -15;
            scene.add(directionalLight);

            // Ground Plane
            const groundGeo = new THREE.PlaneGeometry(40, 40);
            const groundMat = new THREE.MeshStandardMaterial({ color: 0x659D32, side: THREE.DoubleSide });
            const ground = new THREE.Mesh(groundGeo, groundMat);
            ground.rotation.x = -Math.PI / 2;
            ground.receiveShadow = true;
            scene.add(ground);

            // Event Listeners for UI
            setupUIEventListeners();
            updateAllParamsFromUI(); 
            loadPreset('custom'); 

            // Initial Tree
            generateAndDrawTree();

            // Animation loop
            animate();

            // Handle window resize
            window.addEventListener('resize', onWindowResize, false);
        }

        function onWindowResize() {
            camera.aspect = document.getElementById('rendererContainer').clientWidth / document.getElementById('rendererContainer').clientHeight;
            camera.updateProjectionMatrix();
            renderer.setSize(document.getElementById('rendererContainer').clientWidth, document.getElementById('rendererContainer').clientHeight);
        }

        // --- UI HANDLING ---
        function setupUIEventListeners() {
            document.getElementById('generateButton').addEventListener('click', generateAndDrawTree);
            document.getElementById('randomizeAllButton').addEventListener('click', randomizeAll);
            document.getElementById('treePresetSelect').addEventListener('change', (event) => loadPreset(event.target.value));
            
            for (const id in paramConfigs) {
                const el = document.getElementById(id);
                const config = paramConfigs[id];
                const eventType = (el.type === 'checkbox' || el.type === 'select-one' || el.type === 'color') ? 'change' : 'input';
                
                el.addEventListener(eventType, (e) => {
                    let value;
                    if (config.type === 'bool') value = e.target.checked;
                    else if (config.type === 'float') value = parseFloat(e.target.value);
                    else if (config.type === 'int') value = parseInt(e.target.value, 10);
                    else value = e.target.value;
                    
                    params[config.param] = value;
                    if (config.valueDisplay) {
                        document.getElementById(config.valueDisplay).textContent = value.toFixed ? value.toFixed(config.type === 'float' ? 2 : 0) : value;
                    }
                    if(config.param === 'autoRotateCamera') controls.autoRotate = params.autoRotateCamera;

                    if (id !== 'treePresetSelect') {
                        document.getElementById('treePresetSelect').value = 'custom';
                    }
                });
            }
        }
        
        function updateAllParamsFromUI() {
            for (const id in paramConfigs) {
                const el = document.getElementById(id);
                const config = paramConfigs[id];
                let value;
                if (config.type === 'bool') value = el.checked;
                else if (config.type === 'float') value = parseFloat(el.value);
                else if (config.type === 'int') value = parseInt(el.value, 10);
                else value = el.value;
                
                params[config.param] = value;
                if (config.valueDisplay) {
                    document.getElementById(config.valueDisplay).textContent = value.toFixed ? value.toFixed(config.type === 'float' ? 2 : 0) : value;
                }
            }
        }

        function updateUIFromParams() {
            for (const id in paramConfigs) {
                const el = document.getElementById(id);
                const config = paramConfigs[id];
                const value = params[config.param];

                if (config.type === 'bool') el.checked = value;
                else el.value = value;
                
                if (config.valueDisplay) {
                     document.getElementById(config.valueDisplay).textContent = value.toFixed ? value.toFixed(config.type === 'float' ? 2 : 0) : value;
                }
            }
             if(controls) controls.autoRotate = params.autoRotateCamera;
        }

        function randomizeAll() {
            params.seed = Date.now() % 100000;
            // Trunk
            params.trunkHeight = randomRange(2, 8);
            params.trunkBaseRadius = randomRange(0.2, 1.5);
            params.trunkTaper = randomRange(0.1, 1);
            params.trunkColor = '#' + Math.floor(random() * 16777215).toString(16).padStart(6, '0');
            params.trunkRadialSegments = randomInt(5, 16);
            params.trunkHeightSegments = randomInt(2, 5);
            // Branches
            params.maxBranchDepth = randomInt(2, 6);
            params.initialBranchAngle = randomRange(10, 70);
            params.branchSpreadAngle = randomRange(60, 240);
            params.branchPitchVariation = randomRange(5, 30);
            params.branchesPerNode = randomInt(2, 5);
            params.branchLengthFactor = randomRange(0.5, 0.9);
            params.branchRadiusFactor = randomRange(0.5, 0.8);
            params.branchColor = '#' + Math.floor(random() * 16777215).toString(16).padStart(6, '0');
            params.branchRadialSegments = randomInt(3, 8);

            document.getElementById('treePresetSelect').value = 'custom';
            updateUIFromParams();
            generateAndDrawTree();
        }

        const presets = {
            custom: {}, 
            conifer: {
                trunkHeight: 7, trunkBaseRadius: 0.6, trunkTaper: 0.1, trunkColor: '#5C4033',
                maxBranchDepth: 6, initialBranchAngle: 75, branchSpreadAngle: 30, branchPitchVariation: 5,
                branchesPerNode: 4, branchLengthFactor: 0.85, branchRadiusFactor: 0.7, branchColor: '#5C4033',
            },
            deciduous: {
                trunkHeight: 6, trunkBaseRadius: 0.8, trunkTaper: 0.6, trunkColor: '#8B4513',
                maxBranchDepth: 4, initialBranchAngle: 40, branchSpreadAngle: 150, branchPitchVariation: 20,
                branchesPerNode: 3, branchLengthFactor: 0.7, branchRadiusFactor: 0.6, branchColor: '#A0522D',
            },
            fractalSpire: {
                trunkHeight: 8, trunkBaseRadius: 0.3, trunkTaper: 0.9, trunkColor: '#4A4A4A',
                maxBranchDepth: 7, initialBranchAngle: 20, branchSpreadAngle: 70, branchPitchVariation: 5,
                branchesPerNode: 2, branchLengthFactor: 0.8, branchRadiusFactor: 0.8, branchColor: '#606060',
            }
        };

        function loadPreset(presetName) {
            if (presetName === 'custom') {
                updateAllParamsFromUI(); 
                return;
            }
            const presetValues = presets[presetName];
            if (presetValues) {
                for (const key in presetValues) {
                    if (params.hasOwnProperty(key)) {
                        params[key] = presetValues[key];
                    }
                }
                updateUIFromParams();
                generateAndDrawTree();
            }
        }

        // --- TREE GENERATION ---
        function clearTree() {
            if (treeGroup) {
                treeGroup.traverse(object => {
                    if (object.geometry) object.geometry.dispose();
                    if (object.material) {
                        if (Array.isArray(object.material)) {
                            object.material.forEach(mat => {
                                if(mat.map) mat.map.dispose();
                                mat.dispose();
                            });
                        } else {
                            if(object.material.map) object.material.map.dispose();
                            object.material.dispose();
                        }
                    }
                });
                scene.remove(treeGroup);
            }
            treeGroup = new THREE.Group();
            scene.add(treeGroup);
        }

        function generateAndDrawTree() {
            clearTree();
            setSeed(params.seed);

            // Materials
            const trunkMaterial = new THREE.MeshStandardMaterial({ color: params.trunkColor, roughness: 0.8, metalness: 0.2 });
            const branchMaterial = new THREE.MeshStandardMaterial({ color: params.branchColor, roughness: 0.8, metalness: 0.2 });

            // Trunk
            const trunkTopRadius = params.trunkBaseRadius * params.trunkTaper;
            const trunkGeometry = new THREE.CylinderGeometry(
                Math.max(0.01, trunkTopRadius), 
                params.trunkBaseRadius,        
                params.trunkHeight,
                params.trunkRadialSegments,
                params.trunkHeightSegments
            );
            const trunkMesh = new THREE.Mesh(trunkGeometry, trunkMaterial);
            trunkMesh.position.y = params.trunkHeight / 2; 
            trunkMesh.castShadow = true;
            trunkMesh.receiveShadow = true;
            
            const trunkAnchor = new THREE.Object3D(); 
            trunkAnchor.add(trunkMesh);
            trunkAnchor.userData.height = params.trunkHeight;
            trunkAnchor.userData.radius = trunkTopRadius; 
            treeGroup.add(trunkAnchor);

            // Initial branches from trunk
            if (params.maxBranchDepth > 0) {
                 addBranchesRecursive(trunkAnchor, trunkMesh, 1, params.trunkHeight, params.trunkBaseRadius, branchMaterial);
            }
            
            controls.target.set(0, params.trunkHeight / 2, 0); 
        }

        function addBranchesRecursive(parentAnchor, parentSegmentMesh, depth, parentLength, parentRadius, branchMaterial) {
            if (depth > params.maxBranchDepth) return;

            const currentBranchLength = parentLength * params.branchLengthFactor;
            const currentBranchRadius = Math.max(0.01, parentRadius * params.branchRadiusFactor);
            const parentTipY = parentSegmentMesh.geometry.parameters.height; 

            for (let i = 0; i < params.branchesPerNode; i++) {
                const branchGeometry = new THREE.CylinderGeometry(
                    Math.max(0.005, currentBranchRadius * params.trunkTaper), 
                    currentBranchRadius,
                    currentBranchLength,
                    params.branchRadialSegments,
                    1 
                );
                const branchMesh = new THREE.Mesh(branchGeometry, branchMaterial);
                branchMesh.castShadow = true;
                branchMesh.receiveShadow = true;
                branchMesh.position.y = currentBranchLength / 2; 

                const branchAnchor = new THREE.Object3D();
                branchAnchor.add(branchMesh);
                
                branchAnchor.position.y = parentTipY; 

                let pitch = params.initialBranchAngle + randomRange(-params.branchPitchVariation, params.branchPitchVariation);
                if (depth === 1 && params.trunkTaper < 0.3) pitch = Math.min(pitch, 45);

                let yaw = (i / params.branchesPerNode) * 360; 
                yaw += randomRange(-params.branchSpreadAngle / 2, params.branchSpreadAngle / 2);

                branchAnchor.rotation.y = THREE.MathUtils.degToRad(yaw); 
                branchAnchor.rotation.x = THREE.MathUtils.degToRad(pitch); 
                
                parentAnchor.add(branchAnchor); 

                branchAnchor.userData.height = currentBranchLength;
                branchAnchor.userData.radius = currentBranchRadius;
                
                addBranchesRecursive(branchAnchor, branchMesh, depth + 1, currentBranchLength, currentBranchRadius, branchMaterial);
            }
        }
        
        // --- ANIMATION LOOP ---
        function animate() {
            requestAnimationFrame(animate);
            controls.autoRotate = params.autoRotateCamera; 
            controls.update(); 
            renderer.render(scene, camera);
        }

        // Start everything
        document.addEventListener('DOMContentLoaded', init);

    </script>
</body>
</html>