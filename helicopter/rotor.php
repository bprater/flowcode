<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Desert Strike Helicopter Sim</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            align-items: flex-start;
            gap: 30px;
            margin: 20px;
            background-color: #1a1a1a; /* Very dark background */
            color: #ecf0f1;
            overflow: hidden; /* Prevent body scrollbars */
        }
        #canvasContainer {
            border: 3px solid #e67e22; /* Desert orange border */
            box-shadow: 0 0 25px rgba(230, 126, 34, 0.6);
            background-color: #000000; /* Fallback canvas area color */
            cursor: crosshair;
        }
        #controls {
            display: flex;
            flex-direction: column;
            gap: 12px;
            padding: 20px;
            border: 1px solid #333;
            background-color: #2c2c2c; /* Dark controls panel */
            color: #ecf0f1;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.4);
            width: 360px;
            max-height: calc(100vh - 40px);
            overflow-y: auto;
        }
        #controls h2 {
            margin-top: 0;
            color: #e67e22; /* Desert orange title */
            border-bottom: 1px solid #444;
            padding-bottom: 10px;
            font-size: 1.5em;
        }
        .control-group {
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px dashed #444;
        }
        .control-group:last-child { border-bottom: none; }
        label {
            display: block;
            margin-bottom: 6px;
            font-weight: bold;
            font-size: 0.9em;
            color: #bdc3c7;
        }
        input[type="color"], input[type="range"], input[type="number"], input[type="checkbox"] {
            width: calc(100% - 12px);
            padding: 8px;
            border: 1px solid #444;
            background-color: #3a3a3a;
            color: #ecf0f1;
            border-radius: 4px;
            box-sizing: border-box;
        }
        input[type="range"] { cursor: pointer; }
        input[type="checkbox"] { width: auto; margin-right: 5px; vertical-align: middle; }
        .checkbox-label { font-weight: normal; font-size: 0.9em; vertical-align: middle; color: #ecf0f1;}
        input[type="color"] { height: 38px; padding: 3px; }
        .value-display { font-size: 0.85em; color: #e67e22; margin-left: 8px; font-weight: bold; }
        #instructions {
            margin-top: 15px; padding: 15px; background-color: rgba(44, 44, 44, 0.8);
            border: 1px solid #e67e22; border-radius: 4px; font-size: 0.9em; color: #ecf0f1;
        }
        #instructions p { margin: 5px 0; }
        #instructions strong { color: #e67e22; }

        @media (max-width: 1000px) { /* Adjusted breakpoint */
            body { flex-direction: column; align-items: center; }
            #controls { width: 90%; max-width: 500px; margin-top: 20px; }
        }
    </style>
</head>
<body>
    <div id="canvasContainer">
        <canvas id="helicopterCanvas" width="800" height="600"></canvas>
    </div>

    <div id="controls">
        <h2>Desert Strike Sim</h2>
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
            <p><strong>Objective:</strong> Destroy the targets!</p>
            <p>Move mouse to guide helicopter.</p>
            <p>Click to fire rockets!</p>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const canvas = document.getElementById('helicopterCanvas');
            const ctx = canvas.getContext('2d');

            const worldWidth = canvas.width * 2;
            const worldHeight = canvas.height * 2;

            let camera = { x: worldWidth/2 - canvas.width/2, y: worldHeight/2 - canvas.height/2 };
            let mouseWorld = { x: worldWidth / 2, y: worldHeight / 2 }; // Mouse position in world coordinates
            
            let rockets = [];
            let particles = [];
            let explosions = [];
            let targets = [];
            let groundFeatures = []; // For rocks, etc.

            let helicopter = {
                worldX: worldWidth / 2, // Position in world coordinates
                worldY: worldHeight / 2,
                angle: -Math.PI / 2,
                bodyWidth: 50, bodyHeight: 100, bodyColor: '#8B4513', // Brownish
                cockpitColorHex: '#ADD8E6', cockpitAlpha: 0.5, // LightBlue
                rotorColor: '#4A4A4A',
                mainRotorRadius: 90, mainRotorBlades: 4,
                rotorAngle: 0, rotorSpeed: 0.25, rotorBlurAmount: 0.5,
                hasRocketPods: true,
                rocketPodColor: '#696969', rocketPodWidth: 10, rocketPodHeight: 40, // DarkGray
                rocketPodOffsetX: 35, rocketPodOffsetY: -10,
                pylonWidth: 6,
                positionEasing: 0.05,
                rocketBaseSpeed: 1, rocketAcceleration: 0.1, rocketMaxSpeed: 15,
                rocketSize: 4, rocketColor: '#FF4500', // OrangeRed
                particleLife: 40,
            };

            function hexToRgba(hex, alpha) {
                const r = parseInt(hex.slice(1, 3), 16);
                const g = parseInt(hex.slice(3, 5), 16);
                const b = parseInt(hex.slice(5, 7), 16);
                return `rgba(${r}, ${g}, ${b}, ${alpha})`;
            }
            
            // --- INITIALIZATION ---
            function initGroundFeatures() {
                groundFeatures = [];
                const numRocks = 150;
                const rockColors = ['#A0A0A0', '#888888', '#B0B0B0', '#989898'];
                for (let i = 0; i < numRocks; i++) {
                    const size = 10 + Math.random() * 30;
                    groundFeatures.push({
                        type: 'rock',
                        x: Math.random() * worldWidth,
                        y: Math.random() * worldHeight,
                        radius: size / 2,
                        color: rockColors[Math.floor(Math.random() * rockColors.length)],
                        points: Array.from({length: 5 + Math.floor(Math.random()*3)}, () => ({
                            angle: Math.random() * Math.PI * 2,
                            dist: (0.7 + Math.random() * 0.3) * (size/2)
                        })).sort((a,b) => a.angle - b.angle)
                    });
                }
            }

            function initTargets() {
                targets = [];
                const numTargets = 5;
                const targetColors = ['#556B2F', '#8FBC8F', '#2E8B57']; // Olive, DarkSeaGreen, SeaGreen
                for (let i = 0; i < numTargets; i++) {
                    targets.push({
                        worldX: Math.random() * (worldWidth - 200) + 100, // Avoid edges
                        worldY: Math.random() * (worldHeight - 200) + 100,
                        width: 30 + Math.random() * 20,
                        height: 30 + Math.random() * 20,
                        color: targetColors[Math.floor(Math.random() * targetColors.length)],
                        health: 100, // Example health
                        isDestroyed: false
                    });
                }
            }
            
            // --- ROCKETS & PARTICLES ---
            function fireRocketFromPod(podLocalX, podLocalY) {
                const frontOfPodOffset = -helicopter.rocketPodHeight / 2 - 5;
                const rocketStartX = helicopter.worldX + Math.cos(helicopter.angle) * podLocalX - Math.sin(helicopter.angle) * podLocalY + Math.sin(helicopter.angle) * frontOfPodOffset;
                const rocketStartY = helicopter.worldY + Math.sin(helicopter.angle) * podLocalX + Math.cos(helicopter.angle) * podLocalY - Math.cos(helicopter.angle) * frontOfPodOffset;
                
                rockets.push({
                    worldX: rocketStartX, worldY: rocketStartY,
                    angle: helicopter.angle,
                    vx: Math.sin(helicopter.angle) * helicopter.rocketBaseSpeed,
                    vy: -Math.cos(helicopter.angle) * helicopter.rocketBaseSpeed,
                    currentSpeed: helicopter.rocketBaseSpeed,
                });
            }
            
            canvas.addEventListener('mousedown', (e) => {
                if (e.button === 0 && helicopter.hasRocketPods) {
                    fireRocketFromPod(-helicopter.rocketPodOffsetX, helicopter.rocketPodOffsetY);
                    fireRocketFromPod(helicopter.rocketPodOffsetX, helicopter.rocketPodOffsetY);
                }
            });

            function createParticles(x, y, count, baseColor, type = 'explosion') {
                for (let i = 0; i < count; i++) {
                    let angle, speed, life, size, color;
                    if (type === 'explosion') {
                        angle = Math.random() * Math.PI * 2;
                        speed = 1 + Math.random() * 5;
                        life = 30 + Math.random() * 30;
                        size = 2 + Math.random() * 4;
                        color = Math.random() < 0.7 ? baseColor : (Math.random() < 0.5 ? '#FFA500' : '#808080'); // Base, Orange, Gray
                    } else { // rocket trail
                        angle = Math.PI + (Math.random() - 0.5) * Math.PI / 2; // Behind rocket
                        speed = 0.5 + Math.random() * 1.5;
                        life = 15 + Math.random() * 15;
                        size = 1 + Math.random() * 2;
                        color = Math.random() < 0.6 ? '#FFA500' : '#FFD700';
                    }
                    particles.push({
                        worldX: x, worldY: y,
                        vx: Math.cos(angle) * speed, vy: Math.sin(angle) * speed,
                        life: life, maxLife: life, size: size, color: color, type: type
                    });
                }
            }

            function createExplosion(x, y) {
                explosions.push({
                    worldX: x, worldY: y,
                    radius: 0, maxRadius: 50 + Math.random() * 30,
                    life: 0, maxLife: 30, // Frames for fireball
                    color: '#FF8C00'
                });
                createParticles(x, y, 50 + Math.random() * 50, '#FFA500', 'explosion'); // Fire/debris
                createParticles(x, y, 30 + Math.random() * 30, '#808080', 'explosion'); // Smoke
            }

            // --- DRAWING FUNCTIONS (World Coordinates) ---
            function drawGround(ctx) {
                // Base desert color
                ctx.fillStyle = '#D2B48C'; // Tan
                ctx.fillRect(0, 0, worldWidth, worldHeight);

                // Slightly darker patches for variation
                ctx.fillStyle = '#C19A6B'; // Slightly darker tan (Khaki)
                for(let i = 0; i < 50; i++) { // Draw a few large patches
                    const patchX = (i * 37) % worldWidth; // Simple seeded variation
                    const patchY = (i * 53) % worldHeight;
                    const patchWidth = 200 + Math.sin(i) * 100 + 200;
                    const patchHeight = 200 + Math.cos(i*1.5) * 100 + 200;
                    if (patchX + patchWidth > camera.x && patchX < camera.x + canvas.width &&
                        patchY + patchHeight > camera.y && patchY < camera.y + canvas.height) {
                         ctx.beginPath();
                         ctx.ellipse(patchX + patchWidth/2, patchY + patchHeight/2, patchWidth/2, patchHeight/2, Math.sin(i*0.3)*Math.PI/4, 0, Math.PI*2);
                         ctx.fill();
                    }
                }

                // Draw ground features (rocks)
                groundFeatures.forEach(feature => {
                    if (feature.type === 'rock') {
                        // Basic culling
                        if (feature.x + feature.radius > camera.x && feature.x - feature.radius < camera.x + canvas.width &&
                            feature.y + feature.radius > camera.y && feature.y - feature.radius < camera.y + canvas.height) {
                            ctx.fillStyle = feature.color;
                            ctx.beginPath();
                            feature.points.forEach((p, index) => {
                                const px = feature.x + Math.cos(p.angle) * p.dist;
                                const py = feature.y + Math.sin(p.angle) * p.dist;
                                if (index === 0) ctx.moveTo(px, py);
                                else ctx.lineTo(px, py);
                            });
                            ctx.closePath();
                            ctx.fill();
                        }
                    }
                });
            }
            
            function drawMainRotorBlur(ctx, radius, blades, angle, color, blurAmount) {
                if (blurAmount <= 0) return;
                const numBlurSteps = 5 + Math.floor(blurAmount * 15);
                const angleStep = (helicopter.rotorSpeed * 2.5) / numBlurSteps;
                for (let step = 0; step < numBlurSteps; step++) {
                    const stepAngleOffset = (step - numBlurSteps / 2) * angleStep;
                    const stepAlpha = (1 - (Math.abs(step - numBlurSteps / 2) / (numBlurSteps / 2))) * 0.1 * blurAmount;
                    for (let i = 0; i < blades; i++) {
                        const bladeBaseAngle = angle + (i * (Math.PI * 2 / blades)) + stepAngleOffset;
                        ctx.save(); ctx.rotate(bladeBaseAngle); ctx.beginPath();
                        const innerRadius = radius * 0.1; const tipWidthFactor = 0.02 + blurAmount * 0.08;
                        ctx.moveTo(0, -innerRadius);
                        ctx.quadraticCurveTo(radius * tipWidthFactor, -radius * 0.5, 0, -radius);
                        ctx.quadraticCurveTo(-radius * tipWidthFactor, -radius * 0.5, 0, -innerRadius);
                        ctx.fillStyle = hexToRgba(color, stepAlpha * (0.3 + 0.7 * (1-blurAmount)) );
                        ctx.fill(); ctx.restore();
                    }
                }
            }
            
            function drawHelicopter(ctx) {
                ctx.save();
                ctx.translate(helicopter.worldX, helicopter.worldY); // Use world coordinates
                ctx.rotate(helicopter.angle);
                
                drawMainRotorBlur(ctx, helicopter.mainRotorRadius, helicopter.mainRotorBlades, helicopter.rotorAngle, helicopter.rotorColor, helicopter.rotorBlurAmount);
                const bladeAlpha = Math.max(0.05, 1 - helicopter.rotorBlurAmount * 0.95);
                if (bladeAlpha > 0.05) {
                    ctx.strokeStyle = hexToRgba(helicopter.rotorColor, bladeAlpha);
                    ctx.lineWidth = Math.max(1, 6 - helicopter.rotorBlurAmount * 5);
                    for (let i = 0; i < helicopter.mainRotorBlades; i++) {
                        ctx.save(); ctx.rotate(helicopter.rotorAngle + (i * (Math.PI * 2 / helicopter.mainRotorBlades)));
                        ctx.beginPath(); ctx.moveTo(0, -helicopter.mainRotorRadius * 0.1); ctx.lineTo(0, -helicopter.mainRotorRadius);
                        ctx.stroke(); ctx.restore();
                    }
                }
                ctx.fillStyle = helicopter.rotorColor; ctx.beginPath(); ctx.arc(0, 0, 12, 0, Math.PI * 2); ctx.fill();
                
                const bodyTopY = -helicopter.bodyHeight / 2; const bodyBottomY = helicopter.bodyHeight / 2; const bodyHalfWidth = helicopter.bodyWidth / 2;
                ctx.fillStyle = helicopter.bodyColor; ctx.beginPath();
                ctx.moveTo(-bodyHalfWidth, bodyTopY + 15); ctx.quadraticCurveTo(0, bodyTopY - 10, bodyHalfWidth, bodyTopY + 15);
                ctx.lineTo(bodyHalfWidth, bodyBottomY - 20); ctx.quadraticCurveTo(bodyHalfWidth * 0.8, bodyBottomY, 0, bodyBottomY);
                ctx.quadraticCurveTo(-bodyHalfWidth * 0.8, bodyBottomY, -bodyHalfWidth, bodyBottomY - 20); ctx.lineTo(-bodyHalfWidth, bodyTopY + 15);
                ctx.closePath(); ctx.fill();
                
                ctx.fillStyle = hexToRgba(helicopter.cockpitColorHex, helicopter.cockpitAlpha); ctx.beginPath();
                const cockpitFrontOffset = 5; const cockpitHeight = helicopter.bodyHeight * 0.35; const cockpitWidth = helicopter.bodyWidth * 0.9;
                ctx.ellipse(0, bodyTopY + cockpitHeight * 0.4 - cockpitFrontOffset, cockpitWidth / 2, cockpitHeight / 2, 0, Math.PI * 0.9, Math.PI * 0.1, true);
                ctx.closePath(); ctx.fill();
                
                // Tail Boom - No tail rotor
                const tailBoomAttachY = bodyBottomY * 0.6; const tailBoomTipWidth = helicopter.bodyWidth / 3; // Simple taper
                ctx.fillStyle = helicopter.bodyColor; ctx.beginPath();
                ctx.moveTo(-helicopter.bodyWidth / 2.5, tailBoomAttachY); // Wider base for boom
                ctx.lineTo(helicopter.bodyWidth / 2.5, tailBoomAttachY);
                ctx.lineTo(tailBoomTipWidth / 2, tailBoomAttachY + helicopter.rocketPodHeight*1.5); // Shorter boom if no tail rotor
                ctx.lineTo(-tailBoomTipWidth / 2, tailBoomAttachY + helicopter.rocketPodHeight*1.5);
                ctx.closePath(); ctx.fill();

                if (helicopter.hasRocketPods) {
                    const podY = helicopter.rocketPodOffsetY; const pylonAttachPointY = helicopter.bodyHeight * 0.1; 
                    const pylonOuterPointY = podY - helicopter.rocketPodHeight * 0.3; 
                    ctx.fillStyle = helicopter.bodyColor; 
                    ctx.beginPath(); ctx.moveTo(-helicopter.bodyWidth/2 +5, pylonAttachPointY); 
                    ctx.lineTo(-helicopter.rocketPodOffsetX + helicopter.rocketPodWidth/2, pylonOuterPointY); 
                    ctx.lineTo(-helicopter.rocketPodOffsetX - helicopter.rocketPodWidth/2, pylonOuterPointY); 
                    ctx.lineTo(-helicopter.bodyWidth/2 +5, pylonAttachPointY + helicopter.pylonWidth); 
                    ctx.closePath(); ctx.fill();
                    ctx.beginPath(); ctx.moveTo(helicopter.bodyWidth/2 -5, pylonAttachPointY);
                    ctx.lineTo(helicopter.rocketPodOffsetX - helicopter.rocketPodWidth/2, pylonOuterPointY);
                    ctx.lineTo(helicopter.rocketPodOffsetX + helicopter.rocketPodWidth/2, pylonOuterPointY);
                    ctx.lineTo(helicopter.bodyWidth/2 -5, pylonAttachPointY + helicopter.pylonWidth);
                    ctx.closePath(); ctx.fill();
                    ctx.fillStyle = helicopter.rocketPodColor;
                    ctx.fillRect(-helicopter.rocketPodOffsetX - helicopter.rocketPodWidth / 2, podY - helicopter.rocketPodHeight / 2, helicopter.rocketPodWidth, helicopter.rocketPodHeight);
                    ctx.fillRect(helicopter.rocketPodOffsetX - helicopter.rocketPodWidth / 2, podY - helicopter.rocketPodHeight / 2, helicopter.rocketPodWidth, helicopter.rocketPodHeight);
                }
                ctx.restore();
            }

            function drawTargetsAndProjectiles(ctx) {
                // Draw Targets
                targets.forEach(t => {
                    if (!t.isDestroyed) {
                        ctx.fillStyle = t.color;
                        ctx.fillRect(t.worldX, t.worldY, t.width, t.height);
                        // Simple health bar
                        if (t.health < 100) {
                            ctx.fillStyle = 'red';
                            ctx.fillRect(t.worldX, t.worldY - 10, t.width, 5);
                            ctx.fillStyle = 'green';
                            ctx.fillRect(t.worldX, t.worldY - 10, t.width * (t.health / 100), 5);
                        }
                    }
                });

                // Update and Draw Rockets
                for (let i = rockets.length - 1; i >= 0; i--) {
                    const r = rockets[i];
                    if (r.currentSpeed < helicopter.rocketMaxSpeed) {
                        r.currentSpeed += helicopter.rocketAcceleration;
                        r.currentSpeed = Math.min(r.currentSpeed, helicopter.rocketMaxSpeed);
                    }
                    r.vx = Math.sin(r.angle) * r.currentSpeed; r.vy = -Math.cos(r.angle) * r.currentSpeed;
                    r.worldX += r.vx; r.worldY += r.vy;
                    
                    createParticles(r.worldX, r.worldY, 2, '', 'rocket_trail');

                    ctx.fillStyle = helicopter.rocketColor; ctx.beginPath(); ctx.arc(r.worldX, r.worldY, helicopter.rocketSize, 0, Math.PI * 2); ctx.fill();
                    const flameLength = helicopter.rocketSize * (1.5 + Math.random()*0.8 + r.currentSpeed*0.1); const flameWidth = helicopter.rocketSize * (0.8+Math.random()*0.3);
                    ctx.save(); ctx.translate(r.worldX, r.worldY); ctx.rotate(r.angle + Math.PI); ctx.beginPath();
                    ctx.moveTo(0,0); ctx.lineTo(-flameWidth/2, flameLength); ctx.lineTo(flameWidth/2, flameLength); ctx.closePath();
                    const flameColors = ['#FF4500', '#FFA500', '#FFD700'];
                    ctx.fillStyle = hexToRgba(flameColors[Math.floor(Math.random()*flameColors.length)], 0.7 + Math.random()*0.3); ctx.fill(); ctx.restore();

                    // Collision with targets
                    for (let j = targets.length - 1; j >=0; j--) {
                        const t = targets[j];
                        if (!t.isDestroyed && r.worldX > t.worldX && r.worldX < t.worldX + t.width && r.worldY > t.worldY && r.worldY < t.worldY + t.height) {
                            rockets.splice(i, 1);
                            t.health -= 25; // Damage
                            if (t.health <= 0) {
                                t.isDestroyed = true;
                                createExplosion(t.worldX + t.width/2, t.worldY + t.height/2);
                            } else { // Smaller hit explosion
                                createExplosion(r.worldX, r.worldY); // Use rocket pos for non-fatal hit
                            }
                            break; 
                        }
                    }
                    if (i < rockets.length && (r.worldX < 0 || r.worldX > worldWidth || r.worldY < 0 || r.worldY > worldHeight)) {
                        rockets.splice(i, 1);
                    }
                }

                // Update and Draw Particles
                for (let i = particles.length - 1; i >= 0; i--) {
                    const p = particles[i];
                    p.worldX += p.vx;
                    p.worldY += p.vy;
                    p.life--;

                    if (p.life <= 0) { // Check for removal first
                        particles.splice(i, 1);
                        continue; // Skip drawing if particle is dead
                    }

                    const currentRadius = p.size * (p.life / p.maxLife);
                    // Ensure radius is not negative and particle is still somewhat visible
                    if (currentRadius > 0.1) { // Only draw if radius is meaningfully positive
                        const alpha = (p.life / p.maxLife) * (p.type === 'explosion' ? 0.9 : 0.6);
                        ctx.fillStyle = hexToRgba(p.color, alpha);
                        ctx.beginPath();
                        ctx.arc(p.worldX, p.worldY, currentRadius, 0, Math.PI * 2);
                        ctx.fill();
                    }
                }


                // Update and Draw Explosions
                for (let i = explosions.length - 1; i >= 0; i--) {
                    const exp = explosions[i];
                    exp.life++;
                    exp.radius = (exp.life / exp.maxLife) * exp.maxRadius;
                    const fireballAlpha = 1 - (exp.life / exp.maxLife);

                    if (fireballAlpha > 0) {
                        const gradient = ctx.createRadialGradient(exp.worldX, exp.worldY, 0, exp.worldX, exp.worldY, exp.radius);
                        gradient.addColorStop(0, hexToRgba('#FFFFFF', fireballAlpha * 0.8)); // White hot center
                        gradient.addColorStop(0.3, hexToRgba(exp.color, fireballAlpha * 0.9));
                        gradient.addColorStop(0.7, hexToRgba(exp.color, fireballAlpha * 0.5));
                        gradient.addColorStop(1, hexToRgba(exp.color, 0));
                        ctx.fillStyle = gradient;
                        ctx.beginPath();
                        ctx.arc(exp.worldX, exp.worldY, exp.radius, 0, Math.PI * 2);
                        ctx.fill();
                    }
                    if (exp.life >= exp.maxLife) { explosions.splice(i, 1); }
                }
            }

            const controlsMap = {
                bodyColor: { el: document.getElementById('bodyColor'), type: 'color' },
                cockpitColorHex: { el: document.getElementById('cockpitColor'), type: 'color', target: 'cockpitColorHex' },
                rotorColor: { el: document.getElementById('rotorColor'), type: 'color' },
                bodyWidth: { el: document.getElementById('bodyWidth'), valEl: document.getElementById('bodyWidthVal'), type: 'rangeInt' },
                bodyHeight: { el: document.getElementById('bodyHeight'), valEl: document.getElementById('bodyHeightVal'), type: 'rangeInt' },
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
            function updateControlsDisplay() {
                 for (const key in controlsMap) {
                    if (Object.hasOwnProperty.call(controlsMap, key)) {
                        const control = controlsMap[key]; const targetKey = control.target || key;
                        if (control.el) {
                            if (control.type === 'checkbox') { control.el.checked = helicopter[targetKey]; if (key === 'hasRocketPods') { rocketPodControlsDiv.style.display = helicopter.hasRocketPods ? 'block' : 'none'; }}
                            else { control.el.value = helicopter[targetKey]; }
                            if (control.valEl) {
                                if (control.type === 'rangeFloat') { control.valEl.textContent = parseFloat(helicopter[targetKey]).toFixed(control.decimals || 2); }
                                else { control.valEl.textContent = helicopter[targetKey]; }
                            }
                        }
                    }
                }
            }
            function setupControlEvents() {
                for (const key in controlsMap) {
                    if (Object.hasOwnProperty.call(controlsMap, key)) {
                        const control = controlsMap[key]; if (control.el) {
                            control.el.addEventListener('input', (e) => {
                                let value; const targetKey = control.target || key;
                                if (control.type === 'checkbox') { value = e.target.checked; if (key === 'hasRocketPods') { rocketPodControlsDiv.style.display = value ? 'block' : 'none';}}
                                else if (control.type === 'rangeInt' || control.type === 'numberInt') { value = parseInt(e.target.value, 10); }
                                else if (control.type === 'rangeFloat') { value = parseFloat(e.target.value); }
                                else { value = e.target.value; }
                                helicopter[targetKey] = value;
                                if (control.valEl) {
                                    if (control.type === 'rangeFloat') { control.valEl.textContent = value.toFixed(control.decimals || 2); }
                                    else if (control.type !== 'checkbox') { control.valEl.textContent = value; }
                                }
                            });
                        }
                    }
                }
            }
            
            canvas.addEventListener('mousemove', (e) => {
                const rect = canvas.getBoundingClientRect();
                mouseWorld.x = (e.clientX - rect.left) + camera.x;
                mouseWorld.y = (e.clientY - rect.top) + camera.y;
            });

            function updateHelicopterAndCamera() {
                const dx = mouseWorld.x - helicopter.worldX;
                const dy = mouseWorld.y - helicopter.worldY;
                helicopter.worldX += dx * helicopter.positionEasing;
                helicopter.worldY += dy * helicopter.positionEasing;
                
                let targetAngle = Math.atan2(dy, dx) + Math.PI / 2;
                helicopter.angle = targetAngle;
                while (helicopter.angle >= Math.PI * 2) helicopter.angle -= Math.PI * 2;
                while (helicopter.angle < 0) helicopter.angle += Math.PI * 2;

                // Update camera to follow helicopter, keeping it roughly centered
                camera.x = helicopter.worldX - canvas.width / 2;
                camera.y = helicopter.worldY - canvas.height / 2;

                // Clamp camera to world boundaries
                camera.x = Math.max(0, Math.min(worldWidth - canvas.width, camera.x));
                camera.y = Math.max(0, Math.min(worldHeight - canvas.height, camera.y));
                
                // Clamp helicopter to world boundaries too (though camera clamping often handles visual)
                helicopter.worldX = Math.max(0, Math.min(worldWidth, helicopter.worldX));
                helicopter.worldY = Math.max(0, Math.min(worldHeight, helicopter.worldY));

            }

            function gameLoop() {
                helicopter.rotorAngle = (helicopter.rotorAngle + helicopter.rotorSpeed) % (Math.PI * 2);
                updateHelicopterAndCamera();
                
                ctx.clearRect(0, 0, canvas.width, canvas.height); // Clear viewport

                ctx.save();
                ctx.translate(-camera.x, -camera.y); // Apply camera transform

                // --- Draw world elements ---
                drawGround(ctx);
                drawTargetsAndProjectiles(ctx); // Draws targets, rockets, particles, explosions
                drawHelicopter(ctx);
                
                ctx.restore(); // Restore from camera transform

                // --- Draw UI elements fixed to screen (if any, e.g. score) ---
                const activeTargets = targets.filter(t => !t.isDestroyed).length;
                ctx.fillStyle = "#e67e22";
                ctx.font = "18px Segoe UI, sans-serif";
                ctx.textAlign = "right";
                ctx.fillText(`Targets Remaining: ${activeTargets}`, canvas.width - 20, 30);


                requestAnimationFrame(gameLoop);
            }

            initGroundFeatures();
            initTargets();
            updateControlsDisplay();
            setupControlEvents();
            gameLoop();
        });
    </script>
</body>
</html>