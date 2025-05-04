<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Wheezy</title>
  <style>
    body { margin: 0; overflow: hidden; background: #111; color: #fff; font-family: sans-serif; }
    #loading { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center; }
    #progressContainer { width: 300px; height: 20px; background: #333; margin-top: 10px; border-radius: 10px; overflow: hidden; }
    #progressBar { width: 0%; height: 100%; background: #4caf50; }
    canvas { display: none; background: #111; }
  </style>
</head>
<body>
  <div id="loading">
    <div>Generating dungeon: <span id="progressText">0%</span></div>
    <div id="progressContainer"><div id="progressBar"></div></div>
  </div>
  <canvas id="gameCanvas" width="640" height="480"></canvas>
  <script>
    const canvas = document.getElementById('gameCanvas');
    const ctx = canvas.getContext('2d');
    ctx.imageSmoothingEnabled = false;

    // Create textured floor pattern
    const patternCanvas = document.createElement('canvas');
    patternCanvas.width = patternCanvas.height = 8;
    const pCtx = patternCanvas.getContext('2d');
    pCtx.fillStyle = '#444'; pCtx.fillRect(0, 0, 8, 8);
    pCtx.fillStyle = '#555';
    for (let i = 0; i < 8; i += 4) for (let j = 0; j < 8; j += 4) pCtx.fillRect(i, j, 2, 2);
    const floorPattern = ctx.createPattern(patternCanvas, 'repeat');

    // Constants
    const TILE_SIZE = 32;
    const VIEW_COLS = 20;
    const VIEW_ROWS = 15;
    const MAP_COLS = 40;
    const MAP_ROWS = 40;
    const MINI_SCALE = 4;
    const ROOM_COUNT = 6, ROOM_MIN = 8, ROOM_MAX = 14;
    const TREASURE_COUNT = 20, MONSTER_COUNT = 10;
    const radius = TILE_SIZE * 0.4;

    // State variables
    let map, roomMap, rooms;
    let player, monsters, treasures, bullets;
    let camX = 0, camY = 0;
    const keys = {};
    let score = 0;

    function updateProgress(p) {
      document.getElementById('progressBar').style.width = p + '%';
      document.getElementById('progressText').textContent = p + '%';
    }

    async function generateMap() {
      // Initial UI yield
      updateProgress(0);
      await new Promise(r => setTimeout(r, 0));

      map = Array.from({ length: MAP_ROWS }, () => Array(MAP_COLS).fill(1));
      roomMap = Array.from({ length: MAP_ROWS }, () => Array(MAP_COLS).fill(false));
      rooms = [];

      // Phase 1: carve rooms
      for (let i = 0; i < ROOM_COUNT; i++) {
        let carved = false, tries = 0;
        while (!carved && tries < ROOM_COUNT * 5) {
          tries++;
          const w = Math.floor(Math.random() * (ROOM_MAX - ROOM_MIN + 1)) + ROOM_MIN;
          const h = Math.floor(Math.random() * (ROOM_MAX - ROOM_MIN + 1)) + ROOM_MIN;
          const x = Math.floor(Math.random() * (MAP_COLS - w - 2)) + 1;
          const y = Math.floor(Math.random() * (MAP_ROWS - h - 2)) + 1;
          if (!rooms.some(r => x <= r.x + r.w && x + w >= r.x && y <= r.y + r.h && y + h >= r.y)) {
            const type = Math.random() < 0.3 ? 'circle' : 'rect';
            if (type === 'circle') {
              const cx = x + Math.floor(w / 2), cy = y + Math.floor(h / 2);
              const r = Math.floor(Math.min(w, h) / 2);
              rooms.push({ x: cx - r, y: cy - r, w: 2 * r, h: 2 * r, type, cx, cy, r });
              for (let yy = cy - r; yy <= cy + r; yy++) for (let xx = cx - r; xx <= cx + r; xx++) {
                if (xx >= 0 && xx < MAP_COLS && yy >= 0 && yy < MAP_ROWS && (xx - cx) ** 2 + (yy - cy) ** 2 <= r * r) {
                  map[yy][xx] = 0;
                  roomMap[yy][xx] = true;
                }
              }
            } else {
              rooms.push({ x, y, w, h, type });
              for (let yy = y; yy < y + h; yy++) for (let xx = x; xx < x + w; xx++) {
                map[yy][xx] = 0;
                roomMap[yy][xx] = true;
              }
            }
            carved = true;
          }
        }
        updateProgress(Math.floor(((i + 1) / ROOM_COUNT) * 25));
        await new Promise(r => setTimeout(r, 0));
      }

      // Phase 2: connect rooms
      for (let i = 1; i < rooms.length; i++) {
        const a = rooms[i - 1], b = rooms[i];
        const x1 = a.type === 'rect' ? a.x + Math.floor(a.w / 2) : a.cx;
        const y1 = a.type === 'rect' ? a.y + Math.floor(a.h / 2) : a.cy;
        const x2 = b.type === 'rect' ? b.x + Math.floor(b.w / 2) : b.cx;
        const y2 = b.type === 'rect' ? b.y + Math.floor(b.h / 2) : b.cy;
        if (Math.random() < 0.5) {
          for (let xx = Math.min(x1, x2); xx <= Math.max(x1, x2); xx++) map[y1][xx] = 0;
          for (let yy = Math.min(y1, y2); yy <= Math.max(y1, y2); yy++) map[yy][x2] = 0;
        } else {
          for (let yy = Math.min(y1, y2); yy <= Math.max(y1, y2); yy++) map[yy][x1] = 0;
          for (let xx = Math.min(x1, x2); xx <= Math.max(x1, x2); xx++) map[y2][xx] = 0;
        }
        updateProgress(25 + Math.floor((i / (rooms.length - 1)) * 25));
        await new Promise(r => setTimeout(r, 0));
      }

      // Phase 3: place monsters
      monsters = [];
      while (monsters.length < MONSTER_COUNT) {
        const ix = Math.floor(Math.random() * MAP_COLS);
        const iy = Math.floor(Math.random() * MAP_ROWS);
        if (roomMap[iy][ix]) {
          const rnd = Math.random();
          const type = rnd < 0.33 ? 'normal' : rnd < 0.66 ? 'fast' : 'hunter';
          const speed = type === 'fast' ? 2 : 1;
          monsters.push({ type, speed, x: ix * TILE_SIZE + radius, y: iy * TILE_SIZE + radius, vx: 0, vy: 0, changeTimer: Math.random() * 100 + 50 });
          updateProgress(50 + Math.floor((monsters.length / MONSTER_COUNT) * 25));
          await new Promise(r => setTimeout(r, 0));
        }
      }

      // Phase 4: place treasures
      treasures = [];
      while (treasures.length < TREASURE_COUNT) {
        const ix = Math.floor(Math.random() * MAP_COLS);
        const iy = Math.floor(Math.random() * MAP_ROWS);
        if (roomMap[iy][ix]) {
          treasures.push({ x: ix * TILE_SIZE + TILE_SIZE / 2, y: iy * TILE_SIZE + TILE_SIZE / 2 });
          updateProgress(75 + Math.floor((treasures.length / TREASURE_COUNT) * 25));
          await new Promise(r => setTimeout(r, 0));
        }
      }

      updateProgress(100);
    }

    function setupEntities() {
      const f = rooms[0];
      const sx = f.type === 'rect' ? f.x + Math.floor(f.w / 2) : f.cx;
      const sy = f.type === 'rect' ? f.y + Math.floor(f.h / 2) : f.cy;
      player = { x: sx * TILE_SIZE + radius, y: sy * TILE_SIZE + radius, vx: 0, vy: 0, speed: 2 };
      bullets = [];
    }

    function setupInput() {
      window.addEventListener('keydown', e => {
        if (['ArrowUp','ArrowDown','ArrowLeft','ArrowRight','Shift'].includes(e.key)) keys[e.key] = true;
        if (e.key === ' ') { shoot(); e.preventDefault(); }
      });
      window.addEventListener('keyup', e => {
        if (['ArrowUp','ArrowDown','ArrowLeft','ArrowRight','Shift'].includes(e.key)) keys[e.key] = false;
      });
    }

    function updateCamera() {
      camX = Math.max(0, Math.min(MAP_COLS * TILE_SIZE - VIEW_COLS * TILE_SIZE,
        Math.floor(player.x - VIEW_COLS * TILE_SIZE / 2)));
      camY = Math.max(0, Math.min(MAP_ROWS * TILE_SIZE - VIEW_ROWS * TILE_SIZE,
        Math.floor(player.y - VIEW_ROWS * TILE_SIZE / 2)));
    }

    function moveEntity(e, dx, dy) {
      const nx = e.x + dx;
      const ny = e.y + dy;
      const ex = dx ? nx + Math.sign(dx) * radius : e.x;
      const ey = dy ? ny + Math.sign(dy) * radius : e.y;
      // horizontal
      if (dx) {
        const ty1 = Math.floor((e.y - radius) / TILE_SIZE);
        const ty2 = Math.floor((e.y + radius) / TILE_SIZE);
        const tx = Math.floor(ex / TILE_SIZE);
        if (map[ty1][tx] === 0 && map[ty2][tx] === 0) e.x = nx;
      }
      // vertical
      if (dy) {
        const tx1 = Math.floor((e.x - radius) / TILE_SIZE);
        const tx2 = Math.floor((e.x + radius) / TILE_SIZE);
        const ty = Math.floor(ey / TILE_SIZE);
        if (map[ty][tx1] === 0 && map[ty][tx2] === 0) e.y = ny;
      }
    }

    function shoot() {
      if (!monsters.length) return;
      let nearest = null, dist = Infinity;
      for (const m of monsters) {
        const d = Math.hypot(m.x - player.x, m.y - player.y);
        if (d < dist) { dist = d; nearest = m; }
      }
      if (nearest) bullets.push({ x: player.x, y: player.y,
        dx: (nearest.x - player.x) / dist,
        dy: (nearest.y - player.y) / dist,
        speed: 5, length: 10 });
    }

    function update() {
      const moveSpeed = player.speed * (keys['Shift'] ? 2 : 1);
      if (keys['ArrowLeft']) moveEntity(player, -moveSpeed, 0);
      if (keys['ArrowRight']) moveEntity(player, moveSpeed, 0);
      if (keys['ArrowUp']) moveEntity(player, 0, -moveSpeed);
      if (keys['ArrowDown']) moveEntity(player, 0, moveSpeed);
      // monsters behavior
      for (const m of monsters) {
        if (m.type === 'hunter') {
          const dx = player.x - m.x;
          const dy = player.y - m.y;
          const d = Math.hypot(dx, dy) || 1;
          moveEntity(m, (dx / d) * m.speed, (dy / d) * m.speed);
        } else {
          m.changeTimer--;
          if (m.changeTimer <= 0) {
            m.changeTimer = Math.random() * 100 + 50;
            const dir = Math.floor(Math.random() * 4);
            m.vx = m.vy = 0;
            if (dir === 0) m.vx = m.speed;
            if (dir === 1) m.vx = -m.speed;
            if (dir === 2) m.vy = m.speed;
            if (dir === 3) m.vy = -m.speed;
          }
          if (m.vx || m.vy) moveEntity(m, m.vx, m.vy);
        }
      }
      // bullets
      for (let i = bullets.length - 1; i >= 0; i--) {
        const b = bullets[i];
        b.x += b.dx * b.speed;
        b.y += b.dy * b.speed;
        const tx = Math.floor(b.x / TILE_SIZE);
        const ty = Math.floor(b.y / TILE_SIZE);
        if (tx < 0 || ty < 0 || tx >= MAP_COLS || ty >= MAP_ROWS || map[ty][tx] === 1) {
          bullets.splice(i, 1);
          continue;
        }
        for (let j = monsters.length - 1; j >= 0; j--) {
          const m = monsters[j];
          if (Math.hypot(b.x - m.x, b.y - m.y) < radius) {
            monsters.splice(j, 1);
            bullets.splice(i, 1);
            break;
          }
        }
      }
      // collect treasures
      for (let i = treasures.length - 1; i >= 0; i--) {
        const t = treasures[i];
        if (Math.hypot(player.x - t.x, player.y - t.y) < radius + TILE_SIZE * 0.25) {
          treasures.splice(i, 1);
          score++;
        }
      }
      updateCamera();
    }

    function draw() {
      ctx.clearRect(0, 0, canvas.width, canvas.height);
      const startCol = Math.floor(camX / TILE_SIZE);
      const startRow = Math.floor(camY / TILE_SIZE);
      // main view
      for (let r = 0; r < VIEW_ROWS + 1; r++) {
        for (let c = 0; c < VIEW_COLS + 1; c++) {
          const mx = startCol + c;
          const my = startRow + r;
          if (map[my] && map[my][mx] !== undefined) {
            ctx.fillStyle = map[my][mx] === 1 ? '#333' : floorPattern;
            ctx.fillRect(c * TILE_SIZE - (camX % TILE_SIZE), r * TILE_SIZE - (camY % TILE_SIZE), TILE_SIZE, TILE_SIZE);
          }
        }
      }
      // treasures
      for (const t of treasures) {
        const tx = t.x - camX;
        const ty = t.y - camY;
        ctx.fillStyle = 'gold';
        ctx.beginPath();
        ctx.arc(tx, ty, TILE_SIZE * 0.2, 0, 2 * Math.PI);
        ctx.fill();
        ctx.strokeStyle = '#aa7a00'; ctx.lineWidth = 2; ctx.stroke();
      }
      // bullets
      ctx.strokeStyle = 'red'; ctx.lineWidth = 3;
      for (const b of bullets) {
        const sx = b.x - camX;
        const sy = b.y - camY;
        const ex = sx + b.dx * b.length;
        const ey = sy + b.dy * b.length;
        ctx.beginPath(); ctx.moveTo(sx, sy); ctx.lineTo(ex, ey); ctx.stroke();
      }
      // monsters
      for (const m of monsters) {
        const mx = m.x - camX;
        const my = m.y - camY;
        const g = ctx.createRadialGradient(mx, my, radius * 0.2, mx, my, radius);
        if (m.type === 'normal') { g.addColorStop(0, '#ffb347'); g.addColorStop(1, '#ffcc00'); }
        else if (m.type === 'fast') { g.addColorStop(0, '#ff4d4d'); g.addColorStop(1, '#cc0000'); }
        else { g.addColorStop(0, '#66ff66'); g.addColorStop(1, '#009900'); }
        ctx.fillStyle = g;
        ctx.beginPath(); ctx.moveTo(mx, my - radius);
        ctx.lineTo(mx - radius, my + radius);
        ctx.lineTo(mx + radius, my + radius); ctx.closePath(); ctx.fill();
      }
      // player
      const px = player.x - camX;
      const py = player.y - camY;
      const pg = ctx.createRadialGradient(px, py, radius * 0.2, px, py, radius);
      pg.addColorStop(0, '#00d2ff'); pg.addColorStop(1, '#004e92'); ctx.fillStyle = pg;
      ctx.beginPath(); ctx.arc(px, py, radius, 0, Math.PI * 2); ctx.fill();
      // score
      ctx.fillStyle = '#fff'; ctx.font = '20px sans-serif'; ctx.fillText('Score: ' + score, 10, 24);
      // minimap
      const miniW = MAP_COLS * MINI_SCALE;
      const miniH = MAP_ROWS * MINI_SCALE;
      const margin = 10;
      ctx.save();
      ctx.globalAlpha = 0.5;
      ctx.fillStyle = '#000'; ctx.fillRect(canvas.width - miniW - margin, margin, miniW, miniH);
      for (let y = 0; y < MAP_ROWS; y++) {
        for (let x = 0; x < MAP_COLS; x++) {
          ctx.fillStyle = map[y][x] === 1 ? '#333' : '#555';
          ctx.fillRect(canvas.width - miniW - margin + x * MINI_SCALE,
                       margin + y * MINI_SCALE,
                       MINI_SCALE, MINI_SCALE);
        }
      }
      // player ping
      ctx.fillStyle = 'cyan';
      const pxm = Math.floor(player.x / TILE_SIZE * MINI_SCALE);
      const pym = Math.floor(player.y / TILE_SIZE * MINI_SCALE);
      ctx.beginPath();
      ctx.arc(canvas.width - miniW - margin + pxm, margin + pym, MINI_SCALE, 0, 2 * Math.PI);
      ctx.fill();
      ctx.restore();
    }

    function loop() {
      update();
      draw();
      requestAnimationFrame(loop);
    }

    // Initialize
    generateMap().then(() => {
      setupEntities();
      document.getElementById('loading').style.display = 'none';
      canvas.style.display = 'block';
      setupInput();
      updateCamera();
      loop();
    });
  </script>
</body>
</html>
