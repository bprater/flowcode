// Rocket Turret
// Fires guided missiles with homing capability and explosive impact

class RocketTurret extends BaseTurret {
    constructor(config = {}) {
        super({
            type: 'rocket',
            fireRate: 1500,
            damage: 70,
            projectileSpeed: 2,
            range: 400,
            barrelLength: 16,
            projectileClass: 'rocket',
            ...config
        });
        
        // Rocket-specific constants
        this.turnRate = 0.05;
        this.wobbleAmplitude = 4;
        this.wobbleFrequency = 0.2;
        this.puffInterval = 5;
        this.bodyLength = 16;
    }
    
    // Get CSS styles for rocket turret
    static getStyles() {
        return `
            .turret-rocket { 
                width: 20px; height: 32px; 
                background: linear-gradient(#A9A9A9, #696969); 
                border-radius: 5px 5px 2px 2px; 
            }
            .turret-rocket::before { 
                content: ''; position: absolute; top: -3px; left: 2px; 
                width: 16px; height: 5px; background: #505050; border-radius: 2px; 
            }
            
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
            
            .rocket-puff { 
                position: absolute; width: 10px; height: 10px; 
                background: radial-gradient(circle, rgba(220,220,220,0.6) 20%, rgba(180,180,180,0.3) 50%, transparent 70%); 
                border-radius: 50%; pointer-events: none; 
                animation: puff-trail-anim 0.8s ease-out forwards; 
                transform: translate(-50%, -50%); 
            }
            @keyframes puff-trail-anim { 
                0% { transform: translate(-50%, -50%) scale(0.5); opacity: 0.7; } 
                100% { transform: translate(-50%, -50%) scale(2.5); opacity: 0; } 
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
        `;
    }
    
    // Get HTML structure for rocket turret base
    static getHTML(position) {
        return `
            <div id="tower-rocket-base" class="tower-base" style="left: ${position.x}px;">
                <div class="turret-pivot">
                    <div class="turret-model turret-rocket">
                        <div class="turret-glow-indicator"></div>
                    </div>
                </div>
            </div>
        `;
    }
    
    // Get control panel HTML
    static getControlsHTML() {
        return `
            <div class="control-group" id="rocket-controls">
                <h3>Rocket Tower 🚀</h3>
                <label for="rocket-firerate">Fire Rate (ms): <span id="rocket-firerate-val">1500</span></label>
                <input type="range" id="rocket-firerate" min="500" max="5000" value="1500" step="100">
                <label for="rocket-damage">Damage: <span id="rocket-damage-val">70</span></label>
                <input type="range" id="rocket-damage" min="10" max="200" value="70" step="5">
                <label for="rocket-projspeed">Proj. Speed: <span id="rocket-projspeed-val">2</span></label>
                <input type="range" id="rocket-projspeed" min="0.5" max="5" value="2" step="0.1">
                <label for="rocket-range">Range (px): <span id="rocket-range-val">400</span></label>
                <input type="range" id="rocket-range" min="50" max="600" value="400" step="10">
                <div class="damage-meter" id="rocket-damage-meter">
                    <div class="damage-label">Total Damage</div>
                    <div class="damage-value">0</div>
                </div>
            </div>
        `;
    }
    
    fire(target, gameConfig) {
        try {
            this.launchProjectile(target, gameConfig);
            gameConfig.playSound('rocket_fire');
            this.resetCharge();
            this.fireRecoil(gameConfig);
        } catch (e) {
            console.error(`Error in rocket fire for ${this.id}:`, e);
        }
    }
    
    launchProjectile(target, gameConfig) {
        const projectileEl = document.createElement('div');
        projectileEl.classList.add('projectile', this.projectileClass);
        projectileEl.id = `proj-${gameConfig.getNextProjectileId()}`;
        
        const startX = this.x + this.barrelLength * Math.cos(this.currentAngleRad);
        const startY = this.y + this.barrelLength * Math.sin(this.currentAngleRad);
        
        // Create rocket components
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
        
        // Position and add to game
        projectileEl.style.left = '0px';
        projectileEl.style.top = '0px';
        projectileEl.style.visibility = 'hidden';
        gameConfig.gameContainer.appendChild(projectileEl);
        
        projectileEl.style.left = `${startX - projectileEl.offsetWidth / 2}px`;
        projectileEl.style.top = `${startY - projectileEl.offsetHeight / 2}px`;
        projectileEl.style.visibility = 'visible';
        
        // Create projectile data
        const projectile = {
            id: gameConfig.getNextProjectileId(),
            el: projectileEl,
            x: startX,
            y: startY,
            damage: this.damage,
            speed: this.projectileSpeed,
            target: target,
            towerType: this.type,
            tower: this,
            currentAngle: this.currentAngleRad,
            age: 0,
            turnRate: this.turnRate,
            wobbleAmplitude: this.wobbleAmplitude,
            wobbleFrequency: this.wobbleFrequency,
            puffInterval: this.puffInterval,
            bodyLength: this.bodyLength
        };
        
        gameConfig.addProjectile(projectile);
    }
    
    // Update rocket projectile movement
    static updateProjectile(proj, deltaTime, gameConfig) {
        const actualDeltaFactor = deltaTime / (1000/60);
        const targetEnemy = proj.target;
        
        if (!targetEnemy || targetEnemy.health <= 0) {
            return false; // Remove projectile
        }
        
        const targetX = targetEnemy.x + targetEnemy.width / 2;
        const targetY = targetEnemy.y + targetEnemy.height / 2;
        const dx = targetX - proj.x;
        const dy = targetY - proj.y;
        const distance = Math.sqrt(dx * dx + dy * dy);
        const moveSpeed = proj.speed * actualDeltaFactor;
        
        // Spawn trail puff
        if (proj.age % proj.puffInterval === 0) {
            const puffOffset = (proj.bodyLength / 2) + 4;
            const puffX = proj.x - puffOffset * Math.cos(proj.currentAngle);
            const puffY = proj.y - puffOffset * Math.sin(proj.currentAngle);
            gameConfig.createVisualEffect(puffX, puffY, 'rocket_puff_trail');
        }
        
        // Check for impact
        if (distance < moveSpeed + (targetEnemy.width / 3)) {
            const actualDamage = Math.min(proj.damage, targetEnemy.health);
            targetEnemy.health -= proj.damage;
            proj.tower.updateDamage(actualDamage);
            
            gameConfig.createVisualEffect(targetX, targetY, 'enemy_hit_sparkle');
            gameConfig.createVisualEffect(targetX, targetY, 'explosion');
            gameConfig.playSound('rocket_impact');
            
            if (targetEnemy.el) {
                targetEnemy.el.classList.add('hit-rocket');
                setTimeout(() => targetEnemy.el.classList.remove('hit-rocket'), 100);
            }
            
            return false; // Remove projectile
        }
        
        // Homing behavior
        const angleToTarget = Math.atan2(dy, dx);
        let angleDifference = angleToTarget - proj.currentAngle;
        
        while (angleDifference > Math.PI) angleDifference -= 2 * Math.PI;
        while (angleDifference < -Math.PI) angleDifference += 2 * Math.PI;
        
        const turnAmount = Math.sign(angleDifference) * Math.min(Math.abs(angleDifference), proj.turnRate * (moveSpeed / 1.5));
        proj.currentAngle += turnAmount;
        
        let moveX = Math.cos(proj.currentAngle) * moveSpeed;
        let moveY = Math.sin(proj.currentAngle) * moveSpeed;
        
        // Add wobble
        const wobbleAngle = proj.currentAngle + Math.PI / 2;
        moveX += Math.cos(wobbleAngle) * proj.wobbleAmplitude * Math.sin(proj.age * proj.wobbleFrequency) * 0.1 * actualDeltaFactor;
        moveY += Math.sin(wobbleAngle) * proj.wobbleAmplitude * Math.sin(proj.age * proj.wobbleFrequency) * 0.1 * actualDeltaFactor;
        
        proj.x += moveX;
        proj.y += moveY;
        proj.age++;
        
        proj.el.style.left = `${proj.x - proj.el.offsetWidth/2}px`;
        proj.el.style.top = `${proj.y - proj.el.offsetHeight/2}px`;
        proj.el.style.transform = `rotate(${proj.currentAngle}rad)`;
        
        return true; // Keep projectile
    }
    
    createMuzzleEffects(muzzleX, muzzleY, gameConfig) {
        gameConfig.createVisualEffect(muzzleX, muzzleY, 'muzzle_flash', { angle: this.currentAngleRad });
        setTimeout(() => {
            gameConfig.createVisualEffect(
                muzzleX + Math.cos(this.currentAngleRad) * 2, 
                muzzleY + Math.sin(this.currentAngleRad) * 2, 
                'muzzle_puff'
            );
        }, 50);
    }
}

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = RocketTurret;
} else if (typeof window !== 'undefined') {
    window.RocketTurret = RocketTurret;
}