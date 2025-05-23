<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Custom Font 3D Card</title>
    <style>
        body { margin: 0; overflow: hidden; background-color: #282828; color: #fff; font-family: Arial, sans-serif; }
        #container { width: 100vw; height: 100vh; display: flex; justify-content: center; align-items: center; }
        canvas { display: block; }

        #controls {
            position: fixed; /* Changed to fixed for viewport relative */
            top: 10px;
            right: 10px;
            width: 300px; /* Fixed width for the panel */
            max-height: calc(100vh - 20px); /* Max height with padding */
            overflow-y: auto; /* Scroll if content exceeds height */
            background-color: rgba(40, 40, 40, 0.9); /* Darker, slightly transparent */
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0,0,0,0.5);
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px; /* Increased gap */
            font-size: 0.9em; /* Slightly larger base font for controls */
            z-index: 100; /* Ensure it's on top */
        }
        .control-group-label {
            font-weight: bold;
            margin-top: 10px;
            margin-bottom: 2px;
            color: #ddd;
            width: 100%;
            text-align: left;
            border-bottom: 1px solid #555;
            padding-bottom: 3px;
        }
        .control-row { display: flex; align-items: center; gap: 8px; width: 100%; }
        .control-row label { min-width: 100px; text-align: right; color: #ccc; font-size:0.95em; }
        .control-row input[type="range"], .control-row select, .control-row input[type="text"] { 
            flex-grow: 1; 
            background-color: #555;
            color: #fff;
            border: 1px solid #777;
            border-radius: 3px;
            padding: 3px;
        }
        .control-row input[type="text"] { padding: 4px; }
        .control-row span { min-width: 45px; text-align: left; color: #fff; }
        #randomizeButton { 
            padding: 10px 18px; /* Larger button */
            font-size: 1em; 
            cursor: pointer; 
            background-color: #4CAF50; 
            color: white; border: none; 
            border-radius: 4px; margin-top: 15px; 
        }
        #randomizeButton:hover { background-color: #45a049; }
        #randomizeButton:disabled { background-color: #777; cursor: default; }
    </style>
</head>
<body>
    <div id="controls">
        <div class="control-group-label">Interaction Physics</div>
        <div class="control-row">
            <label for="sensitivitySlider">Drag Sensitivity:</label>
            <input type="range" id="sensitivitySlider" min="0.001" max="0.02" step="0.001">
            <span id="sensitivityValue">0.000</span>
        </div>
        <div class="control-row">
            <label for="dampingSlider">Spin Damping:</label>
            <input type="range" id="dampingSlider" min="0.80" max="0.99" step="0.01">
            <span id="dampingValue">0.00</span>
        </div>
        <div class="control-row">
            <label for="returnSpeedSlider">Return Easing Rate:</label>
            <input type="range" id="returnSpeedSlider" min="0.01" max="0.2" step="0.01">
            <span id="returnSpeedValue">0.00</span>
        </div>
        <div class="control-row">
            <label for="easingFunctionSelect">Return Easing Algo:</label>
            <select id="easingFunctionSelect">
                <optgroup label="Standard"><option value="linear">Linear</option></optgroup>
                <optgroup label="Quadratic"><option value="easeInQuad">EaseInQuad</option><option value="easeOutQuad">EaseOutQuad</option><option value="easeInOutQuad">EaseInOutQuad</option></optgroup>
                <optgroup label="Cubic"><option value="easeInCubic">EaseInCubic</option><option value="easeOutCubic" selected>EaseOutCubic</option><option value="easeInOutCubic">EaseInOutCubic</option></optgroup>
                <optgroup label="Quart_Quint"><option value="easeOutQuart">EaseOutQuart</option><option value="easeOutQuint">EaseOutQuint</option></optgroup>
                <optgroup label="Sine_Expo_Circ"><option value="easeOutSine">EaseOutSine</option><option value="easeOutExpo">EaseOutExpo</option><option value="easeOutCirc">EaseOutCirc</option></optgroup>
                <optgroup label="Special"><option value="easeOutBack">EaseOutBack</option></optgroup>
            </select>
        </div>
        
        <div class="control-group-label">Card Oscillation</div>
        <div class="control-row">
            <label for="oscAngleSlider">Osc. Angle (°):</label>
            <input type="range" id="oscAngleSlider" min="10" max="80" step="1">
            <span id="oscAngleValue">0</span>
        </div>
        <div class="control-row">
            <label for="oscSpeedSlider">Osc. Speed:</label>
            <input type="range" id="oscSpeedSlider" min="0.1" max="2.0" step="0.05">
            <span id="oscSpeedValue">0.00</span>
        </div>

        <div class="control-group-label">Card Text</div>
        <div class="control-row">
            <label for="playerNameInput">Player Name:</label>
            <input type="text" id="playerNameInput">
        </div>
        <div class="control-row">
            <label for="playerPositionInput">Position:</label>
            <input type="text" id="playerPositionInput">
        </div>

        <div class="control-group-label">Player Name Font</div>
        <div class="control-row">
            <label for="fontFamilySelect">Font Family:</label>
            <select id="fontFamilySelect">
                <option value="Arial, sans-serif">Arial</option>
                <option value="Verdana, sans-serif">Verdana</option>
                <option value="Georgia, serif">Georgia</option>
                <option value="Times New Roman, serif">Times New Roman</option>
                <option value="Courier New, monospace">Courier New</option>
                <option value="Impact, sans-serif">Impact</option>
                <option value="Comic Sans MS, cursive">Comic Sans MS</option>
            </select>
        </div>
        <div class="control-row">
            <label for="fontSizeSlider">Font Size (factor):</label>
            <input type="range" id="fontSizeSlider" min="0.03" max="0.10" step="0.001">
            <span id="fontSizeValue">0.000</span>
        </div>
        <div class="control-row">
            <label for="letterSpacingSlider">Letter Spacing (px):</label>
            <input type="range" id="letterSpacingSlider" min="-2" max="10" step="0.5"> <!-- px on texture -->
            <span id="letterSpacingValue">0.0</span>
        </div>

        <button id="randomizeButton">Randomize Image & Text</button>
    </div>
    <div id="container"></div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>

    <script>
        let scene, camera, renderer, cardMesh;
        let isDragging = false;
        let previousMousePosition = { x: 0, y: 0 };
        let rotationVelocity = new THREE.Vector2(0, 0);
        let targetReturnRotation = new THREE.Euler(0, 0, 0, 'YXZ');
        let oscillationTime = 0;

        const cardWidth = 2.5, cardHeight = 3.5, cardDepth = 0.05;
        const textureCanvasWidth = 512, textureCanvasHeight = Math.floor(textureCanvasWidth*(cardHeight/cardWidth));
        
        let currentPlayerName = "Starlight Slugger";
        let currentPlayerPosition = "Center Field";
        const defaultNames = ["Star Player", "Cosmic Ace", "Nova Knight", "Galaxy Gladiator"];
        const defaultPositions = ["Pitcher", "Catcher", "Shortstop", "Outfielder"];

        const cFrontBg = '#EFEFEF', cBackBg = '#87CEEB', cEdge = '#AAAAAA', cText = '#111111'; // SkyBlue

        const config = {
            mouseDragSensitivity: 0.005,
            dampingFactor: 0.97,
            returnToPoseSpeed: 0.05,
            oscillationAngleMaxDeg: 45,
            oscillationSpeedFactor: 0.5,
            easingFunction: 'easeOutCubic',
            // Player Name Font Config
            playerNameFontFamily: 'Arial, sans-serif',
            playerNameFontSizeFactor: 0.06, // Factor of textureCanvasHeight
            playerNameLetterSpacing: 0 // In pixels on the texture canvas
        };

        const minInertialVelocity = 0.00001, returnProximityThreshold = 0.005;
        let nextImgObj = null, isPreloading = false;
        let currentCardImage = null;
        const clock = new THREE.Clock();

        const EasingFunctions = { /* ... same ... */ 
            linear:t=>t,easeInQuad:t=>t*t,easeOutQuad:t=>t*(2-t),easeInOutQuad:t=>t<.5?2*t*t:-1+(4-2*t)*t,easeInCubic:t=>t*t*t,easeOutCubic:t=>(--t)*t*t+1,easeInOutCubic:t=>t<.5?4*t*t*t:(t-1)*(2*t-2)*(2*t-2)+1,easeOutQuart:t=>1-(--t)*t*t*t,easeOutQuint:t=>1+(--t)*t*t*t*t,easeOutSine:t=>Math.sin(t*Math.PI/2),easeOutExpo:t=>t===1?1:1-Math.pow(2,-10*t),easeOutCirc:t=>Math.sqrt(1-(--t)*t),easeOutBack:t=>{const c1=1.70158;const c3=c1+1;return 1+c3*Math.pow(t-1,3)+c1*Math.pow(t-1,2);}
        };

        function setupControls() {
            const controlConfigs = { // Changed name for clarity
                sens: {el: 'sensitivitySlider', val: 'sensitivityValue', prop: 'mouseDragSensitivity', target: config, fix: 3},
                damp: {el: 'dampingSlider', val: 'dampingValue', prop: 'dampingFactor', target: config, fix: 2},
                retSpd: {el: 'returnSpeedSlider', val: 'returnSpeedValue', prop: 'returnToPoseSpeed', target: config, fix: 2},
                oscAng: {el: 'oscAngleSlider', val: 'oscAngleValue', prop: 'oscillationAngleMaxDeg', target: config, fix: 0},
                oscSpd: {el: 'oscSpeedSlider', val: 'oscSpeedValue', prop: 'oscillationSpeedFactor', target: config, fix: 2},
                // Player Name Font Controls
                pNameFontFamily: {el: 'fontFamilySelect', prop: 'playerNameFontFamily', target: config, type: 'select'},
                pNameFontSize: {el: 'fontSizeSlider', val: 'fontSizeValue', prop: 'playerNameFontSizeFactor', target: config, fix: 3},
                pNameLetterSpacing: {el: 'letterSpacingSlider', val: 'letterSpacingValue', prop: 'playerNameLetterSpacing', target: config, fix: 1}
            };

            for (const key in controlConfigs) { 
                const cc = controlConfigs[key];
                const element = document.getElementById(cc.el);
                let display;
                if (cc.val) display = document.getElementById(cc.val);

                let initialValue = cc.target[cc.prop];
                element.value = initialValue; 
                if (display) display.textContent = parseFloat(initialValue).toFixed(cc.fix);

                const eventType = cc.type === 'select' ? 'change' : 'input';
                element.addEventListener(eventType, (e) => {
                    const val = cc.type === 'select' ? e.target.value : parseFloat(e.target.value);
                    cc.target[cc.prop] = val;
                    if (display) display.textContent = (cc.type === 'select' ? val : val.toFixed(cc.fix));
                    redrawCardFaceTextOnly(); // Redraw for font changes too
                });
            }

            const easingSelect = document.getElementById('easingFunctionSelect');
            easingSelect.value = config.easingFunction;
            easingSelect.addEventListener('change', (e) => { config.easingFunction = e.target.value; });

            const playerNameInput = document.getElementById('playerNameInput');
            const playerPositionInput = document.getElementById('playerPositionInput');
            playerNameInput.value = currentPlayerName; 
            playerPositionInput.value = currentPlayerPosition;
            playerNameInput.addEventListener('input', (e) => { currentPlayerName = e.target.value; redrawCardFaceTextOnly(); });
            playerPositionInput.addEventListener('input', (e) => { currentPlayerPosition = e.target.value; redrawCardFaceTextOnly(); });
        }

        function randomizeInitialText() { /* ... same ... */ 
             currentPlayerName = defaultNames[Math.floor(Math.random() * defaultNames.length)]; currentPlayerPosition = defaultPositions[Math.floor(Math.random() * defaultPositions.length)];
        }
        function genPicURL() { /* ... same ... */ return `https://picsum.photos/seed/${Date.now()+Math.random()}/${Math.round(textureCanvasWidth*1.5)}/${Math.round(textureCanvasHeight*1.5)}`;}
        function startPreload() { /* ... same ... */ if(isPreloading||nextImgObj)return; isPreloading=true; const pImg=new Image();pImg.crossOrigin="Anonymous"; pImg.onload=()=>{nextImgObj=pImg;isPreloading=false;};pImg.onerror=()=>{isPreloading=false;nextImgObj=null;};pImg.src=genPicURL();}
        
        function _drawContentToCanvas(ctx, imageToDraw) {
            ctx.fillStyle = cFrontBg; ctx.fillRect(0, 0, ctx.canvas.width, ctx.canvas.height);
            const iARat = 0.7; const iAH = ctx.canvas.height * iARat;
            if (imageToDraw && imageToDraw.complete && imageToDraw.naturalHeight !== 0) {
                const imgAR = imageToDraw.width / imageToDraw.height; const areaAR = ctx.canvas.width / iAH;
                let sx=0,sy=0,sW=imageToDraw.width,sH=imageToDraw.height,dx=0,dy=0,dW=ctx.canvas.width,dH=iAH;
                if(imgAR>areaAR){sW=sH*areaAR;sx=(imageToDraw.width-sW)/2;}else{sH=sW/areaAR;sy=(imageToDraw.height-sH)/2;}
                ctx.drawImage(imageToDraw,sx,sy,sW,sH,dx,dy,dW,dH);
            } else {
                ctx.fillStyle='grey'; ctx.fillRect(0,0,ctx.canvas.width,iAH);
                ctx.fillStyle='white'; ctx.font=`bold ${Math.floor(textureCanvasHeight*0.04)}px Arial`;
                ctx.textAlign='center'; ctx.textBaseline='middle';
                ctx.fillText(currentCardImage ? "Loading..." : "No Image", ctx.canvas.width/2, iAH/2);
            }
            
            // Apply text styling
            ctx.fillStyle = cText; ctx.textAlign = 'center'; ctx.textBaseline = 'middle';
            const tAY = iAH; const tAH = ctx.canvas.height - iAH;

            // Player Name - with new font controls
            const nameFontSize = Math.floor(textureCanvasHeight * config.playerNameFontSizeFactor);
            ctx.font = `bold ${nameFontSize}px ${config.playerNameFontFamily}`;
            ctx.letterSpacing = `${config.playerNameLetterSpacing}px`; // Apply letter spacing
            ctx.fillText(currentPlayerName, ctx.canvas.width / 2, tAY + tAH * 0.35);
            ctx.letterSpacing = '0px'; // Reset for other text

            // Player Position - uses default styling for now
            const posFontSize = Math.floor(textureCanvasHeight * 0.04); // Default factor for position
            ctx.font = `normal ${posFontSize}px Arial, sans-serif`; // Default font for position
            ctx.fillText(currentPlayerPosition, ctx.canvas.width / 2, tAY + tAH * 0.70);
        }
        
        function updateCardFaceTextureAndImage(callback) { /* ... same, but calls randomizeInitialText for consistency ... */
            randomizeInitialText(); 
            document.getElementById('playerNameInput').value = currentPlayerName; 
            document.getElementById('playerPositionInput').value = currentPlayerPosition;
            const canvas = document.createElement('canvas'); canvas.width = textureCanvasWidth; canvas.height = textureCanvasHeight; const ctx = canvas.getContext('2d');
            const processAndCallback = (loadedImgObject) => { currentCardImage = loadedImgObject; _drawContentToCanvas(ctx, currentCardImage); const texture = new THREE.CanvasTexture(canvas); texture.needsUpdate = true; callback(texture); if (!nextImgObj && !isPreloading) { startPreload(); } };
            if (nextImgObj) { const imgToUse = nextImgObj; nextImgObj = null; startPreload(); processAndCallback(imgToUse); } 
            else { const img = new Image(); img.crossOrigin = "Anonymous"; img.onload = () => { processAndCallback(img); }; img.onerror = (e) => { console.error("Error loading image for card face:", e); processAndCallback(null); }; img.src = genPicURL(); }
        }
        function redrawCardFaceTextOnly() { /* ... same ... */ 
            if (!cardMesh || !cardMesh.material || !cardMesh.material[4]) return; const frontMaterial = cardMesh.material[4]; const canvas = document.createElement('canvas'); canvas.width = textureCanvasWidth; canvas.height = textureCanvasHeight; const ctx = canvas.getContext('2d'); _drawContentToCanvas(ctx, currentCardImage); if (frontMaterial.map) frontMaterial.map.dispose(); frontMaterial.map = new THREE.CanvasTexture(canvas); frontMaterial.map.needsUpdate = true; frontMaterial.needsUpdate = true;
        }
        function createCard(){ /* ... same ... */ 
            randomizeInitialText(); updCardTexAndImg(fTex=>{ const geo=new THREE.BoxGeometry(cardWidth,cardHeight,cardDepth); const eMat=new THREE.MeshStandardMaterial({color:cEdge,roughness:0.8,metalness:0.2}); const bMat=new THREE.MeshStandardMaterial({color:cBackBg,roughness:0.6,metalness:0.1}); const fMat=new THREE.MeshStandardMaterial({map:fTex,roughness:0.7,metalness:0.1}); cardMesh=new THREE.Mesh(geo,[eMat,eMat,eMat,eMat,fMat,bMat]); targetReturnRotation.copy(cardMesh.rotation); scene.add(cardMesh); if(!nextImgObj&&!isPreloading)startPreload(); });
        }
        // Renamed updCardTexAndImg to avoid conflict in minified version. In full code, use the descriptive name.
        const updCardTexAndImg = updateCardFaceTextureAndImage; 

        function handleRandomizeCard(){ /* ... same ... */ 
            if(!cardMesh||!cardMesh.material[4])return; const fM=cardMesh.material[4]; const btn=document.getElementById('randomizeButton'); btn.disabled=true;btn.textContent="Loading..."; updCardTexAndImg(nTex=>{ if(fM.map)fM.map.dispose(); fM.map=nTex;fM.needsUpdate=true; btn.disabled=false;btn.textContent="Randomize Image & Text";});
        }

        // --- Event Handlers & Animation (onMouseDown, onMouseUp, onMouseMove, normAng, animate, onWindowResize) ---
        // These remain the same. For brevity, not repeating here.
        function onMouseDown(e) { if (e.target === renderer.domElement && cardMesh) { isDragging = true; rotationVelocity.set(0,0); previousMousePosition.x = e.clientX; previousMousePosition.y = e.clientY; } }
        function onMouseUp() { isDragging = false; }
        function onMouseMove(e) { if (!isDragging || !cardMesh) return; const dX = e.clientX - previousMousePosition.x; const dY = e.clientY - previousMousePosition.y; const rX = dY * config.mouseDragSensitivity; const rY = dX * config.mouseDragSensitivity; cardMesh.rotation.x += rX; cardMesh.rotation.y += rY; cardMesh.rotation.z = 0; rotationVelocity.x = rX; rotationVelocity.y = rY; previousMousePosition.x = e.clientX; previousMousePosition.y = e.clientY; }
        function normAng(a) { while(a > Math.PI) a -= 2*Math.PI; while(a < -Math.PI) a += 2*Math.PI; return a; }
        function animate() { requestAnimationFrame(animate); const deltaTime = clock.getDelta(); oscillationTime += deltaTime * config.oscillationSpeedFactor; if (cardMesh) { if (!isDragging) { const angleInRadians = THREE.MathUtils.degToRad(config.oscillationAngleMaxDeg); targetReturnRotation.y = Math.sin(oscillationTime) * angleInRadians; targetReturnRotation.x = 0;  targetReturnRotation.z = 0; cardMesh.rotation.x += rotationVelocity.x; cardMesh.rotation.y += rotationVelocity.y; rotationVelocity.x *= config.dampingFactor; rotationVelocity.y *= config.dampingFactor; let diffX = normAng(targetReturnRotation.x - cardMesh.rotation.x); let diffY = normAng(targetReturnRotation.y - cardMesh.rotation.y); let diffZ = normAng(targetReturnRotation.z - cardMesh.rotation.z); const pullFactor = config.returnToPoseSpeed; cardMesh.rotation.x += diffX * pullFactor; cardMesh.rotation.y += diffY * pullFactor; cardMesh.rotation.z += diffZ * pullFactor; const isMovingSlowly = Math.abs(rotationVelocity.x) < minInertialVelocity && Math.abs(rotationVelocity.y) < minInertialVelocity; let curDiffX = normAng(targetReturnRotation.x - cardMesh.rotation.x); let curDiffY = normAng(targetReturnRotation.y - cardMesh.rotation.y); let curDiffZ = normAng(targetReturnRotation.z - cardMesh.rotation.z); const isAtTarget = Math.abs(curDiffX) < returnProximityThreshold && Math.abs(curDiffY) < returnProximityThreshold && Math.abs(curDiffZ) < returnProximityThreshold; if (isMovingSlowly && isAtTarget) { cardMesh.rotation.copy(targetReturnRotation); rotationVelocity.set(0,0); } } } renderer.render(scene, camera); }
        function onWindowResize() { const cDiv=document.getElementById('controls'); const rH=window.innerHeight-cDiv.offsetHeight; camera.aspect=window.innerWidth/rH; camera.updateProjectionMatrix(); renderer.setSize(window.innerWidth,rH); }


        function init() {
            scene = new THREE.Scene();
            const ctrlDiv = document.getElementById('controls');
            // Initial dynamic height calculation for renderer if controls div has dynamic content height
            const rendH = window.innerHeight; // Start with full height
            camera = new THREE.PerspectiveCamera(75, window.innerWidth / rendH, 0.1, 1000);
            camera.position.z = cardHeight * 1.35;
            renderer = new THREE.WebGLRenderer({ antialias: true });
            renderer.setSize(window.innerWidth, rendH); // Initial size
            renderer.setPixelRatio(window.devicePixelRatio);
            document.getElementById('container').appendChild(renderer.domElement);
            scene.add(new THREE.AmbientLight(0xffffff, 0.8));
            const dirLight = new THREE.DirectionalLight(0xffffff, 0.9);
            dirLight.position.set(2, 3, 5); scene.add(dirLight);
            
            randomizeInitialText(); 
            setupControls(); // Call this to initialize all controls
            createCard(); 
            animate();

            // Adjust renderer size after controls are fully rendered (if fixed height, not strictly needed here)
            // For a fixed position panel, its height doesn't directly subtract from canvas area this way.
            // The canvas will take full body space behind the fixed panel.
            // So onWindowResize only needs to use window.innerWidth/Height.
            // The previous subtraction was for a panel *in flow* above the canvas.
            
            // Corrected onWindowResize for fixed panel:
            window.addEventListener('resize', () => {
                 camera.aspect = window.innerWidth / window.innerHeight;
                 camera.updateProjectionMatrix();
                 renderer.setSize(window.innerWidth, window.innerHeight);
            }, false);
            // Trigger resize once to set initial size based on window
            window.dispatchEvent(new Event('resize'));


            renderer.domElement.addEventListener('mousedown', onMouseDown);
            window.addEventListener('mouseup', onMouseUp);
            window.addEventListener('mousemove', onMouseMove);
            document.getElementById('randomizeButton').addEventListener('click', handleRandomizeCard);
        }
        init();
    </script>
</body>
</html>