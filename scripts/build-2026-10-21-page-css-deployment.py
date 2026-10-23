from __future__ import annotations

import hashlib
import json
from datetime import datetime, timedelta, timezone
from pathlib import Path
from zipfile import ZIP_DEFLATED, ZipFile, ZipInfo

ROOT = Path(__file__).resolve().parents[1]
STAGE = ROOT / "deploy/updates/2026-10-21-page-custom-css"
ARCHIVE = ROOT / "deploy/updates/sch_of_slsc-page-custom-css-update-2026-10-21.zip"
CREATED = datetime.now(timezone(timedelta(hours=5, minutes=30)))
MIGRATION = "database/migrations/2026_10_21_120000_add_custom_css_to_pages_and_page_revisions.php"

paths = {
    "app/Services/PageCss.php",
    "app/Rules/ValidPageCss.php",
    "app/Livewire/Admin/Pages/PageCreate.php",
    "app/Livewire/Admin/Pages/PageEdit.php",
    "app/Models/Page.php",
    "app/Services/PageRevisionService.php",
    "app/Http/Controllers/Admin/PagePreviewController.php",
    MIGRATION,
    "resources/views/components/forms/page-custom-css.blade.php",
    "resources/views/components/page/custom-styles.blade.php",
    "resources/views/components/admin/icon-action.blade.php",
    "resources/views/admin/pages/preview.blade.php",
    "resources/views/pages/show.blade.php",
    "resources/views/livewire/admin/pages/page-create.blade.php",
    "resources/views/livewire/admin/pages/page-edit.blade.php",
    "resources/views/livewire/admin/pages/page-revision-history.blade.php",
    "resources/views/layouts/public.blade.php",
    "themes/school-of-signals/views/pages/show.blade.php",
}
paths.update(path.relative_to(ROOT).as_posix() for path in (ROOT / "public/build").rglob("*") if path.is_file())

payload: dict[str, bytes] = {}
for relative in sorted(paths):
    source = ROOT / relative
    if not source.is_file() or source.is_symlink() or not source.resolve().is_relative_to(ROOT.resolve()):
        raise RuntimeError(f"Missing, linked, or invalid release file: {relative}")
    if relative.startswith((".env", ".git/", "storage/", "vendor/", "node_modules/")):
        raise RuntimeError(f"Excluded release path: {relative}")
    payload[relative] = source.read_bytes()

manifest = json.loads(payload["public/build/manifest.json"])
for entry in manifest.values():
    for filename in [entry["file"], *entry.get("assets", []), *entry.get("css", [])]:
        if "public/build/" + filename not in payload:
            raise RuntimeError(f"Missing compiled asset: {filename}")

metadata = {
    "README.md": (STAGE / "README.md").read_bytes().replace(b"\r\n", b"\n"),
    "deploy.sh": (STAGE / "deploy.sh").read_bytes().replace(b"\r\n", b"\n"),
    "FILES.txt": ("\n".join(payload) + "\n").encode(),
    "FILES.sha256": ("\n".join(f"{hashlib.sha256(data).hexdigest()}  {name}" for name, data in payload.items()) + "\n").encode(),
    "PATCH-MANIFEST.json": (json.dumps({
        "project": "sch_of_slsc",
        "release": "2026-10-21-page-custom-css",
        "created_at": CREATED.isoformat(timespec="seconds"),
        "timezone": "Asia/Colombo",
        "scope": "Page Custom CSS editing, validation, scoped rendering, translations, previews, revisions, compiled assets",
        "requires": "Existing current sch_of_slsc installation with the page editor and translation features",
        "migrations": [MIGRATION],
        "validation": {"targeted_tests_passed": 42, "assertions": 156, "phpstan": "passed", "pint": "passed", "frontend_build": "passed"},
        "file_count": len(payload),
        "files": [{"path": name, "bytes": len(data), "sha256": hashlib.sha256(data).hexdigest()} for name, data in payload.items()],
    }, ensure_ascii=False, indent=2) + "\n").encode(),
}

for name, data in [("payload/" + name, data) for name, data in payload.items()] + list(metadata.items()):
    destination = STAGE / name
    destination.parent.mkdir(parents=True, exist_ok=True)
    destination.write_bytes(data)

with ZipFile(ARCHIVE, "w", compression=ZIP_DEFLATED, compresslevel=9) as archive:
    for name, data in [("payload/" + name, data) for name, data in payload.items()] + list(metadata.items()):
        info = ZipInfo(name, CREATED.timetuple()[:6])
        info.create_system = 3
        info.external_attr = ((0o100755 if name == "deploy.sh" else 0o100644) << 16)
        info.compress_type = ZIP_DEFLATED
        archive.writestr(info, data)

with ZipFile(ARCHIVE) as archive:
    if archive.testzip() is not None:
        raise RuntimeError("ZIP integrity check failed")
    names = archive.namelist()
    if len(names) != len(set(names)):
        raise RuntimeError("Duplicate ZIP entries")
    for name in names:
        if name.startswith("/") or ".." in Path(name).parts or "\\" in name:
            raise RuntimeError(f"Unsafe ZIP entry: {name}")
    for relative, expected in payload.items():
        if archive.read("payload/" + relative) != expected:
            raise RuntimeError(f"ZIP content mismatch: {relative}")

digest = hashlib.sha256(ARCHIVE.read_bytes()).hexdigest()
ARCHIVE.with_suffix(".zip.sha256").write_text(f"{digest}  {ARCHIVE.name}\n", encoding="ascii")
print(json.dumps({"zip": str(ARCHIVE), "payload_files": len(payload), "bytes": ARCHIVE.stat().st_size, "sha256": digest}, indent=2))
