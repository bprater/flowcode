<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Springy 3D Card</title>
    <style>
        body { margin: 0; overflow: hidden; background-color: #282828; color: #fff; font-family: Arial, sans-serif; display: flex; flex-direction: column; align-items: center; height: 100vh; }
        #controls { padding: 10px; background-color: rgba(0,0,0,0.3); border-radius: 0 0 5px 5px; margin-bottom: 5px; display: flex; flex-direction: column; align-items: center; gap: 8px; }
        .slider-container { display: flex; align-items: center; gap: 10px; font-size: 0.9em; }
        .slider-container label { min-width: 130px; text-align: right; }
        .slider-container input[type="range"] { flex-grow: 1; max-width: 150px;}
        .slider-container span { min-width: 35px; text-align: left; }
        #randomizeButton { padding: 8px 15px; font-size: 1em; cursor: pointer; background-color: #4CAF50; color: white; border: none; border-radius: 4px; margin-top: 5px; }
        #randomizeButton:hover { background-color: #45a049; }
        #randomizeButton:disabled { background-color: #777; cursor: default; }
        #container { width: 100vw; flex-grow: 1; display: flex; justify-content: center; align-items: center; }
        canvas { display: block; }
    </style>
</head>
<body>
    <div id="controls">
        <div class="slider-container">
            <label for="sensitivitySlider">Drag Sensitivity:</label>
            <input type="range" id="sensitivitySlider" min="0.001" max="0.02" step="0.001" value="0.005">
            <span id="sensitivityValue">0.005</span>
        </div>
        <div class="slider-container">
            <label for="dampingSlider">Spin Damping:</label>
            <input type="range" id="dampingSlider" min="0.80" max="0.99" step="0.01" value="0.97">
            <span id="dampingValue">0.97</span>
        </div>
        <div class="slider-container">
            <label for="returnSpeedSlider">Return Speed:</label>
            <input type="range" id="returnSpeedSlider" min="0.01" max="0.2" step="0.01" value="0.05">
            <span id="returnSpeedValue">0.05</span>
        </div>
        <button id="randomizeButton">Randomize Card</button>
    </div>
    <div id="container"></div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>

    <script>
        let scene, camera, renderer, cardMesh;
        let isDragging = false;
        let previousMousePosition = { x: 0, y: 0 };
        let rotationVelocity = new THREE.Vector2(0, 0);
        let targetReturnRotation = new THREE.Euler(0, 0, 0, 'YXZ'); 

        const cardWidth = 2.5, cardHeight = 3.5, cardDepth = 0.05;
        const textureCanvasWidth = 512;
        const textureCanvasHeight = Math.floor(textureCanvasWidth * (cardHeight / cardWidth));
        const possibleNames = ["Starlight Slugger", "Cosmic Ace", "Nova Knight", "Galaxy Gladiator", "Quantum Quasar", "Meteor Masher", "Solar Swingman", "Nebula Nomad", "Celestial Comet", "Vortex Voyager"];
        const possiblePositions = ["Pitcher", "Catcher", "First Base", "Shortstop", "Center Field", "Right Field", "Designated Hitter", "Utility Player", "Left Field", "Third Base"];
        let currentPlayerName = possibleNames[0], currentPlayerPosition = possiblePositions[0];
        const cardFrontBgColor = '#EFEFEF', cardBackColor = '#1E90FF', cardEdgeColor = '#AAAAAA', textColor = '#111111'; // DodgerBlue for back

        let defaultSpinSpeed = 0.002;
        let mouseDragSensitivity = 0.005;
        let dampingFactor = 0.97;
        let returnToPoseSpeed = 0.05;
        const minInertialVelocity = 0.00001; // Made smaller for finer control
        const returnProximityThreshold = 0.005; // Made smaller

        let nextImageObject = null, isCurrentlyPreloading = false;

        function init() {
            scene = new THREE.Scene();
            const controlsDiv = document.getElementById('controls');
            const renderHeight = window.innerHeight - controlsDiv.offsetHeight;
            camera = new THREE.PerspectiveCamera(75, window.innerWidth / renderHeight, 0.1, 1000);
            camera.position.z = cardHeight * 1.35; // Slightly more zoom out

            renderer = new THREE.WebGLRenderer({ antialias: true });
            renderer.setSize(window.innerWidth, renderHeight);
            renderer.setPixelRatio(window.devicePixelRatio);
            document.getElementById('container').appendChild(renderer.domElement);

            const ambientLight = new THREE.AmbientLight(0xffffff, 0.8);
            scene.add(ambientLight);
            const directionalLight = new THREE.DirectionalLight(0xffffff, 0.9);
            directionalLight.position.set(2, 3, 5);
            scene.add(directionalLight);
            
            setupSliders();
            createCard(); 
            animate();

            window.addEventListener('resize', onWindowResize, false);
            renderer.domElement.addEventListener('mousedown', onMouseDown, false);
            window.addEventListener('mouseup', onMouseUp, false);
            window.addEventListener('mousemove', onMouseMove, false);
            document.getElementById('randomizeButton').addEventListener('click', handleRandomizeCard);
        }
        
        function setupSliders() {
            const sSlider = document.getElementById('sensitivitySlider'), sVal = document.getElementById('sensitivityValue');
            const dSlider = document.getElementById('dampingSlider'), dVal = document.getElementById('dampingValue');
            const rSlider = document.getElementById('returnSpeedSlider'), rVal = document.getElementById('returnSpeedValue');

            sSlider.value = mouseDragSensitivity; sVal.textContent = mouseDragSensitivity.toFixed(3);
            dSlider.value = dampingFactor; dVal.textContent = dampingFactor.toFixed(2);
            rSlider.value = returnToPoseSpeed; rVal.textContent = returnToPoseSpeed.toFixed(2);

            sSlider.addEventListener('input', (e) => { mouseDragSensitivity = parseFloat(e.target.value); sVal.textContent = mouseDragSensitivity.toFixed(3); });
            dSlider.addEventListener('input', (e) => { dampingFactor = parseFloat(e.target.value); dVal.textContent = dampingFactor.toFixed(2); });
            rSlider.addEventListener('input', (e) => { returnToPoseSpeed = parseFloat(e.target.value); rVal.textContent = returnToPoseSpeed.toFixed(2); });
        }

        function randomizeTextData() {
            currentPlayerName = possibleNames[Math.floor(Math.random() * possibleNames.length)];
            currentPlayerPosition = possiblePositions[Math.floor(Math.random() * possiblePositions.length)];
        }
        function generatePicsumURL() { return `https://picsum.photos/seed/${Date.now() + Math.random()}/${Math.round(textureCanvasWidth * 1.5)}/${Math.round(textureCanvasHeight * 1.5)}`; }
        function startPreloadingNextImage() { /* ... (same as before, kept for brevity) ... */ 
            if (isCurrentlyPreloading || nextImageObject) return; isCurrentlyPreloading = true;
            const preloadImg = new Image(); preloadImg.crossOrigin = "Anonymous";
            preloadImg.onload = () => { nextImageObject = preloadImg; isCurrentlyPreloading = false; };
            preloadImg.onerror = () => { isCurrentlyPreloading = false; nextImageObject = null; console.error("Failed to preload next image.");};
            preloadImg.src = generatePicsumURL();
        }
        function _drawContentToCanvas(ctx, imgObj) { /* ... (same as before, kept for brevity) ... */ 
            ctx.fillStyle = cardFrontBgColor; ctx.fillRect(0, 0, ctx.canvas.width, ctx.canvas.height);
            const imgAreaHRatio = 0.7; const imgAreaH = ctx.canvas.height * imgAreaHRatio;
            if (imgObj) {
                const imgAR = imgObj.width / imgObj.height; const areaAR = ctx.canvas.width / imgAreaH;
                let sx=0, sy=0, sW=imgObj.width, sH=imgObj.height; let dx=0, dy=0, dW=ctx.canvas.width, dH=imgAreaH;
                if (imgAR > areaAR) { sW = sH * areaAR; sx = (imgObj.width - sW) / 2; } else { sH = sW / areaAR; sy = (imgObj.height - sH) / 2; }
                ctx.drawImage(imgObj, sx, sy, sW, sH, dx, dy, dW, dH);
            } else {
                ctx.fillStyle = 'darkred'; ctx.fillRect(0, 0, ctx.canvas.width, imgAreaH);
                ctx.fillStyle = 'white'; ctx.font = `bold ${Math.floor(textureCanvasHeight*0.05)}px Arial`;
                ctx.textAlign = 'center'; ctx.textBaseline = 'middle'; ctx.fillText("Image Error", ctx.canvas.width/2, imgAreaH/2);
            }
            ctx.fillStyle = textColor; ctx.textAlign = 'center'; ctx.textBaseline = 'middle';
            const txtAreaY = imgAreaH; const txtAreaH = ctx.canvas.height - imgAreaH;
            const nameFS = Math.floor(textureCanvasHeight*0.06); ctx.font = `bold ${nameFS}px Arial`;
            ctx.fillText(currentPlayerName, ctx.canvas.width/2, txtAreaY + txtAreaH*0.35);
            const posFS = Math.floor(textureCanvasHeight*0.04); ctx.font = `normal ${posFS}px Arial`;
            ctx.fillText(currentPlayerPosition, ctx.canvas.width/2, txtAreaY + txtAreaH*0.70);
        }
        function updateCardFaceTexture(callback) { /* ... (same as before, kept for brevity) ... */ 
            randomizeTextData(); const canvas = document.createElement('canvas');
            canvas.width = textureCanvasWidth; canvas.height = textureCanvasHeight; const ctx = canvas.getContext('2d');
            const procCb = (img) => { _drawContentToCanvas(ctx, img); const tex = new THREE.CanvasTexture(canvas); tex.needsUpdate = true; callback(tex); if (!nextImageObject && !isCurrentlyPreloading) startPreloadingNextImage(); };
            if (nextImageObject) { const imgUse = nextImageObject; nextImageObject = null; startPreloadingNextImage(); procCb(imgUse); }
            else { const img = new Image(); img.crossOrigin = "Anonymous"; img.onload = () => procCb(img); img.onerror = () => { console.error("Err face img"); procCb(null); }; img.src = generatePicsumURL(); }
        }
        function createCard() { /* ... (same as before, kept for brevity) ... */ 
            const geom = new THREE.BoxGeometry(cardWidth, cardHeight, cardDepth);
            const edgeMat = new THREE.MeshStandardMaterial({color: cardEdgeColor, roughness:0.8, metalness:0.2});
            const backMat = new THREE.MeshStandardMaterial({color: cardBackColor, roughness:0.6, metalness:0.1});
            updateCardFaceTexture(frontTex => {
                const frontMat = new THREE.MeshStandardMaterial({map: frontTex, roughness:0.7, metalness:0.1});
                cardMesh = new THREE.Mesh(geom, [edgeMat,edgeMat,edgeMat,edgeMat,frontMat,backMat]);
                targetReturnRotation.copy(cardMesh.rotation); // Initialize target
                scene.add(cardMesh);
                if (!nextImageObject && !isCurrentlyPreloading) startPreloadingNextImage();
            });
        }
        function handleRandomizeCard() { /* ... (same as before, kept for brevity) ... */ 
            if (!cardMesh || !cardMesh.material || !cardMesh.material[4]) return;
            const frontM = cardMesh.material[4]; const btn = document.getElementById('randomizeButton');
            btn.disabled = true; btn.textContent = "Loading...";
            updateCardFaceTexture(newTex => { if (frontM.map) frontM.map.dispose(); frontM.map = newTex; frontM.needsUpdate = true; btn.disabled = false; btn.textContent = "Randomize Card"; });
        }

        function onMouseDown(event) {
            if (event.target === renderer.domElement && cardMesh) {
                isDragging = true;
                rotationVelocity.set(0, 0); // Reset velocity for the new drag/throw
                targetReturnRotation.copy(cardMesh.rotation); // Capture current pose as the target to return to
                previousMousePosition.x = event.clientX;
                previousMousePosition.y = event.clientY;
            }
        }

        function onMouseUp(event) { 
            isDragging = false; 
            // Inertia will start with the last rotationVelocity value from onMouseMove
        }

        function onMouseMove(event) {
            if (!isDragging || !cardMesh) return;
            const deltaX = event.clientX - previousMousePosition.x;
            const deltaY = event.clientY - previousMousePosition.y;

            const dRotX = deltaY * mouseDragSensitivity; // Vertical mouse moves card around X-axis
            const dRotY = deltaX * mouseDragSensitivity; // Horizontal mouse moves card around Y-axis

            // Directly manipulate card rotation for immediate feedback
            cardMesh.rotation.x += dRotX;
            cardMesh.rotation.y += dRotY;
            
            // Keep Z rotation to 0 if not intended
            cardMesh.rotation.z = 0; 


            // Update rotationVelocity for the inertia when mouse is released
            // This uses the current drag segment's "speed" as the velocity for the throw
            rotationVelocity.x = dRotX; 
            rotationVelocity.y = dRotY;

            previousMousePosition.x = event.clientX;
            previousMousePosition.y = event.clientY;
        }
        
        function normalizeAngle(angle) {
            while (angle > Math.PI) angle -= 2 * Math.PI;
            while (angle < -Math.PI) angle += 2 * Math.PI;
            return angle;
        }

        function animate() {
            requestAnimationFrame(animate);
            if (cardMesh) {
                if (!isDragging) {
                    // 1. Apply inertial rotation from the last drag movement
                    cardMesh.rotation.x += rotationVelocity.x;
                    cardMesh.rotation.y += rotationVelocity.y;

                    // 2. Dampen inertia
                    rotationVelocity.x *= dampingFactor;
                    rotationVelocity.y *= dampingFactor;

                    // 3. Concurrently apply return-to-pose "spring" force
                    let diffX = normalizeAngle(targetReturnRotation.x - cardMesh.rotation.x);
                    let diffY = normalizeAngle(targetReturnRotation.y - cardMesh.rotation.y);
                    
                    cardMesh.rotation.x += diffX * returnToPoseSpeed;
                    cardMesh.rotation.y += diffY * returnToPoseSpeed;
                    cardMesh.rotation.z = 0; // Ensure Z stays at 0

                    // 4. Handle "settled" state and default spin
                    const isMovingSlowly = Math.abs(rotationVelocity.x) < minInertialVelocity &&
                                           Math.abs(rotationVelocity.y) < minInertialVelocity;
                    
                    // Recalculate diff after spring for accurate proximity check
                    let currentDiffX = normalizeAngle(targetReturnRotation.x - cardMesh.rotation.x);
                    let currentDiffY = normalizeAngle(targetReturnRotation.y - cardMesh.rotation.y);

                    const isAtTarget = Math.abs(currentDiffX) < returnProximityThreshold &&
                                       Math.abs(currentDiffY) < returnProximityThreshold;

                    if (isMovingSlowly && isAtTarget) {
                        // Snap to exact target to prevent micro-jitters
                        cardMesh.rotation.x = targetReturnRotation.x;
                        cardMesh.rotation.y = targetReturnRotation.y;
                        rotationVelocity.set(0,0); // Stop any residual micro-velocity

                        // Apply default Y-axis spin IF the target pose is mostly upright
                        if (Math.abs(targetReturnRotation.x) < returnProximityThreshold &&
                            Math.abs(targetReturnRotation.z) < returnProximityThreshold) { // Assuming z is meant to be 0
                            
                            cardMesh.rotation.y += defaultSpinSpeed;
                            // Update targetReturnRotation.y so the spring doesn't fight the default spin next frame
                            targetReturnRotation.y = cardMesh.rotation.y; 
                        }
                    }
                }
                // If isDragging, rotation is handled by onMouseMove directly.
            }
            renderer.render(scene, camera);
        }

        function onWindowResize() { /* ... (same as before) ... */ 
            const controlsDiv = document.getElementById('controls');
            const renderHeight = window.innerHeight - controlsDiv.offsetHeight;
            camera.aspect = window.innerWidth / renderHeight;
            camera.updateProjectionMatrix();
            renderer.setSize(window.innerWidth, renderHeight);
        }
        init();
    </script>
</body>
</html>