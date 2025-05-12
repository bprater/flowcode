<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>3D Text Customizer - Debug V5 Complete</title>
    <style>
        body { margin: 0; font-family: Arial, sans-serif; background-color: #f0f0f0; color: #333; display: flex; flex-direction: column; align-items: center; height: 100vh; overflow: hidden; }
        #controlsContainer { padding: 10px; background-color: #fff; border-radius: 0 0 8px 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); display: flex; flex-wrap: wrap; gap: 10px; align-items: flex-start; width: 100%; box-sizing: border-box; justify-content: center; flex-shrink: 0; }
        .control-group { display: flex; flex-direction: column; gap: 5px; align-items: flex-start; padding: 8px; border: 1px solid #eee; border-radius: 4px; min-width: 180px; }
        .control-group label, .control-group span { font-size: 0.8em; margin-bottom: 2px; }
        .control-group input[type="text"], .control-group input[type="range"] { padding: 6px; border: 1px solid #ccc; border-radius: 4px; font-size: 0.9em; box-sizing: border-box; width: 100%;}
        .control-group input[type="range"] { padding: 0;}
        .button-row { display: flex; gap: 5px; align-items: center; width: 100%; justify-content: space-around; }
        button { padding: 8px 12px; background-color: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 0.9em; flex-grow: 1; min-width: 80px; /* Ensure buttons have some min width */ }
        button:hover { background-color: #0056b3; }
        button:disabled { background-color: #cccccc; cursor: not-allowed; }
        #fontErrorContainer { width: 100%; flex-shrink: 0; }
        #fontError { color: red; padding: 10px; text-align: center; background-color: #ffe0e0; border: 1px solid red; margin: 5px auto; width: 90%; max-width: 600px; box-sizing: border-box; }
        #sceneContainer { width: 100%; flex-grow: 1; display: flex; justify-content: center; align-items: center; min-height: 200px; background-color: #e0e0e0; }
        canvas { display: block; }
    </style>
</head>
<body>

    <div id="controlsContainer">
        <div class="control-group">
            <label for="customText">Text:</label>
            <input type="text" id="customText" value="ABC">
            <button id="updateTextButton" style="width:100%;">Show 3D Text</button>
            <button id="showFlatTextButton" style="width:100%; background-color: #17a2b8;">Show Flat (2D) Text</button>
        </div>

        <div class="control-group">
            <label for="extrusionSlider">3D Extrusion Depth: <span id="extrusionValueDisplay">0.02</span></label>
            <input type="range" id="extrusionSlider" min="0.001" max="1.0" step="0.001" value="0.02">
            <label for="textSizeSlider">Text Size: <span id="textSizeValueDisplay">0.4</span></label>
            <input type="range" id="textSizeSlider" min="0.1" max="1.5" step="0.01" value="0.4">
        </div>

        <div class="control-group">
            <span>Target Face (Index: <span id="targetFaceIndexDisplay">4</span>):</span>
            <div class="button-row">
                <button id="prevFaceButton">< Prev</button>
                <button id="nextFaceButton">Next ></button>
            </div>
            <button id="pinFaceButton" style="width:100%; margin-top: 5px;">Pin This Face</button>
            <button id="showFaceCenterButton" style="width:100%; margin-top: 5px;">Toggle Face Center</button>
        </div>

        <div class="control-group">
            <span>Text Debug:</span>
            <button id="toggleTextWireframeButton" style="width:100%;">Toggle Wireframe</button>
            <button id="toggleNormalsButton" style="width:100%;">Toggle Normals</button>
            <button id="logTextGeoButton" style="width:100%;">Log Geometry</button>
        </div>
        <button id="clearAllButton" style="background-color: #6c757d; width:100%; max-width:180px; margin: 10px auto 0 auto; display: block;">Clear All</button>
    </div>

    <div id="fontErrorContainer"></div>
    <div id="sceneContainer"></div>

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
        import { OrbitControls } from 'three/addons/controls/OrbitControls.js';
        import { FontLoader } from 'three/addons/loaders/FontLoader.js';
        import { TextGeometry } from 'three/addons/geometries/TextGeometry.js';
        import { VertexNormalsHelper } from 'three/addons/helpers/VertexNormalsHelper.js';

        let scene, camera, renderer, cube, textMesh, controls, loadedFont;
        let originalCubeMaterials = [];
        let highlightMaterial, faceCenterMarker, normalsHelper;

        const CUBE_SIZE = 2;
        let CURRENT_TEXT_SIZE = 0.4;
        let CURRENT_TEXT_EXTRUSION = 0.02;
        const FLAT_TEXT_EXTRUSION = 0.0001;
        const TEXT_COLOR = 0xffd700;
        const CUBE_COLOR = 0x1565c0;
        const HIGHLIGHT_COLOR = 0x00ff00;
        const FACE_CENTER_COLOR = 0xff00ff;
        const EPSILON = 0.001;

        let currentHighlightFaceIndex = -1;
        let pinnedTargetFaceIndex = 4;
        const faceNames = ["Right (+X)", "Left (-X)", "Top (+Y)", "Bottom (-Y)", "Front (+Z)", "Back (-Z)"];
        let isTextWireframe = false;
        let normalsHelperActive = false; // State for normals helper

        function init() {
            scene = new THREE.Scene();
            scene.background = new THREE.Color(0xcccccc);

            const controlsDiv = document.getElementById('controlsContainer');
            const errorDiv = document.getElementById('fontErrorContainer');
            let combinedControlsHeight = (controlsDiv?.offsetHeight || 0) + (errorDiv?.offsetHeight || 0);
            const availableHeight = window.innerHeight - combinedControlsHeight;
            const availableWidth = window.innerWidth;

            camera = new THREE.PerspectiveCamera(50, availableWidth / availableHeight, 0.1, 1000);
            camera.position.set(2.5, 2.5, 4.0);

            renderer = new THREE.WebGLRenderer({ antialias: true });
            renderer.setSize(availableWidth, availableHeight);
            document.getElementById('sceneContainer').appendChild(renderer.domElement);

            const ambientLight = new THREE.AmbientLight(0xffffff, 0.9);
            scene.add(ambientLight);
            const directionalLight = new THREE.DirectionalLight(0xffffff, 1.5);
            directionalLight.position.set(5, 10, 7.5);
            directionalLight.castShadow = true;
            scene.add(directionalLight);

            highlightMaterial = new THREE.MeshStandardMaterial({
                color: HIGHLIGHT_COLOR, emissive: HIGHLIGHT_COLOR, emissiveIntensity: 0.7,
                metalness: 0.3, roughness: 0.7, side: THREE.DoubleSide
            });
            const cubeMaterialsForMesh = [];
            for (let i = 0; i < 6; i++) {
                const mat = new THREE.MeshStandardMaterial({ color: CUBE_COLOR, metalness: 0.4, roughness: 0.6 });
                originalCubeMaterials.push(mat);
                cubeMaterialsForMesh.push(mat);
            }
            const cubeGeometry = new THREE.BoxGeometry(CUBE_SIZE, CUBE_SIZE, CUBE_SIZE);
            cube = new THREE.Mesh(cubeGeometry, cubeMaterialsForMesh);
            cube.castShadow = true;
            cube.receiveShadow = true;
            scene.add(cube);

            controls = new OrbitControls(camera, renderer.domElement);
            controls.enableDamping = true;
            controls.target.set(0, 0, 0);

            const fontLoader = new FontLoader();
            // --- IMPORTANT: Ensure this font path is correct for your environment ---
            const fontPath = 'http://flowcode.test/textflow/fonts/gentilis_regular.typeface.json';
            // For testing different font:
            // const fontPath = 'https://raw.githubusercontent.com/mrdoob/three.js/dev/examples/fonts/gentilis_regular.typeface.json';
            fontLoader.load(fontPath, fontLoadSuccess, undefined, fontLoadError);

            setupUIListeners();
            updateTargetFaceIndexDisplay();
            animate();
        }

        function fontLoadSuccess(font) {
            loadedFont = font;
            document.getElementById('updateTextButton').disabled = false;
            document.getElementById('showFlatTextButton').disabled = false;
            clearFontError();
            console.log("Font loaded. Default text size:", CURRENT_TEXT_SIZE, "Default extrusion:", CURRENT_TEXT_EXTRUSION);
        }

        function fontLoadError(error) {
            console.error('Error loading font:', error);
            displayFontError(`ERROR: Font load failed. Path: ${error.target?.src || 'N/A'}. Check console and font path.`);
            document.getElementById('updateTextButton').disabled = true;
            document.getElementById('showFlatTextButton').disabled = true;
        }

        function setupUIListeners() {
            document.getElementById('updateTextButton').disabled = true;
            document.getElementById('showFlatTextButton').disabled = true;

            document.getElementById('updateTextButton').addEventListener('click', () => generateAndPlaceText(false));
            document.getElementById('showFlatTextButton').addEventListener('click', () => generateAndPlaceText(true));

            const extrusionSlider = document.getElementById('extrusionSlider');
            const extrusionValueDisplay = document.getElementById('extrusionValueDisplay');
            extrusionSlider.addEventListener('input', (event) => {
                CURRENT_TEXT_EXTRUSION = parseFloat(event.target.value);
                extrusionValueDisplay.textContent = CURRENT_TEXT_EXTRUSION.toFixed(3);
                if (textMesh && !textMesh.userData.isFlat) generateAndPlaceText(false);
            });
            extrusionValueDisplay.textContent = CURRENT_TEXT_EXTRUSION.toFixed(3);
            extrusionSlider.value = CURRENT_TEXT_EXTRUSION;

            const textSizeSlider = document.getElementById('textSizeSlider');
            const textSizeValueDisplay = document.getElementById('textSizeValueDisplay');
            textSizeSlider.addEventListener('input', (event) => {
                CURRENT_TEXT_SIZE = parseFloat(event.target.value);
                textSizeValueDisplay.textContent = CURRENT_TEXT_SIZE.toFixed(2);
                if (textMesh) generateAndPlaceText(textMesh.userData.isFlat);
            });
            textSizeValueDisplay.textContent = CURRENT_TEXT_SIZE.toFixed(2);
            textSizeSlider.value = CURRENT_TEXT_SIZE;

            document.getElementById('prevFaceButton').addEventListener('click', () => cycleHighlightFace(-1));
            document.getElementById('nextFaceButton').addEventListener('click', () => cycleHighlightFace(1));
            document.getElementById('pinFaceButton').addEventListener('click', pinCurrentHighlightAsTarget);
            document.getElementById('showFaceCenterButton').addEventListener('click', toggleFaceCenterMarker);
            document.getElementById('toggleTextWireframeButton').addEventListener('click', toggleTextWireframe);
            document.getElementById('toggleNormalsButton').addEventListener('click', toggleNormalsVis);
            document.getElementById('logTextGeoButton').addEventListener('click', logCurrentTextGeometry);
            document.getElementById('clearAllButton').addEventListener('click', clearAll);
            document.getElementById('customText').addEventListener('keypress', (event) => {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    if (!document.getElementById('updateTextButton').disabled) {
                        generateAndPlaceText(textMesh ? textMesh.userData.isFlat : false);
                    }
                }
            });
            window.addEventListener('resize', onWindowResize, false);
        }

        function cycleHighlightFace(direction) {
            if (!cube) return;
            resetFaceMaterials();
            if (currentHighlightFaceIndex === -1) {
                currentHighlightFaceIndex = pinnedTargetFaceIndex;
            } else {
                currentHighlightFaceIndex = (currentHighlightFaceIndex + direction + 6) % 6;
            }
            if (cube.material[currentHighlightFaceIndex]) {
                cube.material[currentHighlightFaceIndex] = highlightMaterial;
                cube.material.needsUpdate = true;
            }
            updateTargetFaceIndexDisplay(true);
        }

        function pinCurrentHighlightAsTarget() {
            if (currentHighlightFaceIndex !== -1) {
                pinnedTargetFaceIndex = currentHighlightFaceIndex;
                resetFaceMaterials();
                if (cube.material[pinnedTargetFaceIndex]) {
                    const tempPinMaterial = new THREE.MeshStandardMaterial({color: 0xffff00, side: THREE.DoubleSide, emissive: 0xffff00, emissiveIntensity: 0.5}); // Yellow for pinned
                    cube.material[pinnedTargetFaceIndex] = tempPinMaterial;
                    cube.material.needsUpdate = true;
                    setTimeout(() => {
                        resetFaceMaterials(); // Revert to original after flash
                        if (textMesh) generateAndPlaceText(textMesh.userData.isFlat);
                    }, 500);
                }
            } else { // If no face was actively cycling, just re-apply text to current pinned face
                if (textMesh) generateAndPlaceText(textMesh.userData.isFlat);
            }
            updateTargetFaceIndexDisplay();
            currentHighlightFaceIndex = -1; // Stop cycling mode
        }

        function toggleFaceCenterMarker() {
            if (!cube) return;
            if (!faceCenterMarker) {
                const markerGeo = new THREE.SphereGeometry(0.03, 16, 8);
                const markerMat = new THREE.MeshBasicMaterial({ color: FACE_CENTER_COLOR, depthTest: false });
                faceCenterMarker = new THREE.Mesh(markerGeo, markerMat);
                const offset = CUBE_SIZE / 2 + 0.015; // Slightly more offset for visibility
                switch (pinnedTargetFaceIndex) {
                    case 0: faceCenterMarker.position.set(offset, 0, 0); break;
                    case 1: faceCenterMarker.position.set(-offset, 0, 0); break;
                    case 2: faceCenterMarker.position.set(0, offset, 0); break;
                    case 3: faceCenterMarker.position.set(0, -offset, 0); break;
                    case 4: faceCenterMarker.position.set(0, 0, offset); break;
                    case 5: faceCenterMarker.position.set(0, 0, -offset); break;
                }
                cube.add(faceCenterMarker);
            } else {
                cube.remove(faceCenterMarker);
                faceCenterMarker.geometry.dispose(); faceCenterMarker.material.dispose(); faceCenterMarker = null;
            }
        }

        function toggleTextWireframe() {
            if (textMesh && textMesh.material) {
                isTextWireframe = !isTextWireframe;
                if (textMesh.material.wireframe !== undefined) {
                    textMesh.material.wireframe = isTextWireframe;
                } else if (Array.isArray(textMesh.material)) { // Should not happen with current setup
                    textMesh.material.forEach(m => m.wireframe = isTextWireframe);
                }
                console.log("Text wireframe:", isTextWireframe);
            }
        }

        function toggleNormalsVis() {
            if (normalsHelper) { // If helper exists, remove it
                scene.remove(normalsHelper);
                normalsHelper.dispose();
                normalsHelper = null;
                normalsHelperActive = false;
                console.log("Normals helper removed.");
            } else if (textMesh) { // If no helper, but text exists, create and add
                normalsHelper = new VertexNormalsHelper(textMesh, CURRENT_TEXT_SIZE * 0.1, 0x0000ff); // Size relative to text
                scene.add(normalsHelper);
                normalsHelperActive = true;
                console.log("Normals helper added.");
            } else {
                console.log("No text mesh to attach normals helper to.");
            }
        }

        function resetFaceMaterials() {
            if (cube && originalCubeMaterials.length === 6) {
                for (let i = 0; i < 6; i++) {
                    if (cube.material[i] !== originalCubeMaterials[i]) {
                        cube.material[i].dispose(); // Dispose temporary highlight/pin materials
                        cube.material[i] = originalCubeMaterials[i];
                    }
                }
                cube.material.needsUpdate = true;
            }
        }

        function updateTargetFaceIndexDisplay(isCycling = false) {
            const displayEl = document.getElementById('targetFaceIndexDisplay');
            if(displayEl) {
                if (isCycling && currentHighlightFaceIndex !== -1) {
                    displayEl.textContent = `${currentHighlightFaceIndex} (${faceNames[currentHighlightFaceIndex]}) - Cycling`;
                    displayEl.style.color = "orange";
                } else {
                    displayEl.textContent = `${pinnedTargetFaceIndex} (${faceNames[pinnedTargetFaceIndex]}) - Pinned`;
                    displayEl.style.color = "green";
                }
            }
        }

        function clearAll() {
            resetFaceMaterials();
            currentHighlightFaceIndex = -1;
            if (faceCenterMarker) {
                cube.remove(faceCenterMarker);
                faceCenterMarker.geometry.dispose(); faceCenterMarker.material.dispose(); faceCenterMarker = null;
            }
            if (normalsHelper) {
                scene.remove(normalsHelper);
                normalsHelper.dispose(); normalsHelper = null; normalsHelperActive = false;
            }
            if (textMesh) {
                cube.remove(textMesh);
                textMesh.geometry.dispose(); textMesh.material.dispose(); textMesh = null;
            }
            isTextWireframe = false;
        }

        function displayFontError(message) {
            const el = document.getElementById('fontErrorContainer');
            if (el) el.innerHTML = `<div id="fontError">${message}</div>`;
            onWindowResize(); // Adjust layout if error message appears/disappears
        }

        function clearFontError() {
            const el = document.getElementById('fontErrorContainer');
            if (el) el.innerHTML = '';
            onWindowResize();
        }

        function generateAndPlaceText(isFlat = false) {
            if (!loadedFont) {
                displayFontError("Font not loaded."); return;
            }
            const userText = document.getElementById('customText').value;

            if (textMesh) {
                cube.remove(textMesh); textMesh.geometry.dispose(); textMesh.material.dispose(); textMesh = null;
            }
            if (normalsHelper) {
                scene.remove(normalsHelper); normalsHelper.dispose(); normalsHelper = null; // Always remove old helper
            }

            if (userText.trim() === "") {
                if(renderer && scene && camera) renderer.render(scene, camera); return;
            }

            const extrusionDepthToUse = isFlat ? FLAT_TEXT_EXTRUSION : CURRENT_TEXT_EXTRUSION;
            console.log(`--- Generating Text ---`);
            console.log(`Input: "${userText}", Size: ${CURRENT_TEXT_SIZE.toFixed(2)}, Target Extrusion (depth param): ${extrusionDepthToUse.toFixed(5)}, isFlat: ${isFlat}`);

            const textGeo = new TextGeometry(userText, {
                font: loadedFont,
                size: CURRENT_TEXT_SIZE,
                depth: extrusionDepthToUse,
                curveSegments: 12,
                bevelEnabled: false,
            });

            textGeo.computeBoundingBox();
            const preCenterBB = textGeo.boundingBox.clone();
            const preCenterSize = new THREE.Vector3();
            preCenterBB.getSize(preCenterSize);

            textGeo.center();
            const postCenterBB = textGeo.boundingBox.clone();
            const postCenterSize = new THREE.Vector3();
            postCenterBB.getSize(postCenterSize);

            console.log(`TextGeometry BBox (before center) - MinZ: ${preCenterBB.min.z.toFixed(5)}, MaxZ: ${preCenterBB.max.z.toFixed(5)}, SizeZ: ${preCenterSize.z.toFixed(5)}`);
            console.log(`TextGeometry BBox (after center)  - MinZ: ${postCenterBB.min.z.toFixed(5)}, MaxZ: ${postCenterBB.max.z.toFixed(5)}, SizeZ (Actual Depth): ${postCenterSize.z.toFixed(5)}`);
            if (Math.abs(postCenterSize.z - extrusionDepthToUse) > 0.001 && !isFlat && extrusionDepthToUse > FLAT_TEXT_EXTRUSION * 10) { // Check for significant discrepancy only for 3D
                 console.warn(`Discrepancy: Actual BBox depth ${postCenterSize.z.toFixed(5)} vs Target extrusion ${extrusionDepthToUse.toFixed(5)}`);
            }


            const textMaterial = new THREE.MeshStandardMaterial({
                color: TEXT_COLOR, metalness: 0.6, roughness: 0.4,
                wireframe: isTextWireframe, side: THREE.DoubleSide
            });
            textMesh = new THREE.Mesh(textGeo, textMaterial);
            textMesh.castShadow = true;
            textMesh.userData.isFlat = isFlat;

            textMesh.rotation.set(0, 0, 0);
            // The offset calculation for Z assumes the text's own Z is its depth.
            // After textGeo.center(), the text extends from -depth/2 to +depth/2 in its local Z.
            // We want the text's "back" (local -depth/2) to be on the cube face.
            // So, the text's origin (local 0,0,0) should be positioned at:
            // cube_face_z + (actual_text_depth / 2) + epsilon
            // Using postCenterSize.z as the actual_text_depth for positioning.
            const actualTextDepthForPositioning = postCenterSize.z;
            const offsetFromFaceCenter = (actualTextDepthForPositioning / 2) + EPSILON;
            const facePlaneOffset = CUBE_SIZE / 2;

            switch (pinnedTargetFaceIndex) {
                case 0: textMesh.rotation.y = Math.PI / 2; textMesh.position.x = facePlaneOffset + offsetFromFaceCenter; break;
                case 1: textMesh.rotation.y = -Math.PI / 2; textMesh.position.x = -(facePlaneOffset + offsetFromFaceCenter); break;
                case 2: textMesh.rotation.x = -Math.PI / 2; textMesh.position.y = facePlaneOffset + offsetFromFaceCenter; break;
                case 3: textMesh.rotation.x = Math.PI / 2; textMesh.position.y = -(facePlaneOffset + offsetFromFaceCenter); break;
                case 4: textMesh.position.z = facePlaneOffset + offsetFromFaceCenter; break;
                case 5: textMesh.rotation.y = Math.PI; textMesh.position.z = -(facePlaneOffset + offsetFromFaceCenter); break;
            }
            cube.add(textMesh);
            console.log(`Text placed on face ${pinnedTargetFaceIndex}. Positioned with offset based on actual depth ${actualTextDepthForPositioning.toFixed(5)}.`);

            if (normalsHelperActive && textMesh) { // If normals were meant to be active, re-create
                toggleNormalsVis(); // This will remove (if any) and create new
            }
             console.log(`--- End Text Generation ---`);
        }

        function logCurrentTextGeometry() {
            if (textMesh && textMesh.geometry) {
                console.log("--- TextGeometry LOG ---");
                console.log("Text UserData (isFlat):", textMesh.userData.isFlat);
                console.log("Input Extrusion (CURRENT_TEXT_EXTRUSION or FLAT_TEXT_EXTRUSION):", textMesh.userData.isFlat ? FLAT_TEXT_EXTRUSION : CURRENT_TEXT_EXTRUSION);
                console.log("Geometry Parameters (options.depth):", textMesh.geometry.parameters?.options?.depth);
                textMesh.geometry.computeBoundingBox(); // Ensure it's up-to-date
                const bb = textMesh.geometry.boundingBox;
                if (bb) {
                    const size = new THREE.Vector3();
                    bb.getSize(size);
                    console.log("BoundingBox Min:", bb.min);
                    console.log("BoundingBox Max:", bb.max);
                    console.log("BoundingBox Size (Actual Dimensions):", size);
                    console.log("Actual depth from BBox (size.z):", size.z.toFixed(5));
                }
                console.log("Geometry Groups:", textMesh.geometry.groups);
                console.log("-------------------------");
            } else {
                console.log("No text mesh or geometry to log.");
            }
        }

        function onWindowResize() {
            const controlsDiv = document.getElementById('controlsContainer');
            const errorDiv = document.getElementById('fontErrorContainer');
            let combinedControlsHeight = (controlsDiv?.offsetHeight || 0) + (errorDiv?.offsetHeight || 0);
            const availableHeight = Math.max(200, window.innerHeight - combinedControlsHeight);
            const availableWidth = window.innerWidth;

            if (camera) {
                camera.aspect = availableWidth / availableHeight;
                camera.updateProjectionMatrix();
            }
            if (renderer) {
                renderer.setSize(availableWidth, availableHeight);
            }
        }

        function animate() {
            requestAnimationFrame(animate);
            controls?.update();
            if (normalsHelper && textMesh?.parent && normalsHelper.parent === scene) { // Check if helper is in scene
                normalsHelper.update();
            }
            renderer?.render(scene, camera);
        }

        init();
    </script>
</body>
</html>