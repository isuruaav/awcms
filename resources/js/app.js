import Trix from 'trix';

/*
 * AWCMS Rich Text Editor
 *
 * Trix uses "heading1" internally.
 * Render it as <h2> so article/page content does not
 * introduce a second page-level <h1>.
 */
Trix.config.blockAttributes.heading1.tagName = 'h2';

/*
 * File attachments are disabled.
 *
 * Images and documents must be managed through
 * the controlled AWCMS Media Library.
 */
document.addEventListener('trix-file-accept', (event) => {
    event.preventDefault();
});