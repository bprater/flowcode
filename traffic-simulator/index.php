<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Traffic Simulator - Grid Map</title>
    <style>
        body {
            font-family: sans-serif;
            margin: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            background-color: #f0f0f0;
            padding-top: 10px;
            color: #333;
        }

        .controls {
            margin-bottom: 10px;
            padding: 10px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.15);
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
            justify-content: center;
        }

        .controls button {
            padding: 8px 15px;
            border: none;
            background-color: #007bff;
            color: white;
            cursor: pointer;
            border-radius: 5px;
            font-size: 14px;
            transition: background-color 0.2s ease;
        }

        .controls button:hover {
            background-color: #0056b3;
        }
        
        .controls button:active {
            background-color: #004085;
        }

        .controls p {
            margin: 0 10px;
            font-size: 14px;
        }

        canvas {
            border: 1px solid #ccc; 
            background-color: #e0e0e0; 
            display: block;
            margin: 0 auto;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="controls">
        <button id="addVehiclesBtn">Add 5 Vehicles</button>
        <button id="removeVehicleBtn">Remove Vehicle</button>
        <button id="speedUpBtn">Speed Up</button>
        <button id="speedDownBtn">Speed Down</button>
        <button id="newMapBtn">New Grid Map</button>
        <p>Vehicles: <span id="vehicleCountDisplay">0</span></p>
        <p>Speed: <span id="speedMultiplierDisplay">1.0</span>x</p>
    </div>
    <canvas id="trafficCanvas"></canvas>

    <script>
        // --- Setup ---
        const canvas = document.getElementById('trafficCanvas');
        const ctx = canvas.getContext('2d');

        const vehicleCountDisplayEl = document.getElementById('vehicleCountDisplay');
        const speedMultiplierDisplayEl = document.getElementById('speedMultiplierDisplay');

        canvas.width = 1000;
        canvas.height = 700;

        // --- Constants & Globals ---
        const LANE_WIDTH = 20;
        const VEHICLE_WIDTH = 8;
        const VEHICLE_HEIGHT = 14; 
        const INTERSECTION_RADIUS = LANE_WIDTH * 1.2; 

        let globalSpeedMultiplier = 1.0;
        let vehicles = [];
        let roads = [];
        let intersections = [];
        let nextVehicleId = 0;
        let nextIntersectionId = 0;
        let nextRoadId = 0;
        let spawnInterval = 1000; 
        let lastSpawnTime = 0;
        let maxVehicles = 60; // Increased max vehicles for potentially larger grids


        const COLORS = {
            ROAD: '#777', 
            LANE_DIVIDER_WHITE: '#f0f0f0',
            LANE_DIVIDER_YELLOW: '#FFD700',
            VEHICLE_BLUE: 'rgba(0, 123, 255, 0.95)',
            VEHICLE_GREEN: 'rgba(40, 167, 69, 0.95)',
            VEHICLE_RED: 'rgba(220, 53, 69, 0.95)',
            VEHICLE_YELLOW: 'rgba(255, 193, 7, 0.95)',
            VEHICLE_PURPLE: 'rgba(108, 52, 131, 0.95)',
            INTERSECTION: '#999',
            STOP_SIGN_BG: '#dc3545',
            STOP_SIGN_TEXT: '#fff',
            TRAFFIC_LIGHT_RED: '#dc3545',
            TRAFFIC_LIGHT_YELLOW: '#ffc107',
            TRAFFIC_LIGHT_GREEN: '#28a745',
            TRAFFIC_LIGHT_OFF: '#555',
            TRAFFIC_LIGHT_POLE: '#444'
        };
        const VEHICLE_COLORS = [COLORS.VEHICLE_BLUE, COLORS.VEHICLE_GREEN, COLORS.VEHICLE_RED, COLORS.VEHICLE_YELLOW, COLORS.VEHICLE_PURPLE];

        // --- Helper Functions ---
        function dist(p1, p2) {
            return Math.sqrt((p1.x - p2.x)**2 + (p1.y - p2.y)**2);
        }

        function randomInt(min, max) {
            return Math.floor(Math.random() * (max - min + 1)) + min;
        }

        function randomChoice(arr) {
            if (!arr || arr.length === 0) return undefined;
            return arr[Math.floor(Math.random() * arr.length)];
        }

        // --- Classes (Intersection, TrafficLight, Road, Vehicle - Keep these exactly as in the previous complete code) ---
        class Intersection {
            constructor(x, y, controlType = null) { 
                this.id = `I${nextIntersectionId++}`;
                this.x = x;
                this.y = y;
                this.radius = INTERSECTION_RADIUS;
                this.connectedRoads = []; 
                this.controlType = controlType;
                this.trafficLight = null;

                if (this.controlType === 'light') {
                    this.trafficLight = new TrafficLight(this);
                }
            }

            addRoad(road) {
                this.connectedRoads.push(road);
                if (this.trafficLight) {
                    this.trafficLight.registerRoad(road);
                }
            }

            draw() {
                ctx.fillStyle = COLORS.INTERSECTION;
                ctx.beginPath();
                ctx.arc(this.x, this.y, this.radius, 0, Math.PI * 2);
                ctx.fill();

                if (this.controlType === 'stop') {
                    const signSize = 12;
                    // Simple stop sign at center, real one would be per approach
                    ctx.fillStyle = COLORS.STOP_SIGN_BG;
                    ctx.beginPath();
                    for (let i = 0; i < 8; i++) { 
                        const angle = (Math.PI / 4) * i - (Math.PI / 8);
                        const xPos = this.x + signSize * Math.cos(angle);
                        const yPos = this.y + signSize * Math.sin(angle);
                        if (i === 0) ctx.moveTo(xPos, yPos);
                        else ctx.lineTo(xPos, yPos);
                    }
                    ctx.closePath();
                    ctx.fill();
                    ctx.fillStyle = COLORS.STOP_SIGN_TEXT;
                    ctx.font = `bold ${signSize * 0.6}px Arial`;
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'middle';
                    ctx.fillText('STOP', this.x, this.y);

                } else if (this.trafficLight) {
                    this.trafficLight.draw();
                }
            }

            update(deltaTime) {
                if (this.trafficLight) {
                    this.trafficLight.update(deltaTime);
                }
            }
        }

        class TrafficLight {
            constructor(intersection) {
                this.intersection = intersection;
                this.roadStates = new Map(); 
                this.cycleDuration = 12000; 
                this.yellowDuration = 2000;  
                this.allRedDuration = 500;   
                this.timer = 0;
                this.currentPhaseIndex = 0;
                this.phases = []; 
                this.initialized = false;
                this.currentPhaseState = 'GREEN'; 
            }

            registerRoad(road) {
                const approachNodeId = (road.startNode.id === this.intersection.id) ? road.startNode.id : road.endNode.id;
                this.roadStates.set(road.id, { state: 'RED', approachNodeId: approachNodeId });
            }

            initializePhases() {
                if (this.initialized || this.intersection.connectedRoads.length === 0) return;
                this.phases = []; 

                const connRoads = [...this.intersection.connectedRoads]; 
                if (connRoads.length === 0) return;

                connRoads.sort((a, b) => {
                    const getAngle = (road) => {
                        const otherNode = (road.startNode.id === this.intersection.id) ? road.endNode : road.startNode;
                        return Math.atan2(otherNode.y - this.intersection.y, otherNode.x - this.intersection.x);
                    };
                    return getAngle(a) - getAngle(b);
                });
                
                let usedRoadIds = new Set();
                for (let i = 0; i < connRoads.length; i++) {
                    if (usedRoadIds.has(connRoads[i].id)) continue;

                    let currentPhaseRoads = [connRoads[i].id];
                    usedRoadIds.add(connRoads[i].id);

                    const angle1 = Math.atan2(
                        (connRoads[i].startNode.id === this.intersection.id ? connRoads[i].endNode.y : connRoads[i].startNode.y) - this.intersection.y,
                        (connRoads[i].startNode.id === this.intersection.id ? connRoads[i].endNode.x : connRoads[i].startNode.x) - this.intersection.x
                    );

                    for (let j = i + 1; j < connRoads.length; j++) {
                        if (usedRoadIds.has(connRoads[j].id)) continue;
                        const angle2 = Math.atan2(
                             (connRoads[j].startNode.id === this.intersection.id ? connRoads[j].endNode.y : connRoads[j].startNode.y) - this.intersection.y,
                             (connRoads[j].startNode.id === this.intersection.id ? connRoads[j].endNode.x : connRoads[j].startNode.x) - this.intersection.x
                        );
                        if (Math.abs(Math.abs(angle1 - angle2) - Math.PI) < 0.5) { 
                            currentPhaseRoads.push(connRoads[j].id);
                            usedRoadIds.add(connRoads[j].id);
                            break; 
                        }
                    }
                    if (currentPhaseRoads.length > 0) {
                         const numEffectivePhases = Math.ceil(connRoads.length / 2); // Approximation
                         const phaseDuration = (this.cycleDuration / numEffectivePhases) - this.yellowDuration - this.allRedDuration;
                        this.phases.push({ 
                            activeRoads: currentPhaseRoads, 
                            duration: Math.max(1000, phaseDuration) // Ensure at least 1s green
                        });
                    }
                }


                if (this.phases.length > 0) {
                    this.setPhase(0);
                }
                this.initialized = true;
            }
            
            setPhase(phaseIndex) {
                if (this.phases.length === 0) return;
                this.currentPhaseIndex = phaseIndex % this.phases.length;
                const phase = this.phases[this.currentPhaseIndex];
                
                this.roadStates.forEach(status => status.state = 'RED'); 

                phase.activeRoads.forEach(roadId => {
                    if (this.roadStates.has(roadId)) {
                        this.roadStates.get(roadId).state = 'GREEN';
                    }
                });
                this.timer = 0;
                this.currentPhaseState = 'GREEN';
            }

            update(deltaTime) {
                if (!this.initialized) {
                    this.initializePhases();
                    if (!this.initialized || this.phases.length === 0) return;
                }

                this.timer += deltaTime;
                const currentPhaseDef = this.phases[this.currentPhaseIndex];
                 if (!currentPhaseDef) { // Safety check if phases are empty after init
                    this.initialized = false; // Force re-init next tick
                    return;
                }


                if (this.currentPhaseState === 'GREEN' && this.timer >= currentPhaseDef.duration) {
                    currentPhaseDef.activeRoads.forEach(roadId => {
                        if (this.roadStates.has(roadId) && this.roadStates.get(roadId).state === 'GREEN') {
                            this.roadStates.get(roadId).state = 'YELLOW';
                        }
                    });
                    this.currentPhaseState = 'YELLOW';
                    this.timer = 0; 
                } else if (this.currentPhaseState === 'YELLOW' && this.timer >= this.yellowDuration) {
                    this.roadStates.forEach(status => status.state = 'RED');
                    this.currentPhaseState = 'ALL_RED';
                    this.timer = 0; 
                } else if (this.currentPhaseState === 'ALL_RED' && this.timer >= this.allRedDuration) {
                    this.setPhase(this.currentPhaseIndex + 1);
                }
            }

            getLightStateForApproach(approachingRoadId, vehicleIsAtIntersectionNodeId) {
                if (!this.initialized || !this.roadStates.has(approachingRoadId)) {
                    return 'GREEN'; 
                }
                const roadInfo = this.roadStates.get(approachingRoadId);
                if (roadInfo.approachNodeId === vehicleIsAtIntersectionNodeId) {
                     return roadInfo.state;
                }
                return 'RED'; 
            }

            draw() {
                this.intersection.connectedRoads.forEach(road => {
                    const roadStateInfo = this.roadStates.get(road.id);
                    if (!roadStateInfo) return;

                    const isStartNodeThisIntersection = road.startNode.id === this.intersection.id;
                    const approachAtThisIntersectionNodeId = isStartNodeThisIntersection ? road.startNode.id : road.endNode.id;

                    if (roadStateInfo.approachNodeId !== approachAtThisIntersectionNodeId) return;
                    
                    const lightState = roadStateInfo.state;
                    let color = COLORS.TRAFFIC_LIGHT_OFF;
                    if (lightState === 'GREEN') color = COLORS.TRAFFIC_LIGHT_GREEN;
                    else if (lightState === 'YELLOW') color = COLORS.TRAFFIC_LIGHT_YELLOW;
                    else if (lightState === 'RED') color = COLORS.TRAFFIC_LIGHT_RED;

                    const otherNode = isStartNodeThisIntersection ? road.endNode : road.startNode;
                    const dx = otherNode.x - this.intersection.x; 
                    const dy = otherNode.y - this.intersection.y;
                    const len = Math.sqrt(dx*dx + dy*dy);
                    if (len === 0) return;

                    const lightX = this.intersection.x + (dx/len) * (this.intersection.radius + 8);
                    const lightY = this.intersection.y + (dy/len) * (this.intersection.radius + 8);

                    ctx.fillStyle = COLORS.TRAFFIC_LIGHT_POLE;
                    ctx.fillRect(lightX - 2, lightY - 7, 4, 14);
                    ctx.fillStyle = color;
                    ctx.beginPath();
                    ctx.arc(lightX, lightY, 5, 0, Math.PI * 2);
                    ctx.fill();
                });
            }
        }

        class Road {
            constructor(startNode, endNode, numLanesPerDirection = 1) {
                this.id = `R${nextRoadId++}`;
                this.startNode = startNode; 
                this.endNode = endNode;     
                this.numLanesPerDirection = numLanesPerDirection;
                this.length = dist(startNode, endNode);

                const dx = endNode.x - startNode.x;
                const dy = endNode.y - startNode.y;
                this.angle = Math.atan2(dy, dx);
                this.perpDx = -Math.sin(this.angle); 
                this.perpDy = Math.cos(this.angle);  

                this.lanes = []; 
                this._calculateLanes();
                startNode.addRoad(this);
                endNode.addRoad(this);
            }

            _calculateLanes() {
                const totalRoadHalfWidth = this.numLanesPerDirection * LANE_WIDTH;

                for (let i = 0; i < this.numLanesPerDirection; i++) {
                    const offsetDist = (i + 0.5) * LANE_WIDTH - totalRoadHalfWidth / 2; 
                    
                    this.lanes.push({ 
                        id: `${this.id}-F${i}`, roadId: this.id,
                        path: [
                            { x: this.startNode.x + offsetDist * this.perpDx, y: this.startNode.y + offsetDist * this.perpDy },
                            { x: this.endNode.x + offsetDist * this.perpDx, y: this.endNode.y + offsetDist * this.perpDy }
                        ],
                        startNodeId: this.startNode.id, endNodeId: this.endNode.id
                    });
                    this.lanes.push({ 
                        id: `${this.id}-B${i}`, roadId: this.id,
                        path: [ 
                            { x: this.endNode.x - offsetDist * this.perpDx, y: this.endNode.y - offsetDist * this.perpDy },
                            { x: this.startNode.x - offsetDist * this.perpDx, y: this.startNode.y - offsetDist * this.perpDy }
                        ],
                        startNodeId: this.endNode.id, endNodeId: this.startNode.id   
                    });
                }
            }

            draw() {
                ctx.strokeStyle = COLORS.ROAD;
                ctx.lineWidth = this.numLanesPerDirection * 2 * LANE_WIDTH; 
                ctx.beginPath();
                ctx.moveTo(this.startNode.x, this.startNode.y);
                ctx.lineTo(this.endNode.x, this.endNode.y);
                ctx.stroke();

                ctx.strokeStyle = COLORS.LANE_DIVIDER_YELLOW;
                ctx.lineWidth = 2;
                ctx.setLineDash([]); 
                ctx.beginPath();
                ctx.moveTo(this.startNode.x, this.startNode.y);
                ctx.lineTo(this.endNode.x, this.endNode.y);
                ctx.stroke();

                ctx.strokeStyle = COLORS.LANE_DIVIDER_WHITE;
                ctx.lineWidth = 1;
                ctx.setLineDash([6, 4]);
                this.lanes.forEach(lane => { 
                    ctx.beginPath();
                    ctx.moveTo(lane.path[0].x, lane.path[0].y);
                    ctx.lineTo(lane.path[1].x, lane.path[1].y);
                    ctx.stroke();
                });
                ctx.setLineDash([]); 
            }
        }

        class Vehicle {
            constructor(startLane) { 
                this.id = `V${nextVehicleId++}`;
                this.currentLane = startLane;
                this.x = startLane.path[0].x;
                this.y = startLane.path[0].y;
                this.width = VEHICLE_WIDTH;
                this.height = VEHICLE_HEIGHT;
                this.color = randomChoice(VEHICLE_COLORS);

                this.angle = Math.atan2(
                    this.currentLane.path[1].y - this.currentLane.path[0].y,
                    this.currentLane.path[1].x - this.currentLane.path[0].x
                );
                
                this.baseTargetSpeed = (1.2 + Math.random() * 0.8); 
                this.targetSpeed = this.baseTargetSpeed * globalSpeedMultiplier;
                this.speed = 0; 
                this.maxSpeed = this.targetSpeed * 1.25; 
                this.acceleration = 0.025 * globalSpeedMultiplier;
                this.deceleration = 0.04 * globalSpeedMultiplier; 
                this.emergencyDeceleration = 0.12 * globalSpeedMultiplier; 

                this.progress = 0; 
                this.targetIntersection = intersections.find(i => i.id === this.currentLane.endNodeId);
                this.stopped = false;
                this.isStoppingForControl = false;
                this.waitingAtStopSignTimer = 0; 
                this.stopSignWaitDuration = 1200 + Math.random() * 1200; 
                this.timeSinceSpawn = 0; 
                this.stuckTimer = 0; 
                this.lastPosition = {x: this.x, y: this.y};
            }

            update(deltaTime, allVehicles) {
                this.timeSinceSpawn += deltaTime;
                const dtFactor = deltaTime / (1000/60); 
                if (dtFactor <= 0 || isNaN(dtFactor) || dtFactor > 5) return; 


                this.targetSpeed = this.baseTargetSpeed * globalSpeedMultiplier;
                this.maxSpeed = this.targetSpeed * 1.25;
                this.acceleration = 0.025 * globalSpeedMultiplier;
                this.deceleration = 0.04 * globalSpeedMultiplier;
                this.emergencyDeceleration = 0.12 * globalSpeedMultiplier;


                if (this.stopped) {
                    this.speed = 0;
                    if (this.waitingAtStopSignTimer > 0) {
                        this.waitingAtStopSignTimer -= deltaTime;
                        if (this.waitingAtStopSignTimer <= 0) {
                            this.stopped = false; 
                            this.isStoppingForControl = false;
                        } else {
                            return; 
                        }
                    }
                }
                
                const distToTargetIntersection = this.targetIntersection ? dist({x: this.x, y: this.y}, this.targetIntersection) : Infinity;
                const stoppingDistance = (this.speed * this.speed) / (2 * this.emergencyDeceleration) + this.height * 1.5; 

                let shouldBeStoppingForControl = false;
                let isApproachingControl = this.targetIntersection && (distToTargetIntersection < Math.max(70, stoppingDistance + this.height));

                if (isApproachingControl && this.targetIntersection) {
                    if (this.targetIntersection.controlType === 'light' && this.targetIntersection.trafficLight) {
                        const lightState = this.targetIntersection.trafficLight.getLightStateForApproach(this.currentLane.roadId, this.targetIntersection.id);
                        if (lightState === 'RED' || (lightState === 'YELLOW' && distToTargetIntersection < stoppingDistance + 15)) {
                            shouldBeStoppingForControl = true;
                        }
                    } else if (this.targetIntersection.controlType === 'stop') {
                        if (distToTargetIntersection < this.height * 3 && !this.stopped && this.waitingAtStopSignTimer <=0) {
                             shouldBeStoppingForControl = true;
                             this.waitingAtStopSignTimer = this.stopSignWaitDuration; 
                        } else if (this.stopped && this.waitingAtStopSignTimer > 0) { 
                            shouldBeStoppingForControl = true; 
                        }
                    }
                }
                
                this.isStoppingForControl = shouldBeStoppingForControl;

                if (this.isStoppingForControl && distToTargetIntersection < stoppingDistance) {
                    this.speed = Math.max(0, this.speed - this.emergencyDeceleration * dtFactor); 
                    if (this.speed <= 0.01 && !this.stopped) { 
                        this.speed = 0;
                        this.stopped = true;
                    }
                } else if (this.stopped && !this.isStoppingForControl && this.waitingAtStopSignTimer <=0) {
                    this.stopped = false;
                }

                let vehicleInFrontFactor = 1.0; 
                let desiredSpeed = this.targetSpeed;

                if (!this.stopped && !this.isStoppingForControl) { 
                    for (const other of allVehicles) {
                        if (other === this || other.currentLane.id !== this.currentLane.id) continue;

                        const d = dist({x: this.x, y: this.y}, {x: other.x, y: other.y});
                        const sightDistance = this.speed * 12 + this.height * 4; 

                        if (d < sightDistance) {
                            const dxLane = this.currentLane.path[1].x - this.currentLane.path[0].x;
                            const dyLane = this.currentLane.path[1].y - this.currentLane.path[0].y;
                            const dxToOther = other.x - this.x;
                            const dyToOther = other.y - this.y;

                            if ((dxToOther * dxLane + dyToOther * dyLane) > 0) { 
                                if (d < this.height * 2) { 
                                    desiredSpeed = Math.min(desiredSpeed, Math.max(0, other.speed * 0.7));
                                    vehicleInFrontFactor = 0.2; 
                                } else if (d < this.height * 4) { 
                                    desiredSpeed = Math.min(desiredSpeed, other.speed * 0.9);
                                    vehicleInFrontFactor = 0.6;
                                } else { 
                                    desiredSpeed = Math.min(desiredSpeed, other.speed);
                                    vehicleInFrontFactor = 0.9;
                                }
                                break;
                            }
                        }
                    }
                }

                if (!this.stopped && !this.isStoppingForControl) {
                    if (this.speed < desiredSpeed) {
                        this.speed = Math.min(desiredSpeed, this.speed + this.acceleration * vehicleInFrontFactor * dtFactor);
                    } else if (this.speed > desiredSpeed) {
                        this.speed = Math.max(desiredSpeed, this.speed - (vehicleInFrontFactor < 1.0 ? this.emergencyDeceleration : this.deceleration) * dtFactor);
                    }
                }
                this.speed = Math.min(this.speed, this.maxSpeed);
                this.speed = Math.max(0, this.speed); 

                if (this.speed > 0) {
                    const moveDistance = this.speed * dtFactor; 
                    const laneVecX = this.currentLane.path[1].x - this.currentLane.path[0].x;
                    const laneVecY = this.currentLane.path[1].y - this.currentLane.path[0].y;
                    const laneLen = dist(this.currentLane.path[0], this.currentLane.path[1]);

                    if (laneLen > 0.1) { 
                        this.x += (laneVecX / laneLen) * moveDistance;
                        this.y += (laneVecY / laneLen) * moveDistance;
                        this.progress = dist(this.currentLane.path[0], {x: this.x, y: this.y}) / laneLen;
                    } else { 
                        this.progress = 1.0; 
                    }
                }
                
                if (dist(this, this.lastPosition) < 0.1 && this.speed < 0.1 && this.timeSinceSpawn > 3000) {
                    this.stuckTimer += deltaTime;
                } else {
                    this.stuckTimer = 0;
                }
                this.lastPosition = {x: this.x, y: this.y};
                if (this.stuckTimer > 5000) { 
                     this.despawn(); return;
                }


                const effectiveReach = this.speed * dtFactor + this.height * 0.6; 
                if (this.targetIntersection && (this.progress >= 0.99 || distToTargetIntersection < effectiveReach) ) {
                    if (this.stopped && (this.isStoppingForControl || this.waitingAtStopSignTimer > 0)) {
                        const laneVecX = this.currentLane.path[1].x - this.currentLane.path[0].x;
                        const laneVecY = this.currentLane.path[1].y - this.currentLane.path[0].y;
                        const laneLen = dist(this.currentLane.path[0], this.currentLane.path[1]);
                        if (laneLen > 0.1) {
                             const snapBackDist = this.height * 0.5 + INTERSECTION_RADIUS * 0.1;
                             this.x = this.currentLane.path[1].x - (laneVecX / laneLen) * snapBackDist;
                             this.y = this.currentLane.path[1].y - (laneVecY / laneLen) * snapBackDist;
                        }
                        this.progress = laneLen > 0.1 ? dist(this.currentLane.path[0], {x:this.x, y:this.y}) / laneLen : 1.0;
                        return; 
                    }
                    
                    const currentRoad = roads.find(r => r.id === this.currentLane.roadId);
                    if (!currentRoad) { this.despawn(); return; }

                    const possibleNextRoads = this.targetIntersection.connectedRoads.filter(r => 
                        r.id !== currentRoad.id || this.targetIntersection.connectedRoads.length === 1 
                    );

                    if (possibleNextRoads.length > 0) {
                        const nextRoad = randomChoice(possibleNextRoads);
                        const nextLanes = nextRoad.lanes.filter(l => l.startNodeId === this.targetIntersection.id);

                        if (nextLanes.length > 0) {
                            this.currentLane = randomChoice(nextLanes);
                            this.x = this.currentLane.path[0].x;
                            this.y = this.currentLane.path[0].y;
                            this.angle = Math.atan2(
                                this.currentLane.path[1].y - this.currentLane.path[0].y,
                                this.currentLane.path[1].x - this.currentLane.path[0].x
                            );
                            this.targetIntersection = intersections.find(i => i.id === this.currentLane.endNodeId);
                            this.progress = 0;
                            this.stopped = false; 
                            this.isStoppingForControl = false;
                            this.waitingAtStopSignTimer = 0;
                            this.stuckTimer = 0;
                        } else {
                             if (this.timeSinceSpawn > 3000) this.despawn(); 
                        }
                    } else {
                         if (this.timeSinceSpawn > 3000) this.despawn(); 
                    }
                }
            }

            draw() {
                ctx.save();
                ctx.translate(this.x, this.y);
                ctx.rotate(this.angle + Math.PI / 2); 
                ctx.fillStyle = this.color;
                ctx.beginPath(); 
                const r = this.width / 3; 
                const w = this.width;
                const h = this.height;
                ctx.moveTo(-w/2 + r, -h/2);
                ctx.arcTo(w/2, -h/2, w/2, -h/2 + r, r);
                ctx.arcTo(w/2, h/2, w/2 -r, h/2, r);
                ctx.arcTo(-w/2, h/2, -w/2, h/2 -r, r);
                ctx.arcTo(-w/2, -h/2, -w/2 + r, -h/2, r);
                ctx.closePath();
                ctx.fill();
                
                ctx.fillStyle = 'rgba(200,220,255,0.6)';
                ctx.fillRect(-this.width/2 * 0.8, -this.height/2 * 0.8, this.width * 0.8, this.height * 0.3);
                ctx.restore();

                if (this.isStoppingForControl && this.speed < 0.5) { 
                    ctx.fillStyle = 'rgba(255,100,0,0.4)'; 
                    ctx.beginPath();
                    ctx.arc(this.x, this.y, this.height * 0.55, 0, Math.PI*2);
                    ctx.fill();
                }
            }

            despawn() {
                vehicles = vehicles.filter(v => v.id !== this.id);
            }
        }

        // --- NEW MAP GENERATION FUNCTION ---
        function generateGridMap(gridCols = 6, gridRows = 4, cellWidth = 160, cellHeight = 130, roadConnectionProbability = 0.95) {
            intersections = [];
            roads = [];
            vehicles = [];
            nextIntersectionId = 0;
            nextRoadId = 0;
            nextVehicleId = 0; // Reset for new map

            const totalGridWidth = (gridCols - 1) * cellWidth;
            const totalGridHeight = (gridRows - 1) * cellHeight;
            const offsetX = Math.max(50, (canvas.width - totalGridWidth) / 2);   
            const offsetY = Math.max(50, (canvas.height - totalGridHeight) / 2); 

            const gridIntersections = []; 
            for (let r = 0; r < gridRows; r++) {
                gridIntersections[r] = [];
                for (let c = 0; c < gridCols; c++) {
                    const x = offsetX + c * cellWidth;
                    const y = offsetY + r * cellHeight;

                    let controlType = null;
                    // More likely to have controls at non-edge intersections
                    if (gridCols > 2 && gridRows > 2 && r > 0 && r < gridRows - 1 && c > 0 && c < gridCols - 1) {
                        const rand = Math.random();
                        if (rand < 0.10) controlType = 'stop'; // Fewer stop signs
                        else if (rand < 0.35) controlType = 'light';
                    } else if (Math.random() < 0.1) { // Small chance for controls on edges too
                         controlType = randomChoice(['stop', 'light']);
                    }
                    const intersection = new Intersection(x, y, controlType);
                    intersections.push(intersection);
                    gridIntersections[r][c] = intersection;
                }
            }

            for (let r = 0; r < gridRows; r++) {
                for (let c = 0; c < gridCols; c++) {
                    const currentIntersection = gridIntersections[r][c];

                    if (c < gridCols - 1) { // Connect to right
                        if (Math.random() < roadConnectionProbability) {
                            const rightIntersection = gridIntersections[r][c + 1];
                            roads.push(new Road(currentIntersection, rightIntersection, randomChoice([1, 1, 2])));
                        }
                    }

                    if (r < gridRows - 1) { // Connect to bottom
                         if (Math.random() < roadConnectionProbability) {
                            const bottomIntersection = gridIntersections[r + 1][c];
                            roads.push(new Road(currentIntersection, bottomIntersection, randomChoice([1, 1, 2])));
                        }
                    }
                }
            }
            
            if (roads.length === 0 && intersections.length >= 2) {
                let n1 = intersections[0];
                let n2 = intersections[1];
                if (intersections.length > 1) { // Ensure n2 is different from n1
                    for(let i = 1; i < intersections.length; i++) {
                        if(intersections[i].id !== n1.id) { n2 = intersections[i]; break; }
                    }
                }
                if(n1.id !== n2.id) roads.push(new Road(n1, n2));
            }
            
            intersections.forEach(i => {
                if (i.trafficLight) i.trafficLight.initializePhases();
            });
        }
        
        function spawnVehicle() {
            if (vehicles.length >= maxVehicles || roads.length === 0 || intersections.length < 2) return false;
        
            let startLane = null;
            let attempts = 0;
            while (attempts < 20 && !startLane) {
                const randomRoad = randomChoice(roads);
                if (!randomRoad) { attempts++; continue; }

                const potentialStartNode = randomChoice([randomRoad.startNode, randomRoad.endNode]);
                // Heuristic: prefer intersections with fewer connections (more likely "edge" nodes)
                if (potentialStartNode.connectedRoads.length <= 2 || (potentialStartNode.connectedRoads.length === 1 && Math.random() < 0.7) ) { 
                    const candidateLanes = randomRoad.lanes.filter(l => l.startNodeId === potentialStartNode.id);
                    if (candidateLanes.length > 0) {
                        startLane = randomChoice(candidateLanes);
                    }
                }
                attempts++;
            }

            if (!startLane) { 
                const randomRoad = randomChoice(roads);
                if (randomRoad && randomRoad.lanes.length > 0) {
                    startLane = randomChoice(randomRoad.lanes);
                }
            }

            if (startLane) {
                let occupied = false;
                for (const v of vehicles) { 
                    if (dist({x: v.x, y: v.y}, startLane.path[0]) < VEHICLE_HEIGHT * 1.5) {
                        occupied = true;
                        break;
                    }
                }
                if (!occupied) {
                    vehicles.push(new Vehicle(startLane));
                    return true;
                }
            }
            return false;
        }

        function addMultipleVehicles(count = 5) {
            for (let i = 0; i < count; i++) {
                spawnVehicle();
            }
        }
        
        function removeSingleVehicle() {
            if (vehicles.length > 0) {
                vehicles.pop(); 
            }
        }

        function updateGlobalSpeed(change) {
            globalSpeedMultiplier = Math.max(0.1, Math.min(3.0, globalSpeedMultiplier + change));
            globalSpeedMultiplier = parseFloat(globalSpeedMultiplier.toFixed(1));
            speedMultiplierDisplayEl.textContent = globalSpeedMultiplier.toFixed(1);
        }

        // --- UI Event Listeners ---
        document.getElementById('addVehiclesBtn').addEventListener('click', () => addMultipleVehicles(5));
        document.getElementById('removeVehicleBtn').addEventListener('click', removeSingleVehicle);
        document.getElementById('speedUpBtn').addEventListener('click', () => updateGlobalSpeed(0.2));
        document.getElementById('speedDownBtn').addEventListener('click', () => updateGlobalSpeed(-0.2));
        document.getElementById('newMapBtn').addEventListener('click', () => {
            generateGridMap(randomInt(4, 7), randomInt(3, 5), 150 + randomInt(-10, 10), 130 + randomInt(-10,10), 0.9 + Math.random()*0.1); 
            addMultipleVehicles(randomInt(10,20)); 
        });


        // --- Main Loop ---
        let lastTimestamp = 0;
        function gameLoop(timestamp) {
            const deltaTime = timestamp - lastTimestamp; 
            lastTimestamp = timestamp;

            if (isNaN(deltaTime) || deltaTime <= 0 ) { 
                 requestAnimationFrame(gameLoop);
                 return;
            }
            const effectiveDeltaTime = Math.min(deltaTime, 50); 

            if (timestamp - lastSpawnTime > spawnInterval && vehicles.length < maxVehicles / 1.5) { // Spawn more readily
                if(spawnVehicle()) {
                    lastSpawnTime = timestamp;
                }
            }
            
            intersections.forEach(intersection => intersection.update(effectiveDeltaTime));
            for (let i = vehicles.length - 1; i >= 0; i--) { // Iterate backwards for safe removal
                if (vehicles[i]) { // Check if vehicle still exists (might have been despawned by another process)
                     vehicles[i].update(effectiveDeltaTime, vehicles);
                }
            }

            ctx.clearRect(0, 0, canvas.width, canvas.height); 

            roads.forEach(road => road.draw());
            intersections.forEach(intersection => intersection.draw());
            vehicles.forEach(vehicle => vehicle.draw());

            vehicleCountDisplayEl.textContent = vehicles.length;

            requestAnimationFrame(gameLoop);
        }

        // --- Initialization ---
        function initializeSimulation() {
            generateGridMap();    
            addMultipleVehicles(15); 
            speedMultiplierDisplayEl.textContent = globalSpeedMultiplier.toFixed(1);
            requestAnimationFrame(gameLoop);
        }
        initializeSimulation();

    </script>
</body>
</html>