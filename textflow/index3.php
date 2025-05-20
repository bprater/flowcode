<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Three.js Extrusion Debug</title>
    <style>
        body { margin: 0; overflow: hidden; font-family: Arial, sans-serif; background-color: #222; color: #eee;}
        #controlsContainer {
            position: absolute;
            top: 10px;
            left: 10px;
            background-color: rgba(0,0,0,0.75);
            padding: 15px;
            border-radius: 8px;
            z-index: 10;
            min-width: 250px;
        }
        label, input, select { margin-bottom: 5px; display: block; color: #eee; }
        input[type="text"], select {
            padding: 8px;
            border-radius: 3px;
            border: 1px solid #555;
            background-color: #333;
            color: #eee;
            width: calc(100% - 18px);
            margin-bottom: 15px;
        }
        input[type="range"] {
            width: calc(100% - 50px);
            margin-bottom: 0;
            vertical-align: middle;
        }
        .slider-container { margin-bottom: 15px; }
        .slider-container label { margin-bottom: 2px; }
        .slider-container span {
            display: inline-block;
            width: 40px;
            text-align: right;
            font-size: 0.9em;
            vertical-align: middle;
        }
        #loadingIndicator {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 1.5em;
            display: none;
            background-color: rgba(0,0,0,0.6);
            padding: 10px 20px;
            border-radius: 5px;
        }
        #versionInfo {
            position: absolute;
            bottom: 5px;
            right: 10px;
            font-size: 10px;
            color: #666;
            z-index: 5;
        }
    </style>
</head>
<body>

    <div id="controlsContainer">
        <label for="textInput">Text:</label>
        <input type="text" id="textInput" value="III"> <!-- Simple text for debug -->

        <label for="fontSelect">Font:</label>
        <select id="fontSelect">
            <option value="https://threejs.org/examples/fonts/helvetiker_regular.typeface.json">Helvetiker Regular</option>
            <option value="https://threejs.org/examples/fonts/optimer_bold.typeface.json">Optimer Bold</option>
            <option value="https://threejs.org/examples/fonts/gentilis_regular.typeface.json">Gentilis Regular</option>
        </select>

        <div class="slider-container">
            <label for="depthSlider">Extrusion Depth:</label>
            <input type="range" id="depthSlider" min="0.01" max="1.0" step="0.01" value="0.15">
            <span id="depthValue">0.15</span>
        </div>

        <div class="slider-container">
            <label for="letterSpacingSlider">Letter Spacing:</label>
            <input type="range" id="letterSpacingSlider" min="-0.3" max="0.5" step="0.01" value="0">
            <span id="letterSpacingValue">0.00</span>
        </div>
    </div>

    <div id="loadingIndicator">Updating Text...</div>
    <div id="versionInfo"></div>

    <script type="importmap">
        {
            "imports": {
                "three": "https://unpkg.com/three@0.160.0/build/three.module.js",
                "three/addons/": "https://unpkg.com/three@0.160.0/examples/jsm/"
            }
        }
    </script>

    <script type="module">
        import * as THREE from 'three';
        import { FontLoader } from 'three/addons/loaders/FontLoader.js';
        import { TextGeometry } from 'three/addons/geometries/TextGeometry.js';
        import { OrbitControls } from 'three/addons/controls/OrbitControls.js';

        let scene, camera, renderer, controls;
        let textMeshGroup = null;
        let currentTextMaterial = null;
        const fontLoader = new FontLoader();

        const textInput = document.getElementById('textInput');
        const fontSelect = document.getElementById('fontSelect');
        const depthSlider = document.getElementById('depthSlider');
        const depthValueSpan = document.getElementById('depthValue');
        const letterSpacingSlider = document.getElementById('letterSpacingSlider');
        const letterSpacingValueSpan = document.getElementById('letterSpacingValue');
        const loadingIndicator = document.getElementById('loadingIndicator');
        const versionInfoDiv = document.getElementById('versionInfo');

        const TEXT_BASE_SIZE = 1.5;

        function debounce(func, delay) {
            let timeoutId;
            return function(...args) {
                clearTimeout(timeoutId);
                loadingIndicator.textContent = 'Waiting...'; // Indicate debouncing
                loadingIndicator.style.display = 'block';
                timeoutId = setTimeout(() => {
                    func.apply(this, args);
                }, delay);
            };
        }

        function init() {
            versionInfoDiv.textContent = 'Three.js r' + THREE.REVISION;

            scene = new THREE.Scene();
            scene.background = new THREE.Color(0x222222);

            camera = new THREE.PerspectiveCamera(60, window.innerWidth / window.innerHeight, 0.1, 1000);
            camera.position.set(TEXT_BASE_SIZE * 0.5, TEXT_BASE_SIZE * 1, TEXT_BASE_SIZE * 4.5); // Pull back slightly
            camera.lookAt(0,0,0);


            renderer = new THREE.WebGLRenderer({ antialias: true });
            renderer.setSize(window.innerWidth, window.innerHeight);
            document.body.appendChild(renderer.domElement);

            const ambientLight = new THREE.AmbientLight(0xffffff, 0.9);
            scene.add(ambientLight);
            const directionalLight = new THREE.DirectionalLight(0xffffff, 1.5);
            directionalLight.position.set(TEXT_BASE_SIZE * 1.5, TEXT_BASE_SIZE * 3, TEXT_BASE_SIZE * 2.5);
            scene.add(directionalLight);

            controls = new OrbitControls(camera, renderer.domElement);
            controls.enableDamping = true;
            controls.dampingFactor = 0.05;
            controls.target.set(0,0,0);

            const debouncedUpdate = debounce(updateText, 300); 
            textInput.addEventListener('input', debouncedUpdate);
            fontSelect.addEventListener('change', ()=>{
                // Font change might need immediate feedback if it's quick, or use debounce
                loadingIndicator.textContent = 'Loading Font...';
                loadingIndicator.style.display = 'block';
                updateText(); // No debounce for font change, let it load
            });

            depthSlider.addEventListener('input', () => {
                depthValueSpan.textContent = parseFloat(depthSlider.value).toFixed(2);
                debouncedUpdate();
            });
            letterSpacingSlider.addEventListener('input', () => {
                letterSpacingValueSpan.textContent = parseFloat(letterSpacingSlider.value).toFixed(2);
                debouncedUpdate();
            });

            depthValueSpan.textContent = parseFloat(depthSlider.value).toFixed(2);
            letterSpacingValueSpan.textContent = parseFloat(letterSpacingSlider.value).toFixed(2);
            
            updateText();
        }

        function updateText() {
            console.log("%c--- updateText called ---", "color: orange; font-weight: bold;");
            const requestedText = textInput.value;
            const requestedFontUrl = fontSelect.value;

            // Clean up previous mesh group thoroughly
            if (textMeshGroup) {
                console.log("Disposing old textMeshGroup and its children's geometries.");
                scene.remove(textMeshGroup);
                while (textMeshGroup.children.length > 0) {
                    const child = textMeshGroup.children[0];
                    textMeshGroup.remove(child); // Remove from group first
                    if (child.geometry) child.geometry.dispose();
                    // Material is shared (currentTextMaterial), so don't dispose it per child
                }
                textMeshGroup = null; // Ensure it's nullified
            }
            
            // Dispose material only if text is cleared, otherwise reuse/update
            if (!requestedText.trim() && currentTextMaterial) {
                console.log("Text cleared, disposing currentTextMaterial.");
                currentTextMaterial.dispose();
                currentTextMaterial = null;
                loadingIndicator.style.display = 'none';
                return;
            }
            if (!requestedText.trim()) { // If text is empty and no material, just exit
                 loadingIndicator.style.display = 'none';
                return;
            }


            loadingIndicator.textContent = 'Processing...';
            loadingIndicator.style.display = 'block';

            fontLoader.load(requestedFontUrl, (font) => {
                console.log(`%cFont loaded: ${requestedFontUrl}`, "color: green;");

                // Staleness check
                if (textInput.value !== requestedText || fontSelect.value !== requestedFontUrl) {
                    console.warn("STALE FONT LOAD: Input changed during load. Aborting this render.");
                    // If this was the one showing "Processing...", and a new one is queued,
                    // the new one's "Waiting..." or "Processing..." will take over.
                    // If no new one is queued, indicator might linger.
                    // For simplicity, we assume a new updateText is pending or just ran.
                    return; 
                }

                // --- GET LATEST SLIDER VALUES ---
                const currentExtrusionDepth = parseFloat(depthSlider.value);
                const currentLetterSpacing = parseFloat(letterSpacingSlider.value);
                
                console.log(`%cUsing LATEST values: Depth=${currentExtrusionDepth.toFixed(3)}, Spacing=${currentLetterSpacing.toFixed(3)}`, "color: blue;");

                // Material management
                if (!currentTextMaterial) {
                     console.log("Creating new currentTextMaterial.");
                     currentTextMaterial = new THREE.MeshPhongMaterial({
                        color: 0x007bff, 
                        shininess: 90,
                        specular: 0x222222,
                        // side: THREE.DoubleSide // For debugging if needed
                    });
                } else {
                    console.log("Reusing existing currentTextMaterial.");
                    // Update material properties if they could change (e.g., color picker)
                    // currentTextMaterial.color.set(NEW_COLOR); // Example
                }


                textMeshGroup = new THREE.Group(); // Create new group for the new text
                let currentX = 0;

                // --- DEBUG: Force extreme depth for testing ---
                // let effectiveDepth = currentExtrusionDepth;
                // if (currentExtrusionDepth <= parseFloat(depthSlider.min) + 0.001) { // at min
                //     effectiveDepth = 0.001; console.warn("FORCING DEPTH TO MIN: 0.001");
                // } else if (currentExtrusionDepth >= parseFloat(depthSlider.max) - 0.001) { // at max
                //     effectiveDepth = 1.5; console.warn("FORCING DEPTH TO MAX: 1.5"); // Make it very obvious
                // }
                // --- END DEBUG ---


                for (const char of Array.from(requestedText)) {
                    let charGeometry;
                    let advanceWidth;

                    // Log depth for THIS character
                    console.log(`  Char: '${char}', Target Depth: ${currentExtrusionDepth.toFixed(3)}`);
                    // If using effectiveDepth for debug: console.log(`  Char: '${char}', Effective Depth: ${effectiveDepth.toFixed(3)}`);


                    if (char === ' ') {
                        advanceWidth = (font.data?.glyphs?.[' ']?.ha / font.data?.resolution || 0.3) * TEXT_BASE_SIZE;
                        currentX += advanceWidth + currentLetterSpacing;
                        continue;
                    }
                    
                    charGeometry = new TextGeometry(char, {
                        font: font,
                        size: TEXT_BASE_SIZE,
                        depth: currentExtrusionDepth, // Use the LATEST slider value for depth
                        // depth: effectiveDepth, // UNCOMMENT TO USE FORCED DEBUG DEPTH
                        curveSegments: 6, 
                        bevelEnabled: false, // DEBUG: Disable bevels
                        // bevelThickness: 0.01, // DEBUG: Fixed small bevel
                        // bevelSize: 0.01,      // DEBUG: Fixed small bevel
                        // bevelOffset: 0,
                        // bevelSegments: 1 
                    });
                    
                    const charMesh = new THREE.Mesh(charGeometry, currentTextMaterial);
                    charMesh.position.x = currentX;
                    textMeshGroup.add(charMesh);

                    if (font.data?.glyphs?.[char]) {
                        advanceWidth = (font.data.glyphs[char].ha / font.data.resolution) * TEXT_BASE_SIZE;
                    } else {
                        charGeometry.computeBoundingBox();
                        const charWidth = charGeometry.boundingBox.max.x - charGeometry.boundingBox.min.x;
                        advanceWidth = (charWidth > 0) ? charWidth : TEXT_BASE_SIZE * 0.1; 
                    }
                    currentX += advanceWidth + currentLetterSpacing;
                }

                if (textMeshGroup.children.length > 0) {
                    const groupBox = new THREE.Box3().setFromObject(textMeshGroup);
                    const groupSize = new THREE.Vector3();
                    groupBox.getSize(groupSize);
                    textMeshGroup.position.x = -groupSize.x / 2;
                    // Center vertically based on actual glyph bounds - useful if fonts have varying descenders/ascenders
                    // textMeshGroup.position.y = -groupBox.min.y - groupSize.y / 2; // Full vertical center
                    // For typical text, y=0 (baseline alignment) is often fine.
                }
                
                scene.add(textMeshGroup);
                console.log("%cText mesh group added to scene.", "color: green;");
                loadingIndicator.style.display = 'none';

            }, 
            (xhr) => { // onProgress callback
                // console.log((xhr.loaded / xhr.total * 100) + '% loaded');
                loadingIndicator.textContent = `Loading Font (${Math.round(xhr.loaded / xhr.total * 100)}%)...`;
            },
            (error) => { // onError callback
                console.error('Font loading error:', error);
                loadingIndicator.textContent = 'Error loading font!';
                // Consider hiding indicator after a delay: setTimeout(() => loadingIndicator.style.display = 'none', 3000);
            });
        }

        function onWindowResize() {
            camera.aspect = window.innerWidth / window.innerHeight;
            camera.updateProjectionMatrix();
            renderer.setSize(window.innerWidth, window.innerHeight);
        }

        function animate() {
            requestAnimationFrame(animate);
            controls.update();
            renderer.render(scene, camera);
        }

        init();
        animate();
    </script>
</body>
</html>