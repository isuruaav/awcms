export async function copyPathToClipboard(
    input,
    clipboard = globalThis.navigator?.clipboard,
    document = globalThis.document,
) {
    if (clipboard?.writeText) {
        try {
            await clipboard.writeText(input.value);
            return true;
        } catch (_error) {
            // HTTP sites and denied clipboard permission can use selection instead.
        }
    }

    input.focus();
    input.select();

    try {
        return document.execCommand('copy') === true;
    } catch (_error) {
        return false;
    }
}

window.awcmsCopyPath = function awcmsCopyPath() {
    return {
        copied: false,
        message: '',

        async copy() {
            this.copied = await copyPathToClipboard(this.$refs.path);
            this.message = this.copied
                ? 'Path copied.'
                : 'Path selected. Press Ctrl+C or use your device’s Copy command.';
        },
    };
};
