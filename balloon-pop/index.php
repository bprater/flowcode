<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Balloon Popping Game with Timer, Combos & Decoys</title>
  <style>
    body {
      margin: 0;
      overflow: hidden;
      background: #87ceeb;
      font-family: sans-serif;
    }
    #gameCanvas {
      display: block;
    }
    .hud {
      position: absolute;
      top: 10px;
      left: 10px;
      color: #fff;
      text-shadow: 1px 1px 2px rgba(0,0,0,0.5);
      font-size: 20px;
    }
    .hud div {
      margin-bottom: 5px;
    }
    #overlay {
      display: none;
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0,0,0,0.7);
      color: #fff;
      font-size: 48px;
      text-align: center;
      padding-top: 20%;
      z-index: 1;
    }
  </style>
</head>
<body>
  <canvas id="gameCanvas"></canvas>
  <div class="hud">
    <div>Time: <span id="timeValue">60</span>s</div>
    <div>Score: <span id="scoreValue">0</span></div>
    <div>Combo ×<span id="comboValue">1</span></div>
  </div>
  <div id="overlay"></div>

  <script>
    const canvas = document.getElementById('gameCanvas');
    const ctx = canvas.getContext('2d');
    let width = canvas.width = window.innerWidth;
    let height = canvas.height = window.innerHeight;

    let balloons = [];
    let confetti = [];
    let decoys = [];
    let failures = [];

    let score = 0;
    let combo = 1;
    let lastPopTime = 0;
    const comboResetMs = 1000;

    let timeLeft = 60;
    let gameOver = false;

    function updateScore() {
      document.getElementById('scoreValue').textContent = score;
    }
    function updateCombo() {
      document.getElementById('comboValue').textContent = combo;
    }
    function updateTime() {
      document.getElementById('timeValue').textContent = timeLeft;
    }

    window.addEventListener('resize', () => {
      width = canvas.width = window.innerWidth;
      height = canvas.height = window.innerHeight;
    });

    // Timer
    const countdown = setInterval(() => {
      if (timeLeft > 0) {
        timeLeft--; updateTime();
        if (timeLeft === 0) {
          clearInterval(countdown);
          clearInterval(spawnBalloonInterval);
          clearInterval(spawnDecoyInterval);
          gameOver = true;
          const overlay = document.getElementById('overlay');
          overlay.style.display = 'block';
          overlay.textContent = 'Game Over!';
          const finalScore = document.createElement('div');
          finalScore.style.marginTop = '20px';
          finalScore.textContent = `Final Score: ${score}`;
          overlay.appendChild(finalScore);
        }
      }
    }, 1000);

    class Balloon {
      constructor() {
        this.rx = 20 + Math.random() * 30;
        this.ry = this.rx * 1.2;
        this.x = this.rx + Math.random() * (width - 2 * this.rx);
        this.y = height + this.ry;
        const r = Math.random();
        if (r < 0.1) {
          this.basePoints = 5; this.speed = 0.5 + Math.random(); this.color = '#FFD700';
        } else if (r < 0.3) {
          this.basePoints = 2; this.speed = 2 + Math.random() * 2; this.color = `hsl(${Math.random()*360},90%,50%)`;
        } else {
          this.basePoints = 1; this.speed = 1 + Math.random()*2; this.color = `hsl(${Math.random()*360},70%,60%)`;
        }
        this.state = 'floating';
        this.frame = 0;
        this.totalFrames = 30;
      }
      startPop() {
        this.state = 'popping'; this.frame = 0; this.spawnConfetti();
      }
      spawnConfetti() {
        const colors = ['#f44336','#e91e63','#9c27b0','#3f51b5','#2196f3','#03a9f4','#00bcd4','#009688','#4caf50','#8bc34a','#cddc39','#ffeb3b','#ffc107','#ff9800'];
        for (let i=0;i<20;i++){
          const angle = Math.random()*Math.PI*2;
          const speed = 2+Math.random()*2;
          confetti.push(new Confetti(this.x,this.y,Math.cos(angle)*speed,Math.sin(angle)*speed,colors[Math.floor(Math.random()*colors.length)]));
        }
      }
      update() {
        if (this.state==='floating') {
          this.y -= this.speed;
          if (this.y - this.ry <= 0) {
            this.state='exploded'; this.frame=0;
            score -= this.basePoints; updateScore();
          }
        } else {
          this.frame++;
        }
      }
      draw() {
        if (this.state === 'floating') {
          // balloon
          const grad = ctx.createRadialGradient(this.x - this.rx/3, this.y - this.ry/3, this.rx/8, this.x, this.y, this.rx);
          grad.addColorStop(0,'#fff'); grad.addColorStop(0.2,this.color); grad.addColorStop(1,this.color);
          ctx.fillStyle=grad; ctx.beginPath(); ctx.ellipse(this.x,this.y,this.rx,this.ry,0,0,Math.PI*2); ctx.fill();
          // outline
          if (combo>1) { ctx.save(); ctx.strokeStyle='#fff'; ctx.lineWidth=4; ctx.stroke(); ctx.restore(); }
          ctx.strokeStyle='#800'; ctx.lineWidth=2; ctx.stroke();
          // knot
          ctx.fillStyle='#800'; ctx.beginPath(); ctx.moveTo(this.x-5,this.y+this.ry-2); ctx.lineTo(this.x+5,this.y+this.ry-2); ctx.lineTo(this.x,this.y+this.ry+5); ctx.fill();
          // string
          ctx.strokeStyle='#555'; ctx.lineWidth=1; ctx.beginPath(); ctx.moveTo(this.x,this.y+this.ry+5); ctx.lineTo(this.x,this.y+this.ry+30); ctx.stroke();
        } else if (this.state==='popping') {
          const t=this.frame/this.totalFrames; const alpha=1-t;
          ctx.globalAlpha=alpha; ctx.strokeStyle=this.color; ctx.lineWidth=2;
          for(let i=0;i<12;i++){ const angle=(Math.PI*2/12)*i; const len=this.rx*2*t; ctx.beginPath(); ctx.moveTo(this.x,this.y); ctx.lineTo(this.x+Math.cos(angle)*len,this.y+Math.sin(angle)*len); ctx.stroke(); }
          ctx.globalAlpha=1;
        } else if (this.state==='exploded') {
          const t=this.frame/this.totalFrames; const alpha=1-t;
          ctx.globalAlpha=alpha; ctx.strokeStyle='#fff'; ctx.lineWidth=4; ctx.beginPath(); ctx.arc(this.x,this.y,this.ry*(1+t*3),0,Math.PI*2); ctx.stroke();
          ctx.globalAlpha=1;
        }
      }
      isDone() { return this.state!=='floating' && this.frame>=this.totalFrames; }
    }
    class Confetti {
      constructor(x,y,vx,vy,c){this.x=x;this.y=y;this.vx=vx;this.vy=vy;this.color=c;this.size=4+Math.random()*4;this.frame=0;this.totalFrames=60;}
      update(){this.vy+=0.1;this.x+=this.vx;this.y+=this.vy;this.frame++;}
      draw(){const t=this.frame/this.totalFrames;ctx.globalAlpha=1-t;ctx.fillStyle=this.color;ctx.fillRect(this.x,this.y,this.size,this.size);ctx.globalAlpha=1;}
      isDone(){return this.frame>=this.totalFrames;}
    }
    class Decoy {
      constructor(){this.type=Math.random()<0.5?'umbrella':'bird';this.size=20+Math.random()*30;this.x=this.size+Math.random()*(width-2*this.size);this.y=height+this.size;this.speed=1+Math.random();this.done=false;}
      update(){this.y-=this.speed; if(this.y-this.size<=0) this.done=true;}
      draw(){ctx.save();ctx.translate(this.x,this.y); if(this.type==='umbrella'){ctx.fillStyle='#8B0000';ctx.beginPath();ctx.arc(0,0,this.size,Math.PI,0);ctx.fill();ctx.strokeStyle='#000';ctx.lineWidth=2;ctx.beginPath();ctx.moveTo(-this.size,0);ctx.quadraticCurveTo(0,this.size*0.8,this.size,0);ctx.stroke();ctx.beginPath();ctx.moveTo(0,0);ctx.lineTo(0,this.size*1.5);ctx.stroke();}else{ctx.strokeStyle='#000';ctx.lineWidth=3;ctx.beginPath();ctx.moveTo(-this.size/1.5,0);ctx.lineTo(0,-this.size/1.5);ctx.lineTo(this.size/1.5,0);ctx.stroke();}ctx.restore();}
      isClicked(mx,my){const dx=this.x-mx,dy=this.y-my;return dx*dx+dy*dy<this.size*this.size;}
    }
    class FailureEffect{constructor(x,y){this.x=x;this.y=y;this.frame=0;this.totalFrames=30;}update(){this.frame++;}draw(){const t=this.frame/this.totalFrames,alpha=1-t;ctx.globalAlpha=alpha;ctx.strokeStyle='red';ctx.lineWidth=3;for(let i=0;i<8;i++){const angle=(Math.PI*2/8)*i,len=30*t;ctx.beginPath();ctx.moveTo(this.x,this.y);ctx.lineTo(this.x+Math.cos(angle)*len,this.y+Math.sin(angle)*len);ctx.stroke();}ctx.globalAlpha=1;}isDone(){return this.frame>=this.totalFrames;}}

    function spawnBalloon(){if(!gameOver) balloons.push(new Balloon());}
    function spawnDecoy(){if(!gameOver) decoys.push(new Decoy());}
    const spawnBalloonInterval = setInterval(spawnBalloon,800);
    const spawnDecoyInterval  = setInterval(spawnDecoy,5000);

    canvas.addEventListener('click',e=>{
      if(gameOver)return;
      const rect=canvas.getBoundingClientRect();const mx=e.clientX-rect.left,my=e.clientY-rect.top;
      for(let i=balloons.length-1;i>=0;i--){const b=balloons[i];const dx=b.x-mx,dy=b.y-my;const hit=(dx*dx)/(b.rx*b.rx)+(dy*dy)/(b.ry*b.ry)<1; if(b.state==='floating'&&hit){const now=Date.now();combo=now-lastPopTime<=comboResetMs?combo+1:1;lastPopTime=now;updateCombo();const pts=b.basePoints*combo;score+=pts;updateScore();b.startPop();return;}}
      for(let i=decoys.length-1;i>=0;i--){const d=decoys[i];if(d.isClicked(mx,my)){score-=3;combo=1;updateScore();updateCombo();failures.push(new FailureEffect(d.x,d.y));d.done=true;return;}}
    });

    function animate(){
      ctx.clearRect(0,0,width,height);
      balloons.forEach(b=>{b.update();b.draw();});balloons=balloons.filter(b=>!b.isDone());
      confetti.forEach(c=>{c.update();c.draw();});confetti=confetti.filter(c=>!c.isDone());
      decoys.forEach(d=>{d.update();d.draw();});decoys=decoys.filter(d=>!d.done);
      failures.forEach(f=>{f.update();f.draw();});failures=failures.filter(f=>!f.isDone());
      if(!gameOver) requestAnimationFrame(animate);
    }
    animate();
  </script>
</body>
</html>
