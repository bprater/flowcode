<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Floating Island Retreat</title>
    <style>
        body { margin: 0; overflow: hidden; background-color: #87CEEB; font-family: Arial, sans-serif; }
        canvas { display: block; }
        #controls {
            position: absolute;
            top: 10px;
            left: 10px;
            background-color: rgba(0,0,0,0.6);
            padding: 15px;
            border-radius: 8px;
            color: white;
            max-width: 250px;
        }
        #controls label {
            display: block;
            margin-bottom: 5px;
        }
        #controls input[type="range"] {
            width: 200px;
            margin-bottom: 10px;
        }
        #controls button {
            padding: 8px 12px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 10px;
        }
        #controls button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>

    <div id="controls">
        <div>
            <label for="timeOfDay">Time of Day (Sun Height):</label>
            <input type="range" id="timeOfDay" min="0" max="180" value="90" step="1">
        </div>
        <div>
            <label for="sunAzimuth">Sun Azimuth (Rotation):</label>
            <input type="range" id="sunAzimuth" min="0" max="360" value="45" step="1">
        </div>
        <div>
            <label for="cloudSpeed">Cloud Speed:</label>
            <input type="range" id="cloudSpeed" min="0" max="0.005" value="0.0005" step="0.0001">
        </div>
        <button id="resetCameraButton">Reset Camera</button>
    </div>

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

        let scene, camera, renderer, islandGroup, sunLight, ambientLight, cloudsGroup, orbitControls;
        const skyColors = {
            dawn: new THREE.Color(0xFFB6C1),
            day: new THREE.Color(0x87CEEB),
            dusk: new THREE.Color(0xFF8C69),
            night: new THREE.Color(0x000033)
        };
        const sunColors = {
            dawn: new THREE.Color(0xFFD700),
            day: new THREE.Color(0xFFFFFF),
            dusk: new THREE.Color(0xFFA500),
            night: new THREE.Color(0x444488)
        };
        const ambientIntensities = { dawn: 0.4, day: 0.6, dusk: 0.4, night: 0.1 };
        const sunIntensities = { dawn: 0.8, day: 1.2, dusk: 0.8, night: 0.1 };

        const timeOfDaySlider = document.getElementById('timeOfDay');
        const sunAzimuthSlider = document.getElementById('sunAzimuth');
        const cloudSpeedSlider = document.getElementById('cloudSpeed');
        const resetCameraButton = document.getElementById('resetCameraButton');

        const initialCameraPosition = new THREE.Vector3(15, 15, 25);
        const initialControlsTarget = new THREE.Vector3(0, 2, 0);

        const islandBaseMat = new THREE.MeshStandardMaterial({ color: 0x556B2F, roughness: 0.8, metalness: 0.1 });
        const mountainMat = new THREE.MeshStandardMaterial({ color: 0x8B4513, roughness: 0.9, metalness: 0.05 });
        const snowCapMat = new THREE.MeshStandardMaterial({ color: 0xFFFAFA, roughness: 0.7 });
        const treeTrunkMat = new THREE.MeshStandardMaterial({ color: 0x5D4037, roughness: 0.8 });
        const treeLeavesMat = new THREE.MeshStandardMaterial({ color: 0x2E7D32, roughness: 0.7 });

        function init() {
            scene = new THREE.Scene();
            scene.background = skyColors.day;

            camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 0.1, 1000);
            camera.position.copy(initialCameraPosition);

            renderer = new THREE.WebGLRenderer({ antialias: true });
            renderer.setSize(window.innerWidth, window.innerHeight);
            renderer.shadowMap.enabled = true;
            renderer.shadowMap.type = THREE.PCFSoftShadowMap;
            document.body.appendChild(renderer.domElement);

            islandGroup = new THREE.Group();
            scene.add(islandGroup);

            const islandRadiusTop = 10;
            const islandHeight = 5;
            const islandBaseGeo = new THREE.CylinderGeometry(islandRadiusTop, islandRadiusTop + 2, islandHeight, 32);
            const islandBase = new THREE.Mesh(islandBaseGeo, islandBaseMat);
            islandBase.castShadow = true;
            islandBase.receiveShadow = true;
            islandBase.position.y = -islandHeight / 2;
            islandGroup.add(islandBase);
            const islandTopY = 0;

            const numMountains = 3 + Math.floor(Math.random() * 3);
            for (let i = 0; i < numMountains; i++) {
                const mountainHeight = Math.random() * 6 + 6;
                const mountainRadius = Math.random() * 2 + 2;
                const mountain = createMountain(mountainHeight, mountainRadius); // mountainRadius is base radius

                const R = islandRadiusTop * 0.7 - mountainRadius;
                const angle = Math.random() * Math.PI * 2;
                const x = Math.cos(angle) * R * (Math.random() * 0.5 + 0.5);
                const z = Math.sin(angle) * R * (Math.random() * 0.5 + 0.5);

                mountain.position.set(x, islandTopY + mountainHeight / 2, z);
                islandGroup.add(mountain);
            }

            const numTrees = 20 + Math.floor(Math.random() * 15);
            for (let i = 0; i < numTrees; i++) {
                const tree = createTree();
                const R = islandRadiusTop * 0.95;
                let x, z, validPosition = false;
                let attempts = 0;

                while (!validPosition && attempts < 20) {
                    const angle = Math.random() * Math.PI * 2;
                    const dist = Math.random() * R;
                    x = Math.cos(angle) * dist;
                    z = Math.sin(angle) * dist;
                    validPosition = true;

                    for (const child of islandGroup.children) {
                        if (child.userData.isMountain) {
                            const mountainPos = child.position;
                            // --- FIX HERE ---
                            const mountainBaseRadius = child.userData.baseRadius; // Access stored radius
                            if (mountainBaseRadius === undefined) { // Safety check
                                console.warn("Mountain missing baseRadius in userData", child);
                                continue;
                            }
                            const distanceToMountainCenter = Math.sqrt((x - mountainPos.x) ** 2 + (z - mountainPos.z) ** 2);
                            
                            // Adjust collision check: consider tree is placed at (x, z) relative to island center.
                            // Mountain is also placed relative to island center.
                            // The effective radius for collision is mountainBaseRadius + a small buffer.
                            if (distanceToMountainCenter < mountainBaseRadius * 1.1) { // Check if tree's (x,z) is within mountain's footprint
                                validPosition = false;
                                break;
                            }
                        }
                    }
                    attempts++;
                }

                if (validPosition) {
                    tree.position.set(x, islandTopY, z);
                    islandGroup.add(tree);
                }
            }

            createClouds();
            createLights();

            orbitControls = new OrbitControls(camera, renderer.domElement);
            orbitControls.enableDamping = true;
            orbitControls.dampingFactor = 0.05;
            orbitControls.minDistance = 10;
            orbitControls.maxDistance = 100;
            orbitControls.target.copy(initialControlsTarget);
            orbitControls.rotateSpeed = 0.3;
            orbitControls.update();

            timeOfDaySlider.addEventListener('input', updateSunAndSky);
            sunAzimuthSlider.addEventListener('input', updateSunAndSky);
            resetCameraButton.addEventListener('click', resetCameraView);
            updateSunAndSky();

            window.addEventListener('resize', onWindowResize, false);
            animate();
        }

        function createMountain(height, radius) {
            const mountainGroup = new THREE.Group();
            mountainGroup.userData.isMountain = true;
            // --- FIX HERE ---
            mountainGroup.userData.baseRadius = radius; // Store the base radius (radiusBottom of the cone)

            const mountainGeo = new THREE.ConeGeometry(radius, height, 16);
            const mountainMesh = new THREE.Mesh(mountainGeo, mountainMat); // Renamed to mountainMesh for clarity
            mountainMesh.castShadow = true;
            mountainMesh.receiveShadow = true;
            mountainGroup.add(mountainMesh);

            if (height > 8) {
                const snowCapHeight = height * (Math.random() * 0.2 + 0.2);
                const snowCapRadius = radius * (snowCapHeight / height) * 1.1;
                const snowCapGeo = new THREE.ConeGeometry(snowCapRadius, snowCapHeight, 16);
                const snowCap = new THREE.Mesh(snowCapGeo, snowCapMat);
                snowCap.position.y = height / 2 - snowCapHeight / 2 + 0.1;
                mountainMesh.add(snowCap); // Add snowcap as child of the mountain mesh itself
            }
            return mountainGroup;
        }

        function createTree() {
            const treeGroup = new THREE.Group();
            treeGroup.userData.isTree = true;

            const trunkHeight = Math.random() * 1 + 1.5;
            const trunkRadius = trunkHeight * 0.1;
            const trunkGeo = new THREE.CylinderGeometry(trunkRadius * 0.8, trunkRadius, trunkHeight, 8);
            const trunk = new THREE.Mesh(trunkGeo, treeTrunkMat);
            trunk.castShadow = true;
            trunk.position.y = trunkHeight / 2;
            treeGroup.add(trunk);

            const leavesHeight = Math.random() * 1.5 + trunkHeight * 0.8;
            const leavesRadius = trunkHeight * 0.5 + Math.random() * 0.5;
            const leavesGeo = new THREE.ConeGeometry(leavesRadius, leavesHeight, 8);
            const leaves = new THREE.Mesh(leavesGeo, treeLeavesMat);
            leaves.castShadow = true;
            leaves.position.y = trunkHeight + leavesHeight * 0.4;
            treeGroup.add(leaves);

            treeGroup.scale.setScalar(Math.random() * 0.3 + 0.7);
            return treeGroup;
        }

        function createClouds() {
            cloudsGroup = new THREE.Group();
            const cloudMaterial = new THREE.MeshStandardMaterial({
                color: 0xffffff,
                opacity: 0.85,
                transparent: true,
                roughness: 0.95
            });

            for (let i = 0; i < 20; i++) {
                const cloudParts = Math.floor(Math.random() * 3) + 2;
                const cloudGroup = new THREE.Group();

                for(let j=0; j < cloudParts; j++) {
                    const cloudGeo = new THREE.SphereGeometry(
                        Math.random() * 1.5 + 1, 12, 8 );
                    const cloudPart = new THREE.Mesh(cloudGeo, cloudMaterial);
                    cloudPart.castShadow = true;
                    cloudPart.position.set(
                        (Math.random() - 0.5) * 2.5,
                        (Math.random() - 0.5) * 1,
                        (Math.random() - 0.5) * 2.5
                    );
                    cloudPart.scale.y = Math.random() * 0.4 + 0.5;
                    cloudPart.scale.x = cloudPart.scale.z = Math.random() * 0.5 + 0.8;
                    cloudGroup.add(cloudPart);
                }

                const angle = Math.random() * Math.PI * 2;
                const distance = 18 + Math.random() * 12;
                cloudGroup.position.set(
                    Math.cos(angle) * distance,
                    Math.random() * 6 + 10,
                    Math.sin(angle) * distance
                );
                cloudsGroup.add(cloudGroup);
            }
            scene.add(cloudsGroup);
        }

        function createLights() {
            ambientLight = new THREE.AmbientLight(0xffffff, ambientIntensities.day);
            scene.add(ambientLight);

            sunLight = new THREE.DirectionalLight(0xffffff, sunIntensities.day);
            sunLight.castShadow = true;
            sunLight.shadow.mapSize.width = 2048;
            sunLight.shadow.mapSize.height = 2048;
            sunLight.shadow.camera.near = 0.5;
            sunLight.shadow.camera.far = 500;
            const shadowCamSize = 35;
            sunLight.shadow.camera.left = -shadowCamSize;
            sunLight.shadow.camera.right = shadowCamSize;
            sunLight.shadow.camera.top = shadowCamSize;
            sunLight.shadow.camera.bottom = -shadowCamSize;
            scene.add(sunLight);
        }

        function updateSunAndSky() {
            const sunAngleRad = THREE.MathUtils.degToRad(parseFloat(timeOfDaySlider.value));
            const sunAzimuthRad = THREE.MathUtils.degToRad(parseFloat(sunAzimuthSlider.value));

            const sunDistance = 50;
            sunLight.position.x = sunDistance * Math.sin(sunAngleRad) * Math.cos(sunAzimuthRad);
            sunLight.position.y = sunDistance * Math.cos(sunAngleRad);
            sunLight.position.z = sunDistance * Math.sin(sunAngleRad) * Math.sin(sunAzimuthRad);
            sunLight.target = islandGroup;

            const sunHeightNormalized = sunLight.position.y / sunDistance;
            let currentSky, currentSun, currentAmbient, currentSunIntensity;

            if (sunHeightNormalized < -0.1) { // Night
                currentSky = skyColors.night; currentSun = sunColors.night;
                currentAmbient = ambientIntensities.night; currentSunIntensity = sunIntensities.night;
            } else if (sunHeightNormalized < 0.25) { // Dawn
                const t = THREE.MathUtils.inverseLerp(-0.1, 0.25, sunHeightNormalized);
                currentSky = new THREE.Color().lerpColors(skyColors.night, skyColors.dawn, t);
                currentSun = new THREE.Color().lerpColors(sunColors.night, sunColors.dawn, t);
                currentAmbient = THREE.MathUtils.lerp(ambientIntensities.night, ambientIntensities.dawn, t);
                currentSunIntensity = THREE.MathUtils.lerp(sunIntensities.night, sunIntensities.dawn, t);
            } else if (sunHeightNormalized < 0.75) { // Day
                const t = THREE.MathUtils.inverseLerp(0.25, 0.75, sunHeightNormalized);
                currentSky = new THREE.Color().lerpColors(skyColors.dawn, skyColors.day, t);
                currentSun = new THREE.Color().lerpColors(sunColors.dawn, sunColors.day, t);
                currentAmbient = THREE.MathUtils.lerp(ambientIntensities.dawn, ambientIntensities.day, t);
                currentSunIntensity = THREE.MathUtils.lerp(sunIntensities.dawn, sunIntensities.day, t);
            } else { // Dusk
                const t = THREE.MathUtils.inverseLerp(0.75, 1.0, sunHeightNormalized);
                currentSky = new THREE.Color().lerpColors(skyColors.day, skyColors.dusk, t);
                currentSun = new THREE.Color().lerpColors(sunColors.day, sunColors.dusk, t);
                currentAmbient = THREE.MathUtils.lerp(ambientIntensities.day, ambientIntensities.dusk, t);
                currentSunIntensity = THREE.MathUtils.lerp(sunIntensities.day, sunIntensities.dusk, t);
            }
            
            if (scene.background && scene.background.isColor) {
                 scene.background.lerp(currentSky, 0.1);
            } else {
                 scene.background = currentSky.clone();
            }
            sunLight.color.lerp(currentSun, 0.1);
            ambientLight.color.lerp(currentSun, 0.1);
            ambientLight.intensity = currentAmbient;
            sunLight.intensity = currentSunIntensity;
        }

        function resetCameraView() {
            camera.position.copy(initialCameraPosition);
            orbitControls.target.copy(initialControlsTarget);
            orbitControls.update();
        }

        function onWindowResize() {
            camera.aspect = window.innerWidth / window.innerHeight;
            camera.updateProjectionMatrix();
            renderer.setSize(window.innerWidth, window.innerHeight);
        }

        function animate() {
            requestAnimationFrame(animate);

            const speed = parseFloat(cloudSpeedSlider.value);
            cloudsGroup.rotation.y += speed;
            cloudsGroup.children.forEach((cloudCluster, index) => {
                cloudCluster.children.forEach((cloudPart, partIndex) => {
                    cloudPart.position.y += Math.sin(Date.now() * 0.0003 + index * 0.5 + partIndex * 0.2) * 0.003;
                    cloudPart.position.x += Math.cos(Date.now() * 0.0002 + index * 0.6 + partIndex * 0.3) * 0.002;
                });
            });

            // --- FIX HERE ---
            // orbitControls is already defined globally and initialized in init()
            // We just need to call update() if enableDamping is true
            if (orbitControls) {
                orbitControls.update();
            }
            
            renderer.render(scene, camera);
        }

        init();
    </script>
</body>
</html>