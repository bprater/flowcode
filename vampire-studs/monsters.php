<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Tower Defense: Eyeball Path</title>
  <style>
    body { margin:0; overflow:hidden; background:#000; color:#0f0; font-family:sans-serif; }
    #controls2 { position:absolute; top:10px; left:10px; z-index:10; }
    #controls2 input { margin-right:15px; }
    canvas { display:block; margin:0 auto; background:#fff; cursor:crosshair; }
  </style>
</head>
<body>
  <div id="controls2">
    Spawn Rate: <input id="spawnSlider" type="range" min="0.2" max="3" step="0.1" value="1"> eyeball/sec
    Movement Rate: <input id="moveSlider" type="range" min="10" max="200" step="10" value="50"> px/s
  </div>
  <canvas id="monsterCanvas" width="800" height="600"></canvas>
  <script>
    const canvas = document.getElementById('monsterCanvas');
    const ctx = canvas.getContext('2d');
    let mouseX=null, mouseY=null;
    canvas.addEventListener('mousemove', e=>{
      const rect=canvas.getBoundingClientRect();
      mouseX=e.clientX-rect.left; mouseY=e.clientY-rect.top;
    });

    // Controls
    const spawnSlider=document.getElementById('spawnSlider');
    const moveSlider=document.getElementById('moveSlider');
    let spawnRate=parseFloat(spawnSlider.value);
    let moveSpeed=parseFloat(moveSlider.value);
    spawnSlider.oninput=()=>spawnRate=parseFloat(spawnSlider.value);
    moveSlider.oninput=()=>moveSpeed=parseFloat(moveSlider.value);

    // Path generation (mostly top to bottom)
    const pathPoints=[];
    const segments=6;
    const margin=100;
    for(let i=0;i<=segments;i++){
      const y=(canvas.height/segments)*i;
      const x=margin + Math.random()*(canvas.width-2*margin);
      pathPoints.push({x,y});
    }
    const pathWidth=80;

    // Entity arrays
    const eyeballs=[];
    const cannons=[];
    const bullets=[];
    let spawnTimer=0, last;

    class Eyeball {
      constructor() {
        const p0=pathPoints[0];
        this.x=p0.x; this.y=p0.y;
        this.radius=20; this.finsAngle=0;
        this.blinking=false; this.blinkTime=0;
        this.blinkDuration=0.1; this.nextBlink=2+Math.random()*3; this.blinkTimer=this.nextBlink;
        this.targetIndex=1;
      }
      update(dt){
        // follow path
        const tgt=pathPoints[this.targetIndex];
        let dx=tgt.x-this.x, dy=tgt.y-this.y;
        const dist=Math.hypot(dx,dy);
        if(dist<5){ this.targetIndex++; if(this.targetIndex>=pathPoints.length) this.targetIndex=pathPoints.length-1; }
        else{
          dx/=dist; dy/=dist;
          this.x+=dx*moveSpeed*dt;
          this.y+=dy*moveSpeed*dt;
        }
        // spin
        this.finsAngle+=dt*20;
        // blink
        if(!this.blinking){ this.blinkTimer-=dt; if(this.blinkTimer<=0){this.blinking=true;this.blinkTime=0;} }
        else{ this.blinkTime+=dt; if(this.blinkTime>=this.blinkDuration*2){this.blinking=false; this.blinkTimer=2+Math.random()*3;} }
      }
      draw(ctx){
        // grass background drawn globally
        // path and eyeball drawing below
        // glow
        const glowR=this.radius+10;
        const grad=ctx.createRadialGradient(this.x,this.y,this.radius,this.x,this.y,glowR);
        grad.addColorStop(0,'rgba(50,255,50,0.6)'); grad.addColorStop(1,'rgba(50,255,50,0)');
        ctx.fillStyle=grad; ctx.beginPath(); ctx.arc(this.x,this.y,glowR,0,2*Math.PI); ctx.fill();
        // sclera
        ctx.fillStyle='#fff'; ctx.beginPath(); ctx.arc(this.x,this.y,this.radius,0,2*Math.PI); ctx.fill();
        // iris
        const mx=(mouseX!==null?mouseX:this.x), my=(mouseY!==null?mouseY:this.y);
        let dx=mx-this.x, dy=my-this.y; const len=Math.hypot(dx,dy)||1;
        const ux=dx/len, uy=dy/len;
        const ix=this.x+ux*10, iy=this.y+uy*10;
        ctx.fillStyle='#0a0'; ctx.beginPath(); ctx.ellipse(ix,iy,11,7,0,0,2*Math.PI); ctx.fill();
        // pupil
        ctx.fillStyle='#000'; ctx.beginPath(); ctx.arc(ix+ux*2,iy+uy*2,5,0,2*Math.PI); ctx.fill();
        // blink
        if(this.blinking){ let p=this.blinkTime/this.blinkDuration; if(p>1)p=2-p; p=Math.max(0,Math.min(1,p));
          const cover=this.radius*2*p;
          ctx.fillStyle='#0a0';
          ctx.beginPath();ctx.moveTo(this.x-this.radius,this.y-this.radius);
          ctx.lineTo(this.x+this.radius,this.y-this.radius);
          ctx.lineTo(this.x+this.radius,this.y-this.radius+cover);
          ctx.lineTo(this.x-this.radius,this.y-this.radius+cover);
          ctx.closePath();ctx.fill();
        }
        // fins
        ctx.fillStyle='#0f0'; for(let i=0;i<8;i++){ const ang=this.finsAngle+i*(Math.PI/4);
          const inner=this.radius+5, outer=this.radius+12;
          const x1=this.x+Math.cos(ang)*inner, y1=this.y+Math.sin(ang)*inner;
          const x2=this.x+Math.cos(ang+0.3)*outer, y2=this.y+Math.sin(ang+0.3)*outer;
          const x3=this.x+Math.cos(ang-0.3)*outer, y3=this.y+Math.sin(ang-0.3)*outer;
          ctx.beginPath();ctx.moveTo(x1,y1);ctx.lineTo(x2,y2);ctx.lineTo(x3,y3);ctx.closePath();ctx.fill(); }
      }
    }

    class Cannon { constructor(x,y){this.x=x;this.y=y;this.angle=0;this.timer=0;this.rate=1;} 
      update(dt){ if(eyeballs.length){ const tgt=eyeballs.reduce((a,b)=>( (a.x-this.x)**2+(a.y-this.y)**2 < (b.x-this.x)**2+(b.y-this.y)**2 ? a:b)); 
        const dx=tgt.x-this.x,dy=tgt.y-this.y; this.angle=Math.atan2(dy,dx);
        this.timer+=dt; if(this.timer>=1/this.rate){this.timer-=1/this.rate; const vx=Math.cos(this.angle)*300,vy=Math.sin(this.angle)*300; bullets.push(new Bullet(this.x,this.y,vx,vy)); } }
      }
      draw(ctx){ ctx.save(); ctx.translate(this.x,this.y); ctx.rotate(this.angle);
        ctx.fillStyle='#aaa'; ctx.beginPath();ctx.arc(0,0,8,0,2*Math.PI);ctx.fill(); ctx.fillStyle='#888'; ctx.fillRect(0,-3,16,6);
        ctx.restore(); }
    }

    class Bullet {constructor(x,y,vx,vy){this.x=x;this.y=y;this.vx=vx;this.vy=vy;this.size=4;} update(dt){this.x+=this.vx*dt;this.y+=this.vy*dt;} draw(ctx){ctx.fillStyle='#ff0';ctx.beginPath();ctx.arc(this.x,this.y,this.size,0,2*Math.PI);ctx.fill();}}

    
    function init(){ spawnTimer=0; eyeballs.length=0; last=performance.now(); requestAnimationFrame(loop); }

    function loop(ts){ const dt=(ts-last)/1000; last=ts;
      // draw grass
      ctx.fillStyle='#3a5f0b'; ctx.fillRect(0,0,canvas.width,canvas.height);
      // draw path
      ctx.strokeStyle='#b5651d'; ctx.lineWidth=pathWidth; ctx.lineCap='round';
      ctx.beginPath(); ctx.moveTo(pathPoints[0].x,pathPoints[0].y);
      pathPoints.forEach(pt=>ctx.lineTo(pt.x,pt.y)); ctx.stroke();
      // spawn eyeballs one at a time
      if(eyeballs.length<maxEyeballs){ spawnTimer+=dt; const interval=1/spawnRate;
        while(spawnTimer>=interval && eyeballs.length<maxEyeballs){ eyeballs.push(new Eyeball()); spawnTimer-=interval; }
      }
      eyeballs.forEach(e=>{ e.update(dt); });
      cannons.forEach(c=>c.update(dt));
      bullets.forEach((b,i)=>{ b.update(dt);
        for(let j=eyeballs.length-1;j>=0;j--){ const e=eyeballs[j]; if(Math.hypot(e.x-b.x,e.y-b.y)<e.radius+b.size){ eyeballs.splice(j,1); bullets.splice(i,1); break; } }
      });
      // respawn logic
      if(eyeballs.length===0 && cannons.length) spawnTimer=0;
      eyeballs.forEach(e=>e.draw(ctx)); cannons.forEach(c=>c.draw(ctx)); bullets.forEach(b=>b.draw(ctx));
      requestAnimationFrame(loop);
    }

    const maxEyeballs=8;
    spawnSlider.value=1; moveSlider.value=50;
    canvas.addEventListener('click',e=>{ const rect=canvas.getBoundingClientRect(); cannons.push(new Cannon(e.clientX-rect.left,e.clientY-rect.top)); });
    window.addEventListener('load',init);
  </script>
</body>
</html>
