<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fireworks Simulator</title>
    <style>
        html, body {
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
            font-family: Arial, sans-serif;
            background-color: #111;
            color: #eee;
            overflow: hidden;
        }

        .container {
            display: flex;
            width: 100%;
            height: 100vh;
        }

        #skyCanvas {
            flex-grow: 1;
            min-width: 0;
            background-color: #000010;
            cursor: crosshair;
        }

        #controlsPanel {
            width: 300px;
            min-width: 280px;
            flex-shrink: 0;
            background-color: #222;
            padding: 20px;
            box-sizing: border-box;
            overflow-y: auto;
            border-left: 2px solid #444;
            height: 100vh;
        }

        #controlsPanel h2 {
            margin-top: 0;
            margin-bottom: 15px;
            color: #00aaff;
            border-bottom: 1px solid #444;
            padding-bottom: 5px;
        }

        .control-group {
            margin-bottom: 15px;
        }

        .control-group label {
            display: block;
            margin-bottom: 5px;
            font-size: 0.9em;
        }

        .control-group select,
        .control-group input[type="color"],
        .control-group input[type="checkbox"] {
            width: calc(100% - 10px);
            padding: 5px;
            box-sizing: border-box;
            background-color: #333;
            color: #eee;
            border: 1px solid #555;
            border-radius: 3px;
        }
        .control-group input[type="checkbox"] {
            width: auto;
            margin-right: 5px;
        }

        input[type="range"] {
            -webkit-appearance: none;
            appearance: none;
            width: calc(100% - 10px);
            height: 20px;
            background: transparent;
            outline: none;
            margin-top: 5px;
            margin-bottom: 5px;
            opacity: 0.9;
            transition: opacity 0.2s;
        }
        input[type="range"]:hover { opacity: 1; }
        input[type="range"]::-webkit-slider-runnable-track {
            width: 100%; height: 8px; background: #555;
            border-radius: 4px; cursor: pointer;
        }
        input[type="range"]::-moz-range-track {
            width: 100%; height: 8px; background: #555;
            border-radius: 4px; cursor: pointer; border: none;
        }
        input[type="range"]::-webkit-slider-thumb {
            -webkit-appearance: none; appearance: none;
            width: 18px; height: 18px; background: #007bff;
            border-radius: 50%; border: none; cursor: pointer;
            margin-top: -5px;
        }
        input[type="range"]::-moz-range-thumb {
            width: 18px; height: 18px; background: #007bff;
            border-radius: 50%; border: none; cursor: pointer;
        }
        input[type="range"]:hover::-webkit-slider-thumb,
        input[type="range"]:focus::-webkit-slider-thumb { background: #0056b3; }
        input[type="range"]:hover::-moz-range-thumb,
        input[type="range"]:focus::-moz-range-thumb { background: #0056b3; }

        #controlsPanel button {
            background-color: #007bff; color: white; border: none;
            padding: 10px 15px; text-align: center; text-decoration: none;
            display: inline-block; font-size: 1em; margin: 4px 2px;
            cursor: pointer; border-radius: 4px; width: 100%;
            box-sizing: border-box;
        }
        #controlsPanel button:hover { background-color: #0056b3; }
        #controlsPanel button#soundToggle.muted,
        #controlsPanel button#autoplayToggleButton.active { /* Style for active autoplay */
            background-color: #28a745; /* Green for active */
        }
        #controlsPanel button#autoplayToggleButton.active:hover {
             background-color: #1f7a34; /* Darker green on hover */
        }
        #controlsPanel button#soundToggle.muted:hover { background-color: #444; }

    </style>
</head>
<body>
    <div class="container">
        <canvas id="skyCanvas"></canvas>
        <div id="controlsPanel">
            <h2>Firework Customization</h2>

            <div class="control-group">
                <label for="fireworkType">Type/Shape:</label>
                <select id="fireworkType">
                    <option value="peony">Peony</option>
                    <option value="chrysanthemum">Chrysanthemum</option>
                    <option value="willow">Willow</option>
                    <option value="palm">Palm</option>
                    <option value="crossette">Crossette (Erratic)</option>
                    <option value="strobe">Strobe (Type)</option>
                    <option value="crackle">Crackle (Type)</option>
                    <option value="rocket">Rocket</option>
                </select>
            </div>

            <div class="control-group">
                <label for="primaryColor">Primary Color:</label>
                <input type="color" id="primaryColor" value="#FF0000">
            </div>

            <div class="control-group">
                <label for="secondaryColor">Secondary Color (Optional):</label>
                <input type="color" id="secondaryColor" value="#FFFF00">
                <input type="checkbox" id="useSecondaryColor"> Use
            </div>
            
            <div class="control-group">
                <label for="specialEffect">Special Effect (Particles):</label>
                <select id="specialEffect">
                    <option value="none">None</option>
                    <option value="glitter">Glitter</option>
                    <option value="fallingLeaves">Falling Leaves</option>
                    <option value="comet">Comet Tail</option>
                    <option value="multiBurst">Multi-Burst</option>
                </select>
            </div>

            <div class="control-group">
                <label for="burstRadius">Size/Burst Radius: <span id="burstRadiusValue">50</span></label>
                <input type="range" id="burstRadius" min="10" max="150" value="50">
            </div>

            <div class="control-group">
                <label for="trailLength">Trail Length: <span id="trailLengthValue">50</span></label>
                <input type="range" id="trailLength" min="10" max="100" value="50">
            </div>

            <div class="control-group">
                <label for="soundToggle">Sound:</label>
                <button id="soundToggle">On</button>
            </div>

            <h2>Launch Controls</h2>
            <button id="launchButton">Launch Firework</button>
            <div class="control-group">
                <label for="clickToLaunch">Click-to-Launch:</label>
                <input type="checkbox" id="clickToLaunch" checked> Enabled
            </div>

            <h2>Autoplay</h2>
            <button id="autoplayToggleButton">Start Autoplay</button>
             <div class="control-group">
                <label for="autoplaySpeed">Launch Interval: <span id="autoplaySpeedValue">1000</span> ms</label>
                <input type="range" id="autoplaySpeed" min="200" max="3000" value="1000" step="100">
            </div>


            <h2>Quick Actions</h2>
            <button id="randomizeButton">Randomize Firework</button>
            <button id="clearSkyButton">Clear Sky</button>
            <!-- Finale button removed -->
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const canvas = document.getElementById('skyCanvas');
            const ctx = canvas.getContext('2d');

            const fireworkTypeSelect = document.getElementById('fireworkType');
            const primaryColorInput = document.getElementById('primaryColor');
            const secondaryColorInput = document.getElementById('secondaryColor');
            const useSecondaryColorCheckbox = document.getElementById('useSecondaryColor');
            const specialEffectSelect = document.getElementById('specialEffect');
            const burstRadiusSlider = document.getElementById('burstRadius');
            const burstRadiusValueSpan = document.getElementById('burstRadiusValue');
            const trailLengthSlider = document.getElementById('trailLength');
            const trailLengthValueSpan = document.getElementById('trailLengthValue');
            const soundToggleButton = document.getElementById('soundToggle');
            const launchButton = document.getElementById('launchButton');
            const clickToLaunchCheckbox = document.getElementById('clickToLaunch');
            const randomizeButton = document.getElementById('randomizeButton');
            const clearSkyButton = document.getElementById('clearSkyButton');
            
            // Autoplay elements
            const autoplayToggleButton = document.getElementById('autoplayToggleButton');
            const autoplaySpeedSlider = document.getElementById('autoplaySpeed');
            const autoplaySpeedValueSpan = document.getElementById('autoplaySpeedValue');


            let soundEnabled = true;
            let fireworks = [];
            let particles = [];
            const gravity = 0.03; 
            const airResistance = 0.98;
            const trailAirResistance = 0.95;

            // Autoplay state
            let isAutoplaying = false;
            let autoplayIntervalId = null;
            let currentAutoplayInterval = parseInt(autoplaySpeedSlider.value); // Initial value from slider

            function resizeCanvas() {
                if (canvas.offsetWidth > 0 && canvas.offsetHeight > 0) {
                    canvas.width = canvas.offsetWidth;
                    canvas.height = canvas.offsetHeight;
                }
            }
            window.addEventListener('resize', resizeCanvas);
            resizeCanvas();

            burstRadiusSlider.oninput = () => burstRadiusValueSpan.textContent = burstRadiusSlider.value;
            trailLengthSlider.oninput = () => trailLengthValueSpan.textContent = trailLengthSlider.value;
            
            function hslToHex(h, s, l) {
                l /= 100; const a = s * Math.min(l, 1 - l) / 100;
                const f = n => {
                    const k = (n + h / 30) % 12;
                    const color = l - a * Math.max(Math.min(k - 3, 9 - k, 1), -1);
                    return Math.round(255 * color).toString(16).padStart(2, '0');
                }; return `#${f(0)}${f(8)}${f(4)}`;
            }

            const sounds = { /* Placeholders */ launch: null, explodeSmall: null, explodeMedium: null, explodeLarge: null, crackle: null, whistle: null };
            function playSound(soundName) {
                if (soundEnabled) { console.log(`Sound: ${soundName}`); }
            }
            soundToggleButton.addEventListener('click', () => {
                soundEnabled = !soundEnabled;
                soundToggleButton.textContent = soundEnabled ? 'On' : 'Off';
                soundToggleButton.classList.toggle('muted', !soundEnabled);
            });

            class Particle {
                constructor(x, y, vx, vy, color, size, lifetime, isTrail = false, effect = 'none') {
                    this.x = x; this.y = y; this.vx = vx; this.vy = vy;
                    this.color = color; this.size = size; this.lifetime = lifetime;
                    this.initialLifetime = lifetime; this.isTrail = isTrail; this.effect = effect;
                    this.strobeCounter = 0; this.canCrackle = true; this.canMiniBurst = true;
                }
                update() {
                    this.x += this.vx; this.y += this.vy; this.vy += gravity; 
                    const currentAirResistance = this.isTrail ? trailAirResistance : airResistance;
                    this.vx *= currentAirResistance; this.vy *= currentAirResistance;
                    this.lifetime--;

                    if (this.effect === 'crackle' && this.canCrackle && this.lifetime > 0 && this.lifetime < this.initialLifetime * 0.7 && Math.random() < 0.1) {
                        for (let i = 0; i < 2 + Math.random()*2; i++) { 
                            particles.push(new Particle(this.x, this.y, (Math.random() - 0.5) * 2.5, (Math.random() - 0.5) * 2.5, '#FFFFFF', Math.random() * 1.5 + 0.5, Math.random() * 15 + 10, false, 'none'));
                        }
                        playSound('crackle'); this.canCrackle = false; 
                    }
                    if (this.effect === 'comet' && this.lifetime > this.initialLifetime * 0.1 && Math.random() < 0.3) {
                        particles.push(new Particle(this.x, this.y, this.vx * 0.05, this.vy * 0.05, this.color, Math.max(0.5, this.size * (0.4 + Math.random() * 0.2)), Math.max(5, this.initialLifetime * (0.2 + Math.random() * 0.2)), true, 'none'));
                    }
                    if (this.effect === 'multiBurst' && this.canMiniBurst && this.lifetime < this.initialLifetime * (0.4 + Math.random()*0.2) && this.lifetime > 15) {
                        this.canMiniBurst = false; this.lifetime = Math.min(this.lifetime, 10); 
                        const numMiniParticles = 6 + Math.floor(Math.random() * 6);
                        for (let j = 0; j < numMiniParticles; j++) {
                            const angle = Math.random() * Math.PI * 2;
                            particles.push(new Particle(this.x, this.y, Math.cos(angle) * (1 + Math.random()), Math.sin(angle) * (1 + Math.random()), (useSecondaryColorCheckbox.checked && Math.random() < 0.5) ? secondaryColorInput.value : this.color, this.size * (0.2 + Math.random() * 0.3), 20 + Math.random() * 20, false, Math.random() < 0.15 ? 'glitter' : 'none'));
                        }
                        playSound('explodeSmall');
                    }
                }
                draw() {
                    ctx.globalAlpha = Math.max(0, this.lifetime / this.initialLifetime); 
                    ctx.fillStyle = this.color;
                    if (this.effect === 'strobe' && this.lifetime > 0) {
                        this.strobeCounter++; if (this.strobeCounter % 8 < 4) ctx.fillStyle = 'rgba(255,255,255,'+ (0.6 + Math.random()*0.4) +')'; 
                    }
                    ctx.beginPath(); ctx.arc(this.x, this.y, Math.max(0.1, this.size), 0, Math.PI * 2); ctx.fill();
                    ctx.globalAlpha = 1; 
                }
            }

            class Firework { // ... (Firework class remains largely the same as previous version)
                 constructor(startX, startY, targetX, targetY, type, primaryColor, secondaryColor, useSecondary, burstRadius, trailLength, specialEffect) {
                    this.x = startX; this.y = startY; this.targetX = targetX; this.targetY = targetY;
                    const angle = Math.atan2(targetY - startY, targetX - startX);
                    const speed = 8 + Math.random() * 4; 
                    this.vx = Math.cos(angle) * speed; this.vy = Math.sin(angle) * speed;
                    this.type = type; this.primaryColor = primaryColor; this.secondaryColor = secondaryColor;
                    this.useSecondary = useSecondary; this.burstRadius = parseInt(burstRadius);
                    this.trailLength = parseInt(trailLength); this.specialEffect = specialEffect; // Particle effect
                    this.exploded = false; this.particles = []; this.lifetime = 250; 
                }
                update() {
                    if (!this.exploded) {
                        this.x += this.vx; this.y += this.vy; this.vy += gravity * 0.6; 
                        if (Math.random() < 0.6) {
                            this.particles.push(new Particle(this.x, this.y, -this.vx*0.1, -this.vy*0.1, '#FFD700', Math.random() * 1.8 + 0.5, this.trailLength * (0.5 + Math.random()*0.5), true));
                        }
                        this.particles = this.particles.filter(p => p.lifetime > 0);
                        this.particles.forEach(p => p.update());
                        if (this.y <= this.targetY || this.vy >= -0.2) this.explode();
                    }
                    this.lifetime--;
                }
                explode() {
                    this.exploded = true;
                    playSound(this.burstRadius > 100 ? 'explodeLarge' : (this.burstRadius > 50 ? 'explodeMedium' : 'explodeSmall'));
                    
                    let numParticles = 40 + Math.floor(this.burstRadius);
                    if (this.type === 'rocket') numParticles = 15 + Math.floor(this.burstRadius / 2);
                    if (this.type === 'chrysanthemum') numParticles = 80 + Math.floor(this.burstRadius * 1.2);
                    if (this.type === 'willow' || this.type === 'palm') numParticles *= 0.8;

                    for (let i = 0; i < numParticles; i++) {
                        const angle = Math.random() * Math.PI * 2;
                        let speed = Math.random() * (this.burstRadius / 15 + 1);
                        let pLifetime = 40 + Math.random() * 40 + (this.burstRadius / 2);
                        let pSize = Math.random() * 1.5 + 0.5 + (this.burstRadius / 70);
                        let pColor = this.primaryColor;
                        if (this.useSecondary && Math.random() < 0.45) pColor = this.secondaryColor;
                        
                        let particleEffectToApply = this.specialEffect; 
                        if (this.type === 'strobe') particleEffectToApply = 'strobe';
                        if (this.type === 'crackle') particleEffectToApply = 'crackle';

                        let pVx = Math.cos(angle) * speed; let pVy = Math.sin(angle) * speed;

                        switch (this.type) {
                            case 'willow':
                                pVy += Math.random() * 0.8 + (gravity * 15); pLifetime *= 1.8; pSize *= 0.7;
                                pColor = Math.random() < 0.85 ? '#FFD700' : this.primaryColor; 
                                speed *= (0.6 + Math.random()*0.4);
                                pVx = Math.cos(angle) * speed; pVy = Math.sin(angle) * speed + (gravity * 10);
                                break;
                            case 'palm':
                                const palmBranches = 4 + Math.floor(this.burstRadius / 30);
                                if (i < palmBranches) { 
                                    const bAngle = (i / palmBranches) * Math.PI * 2 + (Math.random() - 0.5) * 0.3;
                                    pVx = Math.cos(bAngle) * speed * 1.3; pVy = Math.sin(bAngle) * speed * 1.3;
                                    pLifetime *= 1.3; pSize *= 1.6;
                                    particleEffectToApply = Math.random() < 0.3 ? 'comet' : particleEffectToApply;
                                } else { 
                                    pVx = Math.cos(angle) * speed * 0.4; pVy = Math.sin(angle) * speed * 0.4;
                                    pLifetime *= 0.6; pSize *= 0.8;
                                }
                                break;
                            case 'crossette': 
                                pVx *= (0.6 + Math.random() * 1.2); pVy *= (0.6 + Math.random() * 1.2);
                                if(Math.random() < 0.3) particleEffectToApply = 'crackle'; 
                                break;
                            case 'rocket':
                                speed *= 0.6; pVx = Math.cos(angle) * speed; pVy = Math.sin(angle) * speed;
                                pLifetime *= 0.4; pSize *= 0.9;
                                break;
                        }
                        if (particleEffectToApply === 'glitter' && Math.random() < 0.4) { pLifetime *= 0.6; pColor = '#FFFFFF'; pSize *= 0.7; }
                        if (particleEffectToApply === 'fallingLeaves') { 
                            pVy = Math.abs(pVy) * 0.05 + (gravity * 3); pVx *= 0.15; 
                            pLifetime *= 2.8; pSize *= 1.4; 
                        }
                        particles.push(new Particle(this.x, this.y, pVx, pVy, pColor, pSize, pLifetime, false, particleEffectToApply));
                    }
                }
                draw() {
                    this.particles.forEach(p => p.draw());
                    if (!this.exploded) {
                        ctx.fillStyle = '#FAFAFA'; ctx.beginPath();
                        ctx.arc(this.x, this.y, 2.5, 0, Math.PI * 2); ctx.fill();
                    }
                }
            }


            function launchConfiguredFirework(targetX, targetY) {
                // This function now ALWAYS reads from the current UI controls
                playSound('launch');
                const currentType = fireworkTypeSelect.value;
                const currentPrimaryColor = primaryColorInput.value;
                const currentSecondaryColor = secondaryColorInput.value;
                const currentUseSecondary = useSecondaryColorCheckbox.checked;
                const currentBurstRadius = burstRadiusSlider.value;
                const currentTrailLength = trailLengthSlider.value;
                const currentSpecialEffect = specialEffectSelect.value;

                const launchX = targetX === undefined ? (canvas.width / 2 + (Math.random() - 0.5) * (canvas.width / 3)) : targetX;
                const launchTargetY = targetY === undefined ? (canvas.height * (0.1 + Math.random() * 0.3)) : targetY;

                fireworks.push(new Firework(launchX, canvas.height, launchX, launchTargetY, currentType, currentPrimaryColor, currentSecondaryColor, currentUseSecondary, currentBurstRadius, currentTrailLength, currentSpecialEffect));
            }

            launchButton.addEventListener('click', () => launchConfiguredFirework());
            canvas.addEventListener('click', (e) => {
                if (clickToLaunchCheckbox.checked) {
                    const rect = canvas.getBoundingClientRect();
                    launchConfiguredFirework(e.clientX - rect.left, Math.max(canvas.height * 0.05, Math.min(e.clientY - rect.top, canvas.height * 0.85)));
                }
            });

            randomizeButton.addEventListener('click', () => {
                const types = Array.from(fireworkTypeSelect.options).map(opt => opt.value);
                fireworkTypeSelect.value = types[Math.floor(Math.random() * types.length)];
                const h1 = Math.random() * 360; primaryColorInput.value = hslToHex(h1, 100, 65 + Math.random()*10);
                const h2 = Math.random() * 360; secondaryColorInput.value = hslToHex(h2, 100, 65 + Math.random()*10);
                useSecondaryColorCheckbox.checked = Math.random() < 0.6;
                const effects = Array.from(specialEffectSelect.options).map(opt => opt.value);
                specialEffectSelect.value = effects[Math.floor(Math.random() * effects.length)];
                burstRadiusSlider.value = Math.random() * 130 + 20;
                burstRadiusValueSpan.textContent = burstRadiusSlider.value;
                trailLengthSlider.value = Math.random() * 80 + 20;
                trailLengthValueSpan.textContent = trailLengthSlider.value;
                launchConfiguredFirework();
            });

            clearSkyButton.addEventListener('click', () => { fireworks = []; particles = []; });

            // --- Autoplay Logic ---
            autoplaySpeedSlider.addEventListener('input', () => {
                currentAutoplayInterval = parseInt(autoplaySpeedSlider.value);
                autoplaySpeedValueSpan.textContent = `${currentAutoplayInterval}`; // Update span without "ms" here as it's in label
                if (isAutoplaying) {
                    stopAutoplay(); 
                    startAutoplay(); 
                }
            });
            autoplaySpeedValueSpan.textContent = `${autoplaySpeedSlider.value}`; // Initialize span

            autoplayToggleButton.addEventListener('click', () => {
                isAutoplaying = !isAutoplaying;
                if (isAutoplaying) {
                    autoplayToggleButton.textContent = 'Stop Autoplay';
                    autoplayToggleButton.classList.add('active');
                    startAutoplay();
                } else {
                    autoplayToggleButton.textContent = 'Start Autoplay';
                    autoplayToggleButton.classList.remove('active');
                    stopAutoplay();
                }
            });

            function startAutoplay() {
                if (autoplayIntervalId) clearInterval(autoplayIntervalId); 

                autoplayIntervalId = setInterval(() => {
                    // Randomize parameters for each autoplayed firework
                    const types = Array.from(fireworkTypeSelect.options).map(opt => opt.value);
                    fireworkTypeSelect.value = types[Math.floor(Math.random() * types.length)];
                    
                    const h1 = Math.random() * 360; primaryColorInput.value = hslToHex(h1, 100, 60 + Math.random()*20);
                    const h2 = Math.random() * 360; secondaryColorInput.value = hslToHex(h2, 100, 60 + Math.random()*20);
                    useSecondaryColorCheckbox.checked = Math.random() < 0.65;

                    const effects = Array.from(specialEffectSelect.options).map(opt => opt.value);
                    specialEffectSelect.value = effects[Math.floor(Math.random() * effects.length)];
                    
                    burstRadiusSlider.value = Math.random() * 100 + 40; // Slightly smaller average, but still varied
                    burstRadiusValueSpan.textContent = burstRadiusSlider.value;
                    trailLengthSlider.value = Math.random() * 70 + 30;
                    trailLengthValueSpan.textContent = trailLengthSlider.value;

                    const autoTargetX = canvas.width * (0.05 + Math.random() * 0.9);
                    const autoTargetY = canvas.height * (0.05 + Math.random() * 0.4);
                    
                    launchConfiguredFirework(autoTargetX, autoTargetY); 
                }, currentAutoplayInterval);
            }

            function stopAutoplay() {
                if (autoplayIntervalId) {
                    clearInterval(autoplayIntervalId);
                    autoplayIntervalId = null;
                }
            }
            // --- End Autoplay Logic ---

            function animate() {
                ctx.fillStyle = 'rgba(0, 0, 8, 0.1)'; 
                ctx.fillRect(0, 0, canvas.width, canvas.height);
                fireworks.forEach(fw => { fw.update(); fw.draw(); });
                fireworks = fireworks.filter(fw => (!fw.exploded && fw.lifetime > 0) || (fw.exploded && fw.lifetime > -300) ); 
                particles.forEach(p => { p.update(); p.draw(); });
                particles = particles.filter(p => p.lifetime > 0);
                requestAnimationFrame(animate);
            }
            animate();
        });
    </script>
</body>
</html>