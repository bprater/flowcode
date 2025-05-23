<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Raindrop Simulator</title>
    <style>
        body {
            font-family: sans-serif;
            margin: 0;
            background-color: #222;
            color: #eee;
            overflow: hidden; /* Prevent body scrollbars */
            display: flex; /* Make body a flex container for app-container */
        }

        #app-container {
            display: flex;
            width: 100vw;
            height: 100vh;
        }

        #rainCanvas {
            flex-grow: 1; /* Canvas takes remaining width */
            height: 100%; 
            background-color: #001020;
            display: block;
            border-right: 1px solid #111; /* Separator, good if controls are on right */
        }

        #controls-container {
            width: 360px; /* Expanded width */
            height: 100vh;
            background-color: #333;
            position: relative; /* For positioning the toggle button */
            display: flex; 
            flex-direction: row; /* Button on left, content on right */
            transition: width 0.3s ease-in-out;
            box-shadow: -3px 0 8px rgba(0,0,0,0.3); /* Shadow on left edge */
            flex-shrink: 0; /* Prevent controls from being squeezed too small */
        }

        #controls-container.collapsed {
            width: 40px; /* Collapsed width, just for the button */
        }

        #toggleControlsBtn {
            background-color: #4a4a4a;
            color: #eee;
            border: none;
            border-right: 1px solid #282828; /* Separator line */
            padding: 0;
            width: 40px; /* Fixed width for the button column */
            height: 60px; 
            align-self: flex-start;
            margin-top: 10px;
            cursor: pointer;
            font-size: 1.8em;
            line-height: 60px; /* Center text vertically */
            text-align: center;
            z-index: 100;
            transition: background-color 0.2s;
            flex-shrink: 0; /* Button should not shrink */
            border-radius: 0 5px 5px 0; /* Rounded right corners if on left of panel */
        }
        #toggleControlsBtn:hover {
            background-color: #5c5c5c;
        }
        /* If button is on the left of the panel content: */
        #controls-container.collapsed #toggleControlsBtn {
             border-radius: 0; /* Flat when only button is visible */
        }


        #controls-content {
            flex-grow: 1; 
            display: flex;
            flex-direction: column;
            overflow: hidden; /* Crucial: Hide content when panel width is too small */
            height: 100%;
            opacity: 1;
            transition: opacity 0.2s ease-in-out 0.1s; /* Delay opacity transition slightly */
        }

        #controls-container.collapsed #controls-content {
            opacity: 0;
            pointer-events: none; /* Prevent interaction with hidden content */
        }

        #controls-content h1 {
            text-align: center;
            margin: 20px 15px 15px 15px;
            font-size: 1.4em;
            flex-shrink: 0; /* Prevent h1 from shrinking/wrapping badly */
            white-space: nowrap; /* Prevent wrapping during transition */
        }

        #controls { /* This is the grid container for sliders */
            padding: 0 15px 15px 15px;
            overflow-y: auto; /* Scroll for sliders if they exceed height */
            flex-grow: 1; /* Takes remaining vertical space in #controls-content */
            display: grid;
            grid-template-columns: auto 1fr auto; 
            gap: 10px 15px;
            align-items: center;
        }
        /* Prevent text wrapping in labels/values during collapse for smoother look */
        #controls label, #controls span {
            white-space: nowrap;
            font-size: 0.9em;
        }
         #controls input[type="range"] {
            width: 100%;
            cursor: pointer;
        }
        #controls span {
            min-width: 35px; 
            text-align: left;
        }


        .control-group-title {
            grid-column: 1 / -1; 
            text-align: center;
            font-weight: bold;
            margin-top: 10px;
            margin-bottom: 5px;
            color: #aaa;
            border-bottom: 1px solid #444;
            padding-bottom: 5px;
            white-space: nowrap;
        }
        .control-group-title:first-of-type {
            margin-top: 0;
        }
    </style>
</head>
<body>
    <div id="app-container">
        <canvas id="rainCanvas"></canvas>
        <div id="controls-container">
            <button id="toggleControlsBtn" title="Hide Controls">></button> 
            <div id="controls-content">
                <h1>Raindrop Simulator</h1>
                <div id="controls">
                    <div class="control-group-title">General Rain</div>
                    <label for="maxDrops">Max Drops:</label>
                    <input type="range" id="maxDrops" min="10" max="1000" value="200">
                    <span id="maxDropsValue">200</span>

                    <label for="minDrops">Min Drops:</label>
                    <input type="range" id="minDrops" min="5" max="500" value="50">
                    <span id="minDropsValue">50</span>

                    <label for="gravity">Gravity:</label>
                    <input type="range" id="gravity" min="0.05" max="2" value="0.5" step="0.05">
                    <span id="gravityValue">0.5</span>

                    <label for="decayRate">Decay Rate:</label>
                    <input type="range" id="decayRate" min="0.001" max="0.05" value="0.01" step="0.001">
                    <span id="decayRateValue">0.01</span>

                    <label for="baseWind">Base Wind:</label>
                    <input type="range" id="baseWind" min="-2" max="2" value="0.1" step="0.05">
                    <span id="baseWindValue">0.1</span>

                    <label for="windVariance">Wind Variance:</label>
                    <input type="range" id="windVariance" min="0" max="1" value="0.3" step="0.05">
                    <span id="windVarianceValue">0.3</span>

                    <div class="control-group-title">Mid-Air Splitting</div>
                    <label for="midAirSplitChance">Mid-Air Split %:</label>
                    <input type="range" id="midAirSplitChance" min="0" max="10" value="0.5" step="0.1">
                    <span id="midAirSplitChanceValue">0.5</span>
                    
                    <label for="minMidAirSplitSize">Min Split Size:</label>
                    <input type="range" id="minMidAirSplitSize" min="0.5" max="3" value="1.5" step="0.1">
                    <span id="minMidAirSplitSizeValue">1.5</span>

                    <label for="maxMidAirSplitSize">Max Spawn Size:</label>
                    <input type="range" id="maxMidAirSplitSize" min="1" max="5" value="2.5" step="0.1">
                    <span id="maxMidAirSplitSizeValue">2.5</span>

                    <div class="control-group-title">Floor Interaction</div>
                    <label for="floorSplitChance">Floor Split %:</label>
                    <input type="range" id="floorSplitChance" min="0" max="100" value="70" step="1">
                    <span id="floorSplitChanceValue">70</span>

                    <label for="minSplashParticleSize">Min Drop Size to Splash:</label>
                    <input type="range" id="minSplashParticleSize" min="0.2" max="3" value="1.0" step="0.1">
                    <span id="minSplashParticleSizeValue">1.0</span>

                    <label for="floorSplitNumMin">Min Splash Drops:</label>
                    <input type="range" id="floorSplitNumMin" min="1" max="5" value="2" step="1">
                    <span id="floorSplitNumMinValue">2</span>

                    <label for="floorSplitNumMax">Max Splash Drops:</label>
                    <input type="range" id="floorSplitNumMax" min="2" max="10" value="5" step="1">
                    <span id="floorSplitNumMaxValue">5</span>

                    <label for="splashParticleSizeMultMin">Splash Size Mult Min:</label>
                    <input type="range" id="splashParticleSizeMultMin" min="0.1" max="0.5" value="0.2" step="0.01">
                    <span id="splashParticleSizeMultMinValue">0.20</span>

                    <label for="splashParticleSizeMultMax">Splash Size Mult Max:</label>
                    <input type="range" id="splashParticleSizeMultMax" min="0.2" max="0.8" value="0.4" step="0.01">
                    <span id="splashParticleSizeMultMaxValue">0.40</span>
                    
                    <label for="floorBounceFactor">Bounce Factor:</label>
                    <input type="range" id="floorBounceFactor" min="0" max="0.9" value="0.4" step="0.05">
                    <span id="floorBounceFactorValue">0.4</span>

                    <label for="floorFriction">Floor Friction:</label>
                    <input type="range" id="floorFriction" min="0.5" max="1" value="0.8" step="0.05">
                    <span id="floorFrictionValue">0.8</span>
                </div> <!-- /#controls -->
            </div> <!-- /#controls-content -->
        </div> <!-- /#controls-container -->
    </div> <!-- /#app-container -->

    <script>
        const canvas = document.getElementById('rainCanvas');
        const ctx = canvas.getContext('2d');
        const controlsContainer = document.getElementById('controls-container');
        const toggleControlsBtn = document.getElementById('toggleControlsBtn');
        
        let controlsVisible = true; // Initial state: expanded

        // --- Settings (will be updated by sliders) ---
        let settings = {
            maxDrops: 200, minDrops: 50, gravity: 0.5, decayRate: 0.01,
            baseWind: 0.1, windVariance: 0.3, midAirSplitChance: 0.005,
            minMidAirSplitSize: 1.5, maxMidAirSplitSize: 2.5, floorSplitChance: 0.7,
            minSplashParticleSize: 1.0, floorSplitNumMin: 2, floorSplitNumMax: 5,
            splashParticleSizeMultMin: 0.2, splashParticleSizeMultMax: 0.4,
            floorBounceFactor: 0.4, floorFriction: 0.8
        };

        let raindrops = [];

        function resizeCanvas() {
            // Set canvas drawing surface size to its actual displayed size
            canvas.width = canvas.offsetWidth;
            canvas.height = canvas.offsetHeight;
            // console.log("Canvas resized to:", canvas.width, canvas.height); // For debugging
        }

        function toggleControls() {
            controlsVisible = !controlsVisible;
            controlsContainer.classList.toggle('collapsed');

            if (controlsVisible) {
                toggleControlsBtn.innerHTML = '>'; // Arrow pointing right (suggests: "push to collapse")
                toggleControlsBtn.title = "Hide Controls";
            } else {
                toggleControlsBtn.innerHTML = '<'; // Arrow pointing left (suggests: "pull to expand")
                toggleControlsBtn.title = "Show Controls";
            }

            // Resize canvas AFTER the CSS transition completes for accurate dimensions
            setTimeout(() => {
                resizeCanvas();
            }, 310); // A bit longer than the CSS transition for width (0.3s)
        }

        // Raindrop Class (no changes needed here from previous version)
        class Raindrop {
            constructor(x, y, vx, vy, radius, opacity, color = 'rgba(170, 200, 255, 0.7)') {
                this.x = x; this.y = y; this.vx = vx; this.vy = vy;
                this.radius = radius; this.opacity = opacity; this.color = color;
                this.canSplitMidAir = true; this.hasHitFloor = false;
            }
            update() {
                this.vy += settings.gravity * 0.1; 
                this.x += this.vx; this.y += this.vy;
                this.opacity -= settings.decayRate;

                if (!this.hasHitFloor && this.y + this.radius > canvas.height) {
                    this.y = canvas.height - this.radius; this.hasHitFloor = true;
                    if (this.radius >= settings.minSplashParticleSize && Math.random() < settings.floorSplitChance) {
                        this.createSplashParticles(); this.opacity = 0;
                    } else {
                        this.vy *= -settings.floorBounceFactor; this.vx *= settings.floorFriction;
                        if (Math.abs(this.vy) < 0.1) this.vy = 0;
                        if (Math.abs(this.vx) < 0.05) this.vx = 0;
                        this.canSplitMidAir = false; 
                    }
                } else if (this.hasHitFloor) {
                    this.vx *= 0.98;
                    if (this.vy === 0) this.opacity -= settings.decayRate * 2;
                }

                if (this.x + this.radius < 0 && this.vx < 0) this.x = canvas.width + this.radius;
                if (this.x - this.radius > canvas.width && this.vx > 0) this.x = 0 - this.radius;

                if (this.canSplitMidAir && !this.hasHitFloor && this.radius > settings.minMidAirSplitSize && 
                    Math.random() < settings.midAirSplitChance && this.y < canvas.height * 0.85) {
                    this.splitMidAir(); this.opacity = 0;
                }
            }
            splitMidAir() {
                const numSplits = Math.floor(Math.random() * 2) + 2;
                for (let i = 0; i < numSplits; i++) {
                    const newRadius = Math.max(0.5, this.radius * (Math.random() * 0.4 + 0.3)); 
                    const newVX = this.vx + (Math.random() - 0.5) * 1.5; 
                    const newVY = this.vy * (Math.random() * 0.5 + 0.2); 
                    const newDrop = new Raindrop(this.x, this.y, newVX, newVY, newRadius, this.opacity * 0.9);
                    newDrop.canSplitMidAir = false; raindrops.push(newDrop);
                }
            }
            createSplashParticles() {
                const numSplits = Math.floor(Math.random() * (settings.floorSplitNumMax - settings.floorSplitNumMin + 1)) + settings.floorSplitNumMin;
                for (let i = 0; i < numSplits; i++) {
                    const sizeMultiplier = Math.random() * (settings.splashParticleSizeMultMax - settings.splashParticleSizeMultMin) + settings.splashParticleSizeMultMin;
                    const newRadius = Math.max(0.3, this.radius * sizeMultiplier);
                    const angle = Math.random() * Math.PI; 
                    const speed = (Math.random() * 1.5 + 0.5) * Math.sqrt(Math.abs(this.vy * 0.5));
                    const newVX = Math.cos(angle) * speed * (Math.random() > 0.5 ? 1 : -1) * 1.5 ;
                    const newVY = -Math.sin(angle) * speed * (Math.random() * 0.5 + 0.5);
                    const newOpacity = Math.min(1, this.opacity * (Math.random() * 0.3 + 0.6));
                    const splashDrop = new Raindrop(this.x, this.y - newRadius, newVX, newVY, newRadius, newOpacity);
                    splashDrop.canSplitMidAir = false; splashDrop.hasHitFloor = true; 
                    raindrops.push(splashDrop);
                }
            }
            draw() {
                if (this.opacity <= 0) return;
                ctx.beginPath();
                let yRadius = this.radius, xRadius = this.radius;
                if (!this.hasHitFloor && Math.abs(this.vy) > settings.gravity * 2) {
                    yRadius = this.radius * (1 + Math.min(0.8, Math.abs(this.vy) * 0.05));
                    xRadius = this.radius * Math.max(0.6, (1 - Math.min(0.4, Math.abs(this.vy) * 0.025)));
                }
                ctx.ellipse(this.x, this.y, xRadius, yRadius, 0, 0, Math.PI * 2);
                const baseColor = this.color.substring(0, this.color.lastIndexOf(','));
                ctx.fillStyle = `${baseColor}, ${Math.max(0, this.opacity)})`;
                ctx.fill();
            }
        }

        // Particle Management (no changes needed here)
        function createRaindrop() {
            const radius = Math.random() * (settings.maxMidAirSplitSize - settings.minMidAirSplitSize) + settings.minMidAirSplitSize;
            const x = Math.random() * canvas.width;
            const y = -radius - Math.random() * canvas.height * 0.1;
            const vx = settings.baseWind + (Math.random() - 0.5) * settings.windVariance * 2;
            const vy = Math.random() * 2 + 1 + settings.gravity;
            const opacity = Math.random() * 0.3 + 0.7; 
            raindrops.push(new Raindrop(x, y, vx, vy, radius, opacity));
        }
        function populateRaindrops() {
            raindrops = raindrops.filter(drop => drop.opacity > 0.005 && drop.y < canvas.height + drop.radius * 10);
            const targetDrops = Math.floor(Math.random() * (settings.maxDrops - settings.minDrops + 1)) + settings.minDrops;
            if (raindrops.length < targetDrops) {
                const dropsToAdd = Math.min(5, targetDrops - raindrops.length);
                for (let i = 0; i < dropsToAdd; i++) { createRaindrop(); }
            }
        }

        // Animation Loop (no changes needed here)
        function animate() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            populateRaindrops();
            for (let i = raindrops.length - 1; i >= 0; i--) {
                raindrops[i].update();
                raindrops[i].draw();
            }
            requestAnimationFrame(animate);
        }

        // Slider Event Listeners (no changes needed here)
        function setupSliders() {
            const controlsElement = document.getElementById('controls'); // Target the inner grid
            const inputs = controlsElement.querySelectorAll('input[type="range"]');
            inputs.forEach(input => {
                const valueSpan = document.getElementById(input.id + 'Value');
                let initialValue = parseFloat(input.value);
                if (input.id === "midAirSplitChance" || input.id === "floorSplitChance") {
                     settings[input.id] = initialValue / 100;
                } else { settings[input.id] = initialValue; }
                if (valueSpan) {
                    valueSpan.textContent = input.value; 
                    if (input.step.includes('.')) {
                         valueSpan.textContent = parseFloat(input.value).toFixed(input.step.split('.')[1].length);
                    }
                }
                input.addEventListener('input', (e) => {
                    const value = parseFloat(e.target.value);
                    if (e.target.id === "midAirSplitChance" || e.target.id === "floorSplitChance") {
                        settings[e.target.id] = value / 100;
                    } else { settings[e.target.id] = value; }
                    if (valueSpan) {
                        valueSpan.textContent = value.toFixed(e.target.step.includes('.') ? e.target.step.split('.')[1].length : 0);
                    }
                    // Min/Max constraints... (ensure these are still correct)
                    const idsToUpdate = {
                        minDrops: "maxDrops", floorSplitNumMin: "floorSplitNumMax", splashParticleSizeMultMin: "splashParticleSizeMultMax"
                    };
                    const maxForMin = { maxDrops: "minDrops", floorSplitNumMax: "floorSplitNumMin", splashParticleSizeMultMax: "splashParticleSizeMultMin" };

                    if (idsToUpdate[e.target.id] && settings[e.target.id] > settings[idsToUpdate[e.target.id]]) {
                        const linkedMaxId = idsToUpdate[e.target.id];
                        settings[linkedMaxId] = settings[e.target.id];
                        document.getElementById(linkedMaxId).value = settings[linkedMaxId];
                        document.getElementById(linkedMaxId + "Value").textContent = settings[linkedMaxId].toFixed(document.getElementById(linkedMaxId).step.includes('.') ? document.getElementById(linkedMaxId).step.split('.')[1].length : 0);
                    }
                    if (maxForMin[e.target.id] && settings[e.target.id] < settings[maxForMin[e.target.id]]) {
                         const linkedMinId = maxForMin[e.target.id];
                        settings[linkedMinId] = settings[e.target.id];
                        document.getElementById(linkedMinId).value = settings[linkedMinId];
                        document.getElementById(linkedMinId + "Value").textContent = settings[linkedMinId].toFixed(document.getElementById(linkedMinId).step.includes('.') ? document.getElementById(linkedMinId).step.split('.')[1].length : 0);
                    }
                });
            });
        }

        // --- Initialization ---
        toggleControlsBtn.addEventListener('click', toggleControls);
        
        // Initial setup for button text based on 'controlsVisible'
        if (controlsVisible) {
            toggleControlsBtn.innerHTML = '>'; 
            toggleControlsBtn.title = "Hide Controls";
        } else { // Should not happen if controlsVisible is true by default and no 'collapsed' class initially
            controlsContainer.classList.add('collapsed'); // Ensure consistency if starting collapsed
            toggleControlsBtn.innerHTML = '<';
            toggleControlsBtn.title = "Show Controls";
        }

        window.addEventListener('resize', resizeCanvas); // For browser window resize

        // Initial sizing, setup, and start animation
        // Use window.onload or a DOMContentLoaded listener to ensure elements are ready
        window.addEventListener('DOMContentLoaded', () => {
            resizeCanvas(); 
            setupSliders();
            animate();
        });

    </script>
</body>
</html>