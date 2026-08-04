#!/usr/bin/env python3
"""Validate the standardized OpenShutterButtonControl repository."""

from __future__ import annotations

import json
import re
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
MODULE_NAME = "OpenShutterButtonControl"
MODULE = ROOT / MODULE_NAME
REPOSITORY_URL = "https://github.com/Burki24/OpenShutterButtonControl"
LIBRARY_ID = "{BF1D68F2-85F4-620A-1237-7EABE668FAD1}"
MODULE_ID = "{12BA38E7-C7CE-9CCA-C8AB-B18BFADD5632}"
PREFIX = "OSBC"


def load_json(path: Path) -> dict[str, object]:
    data = json.loads(path.read_text(encoding="utf-8"))
    if not isinstance(data, dict):
        raise SystemExit(f"{path.relative_to(ROOT)} must contain a JSON object.")
    return data


def require(condition: bool, message: str) -> None:
    if not condition:
        raise SystemExit(message)


required_files = [
    ROOT / "library.json",
    ROOT / "README.md",
    ROOT / "CHANGELOG.md",
    ROOT / ".gitmodules",
    ROOT / ".helper-sync.json",
    ROOT / ".github/workflows/tests.yml",
    ROOT / ".github/workflows/style.yml",
    ROOT / ".github/workflows/update-library-metadata.yml",
    ROOT / ".github/scripts/update_library_metadata.py",
    ROOT / "tests/run.php",
    ROOT / "tests/module.php",
    ROOT / "tests/helper_integrity.py",
    ROOT / "tests/test_update_library_metadata.py",
    ROOT / "libs/helper/VariablePresentationHelper.php",
    ROOT / "libs/helper/manifest.json",
    ROOT / "libs/helper/README.md",
    MODULE / "module.php",
    MODULE / "module.json",
    MODULE / "form.json",
    MODULE / "locale.json",
    MODULE / "README.md",
]
for path in required_files:
    require(path.is_file(), f"Missing required file: {path.relative_to(ROOT)}")

require(not (ROOT / "ShutterButton").exists(), "The obsolete module directory must be deleted.")

library = load_json(ROOT / "library.json")
require(library.get("id") == LIBRARY_ID, "The existing library GUID must remain unchanged.")
require(library.get("name") == MODULE_NAME, "library.json uses the wrong library name.")
require(library.get("url") == REPOSITORY_URL, "library.json uses the wrong repository URL.")
compatibility = library.get("compatibility")
require(isinstance(compatibility, dict), "library.json compatibility must be an object.")
require(compatibility.get("version") == "9.0", "The library must require Symcon 9.0.")
version = library.get("version")
require(isinstance(version, str) and re.fullmatch(r"\d+\.\d+", version) is not None, "Invalid library version.")
require(isinstance(library.get("build"), int), "library.json build must be an integer.")
require(isinstance(library.get("date"), int), "library.json date must be an integer.")

module = load_json(MODULE / "module.json")
require(module.get("id") == MODULE_ID, "The existing module GUID must remain unchanged.")
require(module.get("name") == MODULE_NAME, "module.json uses the wrong module name.")
require(module.get("type") == 3, "The module must remain a device module.")
require(module.get("vendor") == "Burki24", "module.json uses the wrong vendor.")
require(module.get("aliases") == [], "module.json aliases must be an empty array.")
require(module.get("prefix") == PREFIX, f"module.json prefix must be {PREFIX}.")
require(module.get("url") == REPOSITORY_URL, "module.json uses the wrong repository URL.")

form = load_json(MODULE / "form.json")
require(isinstance(form.get("elements"), list) and bool(form["elements"]), "form.json must contain elements.")
require(isinstance(form.get("status"), list) and len(form["status"]) == 3, "form.json must define three error states.")

locale = load_json(MODULE / "locale.json")
translations = locale.get("translations")
require(isinstance(translations, dict), "locale.json translations must be an object.")
german = translations.get("de")
require(isinstance(german, dict), "locale.json must contain German translations.")

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
missing_translations = sorted(caption for caption in form_captions if caption not in german)
require(not missing_translations, f"Missing German form translations: {missing_translations}")

source = (MODULE / "module.php").read_text(encoding="utf-8")
for marker in [
    "class OpenShutterButtonControl extends IPSModuleStrict",
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
]:
    require(marker in source, f"module.php is missing required marker: {marker}")

for forbidden in [
    "class ShutterButton extends",
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
    require(forbidden not in source, f"module.php contains obsolete logic: {forbidden}")

status_idents = re.findall(r"RegisterVariable(?:Boolean|Integer|Float|String)\(\s*['\"]([^'\"]+)['\"]", source)
require(status_idents == ["last_duration_ms", "last_action"], f"Unexpected status-variable idents: {status_idents}")
require(
    all(re.fullmatch(r"[a-z][a-z0-9]*(?:_[a-z0-9]+)*", ident) is not None for ident in status_idents),
    "All status-variable idents must use canonical snake_case.",
)

readme = (MODULE / "README.md").read_text(encoding="utf-8")
for section in range(1, 8):
    require(f"## {section}." in readme, f"Module README is missing section {section}.")
require("Symcon ab Version 9.0" in readme, "Module README must document Symcon 9.0.")
require("keine eigenen Variablenprofile" in readme, "Module README must document native presentations.")
require(REPOSITORY_URL in readme, "Module README uses the wrong repository URL.")

modules_config = (ROOT / ".gitmodules").read_text(encoding="utf-8")
for path, url in {
    ".style": "https://github.com/symcon/StylePHP",
    "tests/stubs": "https://github.com/symcon/SymconStubs",
}.items():
    require(f"path = {path}" in modules_config, f".gitmodules is missing {path}.")
    require(f"url = {url}" in modules_config, f".gitmodules uses the wrong URL for {path}.")

workflow_tests = (ROOT / ".github/workflows/tests.yml").read_text(encoding="utf-8")
workflow_style = (ROOT / ".github/workflows/style.yml").read_text(encoding="utf-8")
workflow_metadata = (ROOT / ".github/workflows/update-library-metadata.yml").read_text(encoding="utf-8")
require("jobs:\n  tests:" in workflow_tests, "Tests workflow job must be named tests.")
require("name: tests" in workflow_tests, "Tests status check must be named tests.")
require(
    "Burki24/Symcon_ModuleCI/php-tests@v1.0.0" in workflow_tests,
    "Tests workflow must use Symcon_ModuleCI php-tests v1.0.0.",
)
require("jobs:\n  style:" in workflow_style, "Style workflow job must be named style.")
require("name: style" in workflow_style, "Style status check must be named style.")
require(
    "Burki24/Symcon_ModuleCI/style@v1.0.0" in workflow_style,
    "Style workflow must use Symcon_ModuleCI style v1.0.0.",
)
for needle in [
    "actions/create-github-app-token@v3",
    "vars.HELPER_SYNC_APP_CLIENT_ID",
    "secrets.HELPER_SYNC_APP_PRIVATE_KEY",
    "Burki24/Symcon_ModuleCI/php-tests@v1.0.0",
    ".github/scripts/update_library_metadata.py",
    "CHORE: Update library metadata",
]:
    require(needle in workflow_metadata, f"Metadata workflow is missing: {needle}")

helper_sync = load_json(ROOT / ".helper-sync.json")
require(helper_sync.get("source_repository") == "Burki24/Symcon_ModuleHelper", "Wrong helper source repository.")
require(helper_sync.get("base_branch") == "dev", "Helper updates must target dev.")

for text_path in [
    ROOT / "library.json",
    ROOT / "README.md",
    MODULE / "module.json",
    MODULE / "README.md",
    ROOT / "tests/module.php",
]:
    text = text_path.read_text(encoding="utf-8")
    require("https://github.com/Burki24/ShutterButtonControl" not in text, f"Old repository URL remains in {text_path.relative_to(ROOT)}.")

require("$GLOBALS['SBC_" not in (ROOT / "tests/module.php").read_text(encoding="utf-8"), "Old test prefix remains.")
require("new ShutterButton()" not in (ROOT / "tests/module.php").read_text(encoding="utf-8"), "Old test class remains.")

print("OpenShutterButtonControl repository structure verified.")
