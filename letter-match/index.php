<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Letter Matching Game</title>
  <style>
    body {
      font-family: 'Arial', sans-serif;
      text-align: center;
      margin: 0;
      padding: 0;
      background-color: #f7f7f7;
      overflow-x: hidden;
    }
    h1 {
      margin-top: 20px;
      color: #333;
    }
    .game-board {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      margin-top: 20px;
      gap: 10px;
      max-width: 800px;
      margin-left: auto;
      margin-right: auto;
    }
    .letter {
      width: 60px;
      height: 60px;
      display: flex;
      justify-content: center;
      align-items: center;
      font-size: 24px;
      font-weight: bold;
      background-color: #ffcc00;
      border-radius: 10px;
      cursor: pointer;
      user-select: none;
      transition: opacity 0.5s ease, transform 0.3s ease;
    }
    .matched {
      background-color: #4CAF50;
      pointer-events: none;
    }
    .fade-out {
      opacity: 0;
    }
    .preserve-space {
      opacity: 0;
      pointer-events: none;
    }
    .shake {
      animation: shake 0.3s;
    }
    @keyframes shake {
      0% { transform: translateX(0); }
      25% { transform: translateX(-5px); }
      50% { transform: translateX(5px); }
      75% { transform: translateX(-5px); }
      100% { transform: translateX(0); }
    }
    .message {
      margin-top: 20px;
      font-size: 18px;
      color: #333;
    }
    .info-bar {
      display: flex;
      justify-content: center;
      gap: 40px;
      margin-top: 10px;
      font-size: 18px;
    }
    .explosion {
      position: absolute;
      width: 100px;
      height: 100px;
      border-radius: 50%;
      background: radial-gradient(circle, rgba(255,0,0,0.8), rgba(255,200,0,0.5), transparent);
      animation: explode 0.6s ease-out forwards;
      pointer-events: none;
      z-index: 999;
    }
    @keyframes explode {
      0% { transform: scale(0.5); opacity: 1; }
      100% { transform: scale(2.5); opacity: 0; }
    }
    .confetti {
      position: absolute;
      width: 8px;
      height: 8px;
      border-radius: 2px;
      opacity: 0.9;
      animation: burst 700ms ease-out forwards;
    }
    @keyframes burst {
      0% { transform: translate(0, 0) scale(1); opacity: 1; }
      100% { transform: translate(var(--x), var(--y)) scale(0.7); opacity: 0; }
    }
    .modal {
      position: fixed;
      top: 0;
      left: 0;
      width: 100vw;
      height: 100vh;
      background: rgba(0, 0, 0, 0.5);
      display: flex;
      justify-content: center;
      align-items: center;
      z-index: 1000;
    }
    .modal-content {
      background: white;
      padding: 20px 30px;
      border-radius: 10px;
      font-size: 20px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.3);
      cursor: pointer;
    }
    .cat {
      position: fixed;
      bottom: 10px;
      left: 50%;
      transform: translateX(-50%);
      font-size: 40px;
      transition: transform 0.3s ease;
    }
    .cat.jump {
      animation: jump 0.5s ease;
    }
    @keyframes jump {
      0% { transform: translate(-50%, 0); }
      50% { transform: translate(-50%, -40px); }
      100% { transform: translate(-50%, 0); }
    }
  </style>
</head>
<body>
  <h1>Letter Matching Game</h1>
  <div class="info-bar">
    <div id="score">Score: 0</div>
    <div id="timer">Time Left: 60s</div>
  </div>
  <div class="game-board" id="game-board"></div>
  <div class="message" id="message"></div>
  <div id="modal" class="modal" style="display: none;">
    <div class="modal-content" id="modal-content">Next Level</div>
  </div>
  <div class="cat" id="cat">🐱</div>

  <script>
    const allLetters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'.split('');
    const gameBoard = document.getElementById('game-board');
    const message = document.getElementById('message');
    const scoreDisplay = document.getElementById('score');
    const timerDisplay = document.getElementById('timer');
    const modal = document.getElementById('modal');
    const modalContent = document.getElementById('modal-content');
    const cat = document.getElementById('cat');

    let selectedLetters = [];
    let matchedPairs = 0;
    let score = 0;
    let timeLeft = 60;
    let timerInterval;
    let level = 1;

    const levelConfigs = [5, 10, 20];

    function startLevel() {
      clearInterval(timerInterval);
      selectedLetters = [];
      matchedPairs = 0;
      timeLeft = 60;
      scoreDisplay.textContent = `Score: ${score}`;
      message.textContent = '';
      gameBoard.innerHTML = '';

      const count = levelConfigs[level - 1];
      let letters = allLetters.slice(0, count);
      let shuffled = [...letters, ...letters].sort(() => Math.random() - 0.5);

      shuffled.forEach(letter => {
        const el = document.createElement('div');
        el.classList.add('letter');
        el.setAttribute('data-letter', letter);
        el.textContent = letter;
        el.addEventListener('click', onLetterClick);
        gameBoard.appendChild(el);
      });

      timerInterval = setInterval(() => {
        timeLeft--;
        timerDisplay.textContent = `Time Left: ${timeLeft}s`;
        if (timeLeft <= 0) {
          clearInterval(timerInterval);
          gameBoard.style.pointerEvents = 'none';
          message.textContent = "Time's up! Final Score: " + score;
        }
      }, 1000);
    }

    function onLetterClick(event) {
      const letterElement = event.target;
      if (letterElement.classList.contains('matched') || selectedLetters.includes(letterElement)) return;
      selectedLetters.push(letterElement);
      letterElement.style.backgroundColor = '#ffa500';
      if (selectedLetters.length === 2) checkForMatch();
    }

    function triggerExplosion(x, y) {
      const e = document.createElement('div');
      e.classList.add('explosion');
      e.style.left = `${x - 50}px`;
      e.style.top = `${y - 50}px`;
      document.body.appendChild(e);
      setTimeout(() => e.remove(), 700);
    }

    function triggerConfetti(x, y) {
      for (let i = 0; i < 12; i++) {
        const c = document.createElement('div');
        c.classList.add('confetti');
        const a = Math.random() * 2 * Math.PI;
        const r = 60 + Math.random() * 40;
        c.style.left = `${x}px`;
        c.style.top = `${y}px`;
        c.style.setProperty('--x', `${Math.cos(a) * r}px`);
        c.style.setProperty('--y', `${Math.sin(a) * r}px`);
        c.style.backgroundColor = `hsl(${Math.random() * 360}, 100%, 60%)`;
        document.body.appendChild(c);
        setTimeout(() => c.remove(), 800);
      }
    }

    function makeCatJump() {
      cat.classList.add('jump');
      setTimeout(() => cat.classList.remove('jump'), 500);
    }

    function checkForMatch() {
      const [a, b] = selectedLetters;
      if (a.getAttribute('data-letter') === b.getAttribute('data-letter')) {
        const r1 = a.getBoundingClientRect(), r2 = b.getBoundingClientRect();
        triggerExplosion(r1.left + r1.width/2, r1.top + r1.height/2);
        triggerExplosion(r2.left + r2.width/2, r2.top + r2.height/2);
        triggerConfetti(r1.left + r1.width/2, r1.top + r1.height/2);
        triggerConfetti(r2.left + r2.width/2, r2.top + r2.height/2);
        makeCatJump();
        a.classList.add('fade-out');
        b.classList.add('fade-out');
        setTimeout(() => {
          a.classList.remove('fade-out');
          b.classList.remove('fade-out');
          a.classList.add('preserve-space');
          b.classList.add('preserve-space');
          score += 10;
          matchedPairs++;
          scoreDisplay.textContent = `Score: ${score}`;
          const needed = levelConfigs[level - 1];
          if (matchedPairs === needed) {
            clearInterval(timerInterval);
            modal.style.display = 'flex';
            modalContent.textContent = level < 3 ? 'Next Level' : 'Game Completed!';
          }
        }, 500);
      } else {
        a.classList.add('shake');
        b.classList.add('shake');
        setTimeout(() => {
          a.classList.remove('shake');
          b.classList.remove('shake');
          a.style.backgroundColor = '#ffcc00';
          b.style.backgroundColor = '#ffcc00';
        }, 500);
      }
      setTimeout(() => selectedLetters = [], 500);
    }

    modalContent.addEventListener('click', () => {
      modal.style.display = 'none';
      if (level < 3) {
        level++;
        startLevel();
      } else {
        message.textContent = 'Thanks for playing!';
      }
    });

    startLevel();
  </script>
</body>
</html>
