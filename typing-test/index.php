<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Typing Speed Game</title>
  <style>
    body {
      font-family: 'Courier New', Courier, monospace;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: flex-start;
      height: 100vh;
      margin: 0;
      padding: 20px;
      background: #f4f4f4;
      transition: background-color 0.2s ease;
    }
    body.flash {
      background-color: #ffcccc;
    }
    h1 {
      margin-bottom: 20px;
    }
    #phrase-container {
      background: #fff;
      padding: 20px;
      border: 2px solid #333;
      border-radius: 8px;
      box-shadow: 0 4px 6px rgba(0,0,0,0.1);
      margin-bottom: 20px;
    }
    #phrase {
      font-size: 28px;
      font-weight: bold;
      color: #333;
    }
    #input {
      font-size: 20px;
      padding: 10px;
      width: 80%;
      max-width: 600px;
      font-family: 'Courier New', Courier, monospace;
    }
    #wpm {
      margin-top: 20px;
      font-size: 22px;
      font-weight: bold;
    }
    #scorecard {
      margin-top: 30px;
      width: 90%;
      max-width: 800px;
      background: #fff;
      border: 1px solid #aaa;
      border-radius: 8px;
      padding: 10px;
      overflow-y: auto;
      max-height: 200px;
    }
    .score-entry {
      margin-bottom: 5px;
    }
    .score-entry .wpm {
      font-weight: bold;
    }
  </style>
</head>
<body>
  <h1>Typing Speed Game</h1>
  <div id="phrase-container">
    <div id="phrase">Loading...</div>
  </div>
  <input type="text" id="input" placeholder="Start typing here..." autofocus />
  <div id="wpm">WPM: 0</div>
  <div id="scorecard"></div>

  <script>
    const phrases = [
      "The sun set behind the hills",
      "She walked quickly down the street",
      "The cat slept on the warm bed",
      "He opened the book and began to read",
      "They sat quietly and listened",
      "It was a bright and sunny day",
      "The children laughed and played",
      "She wrote a letter to her friend",
      "He drank a cup of hot coffee",
      "The dog barked at the stranger"
    ];

    const phraseEl = document.getElementById("phrase");
    const inputEl = document.getElementById("input");
    const wpmEl = document.getElementById("wpm");
    const scorecardEl = document.getElementById("scorecard");
    const bodyEl = document.body;

    let currentPhrase = "";
    let startTime = null;
    let timerInterval = null;

    function setNewPhrase() {
      currentPhrase = phrases[Math.floor(Math.random() * phrases.length)];
      phraseEl.textContent = currentPhrase;
      inputEl.value = "";
      inputEl.style.color = "black";
      clearInterval(timerInterval);
      startTime = null;
      wpmEl.textContent = "WPM: 0";
    }

    function updateWPM() {
      if (!startTime) return;
      const elapsedMinutes = (Date.now() - startTime) / 60000;
      const wordsTyped = inputEl.value.trim().split(/\s+/).filter(Boolean).length;
      const wpm = Math.round(wordsTyped / elapsedMinutes);
      if (isFinite(wpm)) {
        wpmEl.textContent = `WPM: ${wpm}`;
      }
    }

    function flashBackground() {
      bodyEl.classList.add('flash');
      setTimeout(() => {
        bodyEl.classList.remove('flash');
      }, 100);
    }

    function logScore(phrase, wpm) {
      const div = document.createElement('div');
      div.className = 'score-entry';
      div.innerHTML = `"${phrase}" - <span class="wpm">${wpm} WPM</span>`;
      scorecardEl.prepend(div);
    }

    function finishPhrase() {
      clearInterval(timerInterval);
      const elapsedMinutes = (Date.now() - startTime) / 60000;
      const words = currentPhrase.trim().split(/\s+/).length;
      const wpm = Math.round(words / elapsedMinutes);
      updateWPM();
      logScore(currentPhrase, wpm);
      setNewPhrase();
    }

    inputEl.addEventListener("input", () => {
      if (!startTime && inputEl.value.length > 0) {
        startTime = Date.now();
        timerInterval = setInterval(updateWPM, 50);
      }

      const inputText = inputEl.value;
      if (currentPhrase.startsWith(inputText)) {
        inputEl.style.color = "black";
      } else {
        inputEl.style.color = "red";
        flashBackground();
      }

      if (inputText === currentPhrase) {
        finishPhrase();
      }
    });

    inputEl.addEventListener("keydown", (e) => {
      if (e.key === "Enter" && inputEl.value === currentPhrase) {
        finishPhrase();
      }
    });

    window.addEventListener("DOMContentLoaded", setNewPhrase);
  </script>
</body>
</html>
