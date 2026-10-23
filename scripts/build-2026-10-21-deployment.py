from __future__ import annotations

import hashlib
import json
from datetime import datetime, timedelta, timezone
from pathlib import Path
from zipfile import ZIP_DEFLATED, ZipFile, ZipInfo

ROOT = Path(__file__).resolve().parents[1]
STAGE = ROOT / "deploy/updates/2026-10-21-gallery-contact-history"
ARCHIVE = ROOT / "deploy/updates/sch_of_slsc-update-2026-10-21.zip"
ZONE = timezone(timedelta(hours=5, minutes=30))
CREATED = datetime.now(ZONE)

paths = {
    "app/Http/Controllers/PastSergeantMajorController.php",
    "resources/views/public/history/past-sgt-major.blade.php",
    "resources/views/public/galleries/index.blade.php",
    "resources/views/public/galleries/styles.blade.php",
    "routes/web.php",
    "themes/school-of-signals/views/home.blade.php",
    "themes/school-of-signals/views/layout.blade.php",
    "themes/school-of-signals/lang/en/home.php",
    "themes/school-of-signals/lang/si/home.php",
}
paths.update(path.relative_to(ROOT).as_posix() for path in (ROOT / "lang").rglob("*") if path.is_file())

payload = {}
for relative in sorted(paths):
    source = ROOT / relative
    if not source.is_file() or source.is_symlink():
        raise RuntimeError(f"Missing or linked release file: {relative}")
    if not source.resolve().is_relative_to(ROOT.resolve()):
        raise RuntimeError(f"Release file escaped the project: {relative}")
    payload[relative] = source.read_bytes()

manifest = {
    "project": "sch_of_slsc",
    "release": "2026-10-21-gallery-contact-history",
    "created_at": CREATED.isoformat(timespec="seconds"),
    "timezone": "Asia/Colombo",
    "scope": "Gallery styling, homepage contact links, Regimental Sergeant Major history, project and theme language files",
    "requires": "Existing installation with the School Leadership/profile features already deployed",
    "migrations": [],
    "file_count": len(payload),
    "files": [
        {"path": name, "bytes": len(data), "sha256": hashlib.sha256(data).hexdigest()}
        for name, data in payload.items()
    ],
}
metadata = {
    "README.md": (STAGE / "README.md").read_bytes().replace(b"\r\n", b"\n"),
    "deploy.sh": (STAGE / "deploy.sh").read_bytes().replace(b"\r\n", b"\n"),
    "FILES.txt": ("\n".join(payload) + "\n").encode(),
    "FILES.sha256": ("\n".join(f"{hashlib.sha256(data).hexdigest()}  {name}" for name, data in payload.items()) + "\n").encode(),
    "PATCH-MANIFEST.json": (json.dumps(manifest, ensure_ascii=False, indent=2) + "\n").encode(),
}
for name, data in metadata.items():
    (STAGE / name).write_bytes(data)

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
print(json.dumps({"zip": str(ARCHIVE), "files": len(payload), "bytes": ARCHIVE.stat().st_size, "sha256": digest}, indent=2))
