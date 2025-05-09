<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fireworks Simulator - Sunset Reflection</title>
    <style>
        html, body { margin: 0; padding: 0; width: 100%; height: 100%; font-family: Arial, sans-serif; background-color: #111; color: #eee; overflow: hidden; }
        .container { display: flex; width: 100%; height: 100vh; }
        #skyCanvas { flex-grow: 1; min-width: 0; /* background-color will be handled by drawing */ cursor: crosshair; }
        #controlsPanel { width: 300px; min-width: 280px; flex-shrink: 0; background-color: #222; padding: 20px; box-sizing: border-box; overflow-y: auto; border-left: 2px solid #444; height: 100vh; }
        /* ... (rest of CSS is the same as previous) ... */
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
            <!-- ... (Controls UI is the same as previous version) ... -->
            <div class="control-group"> <label for="fireworkType">Type/Shape:</label> <select id="fireworkType"> <option value="peony">Peony (Rainbow)</option> <option value="chrysanthemum">Chrysanthemum (Rainbow)</option> <option value="willow">Willow (Rainbow)</option> <option value="palm">Palm (Rainbow)</option> <option value="crossette">Crossette (Erratic)</option> <option value="strobe">Strobe (Type)</option> <option value="crackle">Crackle (Type)</option> <option value="rocket">Rocket</option> </select> </div> <div class="control-group"> <label for="primaryColor">Base Hue 1 (for Rainbow):</label> <input type="color" id="primaryColor" value="#FF0000"> </div> <div class="control-group"> <label for="secondaryColor">Base Hue 2 (Optional):</label> <input type="color" id="secondaryColor" value="#00FF00"> <input type="checkbox" id="useSecondaryColor" checked> Use </div> <div class="control-group"> <label for="specialEffect">Special Particle Effect:</label> <select id="specialEffect"> <option value="none">None</option> <option value="glitter">Glitter</option> <option value="comet">Comet Tail</option> <option value="multiBurst">Multi-Burst</option> <option value="fountain">Fountain</option> <option value="spinningWheel">Spinning Wheel</option> <option value="colorShift">Color Shift</option> </select> </div> <div class="control-group"> <label for="burstRadius">Size/Burst Radius: <span id="burstRadiusValue">60</span></label> <input type="range" id="burstRadius" min="20" max="180" value="60"> </div> <div class="control-group"> <label for="trailLength">Trail Length: <span id="trailLengthValue">60</span></label> <input type="range" id="trailLength" min="10" max="120" value="60"> </div> <div class="control-group"> <label for="particleSizeScale">Particle Size Scale: <span id="particleSizeScaleValue">1.0</span>x</label> <input type="range" id="particleSizeScale" min="0.2" max="3.0" value="1.0" step="0.1"> </div> <div class="control-group"> <label for="soundToggle">Sound:</label> <button id="soundToggle">On</button> </div> <h2>Launch Controls</h2> <button id="launchButton">Launch Firework</button> <div class="control-group"> <label for="clickToLaunch">Click-to-Launch:</label> <input type="checkbox" id="clickToLaunch" checked> Enabled </div> <h2>Autoplay</h2> <button id="autoplayToggleButton">Start Autoplay</button> <div class="control-group"> <label for="autoplaySpeed">Launch Interval: <span id="autoplaySpeedValue">1000</span> ms</label> <input type="range" id="autoplaySpeed" min="100" max="2500" value="1000" step="50"> </div> <h2>Quick Actions</h2> <button id="randomizeButton">Randomize Firework</button> <button id="clearSkyButton">Clear Sky</button>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const canvas = document.getElementById('skyCanvas');
            const ctx = canvas.getContext('2d');
            // ... (all other UI element getters are the same)
            const fireworkTypeSelect = document.getElementById('fireworkType');
            const primaryColorInput = document.getElementById('primaryColor');
            const secondaryColorInput = document.getElementById('secondaryColor');
            const useSecondaryColorCheckbox = document.getElementById('useSecondaryColor');
            const specialEffectSelect = document.getElementById('specialEffect');
            const burstRadiusSlider = document.getElementById('burstRadius');
            const burstRadiusValueSpan = document.getElementById('burstRadiusValue');
            const trailLengthSlider = document.getElementById('trailLength');
            const trailLengthValueSpan = document.getElementById('trailLengthValue');
            const particleSizeScaleSlider = document.getElementById('particleSizeScale');
            const particleSizeScaleValueSpan = document.getElementById('particleSizeScaleValue');
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
            let globalHueOffset = 0;
            let particleSizeScale = parseFloat(particleSizeScaleSlider.value);

            // Reflection and Horizon settings
            const horizonHeightFactor = 0.75; // Horizon line is at 75% of canvas height from the top
            const reflectionSquishFactor = 0.5; // Reflection is 50% of original height
            const baseReflectionOpacity = 0.35; // Base opacity for reflections

            // --- Web Audio API Setup for Procedural Sounds ---
            // ... (Audio init and playProceduralSound same as previous version)
            let audioCtx = null; function initAudio() { try { audioCtx = new (window.AudioContext || window.webkitAudioContext)(); } catch (e) { console.error("Web Audio API is not supported.", e); soundEnabled = false; soundToggleButton.textContent = 'Off (Error)'; soundToggleButton.classList.add('muted'); soundToggleButton.disabled = true; } } initAudio(); function playProceduralSound(type, options = {}) { if (!soundEnabled || !audioCtx || audioCtx.state === 'suspended') { if (audioCtx && audioCtx.state === 'suspended') { audioCtx.resume().then(() => { if (audioCtx.state === 'running') playProceduralSound(type, options); }); } return; } if (audioCtx.state === 'suspended') audioCtx.resume(); const now = audioCtx.currentTime; const masterGain = audioCtx.createGain(); masterGain.connect(audioCtx.destination); masterGain.gain.setValueAtTime(options.volume || 0.5, now); switch (type) { case 'launch': const noiseDuration = 0.3 + Math.random() * 0.2; const noiseBuffer = audioCtx.createBuffer(1, audioCtx.sampleRate * noiseDuration, audioCtx.sampleRate); const noiseData = noiseBuffer.getChannelData(0); for (let i = 0; i < noiseData.length; i++) { noiseData[i] = Math.random() * 2 - 1; } const noiseSource = audioCtx.createBufferSource(); noiseSource.buffer = noiseBuffer; const filter = audioCtx.createBiquadFilter(); filter.type = 'lowpass'; filter.frequency.setValueAtTime(2000 + Math.random() * 1000, now); filter.frequency.exponentialRampToValueAtTime(200 + Math.random() * 100, now + noiseDuration * 0.8); filter.Q.setValueAtTime(5 + Math.random() * 5, now); const launchGain = audioCtx.createGain(); launchGain.gain.setValueAtTime(0, now); launchGain.gain.linearRampToValueAtTime(0.6, now + 0.02); launchGain.gain.exponentialRampToValueAtTime(0.001, now + noiseDuration); noiseSource.connect(filter); filter.connect(launchGain); launchGain.connect(masterGain); noiseSource.start(now); noiseSource.stop(now + noiseDuration); break; case 'explosion': const size = options.size || 'medium'; let baseGain = 0.7; let duration = 0.5; let lowFreq = 80; let noiseComponents = 3; if (size === 'small') { baseGain = 0.5; duration = 0.3; lowFreq = 100; noiseComponents = 2;} else if (size === 'large') { baseGain = 0.9; duration = 0.8; lowFreq = 60; noiseComponents = 4;} masterGain.gain.setValueAtTime(baseGain * (0.8 + Math.random()*0.4), now); const boomOsc = audioCtx.createOscillator(); boomOsc.type = 'sine'; boomOsc.frequency.setValueAtTime(lowFreq + (Math.random()-0.5)*20, now); boomOsc.frequency.exponentialRampToValueAtTime(lowFreq * 0.5, now + duration * 0.6); const boomGain = audioCtx.createGain(); boomGain.gain.setValueAtTime(0, now); boomGain.gain.linearRampToValueAtTime(1, now + 0.01); boomGain.gain.exponentialRampToValueAtTime(0.001, now + duration); boomOsc.connect(boomGain); boomGain.connect(masterGain); boomOsc.start(now); boomOsc.stop(now + duration); for (let i = 0; i < noiseComponents; i++) { const exNoiseDuration = duration * (0.3 + Math.random() * 0.4); const exNoiseBuffer = audioCtx.createBuffer(1, audioCtx.sampleRate * exNoiseDuration, audioCtx.sampleRate); const exNoiseData = exNoiseBuffer.getChannelData(0); for (let j = 0; j < exNoiseData.length; j++) { exNoiseData[j] = Math.random() * 2 - 1; } const exNoiseSource = audioCtx.createBufferSource(); exNoiseSource.buffer = exNoiseBuffer; const exFilter = audioCtx.createBiquadFilter(); exFilter.type = 'bandpass'; exFilter.frequency.setValueAtTime(500 + Math.random() * 1500, now); exFilter.Q.setValueAtTime(1 + Math.random() * 3, now); const exGain = audioCtx.createGain(); exGain.gain.setValueAtTime(0, now + i * 0.01); exGain.gain.linearRampToValueAtTime(0.8, now + i * 0.01 + 0.005); exGain.gain.exponentialRampToValueAtTime(0.001, now + i * 0.01 + exNoiseDuration); exNoiseSource.connect(exFilter); exFilter.connect(exGain); exGain.connect(masterGain); exNoiseSource.start(now + i * 0.01); exNoiseSource.stop(now + i * 0.01 + exNoiseDuration); } break; case 'crackle': const numCrackles = 5 + Math.floor(Math.random() * 5); const crackleVol = options.volume || 0.25; masterGain.gain.setValueAtTime(crackleVol, now); for (let i = 0; i < numCrackles; i++) { const crackleDelay = now + i * (0.02 + Math.random() * 0.03); const crackleDuration = 0.01 + Math.random() * 0.02; const osc = audioCtx.createOscillator(); osc.type = 'square'; osc.frequency.setValueAtTime(1000 + Math.random() * 2000, crackleDelay); const gainNode = audioCtx.createGain(); gainNode.gain.setValueAtTime(0, crackleDelay); gainNode.gain.linearRampToValueAtTime(0.7, crackleDelay + 0.001); gainNode.gain.exponentialRampToValueAtTime(0.001, crackleDelay + crackleDuration); osc.connect(gainNode); gainNode.connect(masterGain); osc.start(crackleDelay); osc.stop(crackleDelay + crackleDuration); } break; } }

            function resizeCanvas() { if (canvas.offsetWidth > 0 && canvas.offsetHeight > 0) { canvas.width = canvas.offsetWidth; canvas.height = canvas.offsetHeight; } }
            window.addEventListener('resize', resizeCanvas);
            resizeCanvas();

            burstRadiusSlider.oninput = () => burstRadiusValueSpan.textContent = burstRadiusSlider.value;
            trailLengthSlider.oninput = () => trailLengthValueSpan.textContent = trailLengthSlider.value;
            particleSizeScaleSlider.oninput = () => { particleSizeScale = parseFloat(particleSizeScaleSlider.value); particleSizeScaleValueSpan.textContent = particleSizeScale.toFixed(1); };
            particleSizeScaleValueSpan.textContent = parseFloat(particleSizeScaleSlider.value).toFixed(1);
            
            function hslToHex(h, s, l) { /* ... */ l /= 100; const a = s * Math.min(l, 1 - l) / 100; const f = n => { const k = (n + h / 30) % 12; const color = l - a * Math.max(Math.min(k - 3, 9 - k, 1), -1); return Math.round(255 * color).toString(16).padStart(2, '0'); }; return `#${f(0)}${f(8)}${f(4)}`; }
            function hexToHsl(hex) { /* ... */ let r = 0, g = 0, b = 0; if (hex.length == 4) { r = "0x" + hex[1] + hex[1]; g = "0x" + hex[2] + hex[2]; b = "0x" + hex[3] + hex[3]; } else if (hex.length == 7) { r = "0x" + hex[1] + hex[2]; g = "0x" + hex[3] + hex[4]; b = "0x" + hex[5] + hex[6]; } r /= 255; g /= 255; b /= 255; let cmin = Math.min(r,g,b), cmax = Math.max(r,g,b), delta = cmax - cmin, h = 0, s = 0, l = 0; if (delta == 0) h = 0; else if (cmax == r) h = ((g - b) / delta) % 6; else if (cmax == g) h = (b - r) / delta + 2; else h = (r - g) / delta + 4; h = Math.round(h * 60); if (h < 0) h += 360; l = (cmax + cmin) / 2; s = delta == 0 ? 0 : delta / (1 - Math.abs(2 * l - 1)); s = +(s * 100).toFixed(1); l = +(l * 100).toFixed(1); return [h, s, l]; }

            soundToggleButton.addEventListener('click', () => { soundEnabled = !soundEnabled; soundToggleButton.textContent = soundEnabled ? 'On' : 'Off'; soundToggleButton.classList.toggle('muted', !soundEnabled); if (soundEnabled && audioCtx && audioCtx.state === 'suspended') { audioCtx.resume().then(() => { console.log("AudioContext resumed by toggle."); }); } });

            class Particle {
                constructor(x, y, vx, vy, color, size, lifetime, isTrail = false, effect = 'none', parentFirework = null) { this.x = x; this.y = y; this.vx = vx; this.vy = vy; this.baseColor = color; this.color = color; this.baseSize = size; this.lifetime = lifetime; this.initialLifetime = lifetime; this.isTrail = isTrail; this.effect = effect; this.parentFirework = parentFirework; this.strobeCounter = 0; this.canCrackle = true; this.canMiniBurst = true; this.shimmerCounter = Math.random() * 10; this.spinAngle = 0; this.spinRadius = Math.random() * 5 + 2; this.fountainApexY = y - (Math.random() * 50 + 20); }
                update() { /* ... (Particle update logic same as before) ... */ this.x += this.vx; this.y += this.vy; if (this.effect === 'fountain') { if (this.y > this.fountainApexY) { this.vy += gravity * 0.5; } else { this.vy += gravity * 1.5;} } else { this.vy += gravity; } const currentAirResistance = this.isTrail ? trailAirResistance : airResistance; this.vx *= currentAirResistance; this.vy *= currentAirResistance; this.lifetime--; this.shimmerCounter++; if (this.effect === 'crackle' && this.canCrackle && this.lifetime > 0 && this.lifetime < this.initialLifetime * 0.6 && Math.random() < 0.15) { for (let i = 0; i < 3 + Math.random()*2; i++) { particles.push(new Particle(this.x, this.y, (Math.random() - 0.5) * 3, (Math.random() - 0.5) * 3, '#FFFFFF', Math.random() * 1.8 + 0.8, Math.random() * 18 + 12, false, 'none', this.parentFirework)); } playProceduralSound('crackle'); this.canCrackle = false; } if (this.effect === 'comet' && this.lifetime > this.initialLifetime * 0.15 && Math.random() < 0.35) { const tailColor = hslToHex((hexToHsl(this.color)[0] + Math.random()*30 -15 + 360)%360, 100, 60 + Math.random()*10); particles.push(new Particle(this.x, this.y, this.vx * 0.05, this.vy * 0.05, tailColor, Math.max(0.5, this.baseSize * (0.3 + Math.random() * 0.3)), Math.max(8, this.initialLifetime * (0.25 + Math.random() * 0.25)), true, 'none', this.parentFirework)); } if (this.effect === 'multiBurst' && this.canMiniBurst && this.lifetime < this.initialLifetime * (0.3 + Math.random()*0.3) && this.lifetime > 20) { this.canMiniBurst = false; this.lifetime = Math.min(this.lifetime, 15); const numMini = 7 + Math.floor(Math.random() * 7); for (let j = 0; j < numMini; j++) { const angle = Math.random() * Math.PI * 2; const miniColor = hslToHex((hexToHsl(this.baseColor)[0] + Math.random()*60-30 + 360)%360, 100, 65 + Math.random()*10); particles.push(new Particle(this.x, this.y, Math.cos(angle) * (1.2 + Math.random()*0.8), Math.sin(angle) * (1.2 + Math.random()*0.8), miniColor, this.baseSize * (0.25 + Math.random() * 0.35), 25 + Math.random() * 25, false, Math.random() < 0.2 ? 'glitter' : 'none', this.parentFirework)); } playProceduralSound('explosion', { size: 'small', volume: 0.3 }); } if (this.effect === 'spinningWheel') { this.spinAngle += 0.1 + Math.random() * 0.1; this.vx += Math.cos(this.spinAngle) * this.spinRadius * 0.05; this.vy += Math.sin(this.spinAngle) * this.spinRadius * 0.05; this.vx *= 0.97; this.vy *= 0.97; this.spinRadius *= 0.99; } if (this.effect === 'colorShift') { const progress = 1 - (this.lifetime / this.initialLifetime); const currentHue = (hexToHsl(this.baseColor)[0] + progress * 180 + 360) % 360; this.color = hslToHex(currentHue, 100, 60 + Math.sin(progress * Math.PI) * 15); } }
                
                draw(isReflectionPass = false, horizonY = 0) {
                    let alpha = Math.max(0, this.lifetime / this.initialLifetime);
                    if (this.shimmerCounter % 4 < 2 && !this.isTrail) { alpha *= (0.75 + Math.random() * 0.25); }
                    
                    let drawX = this.x;
                    let drawY = this.y;
                    let finalSize = Math.max(1, this.baseSize * particleSizeScale);

                    if (isReflectionPass) {
                        if (this.y > horizonY) return; // Only reflect things above the horizon

                        // Calculate distance from horizon for fading
                        const reflectionDepth = canvas.height * (1 - horizonHeightFactor); // Total height of reflection area
                        const distanceFromHorizonInReflection = (drawY - horizonY); // This will be positive
                        // Fade more the further down it is in the reflection area
                        const fadeFactor = Math.max(0, 1 - (distanceFromHorizonInReflection / (reflectionDepth * 1.5) ) ); 

                        alpha *= baseReflectionOpacity * fadeFactor;
                        if (alpha < 0.01) return; // Don't draw if too faint

                        drawY = horizonY + (horizonY - this.y) * reflectionSquishFactor;
                        finalSize *= reflectionSquishFactor * 0.7; // Reflections are smaller
                    }
                    
                    if (finalSize < 0.5 && isReflectionPass) return;

                    ctx.globalAlpha = alpha;
                    ctx.fillStyle = this.color;
                    if (this.effect === 'strobe' && this.lifetime > 0 && !isReflectionPass) { 
                        this.strobeCounter++; if (this.strobeCounter % 7 < 3) ctx.fillStyle = 'rgba(255,255,255,'+ (0.7 + Math.random()*0.3) +')'; 
                    }
                    
                    ctx.beginPath(); 
                    ctx.arc(drawX, drawY, Math.max(0.5, finalSize), 0, Math.PI * 2); 
                    ctx.fill();
                    ctx.globalAlpha = 1; 
                }
            }

            class Firework { // ... (Constructor and Update are the same)
                constructor(startX, startY, targetX, targetY, type, primaryColor, secondaryColor, useSecondary, burstRadius, trailLength, specialEffect) { this.x = startX; this.y = startY; this.targetX = targetX; this.targetY = targetY; const angle = Math.atan2(targetY - startY, targetX - startX); const speed = 9 + Math.random() * 5; this.vx = Math.cos(angle) * speed; this.vy = Math.sin(angle) * speed; this.type = type; this.baseHue1 = hexToHsl(primaryColor)[0]; this.baseHue2 = useSecondary ? hexToHsl(secondaryColor)[0] : (this.baseHue1 + 30 + Math.random()*60)%360; this.burstRadius = parseInt(burstRadius); this.trailLength = parseInt(trailLength); this.particleEffect = specialEffect; this.exploded = false; this.particles = []; this.lifetime = 280; this.hueCycle = Math.random() * 360;}
                update() { if (!this.exploded) { this.x += this.vx; this.y += this.vy; this.vy += gravity * 0.55; if (Math.random() < 0.65) { const trailHue = (this.hueCycle + Math.random()*60-30 + 360)%360; this.particles.push(new Particle(this.x, this.y, -this.vx*0.05, -this.vy*0.05, hslToHex(trailHue, 100, 70), Math.random() * 2 + 0.8, this.trailLength * (0.6 + Math.random()*0.4), true, 'none', this)); } this.particles = this.particles.filter(p => p.lifetime > 0); this.particles.forEach(p => p.update()); if (this.y <= this.targetY || this.vy >= -0.1) this.explode(); } this.lifetime--;}
                explode() { /* ... (Explode logic for particles is the same) ... */ this.exploded = true; let explosionSizeSound = 'medium'; if (this.burstRadius > 120) explosionSizeSound = 'large'; else if (this.burstRadius < 50) explosionSizeSound = 'small'; playProceduralSound('explosion', { size: explosionSizeSound }); let numParticles = 50 + Math.floor(this.burstRadius * 1.1); if (this.type === 'rocket') numParticles = 20 + Math.floor(this.burstRadius / 1.5); if (this.type === 'chrysanthemum') numParticles = 90 + Math.floor(this.burstRadius * 1.5); if (this.type === 'willow' || this.type === 'palm') numParticles = Math.max(30, numParticles * 0.7); for (let i = 0; i < numParticles; i++) { const angle = Math.random() * Math.PI * 2; let speed = Math.random() * (this.burstRadius / 12 + 1.5); let pLifetime = 50 + Math.random() * 50 + (this.burstRadius / 1.8); let pBaseSize = Math.random() * 1.8 + 0.8 + (this.burstRadius / 60); let particleHue; const hueRand = Math.random(); if (hueRand < 0.45) particleHue = (this.baseHue1 + Math.random() * 60 - 30 + 360)%360; else if (hueRand < 0.9) particleHue = (this.baseHue2 + Math.random() * 60 - 30 + 360)%360; else particleHue = (this.hueCycle + i * (360/numParticles) + Math.random()*20-10 + 360 + globalHueOffset)%360; const pColor = hslToHex(particleHue, 100, 60 + Math.random()*20); let effectToApply = this.particleEffect; if (this.type === 'strobe') effectToApply = 'strobe'; if (this.type === 'crackle') effectToApply = 'crackle'; let pVx = Math.cos(angle) * speed; let pVy = Math.sin(angle) * speed; switch (this.type) { case 'willow': pVy += Math.random() * 1.0 + (gravity * 18); pLifetime *= 2.0; pBaseSize *= 0.65; speed *= (0.5 + Math.random()*0.4); pVx = Math.cos(angle) * speed; pVy = Math.sin(angle) * speed + (gravity * 12); break; case 'palm': const palmBranches = 5 + Math.floor(this.burstRadius / 25); if (i < palmBranches) { const bAngle = (i / palmBranches) * Math.PI * 2 + (Math.random() - 0.5) * 0.25; pVx = Math.cos(bAngle) * speed * 1.4; pVy = Math.sin(bAngle) * speed * 1.4; pLifetime *= 1.4; pBaseSize *= 1.7; effectToApply = Math.random() < 0.35 ? 'comet' : effectToApply; } else { pVx = Math.cos(angle) * speed * 0.35; pVy = Math.sin(angle) * speed * 0.35; pLifetime *= 0.55; pBaseSize *= 0.75; } break; case 'crossette': pVx *= (0.7 + Math.random() * 1.1); pVy *= (0.7 + Math.random() * 1.1); if(Math.random() < 0.35) effectToApply = 'multiBurst'; else if(Math.random() < 0.2) effectToApply = 'crackle'; break; case 'rocket': speed *= 0.55; pVx = Math.cos(angle) * speed; pVy = Math.sin(angle) * speed; pLifetime *= 0.35; pBaseSize *= 0.85; break; } if (effectToApply === 'glitter') { pLifetime *= 0.55; pBaseSize *= 0.65; } particles.push(new Particle(this.x, this.y, pVx, pVy, pColor, pBaseSize, pLifetime, false, effectToApply, this)); } globalHueOffset = (globalHueOffset + 1.5) % 360; }
                
                draw(isReflectionPass = false, horizonY = 0) {
                    this.particles.forEach(p => p.draw(isReflectionPass, horizonY)); // Draw trail particles
                    if (!this.exploded) {
                        let drawX = this.x;
                        let drawY = this.y;
                        let rocketOpacity = 1;
                        let rocketSize = 2.8;

                        if (isReflectionPass) {
                             if (this.y > horizonY) return; // Only reflect rocket if above horizon
                             rocketOpacity = baseReflectionOpacity * 0.7;
                             drawY = horizonY + (horizonY - this.y) * reflectionSquishFactor;
                             rocketSize *= reflectionSquishFactor * 0.7;
                        }
                        
                        ctx.globalAlpha = rocketOpacity;
                        ctx.fillStyle = '#F0F0F0'; 
                        ctx.beginPath();
                        ctx.arc(drawX, drawY, Math.max(0.5, rocketSize) , 0, Math.PI * 2); 
                        ctx.fill();
                        ctx.globalAlpha = 1;
                    }
                }
            }

            function launchConfiguredFirework(targetX, targetY) { /* ... same ... */ playProceduralSound('launch', { volume: 0.4 }); const currentType = fireworkTypeSelect.value; const currentPrimaryColor = primaryColorInput.value; const currentSecondaryColor = secondaryColorInput.value; const currentUseSecondary = useSecondaryColorCheckbox.checked; const currentBurstRadius = burstRadiusSlider.value; const currentTrailLength = trailLengthSlider.value; const currentSpecialEffect = specialEffectSelect.value; const launchX = targetX === undefined ? (canvas.width / 2 + (Math.random() - 0.5) * (canvas.width * 0.7)) : targetX; const launchTargetY = targetY === undefined ? (canvas.height * (0.05 + Math.random() * 0.35)) : targetY; fireworks.push(new Firework(launchX, canvas.height, launchX, launchTargetY, currentType, currentPrimaryColor, currentSecondaryColor, currentUseSecondary, currentBurstRadius, currentTrailLength, currentSpecialEffect)); }

            // ... (launchButton, canvas click, randomizeButton, clearSkyButton, autoplay logic all remain the same)
            launchButton.addEventListener('click', () => launchConfiguredFirework()); canvas.addEventListener('click', (e) => { if (clickToLaunchCheckbox.checked) { const rect = canvas.getBoundingClientRect(); launchConfiguredFirework(e.clientX - rect.left, Math.max(canvas.height * 0.05, Math.min(e.clientY - rect.top, canvas.height * 0.9))); } }); randomizeButton.addEventListener('click', () => { const types = Array.from(fireworkTypeSelect.options).map(opt => opt.value); fireworkTypeSelect.value = types[Math.floor(Math.random() * types.length)]; const h1 = Math.random() * 360; primaryColorInput.value = hslToHex(h1, 100, 65 + Math.random()*15); const h2 = Math.random() * 360; secondaryColorInput.value = hslToHex(h2, 100, 65 + Math.random()*15); useSecondaryColorCheckbox.checked = Math.random() < 0.7; const effects = Array.from(specialEffectSelect.options).map(opt => opt.value); specialEffectSelect.value = effects[Math.floor(Math.random() * effects.length)]; burstRadiusSlider.value = Math.random() * 150 + 30; burstRadiusValueSpan.textContent = burstRadiusSlider.value; trailLengthSlider.value = Math.random() * 90 + 30; trailLengthValueSpan.textContent = trailLengthSlider.value; launchConfiguredFirework(); }); clearSkyButton.addEventListener('click', () => { fireworks = []; particles = []; }); autoplaySpeedSlider.addEventListener('input', () => { currentAutoplayInterval = parseInt(autoplaySpeedSlider.value); autoplaySpeedValueSpan.textContent = `${currentAutoplayInterval}`; if (isAutoplaying) { stopAutoplay(); startAutoplay(); } }); autoplaySpeedValueSpan.textContent = `${autoplaySpeedSlider.value}`; autoplayToggleButton.addEventListener('click', () => { isAutoplaying = !isAutoplaying; if (isAutoplaying) { autoplayToggleButton.textContent = 'Stop Autoplay'; autoplayToggleButton.classList.add('active'); startAutoplay(); } else { autoplayToggleButton.textContent = 'Start Autoplay'; autoplayToggleButton.classList.remove('active'); stopAutoplay(); } }); function startAutoplay() { if (autoplayIntervalId) clearInterval(autoplayIntervalId); autoplayIntervalId = setInterval(() => { const types = Array.from(fireworkTypeSelect.options).map(opt => opt.value); fireworkTypeSelect.value = types[Math.floor(Math.random() * types.length)]; primaryColorInput.value = hslToHex(Math.random() * 360, 100, 60 + Math.random()*25); secondaryColorInput.value = hslToHex(Math.random() * 360, 100, 60 + Math.random()*25); useSecondaryColorCheckbox.checked = Math.random() < 0.75; const effects = Array.from(specialEffectSelect.options).map(opt => opt.value); specialEffectSelect.value = effects[Math.floor(Math.random() * effects.length)]; burstRadiusSlider.value = Math.random() * 120 + 50; burstRadiusValueSpan.textContent = burstRadiusSlider.value; trailLengthSlider.value = Math.random() * 80 + 40; trailLengthValueSpan.textContent = trailLengthSlider.value; const autoTargetX = canvas.width * (0.02 + Math.random() * 0.96); const autoTargetY = canvas.height * (0.02 + Math.random() * 0.4); launchConfiguredFirework(autoTargetX, autoTargetY); }, currentAutoplayInterval); } function stopAutoplay() { if (autoplayIntervalId) { clearInterval(autoplayIntervalId); autoplayIntervalId = null; } }


            function animate() {
                const horizonY = canvas.height * horizonHeightFactor;
                const reflectionAreaHeight = canvas.height * (1 - horizonHeightFactor);

                // 1. Draw Background (Sky & Water Base)
                // Sky - with trails for fireworks
                ctx.fillStyle = 'rgba(0, 0, 10, 0.12)'; // Sky clear, slightly more persistence
                ctx.fillRect(0, 0, canvas.width, horizonY);

                // Sunset Glow (above horizon)
                const sunsetGradient = ctx.createLinearGradient(0, horizonY - 100, 0, horizonY); // Glow extends 100px up
                sunsetGradient.addColorStop(0, 'rgba(20, 10, 30, 0)'); // Fades to sky
                sunsetGradient.addColorStop(0.6, 'rgba(255, 100, 50, 0.08)'); // Orange
                sunsetGradient.addColorStop(1, 'rgba(255, 150, 80, 0.15)');   // Brighter orange/yellow at horizon
                ctx.fillStyle = sunsetGradient;
                ctx.fillRect(0, horizonY - 100, canvas.width, 100);
                
                // Water Base (below horizon) - darker for reflections to pop
                ctx.fillStyle = 'rgba(5, 5, 15, 0.95)'; // Dark, slightly bluish water
                ctx.fillRect(0, horizonY, canvas.width, reflectionAreaHeight);


                // 2. Update all fireworks and particles (physics only)
                fireworks.forEach(fw => fw.update());
                particles.forEach(p => p.update());

                // 3. Draw Sky Objects (Main Pass - above horizon)
                fireworks.forEach(fw => fw.draw(false, horizonY));
                particles.forEach(p => p.draw(false, horizonY));

                // 4. Draw Reflections (Reflection Pass - in water area)
                // Optional: Clip to the reflection area to be absolutely sure nothing draws outside it
                // ctx.save();
                // ctx.beginPath();
                // ctx.rect(0, horizonY, canvas.width, reflectionAreaHeight);
                // ctx.clip();

                fireworks.forEach(fw => fw.draw(true, horizonY));
                particles.forEach(p => p.draw(true, horizonY));
                
                // ctx.restore(); // if ctx.save() and ctx.clip() were used


                // 5. Clean up
                fireworks = fireworks.filter(fw => (!fw.exploded && fw.lifetime > 0) || (fw.exploded && fw.lifetime > -350) ); 
                particles = particles.filter(p => p.lifetime > 0);
                
                requestAnimationFrame(animate);
            }
            animate();
        });
    </script>
</body>
</html>