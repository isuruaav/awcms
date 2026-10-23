export function publicationPdfAssets(emittedWorkerUrl, moduleUrl, version, development = false) {
    // Laravel supplies the mounted/CDN URL of the module. Keep the worker beside it.
    const worker = new URL(development ? emittedWorkerUrl : emittedWorkerUrl.split('/').pop(), moduleUrl);
    const assets = new URL(development ? '../' : `../pdfjs/${version}/`, worker);
    return { worker: worker.href, assets: assets.href };
}
