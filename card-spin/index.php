<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Polished Preset 3D Card</title>
    <style>
        body { margin: 0; overflow: hidden; background-color: #282828; color: #fff; font-family: Arial, sans-serif; }
        #container { width: 100vw; height: 100vh; display: flex; justify-content: center; align-items: center; }
        canvas { display: block; }

        .panel { position: fixed; top: 10px; width: 330px; max-height: calc(100vh - 20px);
            overflow-y: auto; background-color: rgba(40, 40, 40, 0.92); padding: 15px;
            border-radius: 8px; box-shadow: 0 0 15px rgba(0,0,0,0.5); display: flex;
            flex-direction: column; gap: 8px; font-size: 0.9em; z-index: 100;
            scrollbar-width: thin; scrollbar-color: #666 #333;
        }
        .panel::-webkit-scrollbar { width: 8px; }
        .panel::-webkit-scrollbar-track { background: #333; border-radius: 4px;}
        .panel::-webkit-scrollbar-thumb { background-color: #666; border-radius: 4px; border: 2px solid #333; }

        #controls { right: 10px; align-items: center; }
        #presetsPanel { left: 10px; align-items: stretch; }

        .control-group-label, .panel-title {
            font-weight: bold; margin-top: 10px; margin-bottom: 3px; color: #ddd;
            width: 100%; text-align: left; border-bottom: 1px solid #555; padding-bottom: 4px;
        }
        .panel-title { text-align: center; margin-bottom: 10px;}

        .control-row { display: flex; align-items: center; gap: 8px; width: 100%; margin-bottom: 3px; }
        .control-row label { min-width: 120px; text-align: right; color: #ccc; font-size:0.95em; }
        .control-row input[type="range"], .control-row select, .control-row input[type="text"], .control-row input[type="color"] { 
            flex-grow: 1; background-color: #555; color: #fff;
            border: 1px solid #777; border-radius: 3px; padding: 4px; box-sizing: border-box;
        }
        .control-row input[type="color"] { padding: 1px 2px; min-height: 28px; }
        .control-row span { min-width: 45px; text-align: left; color: #fff; }
        
        .panel-button { padding: 10px 15px; font-size: 0.95em; cursor: pointer; 
            background-color: #007bff; color: white; border: none; 
            border-radius: 4px; margin-top: 10px; width: 100%;
            box-sizing: border-box; text-align: center;
        }
        .panel-button:hover { background-color: #0056b3; }
        .panel-button:disabled { background-color: #777; cursor: default; }
        #randomizeButton { background-color: #28a745; }
        #randomizeButton:hover { background-color: #1e7e34; }
        #clearPresetsButton { background-color: #dc3545; margin-top: 5px;}
        #clearPresetsButton:hover { background-color: #c82333; }
        
        #presetsList { list-style: none; padding: 0; margin: 0; width: 100%; }
        #presetsList li { background-color: #383838; padding: 8px; margin-bottom: 5px; border-radius: 4px;
            display: flex; justify-content: space-between; align-items: center; cursor: pointer;
            transition: background-color 0.2s ease; /* Smooth transition for hover/active */
        }
        #presetsList li:hover { background-color: #4a4a4a; }
        #presetsList li.preset-item-active { /* Highlight for active preset */
            background-color: #007bff;
            color: white;
        }
        #presetsList li.preset-item-active:hover { background-color: #0056b3; }

        #presetsList .preset-name { flex-grow: 1; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;}
        #presetsList .delete-preset-btn { background-color: #c0392b; color: white; border: none; padding: 4px 8px;
            border-radius: 3px; cursor: pointer; margin-left: 10px; font-size: 0.8em;
        }
        #presetsList .delete-preset-btn:hover { background-color: #e74c3c; }
    </style>
</head>
<body>
    <div id="presetsPanel" class="panel">
        <div class="panel-title">Presets</div>
        <ul id="presetsList"></ul>
        <button id="savePresetButton" class="panel-button">Save Current as Preset</button>
        <button id="clearPresetsButton" class="panel-button">Clear All Presets</button>
    </div>

    <div id="controls" class="panel">
        <!-- Control groups and rows ... (same as previous version) -->
        <div class="control-group-label">Card Content</div>
        <div class="control-row"><label for="imageSelect">Image:</label><select id="imageSelect"></select></div>
        <div class="control-row"><label for="playerNameInput">Player Name:</label><input type="text" id="playerNameInput"></div>
        <div class="control-row"><label for="playerPositionInput">Position:</label><input type="text" id="playerPositionInput"></div>
        <div class="control-row"><label for="cardBgColorPicker">Card BG Color:</label><input type="color" id="cardBgColorPicker"></div>

        <div class="control-group-label">Player Name Styling</div>
        <div class="control-row"><label for="nameFontFamilySelect">Font Family:</label><select id="nameFontFamilySelect"></select></div>
        <div class="control-row"><label for="nameFontSizeSlider">Font Size (factor):</label><input type="range" id="nameFontSizeSlider" min="0.03" max="0.10" step="0.001"><span id="nameFontSizeValue">0.000</span></div>
        <div class="control-row"><label for="nameLetterSpacingSlider">Letter Spacing (px):</label><input type="range" id="nameLetterSpacingSlider" min="-2" max="10" step="0.5"><span id="nameLetterSpacingValue">0.0</span></div>
        <div class="control-row"><label for="nameFontColorPicker">Font Color:</label><input type="color" id="nameFontColorPicker"></div>
        <div class="control-row"><label for="nameTextOffsetYSlider">Y Offset (px):</label><input type="range" id="nameTextOffsetYSlider" min="-50" max="50" step="1"><span id="nameTextOffsetYValue">0</span></div>

        <div class="control-group-label">Position Styling</div>
        <div class="control-row"><label for="posFontFamilySelect">Font Family:</label><select id="posFontFamilySelect"></select></div>
        <div class="control-row"><label for="posFontSizeSlider">Font Size (factor):</label><input type="range" id="posFontSizeSlider" min="0.02" max="0.08" step="0.001"><span id="posFontSizeValue">0.000</span></div>
        <div class="control-row"><label for="posLetterSpacingSlider">Letter Spacing (px):</label><input type="range" id="posLetterSpacingSlider" min="-2" max="10" step="0.5"><span id="posLetterSpacingValue">0.0</span></div>
        <div class="control-row"><label for="posFontColorPicker">Font Color:</label><input type="color" id="posFontColorPicker"></div>
        <div class="control-row"><label for="posTextOffsetYSlider">Y Offset (px):</label><input type="range" id="posTextOffsetYSlider" min="-50" max="50" step="1"><span id="posTextOffsetYValue">0</span></div>
        
        <div class="control-group-label">Interaction Physics</div>
        <div class="control-row"><label for="sensitivitySlider">Drag Sensitivity:</label><input type="range" id="sensitivitySlider" min="0.001" max="0.02" step="0.001"><span id="sensitivityValue">0.000</span></div>
        <div class="control-row"><label for="dampingSlider">Spin Damping:</label><input type="range" id="dampingSlider" min="0.80" max="0.99" step="0.01"><span id="dampingValue">0.00</span></div>
        <div class="control-row"><label for="returnSpeedSlider">Return Easing Rate:</label><input type="range" id="returnSpeedSlider" min="0.01" max="0.2" step="0.01"><span id="returnSpeedValue">0.00</span></div>
        <div class="control-row"><label for="easingFunctionSelect">Return Easing Algo:</label><select id="easingFunctionSelect"><optgroup label="Standard"><option value="linear">Linear</option></optgroup><optgroup label="Quadratic"><option value="easeInQuad">EaseInQuad</option><option value="easeOutQuad">EaseOutQuad</option><option value="easeInOutQuad">EaseInOutQuad</option></optgroup><optgroup label="Cubic"><option value="easeInCubic">EaseInCubic</option><option value="easeOutCubic" selected>EaseOutCubic</option><option value="easeInOutCubic">EaseInOutCubic</option></optgroup><optgroup label="Quart_Quint"><option value="easeOutQuart">EaseOutQuart</option><option value="easeOutQuint">EaseOutQuint</option></optgroup><optgroup label="Sine_Expo_Circ"><option value="easeOutSine">EaseOutSine</option><option value="easeOutExpo">EaseOutExpo</option><option value="easeOutCirc">EaseOutCirc</option></optgroup><optgroup label="Special"><option value="easeOutBack">EaseOutBack</option></optgroup></select></div>
        
        <div class="control-group-label">Card Oscillation</div>
        <div class="control-row"><label for="oscAngleSlider">Osc. Angle (°):</label><input type="range" id="oscAngleSlider" min="10" max="80" step="1"><span id="oscAngleValue">0</span></div>
        <div class="control-row"><label for="oscSpeedSlider">Osc. Speed:</label><input type="range" id="oscSpeedSlider" min="0.1" max="2.0" step="0.05"><span id="oscSpeedValue">0.00</span></div>
        
        <button id="randomizeButton" class="panel-button" style="width: auto; padding: 10px 18px;">Randomize Card</button>
    </div>
    <div id="container"></div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    <script>
        let scene, camera, renderer, cardMesh;
        let isDragging = false, previousMousePosition = { x: 0, y: 0 }, rotationVelocity = new THREE.Vector2(0, 0);
        let targetReturnRotation = new THREE.Euler(0, 0, 0, 'YXZ'), oscillationTime = 0;

        const cardWidth = 2.5, cardHeight = 3.5, cardDepth = 0.05;
        const textureCanvasWidth = 512, textureCanvasHeight = Math.floor(textureCanvasWidth*(cardHeight/cardWidth));
        
        let currentPlayerName = "Cosmic Ace", currentPlayerPosition = "Shortstop";
        const defaultNames = ["Star Player", "Cosmic Ace", "Nova Knight", "Galaxy Gladiator", "Quantum Striker", "Celestial Guard"];
        const defaultPositions = ["Pitcher", "Catcher", "Outfielder", "Midfielder", "Point Guard", "Goalie"];

        const predefinedImages = [ /* ... same 10 ... */ {name:"Basketball Player",url:"https://picsum.photos/seed/bball1/400/600"},{name:"Soccer Kick",url:"https://picsum.photos/seed/soccer2/400/600"},{name:"Runner Sprinting",url:"https://picsum.photos/seed/run3/400/600"},{name:"Swimmer Mid-Stroke",url:"https://picsum.photos/seed/swim4/400/600"},{name:"Tennis Serve",url:"https://picsum.photos/seed/tennis5/400/600"},{name:"Skateboarder Trick",url:"https://picsum.photos/seed/skate6/400/600"},{name:"Volleyball Spike",url:"https://picsum.photos/seed/vball7/400/600"},{name:"Cyclist Uphill",url:"https://picsum.photos/seed/cycle8/400/600"},{name:"Hockey Goalie",url:"https://picsum.photos/seed/hockey9/400/600"},{name:"Baseball Pitcher",url:"https://picsum.photos/seed/baseb10/400/600"}];
        const availableFontFamilies = [ /* ... same 15 ... */ {name:"Arial",value:"Arial, sans-serif"},{name:"Verdana",value:"Verdana, sans-serif"},{name:"Georgia",value:"Georgia, serif"},{name:"Times New Roman",value:"Times New Roman, serif"},{name:"Courier New",value:"Courier New, monospace"},{name:"Impact",value:"Impact, sans-serif"},{name:"Comic Sans MS",value:"Comic Sans MS, cursive"},{name:"Arial Black",value:"'Arial Black', Gadget, sans-serif"},{name:"Tahoma",value:"Tahoma, Geneva, sans-serif"},{name:"Trebuchet MS",value:"'Trebuchet MS', Helvetica, sans-serif"},{name:"Lucida Sans Unicode",value:"'Lucida Sans Unicode', 'Lucida Grande', sans-serif"},{name:"Palatino Linotype",value:"'Palatino Linotype', 'Book Antiqua', Palatino, serif"},{name:"Garamond",value:"Garamond, Baskerville, 'Baskerville Old Face', 'Hoefler Text', 'Times New Roman', serif"},{name:"Brush Script MT",value:"'Brush Script MT', cursive"},{name:"Lucida Console",value:"'Lucida Console', Monaco, monospace"}];
        
        const CARD_EDGE_COLOR = '#AAAAAA', CARD_BACK_COLOR = '#6c757d';

        const config = { /* ... same with new position font properties ... */
            selectedImageURL: predefinedImages[0].url, cardFrontBgColor: '#EFEFEF',
            playerNameFontFamily: availableFontFamilies[0].value, playerNameFontSizeFactor: 0.06, playerNameLetterSpacing: 0,
            playerNameTextColor: '#111111', playerNameTextOffsetY: 0,
            playerPositionFontFamily: availableFontFamilies[0].value, playerPositionFontSizeFactor: 0.04, playerPositionLetterSpacing: 0,
            playerPositionTextColor: '#333333', playerPositionTextOffsetY: 0,
            mouseDragSensitivity: 0.005, dampingFactor: 0.97, returnToPoseSpeed: 0.05,
            oscillationAngleMaxDeg: 45, oscillationSpeedFactor: 0.5, easingFunction: 'easeOutCubic'
        };
        const threeSpecificConfig = { continuousRotationAxis: new THREE.Vector3(0,1,0) };

        const minInertialVelocity = 0.00001, returnProximityThreshold = 0.005;
        let currentCardImageObject = new Image(), isPreloading = false;
        const clock = new THREE.Clock();
        const EasingFunctions = { /* ... same ... */ linear:t=>t,easeInQuad:t=>t*t,easeOutQuad:t=>t*(2-t),easeInOutQuad:t=>t<.5?2*t*t:-1+(4-2*t)*t,easeInCubic:t=>t*t*t,easeOutCubic:t=>(--t)*t*t+1,easeInOutCubic:t=>t<.5?4*t*t*t:(t-1)*(2*t-2)*(2*t-2)+1,easeOutQuart:t=>1-(--t)*t*t*t,easeOutQuint:t=>1+(--t)*t*t*t*t,easeOutSine:t=>Math.sin(t*Math.PI/2),easeOutExpo:t=>t===1?1:1-Math.pow(2,-10*t),easeOutCirc:t=>Math.sqrt(1-(--t)*t),easeOutBack:t=>{const c1=1.70158;const c3=c1+1;return 1+c3*Math.pow(t-1,3)+c1*Math.pow(t-1,2);}};

        let controlElementsConfig = {};
        let activePresetIndex = -1; // For highlighting

        function populateFontDropdown(selectElementId) { /* ... same ... */ const sE=document.getElementById(selectElementId);sE.innerHTML='';availableFontFamilies.forEach(f=>{const o=document.createElement('option');o.value=f.value;o.textContent=f.name;sE.appendChild(o);});}

        function setupControls() {
            controlElementsConfig = { /* ... same with new position font controls ... */
                imageSelect: {elId: 'imageSelect', prop: 'selectedImageURL', target: config, type: 'select', updateAction: () => updateCardImageAndRedraw(config.selectedImageURL)},
                cardBgColor: {elId: 'cardBgColorPicker', prop: 'cardFrontBgColor', target: config, type: 'color', updateAction: redrawCardFace},
                pNameFontFamily: {elId: 'nameFontFamilySelect', prop: 'playerNameFontFamily', target: config, type: 'select', updateAction: redrawCardFace},
                pNameFontSize: {elId: 'nameFontSizeSlider', valId: 'nameFontSizeValue', prop: 'playerNameFontSizeFactor', target: config, fix: 3, type: 'range', updateAction: redrawCardFace},
                pNameLetterSpacing: {elId: 'nameLetterSpacingSlider', valId: 'nameLetterSpacingValue', prop: 'playerNameLetterSpacing', target: config, fix: 1, type: 'range', updateAction: redrawCardFace},
                pNameFontColor: {elId: 'nameFontColorPicker', prop: 'playerNameTextColor', target: config, type: 'color', updateAction: redrawCardFace},
                pNameTextOffsetY: {elId: 'nameTextOffsetYSlider', valId: 'nameTextOffsetYValue', prop: 'playerNameTextOffsetY', target: config, fix: 0, type: 'range', updateAction: redrawCardFace},
                pPosFontFamily: {elId: 'posFontFamilySelect', prop: 'playerPositionFontFamily', target: config, type: 'select', updateAction: redrawCardFace},
                pPosFontSize: {elId: 'posFontSizeSlider', valId: 'posFontSizeValue', prop: 'playerPositionFontSizeFactor', target: config, fix: 3, type: 'range', updateAction: redrawCardFace},
                pPosLetterSpacing: {elId: 'posLetterSpacingSlider', valId: 'posLetterSpacingValue', prop: 'playerPositionLetterSpacing', target: config, fix: 1, type: 'range', updateAction: redrawCardFace},
                pPosFontColor: {elId: 'posFontColorPicker', prop: 'playerPositionTextColor', target: config, type: 'color', updateAction: redrawCardFace},
                pPosTextOffsetY: {elId: 'posTextOffsetYSlider', valId: 'posTextOffsetYValue', prop: 'playerPositionTextOffsetY', target: config, fix: 0, type: 'range', updateAction: redrawCardFace},
                sens: {elId: 'sensitivitySlider', valId: 'sensitivityValue', prop: 'mouseDragSensitivity', target: config, fix: 3, type: 'range'},
                damp: {elId: 'dampingSlider', valId: 'dampingValue', prop: 'dampingFactor', target: config, fix: 2, type: 'range'},
                retSpd: {elId: 'returnSpeedSlider', valId: 'returnSpeedValue', prop: 'returnToPoseSpeed', target: config, fix: 2, type: 'range'},
                easingFunc: {elId: 'easingFunctionSelect', prop: 'easingFunction', target: config, type: 'select'},
                oscAng: {elId: 'oscAngleSlider', valId: 'oscAngleValue', prop: 'oscillationAngleMaxDeg', target: config, fix: 0, type: 'range'},
                oscSpd: {elId: 'oscSpeedSlider', valId: 'oscSpeedValue', prop: 'oscillationSpeedFactor', target: config, fix: 2, type: 'range'},
            };
            populateFontDropdown('nameFontFamilySelect'); populateFontDropdown('posFontFamilySelect');
            const imageSelectEl = document.getElementById('imageSelect'); predefinedImages.forEach(imgData => { const option = document.createElement('option'); option.value = imgData.url; option.textContent = imgData.name; imageSelectEl.appendChild(option); });
            for (const key in controlElementsConfig) { const cc = controlElementsConfig[key]; cc.element = document.getElementById(cc.elId); if (cc.valId) cc.displayElement = document.getElementById(cc.valId); let initialValue = cc.target[cc.prop]; cc.element.value = initialValue; if (cc.displayElement) cc.displayElement.textContent = parseFloat(initialValue).toFixed(cc.fix); const eventType = (cc.type === 'select' || cc.type === 'color') ? 'change' : 'input'; cc.element.addEventListener(eventType, (e) => { const val = (cc.type === 'select' || cc.type === 'color') ? e.target.value : parseFloat(e.target.value); cc.target[cc.prop] = val; if (cc.displayElement) cc.displayElement.textContent = ((cc.type === 'select' || cc.type === 'color') ? val : val.toFixed(cc.fix)); if (cc.updateAction) cc.updateAction(); activePresetIndex = -1; renderPresetsList(); /* Deselect preset on manual change */ }); }
            const pNameInput = document.getElementById('playerNameInput'); pNameInput.value = currentPlayerName; pNameInput.addEventListener('input', (e)=>{currentPlayerName=e.target.value; redrawCardFace(); activePresetIndex = -1; renderPresetsList();});
            const pPosInput = document.getElementById('playerPositionInput'); pPosInput.value = currentPlayerPosition; pPosInput.addEventListener('input', (e)=>{currentPlayerPosition=e.target.value; redrawCardFace(); activePresetIndex = -1; renderPresetsList();});
            document.getElementById('savePresetButton').addEventListener('click', handleSavePreset);
            document.getElementById('clearPresetsButton').addEventListener('click', handleClearAllPresets);
        }
        function updateControlsFromConfig() { /* ... same ... */ for(const k in controlElementsConfig){const c=controlElementsConfig[k];const v=c.target[c.prop];if(c.element){c.element.value=v;if(c.displayElement)c.displayElement.textContent=(c.type==='select'||c.type==='color'?v:parseFloat(v).toFixed(c.fix));}}document.getElementById('playerNameInput').value=currentPlayerName;document.getElementById('playerPositionInput').value=currentPlayerPosition;}
        const PRESETS_STORAGE_KEY = 'baseballCardPresets_v5'; let savedPresets = []; // Incremented version
        
        function renderPresetsList() { 
            const listElement = document.getElementById('presetsList');
            listElement.innerHTML = ''; 
            savedPresets.forEach((preset, index) => {
                const li = document.createElement('li');
                if (index === activePresetIndex) {
                    li.classList.add('preset-item-active');
                }
                const nameSpan = document.createElement('span');
                nameSpan.className = 'preset-name';
                nameSpan.textContent = preset.name;
                nameSpan.title = preset.name;
                nameSpan.addEventListener('click', () => applyPreset(index));
                li.appendChild(nameSpan);
                const deleteBtn = document.createElement('button');
                deleteBtn.className = 'delete-preset-btn';
                deleteBtn.textContent = 'X';
                deleteBtn.addEventListener('click', (e) => {e.stopPropagation(); if(confirm(`Delete "${preset.name}"?`))deletePreset(index);});
                li.appendChild(deleteBtn);
                listElement.appendChild(li);
            });
        }

        function loadPresets() { const s=localStorage.getItem(PRESETS_STORAGE_KEY);if(s)savedPresets=JSON.parse(s);renderPresetsList();}
        
        function handleSavePreset() {
            if (!currentPlayerName.trim()) { currentPlayerName = "Untitled Card"; } // Default if empty
            let presetName = currentPlayerName.trim();
            let originalName = presetName;
            let counter = 2;
            while (savedPresets.some(p => p.name === presetName)) {
                presetName = `${originalName} (${counter})`;
                counter++;
            }
            const newPreset = { name: presetName, settings: { config: JSON.parse(JSON.stringify(config)), texts: { playerName: currentPlayerName, playerPosition: currentPlayerPosition } } };
            savedPresets.push(newPreset);
            savedPresets.sort((a,b)=>a.name.localeCompare(b.name));
            activePresetIndex = savedPresets.findIndex(p => p.name === presetName); // Find new index
            localStorage.setItem(PRESETS_STORAGE_KEY, JSON.stringify(savedPresets));
            renderPresetsList();
        }

        function applyPreset(index) { 
            if(index<0||index>=savedPresets.length)return; 
            activePresetIndex = index; // Set active preset
            const preset=savedPresets[index]; const loadedConf=preset.settings.config; 
            for(const key in loadedConf){if(config.hasOwnProperty(key))config[key]=loadedConf[key];} 
            currentPlayerName=preset.settings.texts.playerName; 
            currentPlayerPosition=preset.settings.texts.playerPosition; 
            updateControlsFromConfig(); 
            updateCardImageAndRedraw(config.selectedImageURL);
            renderPresetsList(); // Re-render to show highlight

            // Preset apply animation
            if (cardMesh) {
                const originalScale = {x: cardMesh.scale.x, y: cardMesh.scale.y, z: cardMesh.scale.z }; // Store initial scale
                const popDuration = 0.25; // seconds
                const popScaleFactor = 1.1;
                let popStartTime = clock.getElapsedTime();

                function presetPopAnimation() {
                    const elapsedTime = clock.getElapsedTime() - popStartTime;
                    let progress = Math.min(elapsedTime / popDuration, 1);

                    if (progress < 1) {
                        const scale = originalScale.x + (popScaleFactor - 1) * originalScale.x * Math.sin(progress * Math.PI);
                        cardMesh.scale.set(scale, scale, scale);
                        requestAnimationFrame(presetPopAnimation);
                    } else {
                        cardMesh.scale.set(originalScale.x, originalScale.y, originalScale.z); // Reset
                    }
                }
                requestAnimationFrame(presetPopAnimation);
            }
        }
        function deletePreset(index) { if(index<0||index>=savedPresets.length)return;savedPresets.splice(index,1); if(activePresetIndex === index) activePresetIndex = -1; else if (activePresetIndex > index) activePresetIndex--; localStorage.setItem(PRESETS_STORAGE_KEY,JSON.stringify(savedPresets));renderPresetsList();}
        function handleClearAllPresets() { if(confirm("Delete ALL presets?")){savedPresets=[];activePresetIndex=-1;localStorage.removeItem(PRESETS_STORAGE_KEY);renderPresetsList();}}
        function randomizeInitialPlayerText() { currentPlayerName = defaultNames[Math.floor(Math.random()*defaultNames.length)]; currentPlayerPosition = defaultPositions[Math.floor(Math.random()*defaultPositions.length)];}
        
        function _drawContentToCanvas(ctx, imageToDraw) { /* ... same with new position font props ... */
            ctx.fillStyle = config.cardFrontBgColor; 
            ctx.fillRect(0, 0, ctx.canvas.width, ctx.canvas.height);
            const iARat = 0.7; const iAH = ctx.canvas.height * iARat;
            if (imageToDraw && imageToDraw.complete && imageToDraw.naturalHeight !== 0) { const imgAR=imageToDraw.width/imageToDraw.height;const areaAR=ctx.canvas.width/iAH;let sx=0,sy=0,sW=imageToDraw.width,sH=imageToDraw.height,dx=0,dy=0,dW=ctx.canvas.width,dH=iAH;if(imgAR>areaAR){sW=sH*areaAR;sx=(imageToDraw.width-sW)/2;}else{sH=sW/areaAR;sy=(imageToDraw.height-sH)/2;}ctx.drawImage(imageToDraw,sx,sy,sW,sH,dx,dy,dW,dH);}
            else { ctx.fillStyle='grey';ctx.fillRect(0,0,ctx.canvas.width,iAH);ctx.fillStyle='white';ctx.font=`bold ${Math.floor(textureCanvasHeight*0.04)}px Arial`;ctx.textAlign='center';ctx.textBaseline='middle';ctx.fillText(config.selectedImageURL?"Loading...":"No Image",ctx.canvas.width/2,iAH/2);}
            ctx.textAlign = 'center'; ctx.textBaseline = 'middle';
            const tAYBase = iAH; const tAH = ctx.canvas.height - iAH;
            const nameFontSize = Math.floor(textureCanvasHeight * config.playerNameFontSizeFactor);
            ctx.fillStyle = config.playerNameTextColor; 
            ctx.font = `bold ${nameFontSize}px ${config.playerNameFontFamily}`;
            ctx.letterSpacing = `${config.playerNameLetterSpacing}px`;
            const nameY = tAYBase + (tAH * 0.35) + config.playerNameTextOffsetY;
            ctx.fillText(currentPlayerName, ctx.canvas.width / 2, nameY);
            const posFontSize = Math.floor(textureCanvasHeight * config.playerPositionFontSizeFactor);
            ctx.fillStyle = config.playerPositionTextColor;
            ctx.font = `normal ${posFontSize}px ${config.playerPositionFontFamily}`;
            ctx.letterSpacing = `${config.playerPositionLetterSpacing}px`;
            const posY = tAYBase + (tAH * 0.70) + config.playerPositionTextOffsetY;
            ctx.fillText(currentPlayerPosition, ctx.canvas.width / 2, posY);
            ctx.letterSpacing = '0px';
        }
        
        let isInitialTextureReady = false;
        function redrawCardFace() { /* ... same ... */ if (!cardMesh || !cardMesh.material[4] || !isInitialTextureReady) return; const frontMaterial = cardMesh.material[4]; const canvas = document.createElement('canvas'); canvas.width = textureCanvasWidth; canvas.height = textureCanvasHeight; const ctx = canvas.getContext('2d'); _drawContentToCanvas(ctx, currentCardImageObject); if (frontMaterial.map) frontMaterial.map.dispose(); frontMaterial.map = new THREE.CanvasTexture(canvas); frontMaterial.map.needsUpdate = true; frontMaterial.needsUpdate = true; }
        function updateCardImageAndRedraw(imageURL) { /* ... same ... */ const img = currentCardImageObject; img.crossOrigin = "Anonymous"; let needsRedrawForLoading = false; img.onload = () => { config.selectedImageURL = imageURL; redrawCardFace(); }; img.onerror = (e) => { console.error("Error loading image:", imageURL, e); redrawCardFace(); }; if (img.src !== imageURL || !img.complete) { img.src = imageURL; needsRedrawForLoading = true; } else if (img.complete && img.naturalHeight !== 0) { redrawCardFace(); return; } if (needsRedrawForLoading && cardMesh && cardMesh.material[4]) { redrawCardFace(); }}
        function createCard(){ /* ... same ... */ randomizeInitialPlayerText(); const initialImageURL = config.selectedImageURL;const geo=new THREE.BoxGeometry(cardWidth,cardHeight,cardDepth); const eMat=new THREE.MeshStandardMaterial({color: CARD_EDGE_COLOR, roughness:0.8, metalness:0.2}); const bMat=new THREE.MeshStandardMaterial({color: CARD_BACK_COLOR, roughness:0.6, metalness:0.1});const tempCanvas = document.createElement('canvas'); tempCanvas.width = textureCanvasWidth; tempCanvas.height = textureCanvasHeight; const tempCtx = tempCanvas.getContext('2d'); tempCtx.fillStyle = config.cardFrontBgColor; tempCtx.fillRect(0,0,tempCanvas.width, tempCanvas.height); tempCtx.fillStyle = '#555'; tempCtx.textAlign='center';tempCtx.textBaseline='middle';tempCtx.font='20px Arial'; tempCtx.fillText('Initializing Card...', tempCanvas.width/2, tempCanvas.height/2); const initialFrontTexture = new THREE.CanvasTexture(tempCanvas);const fMat=new THREE.MeshStandardMaterial({map:initialFrontTexture,roughness:0.7,metalness:0.1}); cardMesh=new THREE.Mesh(geo,[eMat,eMat,eMat,eMat,fMat,bMat]); targetReturnRotation.copy(cardMesh.rotation); scene.add(cardMesh);isInitialTextureReady = true; updateCardImageAndRedraw(initialImageURL);}

        function handleRandomizeCard(){ 
            if(!cardMesh) return;
            randomizeInitialPlayerText(); 
            config.selectedImageURL = predefinedImages[Math.floor(Math.random() * predefinedImages.length)].url;
            config.cardFrontBgColor = `rgb(${Math.floor(Math.random()*200+55)}, ${Math.floor(Math.random()*200+55)}, ${Math.floor(Math.random()*200+55)})`;
            config.playerNameFontFamily = availableFontFamilies[Math.floor(Math.random() * availableFontFamilies.length)].value;
            config.playerNameFontSizeFactor = 0.04 + Math.random() * 0.05;
            config.playerNameLetterSpacing = Math.floor(Math.random() * 6 - 2);
            config.playerNameTextColor = `rgb(${Math.floor(Math.random()*128)}, ${Math.floor(Math.random()*128)}, ${Math.floor(Math.random()*128)})`;
            config.playerNameTextOffsetY = Math.floor(Math.random() * 60 - 30);
            config.playerPositionFontFamily = availableFontFamilies[Math.floor(Math.random() * availableFontFamilies.length)].value;
            config.playerPositionFontSizeFactor = 0.03 + Math.random() * 0.03;
            config.playerPositionLetterSpacing = Math.floor(Math.random() * 4 - 1);
            config.playerPositionTextColor = `rgb(${Math.floor(Math.random()*128)}, ${Math.floor(Math.random()*128)}, ${Math.floor(Math.random()*128)})`;
            config.playerPositionTextOffsetY = Math.floor(Math.random() * 60 - 30);
            // Randomize interaction/oscillation params too for more variety
            config.mouseDragSensitivity = 0.003 + Math.random() * 0.01;
            config.dampingFactor = 0.90 + Math.random() * 0.09;
            config.returnToPoseSpeed = 0.03 + Math.random() * 0.1;
            config.oscillationAngleMaxDeg = 20 + Math.random() * 60;
            config.oscillationSpeedFactor = 0.2 + Math.random() * 1.3;

            activePresetIndex = -1; // Deselect preset
            updateControlsFromConfig(); 
            const btn=document.getElementById('randomizeButton'); btn.disabled=true;btn.textContent="Loading...";
            updateCardImageAndRedraw(config.selectedImageURL); 
            setTimeout(() => { btn.disabled=false;btn.textContent="Randomize Card"; }, 500);
        }
        
        function onMouseDown(e) { /* ... same ... */ if(e.target===renderer.domElement&&cardMesh){isDragging=true;rotationVelocity.set(0,0);previousMousePosition.x=e.clientX;previousMousePosition.y=e.clientY; activePresetIndex = -1; renderPresetsList();}} // Deselect preset on drag
        function onMouseUp() { /* ... same ... */ isDragging=false;}
        function onMouseMove(e) { /* ... same ... */ if(!isDragging||!cardMesh)return;const dX=e.clientX-previousMousePosition.x;const dY=e.clientY-previousMousePosition.y;const rX=dY*config.mouseDragSensitivity;const rY=dX*config.mouseDragSensitivity;cardMesh.rotation.x+=rX;cardMesh.rotation.y+=rY;cardMesh.rotation.z=0;rotationVelocity.x=rX;rotationVelocity.y=rY;previousMousePosition.x=e.clientX;previousMousePosition.y=e.clientY;}
        function normAng(a) { /* ... same ... */ while(a>Math.PI)a-=2*Math.PI;while(a<-Math.PI)a+=2*Math.PI;return a;}
        function animate() { /* ... same ... */ requestAnimationFrame(animate);const dT=clock.getDelta();oscillationTime+=dT*config.oscillationSpeedFactor;if(cardMesh){if(!isDragging){const aR=THREE.MathUtils.degToRad(config.oscillationAngleMaxDeg);targetReturnRotation.y=Math.sin(oscillationTime)*aR;targetReturnRotation.x=0;targetReturnRotation.z=0;cardMesh.rotation.x+=rotationVelocity.x;cardMesh.rotation.y+=rotationVelocity.y;rotationVelocity.x*=config.dampingFactor;rotationVelocity.y*=config.dampingFactor;let dX=normAng(targetReturnRotation.x-cardMesh.rotation.x);let dY=normAng(targetReturnRotation.y-cardMesh.rotation.y);let dZ=normAng(targetReturnRotation.z-cardMesh.rotation.z);const pF=config.returnToPoseSpeed;cardMesh.rotation.x+=dX*pF;cardMesh.rotation.y+=dY*pF;cardMesh.rotation.z+=dZ*pF;const iMS=Math.abs(rotationVelocity.x)<minInertialVelocity&&Math.abs(rotationVelocity.y)<minInertialVelocity;let cDX=normAng(targetReturnRotation.x-cardMesh.rotation.x);let cDY=normAng(targetReturnRotation.y-cardMesh.rotation.y);let cDZ=normAng(targetReturnRotation.z-cardMesh.rotation.z);const iAT=Math.abs(cDX)<returnProximityThreshold&&Math.abs(cDY)<returnProximityThreshold&&Math.abs(cDZ)<returnProximityThreshold;if(iMS&&iAT){cardMesh.rotation.copy(targetReturnRotation);rotationVelocity.set(0,0);}}}renderer.render(scene,camera);}
        
        function init() { /* ... same ... */ scene=new THREE.Scene();camera=new THREE.PerspectiveCamera(75,window.innerWidth/window.innerHeight,0.1,1000);camera.position.z=cardHeight*1.35;renderer=new THREE.WebGLRenderer({antialias:true});renderer.setSize(window.innerWidth,window.innerHeight);renderer.setPixelRatio(window.devicePixelRatio);document.getElementById('container').appendChild(renderer.domElement);scene.add(new THREE.AmbientLight(0xffffff,0.8));const dL=new THREE.DirectionalLight(0xffffff,0.9);dL.position.set(2,3,5);scene.add(dL);randomizeInitialPlayerText();setupControls();createCard();loadPresets();animate();window.addEventListener('resize',()=>{camera.aspect=window.innerWidth/window.innerHeight;camera.updateProjectionMatrix();renderer.setSize(window.innerWidth,window.innerHeight);},false);window.dispatchEvent(new Event('resize'));renderer.domElement.addEventListener('mousedown',onMouseDown);window.addEventListener('mouseup',onMouseUp);window.addEventListener('mousemove',onMouseMove);document.getElementById('randomizeButton').addEventListener('click',handleRandomizeCard);}
        init();
    </script>
</body>
</html>