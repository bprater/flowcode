<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fireworks Simulator</title>
    <style>
        html, body { margin: 0; padding: 0; width: 100%; height: 100%; font-family: Arial, sans-serif; background-color: #111; color: #eee; overflow: hidden; }
        .container { display: flex; width: 100%; height: 100vh; }
        #skyCanvas { flex-grow: 1; min-width: 0; background-color: #000010; cursor: crosshair; }
        #controlsPanel { width: 300px; min-width: 280px; flex-shrink: 0; background-color: #222; padding: 20px; box-sizing: border-box; overflow-y: auto; border-left: 2px solid #444; height: 100vh; }
        #controlsPanel h2 { margin-top: 0; margin-bottom: 15px; color: #00aaff; border-bottom: 1px solid #444; padding-bottom: 5px; }
        .control-group { margin-bottom: 15px; }
        .control-group label { display: block; margin-bottom: 5px; font-size: 0.9em; }
        .control-group select, .control-group input[type="color"], .control-group input[type="checkbox"] { width: calc(100% - 10px); padding: 5px; box-sizing: border-box; background-color: #333; color: #eee; border: 1px solid #555; border-radius: 3px; }
        .control-group input[type="checkbox"] { width: auto; margin-right: 5px; }
        input[type="range"] { -webkit-appearance: none; appearance: none; width: calc(100% - 10px); height: 20px; background: transparent; outline: none; margin-top: 5px; margin-bottom: 5px; opacity: 0.9; transition: opacity 0.2s; }
        input[type="range"]:hover { opacity: 1; }
        input[type="range"]::-webkit-slider-runnable-track { width: 100%; height: 8px; background: #555; border-radius: 4px; cursor: pointer; }
        input[type="range"]::-moz-range-track { width: 100%; height: 8px; background: #555; border-radius: 4px; cursor: pointer; border: none; }
        input[type="range"]::-webkit-slider-thumb { -webkit-appearance: none; appearance: none; width: 18px; height: 18px; background: #007bff; border-radius: 50%; border: none; cursor: pointer; margin-top: -5px; }
        input[type="range"]::-moz-range-thumb { width: 18px; height: 18px; background: #007bff; border-radius: 50%; border: none; cursor: pointer; }
        input[type="range"]:hover::-webkit-slider-thumb, input[type="range"]:focus::-webkit-slider-thumb { background: #0056b3; }
        input[type="range"]:hover::-moz-range-thumb, input[type="range"]:focus::-moz-range-thumb { background: #0056b3; }
        #controlsPanel button { background-color: #007bff; color: white; border: none; padding: 10px 15px; text-align: center; text-decoration: none; display: inline-block; font-size: 1em; margin: 4px 2px; cursor: pointer; border-radius: 4px; width: 100%; box-sizing: border-box; }
        #controlsPanel button:hover { background-color: #0056b3; }
        #controlsPanel button#soundToggle.muted, #controlsPanel button#autoplayToggleButton.active { background-color: #28a745; }
        #controlsPanel button#autoplayToggleButton.active:hover { background-color: #1f7a34; }
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
                    <option value="peony">Peony (Rainbow)</option>
                    <option value="chrysanthemum">Chrysanthemum (Rainbow)</option>
                    <option value="willow">Willow (Rainbow)</option>
                    <option value="palm">Palm (Rainbow)</option>
                    <option value="crossette">Crossette (Erratic)</option>
                    <option value="strobe">Strobe (Type)</option>
                    <option value="crackle">Crackle (Type)</option>
                    <option value="rocket">Rocket</option>
                </select>
            </div>
            <div class="control-group">
                <label for="primaryColor">Base Hue 1 (for Rainbow):</label>
                <input type="color" id="primaryColor" value="#FF0000">
            </div>
            <div class="control-group">
                <label for="secondaryColor">Base Hue 2 (Optional):</label>
                <input type="color" id="secondaryColor" value="#00FF00">
                <input type="checkbox" id="useSecondaryColor" checked> Use
            </div>
            <div class="control-group">
                <label for="specialEffect">Special Particle Effect:</label>
                <select id="specialEffect">
                    <option value="none">None</option>
                    <option value="glitter">Glitter</option>
                    <option value="comet">Comet Tail</option>
                    <option value="multiBurst">Multi-Burst</option>
                    <option value="fountain">Fountain</option>
                    <option value="spinningWheel">Spinning Wheel</option>
                    <option value="colorShift">Color Shift</option>
                </select>
            </div>
            <div class="control-group">
                <label for="burstRadius">Size/Burst Radius: <span id="burstRadiusValue">60</span></label>
                <input type="range" id="burstRadius" min="20" max="180" value="60">
            </div>
            <div class="control-group">
                <label for="trailLength">Trail Length: <span id="trailLengthValue">60</span></label>
                <input type="range" id="trailLength" min="10" max="120" value="60">
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
                <input type="range" id="autoplaySpeed" min="100" max="2500" value="1000" step="50">
            </div>
            <h2>Quick Actions</h2>
            <button id="randomizeButton">Randomize Firework</button>
            <button id="clearSkyButton">Clear Sky</button>
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
            const autoplayToggleButton = document.getElementById('autoplayToggleButton');
            const autoplaySpeedSlider = document.getElementById('autoplaySpeed');
            const autoplaySpeedValueSpan = document.getElementById('autoplaySpeedValue');

            let soundEnabled = true;
            let fireworks = [];
            let particles = [];
            const gravity = 0.035; 
            const airResistance = 0.985;
            const trailAirResistance = 0.96;

            let isAutoplaying = false;
            let autoplayIntervalId = null;
            let currentAutoplayInterval = parseInt(autoplaySpeedSlider.value);
            let globalHueOffset = 0; // For rainbow cycling

            function resizeCanvas() { if (canvas.offsetWidth > 0 && canvas.offsetHeight > 0) { canvas.width = canvas.offsetWidth; canvas.height = canvas.offsetHeight; } }
            window.addEventListener('resize', resizeCanvas);
            resizeCanvas();

            burstRadiusSlider.oninput = () => burstRadiusValueSpan.textContent = burstRadiusSlider.value;
            trailLengthSlider.oninput = () => trailLengthValueSpan.textContent = trailLengthSlider.value;
            
            function hslToHex(h, s, l) { // h (0-360), s (0-100), l (0-100)
                l /= 100; const a = s * Math.min(l, 1 - l) / 100;
                const f = n => {
                    const k = (n + h / 30) % 12;
                    const color = l - a * Math.max(Math.min(k - 3, 9 - k, 1), -1);
                    return Math.round(255 * color).toString(16).padStart(2, '0');
                }; return `#${f(0)}${f(8)}${f(4)}`;
            }
            function hexToHsl(hex) { // Returns [h, s, l]
                let r = 0, g = 0, b = 0;
                if (hex.length == 4) {
                    r = "0x" + hex[1] + hex[1]; g = "0x" + hex[2] + hex[2]; b = "0x" + hex[3] + hex[3];
                } else if (hex.length == 7) {
                    r = "0x" + hex[1] + hex[2]; g = "0x" + hex[3] + hex[4]; b = "0x" + hex[5] + hex[6];
                }
                r /= 255; g /= 255; b /= 255;
                let cmin = Math.min(r,g,b), cmax = Math.max(r,g,b), delta = cmax - cmin, h = 0, s = 0, l = 0;
                if (delta == 0) h = 0;
                else if (cmax == r) h = ((g - b) / delta) % 6;
                else if (cmax == g) h = (b - r) / delta + 2;
                else h = (r - g) / delta + 4;
                h = Math.round(h * 60);
                if (h < 0) h += 360;
                l = (cmax + cmin) / 2;
                s = delta == 0 ? 0 : delta / (1 - Math.abs(2 * l - 1));
                s = +(s * 100).toFixed(1);
                l = +(l * 100).toFixed(1);
                return [h, s, l];
            }


            const sounds = { /* ... */ };
            function playSound(soundName) { if (soundEnabled) { console.log(`Sound: ${soundName}`); } }
            soundToggleButton.addEventListener('click', () => { soundEnabled = !soundEnabled; soundToggleButton.textContent = soundEnabled ? 'On' : 'Off'; soundToggleButton.classList.toggle('muted', !soundEnabled); });

            class Particle {
                constructor(x, y, vx, vy, color, size, lifetime, isTrail = false, effect = 'none', parentFirework = null) {
                    this.x = x; this.y = y; this.vx = vx; this.vy = vy;
                    this.baseColor = color; // Store original color for effects like colorShift
                    this.color = color;
                    this.size = size; this.lifetime = lifetime;
                    this.initialLifetime = lifetime; this.isTrail = isTrail; this.effect = effect;
                    this.parentFirework = parentFirework; // Reference to parent firework for base hues

                    this.strobeCounter = 0; this.canCrackle = true; this.canMiniBurst = true;
                    this.shimmerCounter = Math.random() * 10; // For shimmering
                    this.spinAngle = 0; // For spinning wheel
                    this.spinRadius = Math.random() * 5 + 2; // For spinning wheel
                    this.fountainApexY = y - (Math.random() * 50 + 20); // For fountain
                }
                update() {
                    this.x += this.vx; this.y += this.vy; 
                    
                    if (this.effect === 'fountain') {
                        if (this.y > this.fountainApexY) { // Moving up
                            this.vy += gravity * 0.5; // Less initial gravity effect
                        } else { // Falling down
                            this.vy += gravity * 1.5;
                        }
                    } else {
                        this.vy += gravity; 
                    }

                    const currentAirResistance = this.isTrail ? trailAirResistance : airResistance;
                    this.vx *= currentAirResistance; this.vy *= currentAirResistance;
                    this.lifetime--;
                    this.shimmerCounter++;

                    // Particle Effects
                    if (this.effect === 'crackle' && this.canCrackle && this.lifetime > 0 && this.lifetime < this.initialLifetime * 0.6 && Math.random() < 0.15) {
                        for (let i = 0; i < 3 + Math.random()*2; i++) { particles.push(new Particle(this.x, this.y, (Math.random() - 0.5) * 3, (Math.random() - 0.5) * 3, '#FFFFFF', Math.random() * 1.8 + 0.8, Math.random() * 18 + 12, false, 'none', this.parentFirework)); }
                        playSound('crackle'); this.canCrackle = false; 
                    }
                    if (this.effect === 'comet' && this.lifetime > this.initialLifetime * 0.15 && Math.random() < 0.35) {
                        const tailColor = hslToHex((hexToHsl(this.color)[0] + Math.random()*30 -15 + 360)%360, 100, 60 + Math.random()*10);
                        particles.push(new Particle(this.x, this.y, this.vx * 0.05, this.vy * 0.05, tailColor, Math.max(0.5, this.size * (0.3 + Math.random() * 0.3)), Math.max(8, this.initialLifetime * (0.25 + Math.random() * 0.25)), true, 'none', this.parentFirework));
                    }
                    if (this.effect === 'multiBurst' && this.canMiniBurst && this.lifetime < this.initialLifetime * (0.3 + Math.random()*0.3) && this.lifetime > 20) {
                        this.canMiniBurst = false; this.lifetime = Math.min(this.lifetime, 15); 
                        const numMini = 7 + Math.floor(Math.random() * 7);
                        for (let j = 0; j < numMini; j++) {
                            const angle = Math.random() * Math.PI * 2;
                            const miniColor = hslToHex((hexToHsl(this.baseColor)[0] + Math.random()*60-30 + 360)%360, 100, 65 + Math.random()*10);
                            particles.push(new Particle(this.x, this.y, Math.cos(angle) * (1.2 + Math.random()*0.8), Math.sin(angle) * (1.2 + Math.random()*0.8), miniColor, this.size * (0.25 + Math.random() * 0.35), 25 + Math.random() * 25, false, Math.random() < 0.2 ? 'glitter' : 'none', this.parentFirework));
                        }
                        playSound('explodeSmall');
                    }
                    if (this.effect === 'spinningWheel') {
                        this.spinAngle += 0.1 + Math.random() * 0.1; // Rotation speed
                        this.vx += Math.cos(this.spinAngle) * this.spinRadius * 0.05; // Apply spiral force
                        this.vy += Math.sin(this.spinAngle) * this.spinRadius * 0.05;
                        this.vx *= 0.97; // Dampen original velocity to emphasize spin
                        this.vy *= 0.97;
                        this.spinRadius *= 0.99; // Radius can shrink or grow
                    }
                     if (this.effect === 'colorShift') {
                        const progress = 1 - (this.lifetime / this.initialLifetime);
                        const currentHue = (hexToHsl(this.baseColor)[0] + progress * 180 + 360) % 360; // Shift hue over lifetime
                        this.color = hslToHex(currentHue, 100, 60 + Math.sin(progress * Math.PI) * 15); // Lightness pulsates
                    }
                }
                draw() {
                    let alpha = Math.max(0, this.lifetime / this.initialLifetime);
                    // Shimmering effect: rapid small changes in alpha/brightness
                    if (this.shimmerCounter % 4 < 2 && !this.isTrail) {
                        alpha *= (0.75 + Math.random() * 0.25);
                    }
                    ctx.globalAlpha = alpha;
                    
                    ctx.fillStyle = this.color;
                    if (this.effect === 'strobe' && this.lifetime > 0) {
                        this.strobeCounter++; if (this.strobeCounter % 7 < 3) ctx.fillStyle = 'rgba(255,255,255,'+ (0.7 + Math.random()*0.3) +')'; 
                    }
                    ctx.beginPath(); ctx.arc(this.x, this.y, Math.max(0.1, this.size), 0, Math.PI * 2); ctx.fill();
                    ctx.globalAlpha = 1; 
                }
            }

            class Firework {
                constructor(startX, startY, targetX, targetY, type, primaryColor, secondaryColor, useSecondary, burstRadius, trailLength, specialEffect) {
                    this.x = startX; this.y = startY; this.targetX = targetX; this.targetY = targetY;
                    const angle = Math.atan2(targetY - startY, targetX - startX);
                    const speed = 9 + Math.random() * 5; 
                    this.vx = Math.cos(angle) * speed; this.vy = Math.sin(angle) * speed;
                    this.type = type;
                    this.baseHue1 = hexToHsl(primaryColor)[0];
                    this.baseHue2 = useSecondary ? hexToHsl(secondaryColor)[0] : (this.baseHue1 + 30 + Math.random()*60)%360;
                    this.burstRadius = parseInt(burstRadius);
                    this.trailLength = parseInt(trailLength); 
                    this.particleEffect = specialEffect; // Effect selected in dropdown
                    this.exploded = false; this.particles = []; this.lifetime = 280; 
                    this.hueCycle = Math.random() * 360; // Initial hue for this firework's rainbow
                }
                update() {
                    if (!this.exploded) {
                        this.x += this.vx; this.y += this.vy; this.vy += gravity * 0.55; 
                        if (Math.random() < 0.65) {
                            const trailHue = (this.hueCycle + Math.random()*60-30 + 360)%360;
                            this.particles.push(new Particle(this.x, this.y, -this.vx*0.05, -this.vy*0.05, hslToHex(trailHue, 100, 70), Math.random() * 2 + 0.8, this.trailLength * (0.6 + Math.random()*0.4), true, 'none', this));
                        }
                        this.particles = this.particles.filter(p => p.lifetime > 0);
                        this.particles.forEach(p => p.update());
                        if (this.y <= this.targetY || this.vy >= -0.1) this.explode();
                    }
                    this.lifetime--;
                }
                explode() {
                    this.exploded = true;
                    playSound(this.burstRadius > 120 ? 'explodeLarge' : (this.burstRadius > 70 ? 'explodeMedium' : 'explodeSmall'));
                    
                    let numParticles = 50 + Math.floor(this.burstRadius * 1.1);
                    if (this.type === 'rocket') numParticles = 20 + Math.floor(this.burstRadius / 1.5);
                    if (this.type === 'chrysanthemum') numParticles = 90 + Math.floor(this.burstRadius * 1.5);
                    if (this.type === 'willow' || this.type === 'palm') numParticles = Math.max(30, numParticles * 0.7);

                    for (let i = 0; i < numParticles; i++) {
                        const angle = Math.random() * Math.PI * 2;
                        let speed = Math.random() * (this.burstRadius / 12 + 1.5);
                        let pLifetime = 50 + Math.random() * 50 + (this.burstRadius / 1.8);
                        let pSize = Math.random() * 1.8 + 0.8 + (this.burstRadius / 60);
                        
                        // --- RAINBOW COLOR LOGIC ---
                        let particleHue;
                        const hueRand = Math.random();
                        if (hueRand < 0.45) particleHue = (this.baseHue1 + Math.random() * 60 - 30 + 360)%360;
                        else if (hueRand < 0.9) particleHue = (this.baseHue2 + Math.random() * 60 - 30 + 360)%360;
                        else particleHue = (this.hueCycle + i * (360/numParticles) + Math.random()*20-10 + 360 + globalHueOffset)%360; // Spread hues
                        const pColor = hslToHex(particleHue, 100, 60 + Math.random()*20);
                        // --- END RAINBOW COLOR LOGIC ---
                        
                        let effectToApply = this.particleEffect; 
                        if (this.type === 'strobe') effectToApply = 'strobe';
                        if (this.type === 'crackle') effectToApply = 'crackle';

                        let pVx = Math.cos(angle) * speed; let pVy = Math.sin(angle) * speed;

                        switch (this.type) {
                            case 'willow':
                                pVy += Math.random() * 1.0 + (gravity * 18); pLifetime *= 2.0; pSize *= 0.65;
                                speed *= (0.5 + Math.random()*0.4);
                                pVx = Math.cos(angle) * speed; pVy = Math.sin(angle) * speed + (gravity * 12);
                                break;
                            case 'palm':
                                const palmBranches = 5 + Math.floor(this.burstRadius / 25);
                                if (i < palmBranches) { 
                                    const bAngle = (i / palmBranches) * Math.PI * 2 + (Math.random() - 0.5) * 0.25;
                                    pVx = Math.cos(bAngle) * speed * 1.4; pVy = Math.sin(bAngle) * speed * 1.4;
                                    pLifetime *= 1.4; pSize *= 1.7;
                                    effectToApply = Math.random() < 0.35 ? 'comet' : effectToApply;
                                } else { 
                                    pVx = Math.cos(angle) * speed * 0.35; pVy = Math.sin(angle) * speed * 0.35;
                                    pLifetime *= 0.55; pSize *= 0.75;
                                }
                                break;
                            case 'crossette': 
                                pVx *= (0.7 + Math.random() * 1.1); pVy *= (0.7 + Math.random() * 1.1);
                                if(Math.random() < 0.35) effectToApply = 'multiBurst'; // Crossettes are about splitting
                                else if(Math.random() < 0.2) effectToApply = 'crackle';
                                break;
                            case 'rocket':
                                speed *= 0.55; pVx = Math.cos(angle) * speed; pVy = Math.sin(angle) * speed;
                                pLifetime *= 0.35; pSize *= 0.85;
                                break;
                        }
                        if (effectToApply === 'glitter') { pLifetime *= 0.55; pSize *= 0.65; }
                        // Fountain and Spinning Wheel are primarily particle-driven effects, not firework type driven here
                        particles.push(new Particle(this.x, this.y, pVx, pVy, pColor, pSize, pLifetime, false, effectToApply, this));
                    }
                     globalHueOffset = (globalHueOffset + 1.5) % 360; // Slowly shift global hue for next firework
                }
                draw() {
                    this.particles.forEach(p => p.draw());
                    if (!this.exploded) {
                        ctx.fillStyle = '#F0F0F0'; ctx.beginPath();
                        ctx.arc(this.x, this.y, 2.8, 0, Math.PI * 2); ctx.fill();
                    }
                }
            }

            function launchConfiguredFirework(targetX, targetY) {
                playSound('launch');
                const currentType = fireworkTypeSelect.value;
                const currentPrimaryColor = primaryColorInput.value; // Used for baseHue1
                const currentSecondaryColor = secondaryColorInput.value; // Used for baseHue2
                const currentUseSecondary = useSecondaryColorCheckbox.checked;
                const currentBurstRadius = burstRadiusSlider.value;
                const currentTrailLength = trailLengthSlider.value;
                const currentSpecialEffect = specialEffectSelect.value;

                const launchX = targetX === undefined ? (canvas.width / 2 + (Math.random() - 0.5) * (canvas.width * 0.7)) : targetX;
                const launchTargetY = targetY === undefined ? (canvas.height * (0.05 + Math.random() * 0.35)) : targetY;

                fireworks.push(new Firework(launchX, canvas.height, launchX, launchTargetY, currentType, currentPrimaryColor, currentSecondaryColor, currentUseSecondary, currentBurstRadius, currentTrailLength, currentSpecialEffect));
            }

            launchButton.addEventListener('click', () => launchConfiguredFirework());
            canvas.addEventListener('click', (e) => {
                if (clickToLaunchCheckbox.checked) {
                    const rect = canvas.getBoundingClientRect();
                    launchConfiguredFirework(e.clientX - rect.left, Math.max(canvas.height * 0.05, Math.min(e.clientY - rect.top, canvas.height * 0.9)));
                }
            });

            randomizeButton.addEventListener('click', () => {
                const types = Array.from(fireworkTypeSelect.options).map(opt => opt.value);
                fireworkTypeSelect.value = types[Math.floor(Math.random() * types.length)];
                const h1 = Math.random() * 360; primaryColorInput.value = hslToHex(h1, 100, 65 + Math.random()*15);
                const h2 = Math.random() * 360; secondaryColorInput.value = hslToHex(h2, 100, 65 + Math.random()*15);
                useSecondaryColorCheckbox.checked = Math.random() < 0.7;
                const effects = Array.from(specialEffectSelect.options).map(opt => opt.value);
                specialEffectSelect.value = effects[Math.floor(Math.random() * effects.length)];
                burstRadiusSlider.value = Math.random() * 150 + 30;
                burstRadiusValueSpan.textContent = burstRadiusSlider.value;
                trailLengthSlider.value = Math.random() * 90 + 30;
                trailLengthValueSpan.textContent = trailLengthSlider.value;
                launchConfiguredFirework();
            });

            clearSkyButton.addEventListener('click', () => { fireworks = []; particles = []; });

            autoplaySpeedSlider.addEventListener('input', () => {
                currentAutoplayInterval = parseInt(autoplaySpeedSlider.value);
                autoplaySpeedValueSpan.textContent = `${currentAutoplayInterval}`;
                if (isAutoplaying) { stopAutoplay(); startAutoplay(); }
            });
            autoplaySpeedValueSpan.textContent = `${autoplaySpeedSlider.value}`;

            autoplayToggleButton.addEventListener('click', () => {
                isAutoplaying = !isAutoplaying;
                if (isAutoplaying) { autoplayToggleButton.textContent = 'Stop Autoplay'; autoplayToggleButton.classList.add('active'); startAutoplay(); }
                else { autoplayToggleButton.textContent = 'Start Autoplay'; autoplayToggleButton.classList.remove('active'); stopAutoplay(); }
            });

            function startAutoplay() {
                if (autoplayIntervalId) clearInterval(autoplayIntervalId); 
                autoplayIntervalId = setInterval(() => {
                    const types = Array.from(fireworkTypeSelect.options).map(opt => opt.value);
                    fireworkTypeSelect.value = types[Math.floor(Math.random() * types.length)];
                    primaryColorInput.value = hslToHex(Math.random() * 360, 100, 60 + Math.random()*25);
                    secondaryColorInput.value = hslToHex(Math.random() * 360, 100, 60 + Math.random()*25);
                    useSecondaryColorCheckbox.checked = Math.random() < 0.75;
                    const effects = Array.from(specialEffectSelect.options).map(opt => opt.value);
                    specialEffectSelect.value = effects[Math.floor(Math.random() * effects.length)];
                    burstRadiusSlider.value = Math.random() * 120 + 50; 
                    burstRadiusValueSpan.textContent = burstRadiusSlider.value;
                    trailLengthSlider.value = Math.random() * 80 + 40;
                    trailLengthValueSpan.textContent = trailLengthSlider.value;
                    const autoTargetX = canvas.width * (0.02 + Math.random() * 0.96);
                    const autoTargetY = canvas.height * (0.02 + Math.random() * 0.4);
                    launchConfiguredFirework(autoTargetX, autoTargetY); 
                }, currentAutoplayInterval);
            }
            function stopAutoplay() { if (autoplayIntervalId) { clearInterval(autoplayIntervalId); autoplayIntervalId = null; } }

            function animate() {
                ctx.fillStyle = 'rgba(0, 0, 5, 0.09)'; // Slightly less alpha for more persistence
                ctx.fillRect(0, 0, canvas.width, canvas.height);
                fireworks.forEach(fw => { fw.update(); fw.draw(); });
                fireworks = fireworks.filter(fw => (!fw.exploded && fw.lifetime > 0) || (fw.exploded && fw.lifetime > -350) ); 
                particles.forEach(p => { p.update(); p.draw(); });
                particles = particles.filter(p => p.lifetime > 0);
                requestAnimationFrame(animate);
            }
            animate();
        });
    </script>
</body>
</html>