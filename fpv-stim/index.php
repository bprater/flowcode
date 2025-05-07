<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FPV Drone Simulator - Acro Mode</title>
    <style>
        body { margin: 0; overflow: hidden; font-family: Arial, sans-serif; background-color: #333; color: #fff; }
        canvas { display: block; }
        #controls {
            position: absolute;
            top: 10px;
            left: 10px;
            background-color: rgba(0,0,0,0.7);
            padding: 15px;
            border-radius: 8px;
            color: #fff;
            max-width: 280px;
        }
        #controls h3 { margin-top: 0; margin-bottom: 10px; }
        #controls label {
            display: block;
            margin-bottom: 3px;
            font-size: 0.9em;
        }
        #controls input[type="range"] {
            width: 100%;
            margin-bottom: 10px;
        }
        #controls p {
            font-size: 0.8em;
            margin-top: 15px;
            line-height: 1.4;
        }
        .slider-value {
            font-size: 0.8em;
            color: #aaa;
            float: right;
        }
    </style>
</head>
<body>
    <div id="controls">
        <h3>Drone Controls (Acro Mode)</h3>
        <p>
            <b>W/S:</b> Pitch Forward/Backward<br>
            <b>A/D:</b> Roll Left/Right<br>
            <b>Space:</b> Thrust (relative to drone tilt)<br>
            <b>, (Comma) / . (Period):</b> Yaw Left/Right<br>
            <b>R:</b> Reset Drone
        </p>
        
        <h3>Settings</h3>
        <div>
            <label for="thrustSlider">Max Thrust: <span id="thrustValue" class="slider-value"></span></label>
            <input type="range" id="thrustSlider" min="10" max="150" value="60"> <!-- Increased max thrust -->
        </div>
        <div>
            <label for="pitchRateSlider">Pitch Rate: <span id="pitchRateValue" class="slider-value"></span></label>
            <input type="range" id="pitchRateSlider" min="0.5" max="5" step="0.1" value="2.5">
        </div>
        <div>
            <label for="rollRateSlider">Roll Rate: <span id="rollRateValue" class="slider-value"></span></label>
            <input type="range" id="rollRateSlider" min="0.5" max="5" step="0.1" value="2.8">
        </div>
        <div>
            <label for="yawRateSlider">Yaw Rate: <span id="yawRateValue" class="slider-value"></span></label>
            <input type="range" id="yawRateSlider" min="0.5" max="5" step="0.1" value="2">
        </div>
        <div>
            <label for="gravitySlider">Gravity: <span id="gravityValue" class="slider-value"></span></label>
            <input type="range" id="gravitySlider" min="0" max="50" step="0.1" value="9.8">
        </div>
        <div>
            <label for="linearDragSlider">Linear Drag: <span id="linearDragValue" class="slider-value"></span></label>
            <input type="range" id="linearDragSlider" min="0.05" max="5" step="0.05" value="0.5"> <!-- Lowered default linear drag -->
        </div>
        <div>
            <label for="angularDragSlider">Angular Drag: <span id="angularDragValue" class="slider-value"></span></label>
            <input type="range" id="angularDragSlider" min="0.1" max="15" step="0.1" value="3"> <!-- Increased angular drag importance -->
        </div>

        <h3>Environment</h3>
        <div>
            <label for="timeOfDaySlider">Time of Day: <span id="timeOfDayValue" class="slider-value"></span></label>
            <input type="range" id="timeOfDaySlider" min="0" max="24" step="0.1" value="10">
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>

    <script>
        let scene, camera, renderer, drone, clock;
        let directionalLight, ambientLight;

        let velocity = new THREE.Vector3();
        let angularVelocity = new THREE.Vector3(); // x: pitch, y: yaw, z: roll (local axes)
        
        let config = {
            maxThrust: 60,
            pitchRate: 2.5, // Radians per second^2 (approx, due to direct angVel manipulation)
            rollRate: 2.8,
            yawRate: 2,
            gravity: 9.8,
            linearDrag: 0.5,
            angularDrag: 3,
            timeOfDay: 10
        };

        const keys = {
            w: false, s: false, a: false, d: false,
            space: false, comma: false, period: false
        };

        const worldBounds = {
            minX: -150, maxX: 150,
            minZ: -150, maxZ: 150,
            minY: 0.2, maxY: 200 // Drone prop radius / ground, max height
        };
        const buildings = [];

        init();
        animate();

        function init() {
            clock = new THREE.Clock();
            scene = new THREE.Scene();
            scene.background = new THREE.Color(0x87ceeb);
            camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 0.1, 1000);
            
            renderer = new THREE.WebGLRenderer({ antialias: true });
            renderer.setSize(window.innerWidth, window.innerHeight);
            renderer.shadowMap.enabled = true;
            document.body.appendChild(renderer.domElement);

            ambientLight = new THREE.AmbientLight(0xffffff, 0.5);
            scene.add(ambientLight);
            directionalLight = new THREE.DirectionalLight(0xffffff, 1);
            directionalLight.castShadow = true;
            directionalLight.shadow.mapSize.width = 1024;
            directionalLight.shadow.mapSize.height = 1024;
            directionalLight.shadow.camera.near = 0.5;
            directionalLight.shadow.camera.far = 500;
            directionalLight.shadow.camera.left = -150;
            directionalLight.shadow.camera.right = 150;
            directionalLight.shadow.camera.top = 150;
            directionalLight.shadow.camera.bottom = -150;
            scene.add(directionalLight);

            drone = createDrone();
            resetDroneState();
            scene.add(drone);

            const groundGeometry = new THREE.PlaneGeometry(300, 300);
            const groundMaterial = new THREE.MeshStandardMaterial({ color: 0x556B2F, side: THREE.DoubleSide });
            const ground = new THREE.Mesh(groundGeometry, groundMaterial);
            ground.rotation.x = -Math.PI / 2;
            ground.receiveShadow = true;
            scene.add(ground);

            createBuildings();

            window.addEventListener('resize', onWindowResize, false);
            document.addEventListener('keydown', onKeyDown, false);
            document.addEventListener('keyup', onKeyUp, false);

            setupSliders();
            updateSunPosition();
        }

        function createDrone() {
            const droneGroup = new THREE.Group();
            droneGroup.propellers = []; // To store propeller meshes for animation

            const bodyGeometry = new THREE.BoxGeometry(1, 0.4, 1.5); // L, H, W
            const bodyMaterial = new THREE.MeshStandardMaterial({ color: 0x444444 });
            const body = new THREE.Mesh(bodyGeometry, bodyMaterial);
            body.castShadow = true;
            droneGroup.add(body);

            const propRadius = 0.4;
            const propHeight = 0.1;
            const propGeometry = new THREE.CylinderGeometry(propRadius, propRadius * 0.8, propHeight, 16); // Slightly tapered
            const propMaterial = new THREE.MeshStandardMaterial({ color: 0x222222 });

            const propPositions = [
                new THREE.Vector3(0.6, 0.2 + propHeight/2, 0.6),  // Front-right (X, Y, Z)
                new THREE.Vector3(-0.6, 0.2 + propHeight/2, 0.6), // Front-left
                new THREE.Vector3(0.6, 0.2 + propHeight/2, -0.6), // Rear-right
                new THREE.Vector3(-0.6, 0.2 + propHeight/2, -0.6) // Rear-left
            ];

            propPositions.forEach(pos => {
                const propeller = new THREE.Mesh(propGeometry, propMaterial);
                propeller.position.copy(pos);
                propeller.castShadow = true;
                droneGroup.add(propeller);
                droneGroup.propellers.push(propeller);
            });
            
            droneGroup.add(camera);
            camera.position.set(0, 0.8, 2.5); // Closer FPV: Y slightly above drone CG, Z behind
            camera.lookAt(new THREE.Vector3(0, 0.5, -3)); // Look forward and slightly down from camera position

            return droneGroup;
        }
        
        function resetDroneState() {
            drone.position.set(0, 5, 0);
            drone.quaternion.set(0,0,0,1); // Reset rotation using quaternion
            velocity.set(0, 0, 0);
            angularVelocity.set(0, 0, 0);
        }

        function createBuildings() {
            // (Identical to previous version)
            const numBuildings = 30;
            const buildingMaterial = new THREE.MeshStandardMaterial({ color: 0xaaaaaa });
            const buildingArea = 130; 

            for (let i = 0; i < numBuildings; i++) {
                const width = Math.random() * 10 + 5;
                const depth = Math.random() * 10 + 5;
                const height = Math.random() * 60 + 20;
                
                const buildingGeometry = new THREE.BoxGeometry(width, height, depth);
                const building = new THREE.Mesh(buildingGeometry, buildingMaterial);
                
                building.castShadow = true;
                building.receiveShadow = true;

                building.position.set(
                    (Math.random() - 0.5) * buildingArea * 2,
                    height / 2,
                    (Math.random() - 0.5) * buildingArea * 2
                );
                if (building.position.distanceTo(new THREE.Vector3(0,0,0)) < 20) {
                    i--; 
                    continue;
                }
                scene.add(building);
                buildings.push(building);
            }
        }

        function setupSliders() {
            const sliders = [
                { id: 'thrustSlider', configKey: 'maxThrust', valueId: 'thrustValue' },
                { id: 'pitchRateSlider', configKey: 'pitchRate', valueId: 'pitchRateValue' },
                { id: 'rollRateSlider', configKey: 'rollRate', valueId: 'rollRateValue' },
                { id: 'yawRateSlider', configKey: 'yawRate', valueId: 'yawRateValue' },
                { id: 'gravitySlider', configKey: 'gravity', valueId: 'gravityValue' },
                { id: 'linearDragSlider', configKey: 'linearDrag', valueId: 'linearDragValue' },
                { id: 'angularDragSlider', configKey: 'angularDrag', valueId: 'angularDragValue' },
                { id: 'timeOfDaySlider', configKey: 'timeOfDay', valueId: 'timeOfDayValue', callback: updateSunPosition }
            ];

            sliders.forEach(s => {
                const sliderElement = document.getElementById(s.id);
                const valueElement = document.getElementById(s.valueId);
                
                sliderElement.value = config[s.configKey];
                valueElement.textContent = config[s.configKey];

                sliderElement.addEventListener('input', (event) => {
                    config[s.configKey] = parseFloat(event.target.value);
                    valueElement.textContent = config[s.configKey];
                    if (s.callback) s.callback();
                });
            });
        }
        
        function updateSunPosition() {
            // (Identical to previous version, but I'll copy it for completeness)
            const angle = ((config.timeOfDay - 6) / 12) * Math.PI; 
            const sunDistance = 200;

            directionalLight.position.set(
                Math.cos(angle) * sunDistance,
                Math.sin(angle) * sunDistance,
                sunDistance / 3
            );
            directionalLight.position.y = Math.max(10, directionalLight.position.y);

            let skyColorHex = 0x87ceeb; 
            let ambientIntensity = 0.6;
            let directionalIntensity = 0.8;

            if (config.timeOfDay < 5 || config.timeOfDay > 19) { 
                skyColorHex = 0x0c1445; 
                ambientIntensity = 0.1;
                directionalIntensity = 0.1;
                 if (config.timeOfDay < 4 || config.timeOfDay > 21) { 
                    directionalIntensity = 0.0; 
                    ambientIntensity = 0.05;
                 }
            } else if (config.timeOfDay < 7 || config.timeOfDay > 17) { 
                skyColorHex = 0xffa500; 
                ambientIntensity = 0.3;
                directionalIntensity = 0.5;
                if (config.timeOfDay > 6 && config.timeOfDay < 7) { 
                     skyColorHex = 0xffb347; 
                } else if (config.timeOfDay > 17 && config.timeOfDay < 18) { 
                     skyColorHex = 0xff8c69; 
                }
            }
            scene.background.setHex(skyColorHex);
            ambientLight.intensity = ambientIntensity;
            directionalLight.intensity = directionalIntensity;
            document.getElementById('timeOfDayValue').textContent = `${Math.floor(config.timeOfDay)}:${String(Math.floor((config.timeOfDay % 1) * 60)).padStart(2, '0')}`;
        }


        function onKeyDown(event) {
            switch (event.key.toLowerCase()) {
                case 'w': keys.w = true; break;
                case 's': keys.s = true; break;
                case 'a': keys.a = true; break;
                case 'd': keys.d = true; break;
                case ' ': keys.space = true; break;
                case ',': keys.comma = true; break;
                case '.': keys.period = true; break;
                case 'r': resetDroneState(); break;
            }
        }

        function onKeyUp(event) {
            switch (event.key.toLowerCase()) {
                case 'w': keys.w = false; break;
                case 's': keys.s = false; break;
                case 'a': keys.a = false; break;
                case 'd': keys.d = false; break;
                case ' ': keys.space = false; break;
                case ',': keys.comma = false; break;
                case '.': keys.period = false; break;
            }
        }

        function onWindowResize() {
            camera.aspect = window.innerWidth / window.innerHeight;
            camera.updateProjectionMatrix();
            renderer.setSize(window.innerWidth, window.innerHeight);
        }
        
        function applyDronePhysics(deltaTime) {
            // --- Rotational Input (Pitch, Roll, Yaw) ---
            // These inputs directly affect angular velocity change this frame
            let pitchInput = 0;
            let rollInput = 0;
            let yawInput = 0;

            if (keys.w) pitchInput -= config.pitchRate; // Pitch nose down (rotates around local X-axis)
            if (keys.s) pitchInput += config.pitchRate; // Pitch nose up
            if (keys.a) rollInput += config.rollRate;   // Roll left (rotates around local Z-axis)
            if (keys.d) rollInput -= config.rollRate;   // Roll right
            if (keys.comma) yawInput += config.yawRate;    // Yaw left (rotates around local Y-axis)
            if (keys.period) yawInput -= config.yawRate;   // Yaw right
            
            // Add inputs to angular velocity
            angularVelocity.x += pitchInput * deltaTime;
            angularVelocity.z += rollInput * deltaTime; // Note: A/D controls roll around Z-axis
            angularVelocity.y += yawInput * deltaTime;


            // --- Apply angular drag ---
            // Higher drag makes it less "floaty" and easier to stop rotations
            angularVelocity.x *= Math.max(0, 1 - config.angularDrag * deltaTime);
            angularVelocity.y *= Math.max(0, 1 - config.angularDrag * deltaTime);
            angularVelocity.z *= Math.max(0, 1 - config.angularDrag * deltaTime);


            // --- Update drone rotation using quaternions ---
            const deltaRotationQuaternion = new THREE.Quaternion();
            // We create small rotations around local axes
            const qPitch = new THREE.Quaternion().setFromAxisAngle(new THREE.Vector3(1, 0, 0), angularVelocity.x * deltaTime);
            const qYaw = new THREE.Quaternion().setFromAxisAngle(new THREE.Vector3(0, 1, 0), angularVelocity.y * deltaTime);
            const qRoll = new THREE.Quaternion().setFromAxisAngle(new THREE.Vector3(0, 0, 1), angularVelocity.z * deltaTime);

            // Apply in order: Yaw, Pitch, Roll (local frame application)
            // drone.quaternion.multiply(qYaw); // Apply yaw first
            // drone.quaternion.multiply(qPitch); // Then pitch on new axes
            // drone.quaternion.multiply(qRoll);  // Then roll on new axes
            // Simpler combined local rotation application:
            deltaRotationQuaternion.setFromEuler(new THREE.Euler(
                angularVelocity.x * deltaTime,
                angularVelocity.y * deltaTime,
                angularVelocity.z * deltaTime,
                'YXZ' // Common aircraft/drone order: Yaw, then Pitch, then Roll locally
            ));
            drone.quaternion.multiplyQuaternions(deltaRotationQuaternion, drone.quaternion); // Pre-multiply for local
            drone.quaternion.normalize();


            // --- Forces ---
            const totalForce = new THREE.Vector3();

            // Thrust (Spacebar) - applied along drone's local "up" (Y-axis)
            if (keys.space) {
                const localThrustDirection = new THREE.Vector3(0, 1, 0); // Drone's local up
                const worldThrustDirection = localThrustDirection.clone().applyQuaternion(drone.quaternion);
                totalForce.addScaledVector(worldThrustDirection, config.maxThrust);

                // Propeller animation
                const propellerSpinSpeed = 25; 
                drone.propellers.forEach(prop => {
                    // Alternate spin direction for visual effect (optional)
                    // if (drone.propellers.indexOf(prop) % 2 === 0) {
                    //    prop.rotation.y += propellerSpinSpeed * deltaTime;
                    // } else {
                    //    prop.rotation.y -= propellerSpinSpeed * deltaTime;
                    // }
                    prop.rotation.y += propellerSpinSpeed * deltaTime;
                });
            } else {
                 // Idle spin (optional)
                // const idleSpinSpeed = 2;
                // drone.propellers.forEach(prop => {
                //     prop.rotation.y += idleSpinSpeed * deltaTime;
                // });
            }
            
            // Gravity (always world down)
            totalForce.y -= config.gravity;

            // --- Apply forces to velocity ---
            velocity.addScaledVector(totalForce, deltaTime);

            // --- Apply linear drag ---
            velocity.multiplyScalar(Math.max(0, 1 - config.linearDrag * deltaTime));


            // --- Update drone position ---
            drone.position.addScaledVector(velocity, deltaTime);
            
            // --- Boundary checks / Collision ---
            if (drone.position.y < worldBounds.minY) {
                drone.position.y = worldBounds.minY;
                velocity.y = Math.max(0, velocity.y * -0.2); // Softer bounce
                // Dampen other velocities on ground impact
                velocity.x *= 0.8;
                velocity.z *= 0.8;
                // Dampen angular velocity on crash
                angularVelocity.x *= 0.5;
                angularVelocity.y *= 0.5;
                angularVelocity.z *= 0.5;
            }
             if (drone.position.x < worldBounds.minX || drone.position.x > worldBounds.maxX ||
                drone.position.z < worldBounds.minZ || drone.position.z > worldBounds.maxZ ||
                drone.position.y > worldBounds.maxY) {
                
                if (drone.position.x < worldBounds.minX && velocity.x < 0) velocity.x = 0;
                if (drone.position.x > worldBounds.maxX && velocity.x > 0) velocity.x = 0;
                if (drone.position.z < worldBounds.minZ && velocity.z < 0) velocity.z = 0;
                if (drone.position.z > worldBounds.maxZ && velocity.z > 0) velocity.z = 0;
                if (drone.position.y > worldBounds.maxY && velocity.y > 0) velocity.y = 0;

                drone.position.x = THREE.MathUtils.clamp(drone.position.x, worldBounds.minX, worldBounds.maxX);
                drone.position.y = THREE.MathUtils.clamp(drone.position.y, worldBounds.minY, worldBounds.maxY);
                drone.position.z = THREE.MathUtils.clamp(drone.position.z, worldBounds.minZ, worldBounds.maxZ);
            }

            const droneBox = new THREE.Box3().setFromObject(drone);
            buildings.forEach(building => {
                const buildingBox = new THREE.Box3().setFromObject(building);
                if (droneBox.intersectsBox(buildingBox)) {
                    // Rudimentary collision response: Stop and small bounce
                    // Calculate rough penetration depth/normal (very simplified)
                    const centerDrone = new THREE.Vector3(); droneBox.getCenter(centerDrone);
                    const centerBuilding = new THREE.Vector3(); buildingBox.getCenter(centerBuilding);
                    const toBuilding = centerBuilding.clone().sub(centerDrone).normalize();

                    // Push drone back slightly opposite to collision direction
                    drone.position.addScaledVector(toBuilding, -0.1); // Small pushback
                    velocity.reflect(toBuilding).multiplyScalar(0.1); // Bounce with damping

                    // Dampen angular velocity on crash
                    angularVelocity.multiplyScalar(0.5);
                }
            });
        }

        function animate() {
            requestAnimationFrame(animate);
            const deltaTime = Math.min(clock.getDelta(), 0.1); // Cap delta to prevent physics explosions
            
            applyDronePhysics(deltaTime);
            
            renderer.render(scene, camera);
        }

    </script>
</body>
</html>