<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tower Defense: Full WebAudio & Gameplay</title>
  <style>
    body { margin:0; overflow:hidden; background:#000; color:#0f0; font-family:sans-serif; }
    #controls { position:absolute; top:10px; left:10px; z-index:10; }
    #controls input, #controls label { margin-right:10px; }
    #hud { position:absolute; top:70px; left:10px; z-index:10; font-size:16px; }
    canvas { display:block; margin:0 auto; background:#fff; cursor:crosshair; }
  </style>
</head>
<body>
  <div id="controls">
    <label><input type="radio" name="towerType" value="basic" checked> Basic</label>
    <label><input type="radio" name="towerType" value="sniper"> Sniper</label>
    <label><input type="radio" name="towerType" value="frost"> Frost</label>
    <label><input type="checkbox" id="muteCheckbox"> Mute</label><br><br>
    Spawn Rate: <input id="spawnSlider" type="range" min="0.2" max="3" step="0.1" value="1"> eyeball/sec
    Movement Rate: <input id="moveSlider" type="range" min="10" max="200" step="10" value="50"> px/s
  </div>
  <div id="hud">Lives: <span id="lives">5</span> | Score: <span id="score">0</span></div>
  <canvas id="monsterCanvas" width="800" height="600"></canvas>
  <script>
    // Web Audio Setup
    const audioCtx = new (window.AudioContext||window.webkitAudioContext)();
    let muted = false;
    document.getElementById('muteCheckbox').addEventListener('change', e => muted = e.target.checked);

    function playGunshot() {
      if (muted) return;
      const dur = 0.15;
      const bufSize = audioCtx.sampleRate * dur;
      const buf = audioCtx.createBuffer(1, bufSize, audioCtx.sampleRate);
      const data = buf.getChannelData(0);
      for (let i=0;i<bufSize;i++) data[i] = (Math.random()*2-1)*Math.exp(-5*i/bufSize);
      const src = audioCtx.createBufferSource(); src.buffer = buf;
      const filter = audioCtx.createBiquadFilter(); filter.type='bandpass'; filter.frequency.value=1000;
      const gain = audioCtx.createGain(); gain.gain.setValueAtTime(1,audioCtx.currentTime);
      gain.gain.exponentialRampToValueAtTime(0.01,audioCtx.currentTime+dur);
      src.connect(filter).connect(gain).connect(audioCtx.destination);
      src.start(); src.stop(audioCtx.currentTime+dur);
    }
    function playExplosion() {
      if (muted) return;
      const dur = 0.5;
      const bufSize = audioCtx.sampleRate * dur;
      const buf = audioCtx.createBuffer(1, bufSize, audioCtx.sampleRate);
      const data = buf.getChannelData(0);
      for (let i=0;i<bufSize;i++) data[i] = (Math.random()*2-1)*Math.exp(-3*i/bufSize);
      const src = audioCtx.createBufferSource(); src.buffer = buf;
      const filter = audioCtx.createBiquadFilter(); filter.type='lowpass'; filter.frequency.value=800;
      const gain = audioCtx.createGain(); gain.gain.setValueAtTime(1,audioCtx.currentTime);
      gain.gain.exponentialRampToValueAtTime(0.01,audioCtx.currentTime+dur);
      src.connect(filter).connect(gain).connect(audioCtx.destination);
      src.start(); src.stop(audioCtx.currentTime+dur);
    }

    const basicFireSound = playGunshot;
    const basicHitSound  = playExplosion;
    const sniperFireSound= playGunshot;
    const sniperHitSound = playExplosion;
    const frostFireSound = playGunshot;
    const frostHitSound  = playExplosion;

    // Canvas & Input
    const canvas=document.getElementById('monsterCanvas'),ctx=canvas.getContext('2d');
    let mouseX=null,mouseY=null;
    canvas.addEventListener('mousemove',e=>{const r=canvas.getBoundingClientRect();mouseX=e.clientX-r.left;mouseY=e.clientY-r.top;});

    // Controls
    const spawnSlider=document.getElementById('spawnSlider'), moveSlider=document.getElementById('moveSlider');
    let spawnRate=+spawnSlider.value, moveSpeed=+moveSlider.value;
    spawnSlider.oninput=()=>spawnRate=+spawnSlider.value;
    moveSlider.oninput=()=>moveSpeed=+moveSlider.value;
    let selectedTower='basic';
    document.querySelectorAll('input[name="towerType"]').forEach(i=>i.onchange=()=>selectedTower=i.value);

    // Path
    const EYE_R=22.5,pathPoints=[];
    for(let i=0;i<=6;i++){pathPoints.push({x:100+Math.random()*(canvas.width-200),y:canvas.height/6*i});}
    const pathW=80,maxEyeballs=8;

    // Game State
    const eyeballs=[],towers=[],bullets=[],trailParticles=[],deathEffects=[];
    let spawnTimer=0,lastTime,lives=5,score=0;

    class Particle{constructor(x,y,vx,vy,size,color,life,type){Object.assign(this,{x,y,vx,vy,size,color,life,age:0,type});}update(dt){this.age+=dt;this.x+=this.vx*dt;this.y+=this.vy*dt;return this.age<this.life;}draw(){const t=1-this.age/this.life;ctx.fillStyle=`rgba(${this.color},${t})`;if(this.type==='spark'||this.type==='mist'){ctx.beginPath();ctx.arc(this.x,this.y,this.size*(this.type==='spark'?t:0.5+t),0,2*Math.PI);ctx.fill();}else ctx.fillRect(this.x,this.y,this.size,this.size);}}

    class Bullet{constructor(x,y,vx,vy,dmg,slow,color,fireFn,hitFn){Object.assign(this,{x,y,vx,vy,size:4,dmg,slow,color,age:0,fireFn,hitFn});this.fireFn();}update(dt){this.age+=dt;this.x+=this.vx*dt;this.y+=this.vy*dt;if(this.dmg>0){const tp=this.color==='255,0,0'?'shard':'spark';trailParticles.push(new Particle(this.x,this.y,-this.vx*0.1,-this.vy*0.1,2,this.color,0.3,tp));}else trailParticles.push(new Particle(this.x,this.y,0,0,5,this.color,0.5,'mist'));}draw(){if(this.color==='255,0,0'){ctx.save();ctx.translate(this.x,this.y);ctx.rotate(Math.atan2(this.vy,this.vx));ctx.fillStyle='#f00';ctx.fillRect(-this.size*2,-2,this.size*4,4);ctx.beginPath();ctx.arc(this.size*2,0,this.size,0,2*Math.PI);ctx.fill();ctx.restore();}else if(this.dmg>0){const rad=this.size*2;const grad=ctx.createRadialGradient(this.x,this.y,0,this.x,this.y,rad);grad.addColorStop(0,`rgba(${this.color},1)`);grad.addColorStop(1,`rgba(${this.color},0)`);ctx.fillStyle=grad;ctx.beginPath();ctx.arc(this.x,this.y,rad,0,2*Math.PI);ctx.fill();}else{ctx.save();ctx.translate(this.x,this.y);ctx.rotate(Math.atan2(this.vy,this.vx));ctx.fillStyle='#0cf';ctx.beginPath();ctx.moveTo(-2,-4);ctx.lineTo(8,0);ctx.lineTo(-2,4);ctx.closePath();ctx.fill();ctx.restore();}}}

    class DeathEffect{constructor(x,y,vx,vy,cr,cg,cb){this.cr=cr;this.cg=cg;this.cb=cb;this.particles=[];for(let i=0;i<20;i++){const a=Math.random()*2*Math.PI,s=50+Math.random()*100;this.particles.push({x,y,vx:vx*0.5+Math.cos(a)*s,vy:vy*0.5+Math.sin(a)*s,size:4+Math.random()*4,life:0.5+Math.random()*0.5,age:0,type:'goo'});}for(let i=0;i<50;i++){const px=x+(Math.random()-0.5)*EYE_R*2,py=y+(Math.random()-0.5)*EYE_R*2;this.particles.push({x:px,y:py,vx:(Math.random()-0.5)*100,vy:-Math.random()*100,size:3,life:0.7+Math.random()*0.5,age:0,type:'pixel'});}}update(dt){this.particles.forEach(p=>{p.age+=dt;p.x+=p.vx*dt;p.y+=p.vy*dt});this.particles=this.particles.filter(p=>p.age<p.life);return this.particles.length>0;}draw(){this.particles.forEach(p=>{const t=1-p.age/p.life;ctx.fillStyle=`rgba(${this.cr},${this.cg},${this.cb},${t})`;if(p.type==='goo'){ctx.beginPath();ctx.arc(p.x,p.y,p.size*t,0,2*Math.PI);ctx.fill();}else ctx.fillRect(p.x,p.y,p.size,p.size);});}}

    class Tower{constructor(x,y,range,rate){Object.assign(this,{x,y,range,rate,timer:0,angle:0});}update(dt){const inR=eyeballs.filter(e=>Math.hypot(e.x-this.x,e.y-this.y)<this.range);if(!inR.length)return;const tgt=inR.reduce((a,b)=>( (a.x-this.x)**2+(a.y-this.y)**2<(b.x-this.x)**2+(b.y-this.y)**2?a:b));this.angle=Math.atan2(tgt.y-this.y,tgt.x-this.x);this.timer+=dt;if(this.timer>=1/this.rate){this.timer-=1/this.rate;this.fire();}}draw(){}fire(){}};
    class BasicTower extends Tower{constructor(x,y){super(x,y,200,1);}draw(ctx){ctx.save();ctx.translate(this.x,this.y);ctx.rotate(this.angle);ctx.fillStyle='#ff0';ctx.beginPath();ctx.arc(0,0,8,0,2*Math.PI);ctx.fill();ctx.fillStyle='#cc0';ctx.fillRect(0,-3,16,6);ctx.restore();}fire(){bullets.push(new Bullet(this.x,this.y,Math.cos(this.angle)*300,Math.sin(this.angle)*300,1,0,'255,255,0',basicFireSound,basicHitSound));}};
    class SniperTower extends Tower{constructor(x,y){super(x,y,600,0.3);}draw(ctx){ctx.save();ctx.translate(this.x,this.y);ctx.rotate(this.angle);ctx.fillStyle='#f00';ctx.beginPath();ctx.arc(0,0,8,0,2*Math.PI);ctx.fill();ctx.fillStyle='#c00';ctx.fillRect(0,-3,16,6);ctx.restore();ctx.lineWidth=2;ctx.strokeStyle='rgba(255,0,0,0.3)';ctx.beginPath();ctx.arc(this.x,this.y,this.range,0,2*Math.PI);ctx.stroke();ctx.lineWidth=1;}fire(){bullets.push(new Bullet(this.x,this.y,Math.cos(this.angle)*500,Math.sin(this.angle)*500,3,0,'255,0,0',sniperFireSound,sniperHitSound));}};
    class FrostTower extends Tower{constructor(x,y){super(x,y,150,0.8);}draw(ctx){ctx.save();ctx.translate(this.x,this.y);ctx.rotate(this.angle);ctx.fillStyle='#0cf';ctx.beginPath();ctx.arc(0,0,8,0,2*Math.PI);ctx.fill();ctx.fillStyle='#09c';ctx.fillRect(0,-3,16,6);ctx.restore();}fire(){bullets.push(new Bullet(this.x,this.y,Math.cos(this.angle)*200,Math.sin(this.angle)*200,0,0.5,'0,204,255',frostFireSound,frostHitSound));}};

    class Eyeball{constructor(){const b=pathPoints[0];Object.assign(this,{x:b.x,y:b.y,radius:EYE_R,maxHealth:3,health:3,fins:0,blink:false,bt:0,bd:0.1,nb:2+Math.random()*3,stm:0,sf:0,targetIndex:1});this.btm=this.nb;}update(dt){let sp=moveSpeed;if(this.stm>0){this.stm-=dt;sp*=(1-this.sf);if(this.stm<0)this.stm=0;}const t=pathPoints[this.targetIndex];let dx=t.x-this.x,dy=t.y-this.y,dist=Math.hypot(dx,dy);if(dist<5){if(this.targetIndex<pathPoints.length-1)this.targetIndex++;else{lives--;document.getElementById('lives').textContent=lives;deathEffects.push(new DeathEffect(this.x,this.y,0,sp,50,255,50));return false;}}else{dx/=dist;dy/=dist;this.x+=dx*dt*sp;this.y+=dy*dt*sp;}const spin=20*(this.stm>0?(1-this.sf):1);this.fins+=dt*spin;if(!this.blink){this.btm-=dt;if(this.btm<=0){this.blink=true;this.bt=0;}}else{this.bt+=dt;if(this.bt>=this.bd*2){this.blink=false;this.btm=this.nb;this.bt=0;}}return true;}draw(ctx){const glowR=this.radius+20;const grad=ctx.createRadialGradient(this.x,this.y,this.radius,this.x,this.y,glowR);grad.addColorStop(0,'rgba(50,255,50,0.6)');grad.addColorStop(1,'rgba(50,255,50,0)');ctx.fillStyle=grad;ctx.beginPath();ctx.arc(this.x,this.y,glowR,0,2*Math.PI);ctx.fill();if(this.stm>0){ctx.strokeStyle='rgba(0,150,255,0.7)';ctx.lineWidth=6;ctx.beginPath();ctx.arc(this.x,this.y,this.radius+12,0,2*Math.PI);ctx.stroke();ctx.lineWidth=1;}ctx.fillStyle='#fff';ctx.beginPath();ctx.arc(this.x,this.y,this.radius,0,2*Math.PI);ctx.fill();const mx=mouseX!==null?mouseX:this.x,my=mouseY!==null?mouseY:this.y;let vx=mx-this.x,vy=my-this.y;const d=Math.hypot(vx,vy)||1;vx/=d;vy/=d;const ix=this.x+vx*10,iy=this.y+vy*10;ctx.fillStyle='#0a0';ctx.beginPath();ctx.ellipse(ix,iy,11,7,0,0,2*Math.PI);ctx.fill();ctx.fillStyle='#000';ctx.beginPath();ctx.arc(ix+vx*2,iy+vy*2,5,0,2*Math.PI);ctx.fill();if(this.blink){let p=this.bt/this.bd;if(p>1)p=2-p;p=Math.max(0,Math.min(1,p));const cov=this.radius*2*p;ctx.fillStyle='#0a0';ctx.beginPath();ctx.moveTo(this.x-this.radius,this.y-this.radius);ctx.lineTo(this.x+this.radius,this.y-this.radius);ctx.lineTo(this.x+this.radius,this.y-this.radius+cov);ctx.lineTo(this.x-this.radius,this.y-this.radius+cov);ctx.closePath();ctx.fill();}ctx.fillStyle='#0f0';for(let i=0;i<8;i++){const ang=this.fins+i*(Math.PI/4),inner=this.radius+5,outer=this.radius+12,x1=this.x+Math.cos(ang)*inner,y1=this.y+Math.sin(ang)*inner,x2=this.x+Math.cos(ang+0.3)*outer,y2=this.y+Math.sin(ang+0.3)*outer,x3=this.x+Math.cos(ang-0.3)*outer,y3=this.y+Math.sin(ang-0.3)*outer;ctx.beginPath();ctx.moveTo(x1,y1);ctx.lineTo(x2,y2);ctx.lineTo(x3,y3);ctx.closePath();ctx.fill();}const bw=40;ctx.fillStyle='red';ctx.fillRect(this.x-bw/2,this.y-this.radius-12,bw,6);ctx.fillStyle='lime';ctx.fillRect(this.x-bw/2,this.y-this.radius-12,bw*(this.health/this.maxHealth),6);} }

    function spawnOne(){eyeballs.push(new Eyeball());}
    function init(){eyeballs.length=0;towers.length=0;bullets.length=0;trailParticles.length=0;deathEffects.length=0;spawnTimer=0;lives=5;score=0;document.getElementById('lives').textContent=lives;document.getElementById('score').textContent=score;lastTime=performance.now();requestAnimationFrame(loop);}
    function loop(ts){const dt=(ts-lastTime)/1000;lastTime=ts;ctx.fillStyle='#3a5f0b';ctx.fillRect(0,0,canvas.width,canvas.height);ctx.strokeStyle='#b5651d';ctx.lineWidth=pathW;ctx.lineCap='round';ctx.beginPath();ctx.moveTo(pathPoints[0].x,pathPoints[0].y);pathPoints.forEach(p=>ctx.lineTo(p.x,p.y));ctx.stroke();if(eyeballs.length<maxEyeballs){spawnTimer+=dt;const iv=1/spawnRate;while(spawnTimer>=iv&&eyeballs.length<maxEyeballs){spawnOne();spawnTimer-=iv;}}eyeballs.forEach((e,i)=>{if(!e.update(dt))eyeballs.splice(i,1);});towers.forEach(t=>t.update(dt));bullets.forEach((b,bi)=>{b.update(dt);if(b.slow){b.hitFn();const near=eyeballs.slice().sort((a,c)=>Math.hypot(a.x-b.x,a.y-b.y)-Math.hypot(c.x-b.x,c.y-b.y)).slice(0,3);near.forEach(e=>{e.sf=b.slow;e.stm=1;deathEffects.push(new DeathEffect(e.x,e.y,b.vx,b.vy,...b.color.split(',')));e.health-=b.dmg;if(e.health<=0){eyeballs.splice(eyeballs.indexOf(e),1);score++;document.getElementById('score').textContent=score;}});bullets.splice(bi,1);}else{for(let ei=eyeballs.length-1;ei>=0;ei--){const e=eyeballs[ei];if(Math.hypot(e.x-b.x,e.y-b.y)<e.radius+b.size){b.hitFn();deathEffects.push(new DeathEffect(e.x,e.y,b.vx,b.vy,...b.color.split(',')));bullets.splice(bi,1);e.health-=b.dmg;if(e.health<=0){eyeballs.splice(ei,1);score++;document.getElementById('score').textContent=score;}break;}}}});trailParticles.forEach((p,pi)=>{if(!p.update(dt))trailParticles.splice(pi,1);});deathEffects.forEach((d,di)=>{if(!d.update(dt))deathEffects.splice(di,1);});if(mouseX!==null){const r=selectedTower==='basic'?200:selectedTower==='sniper'?600:150;ctx.strokeStyle='rgba(255,255,255,0.3)';ctx.lineWidth=2;ctx.beginPath();ctx.arc(mouseX,mouseY,r,0,2*Math.PI);ctx.stroke();ctx.lineWidth=1;}trailParticles.forEach(p=>p.draw());deathEffects.forEach(d=>d.draw());eyeballs.forEach(e=>e.draw(ctx));towers.forEach(t=>t.draw(ctx));bullets.forEach(b=>b.draw());requestAnimationFrame(loop);}canvas.addEventListener('click',e=>{const r=canvas.getBoundingClientRect(),x=e.clientX-r.left,y=e.clientY-r.top; if(selectedTower==='basic')towers.push(new BasicTower(x,y));else if(selectedTower==='sniper')towers.push(new SniperTower(x,y));else towers.push(new FrostTower(x,y));});window.addEventListener('load',init);
  </script>
</body>
</html>
