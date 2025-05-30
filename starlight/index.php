<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Animated Sky Elements</title>
    <style>
        body {
            margin: 0;
            overflow: hidden;
            background: linear-gradient(to bottom, #87CEEB 0%, #B0E0E6 70%, #ADD8E6 100%);
            height: 100vh;
            position: relative;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        #sky-canvas {
            position: absolute;
            top: 0; left: 0;
            width: 100%; height: 100%;
            pointer-events: none;
        }

        /* --- Cloud Styles --- */
        .cloud-container {
            position: absolute;
        }
        .puff {
            position: absolute;
            /* background-color set by JS */
            border-radius: 50%;
            /* box-shadow set by JS */
            transition: background-color 0.5s ease; /* For live whiteness update */
        }
        @keyframes drift-ltr {
            0% { transform: translateX(-350px); opacity: 0; }
            10% { opacity: var(--cloud-opacity, 0.85); }
            90% { opacity: var(--cloud-opacity, 0.85); }
            100% { transform: translateX(calc(100vw + 350px)); opacity: 0; }
        }
        @keyframes drift-rtl {
            0% { transform: translateX(calc(100vw + 350px)); opacity: 0; }
            10% { opacity: var(--cloud-opacity, 0.85); }
            90% { opacity: var(--cloud-opacity, 0.85); }
            100% { transform: translateX(-350px); opacity: 0; }
        }

        /* --- Bird Styles --- */
        .bird-flock {
            position: absolute;
        }
        .bird-svg {
            display: inline-block;
            fill: #333;
            stroke: #222;
            stroke-width: 1; /* Base, JS might adjust */
            margin: 0 1px; /* Slightly less margin for tighter flocks */
            /* The bobbing animation can be removed or kept as a subtle body movement */
            /* animation: bird-bob 1s infinite ease-in-out alternate; */
        }
        .bird-svg path {
            transition: opacity 0.05s linear; /* Fast transition for flapping */
        }

        /* Removed bird-bob for now to focus on path toggling */
        /* @keyframes bird-bob { 
            0% { transform: translateY(0px); }
            50% { transform: translateY(-2px) ; } 
            100% { transform: translateY(0px); }
        } */

        @keyframes bird-drift-ltr {
            0% { transform: translateX(-150px); opacity: 0; }
            10% { opacity: 1; }
            90% { opacity: 1; }
            100% { transform: translateX(calc(100vw + 150px)); opacity: 0; }
        }
        @keyframes bird-drift-rtl {
            0% { transform: translateX(calc(100vw + 150px)); opacity: 0; }
            10% { opacity: 1; }
            90% { opacity: 1; }
            100% { transform: translateX(-150px); opacity: 0; }
        }

        /* --- Controls Panel --- */
        #controls {
            position: fixed;
            top: 10px;
            left: 10px;
            background-color: rgba(255, 255, 255, 0.92);
            padding: 15px;
            border-radius: 8px;
            color: #333;
            z-index: 1000;
            max-width: 330px; /* Slightly wider */
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            max-height: calc(100vh - 20px);
            overflow-y: auto;
        }
        #controls h3 { margin-top: 0; margin-bottom: 10px; font-size: 1.1em; text-align: center; border-bottom: 1px solid #ccc; padding-bottom: 8px; color: #005A9C;}
        #controls h4 { margin-top: 15px; margin-bottom: 5px; font-size: 1em; color: #337ab7; border-bottom: 1px dashed #eee; padding-bottom: 3px;}
        #controls label { display: block; margin-top: 8px; font-size: 0.85em;}
        #controls input[type="range"], #controls input[type="color"] { width: 100%; margin-bottom: 1px; margin-top: 2px; box-sizing: border-box;}
        #controls input[type="color"] { height: 25px; padding: 1px;}
        #controls span { font-size: 0.75em; color: #111; margin-left: 5px; display: inline-block; min-width: 25px;}
        #controls button { display: block; width: 100%; padding: 8px 10px; margin-top: 12px; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 0.9em;}
        #applyAndReset { background-color: #5cb85c; }
        #applyAndReset:hover { background-color: #4cae4c; }
        #randomizeAll { background-color: #337ab7; }
        #randomizeAll:hover { background-color: #286090; }

        /* Custom styles for color input to look like a grayscale slider */
        .grayscale-picker-label { position: relative; }
        .grayscale-picker-label input[type="color"] {
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            width: calc(100% - 30px); /* Make space for the text value */
            height: 15px;
            background-color: transparent;
            border: 1px solid #ccc;
            border-radius: 3px;
            cursor: pointer;
            display: inline-block;
            vertical-align: middle;
        }
        .grayscale-picker-label input[type="color"]::-webkit-color-swatch-wrapper { padding: 0; }
        .grayscale-picker-label input[type="color"]::-webkit-color-swatch { border: none; border-radius: 2px; }
        .grayscale-picker-label input[type="color"]::-moz-color-swatch { border: none; border-radius: 2px; }

        .color-value-display {
            display: inline-block;
            width: 25px; /* Fixed width for hex value */
            text-align: right;
            font-size: 0.75em;
            vertical-align: middle;
        }
    </style>
</head>
<body>
    <div id="sky-canvas"></div>

    <div id="controls">
        <h3>Sky Configuration</h3>

        <h4>Clouds</h4>
        <!-- ... (numClouds, maxPuffs, minPuffSize, maxPuffSize are the same) ... -->
        <label for="numClouds">Cloud Density: <span id="numCloudsValue">10</span></label>
        <input type="range" id="numClouds" min="1" max="30" value="10" data-reset="true">

        <label for="maxPuffs">Max Puffs/Cloud: <span id="maxPuffsValue">7</span></label>
        <input type="range" id="maxPuffs" min="2" max="15" value="7" data-reset="true">

        <label for="minPuffSize">Min Puff Base (px): <span id="minPuffSizeValue">30</span></label>
        <input type="range" id="minPuffSize" min="10" max="100" value="30" data-reset="true">

        <label for="maxPuffSize">Max Puff Base (px): <span id="maxPuffSizeValue">80</span></label>
        <input type="range" id="maxPuffSize" min="20" max="200" value="80" data-reset="true">

        <label for="cloudOpacity">Overall Opacity: <span id="cloudOpacityValue">0.85</span></label>
        <input type="range" id="cloudOpacity" min="0.1" max="1" step="0.01" value="0.85" data-liveupdate="opacity">
        
        <label class="grayscale-picker-label">Puff Color 1 (Lighter):
            <input type="color" id="puffColor1" value="#FFFFFF" data-liveupdate="puffcolor">
            <span id="puffColor1Value" class="color-value-display">#FFF</span>
        </label>
        <label class="grayscale-picker-label">Puff Color 2 (Darker):
            <input type="color" id="puffColor2" value="#DDDDDD" data-liveupdate="puffcolor">
            <span id="puffColor2Value" class="color-value-display">#DDD</span>
        </label>

        <!-- ... (windSpeedMultiplier, cloud durations, wind bias, spawn interval are the same) ... -->
        <label for="windSpeedMultiplier">Wind Speed Multiplier: <span id="windSpeedMultiplierValue">1.0</span>x</label>
        <input type="range" id="windSpeedMultiplier" min="0.1" max="3" step="0.1" value="1.0" data-reset="true">

        <label for="minCloudDuration">Min Cloud Duration (s): <span id="minCloudDurationValue">40</span></label>
        <input type="range" id="minCloudDuration" min="10" max="120" value="40" data-reset="true">

        <label for="maxCloudDuration">Max Cloud Duration (s): <span id="maxCloudDurationValue">90</span></label>
        <input type="range" id="maxCloudDuration" min="20" max="150" value="90" data-reset="true">
        
        <label for="cloudWindDirectionBias">Cloud Wind Dir (0=RTL, 1=LTR): <span id="cloudWindDirectionBiasValue">0.5</span></label>
        <input type="range" id="cloudWindDirectionBias" min="0" max="1" step="0.01" value="0.5" data-reset="true">

        <label for="cloudSpawnInterval">New Cloud Interval (ms): <span id="cloudSpawnIntervalValue">5000</span></label>
        <input type="range" id="cloudSpawnInterval" min="500" max="15000" step="100" value="5000">


        <h4>Birds</h4>
        <!-- ... (birdDensity, birdSize, birdsPerFlock are the same) ... -->
        <label for="birdDensity">Bird Density: <span id="birdDensityValue">3</span></label>
        <input type="range" id="birdDensity" min="0" max="15" value="3" data-reset="true">

        <label for="birdSize">Bird Size (px): <span id="birdSizeValue">20</span></label>
        <input type="range" id="birdSize" min="10" max="50" value="20" data-reset="true">
        
        <label for="birdsPerFlock">Birds per Flock: <span id="birdsPerFlockValue">1</span></label>
        <input type="range" id="birdsPerFlock" min="1" max="5" value="1" data-reset="true">

        <label for="birdFlapSpeed">Bird Flap Speed (ms/frame): <span id="birdFlapSpeedValue">150</span></label>
        <input type="range" id="birdFlapSpeed" min="50" max="500" step="10" value="150" data-reset="true">

        <!-- ... (birdSpeedMultiplier, bird wind bias, spawn interval are the same) ... -->
        <label for="birdSpeedMultiplier">Bird Speed Multiplier: <span id="birdSpeedMultiplierValue">1.5</span>x</label>
        <input type="range" id="birdSpeedMultiplier" min="0.2" max="5" step="0.1" value="1.5" data-reset="true">
        
        <label for="birdWindDirectionBias">Bird Wind Dir (0=RTL, 1=LTR): <span id="birdWindDirectionBiasValue">0.6</span></label>
        <input type="range" id="birdWindDirectionBias" min="0" max="1" step="0.01" value="0.6" data-reset="true">

        <label for="birdSpawnInterval">New Bird Flock Interval (ms): <span id="birdSpawnIntervalValue">7000</span></label>
        <input type="range" id="birdSpawnInterval" min="1000" max="20000" step="100" value="7000">


        <button id="applyAndReset">Apply & Regenerate Sky</button>
        <button id="randomizeAll">Randomize All & Regenerate</button>
    </div>

    <script>
        const skyCanvas = document.getElementById('sky-canvas');
        let appConfig = { clouds: {}, birds: {} };
        let cloudGenerationIntervalId, birdGenerationIntervalId;
        let birdFlapIntervals = []; // Store intervals for individual bird flaps

        // --- DOM Elements ---
        const controls = { /* ... same as before ... */
            numClouds: document.getElementById('numClouds'), maxPuffs: document.getElementById('maxPuffs'),
            minPuffSize: document.getElementById('minPuffSize'), maxPuffSize: document.getElementById('maxPuffSize'),
            cloudOpacity: document.getElementById('cloudOpacity'),
            puffColor1: document.getElementById('puffColor1'), puffColor2: document.getElementById('puffColor2'),
            windSpeedMultiplier: document.getElementById('windSpeedMultiplier'), minCloudDuration: document.getElementById('minCloudDuration'),
            maxCloudDuration: document.getElementById('maxCloudDuration'), cloudWindDirectionBias: document.getElementById('cloudWindDirectionBias'),
            cloudSpawnInterval: document.getElementById('cloudSpawnInterval'),
            birdDensity: document.getElementById('birdDensity'), birdSize: document.getElementById('birdSize'),
            birdsPerFlock: document.getElementById('birdsPerFlock'), birdFlapSpeed: document.getElementById('birdFlapSpeed'),
            birdSpeedMultiplier: document.getElementById('birdSpeedMultiplier'), birdWindDirectionBias: document.getElementById('birdWindDirectionBias'),
            birdSpawnInterval: document.getElementById('birdSpawnInterval'),
        };
        const valueDisplays = Object.keys(controls).reduce((acc, key) => {
            acc[key] = document.getElementById(key + 'Value');
            return acc;
        }, {});
        const applyResetButton = document.getElementById('applyAndReset');
        const randomizeButton = document.getElementById('randomizeAll');

        // --- Utility Functions ---
        function getRandom(min, max) { /* ... same ... */
            min = parseFloat(min); max = parseFloat(max);
            if (min > max) [min, max] = [max, min];
            return Math.random() * (max - min) + min;
        }
        function getRandomInt(min, max) { /* ... same ... */
            min = Math.ceil(min); max = Math.floor(max);
            return Math.floor(Math.random() * (max - min + 1)) + min;
        }
        function hexToRgb(hex) {
            const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
            return result ? {
                r: parseInt(result[1], 16), g: parseInt(result[2], 16), b: parseInt(result[3], 16)
            } : null;
        }
        function rgbToHex(r, g, b) {
            return "#" + ((1 << 24) + (r << 16) + (g << 8) + b).toString(16).slice(1).toUpperCase();
        }
        // Ensure color is grayscale by averaging RGB and applying to all
        function ensureGrayscale(hexColor) {
            const rgb = hexToRgb(hexColor);
            if (!rgb) return hexColor; // Should not happen with color picker
            const avg = Math.round((rgb.r + rgb.g + rgb.b) / 3);
            return rgbToHex(avg, avg, avg);
        }

        // --- Config Update ---
        function updateConfigFromSliders() {
            // Clouds
            appConfig.clouds.numClouds = parseInt(controls.numClouds.value);
            appConfig.clouds.minPuffs = Math.max(1, Math.floor(parseInt(controls.maxPuffs.value) * 0.4));
            appConfig.clouds.maxPuffs = parseInt(controls.maxPuffs.value);
            appConfig.clouds.minBasePuffSize = parseInt(controls.minPuffSize.value);
            appConfig.clouds.maxBasePuffSize = parseInt(controls.maxPuffSize.value);
            appConfig.clouds.cloudOpacity = parseFloat(controls.cloudOpacity.value);
            // Ensure colors are grayscale
            appConfig.clouds.puffColor1 = ensureGrayscale(controls.puffColor1.value);
            appConfig.clouds.puffColor2 = ensureGrayscale(controls.puffColor2.value);
            // Swap if color2 is lighter than color1
            if (parseInt(appConfig.clouds.puffColor1.slice(1), 16) < parseInt(appConfig.clouds.puffColor2.slice(1), 16)) {
                [appConfig.clouds.puffColor1, appConfig.clouds.puffColor2] = [appConfig.clouds.puffColor2, appConfig.clouds.puffColor1];
                // Reflect this swap back to the UI pickers
                controls.puffColor1.value = appConfig.clouds.puffColor1;
                controls.puffColor2.value = appConfig.clouds.puffColor2;
            }

            appConfig.clouds.windSpeedMultiplier = parseFloat(controls.windSpeedMultiplier.value);
            appConfig.clouds.minCloudDuration = parseFloat(controls.minCloudDuration.value);
            appConfig.clouds.maxCloudDuration = parseFloat(controls.maxCloudDuration.value);
            appConfig.clouds.windDirectionBias = parseFloat(controls.cloudWindDirectionBias.value);
            appConfig.clouds.spawnInterval = parseInt(controls.cloudSpawnInterval.value);

            // Birds
            appConfig.birds.density = parseInt(controls.birdDensity.value);
            appConfig.birds.size = parseInt(controls.birdSize.value);
            appConfig.birds.perFlock = parseInt(controls.birdsPerFlock.value);
            appConfig.birds.flapSpeed = parseInt(controls.birdFlapSpeed.value);
            appConfig.birds.speedMultiplier = parseFloat(controls.birdSpeedMultiplier.value);
            appConfig.birds.windDirectionBias = parseFloat(controls.birdWindDirectionBias.value);
            appConfig.birds.spawnInterval = parseInt(controls.birdSpawnInterval.value);
            appConfig.birds.baseDuration = 30;

            // Update display values
            for (const key in controls) {
                if (valueDisplays[key]) {
                    let val = controls[key].value;
                    const el = controls[key];
                    if (el.type === 'color') {
                        val = ensureGrayscale(val).substring(1,4); // Show short hex for grayscale
                        if(val[0]===val[1] && val[1]===val[2]) val = val[0].repeat(3); // e.g. FFF not FEF
                        else val = ensureGrayscale(val).substring(1); // full hex if not pure gray like EFEFEF
                        valueDisplays[key].textContent = '#' + val;
                    } else if (el.step && (el.step.includes('.') || parseFloat(el.step) < 1) || key.includes('Opacity') || key.includes('Multiplier') || key.includes('Bias')) {
                         val = parseFloat(val).toFixed( (key.includes('Multiplier') || key.includes('Bias')) ? (key.includes('SpeedMultiplier') ? 1 : 2) : 2);
                    }
                    valueDisplays[key].textContent = (el.type === 'color' ? '#' : '') + val;
                }
            }
             // Also update the color picker's background to reflect the forced grayscale
            if (controls.puffColor1.value !== appConfig.clouds.puffColor1) controls.puffColor1.value = appConfig.clouds.puffColor1;
            if (controls.puffColor2.value !== appConfig.clouds.puffColor2) controls.puffColor2.value = appConfig.clouds.puffColor2;
        }

        // --- Cloud Creation ---
        function createCloudElement(isInitialPopulation = false) {
            const config = appConfig.clouds;
            if (config.maxBasePuffSize <= config.minBasePuffSize) config.maxBasePuffSize = config.minBasePuffSize + 10;

            const cloudContainer = document.createElement('div');
            cloudContainer.classList.add('cloud-container');
            cloudContainer.style.setProperty('--cloud-opacity', config.cloudOpacity);

            const numPuffs = getRandomInt(config.minPuffs, config.maxPuffs);
            const basePuffSizeForThisCloud = getRandom(config.minBasePuffSize, config.maxBasePuffSize);
            if (basePuffSizeForThisCloud <= 0) return null;

            const color1RGB = hexToRgb(config.puffColor1);
            const color2RGB = hexToRgb(config.puffColor2);

            for (let i = 0; i < numPuffs; i++) {
                const puff = document.createElement('div');
                puff.classList.add('puff');
                const puffSize = basePuffSizeForThisCloud * getRandom(0.7, 1.3);
                if (puffSize <=0) continue; 
                puff.style.width = `${puffSize}px`;
                puff.style.height = `${puffSize * getRandom(0.6, 1)}px`;
                const offsetX = (i - numPuffs / 2 + getRandom(-0.5, 0.5)) * basePuffSizeForThisCloud * 0.35;
                const offsetY = getRandom(-basePuffSizeForThisCloud * 0.25, basePuffSizeForThisCloud * 0.25);
                puff.style.left = `${offsetX - puffSize / 2}px`;
                puff.style.top = `${offsetY - (puffSize * getRandom(0.6,1)) / 2}px`;

                // Interpolate color
                const t = Math.random(); // 0 to 1
                const r = Math.round(color1RGB.r * (1 - t) + color2RGB.r * t);
                const g = Math.round(color1RGB.g * (1 - t) + color2RGB.g * t); // Will be same as r for grayscale
                const b = Math.round(color1RGB.b * (1 - t) + color2RGB.b * t); // Will be same as r for grayscale
                const puffColor = `rgba(${r}, ${g}, ${b}, ${getRandom(0.65, 0.9)})`; // Alpha variation for puffs
                
                puff.style.backgroundColor = puffColor;
                puff.style.boxShadow = `0 0 ${Math.max(2, basePuffSizeForThisCloud*0.2)}px ${Math.max(1, basePuffSizeForThisCloud*0.1)}px rgba(${r}, ${g}, ${b}, 0.4)`;
                cloudContainer.appendChild(puff);
            }

            cloudContainer.style.top = `${getRandom(0, 75)}vh`;
            cloudContainer.style.zIndex = getRandomInt(1, 10);
            let duration = getRandom(config.minCloudDuration, config.maxCloudDuration) / config.windSpeedMultiplier;
            if (duration < 5) duration = 5;
            cloudContainer.style.animationDuration = `${duration}s`;
            cloudContainer.style.animationTimingFunction = 'linear';
            cloudContainer.style.animationIterationCount = '1';
            cloudContainer.style.animationName = Math.random() < config.windDirectionBias ? 'drift-ltr' : 'drift-rtl';
            if (isInitialPopulation) cloudContainer.style.animationDelay = `-${getRandom(0, duration)}s`;
            
            skyCanvas.appendChild(cloudContainer);
            cloudContainer.addEventListener('animationend', () => cloudContainer.remove());
            return cloudContainer;
        }

        // --- Bird Creation & Animation ---
        const birdFrames = {
            up: "M0 15 Q10 0 20 15 T40 15",    // Wings more up
            mid: "M0 15 Q10 8 20 15 T40 15",   // Wings mid (original M)
            down: "M0 12 Q10 20 20 12 T40 12"  // Wings more down
        };
        const birdFrameSequence = ['up', 'mid', 'down', 'mid']; // Flap cycle

        function createBirdFlockElement(isInitialPopulation = false) {
            const config = appConfig.birds;
            const flockContainer = document.createElement('div');
            flockContainer.classList.add('bird-flock');
            flockContainer.dataset.flapSpeed = config.flapSpeed; // Store for potential live update

            const birdCountInFlock = getRandomInt(1, Math.max(1,config.perFlock)); // Ensure at least 1
            for(let i = 0; i < birdCountInFlock; i++) {
                const birdSVG = document.createElementNS("http://www.w3.org/2000/svg", "svg");
                birdSVG.classList.add('bird-svg');
                birdSVG.setAttribute('viewBox', "0 0 40 25"); // Wider viewBox for more wing motion
                birdSVG.style.width = `${config.size + getRandom(-config.size*0.1, config.size*0.1)}px`;
                birdSVG.style.height = 'auto';
                
                // Create all path elements, only one visible at a time
                Object.keys(birdFrames).forEach((frameKey, index) => {
                    const path = document.createElementNS("http://www.w3.org/2000/svg", "path");
                    path.setAttribute('d', birdFrames[frameKey]);
                    path.dataset.frameName = frameKey;
                    path.style.opacity = (index === 0) ? '1' : '0'; // Start with first frame visible
                    path.style.strokeWidth = `${Math.max(0.5, config.size / 20)}px`;
                    birdSVG.appendChild(path);
                });
                
                birdSVG.dataset.currentFrameIndex = 0; // For animation state
                flockContainer.appendChild(birdSVG);
                
                // Start individual bird's flapping animation
                const flapIntervalId = setInterval(() => animateBirdFlap(birdSVG), config.flapSpeed + getRandomInt(-20, 20)); // Add jitter
                birdFlapIntervals.push(flapIntervalId); // Store to clear later
                birdSVG.dataset.flapIntervalId = flapIntervalId; // Associate with SVG
            }
            
            flockContainer.style.top = `${getRandom(5, 60)}vh`;
            flockContainer.style.zIndex = getRandomInt(15, 25);
            let duration = (config.baseDuration / config.speedMultiplier) * getRandom(0.8, 1.2);
            if (duration < 3) duration = 3;
            flockContainer.style.animationDuration = `${duration}s`;
            flockContainer.style.animationTimingFunction = 'linear';
            flockContainer.style.animationIterationCount = '1';
            flockContainer.style.animationName = Math.random() < config.windDirectionBias ? 'bird-drift-ltr' : 'bird-drift-rtl';
            if (isInitialPopulation) flockContainer.style.animationDelay = `-${getRandom(0, duration)}s`;

            skyCanvas.appendChild(flockContainer);
            flockContainer.addEventListener('animationend', () => {
                flockContainer.querySelectorAll('.bird-svg').forEach(birdSVG => {
                    clearInterval(parseInt(birdSVG.dataset.flapIntervalId)); // Stop this bird's flap
                });
                flockContainer.remove();
            });
            return flockContainer;
        }
        
        function animateBirdFlap(birdSVG) {
            if (!birdSVG || !birdSVG.parentNode) { // Bird might have been removed
                clearInterval(parseInt(birdSVG.dataset.flapIntervalId));
                return;
            }
            let currentFrameIndex = parseInt(birdSVG.dataset.currentFrameIndex);
            const paths = birdSVG.querySelectorAll('path');
            
            paths[currentFrameIndex % paths.length].style.opacity = '0'; // Hide current
            currentFrameIndex = (currentFrameIndex + 1) % birdFrameSequence.length; // Cycle through defined sequence
            const nextFrameName = birdFrameSequence[currentFrameIndex];
            
            // Find the path element corresponding to the nextFrameName
            let displayedPath = false;
            paths.forEach(p => {
                if (p.dataset.frameName === nextFrameName) {
                    p.style.opacity = '1'; // Show next
                    displayedPath = true;
                } else {
                    if (p.style.opacity !== '0') p.style.opacity = '0'; // Ensure others are hidden
                }
            });
             if (!displayedPath && paths.length > 0) paths[0].style.opacity = '1'; // Fallback

            birdSVG.dataset.currentFrameIndex = currentFrameIndex;
        }

        // --- Sky Management ---
        function clearAllElements() {
            birdFlapIntervals.forEach(clearInterval); // Stop all bird flapping intervals
            birdFlapIntervals = [];
            while (skyCanvas.firstChild) {
                skyCanvas.removeChild(skyCanvas.firstChild);
            }
        }
        function resetAndPopulateSky() {
            clearAllElements();
            updateConfigFromSliders(); 
            for (let i = 0; i < appConfig.clouds.numClouds; i++) createCloudElement(true);
            for (let i = 0; i < appConfig.birds.density; i++) createBirdFlockElement(true);
        }
        
        // --- Live Updates & Controls Init ---
        function handleLiveUpdate(type, value, controlElement) {
            if (type === 'opacity') {
                skyCanvas.querySelectorAll('.cloud-container').forEach(cloud => {
                    cloud.style.setProperty('--cloud-opacity', value);
                });
            } else if (type === 'puffcolor') {
                // Update config immediately for color changes as it affects new puffs
                // and potentially existing ones if we implement full live color update for clouds
                updateConfigFromSliders(); // This will re-validate and set colors
                // For simplicity, live color updates on clouds might require a targeted re-draw
                // or simply rely on the next full "Apply & Regenerate".
                // The updateConfigFromSliders call handles ensuring grayscale and order.
                // The values in the color pickers themselves are already updated by their 'input' event.
            }
            // Note: Live update for birdFlapSpeed would require iterating and resetting intervals.
            // For now, birdFlapSpeed changes take effect on next regeneration or via "Apply & Regenerate".
        }

        function randomizeAllSlidersAndApply() { /* ... same as before ... */
             for (const key in controls) {
                const slider = controls[key];
                const min = parseFloat(slider.min);
                const max = parseFloat(slider.max);
                let randomValue;

                if (slider.type === 'color') {
                    // Random grayscale color
                    const grayVal = getRandomInt(180, 255); // Keep it in lighter grays for clouds
                    randomValue = rgbToHex(grayVal, grayVal, grayVal);
                } else {
                    const step = parseFloat(slider.step) || ((max - min) > 20 ? 1 : 0.01);
                    if (Number.isInteger(min) && Number.isInteger(max) && step === 1) {
                        randomValue = getRandomInt(min, max);
                    } else {
                        let rVal = getRandom(min, max);
                        randomValue = Math.round(rVal / step) * step;
                        randomValue = Math.max(min, Math.min(max, randomValue));
                        randomValue = parseFloat(randomValue.toFixed(step.toString().includes('.') ? step.toString().split('.')[1].length : 0));
                    }
                }
                slider.value = randomValue;
                slider.dispatchEvent(new Event('input', { bubbles: true }));
            }
            updateConfigFromSliders(); 
            resetAndPopulateSky();
            restartSpawners();
        }

        function initControls() { /* ... almost same, update color input handling ... */
            updateConfigFromSliders(); 

            for (const key in controls) {
                controls[key].addEventListener('input', (e) => {
                    const slider = e.target;
                    let displayValue = slider.value;
                    
                    if (slider.type === 'color') {
                        slider.value = ensureGrayscale(slider.value); // Force grayscale on input
                        displayValue = slider.value.substring(0,7); // Show full hex like #RRGGBB
                        if (valueDisplays[key]) valueDisplays[key].textContent = displayValue;
                         // To reflect forced grayscale immediately in the picker's swatch:
                        if (e.isTrusted) { // only if user interaction, not programmatic
                            slider.style.backgroundColor = slider.value; // May not work on all browsers for type=color
                        }
                    } else if (valueDisplays[key]) {
                         if (slider.step && (slider.step.includes('.') || parseFloat(slider.step) < 1) || key.includes('Opacity') || key.includes('Multiplier') || key.includes('Bias')) {
                            displayValue = parseFloat(displayValue).toFixed( (key.includes('Multiplier') || key.includes('Bias')) ? (key.includes('SpeedMultiplier') ? 1 : 2) : 2);
                        }
                        valueDisplays[key].textContent = displayValue;
                    }
                    
                    const liveUpdateType = slider.dataset.liveupdate;
                    if (liveUpdateType) {
                        handleLiveUpdate(liveUpdateType, slider.value, slider);
                    } else if (key === 'cloudSpawnInterval' || key === 'birdSpawnInterval' || key === 'birdFlapSpeed') {
                         updateConfigFromSliders(); // Update relevant part of config
                         if(key === 'birdFlapSpeed') {
                            // This requires more intricate handling to update existing birds.
                            // For now, it's best to regenerate or let new birds pick up new speed.
                            // To update existing: iterate birds, clear old interval, start new one.
                         } else {
                            restartSpawners();
                         }
                    }
                });
                 // Initial color picker background for grayscale visual cue (may not work on all browsers)
                if (controls[key].type === 'color') {
                    controls[key].style.backgroundColor = controls[key].value;
                }
            }
            
            applyResetButton.addEventListener('click', () => {
                resetAndPopulateSky();
                restartSpawners(); 
            });
            randomizeButton.addEventListener('click', randomizeAllSlidersAndApply);
        }
        
        // --- Spawners --- (largely the same)
        function continuouslySpawnCloud() { /* ... same ... */
             if (document.hidden || skyCanvas.querySelectorAll('.cloud-container').length > appConfig.clouds.numClouds + 10) return;
            createCloudElement(false);
        }
        function continuouslySpawnBirdFlock() { /* ... same ... */
            if (document.hidden || skyCanvas.querySelectorAll('.bird-flock').length > appConfig.birds.density + 5) return;
             if (appConfig.birds.density > 0) createBirdFlockElement(false);
        }
        function startSpawners() { /* ... same ... */
            if (cloudGenerationIntervalId) clearInterval(cloudGenerationIntervalId);
            if (appConfig.clouds.spawnInterval > 0) {
                cloudGenerationIntervalId = setInterval(continuouslySpawnCloud, appConfig.clouds.spawnInterval);
            }
            if (birdGenerationIntervalId) clearInterval(birdGenerationIntervalId);
            if (appConfig.birds.spawnInterval > 0 && appConfig.birds.density > 0) {
                 birdGenerationIntervalId = setInterval(continuouslySpawnBirdFlock, appConfig.birds.spawnInterval);
            }
        }
        function restartSpawners() { /* ... same ... */
            updateConfigFromSliders();
            startSpawners();
        }

        // --- Initialization ---
        initControls(); 
        resetAndPopulateSky();
        startSpawners();
        document.addEventListener("visibilitychange", () => { /* ... same ... */
             if (document.hidden) {
                if (cloudGenerationIntervalId) clearInterval(cloudGenerationIntervalId);
                if (birdGenerationIntervalId) clearInterval(birdGenerationIntervalId);
                 birdFlapIntervals.forEach(clearInterval); // Also pause flapping animations
            } else {
                updateConfigFromSliders(); 
                startSpawners(); 
                 // Resume flapping for existing birds
                skyCanvas.querySelectorAll('.bird-svg').forEach(birdSVG => {
                    const flapSpeed = parseInt(birdSVG.closest('.bird-flock')?.dataset.flapSpeed) || appConfig.birds.flapSpeed;
                    const flapIntervalId = setInterval(() => animateBirdFlap(birdSVG), flapSpeed + getRandomInt(-20,20));
                    birdFlapIntervals.push(flapIntervalId);
                    birdSVG.dataset.flapIntervalId = flapIntervalId;
                });
            }
        });

    </script>
</body>
</html>