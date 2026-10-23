import fs from 'node:fs/promises';
import { compile } from 'tailwindcss';

// Content arrives through stdin; no shell interpolation or temporary source files.
let input = '';
for await (const chunk of process.stdin) input += chunk;
const { candidates } = JSON.parse(input);
if (!Array.isArray(candidates) || candidates.length > 4000) {
    throw new Error('Invalid profile class list.');
}

const [theme, preflight] = await Promise.all([
    fs.readFile(new URL(import.meta.resolve('tailwindcss/theme.css')), 'utf8'),
    fs.readFile(new URL(import.meta.resolve('tailwindcss/preflight.css')), 'utf8'),
]);
const compiler = await compile(`${theme}\n${preflight}\n@tailwind utilities;`);
process.stdout.write(compiler.build(candidates));
