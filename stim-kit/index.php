<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kinetic Joyboard</title>
    <style>
        /* Global Styles */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html, body {
            height: 100%;
            overflow: hidden; /* Prevent scrollbars from main layout */
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #eee;
        }

        body {
            display: flex;
            justify-content: center;
            align-items: center;
            background: linear-gradient(45deg, #1a2a6c, #3a0f3a, #0f3a31);
            background-size: 400% 400%;
            animation: gradientBG 20s ease infinite;
        }

        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .joyboard-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            padding: 20px;
            width: 95vw;
            height: 95vh;
            max-width: 1400px;
            overflow-y: auto; /* Allow scrolling if content overflows container */
        }

        .joy-element {
            background-color: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 15px;
            padding: 15px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            position: relative; /* For absolute positioning of internal items/particles */
            overflow: hidden; /* Crucial for containing particles and effects */
            min-height: 250px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            transition: transform 0.2s ease-out, box-shadow 0.2s ease-out;
        }

        .joy-element:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.3);
        }

        .joy-element h3 {
            margin-bottom: 15px;
            font-weight: 300;
            color: # Moccasin;
            text-align: center;
        }

        /* 1. Squishy Goo Pad */
        #goo-pad-area {
            justify-content: center; /* Center the goo pad itself */
        }
        #goo-pad {
            width: 180px;
            height: 180px;
            background: radial-gradient(circle, #ff00cc, #cc00aa);
            border-radius: 40px;
            cursor: grab;
            transition: transform 0.1s linear, filter 0.1s linear;
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative; /* For the ripple effect */
        }
        #goo-pad:active {
            cursor: grabbing;
            transform: scale(0.9);
            filter: brightness(1.2);
        }
        .goo-ripple {
            position: absolute;
            width: 10px;
            height: 10px;
            background-color: rgba(255, 255, 255, 0.7);
            border-radius: 50%;
            transform: scale(0);
            animation: ripple-effect 0.6s ease-out;
            pointer-events: none; /* So it doesn't interfere with goo pad clicks */
        }
        @keyframes ripple-effect {
            to {
                transform: scale(10);
                opacity: 0;
            }
        }


        /* 2. Cosmic Click-Wheel */
        #cosmic-wheel-area {
             position: relative; /* For absolute positioning of orbs */
        }
        #main-disc {
            width: 150px;
            height: 150px;
            background: conic-gradient(from 0deg, red, orange, yellow, green, blue, indigo, violet, red);
            border-radius: 50%;
            border: 5px solid #444;
            cursor: grab;
            position: relative;
            transition: filter 0.3s;
            animation: slowRotate 20s linear infinite; /* Gentle background spin */
        }
         @keyframes slowRotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .satellite-orb {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            position: absolute;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 10px;
            font-weight: bold;
            color: white;
            text-shadow: 0 0 3px black;
        }
        .satellite-orb:hover {
            transform: scale(1.1);
            box-shadow: 0 0 15px currentColor;
        }
        #orb1 { background-color: #ff5722; /* Deep Orange */ }
        #orb2 { background-color: #2196f3; /* Blue */ }
        #orb3 { background-color: #4caf50; /* Green */ }

        /* 3. Ephemeral Bubble Garden */
        #bubble-garden {
            background: linear-gradient(to bottom, #022c43, #053f5e);
            cursor: crosshair;
        }
        .bubble {
            position: absolute;
            bottom: -50px; /* Start below screen */
            background-color: rgba(173, 216, 230, 0.6); /* Light blue with transparency */
            border-radius: 50%;
            border: 1px solid rgba(224, 255, 255, 0.8); /* Lighter border for sheen */
            animation: floatUp 5s linear infinite, sideToSide 3s ease-in-out infinite alternate;
            pointer-events: auto; /* Bubbles should be clickable */
        }
        @keyframes floatUp {
            to {
                transform: translateY(-350px); /* Adjust based on garden height */
                opacity: 0;
            }
        }
        @keyframes sideToSide {
            from { margin-left: -10px; }
            to { margin-left: 10px; }
        }
        .bubble-pop-particle {
            position: absolute;
            border-radius: 50%;
            background: rgba(224, 255, 255, 0.8);
            animation: pop-particle-anim 0.5s ease-out forwards;
        }
        @keyframes pop-particle-anim {
            0% { transform: scale(1); opacity: 1; }
            100% { transform: scale(0); opacity: 0; }
        }


        /* 4. Infinite Pattern Drawer */
        #pattern-drawer-area {
             padding: 5px; /* To not have canvas flush against border */
        }
        #pattern-canvas {
            width: 100%;
            height: calc(100% - 40px); /* Account for button */
            background-color: #111;
            cursor: crosshair;
            border-radius: 10px;
        }
        #clear-canvas-btn {
            margin-top: 10px;
            padding: 8px 15px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        #clear-canvas-btn:hover {
            background-color: #45a049;
        }

        /* General Particle Style (can be adapted) */
        .particle {
            position: absolute;
            border-radius: 50%;
            pointer-events: none; /* So they don't interfere with interactions */
            animation: fadeOutAndRise 1s ease-out forwards;
        }
        @keyframes fadeOutAndRise {
            0% { transform: translateY(0) scale(1); opacity: 1; }
            100% { transform: translateY(-30px) scale(0.5); opacity: 0; }
        }

    </style>
</head>
<body>

    <div class="joyboard-container">
        <!-- Squishy Goo Pad -->
        <div id="goo-pad-area" class="joy-element">
            <h3>Squishy Goo</h3>
            <div id="goo-pad"></div>
        </div>

        <!-- Cosmic Click-Wheel -->
        <div id="cosmic-wheel-area" class="joy-element">
            <h3>Cosmic Wheel</h3>
            <div id="main-disc"></div>
            <div class="satellite-orb" id="orb1" title="Pulse">P</div>
            <div class="satellite-orb" id="orb2" title="Glow">G</div>
            <div class="satellite-orb" id="orb3" title="Sparkle">S</div>
        </div>

        <!-- Ephemeral Bubble Garden -->
        <div id="bubble-garden" class="joy-element">
            <h3>Bubble Pop</h3>
            <!-- Bubbles will be added here by JS -->
        </div>

        <!-- Infinite Pattern Drawer -->
        <div id="pattern-drawer-area" class="joy-element">
            <h3>Light Draw</h3>
            <canvas id="pattern-canvas"></canvas>
            <button id="clear-canvas-btn">Clear</button>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const joyboardContainer = document.querySelector('.joyboard-container');

            // --- Particle Helper ---
            function createParticle(parent, x, y, color = 'rgba(255, 255, 255, 0.7)', size = 5) {
                const particle = document.createElement('div');
                particle.classList.add('particle'); // General particle class
                particle.style.width = `${Math.random() * size + size / 2}px`;
                particle.style.height = particle.style.width;
                particle.style.backgroundColor = color;
                particle.style.left = `${x}px`;
                particle.style.top = `${y}px`;

                // More specific animation for general particles
                const angle = Math.random() * Math.PI * 2;
                const distance = Math.random() * 20 + 10;
                particle.style.setProperty('--translateX', `${Math.cos(angle) * distance}px`);
                particle.style.setProperty('--translateY', `${Math.sin(angle) * distance}px`);
                
                particle.animate([
                    { transform: 'translate(0,0) scale(1)', opacity: 1 },
                    { transform: `translate(var(--translateX), var(--translateY)) scale(0)`, opacity: 0 }
                ], {
                    duration: Math.random() * 500 + 500, // 0.5 - 1 second
                    easing: 'ease-out'
                });

                parent.appendChild(particle);
                setTimeout(() => particle.remove(), 1000);
            }

            // --- 1. Squishy Goo Pad ---
            const gooPadArea = document.getElementById('goo-pad-area');
            const gooPad = document.getElementById('goo-pad');
            let isGooDragging = false;

            gooPad.addEventListener('mousedown', (e) => {
                isGooDragging = true;
                gooPad.style.cursor = 'grabbing';
                
                // Create a ripple effect
                const ripple = document.createElement('div');
                ripple.classList.add('goo-ripple');
                // Position ripple relative to the gooPad, not the whole area
                const rect = gooPad.getBoundingClientRect();
                const areaRect = gooPadArea.getBoundingClientRect(); // Parent to position ripple correctly
                ripple.style.left = `${e.clientX - rect.left}px`;
                ripple.style.top = `${e.clientY - rect.top}px`;
                gooPad.appendChild(ripple);
                ripple.addEventListener('animationend', () => ripple.remove());

                // Particles on click
                for (let i = 0; i < 5; i++) {
                    createParticle(gooPadArea, e.clientX - areaRect.left, e.clientY - areaRect.top, 'magenta', 8);
                }
            });

            gooPad.addEventListener('mouseup', () => {
                isGooDragging = false;
                gooPad.style.cursor = 'grab';
            });
             gooPad.addEventListener('mouseleave', () => { // Reset if mouse leaves while pressed
                if(isGooDragging) {
                    isGooDragging = false;
                    gooPad.style.cursor = 'grab';
                    gooPad.style.transform = 'scale(1)'; // Reset scale
                }
            });


            // --- 2. Cosmic Click-Wheel ---
            const cosmicWheelArea = document.getElementById('cosmic-wheel-area');
            const mainDisc = document.getElementById('main-disc');
            const orbs = [document.getElementById('orb1'), document.getElementById('orb2'), document.getElementById('orb3')];
            let isDiscDragging = false;
            let currentDiscAngle = 0;
            let startDiscAngle = 0;
            let discRotationSpeed = 0;
            let lastDiscDragTime = 0;

            // Position orbs around the main disc
            const discRadius = mainDisc.offsetWidth / 2;
            const orbRadius = discRadius * 1.3; // Distance from center
            orbs.forEach((orb, i) => {
                const angle = (i / orbs.length) * 2 * Math.PI - Math.PI / 2; // Start from top
                orb.style.left = `calc(50% + ${orbRadius * Math.cos(angle)}px - ${orb.offsetWidth / 2}px)`;
                orb.style.top = `calc(50% + ${orbRadius * Math.sin(angle)}px - ${orb.offsetHeight / 2}px)`;

                orb.addEventListener('click', (e) => {
                    const rect = cosmicWheelArea.getBoundingClientRect();
                    const particleX = e.clientX - rect.left;
                    const particleY = e.clientY - rect.top;
                    mainDisc.style.filter = 'brightness(1.5)';
                    setTimeout(() => mainDisc.style.filter = 'brightness(1)', 300);

                    let particleColor = 'white';
                    if (orb.id === 'orb1') particleColor = '#ff5722';
                    if (orb.id === 'orb2') particleColor = '#2196f3';
                    if (orb.id === 'orb3') particleColor = '#4caf50';

                    for(let j = 0; j < 10; j++) {
                        createParticle(cosmicWheelArea, particleX, particleY, particleColor, 7);
                    }
                });
            });
            
            mainDisc.addEventListener('mousedown', (e) => {
                isDiscDragging = true;
                mainDisc.style.cursor = 'grabbing';
                mainDisc.style.animationPlayState = 'paused'; // Pause CSS animation
                const rect = mainDisc.getBoundingClientRect();
                const centerX = rect.left + rect.width / 2;
                const centerY = rect.top + rect.height / 2;
                startDiscAngle = Math.atan2(e.clientY - centerY, e.clientX - centerX) - currentDiscAngle * (Math.PI / 180);
                discRotationSpeed = 0;
                lastDiscDragTime = Date.now();
            });

            document.addEventListener('mousemove', (e) => {
                if (!isDiscDragging) return;
                const rect = mainDisc.getBoundingClientRect();
                const centerX = rect.left + rect.width / 2;
                const centerY = rect.top + rect.height / 2;
                const angle = Math.atan2(e.clientY - centerY, e.clientX - centerX);
                let newAngleDeg = (angle - startDiscAngle) * (180 / Math.PI);
                
                const currentTime = Date.now();
                const deltaTime = (currentTime - lastDiscDragTime) / 1000 || 0.016; // seconds
                discRotationSpeed = (newAngleDeg - currentDiscAngle) / deltaTime; // degrees per second
                lastDiscDragTime = currentTime;

                currentDiscAngle = newAngleDeg;
                mainDisc.style.transform = `rotate(${currentDiscAngle}deg)`;
            });

            document.addEventListener('mouseup', () => {
                if (isDiscDragging) {
                    isDiscDragging = false;
                    mainDisc.style.cursor = 'grab';
                    // Simple inertia: continue spinning based on last speed
                    // More complex physics would be needed for true inertia
                    if (Math.abs(discRotationSpeed) > 50) { // Only apply if spun fast enough
                         // No real inertia here for simplicity, just resumes CSS animation
                    }
                    mainDisc.style.animationPlayState = 'running'; // Resume CSS animation
                }
            });


            // --- 3. Ephemeral Bubble Garden ---
            const bubbleGarden = document.getElementById('bubble-garden');
            const gardenRect = bubbleGarden.getBoundingClientRect();

            function createBubble() {
                if (document.hidden) return; // Don't create if tab is not visible
                const bubble = document.createElement('div');
                bubble.classList.add('bubble');
                const size = Math.random() * 40 + 10; // 10px to 50px
                bubble.style.width = `${size}px`;
                bubble.style.height = `${size}px`;
                bubble.style.left = `${Math.random() * (gardenRect.width - size)}px`;
                bubble.style.animationDuration = `${Math.random() * 3 + 4}s, ${Math.random() * 2 + 2}s`; // floatUp, sideToSide

                bubble.addEventListener('click', (e) => {
                    e.stopPropagation(); // Prevent garden click
                    // Pop particles
                    const bubbleRect = bubble.getBoundingClientRect();
                    for (let i = 0; i < 5 + Math.floor(size/10); i++) { // More particles for bigger bubbles
                        const p = document.createElement('div');
                        p.classList.add('bubble-pop-particle');
                        p.style.width = `${Math.random() * (size/5) + 2}px`;
                        p.style.height = p.style.width;
                        p.style.left = `${e.clientX - gardenRect.left + (Math.random()*20-10)}px`; // Relative to garden
                        p.style.top = `${e.clientY - gardenRect.top + (Math.random()*20-10)}px`;
                        bubbleGarden.appendChild(p);
                        setTimeout(() => p.remove(), 500);
                    }
                    bubble.remove();
                });
                
                bubbleGarden.appendChild(bubble);
                setTimeout(() => bubble.remove(), parseFloat(bubble.style.animationDuration.split(',')[0]) * 1000 + 500); // Remove after animation + buffer
            }
            setInterval(createBubble, 700); // Create a new bubble periodically

            bubbleGarden.addEventListener('click', (e) => { // Click on garden itself as a "miss"
                // Small splash particle effect
                const rect = bubbleGarden.getBoundingClientRect();
                for(let i=0; i<3; i++) {
                    createParticle(bubbleGarden, e.clientX - rect.left, e.clientY - rect.top, 'rgba(100,150,255,0.5)', 4);
                }
            });


            // --- 4. Infinite Pattern Drawer ---
            const patternCanvas = document.getElementById('pattern-canvas');
            const clearCanvasBtn = document.getElementById('clear-canvas-btn');
            const ctx = patternCanvas.getContext('2d');
            let isDrawing = false;
            let lastX = 0;
            let lastY = 0;
            let hue = 0;

            function resizeCanvas() {
                patternCanvas.width = patternCanvas.clientWidth;
                patternCanvas.height = patternCanvas.clientHeight;
                ctx.lineJoin = 'round';
                ctx.lineCap = 'round';
                ctx.lineWidth = 5; // Initial width
            }
            resizeCanvas(); // Initial size
            window.addEventListener('resize', resizeCanvas); // Adjust on window resize

            function draw(e) {
                if (!isDrawing) return;
                const rect = patternCanvas.getBoundingClientRect();
                const currentX = e.clientX - rect.left;
                const currentY = e.clientY - rect.top;

                ctx.strokeStyle = `hsl(${hue}, 100%, 70%)`;
                ctx.shadowBlur = 10;
                ctx.shadowColor = `hsl(${hue}, 100%, 60%)`;
                
                ctx.beginPath();
                ctx.moveTo(lastX, lastY);
                ctx.lineTo(currentX, currentY);
                ctx.stroke();

                [lastX, lastY] = [currentX, currentY];
                hue = (hue + 1) % 360;
                ctx.lineWidth = Math.sin(hue/20) * 10 + 5; // Vary line width slightly
            }

            patternCanvas.addEventListener('mousedown', (e) => {
                isDrawing = true;
                const rect = patternCanvas.getBoundingClientRect();
                [lastX, lastY] = [e.clientX - rect.left, e.clientY - rect.top];
                hue = Math.random() * 360; // Start with a random color
            });
            patternCanvas.addEventListener('mousemove', draw);
            patternCanvas.addEventListener('mouseup', () => isDrawing = false);
            patternCanvas.addEventListener('mouseout', () => isDrawing = false); // Stop drawing if mouse leaves canvas

            clearCanvasBtn.addEventListener('click', () => {
                ctx.clearRect(0, 0, patternCanvas.width, patternCanvas.height);
                 // Particle burst on clear
                for(let i=0; i<50; i++){
                    createParticle(
                        document.getElementById('pattern-drawer-area'), // Parent for particles
                        Math.random() * patternCanvas.width,
                        Math.random() * patternCanvas.height,
                        `hsl(${Math.random()*360}, 100%, 70%)`,
                        8
                    );
                }
            });

            // Ensure dynamic elements like orbs are positioned after initial layout
            setTimeout(() => {
                // Re-calculate cosmic orb positions if needed, e.g., after fonts load
                 const discRadiusUpdated = mainDisc.offsetWidth / 2;
                 const orbRadiusUpdated = discRadiusUpdated * 1.3;
                 orbs.forEach((orb, i) => {
                    const angle = (i / orbs.length) * 2 * Math.PI - Math.PI / 2;
                    orb.style.left = `calc(50% + ${orbRadiusUpdated * Math.cos(angle)}px - ${orb.offsetWidth / 2}px)`;
                    orb.style.top = `calc(50% + ${orbRadiusUpdated * Math.sin(angle)}px - ${orb.offsetHeight / 2}px)`;
                });
            }, 100);

        });
    </script>
</body>
</html>