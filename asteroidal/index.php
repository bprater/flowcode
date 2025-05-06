<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Asteroids Clone</title>
  <style>
    html, body {
      margin: 0;
      padding: 0;
      overflow: hidden;
      background: black;
      font-family: sans-serif;
    }
    canvas {
      display: block;
      background-color: black;
    }
    #settings {
      position: absolute;
      top: 10px;
      right: 10px;
      background: rgba(0, 0, 0, 0.6);
      color: white;
      padding: 10px;
      border: 1px solid #444;
      border-radius: 5px;
      z-index: 10;
    }
    #settings label {
      display: block;
      margin: 5px 0;
    }
  </style>
</head>
<body>
  <div id="settings">
    <label><input type="checkbox" id="pulseColors" checked> Pulsing Asteroid Colors</label>
    <label><input type="checkbox" id="starfield" checked> Starfield Background</label>
    <label><input type="checkbox" id="glowEffect" checked> Glow Effects</label>
  </div>
  <canvas id="gameCanvas"></canvas>
  <script>
    const canvas = document.getElementById("gameCanvas");
    const ctx = canvas.getContext("2d");
    canvas.width = window.innerWidth;
    canvas.height = window.innerHeight;

    let enablePulse = true;
    let enableStarfield = true;
    let enableGlow = true;

    const stars = Array.from({ length: 100 }, () => ({
      x: Math.random() * canvas.width,
      y: Math.random() * canvas.height,
      size: Math.random() * 2,
      speed: Math.random() * 0.5 + 0.2
    }));

    document.getElementById("pulseColors").onchange = e => enablePulse = e.target.checked;
    document.getElementById("starfield").onchange = e => enableStarfield = e.target.checked;
    document.getElementById("glowEffect").onchange = e => enableGlow = e.target.checked;

    function rand(min, max) {
      return Math.random() * (max - min) + min;
    }

    function angleToVector(angle) {
      return {
        x: Math.cos(angle),
        y: Math.sin(angle)
      };
    }

    function distance(a, b) {
      return Math.hypot(a.x - b.x, a.y - b.y);
    }

    let bullets = [];
    let asteroids = [];
    let explosions = [];
    let explosionParticles = [];
    let thrustParticles = [];
    let score = 0;
    let lives = 3;

    let mouse = { x: canvas.width / 2, y: canvas.height / 2 };
    let thrusting = false;

    class Ship {
      constructor() {
        this.pos = { x: canvas.width / 2, y: canvas.height / 2 };
        this.angle = 0;
        this.vel = { x: 0, y: 0 };
        this.radius = 15;
      }

      update() {
        const dx = mouse.x - this.pos.x;
        const dy = mouse.y - this.pos.y;
        this.angle = Math.atan2(dy, dx);

        if (thrusting) {
          const acc = angleToVector(this.angle);
          this.vel.x += acc.x * 0.1;
          this.vel.y += acc.y * 0.1;

          for (let i = 0; i < 3; i++) {
            thrustParticles.push({
              x: this.pos.x - acc.x * 15 + rand(-5, 5),
              y: this.pos.y - acc.y * 15 + rand(-5, 5),
              vx: -acc.x * rand(0.5, 1),
              vy: -acc.y * rand(0.5, 1),
              ttl: 30
            });
          }
        }

        this.vel.x *= 0.99;
        this.vel.y *= 0.99;

        this.pos.x += this.vel.x;
        this.pos.y += this.vel.y;

        if (this.pos.x < 0 || this.pos.x > canvas.width) this.vel.x *= -1;
        if (this.pos.y < 0 || this.pos.y > canvas.height) this.vel.y *= -1;
      }

      draw() {
        ctx.save();
        ctx.translate(this.pos.x, this.pos.y);
        ctx.rotate(this.angle);
        const gradient = ctx.createLinearGradient(-10, -10, 20, 0);
        gradient.addColorStop(0, "#0ff");
        gradient.addColorStop(1, "#0f0");
        ctx.fillStyle = gradient;
        ctx.beginPath();
        ctx.moveTo(20, 0);
        ctx.lineTo(-10, -10);
        ctx.lineTo(-10, 10);
        ctx.closePath();
        ctx.fill();

        if (thrusting) {
          ctx.fillStyle = "orange";
          ctx.beginPath();
          ctx.moveTo(-10, -5);
          ctx.lineTo(-18, 0);
          ctx.lineTo(-10, 5);
          ctx.closePath();
          ctx.fill();
        }

        ctx.restore();
      }

      shoot() {
        const dir = angleToVector(this.angle);
        bullets.push({
          x: this.pos.x + dir.x * 20,
          y: this.pos.y + dir.y * 20,
          vx: dir.x * 5,
          vy: dir.y * 5,
          ttl: 60
        });
        playSound("shoot");
      }
    }

    function createAsteroid(x, y, size) {
      const points = [];
      const vertexCount = Math.floor(rand(7, 12));
      for (let i = 0; i < vertexCount; i++) {
        const angle = (Math.PI * 2 / vertexCount) * i;
        const radius = rand(size * 0.6, size);
        points.push({ angle, radius });
      }

      return {
        x: x,
        y: y,
        vx: rand(-1.5, 1.5),
        vy: rand(-1.5, 1.5),
        size: size,
        radius: size / 2,
        points: points,
        rotation: rand(-0.03, 0.03),
        angleOffset: 0,
        hue: rand(0, 360)
      };
    }

    function spawnAsteroid() {
      asteroids.push(createAsteroid(rand(0, canvas.width), rand(0, canvas.height), rand(40, 60)));
    }

    function playSound(type) {
      const ctx = new (window.AudioContext || window.webkitAudioContext)();
      const osc = ctx.createOscillator();
      const gain = ctx.createGain();
      osc.type = type === "shoot" ? "square" : "sawtooth";
      osc.frequency.value = type === "shoot" ? 600 : 100;
      gain.gain.setValueAtTime(0.1, ctx.currentTime);
      osc.connect(gain);
      gain.connect(ctx.destination);
      osc.start();
      osc.stop(ctx.currentTime + 0.1);
    }

    const ship = new Ship();
    for (let i = 0; i < 5; i++) spawnAsteroid();

    function update() {
      if (enableGlow) {
        ctx.shadowBlur = 10;
        ctx.shadowColor = "white";
      } else {
        ctx.shadowBlur = 0;
      }

      ctx.clearRect(0, 0, canvas.width, canvas.height);

      if (enableStarfield) {
        ctx.fillStyle = "black";
        ctx.fillRect(0, 0, canvas.width, canvas.height);
        ctx.fillStyle = "white";
        stars.forEach(s => {
          s.y += s.speed;
          if (s.y > canvas.height) s.y = 0;
          ctx.beginPath();
          ctx.arc(s.x, s.y, s.size, 0, Math.PI * 2);
          ctx.fill();
        });
      }

      ship.update();
      ship.draw();

      thrustParticles = thrustParticles.filter(p => p.ttl-- > 0);
      thrustParticles.forEach(p => {
        p.x += p.vx;
        p.y += p.vy;
        ctx.fillStyle = "orange";
        ctx.beginPath();
        ctx.arc(p.x, p.y, 2, 0, Math.PI * 2);
        ctx.fill();
      });

      bullets = bullets.filter(b => b.ttl-- > 0);
      bullets.forEach(b => {
        b.x += b.vx;
        b.y += b.vy;
        ctx.beginPath();
        ctx.arc(b.x, b.y, 2, 0, Math.PI * 2);
        ctx.fillStyle = "white";
        ctx.fill();
      });

      asteroids.forEach((a, i) => {
        a.x += a.vx;
        a.y += a.vy;
        a.angleOffset += a.rotation;

        if (a.x < 0 || a.x > canvas.width) a.vx *= -1;
        if (a.y < 0 || a.y > canvas.height) a.vy *= -1;

        ctx.save();
        ctx.translate(a.x, a.y);
        ctx.rotate(a.angleOffset);
        let hue = a.hue;
        if (enablePulse) hue += Math.sin(performance.now() / 200) * 20;
        ctx.fillStyle = `hsl(${hue}, 100%, 60%)`;
        ctx.beginPath();
        a.points.forEach((p, i) => {
          const x = Math.cos(p.angle) * p.radius;
          const y = Math.sin(p.angle) * p.radius;
          if (i === 0) ctx.moveTo(x, y);
          else ctx.lineTo(x, y);
        });
        ctx.closePath();
        ctx.fill();
        ctx.restore();

        bullets.forEach((b, j) => {
          if (distance(a, b) < a.radius) {
            playSound("explode");
            score += Math.floor(100 * (60 / a.radius));
            bullets.splice(j, 1);
            asteroids.splice(i, 1);
            explosions.push({ x: a.x, y: a.y, ttl: 30, radius: a.radius });
            for (let k = 0; k < 20; k++) {
              explosionParticles.push({
                x: a.x,
                y: a.y,
                vx: rand(-2, 2),
                vy: rand(-2, 2),
                ttl: 40 + Math.random() * 20
              });
            }
            if (a.radius > 15) {
              for (let s = 0; s < 3; s++) {
                asteroids.push(createAsteroid(a.x, a.y, a.radius));
              }
            }
          }
        });
      });

      explosions = explosions.filter(e => e.ttl-- > 0);
      explosions.forEach(e => {
        ctx.beginPath();
        const r = e.radius * (1 + (30 - e.ttl) / 15);
        ctx.arc(e.x, e.y, r, 0, Math.PI * 2);
        ctx.strokeStyle = `rgba(255, 100, 0, ${e.ttl / 30})`;
        ctx.stroke();
      });

      explosionParticles = explosionParticles.filter(p => p.ttl-- > 0);
      explosionParticles.forEach(p => {
        p.x += p.vx;
        p.y += p.vy;
        ctx.fillStyle = `rgba(255,${Math.floor(rand(50, 150))},0,${p.ttl / 60})`;
        ctx.beginPath();
        ctx.arc(p.x, p.y, 2, 0, Math.PI * 2);
        ctx.fill();
      });

      ctx.fillStyle = "white";
      ctx.fillText("Score: " + score, 10, 20);
      ctx.fillText("Lives: " + lives, 10, 40);

      requestAnimationFrame(update);
    }

    update();

    canvas.addEventListener("mousemove", e => {
      mouse.x = e.clientX;
      mouse.y = e.clientY;
    });

    window.addEventListener("mousedown", e => {
      if (e.button === 0) ship.shoot();
      if (e.button === 2) thrusting = true;
    });

    window.addEventListener("mouseup", e => {
      if (e.button === 2) thrusting = false;
    });

    window.addEventListener("contextmenu", e => e.preventDefault());
  </script>
</body>
</html>
