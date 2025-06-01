<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rotor Disc Explorer</title>
    <style>
        body, html {
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: row;
            align-items: center;
            justify-content: center;
            background-color: #333;
            color: #eee;
            font-family: sans-serif;
            overflow: hidden;
        }
        #rotorCanvas {
            border: 1px solid #555;
            background-color: #4a5568;
        }
        .controls-container {
            width: 380px; 
            margin-left: 20px;
            padding: 20px;
            background-color: #444;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.5);
            max-height: 95vh; 
            overflow-y: auto; 
            display: flex;
            flex-direction: column; 
            gap: 15px; 
        }
        .control-group {
            display: flex;
            flex-direction: column; 
            gap: 5px; 
        }
        .control-group label {
            font-size: 0.9em;
            color: #ccc;
        }
        .control-group input[type="range"] {
            width: 100%;
            height: 10px; 
        }
        .control-group .value-display {
            font-size: 0.85em;
            color: #bbb;
            text-align: right;
            min-height: 1em; 
        }
        .controls-container button {
            padding: 10px 15px; 
            background-color: #5a687a;
            color: #eee;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1em;
            margin-top: 10px; 
        }
        .controls-container button:hover {
            background-color: #6a788a;
        }
        .controls-container::-webkit-scrollbar { width: 8px; }
        .controls-container::-webkit-scrollbar-track { background: #3a3a3a; border-radius: 4px; }
        .controls-container::-webkit-scrollbar-thumb { background: #666; border-radius: 4px; }
        .controls-container::-webkit-scrollbar-thumb:hover { background: #888; }
        .controls-container { scrollbar-width: thin; scrollbar-color: #666 #3a3a3a; }
    </style>
</head>
<body>
    <canvas id="rotorCanvas"></canvas>
    <div class="controls-container">
        <div class="control-group">
            <label for="speed">Speed:</label>
            <input type="range" id="speed" min="0" max="0.5" step="0.001" value="0.1">
            <span class="value-display" id="speedValue">0.100</span>
        </div>
        <div class="control-group">
            <label for="discOpacity">Disc Opacity:</label>
            <input type="range" id="discOpacity" min="0" max="1" step="0.01" value="0.3">
            <span class="value-display" id="discOpacityValue">0.30</span>
        </div>
        <div class="control-group">
            <label for="discRadius">Rotor Size (Disc & Blades):</label>
            <input type="range" id="discRadius" min="30" max="300" step="1" value="120">
            <span class="value-display" id="discRadiusValue">120</span>
        </div>
        <div class="control-group">
            <label for="numBlades">Number of Blades:</label>
            <input type="range" id="numBlades" min="1" max="24" step="1" value="4">
            <span class="value-display" id="numBladesValue">4</span>
        </div>
        <div class="control-group">
            <label for="bladeCoreOpacity">Blade Core Opacity:</label>
            <input type="range" id="bladeCoreOpacity" min="0" max="1" step="0.01" value="0.8">
            <span class="value-display" id="bladeCoreOpacityValue">0.80</span>
        </div>
        <div class="control-group">
            <label for="bladeEdgeOpacityFactor">Blade Edge Opacity Factor (rel. to Disc):</label>
            <input type="range" id="bladeEdgeOpacityFactor" min="0" max="1" step="0.01" value="0.3">
            <span class="value-display" id="bladeEdgeOpacityFactorValue">0.30</span>
        </div>
        <div class="control-group">
            <label for="bladeGradientDensity">Blade Gradient Density (Core Width):</label>
            <input type="range" id="bladeGradientDensity" min="0.05" max="0.95" step="0.01" value="0.5">
            <span class="value-display" id="bladeGradientDensityValue">0.50</span>
        </div>
        <div class="control-group">
            <label for="bladeTipWidth">Blade Tip Width:</label>
            <input type="range" id="bladeTipWidth" min="0" max="120" step="1" value="20"> <!-- Min 0 for pointed -->
            <span class="value-display" id="bladeTipWidthValue">20</span>
        </div>
        <button id="randomizeButton">Randomize Parameters</button>
        <button id="pauseButton">Pause</button>
    </div>

    <script>
        const canvas = document.getElementById('rotorCanvas');
        const ctx = canvas.getContext('2d');

        const controlsPanelNominalWidth = 380;
        const controlsPanelMargin = 20;
        const pagePadding = 40; 
        
        let availableWidthForCanvas = window.innerWidth - controlsPanelNominalWidth - controlsPanelMargin - pagePadding;
        let availableHeightForCanvas = window.innerHeight - pagePadding;
        let canvasDim = Math.min(availableWidthForCanvas, availableHeightForCanvas, 600);
        canvas.width = Math.max(100, canvasDim); 
        canvas.height = Math.max(100, canvasDim);

        let currentAngle = 0;
        let animationFrameId = null;
        let isPaused = false;

        const controls = {
            speed: document.getElementById('speed'),
            discOpacity: document.getElementById('discOpacity'),
            discRadius: document.getElementById('discRadius'),
            numBlades: document.getElementById('numBlades'),
            bladeCoreOpacity: document.getElementById('bladeCoreOpacity'),
            bladeEdgeOpacityFactor: document.getElementById('bladeEdgeOpacityFactor'),
            bladeGradientDensity: document.getElementById('bladeGradientDensity'),
            bladeTipWidth: document.getElementById('bladeTipWidth'),
        };

        const controlValues = {
            speed: parseFloat(controls.speed.value),
            discOpacity: parseFloat(controls.discOpacity.value),
            discRadius: parseFloat(controls.discRadius.value),
            numBlades: parseInt(controls.numBlades.value),
            bladeCoreOpacity: parseFloat(controls.bladeCoreOpacity.value),
            bladeEdgeOpacityFactor: parseFloat(controls.bladeEdgeOpacityFactor.value),
            bladeGradientDensity: parseFloat(controls.bladeGradientDensity.value),
            bladeTipWidth: parseFloat(controls.bladeTipWidth.value),
        };

        const valueSpans = {
            speed: document.getElementById('speedValue'),
            discOpacity: document.getElementById('discOpacityValue'),
            discRadius: document.getElementById('discRadiusValue'),
            numBlades: document.getElementById('numBladesValue'),
            bladeCoreOpacity: document.getElementById('bladeCoreOpacityValue'),
            bladeEdgeOpacityFactor: document.getElementById('bladeEdgeOpacityFactorValue'),
            bladeGradientDensity: document.getElementById('bladeGradientDensityValue'),
            bladeTipWidth: document.getElementById('bladeTipWidthValue'),
        };

        const pauseButton = document.getElementById('pauseButton');

        function getDisplayPrecision(key) {
            const controlElement = controls[key];
            if (!controlElement) return 0;
            const step = parseFloat(controlElement.step);
            if (['numBlades', 'bladeTipWidth', 'discRadius'].includes(key)) return 0;
            if (key === 'speed' || step === 0.001) return 3;
            if (step === 0.01 || step === 0.05) return 2;
            return 2;
        }

        for (const key in controls) {
            const controlElement = controls[key];
            controlElement.addEventListener('input', (e) => {
                const value = parseFloat(e.target.value);
                controlValues[key] = value;
                if (valueSpans[key]) {
                    valueSpans[key].textContent = value.toFixed(getDisplayPrecision(key));
                }
            });
        }

        const randomizeButton = document.getElementById('randomizeButton');
        randomizeButton.addEventListener('click', () => {
            for (const key in controls) {
                const controlElement = controls[key];
                if (controlElement.type === "range") {
                    const min = parseFloat(controlElement.min);
                    const max = parseFloat(controlElement.max);
                    const step = parseFloat(controlElement.step);
                    let randomValue;
                    if (['numBlades', 'bladeTipWidth', 'discRadius'].includes(key) || step === 1) {
                        randomValue = Math.floor(Math.random() * (max - min + 1)) + min;
                    } else {
                        const numSteps = Math.round((max - min) / step);
                        randomValue = min + (Math.floor(Math.random() * (numSteps + 1)) * step);
                        randomValue = Math.min(max, parseFloat(randomValue.toFixed(5)));
                    }
                    controlElement.value = randomValue;
                    controlValues[key] = parseFloat(randomValue);
                    if (valueSpans[key]) {
                        valueSpans[key].textContent = parseFloat(randomValue).toFixed(getDisplayPrecision(key));
                    }
                }
            }
        });

        pauseButton.addEventListener('click', () => {
            isPaused = !isPaused;
            if (isPaused) {
                if (animationFrameId) cancelAnimationFrame(animationFrameId);
                pauseButton.textContent = 'Play';
            } else {
                animate();
                pauseButton.textContent = 'Pause';
            }
        });

        const discColorRGB = "180, 180, 190"; 
        const bladeCoreColorRGB = "30, 30, 35"; 

        function drawRotor() {
            const centerX = canvas.width / 2;
            const centerY = canvas.height / 2;
            const rotorRadius = controlValues.discRadius;

            ctx.clearRect(0, 0, canvas.width, canvas.height);

            if (controlValues.discOpacity > 0) {
                ctx.beginPath();
                ctx.arc(centerX, centerY, rotorRadius, 0, Math.PI * 2);
                ctx.fillStyle = `rgba(${discColorRGB}, ${controlValues.discOpacity})`;
                ctx.fill();
            }

            const numBlades = Math.floor(controlValues.numBlades);
            if (numBlades > 0) {
                const angleIncrement = (Math.PI * 2) / numBlades;
                
                const userBladeTipHalfWidth = Math.max(0, controlValues.bladeTipWidth / 2); // Ensure non-negative

                const coreOpacity = controlValues.bladeCoreOpacity;
                const edgeTargetOpacity = controlValues.discOpacity * controlValues.bladeEdgeOpacityFactor;
                const gradientStopCenter = controlValues.bladeGradientDensity;
                const gradientStopEdge = Math.max(0, (0.5 - gradientStopCenter / 2));

                const bladeBaseHubRadius = rotorRadius * 0.08; // Radius of the small central hub/void
                const bladeWidthAtBase = Math.max(1, controlValues.bladeTipWidth * 0.05); // Tapered width at this base radius

                ctx.save();
                ctx.translate(centerX, centerY);
                ctx.rotate(currentAngle);

                for (let i = 0; i < numBlades; i++) {
                    const bladeAngle = i * angleIncrement; // Angle of the blade's centerline
                    
                    // --- Gradient Setup ---
                    // Gradient spans across the user-defined tip width
                    const gradSpan = userBladeTipHalfWidth; 
                    const gradX0 = Math.cos(bladeAngle + Math.PI/2) * gradSpan;
                    const gradY0 = Math.sin(bladeAngle + Math.PI/2) * gradSpan;
                    const gradient = ctx.createLinearGradient(gradX0, gradY0, -gradX0, -gradY0);
                    
                    gradient.addColorStop(0,                      `rgba(${discColorRGB}, ${edgeTargetOpacity})`);
                    gradient.addColorStop(gradientStopEdge,       `rgba(${bladeCoreColorRGB}, ${coreOpacity})`);
                    gradient.addColorStop(1 - gradientStopEdge,   `rgba(${bladeCoreColorRGB}, ${coreOpacity})`);
                    gradient.addColorStop(1,                      `rgba(${discColorRGB}, ${edgeTargetOpacity})`);
                    ctx.fillStyle = gradient;

                    // --- Blade Shape Calculation ---
                    let arcCenterX, arcCenterY, arcRadius, shoulderAngleOffset;

                    if (userBladeTipHalfWidth < 0.1) { // Pointed tip (or nearly flat)
                        arcRadius = 0; // No arc
                        // Tip of the blade is at rotorRadius along bladeAngle
                        arcCenterX = Math.cos(bladeAngle) * rotorRadius; 
                        arcCenterY = Math.sin(bladeAngle) * rotorRadius;
                        shoulderAngleOffset = 0; // Shoulders converge at the tip
                    } else if (userBladeTipHalfWidth >= rotorRadius - bladeBaseHubRadius && rotorRadius > bladeBaseHubRadius) { 
                        // Tip width is so large it effectively forms a semicircle from the hub radius
                        // This case means straight part would be zero or negative if starting from hub
                        arcRadius = rotorRadius - bladeBaseHubRadius;
                        arcCenterX = Math.cos(bladeAngle) * bladeBaseHubRadius; 
                        arcCenterY = Math.sin(bladeAngle) * bladeBaseHubRadius;
                        shoulderAngleOffset = Math.PI / 2;
                         if (arcRadius < 0) arcRadius = 0; // Safety for tiny rotorRadius
                    } else { // Standard case: rounded tip within rotorRadius, tapering from hub
                        arcRadius = userBladeTipHalfWidth;
                        const straightLengthFromHub = rotorRadius - arcRadius - bladeBaseHubRadius;
                        
                        if (straightLengthFromHub < 0) { // Tip radius is too large for the space after hub
                           // Adjust arcRadius to fit, center of arc remains at hub
                            arcRadius = rotorRadius - bladeBaseHubRadius;
                            arcCenterX = Math.cos(bladeAngle) * bladeBaseHubRadius;
                            arcCenterY = Math.sin(bladeAngle) * bladeBaseHubRadius;
                            if (arcRadius < 0) arcRadius = 0;
                        } else {
                            arcCenterX = Math.cos(bladeAngle) * (bladeBaseHubRadius + straightLengthFromHub);
                            arcCenterY = Math.sin(bladeAngle) * (bladeBaseHubRadius + straightLengthFromHub);
                        }
                        shoulderAngleOffset = Math.PI / 2;
                    }

                    // --- Calculate Shoulder Points (where straight sides meet the arc/tip) ---
                    const p_shoulder_left_x = arcCenterX + Math.cos(bladeAngle + shoulderAngleOffset) * arcRadius;
                    const p_shoulder_left_y = arcCenterY + Math.sin(bladeAngle + shoulderAngleOffset) * arcRadius;
                    const p_shoulder_right_x = arcCenterX + Math.cos(bladeAngle - shoulderAngleOffset) * arcRadius;
                    const p_shoulder_right_y = arcCenterY + Math.sin(bladeAngle - shoulderAngleOffset) * arcRadius;

                    // --- Calculate Inner Base Points (on the hub radius) ---
                    const innerBaseHalfWidth = bladeWidthAtBase / 2;
                    const inner_base_center_x = Math.cos(bladeAngle) * bladeBaseHubRadius;
                    const inner_base_center_y = Math.sin(bladeAngle) * bladeBaseHubRadius;

                    const p_inner_left_x = inner_base_center_x + Math.cos(bladeAngle + Math.PI/2) * innerBaseHalfWidth;
                    const p_inner_left_y = inner_base_center_y + Math.sin(bladeAngle + Math.PI/2) * innerBaseHalfWidth;
                    const p_inner_right_x = inner_base_center_x + Math.cos(bladeAngle - Math.PI/2) * innerBaseHalfWidth;
                    const p_inner_right_y = inner_base_center_y + Math.sin(bladeAngle - Math.PI/2) * innerBaseHalfWidth;

                    // --- Draw Path ---
                    ctx.beginPath();
                    ctx.moveTo(p_inner_left_x, p_inner_left_y);
                    ctx.lineTo(p_shoulder_left_x, p_shoulder_left_y);

                    if (arcRadius > 0.1) { // Only draw arc if it's visually significant
                        ctx.arc(arcCenterX, arcCenterY, arcRadius, 
                                bladeAngle + shoulderAngleOffset, 
                                bladeAngle - shoulderAngleOffset, 
                                false); // 'false' for clockwise, creates the outward curve
                    } else { 
                        // If no significant arc (pointed/flat tip), line to the other shoulder (which is effectively the tip)
                        ctx.lineTo(p_shoulder_right_x, p_shoulder_right_y);
                    }
                    
                    ctx.lineTo(p_inner_right_x, p_inner_right_y);
                    ctx.closePath();
                    ctx.fill();
                }
                ctx.restore();
            }

            currentAngle += controlValues.speed;
            if (currentAngle > Math.PI * 2) currentAngle -= Math.PI * 2;
        }
        
        function animate() {
            if (!isPaused) drawRotor();
            animationFrameId = requestAnimationFrame(animate);
        }

        for (const key in controls) {
            const controlElement = controls[key];
            if (valueSpans[key]) {
                 valueSpans[key].textContent = parseFloat(controlElement.value).toFixed(getDisplayPrecision(key));
            }
        }
        
        animate();

        window.addEventListener('resize', () => {
            availableWidthForCanvas = window.innerWidth - controlsPanelNominalWidth - controlsPanelMargin - pagePadding;
            availableHeightForCanvas = window.innerHeight - pagePadding;
            canvasDim = Math.min(availableWidthForCanvas, availableHeightForCanvas, 600);
            canvas.width = Math.max(100, canvasDim);
            canvas.height = Math.max(100, canvasDim);
            if (!isPaused) drawRotor();
        });
    </script>
</body>
</html>