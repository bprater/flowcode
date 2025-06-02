// Base Turret System
// Provides a common interface and base functionality for all turret types

class BaseTurret {
    constructor(config) {
        // Core properties
        this.id = config.id;
        this.type = config.type;
        this.x = 0;
        this.y = 0;
        
        // DOM elements
        this.baseEl = null;
        this.pivotEl = null;
        this.modelEl = null;
        this.glowEl = null;
        this.damageEl = null;
        
        // Stats
        this.fireRate = config.fireRate || 1000;
        this.damage = config.damage || 10;
        this.range = config.range || 300;
        this.projectileSpeed = config.projectileSpeed || 1;
        this.barrelLength = config.barrelLength || 15;
        
        // State
        this.lastShotTime = 0;
        this.currentAngleRad = -Math.PI / 2;
        this.chargeLevel = 0;
        this.totalDamage = 0;
        this.initialChargeTime = 0;
        
        // Visual/audio
        this.recoilClass = 'recoil-active';
        this.projectileClass = config.projectileClass || 'bullet';
        
        // Special properties (overridden by subclasses)
        this.splashRadius = config.splashRadius || 0;
        this.splashDamageFactor = config.splashDamageFactor || 0;
    }
    
    // Initialize DOM elements and positioning
    init(gameContainer) {
        this.baseEl = document.getElementById(`tower-${this.type}-base`);
        this.pivotEl = document.querySelector(`#tower-${this.type}-base .turret-pivot`);
        this.modelEl = document.querySelector(`#tower-${this.type}-base .turret-model`) || 
                      document.querySelector(`#tower-${this.type}-base .turret-${this.type}-barrel`);
        this.glowEl = document.querySelector(`#tower-${this.type}-base .turret-glow-indicator`);
        this.damageEl = document.querySelector(`#${this.type}-damage-meter .damage-value`);
        
        if (this.baseEl) {
            this.x = this.baseEl.offsetLeft + this.baseEl.offsetWidth / 2;
            this.y = this.baseEl.offsetTop + this.baseEl.offsetHeight / 2;
        }
        
        if (this.pivotEl) {
            this.pivotEl.style.transform = `rotate(${this.currentAngleRad - Math.PI/2}rad)`;
        }
        
        // Call subclass-specific initialization
        this.onInit();
    }
    
    // Override in subclasses for custom initialization
    onInit() {}
    
    // Update turret targeting and charging
    update(currentTime, enemies, gameConfig) {
        // Update charge level
        if (this.fireRate > 0) {
            if (this.lastShotTime > 0) {
                this.chargeLevel = Math.min(1, (currentTime - this.lastShotTime) / this.fireRate);
            } else if (this.initialChargeTime > 0) {
                this.chargeLevel = Math.min(1, (currentTime - this.initialChargeTime) / this.fireRate);
            } else {
                this.chargeLevel = 0;
            }
        } else {
            this.chargeLevel = 1;
        }
        
        // Update glow effect
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
            
            if (this.chargeLevel >= 1.0) {
                this.fire(target, gameConfig);
            }
        }
    }
    
    // Find the closest enemy within range
    findTarget(enemies) {
        let closestEnemy = null;
        let minDistance = this.range;
        
        for (const enemy of enemies) {
            const enemyCenterX = enemy.x + enemy.width / 2;
            const enemyCenterY = enemy.y + enemy.height / 2;
            
            const distance = Math.sqrt(
                Math.pow(this.x - enemyCenterX, 2) + 
                Math.pow(this.y - enemyCenterY, 2)
            );
            
            if (distance < minDistance) {
                minDistance = distance;
                closestEnemy = enemy;
            }
        }
        
        return closestEnemy;
    }
    
    // Aim turret at target
    aimAt(target) {
        const targetX = target.x + target.width / 2;
        const targetY = target.y + target.height / 2;
        const angleToTargetRad = Math.atan2(targetY - this.y, targetX - this.x);
        
        this.currentAngleRad = angleToTargetRad;
        
        if (this.pivotEl) {
            this.pivotEl.style.transform = `rotate(${angleToTargetRad - Math.PI/2}rad)`;
        }
    }
    
    // Fire at target - override in subclasses
    fire(target, gameConfig) {
        console.warn(`Fire method not implemented for turret type: ${this.type}`);
    }
    
    // Update damage tracking
    updateDamage(damageDealt) {
        this.totalDamage += damageDealt;
        if (this.damageEl) {
            this.damageEl.textContent = Math.round(this.totalDamage).toLocaleString();
        }
    }
    
    // Create muzzle flash and recoil effects
    fireRecoil(gameConfig) {
        if (this.modelEl && this.recoilClass) {
            const muzzleX = this.x + this.barrelLength * Math.cos(this.currentAngleRad);
            const muzzleY = this.y + this.barrelLength * Math.sin(this.currentAngleRad);

            if (this.recoilClass === 'recoil-active' && this.pivotEl) {
                this.modelEl.style.setProperty('--recoil-amount', `${-gameConfig.globalRecoilMagnitude}px`);
                this.modelEl.style.animationDuration = `${gameConfig.globalRecoilDuration}ms`;
                this.pivotEl.classList.add(this.recoilClass);
            }
            
            // Call subclass-specific visual effects
            this.createMuzzleEffects(muzzleX, muzzleY, gameConfig);
            
            setTimeout(() => {
                if (this.pivotEl) this.pivotEl.classList.remove(this.recoilClass);
                this.onRecoilEnd();
            }, gameConfig.globalRecoilDuration + 50);
        }
    }
    
    // Override in subclasses for custom muzzle effects
    createMuzzleEffects(muzzleX, muzzleY, gameConfig) {}
    
    // Override in subclasses for custom recoil end behavior
    onRecoilEnd() {}
    
    // Reset charge after firing
    resetCharge() {
        this.lastShotTime = performance.now();
        this.chargeLevel = 0;
        
        if (this.glowEl) {
            this.glowEl.style.opacity = 0;
            this.glowEl.style.boxShadow = '0 0 0px 0px transparent';
        }
    }
}

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = BaseTurret;
} else if (typeof window !== 'undefined') {
    window.BaseTurret = BaseTurret;
}