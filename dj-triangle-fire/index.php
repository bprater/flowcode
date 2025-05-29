<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Triangle Subdivision</title>
  <style>
    body {
      margin: 0;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      height: 100vh;
      background-color: #f0f0f0;
    }
    svg {
      width: 80vmin;
      height: 80vmin;
    }
    button, select {
      margin-top: 10px;
      padding: 10px 20px;
      font-size: 16px;
      cursor: pointer;
    }
    @keyframes rainbowColors {
      0% { fill: red; }
      16% { fill: orange; }
      33% { fill: yellow; }
      50% { fill: green; }
      66% { fill: blue; }
      83% { fill: indigo; }
      100% { fill: violet; }
    }
    @keyframes fireGlow {
      0% { fill: #ff6f00; }
      50% { fill: #ffcc00; }
      100% { fill: #ff6f00; }
    }
    @keyframes oceanWave {
      0% { fill: #0077be; }
      50% { fill: #00bfff; }
      100% { fill: #0077be; }
    }
    @keyframes forestPulse {
      0% { fill: #2e8b57; }
      50% { fill: #3cb371; }
      100% { fill: #2e8b57; }
    }
    @keyframes neonBlink {
      0% { fill: #ff00ff; }
      50% { fill: #00ffff; }
      100% { fill: #ff00ff; }
    }
    .rainbow { animation: rainbowColors 5s linear infinite; }
    .fire { animation: fireGlow 2s ease-in-out infinite; }
    .ocean { animation: oceanWave 3s ease-in-out infinite; }
    .forest { animation: forestPulse 4s ease-in-out infinite; }
    .neon { animation: neonBlink 1.5s step-end infinite; }
    .paused { animation-play-state: paused !important; }
  </style>
</head>
<body>
  <svg viewBox="0 0 100 100" id="triangleCanvas"></svg>
  <select id="colorSelect" onchange="onPatternChange()">
    <option value="rainbow">Rainbow</option>
    <option value="fire">Fire</option>
    <option value="ocean">Ocean</option>
    <option value="forest">Forest</option>
    <option value="neon">Neon</option>
  </select>
  <select id="patternSelect" onchange="onPatternChange()">
    <option value="normal">Normal</option>
    <option value="reverse">Reverse</option>
    <option value="centerOut">Center Out</option>
    <option value="spiral">Spiral</option>
    <option value="wave">Wave</option>
    <option value="burst">Radial Burst</option>
    <option value="diamond">Diamond</option>
    <option value="checker">Checkerboard</option>
  </select>
  <button onclick="pauseAnimation()">Pause</button>

  <script>
    const svg = document.getElementById("triangleCanvas");
    const colorSelect = document.getElementById("colorSelect");
    const patternSelect = document.getElementById("patternSelect");

    function drawTriangle(x1, y1, x2, y2, x3, y3, colorClass) {
      const polygon = document.createElementNS("http://www.w3.org/2000/svg", "polygon");
      polygon.setAttribute("points", `${x1},${y1} ${x2},${y2} ${x3},${y3}`);

      if (colorClass) {
        polygon.setAttribute("class", colorClass);
        const cx = (x1 + x2 + x3) / 3;
        const cy = (y1 + y2 + y3) / 3;
        const pattern = patternSelect.value;
        let delay;

        if (pattern === "normal") {
          delay = ((100 - cy) / 100) * 3;
        } else if (pattern === "reverse") {
          delay = (cy / 100) * 3;
        } else if (pattern === "centerOut") {
          const centerY = 50;
          delay = (Math.abs(cy - centerY) / 50) * 3;
        } else if (pattern === "spiral") {
          delay = ((Math.atan2(cy - 50, cx - 50) + Math.PI) / (2 * Math.PI)) * 3;
        } else if (pattern === "wave") {
          delay = ((Math.sin(cx / 100 * 2 * Math.PI) + 1) / 2) * 3;
        } else if (pattern === "burst") {
          const dx = cx - 50;
          const dy = cy - 50;
          const dist = Math.sqrt(dx * dx + dy * dy);
          delay = (dist / 70) * 3; // Normalized radial burst
        } else if (pattern === "diamond") {
          delay = (Math.abs(cx - 50) + Math.abs(cy - 50)) / 100 * 3;
        } else if (pattern === "checker") {
          const checkerX = Math.floor(cx / 10);
          const checkerY = Math.floor(cy / 10);
          delay = ((checkerX + checkerY) % 2 === 0 ? 0.5 : 0) + ((checkerX + checkerY) % 4) * 0.4;
        } else {
          delay = 0;
        }

        polygon.style.animationDelay = `${delay.toFixed(2)}s`;
      } else {
        polygon.setAttribute("fill", getRandomColor());
      }

      svg.appendChild(polygon);
    }

    function getRandomColor() {
      const colors = ["#e63946", "#f1faee", "#a8dadc", "#457b9d", "#1d3557"];
      return colors[Math.floor(Math.random() * colors.length)];
    }

    function subdivideTriangle(x1, y1, x2, y2, x3, y3, depth, colorClass) {
      if (depth === 0) {
        drawTriangle(x1, y1, x2, y2, x3, y3, colorClass);
        return;
      }
      const mx1 = (x1 + x2) / 2;
      const my1 = (y1 + y2) / 2;
      const mx2 = (x2 + x3) / 2;
      const my2 = (y2 + y3) / 2;
      const mx3 = (x3 + x1) / 2;
      const my3 = (y3 + y1) / 2;

      subdivideTriangle(x1, y1, mx1, my1, mx3, my3, depth - 1, colorClass);
      subdivideTriangle(x2, y2, mx1, my1, mx2, my2, depth - 1, colorClass);
      subdivideTriangle(x3, y3, mx2, my2, mx3, my3, depth - 1, colorClass);
      subdivideTriangle(mx1, my1, mx2, my2, mx3, my3, depth - 1, colorClass);
    }

    function generateTriangles() {
      svg.innerHTML = "";
      const colorClass = colorSelect.value;
      subdivideTriangle(50, 0, 0, 100, 100, 100, 5, colorClass);
    }

    function pauseAnimation() {
      const polygons = svg.querySelectorAll("polygon");
      polygons.forEach(p => p.classList.add("paused"));
    }

    function onPatternChange() {
      generateTriangles();
    }

    generateTriangles();
  </script>
</body>
</html>
