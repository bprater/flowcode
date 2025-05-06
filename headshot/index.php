<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Headshot Animation Game</title>
  <style>
    body {
      margin: 0;
      background-color: #222;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      overflow: hidden;
    }
    #game {
      position: relative;
      width: 800px;
      height: 400px;
      background-color: #444;
      border: 2px solid #fff;
      overflow: hidden;
      cursor: crosshair;
    }
    #character {
      position: absolute;
      bottom: 20px;
      width: 80px;
      height: 80px;
      transition: transform 0.1s linear;
    }
  </style>
</head>
<body>
  <div id="game">
    <img id="character" src="/headshot/man1.png" alt="Character" />
  </div>

  <script>
    const game = document.getElementById('game');
    const character = document.getElementById('character');
    const imagesUpDown = ['/headshot/man1.png', '/headshot/man2.png'];
    const imagesLeftRight = ['/headshot/man3.png', '/headshot/man4.png'];

    let currentImage = 0;
    let positionX = 400;
    let positionY = 20;
    let targetX = 400;
    let targetY = 20;

    game.addEventListener('mousemove', (e) => {
      const rect = game.getBoundingClientRect();
      targetX = e.clientX - rect.left - 40;
      targetY = rect.bottom - e.clientY - 40;
    });

    function animate() {
      const dx = targetX - positionX;
      const dy = targetY - positionY;
      const distance = Math.sqrt(dx * dx + dy * dy);

      if (distance > 5) {
        positionX += dx / distance * 10;
        positionY += dy / distance * 10;
      }

      currentImage = 1 - currentImage;

      if (Math.abs(dx) > Math.abs(dy)) {
        // Horizontal movement
        character.src = imagesLeftRight[currentImage];
        character.style.transform = `translateX(${positionX}px) scaleX(${dx > 0 ? -1 : 1})`;
      } else {
        // Vertical movement
        character.src = imagesUpDown[currentImage];
        character.style.transform = `translateX(${positionX}px)`;
      }

      character.style.bottom = `${positionY}px`;
    }

    setInterval(animate, 100);
  </script>
</body>
</html>