const active = new WeakSet();
const previewUrls = new WeakMap();

export function validatePublicationPdf(file) {
    if (!file || !/\.pdf$/i.test(file.name)) throw new Error('Select a PDF file.');
    if (file.size > 20 * 1024 * 1024) throw new Error('The PDF must be 20 MB or smaller.');
    if (file.size === 0) throw new Error('The selected PDF is empty.');
}

export function validatePublicationCover(file) {
    if (!file || !/\.jpe?g$/i.test(file.name) || file.type !== 'image/jpeg') {
        throw new Error('Select a JPG or JPEG cover image.');
    }
    if (file.size > 2 * 1024 * 1024) throw new Error('The cover image must be 2 MB or smaller.');
    if (file.size === 0) throw new Error('The selected cover image is empty.');
}

export function uploadPublicationFile(wire, property, file, progress = () => {}) {
    return new Promise((resolve, reject) => {
        wire.upload(property, file, resolve, () => reject(new Error('The upload failed. Please select the PDF again.')), progress,
            () => reject(new Error('The upload was cancelled. Please select the PDF again.')));
    });
}

export async function preparePublicationUpload(input, type) {
    const form = input.closest('[data-publication-upload]');
    const component = input.closest('[wire\\:id]');
    const file = input.files?.[0];
    if (!form || !component || !file || active.has(form)) return;
    const wire = window.Livewire?.find(component.getAttribute('wire:id'));
    if (!wire) return;
    const status = form.querySelector('[data-publication-upload-status]');
    active.add(form);
    input.disabled = true;
    form.dispatchEvent(new CustomEvent('publication-upload-start', { bubbles: true }));
    try {
        if (type === 'pdf') {
            validatePublicationPdf(file);
            await wire.set('pdf', null, false);
            await wire.set('cover', null, false);
            const preview = form.querySelector('[data-publication-cover-preview]');
            preview.hidden = true;
            form.querySelector('[data-publication-cover-placeholder]').hidden = false;
            const title = form.querySelector('[data-publication-title]');
            if (!title.value.trim()) {
                const suggestion = file.name.replace(/\.pdf$/i, '').replace(/[_-]+/g, ' ').trim().slice(0, 255);
                title.value = suggestion;
                await wire.set('title', suggestion, false);
            }
        } else {
            validatePublicationCover(file);
        }
        const property = type === 'pdf' ? 'pdf' : 'cover';
        const label = type === 'pdf' ? 'PDF' : 'cover image';
        status.textContent = `Uploading ${label}…`;
        await uploadPublicationFile(wire, property, file, (event) => {
            status.textContent = `Uploading ${label}… ${Math.round(event.detail.progress)}%`;
        });
        if (type === 'cover') {
            const preview = form.querySelector('[data-publication-cover-preview]');
            const oldUrl = previewUrls.get(preview);
            if (oldUrl) URL.revokeObjectURL(oldUrl);
            const previewUrl = URL.createObjectURL(file);
            previewUrls.set(preview, previewUrl);
            preview.src = previewUrl;
            preview.hidden = false;
            form.querySelector('[data-publication-cover-placeholder]').hidden = true;
            status.textContent = 'PDF and cover image are ready. Save the publication below.';
        } else {
            status.textContent = 'PDF uploaded. Now upload its first-page cover image.';
        }
    } catch (error) {
        try {
            await wire.set(type === 'pdf' ? 'pdf' : 'cover', null);
        } catch {
            // Leave the upload error visible if Livewire cannot clear its temporary file.
        }
        status.textContent = error instanceof Error && /^(Select|The PDF|The cover|The selected|The upload)/.test(error.message)
            ? error.message : `The ${type === 'pdf' ? 'PDF' : 'cover image'} upload failed. Please try again.`;
        input.value = '';
    } finally {
        active.delete(form);
        input.disabled = false;
        form.dispatchEvent(new CustomEvent('publication-upload-end', { bubbles: true }));
    }
}

if (typeof document !== 'undefined') {
    document.addEventListener('change', (event) => {
        if (event.target?.matches('[data-publication-pdf-input]')) preparePublicationUpload(event.target, 'pdf');
        if (event.target?.matches('[data-publication-cover-input]')) preparePublicationUpload(event.target, 'cover');
    });
    document.addEventListener('submit', (event) => {
        if (event.target?.matches('[data-publication-upload]') && active.has(event.target)) {
            event.preventDefault();
            event.stopImmediatePropagation();
        }
    }, true);
    document.addEventListener('livewire:navigating', () => {
        document.querySelectorAll('[data-publication-cover-preview]').forEach((preview) => {
            const url = previewUrls.get(preview);
            if (url) URL.revokeObjectURL(url);
        });
    });
}
