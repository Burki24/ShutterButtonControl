#!/usr/bin/env python3
"""Validate the ShutterButtonControl modernization contract."""

from __future__ import annotations

import json
import re
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
MODULE = ROOT / "ShutterButton"

required_files = [
    ROOT / "library.json",
    ROOT / "README.md",
    ROOT / "CHANGELOG.md",
    ROOT / ".helper-sync.json",
    ROOT / ".github/workflows/tests.yml",
    ROOT / ".github/workflows/style.yml",
    ROOT / ".github/workflows/update-library-metadata.yml",
    ROOT / ".github/scripts/update_library_metadata.py",
    ROOT / ".style/.php-cs-fixer.php",
    ROOT / "libs/helper/VariablePresentationHelper.php",
    ROOT / "libs/helper/manifest.json",
    ROOT / "libs/helper/README.md",
    MODULE / "module.php",
    MODULE / "module.json",
    MODULE / "form.json",
    MODULE / "locale.json",
    MODULE / "README.md",
]

missing = [str(path.relative_to(ROOT)) for path in required_files if not path.is_file()]
if missing:
    raise SystemExit(f"Missing required files: {missing}")


def load_json(path: Path) -> dict[str, object]:
    data = json.loads(path.read_text(encoding="utf-8"))
    if not isinstance(data, dict):
        raise SystemExit(f"{path.relative_to(ROOT)} must contain a JSON object.")
    return data


library = load_json(ROOT / "library.json")
compatibility = library.get("compatibility")
if not isinstance(compatibility, dict) or compatibility.get("version") != "9.0":
    raise SystemExit("library.json must require Symcon 9.0.")
if library.get("version") != "1.1":
    raise SystemExit("The modernization release must use library version 1.1.")
if not isinstance(library.get("build"), int) or not isinstance(library.get("date"), int):
    raise SystemExit("library.json build and date must be integers.")

module = load_json(MODULE / "module.json")
if module.get("type") != 3 or module.get("prefix") != "SBC":
    raise SystemExit("module.json must describe a device module with prefix SBC.")
if module.get("url") != "https://github.com/Burki24/ShutterButtonControl/tree/main/ShutterButton":
    raise SystemExit("module.json must use the canonical module URL.")

form = load_json(MODULE / "form.json")
if not isinstance(form.get("elements"), list) or not form["elements"]:
    raise SystemExit("form.json must contain a non-empty elements list.")
if not isinstance(form.get("status"), list) or len(form["status"]) != 3:
    raise SystemExit("form.json must define the three configuration error states.")

locale = load_json(MODULE / "locale.json")
translations = locale.get("translations")
if not isinstance(translations, dict) or not isinstance(translations.get("de"), dict):
    raise SystemExit("locale.json must contain German translations.")

form_captions: set[str] = set()


def collect_captions(value: object) -> None:
    if isinstance(value, dict):
        caption = value.get("caption")
        if isinstance(caption, str) and caption:
            form_captions.add(caption)
        for child in value.values():
            collect_captions(child)
    elif isinstance(value, list):
        for child in value:
            collect_captions(child)


collect_captions(form)
german = translations["de"]
missing_translations = sorted(caption for caption in form_captions if caption not in german)
if missing_translations:
    raise SystemExit(f"Missing German form translations: {missing_translations}")

source = (MODULE / "module.php").read_text(encoding="utf-8")
required_markers = [
    "extends IPSModuleStrict",
    "use VariablePresentationHelper;",
    "public function MessageSink(int $TimeStamp, int $SenderID, int $Message, array $Data): void",
    "public function RequestAction(string $Ident, mixed $Value): void",
    "public function Destroy(): void",
    "IntegerPresentation('ms'",
    "OptionsPresentation([",
    "RegisterStatusVariables();",
    "RegisterVariableInteger(",
    "RegisterVariableString(",
    "UpdateButtonRegistration();",
    "UnregisterConfiguredButton();",
    "DetermineConfigurationStatus(): int",
    "MapButtonState(mixed $value): ?bool",
]
for marker in required_markers:
    if marker not in source:
        raise SystemExit(f"module.php is missing required marker: {marker}")

for forbidden in [
    "extends IPSModule\n",
    "VariableProfileHelper",
    "RegisterProfileInteger",
    "RegisterProfileFloat",
    "RegisterProfileBoolean",
    "IPS_CreateVariableProfile",
    "IPS_SetVariableProfile",
    "PositionUp",
    "PositionDown",
    "MigrateLegacyStatusVariables",
    "MigrateLegacyVariableIdent",
    "MaintainVariable(",
]:
    if forbidden in source:
        raise SystemExit(f"module.php still contains obsolete logic: {forbidden}")

status_idents = re.findall(r'RegisterVariable(?:Boolean|Integer|Float|String)\(\s*[\'"]([^\'"]+)[\'"]', source)
if status_idents != ["last_duration_ms", "last_action"]:
    raise SystemExit(f"Unexpected status-variable idents: {status_idents}")
if any(re.fullmatch(r"[a-z][a-z0-9]*(?:_[a-z0-9]+)*", ident) is None for ident in status_idents):
    raise SystemExit("All status-variable idents must use canonical snake_case.")

readme = (MODULE / "README.md").read_text(encoding="utf-8")
for section in range(1, 8):
    if f"## {section}." not in readme:
        raise SystemExit(f"Module README is missing section {section}.")
if "Symcon ab Version 9.0" not in readme:
    raise SystemExit("Module README must document Symcon 9.0.")
if "keine eigenen Variablenprofile" not in readme:
    raise SystemExit("Module README must document the absence of custom profiles.")

print("Symcon module structure verified.")
