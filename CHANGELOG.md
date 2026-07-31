# Changelog

## 1.1 – 2026-07-31

- Mindestversion auf Symcon 9.0 vereinheitlicht.
- Modul vollständig auf `IPSModuleStrict` und streng typisierte Methoden ausgerichtet.
- Zentrale `VariablePresentationHelper`-Darstellungen für Statusvariablen eingebunden.
- Statusvariablen mit `snake_case`-Idents direkt in `Create()` registriert; eine Bestandsmigration ist nicht vorgesehen.
- Veraltete beziehungsweise unnötige Eigenschaften entfernt.
- Tasterzustände für Boolean, 0/1 sowie gebräuchliche Textwerte vereinheitlicht.
- Nachrichtenregistrierung beim Wechsel der Tastervariable und beim Löschen der Instanz bereinigt.
- Mehrfachmeldungen gleicher Tasterzustände werden ignoriert.
- Positionslogik an die Beschriftung `0 = offen / 100 = geschlossen` angepasst.
- Modulstatus und verständliche Konfigurationshinweise ergänzt.
- Tests, StylePHP, Strukturprüfung und automatische Metadatenversionierung hinzugefügt.

## 1.0

- Erste Version.
