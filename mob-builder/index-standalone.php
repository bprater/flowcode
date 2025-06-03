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
        }

        #game-container { 
            position: relative; width: 1000px; height: 600px; 
            background-color: #5DAD36; 
            background-image: linear-gradient(to bottom, #76C84D 0%, #5DAD36 60%, #488A29 100%); 
            border: 2px solid #333; overflow: hidden; margin-top:10px; 
        }
        .path-segment {
            position: absolute;
            background-color: #b08d57;
            border-radius: 20px;
            box-shadow: inset 0 0 10px rgba(138, 109, 64, 0.3);
        }
        
        .path-waypoint {
            position: absolute;
            width: 8px;
            height: 8px;
            background-color: #ff6b6b;
            border-radius: 50%;
            z-index: 10;
        }

        .enemy { 
            position: absolute; width: 35px; height: 35px; 
            display: flex; flex-direction: column; align-items: center; 
            transition: filter 0.1s; 
        }
        .enemy-eyeball {
            width: 30px; height: 30px; 
            background: radial-gradient(circle at 50% 50%, #ffffff 45%, #f0f0f0 50%, #e0e0e0 100%);
            border: 2px solid #333;
            border-radius: 50%;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.3), inset 0 1px 2px rgba(255,255,255,0.8);
        }
        .enemy-iris {
            width: 12px; height: 12px;
            background: radial-gradient(circle at 30% 30%, #4a90e2 20%, #2c5aa0 80%);
            border-radius: 50%;
            position: relative;
            transition: transform 0.1s ease-out;
            box-shadow: inset 0 1px 2px rgba(0,0,0,0.4);
        }
        .enemy-iris::after {
            content: '';
            position: absolute;
            top: 2px; left: 3px;
            width: 3px; height: 3px;
            background: rgba(255,255,255,0.9);
            border-radius: 50%;
            box-shadow: 1px 1px 1px rgba(255,255,255,0.6);
        }
        .enemy-health-bar { 
            width: 32px; height: 4px; background-color: #e74c3c; 
            border: 1px solid #c0392b; margin-top: 3px;
            border-radius: 2px;
        }
        .enemy-health-fill { 
            width: 100%; height: 100%; background-color: #2ecc71; 
            border-radius: 1px; transition: width 0.2s ease;
        }
        .eyeball-death-explosion {
            position: absolute;
            width: 60px; height: 60px;
            background: radial-gradient(circle, #ff6b6b 0%, #ff4757 30%, #ff3742 60%, #c23616 100%);
            border-radius: 50%;
            animation: eyeball-explode 0.6s ease-out forwards;
            transform: translate(-50%, -50%);
            z-index: 150;
        }
        .eyeball-death-explosion::before {
            content: '';
            position: absolute;
            top: 50%; left: 50%;
            width: 100%; height: 100%;
            background: radial-gradient(circle, rgba(255,255,255,0.8) 0%, transparent 70%);
            border-radius: 50%;
            transform: translate(-50%, -50%);
            animation: eyeball-flash 0.2s ease-out;
        }
        .eyeball-death-explosion::after {
            content: '';
            position: absolute;
            top: 50%; left: 50%;
            width: 120%; height: 120%;
            border: 2px solid #ff4757;
            border-radius: 50%;
            transform: translate(-50%, -50%);
            animation: eyeball-shockwave 0.4s ease-out;
        }
        @keyframes eyeball-explode {
            0% { transform: translate(-50%, -50%) scale(0.3); opacity: 1; }
            50% { transform: translate(-50%, -50%) scale(1.2); opacity: 0.8; }
            100% { transform: translate(-50%, -50%) scale(2); opacity: 0; }
        }
        @keyframes eyeball-flash {
            0% { opacity: 1; transform: translate(-50%, -50%) scale(0.5); }
            100% { opacity: 0; transform: translate(-50%, -50%) scale(1.5); }
        }
        @keyframes eyeball-shockwave {
            0% { opacity: 0.6; transform: translate(-50%, -50%) scale(0.8); }
            100% { opacity: 0; transform: translate(-50%, -50%) scale(2.5); }
        }
        
        /* Armored Cyclops Styles */
        .enemy.armored-cyclops { 
            width: 45px; height: 45px; 
        }
        .enemy.armored-cyclops .enemy-eyeball {
            width: 40px; height: 40px; 
            background: radial-gradient(circle at 50% 50%, #e8e8e8 35%, #d0d0d0 45%, #b8b8b8 100%);
            border: 3px solid #666;
            position: relative;
        }
        .enemy.armored-cyclops .enemy-eyeball::before {
            content: '';
            position: absolute;
            top: -2px; left: -2px; right: -2px; bottom: -2px;
            border: 2px solid #444;
            border-radius: 50%;
            background: linear-gradient(45deg, 
                rgba(255,255,255,0.3) 0%, 
                transparent 30%, 
                rgba(0,0,0,0.2) 70%, 
                rgba(255,255,255,0.1) 100%);
        }
        .enemy.armored-cyclops .enemy-eyeball::after {
            content: '';
            position: absolute;
            top: 50%; left: 50%;
            width: 4px; height: 4px;
            background: #888;
            border-radius: 50%;
            transform: translate(-50%, -50%);
            box-shadow: 
                17px 0 #888,     /* Right */
                -17px 0 #888,    /* Left */
                0 13px #888,     /* Bottom */
                0 -13px #888,    /* Top */
                14px 9px #888,   /* Bottom-right (oval) */
                -14px -9px #888, /* Top-left (oval) */
                14px -9px #888,  /* Top-right (oval) */
                -14px 9px #888,  /* Bottom-left (oval) */
                8px 11px #888,   /* Bottom-right mid */
                -8px -11px #888, /* Top-left mid */
                8px -11px #888,  /* Top-right mid */
                -8px 11px #888;  /* Bottom-left mid */
        }
        .enemy.armored-cyclops .enemy-iris {
            width: 18px; height: 18px;
            background: radial-gradient(circle at 30% 30%, #8B0000 20%, #550000 80%);
            border: 2px solid #330000;
        }
        .enemy.armored-cyclops .enemy-iris::after {
            top: 3px; left: 5px;
            width: 4px; height: 4px;
        }
        .enemy.armored-cyclops .enemy-health-bar { 
            width: 42px; height: 5px;
            border: 2px solid #c0392b;
        }
        
        .cyclops-death-explosion {
            position: absolute;
            width: 80px; height: 80px;
            background: radial-gradient(circle, #ff8c00 0%, #ff6b00 30%, #cc4400 60%, #881100 100%);
            border-radius: 50%;
            animation: cyclops-explode 0.8s ease-out forwards;
            transform: translate(-50%, -50%);
            z-index: 150;
        }
        .cyclops-death-explosion::before {
            content: '';
            position: absolute;
            top: 50%; left: 50%;
            width: 120%; height: 120%;
            background: radial-gradient(circle, rgba(255,255,255,0.9) 0%, transparent 60%);
            border-radius: 50%;
            transform: translate(-50%, -50%);
            animation: cyclops-flash 0.3s ease-out;
        }
        .cyclops-death-explosion::after {
            content: '';
            position: absolute;
            top: 50%; left: 50%;
            width: 140%; height: 140%;
            border: 3px solid #ff6b00;
            border-radius: 50%;
            transform: translate(-50%, -50%);
            animation: cyclops-shockwave 0.6s ease-out;
        }
        @keyframes cyclops-explode {
            0% { transform: translate(-50%, -50%) scale(0.3); opacity: 1; }
            50% { transform: translate(-50%, -50%) scale(1.2); opacity: 0.9; }
            100% { transform: translate(-50%, -50%) scale(2.2); opacity: 0; }
        }
        @keyframes cyclops-flash {
            0% { opacity: 1; transform: translate(-50%, -50%) scale(0.4); }
            100% { opacity: 0; transform: translate(-50%, -50%) scale(1.8); }
        }
        @keyframes cyclops-shockwave {
            0% { opacity: 0.8; transform: translate(-50%, -50%) scale(0.6); }
            100% { opacity: 0; transform: translate(-50%, -50%) scale(3); }
        }
        
        .armor-sparks {
            position: absolute;
            width: 30px; height: 30px;
            background: radial-gradient(circle, #ffcc00 0%, #ff8800 40%, transparent 70%);
            border-radius: 50%;
            animation: armor-spark-burst 0.3s ease-out forwards;
            transform: translate(-50%, -50%);
        }
        .armor-sparks::before, .armor-sparks::after {
            content: '';
            position: absolute;
            width: 4px; height: 4px;
            background: #ffaa00;
            border-radius: 50%;
        }
        .armor-sparks::before {
            top: 10%; left: 20%;
            animation: spark-particle 0.3s ease-out forwards;
        }
        .armor-sparks::after {
            top: 70%; right: 30%;
            animation: spark-particle 0.3s ease-out 0.1s forwards;
        }
        @keyframes armor-spark-burst {
            0% { transform: translate(-50%, -50%) scale(0.3); opacity: 1; }
            100% { transform: translate(-50%, -50%) scale(1.5); opacity: 0; }
        }
        @keyframes spark-particle {
            0% { transform: scale(1); opacity: 1; }
            100% { transform: scale(0.3) translate(20px, -15px); opacity: 0; }
        }
        
        #game-ui {
            position: absolute;
            top: 10px;
            left: 10px;
            z-index: 150;
            background-color: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 10px;
            border-radius: 5px;
            font-family: monospace;
            font-size: 16px;
        }
        
        #lives-display {
            color: #ff4444;
            font-weight: bold;
        }
        
        #pause-button, #music-toggle, #sound-toggle-ui {
            background: none;
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: white;
            font-size: 16px;
            cursor: pointer;
            padding: 4px 6px;
            border-radius: 4px;
            margin: 2px;
            transition: all 0.2s ease;
        }
        
        #pause-button {
            background-color: #4CAF50;
        }
        
        #pause-button:hover, #music-toggle:hover, #sound-toggle-ui:hover {
            background-color: rgba(255, 255, 255, 0.2);
            border-color: rgba(255, 255, 255, 0.6);
        }
        
        #pause-button:hover {
            opacity: 0.8;
        }
        
        #music-toggle.muted, #sound-toggle-ui.muted {
            opacity: 0.5;
            text-decoration: line-through;
        }
        
        #tower-palette {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            background-color: rgba(0, 0, 0, 0.95);
            padding: 15px;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 20px;
            z-index: 200;
            border-top: 3px solid #444;
            box-shadow: 0 -5px 15px rgba(0, 0, 0, 0.3);
        }
        
        #tower-palette-header {
            color: white;
            font-size: 14px;
            font-weight: bold;
            user-select: none;
            margin-right: 30px;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.8);
        }
        
        /* Game Over Modal */
        #game-over-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.8);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            animation: modal-fade-in 0.3s ease-out;
        }
        
        #game-over-content {
            background-color: #2c3e50;
            padding: 30px;
            border-radius: 15px;
            text-align: center;
            color: white;
            border: 3px solid #34495e;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
            max-width: 400px;
            animation: modal-slide-in 0.3s ease-out;
        }
        
        #game-over-content h2 {
            color: #e74c3c;
            margin-top: 0;
            font-size: 2em;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }
        
        #game-over-stats {
            margin: 20px 0;
            background-color: rgba(0, 0, 0, 0.3);
            padding: 15px;
            border-radius: 8px;
        }
        
        #game-over-stats div {
            margin: 8px 0;
            font-size: 1.1em;
        }
        
        #restart-button {
            background-color: #27ae60;
            color: white;
            border: none;
            padding: 12px 24px;
            font-size: 1.1em;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            margin-top: 15px;
        }
        
        #restart-button:hover {
            background-color: #2ecc71;
        }
        
        @keyframes modal-fade-in {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes modal-slide-in {
            from { transform: translateY(-50px) scale(0.9); opacity: 0; }
            to { transform: translateY(0) scale(1); opacity: 1; }
        }
        
        .tower-icon {
            width: 80px !important;
            height: 80px !important;
            border-radius: 10px;
            cursor: grab;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            border: 3px solid #666;
            transition: all 0.2s ease;
            position: relative;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
        }
        
        .tower-icon:hover {
            transform: translateY(-3px);
            border-color: #fff;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.4);
        }
        
        .tower-icon.dragging {
            cursor: grabbing;
            opacity: 0.8;
            transform: rotate(5deg);
        }
        
        .tower-icon.rocket {
            background: linear-gradient(135deg, #A0522D 0%, #8B4513 100%);
            color: #FFE4B5;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.8);
        }
        
        .tower-icon.ice {
            background: linear-gradient(180deg, #C0E0FF 0%, #A0C0FF 100%);
            box-shadow: 0 0 10px rgba(173, 216, 230, 0.6);
        }
        
        .tower-icon.lightning {
            background: linear-gradient(135deg, #404058 0%, #202030 100%);
            box-shadow: 0 0 8px #7DF9FF;
        }
        
        .tower-icon.machinegun {
            background: linear-gradient(135deg, #556B2F 0%, #2F4F2F 100%);
        }
        
        .tower-cost {
            position: absolute;
            bottom: -2px;
            right: -2px;
            background: #ff6b6b;
            color: white;
            font-size: 10px;
            padding: 1px 4px;
            border-radius: 3px;
            font-weight: bold;
        }

        #controls { 
            display: flex; flex-wrap: wrap; justify-content: space-around; 
            width: 1000px; margin-top: 10px; padding: 10px; 
            background-color: #e0e0e0; border-radius: 5px; 
            max-height: 200px; overflow-y: auto;
            transition: max-height 0.3s ease;
        }
        
        #controls.collapsed {
            max-height: 50px;
            overflow: hidden;
        }
        
        #controls-toggle {
            width: 100%;
            text-align: center;
            padding: 10px;
            cursor: pointer;
            background-color: #d0d0d0;
            border: none;
            border-radius: 5px;
            margin-bottom: 10px;
            font-weight: bold;
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
            cursor: grab;
            user-select: none;
            transition: transform 0.1s ease;
        }
        
        /* Type-specific tower base styles */
        .tower-base[id*="rocket"] { 
            background: linear-gradient(135deg, #A0522D 0%, #8B4513 100%); 
        }
        .tower-base[id*="rocket"]::before { 
            content: ''; position: absolute; width: 8px; height: 8px; 
            background: #696969; border-radius: 50%; 
            box-shadow: 15px 0 #696969, -15px 0 #696969, 0 15px #696969, 0 -15px #696969; 
        }
        
        .tower-base[id*="ice"] { 
            background: linear-gradient(180deg, #C0E0FF 0%, #A0C0FF 100%); 
            box-shadow: 0 0 15px 5px rgba(173, 216, 230, 0.6), inset 0 0 10px rgba(255,255,255,0.5); 
            border: 2px solid #ADD8E6; 
        }
        .tower-base[id*="ice"]::before, .tower-base[id*="ice"]::after { 
            content: ''; position: absolute; width: 0; height: 0; 
            border-left: 8px solid transparent; border-right: 8px solid transparent; 
            border-bottom: 15px solid rgba(200, 230, 255, 0.7); opacity: 0.8; 
        }
        .tower-base[id*="ice"]::before { transform: rotate(30deg) translate(18px, -15px); }
        .tower-base[id*="ice"]::after { transform: rotate(-40deg) translate(-15px, -18px) scaleX(-1); }
        
        .tower-base[id*="lightning"] { 
            background: linear-gradient(135deg, #404058 0%, #202030 100%); 
            border: 2px solid #606070; 
            box-shadow: 0 3px 6px rgba(0,0,0,0.5), inset 0 2px 4px rgba(0,0,0,0.3), 0 0 8px #7DF9FF; 
            animation: subtle-pulse-base-lightning 4s infinite alternate ease-in-out; 
        }
        .tower-base[id*="lightning"]::after, .tower-base[id*="lightning"]::before { 
            content: ''; position: absolute; width: 5px; height: 110%; 
            background: linear-gradient(to bottom, rgba(125, 249, 255, 0.2), rgba(0, 191, 255, 0.4)); 
            border-radius: 3px; box-shadow: 0 0 2px #00BFFF, 0 0 4px #7DF9FF; opacity: 0.6; 
        }
        .tower-base[id*="lightning"]::after { transform: rotate(35deg); }
        .tower-base[id*="lightning"]::before { transform: rotate(-35deg); }
        @keyframes subtle-pulse-base-lightning { 
            0% { box-shadow: 0 3px 6px rgba(0,0,0,0.5), inset 0 2px 4px rgba(0,0,0,0.3), 0 0 8px #7DF9FF; } 
            100% { box-shadow: 0 3px 6px rgba(0,0,0,0.5), inset 0 2px 4px rgba(0,0,0,0.3), 0 0 20px #00FFFF; } 
        }
        
        .tower-base[id*="machinegun"] { 
            background: linear-gradient(135deg, #556B2F 0%, #2F4F2F 100%); 
            border: 2px solid #1C3A1C; 
        }
        .tower-base[id*="machinegun"]::before { 
            content: ''; position: absolute; width: 10px; height: 10px; 
            background: #8B4513; border-radius: 50%; 
            box-shadow: 12px 0 #654321, -12px 0 #654321, 0 12px #654321, 0 -12px #654321; 
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
        <div id="game-ui">
            <div>Lives: <span id="lives-display">10</span></div>
            <div>Score: <span id="score-display">0</span></div>
            <div>Money: $<span id="money-display">500</span></div>
            <div>
                <button id="pause-button" title="Pause/Resume game">⏸️</button>
                <button id="music-toggle" title="Toggle background music">🎵</button>
                <button id="sound-toggle-ui" title="Toggle sound effects">🔊</button>
            </div>
        </div>
        
        <div id="tower-palette">
            <div id="tower-palette-header">TOWERS</div>
            <div class="tower-icon rocket" data-tower-type="rocket">
                🚀
                <div class="tower-cost">$100</div>
            </div>
            <div class="tower-icon ice" data-tower-type="ice">
                ❄️
                <div class="tower-cost">$75</div>
            </div>
            <div class="tower-icon lightning" data-tower-type="lightning">
                ⚡
                <div class="tower-cost">$150</div>
            </div>
            <div class="tower-icon machinegun" data-tower-type="machinegun">
                🔫
                <div class="tower-cost">$50</div>
            </div>
        </div>
        <div id="path-container"></div>
    </div>
    
    <div id="controls">
        <button id="controls-toggle">▼ Show/Hide Controls ▼</button>
        
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
    

    <script>
        // Simple standalone tower defense system
        const gameContainer = document.getElementById('game-container');
        const pathContainer = document.getElementById('path-container');
        const pauseButton = document.getElementById('pause-button');
        const livesDisplay = document.getElementById('lives-display');
        const scoreDisplay = document.getElementById('score-display');
        
        const gameWidth = gameContainer.offsetWidth;
        const gameHeight = gameContainer.offsetHeight;
        
        // Game state
        let lives = 10;
        let score = 0;
        let money = 500;
        let towerIdCounter = 0;
        
        let enemies = [];
        let projectiles = [];
        let enemyIdCounter = 0;
        let projectileIdCounter = 0;
        let effectIdCounter = 0;
        let globalRecoilMagnitude = 10;
        let globalRecoilDuration = 150;
        
        // Enemy type definitions
        const ENEMY_TYPES = {
            normal: {
                health: 300,
                speed: 0.7,
                width: 30,
                height: 30,
                armor: 0,
                spawnWeight: 70  // 70% chance
            },
            armoredCyclops: {
                health: 600,
                speed: 0.4,
                width: 40,
                height: 40,
                armor: 0.3,  // 30% damage reduction
                spawnWeight: 30  // 30% chance
            }
        };
        
        const ENEMY_SPAWN_INTERVAL = 1200; // Reduced from 2000 for more challenge
        let lastEnemySpawnTime = 0;
        
        let audioCtx = null;
        let userHasInteracted = false;
        let isPaused = false;
        let animationFrameId;
        let lastTimestamp = 0;
        let soundEnabled = true;
        let musicEnabled = true;
        let backgroundMusic = {
            playing: false,
            oscillators: [],
            gainNodes: [],
            sequence: 0,
            tempo: 180 // BPM - Much faster and more energetic
        };
        
        // Drag and drop state
        let dragState = {
            isDragging: false,
            draggedTower: null,
            startX: 0,
            startY: 0,
            offsetX: 0,
            offsetY: 0
        };
        
        // Tower placement state
        let placementState = {
            isPlacing: false,
            placingType: null,
            previewTower: null
        };
        
        
        // Path system - complex winding path
        const pathWaypoints = [
            { x: 0, y: 450 },           // Start (left side, middle-bottom)
            { x: 200, y: 450 },         // Go right
            { x: 200, y: 300 },         // Go up
            { x: 400, y: 300 },         // Go right
            { x: 400, y: 150 },         // Go up
            { x: 650, y: 150 },         // Go right
            { x: 650, y: 400 },         // Go down
            { x: 850, y: 400 },         // Go right
            { x: 850, y: 200 },         // Go up
            { x: 1000, y: 200 }         // End (right side)
        ];
        
        // Tower system - now supports multiple towers per type
        const towers = [];
        
        // Tower costs and default stats
        const towerDefaults = {
            rocket: { fireRate: 1500, damage: 70, range: 400, barrelLength: 16, cost: 100 },
            ice: { fireRate: 900, damage: 20, range: 400, barrelLength: 15, cost: 75, splashRadius: 70, splashDamageFactor: 0.5 },
            lightning: { fireRate: 700, damage: 35, range: 400, barrelLength: 20, cost: 150 },
            machinegun: { fireRate: 200, damage: 8, range: 350, barrelLength: 18, cost: 50 }
        };
        
        // Initialize starter towers (existing ones)
        function initializeStarterTowers() {
            const starterTowers = [
                { type: 'rocket', x: 125, y: 95 },
                { type: 'ice', x: 402.5, y: 95 },
                { type: 'lightning', x: 680, y: 95 },
                { type: 'machinegun', x: 957.5, y: 95 }
            ];
            
            starterTowers.forEach(starter => {
                const tower = createTower(starter.type, starter.x, starter.y, true);
                if (tower) {
                    towers.push(tower);
                }
            });
        }
        
        // Create a new tower
        function createTower(type, x, y, isStarter = false) {
            if (!isStarter && money < towerDefaults[type].cost) {
                return null; // Not enough money
            }
            
            towerIdCounter++;
            const towerId = `tower-${type}-${towerIdCounter}`;
            
            // Create tower DOM element
            const baseEl = document.createElement('div');
            baseEl.id = towerId;
            baseEl.classList.add('tower-base');
            baseEl.style.left = `${x - 22.5}px`;
            baseEl.style.top = `${y - 22.5}px`;
            
            const pivotEl = document.createElement('div');
            pivotEl.classList.add('turret-pivot');
            
            const modelEl = document.createElement('div');
            modelEl.classList.add('turret-model');
            
            // Add type-specific classes
            if (type === 'rocket') {
                modelEl.classList.add('turret-rocket');
            } else if (type === 'ice') {
                modelEl.classList.add('turret-ice');
            } else if (type === 'lightning') {
                modelEl.classList.add('turret-lightning-barrel');
            } else if (type === 'machinegun') {
                modelEl.classList.add('turret-machinegun');
            }
            
            const glowEl = document.createElement('div');
            glowEl.classList.add('turret-glow-indicator');
            
            modelEl.appendChild(glowEl);
            pivotEl.appendChild(modelEl);
            baseEl.appendChild(pivotEl);
            gameContainer.appendChild(baseEl);
            
            // Create tower object
            const tower = {
                id: towerId,
                type: type,
                baseEl: baseEl,
                pivotEl: pivotEl,
                modelEl: modelEl,
                glowEl: glowEl,
                x: x,
                y: y,
                ...towerDefaults[type],
                lastShotTime: 0,
                currentAngleRad: -Math.PI / 2,
                chargeLevel: 0,
                totalDamage: 0,
                disabled: false
            };
            
            // Deduct cost if not a starter tower
            if (!isStarter) {
                money -= towerDefaults[type].cost;
                updateUI();
            }
            
            // Setup drag and drop
            setupDragAndDrop(tower);
            
            return tower;
        }
        
        // Setup tower palette functionality
        function setupTowerPalette() {
            const towerIcons = document.querySelectorAll('.tower-icon');
            
            // Setup tower placement from icons
            towerIcons.forEach(icon => {
                icon.addEventListener('mousedown', (e) => startTowerPlacement(e, icon));
                icon.addEventListener('touchstart', (e) => startTowerPlacement(e, icon), { passive: false });
            });
        }
        
        
        function startTowerPlacement(e, icon) {
            e.preventDefault();
            e.stopPropagation();
            
            const towerType = icon.dataset.towerType;
            const cost = towerDefaults[towerType].cost;
            
            if (money < cost) {
                // Flash red or show message
                icon.style.filter = 'brightness(0.5) sepia(1) hue-rotate(0deg)';
                setTimeout(() => icon.style.filter = '', 200);
                return;
            }
            
            placementState.isPlacing = true;
            placementState.placingType = towerType;
            
            // Create preview immediately
            const gameRect = gameContainer.getBoundingClientRect();
            const clientX = e.type === 'touchstart' ? e.touches[0].clientX : e.clientX;
            const clientY = e.type === 'touchstart' ? e.touches[0].clientY : e.clientY;
            
            const x = clientX - gameRect.left;
            const y = clientY - gameRect.top;
            
            placementState.previewTower = document.createElement('div');
            placementState.previewTower.classList.add('tower-base');
            placementState.previewTower.style.opacity = '0.7';
            placementState.previewTower.style.pointerEvents = 'none';
            placementState.previewTower.style.zIndex = '999';
            placementState.previewTower.style.left = `${x - 22.5}px`;
            placementState.previewTower.style.top = `${y - 22.5}px`;
            gameContainer.appendChild(placementState.previewTower);
            
            // Add global event listeners for placement
            document.addEventListener('mousemove', placementMove);
            document.addEventListener('touchmove', placementMove, { passive: false });
            document.addEventListener('mouseup', placementEnd);
            document.addEventListener('touchend', placementEnd);
        }
        
        function placementMove(e) {
            if (!placementState.isPlacing || !placementState.previewTower) return;
            
            e.preventDefault();
            
            const rect = gameContainer.getBoundingClientRect();
            const clientX = e.type === 'touchmove' ? e.touches[0].clientX : e.clientX;
            const clientY = e.type === 'touchmove' ? e.touches[0].clientY : e.clientY;
            
            const x = clientX - rect.left;
            const y = clientY - rect.top;
            
            // Update preview tower position
            placementState.previewTower.style.left = `${x - 22.5}px`;
            placementState.previewTower.style.top = `${y - 22.5}px`;
            
            // Visual feedback for valid/invalid placement
            if (isValidTowerPosition(x, y)) {
                placementState.previewTower.style.borderColor = '#00ff00';
                placementState.previewTower.style.border = '2px solid #00ff00';
            } else {
                placementState.previewTower.style.borderColor = '#ff0000';
                placementState.previewTower.style.border = '2px solid #ff0000';
            }
        }
        
        function placementClick(e) {
            if (!placementState.isPlacing) return;
            
            const rect = gameContainer.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            
            // Check if position is valid (not on path, not too close to other towers)
            if (isValidTowerPosition(x, y)) {
                const newTower = createTower(placementState.placingType, x, y);
                if (newTower) {
                    towers.push(newTower);
                }
            }
            
            // End placement
            placementEnd();
        }
        
        function placementEnd(e) {
            if (!placementState.isPlacing) return;
            
            // Try to place the tower at the current position
            if (e && placementState.previewTower) {
                const rect = gameContainer.getBoundingClientRect();
                const clientX = e.type === 'touchend' ? e.changedTouches[0].clientX : e.clientX;
                const clientY = e.type === 'touchend' ? e.changedTouches[0].clientY : e.clientY;
                
                const x = clientX - rect.left;
                const y = clientY - rect.top;
                
                // Check if position is valid and create tower
                if (isValidTowerPosition(x, y)) {
                    const newTower = createTower(placementState.placingType, x, y);
                    if (newTower) {
                        towers.push(newTower);
                    }
                }
            }
            
            // Clean up preview tower
            if (placementState.previewTower) {
                placementState.previewTower.remove();
                placementState.previewTower = null;
            }
            
            // Reset placement state
            placementState.isPlacing = false;
            placementState.placingType = null;
            
            // Remove global event listeners
            document.removeEventListener('mousemove', placementMove);
            document.removeEventListener('touchmove', placementMove);
            document.removeEventListener('mouseup', placementEnd);
            document.removeEventListener('touchend', placementEnd);
            document.removeEventListener('click', placementClick);
        }
        
        function isValidTowerPosition(x, y) {
            // Check if too close to other towers
            for (const tower of towers) {
                const distance = Math.sqrt(Math.pow(x - tower.x, 2) + Math.pow(y - tower.y, 2));
                if (distance < 60) { // Minimum distance between towers
                    return false;
                }
            }
            
            // Check if on path (basic check - could be more sophisticated)
            for (let i = 0; i < pathWaypoints.length - 1; i++) {
                const start = pathWaypoints[i];
                const end = pathWaypoints[i + 1];
                
                // Simple distance to line segment check
                const distToPath = distanceToLineSegment(x, y, start.x, start.y, end.x, end.y);
                if (distToPath < 40) { // Too close to path
                    return false;
                }
            }
            
            return true;
        }
        
        function distanceToLineSegment(px, py, x1, y1, x2, y2) {
            const A = px - x1;
            const B = py - y1;
            const C = x2 - x1;
            const D = y2 - y1;
            
            const dot = A * C + B * D;
            const lenSq = C * C + D * D;
            
            if (lenSq === 0) return Math.sqrt(A * A + B * B);
            
            let param = dot / lenSq;
            param = Math.max(0, Math.min(1, param));
            
            const xx = x1 + param * C;
            const yy = y1 + param * D;
            
            const dx = px - xx;
            const dy = py - yy;
            
            return Math.sqrt(dx * dx + dy * dy);
        }
        
        // Create visual path
        function createPath() {
            pathContainer.innerHTML = '';
            
            // Draw path segments between waypoints with better continuity
            for (let i = 0; i < pathWaypoints.length - 1; i++) {
                const start = pathWaypoints[i];
                const end = pathWaypoints[i + 1];
                
                const segment = document.createElement('div');
                segment.classList.add('path-segment');
                
                const isHorizontal = Math.abs(end.x - start.x) > Math.abs(end.y - start.y);
                const pathWidth = 50;
                
                if (isHorizontal) {
                    const width = Math.abs(end.x - start.x) + pathWidth;
                    segment.style.left = `${Math.min(start.x, end.x) - pathWidth/2}px`;
                    segment.style.top = `${start.y - pathWidth/2}px`;
                    segment.style.width = `${width}px`;
                    segment.style.height = `${pathWidth}px`;
                } else {
                    const height = Math.abs(end.y - start.y) + pathWidth;
                    segment.style.left = `${start.x - pathWidth/2}px`;
                    segment.style.top = `${Math.min(start.y, end.y) - pathWidth/2}px`;
                    segment.style.width = `${pathWidth}px`;
                    segment.style.height = `${height}px`;
                }
                
                pathContainer.appendChild(segment);
            }
            
            // Add corner connectors for better visual continuity
            for (let i = 1; i < pathWaypoints.length - 1; i++) {
                const corner = document.createElement('div');
                corner.classList.add('path-segment');
                corner.style.left = `${pathWaypoints[i].x - 25}px`;
                corner.style.top = `${pathWaypoints[i].y - 25}px`;
                corner.style.width = '50px';
                corner.style.height = '50px';
                corner.style.borderRadius = '25px';
                pathContainer.appendChild(corner);
            }
        }
        
        // Get position along path based on progress (0 to 1)
        function getPositionOnPath(progress) {
            if (progress <= 0) return { x: pathWaypoints[0].x, y: pathWaypoints[0].y };
            if (progress >= 1) return { x: pathWaypoints[pathWaypoints.length - 1].x, y: pathWaypoints[pathWaypoints.length - 1].y };
            
            const totalSegments = pathWaypoints.length - 1;
            const segmentProgress = progress * totalSegments;
            const currentSegment = Math.floor(segmentProgress);
            const segmentFraction = segmentProgress - currentSegment;
            
            if (currentSegment >= totalSegments) {
                return { x: pathWaypoints[pathWaypoints.length - 1].x, y: pathWaypoints[pathWaypoints.length - 1].y };
            }
            
            const start = pathWaypoints[currentSegment];
            const end = pathWaypoints[currentSegment + 1];
            
            return {
                x: start.x + (end.x - start.x) * segmentFraction,
                y: start.y + (end.y - start.y) * segmentFraction
            };
        }
        
        // Initialize towers (no longer needed with new system)
        function initializeTowers() {
            // Initialize starter towers
            initializeStarterTowers();
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
                    
                case 'eyeball_death':
                    // Squelchy organic explosion with wet splat
                    dur = 0.8;
                    
                    // Wet splat sound
                    noiseSource = audioCtx.createBufferSource();
                    buffer = audioCtx.createBuffer(1, audioCtx.sampleRate * 0.4, audioCtx.sampleRate);
                    output = buffer.getChannelData(0);
                    for (let i = 0; i < buffer.length; i++) {
                        output[i] = (Math.random() * 2 - 1) * Math.exp(-i / (buffer.length * 0.2));
                    }
                    noiseSource.buffer = buffer;
                    filter = audioCtx.createBiquadFilter();
                    filter.type = "lowpass";
                    filter.frequency.setValueAtTime(600, now);
                    filter.frequency.exponentialRampToValueAtTime(150, now + 0.4);
                    filter.Q.value = 2;
                    gain = audioCtx.createGain();
                    noiseSource.connect(filter);
                    filter.connect(gain);
                    gain.connect(audioCtx.destination);
                    gain.gain.setValueAtTime(0, now);
                    gain.gain.linearRampToValueAtTime(0.5, now + 0.01);
                    gain.gain.exponentialRampToValueAtTime(0.001, now + 0.4);
                    noiseSource.start(now);
                    noiseSource.stop(now + 0.4);
                    
                    // Organic pop sound
                    osc = audioCtx.createOscillator();
                    const popGain = audioCtx.createGain();
                    osc.type = 'sine';
                    osc.frequency.setValueAtTime(200, now + 0.02);
                    osc.frequency.exponentialRampToValueAtTime(80, now + 0.15);
                    popGain.gain.setValueAtTime(0, now + 0.02);
                    popGain.gain.linearRampToValueAtTime(0.3, now + 0.04);
                    popGain.gain.exponentialRampToValueAtTime(0.001, now + 0.15);
                    osc.connect(popGain);
                    popGain.connect(audioCtx.destination);
                    osc.start(now + 0.02);
                    osc.stop(now + 0.15);
                    break;
                    
                case 'cyclops_death':
                    // Metallic armored explosion with deeper tones
                    dur = 1.0;
                    
                    // Heavy metallic clang
                    osc = audioCtx.createOscillator();
                    const metalGain = audioCtx.createGain();
                    osc.type = 'square';
                    osc.frequency.setValueAtTime(120, now);
                    osc.frequency.exponentialRampToValueAtTime(60, now + 0.3);
                    metalGain.gain.setValueAtTime(0, now);
                    metalGain.gain.linearRampToValueAtTime(0.4, now + 0.02);
                    metalGain.gain.exponentialRampToValueAtTime(0.001, now + 0.3);
                    osc.connect(metalGain);
                    metalGain.connect(audioCtx.destination);
                    osc.start(now);
                    osc.stop(now + 0.3);
                    
                    // Armor debris scatter
                    for (let k = 0; k < 8; k++) {
                        const debrisOsc = audioCtx.createOscillator();
                        const debrisGain = audioCtx.createGain();
                        debrisOsc.type = 'sawtooth';
                        const metalFreq = 800 + Math.random() * 1200;
                        debrisOsc.frequency.setValueAtTime(metalFreq, now + k * 0.05 + 0.1);
                        debrisOsc.frequency.exponentialRampToValueAtTime(metalFreq * 0.3, now + k * 0.05 + 0.4);
                        debrisGain.gain.setValueAtTime(0, now + k * 0.05 + 0.1);
                        debrisGain.gain.linearRampToValueAtTime(0.15 / (k * 0.3 + 1), now + k * 0.05 + 0.12);
                        debrisGain.gain.exponentialRampToValueAtTime(0.001, now + k * 0.05 + 0.4);
                        debrisOsc.connect(debrisGain);
                        debrisGain.connect(audioCtx.destination);
                        debrisOsc.start(now + k * 0.05 + 0.1);
                        debrisOsc.stop(now + k * 0.05 + 0.4);
                    }
                    break;
            }
        }
        
        // 8-bit Background Music System
        function startBackgroundMusic() {
            if (!musicEnabled || !ensureAudioContext() || backgroundMusic.playing) return;
            
            backgroundMusic.playing = true;
            backgroundMusic.sequence = 0;
            playMusicSequence();
        }
        
        function stopBackgroundMusic() {
            backgroundMusic.playing = false;
            backgroundMusic.oscillators.forEach(osc => {
                try { osc.stop(); } catch (e) {}
            });
            backgroundMusic.gainNodes.forEach(gain => {
                try { gain.disconnect(); } catch (e) {}
            });
            backgroundMusic.oscillators = [];
            backgroundMusic.gainNodes = [];
        }
        
        function playMusicSequence() {
            if (!backgroundMusic.playing || !audioCtx) return;
            
            const now = audioCtx.currentTime;
            const beatDuration = 60 / backgroundMusic.tempo; // Duration of one beat
            
            // 8-bit adventure melody - Energetic and heroic!
            const melodySequence = [
                // Heroic opening (4 bars) - Fast ascending runs
                [523, 659, 784, 1047, 784, 659, 784, 1047], // C E G C G E G C
                [1175, 1047, 880, 784, 880, 1047, 1175, 1319], // D C A G A C D E
                [1319, 1175, 1047, 880, 1047, 880, 784, 659], // E D C A C A G E
                [784, 880, 1047, 1319, 1047, 784, 659, 523], // G A C E C G E C
                
                // Adventure bridge (4 bars) - Staccato jumps
                [880, 0, 1047, 0, 1319, 0, 1568, 0],       // A - C - E - G -
                [1568, 1319, 1175, 1047, 880, 784, 659, 587], // G E D C A G E D
                [659, 784, 880, 1047, 1175, 1319, 1568, 1760], // E G A C D E G A
                [1760, 1568, 1319, 1047, 784, 659, 523, 440], // A G E C G E C A
                
                // Victory theme (4 bars) - Triumphant arpeggios
                [523, 659, 784, 1047, 523, 659, 784, 1047], // C E G C C E G C
                [587, 740, 880, 1175, 587, 740, 880, 1175], // D F♯ A D D F♯ A D
                [523, 659, 784, 1047, 1319, 1047, 784, 659], // C E G C E C G E
                [523, 784, 1047, 1319, 1047, 784, 523, 0]   // C G C E C G C -
            ];
            
            // Driving bass line - Pulsing eighth notes
            const bassSequence = [
                [131, 131, 196, 196, 131, 131, 196, 196], // C C G G C C G G
                [147, 147, 220, 220, 147, 147, 220, 220], // D D A A D D A A
                [165, 165, 247, 247, 165, 165, 247, 247], // E E B B E E B B
                [131, 131, 196, 196, 131, 196, 131, 196], // C C G G C G C G
                
                [110, 110, 165, 165, 110, 110, 165, 165], // A A E E A A E E
                [123, 123, 185, 185, 123, 123, 185, 185], // B B F♯ F♯ B B F♯ F♯
                [131, 131, 196, 196, 131, 131, 196, 196], // C C G G C C G G
                [147, 147, 220, 220, 147, 220, 147, 220], // D D A A D A D A
                
                [131, 196, 131, 196, 131, 196, 131, 196], // C G C G C G C G
                [147, 220, 147, 220, 147, 220, 147, 220], // D A D A D A D A
                [131, 196, 131, 196, 165, 196, 165, 196], // C G C G E G E G
                [131, 196, 131, 196, 131, 196, 131, 0]    // C G C G C G C -
            ];
            
            const currentBar = Math.floor(backgroundMusic.sequence / 8) % melodySequence.length;
            const currentBeat = backgroundMusic.sequence % 8;
            
            const melodyFreq = melodySequence[currentBar][currentBeat];
            const bassFreq = bassSequence[currentBar][currentBeat];
            
            // Play melody
            if (melodyFreq > 0) {
                const melodyOsc = audioCtx.createOscillator();
                const melodyGain = audioCtx.createGain();
                
                melodyOsc.type = 'square'; // 8-bit square wave
                melodyOsc.frequency.setValueAtTime(melodyFreq, now);
                
                melodyGain.gain.setValueAtTime(0, now);
                melodyGain.gain.linearRampToValueAtTime(0.12, now + 0.005); // Punchier attack
                melodyGain.gain.exponentialRampToValueAtTime(0.08, now + beatDuration * 0.3); // Hold longer
                melodyGain.gain.exponentialRampToValueAtTime(0.01, now + beatDuration * 0.9); // Quick release
                melodyGain.gain.linearRampToValueAtTime(0, now + beatDuration);
                
                melodyOsc.connect(melodyGain);
                melodyGain.connect(audioCtx.destination);
                
                melodyOsc.start(now);
                melodyOsc.stop(now + beatDuration);
                
                backgroundMusic.oscillators.push(melodyOsc);
                backgroundMusic.gainNodes.push(melodyGain);
            }
            
            // Play bass
            if (bassFreq > 0) {
                const bassOsc = audioCtx.createOscillator();
                const bassGain = audioCtx.createGain();
                
                bassOsc.type = 'triangle'; // Softer bass
                bassOsc.frequency.setValueAtTime(bassFreq, now);
                
                bassGain.gain.setValueAtTime(0, now);
                bassGain.gain.linearRampToValueAtTime(0.10, now + 0.005); // Punchier bass
                bassGain.gain.exponentialRampToValueAtTime(0.06, now + beatDuration * 0.4); // Sustain
                bassGain.gain.exponentialRampToValueAtTime(0.01, now + beatDuration * 0.85);
                bassGain.gain.linearRampToValueAtTime(0, now + beatDuration);
                
                bassOsc.connect(bassGain);
                bassGain.connect(audioCtx.destination);
                
                bassOsc.start(now);
                bassOsc.stop(now + beatDuration);
                
                backgroundMusic.oscillators.push(bassOsc);
                backgroundMusic.gainNodes.push(bassGain);
            }
            
            backgroundMusic.sequence++;
            
            // Schedule next beat
            if (backgroundMusic.playing) {
                setTimeout(() => playMusicSequence(), beatDuration * 1000);
            }
        }
        
        function toggleMusic() {
            const musicBtn = document.getElementById('music-toggle');
            
            if (musicEnabled) {
                musicEnabled = false;
                stopBackgroundMusic();
                musicBtn.classList.add('muted');
                musicBtn.textContent = '🔇';
            } else {
                musicEnabled = true;
                startBackgroundMusic();
                musicBtn.classList.remove('muted');
                musicBtn.textContent = '🎵';
            }
        }
        
        function toggleSoundEffects() {
            const soundBtn = document.getElementById('sound-toggle-ui');
            
            if (soundEnabled) {
                soundEnabled = false;
                soundBtn.classList.add('muted');
                soundBtn.textContent = '🔇';
            } else {
                soundEnabled = true;
                soundBtn.classList.remove('muted');
                soundBtn.textContent = '🔊';
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
            } else if (type === 'armor_sparks') { 
                effectEl.classList.add('armor-sparks'); 
                duration = 300; 
            }
            
            effectEl.style.left = `${x}px`;
            effectEl.style.top = `${y}px`;
            gameContainer.appendChild(effectEl);
            setTimeout(() => effectEl.remove(), duration);
        }
        
        // Enemy management
        function chooseEnemyType() {
            const totalWeight = Object.values(ENEMY_TYPES).reduce((sum, type) => sum + type.spawnWeight, 0);
            let random = Math.random() * totalWeight;
            
            for (const [typeName, typeData] of Object.entries(ENEMY_TYPES)) {
                random -= typeData.spawnWeight;
                if (random <= 0) {
                    return typeName;
                }
            }
            return 'normal'; // fallback
        }
        
        function applySlowEffect(enemy, slowMultiplier, duration) {
            enemy.slowEffect.isSlowed = true;
            enemy.slowEffect.slowMultiplier = slowMultiplier;
            enemy.slowEffect.slowEndTime = performance.now() + duration;
            enemy.speed = enemy.baseSpeed * slowMultiplier;
            
            // Visual effect - make enemy slightly blue
            if (enemy.el) {
                enemy.el.style.filter = 'brightness(0.8) saturate(2) hue-rotate(180deg)';
            }
        }
        
        function updateSlowEffect(enemy, currentTime) {
            if (enemy.slowEffect.isSlowed && currentTime >= enemy.slowEffect.slowEndTime) {
                enemy.slowEffect.isSlowed = false;
                enemy.slowEffect.slowMultiplier = 1.0;
                enemy.speed = enemy.baseSpeed;
                
                // Remove visual effect
                if (enemy.el) {
                    enemy.el.style.filter = '';
                }
            }
        }
        
        function spawnEnemy() {
            enemyIdCounter++;
            
            // Choose enemy type based on spawn weights
            const enemyType = chooseEnemyType();
            const enemyStats = ENEMY_TYPES[enemyType];
            
            const enemyEl = document.createElement('div');
            enemyEl.classList.add('enemy');
            enemyEl.id = `enemy-${enemyIdCounter}`;
            
            // Add type-specific class
            if (enemyType === 'armoredCyclops') {
                enemyEl.classList.add('armored-cyclops');
            }
            
            const startPos = getPositionOnPath(0);
            enemyEl.style.left = `${startPos.x - enemyStats.width/2}px`;
            enemyEl.style.top = `${startPos.y - enemyStats.height/2}px`;
            
            // Create eyeball structure
            const eyeball = document.createElement('div');
            eyeball.classList.add('enemy-eyeball');
            const iris = document.createElement('div');
            iris.classList.add('enemy-iris');
            eyeball.appendChild(iris);
            enemyEl.appendChild(eyeball);
            
            // Create health bar
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
                irisEl: iris,
                type: enemyType,
                x: startPos.x,
                y: startPos.y,
                health: enemyStats.health,
                maxHealth: enemyStats.health,
                baseSpeed: enemyStats.speed,
                speed: enemyStats.speed,
                armor: enemyStats.armor,
                healthFillEl: healthFill,
                age: 0,
                width: enemyStats.width,
                height: enemyStats.height,
                pathProgress: 0,  // Track progress along path (0 to 1)
                slowEffect: {
                    isSlowed: false,
                    slowMultiplier: 1.0,
                    slowEndTime: 0
                }
            });
        }
        
        // Update game UI
        function updateUI() {
            livesDisplay.textContent = lives;
            scoreDisplay.textContent = score;
            document.getElementById('money-display').textContent = money;
        }
        
        // Lose a life
        function loseLife() {
            lives--;
            updateUI();
            
            if (lives <= 0) {
                showGameOverModal();
            }
        }
        
        function showGameOverModal() {
            // Pause the game
            isPaused = true;
            
            // Calculate enemies defeated
            const enemiesDefeated = Math.floor(score / 10); // Assuming 10 points per normal enemy
            
            // Update modal content
            document.getElementById('final-score').textContent = score;
            document.getElementById('final-money').textContent = money;
            document.getElementById('enemies-defeated').textContent = enemiesDefeated;
            
            // Show modal
            const modal = document.getElementById('game-over-modal');
            modal.style.display = 'flex';
            
            // Setup restart button
            const restartBtn = document.getElementById('restart-button');
            restartBtn.onclick = restartGame;
        }
        
        function restartGame() {
            // Reset game state
            lives = 10;
            score = 0;
            money = 500;
            
            // Clear enemies and projectiles
            enemies.forEach(enemy => enemy.el.remove());
            projectiles.forEach(proj => proj.el.remove());
            enemies = [];
            projectiles = [];
            
            // Reset tower damage counters
            towers.forEach(tower => {
                tower.totalDamage = 0;
                if (tower.damageEl) {
                    tower.damageEl.textContent = '0';
                }
            });
            
            // Hide modal
            document.getElementById('game-over-modal').style.display = 'none';
            
            // Update UI and resume
            updateUI();
            isPaused = false;
            lastTimestamp = performance.now();
            
            // Restart with initial enemies
            initialEnemyFill();
        }
        
        function initialEnemyFill() {
            // Start with a few enemies already on the path
            for (let i = 0; i < 3; i++) {
                spawnEnemy();
                if (enemies.length > 0) {
                    const enemy = enemies[enemies.length - 1];
                    enemy.pathProgress = i * 0.2; // Space them out along the path
                    const pos = getPositionOnPath(enemy.pathProgress);
                    enemy.x = pos.x;
                    enemy.y = pos.y;
                    enemy.el.style.left = `${pos.x - enemy.width/2}px`;
                    enemy.el.style.top = `${pos.y - enemy.height/2}px`;
                }
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
                enemy.age++;
                
                // Update slow effects
                updateSlowEffect(enemy, currentTime);
                
                // Move along path
                const pathLength = pathWaypoints.length - 1;
                const moveDistance = enemy.speed * actualDeltaFactor;
                const progressIncrement = moveDistance / (pathLength * 100); // Adjust speed as needed
                
                enemy.pathProgress += progressIncrement;
                
                if (enemy.pathProgress >= 1.0) {
                    // Enemy reached the end - lose a life
                    loseLife();
                    enemy.el.remove();
                    enemies.splice(i, 1);
                    continue;
                }
                
                // Update position based on path progress
                const pos = getPositionOnPath(enemy.pathProgress);
                enemy.x = pos.x;
                enemy.y = pos.y;
                enemy.el.style.left = `${pos.x - enemy.width/2}px`;
                enemy.el.style.top = `${pos.y - enemy.height/2}px`;
                
                const healthPercentage = (enemy.health / enemy.maxHealth) * 100;
                enemy.healthFillEl.style.width = `${Math.max(0, healthPercentage)}%`;
                
                // Update iris tracking
                updateEyeballIris(enemy);
                
                if (enemy.health <= 0) {
                    // Create death explosion based on enemy type
                    if (enemy.type === 'armoredCyclops') {
                        createCyclopsDeathExplosion(enemy.x, enemy.y);
                        score += 20; // More points for tougher enemy
                        money += 10;
                    } else {
                        createEyeballDeathExplosion(enemy.x, enemy.y);
                        score += 10;
                        money += 5;
                    }
                    
                    updateUI();
                    enemy.el.remove();
                    enemies.splice(i, 1);
                    continue;
                }
            }
        }
        
        // Eyeball iris tracking
        let mouseX = 0, mouseY = 0;
        
        function updateEyeballIris(enemy) {
            if (!enemy.irisEl) return;
            
            let targetX, targetY;
            
            // Try to track mouse cursor first
            if (mouseX !== 0 || mouseY !== 0) {
                targetX = mouseX;
                targetY = mouseY;
            } else {
                // Fall back to closest turret
                let closestTower = null;
                let minDistance = Infinity;
                
                for (const tower of towers) {
                    const distance = Math.sqrt(
                        Math.pow(enemy.x - tower.x, 2) + 
                        Math.pow(enemy.y - tower.y, 2)
                    );
                    if (distance < minDistance) {
                        minDistance = distance;
                        closestTower = tower;
                    }
                }
                
                if (closestTower) {
                    targetX = closestTower.x;
                    targetY = closestTower.y;
                } else {
                    // Default to center if no target
                    return;
                }
            }
            
            // Calculate angle from enemy to target
            const dx = targetX - enemy.x;
            const dy = targetY - enemy.y;
            const angle = Math.atan2(dy, dx);
            
            // Move iris within eyeball bounds (max 6px from center)
            const maxOffset = 6;
            const irisX = Math.cos(angle) * maxOffset;
            const irisY = Math.sin(angle) * maxOffset;
            
            enemy.irisEl.style.transform = `translate(${irisX}px, ${irisY}px)`;
        }
        
        function createEyeballDeathExplosion(x, y) {
            const explosion = document.createElement('div');
            explosion.classList.add('visual-effect', 'eyeball-death-explosion');
            explosion.style.left = `${x}px`;
            explosion.style.top = `${y}px`;
            
            gameContainer.appendChild(explosion);
            
            // Play explosion sound
            playSound('eyeball_death');
            
            setTimeout(() => explosion.remove(), 600);
        }
        
        function createCyclopsDeathExplosion(x, y) {
            const explosion = document.createElement('div');
            explosion.classList.add('visual-effect', 'cyclops-death-explosion');
            explosion.style.left = `${x}px`;
            explosion.style.top = `${y}px`;
            
            gameContainer.appendChild(explosion);
            
            // Play explosion sound (more metallic)
            playSound('cyclops_death');
            
            setTimeout(() => explosion.remove(), 800);
        }
        
        // Track mouse for iris targeting
        document.addEventListener('mousemove', (e) => {
            const rect = gameContainer.getBoundingClientRect();
            mouseX = e.clientX - rect.left;
            mouseY = e.clientY - rect.top;
        });
        
        // Reset mouse tracking when cursor leaves game area
        gameContainer.addEventListener('mouseleave', () => {
            mouseX = 0;
            mouseY = 0;
        });
        
        // Tower targeting and firing
        function findTarget(tower) {
            let closestEnemy = null;
            let minDistance = tower.range;
            
            for (const enemy of enemies) {
                // Target the center of the enemy
                const enemyCenterX = enemy.x;
                const enemyCenterY = enemy.y;
                
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
            // Target the center of the enemy
            const targetX = target.x;
            const targetY = target.y;
            const angleToTargetRad = Math.atan2(targetY - tower.y, targetX - tower.x);
            
            tower.currentAngleRad = angleToTargetRad;
            
            if (tower.pivotEl) {
                tower.pivotEl.style.transform = `rotate(${angleToTargetRad - Math.PI/2}rad)`;
            }
        }
        
        function fireTower(tower, target) {
            if (tower.type === 'lightning') {
                // Lightning is instant hit with visual bolt
                // Apply armor damage reduction
                let finalDamage = tower.damage;
                if (target.armor > 0) {
                    finalDamage = tower.damage * (1 - target.armor);
                    if (target.type === 'armoredCyclops') {
                        createVisualEffect(target.x + target.width/2, target.y + target.height/2, 'armor_sparks');
                    }
                }
                
                const actualDamage = Math.min(finalDamage, target.health);
                target.health -= finalDamage;
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
                    // Apply armor damage reduction
                    let finalDamage = proj.damage;
                    if (targetEnemy.armor > 0) {
                        finalDamage = proj.damage * (1 - targetEnemy.armor);
                        // Show reduced damage effect for armored enemies
                        if (targetEnemy.type === 'armoredCyclops') {
                            createVisualEffect(targetX, targetY, 'armor_sparks');
                        }
                    }
                    
                    const actualDamage = Math.min(finalDamage, targetEnemy.health);
                    targetEnemy.health -= finalDamage;
                    updateTowerDamage(proj.tower, actualDamage);
                    
                    createVisualEffect(targetX, targetY, 'enemy_hit_sparkle');
                    
                    // Type-specific impact effects with sounds
                    if (proj.towerType === 'rocket') {
                        createVisualEffect(targetX, targetY, 'explosion');
                        playSound('rocket_impact');
                    } else if (proj.towerType === 'ice') {
                        createVisualEffect(targetX, targetY, 'ice_shatter');
                        playSound('ice_impact');
                        // Apply ice slow effect
                        applySlowEffect(targetEnemy, 0.5, 2000); // 50% speed for 2 seconds
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
            
            // Setup controls toggle
            const controlsToggle = document.getElementById('controls-toggle');
            const controls = document.getElementById('controls');
            if (controlsToggle && controls) {
                controlsToggle.addEventListener('click', () => {
                    controls.classList.toggle('collapsed');
                    const isCollapsed = controls.classList.contains('collapsed');
                    controlsToggle.textContent = isCollapsed ? '▲ Show Controls ▲' : '▼ Hide Controls ▼';
                });
                // Start collapsed
                controls.classList.add('collapsed');
                controlsToggle.textContent = '▲ Show Controls ▲';
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
            const buttonIcon = isPaused ? '▶️' : '⏸️';
            if (pauseButton) {
                pauseButton.textContent = buttonIcon;
                pauseButton.style.backgroundColor = isPaused ? '#f44336' : '#4CAF50';
            }
            if (!isPaused) {
                lastTimestamp = performance.now();
                ensureAudioContext();
            }
        }
        
        // Initialize game
        document.addEventListener('DOMContentLoaded', () => {
            createPath();
            initializeTowers();
            setupControls();
            setupTowerPalette();
            updateUI();
            
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
        
        if (pauseButton) {
            pauseButton.addEventListener('click', togglePauseState);
        }
        
        // Setup music and sound toggle buttons
        const musicToggle = document.getElementById('music-toggle');
        const soundToggleUI = document.getElementById('sound-toggle-ui');
        
        if (musicToggle) {
            musicToggle.addEventListener('click', toggleMusic);
        }
        
        if (soundToggleUI) {
            soundToggleUI.addEventListener('click', toggleSoundEffects);
        }
        
        // Start background music once user interacts
        document.addEventListener('click', () => {
            if (musicEnabled && !backgroundMusic.playing) {
                startBackgroundMusic();
            }
        }, { once: true });
    </script>
    
    <!-- Game Over Modal -->
    <div id="game-over-modal">
        <div id="game-over-content">
            <h2>GAME OVER</h2>
            <div id="game-over-stats">
                <div>Final Score: <span id="final-score">0</span></div>
                <div>Money Earned: $<span id="final-money">0</span></div>
                <div>Enemies Defeated: <span id="enemies-defeated">0</span></div>
            </div>
            <button id="restart-button">Play Again</button>
        </div>
    </div>
</body>
</html>