<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Spaceship Simulator</title>
    <style>
        body { margin: 0; background-color: #000; color: #fff; font-family: Arial, sans-serif; overflow: hidden; }
        #spaceCanvas { display: block; width: 100vw; height: 100vh; background-color: #020211; cursor: grab; }
        #spaceCanvas:active { cursor: grabbing; }
        #controlsPanel { position: fixed; top: 10px; left: 10px; background-color: rgba(30,30,50,0.85); padding: 15px; border-radius: 8px; border: 1px solid rgba(100,100,150,0.7); max-height: 90vh; overflow-y: auto; width: 340px; box-shadow: 0 0 15px rgba(100,100,200,0.5); z-index: 10; }
        #controlsPanel h2 { margin-top: 0; text-align: center; color: #aaccff; }
        .control-group { margin-bottom: 12px; }
        .control-group label { display: block; margin-bottom: 4px; font-size: 0.9em; color: #ddeeff; }
        .control-group input[type="range"] { width: calc(100% - 55px); margin-right: 5px; vertical-align: middle; }
        .control-group span { display: inline-block; width: 45px; text-align: right; font-size: 0.9em; vertical-align: middle; }
        button { display: block; width: 100%; padding: 10px; color: white; border: none; border-radius: 5px; cursor: pointer; margin-top: 10px; }
        #resetSceneButton { background-color: #4a70a0; } #resetSceneButton:hover { background-color: #5a80b0; }
        #randomizeAllButton { background-color: #6a4ca0; } #randomizeAllButton:hover { background-color: #7a5cb0; }
        hr { border: 0; height: 1px; background-image: linear-gradient(to right, rgba(100,100,150,0),rgba(100,100,150,0.75),rgba(100,100,150,0)); margin: 20px 0; }
    </style>
</head>
<body>
    <canvas id="spaceCanvas"></canvas>
    <div id="controlsPanel">
        <h2>Controls</h2>
        <!-- Star Controls -->
        <div class="control-group"><label for="starDensity">Star Density:</label><input type="range" id="starDensity" min="50" max="1000" value="200" step="10"><span id="starDensityValue">200</span></div>
        <div class="control-group"><label for="minStarSize">Min Star Size:</label><input type="range" id="minStarSize" min="0.1" max="2" value="0.5" step="0.1"><span id="minStarSizeValue">0.5</span></div>
        <div class="control-group"><label for="maxStarSize">Max Star Size:</label><input type="range" id="maxStarSize" min="0.5" max="5" value="1.5" step="0.1"><span id="maxStarSizeValue">1.5</span></div>
        <div class="control-group"><label for="minStarSpeed">Min Star Speed:</label><input type="range" id="minStarSpeed" min="0.1" max="2" value="0.2" step="0.1"><span id="minStarSpeedValue">0.2</span></div>
        <div class="control-group"><label for="maxStarSpeed">Max Star Speed:</label><input type="range" id="maxStarSpeed" min="0.5" max="5" value="1" step="0.1"><span id="maxStarSpeedValue">1</span></div>
        <div class="control-group"><label for="starBlinkChance">Star Blink (%):</label><input type="range" id="starBlinkChance" min="0" max="100" value="10" step="1"><span id="starBlinkChanceValue">10</span></div>
        <div class="control-group"><label for="starBlinkSpeed">Star Blink Speed:</label><input type="range" id="starBlinkSpeed" min="0.01" max="0.2" value="0.05" step="0.01"><span id="starBlinkSpeedValue">0.05</span></div>
        <div class="control-group"><label for="starColorHueMin">Star Hue Min:</label><input type="range" id="starColorHueMin" min="0" max="360" value="180" step="1"><span id="starColorHueMinValue">180</span></div>
        <div class="control-group"><label for="starColorHueMax">Star Hue Max:</label><input type="range" id="starColorHueMax" min="0" max="360" value="280" step="1"><span id="starColorHueMaxValue">280</span></div>
        <div class="control-group"><label for="starColorSaturation">Star Sat (%):</label><input type="range" id="starColorSaturation" min="0" max="100" value="80" step="1"><span id="starColorSaturationValue">80</span></div>
        <div class="control-group"><label for="starColorLightness">Star Light (%):</label><input type="range" id="starColorLightness" min="50" max="100" value="70" step="1"><span id="starColorLightnessValue">70</span></div>
        <hr>
        <!-- Asteroid Controls (2D) -->
        <div class="control-group"><label for="asteroidDensity">Asteroid Density:</label><input type="range" id="asteroidDensity" min="0" max="200" value="30" step="5"><span id="asteroidDensityValue">30</span></div>
        <div class="control-group"><label for="minAsteroidSize">Min Asteroid Size:</label><input type="range" id="minAsteroidSize" min="5" max="50" value="10" step="1"><span id="minAsteroidSizeValue">10</span></div>
        <div class="control-group"><label for="maxAsteroidSize">Max Asteroid Size:</label><input type="range" id="maxAsteroidSize" min="10" max="150" value="40" step="1"><span id="maxAsteroidSizeValue">40</span></div>
        <div class="control-group"><label for="minAsteroidSpeed">Min Asteroid Speed:</label><input type="range" id="minAsteroidSpeed" min="0.1" max="1.5" value="0.3" step="0.1"><span id="minAsteroidSpeedValue">0.3</span></div>
        <div class="control-group"><label for="maxAsteroidSpeed">Max Asteroid Speed:</label><input type="range" id="maxAsteroidSpeed" min="0.3" max="3" value="0.8" step="0.1"><span id="maxAsteroidSpeedValue">0.8</span></div>
        <hr>
        <!-- Comet Controls -->
        <div class="control-group"><label for="cometChance">Comet Chance (%):</label><input type="range" id="cometChance" min="0" max="5" value="0.5" step="0.1"><span id="cometChanceValue">0.5</span></div>
        <div class="control-group"><label for="cometSpeed">Comet Speed:</label><input type="range" id="cometSpeed" min="1" max="10" value="5" step="0.5"><span id="cometSpeedValue">5</span></div>
        <hr>
        <!-- Nebula Controls -->
        <div class="control-group"><label for="nebulaCount">Nebula Count:</label><input type="range" id="nebulaCount" min="0" max="10" value="3" step="1"><span id="nebulaCountValue">3</span></div>
        <div class="control-group"><label for="nebulaOpacity">Nebula Max Opacity:</label><input type="range" id="nebulaOpacity" min="0.05" max="0.5" value="0.2" step="0.01"><span id="nebulaOpacityValue">0.2</span></div>
        <div class="control-group"><label for="nebulaPulseChance">Nebula Pulse Chance (%):</label><input type="range" id="nebulaPulseChance" min="0" max="100" value="25" step="1"><span id="nebulaPulseChanceValue">25</span></div>
        <div class="control-group"><label for="nebulaPulseSpeed">Nebula Pulse Speed:</label><input type="range" id="nebulaPulseSpeed" min="0.001" max="0.05" value="0.01" step="0.001"><span id="nebulaPulseSpeedValue">0.01</span></div>
        <div class="control-group"><label for="nebulaPulseMagnitude">Nebula Pulse Magnitude:</label><input type="range" id="nebulaPulseMagnitude" min="0" max="0.5" value="0.15" step="0.01"><span id="nebulaPulseMagnitudeValue">0.15</span></div>

        <button id="resetSceneButton">Re-initialize Scene</button>
        <button id="randomizeAllButton">Randomize All & Refresh</button>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const canvas = document.getElementById('spaceCanvas');
            const ctx = canvas.getContext('2d');

            let width, height;
            let stars = [], comets = [], nebulas = [], asteroids = [];
            let isDragging = false, lastMouseY = 0, verticalOffset = 0;

            const controls = { 
                starDensity: document.getElementById('starDensity'), minStarSize: document.getElementById('minStarSize'), maxStarSize: document.getElementById('maxStarSize'), minStarSpeed: document.getElementById('minStarSpeed'), maxStarSpeed: document.getElementById('maxStarSpeed'), starBlinkChance: document.getElementById('starBlinkChance'), starBlinkSpeed: document.getElementById('starBlinkSpeed'), starColorHueMin: document.getElementById('starColorHueMin'), starColorHueMax: document.getElementById('starColorHueMax'), starColorSaturation: document.getElementById('starColorSaturation'), starColorLightness: document.getElementById('starColorLightness'), 
                asteroidDensity: document.getElementById('asteroidDensity'), minAsteroidSize: document.getElementById('minAsteroidSize'), maxAsteroidSize: document.getElementById('maxAsteroidSize'), minAsteroidSpeed: document.getElementById('minAsteroidSpeed'), maxAsteroidSpeed: document.getElementById('maxAsteroidSpeed'),
                cometChance: document.getElementById('cometChance'), cometSpeed: document.getElementById('cometSpeed'),
                nebulaCount: document.getElementById('nebulaCount'), nebulaOpacity: document.getElementById('nebulaOpacity'), 
                nebulaPulseChance: document.getElementById('nebulaPulseChance'), nebulaPulseSpeed: document.getElementById('nebulaPulseSpeed'), nebulaPulseMagnitude: document.getElementById('nebulaPulseMagnitude'),
                resetButton: document.getElementById('resetSceneButton'), randomizeAllButton: document.getElementById('randomizeAllButton')
            };

            const controlValues = { 
                starDensity:()=>parseInt(controls.starDensity.value),minStarSize:()=>parseFloat(controls.minStarSize.value),maxStarSize:()=>parseFloat(controls.maxStarSize.value),minStarSpeed:()=>parseFloat(controls.minStarSpeed.value),maxStarSpeed:()=>parseFloat(controls.maxStarSpeed.value),starBlinkChance:()=>parseInt(controls.starBlinkChance.value)/100,starBlinkSpeed:()=>parseFloat(controls.starBlinkSpeed.value),starColorHueMin:()=>parseInt(controls.starColorHueMin.value),starColorHueMax:()=>parseInt(controls.starColorHueMax.value),starColorSaturation:()=>parseInt(controls.starColorSaturation.value),starColorLightness:()=>parseInt(controls.starColorLightness.value),
                asteroidDensity:()=>parseInt(controls.asteroidDensity.value),minAsteroidSize:()=>parseFloat(controls.minAsteroidSize.value),maxAsteroidSize:()=>parseFloat(controls.maxAsteroidSize.value),minAsteroidSpeed:()=>parseFloat(controls.minAsteroidSpeed.value),maxAsteroidSpeed:()=>parseFloat(controls.maxAsteroidSpeed.value),
                cometChance:()=>parseFloat(controls.cometChance.value)/100,cometSpeed:()=>parseFloat(controls.cometSpeed.value),
                nebulaCount:()=>parseInt(controls.nebulaCount.value),nebulaOpacity:()=>parseFloat(controls.nebulaOpacity.value),
                nebulaPulseChance: () => parseFloat(controls.nebulaPulseChance.value) / 100, nebulaPulseSpeed: () => parseFloat(controls.nebulaPulseSpeed.value), nebulaPulseMagnitude: () => parseFloat(controls.nebulaPulseMagnitude.value)
            };

            function random(min, max) { return Math.random() * (max - min) + min; }
            function setCanvasSize() { width = canvas.width = window.innerWidth; height = canvas.height = window.innerHeight;}

            function createStar(x,y,s,sp,c,iB,bP){return{x,y,size:s,speed:sp,color:c,isBlinking:iB,blinkPhase:bP,baseAlpha:1};}
            function initStars(){stars=[];const d=controlValues.starDensity();for(let i=0;i<d;i++){const dep=random(0.1,1);const sp=controlValues.minStarSpeed()+(controlValues.maxStarSpeed()-controlValues.minStarSpeed())*dep;const s=controlValues.minStarSize()+(controlValues.maxStarSize()-controlValues.minStarSize())*dep;const h=random(controlValues.starColorHueMin(),controlValues.starColorHueMax());const sat=controlValues.starColorSaturation();const l=controlValues.starColorLightness();const clr=`hsla(${h},${sat}%,${l}%,1)`;const iB=Math.random()<controlValues.starBlinkChance();const bP=random(0,Math.PI*2);stars.push(createStar(random(0,width),random(0,height),s,sp,clr,iB,bP));}}
            function updateStar(s){s.x-=s.speed;if(s.x<-s.size){s.x=width+s.size;s.y=random(0,height);const dep=random(0.1,1);s.speed=controlValues.minStarSpeed()+(controlValues.maxStarSpeed()-controlValues.minStarSpeed())*dep;s.size=controlValues.minStarSize()+(controlValues.maxStarSize()-controlValues.minStarSize())*dep;const h=random(controlValues.starColorHueMin(),controlValues.starColorHueMax());s.color=`hsla(${h},${controlValues.starColorSaturation()}%,${controlValues.starColorLightness()}%,1)`;s.isBlinking=Math.random()<controlValues.starBlinkChance();s.blinkPhase=random(0,Math.PI*2);}if(s.isBlinking){s.blinkPhase+=controlValues.starBlinkSpeed();s.baseAlpha=0.75+Math.sin(s.blinkPhase)*0.25;}else{s.baseAlpha=1;}}
            function drawStar(s){const cP=s.color.match(/hsla\((\d+),\s*([\d.]+)%,\s*([\d.]+)%,\s*([\d.]+)\)/);ctx.fillStyle=cP?`hsla(${cP[1]},${cP[2]}%,${cP[3]}%,${s.baseAlpha})`:s.color;ctx.beginPath();ctx.arc(s.x,s.y,s.size/2,0,Math.PI*2);ctx.fill();}

            // --- Asteroid Logic (2D) ---
            function create2DAsteroidShape(avgSize) {
                const vertices = [];
                const numVertices = Math.floor(random(6, 12)); // Number of points for the polygon
                const angleStep = (Math.PI * 2) / numVertices;

                for (let i = 0; i < numVertices; i++) {
                    const currentAngle = i * angleStep;
                    // Perturb the radius for each vertex to make it irregular
                    const radius = avgSize * random(0.6, 1.4); 
                    vertices.push({
                        x: Math.cos(currentAngle) * radius,
                        y: Math.sin(currentAngle) * radius
                    });
                }
                return vertices;
            }

            function initAsteroids() {
                asteroids = [];
                const density = controlValues.asteroidDensity();
                for (let i = 0; i < density; i++) {
                    // Depth factor: 0.1 (close, large, fast) to 1.0 (far, small, slow)
                    const depthFactor = random(0.1, 1); 
                    
                    const size = controlValues.minAsteroidSize() + (controlValues.maxAsteroidSize() - controlValues.minAsteroidSize()) * (1 - depthFactor);
                    const speed = controlValues.minAsteroidSpeed() + (controlValues.maxAsteroidSpeed() - controlValues.minAsteroidSpeed()) * (1 - depthFactor);
                    
                    const x = random(0, width);
                    const y = random(0, height);
                    const angle = random(0, Math.PI * 2); // Initial 2D rotation angle
                    const rotationSpeed = random(-0.03, 0.03); // Radians per frame for 2D spin
                    
                    const vertices = create2DAsteroidShape(size / 2); // Pass average radius
                    
                    const lightness = random(25, 60); // Shades of grey/brown
                    const color = `hsl(30, ${random(10,30)}%, ${lightness}%)`;

                    asteroids.push({ x, y, size, speed, angle, rotationSpeed, vertices, color, depthFactor });
                }
            }

            function updateAsteroid(asteroid) {
                asteroid.x -= asteroid.speed;
                asteroid.angle += asteroid.rotationSpeed;

                if (asteroid.x < -asteroid.size) { // Reset asteroid
                    asteroid.x = width + asteroid.size;
                    asteroid.y = random(0, height);
                    
                    const depthFactor = random(0.1, 1);
                    asteroid.size = controlValues.minAsteroidSize() + (controlValues.maxAsteroidSize() - controlValues.minAsteroidSize()) * (1 - depthFactor);
                    asteroid.speed = controlValues.minAsteroidSpeed() + (controlValues.maxAsteroidSpeed() - controlValues.minAsteroidSpeed()) * (1 - depthFactor);
                    asteroid.vertices = create2DAsteroidShape(asteroid.size / 2);
                    const lightness = random(25, 60);
                    asteroid.color = `hsl(30, ${random(10,30)}%, ${lightness}%)`;
                    asteroid.rotationSpeed = random(-0.03, 0.03);
                    asteroid.depthFactor = depthFactor;
                }
            }

            function drawAsteroid(asteroid) {
                ctx.save();
                ctx.translate(asteroid.x, asteroid.y);
                ctx.rotate(asteroid.angle);
                ctx.fillStyle = asteroid.color;
                ctx.beginPath();
                if (asteroid.vertices.length > 0) {
                    ctx.moveTo(asteroid.vertices[0].x, asteroid.vertices[0].y);
                    for (let i = 1; i < asteroid.vertices.length; i++) {
                        ctx.lineTo(asteroid.vertices[i].x, asteroid.vertices[i].y);
                    }
                }
                ctx.closePath();
                ctx.fill();
                ctx.restore();
            }

            function createComet(){let x,y,a;const s=controlValues.cometSpeed();x=width+random(50,300);y=random(0,height);a=Math.PI+random(-0.1,0.1);const h=random(180,240);const clr=`hsla(${h},90%,70%,1)`;return{x,y,vx:Math.cos(a)*s,vy:Math.sin(a)*s,color:clr,tail:[]};}
            function updateComet(c){c.tail.unshift({x:c.x,y:c.y});if(c.tail.length>20+Math.abs(c.vx)*2)c.tail.pop();c.x+=c.vx;c.y+=c.vy;}
            function drawComet(c){ctx.fillStyle=c.color;ctx.beginPath();ctx.arc(c.x,c.y,3,0,Math.PI*2);ctx.fill();c.tail.forEach((seg,idx)=>{const alpha=1-(idx/c.tail.length);const s=Math.max(0.1,(3-(idx/c.tail.length)*3));const cP=c.color.match(/hsla\((\d+),\s*([\d.]+)%,\s*([\d.]+)%,\s*([\d.]+)\)/);if(cP)ctx.fillStyle=`hsla(${cP[1]},${cP[2]}%,${cP[3]}%,${alpha*0.8})`;ctx.beginPath();ctx.arc(seg.x,seg.y,s,0,Math.PI*2);ctx.fill();});}

            function createNebula() { /* ... (Nebula code remains the same) ... */
                const x = random(-width * 0.2, width * 1.2); const y = random(0, height);
                const radius = random(width * 0.1, width * 0.4); const hue = random(200, 300); 
                const saturation = random(60, 100); const lightness = random(20, 40); 
                const maxOpacity = controlValues.nebulaOpacity(); const speedX = random(-0.2, -0.05);
                const numLayers = Math.floor(random(3, 7)); const layers = [];
                for (let i = 0; i < numLayers; i++) { layers.push({ oX: random(-radius * 0.3, radius * 0.3), oY: random(-radius * 0.3, radius * 0.3), r: random(radius * 0.4, radius * 0.8) * (1 + i * 0.1), oF: random(0.3, 0.8) * (1 - i * 0.1) }); }
                const isPulsing = Math.random() < controlValues.nebulaPulseChance();
                const pulsePhase = random(0, Math.PI * 2);
                return { x, y, radius, hue, saturation, lightness, maxOpacity, speedX, layers, isPulsing, pulsePhase, currentOpacityMultiplier: 1 };
            }
            function initNebulas() { nebulas = []; const count = controlValues.nebulaCount(); for (let i = 0; i < count; i++) { nebulas.push(createNebula()); } }
            function updateNebula(nebula) {
                nebula.x += nebula.speedX;
                if (nebula.x + nebula.radius * 1.5 < 0) { nebula.x = width + nebula.radius * 1.5; nebula.y = random(0, height); nebula.isPulsing = Math.random() < controlValues.nebulaPulseChance(); nebula.pulsePhase = random(0, Math.PI * 2); }
                if (nebula.isPulsing) { nebula.pulsePhase += controlValues.nebulaPulseSpeed(); const pulseEffect = Math.sin(nebula.pulsePhase) * controlValues.nebulaPulseMagnitude(); nebula.currentOpacityMultiplier = 1 + pulseEffect; } 
                else { nebula.currentOpacityMultiplier = 1; }
            }
            function drawNebula(nebula) {
                ctx.save(); ctx.globalCompositeOperation = 'lighter'; 
                nebula.layers.forEach(layer => {
                    const gradX = nebula.x + layer.oX; const gradY = nebula.y + layer.oY; const currentRadius = layer.r;
                    const gradient = ctx.createRadialGradient(gradX, gradY, 0, gradX, gradY, currentRadius);
                    let baseLayerOpacity = nebula.maxOpacity * layer.oF; let pulsedOpacity = baseLayerOpacity * nebula.currentOpacityMultiplier;
                    pulsedOpacity = Math.max(0, Math.min(nebula.maxOpacity, pulsedOpacity));
                    gradient.addColorStop(0, `hsla(${nebula.hue + random(-20, 20)}, ${nebula.saturation}%, ${nebula.lightness + 10}%, ${pulsedOpacity})`);
                    gradient.addColorStop(0.3 + random(-0.1, 0.1), `hsla(${nebula.hue + random(-10, 10)}, ${nebula.saturation - 5}%, ${nebula.lightness}%, ${pulsedOpacity * 0.7})`);
                    gradient.addColorStop(0.7 + random(-0.1, 0.1), `hsla(${nebula.hue}, ${nebula.saturation - 10}%, ${nebula.lightness - 5}%, ${pulsedOpacity * 0.3})`);
                    gradient.addColorStop(1, `hsla(${nebula.hue - random(0, 10)}, ${nebula.saturation - 15}%, ${nebula.lightness - 10}%, 0)`);
                    ctx.fillStyle = gradient; ctx.beginPath(); ctx.arc(gradX, gradY, currentRadius, 0, Math.PI * 2); ctx.fill();
                }); ctx.restore(); 
            }

            function animate() {
                ctx.clearRect(0, 0, width, height);
                ctx.save(); ctx.translate(0, verticalOffset);
                nebulas.forEach(n => { updateNebula(n); drawNebula(n); });
                
                // No Z-sorting needed for 2D asteroids with this draw order
                asteroids.forEach(a => { updateAsteroid(a); drawAsteroid(a); });

                stars.forEach(s => { updateStar(s); drawStar(s); });
                if (Math.random() < controlValues.cometChance()) if (comets.length < 20) comets.push(createComet());
                comets = comets.filter(c=>c.x>-100-c.tail.length*5&&c.x<width+100+c.tail.length*5&&c.y>-100&&c.y<height+100);
                comets.forEach(c => { updateComet(c); drawComet(c); });
                ctx.restore();
                requestAnimationFrame(animate);
            }

            canvas.addEventListener('mousedown',(e)=>{isDragging=true;lastMouseY=e.clientY;canvas.style.cursor='grabbing';e.preventDefault();});
            window.addEventListener('mousemove',(e)=>{if(isDragging){const dY=e.clientY-lastMouseY;verticalOffset+=dY;lastMouseY=e.clientY;}});
            window.addEventListener('mouseup',()=>{if(isDragging){isDragging=false;canvas.style.cursor='grab';}});
            document.addEventListener('mouseleave',()=>{if(isDragging){isDragging=false;canvas.style.cursor='grab';}});

            function setupEventListeners() {
                Object.keys(controls).forEach(k=>{if(k==='resetButton'||k==='randomizeAllButton')return;const iE=controls[k];const vE=document.getElementById(`${k}Value`);if(iE&&vE){iE.addEventListener('input',()=>{vE.textContent=iE.value;const p=[['minStarSize','maxStarSize'],['minStarSpeed','maxStarSpeed'],['starColorHueMin','starColorHueMax'],['minAsteroidSize','maxAsteroidSize'],['minAsteroidSpeed','maxAsteroidSpeed']];p.forEach(pK=>{if(k===pK[0]&&parseFloat(iE.value)>parseFloat(controls[pK[1]].value)){controls[pK[1]].value=iE.value;document.getElementById(`${pK[1]}Value`).textContent=iE.value;}if(k===pK[1]&&parseFloat(iE.value)<parseFloat(controls[pK[0]].value)){controls[pK[0]].value=iE.value;document.getElementById(`${pK[0]}Value`).textContent=iE.value;}});if(k==='nebulaCount'||k==='nebulaOpacity'||k.includes('nebulaPulse'))initNebulas();if(k==='asteroidDensity'||k.includes('AsteroidSize'))initAsteroids();});if(vE)vE.textContent=iE.value;}});
                controls.resetButton.addEventListener('click',()=>{initStars();initNebulas();initAsteroids();comets=[];verticalOffset=0;});
                controls.randomizeAllButton.addEventListener('click',()=>{const sK=Object.keys(controls).filter(k=>k!=='resetButton'&&k!=='randomizeAllButton');sK.forEach(k=>{const s=controls[k];const vS=document.getElementById(`${k}Value`);const min=parseFloat(s.min);const max=parseFloat(s.max);const step=parseFloat(s.step);let rV;if(step===1||(Number.isInteger(step)&&step!==0)){const nSIR=Math.floor((max-min)/step);rV=min+(Math.floor(Math.random()*(nSIR+1))*step);}else{const dP=String(step).includes('.')?String(step).split('.')[1].length:0;const scale=Math.pow(10,dP);const mS=Math.round(min*scale);const mxS=Math.round(max*scale);const sS=Math.round(step*scale);const nSIR=Math.floor((mxS-mS)/sS);const rSC=Math.floor(Math.random()*(nSIR+1));rV=(mS+(rSC*sS))/scale;rV=parseFloat(rV.toFixed(dP));}s.value=rV;if(vS)vS.textContent=s.value;});const p=[['minStarSize','maxStarSize'],['minStarSpeed','maxStarSpeed'],['starColorHueMin','starColorHueMax'],['minAsteroidSize','maxAsteroidSize'],['minAsteroidSpeed','maxAsteroidSpeed']];p.forEach(pK=>{const mS=controls[pK[0]];const mxS=controls[pK[1]];const mVS=document.getElementById(`${pK[0]}Value`);const mxVS=document.getElementById(`${pK[1]}Value`);if(parseFloat(mS.value)>parseFloat(mxS.value)){const t=mS.value;mS.value=mxS.value;mxS.value=t;if(mVS)mVS.textContent=mS.value;if(mxVS)mxVS.textContent=mxS.value;}});initStars();initNebulas();initAsteroids();comets=[];verticalOffset=0;});
                window.addEventListener('resize',()=>{setCanvasSize();initStars();initNebulas();initAsteroids();});
            }
            
            setCanvasSize(); initStars(); initNebulas(); initAsteroids();
            setupEventListeners(); animate();
        });
    </script>
</body>
</html>