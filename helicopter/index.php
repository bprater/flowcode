<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hyper Helicopter Sim</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            align-items: flex-start;
            gap: 30px;
            margin: 20px;
            background-color: #2c3e50; /* Darker background */
            color: #ecf0f1; /* Light text */
            overflow-x: hidden;
        }
        #canvasContainer {
            border: 2px solid #3498db; /* Bright blue border */
            box-shadow: 0 0 20px rgba(52, 152, 219, 0.5);
            background-color: #1c2833; /* Dark canvas background */
            cursor: crosshair;
        }
        #controls {
            display: flex;
            flex-direction: column;
            gap: 12px;
            padding: 20px;
            border: 1px solid #34495e;
            background-color: #34495e; /* Dark controls panel */
            color: #ecf0f1;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            width: 350px;
            max-height: calc(100vh - 40px);
            overflow-y: auto;
        }
        #controls h2 {
            margin-top: 0;
            color: #3498db; /* Bright blue title */
            border-bottom: 1px solid #2c3e50;
            padding-bottom: 10px;
            font-size: 1.5em;
        }
        .control-group {
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px dashed #2c3e50;
        }
        .control-group:last-child { border-bottom: none; }
        label {
            display: block;
            margin-bottom: 6px;
            font-weight: bold;
            font-size: 0.9em;
            color: #bdc3c7; /* Lighter gray label text */
        }
        input[type="color"], input[type="range"], input[type="number"], input[type="checkbox"] {
            width: calc(100% - 12px);
            padding: 7px;
            border: 1px solid #2c3e50;
            background-color: #2c3e50; /* Darker input background */
            color: #ecf0f1; /* Light input text */
            border-radius: 4px;
            box-sizing: border-box;
        }
        input[type="range"] { cursor: pointer; }
        input[type="checkbox"] { width: auto; margin-right: 5px; vertical-align: middle; }
        .checkbox-label { font-weight: normal; font-size: 0.9em; vertical-align: middle; color: #ecf0f1;}
        input[type="color"] { height: 36px; padding: 3px; }
        .value-display { font-size: 0.85em; color: #3498db; margin-left: 8px; font-weight: bold; }
        #instructions {
            margin-top: 15px; padding: 15px; background-color: rgba(44, 62, 80, 0.8);
            border: 1px solid #3498db; border-radius: 4px; font-size: 0.9em; color: #ecf0f1;
        }
        #instructions p { margin: 5px 0; }
        #instructions strong { color: #3498db; }

        @media (max-width: 950px) {
            body { flex-direction: column; align-items: center; }
            #controls { width: 90%; max-width: 480px; margin-top: 20px; }
        }
    </style>
</head>
<body>
    <div id="canvasContainer">
        <canvas id="helicopterCanvas" width="800" height="700"></canvas>
    </div>

    <div id="controls">
        <h2>Hyper Helicopter Sim</h2>
        <!-- General Properties -->
        <div class="control-group">
            <label for="bodyColor">Body Color:</label> <input type="color" id="bodyColor">
            <label for="cockpitColor">Cockpit Color:</label> <input type="color" id="cockpitColor">
            <label for="rotorColor">Rotor Hardware Color:</label> <input type="color" id="rotorColor">
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
            <label for="tailBoomWidth">Tail Boom Base Width: <span id="tailBoomWidthVal" class="value-display"></span></label>
            <input type="range" id="tailBoomWidth" min="8" max="40">
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
            <input type="range" id="rotorSpeed" min="0.01" max="0.8" step="0.01">
            <label for="rotorBlurAmount">Rotor Blur Amount: <span id="rotorBlurAmountVal" class="value-display"></span></label>
            <input type="range" id="rotorBlurAmount" min="0" max="1" step="0.05">
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
                 <label for="rocketAcceleration">Rocket Accel: <span id="rocketAccelerationVal" class="value-display"></span></label>
                 <input type="range" id="rocketAcceleration" min="0.01" max="0.5" step="0.01">
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
            let particles = [];

            let helicopter = {
                x: canvas.width / 2,
                y: canvas.height / 2,
                angle: -Math.PI / 2,
                bodyWidth: 50, bodyHeight: 100, bodyColor: '#4A5A52',
                cockpitColorHex: '#A0D0F0', cockpitAlpha: 0.6,
                rotorColor: '#282828', // Color of rotor hub/hardware
                tailBoomLength: 80, tailBoomWidth: 15, tailRotorRadius: 22,
                mainRotorRadius: 90, mainRotorBlades: 4,
                rotorAngle: 0, rotorSpeed: 0.25, rotorBlurAmount: 0.5,
                tailRotorAngle: 0, tailRotorSpeed: 0.55,
                hasRocketPods: true,
                rocketPodColor: '#3D4530', rocketPodWidth: 10, rocketPodHeight: 40,
                rocketPodOffsetX: 35, rocketPodOffsetY: -10,
                pylonWidth: 6,
                positionEasing: 0.05,
                rocketBaseSpeed: 1, rocketAcceleration: 0.1, rocketMaxSpeed: 15,
                rocketSize: 4, rocketColor: '#FF8C00',
                particleLife: 30, // frames
            };

            function hexToRgba(hex, alpha) {
                const r = parseInt(hex.slice(1, 3), 16);
                const g = parseInt(hex.slice(3, 5), 16);
                const b = parseInt(hex.slice(5, 7), 16);
                return `rgba(${r}, ${g}, ${b}, ${alpha})`;
            }

            function fireRocketFromPod(podX, podY, podAngle) {
                const frontOfPodOffset = -helicopter.rocketPodHeight / 2 - 5; // 5px in front of pod

                // Calculate world position of pod's front
                const rocketStartX = helicopter.x + Math.cos(helicopter.angle) * podX - Math.sin(helicopter.angle) * podY + Math.sin(helicopter.angle + podAngle) * frontOfPodOffset;
                const rocketStartY = helicopter.y + Math.sin(helicopter.angle) * podX + Math.cos(helicopter.angle) * podY - Math.cos(helicopter.angle + podAngle) * frontOfPodOffset;
                
                rockets.push({
                    x: rocketStartX,
                    y: rocketStartY,
                    angle: helicopter.angle + podAngle, // Rocket angle matches pod's effective angle
                    vx: Math.sin(helicopter.angle + podAngle) * helicopter.rocketBaseSpeed,
                    vy: -Math.cos(helicopter.angle + podAngle) * helicopter.rocketBaseSpeed,
                    currentSpeed: helicopter.rocketBaseSpeed,
                    launchedFromPod: true // Flag for different flame later if needed
                });
            }
            
            canvas.addEventListener('mousedown', (e) => {
                if (e.button === 0 && helicopter.hasRocketPods) {
                    // Pod positions relative to helicopter center, BEFORE helicopter rotation
                    const leftPodLocalX = -helicopter.rocketPodOffsetX;
                    const rightPodLocalX = helicopter.rocketPodOffsetX;
                    const podLocalY = helicopter.rocketPodOffsetY;
                    // Assuming pods fire straight ahead relative to helicopter's main axis
                    fireRocketFromPod(leftPodLocalX, podLocalY, 0); // 0 angle for pod means aligned with heli
                    fireRocketFromPod(rightPodLocalX, podLocalY, 0);
                }
            });

            function createRocketParticles(rocket) {
                const numParticles = 3 + Math.random() * 3; // 3-5 particles
                for (let i = 0; i < numParticles; i++) {
                    const angleOffset = (Math.random() - 0.5) * Math.PI / 3; // Spread particles
                    const speed = 1 + Math.random() * 2;
                    particles.push({
                        x: rocket.x - Math.sin(rocket.angle) * (helicopter.rocketSize + 2), // Start behind rocket
                        y: rocket.y + Math.cos(rocket.angle) * (helicopter.rocketSize + 2),
                        vx: -Math.sin(rocket.angle + angleOffset) * speed + (Math.random()-0.5)*0.5, // Opposite direction + some spread
                        vy: Math.cos(rocket.angle + angleOffset) * speed + (Math.random()-0.5)*0.5,
                        life: helicopter.particleLife,
                        maxLife: helicopter.particleLife,
                        size: Math.random() * 2 + 1,
                        color: Math.random() < 0.6 ? '#FFA500' : (Math.random() < 0.7 ? '#FF4500' : '#FFD700') // Orange, Org-Red, Gold
                    });
                }
            }

            function drawMainRotorBlur(ctx, radius, blades, angle, color, blurAmount) {
                if (blurAmount <= 0) return; // No blur to draw
                
                const numBlurSteps = 5 + Math.floor(blurAmount * 15); // More steps for more blur
                const angleStep = (helicopter.rotorSpeed * 2.5) / numBlurSteps; // How much rotation between blur steps
                                                                             // Factor in rotorSpeed for more blur if spinning faster

                for (let step = 0; step < numBlurSteps; step++) {
                    const stepAngleOffset = (step - numBlurSteps / 2) * angleStep;
                    const stepAlpha = (1 - (Math.abs(step - numBlurSteps / 2) / (numBlurSteps / 2))) * 0.1 * blurAmount; // Fade out outer steps

                    for (let i = 0; i < blades; i++) {
                        const bladeBaseAngle = angle + (i * (Math.PI * 2 / blades)) + stepAngleOffset;
                        
                        ctx.save();
                        ctx.rotate(bladeBaseAngle);
                        ctx.beginPath();
                        
                        // Blade "streak" - wider at tip for more blur effect there
                        const innerRadius = radius * 0.1;
                        const tipWidthFactor = 0.02 + blurAmount * 0.08; // Blade gets "thicker" with blur
                        
                        ctx.moveTo(0, -innerRadius); // Base of blade
                        ctx.quadraticCurveTo(radius * tipWidthFactor, -radius * 0.5, 0, -radius); // Outer tip (center)
                        ctx.quadraticCurveTo(-radius * tipWidthFactor, -radius * 0.5, 0, -innerRadius); // Back to base
                        
                        ctx.fillStyle = hexToRgba(color, stepAlpha * (0.3 + 0.7 * (1-blurAmount)) ); // More solid if less blur
                        ctx.fill();
                        ctx.restore();
                    }
                }
            }

            function drawHelicopter() {
                ctx.save();
                ctx.translate(helicopter.x, helicopter.y);
                ctx.rotate(helicopter.angle);
                
                // --- Draw Main Rotor Blur (Advanced) ---
                drawMainRotorBlur(ctx, helicopter.mainRotorRadius, helicopter.mainRotorBlades, helicopter.rotorAngle, helicopter.rotorColor, helicopter.rotorBlurAmount);

                // --- Draw actual Main Rotor Blades (less visible with high blur) ---
                const bladeAlpha = Math.max(0.05, 1 - helicopter.rotorBlurAmount * 0.95);
                if (bladeAlpha > 0.05) {
                    ctx.strokeStyle = hexToRgba(helicopter.rotorColor, bladeAlpha);
                    ctx.lineWidth = Math.max(1, 6 - helicopter.rotorBlurAmount * 5); // Thinner if blurred
                    for (let i = 0; i < helicopter.mainRotorBlades; i++) {
                        ctx.save();
                        ctx.rotate(helicopter.rotorAngle + (i * (Math.PI * 2 / helicopter.mainRotorBlades)));
                        ctx.beginPath();
                        ctx.moveTo(0, -helicopter.mainRotorRadius * 0.1);
                        ctx.lineTo(0, -helicopter.mainRotorRadius);
                        ctx.stroke();
                        ctx.restore();
                    }
                }
                // Main Rotor Hub (always visible)
                ctx.fillStyle = helicopter.rotorColor; // Hub matches hardware color
                ctx.beginPath();
                ctx.arc(0, 0, 12, 0, Math.PI * 2);
                ctx.fill();
                
                // --- Fuselage, Cockpit, Tail Boom (drawn after main rotor hardware, before pods) ---
                const bodyTopY = -helicopter.bodyHeight / 2;
                const bodyBottomY = helicopter.bodyHeight / 2;
                const bodyHalfWidth = helicopter.bodyWidth / 2;

                ctx.fillStyle = helicopter.bodyColor; // Fuselage
                ctx.beginPath();
                ctx.moveTo(-bodyHalfWidth, bodyTopY + 15); 
                ctx.quadraticCurveTo(0, bodyTopY - 10, bodyHalfWidth, bodyTopY + 15);
                ctx.lineTo(bodyHalfWidth, bodyBottomY - 20); 
                ctx.quadraticCurveTo(bodyHalfWidth * 0.8, bodyBottomY, 0, bodyBottomY); 
                ctx.quadraticCurveTo(-bodyHalfWidth * 0.8, bodyBottomY, -bodyHalfWidth, bodyBottomY - 20);
                ctx.lineTo(-bodyHalfWidth, bodyTopY + 15);
                ctx.closePath();
                ctx.fill();
                
                ctx.fillStyle = hexToRgba(helicopter.cockpitColorHex, helicopter.cockpitAlpha); // Cockpit
                ctx.beginPath();
                const cockpitFrontOffset = 5;
                const cockpitHeight = helicopter.bodyHeight * 0.35;
                const cockpitWidth = helicopter.bodyWidth * 0.9;
                ctx.ellipse(0, bodyTopY + cockpitHeight * 0.4 - cockpitFrontOffset, cockpitWidth / 2, cockpitHeight / 2, 0, Math.PI * 0.9, Math.PI * 0.1, true);
                ctx.closePath();
                ctx.fill();

                const tailBoomAttachY = bodyBottomY * 0.6; // Tail Boom
                const tailBoomTipWidth = helicopter.tailBoomWidth / 2.5; // Taper to this width
                ctx.fillStyle = helicopter.bodyColor;
                ctx.beginPath();
                ctx.moveTo(-helicopter.tailBoomWidth / 2, tailBoomAttachY);
                ctx.lineTo(helicopter.tailBoomWidth / 2, tailBoomAttachY);
                ctx.lineTo(tailBoomTipWidth / 2, tailBoomAttachY + helicopter.tailBoomLength);
                ctx.lineTo(-tailBoomTipWidth / 2, tailBoomAttachY + helicopter.tailBoomLength);
                ctx.closePath();
                ctx.fill();

                // --- Draw Tail Rotor ---
                // Axis of rotation is along the helicopter's length (local Y), mounted on the side of the tail boom.
                // Disc of rotation is in the helicopter's local XZ plane.
                // From top-down view, blades sweep left-right from the hub.
                const tailRotorHubX = tailBoomTipWidth / 2 + 5; // Mounted on the helicopter's right side of the tail boom tip
                const tailRotorHubY = tailBoomAttachY + helicopter.tailBoomLength * 0.9; // Near end of boom
                
                ctx.save();
                ctx.translate(tailRotorHubX, tailRotorHubY);
                
                // Hub
                ctx.fillStyle = helicopter.rotorColor;
                ctx.beginPath();
                ctx.arc(0, 0, 4, 0, Math.PI * 2);
                ctx.fill();
                
                // Blurred "line" or thin ellipse effect for tail rotor
                const tailBlurAngleSpread = Math.PI * 0.2 + helicopter.rotorSpeed * 0.5; // How much angular spread for blur
                const tailBladeEffectiveLength = helicopter.tailRotorRadius * (0.5 + helicopter.rotorSpeed * 0.5); // Shorter if slower

                ctx.beginPath();
                // Draw a thin, blurred ellipse representing the spinning blades' path
                ctx.ellipse(
                    0, 0, // centerX, centerY (local to hub)
                    helicopter.tailRotorRadius * 0.15, // radiusX (thin ellipse for side view of disc)
                    helicopter.tailRotorRadius,        // radiusY (full radius for blade length)
                    helicopter.tailRotorAngle,         // rotation of the entire ellipse (simulates blade pitch / spin)
                    0, Math.PI * 2
                );
                ctx.fillStyle = hexToRgba(helicopter.rotorColor, 0.2 + helicopter.rotorBlurAmount * 0.3); // Semi-transparent
                ctx.fill();

                // Optionally, draw a couple of very faint "streaks" for individual blades if not too blurred
                 if (helicopter.rotorBlurAmount < 0.8) {
                    ctx.lineWidth = 1.5;
                    ctx.strokeStyle = hexToRgba(helicopter.rotorColor, 0.3 * (1 - helicopter.rotorBlurAmount));
                    for(let i=0; i < 2; i++) { // Two main blade streaks
                        ctx.save();
                        ctx.rotate(helicopter.tailRotorAngle + i * Math.PI);
                        ctx.beginPath();
                        ctx.moveTo(0, -helicopter.tailRotorRadius);
                        ctx.lineTo(0, helicopter.tailRotorRadius);
                        ctx.stroke();
                        ctx.restore();
                    }
                }
                ctx.restore(); // End tail rotor transform

                // --- Draw Rocket Pods & Pylons (if enabled) ---
                if (helicopter.hasRocketPods) {
                    // ... (Pylon and Pod drawing - same as before, ensure it's drawn on top of fuselage) ...
                    const podY = helicopter.rocketPodOffsetY;
                    const pylonAttachPointY = helicopter.bodyHeight * 0.1; 
                    const pylonOuterPointY = podY - helicopter.rocketPodHeight * 0.3; 
                    ctx.fillStyle = helicopter.bodyColor; 
                    ctx.beginPath(); // Left Pylon
                    ctx.moveTo(-helicopter.bodyWidth/2 +5, pylonAttachPointY); 
                    ctx.lineTo(-helicopter.rocketPodOffsetX + helicopter.rocketPodWidth/2, pylonOuterPointY); 
                    ctx.lineTo(-helicopter.rocketPodOffsetX - helicopter.rocketPodWidth/2, pylonOuterPointY); 
                    ctx.lineTo(-helicopter.bodyWidth/2 +5, pylonAttachPointY + helicopter.pylonWidth); 
                    ctx.closePath(); ctx.fill();
                    ctx.beginPath(); // Right Pylon
                    ctx.moveTo(helicopter.bodyWidth/2 -5, pylonAttachPointY);
                    ctx.lineTo(helicopter.rocketPodOffsetX - helicopter.rocketPodWidth/2, pylonOuterPointY);
                    ctx.lineTo(helicopter.rocketPodOffsetX + helicopter.rocketPodWidth/2, pylonOuterPointY);
                    ctx.lineTo(helicopter.bodyWidth/2 -5, pylonAttachPointY + helicopter.pylonWidth);
                    ctx.closePath(); ctx.fill();
                    ctx.fillStyle = helicopter.rocketPodColor; // Pods
                    ctx.fillRect(-helicopter.rocketPodOffsetX - helicopter.rocketPodWidth / 2, podY - helicopter.rocketPodHeight / 2, helicopter.rocketPodWidth, helicopter.rocketPodHeight);
                    ctx.fillRect(helicopter.rocketPodOffsetX - helicopter.rocketPodWidth / 2, podY - helicopter.rocketPodHeight / 2, helicopter.rocketPodWidth, helicopter.rocketPodHeight);
                }
                ctx.restore(); // Restore main helicopter transform
            }
            
            function drawRocketsAndParticles() {
                // Update and draw rockets
                for (let i = rockets.length - 1; i >= 0; i--) {
                    const r = rockets[i];
                    // Acceleration
                    if (r.currentSpeed < helicopter.rocketMaxSpeed) {
                        r.currentSpeed += helicopter.rocketAcceleration;
                        r.currentSpeed = Math.min(r.currentSpeed, helicopter.rocketMaxSpeed);
                    }
                    r.vx = Math.sin(r.angle) * r.currentSpeed;
                    r.vy = -Math.cos(r.angle) * r.currentSpeed;
                    r.x += r.vx;
                    r.y += r.vy;

                    createRocketParticles(r); // Create particles each frame

                    // Draw rocket body
                    ctx.fillStyle = helicopter.rocketColor;
                    ctx.beginPath();
                    ctx.arc(r.x, r.y, helicopter.rocketSize, 0, Math.PI * 2);
                    ctx.fill();

                    // Draw flame
                    const flameLength = helicopter.rocketSize * (1.5 + Math.random() * 0.8 + r.currentSpeed * 0.1);
                    const flameWidth = helicopter.rocketSize * (0.8 + Math.random() * 0.3);
                    ctx.save();
                    ctx.translate(r.x, r.y);
                    ctx.rotate(r.angle + Math.PI); // Flame points backwards
                    ctx.beginPath();
                    ctx.moveTo(0, 0); // Tip of flame at rocket base
                    ctx.lineTo(-flameWidth / 2, flameLength);
                    ctx.lineTo(flameWidth / 2, flameLength);
                    ctx.closePath();
                    const flameColors = ['#FF4500', '#FFA500', '#FFD700']; // OrangeRed, Orange, Gold
                    ctx.fillStyle = hexToRgba(flameColors[Math.floor(Math.random() * flameColors.length)], 0.7 + Math.random() * 0.3);
                    ctx.fill();
                    ctx.restore();

                    if (r.x < -50 || r.x > canvas.width + 50 || r.y < -50 || r.y > canvas.height + 50) {
                        rockets.splice(i, 1);
                    }
                }

                // Update and draw particles
                for (let i = particles.length - 1; i >= 0; i--) {
                    const p = particles[i];
                    p.x += p.vx;
                    p.y += p.vy;
                    p.life--;
                    const alpha = (p.life / p.maxLife) * 0.8; // Fade out
                    ctx.fillStyle = hexToRgba(p.color, alpha);
                    ctx.beginPath();
                    ctx.arc(p.x, p.y, p.size * (p.life / p.maxLife), 0, Math.PI * 2); // Shrink
                    ctx.fill();
                    if (p.life <= 0) {
                        particles.splice(i, 1);
                    }
                }
            }

            const controlsMap = { // Ensure all controls are mapped
                bodyColor: { el: document.getElementById('bodyColor'), type: 'color' },
                cockpitColorHex: { el: document.getElementById('cockpitColor'), type: 'color', target: 'cockpitColorHex' },
                rotorColor: { el: document.getElementById('rotorColor'), type: 'color' },
                bodyWidth: { el: document.getElementById('bodyWidth'), valEl: document.getElementById('bodyWidthVal'), type: 'rangeInt' },
                bodyHeight: { el: document.getElementById('bodyHeight'), valEl: document.getElementById('bodyHeightVal'), type: 'rangeInt' },
                tailBoomLength: { el: document.getElementById('tailBoomLength'), valEl: document.getElementById('tailBoomLengthVal'), type: 'rangeInt' },
                tailBoomWidth: { el: document.getElementById('tailBoomWidth'), valEl: document.getElementById('tailBoomWidthVal'), type: 'rangeInt', target: 'tailBoomWidth' },
                tailRotorRadius: { el: document.getElementById('tailRotorRadius'), valEl: document.getElementById('tailRotorRadiusVal'), type: 'rangeInt' },
                mainRotorRadius: { el: document.getElementById('mainRotorRadius'), valEl: document.getElementById('mainRotorRadiusVal'), type: 'rangeInt' },
                mainRotorBlades: { el: document.getElementById('mainRotorBlades'), valEl: document.getElementById('mainRotorBladesVal'), type: 'numberInt' },
                rotorSpeed: { el: document.getElementById('rotorSpeed'), valEl: document.getElementById('rotorSpeedVal'), type: 'rangeFloat', decimals: 2 },
                rotorBlurAmount: { el: document.getElementById('rotorBlurAmount'), valEl: document.getElementById('rotorBlurAmountVal'), type: 'rangeFloat', decimals: 2 },
                hasRocketPods: { el: document.getElementById('hasRocketPods'), type: 'checkbox' },
                rocketPodColor: { el: document.getElementById('rocketPodColor'), type: 'color' },
                rocketPodWidth: { el: document.getElementById('rocketPodWidth'), valEl: document.getElementById('rocketPodWidthVal'), type: 'rangeInt' },
                rocketPodHeight: { el: document.getElementById('rocketPodHeight'), valEl: document.getElementById('rocketPodHeightVal'), type: 'rangeInt' },
                rocketAcceleration: { el: document.getElementById('rocketAcceleration'), valEl: document.getElementById('rocketAccelerationVal'), type: 'rangeFloat', decimals: 2},
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
                                if (key === 'hasRocketPods') { rocketPodControlsDiv.style.display = helicopter.hasRocketPods ? 'block' : 'none'; }
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

            function updateHelicopterMovementAndOrientation() { /* ... (same as before) ... */
                const dx = mouse.x - helicopter.x; const dy = mouse.y - helicopter.y;
                helicopter.x += dx * helicopter.positionEasing; helicopter.y += dy * helicopter.positionEasing;
                let targetAngle = Math.atan2(dy, dx) + Math.PI / 2; helicopter.angle = targetAngle;
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
                
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                drawHelicopter();
                drawRocketsAndParticles();
                requestAnimationFrame(gameLoop);
            }

            updateControlsDisplay();
            gameLoop();
        });
    </script>
</body>
</html>