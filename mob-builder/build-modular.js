#!/usr/bin/env node

// Build script to generate the final modular index.php
// This combines the template with turret styles and HTML

const fs = require('fs');
const path = require('path');

// Helper functions to extract content from turret files
function getTurretStyles() {
    // Combine styles from all turret files
    const turretFiles = ['rocket-turret.js', 'ice-turret.js', 'lightning-turret.js', 'machinegun-turret.js'];
    let combinedStyles = '';
    
    turretFiles.forEach(file => {
        const filePath = path.join(__dirname, 'turrets', file);
        const content = fs.readFileSync(filePath, 'utf8');
        
        // Extract styles between getStyles() method
        const styleMatch = content.match(/static getStyles\(\) \{[\s\S]*?return\s+`([\s\S]*?)`;[\s\S]*?\}/m);
        if (styleMatch) {
            combinedStyles += styleMatch[1] + '\n';
        }
    });
    
    return combinedStyles;
}

function getTurretHTML() {
    // Generate HTML for all turrets
    const turrets = [
        { type: 'rocket', position: 100 },
        { type: 'ice', position: 377.5 },
        { type: 'lightning', position: 655 },
        { type: 'machinegun', position: 250 }
    ];
    
    let html = '';
    turrets.forEach(turret => {
        html += `
        <div id="tower-${turret.type}-base" class="tower-base" style="left: ${turret.position}px;">
            <div class="turret-pivot">
                <div class="turret-model turret-${turret.type === 'lightning' ? 'lightning-barrel' : turret.type}">
                    <div class="turret-glow-indicator"></div>
                </div>
            </div>
        </div>`;
    });
    
    return html;
}

function getTurretControls() {
    // Generate controls HTML for all turrets
    const turretFiles = ['rocket-turret.js', 'ice-turret.js', 'lightning-turret.js', 'machinegun-turret.js'];
    let combinedControls = '';
    
    turretFiles.forEach(file => {
        const filePath = path.join(__dirname, 'turrets', file);
        const content = fs.readFileSync(filePath, 'utf8');
        
        // Extract controls between getControlsHTML() method
        const controlsMatch = content.match(/static getControlsHTML\(\) \{[\s\S]*?return\s+`([\s\S]*?)`;[\s\S]*?\}/m);
        if (controlsMatch) {
            combinedControls += controlsMatch[1] + '\n';
        }
    });
    
    return combinedControls;
}

function buildModularFile() {
    console.log('Building modular tower defense file...');
    
    // Read the template
    const templatePath = path.join(__dirname, 'index-modular.php');
    let template = fs.readFileSync(templatePath, 'utf8');
    
    // Read turret files and extract styles/HTML manually
    const turretStyles = getTurretStyles();
    const turretHTML = getTurretHTML();
    const controlsHTML = getTurretControls();
    
    // Replace placeholders in template
    template = template.replace('<TURRET_STYLES>', turretStyles);
    template = template.replace('<TURRET_HTML>', turretHTML);
    template = template.replace('<TURRET_CONTROLS>', controlsHTML);
    
    // Write the final file
    const outputPath = path.join(__dirname, 'index-built.php');
    fs.writeFileSync(outputPath, template, 'utf8');
    
    console.log(`✅ Built modular file: ${outputPath}`);
    console.log(`📁 Turret types included: rocket, ice, lightning, machinegun`);
    console.log(`🎯 Total turrets: 4`);
    
    return outputPath;
}

// Run if called directly
if (require.main === module) {
    try {
        buildModularFile();
        console.log('\n🚀 Build completed successfully!');
        console.log('👉 Open index-built.php in your browser to test the modular turret system.');
    } catch (error) {
        console.error('❌ Build failed:', error);
        process.exit(1);
    }
}

module.exports = buildModularFile;