<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Virtual Campfire</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background: linear-gradient(to bottom, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            font-family: Arial, sans-serif;
            overflow: hidden;
        }
        
        canvas {
            border: 1px solid #333;
            background: radial-gradient(ellipse at bottom, #1a1a1a 0%, #000000 70%);
            cursor: crosshair;
        }
        
        .controls {
            position: absolute;
            top: 20px;
            left: 20px;
            color: white;
            font-size: 14px;
            background: rgba(0, 0, 0, 0.7);
            padding: 20px;
            border-radius: 10px;
            min-width: 250px;
        }
        
        .control-group {
            margin-bottom: 15px;
        }
        
        .control-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        .slider {
            width: 200px;
            margin-bottom: 5px;
        }
        
        .value-display {
            font-size: 12px;
            color: #ccc;
        }
        
        .random-btn {
            background: linear-gradient(45deg, #ff6b35, #ff8e53);
            border: none;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            margin-top: 10px;
            width: 100%;
        }
        
        .random-btn:hover {
            background: linear-gradient(45deg, #ff8e53, #ffab76);
        }
    </style>
</head>
<body>
    <div class="controls">
        <p style="margin-top: 0;">🔥 Campfire Controls</p>
        
        <div class="control-group">
            <label>Fire Intensity</label>
            <input type="range" id="fireIntensity" class="slider" min="1" max="10" value="5">
            <div class="value-display" id="fireIntensityValue">5</div>
        </div>
        
        <div class="control-group">
            <label>Fire Width</label>
            <input type="range" id="fireWidth" class="slider" min="20" max="150" value="60">
            <div class="value-display" id="fireWidthValue">60</div>
        </div>
        
        <div class="control-group">
            <label>Smoke Amount</label>
            <input type="range" id="smokeAmount" class="slider" min="0" max="100" value="30">
            <div class="value-display" id="smokeAmountValue">30%</div>
        </div>
        
        <div class="control-group">
            <label>Spark Frequency</label>
            <input type="range" id="sparkFreq" class="slider" min="0" max="50" value="10">
            <div class="value-display" id="sparkFreqValue">10%</div>
        </div>
        
        <div class="control-group">
            <label>Wind Strength</label>
            <input type="range" id="windStrength" class="slider" min="0" max="100" value="50">
            <div class="value-display" id="windStrengthValue">50%</div>
        </div>
        
        <div class="control-group">
            <label>Particle Size</label>
            <input type="range" id="particleSize" class="slider" min="0.5" max="3" step="0.1" value="1.5">
            <div class="value-display" id="particleSizeValue">1.5x</div>
        </div>
        
        <div class="control-group">
            <label>Emitter Size</label>
            <input type="range" id="emitterSize" class="slider" min="10" max="200" value="80">
            <div class="value-display" id="emitterSizeValue">80</div>
        </div>
        
        <div class="control-group">
            <label>Emitter Shape</label>
            <select id="emitterShape" style="width: 200px; padding: 5px; background: #333; color: white; border: 1px solid #555; border-radius: 3px;">
                <option value="circle">🔥 Circle</option>
                <option value="line">➖ Line</option>
                <option value="oval">🥚 Oval</option>
                <option value="square">⬜ Square</option>
                <option value="triangle">🔺 Triangle</option>
            </select>
        </div>
        
        <button class="random-btn" id="randomBtn">🎲 Randomize Settings</button>
        
        <p style="font-size: 12px; margin-bottom: 0; color: #aaa;">Click and drag to disturb the fire</p>
    </div>
    <canvas id="fireCanvas" width="1200" height="800"></canvas>

    <script>
        class Particle {
            constructor(x, y, type = 'fire') {
                this.x = x;
                this.y = y;
                this.vx = (Math.random() - 0.5) * 2;
                this.vy = Math.random() * -3 - 1;
                this.type = type;
                this.life = 1.0;
                this.decay = Math.random() * 0.01 + 0.005;
                this.size = Math.random() * 8 + 4;
                this.rotation = Math.random() * Math.PI * 2;
                this.rotationSpeed = (Math.random() - 0.5) * 0.1;
                this.flickerPhase = Math.random() * Math.PI * 2;
                this.flickerSpeed = Math.random() * 0.3 + 0.1;
                this.height = this.size * (1.5 + Math.random() * 0.5);
                this.width = this.size * (0.7 + Math.random() * 0.3);
                
                if (type === 'smoke') {
                    this.vx = (Math.random() - 0.5) * 1;
                    this.vy = Math.random() * -2 - 0.5;
                    this.decay = Math.random() * 0.005 + 0.002;
                    this.size = Math.random() * 15 + 10;
                    this.height = this.size;
                    this.width = this.size;
                } else if (type === 'spark') {
                    this.vx = (Math.random() - 0.5) * 6;
                    this.vy = Math.random() * -8 - 2;
                    this.decay = Math.random() * 0.02 + 0.01;
                    this.size = Math.random() * 3 + 1;
                    this.gravity = 0.1;
                    this.height = this.size * 0.3;
                    this.width = this.size * 0.3;
                } else if (type === 'ember') {
                    this.vx = (Math.random() - 0.5) * 4;
                    this.vy = Math.random() * -6 - 1;
                    this.decay = Math.random() * 0.008 + 0.003;
                    this.size = Math.random() * 6 + 2;
                    this.gravity = 0.05;
                    this.height = this.size * 0.8;
                    this.width = this.size * 0.8;
                }
            }
            
            update() {
                this.x += this.vx;
                this.y += this.vy;
                this.life -= this.decay;
                this.rotation += this.rotationSpeed;
                this.flickerPhase += this.flickerSpeed;
                
                if (this.type === 'fire') {
                    this.vy *= 0.99;
                    this.vx *= 0.95;
                    this.size *= 0.98;
                    this.height *= 0.98;
                    this.width *= 0.98;
                    // Add flickering to fire shapes
                    this.flickerIntensity = 0.1 + 0.05 * Math.sin(this.flickerPhase);
                } else if (this.type === 'smoke') {
                    this.vy *= 0.98;
                    this.vx += (Math.random() - 0.5) * 0.1;
                    this.size *= 1.01;
                    this.height *= 1.01;
                    this.width *= 1.01;
                } else if (this.type === 'spark' || this.type === 'ember') {
                    this.vy += this.gravity;
                    this.vx *= 0.98;
                }
                
                return this.life > 0;
            }
            
            drawFlameShape(ctx, x, y, width, height, flicker) {
                // Create a more realistic flame shape using curves
                const flickerOffset = flicker * 3;
                const w = width + flickerOffset;
                const h = height + flickerOffset;
                
                ctx.beginPath();
                ctx.moveTo(x, y + h * 0.8);
                
                // Left side of flame
                ctx.quadraticCurveTo(
                    x - w * 0.6, y + h * 0.6 + flickerOffset,
                    x - w * 0.3, y + h * 0.3
                );
                ctx.quadraticCurveTo(
                    x - w * 0.4, y + h * 0.1 + flickerOffset,
                    x - w * 0.1, y
                );
                
                // Top of flame (pointed)
                ctx.quadraticCurveTo(x, y - h * 0.1, x + w * 0.1, y);
                
                // Right side of flame
                ctx.quadraticCurveTo(
                    x + w * 0.4, y + h * 0.1 - flickerOffset,
                    x + w * 0.3, y + h * 0.3
                );
                ctx.quadraticCurveTo(
                    x + w * 0.6, y + h * 0.6 - flickerOffset,
                    x, y + h * 0.8
                );
                
                ctx.closePath();
            }
            
            draw(ctx) {
                const alpha = this.life;
                ctx.save();
                ctx.globalAlpha = alpha;
                ctx.translate(this.x, this.y);
                ctx.rotate(this.rotation);
                
                if (this.type === 'fire') {
                    const flicker = this.flickerIntensity || 0;
                    
                    // Create gradient for flame
                    const gradient = ctx.createLinearGradient(0, this.height * 0.8, 0, -this.height * 0.2);
                    gradient.addColorStop(0, `rgba(255, 50, 0, ${alpha})`);
                    gradient.addColorStop(0.3, `rgba(255, 120, 0, ${alpha})`);
                    gradient.addColorStop(0.7, `rgba(255, 200, 50, ${alpha * 0.9})`);
                    gradient.addColorStop(1, `rgba(255, 255, 200, ${alpha * 0.7})`);
                    
                    ctx.fillStyle = gradient;
                    this.drawFlameShape(ctx, 0, 0, this.width, this.height, flicker);
                    ctx.fill();
                    
                    // Add inner glow
                    ctx.globalAlpha = alpha * 0.6;
                    const innerGradient = ctx.createLinearGradient(0, this.height * 0.6, 0, -this.height * 0.1);
                    innerGradient.addColorStop(0, `rgba(255, 150, 0, ${alpha * 0.8})`);
                    innerGradient.addColorStop(1, `rgba(255, 255, 150, ${alpha * 0.9})`);
                    ctx.fillStyle = innerGradient;
                    this.drawFlameShape(ctx, 0, 0, this.width * 0.6, this.height * 0.8, flicker * 0.5);
                    ctx.fill();
                    
                } else if (this.type === 'smoke') {
                    // Irregular smoke blob
                    const points = 8;
                    ctx.beginPath();
                    for (let i = 0; i < points; i++) {
                        const angle = (i / points) * Math.PI * 2;
                        const radius = this.size * (0.8 + 0.3 * Math.sin(this.flickerPhase + i));
                        const x = Math.cos(angle) * radius;
                        const y = Math.sin(angle) * radius;
                        if (i === 0) ctx.moveTo(x, y);
                        else ctx.lineTo(x, y);
                    }
                    ctx.closePath();
                    
                    const smokeGradient = ctx.createRadialGradient(0, 0, 0, 0, 0, this.size);
                    smokeGradient.addColorStop(0, `rgba(80, 80, 80, ${alpha * 0.4})`);
                    smokeGradient.addColorStop(0.7, `rgba(60, 60, 60, ${alpha * 0.2})`);
                    smokeGradient.addColorStop(1, `rgba(40, 40, 40, 0)`);
                    ctx.fillStyle = smokeGradient;
                    ctx.fill();
                    
                } else if (this.type === 'spark') {
                    // Small bright rectangles for sparks
                    ctx.fillStyle = `rgba(255, 255, 200, ${alpha})`;
                    ctx.fillRect(-this.width/2, -this.height/2, this.width, this.height);
                    
                    // Add glow
                    ctx.globalAlpha = alpha * 0.5;
                    ctx.fillStyle = `rgba(255, 200, 100, ${alpha})`;
                    ctx.fillRect(-this.width, -this.height, this.width * 2, this.height * 2);
                    
                } else if (this.type === 'ember') {
                    // Glowing irregular shapes for embers
                    const points = 6;
                    ctx.beginPath();
                    for (let i = 0; i < points; i++) {
                        const angle = (i / points) * Math.PI * 2;
                        const radius = this.size * (0.7 + 0.3 * Math.random());
                        const x = Math.cos(angle) * radius;
                        const y = Math.sin(angle) * radius;
                        if (i === 0) ctx.moveTo(x, y);
                        else ctx.lineTo(x, y);
                    }
                    ctx.closePath();
                    
                    const emberGradient = ctx.createRadialGradient(0, 0, 0, 0, 0, this.size);
                    emberGradient.addColorStop(0, `rgba(255, 150, 50, ${alpha})`);
                    emberGradient.addColorStop(0.5, `rgba(255, 80, 0, ${alpha})`);
                    emberGradient.addColorStop(1, `rgba(150, 30, 0, ${alpha * 0.3})`);
                    ctx.fillStyle = emberGradient;
                    ctx.fill();
                }
                
                ctx.restore();
            }
        }
        
        class CampfireSimulation {
            constructor(canvas) {
                this.canvas = canvas;
                this.ctx = canvas.getContext('2d');
                this.particles = [];
                this.mouse = { x: 0, y: 0, pressed: false };
                this.fireBaseY = canvas.height - 100;
                this.fireBaseX = canvas.width / 2;
                this.windForce = { x: 0, y: 0 };
                
                // Configurable parameters
                this.config = {
                    fireIntensity: 5,
                    fireWidth: 60,
                    smokeAmount: 30,
                    sparkFreq: 10,
                    windStrength: 50,
                    particleSize: 1.5,
                    emitterSize: 80,
                    emitterShape: 'circle'
                };
                
                this.setupEventListeners();
                this.setupControls();
                this.animate();
            }
            
            setupEventListeners() {
                this.canvas.addEventListener('mousedown', (e) => {
                    this.mouse.pressed = true;
                    this.updateMousePosition(e);
                });
                
                this.canvas.addEventListener('mouseup', () => {
                    this.mouse.pressed = false;
                    this.windForce.x *= 0.5;
                    this.windForce.y *= 0.5;
                });
                
                this.canvas.addEventListener('mousemove', (e) => {
                    const prevX = this.mouse.x;
                    const prevY = this.mouse.y;
                    this.updateMousePosition(e);
                    
                    if (this.mouse.pressed) {
                        this.windForce.x = (this.mouse.x - prevX) * 0.5;
                        this.windForce.y = (this.mouse.y - prevY) * 0.5;
                        this.createDisturbance();
                    }
                });
            }
            
            setupControls() {
                // Get all sliders and their value displays
                const controls = {
                    fireIntensity: { slider: document.getElementById('fireIntensity'), display: document.getElementById('fireIntensityValue') },
                    fireWidth: { slider: document.getElementById('fireWidth'), display: document.getElementById('fireWidthValue') },
                    smokeAmount: { slider: document.getElementById('smokeAmount'), display: document.getElementById('smokeAmountValue') },
                    sparkFreq: { slider: document.getElementById('sparkFreq'), display: document.getElementById('sparkFreqValue') },
                    windStrength: { slider: document.getElementById('windStrength'), display: document.getElementById('windStrengthValue') },
                    particleSize: { slider: document.getElementById('particleSize'), display: document.getElementById('particleSizeValue') },
                    emitterSize: { slider: document.getElementById('emitterSize'), display: document.getElementById('emitterSizeValue') }
                };
                
                // Setup event listeners for each control
                Object.keys(controls).forEach(key => {
                    const control = controls[key];
                    
                    control.slider.addEventListener('input', (e) => {
                        const value = parseFloat(e.target.value);
                        this.config[key] = value;
                        
                        // Update display
                        if (key === 'smokeAmount' || key === 'sparkFreq' || key === 'windStrength') {
                            control.display.textContent = value + '%';
                        } else if (key === 'particleSize') {
                            control.display.textContent = value + 'x';
                        } else {
                            control.display.textContent = value;
                        }
                    });
                });
                
                // Emitter shape selector
                document.getElementById('emitterShape').addEventListener('change', (e) => {
                    this.config.emitterShape = e.target.value;
                });
                
                // Random button
                document.getElementById('randomBtn').addEventListener('click', () => {
                    this.randomizeSettings(controls);
                });
            }
            
            randomizeSettings(controls) {
                const shapes = ['circle', 'line', 'oval', 'square', 'triangle'];
                const randomValues = {
                    fireIntensity: Math.floor(Math.random() * 10) + 1,
                    fireWidth: Math.floor(Math.random() * 130) + 20,
                    smokeAmount: Math.floor(Math.random() * 100),
                    sparkFreq: Math.floor(Math.random() * 50),
                    windStrength: Math.floor(Math.random() * 100),
                    particleSize: Math.round((Math.random() * 2.5 + 0.5) * 10) / 10,
                    emitterSize: Math.floor(Math.random() * 190) + 10
                };
                
                // Randomize shape
                this.config.emitterShape = shapes[Math.floor(Math.random() * shapes.length)];
                document.getElementById('emitterShape').value = this.config.emitterShape;
                
                Object.keys(randomValues).forEach(key => {
                    const value = randomValues[key];
                    this.config[key] = value;
                    controls[key].slider.value = value;
                    
                    // Update display
                    if (key === 'smokeAmount' || key === 'sparkFreq' || key === 'windStrength') {
                        controls[key].display.textContent = value + '%';
                    } else if (key === 'particleSize') {
                        controls[key].display.textContent = value + 'x';
                    } else {
                        controls[key].display.textContent = value;
                    }
                });
            }
            
            updateMousePosition(e) {
                const rect = this.canvas.getBoundingClientRect();
                this.mouse.x = e.clientX - rect.left;
                this.mouse.y = e.clientY - rect.top;
            }
            
            createDisturbance() {
                const distance = Math.sqrt(
                    Math.pow(this.mouse.x - this.fireBaseX, 2) + 
                    Math.pow(this.mouse.y - this.fireBaseY, 2)
                );
                
                if (distance < 150) {
                    for (let i = 0; i < 5; i++) {
                        this.particles.push(new Particle(
                            this.fireBaseX + (Math.random() - 0.5) * 50,
                            this.fireBaseY + Math.random() * 20,
                            'spark'
                        ));
                    }
                }
            }
            
            getEmitterPosition() {
                const size = this.config.emitterSize;
                const shape = this.config.emitterShape;
                const baseX = this.fireBaseX;
                const baseY = this.fireBaseY;
                
                switch (shape) {
                    case 'circle':
                        const angle = Math.random() * Math.PI * 2;
                        const radius = Math.random() * (size / 2);
                        return {
                            x: baseX + Math.cos(angle) * radius,
                            y: baseY + Math.sin(angle) * radius * 0.3
                        };
                    
                    case 'line':
                        return {
                            x: baseX + (Math.random() - 0.5) * size,
                            y: baseY + Math.random() * 10
                        };
                    
                    case 'oval':
                        const ovalAngle = Math.random() * Math.PI * 2;
                        const ovalRadiusX = Math.random() * (size / 2);
                        const ovalRadiusY = Math.random() * (size / 4);
                        return {
                            x: baseX + Math.cos(ovalAngle) * ovalRadiusX,
                            y: baseY + Math.sin(ovalAngle) * ovalRadiusY
                        };
                    
                    case 'square':
                        return {
                            x: baseX + (Math.random() - 0.5) * size,
                            y: baseY + (Math.random() - 0.5) * (size * 0.3)
                        };
                    
                    case 'triangle':
                        const triRand = Math.random();
                        const triWidth = triRand * size;
                        return {
                            x: baseX + (Math.random() - 0.5) * triWidth,
                            y: baseY + triRand * 20
                        };
                    
                    default:
                        return { x: baseX, y: baseY };
                }
            }
            
            spawnParticles() {
                const intensity = this.config.fireIntensity;
                const width = this.config.fireWidth;
                const smokeChance = this.config.smokeAmount / 100;
                const sparkChance = this.config.sparkFreq / 100;
                
                // Fire particles - more intense and fuller
                const fireCount = Math.floor(intensity * 2);
                for (let i = 0; i < fireCount; i++) {
                    const pos = this.getEmitterPosition();
                    const particle = new Particle(pos.x, pos.y, 'fire');
                    particle.size *= this.config.particleSize;
                    this.particles.push(particle);
                }
                
                // Additional dense fire core for fuller flames
                for (let i = 0; i < Math.floor(intensity); i++) {
                    const pos = this.getEmitterPosition();
                    const particle = new Particle(
                        pos.x + (Math.random() - 0.5) * 20,
                        pos.y + Math.random() * 10,
                        'fire'
                    );
                    particle.size *= this.config.particleSize * 1.2;
                    particle.vy *= 1.5;
                    this.particles.push(particle);
                }
                
                // Smoke particles
                if (Math.random() < smokeChance) {
                    const pos = this.getEmitterPosition();
                    const particle = new Particle(
                        pos.x,
                        pos.y - 30,
                        'smoke'
                    );
                    particle.size *= this.config.particleSize;
                    this.particles.push(particle);
                }
                
                // Sparks
                if (Math.random() < sparkChance) {
                    const pos = this.getEmitterPosition();
                    const particle = new Particle(pos.x, pos.y, 'spark');
                    particle.size *= this.config.particleSize * 0.8;
                    this.particles.push(particle);
                }
                
                // Embers
                if (Math.random() < sparkChance * 0.5) {
                    const pos = this.getEmitterPosition();
                    const particle = new Particle(pos.x, pos.y, 'ember');
                    particle.size *= this.config.particleSize;
                    this.particles.push(particle);
                }
            }
            
            update() {
                this.spawnParticles();
                
                // Apply wind force to particles
                this.particles.forEach(particle => {
                    if (this.mouse.pressed) {
                        const distance = Math.sqrt(
                            Math.pow(particle.x - this.mouse.x, 2) + 
                            Math.pow(particle.y - this.mouse.y, 2)
                        );
                        
                        if (distance < 100) {
                            const force = (100 - distance) / 100;
                            const windMultiplier = this.config.windStrength / 100;
                            particle.vx += this.windForce.x * force * 0.1 * windMultiplier;
                            particle.vy += this.windForce.y * force * 0.1 * windMultiplier;
                        }
                    }
                });
                
                // Update particles and remove dead ones
                this.particles = this.particles.filter(particle => particle.update());
                
                // Decay wind force
                this.windForce.x *= 0.95;
                this.windForce.y *= 0.95;
            }
            
            draw() {
                // Clear canvas with fade effect for trails
                this.ctx.fillStyle = 'rgba(0, 0, 0, 0.1)';
                this.ctx.fillRect(0, 0, this.canvas.width, this.canvas.height);
                
                // Draw particles
                this.particles.forEach(particle => particle.draw(this.ctx));
                
                // Draw fire base glow
                this.ctx.save();
                this.ctx.globalAlpha = 0.3;
                const baseGradient = this.ctx.createRadialGradient(
                    this.fireBaseX, this.fireBaseY, 0,
                    this.fireBaseX, this.fireBaseY, 80
                );
                baseGradient.addColorStop(0, 'rgba(255, 100, 0, 0.5)');
                baseGradient.addColorStop(1, 'rgba(255, 0, 0, 0)');
                this.ctx.fillStyle = baseGradient;
                this.ctx.fillRect(this.fireBaseX - 80, this.fireBaseY - 40, 160, 80);
                this.ctx.restore();
            }
            
            animate() {
                this.update();
                this.draw();
                requestAnimationFrame(() => this.animate());
            }
        }
        
        // Initialize the simulation
        const canvas = document.getElementById('fireCanvas');
        const campfire = new CampfireSimulation(canvas);
    </script>
</body>
</html>