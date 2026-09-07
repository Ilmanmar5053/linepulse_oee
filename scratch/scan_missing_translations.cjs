const fs = require('fs');
let code = fs.readFileSync('resources/js/services/i18n.js', 'utf8');
const dictMatch = code.match(/const MONOZUKURI_MASTER_DICTIONARY = (\[[\s\S]*?\]);/);
const dict = eval(dictMatch[1]);
dict.sort((a, b) => b[0].length - a[0].length);

function translate(text) {
    let out = text;
    for (const [from, to] of dict) {
        if (out.includes(from)) {
            out = out.split(from).join(to);
        }
    }
    return out;
}

const appCode = fs.readFileSync('resources/js/app.js', 'utf8');

const regexes = [
    /<h[1-4][^>]*>([\s\S]*?)<\/h[1-4]>/g,
    /<p[^>]*class="[^"]*(?:text-xs|text-sm|text-\[11px\])[^"]*"[^>]*>([\s\S]*?)<\/p>/g,
    /<span[^>]*class="[^"]*(?:uppercase|font-bold|font-semibold)[^"]*"[^>]*>([\s\S]*?)<\/span>/g,
    /placeholder="([^"]+)"/g,
    /<th[^>]*>([\s\S]*?)<\/th>/g,
    /<button[^>]*>([\s\S]*?)<\/button>/g,
    /<label[^>]*>([\s\S]*?)<\/label>/g,
    /<option[^>]*>([\s\S]*?)<\/option>/g
];

const extracted = new Set();
regexes.forEach(r => {
    let m;
    while ((m = r.exec(appCode)) !== null) {
        let raw = m[1].replace(/<[^>]+>/g, ' ').replace(/\s+/g, ' ').trim();
        // Remove template expressions like ${...}
        raw = raw.replace(/\$\{[^}]+\}/g, '').trim();
        if (raw.length >= 3 && !raw.startsWith('http') && !raw.includes('lucide')) {
            extracted.add(raw);
        }
    }
});

console.log('Total extracted candidate phrases:', extracted.size);
const missing = [];
extracted.forEach(phrase => {
    const res = translate(phrase);
    if (res === phrase && /[a-zA-Z]/.test(phrase)) {
        // filter out pure variable identifiers or code-like items
        if (!phrase.startsWith('bg-') && !phrase.startsWith('text-') && !phrase.includes('px-') && !phrase.includes('class=')) {
            missing.push(phrase);
        }
    }
});

console.log('Missing phrases count:', missing.length);
fs.writeFileSync('scratch/missing_phrases.json', JSON.stringify(missing, null, 2));
console.log('Written to scratch/missing_phrases.json');
