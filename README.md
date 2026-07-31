# ShutterButtonControl

Symcon-Bibliothek zur Steuerung eines Rollladens über einen Taster. Ein kurzer Tastendruck fährt eine Endposition an; Gedrückthalten startet die kontinuierliche Bewegung und Loslassen sendet `STOP`.

![Version](https://img.shields.io/badge/version-1.1-blue.svg)
![Symcon](https://img.shields.io/badge/Symcon-9.0+-green.svg)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)

## Enthaltenes Modul

### ShutterButton

Verknüpft eine Tastervariable mit einer Bewegungs- und einer Positionsvariable eines Rollladens.

## Funktionen

- Unterscheidung zwischen kurzem und langem Tastendruck
- Kurzer Tastendruck: Anfahren der konfigurierten Endposition
- Langer Tastendruck: `OPEN` oder `CLOSE`; beim Loslassen `STOP`
- Unterstützung für Boolean-, 0/1- und gebräuchliche Textzustände
- Native Symcon-9-Darstellungen ohne eigene Variablenprofile
- Bereinigung alter Nachrichtenregistrierungen beim Wechsel der Tastervariable
- Anzeige der letzten Aktion und Druckdauer

## Voraussetzungen

- Symcon ab Version 9.0
- Bewegungsvariable vom Typ String mit einer Aktion für `OPEN`, `CLOSE` und `STOP`
- Numerische Positionsvariable mit einer Aktion für Werte von 0 bis 100

## Installation

Die Repository-URL im Symcon Module Control hinzufügen:

```text
https://github.com/Burki24/ShutterButtonControl
```

Die Moduldetails stehen in der [Moduldokumentation](ShutterButton/README.md).

## Entwicklung

Das Repository enthält Tests für Struktur, Helper-Integrität, Tasterlogik, Nachrichtenregistrierung, native Variablendarstellungen und Metadatenversionierung.

## Lizenz

MIT-Lizenz, siehe [LICENSE](LICENSE).
