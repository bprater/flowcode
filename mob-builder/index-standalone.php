<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TD Simulator - Modular Turret System</title>
    <style>
        body { 
            font-family: sans-serif; display: flex; flex-direction: column; 
            align-items: center; margin: 0; background-color: #e0e0e0; 
            padding-bottom: 70px; 
        }
        .pause-button-style { 
            padding: 10px 20px; font-size: 16px; cursor: pointer; 
            background-color: #4CAF50; color: white; border: none; 
            border-radius: 5px; margin: 0 5px; 
        }
        .pause-button-style.paused { background-color: #f44336; }
        
        #bottom-pause-bar { 
            position: fixed; bottom: 0; left: 0; width: 100%; 
            background-color: rgba(200, 200, 200, 0.9); padding: 10px 0; 
            display: flex; justify-content: center; z-index: 200; 
            box-shadow: 0 -2px 5px rgba(0,0,0,0.2); 
        }

        #game-container { 
            position: relative; width: 800px; height: 450px; 
            background-color: #5DAD36; 
            background-image: linear-gradient(to bottom, #76C84D 0%, #5DAD36 60%, #488A29 100%); 
            border: 2px solid #333; overflow: hidden; margin-top:10px; 
        }
        #path { 
            position: absolute; bottom: 30px; left: 0; width: 100%; height: 60px; 
            background-color: #b08d57; border-top: 1px dashed #8a6d40; 
            border-bottom: 1px dashed #8a6d40; 
        }

        .enemy { 
            position: absolute; width: 30px; height: 30px; 
            background-color: #2ecc71; border: 1px solid #27ae60; 
            border-radius: 5px; display: flex; flex-direction: column; 
            align-items: center; justify-content: center; box-sizing: border-box; 
            font-size: 10px; color: white; transition: filter 0.1s; 
        }
        .enemy-health-bar { 
            width: 90%; height: 5px; background-color: #e74c3c; 
            border: 1px solid #c0392b; margin-top: 2px; 
        }
        .enemy-health-fill { width: 100%; height: 100%; background-color: #2ecc71; }

        #controls { 
            display: flex; flex-wrap: wrap; justify-content: space-around; 
            width: 800px; margin-top: 10px; padding: 10px; 
            background-color: #e0e0e0; border-radius: 5px; 
        }
        .control-group { 
            border: 1px solid #ccc; padding: 10px; border-radius: 5px; 
            background-color: #f9f9f9; margin-bottom: 10px; 
            width: calc(25% - 20px); 
        }
        .control-group h3 { margin-top: 0; text-align: center; }
        .control-group label { 
            display: block; margin: 5px 0 2px; font-size: 0.9em; 
        }
        .control-group input[type="range"] { width: 100%; box-sizing: border-box; }
        .control-group span { font-size: 0.8em; color: #555; }
        
        .global-controls { 
            width: 100%; margin-bottom: 15px; padding: 10px; 
            background-color: #d0d0d0; border-radius: 5px; text-align: center; 
        }
        .global-controls label { margin: 0 10px; }
        
        .damage-meter { 
            margin-top: 10px; padding: 8px; background-color: #2c3e50; 
            border-radius: 4px; color: #ecf0f1; text-align: center; 
            border: 1px solid #34495e; box-shadow: inset 0 1px 3px rgba(0,0,0,0.3);
        }
        .damage-meter .damage-label { 
            font-size: 0.7em; color: #bdc3c7; margin-bottom: 2px; 
        }
        .damage-meter .damage-value { 
            font-size: 1.1em; font-weight: bold; color: #e74c3c; 
            text-shadow: 0 0 3px rgba(231, 76, 60, 0.5);
        }

        /* Tower base styles */
        .tower-base { 
            position: absolute; width: 45px; height: 45px; border-radius: 50%; 
            display: flex; align-items: center; justify-content: center; 
            box-shadow: 0 3px 6px rgba(0,0,0,0.4), inset 0 2px 4px rgba(0,0,0,0.2); 
            top: 50px; 
            cursor: grab;
            user-select: none;
            transition: transform 0.1s ease;
        }
        
        .tower-base:hover {
            transform: scale(1.05);
        }
        
        .tower-base.dragging {
            cursor: grabbing;
            z-index: 1000;
            transform: scale(1.1);
            opacity: 0.8;
            box-shadow: 0 8px 16px rgba(0,0,0,0.6), inset 0 2px 4px rgba(0,0,0,0.2);
        }
        
        .tower-base.disabled {
            opacity: 0.6;
            filter: grayscale(0.3);
        }
        
        #tower-rocket-base { 
            background: linear-gradient(135deg, #A0522D 0%, #8B4513 100%); 
        }
        #tower-rocket-base::before { 
            content: ''; position: absolute; width: 8px; height: 8px; 
            background: #696969; border-radius: 50%; 
            box-shadow: 15px 0 #696969, -15px 0 #696969, 0 15px #696969, 0 -15px #696969; 
        }
        
        #tower-ice-base { 
            background: linear-gradient(180deg, #C0E0FF 0%, #A0C0FF 100%); 
            box-shadow: 0 0 15px 5px rgba(173, 216, 230, 0.6), inset 0 0 10px rgba(255,255,255,0.5); 
            border: 2px solid #ADD8E6; 
        }
        #tower-ice-base::before, #tower-ice-base::after { 
            content: ''; position: absolute; width: 0; height: 0; 
            border-left: 8px solid transparent; border-right: 8px solid transparent; 
            border-bottom: 15px solid rgba(200, 230, 255, 0.7); opacity: 0.8; 
        }
        #tower-ice-base::before { transform: rotate(30deg) translate(18px, -15px); }
        #tower-ice-base::after { transform: rotate(-40deg) translate(-15px, -18px) scaleX(-1); }
        
        #tower-lightning-base { 
            background: linear-gradient(135deg, #404058 0%, #202030 100%); 
            border: 2px solid #606070; 
            box-shadow: 0 3px 6px rgba(0,0,0,0.5), inset 0 2px 4px rgba(0,0,0,0.3), 0 0 8px #7DF9FF; 
            animation: subtle-pulse-base-lightning 4s infinite alternate ease-in-out; 
        }
        #tower-lightning-base::after, #tower-lightning-base::before { 
            content: ''; position: absolute; width: 5px; height: 110%; 
            background: linear-gradient(to bottom, rgba(125, 249, 255, 0.2), rgba(0, 191, 255, 0.4)); 
            border-radius: 3px; box-shadow: 0 0 2px #00BFFF, 0 0 4px #7DF9FF; opacity: 0.6; 
        }
        #tower-lightning-base::after { transform: rotate(35deg); }
        #tower-lightning-base::before { transform: rotate(-35deg); }
        @keyframes subtle-pulse-base-lightning { 
            0% { box-shadow: 0 3px 6px rgba(0,0,0,0.5), inset 0 2px 4px rgba(0,0,0,0.3), 0 0 8px #7DF9FF; } 
            100% { box-shadow: 0 3px 6px rgba(0,0,0,0.5), inset 0 2px 4px rgba(0,0,0,0.3), 0 0 20px #00FFFF; } 
        }
        
        #tower-machinegun-base { 
            background: linear-gradient(135deg, #556B2F 0%, #2F4F2F 100%); 
            border: 2px solid #1C3A1C; 
        }
        #tower-machinegun-base::before { 
            content: ''; position: absolute; width: 10px; height: 10px; 
            background: #8B4513; border-radius: 50%; 
            box-shadow: 12px 0 #654321, -12px 0 #654321, 0 12px #654321, 0 -12px #654321; 
        }
        
        .turret-pivot { 
            position: absolute; width: 100%; height: 100%; top: 0; left: 0; 
            transform-origin: center center; 
        }
        
        .turret-model { 
            position: absolute; border: 1px solid #333; transform-origin: 50% 90%; 
            left: 50%; bottom: 45%; transform: translateX(-50%); 
            box-shadow: 0 1px 3px rgba(0,0,0,0.3); 
        }
        
        .turret-glow-indicator { 
            position: absolute; top: 0; left: 0; right: 0; bottom: 0; 
            border-radius: inherit; box-shadow: 0 0 0px 0px transparent; 
            opacity: 0; transition: opacity 0.1s, box-shadow 0.1s; 
            pointer-events: none; z-index: 1; 
        }
        
        /* Turret-specific styles */
        .turret-rocket { 
            width: 20px; height: 32px; 
            background: linear-gradient(#A9A9A9, #696969); 
            border-radius: 5px 5px 2px 2px; 
        }
        .turret-rocket::before { 
            content: ''; position: absolute; top: -3px; left: 2px; 
            width: 16px; height: 5px; background: #505050; border-radius: 2px; 
        }
        
        .turret-ice { 
            width: 16px; height: 30px; 
            background: transparent; border: none; box-shadow: none; 
        }
        .turret-ice::before, .turret-ice::after { 
            content: ''; position: absolute; 
            background-color: rgba(173, 216, 230, 0.6); 
            border: 1px solid rgba(200, 240, 255, 0.8); 
            box-shadow: 0 0 5px rgba(220, 250, 255, 0.7); 
        }
        .turret-ice::before { 
            width: 8px; height: 100%; top: 0; left: calc(50% - 4px); 
            border-radius: 3px 3px 0 0; 
        }
        .turret-ice::after { 
            width: 100%; height: 6px; top: 30%; left: 0; 
            border-radius: 2px; transform: rotate(-10deg); 
        }
        
        .turret-lightning-barrel { 
            width: 18px; height: 28px; 
            background: linear-gradient(#687A8F, #394653); 
            border-radius: 4px 4px 1px 1px; border: 1px solid #2A343F; 
            box-shadow: 0 1px 2px rgba(0,0,0,0.4); 
        }
        .turret-lightning-barrel::before { 
            content: ''; position: absolute; top: 5px; left: 50%; 
            transform: translateX(-50%); width: 100%; height: 5px; 
            background: #7DF9FF; border-radius: 2px; 
            box-shadow: 0 0 3px #7DF9FF, inset 0 0 2px rgba(255,255,255,0.5); 
            opacity: 0.7; 
        }
        .turret-lightning-barrel::after { 
            content: ''; position: absolute; top: -3px; left: calc(50% - 3px); 
            width: 6px; height: 6px; background: #00FFFF; border-radius: 50%; 
            box-shadow: 0 0 5px #00FFFF, 0 0 10px #7DF9FF; 
            animation: lightning-tip-pulse 1s infinite alternate; 
        }
        @keyframes lightning-tip-pulse { 
            0% { opacity: 0.6; transform: scale(0.8); } 
            100% { opacity: 1; transform: scale(1.2); } 
        }
        
        .turret-machinegun { 
            width: 22px; height: 35px; 
            background: linear-gradient(#556B2F, #2F4F2F); 
            border-radius: 3px 3px 1px 1px; 
            border: 1px solid #1C3A1C; 
            box-shadow: 0 1px 3px rgba(0,0,0,0.4); 
        }
        .turret-machinegun::before { 
            content: ''; position: absolute; top: 2px; left: 50%; 
            transform: translateX(-50%); width: 80%; height: 4px; 
            background: #8B4513; border-radius: 2px; 
            box-shadow: 0 0 2px #654321; 
        }
        .turret-machinegun::after { 
            content: ''; position: absolute; top: -2px; left: calc(50% - 2px); 
            width: 4px; height: 4px; background: #2F4F2F; border-radius: 50%; 
            box-shadow: 0 0 2px #556B2F; 
        }
        
        .projectile { position: absolute; box-sizing: border-box; }
        .visual-effect { position: absolute; pointer-events: none; z-index: 100; }
        
        .enemy-hit-sparkle {
            position: absolute; width: 7px; height: 7px;
            background-color: #FFFACD; border-radius: 50%;
            box-shadow: 0 0 4px #FFFFE0, 0 0 7px #FFD700;
            pointer-events: none; animation: hit-sparkle-anim 0.4s ease-out forwards;
            transform: translate(-50%, -50%); z-index: 101;
        }
        @keyframes hit-sparkle-anim {
            0% { transform: translate(-50%, -50%) scale(1.5); opacity: 1; }
            100% { transform: translate(calc(-50% + var(--sparkle-dx, 0px)), calc(-50% + var(--sparkle-dy, 0px))) scale(0.1); opacity: 0; }
        }
        
        .recoil-active > .turret-model { 
            animation-name: recoil-pushback-dynamic; 
            animation-timing-function: ease-out; 
        }
        @keyframes recoil-pushback-dynamic { 
            0% { transform: translateX(-50%) translateY(0); } 
            50% { transform: translateX(-50%) translateY(var(--recoil-amount)); } 
            100% { transform: translateX(-50%) translateY(0); } 
        }
        
        .enemy.hit-generic { filter: brightness(1.3) contrast(1.2); }
        .enemy.hit-rocket { filter: brightness(1.8) sepia(0.5) hue-rotate(-20deg); }
        .enemy.hit-ice { filter: brightness(1.5) saturate(2) hue-rotate(180deg); }
        .enemy.hit-lightning { filter: brightness(2.5) saturate(0.5); }
        .enemy.hit-bullet { filter: brightness(1.4) sepia(0.3) hue-rotate(10deg); }
        
        /* Projectile Styles */
        .rocket {
            width: 20px; height: 10px;
            transform-origin: 70% 50%;
            position: relative;
        }
        .rocket-body {
            position: absolute; width: 16px; height: 8px;
            background-color: #606060; border: 1px solid #404040;
            border-radius: 2px 8px 8px 2px / 20% 50% 50% 20%;
            top: 50%; left: 0; transform: translateY(-50%);
        }
        .rocket-fin {
            position: absolute; background-color: #B22222; border: 1px solid #800000;
            width: 4px; height: 9px; left: 1px; z-index: -1;
        }
        .rocket-fin-top {
            top: calc(50% - 8px); transform: skewY(35deg);
        }
        .rocket-fin-bottom {
            bottom: calc(50% - 8px); transform: skewY(-35deg);
        }
        .rocket-flame {
            position: absolute; left: -6px; top: 50%; transform: translateY(-50%);
            width: 12px; height: 12px;
            background: radial-gradient(circle, #FFFF8C 10%, #FFD700 30%, orangered 60%, transparent 80%);
            border-radius: 50%; animation: flicker-strong-rocket 0.08s infinite alternate;
        }
        @keyframes flicker-strong-rocket {
            0% { transform: scale(0.7) translateY(-50%); opacity: 0.8; filter: brightness(1.2); }
            100% { transform: scale(1.3) translateY(-50%); opacity: 1; filter: brightness(1.5); }
        }
        
        .ice-crystal-projectile { 
            width: 14px; height: 14px; position: relative; 
            animation: spin 0.7s linear infinite; 
        }
        .ice-crystal-projectile::before, .ice-crystal-projectile::after { 
            content: ''; position: absolute; 
            background-color: #afeeee; 
            box-shadow: 0 0 3px #fff, 0 0 5px #add8e6; border-radius: 1px; 
        }
        .ice-crystal-projectile::before { 
            width: 100%; height: 20%; top: 40%; left: 0; 
        }
        .ice-crystal-projectile::after { 
            width: 20%; height: 100%; top: 0; left: 40%; 
        }
        .ice-crystal-projectile > div { 
            position: absolute; width: 100%; height: 20%; top: 40%; left: 0; 
            background-color: #afeeee; 
            box-shadow: 0 0 3px #fff, 0 0 5px #add8e6; border-radius: 1px; 
            transform: rotate(45deg); 
        }
        @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
        
        .bullet { 
            width: 6px; height: 6px; 
            background: linear-gradient(45deg, #FFD700 0%, #FFA500 50%, #FF8C00 100%); 
            border-radius: 50%; 
            box-shadow: 0 0 2px #FF8C00, 0 0 4px rgba(255, 140, 0, 0.5); 
            position: relative;
        }
        .bullet::before { 
            content: ''; position: absolute; 
            width: 8px; height: 2px; 
            background: linear-gradient(90deg, transparent 0%, rgba(255, 215, 0, 0.8) 20%, transparent 100%); 
            left: -4px; top: 50%; transform: translateY(-50%); 
            border-radius: 1px; 
        }
        
        .explosion { 
            width: 70px; height: 70px; 
            background: radial-gradient(circle, #fff700 0%, #ff8c00 20%, #ff4500 50%, #400000 70%, transparent 80%); 
            border-radius: 50%; animation: explosion-anim 0.5s forwards; 
            transform: translate(-50%, -50%); 
        }
        @keyframes explosion-anim { 
            0% { transform: translate(-50%, -50%) scale(0.2); opacity: 1; } 
            100% { transform: translate(-50%, -50%) scale(1.8); opacity: 0; } 
        }
        
        .ice-shatter { 
            width: 50px; height: 50px; transform: translate(-50%, -50%); 
            animation: ice-shatter-anim 0.4s forwards; 
        }
        @keyframes ice-shatter-anim { 
            0% { opacity: 1; transform: translate(-50%, -50%) scale(0.5); } 
            100% { opacity: 0; transform: translate(-50%, -50%) scale(1.5) rotate(120deg); } 
        }
        
        .bullet-impact { 
            width: 20px; height: 20px; 
            background: radial-gradient(circle, #FFD700 10%, #FFA500 40%, #FF6347 70%, transparent 90%); 
            border-radius: 50%; animation: bullet-impact-anim 0.2s forwards; 
            transform: translate(-50%, -50%); 
        }
        @keyframes bullet-impact-anim { 
            0% { transform: translate(-50%, -50%) scale(0.3); opacity: 1; } 
            100% { transform: translate(-50%, -50%) scale(1.5); opacity: 0; } 
        }
        
        .lightning-strike-segment, .lightning-tendril-segment { 
            position: absolute; background-color: #ADD8E6; height: 2px; 
            transform-origin: 0 50%; 
            box-shadow: 0 0 4px #7DF9FF, 0 0 7px white; 
            animation: lightning-flash-quick 0.15s forwards; 
        }
        .lightning-tendril-segment { height: 1px; opacity: 0.7; }
        @keyframes lightning-flash-quick { 
            0% { opacity: 0; } 60% { opacity: 1; } 100% { opacity: 0; } 
        }
        
        .lightning-impact-flash { 
            width: 45px; height: 45px; 
            background: radial-gradient(circle, white 30%, #7DF9FF 70%, transparent 85%); 
            border-radius: 50%; animation: explosion-anim 0.2s forwards; 
            transform: translate(-50%, -50%); 
        }
    </style>
</head>
<body>
    <h1>TD Simulator - Modular Turret System</h1>
    <div id="game-container">
        <div id="path"></div>
        
        <div id="tower-rocket-base" class="tower-base" style="left: 100px;">
            <div class="turret-pivot">
                <div class="turret-model turret-rocket">
                    <div class="turret-glow-indicator"></div>
                </div>
            </div>
        </div>
        
        <div id="tower-ice-base" class="tower-base" style="left: 277.5px;">
            <div class="turret-pivot">
                <div class="turret-model turret-ice">
                    <div class="turret-glow-indicator"></div>
                </div>
            </div>
        </div>
        
        <div id="tower-lightning-base" class="tower-base" style="left: 455px;">
            <div class="turret-pivot">
                <div class="turret-model turret-lightning-barrel">
                    <div class="turret-glow-indicator"></div>
                </div>
            </div>
        </div>
        
        <div id="tower-machinegun-base" class="tower-base" style="left: 632.5px;">
            <div class="turret-pivot">
                <div class="turret-model turret-machinegun">
                    <div class="turret-glow-indicator"></div>
                </div>
            </div>
        </div>
    </div>
    
    <div id="controls">
        
        <div class="control-group" id="rocket-controls">
            <h3>Rocket Tower 🚀</h3>
            <label for="rocket-firerate">Fire Rate (ms): <span id="rocket-firerate-val">1500</span></label>
            <input type="range" id="rocket-firerate" min="500" max="5000" value="1500" step="100">
            <label for="rocket-damage">Damage: <span id="rocket-damage-val">70</span></label>
            <input type="range" id="rocket-damage" min="10" max="200" value="70" step="5">
            <label for="rocket-range">Range (px): <span id="rocket-range-val">400</span></label>
            <input type="range" id="rocket-range" min="50" max="600" value="400" step="10">
            <div class="damage-meter" id="rocket-damage-meter">
                <div class="damage-label">Total Damage</div>
                <div class="damage-value">0</div>
            </div>
        </div>
        
        <div class="control-group" id="ice-controls">
            <h3>Ice Tower ❄️</h3>
            <label for="ice-firerate">Fire Rate (ms): <span id="ice-firerate-val">900</span></label>
            <input type="range" id="ice-firerate" min="200" max="2500" value="900" step="50">
            <label for="ice-damage">Damage: <span id="ice-damage-val">20</span></label>
            <input type="range" id="ice-damage" min="5" max="100" value="20" step="5">
            <label for="ice-range">Range (px): <span id="ice-range-val">400</span></label>
            <input type="range" id="ice-range" min="50" max="600" value="400" step="10">
            <div class="damage-meter" id="ice-damage-meter">
                <div class="damage-label">Total Damage</div>
                <div class="damage-value">0</div>
            </div>
        </div>
        
        <div class="control-group" id="lightning-controls">
            <h3>Lightning Tower ⚡</h3>
            <label for="lightning-firerate">Fire Rate (ms): <span id="lightning-firerate-val">700</span></label>
            <input type="range" id="lightning-firerate" min="100" max="1500" value="700" step="50">
            <label for="lightning-damage">Damage: <span id="lightning-damage-val">35</span></label>
            <input type="range" id="lightning-damage" min="10" max="150" value="35" step="5">
            <label for="lightning-range">Range (px): <span id="lightning-range-val">400</span></label>
            <input type="range" id="lightning-range" min="50" max="600" value="400" step="10">
            <div class="damage-meter" id="lightning-damage-meter">
                <div class="damage-label">Total Damage</div>
                <div class="damage-value">0</div>
            </div>
        </div>
        
        <div class="control-group" id="machinegun-controls">
            <h3>Machine Gun Tower 🔫</h3>
            <label for="machinegun-firerate">Fire Rate (ms): <span id="machinegun-firerate-val">200</span></label>
            <input type="range" id="machinegun-firerate" min="50" max="500" value="200" step="10">
            <label for="machinegun-damage">Damage: <span id="machinegun-damage-val">8</span></label>
            <input type="range" id="machinegun-damage" min="3" max="25" value="8" step="1">
            <label for="machinegun-range">Range (px): <span id="machinegun-range-val">350</span></label>
            <input type="range" id="machinegun-range" min="200" max="500" value="350" step="10">
            <div class="damage-meter" id="machinegun-damage-meter">
                <div class="damage-label">Total Damage</div>
                <div class="damage-value">0</div>
            </div>
        </div>
    </div>
    
    <div id="bottom-pause-bar">
        <button id="pause-button-bottom" class="pause-button-style">Pause</button>
        <label for="sound-toggle" style="margin-left: 20px; color: #333;">
            <input type="checkbox" id="sound-toggle" checked> Sound Effects
        </label>
    </div>

    <script>
        // Simple standalone tower defense system
        const gameContainer = document.getElementById('game-container');
        const pathEl = document.getElementById('path');
        const pauseButtonBottom = document.getElementById('pause-button-bottom');
        
        let pathTop = 0; 
        let pathHeight = 0;
        const gameWidth = gameContainer.offsetWidth;
        const gameHeight = gameContainer.offsetHeight;
        
        let enemies = [];
        let projectiles = [];
        let enemyIdCounter = 0;
        let projectileIdCounter = 0;
        let effectIdCounter = 0;
        let globalRecoilMagnitude = 10;
        let globalRecoilDuration = 150;
        
        const ENEMY_HEALTH_MAX = 300;
        const ENEMY_SPEED = 0.7;
        const ENEMY_WIDTH = 30;
        const ENEMY_HEIGHT = 30;
        const ENEMY_SPAWN_INTERVAL = 2000;
        let lastEnemySpawnTime = 0;
        
        let audioCtx = null;
        let userHasInteracted = false;
        let isPaused = false;
        let animationFrameId;
        let lastTimestamp = 0;
        let soundEnabled = true;
        
        // Drag and drop state
        let dragState = {
            isDragging: false,
            draggedTower: null,
            startX: 0,
            startY: 0,
            offsetX: 0,
            offsetY: 0
        };
        
        // Simple tower system
        const towers = [
            { 
                id: 'tower-rocket', 
                type: 'rocket',
                baseEl: null, pivotEl: null, modelEl: null, glowEl: null, damageEl: null,
                x: 0, y: 0,
                fireRate: 1500, damage: 70, range: 400, barrelLength: 16,
                lastShotTime: 0, currentAngleRad: -Math.PI / 2, chargeLevel: 0, totalDamage: 0,
                disabled: false
            },
            { 
                id: 'tower-ice', 
                type: 'ice',
                baseEl: null, pivotEl: null, modelEl: null, glowEl: null, damageEl: null,
                x: 0, y: 0,
                fireRate: 900, damage: 20, range: 400, barrelLength: 15,
                lastShotTime: 0, currentAngleRad: -Math.PI / 2, chargeLevel: 0, totalDamage: 0,
                splashRadius: 70, splashDamageFactor: 0.5, disabled: false
            },
            { 
                id: 'tower-lightning', 
                type: 'lightning',
                baseEl: null, pivotEl: null, modelEl: null, glowEl: null, damageEl: null,
                x: 0, y: 0,
                fireRate: 700, damage: 35, range: 400, barrelLength: 20,
                lastShotTime: 0, currentAngleRad: -Math.PI / 2, chargeLevel: 0, totalDamage: 0,
                disabled: false
            },
            { 
                id: 'tower-machinegun', 
                type: 'machinegun',
                baseEl: null, pivotEl: null, modelEl: null, glowEl: null, damageEl: null,
                x: 0, y: 0,
                fireRate: 200, damage: 8, range: 350, barrelLength: 18,
                lastShotTime: 0, currentAngleRad: -Math.PI / 2, chargeLevel: 0, totalDamage: 0,
                disabled: false
            }
        ];
        
        // Initialize towers
        function initializeTowers() {
            towers.forEach(tower => {
                tower.baseEl = document.getElementById(`tower-${tower.type}-base`);
                tower.pivotEl = document.querySelector(`#tower-${tower.type}-base .turret-pivot`);
                tower.modelEl = document.querySelector(`#tower-${tower.type}-base .turret-model`);
                tower.glowEl = document.querySelector(`#tower-${tower.type}-base .turret-glow-indicator`);
                tower.damageEl = document.querySelector(`#${tower.type}-damage-meter .damage-value`);
                
                if (tower.baseEl) {
                    tower.x = tower.baseEl.offsetLeft + tower.baseEl.offsetWidth / 2;
                    tower.y = tower.baseEl.offsetTop + tower.baseEl.offsetHeight / 2;
                }
                
                if (tower.pivotEl) {
                    tower.pivotEl.style.transform = `rotate(${tower.currentAngleRad - Math.PI/2}rad)`;
                }
                
                // Add drag and drop event handlers
                if (tower.baseEl) {
                    setupDragAndDrop(tower);
                }
            });
        }
        
        // Update tower damage display
        function updateTowerDamage(tower, damageDealt) {
            tower.totalDamage += damageDealt;
            if (tower.damageEl) {
                tower.damageEl.textContent = Math.round(tower.totalDamage).toLocaleString();
            }
        }
        
        // Drag and drop functionality
        function setupDragAndDrop(tower) {
            tower.baseEl.addEventListener('mousedown', (e) => startDrag(e, tower));
            tower.baseEl.addEventListener('touchstart', (e) => startDrag(e, tower), { passive: false });
        }
        
        function startDrag(e, tower) {
            e.preventDefault();
            
            dragState.isDragging = true;
            dragState.draggedTower = tower;
            
            const rect = gameContainer.getBoundingClientRect();
            const clientX = e.type === 'touchstart' ? e.touches[0].clientX : e.clientX;
            const clientY = e.type === 'touchstart' ? e.touches[0].clientY : e.clientY;
            
            dragState.offsetX = clientX - rect.left - tower.baseEl.offsetLeft;
            dragState.offsetY = clientY - rect.top - tower.baseEl.offsetTop;
            
            // Disable tower and add visual feedback
            tower.disabled = true;
            tower.baseEl.classList.add('dragging');
            tower.baseEl.classList.add('disabled');
            
            // Add global event listeners
            document.addEventListener('mousemove', dragMove);
            document.addEventListener('touchmove', dragMove, { passive: false });
            document.addEventListener('mouseup', dragEnd);
            document.addEventListener('touchend', dragEnd);
        }
        
        function dragMove(e) {
            if (!dragState.isDragging || !dragState.draggedTower) return;
            
            e.preventDefault();
            
            const rect = gameContainer.getBoundingClientRect();
            const clientX = e.type === 'touchmove' ? e.touches[0].clientX : e.clientX;
            const clientY = e.type === 'touchmove' ? e.touches[0].clientY : e.clientY;
            
            let newX = clientX - rect.left - dragState.offsetX;
            let newY = clientY - rect.top - dragState.offsetY;
            
            // Constrain to game container bounds
            const towerSize = 45;
            newX = Math.max(0, Math.min(gameContainer.offsetWidth - towerSize, newX));
            newY = Math.max(0, Math.min(gameContainer.offsetHeight - towerSize, newY));
            
            dragState.draggedTower.baseEl.style.left = `${newX}px`;
            dragState.draggedTower.baseEl.style.top = `${newY}px`;
        }
        
        function dragEnd(e) {
            if (!dragState.isDragging || !dragState.draggedTower) return;
            
            const tower = dragState.draggedTower;
            
            // Update tower position
            tower.x = tower.baseEl.offsetLeft + tower.baseEl.offsetWidth / 2;
            tower.y = tower.baseEl.offsetTop + tower.baseEl.offsetHeight / 2;
            
            // Re-enable tower and remove visual feedback
            tower.disabled = false;
            tower.baseEl.classList.remove('dragging');
            tower.baseEl.classList.remove('disabled');
            
            // Reset drag state
            dragState.isDragging = false;
            dragState.draggedTower = null;
            
            // Remove global event listeners
            document.removeEventListener('mousemove', dragMove);
            document.removeEventListener('touchmove', dragMove);
            document.removeEventListener('mouseup', dragEnd);
            document.removeEventListener('touchend', dragEnd);
        }
        
        // Audio system
        function ensureAudioContext() {
            if (audioCtx && audioCtx.state === 'running') return true;
            if (!userHasInteracted) return false;
            if (!audioCtx) {
                try { 
                    audioCtx = new (window.AudioContext || window.webkitAudioContext)(); 
                } catch (e) { 
                    console.error("Failed to create AudioContext:", e); 
                    return false; 
                }
            }
            if (audioCtx.state === 'suspended') {
                audioCtx.resume().catch(e => console.error("Failed to resume AudioContext:", e));
                return false;
            }
            return audioCtx.state === 'running';
        }
        
        function playSound(type) {
            if (!soundEnabled || !ensureAudioContext()) return;
            
            if (!audioCtx) return;
            let osc, gain, dur, freq, noiseSource, buffer, output, filter;
            const now = audioCtx.currentTime;
            
            switch (type) {
                case 'rocket_fire':
                    // Epic rocket launcher: Deep THUNK + whoosh + ignition
                    // Deep mechanical thunk
                    osc = audioCtx.createOscillator();
                    gain = audioCtx.createGain();
                    osc.type = 'square';
                    freq = 35;
                    dur = 0.15;
                    osc.frequency.setValueAtTime(freq, now);
                    osc.frequency.exponentialRampToValueAtTime(freq * 0.3, now + dur);
                    gain.gain.setValueAtTime(0, now);
                    gain.gain.linearRampToValueAtTime(0.3, now + 0.01);
                    gain.gain.exponentialRampToValueAtTime(0.001, now + dur);
                    osc.connect(gain);
                    gain.connect(audioCtx.destination);
                    osc.start(now);
                    osc.stop(now + dur);
                    
                    // Rocket ignition hiss
                    noiseSource = audioCtx.createBufferSource();
                    buffer = audioCtx.createBuffer(1, audioCtx.sampleRate * 0.8, audioCtx.sampleRate);
                    output = buffer.getChannelData(0);
                    for (let i = 0; i < buffer.length; i++) output[i] = (Math.random() * 2 - 1) * Math.exp(-i / (buffer.length * 0.7));
                    noiseSource.buffer = buffer;
                    filter = audioCtx.createBiquadFilter();
                    filter.type = "highpass";
                    filter.frequency.setValueAtTime(2000, now + 0.1);
                    filter.frequency.linearRampToValueAtTime(800, now + 0.8);
                    filter.Q.value = 3;
                    const hissGain = audioCtx.createGain();
                    noiseSource.connect(filter);
                    filter.connect(hissGain);
                    hissGain.connect(audioCtx.destination);
                    hissGain.gain.setValueAtTime(0, now + 0.05);
                    hissGain.gain.linearRampToValueAtTime(0.15, now + 0.1);
                    hissGain.gain.exponentialRampToValueAtTime(0.001, now + 0.8);
                    noiseSource.start(now + 0.05);
                    noiseSource.stop(now + 0.8);
                    break;
                    
                case 'ice_fire':
                    // Magical ice conjuring: ethereal chimes + frost crack
                    // Crystalline bell sequence
                    const bellFreqs = [2637, 3136, 3729]; // High bell notes
                    bellFreqs.forEach((bellFreq, idx) => {
                        const bellOsc = audioCtx.createOscillator();
                        const bellGain = audioCtx.createGain();
                        bellOsc.type = 'sine';
                        bellOsc.frequency.setValueAtTime(bellFreq, now + idx * 0.05);
                        bellOsc.frequency.exponentialRampToValueAtTime(bellFreq * 1.1, now + idx * 0.05 + 0.3);
                        bellGain.gain.setValueAtTime(0, now + idx * 0.05);
                        bellGain.gain.linearRampToValueAtTime(0.08, now + idx * 0.05 + 0.01);
                        bellGain.gain.exponentialRampToValueAtTime(0.001, now + idx * 0.05 + 0.5);
                        bellOsc.connect(bellGain);
                        bellGain.connect(audioCtx.destination);
                        bellOsc.start(now + idx * 0.05);
                        bellOsc.stop(now + idx * 0.05 + 0.5);
                    });
                    
                    // Ice crack sound
                    noiseSource = audioCtx.createBufferSource();
                    buffer = audioCtx.createBuffer(1, audioCtx.sampleRate * 0.2, audioCtx.sampleRate);
                    output = buffer.getChannelData(0);
                    for (let i = 0; i < buffer.length; i++) {
                        output[i] = (Math.random() * 2 - 1) * Math.exp(-i / (buffer.length * 0.1));
                    }
                    noiseSource.buffer = buffer;
                    filter = audioCtx.createBiquadFilter();
                    filter.type = "bandpass";
                    filter.frequency.value = 4500 + Math.random() * 1000;
                    filter.Q.value = 15;
                    const crackGain = audioCtx.createGain();
                    noiseSource.connect(filter);
                    filter.connect(crackGain);
                    crackGain.connect(audioCtx.destination);
                    crackGain.gain.setValueAtTime(0, now + 0.15);
                    crackGain.gain.linearRampToValueAtTime(0.12, now + 0.16);
                    crackGain.gain.exponentialRampToValueAtTime(0.001, now + 0.35);
                    noiseSource.start(now + 0.15);
                    noiseSource.stop(now + 0.35);
                    break;
                    
                case 'lightning_fire':
                    // Tesla coil discharge: building energy + violent ZAP
                    // Energy buildup
                    osc = audioCtx.createOscillator();
                    gain = audioCtx.createGain();
                    osc.type = 'sawtooth';
                    freq = 120;
                    osc.frequency.setValueAtTime(freq, now);
                    osc.frequency.exponentialRampToValueAtTime(freq * 8, now + 0.06);
                    gain.gain.setValueAtTime(0, now);
                    gain.gain.linearRampToValueAtTime(0.1, now + 0.06);
                    gain.gain.linearRampToValueAtTime(0, now + 0.07);
                    osc.connect(gain);
                    gain.connect(audioCtx.destination);
                    osc.start(now);
                    osc.stop(now + 0.07);
                    
                    // Violent electrical discharge
                    noiseSource = audioCtx.createBufferSource();
                    buffer = audioCtx.createBuffer(1, audioCtx.sampleRate * 0.15, audioCtx.sampleRate);
                    output = buffer.getChannelData(0);
                    for (let i = 0; i < buffer.length; i++) {
                        output[i] = (Math.random() * 2 - 1) * (1 - i / buffer.length) * (Math.random() < 0.7 ? 1 : 0);
                    }
                    noiseSource.buffer = buffer;
                    filter = audioCtx.createBiquadFilter();
                    filter.type = "bandpass";
                    filter.frequency.setValueAtTime(4000 + Math.random() * 3000, now + 0.06);
                    filter.Q.value = 25 + Math.random() * 15;
                    gain = audioCtx.createGain();
                    noiseSource.connect(filter);
                    filter.connect(gain);
                    gain.connect(audioCtx.destination);
                    gain.gain.setValueAtTime(0, now + 0.06);
                    gain.gain.linearRampToValueAtTime(0.35, now + 0.065);
                    gain.gain.exponentialRampToValueAtTime(0.001, now + 0.21);
                    noiseSource.start(now + 0.06);
                    noiseSource.stop(now + 0.21);
                    break;
                    
                case 'machinegun_fire':
                    // Heavy machine gun: mechanical bolt + explosive discharge
                    // Mechanical action
                    osc = audioCtx.createOscillator();
                    gain = audioCtx.createGain();
                    osc.type = 'square';
                    freq = 150 + Math.random() * 50;
                    dur = 0.03;
                    osc.frequency.setValueAtTime(freq, now);
                    osc.frequency.exponentialRampToValueAtTime(freq * 0.4, now + dur);
                    gain.gain.setValueAtTime(0, now);
                    gain.gain.linearRampToValueAtTime(0.15, now + 0.005);
                    gain.gain.exponentialRampToValueAtTime(0.001, now + dur);
                    osc.connect(gain);
                    gain.connect(audioCtx.destination);
                    osc.start(now);
                    osc.stop(now + dur);
                    
                    // Gunpowder explosion
                    noiseSource = audioCtx.createBufferSource();
                    buffer = audioCtx.createBuffer(1, audioCtx.sampleRate * 0.12, audioCtx.sampleRate);
                    output = buffer.getChannelData(0);
                    for (let i = 0; i < buffer.length; i++) {
                        output[i] = (Math.random() * 2 - 1) * Math.exp(-i / (buffer.length * 0.2));
                    }
                    noiseSource.buffer = buffer;
                    filter = audioCtx.createBiquadFilter();
                    filter.type = "lowpass";
                    filter.frequency.setValueAtTime(2000 + Math.random() * 1000, now);
                    filter.frequency.exponentialRampToValueAtTime(400, now + 0.12);
                    filter.Q.value = 8;
                    gain = audioCtx.createGain();
                    noiseSource.connect(filter);
                    filter.connect(gain);
                    gain.connect(audioCtx.destination);
                    gain.gain.setValueAtTime(0, now);
                    gain.gain.linearRampToValueAtTime(0.25, now + 0.005);
                    gain.gain.exponentialRampToValueAtTime(0.001, now + 0.12);
                    noiseSource.start(now);
                    noiseSource.stop(now + 0.12);
                    break;
                    
                // Impact sounds
                case 'rocket_impact':
                    // Massive explosion
                    dur = 1.2;
                    noiseSource = audioCtx.createBufferSource();
                    buffer = audioCtx.createBuffer(1, audioCtx.sampleRate * dur, audioCtx.sampleRate);
                    output = buffer.getChannelData(0);
                    for (let i = 0; i < buffer.length; i++) {
                        output[i] = (Math.random() * 2 - 1) * Math.exp(-i / (buffer.length * 0.4));
                    }
                    noiseSource.buffer = buffer;
                    filter = audioCtx.createBiquadFilter();
                    filter.type = "lowpass";
                    filter.frequency.setValueAtTime(800, now);
                    filter.frequency.exponentialRampToValueAtTime(100, now + dur);
                    filter.Q.value = 3;
                    gain = audioCtx.createGain();
                    noiseSource.connect(filter);
                    filter.connect(gain);
                    gain.connect(audioCtx.destination);
                    gain.gain.setValueAtTime(0, now);
                    gain.gain.linearRampToValueAtTime(0.4, now + 0.02);
                    gain.gain.exponentialRampToValueAtTime(0.001, now + dur);
                    noiseSource.start(now);
                    noiseSource.stop(now + dur);
                    break;
                    
                case 'ice_impact':
                    // Crystalline shatter with magical sparkles
                    noiseSource = audioCtx.createBufferSource();
                    buffer = audioCtx.createBuffer(1, audioCtx.sampleRate * 0.3, audioCtx.sampleRate);
                    output = buffer.getChannelData(0);
                    for (let i = 0; i < buffer.length; i++) {
                        output[i] = (Math.random() * 2 - 1) * Math.exp(-i / (buffer.length * 0.15));
                    }
                    noiseSource.buffer = buffer;
                    filter = audioCtx.createBiquadFilter();
                    filter.type = "highpass";
                    filter.frequency.value = 3000 + Math.random() * 2000;
                    filter.Q.value = 12;
                    gain = audioCtx.createGain();
                    noiseSource.connect(filter);
                    filter.connect(gain);
                    gain.connect(audioCtx.destination);
                    gain.gain.setValueAtTime(0, now);
                    gain.gain.linearRampToValueAtTime(0.2, now + 0.01);
                    gain.gain.exponentialRampToValueAtTime(0.001, now + 0.3);
                    noiseSource.start(now);
                    noiseSource.stop(now + 0.3);
                    
                    // Magical chime
                    osc = audioCtx.createOscillator();
                    gain = audioCtx.createGain();
                    osc.type = 'sine';
                    freq = 2637 + Math.random() * 500;
                    osc.frequency.setValueAtTime(freq, now);
                    gain.gain.setValueAtTime(0, now);
                    gain.gain.linearRampToValueAtTime(0.1, now + 0.02);
                    gain.gain.exponentialRampToValueAtTime(0.001, now + 0.4);
                    osc.connect(gain);
                    gain.connect(audioCtx.destination);
                    osc.start(now);
                    osc.stop(now + 0.4);
                    break;
                    
                case 'lightning_impact':
                    // Electric sizzle and pop
                    noiseSource = audioCtx.createBufferSource();
                    buffer = audioCtx.createBuffer(1, audioCtx.sampleRate * 0.2, audioCtx.sampleRate);
                    output = buffer.getChannelData(0);
                    for (let i = 0; i < buffer.length; i++) {
                        output[i] = (Math.random() * 2 - 1) * (Math.random() < 0.6 ? 1 : 0);
                    }
                    noiseSource.buffer = buffer;
                    filter = audioCtx.createBiquadFilter();
                    filter.type = "highpass";
                    filter.frequency.setValueAtTime(5000 + Math.random() * 2000, now);
                    filter.Q.value = 20;
                    gain = audioCtx.createGain();
                    noiseSource.connect(filter);
                    filter.connect(gain);
                    gain.connect(audioCtx.destination);
                    gain.gain.setValueAtTime(0, now);
                    gain.gain.linearRampToValueAtTime(0.25, now + 0.005);
                    gain.gain.exponentialRampToValueAtTime(0.001, now + 0.2);
                    noiseSource.start(now);
                    noiseSource.stop(now + 0.2);
                    break;
                    
                case 'bullet_impact':
                    // Sharp metallic ping
                    osc = audioCtx.createOscillator();
                    gain = audioCtx.createGain();
                    osc.type = 'square';
                    freq = 1200 + Math.random() * 800;
                    dur = 0.08;
                    osc.frequency.setValueAtTime(freq, now);
                    osc.frequency.exponentialRampToValueAtTime(freq * 0.3, now + dur);
                    gain.gain.setValueAtTime(0, now);
                    gain.gain.linearRampToValueAtTime(0.15, now + 0.005);
                    gain.gain.exponentialRampToValueAtTime(0.001, now + dur);
                    osc.connect(gain);
                    gain.connect(audioCtx.destination);
                    osc.start(now);
                    osc.stop(now + dur);
                    break;
            }
        }
        
        // Visual effects
        function createVisualEffect(x, y, type, options = {}) {
            let effectEl;
            let duration = 400;
            
            if (type === 'enemy_hit_sparkle') {
                const count = options.count || (3 + Math.floor(Math.random() * 3));
                for (let i = 0; i < count; i++) {
                    effectIdCounter++;
                    const particleEl = document.createElement('div');
                    particleEl.id = `effect-${effectIdCounter}`;
                    particleEl.classList.add('visual-effect', 'enemy-hit-sparkle');
                    particleEl.style.left = `${x}px`;
                    particleEl.style.top = `${y}px`;
                    const angle = Math.random() * Math.PI * 2;
                    const dist = 15 + Math.random() * 25;
                    particleEl.style.setProperty('--sparkle-dx', `${Math.cos(angle) * dist}px`);
                    particleEl.style.setProperty('--sparkle-dy', `${Math.sin(angle) * dist}px`);
                    particleEl.style.animationDelay = `${Math.random() * 0.08}s`;
                    gameContainer.appendChild(particleEl);
                    setTimeout(() => particleEl.remove(), 400);
                }
                return;
            }
            
            effectIdCounter++;
            effectEl = document.createElement('div');
            effectEl.id = `effect-${effectIdCounter}`;
            effectEl.classList.add('visual-effect');
            
            if (type === 'explosion') { 
                effectEl.classList.add('explosion'); 
                duration = 500; 
            } else if (type === 'ice_shatter') { 
                effectEl.classList.add('ice-shatter'); 
                duration = 400; 
            } else if (type === 'bullet_impact') { 
                effectEl.classList.add('bullet-impact'); 
                duration = 200; 
            } else if (type === 'lightning_impact') { 
                effectEl.classList.add('lightning-impact-flash'); 
                duration = 200; 
            }
            
            effectEl.style.left = `${x}px`;
            effectEl.style.top = `${y}px`;
            gameContainer.appendChild(effectEl);
            setTimeout(() => effectEl.remove(), duration);
        }
        
        // Enemy management
        function updatePathPosition() {
            if (pathEl) {
                pathTop = pathEl.offsetTop;
                pathHeight = pathEl.offsetHeight;
            }
        }
        
        function spawnEnemy() {
            if (pathHeight === 0 && pathTop === 0 && enemies.length < 1) return;
            
            enemyIdCounter++;
            const enemyEl = document.createElement('div');
            enemyEl.classList.add('enemy');
            enemyEl.id = `enemy-${enemyIdCounter}`;
            enemyEl.style.left = `${gameWidth - ENEMY_WIDTH}px`;
            enemyEl.style.top = `${pathTop + (pathHeight / 2) - (ENEMY_HEIGHT / 2)}px`;
            
            const healthBar = document.createElement('div');
            healthBar.classList.add('enemy-health-bar');
            const healthFill = document.createElement('div');
            healthFill.classList.add('enemy-health-fill');
            healthBar.appendChild(healthFill);
            enemyEl.appendChild(healthBar);
            
            gameContainer.appendChild(enemyEl);
            
            enemies.push({
                id: enemyIdCounter,
                el: enemyEl,
                x: gameWidth - ENEMY_WIDTH,
                y: pathTop + (pathHeight / 2) - (ENEMY_HEIGHT / 2),
                health: ENEMY_HEALTH_MAX,
                maxHealth: ENEMY_HEALTH_MAX,
                speed: ENEMY_SPEED,
                healthFillEl: healthFill,
                age: 0,
                width: ENEMY_WIDTH,
                height: ENEMY_HEIGHT
            });
        }
        
        function initialEnemyFill() {
            if (pathHeight === 0 && pathTop === 0) return;
            
            const baseSpacing = ENEMY_WIDTH + 10;
            const numToFill = Math.floor(gameWidth / baseSpacing);
            let currentX = gameWidth - ENEMY_WIDTH;
            
            for (let i = 0; i < numToFill; i++) {
                if (currentX + ENEMY_WIDTH < 0) break;
                
                // Add randomness to spacing (±5px) and Y position (±8px)
                const spacingVariation = (Math.random() - 0.5) * 10; // ±5px
                const yVariation = (Math.random() - 0.5) * 16; // ±8px
                const xPos = currentX;
                
                enemyIdCounter++;
                const enemyEl = document.createElement('div');
                enemyEl.classList.add('enemy');
                enemyEl.id = `enemy-${enemyIdCounter}`;
                enemyEl.style.left = `${xPos}px`;
                
                const centerY = pathTop + (pathHeight / 2) - (ENEMY_HEIGHT / 2);
                const finalY = Math.max(pathTop, Math.min(pathTop + pathHeight - ENEMY_HEIGHT, centerY + yVariation));
                enemyEl.style.top = `${finalY}px`;
                
                const healthBar = document.createElement('div');
                healthBar.classList.add('enemy-health-bar');
                const healthFill = document.createElement('div');
                healthFill.classList.add('enemy-health-fill');
                healthBar.appendChild(healthFill);
                enemyEl.appendChild(healthBar);
                
                gameContainer.appendChild(enemyEl);
                
                enemies.push({
                    id: enemyIdCounter,
                    el: enemyEl,
                    x: xPos,
                    y: finalY,
                    health: ENEMY_HEALTH_MAX,
                    maxHealth: ENEMY_HEALTH_MAX,
                    speed: ENEMY_SPEED,
                    healthFillEl: healthFill,
                    age: 0,
                    width: ENEMY_WIDTH,
                    height: ENEMY_HEIGHT
                });
                
                // Update position for next enemy with random spacing
                currentX -= baseSpacing + spacingVariation;
            }
        }
        
        function updateEnemies(currentTime, deltaTime) {
            if (isPaused) return;
            
            const actualDeltaFactor = deltaTime / (1000/60);
            
            if (currentTime - lastEnemySpawnTime > ENEMY_SPAWN_INTERVAL) {
                spawnEnemy();
                lastEnemySpawnTime = currentTime;
            }
            
            for (let i = enemies.length - 1; i >= 0; i--) {
                const enemy = enemies[i];
                enemy.x -= enemy.speed * actualDeltaFactor;
                enemy.age++;
                enemy.el.style.left = `${enemy.x}px`;
                
                const healthPercentage = (enemy.health / enemy.maxHealth) * 100;
                enemy.healthFillEl.style.width = `${Math.max(0, healthPercentage)}%`;
                
                if (enemy.health <= 0) {
                    enemy.el.remove();
                    enemies.splice(i, 1);
                    continue;
                }
                
                if (enemy.x + ENEMY_WIDTH < 0) {
                    enemy.el.remove();
                    enemies.splice(i, 1);
                }
            }
        }
        
        // Tower targeting and firing
        function findTarget(tower) {
            let closestEnemy = null;
            let minDistance = tower.range;
            
            for (const enemy of enemies) {
                const enemyCenterX = enemy.x + enemy.width / 2;
                const enemyCenterY = enemy.y + enemy.height / 2;
                
                const distance = Math.sqrt(
                    Math.pow(tower.x - enemyCenterX, 2) + 
                    Math.pow(tower.y - enemyCenterY, 2)
                );
                
                if (distance < minDistance) {
                    minDistance = distance;
                    closestEnemy = enemy;
                }
            }
            
            return closestEnemy;
        }
        
        function aimAt(tower, target) {
            const targetX = target.x + target.width / 2;
            const targetY = target.y + target.height / 2;
            const angleToTargetRad = Math.atan2(targetY - tower.y, targetX - tower.x);
            
            tower.currentAngleRad = angleToTargetRad;
            
            if (tower.pivotEl) {
                tower.pivotEl.style.transform = `rotate(${angleToTargetRad - Math.PI/2}rad)`;
            }
        }
        
        function fireTower(tower, target) {
            if (tower.type === 'lightning') {
                // Lightning is instant hit with visual bolt
                const actualDamage = Math.min(tower.damage, target.health);
                target.health -= tower.damage;
                updateTowerDamage(tower, actualDamage);
                
                // Create lightning bolt visual
                createLightningBolt(tower, target);
                
                createVisualEffect(
                    target.x + target.width/2, 
                    target.y + target.height/2, 
                    'enemy_hit_sparkle'
                );
                
                createVisualEffect(
                    target.x + target.width/2, 
                    target.y + target.height/2, 
                    'lightning_impact'
                );
                
                playSound('lightning_impact');
                
                if (target.el) {
                    target.el.classList.add(`hit-${tower.type}`);
                    setTimeout(() => target.el.classList.remove(`hit-${tower.type}`), 100);
                }
            } else {
                // Other towers launch projectiles
                launchProjectile(tower, target);
            }
            
            playSound(`${tower.type}_fire`);
            
            // Recoil effect
            if (tower.modelEl && tower.pivotEl) {
                tower.modelEl.style.setProperty('--recoil-amount', `${-globalRecoilMagnitude}px`);
                tower.modelEl.style.animationDuration = `${globalRecoilDuration}ms`;
                tower.pivotEl.classList.add('recoil-active');
                
                setTimeout(() => {
                    tower.pivotEl.classList.remove('recoil-active');
                }, globalRecoilDuration + 50);
            }
            
            tower.lastShotTime = performance.now();
            tower.chargeLevel = 0;
            
            if (tower.glowEl) {
                tower.glowEl.style.opacity = 0;
                tower.glowEl.style.boxShadow = '0 0 0px 0px transparent';
            }
        }
        
        function createLightningBolt(tower, target) {
            const originX = tower.x + tower.barrelLength * Math.cos(tower.currentAngleRad);
            const originY = tower.y + tower.barrelLength * Math.sin(tower.currentAngleRad);
            const targetX = target.x + target.width / 2;
            const targetY = target.y + target.height / 2;
            
            // Draw main lightning bolt with segments
            drawLightningSegments(originX, originY, targetX, targetY, 5, 30, 'lightning-strike-segment', true);
            
            // Draw branching tendrils
            const numTendrils = 2 + Math.floor(Math.random() * 2);
            for (let t = 0; t < numTendrils; t++) {
                const branchPointRatio = 0.3 + Math.random() * 0.4;
                const branchOriginX = originX + (targetX - originX) * branchPointRatio;
                const branchOriginY = originY + (targetY - originY) * branchPointRatio;
                const tendrilEndX = branchOriginX + (Math.random() - 0.5) * 80;
                const tendrilEndY = branchOriginY + (Math.random() - 0.5) * 80;
                
                drawLightningSegments(branchOriginX, branchOriginY, tendrilEndX, tendrilEndY, 3, 20, 'lightning-tendril-segment', false);
            }
        }
        
        function drawLightningSegments(startX, startY, endX, endY, segments, deviation, className, isMainBolt) {
            let currentX = startX;
            let currentY = startY;
            const totalDx = endX - startX;
            const totalDy = endY - startY;
            
            for (let i = 0; i < segments; i++) {
                effectIdCounter++;
                const segmentEl = document.createElement('div');
                segmentEl.id = `effect-${effectIdCounter}`;
                segmentEl.classList.add(className);
                
                let nextX, nextY;
                if (i === segments - 1) {
                    nextX = endX;
                    nextY = endY;
                } else {
                    const progress = (i + 1) / segments;
                    nextX = startX + totalDx * progress + (Math.random() - 0.5) * deviation;
                    nextY = startY + totalDy * progress + (Math.random() - 0.5) * deviation;
                }
                
                const dx = nextX - currentX;
                const dy = nextY - currentY;
                const length = Math.sqrt(dx * dx + dy * dy);
                const angle = Math.atan2(dy, dx) * (180 / Math.PI);
                
                segmentEl.style.width = `${length}px`;
                segmentEl.style.left = `${currentX}px`;
                segmentEl.style.top = `${currentY - 1}px`; // Center the 2px height
                segmentEl.style.transform = `rotate(${angle}deg)`;
                
                gameContainer.appendChild(segmentEl);
                setTimeout(() => segmentEl.remove(), isMainBolt ? 150 : 100);
                
                currentX = nextX;
                currentY = nextY;
            }
        }
        
        function launchProjectile(tower, target) {
            projectileIdCounter++;
            
            const projectileEl = document.createElement('div');
            projectileEl.classList.add('projectile');
            projectileEl.id = `proj-${projectileIdCounter}`;
            
            // Center projectile on tower position
            const startX = tower.x;
            const startY = tower.y;
            
            // Create projectile based on tower type
            if (tower.type === 'rocket') {
                projectileEl.classList.add('rocket');
                
                const bodyEl = document.createElement('div');
                bodyEl.classList.add('rocket-body');
                projectileEl.appendChild(bodyEl);
                
                const finTop = document.createElement('div');
                finTop.classList.add('rocket-fin', 'rocket-fin-top');
                projectileEl.appendChild(finTop);
                
                const finBottom = document.createElement('div');
                finBottom.classList.add('rocket-fin', 'rocket-fin-bottom');
                projectileEl.appendChild(finBottom);
                
                const flameEl = document.createElement('div');
                flameEl.classList.add('rocket-flame');
                projectileEl.appendChild(flameEl);
                
            } else if (tower.type === 'ice') {
                projectileEl.classList.add('ice-crystal-projectile');
                const crystalInnerDiv = document.createElement('div');
                projectileEl.appendChild(crystalInnerDiv);
                
            } else if (tower.type === 'machinegun') {
                projectileEl.classList.add('bullet');
            }
            
            // Position projectile centered on tower
            projectileEl.style.left = `${startX - 10}px`;
            projectileEl.style.top = `${startY - 5}px`;
            gameContainer.appendChild(projectileEl);
            
            // Create projectile data
            const projectile = {
                id: projectileIdCounter,
                el: projectileEl,
                x: startX,
                y: startY,
                damage: tower.damage,
                speed: tower.type === 'rocket' ? 3 : tower.type === 'ice' ? 5 : 8,
                target: target,
                towerType: tower.type,
                tower: tower,
                currentAngle: tower.currentAngleRad,
                age: 0
            };
            
            projectiles.push(projectile);
        }
        
        function updateProjectiles(deltaTime) {
            if (isPaused) return;
            
            const actualDeltaFactor = deltaTime / (1000/60);
            
            for (let i = projectiles.length - 1; i >= 0; i--) {
                const proj = projectiles[i];
                const targetEnemy = proj.target;
                
                if (!targetEnemy || targetEnemy.health <= 0 || !enemies.includes(targetEnemy)) {
                    if (proj.el) proj.el.remove();
                    projectiles.splice(i, 1);
                    continue;
                }
                
                const targetX = targetEnemy.x + targetEnemy.width / 2;
                const targetY = targetEnemy.y + targetEnemy.height / 2;
                const dx = targetX - proj.x;
                const dy = targetY - proj.y;
                const distance = Math.sqrt(dx * dx + dy * dy);
                const moveSpeed = proj.speed * actualDeltaFactor;
                
                // Check for impact
                if (distance < moveSpeed + (targetEnemy.width / 3)) {
                    const actualDamage = Math.min(proj.damage, targetEnemy.health);
                    targetEnemy.health -= proj.damage;
                    updateTowerDamage(proj.tower, actualDamage);
                    
                    createVisualEffect(targetX, targetY, 'enemy_hit_sparkle');
                    
                    // Type-specific impact effects with sounds
                    if (proj.towerType === 'rocket') {
                        createVisualEffect(targetX, targetY, 'explosion');
                        playSound('rocket_impact');
                    } else if (proj.towerType === 'ice') {
                        createVisualEffect(targetX, targetY, 'ice_shatter');
                        playSound('ice_impact');
                    } else if (proj.towerType === 'machinegun') {
                        createVisualEffect(targetX, targetY, 'bullet_impact');
                        playSound('bullet_impact');
                    }
                    
                    if (targetEnemy.el) {
                        const hitClass = proj.towerType === 'machinegun' ? 'hit-bullet' : `hit-${proj.towerType}`;
                        targetEnemy.el.classList.add(hitClass);
                        setTimeout(() => targetEnemy.el.classList.remove(hitClass), 100);
                    }
                    
                    proj.el.remove();
                    projectiles.splice(i, 1);
                    continue;
                }
                
                // Move projectile
                let moveX, moveY;
                
                if (proj.towerType === 'rocket') {
                    // Homing behavior for rockets
                    const angleToTarget = Math.atan2(dy, dx);
                    let angleDifference = angleToTarget - proj.currentAngle;
                    
                    while (angleDifference > Math.PI) angleDifference -= 2 * Math.PI;
                    while (angleDifference < -Math.PI) angleDifference += 2 * Math.PI;
                    
                    const turnAmount = Math.sign(angleDifference) * Math.min(Math.abs(angleDifference), 0.05);
                    proj.currentAngle += turnAmount;
                    
                    moveX = Math.cos(proj.currentAngle) * moveSpeed;
                    moveY = Math.sin(proj.currentAngle) * moveSpeed;
                    
                    proj.el.style.transform = `rotate(${proj.currentAngle}rad)`;
                } else {
                    // Straight line for ice and bullets
                    moveX = (dx / distance) * moveSpeed;
                    moveY = (dy / distance) * moveSpeed;
                }
                
                proj.x += moveX;
                proj.y += moveY;
                proj.age++;
                
                proj.el.style.left = `${proj.x - 10}px`;
                proj.el.style.top = `${proj.y - 5}px`;
                
                // Remove if out of bounds
                if (proj.x < -50 || proj.x > gameWidth + 50 || proj.y < -50 || proj.y > gameHeight + 100) {
                    proj.el.remove();
                    projectiles.splice(i, 1);
                }
            }
        }
        
        function updateTowers(currentTime, deltaTime) {
            if (isPaused) return;
            
            towers.forEach(tower => {
                // Skip disabled towers (being dragged)
                if (tower.disabled) return;
                // Update charge level
                if (tower.fireRate > 0) {
                    if (tower.lastShotTime > 0) {
                        tower.chargeLevel = Math.min(1, (currentTime - tower.lastShotTime) / tower.fireRate);
                    } else {
                        tower.chargeLevel = Math.min(1, currentTime / tower.fireRate);
                    }
                } else {
                    tower.chargeLevel = 1;
                }
                
                // Update glow effect
                if (tower.glowEl) {
                    tower.glowEl.style.opacity = tower.chargeLevel * 0.8;
                    const hue = 60 - (tower.chargeLevel * 60);
                    const spread = tower.chargeLevel * 8;
                    const blur = tower.chargeLevel * 15;
                    tower.glowEl.style.boxShadow = `0 0 ${blur}px ${spread}px hsla(${hue}, 100%, 50%, 0.7)`;
                }
                
                // Find and target enemy
                const target = findTarget(tower);
                if (target) {
                    aimAt(tower, target);
                    
                    if (tower.chargeLevel >= 1.0) {
                        fireTower(tower, target);
                    }
                }
            });
        }
        
        // Game loop
        function gameLoop(timestamp) {
            if (!lastTimestamp) lastTimestamp = timestamp;
            const deltaTime = timestamp - lastTimestamp;
            lastTimestamp = timestamp;
            
            if (!isPaused) {
                updateEnemies(timestamp, deltaTime);
                updateTowers(timestamp, deltaTime);
                updateProjectiles(deltaTime);
            }
            
            animationFrameId = requestAnimationFrame(gameLoop);
        }
        
        // Control system
        function setupControls() {
            const soundToggle = document.getElementById('sound-toggle');
            if (soundToggle) {
                soundToggle.addEventListener('change', (e) => {
                    soundEnabled = e.target.checked;
                });
                soundEnabled = soundToggle.checked;
            }
            
            towers.forEach(tower => {
                // Handle fireRate specially since HTML uses 'firerate' but object uses 'fireRate'
                const fireRateSlider = document.getElementById(`${tower.type}-firerate`);
                const fireRateDisplay = document.getElementById(`${tower.type}-firerate-val`);
                if (fireRateSlider && fireRateDisplay) {
                    fireRateSlider.addEventListener('input', () => {
                        tower.fireRate = parseFloat(fireRateSlider.value);
                        fireRateDisplay.textContent = fireRateSlider.value;
                    });
                    tower.fireRate = parseFloat(fireRateSlider.value);
                }
                
                // Handle other stats
                ['damage', 'range'].forEach(stat => {
                    const slider = document.getElementById(`${tower.type}-${stat}`);
                    const valDisplay = document.getElementById(`${tower.type}-${stat}-val`);
                    
                    if (slider && valDisplay) {
                        slider.addEventListener('input', () => {
                            tower[stat] = parseFloat(slider.value);
                            valDisplay.textContent = slider.value;
                        });
                        tower[stat] = parseFloat(slider.value);
                    }
                });
            });
        }
        
        function togglePauseState() {
            isPaused = !isPaused;
            const buttonText = isPaused ? 'Resume' : 'Pause';
            if (pauseButtonBottom) {
                pauseButtonBottom.textContent = buttonText;
                pauseButtonBottom.classList.toggle('paused', isPaused);
            }
            if (!isPaused) {
                lastTimestamp = performance.now();
                ensureAudioContext();
            }
        }
        
        // Initialize game
        document.addEventListener('DOMContentLoaded', () => {
            updatePathPosition();
            initializeTowers();
            setupControls();
            
            const userGestureEvents = ['click', 'touchstart', 'keydown', 'mousemove'];
            function handleUserGesture() {
                if (!userHasInteracted) {
                    userHasInteracted = true;
                    ensureAudioContext();
                    userGestureEvents.forEach(event => 
                        document.body.removeEventListener(event, handleUserGesture)
                    );
                }
            }
            userGestureEvents.forEach(event => {
                document.body.addEventListener(event, handleUserGesture, { once: true });
            });
            
            // Fill the lane with enemies immediately on load
            initialEnemyFill();
            lastEnemySpawnTime = performance.now();
            animationFrameId = requestAnimationFrame(gameLoop);
        });
        
        if (pauseButtonBottom) {
            pauseButtonBottom.addEventListener('click', togglePauseState);
        }
    </script>
</body>
</html>