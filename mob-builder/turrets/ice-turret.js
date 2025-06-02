// Ice Turret
// Fires spinning ice crystals with splash damage and freezing effects

class IceTurret extends BaseTurret {
    constructor(config = {}) {
        super({
            type: 'ice',
            fireRate: 900,
            damage: 20,
            projectileSpeed: 5,
            range: 400,
            barrelLength: 15,
            projectileClass: 'ice-crystal-projectile',
            splashRadius: 70,
            splashDamageFactor: 0.5,
            ...config
        });
    }
    
    // Get CSS styles for ice turret
    static getStyles() {
        return `
            .turret-ice { 
                width: 16px; height: 30px; 
                background: transparent; border: none; box-shadow: none; 
            }
            .turret-ice::before, .turret-ice::after { 
                content: ''; position: absolute; 
                background-color: rgba(173, 216, 230, 0.6); 
                border: 1px solid rgba(200, 240, 255, 0.8); 
                box-shadow: 0 0 5px rgba(220, 250, 255, 0.7); 
            }
            .turret-ice::before { 
                width: 8px; height: 100%; top: 0; left: calc(50% - 4px); 
                border-radius: 3px 3px 0 0; 
            }
            .turret-ice::after { 
                width: 100%; height: 6px; top: 30%; left: 0; 
                border-radius: 2px; transform: rotate(-10deg); 
            }
            .turret-ice.firing::after { 
                content: ''; position: absolute; top: -10px; left: 50%; 
                transform: translateX(-50%); width: 24px; height: 24px; 
                background: radial-gradient(circle, rgba(224,255,255,0.8) 10%, rgba(175,238,238,0.4) 40%, transparent 70%); 
                border-radius: 50%; opacity: 1; animation: quick-fade 0.2s forwards; 
            }
            
            .frost-mote { 
                position: absolute; width: 3px; height: 3px; 
                background-color: rgba(220, 240, 255, 0.7); border-radius: 50%; 
                pointer-events: none; animation: floatMote 4s linear infinite; opacity: 0; 
            }
            @keyframes floatMote { 
                0% { transform: translate(0,0) scale(0.5); opacity: 0; } 
                25% { opacity: 0.7; } 
                75% { opacity: 0.7; } 
                100% { transform: translate(calc(var(--mote-dx) * 1px), calc(var(--mote-dy) * 1px)) scale(1.2); opacity: 0; } 
            }
            
            .ice-crystal-projectile { 
                width: 14px; height: 14px; position: relative; 
                animation: spin 0.7s linear infinite; 
            }
            .ice-crystal-projectile::before, .ice-crystal-projectile::after { 
                content: ''; position: absolute; 
                background-color: #afeeee; 
                box-shadow: 0 0 3px #fff, 0 0 5px #add8e6; border-radius: 1px; 
            }
            .ice-crystal-projectile::before { 
                width: 100%; height: 20%; top: 40%; left: 0; 
            }
            .ice-crystal-projectile::after { 
                width: 20%; height: 100%; top: 0; left: 40%; 
            }
            .ice-crystal-projectile > div { 
                position: absolute; width: 100%; height: 20%; top: 40%; left: 0; 
                background-color: #afeeee; 
                box-shadow: 0 0 3px #fff, 0 0 5px #add8e6; border-radius: 1px; 
                transform: rotate(45deg); 
            }
            @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
            
            .ice-shatter { 
                width: 50px; height: 50px; transform: translate(-50%, -50%); 
                animation: ice-shatter-anim 0.4s forwards; 
            }
            @keyframes ice-shatter-anim { 
                0% { opacity: 1; transform: translate(-50%, -50%) scale(0.5); } 
                100% { opacity: 0; transform: translate(-50%, -50%) scale(1.5) rotate(120deg); } 
            }
            
            .ice-splash-effect { 
                width: var(--splash-radius, 60px); height: var(--splash-radius, 60px); 
                border: 2px solid rgba(173, 216, 230, 0.5); 
                background-color: rgba(200, 240, 255, 0.2); border-radius: 50%; 
                animation: splash-anim 0.3s ease-out forwards; 
                transform: translate(-50%, -50%); 
            }
            @keyframes splash-anim { 
                0% { transform: translate(-50%,-50%) scale(0.1); opacity: 0.7; } 
                100% { transform: translate(-50%,-50%) scale(1); opacity: 0; } 
            }
            
            @keyframes quick-fade { 
                0% { transform: translate(-50%, -70%) scale(1.2); opacity: 1; } 
                100% { transform: translate(-50%, -70%) scale(0.5); opacity: 0; } 
            }
        `;
    }
    
    // Get HTML structure for ice turret base
    static getHTML(position) {
        return `
            <div id="tower-ice-base" class="tower-base" style="left: ${position.x}px;">
                <div class="turret-pivot">
                    <div class="turret-model turret-ice">
                        <div class="turret-glow-indicator"></div>
                    </div>
                </div>
            </div>
        `;
    }
    
    // Get control panel HTML
    static getControlsHTML() {
        return `
            <div class="control-group" id="ice-controls">
                <h3>Ice Tower ❄️</h3>
                <label for="ice-firerate">Fire Rate (ms): <span id="ice-firerate-val">900</span></label>
                <input type="range" id="ice-firerate" min="200" max="2500" value="900" step="50">
                <label for="ice-damage">Damage: <span id="ice-damage-val">20</span></label>
                <input type="range" id="ice-damage" min="5" max="100" value="20" step="5">
                <label for="ice-projspeed">Proj. Speed: <span id="ice-projspeed-val">5</span></label>
                <input type="range" id="ice-projspeed" min="1" max="12" value="5" step="0.5">
                <label for="ice-range">Range (px): <span id="ice-range-val">400</span></label>
                <input type="range" id="ice-range" min="50" max="600" value="400" step="10">
                <div class="damage-meter" id="ice-damage-meter">
                    <div class="damage-label">Total Damage</div>
                    <div class="damage-value">0</div>
                </div>
            </div>
        `;
    }
    
    onInit() {
        // Add floating frost motes
        if (this.baseEl) {
            for (let i = 0; i < 5; i++) {
                const mote = document.createElement('div');
                mote.classList.add('frost-mote');
                mote.style.left = `${Math.random() * 80 - 40}%`;
                mote.style.top = `${Math.random() * 80 - 40}%`;
                mote.style.setProperty('--mote-dx', Math.random() * 20 - 10);
                mote.style.setProperty('--mote-dy', Math.random() * 20 - 10);
                mote.style.animationDelay = `${Math.random() * 4}s`;
                this.baseEl.appendChild(mote);
            }
        }
    }
    
    fire(target, gameConfig) {
        try {
            this.launchProjectile(target, gameConfig);
            gameConfig.playSound('ice_fire');
            this.resetCharge();
            this.fireRecoil(gameConfig);
        } catch (e) {
            console.error(`Error in ice fire for ${this.id}:`, e);
        }
    }
    
    launchProjectile(target, gameConfig) {
        const projectileEl = document.createElement('div');
        projectileEl.classList.add('projectile', this.projectileClass);
        projectileEl.id = `proj-${gameConfig.getNextProjectileId()}`;
        
        const startX = this.x + this.barrelLength * Math.cos(this.currentAngleRad);
        const startY = this.y + this.barrelLength * Math.sin(this.currentAngleRad);
        
        // Create ice crystal inner structure
        const crystalInnerDiv = document.createElement('div');
        projectileEl.appendChild(crystalInnerDiv);
        
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
            currentAngle: Math.atan2(target.y + target.height/2 - startY, target.x + target.width/2 - startX),
            age: 0,
            splashRadius: this.splashRadius,
            splashDamageFactor: this.splashDamageFactor
        };
        
        gameConfig.addProjectile(projectile);
    }
    
    // Update ice projectile movement
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
            gameConfig.createVisualEffect(targetX, targetY, 'ice_shatter');
            gameConfig.createVisualEffect(targetX, targetY, 'ice_splash', { radius: proj.splashRadius });
            gameConfig.playSound('ice_impact');
            
            // Splash damage
            if (proj.splashRadius && proj.splashDamageFactor) {
                gameConfig.enemies.forEach(otherEnemy => {
                    if (otherEnemy !== targetEnemy && otherEnemy.health > 0) {
                        const splashDist = Math.sqrt(
                            Math.pow(targetX - (otherEnemy.x + otherEnemy.width/2), 2) + 
                            Math.pow(targetY - (otherEnemy.y + otherEnemy.height/2), 2)
                        );
                        
                        if (splashDist <= proj.splashRadius) {
                            const splashDamage = proj.damage * proj.splashDamageFactor;
                            const actualSplashDamage = Math.min(splashDamage, otherEnemy.health);
                            otherEnemy.health -= splashDamage;
                            proj.tower.updateDamage(actualSplashDamage);
                            
                            if (otherEnemy.el) {
                                otherEnemy.el.classList.add('hit-ice');
                                setTimeout(() => otherEnemy.el.classList.remove('hit-ice'), 100);
                                gameConfig.createVisualEffect(
                                    otherEnemy.x + otherEnemy.width/2, 
                                    otherEnemy.y + otherEnemy.height/2, 
                                    'enemy_hit_sparkle'
                                );
                            }
                        }
                    }
                });
            }
            
            if (targetEnemy.el) {
                targetEnemy.el.classList.add('hit-ice');
                setTimeout(() => targetEnemy.el.classList.remove('hit-ice'), 100);
            }
            
            return false; // Remove projectile
        }
        
        // Straight-line movement
        const moveX = (dx / distance) * moveSpeed;
        const moveY = (dy / distance) * moveSpeed;
        
        proj.x += moveX;
        proj.y += moveY;
        proj.age++;
        
        proj.el.style.left = `${proj.x - proj.el.offsetWidth/2}px`;
        proj.el.style.top = `${proj.y - proj.el.offsetHeight/2}px`;
        
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
    
    onRecoilEnd() {
        if (this.modelEl) {
            this.modelEl.classList.remove('firing');
        }
    }
    
    fireRecoil(gameConfig) {
        super.fireRecoil(gameConfig);
        if (this.modelEl) {
            this.modelEl.classList.add('firing');
        }
    }
}

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = IceTurret;
} else if (typeof window !== 'undefined') {
    window.IceTurret = IceTurret;
}