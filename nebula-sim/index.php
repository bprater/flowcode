<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Epic Aurora Generator V2 (PI Fix)</title>
    <style>
        body {
            margin: 0;
            overflow: hidden;
            background-color: #000;
            color: #fff;
            font-family: 'Arial', sans-serif;
        }
        #scene-container {
            width: 100vw;
            height: 100vh;
            display: block;
        }
        #controls {
            position: absolute;
            bottom: 10px; /* MOVED TO BOTTOM */
            left: 10px;
            right: 10px;
            background: rgba(0, 0, 0, 0.75);
            padding: 10px;
            border-radius: 8px;
            color: #eee;
            z-index: 10;
            display: flex; /* For better layout of many controls */
            flex-wrap: wrap;
            justify-content: center;
            gap: 15px;
        }
        .control-group {
            padding: 5px 10px;
            border: 1px solid #444;
            border-radius: 5px;
            min-width: 200px; /* Adjust as needed */
        }
        .control-group h4 {
            margin-top: 0;
            margin-bottom: 8px;
            font-size: 0.9em;
            text-align: center;
        }
        #controls label {
            display: block;
            margin-top: 8px;
            font-size: 0.8em;
        }
        #controls input[type="range"] {
            width: 100%;
            margin-top: 3px;
        }
    </style>
</head>
<body>
    <div id="scene-container"></div>
    <div id="controls">
        <div class="control-group">
            <h4>Aurora Main</h4>
            <label for="auroraSpeed">Overall Speed:</label>
            <input type="range" id="auroraSpeed" min="0.01" max="0.5" step="0.005" value="0.07">

            <label for="auroraHeight">Height Scale:</label>
            <input type="range" id="auroraHeight" min="0.2" max="3" step="0.05" value="1.2">

            <label for="auroraIntensity">Intensity:</label>
            <input type="range" id="auroraIntensity" min="0.2" max="3.0" step="0.05" value="1.5">
        </div>
        <div class="control-group">
            <h4>Aurora Shape & Dance</h4>
            <label for="auroraComplexity">Horizontal Complexity:</label>
            <input type="range" id="auroraComplexity" min="1.0" max="15.0" step="0.1" value="5.0">

            <label for="auroraVerticality">Vertical Streaks:</label>
            <input type="range" id="auroraVerticality" min="1.0" max="20.0" step="0.1" value="8.0">
            
            <label for="danceIntensity">Dance Intensity:</label>
            <input type="range" id="danceIntensity" min="0.0" max="2.0" step="0.05" value="0.8">
        </div>
        <div class="control-group">
            <h4>Aurora Colors</h4>
            <label for="colorBalance">Green/Magenta Balance (0=G, 1=M):</label>
            <input type="range" id="colorBalance" min="0.0" max="1.0" step="0.01" value="0.3">

            <label for="brightnessBoost">Brightness Boost:</label>
            <input type="range" id="brightnessBoost" min="0.0" max="2.0" step="0.05" value="0.5">
        </div>
        <div class="control-group">
            <h4>Scene</h4>
            <label for="starCount">Star Count:</label>
            <input type="range" id="starCount" min="500" max="15000" step="100" value="4000">
            
            <label for="cometFrequency">Comet Freq (low=more):</label>
            <input type="range" id="cometFrequency" min="0.001" max="0.02" step="0.0005" value="0.006">
        </div>
    </div>

    <script type="importmap">
    {
        "imports": {
            "three": "https://unpkg.com/three@0.160.0/build/three.module.js"
        }
    }
    </script>

    <script type="module">
        import * as THREE from 'three';

        let scene, camera, renderer;
        let stars, nebula;
        let auroraMesh, auroraReflectionMesh;
        let comets = [];
        const MAX_COMETS = 5;
        let lakePlane; // Declared here, initialized in createLakeAndHorizon

        // --- Shader Code ---
        const glslNoise = `
            vec3 mod289(vec3 x) { return x - floor(x * (1.0 / 289.0)) * 289.0; }
            vec4 mod289(vec4 x) { return x - floor(x * (1.0 / 289.0)) * 289.0; }
            vec4 permute(vec4 x) { return mod289(((x*34.0)+1.0)*x); }
            vec4 taylorInvSqrt(vec4 r) { return 1.79284291400159 - 0.85373472095314 * r; }
            vec3 fade(vec3 t) { return t*t*t*(t*(t*6.0-15.0)+10.0); }

            float pnoise(vec3 P, vec3 rep) {
                vec3 Pi0_3 = mod(floor(P), rep); 
                vec3 Pi1_3 = mod(Pi0_3 + vec3(1.0), rep); 
                Pi0_3 = mod289(Pi0_3); 
                Pi1_3 = mod289(Pi1_3); 
                vec3 Pf0 = fract(P); 
                vec3 Pf1 = Pf0 - vec3(1.0);

                vec4 ix = vec4(Pi0_3.x, Pi1_3.x, Pi0_3.x, Pi1_3.x); 
                vec4 iy = vec4(Pi0_3.y, Pi0_3.y, Pi1_3.y, Pi1_3.y); 
                vec4 iz0 = vec4(Pi0_3.z); 
                vec4 iz1 = vec4(Pi1_3.z); 

                vec4 ixy = permute(ix + iy);
                vec4 ixy0 = permute(ixy + iz0);
                vec4 ixy1 = permute(ixy + iz1);
                
                vec4 gx0 = ixy0 * (1.0 / 7.0);
                vec4 gy0 = fract(floor(gx0) * (1.0 / 7.0)) - 0.5;
                gx0 = fract(gx0);
                vec4 gz0 = vec4(0.5) - abs(gx0) - abs(gy0);
                vec4 sz0 = step(gz0, vec4(0.0));
                gx0 -= sz0 * (step(0.0, gx0) - 0.5);
                gy0 -= sz0 * (step(0.0, gy0) - 0.5);

                vec4 gx1 = ixy1 * (1.0 / 7.0);
                vec4 gy1 = fract(floor(gx1) * (1.0 / 7.0)) - 0.5;
                gx1 = fract(gx1);
                vec4 gz1 = vec4(0.5) - abs(gx1) - abs(gy1);
                vec4 sz1 = step(gz1, vec4(0.0));
                gx1 -= sz1 * (step(0.0, gx1) - 0.5);
                gy1 -= sz1 * (step(0.0, gy1) - 0.5);

                vec3 g000 = vec3(gx0.x,gy0.x,gz0.x);
                vec3 g100 = vec3(gx0.y,gy0.y,gz0.y);
                vec3 g010 = vec3(gx0.z,gy0.z,gz0.z);
                vec3 g110 = vec3(gx0.w,gy0.w,gz0.w);
                vec3 g001 = vec3(gx1.x,gy1.x,gz1.x);
                vec3 g101 = vec3(gx1.y,gy1.y,gz1.y);
                vec3 g011 = vec3(gx1.z,gy1.z,gz1.z);
                vec3 g111 = vec3(gx1.w,gy1.w,gz1.w);

                vec4 norm0 = taylorInvSqrt(vec4(dot(g000, g000), dot(g010, g010), dot(g100, g100), dot(g110, g110)));
                g000 *= norm0.x;
                g010 *= norm0.y;
                g100 *= norm0.z;
                g110 *= norm0.w;
                vec4 norm1 = taylorInvSqrt(vec4(dot(g001, g001), dot(g011, g011), dot(g101, g101), dot(g111, g111)));
                g001 *= norm1.x;
                g011 *= norm1.y;
                g101 *= norm1.z;
                g111 *= norm1.w;

                float n000 = dot(g000, Pf0);
                float n100 = dot(g100, vec3(Pf1.x, Pf0.yz));
                float n010 = dot(g010, vec3(Pf0.x, Pf1.y, Pf0.z));
                float n110 = dot(g110, vec3(Pf1.xy, Pf0.z));
                float n001 = dot(g001, vec3(Pf0.xy, Pf1.z));
                float n101 = dot(g101, vec3(Pf1.x, Pf0.y, Pf1.z));
                float n011 = dot(g011, vec3(Pf0.x, Pf1.yz));
                float n111 = dot(g111, Pf1);

                vec3 fade_xyz = fade(Pf0);
                vec4 n_z = mix(vec4(n000, n100, n010, n110), vec4(n001, n101, n011, n111), fade_xyz.z);
                vec2 n_yz = mix(n_z.xy, n_z.zw, fade_xyz.y);
                float n_xyz = mix(n_yz.x, n_yz.y, fade_xyz.x); 
                return 2.2 * n_xyz;
            }

            // FBM function for more complex noise
            float fbm(vec3 p, vec3 rep, int octaves, float lacunarity, float gain) {
                float total = 0.0;
                float amplitude = 0.5;
                float frequency = 1.0;
                for (int i = 0; i < octaves; i++) {
                    total += pnoise(p * frequency, rep * frequency) * amplitude;
                    frequency *= lacunarity;
                    amplitude *= gain;
                }
                return total;
            }
        `;
        
        const auroraVertexShader = `
            #define PI 3.141592653589793
            uniform float uTime;
            uniform float uHeightScale;
            uniform float uComplexity;    // Horizontal complexity
            uniform float uVerticality;   // Vertical streakiness control
            uniform float uDanceIntensity;

            varying vec2 vUv;
            varying float vNoiseVal1;
            varying float vNoiseVal2;
            varying float vRayStrength;

            ${glslNoise}

            void main() {
                vUv = uv;
                vec3 pos = position;
                
                // Time for different animation speeds
                float time1 = uTime * 0.3;
                float time2 = uTime * 0.5;
                float time3 = uTime * 0.2;

                // Base curtain waviness (horizontal)
                float baseWave = pnoise(vec3(uv.x * uComplexity * 0.3, time1, 0.0), vec3(10.0));
                pos.x += baseWave * 50.0 * uHeightScale * uDanceIntensity;
                pos.z += sin(uv.x * PI * 0.5 + time2 * 0.5 + baseWave * 2.0) * 30.0 * uHeightScale;

                // Vertical rays/curtains deformation
                // More pronounced effect at the 'top' of the plane (which appears as the lower edge of aurora)
                float rayPatternNoise = fbm(vec3(uv.x * uVerticality, uv.y * 2.0, time2), vec3(20.0, 10.0, 10.0), 3, 2.0, 0.5);
                vRayStrength = pow(smoothstep(0.0, 1.0, uv.y), 1.5); // Stronger effect towards uv.y = 1 (top of plane)
                
                pos.y += rayPatternNoise * 40.0 * vRayStrength * uHeightScale;
                pos.z -= rayPatternNoise * 20.0 * vRayStrength * uHeightScale * (sin(uv.x * PI + time1) * 0.5 + 0.5); // Add some sway to rays

                // General "dancing" or boiling motion
                float danceNoiseX = fbm(vec3(uv.x * uComplexity * 0.7, uv.y * 1.5, time3 + 5.0), vec3(15.0), 2, 2.2, 0.45);
                float danceNoiseY = fbm(vec3(uv.x * uComplexity * 0.7, uv.y * 1.5, time3 + 10.0), vec3(15.0), 2, 2.2, 0.45);
                pos.x += danceNoiseX * 25.0 * uDanceIntensity * uHeightScale * vRayStrength;
                pos.y += danceNoiseY * 15.0 * uDanceIntensity * uHeightScale * (1.0-vRayStrength); // Dance more where rays are weaker

                // Pass noise values to fragment shader for coloring
                vNoiseVal1 = fbm(vec3(uv.x * uComplexity, uv.y * 3.0, time1 * 1.2), vec3(10.0), 3, 2.0, 0.5);
                vNoiseVal2 = fbm(vec3(uv.x * uVerticality * 0.5, uv.y * 5.0, time2 * 0.8), vec3(15.0,20.0,10.0), 2, 2.5, 0.4);
                
                gl_Position = projectionMatrix * modelViewMatrix * vec4(pos, 1.0);
            }
        `;

        const auroraFragmentShader = `
            #define PI 3.141592653589793 // CORRECTED: PI defined here too
            uniform float uTime;
            uniform float uIntensity;
            uniform float uVerticality; // For coloring streaks
            uniform float uColorBalance;  // 0 for green, 1 for magenta
            uniform float uBrightnessBoost;

            varying vec2 vUv;
            varying float vNoiseVal1; // General noise
            varying float vNoiseVal2; // Finer noise for details/layers
            varying float vRayStrength; // From vertex shader, how strong the ray deformation is

            ${glslNoise}

            void main() {
                float t1 = uTime * 0.1; // Slow time for broad color shifts
                float t2 = uTime * 0.4; // Faster time for streaks

                // Define core colors based on reference
                vec3 colorGreen = vec3(0.1, 0.9, 0.3);
                vec3 colorMagenta = vec3(0.8, 0.15, 0.7);
                vec3 colorPinkHighlight = vec3(1.0, 0.4, 0.8);
                vec3 colorBlueTint = vec3(0.2, 0.3, 0.9);

                // Base color mix (Green/Magenta)
                vec3 baseColor = mix(colorGreen, colorMagenta, uColorBalance);
                
                // Modulate base color with large scale noise (vNoiseVal1)
                // This creates broader patches of color variation
                float colorRegionMix = smoothstep(-0.3, 0.3, vNoiseVal1 + sin(vUv.x * 5.0 + t1) * 0.2);
                baseColor = mix(baseColor, mix(colorGreen, colorPinkHighlight, uColorBalance + 0.2), colorRegionMix);

                // Add blue tint at higher altitudes (top of aurora curtain, which is lower vUv.y on plane)
                float blueMixFactor = smoothstep(0.0, 0.4, 1.0 - vUv.y); // Stronger blue at the "top" (low vUv.y)
                baseColor = mix(baseColor, colorBlueTint, blueMixFactor * 0.3 * (1.0-uColorBalance)); // More blue if more green overall

                // Vertical streaks / layers using vNoiseVal2 and vRayStrength
                // Simulate faster moving, brighter vertical elements
                float streakPattern = fbm(vec3(vUv.x * uVerticality * 1.5, vUv.y * 8.0 + t2, t2 * 0.5), vec3(30.0, 15.0, 10.0), 2, 2.0, 0.6);
                streakPattern = pow(smoothstep(0.2, 0.7, streakPattern), 3.0); // Sharpen streaks
                
                // Make streaks brighter and slightly whiter/pinker
                vec3 streakColor = mix(vec3(0.9,1.0,0.9), colorPinkHighlight, smoothstep(0.0,1.0,vUv.y));
                vec3 finalColor = baseColor + streakColor * streakPattern * (0.5 + vRayStrength) * (1.0 + uBrightnessBoost);

                // Alpha calculation for curtain shape and fading
                // Base alpha on vUv.y (stronger at "bottom" of aurora / top of plane)
                float alphaShape = pow(vUv.y, 0.8) * (0.6 + vNoiseVal1 * 0.4);
                
                // Sharpen curtain effect using a sine wave modulated by noise
                float curtainEdge = sin(vUv.x * PI * 2.0 + vNoiseVal2 * 2.0 + t1 * 2.0) * 0.5 + 0.5;
                curtainEdge = pow(curtainEdge, 2.5);
                alphaShape *= curtainEdge;

                // Overall intensity and clamp
                float alpha = alphaShape * uIntensity * (0.5 + vRayStrength * 0.8); // Rays are more opaque
                alpha = clamp(alpha, 0.0, 1.0);

                gl_FragColor = vec4(finalColor, alpha);
            }
        `;

        const nebulaFragmentShader = `
            uniform float uTime;
            varying vec2 vUv;
            ${glslNoise}

            float fbm_nebula(vec3 p, vec3 rep) { // Renamed to avoid conflict if fbm is used elsewhere
                float total = 0.0;
                float amplitude = 0.6; 
                float frequency = 2.0;
                float lacunarity = 2.1; 
                float gain = 0.45;      
                int octaves = 5;

                for (int i = 0; i < octaves; i++) {
                    total += pnoise(p * frequency, rep * frequency) * amplitude;
                    frequency *= lacunarity;
                    amplitude *= gain;
                }
                return total;
            }

            void main() {
                vec2 uv = vUv - 0.5; 
                float dist = length(uv);

                vec3 p = vec3(uv * 3.0, uTime * 0.01); 
                float noiseVal = fbm_nebula(p, vec3(10.0));

                vec3 color1 = vec3(0.05, 0.02, 0.15); 
                vec3 color2 = vec3(0.2, 0.05, 0.3);  
                vec3 color3 = vec3(0.4, 0.1, 0.2);   

                vec3 baseColor = mix(color1, color2, smoothstep(-0.2, 0.2, noiseVal));
                baseColor = mix(baseColor, color3, smoothstep(0.3, 0.5, noiseVal * sin(uTime * 0.005 + uv.x * 5.0))); 

                float specks = pow(pnoise(p * 20.0, vec3(50.0)), 10.0); 
                specks = smoothstep(0.4, 0.6, specks) * 0.3; 

                float intensity = pow(1.0 - dist, 2.0); 
                intensity *= (0.2 + smoothstep(-0.3, 0.3, noiseVal) * 0.8); 

                gl_FragColor = vec4(baseColor + specks, intensity * 0.6); 
            }
        `;


        // --- Control Values ---
        let controlValues = {
            auroraSpeed: 0.07,
            auroraHeight: 1.2,
            auroraIntensity: 1.5,
            auroraComplexity: 5.0,
            auroraVerticality: 8.0,
            danceIntensity: 0.8,
            colorBalance: 0.3,
            brightnessBoost: 0.5,
            starCount: 4000,
            cometFrequency: 0.006,
        };

        // Uniforms references
        let auroraUniforms;
        let auroraReflectionUniforms;


        function init() {
            scene = new THREE.Scene();
            scene.fog = new THREE.FogExp2(0x000000, 0.0005); 

            camera = new THREE.PerspectiveCamera(70, window.innerWidth / window.innerHeight, 0.1, 6000);
            camera.position.set(0, 80, 300); 
            camera.lookAt(0, 120, 0); 

            renderer = new THREE.WebGLRenderer({ antialias: true });
            renderer.setSize(window.innerWidth, window.innerHeight);
            renderer.setPixelRatio(window.devicePixelRatio);
            document.getElementById('scene-container').appendChild(renderer.domElement);

            const ambientLight = new THREE.AmbientLight(0x303040, 0.1);
            scene.add(ambientLight);

            createStars(controlValues.starCount);
            createNebula();
            createLakeAndHorizon(); 
            createAurora();         
            
            setupControls();
            window.addEventListener('resize', onWindowResize, false);
            animate();
        }

        function createStars(count) {
            if (stars) {
                stars.geometry.dispose();
                stars.material.dispose();
                scene.remove(stars);
            }
            const starVertices = [];
            for (let i = 0; i < count; i++) {
                const x = THREE.MathUtils.randFloatSpread(4000); 
                const y = THREE.MathUtils.randFloat(150, 1500); 
                const z = THREE.MathUtils.randFloatSpread(4000);
                starVertices.push(x, y, z);
            }
            const starGeometry = new THREE.BufferGeometry();
            starGeometry.setAttribute('position', new THREE.Float32BufferAttribute(starVertices, 3));
            const starMaterial = new THREE.PointsMaterial({ 
                color: 0xffffff, 
                size: THREE.MathUtils.randFloat(1.5, 3.0), 
                sizeAttenuation: true,
                transparent: true,
                opacity: Math.random() * 0.4 + 0.4 
            });
            stars = new THREE.Points(starGeometry, starMaterial);
            scene.add(stars);
        }

        function createNebula() {
            if (nebula) { 
                nebula.geometry.dispose();
                nebula.material.dispose();
                scene.remove(nebula);
            }
            const nebulaGeometry = new THREE.SphereGeometry(2500, 64, 32); 
            const nebulaMaterial = new THREE.ShaderMaterial({
                uniforms: {
                    uTime: { value: 0.0 }
                },
                vertexShader: `
                    varying vec2 vUv;
                    void main() {
                        vUv = uv;
                        gl_Position = projectionMatrix * modelViewMatrix * vec4(position, 1.0);
                    }
                `,
                fragmentShader: nebulaFragmentShader,
                side: THREE.BackSide, 
                transparent: true,
                blending: THREE.AdditiveBlending,
                depthWrite: false 
            });

            nebula = new THREE.Mesh(nebulaGeometry, nebulaMaterial);
            nebula.position.set(0, 100, -500); 
            scene.add(nebula);
        }


        function createAurora() {
            const auroraGeometry = new THREE.PlaneGeometry(1600, 600, 256, 128); 
            
            const materialUniforms = {
                uTime: { value: 0.0 },
                uHeightScale: { value: controlValues.auroraHeight },
                uIntensity: { value: controlValues.auroraIntensity },
                uComplexity: { value: controlValues.auroraComplexity },
                uVerticality: { value: controlValues.auroraVerticality },
                uDanceIntensity: { value: controlValues.danceIntensity },
                uColorBalance: { value: controlValues.colorBalance },
                uBrightnessBoost: { value: controlValues.brightnessBoost }
            };
            auroraUniforms = materialUniforms;

            const auroraMaterial = new THREE.ShaderMaterial({
                uniforms: auroraUniforms,
                vertexShader: auroraVertexShader,
                fragmentShader: auroraFragmentShader,
                transparent: true,
                blending: THREE.AdditiveBlending, 
                depthWrite: false, 
                side: THREE.DoubleSide
            });

            auroraMesh = new THREE.Mesh(auroraGeometry, auroraMaterial);
            auroraMesh.position.set(0, 250, -300); 
            auroraMesh.rotation.x = Math.PI * 0.30; 
            scene.add(auroraMesh);

            auroraReflectionUniforms = THREE.UniformsUtils.clone(auroraUniforms);
            auroraReflectionUniforms.uIntensity.value = controlValues.auroraIntensity * 0.25; 
            
            const reflectionMaterial = auroraMaterial.clone();
            reflectionMaterial.uniforms = auroraReflectionUniforms;

            auroraReflectionMesh = new THREE.Mesh(auroraGeometry, reflectionMaterial);
            auroraReflectionMesh.scale.y = -1; 
            auroraReflectionMesh.position.copy(auroraMesh.position);
            auroraReflectionMesh.position.y = -auroraMesh.position.y + lakePlane.position.y * 2 + 20; 
            auroraReflectionMesh.rotation.copy(auroraMesh.rotation);
            scene.add(auroraReflectionMesh);
        }
        
        function createLakeAndHorizon() {
            const lakeGeometry = new THREE.PlaneGeometry(8000, 4000); 
            const lakeMaterial = new THREE.MeshStandardMaterial({
                color: 0x030308, 
                metalness: 0.95,   
                roughness: 0.15,  
                transparent: true,
                opacity: 0.9
            });
            lakePlane = new THREE.Mesh(lakeGeometry, lakeMaterial); 
            lakePlane.rotation.x = -Math.PI / 2;
            lakePlane.position.y = 0; 
            scene.add(lakePlane);
        }

        function createComet() {
            const cometGeometry = new THREE.SphereGeometry(THREE.MathUtils.randFloat(1.5, 4), 16, 8);
            const cometMaterial = new THREE.MeshBasicMaterial({ color: 0xffffdd });
            const comet = new THREE.Mesh(cometGeometry, cometMaterial);

            const trailPoints = [];
            for (let i = 0; i < 40; i++) trailPoints.push(new THREE.Vector3()); 
            const trailGeometry = new THREE.BufferGeometry().setFromPoints(trailPoints);
            const trailMaterial = new THREE.LineBasicMaterial({ 
                color: 0xffffdd, 
                transparent: true, 
                opacity: 0.6,
                linewidth: 1.5 
            });
            const trail = new THREE.Line(trailGeometry, trailMaterial);
            comet.trail = trail;
            comet.trailPoints = trailPoints;
            comet.trailIndex = 0;

            resetComet(comet);
            scene.add(comet);
            scene.add(trail);
            comets.push(comet);
        }

        function resetComet(comet) {
            comet.position.set(
                THREE.MathUtils.randFloatSpread(2000), 
                THREE.MathUtils.randFloat(300, 800),   
                THREE.MathUtils.randFloat(-1500, -2000) 
            );
            comet.velocity = new THREE.Vector3(
                THREE.MathUtils.randFloat(-3, 3),    
                THREE.MathUtils.randFloat(-1, -2), 
                THREE.MathUtils.randFloat(4, 8)       
            );
            comet.trailPoints.forEach(p => p.copy(comet.position));
            if (comet.trail.geometry.attributes.position) {
                 comet.trail.geometry.attributes.position.needsUpdate = true;
            }
            comet.trailIndex = 0;
        }
         function updateComets(deltaTime) {
            if (Math.random() < controlValues.cometFrequency && comets.length < MAX_COMETS) {
                createComet();
            }

            for (let i = comets.length - 1; i >= 0; i--) { 
                const comet = comets[i];
                if (!comet || !comet.trail) continue;

                comet.position.addScaledVector(comet.velocity, deltaTime * 40); 

                comet.trailIndex = (comet.trailIndex + 1) % comet.trailPoints.length;
                comet.trailPoints[comet.trailIndex].copy(comet.position);
                
                const orderedPoints = [];
                for (let j = 0; j < comet.trailPoints.length; j++) {
                    orderedPoints.push(comet.trailPoints[(comet.trailIndex + 1 + j) % comet.trailPoints.length]);
                }
                comet.trail.geometry.setFromPoints(orderedPoints);
                if(comet.trail.geometry.attributes.position) comet.trail.geometry.attributes.position.needsUpdate = true;


                if (comet.position.z > camera.position.z + 200 || comet.position.y < lakePlane.position.y - 100) {
                    comet.geometry.dispose();
                    comet.material.dispose();
                    scene.remove(comet);
                    
                    comet.trail.geometry.dispose();
                    comet.trail.material.dispose();
                    scene.remove(comet.trail);
                    
                    comets.splice(i, 1);
                }
            }
        }


        function setupControls() {
            function setupSlider(id, uniformName, isReflectionSensitive = false, reflectionMultiplier = 0.3) {
                const slider = document.getElementById(id);
                slider.addEventListener('input', e => {
                    const value = parseFloat(e.target.value);
                    const controlValueKey = id.replace(/([A-Z])/g, (match, p1) => p1.toLowerCase());
                    if (controlValues.hasOwnProperty(controlValueKey)) {
                         controlValues[controlValueKey] = value;
                    }
                    
                    if (auroraUniforms && auroraUniforms[uniformName]) {
                        auroraUniforms[uniformName].value = value;
                    }
                    if (isReflectionSensitive && auroraReflectionUniforms && auroraReflectionUniforms[uniformName]) {
                        auroraReflectionUniforms[uniformName].value = value * reflectionMultiplier;
                    } else if (auroraReflectionUniforms && auroraReflectionUniforms[uniformName]) {
                         auroraReflectionUniforms[uniformName].value = value;
                    }
                });
                const controlValueKey = id.replace(/([A-Z])/g, (match, p1) => p1.toLowerCase());
                if (controlValues.hasOwnProperty(controlValueKey)) {
                    slider.value = controlValues[controlValueKey];
                }
            }

            setupSlider('auroraSpeed', 'uTime', false); 
            setupSlider('auroraHeight', 'uHeightScale', true);
            setupSlider('auroraIntensity', 'uIntensity', true, 0.25);
            setupSlider('auroraComplexity', 'uComplexity', true);
            setupSlider('auroraVerticality', 'uVerticality', true);
            setupSlider('danceIntensity', 'uDanceIntensity', true);
            setupSlider('colorBalance', 'uColorBalance', true);
            setupSlider('brightnessBoost', 'uBrightnessBoost', true);
            
            document.getElementById('starCount').addEventListener('input', e => {
                controlValues.starCount = parseInt(e.target.value);
                createStars(controlValues.starCount); 
            });
            document.getElementById('starCount').value = controlValues.starCount;

            document.getElementById('cometFrequency').addEventListener('input', e => {
                controlValues.cometFrequency = parseFloat(e.target.value);
            });
            document.getElementById('cometFrequency').value = controlValues.cometFrequency;
        }

        function onWindowResize() {
            camera.aspect = window.innerWidth / window.innerHeight;
            camera.updateProjectionMatrix();
            renderer.setSize(window.innerWidth, window.innerHeight);
        }

        const clock = new THREE.Clock();

        function animate() {
            requestAnimationFrame(animate);
            
            const deltaTime = clock.getDelta();
            const elapsedTime = clock.getElapsedTime();

            const scaledTime = elapsedTime * controlValues.auroraSpeed; 
            if (auroraUniforms) auroraUniforms.uTime.value = scaledTime;
            if (auroraReflectionUniforms) auroraReflectionUniforms.uTime.value = scaledTime;
            
            if (nebula && nebula.material.uniforms.uTime) {
                 nebula.material.uniforms.uTime.value = elapsedTime * 0.1; 
            }

            updateComets(deltaTime);
            renderer.render(scene, camera);
        }

        init();
    </script>
</body>
</html>