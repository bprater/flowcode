<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Monster Explorer</title>
  <style>
    body {
      margin: 0;
      padding: 0;
      font-family: sans-serif;
      background: #111;
      color: #eee;
      display: flex;
      flex-direction: column;
      align-items: center;
    }
    #app {
      text-align: center;
      padding: 20px;
    }
    #monster-canvas {
      border: 2px solid #444;
      background: #222;
    }
    select {
      margin: 10px;
      padding: 5px;
      background: #333;
      color: #eee;
      border: none;
      border-radius: 4px;
    }
  </style>
</head>
<body>
  <div id="app">
    <h1>Monster Explorer</h1>
    <select id="monster-select"></select>
    <canvas id="monster-canvas" width="400" height="400"></canvas>
  </div>
  <script>
    const canvas = document.getElementById('monster-canvas');
    const ctx = canvas.getContext('2d');

    const monsters = {
      geometricBeast: {
        name: 'Geometric Beast',
        draw(ctx, t, rotAngle) {
          const cx = 200;
          const cy = 200;
          const size = 50;

          // Pulsing red aura
          const pulseT = (t % 1000) / 1000;
          const auraRadius = size + 20 * pulseT;
          const auraAlpha = 1 - pulseT;
          ctx.fillStyle = `rgba(255,0,0,${auraAlpha.toFixed(2)})`;
          ctx.beginPath();
          ctx.arc(cx, cy, auraRadius, 0, Math.PI * 2);
          ctx.fill();

          // Body & spikes rotation
          ctx.save();
          ctx.translate(cx, cy);
          ctx.rotate(rotAngle);

          // Body (hexagon)
          ctx.fillStyle = '#a00';
          ctx.beginPath();
          for (let i = 0; i < 6; i++) {
            const angle = Math.PI / 3 * i;
            const x = size * Math.cos(angle);
            const y = size * Math.sin(angle);
            i === 0 ? ctx.moveTo(x, y) : ctx.lineTo(x, y);
          }
          ctx.closePath();
          ctx.fill();

          // Spikes
          ctx.fillStyle = '#800';
          for (let i = 0; i < 6; i++) {
            const angle = Math.PI / 3 * i;
            const x1 = (size + 5) * Math.cos(angle);
            const y1 = (size + 5) * Math.sin(angle);
            const x2 = (size + 25) * Math.cos(angle + 0.1);
            const y2 = (size + 25) * Math.sin(angle + 0.1);
            const x3 = (size + 25) * Math.cos(angle - 0.1);
            const y3 = (size + 25) * Math.sin(angle - 0.1);
            ctx.beginPath();
            ctx.moveTo(x1, y1);
            ctx.lineTo(x2, y2);
            ctx.lineTo(x3, y3);
            ctx.closePath();
            ctx.fill();
          }
          ctx.restore();

          // Eye animation (pulsing size)
          const eyeBase = 10;
          const eyePulse = 3 * Math.sin(t / 300);
          const eyeRadius = eyeBase + eyePulse;
          const pupilRadius = 5 + 2 * Math.sin(t / 200 + Math.PI);

          // Eyes
          ctx.fillStyle = '#fff';
          ctx.beginPath();
          ctx.arc(cx - 20, cy - 10, eyeRadius, 0, Math.PI * 2);
          ctx.arc(cx + 20, cy - 10, eyeRadius, 0, Math.PI * 2);
          ctx.fill();

          // Pupils
          ctx.fillStyle = '#000';
          ctx.beginPath();
          ctx.arc(cx - 20, cy - 10, pupilRadius, 0, Math.PI * 2);
          ctx.arc(cx + 20, cy - 10, pupilRadius, 0, Math.PI * 2);
          ctx.fill();

          // Angry brows
          ctx.strokeStyle = '#000';
          ctx.lineWidth = 4;
          ctx.beginPath();
          ctx.moveTo(cx - 30, cy - 20);
          ctx.lineTo(cx - 10, cy - 5);
          ctx.moveTo(cx + 10, cy - 5);
          ctx.lineTo(cx + 30, cy - 20);
          ctx.stroke();

          // Mouth (jagged)
          ctx.fillStyle = '#000';
          ctx.beginPath();
          ctx.moveTo(cx - 30, cy + 20);
          for (let i = 0; i < 6; i++) {
            const px = cx - 30 + i * 10;
            const py = cy + (i % 2 === 0 ? 10 : 20);
            ctx.lineTo(px, py);
          }
          ctx.lineTo(cx + 30, cy + 20);
          ctx.lineTo(cx + 30, cy + 30);
          ctx.lineTo(cx - 30, cy + 30);
          ctx.closePath();
          ctx.fill();
        }
      }
    };

    const select = document.getElementById('monster-select');
    Object.keys(monsters).forEach(key => {
      const opt = document.createElement('option');
      opt.value = key;
      opt.textContent = monsters[key].name;
      select.appendChild(opt);
    });

    let animationId;
    function startAnimation(monster) {
      let posX = 0;
      const monsterWidth = 100;
      let dir = 1;
      const pathSpeed = 2;
      let lastTimestamp = null;
      let rotAngle = 0;

      function animate(timestamp) {
        if (lastTimestamp === null) lastTimestamp = timestamp;
        const delta = timestamp - lastTimestamp;
        lastTimestamp = timestamp;

        // Modulate spin speed: normal for 2s, then full for 1s
        const cycle = timestamp % 3000;
        const speedFactor = cycle < 2000 ? 1 : 4;

        // Update rotation angle
        rotAngle += (delta / 1000) * speedFactor;

        // Clear canvas
        ctx.clearRect(0, 0, canvas.width, canvas.height);

        // Draw monster
        ctx.save();
        ctx.translate(posX, 0);
        monster.draw(ctx, timestamp, rotAngle);
        ctx.restore();

        // Update position along path
        posX += pathSpeed * dir;
        if (posX > canvas.width - monsterWidth || posX < 0) dir *= -1;

        animationId = requestAnimationFrame(animate);
      }
      animationId = requestAnimationFrame(animate);
    }

    function resetAnimation() {
      if (animationId) cancelAnimationFrame(animationId);
      ctx.clearRect(0, 0, canvas.width, canvas.height);
    }

    select.addEventListener('change', () => {
      resetAnimation();
      startAnimation(monsters[select.value]);
    });

    // Initialize
    select.value = Object.keys(monsters)[0];
    startAnimation(monsters[select.value]);
  </script>
</body>
</html>
