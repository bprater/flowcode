<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minimal TextGeometry Test</title>
    <style>
        body { margin: 0; overflow: hidden; background-color: #333; }
        canvas { display: block; }
        #info { position: absolute; top: 10px; left: 10px; color: white; font-family: monospace; }
    </style>
</head>
<body>
    <div id="info">Loading font...</div>
    <script type="importmap">
    {
        "imports": {
            "three": "https://unpkg.com/three@0.160.0/build/three.module.js",
            "three/addons/": "https://unpkg.com/three@0.160.0/examples/jsm/"
        }
    }
    </script>
    <script type="module">
        import * as THREE from 'three';
        import { OrbitControls } from 'three/addons/controls/OrbitControls.js';
        import { FontLoader } from 'three/addons/loaders/FontLoader.js';
        import { TextGeometry } from 'three/addons/geometries/TextGeometry.js';

        let scene, camera, renderer, controls, textMesh;
        const infoDiv = document.getElementById('info');

        function updateInfo(message) {
            console.log(message);
            infoDiv.textContent = message;
        }

        function init() {
            scene = new THREE.Scene();
            scene.background = new THREE.Color(0x222222);

            camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 0.1, 1000);
            camera.position.set(0, 0.5, 2); // Look directly at the text

            renderer = new THREE.WebGLRenderer({ antialias: true });
            renderer.setSize(window.innerWidth, window.innerHeight);
            document.body.appendChild(renderer.domElement);

            controls = new OrbitControls(camera, renderer.domElement);
            controls.target.set(0, 0, 0); // Target origin where text will be

            const ambientLight = new THREE.AmbientLight(0xffffff, 0.7);
            scene.add(ambientLight);
            const directionalLight = new THREE.DirectionalLight(0xffffff, 1.0);
            directionalLight.position.set(1, 2, 3);
            scene.add(directionalLight);

            // Load a known good font directly from Three.js examples
            const fontLoader = new FontLoader();
            const fontPath = 'https://raw.githubusercontent.com/mrdoob/three.js/dev/examples/fonts/helvetiker_regular.typeface.json';
            // Or try gentilis again if helvetiker also fails here:
            // const fontPath = 'https://raw.githubusercontent.com/mrdoob/three.js/dev/examples/fonts/gentilis_regular.typeface.json';

            updateInfo(`Loading font from: ${fontPath}`);

            fontLoader.load(fontPath,
                function (font) { // onSuccess
                    updateInfo("Font loaded. Creating text...");
                    createText(font);
                    animate(); // Start animation loop only after font is loaded
                },
                undefined, // onProgress
                function (error) { // onError
                    updateInfo(`ERROR loading font: ${error.message || error}. Check console.`);
                    console.error("Font loading error:", error);
                }
            );

            window.addEventListener('resize', onWindowResize, false);
        }

        function createText(font) {
            const textContent = "Test";
            const textSize = 0.5;
            
            // --- TEST 1: EXTREMELY THIN (FLAT) TEXT ---
            // const textDepth = 0.0001; 
            // updateInfo(`Creating text "${textContent}" with target depth: ${textDepth}`);

            // --- TEST 2: MODERATE EXTRUSION ---
            const textDepth = 0.1;
            updateInfo(`Creating text "${textContent}" with target depth: ${textDepth}`);
            
            // --- TEST 3: ZERO DEPTH (if TextGeometry supports it, should be flat) ---
            // const textDepth = 0; // Some versions might error or create 2D plane
            // updateInfo(`Creating text "${textContent}" with target depth: ${textDepth}`);


            const geometry = new TextGeometry(textContent, {
                font: font,
                size: textSize,
                depth: textDepth,       // The critical parameter
                curveSegments: 12,
                bevelEnabled: false
            });

            geometry.computeBoundingBox();
            const bb = geometry.boundingBox;
            const sizeVec = new THREE.Vector3();
            bb.getSize(sizeVec);
            updateInfo(`Target depth: ${textDepth.toFixed(5)}. Actual BBox Z-size: ${sizeVec.z.toFixed(5)}`);

            geometry.center(); // Center the geometry

            // Use a very simple material to rule out material issues
            const material = new THREE.MeshStandardMaterial({
                color: 0xffaa00, // Orange
                side: THREE.DoubleSide, // Ensure we see both sides
                // wireframe: true // Uncomment to see wireframe
            });

            textMesh = new THREE.Mesh(geometry, material);
            textMesh.position.set(0, 0, 0); // Position at origin
            scene.add(textMesh);

            // Log details
            console.log("Text Mesh:", textMesh);
            console.log("Geometry parameters:", geometry.parameters);
            console.log("Geometry Bounding Box (after center):", geometry.boundingBox);
            console.log("Calculated BBox Z-size:", sizeVec.z);
        }

        function onWindowResize() {
            camera.aspect = window.innerWidth / window.innerHeight;
            camera.updateProjectionMatrix();
            renderer.setSize(window.innerWidth, window.innerHeight);
        }

        function animate() {
            requestAnimationFrame(animate);
            controls.update();
            renderer.render(scene, camera);
        }

        init();
    </script>
</body>
</html>