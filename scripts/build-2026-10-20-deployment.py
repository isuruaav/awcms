from __future__ import annotations

import hashlib
import json
from datetime import datetime, timedelta, timezone
from pathlib import Path
from zipfile import ZIP_DEFLATED, ZipFile, ZipInfo

ROOT = Path(__file__).resolve().parents[1]
STAGE = ROOT / "deploy/updates/2026-10-20-1404"
ZONE = timezone(timedelta(hours=5, minutes=30))
CUTOFF = datetime(2026, 10, 20, 14, 4, tzinfo=ZONE)
CREATED = datetime.now(ZONE)
ARCHIVE = ROOT / "deploy/updates/sch_of_slsc-update-2026-10-20-from-1404-v2.zip"
selected: dict[str, dict] = {}


def add(path: Path, reason: str) -> None:
    if not path.is_file() or path.is_symlink():
        raise RuntimeError(f"Missing or linked release file: {path}")
    relative = path.relative_to(ROOT).as_posix()
    if relative.startswith((".env", "storage/", "vendor/", "node_modules/", ".git/")):
        raise RuntimeError(f"Excluded release path: {relative}")
    if relative not in selected:
        selected[relative] = {"source": path, "reason": reason}


for folder in ["app", "bootstrap", "config", "database", "lang", "resources", "routes", "themes", "public", "tests"]:
    for path in (ROOT / folder).rglob("*"):
        relative = path.relative_to(ROOT)
        if not path.is_file() or path.is_symlink():
            continue
        if any(part in {"cache", "storage", "node_modules", ".git"} for part in relative.parts):
            continue
        if path.stat().st_mtime >= CUTOFF.timestamp():
            add(path, "Modified on or after 2026-10-20 14:04 Asia/Colombo")

support = {
    "app/Http/Controllers/PublicHomeController.php": "Controller data required by the updated homepage",
    "app/Http/Controllers/PublicSchoolLeadershipController.php": "Controller referenced by the current routes file",
    "app/Livewire/Admin/SchoolLeaders/SchoolLeaderProfiles.php": "Livewire component referenced by the current routes file",
    "app/Models/SchoolLeader.php": "Leadership methods used by the current routes and homepage",
    "app/Services/LeadershipProfileContent.php": "Required by the leadership controller, model and profile editor",
    "database/migrations/2026_10_19_120000_add_profiles_to_school_leaders_table.php": "Schema dependency of the referenced leadership profile code",
    "resources/views/admin/dashboard.blade.php": "Removes links to the menu route retired in the current routes file",
    "resources/views/admin/partials/sidebar-navigation.blade.php": "Navigation compatible with the current routes file",
    "resources/views/components/layouts/admin.blade.php": "Livewire/Flux scripts required by the referenced profile editor",
    "resources/views/components/admin/icon-action.blade.php": "Blade component used by the profile editor",
    "resources/views/livewire/admin/school-leaders/school-leader-profiles.blade.php": "View required by the referenced profile editor",
    "resources/views/public/history/past-chief-instructors.blade.php": "History view compatible with the current leadership model",
    "themes/school-of-signals/views/history/past-commandants.blade.php": "History view compatible with the current leadership model",
    "themes/school-of-signals/views/commandant.blade.php": "View required by the leadership controller",
    "themes/school-of-signals/views/leader-profile.blade.php": "View required by the leadership controller",
    "themes/school-of-signals/views/school-leadership.blade.php": "View required by the leadership controller",
    "themes/school-of-signals/views/partials/commandant-feature.blade.php": "Partial included by the updated homepage",
    "themes/school-of-signals/views/partials/leadership-profile-content.blade.php": "Partial included by the leadership views",
    "scripts/compile-leadership-profile.mjs": "Runtime compiler used by the referenced leadership profile editor",
    "package.json": "Dependency manifest for the referenced runtime profile compiler",
    "package-lock.json": "Locked dependencies for the referenced runtime profile compiler",
}
for relative, reason in support.items():
    add(ROOT / relative, reason)

for folder, reason in [
    ("public/build", "Complete compiled frontend build, including all manifest dependencies"),
    ("themes/school-of-signals/assets", "Complete theme assets required by the theme publishing command"),
]:
    for path in (ROOT / folder).rglob("*"):
        if path.is_file():
            add(path, reason)

manifest_files = []
payload: dict[str, bytes] = {}
for relative, item in sorted(selected.items()):
    content = item["source"].read_bytes()
    digest = hashlib.sha256(content).hexdigest()
    payload[relative] = content
    manifest_files.append({
        "path": relative,
        "bytes": len(content),
        "sha256": digest,
        "source_modified_at": datetime.fromtimestamp(item["source"].stat().st_mtime, ZONE).isoformat(timespec="seconds"),
        "reason": item["reason"],
    })

manifest = {
    "project": "sch_of_slsc",
    "release_revision": 2,
    "deployment_fix": "Install production dependencies before clearing generated provider caches and discovering packages; supports resuming a failed deployment",
    "cutoff": CUTOFF.isoformat(),
    "created_at": CREATED.isoformat(timespec="seconds"),
    "timezone": "Asia/Colombo",
    "selection": "Filesystem modification times, with explicitly listed runtime and compiled-asset dependencies",
    "file_count": len(payload),
    "excluded": [".env and credentials", "vendor", "node_modules", "uploaded media", "runtime caches", "full database dumps", "Apache configuration changes"],
    "migrations": [
        "2026_10_19_120000_add_profiles_to_school_leaders_table.php",
        "2026_10_20_120000_localize_school_footer_content.php",
        "2026_10_20_130000_match_school_footer_layouts.php",
    ],
    "footer_import": "sync-footers.php imports the reviewed footer-layouts.json snapshot after the migrations",
    "files": manifest_files,
}
metadata = {
    "README.md": (STAGE / "README.md").read_bytes(),
    "deploy.sh": (STAGE / "deploy.sh").read_bytes(),
    "resume.sh": (STAGE / "resume.sh").read_bytes(),
    "sync-footers.php": (STAGE / "sync-footers.php").read_bytes(),
    "footer-layouts.json": (STAGE / "footer-layouts.json").read_bytes(),
    "PATCH-MANIFEST.json": (json.dumps(manifest, ensure_ascii=False, indent=2) + "\n").encode(),
    "FILES.txt": ("\n".join(payload) + "\n").encode(),
    "FILES.sha256": ("\n".join(f"{hashlib.sha256(data).hexdigest()}  {path}" for path, data in payload.items()) + "\n").encode(),
}
for name, data in metadata.items():
    (STAGE / name).write_bytes(data)

with ZipFile(ARCHIVE, "w", compression=ZIP_DEFLATED, compresslevel=9) as archive:
    for name, data in [*(('payload/' + path, data) for path, data in payload.items()), *metadata.items()]:
        info = ZipInfo(name, CREATED.timetuple()[:6])
        info.create_system = 3
        info.external_attr = ((0o100755 if name in {"deploy.sh", "resume.sh"} else 0o100644) << 16)
        info.compress_type = ZIP_DEFLATED
        archive.writestr(info, data)

with ZipFile(ARCHIVE) as archive:
    if archive.testzip() is not None:
        raise RuntimeError("ZIP integrity check failed")
    names = archive.namelist()
    if len(names) != len(set(names)):
        raise RuntimeError("Duplicate ZIP entries")
    for relative, expected in payload.items():
        if archive.read("payload/" + relative) != expected:
            raise RuntimeError(f"ZIP content mismatch: {relative}")
    for name in names:
        if name.startswith("/") or ".." in Path(name).parts:
            raise RuntimeError(f"Unsafe ZIP entry: {name}")

archive_hash = hashlib.sha256(ARCHIVE.read_bytes()).hexdigest()
ARCHIVE.with_suffix(".zip.sha256").write_text(f"{archive_hash}  {ARCHIVE.name}\n", encoding="ascii")
print(json.dumps({"zip": str(ARCHIVE), "payload_files": len(payload), "zip_bytes": ARCHIVE.stat().st_size, "sha256": archive_hash}, indent=2))
