<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Refined 3D Planet Simulator</title>
    <style>
        body { margin: 0; overflow: hidden; background-color: #000; display: flex; font-family: Arial, sans-serif; color: #fff; }
        #leftPanel { width: 200px; background-color: rgba(10,20,40,0.85); padding: 10px; box-sizing: border-box; overflow-y: auto; height: 100vh; border-right: 1px solid #335; z-index: 1; }
        #leftPanel h3 { margin-top: 0; color: #aaccff; text-align: center; }
        #planetList button { display: flex; align-items: center; width: 100%; padding: 8px; margin-bottom: 5px; background-color: #223355; color: #ddeeff; border: 1px solid #445577; border-radius: 4px; text-align: left; cursor: pointer; font-size: 0.9em; }
        .planet-color-swatch { width: 12px; height: 12px; border-radius: 3px; margin-right: 8px; border: 1px solid rgba(255,255,255,0.2); }
        #planetList button:hover { background-color: #334466; }
        #planetList button.selected { background-color: #5577aa; font-weight: bold; border-color: #7799cc; }
        #dashboard { margin-top: 20px; padding: 10px; background-color: rgba(0,0,0,0.3); border-radius: 5px; }
        #dashboard button { padding: 7px 10px; margin: 3px; background-color: #444; color: #fff; border: none; border-radius: 3px; cursor: pointer; width: calc(50% - 6px); }
        #dashboard button:hover { background-color: #666; }
        #speedIndicator { margin-top: 10px; font-size: 0.9em; text-align: center; }
        canvas { display: block; background-color: #000; flex-grow: 1; }
        #planetInfoPopup { position: fixed; background-color: rgba(20,20,30,0.95); color: #eee; border: 1px solid #557; border-radius: 8px; padding: 15px; width: 250px; box-shadow: 0 0 15px rgba(100,150,255,0.3); z-index: 20; font-size: 0.9em; }
        #planetInfoPopup h3 { margin-top: 0; color: #aaf; } #planetInfoPopup p { margin: 5px 0; }
        .close-popup-btn { display: block; margin: 10px auto 0 auto; }
    </style>
</head>
<body>
    <div id="leftPanel"><h3>Solar System</h3><div id="planetList"></div><div id="dashboard"><button id="pauseBtn">Pause</button><button id="resetViewBtn">Reset View</button><button id="slowDownBtn">« Slower</button><button id="speedUpBtn">Faster »</button><div id="speedIndicator">Speed: 1.0x</div></div></div>
    <canvas id="planetCanvas"></canvas>
    <div id="planetInfoPopup" style="display:none;"><h3 id="popupPlanetName"></h3><p id="popupPlanetDesc"></p><p>Avg. Radius: <span id="popupPlanetRadius"></span> km</p><p>Avg. Distance from Sun: <span id="popupPlanetDistance"></span> AU</p><button class="close-popup-btn">Close</button></div>

    <script>
        const canvas = document.getElementById('planetCanvas');
        const ctx = canvas.getContext('2d');
        const planetListDiv = document.getElementById('planetList');

        let canvasWidth = window.innerWidth - 200; 
        let canvasHeight = window.innerHeight;
        canvas.width = canvasWidth; canvas.height = canvasHeight;

        const SUN_RADIUS_KM = 695700; 
        const BASE_PLANET_SCALE = 0.00005 * 7; 
        const SUN_DISPLAY_RADIUS_FACTOR = 0.4;
        const ORBIT_DISTANCE_SCALE = 1.0; 
        let timeMultiplier = 32.0; 
        let isPaused = false; let selectedPlanet = null; 

        let viewScale = 0.3; 
        let targetOffsetX = canvasWidth / 2; let targetOffsetY = canvasHeight / 2;
        let currentOffsetX = canvasWidth / 2; let currentOffsetY = canvasHeight / 2;
        let cameraAlpha = 0; let cameraBeta = 0.25; 
        const PERSPECTIVE_STRENGTH = 600; const SLEW_RATE = 0.05; 

        const numStars = 600; const stars = [];
        for (let i = 0; i < numStars; i++) {
            const dist = Math.random()*2500+1500; const angle = Math.random()*Math.PI*2; const heightAngle = (Math.random()-0.5)*Math.PI;
            stars.push({ x:dist*Math.cos(angle)*Math.cos(heightAngle), y:dist*Math.sin(heightAngle), z:dist*Math.sin(angle)*Math.cos(heightAngle), size:Math.random()*1.8+0.6, opacity:Math.random()*0.5+0.3 });
        }
        
        function adjustColorForOrbit(baseColor, alphaFactor, brightnessFactor = 1) {
            const match = baseColor.match(/rgba?\((\d+),\s*(\d+),\s*(\d+)(?:,\s*([\d.]+))?\)/);
            if (match) {
                let r = parseInt(match[1]); let g = parseInt(match[2]); let b = parseInt(match[3]);
                r = Math.min(255, Math.max(0, Math.round(r * brightnessFactor)));
                g = Math.min(255, Math.max(0, Math.round(g * brightnessFactor)));
                b = Math.min(255, Math.max(0, Math.round(b * brightnessFactor)));
                return `rgba(${r},${g},${b},${alphaFactor.toFixed(2)})`;
            }
            return `rgba(200,200,200,${alphaFactor.toFixed(2)})`;
        }

        const planetsData = [ 
             { name: "Sun", radiusKm: SUN_RADIUS_KM, color: "rgba(255,220,100,1)", orbitalRadius: 0, period: 0, isSun: true,
              description: "The star at the center of our Solar System.", distanceFromSunAU: 0,
              particleSettings: { count: 150, speed: 2.0, lifetime: 150, size: 2.0, color: "rgba(255,120,0,0.6)" } },
            { name: "Mercury", radiusKm: 2439.7, color: "rgba(160,155,150,1)", orbitalRadius: 55, period: 0.24,
              description: "The smallest planet and closest to the Sun.", distanceFromSunAU: 0.39,
              particleSettings: { count: 20, speed: 0.3, lifetime: 80, size: 1, color: "rgba(180,180,170,0.5)" } },
            { name: "Venus", radiusKm: 6051.8, color: "rgba(230,200,150,1)", orbitalRadius: 90, period: 0.62,
              description: "Known for its thick, toxic atmosphere and extreme temperatures.", distanceFromSunAU: 0.72,
              particleSettings: { count: 30, speed: 0.4, lifetime: 100, size: 1.5, color: "rgba(240,220,180,0.5)" } },
            { name: "Earth", radiusKm: 6371, color: "rgba(100,150,255,1)", orbitalRadius: 130, period: 1,
              description: "Our home planet, the only known celestial body to harbor life.", distanceFromSunAU: 1.00,
              particleSettings: { count: 40, speed: 0.5, lifetime: 120, size: 1.5, color: "rgba(150,200,255,0.6)" } },
            { name: "Mars", radiusKm: 3389.5, color: "rgba(200,100,50,1)", orbitalRadius: 200, period: 1.88,
              description: "The 'Red Planet', known for its rusty appearance and potential for past life.", distanceFromSunAU: 1.52,
              particleSettings: { count: 25, speed: 0.3, lifetime: 90, size: 1, color: "rgba(220,120,80,0.5)" } },
            { name: "Jupiter", radiusKm: 69911, color: "rgba(200,180,150,1)", orbitalRadius: 450, period: 11.86,
              description: "The largest planet, a gas giant with a Great Red Spot.", distanceFromSunAU: 5.20,
              particleSettings: { count: 100, speed: 0.8, lifetime: 200, size: 2.5, color: "rgba(220,200,170,0.4)" } },
            { name: "Saturn", radiusKm: 58232, color: "rgba(220,210,180,1)", orbitalRadius: 700, period: 29.46, hasRings: true,
              description: "Famous for its spectacular ring system, composed mostly of ice particles.", distanceFromSunAU: 9.58,
              particleSettings: { count: 80, speed: 0.6, lifetime: 180, size: 2, color: "rgba(230,220,190,0.4)" } },
            { name: "Uranus", radiusKm: 25362, color: "rgba(180,220,230,1)", orbitalRadius: 980, period: 84.01,
              description: "An ice giant tilted on its side, giving it unique seasons.", distanceFromSunAU: 19.22,
              particleSettings: { count: 60, speed: 0.5, lifetime: 150, size: 1.8, color: "rgba(200,230,240,0.5)" } },
            { name: "Neptune", radiusKm: 24622, color: "rgba(100,120,200,1)", orbitalRadius: 1250, period: 164.8,
              description: "The most distant planet, a cold and stormy ice giant.", distanceFromSunAU: 30.05,
              particleSettings: { count: 50, speed: 0.4, lifetime: 160, size: 1.7, color: "rgba(120,140,220,0.5)" } }
        ];

        function transformPoint(x,y,z,focusX=0,focusY=0,focusZ=0){ const dx=x-focusX;const dy=y-focusY;const dz=z-focusZ; let x1=dx*Math.cos(cameraAlpha)-dz*Math.sin(cameraAlpha); let z1=dx*Math.sin(cameraAlpha)+dz*Math.cos(cameraAlpha); let y_rotated=dy*Math.cos(cameraBeta)-z1*Math.sin(cameraBeta); let z_rotated=dy*Math.sin(cameraBeta)+z1*Math.cos(cameraBeta); let pF=PERSPECTIVE_STRENGTH/(PERSPECTIVE_STRENGTH+z_rotated);pF=Math.max(0.01,Math.min(pF,10)); return{x:x1*pF,y:y_rotated*pF,z:z_rotated,pFactor:pF};}
        class Particle { 
            constructor(pX,pY,pZ,pR,s){this.pR=pR;this.s=s;const a=Math.random()*Math.PI*2;const eA=(Math.random()-0.5)*Math.PI;const sD=this.pR+Math.random()*(this.s.size*2);this.rX=Math.cos(a)*Math.cos(eA)*sD;this.rY=Math.sin(eA)*sD;this.rZ=Math.sin(a)*Math.cos(eA)*sD;this.wX=pX+this.rX;this.wY=pY+this.rY;this.wZ=pZ+this.rZ;const sF=(Math.random()*0.5+0.75);this.vx=(Math.random()-0.5)*2*this.s.speed*sF;this.vy=(Math.random()-0.5)*2*this.s.speed*sF;this.vz=(Math.random()-0.5)*2*this.s.speed*sF;if(s.isSunParticle){const sM=1+Math.random()*0.5;const dX=this.rX/(sD||1);const dY=this.rY/(sD||1);const dZ=this.rZ/(sD||1);this.vx=dX*this.s.speed*sM;this.vy=dY*this.s.speed*sM;this.vz=dZ*this.s.speed*sM;}this.l=this.s.lifetime*(Math.random()*0.5+0.75);this.iL=this.l;this.size=this.s.size*(Math.random()*0.4+0.8);this.c=this.s.color;this.z_r=0;}
            update(pX,pY,pZ,dT){const sDT=dT*timeMultiplier;if(isPaused&&!this.s.isSunParticle)return;if(!this.s.isSunParticle){this.wX=pX+this.rX;this.wY=pY+this.rY;this.wZ=pZ+this.rZ;const pF=0.01+Math.random()*0.01;this.rX+=((Math.random()-0.5)*0.2-(this.rX/(this.pR+1))*pF)*sDT*60;this.rY+=((Math.random()-0.5)*0.2-(this.rY/(this.pR+1))*pF)*sDT*60;this.rZ+=((Math.random()-0.5)*0.2-(this.rZ/(this.pR+1))*pF)*sDT*60;}else{this.wX+=this.vx*sDT;this.wY+=this.vy*sDT;this.wZ+=this.vz*sDT;this.vx*=0.998;this.vy*=0.998;this.vz*=0.998;}this.l-=dT;}
            draw(ctx,fP){const t=transformPoint(this.wX,this.wY,this.wZ,fP.x,fP.y,fP.z);this.z_r=t.z;const aF=this.s.isSunParticle?0.7:0.9;let a=Math.max(0,(this.l/this.iL)*parseFloat(this.c.split(',')[3]||'1)')*aF);const pSS=Math.max(0.2,this.size*t.pFactor*viewScale);if(pSS<0.2&&a<0.05)return;if(t.z<-PERSPECTIVE_STRENGTH*0.95)return;const cP=this.c.split(',');ctx.fillStyle=`${cP[0]},${cP[1]},${cP[2]},${a.toFixed(3)})`;ctx.beginPath();ctx.arc(t.x*viewScale+currentOffsetX,t.y*viewScale+currentOffsetY,pSS,0,Math.PI*2);ctx.fill();}
        }

        class Planet {
            constructor(data) { 
                this.id=data.name.toLowerCase().replace(/\s+/g,'-');this.name=data.name;this.actualRadiusKm=data.radiusKm;this.orbitalRadius=data.orbitalRadius===0?0:(data.orbitalRadius*ORBIT_DISTANCE_SCALE);this.displayRadius=data.isSun?SUN_RADIUS_KM*BASE_PLANET_SCALE*SUN_DISPLAY_RADIUS_FACTOR:Math.max(1.5,this.actualRadiusKm*BASE_PLANET_SCALE);this.color=data.color;this.period=data.period;this.angle=Math.random()*Math.PI*2;this.speed=this.period>0?(1/(this.period*50))*100:0;this.isSun=data.isSun||false;this.hasRings=data.hasRings||false;this.description=data.description||"A celestial body.";this.distanceFromSunAU=data.distanceFromSunAU||"N/A";this.x=0;this.y=0;this.z=0;this.z_rotated=0;this.particles=[];this.particleSettings=data.particleSettings||{count:0};this.particleSettings.isSunParticle=this.isSun;
            }
            update(dT){ 
                const sDT=dT*timeMultiplier;if(isPaused&&!this.isSun)return;if(!this.isSun){this.angle+=this.speed*sDT*0.01;this.x=Math.cos(this.angle)*this.orbitalRadius;this.z=Math.sin(this.angle)*this.orbitalRadius;this.y=0;}else{this.x=0;this.y=0;this.z=0;}for(let i=this.particles.length-1;i>=0;i--){this.particles[i].update(this.x,this.y,this.z,dT);if(this.particles[i].l<=0){this.particles.splice(i,1);}}if(this.particles.length<this.particleSettings.count&&Math.random()<0.7){const nTE=Math.min(4,this.particleSettings.count-this.particles.length);for(let i=0;i<nTE;i++){this.particles.push(new Particle(this.x,this.y,this.z,this.displayRadius,this.particleSettings));}}
            }
            draw(ctx,fP){ 
                const t=transformPoint(this.x,this.y,this.z,fP.x,fP.y,fP.z);this.z_rotated=t.z;const sX=t.x*viewScale+currentOffsetX;const sY=t.y*viewScale+currentOffsetY;let pSR=Math.max(0.5,this.displayRadius*t.pFactor*viewScale);if(pSR<0.2||t.z<-PERSPECTIVE_STRENGTH*0.95)return;if(selectedPlanet===this){pSR*=1.05;}if(this.isSun){const sGR=pSR*1.5;const sGrad=ctx.createRadialGradient(sX,sY,pSR*0.1,sX,sY,sGR);const bSC=this.color.substring(0,this.color.lastIndexOf(','))+',';sGrad.addColorStop(0,bSC+"1)");sGrad.addColorStop(0.3,bSC+"0.9)");sGrad.addColorStop(0.6,bSC+"0.5)");sGrad.addColorStop(0.8,bSC+"0.2)");sGrad.addColorStop(1,bSC+"0)");ctx.fillStyle=sGrad;ctx.beginPath();ctx.arc(sX,sY,sGR,0,Math.PI*2);ctx.fill();}else{const lAV=Math.atan2(sY-currentOffsetY,sX-currentOffsetX)+Math.PI;const hOX=Math.cos(lAV)*pSR*0.35;const hOY=Math.sin(lAV)*pSR*0.35;const pGrad=ctx.createRadialGradient(sX+hOX,sY+hOY,pSR*0.05,sX,sY,pSR);const cM=this.color.match(/rgba?\((\d+),\s*(\d+),\s*(\d+)(?:,\s*([\d.]+))?\)/);let r=0,g=0,b=0;if(cM){r=parseInt(cM[1]);g=parseInt(cM[2]);b=parseInt(cM[3]);}const hC=`rgba(${Math.min(255,r+70)},${Math.min(255,g+70)},${Math.min(255,b+70)},1)`;const sC=`rgba(${Math.max(0,r-60)},${Math.max(0,g-60)},${Math.max(0,b-60)},1)`;pGrad.addColorStop(0,hC);pGrad.addColorStop(0.5,this.color);pGrad.addColorStop(1,sC);ctx.fillStyle=pGrad;ctx.beginPath();ctx.arc(sX,sY,pSR,0,Math.PI*2);ctx.fill();}if(this.hasRings){ctx.save();const rOS=t.pFactor*viewScale;const rS=[{i:1.7,o:2.4,c:"rgba(200,190,160,0.6)"},{i:1.3,o:1.65,c:"rgba(180,170,140,0.5)"},{i:1.1,o:1.25,c:"rgba(160,150,120,0.4)"}];for(const r of rS){ctx.fillStyle=r.c;ctx.beginPath();ctx.ellipse(sX,sY,this.displayRadius*r.o*rOS,this.displayRadius*r.o*rOS*Math.abs(Math.cos(cameraBeta)),-cameraAlpha,0,Math.PI*2);ctx.ellipse(sX,sY,this.displayRadius*r.i*rOS,this.displayRadius*r.i*rOS*Math.abs(Math.cos(cameraBeta)),-cameraAlpha,0,Math.PI*2,true);ctx.fill();}ctx.restore();}
            }

            // <<< CORRECTED drawOrbit method >>>
            drawOrbit(ctx, focusPoint) {
                if (!this.isSun && this.orbitalRadius > 0) {
                    let orbitColorAlpha = 0.3;
                    let orbitBrightness = 0.8; 
                    let orbitWidthFactor = 1.0;
                    let shadowAlphaFactor = 0.4;
                    
                    if (selectedPlanet === this) {
                        orbitColorAlpha = 0.75; 
                        orbitBrightness = 1.2;  
                        orbitWidthFactor = 2.2;
                        shadowAlphaFactor = 0.6;
                    }

                    const baseOrbitColor = adjustColorForOrbit(this.color, orbitColorAlpha, orbitBrightness);
                    const shadowColor = adjustColorForOrbit(this.color, shadowAlphaFactor, orbitBrightness * 1.1);

                    let orbitWidth = Math.max(0.4, (1.0 / viewScale) * orbitWidthFactor);
                    let shadowBlur = Math.min(12, Math.max(1.5, (5 / viewScale) * (orbitWidthFactor * 0.8) ));

                    ctx.save(); 
                    ctx.shadowBlur = shadowBlur;
                    ctx.shadowColor = shadowColor;
                    
                    ctx.strokeStyle = baseOrbitColor;
                    ctx.lineWidth = orbitWidth;
                    
                    ctx.beginPath(); // Start a new path for the entire orbit
                    let firstSegmentPointDrawn = false; // Flag to track if moveTo has been called for the current segment

                    const segments = 100;
                    for (let i = 0; i <= segments; i++) {
                        const angle = (i / segments) * Math.PI * 2;
                        const worldX = Math.cos(angle) * this.orbitalRadius;
                        const worldZ = Math.sin(angle) * this.orbitalRadius; 
                        const transformed = transformPoint(worldX, 0, worldZ, focusPoint.x, focusPoint.y, focusPoint.z);
                        const sx = transformed.x * viewScale + currentOffsetX;
                        const sy = transformed.y * viewScale + currentOffsetY;

                        if (transformed.z < -PERSPECTIVE_STRENGTH * 0.98) {
                            // If a point is culled, reset the flag so the next valid point starts a new line segment (moveTo)
                            // This prevents trying to draw a line from a previous visible point to a new visible point
                            // if there was an invisible (culled) segment in between.
                            if (firstSegmentPointDrawn) { // If we were drawing a segment, stroke it before breaking
                                ctx.stroke();
                                ctx.beginPath(); // Prepare for a new potential segment
                            }
                            firstSegmentPointDrawn = false; 
                            continue;
                        }

                        if (!firstSegmentPointDrawn) {
                            ctx.moveTo(sx, sy);
                            firstSegmentPointDrawn = true;
                        } else {
                            ctx.lineTo(sx, sy);
                        }
                    }
                    
                    // Stroke any path that was built up and not yet stroked due to culling logic
                    if (firstSegmentPointDrawn) { 
                        ctx.stroke(); 
                    }
                    ctx.restore(); 
                }
            }
            isClicked(mX,mY,fP){const t=transformPoint(this.x,this.y,this.z,fP.x,fP.y,fP.z);const sX=t.x*viewScale+currentOffsetX;const sY=t.y*viewScale+currentOffsetY;const pSR=this.displayRadius*t.pFactor*viewScale*(selectedPlanet===this?1.05:1);const dSq=(mX-sX)*(mX-sX)+(mY-sY)*(mY-sY);return dSq<=pSR*pSR;}
        }

        const celestialBodies = planetsData.map(data => new Planet(data));
        const sun = celestialBodies.find(p => p.isSun);
        selectedPlanet = sun; 

        const pauseBtn=document.getElementById('pauseBtn'); const slowDownBtn=document.getElementById('slowDownBtn'); const speedUpBtn=document.getElementById('speedUpBtn'); const resetViewBtn=document.getElementById('resetViewBtn'); const speedIndicator=document.getElementById('speedIndicator'); const planetInfoPopup=document.getElementById('planetInfoPopup'); const popupPlanetName=document.getElementById('popupPlanetName'); const popupPlanetDesc=document.getElementById('popupPlanetDesc'); const popupPlanetRadius=document.getElementById('popupPlanetRadius'); const popupPlanetDistance=document.getElementById('popupPlanetDistance');
        document.querySelectorAll('.close-popup-btn').forEach(btn=>{btn.onclick=()=>{planetInfoPopup.style.display='none';}});
        function updateSpeedIndicator(){speedIndicator.textContent=`Speed: ${timeMultiplier.toFixed(timeMultiplier<1?1:0)}x ${isPaused?"(Paused)":""}`;}
        pauseBtn.addEventListener('click',()=>{isPaused=!isPaused;pauseBtn.textContent=isPaused?"Play":"Pause";updateSpeedIndicator();});
        slowDownBtn.addEventListener('click',()=>{timeMultiplier=Math.max(0.1,timeMultiplier/1.5);updateSpeedIndicator();});
        speedUpBtn.addEventListener('click',()=>{timeMultiplier=Math.min(64,timeMultiplier*1.5);updateSpeedIndicator();});
        resetViewBtn.addEventListener('click',()=>{setSelectedPlanet(sun);cameraAlpha=0;cameraBeta=0.25;viewScale=0.3;});

        celestialBodies.forEach(planet => { 
            if (planet.isSun) return; 
            const button = document.createElement('button');
            const swatch = document.createElement('div');
            swatch.className = 'planet-color-swatch';
            swatch.style.backgroundColor = planet.color;
            button.appendChild(swatch);
            const nameSpan = document.createElement('span');
            nameSpan.textContent = planet.name;
            button.appendChild(nameSpan);
            button.id = `btn-${planet.id}`;
            button.addEventListener('click', () => { setSelectedPlanet(planet); });
            planetListDiv.appendChild(button);
        });

        function setSelectedPlanet(planet){if(selectedPlanet&&selectedPlanet.id!==planet.id){const oldBtn=document.getElementById(`btn-${selectedPlanet.id}`);if(oldBtn)oldBtn.classList.remove('selected');}selectedPlanet=planet;if(!planet.isSun){const newBtn=document.getElementById(`btn-${planet.id}`);if(newBtn)newBtn.classList.add('selected');}else{document.querySelectorAll('#planetList button.selected').forEach(b=>b.classList.remove('selected'));}planetInfoPopup.style.display='none';}
        setSelectedPlanet(sun); 

        let isDragging=false;let lastMouseX,lastMouseY;let isPanning=false;let draggedSinceMouseDown=false;
        canvas.addEventListener('mousedown',(e)=>{if(e.button===0){isDragging=true;isPanning=e.shiftKey;lastMouseX=e.clientX;lastMouseY=e.clientY;canvas.style.cursor=isPanning?'grabbing':'move';draggedSinceMouseDown=false;setTimeout(()=>{if(!draggedSinceMouseDown&&!isPanning){handlePlanetClick(e.clientX,e.clientY);}},100);}});
        canvas.addEventListener('mousemove',(e)=>{if(isDragging){draggedSinceMouseDown=true;const dX=e.clientX-lastMouseX;const dY=e.clientY-lastMouseY;if(isPanning){targetOffsetX+=dX;targetOffsetY+=dY;}else{cameraAlpha-=dX*0.005;cameraBeta-=dY*0.005;cameraBeta=Math.max(-Math.PI/2+0.05,Math.min(Math.PI/2-0.05,cameraBeta));}lastMouseX=e.clientX;lastMouseY=e.clientY;}});
        document.addEventListener('mouseup',(e)=>{if(e.button===0){isDragging=false;canvas.style.cursor='grab';}});
        canvas.addEventListener('wheel',(e)=>{e.preventDefault();const zI=0.1;const s=e.deltaY<0?(1+zI):(1-zI);const fP=selectedPlanet||{x:0,y:0,z:0};const tF=transformPoint(fP.x,fP.y,fP.z,fP.x,fP.y,fP.z);const mXS=e.clientX-currentOffsetX;const mYS=e.clientY-currentOffsetY;const mXW=mXS/viewScale-tF.x;const mYW=mYS/viewScale-tF.y;viewScale*=s;viewScale=Math.max(0.005,Math.min(viewScale,100));targetOffsetX=e.clientX-(mXW+tF.x)*viewScale;targetOffsetY=e.clientY-(mYW+tF.y)*viewScale;});
        function handlePlanetClick(cX,cY){const fP=selectedPlanet||{x:0,y:0,z:0};const sFC=[...celestialBodies].sort((a,b)=>{const tA=transformPoint(a.x,a.y,a.z,fP.x,fP.y,fP.z);const tB=transformPoint(b.x,b.y,b.z,fP.x,fP.y,fP.z);return tA.z-tB.z;});let cB=null;for(const p of sFC){if(p.isClicked(cX,cY,fP)){cB=p;break;}}if(cB){setSelectedPlanet(cB);if(!cB.isSun){popupPlanetName.textContent=cB.name;popupPlanetDesc.textContent=cB.description;popupPlanetRadius.textContent=cB.actualRadiusKm.toLocaleString();popupPlanetDistance.textContent=cB.distanceFromSunAU.toLocaleString()+(cB.distanceFromSunAU===0?"":" AU");planetInfoPopup.style.left=`${cX+15}px`;planetInfoPopup.style.top=`${cY+15}px`;if(cX+15+planetInfoPopup.offsetWidth>canvas.offsetLeft+canvasWidth){planetInfoPopup.style.left=`${cX-15-planetInfoPopup.offsetWidth}px`;}if(cY+15+planetInfoPopup.offsetHeight>canvasHeight){planetInfoPopup.style.top=`${cY-15-planetInfoPopup.offsetHeight}px`;}planetInfoPopup.style.display='block';}}else{planetInfoPopup.style.display='none';}}
        
        let lastTime=0;
        function animate(cT){const now=performance.now();const dT=Math.min(0.1,(now-(lastTime||now))/16.666);lastTime=now;const fP=selectedPlanet||sun;targetOffsetX=canvasWidth/2;targetOffsetY=canvasHeight/2;currentOffsetX+=(targetOffsetX-currentOffsetX)*SLEW_RATE;currentOffsetY+=(targetOffsetY-currentOffsetY)*SLEW_RATE;ctx.fillStyle='#000';ctx.fillRect(0,0,canvasWidth,canvasHeight);stars.forEach(s=>{const tS=transformPoint(s.x,s.y,s.z,fP.x,fP.y,fP.z);if(tS.z<-PERSPECTIVE_STRENGTH*0.9)return;const sSS=s.size*tS.pFactor*Math.min(1.5,viewScale*0.8+0.2);if(sSS<0.1)return;const tw=Math.sin(now*0.0002*(s.x%10+1)+s.y)*0.25+0.75;ctx.fillStyle=`rgba(255,255,255,${s.opacity*tw*Math.min(1,tS.pFactor*1.5)})`;ctx.beginPath();ctx.arc(tS.x*viewScale+currentOffsetX,tS.y*viewScale+currentOffsetY,sSS,0,Math.PI*2);ctx.fill();});celestialBodies.forEach(p=>p.update(dT));const sTD=[...celestialBodies].sort((a,b)=>{const tA=transformPoint(a.x,a.y,a.z,fP.x,fP.y,fP.z);const tB=transformPoint(b.x,b.y,b.z,fP.x,fP.y,fP.z);return tB.z-tA.z;});sTD.forEach(p=>p.drawOrbit(ctx,fP));if(sun)sun.particles.forEach(p=>p.draw(ctx,fP));sTD.forEach(p=>p.draw(ctx,fP));sTD.forEach(p=>{if(!p.isSun)p.particles.forEach(p=>p.draw(ctx,fP));});requestAnimationFrame(animate);}
        window.addEventListener('resize',()=>{const pW=document.getElementById('leftPanel').offsetWidth;canvasWidth=window.innerWidth-pW;canvasHeight=window.innerHeight;canvas.width=canvasWidth;canvas.height=canvasHeight;});
        updateSpeedIndicator();requestAnimationFrame(animate);
    </script>
</body>
</html>