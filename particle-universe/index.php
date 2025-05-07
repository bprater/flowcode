<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>3D Particle Shape Spinner</title>
    <style>
        body { margin: 0; font-family: Arial, sans-serif; background-color: #222; color: #eee; display: flex; flex-direction: column; align-items: center; }
        #container { width: 100%; max-width: 800px; text-align: center; }
        canvas { display: block; margin: 10px auto; background-color: #000; touch-action: none; /* For OrbitControls touch compatibility */ }
        .controls {
            background-color: #333;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 10px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 10px;
            width: 100%;
            box-sizing: border-box;
        }
        .control-group {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }
        .control-group label { margin-bottom: 5px; font-size: 0.9em; }
        .control-group input[type="range"],
        .control-group select,
        .control-group input[type="color"] {
            width: 100%;
            box-sizing: border-box;
        }
        .control-group input[type="color"] {
            height: 30px;
            padding: 2px;
        }
        /* Hide single particle color picker as we're using rainbow */
        #particleColorGroup { display: none; }
    </style>
    
    <!-- Import map for Three.js modules -->
    <script type="importmap">
        {
            "imports": {
                "three": "https://cdn.jsdelivr.net/npm/three@0.128.0/build/three.module.js",
                "three/addons/": "https://cdn.jsdelivr.net/npm/three@0.128.0/examples/jsm/"
            }
        }
    </script>
</head>
<body>
    <div id="container">
        <h1>3D Particle Shape Spinner</h1>
        <div class="controls">
            <div class="control-group">
                <label for="shapeType">Shape:</label>
                <select id="shapeType">
                    <option value="cube">Cube</option>
                    <option value="sphere">Sphere</option>
                    <option value="torus">Torus</option>
                    <option value="icosahedron">Icosahedron</option>
                    <option value="cone">Cone</option>
                    <option value="cylinder">Cylinder</option>
                    <option value="torusknot">TorusKnot</option>
                    <option value="dodecahedron">Dodecahedron</option>
                    <option value="octahedron">Octahedron</option>
                    <option value="tetrahedron">Tetrahedron</option>
                </select>
            </div>
            <div class="control-group">
                <label for="rotationSpeed">Auto-Rotation Speed:</label>
                <input type="range" id="rotationSpeed" min="0" max="0.05" step="0.001" value="0.005">
            </div>
            <div class="control-group">
                <label for="particleDensity">Particle Density:</label>
                <input type="range" id="particleDensity" min="500" max="15000" step="100" value="3000">
            </div>
            <div class="control-group">
                <label for="particleSize">Particle Pixel Size:</label>
                <input type="range" id="particleSize" min="1" max="5" step="0.5" value="1.5">
            </div>
            <div class="control-group" id="particleColorGroup"> <!-- Initially hidden -->
                <label for="particleColor">Particle Color (Unused with Rainbow):</label>
                <input type="color" id="particleColor" value="#00ff00">
            </div>
        </div>
        <div id="canvas-container"></div>
    </div>

    <script type="module">
        import * as THREE from 'three';
        import { OrbitControls } from 'three/addons/controls/OrbitControls.js';

        let scene, camera, renderer, orbitControls;
        let currentShapeMesh, particleSystem;
        let uiControls = {}; // To store DOM elements

        const canvasContainer = document.getElementById('canvas-container');
        const canvasWidth = Math.min(window.innerWidth * 0.9, 780);
        const canvasHeight = Math.min(window.innerHeight * 0.6, 500);

        function init() {
            // Scene
            scene = new THREE.Scene();

            // Camera
            camera = new THREE.PerspectiveCamera(75, canvasWidth / canvasHeight, 0.1, 1000);
            camera.position.z = 5;

            // Renderer
            renderer = new THREE.WebGLRenderer({ antialias: true });
            renderer.setSize(canvasWidth, canvasHeight);
            canvasContainer.appendChild(renderer.domElement);

            // OrbitControls
            orbitControls = new OrbitControls(camera, renderer.domElement);
            orbitControls.enableDamping = true;
            orbitControls.dampingFactor = 0.05;
            orbitControls.screenSpacePanning = false;
            orbitControls.minDistance = 2;
            orbitControls.maxDistance = 20;
            orbitControls.target.set(0, 0, 0);
            orbitControls.update();


            // Lights
            const ambientLight = new THREE.AmbientLight(0xcccccc, 1); // soft white light
            scene.add(ambientLight);
            const directionalLight = new THREE.DirectionalLight(0xffffff, 1);
            directionalLight.position.set(5, 5, 5).normalize();
            scene.add(directionalLight);

            // UI Controls
            uiControls.shapeType = document.getElementById('shapeType');
            uiControls.rotationSpeed = document.getElementById('rotationSpeed');
            uiControls.particleDensity = document.getElementById('particleDensity');
            uiControls.particleSize = document.getElementById('particleSize');
            // uiControls.particleColor = document.getElementById('particleColor'); // Not used for rainbow

            // Event Listeners
            uiControls.shapeType.addEventListener('change', resetScene);
            uiControls.rotationSpeed.addEventListener('input', () => { /* Handled in animate */ });
            uiControls.particleDensity.addEventListener('input', regenerateParticles);
            uiControls.particleSize.addEventListener('input', updateParticleMaterial);
            // uiControls.particleColor.addEventListener('input', updateParticleMaterial); // Not used for rainbow

            // Initial setup
            resetScene();
            animate();
        }

        function resetScene() {
            if (currentShapeMesh) {
                scene.remove(currentShapeMesh);
                currentShapeMesh.geometry.dispose();
                if (currentShapeMesh.material) currentShapeMesh.material.dispose(); // Material might not exist if it's just a container
                if (particleSystem) {
                    currentShapeMesh.remove(particleSystem); // Particles are children
                }
            }
            if (particleSystem) {
                particleSystem.geometry.dispose();
                particleSystem.material.dispose();
            }

            createShape();
            regenerateParticles(); 
        }

        function createShape() {
            const shapeType = uiControls.shapeType.value;
            let geometry;
            const shapeSize = 1.8; // Base size for shapes, adjust as needed

            switch (shapeType) {
                case 'cube':
                    geometry = new THREE.BoxGeometry(shapeSize, shapeSize, shapeSize);
                    break;
                case 'sphere':
                    geometry = new THREE.SphereGeometry(shapeSize / 1.5, 32, 32);
                    break;
                case 'torus':
                    geometry = new THREE.TorusGeometry(shapeSize / 2, shapeSize / 5, 32, 100);
                    break;
                case 'icosahedron':
                    geometry = new THREE.IcosahedronGeometry(shapeSize / 1.5, 1); // Detail 1 for more faces
                    break;
                case 'cone':
                    geometry = new THREE.ConeGeometry(shapeSize / 2, shapeSize, 32); // radius, height, segments
                    break;
                case 'cylinder':
                    geometry = new THREE.CylinderGeometry(shapeSize / 2, shapeSize / 2, shapeSize, 32); // radiusTop, radiusBottom, height, segments
                    break;
                case 'torusknot':
                    geometry = new THREE.TorusKnotGeometry(shapeSize / 2.5, shapeSize / 8, 100, 16, 2, 3); // radius, tube, tubularSegments, radialSegments, p, q
                    break;
                case 'dodecahedron':
                    geometry = new THREE.DodecahedronGeometry(shapeSize / 1.5, 0); // radius, detail
                    break;
                case 'octahedron':
                    geometry = new THREE.OctahedronGeometry(shapeSize / 1.5, 0); // radius, detail
                    break;
                case 'tetrahedron':
                    geometry = new THREE.TetrahedronGeometry(shapeSize / 1.5, 0); // radius, detail
                    break;
                default:
                    geometry = new THREE.BoxGeometry(shapeSize, shapeSize, shapeSize);
            }

            // Make the main shape very subtle or invisible, as particles define it
            const material = new THREE.MeshStandardMaterial({
                color: 0xaaaaaa,
                wireframe: true,
                opacity: 0.05, // Very transparent
                transparent: true,
                depthWrite: false // Particles show through better
            });
            currentShapeMesh = new THREE.Mesh(geometry, material);
            scene.add(currentShapeMesh);
        }

        function regenerateParticles() {
            if (particleSystem) {
                currentShapeMesh.remove(particleSystem);
                particleSystem.geometry.dispose();
                particleSystem.material.dispose();
            }
            if (!currentShapeMesh) return;
            if (!currentShapeMesh.geometry) {
                console.error("CRITICAL: currentShapeMesh.geometry is null or undefined in regenerateParticles. Cannot generate particles.");
                return;
            }

            const density = parseInt(uiControls.particleDensity.value);
            const particlePositions = new Float32Array(density * 3);
            const particleColors = new Float32Array(density * 3); // For R, G, B

            const shapeGeometry = currentShapeMesh.geometry;
            const shapeType = uiControls.shapeType.value;
            
            let minX = Infinity, maxX = -Infinity;
            let minY = Infinity, maxY = -Infinity;
            let minZ = Infinity, maxZ = -Infinity;

            // First pass: Generate positions and find bounds for color normalization
            for (let i = 0; i < density; i++) {
                let x, y, z;
                // Using a slightly smaller radius/size for particles to prevent them from being exactly on the mathematical surface
                // which can sometimes cause z-fighting with the wireframe if it were more opaque.
                const scaleFactor = 0.99; 

                if (!shapeGeometry || !shapeGeometry.parameters) {
                    console.error(`CRITICAL: shapeGeometry or shapeGeometry.parameters is missing for shapeType: "${shapeType}", particle ${i}. Defaulting position. ShapeGeom:`, shapeGeometry);
                    x = 0; y = 0; z = 0;
                } else {
                    if (shapeType === 'cube') {
                        if (shapeGeometry.parameters && typeof shapeGeometry.parameters.width === 'number') {
                            const s = shapeGeometry.parameters.width / 2 * scaleFactor;
                            const face = Math.floor(Math.random() * 6);
                            const u = (Math.random() - 0.5) * 2 * s;
                            const v = (Math.random() - 0.5) * 2 * s;
                            switch (face) {
                                case 0: x = s; y = u; z = v; break;  // +X
                                case 1: x = -s; y = u; z = v; break; // -X
                                case 2: y = s; x = u; z = v; break;  // +Y
                                case 3: y = -s; x = u; z = v; break; // -Y
                                case 4: z = s; x = u; y = v; break;  // +Z
                                case 5: z = -s; x = u; y = v; break; // -Z
                            }
                        } else {
                            console.error(`Error: Cube geometry parameters missing or invalid for particle ${i}. Expected 'width'. ShapeGeom:`, shapeGeometry);
                            x = 0; y = 0; z = 0;
                        }
                    } else if (shapeType === 'sphere' || shapeType === 'icosahedron' || shapeType === 'dodecahedron' || shapeType === 'octahedron' || shapeType === 'tetrahedron') {
                        if (shapeGeometry.parameters && typeof shapeGeometry.parameters.radius === 'number') {
                            const radius = shapeGeometry.parameters.radius * scaleFactor;
                            const phi = Math.random() * 2 * Math.PI;
                            const cosTheta = Math.random() * 2 - 1; 
                            const theta = Math.acos(cosTheta);
                            x = radius * Math.sin(theta) * Math.cos(phi);
                            y = radius * Math.sin(theta) * Math.sin(phi);
                            z = radius * Math.cos(theta);
                        } else {
                            console.error(`Error: ${shapeType} geometry parameters missing or invalid for particle ${i}. Expected 'radius'. ShapeGeom:`, shapeGeometry);
                            x = 0; y = 0; z = 0;
                        }
                    } else if (shapeType === 'torus' || shapeType === 'torusknot') { // TorusKnot will use similar particle distribution
                        if (shapeGeometry.parameters && typeof shapeGeometry.parameters.radius === 'number' && typeof shapeGeometry.parameters.tube === 'number') {
                            const R = shapeGeometry.parameters.radius * scaleFactor; // Main radius
                            const r_tube = shapeGeometry.parameters.tube * scaleFactor; // Tube radius
                            const u_angle = Math.random() * 2 * Math.PI; // Angle around the main ring
                            const v_angle = Math.random() * 2 * Math.PI; // Angle around the tube cross-section
                            
                            x = (R + r_tube * Math.cos(v_angle)) * Math.cos(u_angle);
                            y = (R + r_tube * Math.cos(v_angle)) * Math.sin(u_angle);
                            z = r_tube * Math.sin(v_angle);
                        } else {
                            console.error(`Error: ${shapeType} geometry parameters missing or invalid for particle ${i}. Expected 'radius' and 'tube'. ShapeGeom:`, shapeGeometry);
                            x = 0; y = 0; z = 0;
                        }
                    } else if (shapeType === 'cylinder') {
                        if (shapeGeometry.parameters && typeof shapeGeometry.parameters.radiusTop === 'number' && typeof shapeGeometry.parameters.height === 'number') {
                            const radius = shapeGeometry.parameters.radiusTop * scaleFactor; // Assuming radiusTop = radiusBottom
                            const height = shapeGeometry.parameters.height * scaleFactor;
                            const h_half = height / 2;
                            const areaSide = 2 * Math.PI * radius * height;
                            const areaCap = Math.PI * radius * radius;
                            const totalArea = areaSide + 2 * areaCap;
                            const rand = Math.random() * totalArea;

                            if (rand < areaSide) { // On the side
                                const angle = Math.random() * 2 * Math.PI;
                                x = radius * Math.cos(angle);
                                y = (Math.random() - 0.5) * height;
                                z = radius * Math.sin(angle);
                            } else if (rand < areaSide + areaCap) { // On the top cap
                                const r_cap = Math.sqrt(Math.random()) * radius;
                                const angle = Math.random() * 2 * Math.PI;
                                x = r_cap * Math.cos(angle);
                                y = h_half;
                                z = r_cap * Math.sin(angle);
                            } else { // On the bottom cap
                                const r_cap = Math.sqrt(Math.random()) * radius;
                                const angle = Math.random() * 2 * Math.PI;
                                x = r_cap * Math.cos(angle);
                                y = -h_half;
                                z = r_cap * Math.sin(angle);
                            }
                        } else {
                            console.error(`Error: Cylinder geometry parameters missing or invalid for particle ${i}. Expected 'radiusTop' and 'height'. ShapeGeom:`, shapeGeometry);
                            x = 0; y = 0; z = 0;
                        }
                    } else if (shapeType === 'cone') {
                        if (shapeGeometry.parameters && typeof shapeGeometry.parameters.radius === 'number' && typeof shapeGeometry.parameters.height === 'number') {
                            const radius = shapeGeometry.parameters.radius * scaleFactor;
                            const height = shapeGeometry.parameters.height * scaleFactor;
                            const h_half = height / 2; 
                            const slantHeight = Math.sqrt(radius * radius + height * height);
                            const areaBase = Math.PI * radius * radius;
                            const areaSlanted = Math.PI * radius * slantHeight;
                            const totalArea = areaBase + areaSlanted;
                            const rand = Math.random() * totalArea;

                            if (rand < areaBase) { // On the base
                                const r_base = Math.sqrt(Math.random()) * radius;
                                const angle = Math.random() * 2 * Math.PI;
                                x = r_base * Math.cos(angle);
                                y = -h_half; 
                                z = r_base * Math.sin(angle);
                            } else { // On the slanted surface
                                const rand_y_normalized = Math.random(); 
                                const current_radius = radius * (1 - rand_y_normalized); 
                                const angle = Math.random() * 2 * Math.PI;
                                x = current_radius * Math.cos(angle);
                                y = rand_y_normalized * height - h_half; 
                                z = current_radius * Math.sin(angle);
                            }
                        } else {
                            console.error(`Error: Cone geometry parameters missing or invalid for particle ${i}. Expected 'radius' and 'height'. ShapeGeom:`, shapeGeometry);
                            x = 0; y = 0; z = 0;
                        }
                    } else { // Fallback for unknown shapeType
                        console.error(`Error: Unknown shapeType "${shapeType}" encountered for particle ${i}. Defaulting particle position.`);
                        x = 0; y = 0; z = 0;
                    }
                }

                // Universal NaN check before assignment
                if (isNaN(x) || isNaN(y) || isNaN(z)) {
                    console.error(`CRITICAL: NaN detected for particle ${i}. Shape: "${shapeType}", Params:`, 
                                  shapeGeometry && shapeGeometry.parameters ? shapeGeometry.parameters : 'Params N/A', 
                                  `Original x,y,z: ${x},${y},${z}. Defaulting position.`);
                    x = 0; y = 0; z = 0;
                }

                particlePositions[i * 3] = x;
                particlePositions[i * 3 + 1] = y;
                particlePositions[i * 3 + 2] = z;

                if (x < minX) minX = x; if (x > maxX) maxX = x;
                if (y < minY) minY = y; if (y > maxY) maxY = y;
                if (z < minZ) minZ = z; if (z > maxZ) maxZ = z;
            }

            const rangeX = maxX - minX;
            // const rangeY = maxY - minY; // Could use for other color dimensions
            // const rangeZ = maxZ - minZ; // Could use for other color dimensions

            // Second pass: Assign colors based on normalized position
            const tempColor = new THREE.Color();
            for (let i = 0; i < density; i++) {
                const px = particlePositions[i * 3];
                // Normalize x position to 0-1 range for hue
                const normalizedX = rangeX === 0 ? 0.5 : (px - minX) / rangeX;
                
                tempColor.setHSL(normalizedX, 1.0, 0.5); // Hue, Saturation, Lightness

                particleColors[i * 3] = tempColor.r;
                particleColors[i * 3 + 1] = tempColor.g;
                particleColors[i * 3 + 2] = tempColor.b;
            }


            const particlesGeometry = new THREE.BufferGeometry();
            particlesGeometry.setAttribute('position', new THREE.BufferAttribute(particlePositions, 3));
            particlesGeometry.setAttribute('color', new THREE.BufferAttribute(particleColors, 3));


            const particleMaterial = new THREE.PointsMaterial({
                size: parseFloat(uiControls.particleSize.value),
                vertexColors: true, // Use per-vertex colors
                sizeAttenuation: false, // Size in screen pixels
                blending: THREE.AdditiveBlending,
                transparent: true,
                depthWrite: false // Important for additive blending to work correctly with other objects
            });

            particleSystem = new THREE.Points(particlesGeometry, particleMaterial);
            currentShapeMesh.add(particleSystem); 
        }

        function updateParticleMaterial() {
            if (particleSystem) {
                particleSystem.material.size = parseFloat(uiControls.particleSize.value);
                // Color is now per-vertex, so global color picker is not used.
                // If you wanted to re-enable single color:
                // particleSystem.material.color.set(uiControls.particleColor.value);
                // particleSystem.material.vertexColors = false; // Disable vertex colors
                particleSystem.material.needsUpdate = true; // Important if changing material properties like vertexColors
            }
        }

        function animate() {
            requestAnimationFrame(animate);

            const speed = parseFloat(uiControls.rotationSpeed.value);
            if (currentShapeMesh && speed > 0) { // Only apply auto-rotation if speed is set
                currentShapeMesh.rotation.y += speed;
                // currentShapeMesh.rotation.x += speed * 0.5; // Optional X rotation
            }
            
            orbitControls.update(); // Required if enableDamping or autoRotate is set
            renderer.render(scene, camera);
        }

        function onWindowResize() {
            const newWidth = Math.min(window.innerWidth * 0.9, 780);
            const newHeight = Math.min(window.innerHeight * 0.6, 500);

            camera.aspect = newWidth / newHeight;
            camera.updateProjectionMatrix();
            renderer.setSize(newWidth, newHeight);
        }

        window.addEventListener('resize', onWindowResize, false);

        // Start
        init();
    </script>
</body>
</html>