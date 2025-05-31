<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Spaceship Simulator</title>
    <style>
        body {
            margin: 0;
            background-color: #000000;
            color: #fff;
            font-family: Arial, sans-serif;
            overflow: hidden; /* Hide scrollbars from canvas content */
        }

        #spaceCanvas {
            display: block; /* Remove extra space below canvas */
            width: 100vw;
            height: 100vh;
            background-color: #020211; /* Deep space blue/black */
            cursor: grab; /* Indicate draggable */
        }
        #spaceCanvas:active {
            cursor: grabbing; /* Indicate dragging */
        }

        #controlsPanel {
            position: fixed;
            top: 10px;
            left: 10px;
            background-color: rgba(30, 30, 50, 0.85);
            padding: 15px;
            border-radius: 8px;
            border: 1px solid rgba(100, 100, 150, 0.7);
            max-height: 90vh;
            overflow-y: auto;
            width: 320px; /* Adjust as needed */
            box-shadow: 0 0 15px rgba(100, 100, 200, 0.5);
            z-index: 10; /* Ensure controls are on top */
        }

        #controlsPanel h2 {
            margin-top: 0;
            text-align: center;
            color: #aaccff;
        }

        .control-group {
            margin-bottom: 12px;
        }

        .control-group label {
            display: block;
            margin-bottom: 4px;
            font-size: 0.9em;
            color: #ddeeff;
        }

        .control-group input[type="range"] {
            width: calc(100% - 50px); /* Adjust based on span width */
            margin-right: 5px;
            vertical-align: middle;
        }

        .control-group span {
            display: inline-block;
            width: 40px; /* Adjust as needed */
            text-align: right;
            font-size: 0.9em;
            vertical-align: middle;
        }

        button { /* General button styling */
            display: block;
            width: 100%;
            padding: 10px;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 10px; /* Consistent margin for buttons */
        }

        #resetSceneButton {
            background-color: #4a70a0;
        }
        #resetSceneButton:hover {
            background-color: #5a80b0;
        }

        #randomizeAllButton {
            background-color: #6a4ca0; /* Different color for distinction */
        }
        #randomizeAllButton:hover {
            background-color: #7a5cb0;
        }


        hr {
            border: 0;
            height: 1px;
            background-image: linear-gradient(to right, rgba(100,100,150,0), rgba(100,100,150,0.75), rgba(100,100,150,0));
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <canvas id="spaceCanvas"></canvas>

    <div id="controlsPanel">
        <h2>Controls</h2>

        <div class="control-group">
            <label for="starDensity">Star Density:</label>
            <input type="range" id="starDensity" min="50" max="1000" value="200" step="10">
            <span id="starDensityValue">200</span>
        </div>
        <div class="control-group">
            <label for="minStarSize">Min Star Size:</label>
            <input type="range" id="minStarSize" min="0.1" max="2" value="0.5" step="0.1">
            <span id="minStarSizeValue">0.5</span>
        </div>
        <div class="control-group">
            <label for="maxStarSize">Max Star Size:</label>
            <input type="range" id="maxStarSize" min="0.5" max="5" value="1.5" step="0.1">
            <span id="maxStarSizeValue">1.5</span>
        </div>
        <div class="control-group">
            <label for="minStarSpeed">Min Star Speed (Depth):</label>
            <input type="range" id="minStarSpeed" min="0.1" max="2" value="0.2" step="0.1">
            <span id="minStarSpeedValue">0.2</span>
        </div>
        <div class="control-group">
            <label for="maxStarSpeed">Max Star Speed (Depth):</label>
            <input type="range" id="maxStarSpeed" min="0.5" max="5" value="1" step="0.1">
            <span id="maxStarSpeedValue">1</span>
        </div>
        <div class="control-group">
            <label for="starBlinkChance">Star Blink Chance (%):</label>
            <input type="range" id="starBlinkChance" min="0" max="100" value="10" step="1">
            <span id="starBlinkChanceValue">10</span>
        </div>
        <div class="control-group">
            <label for="starBlinkSpeed">Star Blink Speed:</label>
            <input type="range" id="starBlinkSpeed" min="0.01" max="0.2" value="0.05" step="0.01">
            <span id="starBlinkSpeedValue">0.05</span>
        </div>
        <div class="control-group">
            <label for="starColorHueMin">Star Color Hue Min:</label>
            <input type="range" id="starColorHueMin" min="0" max="360" value="180" step="1">
            <span id="starColorHueMinValue">180</span>
        </div>
        <div class="control-group">
            <label for="starColorHueMax">Star Color Hue Max:</label>
            <input type="range" id="starColorHueMax" min="0" max="360" value="280" step="1">
            <span id="starColorHueMaxValue">280</span>
        </div>
         <div class="control-group">
            <label for="starColorSaturation">Star Color Saturation (%):</label>
            <input type="range" id="starColorSaturation" min="0" max="100" value="80" step="1">
            <span id="starColorSaturationValue">80</span>
        </div>
        <div class="control-group">
            <label for="starColorLightness">Star Color Lightness (%):</label>
            <input type="range" id="starColorLightness" min="50" max="100" value="70" step="1">
            <span id="starColorLightnessValue">70</span>
        </div>

        <hr>
        <div class="control-group">
            <label for="asteroidDensity">Asteroid Density:</label>
            <input type="range" id="asteroidDensity" min="0" max="200" value="20" step="5">
            <span id="asteroidDensityValue">20</span>
        </div>
        <div class="control-group">
            <label for="minAsteroidSize">Min Asteroid Size:</label>
            <input type="range" id="minAsteroidSize" min="2" max="20" value="5" step="1">
            <span id="minAsteroidSizeValue">5</span>
        </div>
        <div class="control-group">
            <label for="maxAsteroidSize">Max Asteroid Size:</label>
            <input type="range" id="maxAsteroidSize" min="5" max="50" value="15" step="1">
            <span id="maxAsteroidSizeValue">15</span>
        </div>
        <div class="control-group">
            <label for="minAsteroidSpeed">Min Asteroid Speed:</label>
            <input type="range" id="minAsteroidSpeed" min="0.1" max="1.5" value="0.3" step="0.1">
            <span id="minAsteroidSpeedValue">0.3</span>
        </div>
        <div class="control-group">
            <label for="maxAsteroidSpeed">Max Asteroid Speed:</label>
            <input type="range" id="maxAsteroidSpeed" min="0.3" max="3" value="0.8" step="0.1">
            <span id="maxAsteroidSpeedValue">0.8</span>
        </div>


        <hr>
        <div class="control-group">
            <label for="cometChance">Comet Chance (per frame, %):</label>
            <input type="range" id="cometChance" min="0" max="5" value="0.5" step="0.1">
            <span id="cometChanceValue">0.5</span>
        </div>
        <div class="control-group">
            <label for="cometSpeed">Comet Speed:</label>
            <input type="range" id="cometSpeed" min="1" max="10" value="5" step="0.5">
            <span id="cometSpeedValue">5</span>
        </div>

        <hr>
        <div class="control-group">
            <label for="nebulaCount">Nebula Count:</label>
            <input type="range" id="nebulaCount" min="0" max="10" value="3" step="1">
            <span id="nebulaCountValue">3</span>
        </div>
         <div class="control-group">
            <label for="nebulaOpacity">Nebula Max Opacity:</label>
            <input type="range" id="nebulaOpacity" min="0.05" max="0.5" value="0.2" step="0.01">
            <span id="nebulaOpacityValue">0.2</span>
        </div>

        <button id="resetSceneButton">Re-initialize Scene</button>
        <button id="randomizeAllButton">Randomize All & Refresh</button>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const canvas = document.getElementById('spaceCanvas');
            const ctx = canvas.getContext('2d');

            let width, height;
            let stars = [];
            let comets = [];
            let nebulas = [];
            let asteroids = [];

            // --- Mouse Drag Variables ---
            let isDragging = false;
            let lastMouseY = 0;
            let verticalOffset = 0; // This will be the total vertical pan

            // --- Control Elements (same as before) ---
            const controls = {
                starDensity: document.getElementById('starDensity'), minStarSize: document.getElementById('minStarSize'),
                maxStarSize: document.getElementById('maxStarSize'), minStarSpeed: document.getElementById('minStarSpeed'),
                maxStarSpeed: document.getElementById('maxStarSpeed'), starBlinkChance: document.getElementById('starBlinkChance'),
                starBlinkSpeed: document.getElementById('starBlinkSpeed'), starColorHueMin: document.getElementById('starColorHueMin'),
                starColorHueMax: document.getElementById('starColorHueMax'), starColorSaturation: document.getElementById('starColorSaturation'),
                starColorLightness: document.getElementById('starColorLightness'), asteroidDensity: document.getElementById('asteroidDensity'),
                minAsteroidSize: document.getElementById('minAsteroidSize'), maxAsteroidSize: document.getElementById('maxAsteroidSize'),
                minAsteroidSpeed: document.getElementById('minAsteroidSpeed'), maxAsteroidSpeed: document.getElementById('maxAsteroidSpeed'),
                cometChance: document.getElementById('cometChance'), cometSpeed: document.getElementById('cometSpeed'),
                nebulaCount: document.getElementById('nebulaCount'), nebulaOpacity: document.getElementById('nebulaOpacity'),
                resetButton: document.getElementById('resetSceneButton'), randomizeAllButton: document.getElementById('randomizeAllButton')
            };

            const controlValues = {
                starDensity:()=>parseInt(controls.starDensity.value),minStarSize:()=>parseFloat(controls.minStarSize.value),
                maxStarSize:()=>parseFloat(controls.maxStarSize.value),minStarSpeed:()=>parseFloat(controls.minStarSpeed.value),
                maxStarSpeed:()=>parseFloat(controls.maxStarSpeed.value),starBlinkChance:()=>parseInt(controls.starBlinkChance.value)/100,
                starBlinkSpeed:()=>parseFloat(controls.starBlinkSpeed.value),starColorHueMin:()=>parseInt(controls.starColorHueMin.value),
                starColorHueMax:()=>parseInt(controls.starColorHueMax.value),starColorSaturation:()=>parseInt(controls.starColorSaturation.value),
                starColorLightness:()=>parseInt(controls.starColorLightness.value),asteroidDensity:()=>parseInt(controls.asteroidDensity.value),
                minAsteroidSize:()=>parseFloat(controls.minAsteroidSize.value),maxAsteroidSize:()=>parseFloat(controls.maxAsteroidSize.value),
                minAsteroidSpeed:()=>parseFloat(controls.minAsteroidSpeed.value),maxAsteroidSpeed:()=>parseFloat(controls.maxAsteroidSpeed.value),
                cometChance:()=>parseFloat(controls.cometChance.value)/100,cometSpeed:()=>parseFloat(controls.cometSpeed.value),
                nebulaCount:()=>parseInt(controls.nebulaCount.value),nebulaOpacity:()=>parseFloat(controls.nebulaOpacity.value)
            };

            // --- Helper Functions ---
            function random(min, max) { return Math.random() * (max - min) + min; }
            function setCanvasSize() { width = canvas.width = window.innerWidth; height = canvas.height = window.innerHeight;}

            // --- Star Logic (unchanged) ---
            function createStar(x,y,size,speed,color,isBlinking,blinkPhase){return{x,y,size,speed,color,isBlinking,blinkPhase,baseAlpha:1};}
            function initStars(){stars=[];const d=controlValues.starDensity();for(let i=0;i<d;i++){const depth=random(0.1,1);const speed=controlValues.minStarSpeed()+(controlValues.maxStarSpeed()-controlValues.minStarSpeed())*depth;const size=controlValues.minStarSize()+(controlValues.maxStarSize()-controlValues.minStarSize())*depth;const hue=random(controlValues.starColorHueMin(),controlValues.starColorHueMax());const sat=controlValues.starColorSaturation();const lig=controlValues.starColorLightness();const clr=`hsla(${hue},${sat}%,${lig}%,1)`;const isB=Math.random()<controlValues.starBlinkChance();const bP=random(0,Math.PI*2);stars.push(createStar(random(0,width),random(0,height),size,speed,clr,isB,bP));}}
            function updateStar(s){s.x-=s.speed;if(s.x<-s.size){s.x=width+s.size;s.y=random(0,height);const depth=random(0.1,1);s.speed=controlValues.minStarSpeed()+(controlValues.maxStarSpeed()-controlValues.minStarSpeed())*depth;s.size=controlValues.minStarSize()+(controlValues.maxStarSize()-controlValues.minStarSize())*depth;const hue=random(controlValues.starColorHueMin(),controlValues.starColorHueMax());s.color=`hsla(${hue},${controlValues.starColorSaturation()}%,${controlValues.starColorLightness()}%,1)`;s.isBlinking=Math.random()<controlValues.starBlinkChance();s.blinkPhase=random(0,Math.PI*2);}if(s.isBlinking){s.blinkPhase+=controlValues.starBlinkSpeed();s.baseAlpha=0.75+Math.sin(s.blinkPhase)*0.25;}else{s.baseAlpha=1;}}
            function drawStar(s){const cP=s.color.match(/hsla\((\d+),\s*([\d.]+)%,\s*([\d.]+)%,\s*([\d.]+)\)/);if(cP){ctx.fillStyle=`hsla(${cP[1]},${cP[2]}%,${cP[3]}%,${s.baseAlpha})`;}else{ctx.fillStyle=s.color;}ctx.beginPath();ctx.arc(s.x,s.y,s.size/2,0,Math.PI*2);ctx.fill();}

            // --- Asteroid Logic (unchanged) ---
            function createAsteroidShape(avgS){const v=[];const nV=Math.floor(random(5,10));for(let i=0;i<nV;i++){const a=(i/nV)*Math.PI*2;const r=avgS*random(0.7,1.3);v.push({x:Math.cos(a)*r,y:Math.sin(a)*r});}return v;}
            function initAsteroids(){asteroids=[];const d=controlValues.asteroidDensity();for(let i=0;i<d;i++){const depth=random(0.1,1);const size=controlValues.minAsteroidSize()+(controlValues.maxAsteroidSize()-controlValues.minAsteroidSize())*depth;const speed=controlValues.minAsteroidSpeed()+(controlValues.maxAsteroidSpeed()-controlValues.minAsteroidSpeed())*depth;const x=random(0,width);const y=random(0,height);const angle=random(0,Math.PI*2);const rS=random(-0.02,0.02);const verts=createAsteroidShape(size/2);const l=random(20,50);const clr=`hsl(0,0%,${l}%)`;asteroids.push({x,y,size,speed,angle,rotationSpeed:rS,vertices:verts,color:clr});}}
            function updateAsteroid(a){a.x-=a.speed;a.angle+=a.rotationSpeed;if(a.x<-a.size){a.x=width+a.size;a.y=random(0,height);const depth=random(0.1,1);a.size=controlValues.minAsteroidSize()+(controlValues.maxAsteroidSize()-controlValues.minAsteroidSize())*depth;a.speed=controlValues.minAsteroidSpeed()+(controlValues.maxAsteroidSpeed()-controlValues.minAsteroidSpeed())*depth;a.vertices=createAsteroidShape(a.size/2);const l=random(20,50);a.color=`hsl(0,0%,${l}%)`;}}
            function drawAsteroid(a){ctx.save();ctx.translate(a.x,a.y);ctx.rotate(a.angle);ctx.fillStyle=a.color;ctx.beginPath();ctx.moveTo(a.vertices[0].x,a.vertices[0].y);for(let i=1;i<a.vertices.length;i++){ctx.lineTo(a.vertices[i].x,a.vertices[i].y);}ctx.closePath();ctx.fill();ctx.restore();}

            // --- Comet Logic (unchanged) ---
            function createComet(){let x,y,angle;const speed=controlValues.cometSpeed();x=width+random(50,300);y=random(0,height);angle=Math.PI+random(-0.1,0.1);const hue=random(180,240);const clr=`hsla(${hue},90%,70%,1)`;return{x,y,vx:Math.cos(angle)*speed,vy:Math.sin(angle)*speed,color:clr,tail:[]};}
            function updateComet(c){c.tail.unshift({x:c.x,y:c.y});if(c.tail.length>20+Math.abs(c.vx)*2){c.tail.pop();}c.x+=c.vx;c.y+=c.vy;}
            function drawComet(c){ctx.fillStyle=c.color;ctx.beginPath();ctx.arc(c.x,c.y,3,0,Math.PI*2);ctx.fill();c.tail.forEach((seg,idx)=>{const alpha=1-(idx/c.tail.length);const size=Math.max(0.1,(3-(idx/c.tail.length)*3));const cP=c.color.match(/hsla\((\d+),\s*([\d.]+)%,\s*([\d.]+)%,\s*([\d.]+)\)/);if(cP){ctx.fillStyle=`hsla(${cP[1]},${cP[2]}%,${cP[3]}%,${alpha*0.8})`;}ctx.beginPath();ctx.arc(seg.x,seg.y,size,0,Math.PI*2);ctx.fill();});}

            // --- Nebula Logic (unchanged) ---
            function createNebula(){const x=random(-width*0.2,width*1.2);const y=random(0,height);const rad=random(width*0.1,width*0.4);const hue=random(200,300);const sat=random(60,100);const lig=random(20,40);const maxO=controlValues.nebulaOpacity();const sX=random(-0.2,-0.05);const nL=Math.floor(random(3,7));const layers=[];for(let i=0;i<nL;i++){layers.push({oX:random(-rad*0.3,rad*0.3),oY:random(-rad*0.3,rad*0.3),r:random(rad*0.4,rad*0.8)*(1+i*0.1),oF:random(0.3,0.8)*(1-i*0.1)}); }return{x,y,radius:rad,hue,saturation:sat,lightness:lig,maxOpacity:maxO,speedX:sX,layers};}
            function initNebulas(){nebulas=[];const c=controlValues.nebulaCount();for(let i=0;i<c;i++){nebulas.push(createNebula());}}
            function updateNebula(n){n.x+=n.speedX;if(n.x+n.radius*1.5<0){n.x=width+n.radius*1.5;n.y=random(0,height);}}
            function drawNebula(n){ctx.save();ctx.globalCompositeOperation='lighter';n.layers.forEach(l=>{const gX=n.x+l.oX;const gY=n.y+l.oY;const cR=l.r;const grad=ctx.createRadialGradient(gX,gY,0,gX,gY,cR);const op=n.maxOpacity*l.oF;grad.addColorStop(0,`hsla(${n.hue+random(-20,20)},${n.saturation}%,${n.lightness+10}%,${op})`);grad.addColorStop(0.3+random(-0.1,0.1),`hsla(${n.hue+random(-10,10)},${n.saturation-5}%,${n.lightness}%,${op*0.7})`);grad.addColorStop(0.7+random(-0.1,0.1),`hsla(${n.hue},${n.saturation-10}%,${n.lightness-5}%,${op*0.3})`);grad.addColorStop(1,`hsla(${n.hue-random(0,10)},${n.saturation-15}%,${n.lightness-10}%,0)`);ctx.fillStyle=grad;ctx.beginPath();ctx.arc(gX,gY,cR,0,Math.PI*2);ctx.fill();});ctx.restore();}

            // --- Main Animation Loop ---
            function animate() {
                ctx.clearRect(0, 0, width, height);

                // Apply global vertical offset for panning
                ctx.save();
                ctx.translate(0, verticalOffset);

                nebulas.forEach(nebula => { updateNebula(nebula); drawNebula(nebula); });
                asteroids.forEach(asteroid => { updateAsteroid(asteroid); drawAsteroid(asteroid); });
                stars.forEach(star => { updateStar(star); drawStar(star); });

                if (Math.random() < controlValues.cometChance()) {
                    if (comets.length < 20) { comets.push(createComet()); }
                }
                comets = comets.filter(c => c.x > -100 - c.tail.length*5 && c.x < width+100+c.tail.length*5 && c.y > -100 && c.y < height+100);
                comets.forEach(comet => { updateComet(comet); drawComet(comet); });
                
                ctx.restore(); // Restore context after panning translation

                requestAnimationFrame(animate);
            }

            // --- Mouse Event Listeners for Panning ---
            canvas.addEventListener('mousedown', (e) => {
                isDragging = true;
                lastMouseY = e.clientY; // Use clientY for position relative to viewport
                canvas.style.cursor = 'grabbing';
                e.preventDefault(); // Prevent text selection/default drag behavior
            });

            window.addEventListener('mousemove', (e) => { // Listen on window to catch drags outside canvas
                if (isDragging) {
                    const deltaY = e.clientY - lastMouseY;
                    verticalOffset += deltaY;
                    lastMouseY = e.clientY;
                }
            });

            window.addEventListener('mouseup', () => { // Listen on window
                if (isDragging) {
                    isDragging = false;
                    canvas.style.cursor = 'grab';
                }
            });
            // Optional: Handle mouse leaving the window entirely
            document.addEventListener('mouseleave', () => {
                 if (isDragging) {
                    isDragging = false;
                    canvas.style.cursor = 'grab';
                }
            });


            // --- Event Listeners for Controls (Slider logic mostly unchanged) ---
            function setupEventListeners() {
                Object.keys(controls).forEach(key => {
                    if (key === 'resetButton' || key === 'randomizeAllButton') return; 
                    const inputElement = controls[key]; const valueElement = document.getElementById(`${key}Value`);
                    if (inputElement && valueElement) {
                        inputElement.addEventListener('input', () => {
                            valueElement.textContent = inputElement.value;
                            const pairs = [['minStarSize','maxStarSize'],['minStarSpeed','maxStarSpeed'],['starColorHueMin','starColorHueMax'],['minAsteroidSize','maxAsteroidSize'],['minAsteroidSpeed','maxAsteroidSpeed']];
                            pairs.forEach(pK => { if (key===pK[0]&&parseFloat(inputElement.value)>parseFloat(controls[pK[1]].value)){controls[pK[1]].value=inputElement.value;document.getElementById(`${pK[1]}Value`).textContent=inputElement.value;} if (key===pK[1]&&parseFloat(inputElement.value)<parseFloat(controls[pK[0]].value)){controls[pK[0]].value=inputElement.value;document.getElementById(`${pK[0]}Value`).textContent=inputElement.value;} });
                            if (key==='nebulaCount'||key==='nebulaOpacity'){initNebulas();} if (key==='asteroidDensity'){initAsteroids();}
                        });
                        if (valueElement) valueElement.textContent = inputElement.value;
                    }
                });
                controls.resetButton.addEventListener('click',()=>{initStars();initNebulas();initAsteroids();comets=[];verticalOffset=0; /* Reset pan on scene reset */});
                controls.randomizeAllButton.addEventListener('click',()=>{const sK=Object.keys(controls).filter(k=>k!=='resetButton'&&k!=='randomizeAllButton');sK.forEach(key=>{const s=controls[key];const vS=document.getElementById(`${key}Value`);const min=parseFloat(s.min);const max=parseFloat(s.max);const step=parseFloat(s.step);let rV;if(step===1||(Number.isInteger(step)&&step!==0)){const nSIR=Math.floor((max-min)/step);rV=min+(Math.floor(Math.random()*(nSIR+1))*step);}else{const dP=String(step).includes('.')?String(step).split('.')[1].length:0;const scale=Math.pow(10,dP);const mS=Math.round(min*scale);const mxS=Math.round(max*scale);const sS=Math.round(step*scale);const nSIR=Math.floor((mxS-mS)/sS);const rSC=Math.floor(Math.random()*(nSIR+1));rV=(mS+(rSC*sS))/scale;rV=parseFloat(rV.toFixed(dP));}s.value=rV;if(vS){vS.textContent=s.value;}});const pairs=[['minStarSize','maxStarSize'],['minStarSpeed','maxStarSpeed'],['starColorHueMin','starColorHueMax'],['minAsteroidSize','maxAsteroidSize'],['minAsteroidSpeed','maxAsteroidSpeed']];pairs.forEach(pK=>{const mS=controls[pK[0]];const mxS=controls[pK[1]];const mVS=document.getElementById(`${pK[0]}Value`);const mxVS=document.getElementById(`${pK[1]}Value`);if(parseFloat(mS.value)>parseFloat(mxS.value)){const t=mS.value;mS.value=mxS.value;mxS.value=t;if(mVS)mVS.textContent=mS.value;if(mxVS)mxVS.textContent=mxS.value;}});initStars();initNebulas();initAsteroids();comets=[];verticalOffset=0; /* Reset pan on randomize */});
                window.addEventListener('resize',()=>{setCanvasSize();initStars();initNebulas();initAsteroids(); /* Don't reset verticalOffset on resize */});
            }
            
            // --- Initialization ---
            setCanvasSize();
            initStars();
            initNebulas();
            initAsteroids();
            setupEventListeners();
            animate();
        });
    </script>
</body>
</html>