<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Oscillating 3D Card</title>
    <style>
        body { margin: 0; overflow: hidden; background-color: #282828; color: #fff; font-family: Arial, sans-serif; display: flex; flex-direction: column; align-items: center; height: 100vh; }
        #controls { padding: 10px; background-color: rgba(0,0,0,0.3); border-radius: 0 0 5px 5px; margin-bottom: 5px; display: flex; flex-direction: column; align-items: center; gap: 5px; font-size: 0.85em;}
        .slider-container { display: flex; align-items: center; gap: 8px; width: 100%; max-width: 350px; }
        .slider-container label { min-width: 120px; text-align: right; }
        .slider-container input[type="range"] { flex-grow: 1; }
        .slider-container span { min-width: 45px; text-align: left; }
        #randomizeButton { padding: 8px 15px; font-size: 0.95em; cursor: pointer; background-color: #4CAF50; color: white; border: none; border-radius: 4px; margin-top: 8px; }
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
            <input type="range" id="sensitivitySlider" min="0.001" max="0.02" step="0.001">
            <span id="sensitivityValue">0.000</span>
        </div>
        <div class="slider-container">
            <label for="dampingSlider">Spin Damping:</label>
            <input type="range" id="dampingSlider" min="0.80" max="0.99" step="0.01">
            <span id="dampingValue">0.00</span>
        </div>
        <div class="slider-container">
            <label for="returnSpeedSlider">Return Easing:</label> <!-- Renamed for clarity -->
            <input type="range" id="returnSpeedSlider" min="0.01" max="0.2" step="0.01">
            <span id="returnSpeedValue">0.00</span>
        </div>
        <hr style="width:80%; border-color: #555;">
        <div class="slider-container">
            <label for="oscAngleSlider">Osc. Angle (°):</label>
            <input type="range" id="oscAngleSlider" min="10" max="80" step="1"> <!-- Degrees for easier use -->
            <span id="oscAngleValue">0</span>
        </div>
        <div class="slider-container">
            <label for="oscSpeedSlider">Osc. Speed:</label>
            <input type="range" id="oscSpeedSlider" min="0.1" max="2.0" step="0.05">
            <span id="oscSpeedValue">0.00</span>
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
        let oscillationTime = 0;

        const cardWidth = 2.5, cardHeight = 3.5, cardDepth = 0.05;
        const textureCanvasWidth = 512, textureCanvasHeight = Math.floor(textureCanvasWidth*(cardHeight/cardWidth));
        const pNames = ["Star Slugger", "Cosmic Ace", "Nova Knight", "Galaxy Gladiator", "Quantum Quasar"], pPos = ["Pitcher", "Catcher", "Shortstop", "Center Field", "Right Field"];
        let curName = pNames[0], curPos = pPos[0];
        const cFrontBg = '#EFEFEF', cBackBg = '#8FBC8F', cEdge = '#AAAAAA', cText = '#111111'; // DarkSeaGreen

        const config = {
            mouseDragSensitivity: 0.005,
            dampingFactor: 0.97,
            returnToPoseSpeed: 0.05, // This is the "easing rate" for returning
            oscillationAngleMaxDeg: 45, // Max angle in degrees
            oscillationSpeedFactor: 0.5 // Multiplier for time in oscillation
        };

        const minInertialVelocity = 0.00001, returnProximityThreshold = 0.005;
        let nextImgObj = null, isPreloading = false;
        const clock = new THREE.Clock(); // For delta time

        function setupSliders() {
            const sliderConfigs = {
                sens: {el: 'sensitivitySlider', val: 'sensitivityValue', prop: 'mouseDragSensitivity', target: config, fix: 3},
                damp: {el: 'dampingSlider', val: 'dampingValue', prop: 'dampingFactor', target: config, fix: 2},
                ret: {el: 'returnSpeedSlider', val: 'returnSpeedValue', prop: 'returnToPoseSpeed', target: config, fix: 2},
                oscAng: {el: 'oscAngleSlider', val: 'oscAngleValue', prop: 'oscillationAngleMaxDeg', target: config, fix: 0}, // Degrees
                oscSpd: {el: 'oscSpeedSlider', val: 'oscSpeedValue', prop: 'oscillationSpeedFactor', target: config, fix: 2}
            };
            for (const key in sliderConfigs) {
                const sc = sliderConfigs[key];
                const slider = document.getElementById(sc.el);
                const display = document.getElementById(sc.val);
                let initialValue = sc.target[sc.prop];
                
                slider.value = initialValue; 
                display.textContent = parseFloat(initialValue).toFixed(sc.fix);
                slider.addEventListener('input', (e) => {
                    const val = parseFloat(e.target.value);
                    sc.target[sc.prop] = val;
                    display.textContent = val.toFixed(sc.fix);
                });
            }
        }
        // --- Other helper functions (randomizeTextData, genPicURL, startPreload, drawToCanv, updCardTex, createCard, handleRandomizeCard) ---
        // These remain the same as the corrected version from the "NaN fix".
        // For brevity, I'm omitting them here.
        function randomizeTextData() { curName = pNames[Math.floor(Math.random()*pNames.length)]; curPos = pPos[Math.floor(Math.random()*pPos.length)]; }
        function genPicURL() { return `https://picsum.photos/seed/${Date.now()+Math.random()}/${Math.round(textureCanvasWidth*1.5)}/${Math.round(textureCanvasHeight*1.5)}`;}
        function startPreload() { if(isPreloading||nextImgObj)return; isPreloading=true; const pImg=new Image();pImg.crossOrigin="Anonymous"; pImg.onload=()=>{nextImgObj=pImg;isPreloading=false;};pImg.onerror=()=>{isPreloading=false;nextImgObj=null;};pImg.src=genPicURL();}
        function drawToCanv(ctx,img){ctx.fillStyle=cFrontBg;ctx.fillRect(0,0,ctx.canvas.width,ctx.canvas.height);const iARat=0.7;const iAH=ctx.canvas.height*iARat;if(img){const imgAR=img.width/img.height;const areaAR=ctx.canvas.width/iAH;let sx=0,sy=0,sW=img.width,sH=img.height,dx=0,dy=0,dW=ctx.canvas.width,dH=iAH;if(imgAR>areaAR){sW=sH*areaAR;sx=(img.width-sW)/2;}else{sH=sW/areaAR;sy=(img.height-sH)/2;}ctx.drawImage(img,sx,sy,sW,sH,dx,dy,dW,dH);}else{ctx.fillStyle='darkred';ctx.fillRect(0,0,ctx.canvas.width,iAH);ctx.fillStyle='white';ctx.font=`bold ${Math.floor(textureCanvasHeight*0.05)}px Arial`;ctx.textAlign='center';ctx.textBaseline='middle';ctx.fillText("Img Err",ctx.canvas.width/2,iAH/2);}ctx.fillStyle=cText;ctx.textAlign='center';ctx.textBaseline='middle';const tAY=iAH;const tAH=ctx.canvas.height-iAH;const nFS=Math.floor(textureCanvasHeight*0.06);ctx.font=`bold ${nFS}px Arial`;ctx.fillText(curName,ctx.canvas.width/2,tAY+tAH*0.35);const pFS=Math.floor(textureCanvasHeight*0.04);ctx.font=`normal ${pFS}px Arial`;ctx.fillText(curPos,ctx.canvas.width/2,tAY+tAH*0.70);}
        function updCardTex(cb){randomizeTextData();const canv=document.createElement('canvas');canv.width=textureCanvasWidth;canv.height=textureCanvasHeight;const ctx=canv.getContext('2d');const pCb=(img)=>{drawToCanv(ctx,img);const tex=new THREE.CanvasTexture(canv);tex.needsUpdate=true;cb(tex);if(!nextImgObj&&!isPreloading)startPreload();};if(nextImgObj){const iUse=nextImgObj;nextImgObj=null;startPreload();pCb(iUse);}else{const img=new Image();img.crossOrigin="Anonymous";img.onload=()=>pCb(img);img.onerror=()=>{pCb(null);};img.src=genPicURL();}}
        function createCard(){const geo=new THREE.BoxGeometry(cardWidth,cardHeight,cardDepth);const eMat=new THREE.MeshStandardMaterial({color:cEdge,roughness:0.8,metalness:0.2});const bMat=new THREE.MeshStandardMaterial({color:cBackBg,roughness:0.6,metalness:0.1});updCardTex(fTex=>{const fMat=new THREE.MeshStandardMaterial({map:fTex,roughness:0.7,metalness:0.1});cardMesh=new THREE.Mesh(geo,[eMat,eMat,eMat,eMat,fMat,bMat]);targetReturnRotation.copy(cardMesh.rotation);scene.add(cardMesh);if(!nextImgObj&&!isPreloading)startPreload();});}
        function handleRandomizeCard(){if(!cardMesh||!cardMesh.material[4])return;const fM=cardMesh.material[4];const btn=document.getElementById('randomizeButton');btn.disabled=true;btn.textContent="Loading...";updCardTex(nTex=>{if(fM.map)fM.map.dispose();fM.map=nTex;fM.needsUpdate=true;btn.disabled=false;btn.textContent="Randomize Card";});}


        function onMouseDown(e) {
            if (e.target === renderer.domElement && cardMesh) {
                isDragging = true;
                rotationVelocity.set(0,0);
                // targetReturnRotation will continue to update based on oscillation
                previousMousePosition.x = e.clientX;
                previousMousePosition.y = e.clientY;
            }
        }
        function onMouseUp() {
            isDragging = false;
        }

        function onMouseMove(e) {
            if (!isDragging || !cardMesh) return;
            const dX = e.clientX - previousMousePosition.x;
            const dY = e.clientY - previousMousePosition.y;
            const rX = dY * config.mouseDragSensitivity;
            const rY = dX * config.mouseDragSensitivity;
            cardMesh.rotation.x += rX;
            cardMesh.rotation.y += rY;
            cardMesh.rotation.z = 0;
            rotationVelocity.x = rX;
            rotationVelocity.y = rY;
            previousMousePosition.x = e.clientX;
            previousMousePosition.y = e.clientY;
        }

        function normAng(a) { while(a > Math.PI) a -= 2*Math.PI; while(a < -Math.PI) a += 2*Math.PI; return a; }

        function animate() {
            requestAnimationFrame(animate);
            const deltaTime = clock.getDelta(); // Get time since last frame
            oscillationTime += deltaTime * config.oscillationSpeedFactor; // Accumulate time scaled by speed factor

            if (cardMesh) {
                if (!isDragging) {
                    // 1. Update targetReturnRotation based on oscillation
                    const angleInRadians = THREE.MathUtils.degToRad(config.oscillationAngleMaxDeg);
                    targetReturnRotation.y = Math.sin(oscillationTime) * angleInRadians;
                    targetReturnRotation.x = 0; // Keep card upright
                    targetReturnRotation.z = 0; // Keep card from tilting side-to-side

                    // 2. Apply inertial rotation from drag
                    cardMesh.rotation.x += rotationVelocity.x;
                    cardMesh.rotation.y += rotationVelocity.y;

                    // 3. Dampen inertia
                    rotationVelocity.x *= config.dampingFactor;
                    rotationVelocity.y *= config.dampingFactor;

                    // 4. Spring towards the oscillating targetReturnRotation
                    let diffX = normAng(targetReturnRotation.x - cardMesh.rotation.x);
                    let diffY = normAng(targetReturnRotation.y - cardMesh.rotation.y);
                    let diffZ = normAng(targetReturnRotation.z - cardMesh.rotation.z);

                    cardMesh.rotation.x += diffX * config.returnToPoseSpeed;
                    cardMesh.rotation.y += diffY * config.returnToPoseSpeed;
                    cardMesh.rotation.z += diffZ * config.returnToPoseSpeed;

                    // 5. Handle "settled" state (snap if very close and slow)
                    const isMovingSlowly = Math.abs(rotationVelocity.x) < minInertialVelocity &&
                                           Math.abs(rotationVelocity.y) < minInertialVelocity;
                    
                    // Check proximity to current oscillation target
                    let currentDiffX = normAng(targetReturnRotation.x - cardMesh.rotation.x);
                    let currentDiffY = normAng(targetReturnRotation.y - cardMesh.rotation.y);
                    let currentDiffZ = normAng(targetReturnRotation.z - cardMesh.rotation.z);

                    const isAtTarget = Math.abs(currentDiffX) < returnProximityThreshold &&
                                       Math.abs(currentDiffY) < returnProximityThreshold &&
                                       Math.abs(currentDiffZ) < returnProximityThreshold;

                    if (isMovingSlowly && isAtTarget) {
                        cardMesh.rotation.copy(targetReturnRotation); // Snap to the current oscillation point
                        rotationVelocity.set(0,0);
                    }
                }
            }
            renderer.render(scene, camera);
        }

        function onWindowResize() {
            const cDiv=document.getElementById('controls');
            const rH=window.innerHeight-cDiv.offsetHeight;
            camera.aspect=window.innerWidth/rH;
            camera.updateProjectionMatrix();
            renderer.setSize(window.innerWidth,rH);
        }

        function init() {
            scene = new THREE.Scene();
            const ctrlDiv = document.getElementById('controls');
            const rendH = window.innerHeight - ctrlDiv.offsetHeight;
            camera = new THREE.PerspectiveCamera(75, window.innerWidth / rendH, 0.1, 1000);
            camera.position.z = cardHeight * 1.35;

            renderer = new THREE.WebGLRenderer({ antialias: true });
            renderer.setSize(window.innerWidth, rendH);
            renderer.setPixelRatio(window.devicePixelRatio);
            document.getElementById('container').appendChild(renderer.domElement);

            scene.add(new THREE.AmbientLight(0xffffff, 0.8));
            const dirLight = new THREE.DirectionalLight(0xffffff, 0.9);
            dirLight.position.set(2, 3, 5); scene.add(dirLight);
            
            setupSliders();
            createCard();
            animate();

            window.addEventListener('resize', onWindowResize);
            renderer.domElement.addEventListener('mousedown', onMouseDown);
            window.addEventListener('mouseup', onMouseUp);
            window.addEventListener('mousemove', onMouseMove);
            document.getElementById('randomizeButton').addEventListener('click', handleRandomizeCard);
        }

        init();
    </script>
</body>
</html>