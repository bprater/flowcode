<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>2D Verdant Terrain Generator</title>
    <style>
        body {
            font-family: sans-serif;
            margin: 0;
            background-color: #1e272e; /* Even darker page background */
            color: #ecf0f1;
            display: flex;
            flex-direction: column; /* Stack H1 and app-layout vertically */
            height: 100vh;
            overflow: hidden; /* Prevent body scrollbars */
        }

        h1 {
            text-align: center;
            margin: 10px 0;
            padding: 5px 0;
            color: #ecf0f1;
            background-color: #2c3e50; /* Title background */
            width: 100%;
            box-sizing: border-box;
        }

        #app-layout {
            display: flex;
            flex-direction: row; /* Controls and Canvas side-by-side */
            flex-grow: 1; /* Take remaining vertical space */
            overflow: hidden; /* Important for nested flex/scroll */
        }

        #controls {
            width: 320px; /* Fixed width for sidebar */
            min-width: 280px; /* Minimum width */
            height: 100%; /* Fill height of parent app-layout */
            background-color: #34495e;
            padding: 15px;
            box-sizing: border-box;
            overflow-y: auto; /* Allow sliders to scroll if they overflow */
            display: grid;
            /* MODIFIED: Force single column for sliders in sidebar */
            grid-template-columns: 1fr; 
            gap: 15px; /* Increased gap for better spacing in single column */
            align-content: flex-start; /* Pack items to the top */
        }

        .control-group {
            display: flex;
            flex-direction: column;
            background-color: #4a6378; /* Slightly lighter group background */
            padding: 10px;
            border-radius: 5px;
        }

        .control-group h3 {
            margin-top: 0;
            margin-bottom: 10px;
            font-size: 1em;
            color: #95a5a6;
            border-bottom: 1px solid #7f8c8d;
            padding-bottom: 5px;
        }

        .control-group label {
            margin-bottom: 3px;
            font-size: 0.85em;
        }

        .control-group input[type="range"] {
            width: 100%;
            margin-bottom: 3px;
        }

        .control-group span {
            font-size: 0.75em;
            text-align: right;
            color: #bdc3c7;
        }
        
        #randomizeButton {
            /* No grid-column span needed if controls is single column grid */
            padding: 12px; /* Slightly larger button */
            background-color: #27ae60;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1em;
            transition: background-color 0.2s;
            margin-top: 10px; /* Add some space above the button */
        }

        #randomizeButton:hover {
            background-color: #229954;
        }

        #canvas-container {
            flex-grow: 1; /* Canvas area takes remaining horizontal space */
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 15px; /* Padding around the canvas */
            background-color: #2c3e50; /* Background for canvas area */
            height: 100%;
            box-sizing: border-box;
            overflow: hidden; /* Prevent canvas from causing scroll on container */
        }

        #gameCanvas {
            border: 1px solid #7f8c8d;
            cursor: grab;
            touch-action: none;
            /* Max width/height helps if JS sizing is slightly off or during resize */
            max-width: 100%; 
            max-height: 100%;
            display: block; /* Removes any potential extra space below canvas */
        }

        #gameCanvas:active {
            cursor: grabbing;
        }

    </style>
</head>
<body>
    <h1>Verdant Terrain Designer</h1>

    <div id="app-layout">
        <div id="controls">
            <!-- Grass Controls -->
            <div class="control-group">
                <h3>Grass</h3>
                <label for="grassBaseHue">Base Hue (80-140):</label>
                <input type="range" id="grassBaseHue" min="80" max="140" value="110">
                <span id="grassBaseHueValue">110</span>

                <label for="grassHueVariation">Hue Variation (+/-):</label>
                <input type="range" id="grassHueVariation" min="0" max="30" value="10">
                <span id="grassHueVariationValue">10</span>

                <label for="grassSaturation">Saturation (40-100%):</label>
                <input type="range" id="grassSaturation" min="40" max="100" value="70">
                <span id="grassSaturationValue">70</span>

                <label for="grassLightness">Lightness (30-70%):</label>
                <input type="range" id="grassLightness" min="30" max="70" value="50">
                <span id="grassLightnessValue">50</span>

                <label for="grassTextureScale">Texture Scale:</label>
                <input type="range" id="grassTextureScale" min="10" max="80" value="30">
                <span id="grassTextureScaleValue">0.030</span>

                <label for="grassTextureIntensity">Texture Intensity:</label>
                <input type="range" id="grassTextureIntensity" min="0" max="30" value="10">
                <span id="grassTextureIntensityValue">10</span>
            </div>

            <!-- Water Controls -->
            <div class="control-group">
                <h3>Water</h3>
                <label for="waterLevel">Water Level (Noise Threshold):</label>
                <input type="range" id="waterLevel" min="10" max="70" value="40"> <!-- 0.1 to 0.7 -->
                <span id="waterLevelValue">0.40</span>

                <label for="waterNoiseScale">Shape Scale:</label>
                <input type="range" id="waterNoiseScale" min="5" max="50" value="15">
                <span id="waterNoiseScaleValue">0.015</span>
                
                <label for="waterHue">Hue (180-240):</label>
                <input type="range" id="waterHue" min="180" max="240" value="200">
                <span id="waterHueValue">200</span>

                <label for="waterSaturation">Saturation (50-100%):</label>
                <input type="range" id="waterSaturation" min="50" max="100" value="75">
                <span id="waterSaturationValue">75</span>

                <label for="waterLightness">Lightness (25-65%):</label>
                <input type="range" id="waterLightness" min="25" max="65" value="45">
                <span id="waterLightnessValue">45</span>
                
                <label for="shoreBlend">Shore Blend Range:</label>
                <input type="range" id="shoreBlend" min="1" max="15" value="5"> <!-- 0.01 to 0.15 -->
                <span id="shoreBlendValue">0.05</span>
            </div>

            <!-- Tree Controls -->
            <div class="control-group">
                <h3>Trees</h3>
                <label for="treeDensity">Density:</label>
                <input type="range" id="treeDensity" min="0" max="100" value="30"> <!-- Scaled to 0-0.001 -->
                <span id="treeDensityValue">0.0003</span>

                <label for="treeMinSize">Min Size:</label>
                <input type="range" id="treeMinSize" min="10" max="30" value="15">
                <span id="treeMinSizeValue">15</span>

                <label for="treeMaxSize">Max Size:</label>
                <input type="range" id="treeMaxSize" min="15" max="50" value="30">
                <span id="treeMaxSizeValue">30</span>

                <label for="leafHue">Leaf Hue (80-130):</label>
                <input type="range" id="leafHue" min="80" max="130" value="100">
                <span id="leafHueValue">100</span>

                <label for="leafSaturation">Leaf Saturation (40-90%):</label>
                <input type="range" id="leafSaturation" min="40" max="90" value="60">
                <span id="leafSaturationValue">60</span>

                <label for="leafLightness">Leaf Lightness (25-55%):</label>
                <input type="range" id="leafLightness" min="25" max="55" value="40">
                <span id="leafLightnessValue">40</span>
                
                <label for="trunkLightness">Trunk Lightness (10-40%):</label>
                <input type="range" id="trunkLightness" min="10" max="40" value="20">
                <span id="trunkLightnessValue">20</span>
            </div>
            
            <!-- Foliage Controls -->
            <div class="control-group">
                <h3>Small Foliage</h3>
                <label for="foliageDensity">Density:</label>
                <input type="range" id="foliageDensity" min="0" max="150" value="40"> <!-- Scaled to 0-0.0015 -->
                <span id="foliageDensityValue">0.0004</span>

                <label for="foliageMinSize">Min Size:</label>
                <input type="range" id="foliageMinSize" min="1" max="5" value="2">
                <span id="foliageMinSizeValue">2</span>

                <label for="foliageMaxSize">Max Size:</label>
                <input type="range" id="foliageMaxSize" min="2" max="10" value="5">
                <span id="foliageMaxSizeValue">5</span>
            </div>
            
            <button id="randomizeButton">Randomize All Sliders</button>
        </div> <!-- End #controls -->

        <div id="canvas-container">
            <canvas id="gameCanvas"></canvas>
        </div> <!-- End #canvas-container -->

    </div> <!-- End #app-layout -->


    <script>
        // Compact Simplex Noise (same as before)
        var SimplexNoise = (function(){'use strict';var F2=(Math.sqrt(3)-1)/2,G2=(3-Math.sqrt(3))/6,F3=1/3,G3=1/6,F4=(Math.sqrt(5)-1)/4,G4=(5-Math.sqrt(5))/20,grad3=[[1,1,0],[-1,1,0],[1,-1,0],[-1,-1,0],[1,0,1],[-1,0,1],[1,0,-1],[-1,0,-1],[0,1,1],[0,-1,1],[0,1,-1],[0,-1,-1]],grad4=[[0,1,1,1],[0,1,1,-1],[0,1,-1,1],[0,1,-1,-1],[0,-1,1,1],[0,-1,1,-1],[0,-1,-1,1],[0,-1,-1,-1],[1,0,1,1],[1,0,1,-1],[1,0,-1,1],[1,0,-1,-1],[-1,0,1,1],[-1,0,1,-1],[-1,0,-1,1],[-1,0,-1,-1],[1,1,0,1],[1,1,0,-1],[1,-1,0,1],[1,-1,0,-1],[-1,1,0,1],[-1,1,0,-1],[-1,-1,0,1],[-1,-1,0,-1],[1,1,1,0],[1,1,-1,0],[1,-1,1,0],[1,-1,-1,0],[-1,1,1,0],[-1,1,-1,0],[-1,-1,1,0],[-1,-1,-1,0]];function SimplexNoise(random){if(typeof random!='function')random=Math.random;this.p=new Uint8Array(256);this.perm=new Uint8Array(512);this.permMod12=new Uint8Array(512);for(var i=0;i<256;i++){this.p[i]=random()*256|0}for(i=0;i<512;i++){this.perm[i]=this.p[i&255];this.permMod12[i]=this.perm[i]%12}}SimplexNoise.prototype={constructor:SimplexNoise,noise2D:function(x,y){var permMod12=this.permMod12,perm=this.perm,n0=0,n1=0,n2=0,s=(x+y)*F2,i=Math.floor(x+s),j=Math.floor(y+s),t=(i+j)*G2,X0=i-t,Y0=j-t,x0=x-X0,y0=y-Y0,i1,j1;if(x0>y0){i1=1;j1=0}else{i1=0;j1=1}var x1=x0-i1+G2,y1=y0-j1+G2,x2=x0-1+2*G2,y2=y0-1+2*G2,ii=i&255,jj=j&255,gi0=permMod12[ii+perm[jj]],gi1=permMod12[ii+i1+perm[jj+j1]],gi2=permMod12[ii+1+perm[jj+1]],t0=Math.max(0,0.5-x0*x0-y0*y0);if(t0>0){var g0=grad3[gi0];n0=t0*t0*t0*t0*(g0[0]*x0+g0[1]*y0)}var t1=Math.max(0,0.5-x1*x1-y1*y1);if(t1>0){var g1=grad3[gi1];n1=t1*t1*t1*t1*(g1[0]*x1+g1[1]*y1)}var t2=Math.max(0,0.5-x2*x2-y2*y2);if(t2>0){var g2=grad3[gi2];n2=t2*t2*t2*t2*(g2[0]*x2+g2[1]*y2)}return 70*(n0+n1+n2)},noise3D:function(x,y,z){var permMod12=this.permMod12,perm=this.perm,n0,n1,n2,n3,s=(x+y+z)*F3,i=Math.floor(x+s),j=Math.floor(y+s),k=Math.floor(z+s),t=(i+j+k)*G3,X0=i-t,Y0=j-t,Z0=k-t,x0=x-X0,y0=y-Y0,z0=z-Z0,i1,j1,k1,i2,j2,k2;if(x0>=y0){if(y0>=z0){i1=1;j1=0;k1=0;i2=1;j2=1;k2=0}else if(x0>=z0){i1=1;j1=0;k1=0;i2=1;j2=0;k2=1}else{i1=0;j1=0;k1=1;i2=1;j2=0;k2=1}}else{if(y0<z0){i1=0;j1=0;k1=1;i2=0;j2=1;k2=1}else if(x0<z0){i1=0;j1=1;k1=0;i2=0;j2=1;k2=1}else{i1=0;j1=1;k1=0;i2=1;j2=1;k2=0}}var x1=x0-i1+G3,y1=y0-j1+G3,z1=z0-k1+G3,x2=x0-i2+2*G3,y2=y0-j2+2*G3,z2=z0-k2+2*G3,x3=x0-1+3*G3,y3=y0-1+3*G3,z3=z0-1+3*G3,ii=i&255,jj=j&255,kk=k&255,gi0=permMod12[ii+perm[jj+perm[kk]]],gi1=permMod12[ii+i1+perm[jj+j1+perm[kk+k1]]],gi2=permMod12[ii+i2+perm[jj+j2+perm[kk+k2]]],gi3=permMod12[ii+1+perm[jj+1+perm[kk+1]]],t0=Math.max(0,0.6-x0*x0-y0*y0-z0*z0);if(t0>0){var g0=grad3[gi0];n0=t0*t0*t0*t0*(g0[0]*x0+g0[1]*y0+g0[2]*z0)}else n0=0;var t1=Math.max(0,0.6-x1*x1-y1*y1-z1*z1);if(t1>0){var g1=grad3[gi1];n1=t1*t1*t1*t1*(g1[0]*x1+g1[1]*y1+g1[2]*z1)}else n1=0;var t2=Math.max(0,0.6-x2*x2-y2*y2-z2*z2);if(t2>0){var g2=grad3[gi2];n2=t2*t2*t2*t2*(g2[0]*x2+g2[1]*y2+g2[2]*z2)}else n2=0;var t3=Math.max(0,0.6-x3*x3-y3*y3-z3*z3);if(t3>0){var g3=grad3[gi3];n3=t3*t3*t2*t3*(g3[0]*x3+g3[1]*y3+g3[2]*z3)}else n3=0;return 32*(n0+n1+n2+n3)}};return SimplexNoise})()

        const simplexBase = new SimplexNoise(() => Math.random());
        const simplexTexture = new SimplexNoise(() => Math.random());

        const canvas = document.getElementById('gameCanvas');
        const ctx = canvas.getContext('2d');
        const terrainCanvas = document.createElement('canvas');
        const terrainCtx = terrainCanvas.getContext('2d');

        // NEW: Get reference to canvas container for sizing
        const canvasContainer = document.getElementById('canvas-container');

        const TERRAIN_MULTIPLIER = 4;
        let viewportWidth, viewportHeight; // These will be set in resize and init

        // Initialize pan and dragging variables (same as before)
        let panX = 0; 
        let panY = 0;
        let isDragging = false;
        let lastMouseX, lastMouseY;

        const params = {}; 
        const flowerColors = [ 
            { h: 0, s: 90, l: 70 }, { h: 300, s: 90, l: 70 }, { h: 60, s: 90, l: 70 },
            { h: 260, s: 90, l: 75 }, { h: 0, s: 0, l: 95 }
        ];

        const sliders = { /* ... same slider definitions as before ... */ 
            grassBaseHue: document.getElementById('grassBaseHue'),
            grassHueVariation: document.getElementById('grassHueVariation'),
            grassSaturation: document.getElementById('grassSaturation'),
            grassLightness: document.getElementById('grassLightness'),
            grassTextureScale: document.getElementById('grassTextureScale'),
            grassTextureIntensity: document.getElementById('grassTextureIntensity'),
            waterLevel: document.getElementById('waterLevel'),
            waterNoiseScale: document.getElementById('waterNoiseScale'),
            waterHue: document.getElementById('waterHue'),
            waterSaturation: document.getElementById('waterSaturation'),
            waterLightness: document.getElementById('waterLightness'),
            shoreBlend: document.getElementById('shoreBlend'),
            treeDensity: document.getElementById('treeDensity'),
            treeMinSize: document.getElementById('treeMinSize'),
            treeMaxSize: document.getElementById('treeMaxSize'),
            leafHue: document.getElementById('leafHue'),
            leafSaturation: document.getElementById('leafSaturation'),
            leafLightness: document.getElementById('leafLightness'),
            trunkLightness: document.getElementById('trunkLightness'),
            foliageDensity: document.getElementById('foliageDensity'),
            foliageMinSize: document.getElementById('foliageMinSize'),
            foliageMaxSize: document.getElementById('foliageMaxSize'),
        };
        const randomizeButton = document.getElementById('randomizeButton');

        function updateParamsFromSliders() { /* ... same as before ... */ 
            params.grassBaseHue = parseInt(sliders.grassBaseHue.value);
            params.grassHueVariation = parseInt(sliders.grassHueVariation.value);
            params.grassSaturation = parseInt(sliders.grassSaturation.value);
            params.grassLightness = parseInt(sliders.grassLightness.value);
            params.grassTextureScale = parseInt(sliders.grassTextureScale.value) / 1000;
            params.grassTextureIntensity = parseInt(sliders.grassTextureIntensity.value);

            params.waterLevel = parseInt(sliders.waterLevel.value) / 100; 
            params.waterNoiseScale = parseInt(sliders.waterNoiseScale.value) / 1000;
            params.waterHue = parseInt(sliders.waterHue.value);
            params.waterSaturation = parseInt(sliders.waterSaturation.value);
            params.waterLightness = parseInt(sliders.waterLightness.value);
            params.shoreBlend = parseInt(sliders.shoreBlend.value) / 100;

            params.treeDensity = parseInt(sliders.treeDensity.value) / 100000;
            params.treeMinSize = parseInt(sliders.treeMinSize.value);
            params.treeMaxSize = parseInt(sliders.treeMaxSize.value);
            if (params.treeMinSize > params.treeMaxSize) { 
                params.treeMinSize = params.treeMaxSize;
                sliders.treeMinSize.value = params.treeMinSize;
            }
            params.leafHue = parseInt(sliders.leafHue.value);
            params.leafSaturation = parseInt(sliders.leafSaturation.value);
            params.leafLightness = parseInt(sliders.leafLightness.value);
            params.trunkLightness = parseInt(sliders.trunkLightness.value);
            
            params.foliageDensity = parseInt(sliders.foliageDensity.value) / 100000;
            params.foliageMinSize = parseInt(sliders.foliageMinSize.value);
            params.foliageMaxSize = parseInt(sliders.foliageMaxSize.value);
            if (params.foliageMinSize > params.foliageMaxSize) { 
                params.foliageMinSize = params.foliageMaxSize;
                sliders.foliageMinSize.value = params.foliageMinSize;
            }

            for (const key in sliders) {
                const span = document.getElementById(key + 'Value');
                if (span) {
                    let valueToDisplay = params[key] !== undefined ? params[key] : sliders[key].value; // Fallback for unscaled params
                     if (key === 'grassTextureScale' || key === 'waterNoiseScale' || key === 'waterLevel' || key === 'shoreBlend') {
                         valueToDisplay = parseFloat(valueToDisplay).toFixed(3);
                     } else if (key === 'treeDensity' || key === 'foliageDensity') {
                         valueToDisplay = parseFloat(valueToDisplay).toFixed(5);
                     }
                    span.textContent = valueToDisplay;
                }
            }
             document.getElementById('treeMinSizeValue').textContent = params.treeMinSize;
             document.getElementById('foliageMinSizeValue').textContent = params.foliageMinSize;
            generateAndDraw();
        }

        Object.values(sliders).forEach(slider => slider.addEventListener('input', updateParamsFromSliders));

        function randomizeSliders() { /* ... same as before ... */ 
            Object.values(sliders).forEach(slider => {
                const min = parseFloat(slider.min);
                const max = parseFloat(slider.max);
                const step = parseFloat(slider.step) || 1;
                let randomValue = min + Math.random() * (max - min);
                randomValue = Math.round(randomValue / step) * step;
                slider.value = Math.max(min, Math.min(max, randomValue));
            });
            if (parseFloat(sliders.treeMinSize.value) > parseFloat(sliders.treeMaxSize.value)) {
                sliders.treeMinSize.value = sliders.treeMaxSize.value;
            }
            if (parseFloat(sliders.foliageMinSize.value) > parseFloat(sliders.foliageMaxSize.value)) {
                sliders.foliageMinSize.value = sliders.foliageMaxSize.value;
            }
            updateParamsFromSliders();
        }
        randomizeButton.addEventListener('click', randomizeSliders);

        function generateTerrain() { /* ... same as before ... */ 
            console.time("TerrainGeneration");
            const { width, height } = terrainCanvas;
            const imageData = terrainCtx.createImageData(width, height);
            const data = imageData.data;

            for (let y = 0; y < height; y++) {
                for (let x = 0; x < width; x++) {
                    const i = (y * width + x) * 4;
                    const waterNoiseVal = (simplexBase.noise2D(x * params.waterNoiseScale, y * params.waterNoiseScale) + 1) / 2;
                    const textureNoiseVal = (simplexTexture.noise2D(x * params.grassTextureScale, y * params.grassTextureScale) + 1) / 2;
                    let r, g, b;
                    if (waterNoiseVal < params.waterLevel) {
                        const grassLVar = (textureNoiseVal - 0.5) * params.grassTextureIntensity;
                        const grassHVar = (textureNoiseVal - 0.5) * params.grassHueVariation;
                        const currentH = (params.grassBaseHue + grassHVar + 360) % 360;
                        const currentS = params.grassSaturation;
                        const currentL = Math.max(0, Math.min(100, params.grassLightness + grassLVar));
                        [r, g, b] = hslToRgb(currentH / 360, currentS / 100, currentL / 100);
                    } else { 
                        let currentL = params.waterLightness;
                        const shoreProximity = (waterNoiseVal - params.waterLevel) / params.shoreBlend;
                        if (shoreProximity < 1 && params.shoreBlend > 0) { // check params.shoreBlend > 0 to avoid division by zero
                             currentL = params.waterLightness - (1 - shoreProximity) * 15; 
                        }
                        const waterTextureVar = (textureNoiseVal - 0.5) * 5;
                        currentL = Math.max(0, Math.min(100, currentL + waterTextureVar));
                        [r, g, b] = hslToRgb(params.waterHue / 360, params.waterSaturation / 100, currentL / 100);
                    }
                    data[i] = r; data[i + 1] = g; data[i + 2] = b; data[i + 3] = 255;
                }
            }
            terrainCtx.putImageData(imageData, 0, 0);

            const numTrees = Math.floor(width * height * params.treeDensity);
            for (let i = 0; i < numTrees; i++) {
                const treeX = Math.random() * width;
                const treeY = Math.random() * height;
                const waterCheckVal = (simplexBase.noise2D(treeX * params.waterNoiseScale, treeY * params.waterNoiseScale) + 1) / 2;
                if (waterCheckVal < params.waterLevel) {
                    const treeSize = params.treeMinSize + Math.random() * (params.treeMaxSize - params.treeMinSize);
                    const trunkWidth = treeSize * (0.15 + Math.random() * 0.1);
                    const trunkHeight = treeSize * (0.3 + Math.random() * 0.2);
                    const canopyRadius = treeSize / 2;
                    terrainCtx.fillStyle = 'rgba(0,0,0,0.15)';
                    terrainCtx.beginPath();
                    terrainCtx.ellipse(treeX, treeY + trunkHeight * 0.3, canopyRadius * 0.9, canopyRadius * 0.4, 0, 0, Math.PI * 2);
                    terrainCtx.fill();
                    const trunkColor = hslToRgbString(params.leafHue - 60, params.leafSaturation * 0.5, params.trunkLightness);
                    terrainCtx.fillStyle = trunkColor;
                    terrainCtx.fillRect(treeX - trunkWidth / 2, treeY - trunkHeight / 2, trunkWidth, trunkHeight);
                    const numClumps = 3 + Math.floor(Math.random() * 3);
                    for (let c = 0; c < numClumps; c++) {
                        const clumpOffsetX = (Math.random() - 0.5) * canopyRadius * 0.6;
                        const clumpOffsetY = (Math.random() - 0.5) * canopyRadius * 0.4 - canopyRadius * 0.5;
                        const clumpSize = canopyRadius * (0.5 + Math.random() * 0.5);
                        const leafLVariation = (Math.random() - 0.5) * 10;
                        const leafHVariation = (Math.random() - 0.5) * (params.grassHueVariation/2);
                        const leafColor = hslToRgbString(
                            (params.leafHue + leafHVariation + 360) % 360, 
                            params.leafSaturation, 
                            Math.max(10, Math.min(90, params.leafLightness + leafLVariation))
                        );
                        terrainCtx.fillStyle = leafColor;
                        terrainCtx.beginPath();
                        terrainCtx.arc(treeX + clumpOffsetX, treeY + clumpOffsetY, clumpSize, 0, Math.PI * 2);
                        terrainCtx.fill();
                    }
                }
            }
            
            const numFoliage = Math.floor(width * height * params.foliageDensity);
            for (let i = 0; i < numFoliage; i++) {
                const foliageX = Math.random() * width;
                const foliageY = Math.random() * height;
                const waterCheckVal = (simplexBase.noise2D(foliageX * params.waterNoiseScale, foliageY * params.waterNoiseScale) + 1) / 2;
                if (waterCheckVal < params.waterLevel - params.shoreBlend * 0.2) { 
                    const foliageSize = params.foliageMinSize + Math.random() * (params.foliageMaxSize - params.foliageMinSize);
                    let foliageColor;
                    if (Math.random() < 0.3) { 
                        const flowerC = flowerColors[Math.floor(Math.random() * flowerColors.length)];
                        foliageColor = hslToRgbString(flowerC.h, flowerC.s, flowerC.l);
                    } else { 
                         const bushLVar = (Math.random() - 0.5) * 10;
                         const bushHVar = (Math.random() - 0.5) * params.grassHueVariation;
                         foliageColor = hslToRgbString(
                             (params.grassBaseHue + bushHVar + 360) % 360, 
                             params.grassSaturation * 0.8, 
                             Math.max(10, Math.min(90, params.grassLightness * 0.8 + bushLVar)) 
                         );
                    }
                    terrainCtx.fillStyle = foliageColor;
                    const numBlobs = 1 + Math.floor(Math.random() * 3); 
                    for(let b=0; b<numBlobs; b++) {
                        const blobOffsetX = (Math.random() - 0.5) * foliageSize * 0.5;
                        const blobOffsetY = (Math.random() - 0.5) * foliageSize * 0.5;
                        const blobRadius = foliageSize * (0.3 + Math.random() * 0.4);
                        terrainCtx.beginPath();
                        terrainCtx.arc(foliageX + blobOffsetX, foliageY + blobOffsetY, blobRadius, 0, Math.PI * 2);
                        terrainCtx.fill();
                    }
                }
            }
            console.timeEnd("TerrainGeneration");
        }

        function drawScene() { /* ... same as before ... */ 
            ctx.clearRect(0, 0, viewportWidth, viewportHeight);
            ctx.imageSmoothingEnabled = false; 
            ctx.drawImage(
                terrainCanvas,
                panX, panY, viewportWidth, viewportHeight,
                0, 0, viewportWidth, viewportHeight
            );
        }
        
        let generationTimeout;
        function generateAndDraw() { /* ... same as before ... */ 
            clearTimeout(generationTimeout);
            generationTimeout = setTimeout(() => {
                 generateTerrain();
                 drawScene();
            }, 50);
        }

        function hslToRgb(h, s, l) { /* ... same as before ... */ 
            let r, g, b;
            if (s == 0) { r = g = b = l; }
            else {
                const hue2rgb = (p, q, t) => {
                    if (t < 0) t += 1; if (t > 1) t -= 1;
                    if (t < 1 / 6) return p + (q - p) * 6 * t;
                    if (t < 1 / 2) return q;
                    if (t < 2 / 3) return p + (q - p) * (2 / 3 - t) * 6;
                    return p;
                };
                const q = l < 0.5 ? l * (1 + s) : l + s - l * s;
                const p = 2 * l - q;
                r = hue2rgb(p, q, h + 1 / 3); g = hue2rgb(p, q, h); b = hue2rgb(p, q, h - 1 / 3);
            }
            return [Math.round(r * 255), Math.round(g * 255), Math.round(b * 255)];
        }
        function hslToRgbString(h, s, l) { /* ... same as before ... */ 
            const [r,g,b] = hslToRgb(h/360, s/100, l/100);
            return `rgb(${r},${g},${b})`;
        }

        // Panning logic (same as before)
        function getMousePos(evt) { const rect = canvas.getBoundingClientRect(); return { x: evt.clientX - rect.left, y: evt.clientY - rect.top }; }
        function getTouchPos(evt) { const rect = canvas.getBoundingClientRect(); return { x: evt.touches[0].clientX - rect.left, y: evt.touches[0].clientY - rect.top }; }
        function startPan(pos) { isDragging = true; lastMouseX = pos.x; lastMouseY = pos.y; canvas.style.cursor = 'grabbing'; }
        function doPan(pos) {
            if (!isDragging) return;
            const dx = pos.x - lastMouseX; const dy = pos.y - lastMouseY;
            panX -= dx; panY -= dy;
            panX = Math.max(0, Math.min(panX, terrainCanvas.width - viewportWidth));
            panY = Math.max(0, Math.min(panY, terrainCanvas.height - viewportHeight));
            lastMouseX = pos.x; lastMouseY = pos.y;
            drawScene();
        }
        function endPan() { isDragging = false; canvas.style.cursor = 'grab'; }

        canvas.addEventListener('mousedown', (e) => startPan(getMousePos(e)));
        canvas.addEventListener('mousemove', (e) => doPan(getMousePos(e)));
        canvas.addEventListener('mouseup', endPan);
        canvas.addEventListener('mouseleave', endPan);
        canvas.addEventListener('touchstart', (e) => { e.preventDefault(); startPan(getTouchPos(e)); }, { passive: false });
        canvas.addEventListener('touchmove', (e) => { e.preventDefault(); doPan(getTouchPos(e)); }, { passive: false });
        canvas.addEventListener('touchend', endPan);
        canvas.addEventListener('touchcancel', endPan);
        
        // MODIFIED: Resize handler to use canvas-container dimensions
        function handleResize() {
            // Use clientWidth/Height of the container for the canvas.
            // Subtract padding of canvas-container (15px each side = 30px total)
            const containerPadding = 30; 
            viewportWidth = Math.max(50, canvasContainer.clientWidth - containerPadding);
            viewportHeight = Math.max(50, canvasContainer.clientHeight - containerPadding);
            
            // Optional: cap max size for performance or aesthetics
            viewportWidth = Math.min(1200, viewportWidth); 
            viewportHeight = Math.min(800, viewportHeight);

            canvas.width = viewportWidth;
            canvas.height = viewportHeight;

            const oldTerrainWidth = terrainCanvas.width;
            const oldTerrainHeight = terrainCanvas.height;

            terrainCanvas.width = viewportWidth * TERRAIN_MULTIPLIER;
            terrainCanvas.height = viewportHeight * TERRAIN_MULTIPLIER;
            
            // Recenter pan if possible, or reset if dimensions are too different
             if (oldTerrainWidth > 0 && oldTerrainHeight > 0) {
                panX = Math.max(0, Math.min(panX * (terrainCanvas.width / oldTerrainWidth), terrainCanvas.width - viewportWidth));
                panY = Math.max(0, Math.min(panY * (terrainCanvas.height / oldTerrainHeight), terrainCanvas.height - viewportHeight));
            } else { // First load or invalid old dimensions
                panX = (terrainCanvas.width - viewportWidth) / 2;
                panY = (terrainCanvas.height - viewportHeight) / 2;
            }

            generateAndDraw();
        }

        window.addEventListener('resize', handleResize);

        // Initial setup
        handleResize(); // Call resize handler to set initial canvas sizes
        updateParamsFromSliders(); // This will also call generateAndDraw

    </script>
</body>
</html>