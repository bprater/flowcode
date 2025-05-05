<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <title>Vampire Studs</title>
  <style>
    body { margin: 0; overflow: hidden; background: #000; color: #0f0; font-family: sans-serif; }
    canvas { display: block; margin: 0 auto; background: #222; }
    #hud { position: absolute; top: 10px; left: 10px; color: #0f0; font-size: 16px; z-index: 10; }
    #controls { position: absolute; top: 40px; left: 10px; color: #0f0; font-size: 14px; z-index: 10; }
    #controls select, #controls input { margin-left: 5px; }
  </style>
</head>
<body>
  <div id="hud">Enemies: 0</div>
  <div id="controls">
    Firing Rate: <input id="rateSlider" type="range" min="1" max="5" step="0.1" value="2">
    Sound: <select id="soundSelect">
      <option value="pew">Pew</option>
      <option value="bang">Bang</option>
      <option value="laser">Laser</option>
      <option value="shotgun">Shotgun</option>
      <option value="pop" selected>Pop</option>
    </select>
  </div>
  <canvas id="gameCanvas" width="480" height="800"></canvas>
  <script>
    console.log('Vampire Studs script loaded');
    // Audio
    const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
    const soundSelect = document.getElementById('soundSelect');
    function playShot() {
      const osc = audioCtx.createOscillator();
      const gain = audioCtx.createGain();
      osc.type = 'sine';
      osc.frequency.setValueAtTime(200, audioCtx.currentTime);
      gain.gain.setValueAtTime(0.2, audioCtx.currentTime);
      gain.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + 0.05);
      osc.connect(gain).connect(audioCtx.destination);
      osc.start(); osc.stop(audioCtx.currentTime + 0.05);
    }

    // Canvas & HUD refs
    const canvas = document.getElementById('gameCanvas');
    const ctx = canvas.getContext('2d');
    const hud = document.getElementById('hud');
    const rateSlider = document.getElementById('rateSlider');

    // World
    const VIEW_W = canvas.width, VIEW_H = canvas.height;
    const WORLD_W = 2000, WORLD_H = 2000, TILE = 100;

    // Player
    const baseSpeed = 200;
    const player = { x: 0, y: 0, speed: baseSpeed, angle: 0, radius: 15 };
    let speedTimer = 0;

    // Input
    let keys = {};
    window.addEventListener('keydown', e => keys[e.key.toLowerCase()] = true);
    window.addEventListener('keyup',   e => keys[e.key.toLowerCase()] = false);

    // Camera
    function getCam() {
      const x = Math.min(Math.max(player.x - VIEW_W/2, 0), WORLD_W - VIEW_W);
      const y = Math.min(Math.max(player.y - VIEW_H/2, 0), WORLD_H - VIEW_H);
      return { x, y };
    }

    // Collision utils
    function rectOverlap(a, b) {
      return a.x < b.x + b.w && a.x + a.w > b.x && a.y < b.y + b.h && a.y + a.h > b.y;
    }
    function circRect(cx, cy, r, rx, ry, rw, rh) {
      const cxp = Math.max(rx, Math.min(cx, rx + rw));
      const cyp = Math.max(ry, Math.min(cy, ry + rh));
      const dx = cx - cxp, dy = cy - cyp;
      return dx * dx + dy * dy < r * r;
    }

    // Obstacles
    let obstacles = [];
    function genObs() {
      obstacles = [];
      const worldArea = WORLD_W * WORLD_H * 0.05;
      let used = 0;
      const clusters = 5, cSize = 150, minB = 20, maxB = 60, dens = 0.4;
      // clusters
      for (let i = 0; i < clusters; i++) {
        const cx = Math.random() * (WORLD_W - cSize);
        const cy = Math.random() * (WORLD_H - cSize);
        const target = cSize * cSize * dens;
        let cl = 0, t = 0;
        while (cl < target && t < 500) {
          t++;
          const w = minB + Math.random() * (maxB - minB);
          const h = minB + Math.random() * (maxB - minB);
          const x = cx + Math.random() * (cSize - w);
          const y = cy + Math.random() * (cSize - h);
          const ob = { x, y, w, h };
          if (!obstacles.some(o => rectOverlap(o, ob))) {
            obstacles.push(ob);
            cl += w * h;
            used += w * h;
          }
        }
      }
      // scattered
      let tries = 0;
      while (used < worldArea && tries < 50000) {
        tries++;
        const w = minB + Math.random() * (maxB - minB);
        const h = minB + Math.random() * (maxB - minB);
        const x = Math.random() * (WORLD_W - w);
        const y = Math.random() * (WORLD_H - h);
        const ob = { x, y, w, h };
        if (!obstacles.some(o => rectOverlap(o, ob))) {
          obstacles.push(ob);
          used += w * h;
        }
      }
    }

    // Pickups
    let pickups = [];
    function genPick() {
      pickups = [];
      const cnt = { weapon: 10, speed: 10 };
      const sz = 20;
      for (let type in cnt) {
        for (let i = 0; i < cnt[type]; i++) {
          let x, y, t = 0;
          do {
            x = Math.random() * (WORLD_W - sz);
            y = Math.random() * (WORLD_H - sz);
            t++;
          } while (obstacles.some(o => rectOverlap(o, { x, y, w: sz, h: sz })) && t < 1000);
          if (t < 1000) pickups.push({ x, y, w: sz, h: sz, type });
        }
      }
    }

    // Enemies
    let enemies = [];
    const types = [{ color: '#f00', size: 20, speed: 50 }, { color: '#0ff', size: 15, speed: 80 }, { color: '#f0f', size: 25, speed: 30 }];
    function spawnE() {
      const cam = getCam();
      let x, y, t, ok = false, tt = 0;
      while (!ok && tt < 1000) {
        tt++;
        t = types[Math.floor(Math.random() * types.length)];
        x = Math.random() * (WORLD_W - 2 * t.size) + t.size;
        y = Math.random() * (WORLD_H - 2 * t.size) + t.size;
        if (!(x > cam.x && x < cam.x + VIEW_W && y > cam.y && y < cam.y + VIEW_H) &&
            !obstacles.some(o => circRect(x, y, t.size, o.x, o.y, o.w, o.h))) {
          ok = true;
        }
      }
      if (!ok) return;
      const animType = t.color === '#0ff' ? 'halo' : (Math.random() < 0.5 ? 'spin' : 'spike');
      enemies.push({ x, y, ...t, animType, animAngle: Math.random() * 2 * Math.PI });
    }

    // Projectiles
    let bullets = [];
    let rate = 2, aTimer = 0;
    let spawnT = 0, sCount = 5;
    const sInt = 3000;
    rateSlider.oninput = () => rate = parseFloat(rateSlider.value);

    // Restart game
    function restart() {
      console.log('Restarting game');
      genObs();
      genPick();
      // place player clear
      let ok = false, tt = 0;
      while (!ok && tt < 1000) {
        player.x = tt === 1 ? WORLD_W / 2 : Math.random() * (WORLD_W - 2 * player.radius) + player.radius;
        player.y = tt === 1 ? WORLD_H / 2 : Math.random() * (WORLD_H - 2 * player.radius) + player.radius;
        ok = !obstacles.some(o => circRect(player.x, player.y, player.radius, o.x, o.y, o.w, o.h));
        tt++;
      }
      console.log('Player placed at', player.x, player.y);
      player.angle = 0;
      player.speed = baseSpeed;
      speedTimer = 0;
      enemies = [];
      for (let i = 0; i < 50; i++) spawnE();
      bullets = [];
      spawnT = 0;
      sCount = 5;
      aTimer = 0;
      gameOver = false;
    }

    // Visibility filter
    function isVis(e, cam) {
      return e.x >= cam.x && e.x <= cam.x + VIEW_W && e.y >= cam.y && e.y <= cam.y + VIEW_H;
    }

    // Main update
    function update(dt) {
      if (gameOver) return;
      // movement
      const ox = player.x, oy = player.y;
      let dx = 0, dy = 0;
      if (keys['w'] || keys['arrowup']) dy--;
      if (keys['s'] || keys['arrowdown']) dy++;
      if (keys['a'] || keys['arrowleft']) dx--;
      if (keys['d'] || keys['arrowright']) dx++;
      if (dx || dy) {
        const len = Math.hypot(dx, dy);
        player.x += (dx/len) * player.speed * dt;
        player.y += (dy/len) * player.speed * dt;
        if (player.x < 0 || player.x > WORLD_W || player.y < 0 || player.y > WORLD_H) {
          player.x = ox; player.y = oy;
        } else {
          for (const o of obstacles) {
            if (circRect(player.x, player.y, player.radius, o.x, o.y, o.w, o.h)) {
              player.x = ox; player.y = oy;
              break;
            }
          }
        }
      }
      const cam = getCam();
      // face nearest
      if (enemies.length) {
        let c = enemies[0], md = Infinity;
        enemies.forEach(e => {
          const d = (e.x - player.x)**2 + (e.y - player.y)**2;
          if (d < md) { md = d; c = e; }
        });
        player.angle = Math.atan2(c.y - player.y, c.x - player.x);
      }
      // pickups
      pickups = pickups.filter(p => {
        if (circRect(player.x, player.y, player.radius, p.x, p.y, p.w, p.h)) {
          if (p.type === 'weapon') rate *= 3;
          else { speedTimer = 5; player.speed = baseSpeed * 1.5; }
          return false;
        }
        return true;
      });
      if (speedTimer > 0) {
        speedTimer -= dt;
        if (speedTimer <= 0) player.speed = baseSpeed;
      }
      // auto-attack
      aTimer += dt;
      if (aTimer >= 1 / rate) {
        const vis = enemies.filter(e => isVis(e, cam));
        if (vis.length) {
          aTimer -= 1 / rate;
          bullets.push({ x: player.x, y: player.y, dx: Math.cos(player.angle)*400, dy: Math.sin(player.angle)*400, size: 5 });
          playShot();
        }
      }
      // update bullets
      for (let i = bullets.length-1; i >= 0; i--) {
        const b = bullets[i];
        b.x += b.dx * dt;
        b.y += b.dy * dt;
        if (b.x < 0 || b.x > WORLD_W || b.y < 0 || b.y > WORLD_H || obstacles.some(o => circRect(b.x, b.y, b.size, o.x, o.y, o.w, o.h))) {
          bullets.splice(i, 1);
        }
      }
      // enemies
      enemies.forEach(e => {
        const ex = player.x - e.x, ey = player.y - e.y;
        const l = Math.hypot(ex, ey);
        e.x += (ex/l)*e.speed*dt;
        e.y += (ey/l)*e.speed*dt;
        for (const o of obstacles) {
          if (circRect(e.x, e.y, e.size, o.x, o.y, o.w, o.h)) {
            e.x -= (ex/l)*e.speed*dt;
            e.y -= (ey/l)*e.speed*dt;
            break;
          }
        }
        e.animAngle += dt;
        if (circRect(player.x, player.y, player.radius, e.x, e.y, e.size*2, e.size*2)) {
          gameOver = true;
          if (confirm('Game Over! Restart?')) restart();
        }
      });
      // bullet hits
      for (let i = enemies.length-1; i >= 0; i--) {
        for (let j = bullets.length-1; j >= 0; j--) {
          const e = enemies[i], b = bullets[j];
          if (Math.hypot(e.x - b.x, e.y - b.y) < e.size + b.size) {
            enemies.splice(i, 1);
            bullets.splice(j, 1);
            break;
          }
        }
      }
      // spawn more
      spawnT += dt * 1000;
      if (spawnT >= sInt) {
        spawnT -= sInt;
        for (let i = 0; i < sCount; i++) spawnE();
        sCount += 2;
      }
      hud.textContent = `Enemies: ${enemies.length}`;
    }

    function draw() {
      const cam = getCam();
      ctx.clearRect(0, 0, VIEW_W, VIEW_H);
      // grid
      for (let x = 0; x < WORLD_W; x += TILE) {
        for (let y = 0; y < WORLD_H; y += TILE) {
          const sx = x - cam.x, sy = y - cam.y;
          if (sx + TILE < 0 || sy + TILE < 0 || sx > VIEW_W || sy > VIEW_H) continue;
          ctx.fillStyle = ((x/TILE|0) + (y/TILE|0))%2 ? '#555' : '#444';
          ctx.fillRect(sx, sy, TILE, TILE);
        }
      }
      // obstacles
      ctx.fillStyle = '#777';
      obstacles.forEach(o => ctx.fillRect(o.x-cam.x, o.y-cam.y, o.w, o.h));
      // pickups
      pickups.forEach(p => {
        ctx.fillStyle = p.type === 'weapon' ? '#ff0' : '#0ff';
        ctx.fillRect(p.x-cam.x, p.y-cam.y, p.w, p.h);
      });
      // player
      ctx.save();
      ctx.translate(player.x-cam.x, player.y-cam.y);
      ctx.rotate(player.angle);
      ctx.fillStyle = speedTimer>0 ? '#0ff' : '#0f0';
      ctx.beginPath(); ctx.arc(0,0,player.radius,0,2*Math.PI); ctx.fill();
      ctx.fillStyle = '#333'; ctx.fillRect(-10,-20,20,6); ctx.fillRect(-5,-26,10,6);
      ctx.fillStyle = '#aaa'; ctx.fillRect(15,-2,20,4);
      ctx.restore();
      // enemies
      enemies.forEach(e => {
        ctx.save(); ctx.translate(e.x-cam.x, e.y-cam.y);
        if (e.animType==='spin') {
          ctx.rotate(e.animAngle);
          ctx.strokeStyle = e.color; ctx.lineWidth = 2;
          ctx.beginPath(); ctx.arc(0,0,e.size,0,2*Math.PI); ctx.stroke();
          ctx.beginPath(); ctx.moveTo(0,0); ctx.lineTo(e.size,0); ctx.stroke();
        } else if (e.animType==='halo') {
          const p = Math.sin(e.animAngle*2)*5;
          ctx.strokeStyle = e.color; ctx.lineWidth = 3;
          ctx.beginPath(); ctx.arc(0,0,e.size+8+p,0,2*Math.PI); ctx.stroke();
        } else {
          ctx.fillStyle = e.color;
          ctx.beginPath(); ctx.arc(0,0,e.size,0,2*Math.PI); ctx.fill();
          const spikes = 8, ms = e.size*0.5;
          for (let i=0; i<spikes; i++) {
            const ang = i*(2*Math.PI/spikes)+e.animAngle;
            const len = e.size + Math.sin(e.animAngle*3 + i)*ms;
            ctx.strokeStyle = e.color; ctx.lineWidth = 2;
            ctx.beginPath(); ctx.moveTo(Math.cos(ang)*e.size, Math.sin(ang)*e.size);
            ctx.lineTo(Math.cos(ang)*len, Math.sin(ang)*len); ctx.stroke();
          }
        }
        ctx.restore();
      });
      // bullets
      ctx.fillStyle = '#ff0';
      bullets.forEach(b => {
        ctx.beginPath(); ctx.arc(b.x-cam.x, b.y-cam.y, b.size, 0, 2*Math.PI); ctx.fill();
      });
    }

    // Start
    window.addEventListener('load', () => {
      console.log('window.onload fired');
      restart();
      lastTime = performance.now();
      gameLoop();
    });

    function gameLoop() {
      const now = performance.now();
      const dt = (now - lastTime) / 1000;
      lastTime = now;
      update(dt);
      draw();
      requestAnimationFrame(gameLoop);
    }
  </script>
</body>
</html>
