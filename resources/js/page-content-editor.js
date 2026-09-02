window.awcmsPageContentEditor = function awcmsPageContentEditor(
    initialContent = '',
    initialMode = 'visual',
) {
    return {
        mode: initialMode === 'html' ? 'html' : 'visual',
        content: typeof initialContent === 'string' ? initialContent : '',
        fullscreen: false,
        advancedWarning: false,
        savedRange: null,
        history: [],
        historyIndex: -1,
        restoringHistory: false,

        init() {
            this.$nextTick(() => {
                this.$refs.visual.innerHTML = this.content;
                this.$refs.code.value = this.prettyFormatHtml(this.content);
                this.pushHistory(this.content, true);
                this.syncLivewireInputs();
            });
        },

        destroy() {
            document.documentElement.style.overflow = '';
        },

        syncLivewireInputs() {
            this.$refs.contentInput.value = this.content;
            this.$refs.contentInput.dispatchEvent(
                new Event('input', { bubbles: true }),
            );

            this.$refs.modeInput.value = this.mode;
            this.$refs.modeInput.dispatchEvent(
                new Event('input', { bubbles: true }),
            );
        },

        syncFromVisual(recordHistory = true) {
            this.normaliseVisualMarkup();
            this.content = this.$refs.visual.innerHTML.trim();
            this.$refs.code.value = this.prettyFormatHtml(this.content);

            if (recordHistory && ! this.restoringHistory) {
                this.pushHistory(this.content);
            }

            this.syncLivewireInputs();
        },

        syncFromCode() {
            this.content = this.$refs.code.value;
            this.syncLivewireInputs();
        },

        switchMode(targetMode) {
            const target = targetMode === 'html' ? 'html' : 'visual';

            if (target === this.mode) {
                return;
            }

            if (this.mode === 'visual') {
                this.syncFromVisual();
            } else {
                this.syncFromCode();
            }

            if (target === 'visual') {
                const advanced = this.hasAdvancedMarkup(this.content);

                if (
                    advanced
                    && ! window.confirm(
                        'Advanced HTML/Tailwind markup was detected. Opening it in the Visual Editor may change complex layout markup if you edit and save it.\n\nChoose OK to open visually, or Cancel to stay in HTML + Tailwind.',
                    )
                ) {
                    return;
                }

                this.advancedWarning = advanced;
                this.$refs.visual.innerHTML = this.content;
                this.pushHistory(this.content, true);
            } else {
                this.advancedWarning = false;
                this.content = this.prettyFormatHtml(this.content);
                this.$refs.code.value = this.content;
            }

            this.mode = target;
            this.syncLivewireInputs();

            this.$nextTick(() => {
                if (this.mode === 'visual') {
                    this.$refs.visual.focus();
                    this.rememberSelection();
                } else {
                    this.$refs.code.focus();
                }
            });
        },

        hasAdvancedMarkup(html) {
            if (typeof html !== 'string' || html.trim() === '') {
                return false;
            }

            return /\bclass\s*=\s*["'][^"']+["']/i.test(html)
                || /<(section|main|article|header|footer|nav|aside)\b/i.test(html)
                || /\b(?:sm|md|lg|xl|2xl):[\w\-\[\]\/.:]+/i.test(html);
        },

        selectionIsInsideEditor(selection = window.getSelection()) {
            if (! selection || selection.rangeCount === 0) {
                return false;
            }

            const anchor = selection.anchorNode;
            const focus = selection.focusNode;

            return Boolean(
                anchor
                && focus
                && this.$refs.visual.contains(anchor)
                && this.$refs.visual.contains(focus),
            );
        },


        captureToolbarSelection() {
            if (this.mode !== 'visual') {
                return;
            }

            const selection = window.getSelection();

            if (this.selectionIsInsideEditor(selection)) {
                this.savedRange = selection.getRangeAt(0).cloneRange();
            }
        },

        rememberSelection() {
            if (this.mode !== 'visual') {
                return;
            }

            const selection = window.getSelection();

            if (! this.selectionIsInsideEditor(selection)) {
                return;
            }

            this.savedRange = selection.getRangeAt(0).cloneRange();
        },

        restoreSelection() {
            this.$refs.visual.focus({ preventScroll: true });

            if (! this.savedRange) {
                return false;
            }

            const selection = window.getSelection();

            if (! selection) {
                return false;
            }

            try {
                selection.removeAllRanges();
                selection.addRange(this.savedRange);

                return true;
            } catch (_error) {
                this.savedRange = null;

                return false;
            }
        },

        currentRange() {
            this.restoreSelection();

            const selection = window.getSelection();

            if (! this.selectionIsInsideEditor(selection)) {
                return null;
            }

            return selection.getRangeAt(0);
        },

        selectNodeContents(node) {
            const selection = window.getSelection();

            if (! selection) {
                return;
            }

            const range = document.createRange();
            range.selectNodeContents(node);
            selection.removeAllRanges();
            selection.addRange(range);
            this.savedRange = range.cloneRange();
        },

        runNativeCommand(command, value = null) {
            this.restoreSelection();

            try {
                return document.execCommand(command, false, value);
            } catch (_error) {
                return false;
            }
        },

        runCommand(command, value = null) {
            switch (command) {
                case 'bold':
                    this.applyInlineTag('strong', 'bold');
                    return;
                case 'italic':
                    this.applyInlineTag('em', 'italic');
                    return;
                case 'underline':
                    this.applyInlineTag('u', 'underline');
                    return;
                case 'justifyLeft':
                    this.applyAlignment('left');
                    return;
                case 'justifyCenter':
                    this.applyAlignment('center');
                    return;
                case 'justifyRight':
                    this.applyAlignment('right');
                    return;
                case 'justifyFull':
                    this.applyAlignment('justify');
                    return;
                case 'insertUnorderedList':
                    this.applyList('ul', 'insertUnorderedList');
                    return;
                case 'insertOrderedList':
                    this.applyList('ol', 'insertOrderedList');
                    return;
                case 'undo':
                    this.undoVisual();
                    return;
                case 'redo':
                    this.redoVisual();
                    return;
                default:
                    this.runNativeCommand(command, value);
                    this.finishVisualCommand();
            }
        },

        applyInlineTag(tagName, nativeCommand) {
            const range = this.currentRange();

            if (! range) {
                return;
            }

            if (range.collapsed) {
                this.runNativeCommand(nativeCommand);
                this.finishVisualCommand();
                return;
            }

            const fragment = range.cloneContents();
            const containsBlock = Array.from(fragment.childNodes).some(
                (node) => node.nodeType === Node.ELEMENT_NODE
                    && this.isBlockElement(node),
            );

            if (containsBlock) {
                this.runNativeCommand(nativeCommand);
                this.finishVisualCommand();
                return;
            }

            const wrapper = document.createElement(tagName);
            wrapper.appendChild(range.extractContents());
            range.insertNode(wrapper);
            this.selectNodeContents(wrapper);
            this.finishVisualCommand();
        },

        applyInlineStyle(property, value, nativeCommand) {
            const range = this.currentRange();

            if (! range) {
                return;
            }

            if (range.collapsed) {
                this.runNativeCommand('styleWithCSS', true);
                this.runNativeCommand(nativeCommand, value);
                this.finishVisualCommand();
                return;
            }

            const wrapper = document.createElement('span');
            wrapper.style[property] = value;
            wrapper.appendChild(range.extractContents());
            range.insertNode(wrapper);
            this.selectNodeContents(wrapper);
            this.finishVisualCommand();
        },

        formatBlock(tagName) {
            const allowed = ['P', 'H1', 'H2', 'H3', 'BLOCKQUOTE'];
            const tag = String(tagName || 'P').toUpperCase();

            if (! allowed.includes(tag)) {
                return;
            }

            const range = this.currentRange();

            if (! range) {
                return;
            }

            const blocks = this.blocksForRange(range).filter((block) =>
                ['P', 'H1', 'H2', 'H3', 'BLOCKQUOTE'].includes(block.tagName),
            );

            if (blocks.length === 0) {
                this.runNativeCommand('formatBlock', `<${tag.toLowerCase()}>`);
                this.finishVisualCommand();
                return;
            }

            let lastReplacement = null;

            blocks.forEach((block) => {
                if (block.tagName === tag) {
                    lastReplacement = block;
                    return;
                }

                const replacement = document.createElement(tag.toLowerCase());

                Array.from(block.attributes).forEach((attribute) => {
                    replacement.setAttribute(attribute.name, attribute.value);
                });

                while (block.firstChild) {
                    replacement.appendChild(block.firstChild);
                }

                block.replaceWith(replacement);
                lastReplacement = replacement;
            });

            if (lastReplacement) {
                this.selectNodeContents(lastReplacement);
            }

            this.finishVisualCommand();
        },

        setTextColor(colour) {
            if (! /^#[0-9a-f]{6}$/i.test(colour)) {
                return;
            }

            const range = this.currentRange();

            if (! range) {
                return;
            }

            // Do not rely on deprecated execCommand colour handling. Some
            // Chromium builds report success without keeping the selected
            // range. Apply the safe inline style directly to the DOM instead.
            this.applyInlineStyleFallback(range, 'color', colour);
            this.finishVisualCommand();
        },

        setBackgroundColor(colour) {
            if (! /^#[0-9a-f]{6}$/i.test(colour)) {
                return;
            }

            const range = this.currentRange();

            if (! range) {
                return;
            }

            this.applyInlineStyleFallback(
                range,
                'backgroundColor',
                colour,
            );
            this.finishVisualCommand();
        },

        applyInlineStyleFallback(range, property, value) {
            if (range.collapsed) {
                const span = document.createElement('span');
                span.style[property] = value;
                span.appendChild(document.createTextNode('\u200b'));
                range.insertNode(span);

                const selection = window.getSelection();
                const nextRange = document.createRange();
                nextRange.setStart(span.firstChild, 1);
                nextRange.collapse(true);

                if (selection) {
                    selection.removeAllRanges();
                    selection.addRange(nextRange);
                }

                this.savedRange = nextRange.cloneRange();
                return;
            }

            const fragment = range.cloneContents();
            const containsBlock = Array.from(fragment.childNodes).some(
                (node) => node.nodeType === Node.ELEMENT_NODE
                    && this.isBlockElement(node),
            );

            if (! containsBlock) {
                const span = document.createElement('span');
                span.style[property] = value;
                span.appendChild(range.extractContents());
                range.insertNode(span);
                this.selectNodeContents(span);
                return;
            }

            this.blocksForRange(range).forEach((block) => {
                block.style[property] = value;
            });
        },

        applyAlignment(alignment) {
            if (! ['left', 'center', 'right', 'justify'].includes(alignment)) {
                return;
            }

            const range = this.currentRange();

            if (! range) {
                return;
            }

            const blocks = this.blocksForRange(range);

            if (blocks.length === 0) {
                const command = {
                    left: 'justifyLeft',
                    center: 'justifyCenter',
                    right: 'justifyRight',
                    justify: 'justifyFull',
                }[alignment];

                this.runNativeCommand(command);
                this.finishVisualCommand();
                return;
            }

            blocks.forEach((block) => {
                block.style.textAlign = alignment;
            });

            this.finishVisualCommand();
        },

        applyList(listTag, nativeCommand) {
            const range = this.currentRange();

            if (! range) {
                return;
            }

            if (this.runNativeCommand(nativeCommand)) {
                this.finishVisualCommand();
                return;
            }

            const block = this.closestBlock(range.startContainer);

            if (! block || ! ['P', 'DIV', 'H1', 'H2', 'H3', 'BLOCKQUOTE'].includes(block.tagName)) {
                return;
            }

            const list = document.createElement(listTag);
            const item = document.createElement('li');

            while (block.firstChild) {
                item.appendChild(block.firstChild);
            }

            list.appendChild(item);
            block.replaceWith(list);
            this.selectNodeContents(item);
            this.finishVisualCommand();
        },

        createLink() {
            this.restoreSelection();

            const url = window.prompt(
                'Enter the link URL. You may use https://, mailto:, tel: or a relative website path.',
                'https://',
            );

            if (! url || url.trim() === '') {
                return;
            }

            const cleanUrl = url.trim();
            const range = this.currentRange();

            if (range && ! range.collapsed) {
                const anchor = document.createElement('a');
                anchor.href = cleanUrl;
                anchor.appendChild(range.extractContents());
                range.insertNode(anchor);
                this.selectNodeContents(anchor);
            } else {
                const anchor = document.createElement('a');
                anchor.href = cleanUrl;
                anchor.textContent = cleanUrl;
                this.insertElement(anchor, false);
            }

            this.finishVisualCommand();
        },

        insertImage() {
            this.restoreSelection();

            const url = window.prompt(
                'Enter the image URL. For AWCMS media, copy the public image URL from the Media Library.',
                '',
            );

            if (! url || url.trim() === '') {
                return;
            }

            const alt = window.prompt(
                'Enter alternative text for accessibility.',
                '',
            );

            const image = document.createElement('img');
            image.src = url.trim();
            image.alt = (alt || '').trim();

            this.insertElement(image, false);
            this.finishVisualCommand();
        },

        insertTable() {
            this.restoreSelection();

            const rowsInput = window.prompt(
                'Number of rows (1-20):',
                '3',
            );

            if (rowsInput === null) {
                return;
            }

            const columnsInput = window.prompt(
                'Number of columns (1-10):',
                '3',
            );

            if (columnsInput === null) {
                return;
            }

            const rows = Math.min(
                20,
                Math.max(1, Number.parseInt(rowsInput, 10) || 1),
            );

            const columns = Math.min(
                10,
                Math.max(1, Number.parseInt(columnsInput, 10) || 1),
            );

            const table = document.createElement('table');
            const body = document.createElement('tbody');

            for (let rowIndex = 0; rowIndex < rows; rowIndex += 1) {
                const row = document.createElement('tr');

                for (
                    let columnIndex = 0;
                    columnIndex < columns;
                    columnIndex += 1
                ) {
                    const cell = document.createElement(
                        rowIndex === 0 ? 'th' : 'td',
                    );

                    cell.textContent = rowIndex === 0
                        ? `Heading ${columnIndex + 1}`
                        : `Cell ${rowIndex + 1}.${columnIndex + 1}`;

                    row.appendChild(cell);
                }

                body.appendChild(row);
            }

            table.appendChild(body);
            this.insertElement(table, false);
            this.finishVisualCommand();
        },

        insertElement(element, sync = true) {
            const range = this.currentRange();
            const selection = window.getSelection();

            if (! range || ! selection) {
                this.$refs.visual.appendChild(element);

                if (sync) {
                    this.finishVisualCommand();
                }

                return;
            }

            range.deleteContents();
            range.insertNode(element);

            const spacer = document.createElement('br');
            element.after(spacer);

            range.setStartAfter(spacer);
            range.collapse(true);
            selection.removeAllRanges();
            selection.addRange(range);
            this.savedRange = range.cloneRange();

            if (sync) {
                this.finishVisualCommand();
            }
        },

        clearFormatting() {
            const range = this.currentRange();

            if (! range) {
                return;
            }

            if (this.runNativeCommand('removeFormat')) {
                this.normaliseVisualMarkup();
                this.finishVisualCommand();
                return;
            }

            if (! range.collapsed) {
                const text = range.toString();
                range.deleteContents();
                const textNode = document.createTextNode(text);
                range.insertNode(textNode);
                this.selectNodeContents(textNode);
                this.finishVisualCommand();
            }
        },

        finishVisualCommand() {
            this.normaliseVisualMarkup();
            this.syncFromVisual();
            this.rememberSelection();
        },

        isBlockElement(node) {
            return node instanceof Element
                && /^(ADDRESS|ARTICLE|ASIDE|BLOCKQUOTE|DIV|FIGCAPTION|FIGURE|FOOTER|H1|H2|H3|H4|H5|H6|HEADER|HR|LI|MAIN|NAV|OL|P|PRE|SECTION|TABLE|TBODY|TD|TFOOT|TH|THEAD|TR|UL)$/.test(node.tagName);
        },

        closestBlock(node) {
            let current = node instanceof Element ? node : node?.parentElement;

            while (current && current !== this.$refs.visual) {
                if (this.isBlockElement(current)) {
                    return current;
                }

                current = current.parentElement;
            }

            return null;
        },

        blocksForRange(range) {
            const selector = 'p,h1,h2,h3,blockquote,li,td,th';
            const blocks = Array.from(this.$refs.visual.querySelectorAll(selector));
            const selected = blocks.filter((block) => {
                try {
                    return range.intersectsNode(block);
                } catch (_error) {
                    return false;
                }
            });

            if (selected.length > 0) {
                return selected;
            }

            const current = this.closestBlock(range.startContainer);

            return current ? [current] : [];
        },

        normaliseVisualMarkup() {
            const editor = this.$refs.visual;

            editor.querySelectorAll('font').forEach((font) => {
                const span = document.createElement('span');
                const colour = font.getAttribute('color');
                const background = font.style.backgroundColor;

                if (colour) {
                    span.style.color = colour;
                }

                if (background) {
                    span.style.backgroundColor = background;
                }

                while (font.firstChild) {
                    span.appendChild(font.firstChild);
                }

                font.replaceWith(span);
            });

            editor.querySelectorAll('[align]').forEach((element) => {
                const alignment = element.getAttribute('align');

                if (
                    alignment
                    && ['left', 'center', 'right', 'justify'].includes(
                        alignment.toLowerCase(),
                    )
                ) {
                    element.style.textAlign = alignment.toLowerCase();
                }

                element.removeAttribute('align');
            });

            editor.querySelectorAll('b').forEach((element) => {
                const strong = document.createElement('strong');
                strong.innerHTML = element.innerHTML;
                element.replaceWith(strong);
            });

            editor.querySelectorAll('i').forEach((element) => {
                const emphasis = document.createElement('em');
                emphasis.innerHTML = element.innerHTML;
                element.replaceWith(emphasis);
            });

            editor.querySelectorAll('[style=""]').forEach((element) => {
                element.removeAttribute('style');
            });
        },

        pushHistory(html, reset = false) {
            const snapshot = typeof html === 'string' ? html : '';

            if (reset) {
                this.history = [snapshot];
                this.historyIndex = 0;
                return;
            }

            if (this.history[this.historyIndex] === snapshot) {
                return;
            }

            this.history = this.history.slice(0, this.historyIndex + 1);
            this.history.push(snapshot);

            if (this.history.length > 100) {
                this.history.shift();
            }

            this.historyIndex = this.history.length - 1;
        },

        undoVisual() {
            if (this.historyIndex <= 0) {
                return;
            }

            this.historyIndex -= 1;
            this.restoreHistorySnapshot();
        },

        redoVisual() {
            if (this.historyIndex >= this.history.length - 1) {
                return;
            }

            this.historyIndex += 1;
            this.restoreHistorySnapshot();
        },

        restoreHistorySnapshot() {
            const snapshot = this.history[this.historyIndex] ?? '';
            this.restoringHistory = true;
            this.content = snapshot;
            this.$refs.visual.innerHTML = snapshot;
            this.$refs.code.value = this.prettyFormatHtml(snapshot);
            this.syncLivewireInputs();
            this.restoringHistory = false;
            this.$refs.visual.focus({ preventScroll: true });
            this.savedRange = null;
        },

        formatCode() {
            this.syncFromCode();
            this.content = this.prettyFormatHtml(this.content);
            this.$refs.code.value = this.content;
            this.syncLivewireInputs();
        },

        prettyFormatHtml(html) {
            if (typeof html !== 'string' || html.trim() === '') {
                return '';
            }

            const template = document.createElement('template');
            template.innerHTML = html.trim();

            return Array.from(template.content.childNodes)
                .map((node) => this.serializePrettyNode(node, 0))
                .filter((line) => line !== '')
                .join('\n')
                .trim();
        },

        serializePrettyNode(node, depth) {
            const indent = '    '.repeat(depth);

            if (node.nodeType === Node.COMMENT_NODE) {
                return `${indent}<!--${node.textContent ?? ''}-->`;
            }

            if (node.nodeType === Node.TEXT_NODE) {
                const text = (node.textContent ?? '').trim();

                return text === '' ? '' : `${indent}${text}`;
            }

            if (! (node instanceof Element)) {
                return '';
            }

            const tag = node.tagName.toLowerCase();
            const opening = this.openingTag(node);
            const voidTags = new Set([
                'area', 'base', 'br', 'col', 'embed', 'hr', 'img', 'input',
                'link', 'meta', 'param', 'source', 'track', 'wbr',
            ]);

            if (voidTags.has(tag)) {
                return `${indent}${opening}`;
            }

            const blockTags = new Set([
                'article', 'aside', 'blockquote', 'div', 'figcaption', 'figure',
                'footer', 'form', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'header',
                'li', 'main', 'nav', 'ol', 'p', 'pre', 'section', 'table',
                'tbody', 'td', 'tfoot', 'th', 'thead', 'tr', 'ul',
            ]);

            const hasBlockChild = Array.from(node.children).some((child) =>
                blockTags.has(child.tagName.toLowerCase()),
            );

            if (! hasBlockChild) {
                return `${indent}${opening}${node.innerHTML.trim()}</${tag}>`;
            }

            const children = Array.from(node.childNodes)
                .map((child) => this.serializePrettyNode(child, depth + 1))
                .filter((line) => line !== '')
                .join('\n');

            if (children === '') {
                return `${indent}${opening}</${tag}>`;
            }

            return `${indent}${opening}\n${children}\n${indent}</${tag}>`;
        },

        openingTag(element) {
            const clone = element.cloneNode(false);
            const tag = element.tagName.toLowerCase();
            const outer = clone.outerHTML;
            const closing = `</${tag}>`;

            return outer.endsWith(closing)
                ? outer.slice(0, -closing.length)
                : outer;
        },

        toggleFullscreen() {
            this.fullscreen = ! this.fullscreen;
            document.documentElement.style.overflow = this.fullscreen
                ? 'hidden'
                : '';

            this.$nextTick(() => {
                if (this.mode === 'visual') {
                    this.$refs.visual.focus();
                } else {
                    this.$refs.code.focus();
                }
            });
        },
    };
};
