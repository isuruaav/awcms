#!/usr/bin/env bash
set -Eeuo pipefail
umask 022

RELEASE_DIR="$(CDPATH= cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd)"
APP_DIR="${1:-}"
PHP_BIN="${PHP_BIN:-php}"
if [[ -z "$APP_DIR" || $# -gt 2 || ( $# -eq 2 && "$2" != "--check" ) ]]; then
    echo "Usage: bash deploy.sh /absolute/path/to/laravel-project [--check]" >&2
    exit 1
fi
APP_DIR="$(CDPATH= cd -- "$APP_DIR" && pwd)"
if [[ ! -f "$APP_DIR/artisan" || ! -f "$APP_DIR/.env" || ! -f "$APP_DIR/vendor/autoload.php" ]]; then
    echo "The target must be the existing project root containing artisan, .env and vendor/." >&2
    exit 1
fi
if [[ ! -d "$RELEASE_DIR/payload" || ! -f "$RELEASE_DIR/FILES.sha256" || ! -f "$RELEASE_DIR/FILES.txt" ]]; then
    echo "Extract the complete update ZIP before running this script." >&2
    exit 1
fi
command -v "$PHP_BIN" >/dev/null
command -v sha256sum >/dev/null
command -v tar >/dev/null
"$PHP_BIN" -r 'exit(PHP_VERSION_ID >= 80300 ? 0 : 1);' || {
    echo "PHP 8.3 or newer is required. Set PHP_BIN to the hosting PHP CLI executable." >&2
    exit 1
}
(cd "$RELEASE_DIR/payload" && sha256sum --check "$RELEASE_DIR/FILES.sha256")
if [[ "${2:-}" == "--check" ]]; then
    echo "Package hashes and target checks passed. No application files were changed."
    exit 0
fi

mkdir -p "$APP_DIR/storage/app/deployment-backups"
BACKUP_DIR="$(mktemp -d "$APP_DIR/storage/app/deployment-backups/2026-10-21-hero-link-XXXXXX")"
chmod 700 "$BACKUP_DIR"
: > "$BACKUP_DIR/existing-files.txt"
: > "$BACKUP_DIR/new-files.txt"
while IFS= read -r relative; do
    if [[ -e "$APP_DIR/$relative" ]]; then
        printf '%s\n' "$relative" >> "$BACKUP_DIR/existing-files.txt"
    else
        printf '%s\n' "$relative" >> "$BACKUP_DIR/new-files.txt"
    fi
done < "$RELEASE_DIR/FILES.txt"
tar -C "$APP_DIR" -czf "$BACKUP_DIR/files-before-update.tar.gz" -T "$BACKUP_DIR/existing-files.txt"
echo "File backup: $BACKUP_DIR/files-before-update.tar.gz"

cd "$APP_DIR"
MAINTENANCE_STARTED=0
trap 'code=$?; echo "Update stopped (exit $code). File backup: $BACKUP_DIR. Resolve the error before running php artisan up if this installer started maintenance mode." >&2; exit "$code"' ERR
if [[ ! -f "$APP_DIR/storage/framework/down" ]]; then
    "$PHP_BIN" artisan down --retry=60
    MAINTENANCE_STARTED=1
fi
while IFS= read -r relative; do
    mkdir -p "$(dirname -- "$APP_DIR/$relative")"
    cp -- "$RELEASE_DIR/payload/$relative" "$APP_DIR/$relative"
done < "$RELEASE_DIR/FILES.txt"
"$PHP_BIN" artisan view:clear
if [[ "$MAINTENANCE_STARTED" == "1" ]]; then
    "$PHP_BIN" artisan up
fi
trap - ERR
echo "Hero button link fix complete. File backup: $BACKUP_DIR/files-before-update.tar.gz"
echo "Refresh the English and Sinhala homepages with Ctrl+F5, then check the hero buttons."
