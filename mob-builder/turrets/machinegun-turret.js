// Machine Gun Turret
// Fires rapid bursts of bullets with high rate of fire but low damage

class MachineGunTurret extends BaseTurret {
    constructor(config = {}) {
        super({
            type: 'machinegun',
            fireRate: 200, // Very fast fire rate
            damage: 8, // Low damage per shot
            projectileSpeed: 8, // Fast bullets
            range: 350,
            barrelLength: 18,
            projectileClass: 'bullet',
            ...config
        });
        
        // Machine gun specific properties
        this.burstSize = 3; // Number of bullets per burst
        this.burstDelay = 50; // Delay between bullets in burst (ms)
        this.currentBurst = 0;
        this.burstStartTime = 0;
        this.spread = 0.1; // Bullet spread in radians
    }
    
    // Get CSS styles for machine gun turret
    static getStyles() {
        return `
            .turret-machinegun { 
                width: 22px; height: 35px; 
                background: linear-gradient(#556B2F, #2F4F2F); 
                border-radius: 3px 3px 1px 1px; 
                border: 1px solid #1C3A1C; 
                box-shadow: 0 1px 3px rgba(0,0,0,0.4); 
            }
            .turret-machinegun::before { 
                content: ''; position: absolute; top: 2px; left: 50%; 
                transform: translateX(-50%); width: 80%; height: 4px; 
                background: #8B4513; border-radius: 2px; 
                box-shadow: 0 0 2px #654321; 
            }
            .turret-machinegun::after { 
                content: ''; position: absolute; top: -2px; left: calc(50% - 2px); 
                width: 4px; height: 4px; background: #2F4F2F; border-radius: 50%; 
                box-shadow: 0 0 2px #556B2F; 
            }
            .turret-machinegun.firing { 
                animation: rapid-fire-shake 0.05s infinite; 
            }
            @keyframes rapid-fire-shake { 
                0% { transform: translateX(-50%) translateY(0); } 
                25% { transform: translateX(-50%) translateY(-1px); } 
                50% { transform: translateX(-50%) translateY(0); } 
                75% { transform: translateX(-50%) translateY(1px); } 
                100% { transform: translateX(-50%) translateY(0); } 
            }
            
            .bullet { 
                width: 6px; height: 6px; 
                background: linear-gradient(45deg, #FFD700 0%, #FFA500 50%, #FF8C00 100%); 
                border-radius: 50%; 
                box-shadow: 0 0 2px #FF8C00, 0 0 4px rgba(255, 140, 0, 0.5); 
                position: relative;
            }
            .bullet::before { 
                content: ''; position: absolute; 
                width: 8px; height: 2px; 
                background: linear-gradient(90deg, transparent 0%, rgba(255, 215, 0, 0.8) 20%, transparent 100%); 
                left: -4px; top: 50%; transform: translateY(-50%); 
                border-radius: 1px; 
            }
            
            .bullet-impact { 
                width: 20px; height: 20px; 
                background: radial-gradient(circle, #FFD700 10%, #FFA500 40%, #FF6347 70%, transparent 90%); 
                border-radius: 50%; animation: bullet-impact-anim 0.2s forwards; 
                transform: translate(-50%, -50%); 
            }
            @keyframes bullet-impact-anim { 
                0% { transform: translate(-50%, -50%) scale(0.3); opacity: 1; } 
                100% { transform: translate(-50%, -50%) scale(1.5); opacity: 0; } 
            }
            
            .muzzle-flash-rapid { 
                width: 20px; height: 15px; 
                background: radial-gradient(ellipse at center, white 20%, #FFD700 50%, #FF8C00 80%, transparent 95%); 
                border-radius: 50% 50% 30% 30%; 
                animation: quick-fade-rapid 0.08s forwards; 
                transform: translate(-50%, -60%) rotate(0deg); 
            }
            @keyframes quick-fade-rapid { 
                0% { transform: translate(-50%, -60%) scale(1.2); opacity: 1; } 
                100% { transform: translate(-50%, -60%) scale(0.3); opacity: 0; } 
            }
            
            .shell-casing { 
                width: 3px; height: 8px; 
                background: linear-gradient(#DAA520, #B8860B); 
                border-radius: 1px; 
                position: absolute; 
                animation: shell-eject 0.6s ease-out forwards; 
                transform-origin: bottom center; 
            }
            @keyframes shell-eject { 
                0% { 
                    transform: translate(0, 0) rotate(0deg) scale(1); 
                    opacity: 1; 
                } 
                50% { 
                    transform: translate(var(--eject-x, -10px), var(--eject-y, -15px)) rotate(180deg) scale(0.8); 
                    opacity: 0.8; 
                } 
                100% { 
                    transform: translate(var(--eject-x, -15px), var(--eject-y, 20px)) rotate(360deg) scale(0.5); 
                    opacity: 0; 
                } 
            }
        `;
    }
    
    // Get HTML structure for machine gun turret base
    static getHTML(position) {
        return `
            <div id="tower-machinegun-base" class="tower-base" style="left: ${position.x}px;">
                <div class="turret-pivot">
                    <div class="turret-model turret-machinegun">
                        <div class="turret-glow-indicator"></div>
                    </div>
                </div>
            </div>
        `;
    }
    
    // Get control panel HTML
    static getControlsHTML() {
        return `
            <div class="control-group" id="machinegun-controls">
                <h3>Machine Gun Tower 🔫</h3>
                <label for="machinegun-firerate">Fire Rate (ms): <span id="machinegun-firerate-val">200</span></label>
                <input type="range" id="machinegun-firerate" min="50" max="500" value="200" step="10">
                <label for="machinegun-damage">Damage: <span id="machinegun-damage-val">8</span></label>
                <input type="range" id="machinegun-damage" min="3" max="25" value="8" step="1">
                <label for="machinegun-projspeed">Proj. Speed: <span id="machinegun-projspeed-val">8</span></label>
                <input type="range" id="machinegun-projspeed" min="3" max="15" value="8" step="0.5">
                <label for="machinegun-range">Range (px): <span id="machinegun-range-val">350</span></label>
                <input type="range" id="machinegun-range" min="200" max="500" value="350" step="10">
                <div class="damage-meter" id="machinegun-damage-meter">
                    <div class="damage-label">Total Damage</div>
                    <div class="damage-value">0</div>
                </div>
            </div>
        `;
    }
    
    fire(target, gameConfig) {
        try {
            // Start a burst
            if (this.currentBurst === 0) {
                this.burstStartTime = performance.now();
                this.startBurstVisuals();
            }
            
            this.fireBullet(target, gameConfig);
            this.currentBurst++;
            
            // Check if burst is complete
            if (this.currentBurst >= this.burstSize) {
                this.currentBurst = 0;
                this.resetCharge();
                this.endBurstVisuals();
            } else {
                // Schedule next bullet in burst
                setTimeout(() => {
                    if (this.findTarget(gameConfig.enemies)) {
                        this.fire(target, gameConfig);
                    } else {
                        this.currentBurst = 0;
                        this.endBurstVisuals();
                    }
                }, this.burstDelay);
            }
        } catch (e) {
            console.error(`Error in machine gun fire for ${this.id}:`, e);
        }
    }
    
    fireBullet(target, gameConfig) {
        this.launchProjectile(target, gameConfig);
        gameConfig.playSound('machinegun_fire');
        this.createMuzzleEffects(
            this.x + this.barrelLength * Math.cos(this.currentAngleRad),
            this.y + this.barrelLength * Math.sin(this.currentAngleRad),
            gameConfig
        );
        this.ejectShell(gameConfig);
    }
    
    launchProjectile(target, gameConfig) {
        const projectileEl = document.createElement('div');
        projectileEl.classList.add('projectile', this.projectileClass);
        projectileEl.id = `proj-${gameConfig.getNextProjectileId()}`;
        
        const startX = this.x + this.barrelLength * Math.cos(this.currentAngleRad);
        const startY = this.y + this.barrelLength * Math.sin(this.currentAngleRad);
        
        // Position and add to game
        projectileEl.style.left = `${startX - projectileEl.offsetWidth / 2}px`;
        projectileEl.style.top = `${startY - projectileEl.offsetHeight / 2}px`;
        gameConfig.gameContainer.appendChild(projectileEl);
        
        // Add spread to bullets
        const spreadAngle = this.currentAngleRad + (Math.random() - 0.5) * this.spread;
        
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
            currentAngle: spreadAngle,
            age: 0
        };
        
        gameConfig.addProjectile(projectile);
    }
    
    // Update bullet movement
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
        
        // Check for impact
        if (distance < moveSpeed + (targetEnemy.width / 3)) {
            const actualDamage = Math.min(proj.damage, targetEnemy.health);
            targetEnemy.health -= proj.damage;
            proj.tower.updateDamage(actualDamage);
            
            gameConfig.createVisualEffect(targetX, targetY, 'enemy_hit_sparkle');
            gameConfig.createVisualEffect(targetX, targetY, 'bullet_impact');
            gameConfig.playSound('bullet_impact');
            
            if (targetEnemy.el) {
                targetEnemy.el.classList.add('hit-bullet');
                setTimeout(() => targetEnemy.el.classList.remove('hit-bullet'), 100);
            }
            
            return false; // Remove projectile
        }
        
        // Straight-line movement with slight course correction
        const moveX = Math.cos(proj.currentAngle) * moveSpeed;
        const moveY = Math.sin(proj.currentAngle) * moveSpeed;
        
        proj.x += moveX;
        proj.y += moveY;
        proj.age++;
        
        proj.el.style.left = `${proj.x - proj.el.offsetWidth/2}px`;
        proj.el.style.top = `${proj.y - proj.el.offsetHeight/2}px`;
        
        return true; // Keep projectile
    }
    
    createMuzzleEffects(muzzleX, muzzleY, gameConfig) {
        gameConfig.createVisualEffect(muzzleX, muzzleY, 'muzzle_flash_rapid', { angle: this.currentAngleRad });
    }
    
    ejectShell(gameConfig) {
        const shellEl = document.createElement('div');
        shellEl.classList.add('shell-casing');
        
        const ejectX = this.x + (this.barrelLength * 0.7) * Math.cos(this.currentAngleRad);
        const ejectY = this.y + (this.barrelLength * 0.7) * Math.sin(this.currentAngleRad);
        
        shellEl.style.left = `${ejectX}px`;
        shellEl.style.top = `${ejectY}px`;
        
        // Random eject direction
        const ejectAngle = this.currentAngleRad + Math.PI/2 + (Math.random() - 0.5) * 0.5;
        const ejectDist = 10 + Math.random() * 10;
        
        shellEl.style.setProperty('--eject-x', `${Math.cos(ejectAngle) * ejectDist}px`);
        shellEl.style.setProperty('--eject-y', `${Math.sin(ejectAngle) * ejectDist}px`);
        
        gameConfig.gameContainer.appendChild(shellEl);
        setTimeout(() => shellEl.remove(), 600);
    }
    
    startBurstVisuals() {
        if (this.modelEl) {
            this.modelEl.classList.add('firing');
        }
    }
    
    endBurstVisuals() {
        if (this.modelEl) {
            this.modelEl.classList.remove('firing');
        }
    }
    
    // Override charge level calculation for burst firing
    update(currentTime, enemies, gameConfig) {
        // Handle burst timing
        if (this.currentBurst > 0) {
            const timeSinceBurstStart = currentTime - this.burstStartTime;
            const expectedBurstTime = this.currentBurst * this.burstDelay;
            
            if (timeSinceBurstStart >= expectedBurstTime) {
                this.chargeLevel = 1.0; // Ready to fire next bullet
            } else {
                this.chargeLevel = 0; // Still in burst cooldown
            }
        } else {
            // Normal charge calculation
            super.update(currentTime, enemies, gameConfig);
        }
        
        // Update glow effect (but skip the firing logic, handle that separately)
        if (this.glowEl) {
            this.glowEl.style.opacity = this.chargeLevel * 0.8;
            const hue = 60 - (this.chargeLevel * 60);
            const spread = this.chargeLevel * 8;
            const blur = this.chargeLevel * 15;
            this.glowEl.style.boxShadow = `0 0 ${blur}px ${spread}px hsla(${hue}, 100%, 50%, 0.7)`;
        }
        
        // Find and target enemy
        const target = this.findTarget(enemies);
        if (target) {
            this.aimAt(target);
            
            if (this.chargeLevel >= 1.0 && this.currentBurst === 0) {
                this.fire(target, gameConfig);
            }
        }
    }
}

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = MachineGunTurret;
} else if (typeof window !== 'undefined') {
    window.MachineGunTurret = MachineGunTurret;
}