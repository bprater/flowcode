<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>NATO Alphabet Trainer</title>
  <style>
    body {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      height: 100vh;
      margin: 0;
      font-family: Arial, sans-serif;
      font-size: 10em;
      text-align: center;
      user-select: none;
      font-weight: bold;
      transition: background-color 0.4s, color 0.4s;
      cursor: default;
      position: relative;
      background-color: #111;
      color: #fff;
      overflow: hidden;
    }

    #nato, #nato-words {
      letter-spacing: 0.05em;
      transition: transform 0.3s ease, opacity 0.3s ease;
    }

    .slide-out { transform: translateX(-100%); opacity: 0; }
    .slide-in { transform: translateX(100%); opacity: 0; }
    .slide-reset { transform: translateX(0); opacity: 1; }

    #nato-words {
      font-size: 0.25em;
      margin-top: 0.3em;
    }

    #scoreboard {
      position: absolute;
      bottom: 20px;
      display: flex;
      gap: 1em;
      font-size: 0.2em;
      font-weight: normal;
    }

    .score-box {
      padding: 0.4em 0.8em;
      background-color: #ffffff11;
      border: 2px solid #ffffff33;
      border-radius: 0.5em;
      transition: transform 0.2s ease;
    }

    .pulse {
      animation: pulse 0.4s ease-in-out;
    }

    @keyframes pulse {
      0% { transform: scale(1); }
      50% { transform: scale(1.3); }
      100% { transform: scale(1); }
    }

    .button-container {
      margin-top: 0.5em;
    }

    .btn {
      font-size: 0.25em;
      padding: 0.3em 0.6em;
      margin: 0 0.3em;
      font-weight: bold;
      cursor: pointer;
      border: none;
      border-radius: 0.2em;
      background-color: #ffffff22;
      color: inherit;
      transition: background-color 0.3s;
    }

    .btn:hover {
      background-color: #ffffff44;
    }

    #pauseBtn {
      position: absolute;
      top: 12px;
      right: 12px;
      font-size: 0.2em;
      padding: 0.2em 0.5em;
      border-radius: 0.3em;
      background-color: #ffffff22;
      color: inherit;
      border: none;
      cursor: pointer;
    }

    #pauseBtn:hover {
      background-color: #ffffff44;
    }

    #timer-bar {
      position: absolute;
      top: 0;
      left: 0;
      height: 5px;
      background-color: #00ff99;
      width: 100%;
      transform-origin: left;
      transition: transform 0.1s linear;
    }

    #timer-bar.paused {
      transition: none !important;
    }

    .celebrate {
      animation: pop 0.4s ease-in-out;
    }

    @keyframes pop {
      0% { transform: scale(1); }
      50% { transform: scale(1.3); }
      100% { transform: scale(1); }
    }

    .confetti {
      position: absolute;
      width: 10px;
      height: 10px;
      background: red;
      animation: confetti-pop 1s ease-out forwards;
      opacity: 0.8;
      z-index: 1000;
    }

    @keyframes confetti-pop {
      0% { transform: translate(0, 0) rotate(0deg); opacity: 1; }
      100% { transform: translate(var(--x), var(--y)) rotate(720deg); opacity: 0; }
    }
  </style>
</head>
<body>
  <div id="timer-bar"></div>
  <button id="pauseBtn">Pause</button>
  <div id="nato" class="slide-reset">Loading...</div>
  <div id="nato-words" class="slide-reset"></div>
  <div class="button-container">
    <button class="btn" id="yesBtn">Yes</button>
    <button class="btn" id="noBtn">No</button>
  </div>
  <div id="scoreboard">
    <div id="score-correct" class="score-box">✅ 0</div>
    <div id="score-incorrect" class="score-box">❌ 0</div>
  </div>

  <script>
    const letters = "ABCDEFGHIJKLMNOPQRSTUVWXYZ".split("");
    const natoWords = {
      A: "Alpha", B: "Bravo", C: "Charlie", D: "Delta", E: "Echo", F: "Foxtrot",
      G: "Golf", H: "Hotel", I: "India", J: "Juliett", K: "Kilo", L: "Lima",
      M: "Mike", N: "November", O: "Oscar", P: "Papa", Q: "Quebec", R: "Romeo",
      S: "Sierra", T: "Tango", U: "Uniform", V: "Victor", W: "Whiskey",
      X: "X-ray", Y: "Yankee", Z: "Zulu"
    };

    const display = document.getElementById("nato");
    const wordsDisplay = document.getElementById("nato-words");
    const yesBtn = document.getElementById("yesBtn");
    const noBtn = document.getElementById("noBtn");
    const pauseBtn = document.getElementById("pauseBtn");
    const timerBar = document.getElementById("timer-bar");
    const scoreCorrect = document.getElementById("score-correct");
    const scoreIncorrect = document.getElementById("score-incorrect");
    const body = document.body;

    const themes = [...Array(32).keys()].map((_, i) => ({
      bg: `hsl(${i * 11}, 70%, 10%)`,
      text: `hsl(${i * 11}, 100%, 85%)`
    }));

    let themeIndex = 0;
    let showingWords = false;
    let currentLetters = [];
    let correctCount = 0;
    let incorrectCount = 0;
    let timer = 10;
    let countdownInterval;
    let advanceTimeout;
    let paused = false;

    function shuffle(array) {
      for (let i = array.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [array[i], array[j]] = [array[j], array[i]];
      }
    }
    shuffle(themes);

    function speak(text) {
      if ('speechSynthesis' in window) {
        const utterance = new SpeechSynthesisUtterance(text);
        utterance.rate = 1.1;
        speechSynthesis.cancel();
        speechSynthesis.speak(utterance);
      }
    }

    function getRandomLetterPair() {
      const first = letters[Math.floor(Math.random() * letters.length)];
      const second = letters[Math.floor(Math.random() * letters.length)];
      return [first, second];
    }

    function updateProgress() {
      scoreCorrect.textContent = `✅ ${correctCount}`;
      scoreIncorrect.textContent = `❌ ${incorrectCount}`;
    }

    function pulseScore(el) {
      el.classList.add("pulse");
      setTimeout(() => el.classList.remove("pulse"), 400);
    }

    function startTimerBar() {
      timerBar.classList.remove("paused");
      timerBar.style.transition = "none";
      timerBar.style.transform = "scaleX(1)";
      setTimeout(() => {
        if (!paused) {
          timerBar.style.transition = `transform ${timer}s linear`;
          timerBar.style.transform = "scaleX(0)";
        }
      }, 50);
    }

    function resetTimer() {
      clearInterval(countdownInterval);
      if (paused) return;
      timer = 10;
      startTimerBar();
      countdownInterval = setInterval(() => {
        timer--;
        if (timer <= 0) {
          handleResponse(false);
        }
      }, 1000);
    }

    function animateSlideTransition(callback) {
      display.classList.remove("slide-reset");
      wordsDisplay.classList.remove("slide-reset");
      display.classList.add("slide-out");
      wordsDisplay.classList.add("slide-out");

      setTimeout(() => {
        callback();
        display.classList.remove("slide-out");
        wordsDisplay.classList.remove("slide-out");
        display.classList.add("slide-in");
        wordsDisplay.classList.add("slide-in");

        setTimeout(() => {
          display.classList.remove("slide-in");
          wordsDisplay.classList.remove("slide-in");
          display.classList.add("slide-reset");
          wordsDisplay.classList.add("slide-reset");
        }, 10);
      }, 300);
    }

    function createConfetti() {
      const rect = display.getBoundingClientRect();
      for (let i = 0; i < 25; i++) {
        const confetti = document.createElement("div");
        confetti.className = "confetti";
        const x = (Math.random() - 0.5) * 200 + "px";
        const y = (Math.random() - 1.2) * 200 + "px";
        confetti.style.left = `${rect.left + rect.width / 2}px`;
        confetti.style.top = `${rect.top + rect.height / 2}px`;
        confetti.style.setProperty("--x", x);
        confetti.style.setProperty("--y", y);
        confetti.style.backgroundColor = `hsl(${Math.random() * 360}, 100%, 70%)`;
        document.body.appendChild(confetti);
        setTimeout(() => confetti.remove(), 1000);
      }
    }

    function updateDisplay() {
      animateSlideTransition(() => {
        currentLetters = getRandomLetterPair();
        display.textContent = currentLetters.join("");
        wordsDisplay.textContent = "";
        const theme = themes[themeIndex % themes.length];
        body.style.backgroundColor = theme.bg;
        body.style.color = theme.text;
        themeIndex++;
        showingWords = false;
        resetTimer();
      });
    }

    function handleResponse(correct) {
      clearInterval(countdownInterval);
      if (correct) {
        correctCount++;
        pulseScore(scoreCorrect);
        display.classList.add("celebrate");
        createConfetti();
        setTimeout(() => display.classList.remove("celebrate"), 400);
      } else {
        incorrectCount++;
        pulseScore(scoreIncorrect);
      }
      updateProgress();

      if (!showingWords) {
        const [first, second] = currentLetters;
        const words = `${natoWords[first]} ${natoWords[second]}`;
        wordsDisplay.textContent = words;
        speak(words);
        showingWords = true;
      }

      clearTimeout(advanceTimeout);
      advanceTimeout = setTimeout(() => {
        updateDisplay();
      }, 1000);
    }

    yesBtn.addEventListener("click", () => handleResponse(true));
    noBtn.addEventListener("click", () => handleResponse(false));

    pauseBtn.addEventListener("click", () => {
      paused = !paused;
      pauseBtn.textContent = paused ? "Resume" : "Pause";
      if (paused) {
        clearInterval(countdownInterval);
        timerBar.classList.add("paused");
        const computedStyle = window.getComputedStyle(timerBar);
        const matrix = new WebKitCSSMatrix(computedStyle.transform);
        const scaleX = matrix.a;
        timerBar.style.transition = "none";
        timerBar.style.transform = `scaleX(${scaleX})`;
      } else {
        resetTimer();
      }
    });

    updateProgress();
    updateDisplay();
  </script>
</body>
</html>
