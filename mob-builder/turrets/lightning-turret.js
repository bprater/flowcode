// Lightning Turret
// Fires instant lightning bolts with branching tendrils

class LightningTurret extends BaseTurret {
    constructor(config = {}) {
        super({
            type: 'lightning',
            fireRate: 700,
            damage: 35,
            projectileSpeed: 0, // Instant hit
            range: 400,
            barrelLength: 20,
            projectileClass: 'lightning-bolt',
            ...config
        });
    }
    
    // Get CSS styles for lightning turret
    static getStyles() {
        return `
            .turret-lightning-barrel { 
                width: 18px; height: 28px; 
                background: linear-gradient(#687A8F, #394653); 
                border-radius: 4px 4px 1px 1px; border: 1px solid #2A343F; 
                box-shadow: 0 1px 2px rgba(0,0,0,0.4); 
            }
            .turret-lightning-barrel::before { 
                content: ''; position: absolute; top: 5px; left: 50%; 
                transform: translateX(-50%); width: 100%; height: 5px; 
                background: #7DF9FF; border-radius: 2px; 
                box-shadow: 0 0 3px #7DF9FF, inset 0 0 2px rgba(255,255,255,0.5); 
                opacity: 0.7; 
            }
            .turret-lightning-barrel::after { 
                content: ''; position: absolute; top: -3px; left: calc(50% - 3px); 
                width: 6px; height: 6px; background: #00FFFF; border-radius: 50%; 
                box-shadow: 0 0 5px #00FFFF, 0 0 10px #7DF9FF; 
                animation: lightning-tip-pulse 1s infinite alternate; 
            }
            @keyframes lightning-tip-pulse { 
                0% { opacity: 0.6; transform: scale(0.8); } 
                100% { opacity: 1; transform: scale(1.2); } 
            }
            
            .lightning-strike-segment, .lightning-tendril-segment { 
                position: absolute; background-color: #ADD8E6; height: 2px; 
                transform-origin: 0 50%; 
                box-shadow: 0 0 4px #7DF9FF, 0 0 7px white; 
                animation: lightning-flash-quick 0.15s forwards; 
            }
            .lightning-tendril-segment { height: 1px; opacity: 0.7; }
            @keyframes lightning-flash-quick { 
                0% { opacity: 0; } 60% { opacity: 1; } 100% { opacity: 0; } 
            }
            
            .lightning-impact-flash { 
                width: 45px; height: 45px; 
                background: radial-gradient(circle, white 30%, #7DF9FF 70%, transparent 85%); 
                border-radius: 50%; animation: explosion-anim 0.2s forwards; 
                transform: translate(-50%, -50%); 
            }
            @keyframes explosion-anim { 
                0% { transform: translate(-50%, -50%) scale(0.2); opacity: 1; } 
                100% { transform: translate(-50%, -50%) scale(1.8); opacity: 0; } 
            }
            
            .muzzle-flash-lightning { 
                width: 35px; height: 20px; 
                background: radial-gradient(ellipse at center, white 10%, #7DF9FF 40%, rgba(0,191,255,0.5) 70%, transparent 90%); 
                border-radius: 40% 40% 50% 50% / 80% 80% 20% 20%; 
                animation: quick-fade-lightning 0.1s forwards; 
                transform: translate(-50%, -80%) rotate(0deg); opacity: 0.8; 
            }
            @keyframes quick-fade-lightning { 
                0% { transform: translate(-50%, -80%) scale(1.1); opacity: 0.8; } 
                100% { transform: translate(-50%, -80%) scale(0.4); opacity: 0; } 
            }
        `;
    }
    
    // Get HTML structure for lightning turret base
    static getHTML(position) {
        return `
            <div id="tower-lightning-base" class="tower-base" style="left: ${position.x}px;">
                <div class="turret-pivot">
                    <div class="turret-model turret-lightning-barrel">
                        <div class="turret-glow-indicator"></div>
                    </div>
                </div>
            </div>
        `;
    }
    
    // Get control panel HTML
    static getControlsHTML() {
        return `
            <div class="control-group" id="lightning-controls">
                <h3>Lightning Tower ⚡</h3>
                <label for="lightning-firerate">Fire Rate (ms): <span id="lightning-firerate-val">700</span></label>
                <input type="range" id="lightning-firerate" min="100" max="1500" value="700" step="50">
                <label for="lightning-damage">Damage: <span id="lightning-damage-val">35</span></label>
                <input type="range" id="lightning-damage" min="10" max="150" value="35" step="5">
                <label for="lightning-range">Range (px): <span id="lightning-range-val">400</span></label>
                <input type="range" id="lightning-range" min="50" max="600" value="400" step="10">
                <div class="damage-meter" id="lightning-damage-meter">
                    <div class="damage-label">Total Damage</div>
                    <div class="damage-value">0</div>
                </div>
            </div>
        `;
    }
    
    fire(target, gameConfig) {
        try {
            // Instant damage
            const actualDamage = Math.min(this.damage, target.health);
            target.health -= this.damage;
            this.updateDamage(actualDamage);
            
            // Visual effects
            this.createLightningBoltVisual(target, gameConfig);
            
            // Audio
            gameConfig.playSound('lightning_fire');
            setTimeout(() => gameConfig.playSound('lightning_impact'), 50);
            
            // Enemy hit effects
            if (target.el) {
                target.el.classList.add('hit-lightning');
                setTimeout(() => target.el.classList.remove('hit-lightning'), 100);
                gameConfig.createVisualEffect(
                    target.x + target.width/2, 
                    target.y + target.height/2, 
                    'enemy_hit_sparkle'
                );
            }
            
            this.resetCharge();
            this.fireRecoil(gameConfig);
        } catch (e) {
            console.error(`Error in lightning fire for ${this.id}:`, e);
        }
    }
    
    createLightningBoltVisual(target, gameConfig) {
        const originX = this.x + this.barrelLength * Math.cos(this.currentAngleRad);
        const originY = this.y + this.barrelLength * Math.sin(this.currentAngleRad);
        const targetX = target.x + target.width / 2;
        const targetY = target.y + target.height / 2;
        
        // Draw main lightning bolt
        this.drawSegmentedLine(
            originX, originY, targetX, targetY, 
            5, 30, 'lightning-strike-segment', true, gameConfig
        );
        
        // Draw branching tendrils
        const numTendrils = 2 + Math.floor(Math.random() * 2);
        for (let t = 0; t < numTendrils; t++) {
            const branchPointRatio = 0.3 + Math.random() * 0.4;
            const branchOriginX = originX + (targetX - originX) * branchPointRatio;
            const branchOriginY = originY + (targetY - originY) * branchPointRatio;
            const tendrilEndX = branchOriginX + (Math.random() - 0.5) * 80;
            const tendrilEndY = branchOriginY + (Math.random() - 0.5) * 80;
            
            this.drawSegmentedLine(
                branchOriginX, branchOriginY, tendrilEndX, tendrilEndY,
                3, 20, 'lightning-tendril-segment', false, gameConfig
            );
        }
        
        // Impact flash
        gameConfig.createVisualEffect(targetX, targetY, 'lightning_impact');
    }
    
    drawSegmentedLine(startX, startY, endX, endY, segments, deviation, className, isMainBolt, gameConfig) {
        let currentX = startX;
        let currentY = startY;
        const totalDx = endX - startX;
        const totalDy = endY - startY;
        
        for (let i = 0; i < segments; i++) {
            const segmentEl = document.createElement('div');
            segmentEl.id = `effect-${gameConfig.getNextEffectId()}`;
            segmentEl.classList.add(className);
            
            let nextX, nextY;
            if (i === segments - 1) {
                nextX = endX;
                nextY = endY;
            } else {
                const progress = (i + 1) / segments;
                nextX = startX + totalDx * progress + (Math.random() - 0.5) * deviation;
                nextY = startY + totalDy * progress + (Math.random() - 0.5) * deviation;
            }
            
            const dx = nextX - currentX;
            const dy = nextY - currentY;
            const length = Math.sqrt(dx * dx + dy * dy);
            const angle = Math.atan2(dy, dx) * (180 / Math.PI);
            const segmentHeight = parseFloat(getComputedStyle(segmentEl).height || '2px');
            
            segmentEl.style.width = `${length}px`;
            segmentEl.style.left = `${currentX}px`;
            segmentEl.style.top = `${currentY - segmentHeight / 2}px`;
            segmentEl.style.transform = `rotate(${angle}deg)`;
            
            gameConfig.gameContainer.appendChild(segmentEl);
            setTimeout(() => segmentEl.remove(), isMainBolt ? 150 : 100);
            
            currentX = nextX;
            currentY = nextY;
        }
    }
    
    createMuzzleEffects(muzzleX, muzzleY, gameConfig) {
        gameConfig.createVisualEffect(muzzleX, muzzleY, 'muzzle_flash_lightning', { angle: this.currentAngleRad });
    }
}

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = LightningTurret;
} else if (typeof window !== 'undefined') {
    window.LightningTurret = LightningTurret;
}