import { getDocument, GlobalWorkerOptions, version } from 'pdfjs-dist';
import workerUrl from 'pdfjs-dist/build/pdf.worker.min.mjs?url';
import { publicationPdfAssets } from './publication-pdf-assets.js';

const urls = publicationPdfAssets(workerUrl, import.meta.url, version, import.meta.env.DEV);
GlobalWorkerOptions.workerSrc = urls.worker;
const assets = urls.assets;

export async function firstPageCover(file) {
    const task = getDocument({
        data: new Uint8Array(await file.arrayBuffer()),
        cMapUrl: `${assets}cmaps/`, cMapPacked: true,
        standardFontDataUrl: `${assets}standard_fonts/`, wasmUrl: `${assets}wasm/`,
        useSystemFonts: true, isEvalSupported: false, maxImageSize: 24000000,
    });
    try {
        const pdf = await task.promise;
        const page = await pdf.getPage(1);
        const initial = page.getViewport({ scale: 1 });
        if (!(initial.width > 0 && initial.height > 0)) throw new Error('Invalid PDF page dimensions.');
        const viewport = page.getViewport({ scale: Math.min(800 / initial.width, 1200 / initial.height, 2) });
        const canvas = document.createElement('canvas');
        canvas.width = Math.max(1, Math.ceil(viewport.width));
        canvas.height = Math.max(1, Math.ceil(viewport.height));
        await page.render({ canvas, canvasContext: canvas.getContext('2d'), viewport, background: 'rgb(255,255,255)', annotationMode: 0 }).promise;
        const blob = await new Promise((resolve) => canvas.toBlob(resolve, 'image/jpeg', 0.88));
        if (!blob) throw new Error('The PDF cover could not be created.');
        return new File([blob], 'publication-cover.jpg', { type: 'image/jpeg' });
    } finally {
        await task.destroy();
    }
}
