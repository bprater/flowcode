<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Scary Eyeballs & Cannons</title>
  <style>
    body { margin: 0; overflow: hidden; background: #000; }
    canvas { display: block; margin: 0 auto; background: #111; cursor: crosshair; }
  </style>
</head>
<body>
  <canvas id="monsterCanvas" width="800" height="600"></canvas>
  <script>
    const canvas = document.getElementById('monsterCanvas');
    const ctx = canvas.getContext('2d');
    let mouseX = null, mouseY = null;
    canvas.addEventListener('mousemove', e => {
      const rect = canvas.getBoundingClientRect();
      mouseX = e.clientX - rect.left;
      mouseY = e.clientY - rect.top;
    });

    // Place cannon on click
    const cannons = [];
    canvas.addEventListener('click', e => {
      const rect = canvas.getBoundingClientRect();
      const x = e.clientX - rect.left;
      const y = e.clientY - rect.top;
      cannons.push(new Cannon(x, y));
    });

    class Eyeball {
      constructor(x, y) {
        this.x = x; this.y = y;
        this.radius = 22.5; this.speed = 100;
        this.finsAngle = 0;
        this.blinking = false; this.blinkTime = 0;
        this.blinkDuration = 0.1;
        this.nextBlink = 2 + Math.random() * 3;
        this.blinkTimer = this.nextBlink;
      }
      update(dt) {
        if (mouseX !== null && mouseY !== null) {
          const dx = mouseX - this.x, dy = mouseY - this.y;
          const len = Math.hypot(dx, dy) || 1;
          this.x += (dx/len) * this.speed * dt;
          this.y += (dy/len) * this.speed * dt;
        }
        const dist = mouseX !== null ? Math.hypot(this.x - mouseX, this.y - mouseY) : Infinity;
        const spinSpeed = 5 + 50 * (1 - Math.min(1, dist / 400));
        this.finsAngle += dt * spinSpeed;
        if (!this.blinking) {
          this.blinkTimer -= dt;
          if (this.blinkTimer <= 0) { this.blinking = true; this.blinkTime = 0; }
        } else {
          this.blinkTime += dt;
          if (this.blinkTime >= this.blinkDuration * 2) {
            this.blinking = false;
            this.blinkTimer = 2 + Math.random() * 3;
            this.blinkTime = 0;
          }
        }
      }
      draw(ctx) {
        // glow
        const glowR = this.radius + 20;
        const grad = ctx.createRadialGradient(this.x, this.y, this.radius, this.x, this.y, glowR);
        grad.addColorStop(0, 'rgba(50,255,50,0.6)');
        grad.addColorStop(1, 'rgba(50,255,50,0)');
        ctx.fillStyle = grad;
        ctx.beginPath(); ctx.arc(this.x, this.y, glowR, 0, 2*Math.PI); ctx.fill();
        // sclera
        ctx.fillStyle = '#fff'; ctx.beginPath(); ctx.arc(this.x, this.y, this.radius, 0, 2*Math.PI); ctx.fill();
        // iris & pupil
        const dx = (mouseX!==null?mouseX:this.x)-this.x;
        const dy = (mouseY!==null?mouseY:this.y)-this.y;
        const len = Math.hypot(dx,dy)||1;
        const ux = dx/len, uy = dy/len;
        const ix = this.x + ux*10, iy = this.y + uy*10;
        ctx.fillStyle = '#0a0';
        ctx.beginPath(); ctx.ellipse(ix, iy, 11.25, 7.5, 0, 0, 2*Math.PI); ctx.fill();
        ctx.fillStyle = '#000';
        ctx.beginPath(); ctx.arc(ix+ux*2, iy+uy*2, 5, 0, 2*Math.PI); ctx.fill();
        // blink eyelid
        if (this.blinking) {
          let p = this.blinkTime/this.blinkDuration;
          if (p>1) p = 2-p;
          p = Math.max(0,Math.min(1,p));
          const cover = this.radius*2*p;
          ctx.fillStyle = '#0a0';
          ctx.beginPath();
          ctx.moveTo(this.x - this.radius, this.y - this.radius);
          ctx.lineTo(this.x + this.radius, this.y - this.radius);
          ctx.lineTo(this.x + this.radius, this.y - this.radius + cover);
          ctx.lineTo(this.x - this.radius, this.y - this.radius + cover);
          ctx.closePath(); ctx.fill();
        }
        // fins
        ctx.fillStyle = '#0f0';
        for (let i=0;i<8;i++){
          const ang = this.finsAngle + i*(2*Math.PI/8);
          const innerR = this.radius+8, outerR = this.radius+15;
          const x1 = this.x+Math.cos(ang)*innerR, y1 = this.y+Math.sin(ang)*innerR;
          const x2 = this.x+Math.cos(ang+0.25)*outerR, y2 = this.y+Math.sin(ang+0.25)*outerR;
          const x3 = this.x+Math.cos(ang-0.25)*outerR, y3 = this.y+Math.sin(ang-0.25)*outerR;
          ctx.beginPath(); ctx.moveTo(x1,y1); ctx.lineTo(x2,y2); ctx.lineTo(x3,y3); ctx.closePath(); ctx.fill();
        }
      }
    }

    class Cannon {
      constructor(x,y) {
        this.x = x; this.y = y;
        this.timer = 0;
        this.rate = 1; // shots per sec
      }
      update(dt) {
        this.timer += dt;
        if (this.timer >= 1/this.rate) {
          this.timer -= 1/this.rate;
          this.fire();
        }
      }
      fire() {
        // aim at closest eyeball
        if (eyeballs.length === 0) return;
        let closest = eyeballs[0], md = Infinity;
        for (const e of eyeballs) {
          const dx = e.x - this.x, dy = e.y - this.y;
          const d = dx*dx+dy*dy;
          if (d<md) { md = d; closest = e; }
        }
        const dx = closest.x - this.x, dy = closest.y - this.y;
        const len = Math.hypot(dx,dy)||1;
        const vx = (dx/len)*300, vy = (dy/len)*300;
        bullets.push(new Bullet(this.x,this.y,vx,vy));
      }
      draw(ctx) {
        ctx.fillStyle = '#aaa';
        ctx.beginPath(); ctx.arc(this.x,this.y,10,0,2*Math.PI); ctx.fill();
        ctx.fillStyle='#888'; ctx.fillRect(this.x-3,this.y-20,6,20);
      }
    }

    class Bullet {
      constructor(x,y,vx,vy) { this.x=x; this.y=y; this.vx=vx; this.vy=vy; this.size=5; }
      update(dt) { this.x+=this.vx*dt; this.y+=this.vy*dt; }
      draw(ctx) { ctx.fillStyle='#ff0'; ctx.beginPath(); ctx.arc(this.x,this.y,this.size,0,2*Math.PI); ctx.fill(); }
    }

    const eyeballs = [];
    const bullets = [];
    let last;
    function init() {
      for (let i=0;i<8;i++){
        const ang = (i/8)*2*Math.PI;
        const r=200;
        eyeballs.push(new Eyeball(canvas.width/2+Math.cos(ang)*r, canvas.height/2+Math.sin(ang)*r));
      }
      last = performance.now(); requestAnimationFrame(loop);
    }
    function loop(ts){
      const dt=(ts-last)/1000; last=ts;
      ctx.clearRect(0,0,canvas.width,canvas.height);
      eyeballs.forEach(e=>e.update(dt));
      cannons.forEach(c=>c.update(dt));
      bullets.forEach((b,i)=>{
        b.update(dt);
        // collision with eyeballs
        for (let j=eyeballs.length-1;j>=0;j--) {
          const e = eyeballs[j];
          if (Math.hypot(e.x-b.x,e.y-b.y) < e.radius + b.size) {
            eyeballs.splice(j,1);
            bullets.splice(i,1);
            return;
          }
        }
      });
      // draw order
      eyeballs.forEach(e=>e.draw(ctx));
      cannons.forEach(c=>c.draw(ctx));
      bullets.forEach(b=>b.draw(ctx));
      requestAnimationFrame(loop);
    }
    window.addEventListener('load', init);
  </script>
</body>
</html>
