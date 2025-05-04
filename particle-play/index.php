<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Particle Rail System</title>
  <style>
    html, body {
      margin: 0;
      padding: 0;
      height: 100%;
      background-color: #111;
      color: #fff;
      font-family: sans-serif;
    }
    canvas {
      display: block;
    }
    .controls {
      position: fixed;
      top: 10px;
      left: 10px;
      background: rgba(0, 0, 0, 0.7);
      padding: 10px;
      border-radius: 8px;
    }
    .controls label {
      display: block;
      margin-bottom: 5px;
    }
    .particle-count {
      position: fixed;
      top: 10px;
      right: 10px;
      background: rgba(0, 0, 0, 0.7);
      padding: 10px;
      border-radius: 8px;
      font-size: 14px;
    }
  </style>
</head>
<body>
  <canvas id="canvas"></canvas>
  <div class="controls">
    <label>Gravity: <input type="range" min="0.1" max="2" step="0.1" id="gravity" value="0.5"></label>
    <label>Spawn Rate: <input type="range" min="1" max="20" step="1" id="spawnRate" value="5"></label>
    <label>Jump Height: <input type="range" min="0" max="5" step="0.1" id="jumpHeight" value="2"></label>
    <label>Arc Strength: <input type="range" min="10" max="100" step="1" id="arcStrength" value="20"></label>
    <label>Bounce Strength: <input type="range" min="0" max="1" step="0.05" id="bounceStrength" value="0.5"></label>
    <label>Wind Force: <input type="range" min="-1" max="1" step="0.01" id="windForce" value="0.1"></label>
  </div>
  <div class="particle-count">Particles: <span id="particleCount">0</span></div>
  <script>
    const canvas = document.getElementById("canvas");
    const ctx = canvas.getContext("2d");

    let gravity = parseFloat(document.getElementById("gravity").value);
    let spawnRate = parseInt(document.getElementById("spawnRate").value);
    let jumpHeight = parseFloat(document.getElementById("jumpHeight").value);
    let arcStrength = parseFloat(document.getElementById("arcStrength").value);
    let bounceStrength = parseFloat(document.getElementById("bounceStrength").value);
    let windForce = parseFloat(document.getElementById("windForce").value);

    const particleCountDisplay = document.getElementById("particleCount");

    document.getElementById("gravity").addEventListener("input", e => gravity = parseFloat(e.target.value));
    document.getElementById("spawnRate").addEventListener("input", e => spawnRate = parseInt(e.target.value));
    document.getElementById("jumpHeight").addEventListener("input", e => jumpHeight = parseFloat(e.target.value));
    document.getElementById("arcStrength").addEventListener("input", e => arcStrength = parseFloat(e.target.value));
    document.getElementById("bounceStrength").addEventListener("input", e => bounceStrength = parseFloat(e.target.value));
    document.getElementById("windForce").addEventListener("input", e => windForce = parseFloat(e.target.value));

    canvas.width = window.innerWidth;
    canvas.height = window.innerHeight;

    const floorY = canvas.height - 10;

    function arcY(x) {
      const radius = canvas.width / 2;
      const centerX = canvas.width / 2;
      const centerY = canvas.height / 2.5;
      const dx = x - centerX;
      if (Math.abs(dx) > radius) return centerY;
      return centerY - Math.sqrt(radius * radius - dx * dx) * (arcStrength / 100);
    }

    class Particle {
      constructor(x) {
        this.x = x;
        this.y = arcY(x);
        this.vx = (Math.random() - 0.5) * 2 * Math.abs(windForce);
        this.vy = -(Math.random() * jumpHeight + jumpHeight);
        this.radius = 1.5 + Math.random();
        this.opacity = 1;
        this.hue = Math.random() * 360;
        this.brightness = 50 + Math.random() * 50;
      }

      update() {
        const randomShift = (Math.random() - 0.5) * 0.1;
        this.vx += windForce * 0.01 + randomShift;
        this.x += this.vx;

        this.vy += gravity;
        this.y += this.vy;

        if (this.y + this.radius > floorY) {
          this.y = floorY - this.radius;
          this.vy *= -bounceStrength;
        }

        this.opacity -= 0.01;
        this.hue += 0.5;
      }

      draw(ctx) {
        const color = `hsl(${this.hue}, 100%, ${this.brightness}%)`;
        ctx.save();
        ctx.globalAlpha = Math.max(this.opacity, 0);
        ctx.shadowBlur = 8;
        ctx.shadowColor = color;
        ctx.beginPath();
        ctx.arc(this.x, this.y, this.radius, 0, Math.PI * 2);
        ctx.fillStyle = color;
        ctx.fill();
        ctx.restore();
      }

      isAlive() {
        return this.opacity > 0 && this.x >= -50 && this.x <= canvas.width + 50;
      }
    }

    const particles = [];

    function spawnParticles() {
      for (let i = 0; i < spawnRate; i++) {
        const x = Math.random() * canvas.width;
        particles.push(new Particle(x));
      }
    }

    function drawRail() {
      ctx.beginPath();
      ctx.moveTo(0, arcY(0));
      for (let x = 1; x < canvas.width; x++) {
        ctx.lineTo(x, arcY(x));
      }
      ctx.strokeStyle = "white";
      ctx.lineWidth = 2;
      ctx.stroke();
    }

    function drawFloor() {
      ctx.beginPath();
      ctx.moveTo(0, floorY);
      ctx.lineTo(canvas.width, floorY);
      ctx.strokeStyle = "#444";
      ctx.lineWidth = 2;
      ctx.stroke();
    }

    function animate() {
      ctx.fillStyle = "rgba(17, 17, 17, 0.2)";
      ctx.fillRect(0, 0, canvas.width, canvas.height);

      drawRail();
      drawFloor();
      spawnParticles();

      for (let i = particles.length - 1; i >= 0; i--) {
        const p = particles[i];
        p.update();
        if (p.isAlive()) {
          p.draw(ctx);
        } else {
          particles.splice(i, 1);
        }
      }

      particleCountDisplay.textContent = particles.length;
      requestAnimationFrame(animate);
    }

    animate();

    window.addEventListener("resize", () => {
      canvas.width = window.innerWidth;
      canvas.height = window.innerHeight;
    });
  </script>
</body>
</html>
