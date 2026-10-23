"""Package code changed after 12:35 Asia/Colombo; do not deploy or export database content."""

from datetime import datetime, timedelta, timezone
from pathlib import Path, PurePosixPath
from zipfile import ZIP_DEFLATED, ZipFile, ZipInfo
import hashlib
import json
import re

ROOT = Path(__file__).resolve().parents[1]
ZONE = timezone(timedelta(hours=5, minutes=30))
CUTOFF = datetime(2026, 10, 22, 12, 35, tzinfo=ZONE)
CREATED = datetime.now(ZONE)
STAGE = ROOT / "deploy/updates/2026-10-22-after-1235"
ARCHIVE = ROOT / "deploy/updates/sch_of_slsc-update-after-1235-2026-10-22.zip"
COMMAND_FILE = ROOT / "deploy/updates/sch_of_slsc-update-after-1235-2026-10-22-COMMANDS.txt"

RUNTIME_FILES = {
    "app/Livewire/Admin/ContactMessages/ContactMessageIndex.php",
    "app/Livewire/Admin/ContactMessages/ContactMessageNavLink.php",
    "app/Livewire/Admin/Settings/SiteSettingsIndex.php",
    "app/Models/SocialLink.php",
    "app/Services/ThemeLayoutRenderer.php",
    "app/Services/ThemeLayoutSanitizer.php",
    "resources/views/admin/partials/sidebar-navigation.blade.php",
    "resources/views/livewire/admin/contact-messages/contact-message-nav-link.blade.php",
    "resources/views/livewire/admin/settings/site-settings-index.blade.php",
    "themes/school-of-signals/assets/css/theme.css",
    "themes/school-of-signals/lang/en/home.php",
    "themes/school-of-signals/lang/si/home.php",
    "themes/school-of-signals/views/home.blade.php",
    "themes/school-of-signals/views/layout.blade.php",
    "public/themes/school-of-signals/assets/css/theme.css",
}
BUILD = ROOT / "public/build"
RUNTIME_FILES.update(path.relative_to(ROOT).as_posix() for path in BUILD.rglob("*") if path.is_file())

for folder in ["app", "bootstrap", "config", "database", "lang", "resources", "routes", "themes", "public"]:
    for path in (ROOT / folder).rglob("*"):
        if not path.is_file() or path.is_symlink():
            continue
        relative = path.relative_to(ROOT).as_posix()
        if relative.startswith(("bootstrap/cache/", "public/storage/", "public/uploads/")):
            continue
        if datetime.fromtimestamp(path.stat().st_mtime, ZONE) >= CUTOFF and relative not in RUNTIME_FILES:
            raise RuntimeError("Runtime change after cutoff is missing from the package: " + relative)

payload = {}
manifest = []
for relative in sorted(RUNTIME_FILES):
    path = ROOT / relative
    if not path.is_file() or path.is_symlink() or not path.resolve().is_relative_to(ROOT):
        raise RuntimeError("Missing, linked or invalid runtime file: " + relative)
    if path.suffix.lower() not in {".php", ".css", ".js", ".json", ".woff", ".woff2"}:
        raise RuntimeError("Unexpected runtime file extension: " + relative)
    if any(part.startswith(".") for part in PurePosixPath(relative).parts):
        raise RuntimeError("Hidden or private file in payload: " + relative)
    data = path.read_bytes()
    payload[relative] = data
    manifest.append({
        "path": relative,
        "bytes": len(data),
        "sha256": hashlib.sha256(data).hexdigest(),
        "source_modified_at": datetime.fromtimestamp(path.stat().st_mtime, ZONE).isoformat(timespec="seconds"),
    })

assert len(payload) == 28, f"Review the changed runtime file count: {len(payload)}"
assert payload["themes/school-of-signals/assets/css/theme.css"] == payload["public/themes/school-of-signals/assets/css/theme.css"]

vite_manifest = json.loads(payload["public/build/manifest.json"])
for entry in vite_manifest.values():
    for reference in [entry["file"], *entry.get("css", []), *entry.get("assets", [])]:
        assert "public/build/" + reference in payload, "Missing Vite dependency: " + reference
for relative, data in payload.items():
    if relative.startswith("public/build/") and relative.endswith(".css"):
        for url in re.findall(r"url\(([^)]+)\)", data.decode("utf-8")):
            url = url.strip("\"'")
            if url.startswith("data:") or "://" in url:
                continue
            asset = "public/" + url.lstrip("/") if url.startswith("/") else (PurePosixPath(relative).parent / url).as_posix()
            assert asset in payload, "Missing CSS font dependency: " + asset

commands = (
    "# Run from the Laravel project root containing artisan, .env and vendor/.\n"
    "unzip sch_of_slsc-update-after-1235-2026-10-22.zip -d update-after-1235-20261022\n"
    'bash update-after-1235-20261022/deploy.sh "$PWD"\n'
)
metadata = {
    "README.md": (STAGE / "README.md").read_bytes().replace(b"\r\n", b"\n"),
    "deploy.sh": (STAGE / "deploy.sh").read_bytes().replace(b"\r\n", b"\n"),
    "RUN-COMMANDS.txt": commands.encode("utf-8"),
    "FILES.txt": ("\n".join(payload) + "\n").encode("utf-8"),
    "FILES.sha256": "".join(f'{row["sha256"]}  {row["path"]}\n' for row in manifest).encode("ascii"),
    "PATCH-MANIFEST.json": (json.dumps({
        "release": "2026-10-22-after-1235",
        "project": "sch_of_slsc",
        "created_at": CREATED.isoformat(timespec="seconds"),
        "cutoff": CUTOFF.isoformat(timespec="seconds"),
        "timezone": "Asia/Colombo",
        "scope": "Code-only changes after 12:35, with complete compiled Vite assets",
        "database_content_included": False,
        "requires_migration": False,
        "requires_npm_build_on_server": False,
        "validation": {"targeted_tests": 69, "assertions": 305, "pint": "passed", "phpstan_errors": 0, "vite_build": "passed"},
        "payload_files": len(payload),
        "files": manifest,
    }, indent=2) + "\n").encode("utf-8"),
}

entries = [("payload/" + relative, data) for relative, data in payload.items()] + list(metadata.items())
expected = {relative for relative, _ in entries}
for relative, data in entries:
    target = STAGE / relative
    assert target.resolve().is_relative_to(STAGE.resolve())
    target.parent.mkdir(parents=True, exist_ok=True)
    target.write_bytes(data)
actual = {path.relative_to(STAGE).as_posix() for path in STAGE.rglob("*") if path.is_file()}
assert actual == expected, "Unexpected or missing staged files"

with ZipFile(ARCHIVE, "w", compression=ZIP_DEFLATED, compresslevel=9) as archive:
    for relative, data in entries:
        info = ZipInfo(relative, CREATED.timetuple()[:6])
        info.create_system = 3
        info.external_attr = ((0o100755 if relative == "deploy.sh" else 0o100644) << 16)
        info.compress_type = ZIP_DEFLATED
        archive.writestr(info, data)
with ZipFile(ARCHIVE) as archive:
    assert archive.testzip() is None
    assert len(archive.namelist()) == len(set(archive.namelist())) == len(expected)
    for row in manifest:
        assert hashlib.sha256(archive.read("payload/" + row["path"])).hexdigest() == row["sha256"]
    assert all(not name.startswith("/") and ".." not in PurePosixPath(name).parts and "\\" not in name for name in archive.namelist())

digest = hashlib.sha256(ARCHIVE.read_bytes()).hexdigest()
ARCHIVE.with_suffix(".zip.sha256").write_bytes((digest + "  " + ARCHIVE.name + "\n").encode("ascii"))
COMMAND_FILE.write_bytes(commands.encode("utf-8"))
print(json.dumps({"archive": str(ARCHIVE), "command_file": str(COMMAND_FILE), "payload_files": len(payload), "archive_bytes": ARCHIVE.stat().st_size, "sha256": digest}))
