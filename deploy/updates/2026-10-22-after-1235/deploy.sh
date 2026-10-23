#!/usr/bin/env bash
set -Eeuo pipefail
umask 022

RELEASE_DIR="$(CDPATH= cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd -P)"
APP_DIR="${1:-$PWD}"
PHP_BIN="${PHP_BIN:-php}"
COMPOSER_BIN="${COMPOSER_BIN:-composer}"

if [[ $# -gt 2 || ( $# -eq 2 && "$2" != "--check" ) ]]; then
    echo "Usage: bash deploy.sh /absolute/path/to/laravel-project [--check]" >&2
    exit 1
fi

APP_DIR="$(CDPATH= cd -- "$APP_DIR" && pwd -P)"
if [[ ! -f "$APP_DIR/artisan" || ! -f "$APP_DIR/.env" || ! -f "$APP_DIR/vendor/autoload.php" ]]; then
    echo "Run this against the existing Laravel project root containing artisan, .env and vendor/." >&2
    exit 1
fi
if [[ ! -d "$RELEASE_DIR/payload" || ! -f "$RELEASE_DIR/FILES.sha256" || ! -f "$RELEASE_DIR/FILES.txt" ]]; then
    echo "Extract the complete ZIP before running deploy.sh." >&2
    exit 1
fi

for required in "$PHP_BIN" sha256sum tar mktemp cp; do
    command -v "$required" >/dev/null
done
"$PHP_BIN" -r 'exit(PHP_VERSION_ID >= 80300 ? 0 : 1);' || {
    echo "PHP CLI 8.3+ is required. Set PHP_BIN if the host uses a version-specific executable." >&2
    exit 1
}

while IFS= read -r relative; do
    if [[ -z "$relative" || "$relative" == /* || "$relative" == *'..'* || "$relative" == *\\* ]]; then
        echo "Invalid payload path: $relative" >&2
        exit 1
    fi
    case "$relative" in
        app/*|resources/views/*|themes/school-of-signals/*|public/build/*|public/themes/school-of-signals/*) ;;
        *) echo "Unexpected payload path: $relative" >&2; exit 1 ;;
    esac
done < "$RELEASE_DIR/FILES.txt"

(cd "$RELEASE_DIR/payload" && sha256sum --check "$RELEASE_DIR/FILES.sha256")
AUTOLOAD_AUTHORITATIVE="$("$PHP_BIN" -r '$loader = require $argv[1]; echo $loader->isClassMapAuthoritative() ? "1" : "0";' "$APP_DIR/vendor/autoload.php")"
if [[ "$AUTOLOAD_AUTHORITATIVE" == "1" ]]; then
    command -v "$COMPOSER_BIN" >/dev/null || {
        echo "This installation uses an authoritative class map. Composer is required to register the new Livewire class." >&2
        exit 1
    }
fi

if [[ "${2:-}" == "--check" ]]; then
    echo "Package hashes and target checks passed. No application files were changed."
    exit 0
fi

mkdir -p "$APP_DIR/storage/app/deployment-backups"
BACKUP_DIR="$(mktemp -d "$APP_DIR/storage/app/deployment-backups/after-1235-20261022-XXXXXX")"
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
if [[ "$AUTOLOAD_AUTHORITATIVE" == "1" ]]; then
    printf '%s\n' "vendor/composer" >> "$BACKUP_DIR/existing-files.txt"
fi
tar -C "$APP_DIR" -czf "$BACKUP_DIR/files-before-update.tar.gz" -T "$BACKUP_DIR/existing-files.txt"
echo "File backup: $BACKUP_DIR/files-before-update.tar.gz"

cd "$APP_DIR"
MAINTENANCE_STARTED=0
trap 'code=$?; echo "Update stopped (exit $code). Backup: $BACKUP_DIR. Resolve the error before running php artisan up if this installer started maintenance mode." >&2; exit "$code"' ERR
if [[ ! -f "$APP_DIR/storage/framework/down" ]]; then
    "$PHP_BIN" artisan down --retry=60
    MAINTENANCE_STARTED=1
fi

while IFS= read -r relative; do
    mkdir -p "$(dirname -- "$APP_DIR/$relative")"
    cp -- "$RELEASE_DIR/payload/$relative" "$APP_DIR/$relative"
done < "$RELEASE_DIR/FILES.txt"
if [[ "$AUTOLOAD_AUTHORITATIVE" == "1" ]]; then
    "$COMPOSER_BIN" dump-autoload --no-dev --optimize --no-scripts --no-interaction
fi
"$PHP_BIN" artisan view:clear

if [[ "$MAINTENANCE_STARTED" == "1" ]]; then
    "$PHP_BIN" artisan up
fi
trap - ERR
echo "After-12:35 code update complete. Backup: $BACKUP_DIR/files-before-update.tar.gz"
echo "Refresh with Ctrl+F5. Check header/footer CSS, contact links, social icons and the new-message badge."
