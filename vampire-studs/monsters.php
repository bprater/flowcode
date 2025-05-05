<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Scary Eyeballs Showcase</title>
  <style>
    body { margin: 0; overflow: hidden; background: #000; }
    canvas { display: block; margin: 0 auto; background: #111; }
  </style>
</head>
<body>
  <canvas id="monsterCanvas" width="800" height="600"></canvas>
  <script>
    const canvas = document.getElementById('monsterCanvas');
    const ctx = canvas.getContext('2d');
    let mouseX = null, mouseY = null;
    window.addEventListener('mousemove', e => {
      const rect = canvas.getBoundingClientRect();
      mouseX = e.clientX - rect.left;
      mouseY = e.clientY - rect.top;
    });

    class Eyeball {
      constructor(x, y) {
        this.x = x;
        this.y = y;
        this.radius = 22.5;
        this.speed = 100;
        this.finsAngle = 0;
        // blinking
        this.blinking = false;
        this.blinkTime = 0;
        this.blinkDuration = 0.1; // single phase duration
        this.nextBlink = 2 + Math.random() * 3;
        this.blinkTimer = this.nextBlink;
      }
      update(dt) {
        // attract to cursor
        if (mouseX !== null && mouseY !== null) {
          const dx = mouseX - this.x;
          const dy = mouseY - this.y;
          const len = Math.hypot(dx, dy) || 1;
          this.x += (dx / len) * this.speed * dt;
          this.y += (dy / len) * this.speed * dt;
        }
        // fins speed based on cursor distance
        const dist = mouseX !== null ? Math.hypot(this.x - mouseX, this.y - mouseY) : Infinity;
        const spinSpeed = 5 + 50 * (1 - Math.min(1, dist / 400));
        this.finsAngle += dt * spinSpeed;
        // blinking logic
        if (!this.blinking) {
          this.blinkTimer -= dt;
          if (this.blinkTimer <= 0) {
            this.blinking = true;
            this.blinkTime = 0;
          }
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
        const glowRadius = this.radius + 20;
        const grad = ctx.createRadialGradient(
          this.x, this.y, this.radius,
          this.x, this.y, glowRadius
        );
        grad.addColorStop(0, 'rgba(50,255,50,0.6)');
        grad.addColorStop(1, 'rgba(50,255,50,0)');
        ctx.fillStyle = grad;
        ctx.beginPath(); ctx.arc(this.x, this.y, glowRadius, 0, 2*Math.PI); ctx.fill();
        // sclera
        ctx.fillStyle = '#fff';
        ctx.beginPath(); ctx.arc(this.x, this.y, this.radius, 0, 2*Math.PI); ctx.fill();
        // iris
        const dx = (mouseX !== null ? mouseX : this.x) - this.x;
        const dy = (mouseY !== null ? mouseY : this.y) - this.y;
        const len = Math.hypot(dx, dy) || 1;
        const ux = dx / len, uy = dy / len;
        const ix = this.x + ux * 10;
        const iy = this.y + uy * 10;
        ctx.fillStyle = '#0a0';
        ctx.beginPath(); ctx.ellipse(ix, iy, 11.25, 7.5, 0, 0, 2*Math.PI); ctx.fill();
        // pupil
        ctx.fillStyle = '#000';
        ctx.beginPath(); ctx.arc(ix + ux * 2, iy + uy * 2, 5, 0, 2*Math.PI); ctx.fill();
        // blinking overlay (multi-frame)
        if (this.blinking) {
          let p = this.blinkTime / this.blinkDuration;
          if (p > 1) p = 2 - p; // triangular: close then open
          p = Math.max(0, Math.min(1, p));
          const cover = this.radius * 2 * p;
          ctx.fillStyle = '#0f0';
          ctx.beginPath();
          ctx.moveTo(this.x - this.radius, this.y - this.radius);
          ctx.lineTo(this.x + this.radius, this.y - this.radius);
          ctx.lineTo(this.x + this.radius, this.y - this.radius + cover);
          ctx.lineTo(this.x - this.radius, this.y - this.radius + cover);
          ctx.closePath(); ctx.fill();
        }
        // razor fins
        ctx.fillStyle = '#0f0';
        const fins = 8;
        for (let i = 0; i < fins; i++) {
          const ang = this.finsAngle + i * (2*Math.PI/fins);
          const outerR = this.radius + 15;
          const innerR = this.radius + 8;
          const x1 = this.x + Math.cos(ang) * innerR;
          const y1 = this.y + Math.sin(ang) * innerR;
          const x2 = this.x + Math.cos(ang + 0.25) * outerR;
          const y2 = this.y + Math.sin(ang + 0.25) * outerR;
          const x3 = this.x + Math.cos(ang - 0.25) * outerR;
          const y3 = this.y + Math.sin(ang - 0.25) * outerR;
          ctx.beginPath();
          ctx.moveTo(x1, y1);
          ctx.lineTo(x2, y2);
          ctx.lineTo(x3, y3);
          ctx.closePath();
          ctx.fill();
        }
      }
    }
    // prevent full overlap
    function separate(eyes) {
      const minDist = eyes[0].radius * 2;
      for (let i = 0; i < eyes.length; i++) {
        for (let j = i+1; j < eyes.length; j++) {
          const a = eyes[i], b = eyes[j];
          const dx = b.x - a.x, dy = b.y - a.y;
          const dist = Math.hypot(dx, dy) || 1;
          if (dist < minDist) {
            const overlap = minDist - dist;
            const ux = dx/dist, uy = dy/dist;
            a.x -= ux * overlap * 0.5;
            a.y -= uy * overlap * 0.5;
            b.x += ux * overlap * 0.5;
            b.y += uy * overlap * 0.5;
          }
        }
      }
    }
    const eyeballs = [];
    function init() {
      for (let i = 0; i < 8; i++) {
        const angle = (i/8) * 2 * Math.PI;
        const r = 200;
        const cx = canvas.width/2 + Math.cos(angle) * r;
        const cy = canvas.height/2 + Math.sin(angle) * r;
        eyeballs.push(new Eyeball(cx, cy));
      }
      last = performance.now(); requestAnimationFrame(loop);
    }
    let last;
    function loop(ts) {
      const dt = (ts-last)/1000; last = ts;
      ctx.clearRect(0,0,canvas.width,canvas.height);
      eyeballs.forEach(e => e.update(dt));
      separate(eyeballs);
      eyeballs.forEach(e => e.draw(ctx));
      requestAnimationFrame(loop);
    }
    window.addEventListener('load', init);
  </script>
</body>
</html>
