# Changelog

Alle wesentlichen Änderungen an der Library **OpenShutterButtonControl** werden in dieser Datei dokumentiert.

## Unveröffentlicht

### Review-Härtung

- Initialisierung fremder Variablen auf `KR_READY` beziehungsweise `IPS_KERNELSTARTED` umgestellt.
- Variablentypen und notwendige Aktionen werden im Konfigurationsformular und zusätzlich zur Laufzeit geprüft.
- Eigene Statusmeldungen für unvollständige Konfiguration, fehlende Aktionen und ungültige Eigenschaftswerte ergänzt.
- Alle konfigurierten Variablen werden als Symcon-Objektreferenzen registriert und bei Änderungen sauber aktualisiert.
- Die tatsächlich gestartete Bewegungsvariable wird gespeichert und bei Loslassen, `ApplyChanges()` oder `Destroy()` einmalig mit `STOP` beendet.
- Verzögerte Timer-Ausführung erzeugt weiterhin eine vollständige Sequenz aus `OPEN`/`CLOSE` und `STOP`.
- Positionsbefehle werden typgerecht als Integer oder Float an die Zielvariable übergeben.
- Regressionstests für Kernelstart, Variablenaktionen, Eigenschaften, Referenzen und sichere Bewegungsbeendigung ergänzt.
- Statisches Versions-Badge durch das aktuelle GitHub-Release-Badge ersetzt.

## 1.7 – 2026-08-04

### Vereinheitlichung

- Repository, Library, Modul, Klasse und Verzeichnis wurden vollständig auf `OpenShutterButtonControl` vereinheitlicht.
- Das Modulpräfix wurde einheitlich auf `OSBC` festgelegt.
- Die bestehenden Library- und Modul-GUIDs bleiben unverändert.
- Frühere Aliase wurden entfernt, da keine veröffentlichte Bestandsinstallation berücksichtigt werden muss.
- Tests und Styleprüfung verwenden `Symcon_ModuleCI v1.0.0` mit den einheitlichen Status-Checks `tests` und `style`.
- StylePHP und SymconStubs werden als offizielle Git-Submodule eingebunden.
- Die automatische Aktualisierung der Bibliotheksmetadaten verwendet die GitHub App.

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
