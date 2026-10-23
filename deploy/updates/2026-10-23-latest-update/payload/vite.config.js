import {
    defineConfig
} from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from "@tailwindcss/vite";
import { cpSync, mkdirSync, readFileSync } from 'node:fs';
import { resolve } from 'node:path';

const pdfjsVersion = JSON.parse(readFileSync('node_modules/pdfjs-dist/package.json', 'utf8')).version;

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/passkeys.js',
            ],
            refresh: true,
            fonts: [
                bunny('Instrument Sans', {
                    weights: [400, 500, 600],
                }),
            ],
        }),
        tailwindcss(),
        {
            name: 'publication-pdf-assets',
            apply: 'build',
            writeBundle(output) {
                const target = resolve(output.dir || 'public/build', 'pdfjs', pdfjsVersion);
                mkdirSync(target, { recursive: true });
                for (const directory of ['cmaps', 'standard_fonts', 'wasm']) {
                    cpSync(resolve('node_modules/pdfjs-dist', directory), resolve(target, directory), { recursive: true });
                }
                cpSync('node_modules/pdfjs-dist/LICENSE', resolve(target, 'LICENSE'));
            },
        },
    ],
    server: {
        cors: true,
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
