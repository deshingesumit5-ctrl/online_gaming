const fs = require('fs');
const path = require('path');

const file = path.join(__dirname, '..', 'public', 'css', 'tailwind-utilities.css');
let css = fs.readFileSync(file, 'utf8');

function extractAtRuleBlock(css, startToken) {
    const idx = css.indexOf(startToken);
    if (idx < 0) return null;
    let i = idx + startToken.length;
    while (i < css.length && css[i] !== '{') i++;
    if (css[i] !== '{') return null;
    const bodyStart = i + 1;
    let depth = 1;
    i = bodyStart;
    while (i < css.length && depth > 0) {
        const ch = css[i];
        if (ch === '{') depth++;
        else if (ch === '}') depth--;
        i++;
    }
    return {
        before: css.slice(0, idx),
        body: css.slice(bodyStart, i - 1),
        after: css.slice(i),
    };
}

css = css.replace(/@layer\s+theme\s*,\s*base\s*,\s*components\s*,\s*utilities\s*;?/g, '');

const drop = ['properties'];
for (const name of drop) {
    const extracted = extractAtRuleBlock(css, '@layer ' + name);
    if (extracted) css = extracted.before + extracted.after;
}

const keep = ['theme', 'base', 'components', 'utilities'];
let unlayered = '';
for (const name of keep) {
    let extracted = extractAtRuleBlock(css, '@layer ' + name);
    while (extracted) {
        unlayered += extracted.body;
        css = extracted.before + extracted.after;
        extracted = extractAtRuleBlock(css, '@layer ' + name);
    }
}

css = (css + unlayered)
    .replace(/@layer\s+[a-zA-Z0-9_,\s-]+\{/g, '')
    .replace(/\n{3,}/g, '\n');

fs.writeFileSync(file, css);
console.log('wrote', file, 'bytes', css.length, 'layers left', (css.match(/@layer/g) || []).length);
console.log('has hidden', css.includes('.hidden{'));
console.log('has p-5', css.includes('.p-5{'));
console.log('has md:flex', css.includes('md\\:flex') || css.includes('.md\\:flex'));
