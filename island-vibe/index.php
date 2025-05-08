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
            background-color: rgba(0,0,0,0.65);
            padding: 15px;
            border-radius: 8px;
            color: white;
            max-width: 260px;
        }
        #controls label { display: block; margin-bottom: 5px; font-size: 0.9em; }
        #controls input[type="range"] { width: 220px; margin-bottom: 10px; }
        #controls button { padding: 8px 12px; background-color: #4CAF50; color: white; border: none; border-radius: 4px; cursor: pointer; margin-top: 10px; }
        #controls button:hover { background-color: #45a049; }
    </style>
</head>
<body>

    <div id="controls">
        <div>
            <label for="timeOfDay">Time of Day (Sun Height):</label>
            <input type="range" id="timeOfDay" min="0" max="180" value="60" step="1">
        </div>
        <div>
            <label for="sunAzimuth">Sun Azimuth (Rotation):</label>
            <input type="range" id="sunAzimuth" min="0" max="360" value="30" step="1">
        </div>
        <div>
            <label for="zoomLevel">Zoom Level:</label>
            <input type="range" id="zoomLevel" min="1.5" max="100" value="25" step="0.1">
        </div>
        <div>
            <label for="cloudSpeed">Cloud Speed:</label>
            <input type="range" id="cloudSpeed" min="0" max="0.005" value="0.0005" step="0.0001">
        </div>
        <div>
            <label for="islandRotationSpeed">Island Rotation Speed:</label>
            <input type="range" id="islandRotationSpeed" min="0" max="0.003" value="0.0002" step="0.00005">
        </div>
        <div>
            <label for="mountainMistOpacity">Mountain Mist Opacity:</label>
            <input type="range" id="mountainMistOpacity" min="0" max="0.5" value="0.15" step="0.01">
        </div>
        <button id="resetCameraButton">Reset Camera</button>
    </div>

    <script type="importmap">
        { "imports": { "three": "https://unpkg.com/three@0.160.0/build/three.module.js", "three/addons/": "https://unpkg.com/three@0.160.0/examples/jsm/" } }
    </script>

    <script type="module">
        import * as THREE from 'three';
        import { OrbitControls } from 'three/addons/controls/OrbitControls.js';

        let scene, camera, renderer, islandGroup, sunLight, ambientLight, cloudsGroup, mountainMistGroup, birdsGroup, orbitControls;
        let jewelClusters = [];
        const MAX_JEWELS = 3;
        let raycaster, mouse;

        const skyColors = { dawn: new THREE.Color(0xFFB6C1), day: new THREE.Color(0x87CEEB), dusk: new THREE.Color(0xFF8C69), night: new THREE.Color(0x000033) };
        const sunColors = { dawn: new THREE.Color(0xFFD700), day: new THREE.Color(0xFFFFFF), dusk: new THREE.Color(0xFFA500), night: new THREE.Color(0x444488) };
        const ambientIntensities = { dawn: 0.4, day: 0.6, dusk: 0.4, night: 0.1 };
        const sunIntensities = { dawn: 0.8, day: 1.2, dusk: 0.8, night: 0.1 };

        const timeOfDaySlider = document.getElementById('timeOfDay');
        const sunAzimuthSlider = document.getElementById('sunAzimuth');
        const zoomLevelSlider = document.getElementById('zoomLevel');
        const cloudSpeedSlider = document.getElementById('cloudSpeed');
        const islandRotationSpeedSlider = document.getElementById('islandRotationSpeed');
        const mountainMistOpacitySlider = document.getElementById('mountainMistOpacity');
        const resetCameraButton = document.getElementById('resetCameraButton');

        const initialCameraPosition = new THREE.Vector3(18, 12, 28);
        const initialControlsTarget = new THREE.Vector3(0, 3, 0);
        let initialZoomDistance;

        const islandBaseMat = new THREE.MeshStandardMaterial({ color: 0x556B2F, roughness: 0.8, metalness: 0.1 });
        const mountainMat = new THREE.MeshStandardMaterial({ color: 0x8B4513, roughness: 0.9, metalness: 0.05 });
        const snowCapMat = new THREE.MeshStandardMaterial({ color: 0xFFFAFA, roughness: 0.7 });
        const treeTrunkMat = new THREE.MeshStandardMaterial({ color: 0x5D4037, roughness: 0.8 });
        const treeLeavesMat = new THREE.MeshStandardMaterial({ color: 0x2E7D32, roughness: 0.7 });
        const grassMat = new THREE.MeshStandardMaterial({ color: 0x4CAF50, roughness: 0.8, side: THREE.DoubleSide });
        const birdMaterial = new THREE.MeshStandardMaterial({ color: 0x222222, roughness: 0.6 });
        let baseMountainMistMaterial;
        const jewelMaterial = new THREE.MeshStandardMaterial({ color: 0x00FFFF, emissive: 0x00AAAA, emissiveIntensity: 1.5, roughness: 0.1, metalness: 0.3, transparent: true, opacity: 0.85 });
        const flowerMaterials = [
            new THREE.MeshStandardMaterial({ color: 0xFF69B4, emissive: 0xCC5490, emissiveIntensity: 0.5, roughness: 0.6 }),
            new THREE.MeshStandardMaterial({ color: 0xFFFF00, emissive: 0xCCCC00, emissiveIntensity: 0.5, roughness: 0.6 }),
            new THREE.MeshStandardMaterial({ color: 0x9370DB, emissive: 0x7A5CBF, emissiveIntensity: 0.5, roughness: 0.6 }),
            new THREE.MeshStandardMaterial({ color: 0xFFA07A, emissive: 0xCC8061, emissiveIntensity: 0.5, roughness: 0.6 })
        ];

        const tempWorldPosition = new THREE.Vector3();
        const BASE_CLOUD_OPACITY = 0.80; const MIN_CLOUD_OPACITY = 0.1;
        const MIN_OPACITY_DISTANCE_CLOUD = 8; const MAX_OPACITY_DISTANCE_CLOUD = 20;
        let islandRadiusTop, islandTopY;

        function init() {
            scene = new THREE.Scene();
            scene.background = skyColors.day;

            camera = new THREE.PerspectiveCamera(60, window.innerWidth / window.innerHeight, 0.1, 1000);
            camera.position.copy(initialCameraPosition);
            initialZoomDistance = camera.position.distanceTo(initialControlsTarget);

            renderer = new THREE.WebGLRenderer({ antialias: true });
            renderer.setSize(window.innerWidth, window.innerHeight);
            renderer.shadowMap.enabled = true;
            renderer.shadowMap.type = THREE.PCFSoftShadowMap;
            document.body.appendChild(renderer.domElement);

            baseMountainMistMaterial = new THREE.MeshStandardMaterial({
                color: 0xffffff, opacity: parseFloat(mountainMistOpacitySlider.value), transparent: true, roughness: 0.98, depthWrite: false
            });

            islandGroup = new THREE.Group();
            scene.add(islandGroup);

            islandRadiusTop = 10; const islandHeight = 5;
            islandTopY = 0;
            const islandBaseGeo = new THREE.CylinderGeometry(islandRadiusTop, islandRadiusTop + 2, islandHeight, 32);
            const islandBase = new THREE.Mesh(islandBaseGeo, islandBaseMat);
            islandBase.castShadow = true; islandBase.receiveShadow = true;
            islandBase.position.y = -islandHeight / 2;
            islandGroup.add(islandBase);

            mountainMistGroup = new THREE.Group();
            scene.add(mountainMistGroup);

            const numMountains = 3 + Math.floor(Math.random() * 3);
            for (let i = 0; i < numMountains; i++) {
                const mountainHeight = Math.random() * 6 + 6; const mountainRadius = Math.random() * 2 + 2;
                const mountain = createMountain(mountainHeight, mountainRadius);
                const R = islandRadiusTop * 0.7 - mountainRadius; const angle = Math.random() * Math.PI * 2;
                const x = Math.cos(angle) * R * (Math.random() * 0.5 + 0.5);
                const z = Math.sin(angle) * R * (Math.random() * 0.5 + 0.5);
                mountain.position.set(x, islandTopY + mountainHeight / 2, z);
                islandGroup.add(mountain);
                createMountainMist(mountain.position, mountainHeight, mountainRadius);
            }

            const numTrees = 20 + Math.floor(Math.random() * 15);
            for (let i = 0; i < numTrees; i++) {
                const tree = createTree();
                const R = islandRadiusTop * 0.9; let x, z, validPosition = false, attempts = 0;
                while (!validPosition && attempts < 20) {
                    const angle = Math.random() * Math.PI * 2; const dist = Math.random() * R; x = Math.cos(angle) * dist; z = Math.sin(angle) * dist; validPosition = true;
                    for (const child of islandGroup.children) {
                        if (child.userData.isMountain) {
                            const mountainPos = child.position; const mountainBaseRadius = child.userData.baseRadius; if (mountainBaseRadius === undefined) continue;
                            const distanceToMountainCenter = Math.sqrt((x - mountainPos.x) ** 2 + (z - mountainPos.z) ** 2);
                            if (distanceToMountainCenter < mountainBaseRadius * 1.2) { validPosition = false; break; }
                        }
                    } attempts++;
                }
                if (validPosition) { tree.position.set(x, islandTopY, z); islandGroup.add(tree); }
            }

            const numGrassPatches = 150 + Math.floor(Math.random() * 100);
            for (let i = 0; i < numGrassPatches; i++) {
                const grassPatch = createGrassPatch();
                const R = islandRadiusTop * 0.98; let x, z, validPosition = false, attempts = 0;
                while (!validPosition && attempts < 10) {
                    const angle = Math.random() * Math.PI * 2; const dist = Math.random() * R; x = Math.cos(angle) * dist; z = Math.sin(angle) * dist; validPosition = true;
                    for (const child of islandGroup.children) {
                        if (child.userData.isMountain) { const mountainPos = child.position; const mountainBaseRadius = child.userData.baseRadius; if (mountainBaseRadius === undefined) continue; const distanceToMountainCenter = Math.sqrt((x - mountainPos.x) ** 2 + (z - mountainPos.z) ** 2); if (distanceToMountainCenter < mountainBaseRadius * 0.9) { validPosition = false; break; } }
                        if (child.userData.isTree) { const treePos = child.position; const distanceToTreeCenter = Math.sqrt((x - treePos.x)**2 + (z - treePos.z)**2); if (distanceToTreeCenter < 0.5) { validPosition = false; break; } }
                    } attempts++;
                }
                if(validPosition){ grassPatch.position.set(x, islandTopY, z); islandGroup.add(grassPatch); }
            }

            createClouds(); createBirds(); createLights();
            for (let i = 0; i < MAX_JEWELS; i++) { addJewelCluster(); }

            orbitControls = new OrbitControls(camera, renderer.domElement);
            orbitControls.enableDamping = true; orbitControls.dampingFactor = 0.04;
            orbitControls.minDistance = 1.5; orbitControls.maxDistance = 100;
            orbitControls.zoomSpeed = 1.0; orbitControls.panSpeed = 0.5; orbitControls.rotateSpeed = 0.3;
            orbitControls.target.copy(initialControlsTarget);
            orbitControls.update();

            zoomLevelSlider.min = orbitControls.minDistance.toString();
            zoomLevelSlider.max = orbitControls.maxDistance.toString();
            zoomLevelSlider.value = initialZoomDistance.toString();
            zoomLevelSlider.addEventListener('input', handleZoomSliderChange);
            orbitControls.addEventListener('change', updateZoomSliderFromControls);

            raycaster = new THREE.Raycaster();
            mouse = new THREE.Vector2();
            window.addEventListener('click', onMouseClick, false);

            timeOfDaySlider.addEventListener('input', updateSunAndSky);
            sunAzimuthSlider.addEventListener('input', updateSunAndSky);
            mountainMistOpacitySlider.addEventListener('input', updateMountainMistOpacity);
            resetCameraButton.addEventListener('click', resetCameraView);
            updateSunAndSky(); updateMountainMistOpacity();

            window.addEventListener('resize', onWindowResize, false);
            animate();
        }

        function createMountain(height, radius) {
            const mountainGroup = new THREE.Group(); mountainGroup.userData.isMountain = true; mountainGroup.userData.baseRadius = radius;
            const mountainGeo = new THREE.ConeGeometry(radius, height, 16);
            const mountainMesh = new THREE.Mesh(mountainGeo, mountainMat); mountainMesh.castShadow = true; mountainMesh.receiveShadow = true; mountainGroup.add(mountainMesh);
            if (height > 8) {
                const snowCapHeight = height * (Math.random() * 0.2 + 0.2); const snowCapRadius = radius * (snowCapHeight / height) * 1.1;
                const snowCapGeo = new THREE.ConeGeometry(snowCapRadius, snowCapHeight, 16); const snowCap = new THREE.Mesh(snowCapGeo, snowCapMat);
                snowCap.position.y = height / 2 - snowCapHeight / 2 + 0.1; mountainMesh.add(snowCap);
            } return mountainGroup;
        }

        function createTree() {
            const treeGroup = new THREE.Group(); treeGroup.userData.isTree = true;
            treeGroup.userData.swayInfo = { offset: Math.random() * Math.PI * 2, speed: Math.random() * 0.5 + 0.4, amplitude: Math.random() * 0.02 + 0.01 };
            const trunkHeight = Math.random() * 1 + 1.5; const trunkRadius = trunkHeight * 0.1;
            const trunkGeo = new THREE.CylinderGeometry(trunkRadius * 0.8, trunkRadius, trunkHeight, 8);
            const trunk = new THREE.Mesh(trunkGeo, treeTrunkMat); trunk.castShadow = true; trunk.position.y = trunkHeight / 2; treeGroup.add(trunk);
            const leavesHeight = Math.random() * 1.5 + trunkHeight * 0.8; const leavesRadius = trunkHeight * 0.5 + Math.random() * 0.5;
            const leavesGeo = new THREE.ConeGeometry(leavesRadius, leavesHeight, 8);
            const leaves = new THREE.Mesh(leavesGeo, treeLeavesMat); leaves.name = "leaves"; leaves.castShadow = true; leaves.position.y = trunkHeight + leavesHeight * 0.4; treeGroup.add(leaves);
            treeGroup.scale.setScalar(Math.random() * 0.3 + 0.7); return treeGroup;
        }

        function createGrassPatch() {
            const patchGroup = new THREE.Group(); patchGroup.userData.isGrass = true;
            patchGroup.userData.swayInfo = { offset: Math.random() * Math.PI * 2, speed: Math.random() * 0.8 + 0.6, amplitude: Math.random() * 0.15 + 0.05 };
            const numBlades = 3 + Math.floor(Math.random() * 3);
            for (let i = 0; i < numBlades; i++) {
                const bladeHeight = Math.random() * 0.5 + 0.3; const bladeWidth = bladeHeight * (Math.random() * 0.1 + 0.05);
                const bladeGeo = new THREE.PlaneGeometry(bladeWidth, bladeHeight); bladeGeo.translate(0, bladeHeight / 2, 0);
                const blade = new THREE.Mesh(bladeGeo, grassMat); blade.castShadow = true;
                blade.rotation.y = (Math.random() - 0.5) * Math.PI * 0.3; blade.rotation.z = (Math.random() - 0.5) * Math.PI * 0.1; blade.rotation.x = (Math.random() - 0.5) * Math.PI * 0.05;
                blade.position.x = (Math.random() - 0.5) * 0.1; blade.position.z = (Math.random() - 0.5) * 0.1;
                patchGroup.add(blade);
                if (Math.random() < 0.35) {
                    const flowerHeadSize = bladeWidth * (Math.random() * 1.5 + 1.2);
                    const flowerGeo = new THREE.SphereGeometry(flowerHeadSize, 6, 4);
                    const flowerMatInstance = flowerMaterials[Math.floor(Math.random() * flowerMaterials.length)];
                    const flowerHead = new THREE.Mesh(flowerGeo, flowerMatInstance);
                    flowerHead.position.y = bladeHeight + flowerHeadSize * 0.8; flowerHead.castShadow = true;
                    blade.add(flowerHead);
                }
            } patchGroup.scale.setScalar(Math.random() * 0.4 + 0.8); return patchGroup;
        }

        function createClouds() {
            cloudsGroup = new THREE.Group();
            const baseCloudMaterial = new THREE.MeshStandardMaterial({ color: 0xffffff, opacity: BASE_CLOUD_OPACITY, transparent: true, roughness: 0.95 });
            for (let i = 0; i < 20; i++) {
                const cloudParts = Math.floor(Math.random() * 3) + 2; const cloudClusterGroup = new THREE.Group();
                for(let j=0; j < cloudParts; j++) {
                    const cloudGeo = new THREE.SphereGeometry(Math.random() * 1.5 + 1, 12, 8); const cloudPartMaterialInstance = baseCloudMaterial.clone();
                    const cloudPart = new THREE.Mesh(cloudGeo, cloudPartMaterialInstance); cloudPart.castShadow = true;
                    cloudPart.position.set((Math.random() - 0.5) * 2.5, (Math.random() - 0.5) * 1, (Math.random() - 0.5) * 2.5);
                    cloudPart.scale.y = Math.random() * 0.4 + 0.5; cloudPart.scale.x = cloudPart.scale.z = Math.random() * 0.5 + 0.8;
                    cloudClusterGroup.add(cloudPart);
                }
                const angle = Math.random() * Math.PI * 2; const distance = 18 + Math.random() * 12;
                cloudClusterGroup.position.set(Math.cos(angle) * distance, Math.random() * 6 + 10, Math.sin(angle) * distance);
                cloudsGroup.add(cloudClusterGroup);
            } scene.add(cloudsGroup);
        }

        function createBirds() {
            birdsGroup = new THREE.Group(); const numBirds = 7 + Math.floor(Math.random() * 6);
            const birdGeo = new THREE.ConeGeometry(0.15, 0.6, 8); birdGeo.rotateX(Math.PI / 2);
            for (let i = 0; i < numBirds; i++) {
                const bird = new THREE.Mesh(birdGeo, birdMaterial.clone()); bird.castShadow = true;
                const orbitRadius = 15 + Math.random() * 15; const orbitSpeed = (Math.random() * 0.005 + 0.005) * (Math.random() > 0.5 ? 1 : -1);
                const startAngle = Math.random() * Math.PI * 2; const flightHeight = 10 + Math.random() * 10;
                const verticalBobAmplitude = 0.2 + Math.random() * 0.5; const verticalBobFrequency = Math.random() * 3 + 2;
                bird.userData = { orbitRadius, orbitSpeed, currentAngle: startAngle, baseHeight: flightHeight, verticalBobAmplitude, verticalBobFrequency };
                bird.position.set(Math.cos(startAngle) * orbitRadius, flightHeight, Math.sin(startAngle) * orbitRadius); birdsGroup.add(bird);
            } scene.add(birdsGroup);
        }

        function createLights() {
            ambientLight = new THREE.AmbientLight(0xffffff, ambientIntensities.day); scene.add(ambientLight);
            sunLight = new THREE.DirectionalLight(0xffffff, sunIntensities.day); sunLight.castShadow = true;
            sunLight.shadow.mapSize.width = 2048; sunLight.shadow.mapSize.height = 2048; sunLight.shadow.camera.near = 0.5; sunLight.shadow.camera.far = 500;
            const shadowCamSize = 40; sunLight.shadow.camera.left = -shadowCamSize; sunLight.shadow.camera.right = shadowCamSize; sunLight.shadow.camera.top = shadowCamSize; sunLight.shadow.camera.bottom = -shadowCamSize;
            scene.add(sunLight);
        }

        function createMountainMist(mountainWorldPos, mountainHeight, mountainRadius) {
            const numMistParticles = 5 + Math.floor(Math.random() * 5);
            for (let i = 0; i < numMistParticles; i++) {
                const mistGeo = new THREE.SphereGeometry(mountainRadius * (Math.random() * 0.5 + 0.4), 10, 6);
                const mistParticle = new THREE.Mesh(mistGeo, baseMountainMistMaterial.clone());
                const angle = Math.random() * Math.PI * 2; const radiusOffset = mountainRadius * (Math.random() * 0.6 + 0.2);
                const heightOffset = mountainHeight * (Math.random() * 0.3 + 0.4);
                mistParticle.position.set(mountainWorldPos.x + Math.cos(angle) * radiusOffset, mountainWorldPos.y - (mountainHeight/2) + heightOffset, mountainWorldPos.z + Math.sin(angle) * radiusOffset);
                mistParticle.scale.y = Math.random() * 0.3 + 0.3; mistParticle.scale.x = mistParticle.scale.z = Math.random() * 0.4 + 0.8;
                mistParticle.userData.swaySpeed = Math.random() * 0.0005 + 0.0002; mistParticle.userData.swayAmplitude = Math.random() * 0.2 + 0.1; mistParticle.userData.initialY = mistParticle.position.y;
                mountainMistGroup.add(mistParticle);
            }
        }

        function updateMountainMistOpacity() {
            const newOpacity = parseFloat(mountainMistOpacitySlider.value);
            baseMountainMistMaterial.opacity = newOpacity;
            mountainMistGroup.children.forEach(mist => { if (mist.material) { mist.material.opacity = newOpacity; } });
        }

        function addJewelCluster() {
            const clusterGroup = new THREE.Group(); clusterGroup.name = "jewelClusterInstance";
            const numJewelsInCluster = 3 + Math.floor(Math.random() * 3); const baseJewelSize = 0.25;
            const clusterLight = new THREE.PointLight(0x00FFFF, 3, 5); // Light color matches jewel material
            for (let i = 0; i < numJewelsInCluster; i++) {
                const jewelGeo = new THREE.IcosahedronGeometry(baseJewelSize * (Math.random() * 0.3 + 0.85), 0);
                const jewel = new THREE.Mesh(jewelGeo, jewelMaterial);
                jewel.position.set((Math.random() - 0.5) * baseJewelSize * 1.5, (Math.random() - 0.5) * baseJewelSize * 1.5, (Math.random() - 0.5) * baseJewelSize * 1.5);
                jewel.rotation.set(Math.random() * Math.PI, Math.random() * Math.PI, Math.random() * Math.PI); clusterGroup.add(jewel);
            }
            let x, z, validPosition = false, attempts = 0; const R = islandRadiusTop * 0.85;
            while (!validPosition && attempts < 30) {
                const angle = Math.random() * Math.PI * 2; const dist = Math.random() * R; x = Math.cos(angle) * dist; z = Math.sin(angle) * dist; validPosition = true;
                for (const child of islandGroup.children) { if (child.userData.isMountain) { const mountainPos = child.position; const mountainBaseRadius = child.userData.baseRadius; if (mountainBaseRadius === undefined) continue; const distanceToMountainCenter = Math.sqrt((x - mountainPos.x) ** 2 + (z - mountainPos.z) ** 2); if (distanceToMountainCenter < mountainBaseRadius * 1.3) { validPosition = false; break; } } } attempts++;
            } clusterGroup.position.set(x, islandTopY + baseJewelSize * 0.7, z);
            clusterLight.position.set(0, baseJewelSize * 0.5, 0); clusterGroup.add(clusterLight); clusterGroup.userData.light = clusterLight;
            islandGroup.add(clusterGroup); jewelClusters.push(clusterGroup);
        }
        
        function replaceJewelCluster(oldCluster) {
            const index = jewelClusters.indexOf(oldCluster); if (index > -1) { jewelClusters.splice(index, 1); }
            islandGroup.remove(oldCluster); if (oldCluster.userData.light) oldCluster.remove(oldCluster.userData.light);
            oldCluster.traverse(child => { if (child.geometry) child.geometry.dispose(); }); addJewelCluster();
        }

        function onMouseClick(event) {
             mouse.x = (event.clientX / window.innerWidth) * 2 - 1; mouse.y = -(event.clientY / window.innerHeight) * 2 + 1;
            raycaster.setFromCamera(mouse, camera); const intersects = raycaster.intersectObjects(jewelClusters, true);
            if (intersects.length > 0) {
                let clickedCluster = null;
                for (const intersect of intersects) { let parent = intersect.object.parent; while(parent) { if (parent.name === "jewelClusterInstance") { clickedCluster = parent; break; } parent = parent.parent; } if (clickedCluster) break; }
                if (clickedCluster) { replaceJewelCluster(clickedCluster); }
            }
        }

        function handleZoomSliderChange() {
            if (!orbitControls || !camera) return;
            const newDistance = parseFloat(zoomLevelSlider.value);
            // Get the current direction from camera to target
            const direction = new THREE.Vector3().subVectors(orbitControls.target, camera.position).normalize();
            // New position is target minus direction scaled by new distance
            camera.position.copy(orbitControls.target).addScaledVector(direction, -newDistance);
            orbitControls.update(); // This will also trigger 'change' event
        }

        let isSliderUpdatingControls = false; // Flag to prevent loop between slider and controls
        function updateZoomSliderFromControls() {
            if (!orbitControls || isSliderUpdatingControls) return;
            const distance = camera.position.distanceTo(orbitControls.target); // Use actual distance
            zoomLevelSlider.value = distance.toString();
        }

        function updateSunAndSky() {
            const sunAngleRad = THREE.MathUtils.degToRad(parseFloat(timeOfDaySlider.value)); const sunAzimuthRad = THREE.MathUtils.degToRad(parseFloat(sunAzimuthSlider.value));
            const sunDistance = 50; sunLight.position.x = sunDistance * Math.sin(sunAngleRad) * Math.cos(sunAzimuthRad); sunLight.position.y = sunDistance * Math.cos(sunAngleRad); sunLight.position.z = sunDistance * Math.sin(sunAngleRad) * Math.sin(sunAzimuthRad); sunLight.target = islandGroup;
            const sunHeightNormalized = sunLight.position.y / sunDistance; let currentSky, currentSun, currentAmbient, currentSunIntensity;
            if (sunHeightNormalized < -0.1) { currentSky = skyColors.night; currentSun = sunColors.night; currentAmbient = ambientIntensities.night; currentSunIntensity = sunIntensities.night;
            } else if (sunHeightNormalized < 0.25) { const t = THREE.MathUtils.inverseLerp(-0.1, 0.25, sunHeightNormalized); currentSky = new THREE.Color().lerpColors(skyColors.night, skyColors.dawn, t); currentSun = new THREE.Color().lerpColors(sunColors.night, sunColors.dawn, t); currentAmbient = THREE.MathUtils.lerp(ambientIntensities.night, ambientIntensities.dawn, t); currentSunIntensity = THREE.MathUtils.lerp(sunIntensities.night, sunIntensities.dawn, t);
            } else if (sunHeightNormalized < 0.75) { const t = THREE.MathUtils.inverseLerp(0.25, 0.75, sunHeightNormalized); currentSky = new THREE.Color().lerpColors(skyColors.dawn, skyColors.day, t); currentSun = new THREE.Color().lerpColors(sunColors.dawn, sunColors.day, t); currentAmbient = THREE.MathUtils.lerp(ambientIntensities.dawn, ambientIntensities.day, t); currentSunIntensity = THREE.MathUtils.lerp(sunIntensities.dawn, sunIntensities.day, t);
            } else { const t = THREE.MathUtils.inverseLerp(0.75, 1.0, sunHeightNormalized); currentSky = new THREE.Color().lerpColors(skyColors.day, skyColors.dusk, t); currentSun = new THREE.Color().lerpColors(sunColors.day, sunColors.dusk, t); currentAmbient = THREE.MathUtils.lerp(ambientIntensities.day, ambientIntensities.dusk, t); currentSunIntensity = THREE.MathUtils.lerp(sunIntensities.day, sunIntensities.dusk, t); }
            if (scene.background && scene.background.isColor) scene.background.lerp(currentSky, 0.1); else scene.background = currentSky.clone();
            sunLight.color.lerp(currentSun, 0.1); ambientLight.color.lerp(currentSun, 0.1); ambientLight.intensity = currentAmbient; sunLight.intensity = currentSunIntensity;
        }

        function resetCameraView() {
            camera.position.copy(initialCameraPosition);
            orbitControls.target.copy(initialControlsTarget);
            orbitControls.update(); // This will trigger 'change' and update slider
        }
        function onWindowResize() { camera.aspect = window.innerWidth / window.innerHeight; camera.updateProjectionMatrix(); renderer.setSize(window.innerWidth, window.innerHeight); }

        const birdLookAheadTarget = new THREE.Vector3();

        function animate() {
            requestAnimationFrame(animate);
            const time = Date.now() * 0.001;

            const islandRotationSpeed = parseFloat(islandRotationSpeedSlider.value); islandGroup.rotation.y += islandRotationSpeed;
            mountainMistGroup.children.forEach(mist => { mist.position.y = mist.userData.initialY + Math.sin(time * 0.2 + mist.id) * mist.userData.swayAmplitude * 0.3; mist.rotation.y += mist.userData.swaySpeed * (Math.sin(time * 0.1 + mist.id * 0.5) + 1); });
            const cloudSpeed = parseFloat(cloudSpeedSlider.value); cloudsGroup.rotation.y += cloudSpeed;
            cloudsGroup.children.forEach((cloudCluster) => { cloudCluster.children.forEach((cloudPart) => { cloudPart.getWorldPosition(tempWorldPosition); const distanceToCamera = tempWorldPosition.distanceTo(camera.position); const opacityT = THREE.MathUtils.smoothstep(distanceToCamera, MIN_OPACITY_DISTANCE_CLOUD, MAX_OPACITY_DISTANCE_CLOUD); cloudPart.material.opacity = THREE.MathUtils.lerp(MIN_CLOUD_OPACITY, BASE_CLOUD_OPACITY, opacityT); cloudPart.position.y += Math.sin(time * 0.3 + cloudPart.id * 0.5) * 0.002; cloudPart.position.x += Math.cos(time * 0.2 + cloudPart.id * 0.6) * 0.001; }); });
            if (birdsGroup) { birdsGroup.children.forEach(bird => { const ud = bird.userData; ud.currentAngle += ud.orbitSpeed; bird.position.x = Math.cos(ud.currentAngle) * ud.orbitRadius; bird.position.z = Math.sin(ud.currentAngle) * ud.orbitRadius; bird.position.y = ud.baseHeight + Math.sin(time * ud.verticalBobFrequency + ud.currentAngle) * ud.verticalBobAmplitude; birdLookAheadTarget.set( Math.cos(ud.currentAngle + ud.orbitSpeed * 10) * ud.orbitRadius, ud.baseHeight + Math.sin(time * ud.verticalBobFrequency + (ud.currentAngle + ud.orbitSpeed * 10)) * ud.verticalBobAmplitude, Math.sin(ud.currentAngle + ud.orbitSpeed * 10) * ud.orbitRadius ); bird.lookAt(birdLookAheadTarget); }); }
            islandGroup.children.forEach(child => { if (child.userData.swayInfo) { const sway = child.userData.swayInfo; if (child.userData.isTree) { const leaves = child.getObjectByName("leaves"); if (leaves) { leaves.rotation.z = Math.sin(time * sway.speed + sway.offset) * sway.amplitude; leaves.rotation.x = Math.cos(time * sway.speed * 0.7 + sway.offset + Math.PI / 3) * sway.amplitude * 0.6; } } else if (child.userData.isGrass) { child.children.forEach((blade, index) => { blade.rotation.z = Math.sin(time * sway.speed + sway.offset + index * 0.3) * sway.amplitude; blade.rotation.x = Math.cos(time * sway.speed * 0.7 + sway.offset + index * 0.3 + Math.PI / 3) * sway.amplitude * 0.6; }); } } });

            jewelClusters.forEach(cluster => {
                if (cluster) {
                    const pulse = Math.sin(time * 3 + cluster.id * 0.5) * 0.1 + 0.95; cluster.scale.set(pulse, pulse, pulse);
                    jewelMaterial.emissiveIntensity = 1.5 + Math.sin(time * 3) * 0.5; // Shared material emissive
                    if (cluster.userData.light) { cluster.userData.light.intensity = 3 + Math.sin(time * 3 + cluster.id * 0.5) * 1; }
                }
            });

            if (orbitControls) {
                isSliderUpdatingControls = true; // Prevent slider update from triggering this back
                orbitControls.update();
                isSliderUpdatingControls = false;
            }
            renderer.render(scene, camera);
        }
        init();
    </script>
</body>
</html>