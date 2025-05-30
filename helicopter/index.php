<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Advanced Helicopter Sim</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            align-items: flex-start;
            gap: 30px;
            margin: 20px;
            background-color: #e8eff5;
            color: #333;
            overflow-x: hidden;
        }
        #canvasContainer {
            border: 2px solid #a0b0c0;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            background-color: #ffffff;
            cursor: crosshair;
        }
        #controls {
            display: flex;
            flex-direction: column;
            gap: 12px; /* Reduced gap slightly */
            padding: 20px;
            border: 1px solid #c0d0e0;
            background-color: #f8f9fa;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            width: 330px; /* Slightly wider */
            max-height: calc(100vh - 40px);
            overflow-y: auto;
        }
        #controls h2 {
            margin-top: 0;
            color: #0056b3;
            border-bottom: 1px solid #ddeeff;
            padding-bottom: 10px;
            font-size: 1.4em;
        }
        .control-group {
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px dashed #e0e0e0;
        }
        .control-group:last-child { border-bottom: none; }
        label {
            display: block;
            margin-bottom: 6px;
            font-weight: bold;
            font-size: 0.9em;
            color: #495057;
        }
        input[type="color"], input[type="range"], input[type="number"], input[type="checkbox"] {
            width: calc(100% - 12px);
            padding: 6px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            box-sizing: border-box;
        }
        input[type="range"] { cursor: pointer; }
        input[type="checkbox"] { width: auto; margin-right: 5px; vertical-align: middle; }
        .checkbox-label { font-weight: normal; font-size: 0.9em; vertical-align: middle; }
        input[type="color"] { height: 34px; padding: 3px; }
        .value-display { font-size: 0.85em; color: #007bff; margin-left: 8px; font-weight: bold; }
        #instructions {
            margin-top: 15px; padding: 15px; background-color: #d1ecf1;
            border: 1px solid #bee5eb; border-radius: 4px; font-size: 0.9em; color: #0c5460;
        }
        #instructions p { margin: 5px 0; }
        #instructions strong { color: #004085; }

        @media (max-width: 900px) {
            body { flex-direction: column; align-items: center; }
            #controls { width: 90%; max-width: 450px; margin-top: 20px; }
        }
    </style>
</head>
<body>
    <div id="canvasContainer">
        <canvas id="helicopterCanvas" width="700" height="600"></canvas>
    </div>

    <div id="controls">
        <h2>Helicopter Customizer</h2>
        <!-- General Properties -->
        <div class="control-group">
            <label for="bodyColor">Body Color:</label> <input type="color" id="bodyColor">
            <label for="cockpitColor">Cockpit Color:</label> <input type="color" id="cockpitColor">
            <label for="rotorColor">Rotor Color:</label> <input type="color" id="rotorColor">
        </div>
        <div class="control-group">
            <label for="bodyWidth">Body Width: <span id="bodyWidthVal" class="value-display"></span></label>
            <input type="range" id="bodyWidth" min="20" max="100">
            <label for="bodyHeight">Fuselage Length: <span id="bodyHeightVal" class="value-display"></span></label>
            <input type="range" id="bodyHeight" min="40" max="180">
        </div>
        <!-- Tail Section -->
        <div class="control-group">
            <label for="tailBoomLength">Tail Boom Length: <span id="tailBoomLengthVal" class="value-display"></span></label>
            <input type="range" id="tailBoomLength" min="20" max="150">
            <label for="tailBoomHeight">Tail Boom Width: <span id="tailBoomHeightVal" class="value-display"></span></label>
            <input type="range" id="tailBoomHeight" min="8" max="40">
            <label for="tailRotorRadius">Tail Rotor Radius: <span id="tailRotorRadiusVal" class="value-display"></span></label>
            <input type="range" id="tailRotorRadius" min="8" max="50">
        </div>
        <!-- Main Rotor Section -->
        <div class="control-group">
            <label for="mainRotorRadius">Main Rotor Radius: <span id="mainRotorRadiusVal" class="value-display"></span></label>
            <input type="range" id="mainRotorRadius" min="30" max="200">
            <label for="mainRotorBlades">Main Rotor Blades: <span id="mainRotorBladesVal" class="value-display"></span></label>
            <input type="number" id="mainRotorBlades" min="2" max="8" step="1">
            <label for="rotorSpeed">Rotor Speed: <span id="rotorSpeedVal" class="value-display"></span></label>
            <input type="range" id="rotorSpeed" min="0.01" max="0.6" step="0.01">
            <label for="rotorBlurAmount">Rotor Blur: <span id="rotorBlurAmountVal" class="value-display"></span></label>
            <input type="range" id="rotorBlurAmount" min="0" max="1" step="0.01">
        </div>
        <!-- Armaments -->
        <div class="control-group">
            <input type="checkbox" id="hasRocketPods">
            <label for="hasRocketPods" class="checkbox-label">Show Rocket Pods</label>
            <div id="rocketPodControls" style="margin-top:10px; display:none;">
                 <label for="rocketPodColor">Pod Color:</label> <input type="color" id="rocketPodColor">
                 <label for="rocketPodWidth">Pod Width: <span id="rocketPodWidthVal" class="value-display"></span></label>
                 <input type="range" id="rocketPodWidth" min="5" max="30">
                 <label for="rocketPodHeight">Pod Length: <span id="rocketPodHeightVal" class="value-display"></span></label>
                 <input type="range" id="rocketPodHeight" min="10" max="60">
            </div>
        </div>
        <!-- Movement -->
        <div class="control-group">
            <label for="positionEasing">Position Easing: <span id="positionEasingVal" class="value-display"></span></label>
            <input type="range" id="positionEasing" min="0.01" max="0.2" step="0.01">
        </div>

        <div id="instructions">
            <p><strong>Interaction:</strong></p>
            <p>Move mouse to guide helicopter.</p>
            <p>Click to fire rockets!</p>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const canvas = document.getElementById('helicopterCanvas');
            const ctx = canvas.getContext('2d');

            let mouse = { x: canvas.width / 2, y: canvas.height / 2 };
            let rockets = [];

            let helicopter = {
                x: canvas.width / 2,
                y: canvas.height / 2,
                angle: -Math.PI / 2, // Start pointing up (towards negative Y)
                bodyWidth: 50,
                bodyHeight: 100,
                bodyColor: '#4A5A52',
                cockpitColorHex: '#A0D0F0',
                cockpitAlpha: 0.6,
                rotorColor: '#282828',
                tailBoomLength: 80,
                tailBoomHeight: 15, // Width at base of tail boom
                tailRotorRadius: 22,
                mainRotorRadius: 90,
                mainRotorBlades: 4,
                rotorAngle: 0,
                rotorSpeed: 0.15,
                rotorBlurAmount: 0.3, // 0 to 1
                tailRotorAngle: 0,
                tailRotorSpeed: 0.45, // Sped up tail rotor slightly
                hasRocketPods: true,
                rocketPodColor: '#3D4530',
                rocketPodWidth: 10,
                rocketPodHeight: 40,
                rocketPodOffsetX: 35,
                rocketPodOffsetY: -10,
                pylonWidth: 5, // Width of pylon connecting pod to body
                
                positionEasing: 0.05,
                // rotationEasing removed
                
                rocketSpeed: 7,
                rocketSize: 4,
                rocketColor: '#FF8C00' // Orange
            };

            function hexToRgba(hex, alpha) {
                const r = parseInt(hex.slice(1, 3), 16);
                const g = parseInt(hex.slice(3, 5), 16);
                const b = parseInt(hex.slice(5, 7), 16);
                return `rgba(${r}, ${g}, ${b}, ${alpha})`;
            }

            function fireRocket() {
                const noseOffset = -helicopter.bodyHeight / 2 * 0.8; // Fire from near the nose
                const spread = helicopter.bodyWidth * 0.2; // Spread rockets slightly

                for (let i = -1; i <= 1; i+=2) { // Fire two rockets, one left, one right of center
                    rockets.push({
                        x: helicopter.x + Math.cos(helicopter.angle) * (spread * i) + Math.sin(helicopter.angle) * noseOffset,
                        y: helicopter.y + Math.sin(helicopter.angle) * (spread * i) - Math.cos(helicopter.angle) * noseOffset,
                        angle: helicopter.angle,
                        vx: Math.sin(helicopter.angle) * helicopter.rocketSpeed,
                        vy: -Math.cos(helicopter.angle) * helicopter.rocketSpeed,
                    });
                }
            }
            
            canvas.addEventListener('mousedown', (e) => {
                if (e.button === 0) { // Left mouse button
                    fireRocket();
                }
            });


            function drawHelicopter() {
                ctx.save();
                ctx.translate(helicopter.x, helicopter.y);
                ctx.rotate(helicopter.angle);

                // --- Draw Main Rotor Blur Disk (if blur > 0) ---
                if (helicopter.rotorBlurAmount > 0) {
                    ctx.fillStyle = hexToRgba(helicopter.rotorColor, helicopter.rotorBlurAmount * 0.25); // Max 0.25 alpha for blur disk
                    ctx.beginPath();
                    ctx.arc(0, 0, helicopter.mainRotorRadius, 0, Math.PI * 2);
                    ctx.fill();
                }
                
                // --- Draw Body (Fuselage) ---
                ctx.fillStyle = helicopter.bodyColor;
                ctx.beginPath();
                const bodyTopY = -helicopter.bodyHeight / 2;
                const bodyBottomY = helicopter.bodyHeight / 2;
                const bodyHalfWidth = helicopter.bodyWidth / 2;
                ctx.moveTo(-bodyHalfWidth, bodyTopY + 15); 
                ctx.quadraticCurveTo(0, bodyTopY - 10, bodyHalfWidth, bodyTopY + 15);
                ctx.lineTo(bodyHalfWidth, bodyBottomY - 20); 
                ctx.quadraticCurveTo(bodyHalfWidth * 0.8, bodyBottomY, 0, bodyBottomY); 
                ctx.quadraticCurveTo(-bodyHalfWidth * 0.8, bodyBottomY, -bodyHalfWidth, bodyBottomY - 20);
                ctx.lineTo(-bodyHalfWidth, bodyTopY + 15);
                ctx.closePath();
                ctx.fill();
                
                // --- Draw Cockpit Glass ---
                ctx.fillStyle = hexToRgba(helicopter.cockpitColorHex, helicopter.cockpitAlpha);
                ctx.beginPath();
                const cockpitFrontOffset = 5;
                const cockpitHeight = helicopter.bodyHeight * 0.35;
                const cockpitWidth = helicopter.bodyWidth * 0.9;
                ctx.ellipse(0, bodyTopY + cockpitHeight * 0.4 - cockpitFrontOffset, cockpitWidth / 2, cockpitHeight / 2, 0, Math.PI * 0.9, Math.PI * 0.1, true);
                ctx.closePath();
                ctx.fill();

                // --- Draw Tail Boom ---
                const tailBoomAttachY = bodyBottomY * 0.6;
                ctx.fillStyle = helicopter.bodyColor;
                ctx.beginPath();
                ctx.moveTo(-helicopter.tailBoomHeight / 2, tailBoomAttachY);
                ctx.lineTo(helicopter.tailBoomHeight / 2, tailBoomAttachY);
                ctx.lineTo(helicopter.tailBoomHeight / 3, tailBoomAttachY + helicopter.tailBoomLength); // More tapered
                ctx.lineTo(-helicopter.tailBoomHeight / 3, tailBoomAttachY + helicopter.tailBoomLength);
                ctx.closePath();
                ctx.fill();

                // --- Draw Tail Rotor ---
                // Positioned on helicopter's right side of tail boom (common config)
                // Axis of rotation is parallel to helicopter's Y (longitudinal) axis. Blades spin in local X-Z plane.
                // From overhead, we see blades moving left/right from the hub.
                const tailRotorHubX = helicopter.tailBoomHeight / 3 + 3; // On right side of tapered boom end
                const tailRotorHubY = tailBoomAttachY + helicopter.tailBoomLength * 0.95;

                ctx.save();
                ctx.translate(tailRotorHubX, tailRotorHubY); // Move to hub on side of tail
                
                // Hub
                ctx.fillStyle = '#444444';
                ctx.beginPath();
                ctx.arc(0, 0, 4, 0, Math.PI * 2);
                ctx.fill();
                
                // Blades
                ctx.strokeStyle = helicopter.rotorColor;
                ctx.lineWidth = 2;
                const numTailBlades = 4; // Common to have more tail blades
                for (let i = 0; i < numTailBlades; i++) {
                    ctx.save();
                    ctx.rotate(helicopter.tailRotorAngle + (i * (Math.PI * 2 / numTailBlades)));
                    ctx.beginPath();
                    ctx.moveTo(-helicopter.tailRotorRadius, 0); // Blades extend along local X
                    ctx.lineTo(helicopter.tailRotorRadius, 0);
                    ctx.stroke();
                    ctx.restore();
                }
                ctx.restore(); // Restore from tail rotor transform

                // --- Draw Rocket Pods & Pylons (if enabled) ---
                if (helicopter.hasRocketPods) {
                    const podY = helicopter.rocketPodOffsetY;
                    const pylonAttachPointY = helicopter.bodyHeight * 0.1; // Y on fuselage where pylon starts
                    const pylonOuterPointY = podY - helicopter.rocketPodHeight * 0.3; // Y where pylon meets pod

                    // Pylons
                    ctx.fillStyle = helicopter.bodyColor; // Pylons match body
                    ctx.beginPath(); // Left Pylon
                    ctx.moveTo(-helicopter.bodyWidth/2 +5, pylonAttachPointY); // Inner top
                    ctx.lineTo(-helicopter.rocketPodOffsetX + helicopter.rocketPodWidth/2, pylonOuterPointY); // Outer top
                    ctx.lineTo(-helicopter.rocketPodOffsetX - helicopter.rocketPodWidth/2, pylonOuterPointY); // Outer bottom
                    ctx.lineTo(-helicopter.bodyWidth/2 +5, pylonAttachPointY + helicopter.pylonWidth); // Inner bottom
                    ctx.closePath();
                    ctx.fill();

                    ctx.beginPath(); // Right Pylon
                    ctx.moveTo(helicopter.bodyWidth/2 -5, pylonAttachPointY);
                    ctx.lineTo(helicopter.rocketPodOffsetX - helicopter.rocketPodWidth/2, pylonOuterPointY);
                    ctx.lineTo(helicopter.rocketPodOffsetX + helicopter.rocketPodWidth/2, pylonOuterPointY);
                    ctx.lineTo(helicopter.bodyWidth/2 -5, pylonAttachPointY + helicopter.pylonWidth);
                    ctx.closePath();
                    ctx.fill();

                    // Pods
                    ctx.fillStyle = helicopter.rocketPodColor;
                    ctx.fillRect(-helicopter.rocketPodOffsetX - helicopter.rocketPodWidth / 2, podY - helicopter.rocketPodHeight / 2, helicopter.rocketPodWidth, helicopter.rocketPodHeight);
                    ctx.fillRect(helicopter.rocketPodOffsetX - helicopter.rocketPodWidth / 2, podY - helicopter.rocketPodHeight / 2, helicopter.rocketPodWidth, helicopter.rocketPodHeight);
                }

                // --- Draw Main Rotor Blades ---
                // Blades are more transparent if blur is high
                const bladeAlpha = Math.max(0.1, 1 - helicopter.rotorBlurAmount * 0.9);
                if (bladeAlpha > 0.1 || helicopter.rotorBlurAmount < 0.1) { // Draw blades if not fully blurred
                    ctx.strokeStyle = hexToRgba(helicopter.rotorColor, bladeAlpha);
                    ctx.lineWidth = 6;
                    for (let i = 0; i < helicopter.mainRotorBlades; i++) {
                        ctx.save();
                        ctx.rotate(helicopter.rotorAngle + (i * (Math.PI * 2 / helicopter.mainRotorBlades)));
                        ctx.beginPath();
                        ctx.moveTo(0, -helicopter.mainRotorRadius * 0.1); // Start slightly out from center
                        ctx.lineTo(0, -helicopter.mainRotorRadius);
                        ctx.stroke();
                        ctx.restore();
                    }
                }
                // Main Rotor Hub
                ctx.fillStyle = '#383838';
                ctx.beginPath();
                ctx.arc(0, 0, 12, 0, Math.PI * 2);
                ctx.fill();

                ctx.restore(); // Restore main transform state
            }
            
            function drawRockets() {
                for (let i = rockets.length - 1; i >= 0; i--) {
                    const r = rockets[i];
                    r.x += r.vx;
                    r.y += r.vy;

                    ctx.fillStyle = helicopter.rocketColor;
                    ctx.beginPath();
                    ctx.arc(r.x, r.y, helicopter.rocketSize, 0, Math.PI * 2);
                    ctx.fill();
                    
                    // Simple trail
                    ctx.fillStyle = hexToRgba(helicopter.rocketColor, 0.5);
                    ctx.beginPath();
                    ctx.arc(r.x - r.vx*0.5, r.y - r.vy*0.5, helicopter.rocketSize*0.8, 0, Math.PI * 2);
                    ctx.fill();
                     ctx.fillStyle = hexToRgba("#FFFFFF", 0.3); // Whiteish core
                    ctx.beginPath();
                    ctx.arc(r.x - r.vx, r.y - r.vy, helicopter.rocketSize*0.6, 0, Math.PI * 2);
                    ctx.fill();


                    if (r.x < 0 || r.x > canvas.width || r.y < 0 || r.y > canvas.height) {
                        rockets.splice(i, 1);
                    }
                }
            }


            // --- Control Setup (Mostly the same, ensure new controls are mapped) ---
            const controlsMap = {
                bodyColor: { el: document.getElementById('bodyColor'), type: 'color' },
                cockpitColorHex: { el: document.getElementById('cockpitColor'), type: 'color', target: 'cockpitColorHex' },
                rotorColor: { el: document.getElementById('rotorColor'), type: 'color' },
                bodyWidth: { el: document.getElementById('bodyWidth'), valEl: document.getElementById('bodyWidthVal'), type: 'rangeInt' },
                bodyHeight: { el: document.getElementById('bodyHeight'), valEl: document.getElementById('bodyHeightVal'), type: 'rangeInt' },
                tailBoomLength: { el: document.getElementById('tailBoomLength'), valEl: document.getElementById('tailBoomLengthVal'), type: 'rangeInt' },
                tailBoomHeight: { el: document.getElementById('tailBoomHeight'), valEl: document.getElementById('tailBoomHeightVal'), type: 'rangeInt' },
                tailRotorRadius: { el: document.getElementById('tailRotorRadius'), valEl: document.getElementById('tailRotorRadiusVal'), type: 'rangeInt' },
                mainRotorRadius: { el: document.getElementById('mainRotorRadius'), valEl: document.getElementById('mainRotorRadiusVal'), type: 'rangeInt' },
                mainRotorBlades: { el: document.getElementById('mainRotorBlades'), valEl: document.getElementById('mainRotorBladesVal'), type: 'numberInt' },
                rotorSpeed: { el: document.getElementById('rotorSpeed'), valEl: document.getElementById('rotorSpeedVal'), type: 'rangeFloat', decimals: 2 },
                rotorBlurAmount: { el: document.getElementById('rotorBlurAmount'), valEl: document.getElementById('rotorBlurAmountVal'), type: 'rangeFloat', decimals: 2 },
                hasRocketPods: { el: document.getElementById('hasRocketPods'), type: 'checkbox' },
                rocketPodColor: { el: document.getElementById('rocketPodColor'), type: 'color' },
                rocketPodWidth: { el: document.getElementById('rocketPodWidth'), valEl: document.getElementById('rocketPodWidthVal'), type: 'rangeInt' },
                rocketPodHeight: { el: document.getElementById('rocketPodHeight'), valEl: document.getElementById('rocketPodHeightVal'), type: 'rangeInt' },
                positionEasing: { el: document.getElementById('positionEasing'), valEl: document.getElementById('positionEasingVal'), type: 'rangeFloat', decimals: 2 },
            };
            const rocketPodControlsDiv = document.getElementById('rocketPodControls');
            function updateControlsDisplay() { /* ... (same as before, ensure all keys covered) ... */ 
                for (const key in controlsMap) {
                    if (Object.hasOwnProperty.call(controlsMap, key)) {
                        const control = controlsMap[key];
                        const targetKey = control.target || key;
                        if (control.el) {
                            if (control.type === 'checkbox') {
                                control.el.checked = helicopter[targetKey];
                                if (key === 'hasRocketPods') {
                                     rocketPodControlsDiv.style.display = helicopter.hasRocketPods ? 'block' : 'none';
                                }
                            } else { control.el.value = helicopter[targetKey]; }
                            if (control.valEl) {
                                if (control.type === 'rangeFloat') { control.valEl.textContent = parseFloat(helicopter[targetKey]).toFixed(control.decimals || 2); }
                                else { control.valEl.textContent = helicopter[targetKey]; }
                            }
                        }
                    }
                }
            }
            for (const key in controlsMap) { /* ... (same as before, ensure all keys covered) ... */ 
                 if (Object.hasOwnProperty.call(controlsMap, key)) {
                    const control = controlsMap[key];
                    if (control.el) {
                        control.el.addEventListener('input', (e) => {
                            let value; const targetKey = control.target || key;
                            if (control.type === 'checkbox') {
                                value = e.target.checked;
                                if (key === 'hasRocketPods') { rocketPodControlsDiv.style.display = value ? 'block' : 'none';}
                            } else if (control.type === 'rangeInt' || control.type === 'numberInt') { value = parseInt(e.target.value, 10);
                            } else if (control.type === 'rangeFloat') { value = parseFloat(e.target.value);
                            } else { value = e.target.value; }
                            helicopter[targetKey] = value;
                            if (control.valEl) {
                                if (control.type === 'rangeFloat') { control.valEl.textContent = value.toFixed(control.decimals || 2); }
                                else if (control.type !== 'checkbox') { control.valEl.textContent = value; }
                            }
                        });
                    }
                }
            }
            
            canvas.addEventListener('mousemove', (e) => {
                const rect = canvas.getBoundingClientRect();
                mouse.x = e.clientX - rect.left;
                mouse.y = e.clientY - rect.top;
            });

            function updateHelicopterMovementAndOrientation() {
                const dx = mouse.x - helicopter.x;
                const dy = mouse.y - helicopter.y;
                helicopter.x += dx * helicopter.positionEasing;
                helicopter.y += dy * helicopter.positionEasing;

                // Helicopter's "angle 0" points towards negative Y (UP on canvas).
                // We want this "angle 0" direction to align with the vector from helicopter to mouse.
                // Angle of vector from helicopter to mouse: Math.atan2(dy, dx)
                // This angle is 0 for +X (right).
                // To make helicopter's "UP" (angle 0) point towards this, we need to add PI/2.
                let targetAngle = Math.atan2(dy, dx) + Math.PI / 2;
                
                // Snap rotation (no easing)
                helicopter.angle = targetAngle;

                // Normalize helicopter.angle to [0, 2PI)
                while (helicopter.angle >= Math.PI * 2) helicopter.angle -= Math.PI * 2;
                while (helicopter.angle < 0) helicopter.angle += Math.PI * 2;

                const padding = Math.max(helicopter.mainRotorRadius, helicopter.bodyHeight/2) + 10;
                helicopter.x = Math.max(padding, Math.min(canvas.width - padding, helicopter.x));
                helicopter.y = Math.max(padding, Math.min(canvas.height - padding, helicopter.y));
            }

            function gameLoop() {
                helicopter.rotorAngle = (helicopter.rotorAngle + helicopter.rotorSpeed) % (Math.PI * 2);
                helicopter.tailRotorAngle = (helicopter.tailRotorAngle + helicopter.tailRotorSpeed) % (Math.PI * 2);
                updateHelicopterMovementAndOrientation();
                
                ctx.clearRect(0, 0, canvas.width, canvas.height); // Clear before drawing
                drawHelicopter();
                drawRockets();
                requestAnimationFrame(gameLoop);
            }

            updateControlsDisplay();
            gameLoop();
        });
    </script>
</body>
</html>