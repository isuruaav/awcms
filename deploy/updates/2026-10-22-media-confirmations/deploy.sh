#!/usr/bin/env bash
set -Eeuo pipefail
umask 022

RELEASE_DIR="$(CDPATH= cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd -P)"
APP_DIR="${1:-$PWD}"
PHP_BIN="${PHP_BIN:-php}"
COMPOSER_BIN="${COMPOSER_BIN:-composer}"
MIGRATION="database/migrations/2026_10_22_160000_add_public_filename_to_media_assets_table.php"

if [[ $# -gt 2 || ( $# -eq 2 && "$2" != "--check" ) ]]; then
    echo "Usage: bash deploy.sh /absolute/path/to/laravel-project [--check]" >&2
    exit 1
fi
APP_DIR="$(CDPATH= cd -- "$APP_DIR" && pwd -P)"
for required in "$PHP_BIN" sha256sum tar mktemp cp realpath; do
    command -v "$required" >/dev/null
done
for required in artisan .env vendor/autoload.php bootstrap/app.php; do
    [[ -f "$APP_DIR/$required" ]] || { echo "Missing $required: select the existing Laravel project root." >&2; exit 1; }
done
for required in payload FILES.txt FILES.sha256; do
    [[ -e "$RELEASE_DIR/$required" ]] || { echo "Extract the complete update ZIP first." >&2; exit 1; }
done
"$PHP_BIN" -r 'exit(PHP_VERSION_ID >= 80300 ? 0 : 1);' || {
    echo "PHP CLI 8.3+ is required. Set PHP_BIN for your server's PHP executable." >&2
    exit 1
}

# This patch follows the existing school leadership, page CSS and document
# upload modules. Stop before replacing files if the target is an older app.
for required in \
    app/Http/Controllers/PublicSchoolLeadershipController.php \
    app/Http/Controllers/PastSergeantMajorController.php \
    app/Livewire/Admin/SchoolLeaders/SchoolLeaderProfiles.php \
    app/Livewire/Admin/ContactMessages/ContactMessageNavLink.php \
    app/Services/DocumentUploadManager.php \
    app/Services/PageCss.php \
    resources/views/components/forms/page-custom-css.blade.php; do
    [[ -f "$APP_DIR/$required" ]] || {
        echo "The target is missing a prerequisite from the existing application: $required" >&2
        exit 1
    }
done

while IFS= read -r relative; do
    if [[ -z "$relative" || "$relative" == /* || "$relative" == *'..'* || "$relative" == *\\* ]]; then
        echo "Invalid payload path: $relative" >&2
        exit 1
    fi
    case "$relative" in
        app/*|resources/js/*|resources/views/*|public/build/*|routes/web.php|"$MIGRATION") ;;
        *) echo "Unexpected payload path: $relative" >&2; exit 1 ;;
    esac
    resolved="$(realpath -m -- "$APP_DIR/$relative")"
    [[ "$resolved" == "$APP_DIR/"* ]] || { echo "Target path leaves the project: $relative" >&2; exit 1; }
    [[ ! -d "$APP_DIR/$relative" ]] || { echo "A file target is a directory: $relative" >&2; exit 1; }
done < "$RELEASE_DIR/FILES.txt"
(cd "$RELEASE_DIR/payload" && sha256sum --check "$RELEASE_DIR/FILES.sha256")

AUTOLOAD_AUTHORITATIVE="$("$PHP_BIN" -r '$loader = require $argv[1]; echo $loader->isClassMapAuthoritative() ? "1" : "0";' "$APP_DIR/vendor/autoload.php")"
if [[ "$AUTOLOAD_AUTHORITATIVE" == "1" ]]; then
    command -v "$COMPOSER_BIN" >/dev/null || {
        echo "Composer is needed to register the new classes in the authoritative class map." >&2
        exit 1
    }
fi
if [[ "${2:-}" == "--check" ]]; then
    echo "Package hashes and target checks passed. No application files were changed."
    exit 0
fi

mkdir -p "$APP_DIR/storage/app/deployment-backups"
BACKUP_DIR="$(mktemp -d "$APP_DIR/storage/app/deployment-backups/media-confirmations-20261022-XXXXXX")"
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
    printf '%s\n' 'vendor/composer' >> "$BACKUP_DIR/existing-files.txt"
fi
tar -C "$APP_DIR" -czf "$BACKUP_DIR/files-before-update.tar.gz" -T "$BACKUP_DIR/existing-files.txt"
echo "File backup: $BACKUP_DIR/files-before-update.tar.gz"

cd "$APP_DIR"
MAINTENANCE_STARTED=0
trap 'code=$?; echo "Update stopped (exit $code). Backup: $BACKUP_DIR. Correct the error before using php artisan up if this installer enabled maintenance mode." >&2; exit "$code"' ERR
MAINTENANCE_STATE="$("$PHP_BIN" -r 'require $argv[1]."/vendor/autoload.php"; $app = require $argv[1]."/bootstrap/app.php"; $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap(); echo $app->isDownForMaintenance() ? "down" : "up";' "$APP_DIR")"
if [[ "$MAINTENANCE_STATE" == "up" ]]; then
    "$PHP_BIN" artisan down --retry=60
    MAINTENANCE_STARTED=1
elif [[ "$MAINTENANCE_STATE" != "down" ]]; then
    echo "Could not determine the application's maintenance state." >&2
    exit 1
fi

while IFS= read -r relative; do
    mkdir -p "$(dirname -- "$APP_DIR/$relative")"
    cp -- "$RELEASE_DIR/payload/$relative" "$APP_DIR/$relative"
done < "$RELEASE_DIR/FILES.txt"
if [[ "$AUTOLOAD_AUTHORITATIVE" == "1" ]]; then
    "$COMPOSER_BIN" dump-autoload --no-dev --optimize --no-scripts --no-interaction
fi
"$PHP_BIN" artisan migrate --path="$MIGRATION" --force
"$PHP_BIN" artisan route:clear
"$PHP_BIN" artisan view:clear

if [[ "$MAINTENANCE_STARTED" == "1" ]]; then
    "$PHP_BIN" artisan up
fi
trap - ERR
echo "Media paths and Yes/No confirmation update complete."
echo "Backup: $BACKUP_DIR/files-before-update.tar.gz"
echo "Refresh with Ctrl+F5. Check Copy Path, an image/document in a published page, and News Yes/No actions."
