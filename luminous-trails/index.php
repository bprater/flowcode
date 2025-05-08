<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurable Luminous Trails</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        html, body {
            height: 100%;
            overflow: hidden;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #0a0a10;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            color: #eee;
        }
        .app-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
            border-radius: 15px;
            background: linear-gradient(60deg, #101020, #181018, #101810);
            background-size: 300% 300%;
            animation: gradientBG 30s ease infinite;
            box-shadow: 0 0 40px rgba(75, 0, 130, 0.6);
            position: relative; /* For DOM particles */
        }
        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        h1 {
            font-weight: 200; font-size: 2em; margin-bottom: 15px;
            color: #e0e0ff; text-shadow: 0 0 8px #c0c0ff;
        }
        #drawing-canvas {
            width: 80vw; height: 70vh;
            max-width: 900px; max-height: 650px;
            background-color: rgba(0,0,0,0.2);
            cursor: crosshair; border-radius: 10px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            touch-action: none;
        }
        #clear-btn {
            margin-top: 15px; padding: 12px 25px;
            background-color: #4B0082; color: white; border: none;
            border-radius: 8px; cursor: pointer; font-size: 1em;
            transition: background-color 0.2s, transform 0.1s;
            box-shadow: 0 0 15px rgba(75, 0, 130, 0.8);
        }
        #clear-btn:hover { background-color: #5D1092; }
        #clear-btn:active { transform: scale(0.95); }

        .dom-particle {
            position: absolute; border-radius: 50%;
            pointer-events: none; z-index: 10; opacity: 0;
        }

        /* Configuration Panel */
        #config-panel-toggle {
            position: fixed;
            top: 10px;
            right: 10px;
            padding: 8px 12px;
            background-color: rgba(75, 0, 130, 0.7);
            color: white;
            border: 1px solid rgba(255,255,255,0.3);
            border-radius: 5px;
            cursor: pointer;
            z-index: 1001;
            font-size: 0.9em;
        }
        #config-panel {
            position: fixed;
            top: 50px;
            right: 10px;
            width: 280px;
            max-height: calc(100vh - 70px);
            overflow-y: auto;
            background-color: rgba(30, 30, 50, 0.9);
            border: 1px solid rgba(128, 128, 200, 0.5);
            border-radius: 8px;
            padding: 15px;
            z-index: 1000;
            box-shadow: -5px 5px 15px rgba(0,0,0,0.3);
            display: none; /* Hidden by default */
            color: #e0e0ff;
        }
        #config-panel.visible {
            display: block;
        }
        #config-panel h3 {
            margin-top: 10px;
            margin-bottom: 8px;
            font-weight: 400;
            border-bottom: 1px solid rgba(255,255,255,0.2);
            padding-bottom: 5px;
        }
        #config-panel h3:first-child {
            margin-top: 0;
        }
        #config-panel label {
            display: block;
            margin-bottom: 3px;
            font-size: 0.9em;
        }
        #config-panel input[type="range"],
        #config-panel input[type="number"] {
            width: calc(100% - 50px);
            margin-bottom: 10px;
            vertical-align: middle;
        }
        #config-panel .value-display {
            display: inline-block;
            width: 40px;
            text-align: right;
            font-size: 0.9em;
            margin-left: 5px;
            vertical-align: middle;
        }
        #config-panel .input-group {
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <button id="config-panel-toggle">Settings</button>
    <div id="config-panel">
        <h3>Trail Settings</h3>
        <div class="input-group">
            <label for="trailMaxLife">Max Lifespan (ms):</label>
            <input type="range" id="trailMaxLife" min="1000" max="20000" step="500">
            <span class="value-display" id="trailMaxLifeValue"></span>
        </div>
        <div class="input-group">
            <label for="trailGlobalMinLineWidth">New Line Min Width (px):</label>
            <input type="range" id="trailGlobalMinLineWidth" min="1" max="10" step="0.5">
            <span class="value-display" id="trailGlobalMinLineWidthValue"></span>
        </div>
        <div class="input-group">
            <label for="trailGlobalMaxWidth">New Line Max Width (px):</label>
            <input type="range" id="trailGlobalMaxWidth" min="5" max="30" step="0.5">
            <span class="value-display" id="trailGlobalMaxWidthValue"></span>
        </div>
         <div class="input-group">
            <label for="trailHuePulseAmount">Hue Pulse Amount (°):</label>
            <input type="range" id="trailHuePulseAmount" min="0" max="60" step="1">
            <span class="value-display" id="trailHuePulseAmountValue"></span>
        </div>
        <div class="input-group">
            <label for="trailSparkleDensity">Sparkle Density (0-1):</label>
            <input type="range" id="trailSparkleDensity" min="0" max="1" step="0.05">
            <span class="value-display" id="trailSparkleDensityValue"></span>
        </div>

        <h3>DOM Particle Settings</h3>
        <div class="input-group">
            <label for="domParticleCountFactor">Count Factor:</label>
            <input type="range" id="domParticleCountFactor" min="0.1" max="3" step="0.1">
            <span class="value-display" id="domParticleCountFactorValue"></span>
        </div>
        <div class="input-group">
            <label for="domParticleDurationFactor">Duration Factor:</label>
            <input type="range" id="domParticleDurationFactor" min="0.2" max="2" step="0.1">
            <span class="value-display" id="domParticleDurationFactorValue"></span>
        </div>
    </div>

    <div class="app-container">
        <h1>Configurable Luminous Trails</h1>
        <canvas id="drawing-canvas"></canvas>
        <button id="clear-btn">Clear Canvas</button>
    </div>

    <script>
        const canvas = document.getElementById('drawing-canvas');
        const ctx = canvas.getContext('2d');
        const clearButton = document.getElementById('clear-btn');
        const appContainer = document.querySelector('.app-container');
        const configPanel = document.getElementById('config-panel');
        const configPanelToggle = document.getElementById('config-panel-toggle');

        let isDrawing = false;
        let activeTrails = [];
        let currentTrail = null;

        // --- Configuration Object ---
        const config = {
            trailMaxLife: 7000,
            trailGlobalMinLineWidth: 3,
            trailGlobalMaxWidth: 15,
            trailLineWidthOscillationSpeed: 0.1,
            trailHuePulseAmount: 20, // degrees
            trailSparkleDensity: 0.3, // 0 to 1, influences chance/count
            
            // DOM Particle Base Values (multiplied by factors)
            domParticleBaseCounts: { burst: 15, poof: 8, explosion: 20 },
            domParticleBaseDurations: { burst: 800, poof: 600, explosion: 1200 },
            domParticleCountFactor: 1.0,
            domParticleDurationFactor: 1.0,
        };
        
        // Global properties for NEW lines being drawn (these cycle/oscillate)
        let globalHue = 0;
        let globalLineWidth = (config.trailGlobalMinLineWidth + config.trailGlobalMaxWidth) / 2;
        let globalLineWidthDirection = 1;


        // --- Config Panel UI Setup ---
        const uiElements = {
            trailMaxLife: document.getElementById('trailMaxLife'),
            trailMaxLifeValue: document.getElementById('trailMaxLifeValue'),
            trailGlobalMinLineWidth: document.getElementById('trailGlobalMinLineWidth'),
            trailGlobalMinLineWidthValue: document.getElementById('trailGlobalMinLineWidthValue'),
            trailGlobalMaxWidth: document.getElementById('trailGlobalMaxWidth'),
            trailGlobalMaxWidthValue: document.getElementById('trailGlobalMaxWidthValue'),
            trailHuePulseAmount: document.getElementById('trailHuePulseAmount'),
            trailHuePulseAmountValue: document.getElementById('trailHuePulseAmountValue'),
            trailSparkleDensity: document.getElementById('trailSparkleDensity'),
            trailSparkleDensityValue: document.getElementById('trailSparkleDensityValue'),
            domParticleCountFactor: document.getElementById('domParticleCountFactor'),
            domParticleCountFactorValue: document.getElementById('domParticleCountFactorValue'),
            domParticleDurationFactor: document.getElementById('domParticleDurationFactor'),
            domParticleDurationFactorValue: document.getElementById('domParticleDurationFactorValue'),
        };

        function updateConfigValue(key, value, displayElement) {
            config[key] = parseFloat(value);
            if (displayElement) displayElement.textContent = parseFloat(value).toFixed(key.includes('Factor') || key.includes('Density') ? 2 : 0);
            // Special handling for min/max width to ensure min <= max
            if (key === 'trailGlobalMinLineWidth' && config.trailGlobalMinLineWidth > config.trailGlobalMaxWidth) {
                config.trailGlobalMaxWidth = config.trailGlobalMinLineWidth;
                uiElements.trailGlobalMaxWidth.value = config.trailGlobalMaxWidth;
                uiElements.trailGlobalMaxWidthValue.textContent = config.trailGlobalMaxWidth.toFixed(1);
            }
            if (key === 'trailGlobalMaxWidth' && config.trailGlobalMaxWidth < config.trailGlobalMinLineWidth) {
                config.trailGlobalMinLineWidth = config.trailGlobalMaxWidth;
                uiElements.trailGlobalMinLineWidth.value = config.trailGlobalMinLineWidth;
                uiElements.trailGlobalMinLineWidthValue.textContent = config.trailGlobalMinLineWidth.toFixed(1);
            }
        }

        function initializeConfigPanel() {
            for (const key in uiElements) {
                if (uiElements[key] && uiElements[key].type === 'range') {
                    const configKey = key.replace('Value', ''); // trailMaxLifeValue -> trailMaxLife
                    uiElements[key].value = config[configKey];
                    if(uiElements[configKey + 'Value']) { // Check if corresponding value span exists
                       uiElements[configKey + 'Value'].textContent = parseFloat(config[configKey]).toFixed(configKey.includes('Factor') || configKey.includes('Density') ? 2 : 0);
                    }
                    uiElements[key].addEventListener('input', (e) => {
                        updateConfigValue(configKey, e.target.value, uiElements[configKey + 'Value']);
                    });
                }
            }
            configPanelToggle.addEventListener('click', () => {
                configPanel.classList.toggle('visible');
                configPanelToggle.textContent = configPanel.classList.contains('visible') ? 'Hide Settings' : 'Settings';
            });
            // Prevent drawing on canvas when interacting with panel
            configPanel.addEventListener('mousedown', (e) => e.stopPropagation());
            configPanel.addEventListener('touchstart', (e) => e.stopPropagation());
        }


        // --- Canvas Setup ---
        function resizeCanvas() { /* ... same as before ... */ 
            const dpr = window.devicePixelRatio || 1;
            const rect = canvas.getBoundingClientRect();
            canvas.width = rect.width * dpr;
            canvas.height = rect.height * dpr;
            ctx.scale(dpr, dpr);
        }
        window.addEventListener('resize', resizeCanvas);
        

        // --- DOM Particle Creation ---
        function createDOMParticle(x, y, options = {}) {
            const type = options.type || 'burst';
            const baseCount = config.domParticleBaseCounts[type] || 10;
            const baseDuration = config.domParticleBaseDurations[type] || 800;

            const { 
                count = Math.round(baseCount * config.domParticleCountFactor), 
                baseSize = 8, 
                sizeVariance = 4, 
                color = `hsl(${globalHue}, 100%, 70%)`, 
                duration = baseDuration * config.domParticleDurationFactor, 
                spread = 50
            } = options;

            for (let i = 0; i < count; i++) {
                const particle = document.createElement('div');
                particle.classList.add('dom-particle');
                const size = baseSize + Math.random() * sizeVariance - sizeVariance / 2;
                particle.style.width = `${size}px`; particle.style.height = `${size}px`;
                particle.style.backgroundColor = color;
                const appRect = appContainer.getBoundingClientRect();
                const canvasRect = canvas.getBoundingClientRect();
                particle.style.left = `${(x + canvasRect.left - appRect.left)}px`;
                particle.style.top = `${(y + canvasRect.top - appRect.top)}px`;
                let translateX, translateY;
                if (type === 'explosion') { const angle = Math.random() * Math.PI * 2; const dist = Math.random() * spread * 1.5 + spread * 0.5; translateX = Math.cos(angle) * dist; translateY = Math.sin(angle) * dist; }
                else if (type === 'poof') { const angle = Math.random() * Math.PI * 2; const dist = Math.random() * (spread / 2); translateX = Math.cos(angle) * dist; translateY = Math.sin(angle) * dist - (spread / 3); }
                else { const angle = Math.random() * Math.PI * 2; const dist = Math.random() * spread; translateX = Math.cos(angle) * dist; translateY = Math.sin(angle) * dist; }
                particle.animate([
                    { transform: `translate(-50%, -50%) scale(0.5)`, opacity: 1 },
                    { transform: `translate(calc(-50% + ${translateX}px), calc(-50% + ${translateY}px)) scale(1)`, opacity: 0.8, offset: 0.3 },
                    { transform: `translate(calc(-50% + ${translateX * 1.2}px), calc(-50% + ${translateY * 1.2}px)) scale(0)`, opacity: 0 }
                ], { duration: duration + Math.random() * (duration / 2), easing: 'cubic-bezier(0.25, 1, 0.5, 1)', fill: 'forwards' });
                appContainer.appendChild(particle);
                setTimeout(() => particle.remove(), duration + (duration / 2) + 100);
            }
        }

        // --- Trail Drawing and Animation Loop ---
        function animateTrails() {
            const dpr = window.devicePixelRatio || 1;
            ctx.clearRect(0, 0, canvas.width / dpr, canvas.height / dpr);
            const now = Date.now();

            globalHue = (globalHue + 0.5) % 360;
            globalLineWidth += globalLineWidthDirection * config.trailLineWidthOscillationSpeed;
            if (globalLineWidth > config.trailGlobalMaxWidth || globalLineWidth < config.trailGlobalMinLineWidth) {
                globalLineWidthDirection *= -1;
                globalLineWidth = Math.max(config.trailGlobalMinLineWidth, Math.min(config.trailGlobalMaxWidth, globalLineWidth));
            }

            for (let i = activeTrails.length - 1; i >= 0; i--) {
                const trail = activeTrails[i];
                const age = now - trail.creationTime;

                if (age > trail.maxLife) {
                    activeTrails.splice(i, 1); continue;
                }

                const lifeRatio = age / trail.maxLife;
                const opacity = Math.max(0, 1 - lifeRatio * lifeRatio);

                const pulseTimeHue = now * 0.0005;
                const currentHue = (trail.originalHue + Math.sin(pulseTimeHue + i * 0.5) * config.trailHuePulseAmount + 360) % 360;
                
                const pulseTimeLightness = now * 0.002;
                const currentLightness = 60 + Math.sin(pulseTimeLightness + i * 0.3) * 15;

                const pulseTimeWidth = now * 0.0015;
                const currentLineWidth = Math.max(1, trail.initialLineWidth + Math.sin(pulseTimeWidth + i * 0.2) * (trail.initialLineWidth * 0.3));

                ctx.globalAlpha = opacity;
                ctx.lineCap = 'round'; ctx.lineJoin = 'round';
                
                ctx.strokeStyle = `hsl(${currentHue}, 100%, ${currentLightness}%)`;
                ctx.lineWidth = currentLineWidth;
                ctx.shadowBlur = currentLineWidth * 1.5;
                ctx.shadowColor = `hsla(${currentHue}, 100%, ${currentLightness - 10}%, 0.7)`;

                if (trail.points.length > 1) {
                    ctx.beginPath();
                    ctx.moveTo(trail.points[0].x, trail.points[0].y);
                    for (let j = 1; j < trail.points.length; j++) {
                        ctx.lineTo(trail.points[j].x, trail.points[j].y);
                    }
                    ctx.stroke();
                }
                
                const baseSparkCount = Math.floor(trail.points.length / 30) + 1; // Base density
                const numSparks = Math.floor(baseSparkCount * (config.trailSparkleDensity * 2 + 0.5)); // Scale with config

                if (opacity > 0.1 && Math.random() < (0.2 + config.trailSparkleDensity * 0.5)) { // Chance also affected by density
                    for (let k = 0; k < numSparks; k++) {
                        if (trail.points.length === 0) continue;
                        const pointIndex = Math.floor(Math.random() * trail.points.length);
                        const sparkPoint = trail.points[pointIndex];
                        
                        const sparkSize = Math.random() * (currentLineWidth / 3) + 1;
                        const sparkLightness = 80 + Math.random() * 15;
                        ctx.fillStyle = `hsla(${currentHue}, 100%, ${sparkLightness}%, ${0.5 + Math.random() * 0.5})`;
                        
                        const oldShadowBlur = ctx.shadowBlur; const oldShadowColor = ctx.shadowColor;
                        ctx.shadowBlur = 0;
                        ctx.beginPath();
                        ctx.arc(sparkPoint.x + (Math.random() - 0.5) * 5, sparkPoint.y + (Math.random() - 0.5) * 5, sparkSize, 0, Math.PI * 2);
                        ctx.fill();
                        ctx.shadowBlur = oldShadowBlur; ctx.shadowColor = oldShadowColor;
                    }
                }
            }
            ctx.globalAlpha = 1.0; ctx.shadowBlur = 0;
            requestAnimationFrame(animateTrails);
        }

        // --- Mouse/Touch Input Handling ---
        function getEventCoordinates(e) { /* ... same as before ... */ 
            const rect = canvas.getBoundingClientRect();
            if (e.touches && e.touches.length > 0) {
                return { x: e.touches[0].clientX - rect.left, y: e.touches[0].clientY - rect.top };
            }
            return { x: e.clientX - rect.left, y: e.clientY - rect.top };
        }
        function startDrawing(e) { /* ... uses config.trailMaxLife ... */ 
            e.preventDefault();
            isDrawing = true;
            const { x, y } = getEventCoordinates(e);

            currentTrail = {
                points: [{ x, y }],
                originalHue: globalHue,
                initialLineWidth: Math.max(config.trailGlobalMinLineWidth, globalLineWidth),
                creationTime: Date.now(),
                maxLife: config.trailMaxLife + (Math.random() * config.trailMaxLife * 0.3 - config.trailMaxLife * 0.15) // Add some variance
            };
            activeTrails.push(currentTrail);

            createDOMParticle(x, y, {
                type: 'burst', color: `hsl(${currentTrail.originalHue}, 100%, 70%)`,
                spread: 40
            });
        }
        function draw(e) { /* ... same as before ... */ 
            if (!isDrawing || !currentTrail) return;
            e.preventDefault();
            const { x, y } = getEventCoordinates(e);
            currentTrail.points.push({ x, y });
        }
        function stopDrawing(e) { /* ... same as before ... */ 
            if (!isDrawing) return;
            isDrawing = false;
            if (currentTrail && currentTrail.points.length > 0) {
                const lastPoint = currentTrail.points[currentTrail.points.length - 1];
                createDOMParticle(lastPoint.x, lastPoint.y, {
                    type: 'poof', color: `hsla(${currentTrail.originalHue}, 100%, 70%, 0.7)`,
                    spread: 30
                });
            }
            currentTrail = null;
        }

        canvas.addEventListener('mousedown', startDrawing);
        canvas.addEventListener('mousemove', draw);
        canvas.addEventListener('mouseup', stopDrawing);
        canvas.addEventListener('mouseout', stopDrawing);
        canvas.addEventListener('touchstart', startDrawing);
        canvas.addEventListener('touchmove', draw);
        canvas.addEventListener('touchend', stopDrawing);
        canvas.addEventListener('touchcancel', stopDrawing);

        clearButton.addEventListener('click', () => { /* ... same as before ... */ 
            activeTrails = [];
            const canvasRect = canvas.getBoundingClientRect();
            const centerX = canvasRect.width / 2;
            const centerY = canvasRect.height / 2;
            for(let i=0; i < 5; i++){
                setTimeout(() => {
                     createDOMParticle(centerX + (Math.random()-0.5)*100, centerY + (Math.random()-0.5)*100, {
                        type: 'explosion', color: `hsl(${Math.random() * 360}, 100%, 70%)`,
                        spread: 150
                    });
                }, i * 100);
            }
        });

        // Initialize and Start
        resizeCanvas();
        initializeConfigPanel();
        animateTrails();
    </script>
</body>
</html>