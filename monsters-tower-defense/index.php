<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tower Defense: Multiple Towers & Health Bars</title>
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
    <br><br>
    Spawn Rate: <input id="spawnSlider" type="range" min="0.2" max="3" step="0.1" value="1"> eyeball/sec
    Movement Rate: <input id="moveSlider" type="range" min="10" max="200" step="10" value="50"> px/s
  </div>
  <div id="hud">Lives: <span id="lives">5</span> | Score: <span id="score">0</span></div>
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

    // Controls
    const spawnSlider = document.getElementById('spawnSlider');
    const moveSlider = document.getElementById('moveSlider');
    let spawnRate = +spawnSlider.value;
    let moveSpeed = +moveSlider.value;
    spawnSlider.oninput = () => spawnRate = +spawnSlider.value;
    moveSlider.oninput = () => moveSpeed = +moveSlider.value;

    // Tower selection
    let selectedTower = 'basic';
    document.querySelectorAll('input[name="towerType"]').forEach(inp => {
      inp.addEventListener('change', () => selectedTower = inp.value);
    });

    // Path generation
    const EYE_RADIUS = 22.5;
    const pathPoints = [];
    const segments = 6, margin = 100;
    for (let i = 0; i <= segments; i++) {
      const y = (canvas.height / segments) * i;
      const x = margin + Math.random() * (canvas.width - 2 * margin);
      pathPoints.push({ x, y });
    }
    const pathWidth = 80;
    const maxEyeballs = 8;

    // Game state
    const eyeballs = [];
    const towers = [];
    const bullets = [];
    const deathEffects = [];
    let spawnTimer = 0, lastTime, lives = 5, score = 0;

    // Base Tower class
    class Tower {
      constructor(x, y, range, rate) {
        this.x = x; this.y = y;
        this.range = range; this.rate = rate;
        this.timer = 0; this.angle = 0;
      }
      update(dt) {
        const inRange = eyeballs.filter(e => Math.hypot(e.x - this.x, e.y - this.y) < this.range);
        if (!inRange.length) return;
        const tgt = inRange.reduce((a, b) => ((a.x - this.x)**2 + (a.y - this.y)**2 <
          (b.x - this.x)**2 + (b.y - this.y)**2 ? a : b));
        const dx = tgt.x - this.x, dy = tgt.y - this.y;
        this.angle = Math.atan2(dy, dx);
        this.timer += dt;
        if (this.timer >= 1 / this.rate) {
          this.timer -= 1 / this.rate;
          this.fire(tgt);
        }
      }
      draw(ctx) {}
      fire() {}
    }

    // Basic Tower (yellow)
    class BasicTower extends Tower {
      constructor(x, y) { super(x, y, 200, 1); }
      draw(ctx) {
        ctx.save(); ctx.translate(this.x, this.y); ctx.rotate(this.angle);
        ctx.fillStyle = '#ff0'; ctx.beginPath(); ctx.arc(0, 0, 8, 0, 2 * Math.PI); ctx.fill();
        ctx.fillStyle = '#cc0'; ctx.fillRect(0, -3, 16, 6);
        ctx.restore();
      }
      fire() {
        bullets.push(new Bullet(
          this.x, this.y,
          Math.cos(this.angle) * 300,
          Math.sin(this.angle) * 300,
          1, 0
        ));
      }
    }

    // Sniper Tower (red)
    class SniperTower extends Tower {
      constructor(x, y) { super(x, y, 600, 0.3); }
      draw(ctx) {
        ctx.save(); ctx.translate(this.x, this.y); ctx.rotate(this.angle);
        ctx.fillStyle = '#f00'; ctx.beginPath(); ctx.arc(0, 0, 8, 0, 2 * Math.PI); ctx.fill();
        ctx.fillStyle = '#c00'; ctx.fillRect(0, -3, 16, 6);
        ctx.restore();
        ctx.lineWidth = 2;
        ctx.strokeStyle = 'rgba(255,0,0,0.3)';
        ctx.beginPath(); ctx.arc(this.x, this.y, this.range, 0, 2 * Math.PI); ctx.stroke();
        ctx.lineWidth = 1;
      }
      fire() {
        bullets.push(new Bullet(
          this.x, this.y,
          Math.cos(this.angle) * 500,
          Math.sin(this.angle) * 500,
          3, 0,
          255, 0, 0
        ));
      }
    }

    // Frost Tower (cyan)
    class FrostTower extends Tower {
      constructor(x, y) { super(x, y, 150, 0.8); }
      draw(ctx) {
        ctx.save(); ctx.translate(this.x, this.y); ctx.rotate(this.angle);
        ctx.fillStyle = '#0cf'; ctx.beginPath(); ctx.arc(0, 0, 8, 0, 2 * Math.PI); ctx.fill();
        ctx.fillStyle = '#09c'; ctx.fillRect(0, -3, 16, 6);
        ctx.restore();
      }
      fire() {
        bullets.push(new Bullet(
          this.x, this.y,
          Math.cos(this.angle) * 200,
          Math.sin(this.angle) * 200,
          0.2, 0.5
        ));
      }
    }

    // Eyeball with health and slowdown
    class Eyeball {
      constructor() {
        const base = pathPoints[0];
        this.x = base.x; this.y = base.y;
        this.radius = EYE_RADIUS;
        this.maxHealth = 3; this.health = this.maxHealth;
        this.finsAngle = 0;
        this.blinking = false; this.blinkTime = 0;
        this.blinkDuration = 0.1;
        this.nextBlink = 2 + Math.random() * 3;
        this.blinkTimer = this.nextBlink;
        this.targetIndex = 1;
        this.slowTimer = 0;
        this.slowFactor = 0;
      }
      update(dt) {
        // handle slow
        let speedMult = 1;
        if (this.slowTimer > 0) {
          this.slowTimer -= dt;
          speedMult = 1 - this.slowFactor;
          if (this.slowTimer < 0) this.slowTimer = 0;
        }
        const tgt = pathPoints[this.targetIndex];
        let dx = tgt.x - this.x, dy = tgt.y - this.y;
        const dist = Math.hypot(dx, dy);
        if (dist < 5) {
          if (this.targetIndex < pathPoints.length - 1) {
            this.targetIndex++;
          } else {
            lives--; document.getElementById('lives').textContent = lives;
            deathEffects.push(new DeathEffect(this.x, this.y, 0, moveSpeed * speedMult, 50, 255, 50));
            return false;
          }
        } else {
          dx /= dist; dy /= dist;
          this.x += dx * moveSpeed * dt * speedMult;
          this.y += dy * moveSpeed * dt * speedMult;
        }
        this.finsAngle += dt * 20;
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
        return true;
      }
      draw(ctx) {
        // glow
        const glowR = this.radius + 20;
        const grad = ctx.createRadialGradient(this.x, this.y, this.radius, this.x, this.y, glowR);
        grad.addColorStop(0, 'rgba(50,255,50,0.6)');
        grad.addColorStop(1, 'rgba(50,255,50,0)');
        ctx.fillStyle = grad;
        ctx.beginPath(); ctx.arc(this.x, this.y, glowR, 0, 2 * Math.PI); ctx.fill();
        // eye
        ctx.fillStyle = '#fff'; ctx.beginPath(); ctx.arc(this.x, this.y, this.radius, 0, 2 * Math.PI); ctx.fill();
        // pupil
        const mx = mouseX !== null ? mouseX : this.x;
        const my = mouseY !== null ? mouseY : this.y;
        let vx = mx - this.x, vy = my - this.y;
        const d = Math.hypot(vx, vy) || 1; vx /= d; vy /= d;
        const ix = this.x + vx * 10, iy = this.y + vy * 10;
        ctx.fillStyle = '#0a0'; ctx.beginPath(); ctx.ellipse(ix, iy, 11, 7, 0, 0, 2 * Math.PI); ctx.fill();
        ctx.fillStyle = '#000'; ctx.beginPath(); ctx.arc(ix + vx * 2, iy + vy * 2, 5, 0, 2 * Math.PI); ctx.fill();
        // blink
        if (this.blinking) {
          let p = this.blinkTime / this.blinkDuration;
          if (p > 1) p = 2 - p;
          p = Math.max(0, Math.min(1, p));
          const cover = this.radius * 2 * p;
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
        for (let i = 0; i < 8; i++) {
          const ang = this.finsAngle + i * (Math.PI / 4);
          const inner = this.radius + 5, outer = this.radius + 12;
          const x1 = this.x + Math.cos(ang) * inner;
          const y1 = this.y + Math.sin(ang) * inner;
          const x2 = this.x + Math.cos(ang + 0.3) * outer;
          const y2 = this.y + Math.sin(ang + 0.3) * outer;
          const x3 = this.x + Math.cos(ang - 0.3) * outer;
          const y3 = this.y + Math.sin(ang - 0.3) * outer;
          ctx.beginPath(); ctx.moveTo(x1, y1); ctx.lineTo(x2, y2); ctx.lineTo(x3, y3); ctx.closePath(); ctx.fill();
        }
        // health bar
        const barW = 40;
        ctx.fillStyle = 'red'; ctx.fillRect(this.x - barW/2, this.y - this.radius - 12, barW, 6);
        ctx.fillStyle = 'lime'; ctx.fillRect(this.x - barW/2, this.y - this.radius - 12, barW * (this.health / this.maxHealth), 6);
      }
    }

    // Bullet class
    class Bullet {
      constructor(x, y, vx, vy, dmg, slow, r = null, g = null, b = null) {
        this.x = x; this.y = y; this.vx = vx; this.vy = vy; this.size = 4;
        this.dmg = dmg; this.slow = slow;
        if (r !== null) { this.r = r; this.g = g; this.b = b; }
        else if (slow) { this.r = 0; this.g = 204; this.b = 255; }
        else { this.r = 255; this.g = 255; this.b = 0; }
      }
      update(dt) { this.x += this.vx * dt; this.y += this.vy * dt; }
      draw(ctx) {
        ctx.fillStyle = `rgb(${this.r},${this.g},${this.b})`;
        ctx.beginPath(); ctx.arc(this.x, this.y, this.size, 0, 2 * Math.PI); ctx.fill();
      }
    }

    // DeathEffect class
    class DeathEffect {
      constructor(x, y, vx, vy, cr = 50, cg = 255, cb = 50) {
        this.cr = cr; this.cg = cg; this.cb = cb; this.particles = [];
        // goo particles
        for (let i = 0; i < 20; i++) {
          const ang = Math.random() * 2 * Math.PI;
          const sp = 50 + Math.random() * 100;
          this.particles.push({ x, y, vx: vx*0.5 + Math.cos(ang)*sp, vy: vy*0.5 + Math.sin(ang)*sp, size: 4 + Math.random()*4, life: 0.5 + Math.random()*0.5, age: 0, type: 'goo' });
        }
        // pixel particles
        for (let i = 0; i < 50; i++) {
          const px = x + (Math.random() - 0.5) * EYE_RADIUS * 2;
          const py = y + (Math.random() - 0.5) * EYE_RADIUS * 2;
          this.particles.push({ x: px, y: py, vx: (Math.random()-0.5)*100, vy: -Math.random()*100, size: 3, life: 0.7 + Math.random()*0.5, age: 0, type: 'pixel' });
        }
      }
      update(dt) {
        this.particles.forEach(p => { p.age += dt; p.x += p.vx * dt; p.y += p.vy * dt; });
        this.particles = this.particles.filter(p => p.age < p.life);
        return this.particles.length > 0;
      }
      draw(ctx) {
        this.particles.forEach(p => {
          const t = 1 - p.age / p.life;
          ctx.fillStyle = `rgba(${this.cr},${this.cg},${this.cb},${t})`;
          if (p.type === 'goo') {
            ctx.beginPath(); ctx.arc(p.x, p.y, p.size * t, 0, 2 * Math.PI); ctx.fill();
          } else {
            ctx.fillRect(p.x, p.y, p.size, p.size);
          }
        });
      }
    }

    // Game functions
    function spawnOne() { eyeballs.push(new Eyeball()); }
    function init() {
      eyeballs.length = 0; towers.length = 0; bullets.length = 0; deathEffects.length = 0;
      spawnTimer = 0; lives = 5; score = 0;
      document.getElementById('lives').textContent = lives;
      document.getElementById('score').textContent = score;
      lastTime = performance.now(); requestAnimationFrame(loop);
    }
    function loop(ts) {
      const dt = (ts - lastTime) / 1000; lastTime = ts;
      // background + path
      ctx.fillStyle = '#3a5f0b'; ctx.fillRect(0, 0, canvas.width, canvas.height);
      ctx.strokeStyle = '#b5651d'; ctx.lineWidth = pathWidth; ctx.lineCap = 'round';
      ctx.beginPath(); ctx.moveTo(pathPoints[0].x, pathPoints[0].y);
      pathPoints.forEach(p => ctx.lineTo(p.x, p.y)); ctx.stroke();
      // spawn
      if (eyeballs.length < maxEyeballs) { spawnTimer += dt; const iv = 1/spawnRate; while(spawnTimer >= iv && eyeballs.length < maxEyeballs){ spawnOne(); spawnTimer -= iv; }}
      // updates
      eyeballs.forEach((e,i) => { if (!e.update(dt)) eyeballs.splice(i,1); });
      towers.forEach(t => t.update(dt));
      bullets.forEach((b, bi) => { b.update(dt);
        for (let ei = eyeballs.length-1; ei >= 0; ei--) {
          const e = eyeballs[ei];
          if (Math.hypot(e.x-b.x, e.y-b.y) < e.radius + b.size) {
            // slow effect
            if (b.slow) { e.slowFactor = b.slow; e.slowTimer = 1; }
            deathEffects.push(new DeathEffect(e.x, e.y, b.vx, b.vy, b.r, b.g, b.b));
            bullets.splice(bi,1);
            e.health -= b.dmg;
            if (e.health <= 0) { eyeballs.splice(ei,1); score++; document.getElementById('score').textContent = score; }
            break;
          }
        }
      });
      deathEffects.forEach((d, di) => { if(!d.update(dt)) deathEffects.splice(di,1); });
      // preview radius
      if (mouseX !== null) {
        const rad = selectedTower === 'basic' ? 200 : (selectedTower === 'sniper' ? 600 : 150);
        ctx.strokeStyle = 'rgba(255,255,255,0.3)'; ctx.lineWidth = 2;
        ctx.beginPath(); ctx.arc(mouseX, mouseY, rad, 0, 2 * Math.PI); ctx.stroke(); ctx.lineWidth = 1;
      }
      // draws
      deathEffects.forEach(d => d.draw(ctx));
      eyeballs.forEach(e => e.draw(ctx));
      towers.forEach(t => t.draw(ctx));
      bullets.forEach(b => b.draw(ctx));
      requestAnimationFrame(loop);
    }
    canvas.addEventListener('click', e => {
      const rect = canvas.getBoundingClientRect();
      const x = e.clientX - rect.left, y = e.clientY - rect.top;
      if (selectedTower === 'basic') towers.push(new BasicTower(x,y));
      else if (selectedTower === 'sniper') towers.push(new SniperTower(x,y));
      else towers.push(new FrostTower(x,y));
    });
    window.addEventListener('load', init);
  </script>
</body>
</html>
