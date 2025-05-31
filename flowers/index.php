<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Flower Generator with Wind</title>
    <style>
        body {
            font-family: sans-serif;
            margin: 0;
            display: flex;
            height: 100vh;
            background-color: #f0f0f0;
            overflow: hidden;
        }

        #sidebar {
            width: 300px; /* Slightly wider for more sliders */
            padding: 20px;
            background-color: #e0e0e0;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 12px; /* Adjusted gap */
        }

        #sidebar h2, #sidebar h3 {
            margin-top: 10px;
            margin-bottom: 5px;
            text-align: center;
            color: #333;
        }
        #sidebar h2 { margin-top: 0; }


        .slider-group {
            margin-bottom: 5px;
        }

        .slider-group label {
            display: block;
            margin-bottom: 5px;
            font-size: 0.9em;
            color: #555;
        }
        .slider-group label span {
            float: right;
            font-weight: bold;
            color: #333;
        }

        input[type="range"] {
            width: 100%;
            cursor: pointer;
        }

        #randomizeButton, #toggleWindButton {
            padding: 10px;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1em;
            transition: background-color 0.2s;
            margin-bottom: 5px;
        }
        #randomizeButton {
            background-color: #4CAF50;
        }
        #randomizeButton:hover {
            background-color: #45a049;
        }
        #toggleWindButton {
            background-color: #2196F3; /* Blue */
        }
        #toggleWindButton.active {
            background-color: #f44336; /* Red when active */
        }
        #toggleWindButton:hover {
            opacity: 0.9;
        }


        #canvas-container {
            flex-grow: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            background-color: #cceeff; /* Sky blue */
            position: relative; 
        }

        #ground {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 20%; 
            background-color: #8FBC8F; /* DarkSeaGreen */
            z-index: 0; 
        }

        #flower-svg {
            width: 95%;
            height: 95%;
            border: 1px solid #ccc;
            background-color: transparent; 
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            position: relative; 
            z-index: 1;
        }
    </style>
</head>
<body>
    <aside id="sidebar">
        <h2>Flower Controls</h2>

        <button id="randomizeButton">Randomize Scene</button>
        <button id="toggleWindButton">Start Wind</button>

        <h3>Scene & Clumps</h3>
        <div class="slider-group">
            <label for="numClumps">Number of Clumps: <span id="numClumpsValue">5</span></label>
            <input type="range" id="numClumps" min="1" max="15" value="5" step="1">
        </div>
        <div class="slider-group">
            <label for="flowersPerClump">Flowers per Clump: <span id="flowersPerClumpValue">3</span></label>
            <input type="range" id="flowersPerClump" min="1" max="8" value="3" step="1">
        </div>
        <div class="slider-group">
            <label for="clumpSpread">Clump Spread (px): <span id="clumpSpreadValue">80</span></label>
            <input type="range" id="clumpSpread" min="10" max="150" value="80" step="5">
        </div>

        <h3>Flower Appearance</h3>
        <div class="slider-group">
            <label for="flowerSize">Base Flower Size (px): <span id="flowerSizeValue">30</span></label>
            <input type="range" id="flowerSize" min="10" max="60" value="30" step="1">
        </div>
        <div class="slider-group">
            <label for="stemLength">Stem Length (px): <span id="stemLengthValue">100</span></label>
            <input type="range" id="stemLength" min="20" max="250" value="100" step="5">
        </div>
        <div class="slider-group">
            <label for="petalCount">Petal Count: <span id="petalCountValue">6</span></label>
            <input type="range" id="petalCount" min="3" max="18" value="6" step="1">
        </div>
        <div class="slider-group">
            <label for="petalStyle">Petal Style: <span id="petalStyleValue">1</span></label>
            <input type="range" id="petalStyle" min="1" max="3" value="1" step="1">
        </div>
        <div class="slider-group">
            <label for="petalWidthFactor">Petal Width Factor: <span id="petalWidthFactorValue">0.4</span></label>
            <input type="range" id="petalWidthFactor" min="0.1" max="1.0" value="0.4" step="0.05">
        </div>
        <div class="slider-group">
            <label for="petalLengthFactor">Petal Length Factor: <span id="petalLengthFactorValue">1.2</span></label>
            <input type="range" id="petalLengthFactor" min="0.5" max="2.5" value="1.2" step="0.05">
        </div>
        <div class="slider-group">
            <label for="centerSizeFactor">Center Size Factor: <span id="centerSizeFactorValue">0.3</span></label>
            <input type="range" id="centerSizeFactor" min="0.1" max="0.8" value="0.3" step="0.05">
        </div>
        
        <h3>Color</h3>
        <div class="slider-group">
            <label for="baseHue">Base Hue (0-360): <span id="baseHueValue">30</span></label>
            <input type="range" id="baseHue" min="0" max="360" value="30" step="1">
        </div>
        <div class="slider-group">
            <label for="hueVariation">Hue Variation (+/-): <span id="hueVariationValue">30</span></label>
            <input type="range" id="hueVariation" min="0" max="90" value="30" step="1">
        </div>
        <div class="slider-group">
            <label for="saturation">Saturation (%): <span id="saturationValue">80</span></label>
            <input type="range" id="saturation" min="30" max="100" value="80" step="1">
        </div>
        <div class="slider-group">
            <label for="lightness">Lightness (%): <span id="lightnessValue">60</span></label>
            <input type="range" id="lightness" min="30" max="90" value="60" step="1">
        </div>

        <h3>Wind</h3>
         <div class="slider-group">
            <label for="windStrength">Wind Strength: <span id="windStrengthValue">0.1</span></label>
            <input type="range" id="windStrength" min="0" max="0.5" value="0.1" step="0.01">
        </div>
        <div class="slider-group">
            <label for="windGustiness">Wind Gustiness: <span id="windGustinessValue">0.5</span></label>
            <input type="range" id="windGustiness" min="0" max="1" value="0.5" step="0.05">
        </div>
        <div class="slider-group">
            <label for="windSpeed">Wind Oscillation Speed: <span id="windSpeedValue">1</span></label>
            <input type="range" id="windSpeed" min="0.1" max="5" value="1" step="0.1">
        </div>

    </aside>

    <main id="canvas-container">
        <div id="ground"></div>
        <svg id="flower-svg"></svg>
    </main>

    <script>
        const svgNS = "http://www.w3.org/2000/svg";
        const svg = document.getElementById('flower-svg');
        const randomizeButton = document.getElementById('randomizeButton');
        const toggleWindButton = document.getElementById('toggleWindButton');

        const sliders = {
            numClumps: document.getElementById('numClumps'),
            flowersPerClump: document.getElementById('flowersPerClump'),
            clumpSpread: document.getElementById('clumpSpread'),
            flowerSize: document.getElementById('flowerSize'),
            stemLength: document.getElementById('stemLength'),
            petalCount: document.getElementById('petalCount'),
            petalStyle: document.getElementById('petalStyle'),
            petalWidthFactor: document.getElementById('petalWidthFactor'),
            petalLengthFactor: document.getElementById('petalLengthFactor'),
            centerSizeFactor: document.getElementById('centerSizeFactor'),
            baseHue: document.getElementById('baseHue'),
            hueVariation: document.getElementById('hueVariation'),
            saturation: document.getElementById('saturation'),
            lightness: document.getElementById('lightness'),
            windStrength: document.getElementById('windStrength'),
            windGustiness: document.getElementById('windGustiness'),
            windSpeed: document.getElementById('windSpeed'),
        };

        const sliderValuesDisplay = {
            numClumps: document.getElementById('numClumpsValue'),
            flowersPerClump: document.getElementById('flowersPerClumpValue'),
            clumpSpread: document.getElementById('clumpSpreadValue'),
            flowerSize: document.getElementById('flowerSizeValue'),
            stemLength: document.getElementById('stemLengthValue'),
            petalCount: document.getElementById('petalCountValue'),
            petalStyle: document.getElementById('petalStyleValue'),
            petalWidthFactor: document.getElementById('petalWidthFactorValue'),
            petalLengthFactor: document.getElementById('petalLengthFactorValue'),
            centerSizeFactor: document.getElementById('centerSizeFactorValue'),
            baseHue: document.getElementById('baseHueValue'),
            hueVariation: document.getElementById('hueVariationValue'),
            saturation: document.getElementById('saturationValue'),
            lightness: document.getElementById('lightnessValue'),
            windStrength: document.getElementById('windStrengthValue'),
            windGustiness: document.getElementById('windGustinessValue'),
            windSpeed: document.getElementById('windSpeedValue'),
        };

        let config = {};
        let animationFrameId = null;
        let lastTimestamp = 0;
        let windTime = 0; 
        let isWindActive = false;

        let sceneFlowerData = []; 

        function updateConfig() {
            for (const key in sliders) {
                config[key] = parseFloat(sliders[key].value);
                if (sliderValuesDisplay[key]) {
                    sliderValuesDisplay[key].textContent = sliders[key].value;
                }
            }
        }

        function getRandom(min, max) {
            return Math.random() * (max - min) + min;
        }

        function createSVGElement(type, attributes) {
            const el = document.createElementNS(svgNS, type);
            for (const attr in attributes) {
                el.setAttribute(attr, attributes[attr]);
            }
            return el;
        }
        
        function generateFlowerData(cx, cy, flowerConfig) {
            const baseFlowerVisualSize = flowerConfig.flowerSize * getRandom(0.8, 1.2);
            const actualStemLength = flowerConfig.stemLength * getRandom(0.7, 1.3);
            const stemBottomY = cy;
            const stemOriginalTopY = stemBottomY - actualStemLength; 
            const stemColor = `hsl(120, 60%, ${getRandom(25,40)}%)`; // Vary stem green
            const stemWidth = Math.max(2, baseFlowerVisualSize * 0.08 * getRandom(0.7,1.3));

            const numPetals = Math.round(flowerConfig.petalCount);
            const angleStep = 360 / numPetals;
            const petalLFactor = flowerConfig.petalLengthFactor * getRandom(0.9,1.1);
            const petalWFactor = flowerConfig.petalWidthFactor * getRandom(0.8,1.2);
            
            const petalsData = [];
            for (let i = 0; i < numPetals; i++) {
                const angle = i * angleStep + getRandom(-angleStep * 0.2, angleStep * 0.2); // More angular variation
                const petalHue = flowerConfig.baseHue + getRandom(-flowerConfig.hueVariation, flowerConfig.hueVariation);
                const petalSaturation = flowerConfig.saturation + getRandom(-10, 10);
                const petalLightness = flowerConfig.lightness + getRandom(-10, 10);
                
                petalsData.push({
                    angle: angle,
                    color: `hsl(${petalHue % 360}, ${Math.max(30, Math.min(100, petalSaturation))}%, ${Math.max(30, Math.min(90, petalLightness))}%)`,
                    strokeColor: `hsl(${petalHue % 360}, ${Math.max(20, Math.min(100, petalSaturation - 15))}%, ${Math.max(10, Math.min(80, petalLightness - 20))}%)`
                });
            }

            const centerRadiusFactor = flowerConfig.centerSizeFactor * getRandom(0.9,1.1);
            const centerHue = (flowerConfig.baseHue + 40 + getRandom(-20, 20)) % 360;
            const centerColor = `hsl(${centerHue}, ${Math.max(40, flowerConfig.saturation + 15)}%, ${Math.max(30, flowerConfig.lightness - 10)}%)`;
            const centerStrokeColor = `hsl(${centerHue}, ${Math.max(30, flowerConfig.saturation)}%, ${Math.max(20, flowerConfig.lightness - 25)}%)`;

            return {
                id: `flower-${Date.now()}-${Math.random().toString(16).slice(2)}`,
                cx, 
                stemBottomY,
                actualStemLength,
                stemOriginalTopY,
                stemColor,
                stemWidth,
                petalsData,
                petalStyle: Math.round(flowerConfig.petalStyle),
                petalLFactor, 
                petalWFactor,
                baseFlowerVisualSize, 
                centerRadiusFactor,
                centerColor,
                centerStrokeColor,
                windSensitivity: getRandom(0.6, 1.4), // How much this flower is affected by wind
                windPhaseOffset: getRandom(0, Math.PI * 2) // To make flowers sway out of sync
            };
        }

        function drawFlowerFromData(flowerData, currentWindCycleValue) {
            const group = createSVGElement('g', {id: flowerData.id});
            
            const windEffect = currentWindCycleValue * config.windStrength * flowerData.windSensitivity;
            
            const stemBaseX = flowerData.cx;
            const stemBaseY = flowerData.stemBottomY;
            
            // Max horizontal displacement based on stem length.
            // The '0.8' here is an arbitrary factor to control max bend, adjust as needed.
            const maxHorizontalDisplacement = flowerData.actualStemLength * windEffect * 0.8; 

            const stemTopX = flowerData.cx + maxHorizontalDisplacement;
            // Keep stemTopY the same as original, unless you want wind to lift/press flowers.
            // For simplicity, we'll keep it as flowerData.stemOriginalTopY for now.
            // A slight vertical displacement can be added:
            // const verticalDisplacement = Math.abs(maxHorizontalDisplacement) * 0.1 * -Math.sign(windEffect);
            const stemTopY = flowerData.stemOriginalTopY; // + verticalDisplacement;

            // Control point for Bezier curve.
            // For a natural bend, place it partway along the horizontal displacement.
            // And vertically halfway between stem base and (original) stem top.
            const controlX = flowerData.cx + maxHorizontalDisplacement * 0.5; // Control point x
            const controlY = (stemBaseY + flowerData.stemOriginalTopY) / 2;   // Control point y

            const stemPathD = `M ${stemBaseX},${stemBaseY} Q ${controlX},${controlY} ${stemTopX},${stemTopY}`;
            const stem = createSVGElement('path', {
                d: stemPathD,
                stroke: flowerData.stemColor,
                'stroke-width': flowerData.stemWidth,
                fill: 'none'
            });
            group.appendChild(stem);

            // Calculate angle of the flower head.
            // This is the angle of the tangent at the end of the Bezier curve (stemTopX, stemTopY).
            // For a quadratic Bezier P0, P1 (control), P2 (end):
            // Tangent at P2 is P2 - P1.
            const tangentX = stemTopX - controlX;
            const tangentY = stemTopY - controlY;
            let headAngle = Math.atan2(tangentY, tangentX) * (180 / Math.PI);
            // Our assets are typically drawn pointing "up" (negative Y).
            // A horizontal stem (tangentY=0, tangentX>0) should be 0 deg for our assets if they point right.
            // If assets point up, a horizontal stem means +90 or -90.
            // Since our petals are drawn extending towards negative Y from origin (0,0) with 0 rotation,
            // we want the flower head's "up" to align with the stem's end direction.
            // The angle from atan2 is relative to positive X-axis. Add 90 to align "up" (0,-1) vector.
            headAngle += 90;


            const petalL = flowerData.baseFlowerVisualSize * flowerData.petalLFactor;
            const petalW = flowerData.baseFlowerVisualSize * flowerData.petalWFactor;

            flowerData.petalsData.forEach(pData => {
                let petal;
                const transform = `translate(${stemTopX}, ${stemTopY}) rotate(${pData.angle + headAngle})`; 

                switch(flowerData.petalStyle) {
                    case 1: 
                        petal = createSVGElement('ellipse', {
                            cx: 0, cy: -petalL / 2, rx: petalW / 2, ry: petalL / 2,
                        });
                        break;
                    case 2: 
                        petal = createSVGElement('path', { d: `M 0,0 Q ${petalW / 2},-${petalL * 0.7} 0,-${petalL} Q -${petalW / 2},-${petalL * 0.7} 0,0 Z` });
                        break;
                    case 3: 
                        petal = createSVGElement('path', { d: `M ${-petalW/2 * 0.7},0 L ${-petalW/2 * 0.9},-${petalL*0.6} Q 0,-${petalL} ${petalW/2 * 0.9},-${petalL*0.6} L ${petalW/2 * 0.7},0 Z` });
                        break;
                }
                if (petal) {
                    petal.setAttribute('fill', pData.color);
                    petal.setAttribute('stroke', pData.strokeColor);
                    petal.setAttribute('stroke-width', '1');
                    petal.setAttribute('transform', transform);
                    group.appendChild(petal);
                }
            });

            const centerRadius = flowerData.baseFlowerVisualSize * flowerData.centerRadiusFactor;
            const center = createSVGElement('circle', {
                cx: stemTopX, 
                cy: stemTopY,
                r: centerRadius,
                fill: flowerData.centerColor,
                stroke: flowerData.centerStrokeColor,
                'stroke-width': Math.max(1, centerRadius * 0.15)
            });
            group.appendChild(center);
            
            return group;
        }

        function regenerateSceneData() {
            sceneFlowerData = []; 
            const svgRect = svg.getBoundingClientRect();
            if (svgRect.width === 0 || svgRect.height === 0) return;

            const groundHeightPercentage = 0.20; 
            const groundLevel = svgRect.height * (1 - groundHeightPercentage);

            for (let i = 0; i < config.numClumps; i++) {
                const clumpBaseX = getRandom(svgRect.width * 0.05, svgRect.width * 0.95);
                for (let j = 0; j < config.flowersPerClump; j++) {
                    const flowerX = clumpBaseX + getRandom(-config.clumpSpread, config.clumpSpread);
                    const boundedFlowerX = Math.max(config.flowerSize / 2, Math.min(svgRect.width - config.flowerSize / 2, flowerX));
                    const flowerBaseY = groundLevel - getRandom(0, Math.min(30, config.stemLength * 0.1));
                    
                    sceneFlowerData.push(generateFlowerData(boundedFlowerX, flowerBaseY, config));
                }
            }
        }
        
        function drawAnimatedScene(timestamp) {
            svg.innerHTML = ''; 
            if (!sceneFlowerData.length) regenerateSceneData();

            let currentWindCycleValue = 0; // This will be a value between -1 and 1 typically

            if (isWindActive) {
                const deltaTime = lastTimestamp ? (timestamp - lastTimestamp) / 1000 : 0;
                lastTimestamp = timestamp;
                windTime += deltaTime * config.windSpeed;

                // Calculate a wind value using sine waves for oscillation and gustiness
                const baseOscillation = Math.sin(windTime);
                // A secondary, faster oscillation for "gusts"
                const gustOscillation = Math.sin(windTime * 2.3 + 1.5) * Math.sin(windTime * 0.8 - 0.7); // example
                currentWindCycleValue = baseOscillation + gustOscillation * config.windGustiness;
                // Clamp to -1 to 1 if gustiness is high, though not strictly necessary
                currentWindCycleValue = Math.max(-1, Math.min(1, currentWindCycleValue / (1 + config.windGustiness)));
            }
            
            sceneFlowerData.forEach(flowerData => {
                // Apply individual phase offset to this flower's wind calculation
                const flowerSpecificWindTime = windTime + flowerData.windPhaseOffset;
                let flowerWindCycleValue = 0;
                if(isWindActive) {
                    const baseOsc = Math.sin(flowerSpecificWindTime);
                    const gustOsc = Math.sin(flowerSpecificWindTime * 2.3 + 1.5) * Math.sin(flowerSpecificWindTime * 0.8 - 0.7);
                    flowerWindCycleValue = baseOsc + gustOsc * config.windGustiness;
                    flowerWindCycleValue = Math.max(-1, Math.min(1, flowerWindCycleValue / (1 + config.windGustiness)));
                }

                const flowerElement = drawFlowerFromData(flowerData, flowerWindCycleValue);
                svg.appendChild(flowerElement);
            });

            if (isWindActive) {
                animationFrameId = requestAnimationFrame(drawAnimatedScene);
            }
        }
        
        function randomizeScene() {
            const wasWindActive = isWindActive;
            if (isWindActive) stopWindAnimation(); 

            for (const key in sliders) {
                const slider = sliders[key];
                const min = parseFloat(slider.min);
                const max = parseFloat(slider.max);
                const step = parseFloat(slider.step) || 0.01; 

                if (isNaN(min) || isNaN(max) || isNaN(step)) continue;

                const numSteps = Math.floor((max - min) / step);
                const randomStepIndex = Math.floor(Math.random() * (numSteps + 1));
                let randomValue = min + (randomStepIndex * step);
                
                const decimalPlaces = step.toString().includes('.') ? step.toString().split('.')[1].length : 0;
                randomValue = parseFloat(randomValue.toFixed(decimalPlaces || 0));
                slider.value = Math.max(min, Math.min(max, randomValue));
            }
            if (sliders.petalWidthFactor.value > sliders.petalLengthFactor.value * 0.8) {
                 sliders.petalWidthFactor.value = Math.max(parseFloat(sliders.petalWidthFactor.min), sliders.petalLengthFactor.value * getRandom(0.4, 0.7)).toFixed(2);
            }
            if (sliders.centerSizeFactor.value > sliders.petalLengthFactor.value * 0.5) {
                sliders.centerSizeFactor.value = Math.max(parseFloat(sliders.centerSizeFactor.min), sliders.petalLengthFactor.value * getRandom(0.2, 0.4)).toFixed(2);
            }
            updateConfig();
            regenerateSceneData(); 
            requestAnimationFrame(drawAnimatedScene); // Redraw once with new static scene
            if (wasWindActive) startWindAnimation(); // Restart wind if it was active
        }

        function startWindAnimation() {
            if (!isWindActive) {
                isWindActive = true;
                toggleWindButton.textContent = "Stop Wind";
                toggleWindButton.classList.add('active');
                lastTimestamp = performance.now(); 
                windTime = 0; 
                animationFrameId = requestAnimationFrame(drawAnimatedScene);
            }
        }

        function stopWindAnimation() {
            if (isWindActive) {
                isWindActive = false;
                toggleWindButton.textContent = "Start Wind";
                toggleWindButton.classList.remove('active');
                if (animationFrameId) {
                    cancelAnimationFrame(animationFrameId);
                    animationFrameId = null;
                }
                // Redraw once in static position (wind effect will be zero)
                requestAnimationFrame(drawAnimatedScene); 
            }
        }
        
        function toggleWind() {
            if (isWindActive) {
                stopWindAnimation();
            } else {
                if (!sceneFlowerData.length) { 
                    updateConfig(); 
                    regenerateSceneData();
                }
                startWindAnimation();
            }
        }

        function handleSliderChange(event) {
            const oldConfig = JSON.parse(JSON.stringify(config)); 
            updateConfig();

            const changedSliderId = event.target.id;
            const isWindSlider = ['windStrength', 'windGustiness', 'windSpeed'].includes(changedSliderId);

            if (!isWindSlider) { // If a non-wind parameter changed
                const wasWindActive = isWindActive;
                if(isWindActive) stopWindAnimation();
                regenerateSceneData();
                requestAnimationFrame(drawAnimatedScene); // Redraw static scene
                if(wasWindActive) startWindAnimation(); // Restart wind if it was on
            } else if (!isWindActive) { // If a wind slider changed but wind is off
                 requestAnimationFrame(drawAnimatedScene); // Just update static view (wind effect will be 0)
            }
            // If a wind slider changed AND wind is active, the animation loop will pick up changes.
        }


        function init() {
            updateConfig(); 
            regenerateSceneData(); 
            requestAnimationFrame(drawAnimatedScene); // Initial static draw

            for (const key in sliders) {
                sliders[key].addEventListener('input', handleSliderChange);
            }
            randomizeButton.addEventListener('click', randomizeScene);
            toggleWindButton.addEventListener('click', toggleWind);
            
            let resizeTimer;
            window.addEventListener('resize', () => {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(() => {
                    const wasWindActive = isWindActive;
                    if (isWindActive) stopWindAnimation();
                    regenerateSceneData(); 
                    requestAnimationFrame(drawAnimatedScene); // Redraw static
                    if(wasWindActive) startWindAnimation();
                }, 150); 
            });
        }

        init();
    </script>
</body>
</html>