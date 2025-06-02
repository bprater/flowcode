// Turret Manager
// Manages registration, creation, and coordination of all turret types

class TurretManager {
    constructor() {
        this.turretTypes = new Map();
        this.activeTurrets = [];
        this.projectiles = [];
        this.projectileIdCounter = 0;
        this.effectIdCounter = 0;
    }
    
    // Register a turret type
    registerTurretType(name, turretClass) {
        this.turretTypes.set(name, turretClass);
    }
    
    // Get all registered turret types
    getTurretTypes() {
        return Array.from(this.turretTypes.keys());
    }
    
    // Create a turret instance
    createTurret(type, config = {}) {
        const TurretClass = this.turretTypes.get(type);
        if (!TurretClass) {
            console.error(`Unknown turret type: ${type}`);
            return null;
        }
        
        const turret = new TurretClass(config);
        this.activeTurrets.push(turret);
        return turret;
    }
    
    // Initialize all turrets
    initializeTurrets(gameContainer) {
        this.activeTurrets.forEach(turret => {
            turret.init(gameContainer);
        });
    }
    
    // Update all turrets
    updateTurrets(currentTime, deltaTime, enemies, gameConfig) {
        if (gameConfig.isPaused) return;
        
        this.activeTurrets.forEach(turret => {
            turret.update(currentTime, enemies, gameConfig);
        });
    }
    
    // Add projectile to tracking
    addProjectile(projectile) {
        this.projectiles.push(projectile);
    }
    
    // Update all projectiles
    updateProjectiles(deltaTime, gameConfig) {
        if (gameConfig.isPaused) return;
        
        for (let i = this.projectiles.length - 1; i >= 0; i--) {
            const proj = this.projectiles[i];
            
            // Get the appropriate update function for this projectile type
            const TurretClass = this.turretTypes.get(proj.towerType);
            let keepProjectile = true;
            
            if (TurretClass && TurretClass.updateProjectile) {
                keepProjectile = TurretClass.updateProjectile(proj, deltaTime, gameConfig);
            } else {
                // Default projectile behavior for unknown types
                keepProjectile = this.updateGenericProjectile(proj, deltaTime, gameConfig);
            }
            
            // Remove projectile if it should be destroyed
            if (!keepProjectile || this.isProjectileOutOfBounds(proj, gameConfig)) {
                if (proj.el) proj.el.remove();
                this.projectiles.splice(i, 1);
            }
        }
    }
    
    // Generic projectile update for simple bullets
    updateGenericProjectile(proj, deltaTime, gameConfig) {
        const actualDeltaFactor = deltaTime / (1000/60);
        const targetEnemy = proj.target;
        
        if (!targetEnemy || targetEnemy.health <= 0 || !gameConfig.enemies.includes(targetEnemy)) {
            return false;
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
            
            if (targetEnemy.el) {
                targetEnemy.el.classList.add('hit-generic');
                setTimeout(() => targetEnemy.el.classList.remove('hit-generic'), 100);
            }
            
            return false; // Remove projectile
        }
        
        // Move towards target
        const moveX = (dx / distance) * moveSpeed;
        const moveY = (dy / distance) * moveSpeed;
        
        proj.x += moveX;
        proj.y += moveY;
        proj.age++;
        
        proj.el.style.left = `${proj.x - proj.el.offsetWidth/2}px`;
        proj.el.style.top = `${proj.y - proj.el.offsetHeight/2}px`;
        
        return true; // Keep projectile
    }
    
    // Check if projectile is out of bounds
    isProjectileOutOfBounds(proj, gameConfig) {
        return proj.x < -50 || 
               proj.x > gameConfig.gameWidth + 50 || 
               proj.y < -50 || 
               proj.y > gameConfig.gameHeight + 100;
    }
    
    // Generate combined CSS for all registered turret types
    generateCSS() {
        let css = '';
        
        // Add base styles
        css += `
            .tower-base { 
                position: absolute; width: 45px; height: 45px; border-radius: 50%; 
                display: flex; align-items: center; justify-content: center; 
                box-shadow: 0 3px 6px rgba(0,0,0,0.4), inset 0 2px 4px rgba(0,0,0,0.2); 
                top: 50px; 
            }
            
            .turret-pivot { 
                position: absolute; width: 100%; height: 100%; top: 0; left: 0; 
                transform-origin: center center; 
            }
            
            .turret-model { 
                position: absolute; border: 1px solid #333; transform-origin: 50% 90%; 
                left: 50%; bottom: 45%; transform: translateX(-50%); 
                box-shadow: 0 1px 3px rgba(0,0,0,0.3); 
            }
            
            .turret-glow-indicator { 
                position: absolute; top: 0; left: 0; right: 0; bottom: 0; 
                border-radius: inherit; box-shadow: 0 0 0px 0px transparent; 
                opacity: 0; transition: opacity 0.1s, box-shadow 0.1s; 
                pointer-events: none; z-index: 1; 
            }
            
            .projectile { position: absolute; box-sizing: border-box; }
            .visual-effect { position: absolute; pointer-events: none; z-index: 100; }
            
            .enemy-hit-sparkle {
                position: absolute; width: 7px; height: 7px;
                background-color: #FFFACD; border-radius: 50%;
                box-shadow: 0 0 4px #FFFFE0, 0 0 7px #FFD700;
                pointer-events: none; animation: hit-sparkle-anim 0.4s ease-out forwards;
                transform: translate(-50%, -50%); z-index: 101;
            }
            @keyframes hit-sparkle-anim {
                0% { transform: translate(-50%, -50%) scale(1.5); opacity: 1; }
                100% { transform: translate(calc(-50% + var(--sparkle-dx, 0px)), calc(-50% + var(--sparkle-dy, 0px))) scale(0.1); opacity: 0; }
            }
            
            .muzzle-puff { 
                width: 25px; height: 25px; 
                background: radial-gradient(circle, rgba(180,180,180,0.7) 10%, rgba(150,150,150,0.4) 40%, transparent 60%); 
                border-radius: 50%; animation: puff-anim 0.4s ease-out forwards; 
                transform: translate(-50%, -50%); 
            }
            @keyframes puff-anim { 
                0% { transform: translate(-50%, -50%) scale(0.3); opacity: 0.9; } 
                100% { transform: translate(-50%, -50%) scale(1.8); opacity: 0; } 
            }
            
            .muzzle-flash { 
                width: 30px; height: 30px; 
                background: radial-gradient(circle, white 20%, yellow 50%, orangered 80%, transparent 90%); 
                border-radius: 50%; animation: quick-fade 0.15s forwards; 
                transform: translate(-50%, -70%); 
            }
            @keyframes quick-fade { 
                0% { transform: translate(-50%, -70%) scale(1.2); opacity: 1; } 
                100% { transform: translate(-50%, -70%) scale(0.5); opacity: 0; } 
            }
            
            .recoil-active > .turret-model { 
                animation-name: recoil-pushback-dynamic; 
                animation-timing-function: ease-out; 
            }
            @keyframes recoil-pushback-dynamic { 
                0% { transform: translateX(-50%) translateY(0); } 
                50% { transform: translateX(-50%) translateY(var(--recoil-amount)); } 
                100% { transform: translateX(-50%) translateY(0); } 
            }
            
            .enemy.hit-generic { filter: brightness(1.3) contrast(1.2); }
            .enemy.hit-rocket { filter: brightness(1.8) sepia(0.5) hue-rotate(-20deg); }
            .enemy.hit-ice { filter: brightness(1.5) saturate(2) hue-rotate(180deg); }
            .enemy.hit-lightning { filter: brightness(2.5) saturate(0.5); }
            .enemy.hit-bullet { filter: brightness(1.4) sepia(0.3) hue-rotate(10deg); }
        `;
        
        // Add turret-specific styles
        for (const [name, TurretClass] of this.turretTypes) {
            if (TurretClass.getStyles) {
                css += TurretClass.getStyles();
            }
        }
        
        return css;
    }
    
    // Generate combined HTML for all turret bases
    generateTurretHTML() {
        const positions = [
            { x: 100 },  // Rocket position
            { x: 377.5 }, // Ice position  
            { x: 655 },   // Lightning position
            { x: 250 }    // Machine gun position
        ];
        
        let html = '';
        let posIndex = 0;
        
        for (const [name, TurretClass] of this.turretTypes) {
            if (TurretClass.getHTML && posIndex < positions.length) {
                html += TurretClass.getHTML(positions[posIndex]);
                posIndex++;
            }
        }
        
        return html;
    }
    
    // Generate combined controls HTML
    generateControlsHTML() {
        let html = '';
        
        for (const [name, TurretClass] of this.turretTypes) {
            if (TurretClass.getControlsHTML) {
                html += TurretClass.getControlsHTML();
            }
        }
        
        return html;
    }
    
    // Setup controls for all turrets
    setupControls() {
        this.activeTurrets.forEach(turret => {
            const type = turret.type;
            
            // Setup sliders for each stat
            ['firerate', 'damage', 'range'].forEach(stat => {
                const slider = document.getElementById(`${type}-${stat}`);
                const valDisplay = document.getElementById(`${type}-${stat}-val`);
                
                if (slider && valDisplay) {
                    slider.addEventListener('input', () => {
                        turret[stat] = parseFloat(slider.value);
                        valDisplay.textContent = slider.value;
                    });
                    turret[stat] = parseFloat(slider.value);
                }
            });
            
            // Setup projectile speed slider (if applicable)
            if (turret.projectileSpeed !== undefined && turret.type !== 'lightning') {
                const projSpeedSlider = document.getElementById(`${type}-projspeed`);
                const projSpeedValDisplay = document.getElementById(`${type}-projspeed-val`);
                
                if (projSpeedSlider && projSpeedValDisplay) {
                    projSpeedSlider.addEventListener('input', () => {
                        turret.projectileSpeed = parseFloat(projSpeedSlider.value);
                        projSpeedValDisplay.textContent = projSpeedSlider.value;
                    });
                    turret.projectileSpeed = parseFloat(projSpeedSlider.value);
                }
            }
        });
    }
    
    // Get next projectile ID
    getNextProjectileId() {
        return ++this.projectileIdCounter;
    }
    
    // Get next effect ID
    getNextEffectId() {
        return ++this.effectIdCounter;
    }
    
    // Cleanup
    destroy() {
        this.projectiles.forEach(proj => {
            if (proj.el) proj.el.remove();
        });
        this.projectiles = [];
        this.activeTurrets = [];
    }
}

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = TurretManager;
} else if (typeof window !== 'undefined') {
    window.TurretManager = TurretManager;
}