# OpenShutterButtonControl

Herstellerunabhängige Symcon-Bibliothek zur Steuerung eines Rollladens über einen Taster. Ein kurzer Tastendruck fährt eine Endposition an; Gedrückthalten startet die kontinuierliche Bewegung und Loslassen sendet `STOP`.

[![Release](https://img.shields.io/github/v/release/Burki24/OpenShutterButtonControl?sort=semver)](https://github.com/Burki24/OpenShutterButtonControl/releases)
![Symcon](https://img.shields.io/badge/Symcon-9.0+-green.svg)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)

## Enthaltenes Modul

### OpenShutterButtonControl

Verknüpft eine Tastervariable mit einer Bewegungs- und einer Positionsvariable eines Rollladens.

## Funktionen

- Unterscheidung zwischen kurzem und langem Tastendruck
- Kurzer Tastendruck: Anfahren der konfigurierten Endposition
- Langer Tastendruck: `OPEN` oder `CLOSE`; beim Loslassen `STOP`
- Unterstützung für Boolean-, 0/1- und gebräuchliche Textzustände
- Native Symcon-9-Darstellungen ohne eigene Variablenprofile
- Kernel-sichere Initialisierung erst ab `KR_READY`
- Prüfung von Variablentypen und ausführbaren Aktionen im Formular und zur Laufzeit
- Objektverweise für alle konfigurierten Variablen
- Sicheres `STOP` auf der tatsächlich gestarteten Bewegungsvariable bei Loslassen, Konfigurationswechsel und Löschen
- Bereinigung alter Nachrichtenregistrierungen beim Wechsel der Tastervariable
- Anzeige der letzten Aktion und Druckdauer

## Voraussetzungen

- Symcon ab Version 9.0
- Bewegungsvariable vom Typ String mit einer Aktion für `OPEN`, `CLOSE` und `STOP`
- Numerische Positionsvariable mit einer Aktion für Werte von 0 bis 100

## Statushinweise

Die Instanz zeigt ihren aktuellen Zustand direkt in der Symcon-Konsole an. Bei einem Fehler bleibt die Tastersteuerung deaktiviert, bis die Ursache behoben ist.

| Status | Bedeutung | Empfohlene Prüfung |
| --- | --- | --- |
| `102` | Instanz ist aktiv und betriebsbereit | Keine Maßnahme erforderlich |
| `104` | Initialisierung ist noch nicht möglich | Symcon-Kernel startet noch; nach `KR_READY` initialisiert sich das Modul automatisch |
| `201` | Tastervariable fehlt oder hat einen nicht unterstützten Typ | Boolean-, Integer-, Float- oder Stringvariable auswählen |
| `202` | Bewegungsvariable ist keine Stringvariable | Stringvariable für `OPEN`, `CLOSE` und `STOP` auswählen |
| `203` | Positionsvariable ist nicht numerisch | Integer- oder Floatvariable mit dem Wertebereich 0 bis 100 auswählen |
| `204` | Bewegungsvariable besitzt keine ausführbare Aktion | Standard- oder benutzerdefinierte Aktion der Bewegungsvariable prüfen |
| `205` | Positionsvariable besitzt keine ausführbare Aktion | Standard- oder benutzerdefinierte Aktion der Positionsvariable prüfen |
| `206` | Grenze für langen Tastendruck ist ungültig | Wert zwischen 100 und 5000 Millisekunden einstellen |
| `207` | Konfigurierte Richtung ist ungültig | „Rollladen öffnen“ oder „Rollladen schließen“ auswählen |
| `208` | Konfigurierte Positionslogik ist ungültig | Eine der beiden angebotenen Zuordnungen für 0 und 100 auswählen |
| `209` | Konfiguration ist unvollständig | Taster-, Bewegungs- und Positionsvariable vollständig auswählen |

## Installation

Die Repository-URL im Symcon Module Control hinzufügen:

```text
https://github.com/Burki24/OpenShutterButtonControl
```

Die Moduldetails stehen in der [Moduldokumentation](OpenShutterButtonControl/README.md).

## Entwicklung

Das Repository verwendet die zentralen Actions aus `Symcon_ModuleCI v1.0.0`.
Die einheitlichen Status-Checks heißen:

- `tests`
- `style`

Die offiziellen Symcon-Quellen werden als Git-Submodule eingebunden:

- `.style` → `symcon/StylePHP`
- `tests/stubs` → `symcon/SymconStubs`

Der lokale Test-Einstiegspunkt lautet:

```text
php tests/run.php
```

## Lizenz

Dieses Projekt steht unter der [MIT-Lizenz](LICENSE).
