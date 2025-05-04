<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>10×10 Maze Generator & Solver</title>
  <style>
    body, html { margin: 0; padding: 0; height: 100%; }
    #controls { padding: 10px; background: #f8f8f8; }
    canvas { display: block; }
    button, input { margin-right: 0.5em; }
  </style>
</head>
<body>
  <div id="controls">
    <button id="generate-button">Generate New Maze</button>
    <input type="file" id="file-input" accept="application/json">
    <span id="timer">Operation Time: 0 ms</span>
  </div>
  <canvas id="maze"></canvas>

  <script>
    const canvas = document.getElementById("maze");
    const ctx = canvas.getContext("2d");
    const generateBtn = document.getElementById("generate-button");
    const fileInput = document.getElementById("file-input");
    const timerDisplay = document.getElementById("timer");
    const controls = document.getElementById("controls");

    let cols = 10;
    let rows = 10;
    let cellSize;
    let grid = [];
    let solver;

    class Cell {
      constructor(x, y, walls) {
        this.x = x;
        this.y = y;
        this.walls = walls || [true, true, true, true];
        this.visited = false;
      }
      draw() {
        const x = this.x * cellSize;
        const y = this.y * cellSize;
        ctx.strokeStyle = "black";
        ctx.lineWidth = 2;
        if (this.walls[0]) drawLine(x, y, x + cellSize, y);
        if (this.walls[1]) drawLine(x + cellSize, y, x + cellSize, y + cellSize);
        if (this.walls[2]) drawLine(x + cellSize, y + cellSize, x, y + cellSize);
        if (this.walls[3]) drawLine(x, y + cellSize, x, y);
      }
    }

    function drawLine(x1, y1, x2, y2) {
      ctx.beginPath();
      ctx.moveTo(x1, y1);
      ctx.lineTo(x2, y2);
      ctx.stroke();
    }

    function resizeCanvas() {
      const width = window.innerWidth;
      const height = window.innerHeight - controls.offsetHeight;
      canvas.width = width;
      canvas.height = height;
      cellSize = Math.min(width / cols, height / rows);
      drawMaze();
    }

    window.addEventListener('resize', resizeCanvas);

    function generateMaze() {
      grid = [];
      for (let y = 0; y < rows; y++) {
        for (let x = 0; x < cols; x++) {
          grid.push(new Cell(x, y));
        }
      }
      let current = grid[0];
      current.visited = true;
      const stack = [];
      const start = performance.now();

      while (true) {
        const neighbors = [];
        [[0, -1], [1, 0], [0, 1], [-1, 0]].forEach(([dx, dy], i) => {
          const ni = index(current.x + dx, current.y + dy);
          if (ni > -1 && !grid[ni].visited) neighbors.push({ cell: grid[ni], dir: i });
        });

        if (neighbors.length) {
          const { cell: next, dir } = neighbors[Math.floor(Math.random() * neighbors.length)];
          const opposite = (dir + 2) % 4;
          current.walls[dir] = false;
          next.walls[opposite] = false;
          stack.push(current);
          current = next;
          current.visited = true;
        } else if (stack.length) {
          current = stack.pop();
        } else break;
      }

      const end = performance.now();
      timerDisplay.textContent = `Generation Time: ${Math.round(end - start)} ms`;
      resizeCanvas();
      startSolving();
    }

    function drawMaze() {
      ctx.clearRect(0, 0, canvas.width, canvas.height);
      grid.forEach(c => c.draw());
    }

    function index(x, y) {
      return x < 0 || y < 0 || x >= cols || y >= rows ? -1 : x + y * cols;
    }

    class Solver {
      constructor(sx, sy) {
        this.stack = [{ x: sx, y: sy }];
        this.visited = new Set();
        this.current = null;
      }
      step() {
        if (!this.stack.length) return;
        if (this.current) {
          grid[index(this.current.x, this.current.y)].draw();
        }
        this.current = this.stack.pop();
        const key = `${this.current.x},${this.current.y}`;
        if (this.visited.has(key)) return;
        this.visited.add(key);
        const cell = grid[index(this.current.x, this.current.y)];
        const cx = this.current.x * cellSize + cellSize / 2;
        const cy = this.current.y * cellSize + cellSize / 2;
        ctx.fillStyle = 'red';
        ctx.beginPath();
        ctx.arc(cx, cy, cellSize / 6, 0, Math.PI * 2);
        ctx.fill();

        [[0, -1, 0], [1, 0, 1], [0, 1, 2], [-1, 0, 3]].forEach(([dx, dy, w]) => {
          if (!cell.walls[w]) {
            const nx = this.current.x + dx;
            const ny = this.current.y + dy;
            const nk = `${nx},${ny}`;
            if (!this.visited.has(nk)) this.stack.push({ x: nx, y: ny });
          }
        });
      }
    }

    function startSolving() {
      solver = new Solver(0, 0);
      animate();
    }

    function animate() {
      solver.step();
      setTimeout(() => requestAnimationFrame(animate), 100);
    }

    fileInput.addEventListener('change', e => {
      const file = e.target.files[0];
      if (!file) return;
      const reader = new FileReader();
      reader.onload = ev => {
        const t0 = performance.now();
        const data = JSON.parse(ev.target.result);
        cols = data.cols;
        rows = data.rows;
        grid = data.maze.map(c => new Cell(c.x, c.y, c.walls));
        const t1 = performance.now();
        timerDisplay.textContent = `Load Time: ${Math.round(t1 - t0)} ms`;
        resizeCanvas();
        startSolving();
      };
      reader.readAsText(file);
    });

    generateBtn.addEventListener('click', generateMaze);

    // Initialize canvas size
    resizeCanvas();
  </script>
</body>
</html>
