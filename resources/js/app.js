import Trix from 'trix';

Trix.config.blockAttributes.heading1.tagName = 'h2';

/*
 * Media uploads will be connected to the AWCMS Media Library later.
 * Until then, pasted and dropped files are blocked.
 */
document.addEventListener('trix-file-accept', (event) => {
    event.preventDefault();
});