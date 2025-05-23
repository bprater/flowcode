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
      background: #2e1e00;
      color: #c0a080;
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
      text-shadow: 0 0 5px #8b5a2b;
    }

    /* Two-column grid container */
    .container {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 1rem;
      max-width: 800px;
      margin: 0 auto;
    }

    /* Card styling */
    .card {
      border: 2px solid #4b6220;
      background: #705e3b;
      padding: 1rem;
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 0.5rem;
    }

    .card img {
      max-width: 300px;
      max-height: 100px;
      border: 2px solid #c0a080;
    }

    .card h2 {
      font-size: 1.2rem;
      color: #ecd078;
    }

    .card p {
      font-size: 0.8rem;
      text-align: center;
    }

    .card a {
      font-size: 0.8rem;
      text-decoration: none;
      color: #a09060;
      text-shadow: 0 0 3px #a09060;
    }

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
    <!-- ToDo App -->
    <article class="card">
      <img src="https://picsum.photos/seed/todo-app/300/100" alt="ToDo App">
      <h2>ToDo App</h2>
      <p>Manage tasks and mark them off as you complete them.</p>
      <a href="/todo-app/">View Project</a>
    </article>
    <!-- Vampire Studs -->
    <article class="card">
      <img src="https://picsum.photos/seed/vampire-studs/300/100" alt="Vampire Studs">
      <h2>Vampire Studs</h2>
      <p>Collect and battle with vampire-themed stud cards.</p>
      <a href="/vampire-studs/">View Project</a>
    </article>
    <!-- Monsters Tower Defense -->
    <article class="card">
      <img src="https://picsum.photos/seed/monsters-tower-defense/300/100" alt="Monsters Tower Defense">
      <h2>Monsters Tower Defense</h2>
      <p>Defend against waves of monsters with strategic tower placement.</p>
      <a href="/monsters-tower-defense/">View Project</a>
    </article>
    <!-- Monster Livery -->
    <article class="card">
      <img src="https://picsum.photos/seed/monster-livery/300/100" alt="Monster Livery">
      <h2>Monster Livery</h2>
      <p>Design custom liveries for monster trucks and vehicles.</p>
      <a href="/monster-livery/">View Project</a>
    </article>
    <!-- Headshot -->
    <article class="card">
      <img src="https://picsum.photos/seed/headshot/300/100" alt="Headshot">
      <h2>Headshot</h2>
      <p>Generate AI-powered professional headshots with style presets.</p>
      <a href="/headshot/">View Project</a>
    </article>
    <!-- Asteroidal -->
    <article class="card">
      <img src="https://picsum.photos/seed/asteroidal/300/100" alt="Asteroidal">
      <h2>Asteroidal</h2>
      <p>Pilot your ship through an asteroid field and avoid collisions.</p>
      <a href="/asteroidal/">View Project</a>
    </article>
    <!-- Tower Suspense -->
    <article class="card">
      <img src="https://picsum.photos/seed/tower-suspense/300/100" alt="Tower Suspense">
      <h2>Tower Suspense</h2>
      <p>Build your tower while suspense rises as enemies approach.</p>
      <a href="/tower-suspense/">View Project</a>
    </article>
    <!-- ThreeJS STL Viewer -->
    <article class="card">
      <img src="https://picsum.photos/seed/threejs-stl-viewer/300/100" alt="ThreeJS STL Viewer">
      <h2>ThreeJS STL Viewer</h2>
      <p>View and interact with 3D STL models using Three.js.</p>
      <a href="/threejs-stl-viewer/">View Project</a>
    </article>
    <!-- FPV Stim -->
    <article class="card">
      <img src="https://picsum.photos/seed/fpv-stim/300/100" alt="FPV Stim">
      <h2>FPV Stim</h2>
      <p>Simulate first-person drone flying with realistic camera feed.</p>
      <a href="/fpv-stim/">View Project</a>
    </article>
    <!-- Particle Universe -->
    <article class="card">
      <img src="https://picsum.photos/seed/particle-universe/300/100" alt="Particle Universe">
      <h2>Particle Universe</h2>
      <p>Create cosmic particle effects exploring space phenomena.</p>
      <a href="/particle-universe/">View Project</a>
    </article>
    <!-- Solar System Sim -->
    <article class="card">
      <img src="https://picsum.photos/seed/solar-system-sim/300/100" alt="Solar System Sim">
      <h2>Solar System Sim</h2>
      <p>Simulate planetary orbits and visualize the solar system.</p>
      <a href="/solar-system-sim/">View Project</a>
    </article>
    <!-- Luminous Trails -->
    <article class="card">
      <img src="https://picsum.photos/seed/luminous-trails/300/100" alt="Luminous Trails">
      <h2>Luminous Trails</h2>
      <p>Draw glowing trails following moving objects.</p>
      <a href="/luminous-trails/">View Project</a>
    </article>
    <!-- Island Vibe -->
    <article class="card">
      <img src="https://picsum.photos/seed/island-vibe/300/100" alt="Island Vibe">
      <h2>Island Vibe</h2>
      <p>Relax to ambient island-themed visuals and sounds.</p>
      <a href="/island-vibe/">View Project</a>
    </article>
  </main>
</body>
</html>