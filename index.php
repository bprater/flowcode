<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Ben's Flow Code Apps</title>
  <!-- Pixel-style retro font -->
  <link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap" rel="stylesheet">
  <style>
    /* Global resets */
    * { box-sizing: border-box; margin: 0; padding: 0; }
    html, body { height: 100%; }

    /* Muted Pitfall-inspired palette */
    body {
      background: #2e1e00; /* Deep brown ground */
      color: #c0a080; /* Soft tan text */
      font-family: 'Press Start 2P', cursive;
      line-height: 1.4;
      padding: 1rem;
    }

    /* Header styling */
    header {
      text-align: center;
      margin-bottom: 2rem;
    }
    header h1 {
      font-size: 1.5rem;
      text-shadow: 0 0 5px #8b5a2b; /* Muted brown glow */
    }

    /* Container for entries - two columns */
    .container {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 1rem;
      max-width: 800px;
      margin: 0 auto;
    }

    /* Card styling */
    .card {
      border: 2px solid #4b6220; /* Muted olive green */
      padding: 1rem;
      background: #705e3b; /* Lighter muted brown */
      display: flex;
      flex-direction: column;
      gap: 0.5rem;
      align-items: center;
    }

    .card img {
      width: auto;
      max-width: 300px; /* Max width */
      max-height: 100px; /* Half as tall */
      border: 2px solid #c0a080; /* Soft tan border */
    }

    .card h2 {
      font-size: 1.2rem; /* Larger app name */
      color: #ecd078; /* Pale yellow standout */
    }

    .card p {
      font-size: 0.8rem;
      text-align: center;
    }

    .card a {
      font-size: 0.8rem;
      text-decoration: none;
      color: #a09060; /* Muted gold link */
      text-shadow: 0 0 3px #a09060;
    }

    /* Link hover glow */
    .card a:hover {
      text-shadow: 0 0 7px #a09060;
    }
  </style>
</head>
<body>
  <header>
    <h1>Ben's Flow Code Apps</h1>
  </header>
  <main class="container">
    <!-- Typing Test -->
    <article class="card">
      <img src="https://picsum.photos/seed/typing-test/300/100" alt="Typing Test">
      <h2>Typing Test</h2>
      <p>Test your typing speed and accuracy with real-time feedback.</p>
      <a href="/typing-test/">View Project</a>
    </article>

    <!-- Balloon Pop -->
    <article class="card">
      <img src="https://picsum.photos/seed/balloon-pop/300/100" alt="Balloon Pop">
      <h2>Balloon Pop</h2>
      <p>Pop balloons before they float away in this fun, fast-paced game.</p>
      <a href="/balloon-pop/">View Project</a>
    </article>

    <!-- Letter Match -->
    <article class="card">
      <img src="https://picsum.photos/seed/letter-match/300/100" alt="Letter Match">
      <h2>Letter Match</h2>
      <p>Match letters to their correct positions before time runs out.</p>
      <a href="/letter-match/">View Project</a>
    </article>

    <!-- Piano -->
    <article class="card">
      <img src="https://picsum.photos/seed/piano/300/100" alt="Piano">
      <h2>Piano</h2>
      <p>Play tunes on a virtual piano keyboard with responsive keys.</p>
      <a href="/piano/">View Project</a>
    </article>

    <!-- Particle Play -->
    <article class="card">
      <img src="https://picsum.photos/seed/particle-play/300/100" alt="Particle Play">
      <h2>Particle Play</h2>
      <p>Experiment with interactive particle systems and physics.</p>
      <a href="/particle-play/">View Project</a>
    </article>

    <!-- Maze -->
    <article class="card">
      <img src="https://picsum.photos/seed/maze/300/100" alt="Maze">
      <h2>Maze</h2>
      <p>Navigate through generated mazes to find the exit.</p>
      <a href="/maze/">View Project</a>
    </article>

    <!-- NATO Flashcards -->
    <article class="card">
      <img src="https://picsum.photos/seed/nato-flashcards/300/100" alt="NATO Flashcards">
      <h2>NATO Flashcards</h2>
      <p>Practice the NATO phonetic alphabet with interactive flashcards.</p>
      <a href="/nato-flashcards/">View Project</a>
    </article>

    <!-- Stardew Friends -->
    <article class="card">
      <img src="https://picsum.photos/seed/stardew-friends/300/100" alt="Stardew Friends">
      <h2>Stardew Friends</h2>
      <p>Track and manage your friendships in Stardew Valley.</p>
      <a href="/stardew-friends/">View Project</a>
    </article>

    <!-- Wheezy Video Game -->
    <article class="card">
      <img src="https://picsum.photos/seed/wheezy-video-game/300/100" alt="Wheezy Video Game">
      <h2>Wheezy Video Game</h2>
      <p>A top-down adventure game featuring Wheezy’s quests.</p>
      <a href="/wheezy-video-game/">View Project</a>
    </article>

    <!-- Bouncing Ball -->
    <article class="card">
      <img src="https://picsum.photos/seed/bouncing-ball/300/100" alt="Bouncing Ball">
      <h2>Bouncing Ball</h2>
      <p>Watch a ball bounce with realistic physics and controls.</p>
      <a href="/bouncing-ball/">View Project</a>
    </article>

  </main>
</body>
</html>
