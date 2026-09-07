const fs = require('fs');
const content = fs.readFileSync('resources/js/app.js', 'utf8');

// Check for undeclared isLight
const lines = content.split('\n');
let currentFunction = '';
let isLightDeclared = false;

lines.forEach((line, idx) => {
    if (line.match(/(async\s+)?([a-zA-Z0-9_]+)\s*\([^)]*\)\s*\{/)) {
        currentFunction = line.trim();
        isLightDeclared = false;
    }
    if (line.includes('const isLight') || line.includes('let isLight') || line.includes('var isLight')) {
        isLightDeclared = true;
    }
    if (line.includes('isLight') && !isLightDeclared && !line.includes('const isLight') && !line.includes('let isLight')) {
        console.log(`Potential undeclared isLight at line ${idx + 1} inside: ${currentFunction}\n  ${line.trim()}`);
    }
});
