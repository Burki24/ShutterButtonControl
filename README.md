# IPS ShutterButtonControl

Symcon Modul zur Steuerung von Rollläden über Taster (z. B. SODA S8 Griff).  
Unterscheidet zwischen kurzem und langem Tastendruck und steuert entsprechend die Rollladenbewegung.

![Version](https://img.shields.io/badge/version-1.0-blue.svg)
![Symcon](https://img.shields.io/badge/Symcon-9.0+-green.svg)
[![Symcon PHP SDK](https://img.shields.io/badge/Symcon-PHP%20Modul-orange)](https://www.symcon.de/service/dokumentation/entwicklerbereich/sdk-tools/sdk-php/)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)

---

## Enthaltenes Modul

### ShutterButton

Steuert Rollläden über Taster und unterscheidet zwischen kurzem und langem Tastendruck.

---

## Funktionsumfang

- ShortPress / LongPress Erkennung
- Steuerung über:
  - Positionsvariable (Integer, 0–100)
  - Bewegungsvariable (String, z. B. OPEN/CLOSE)
- Unterstützung verschiedener Geräte-Logiken
- Konfigurierbare Dauer für kurzen Tastendruck
- Konfigurierbare Positionslogik:
  - 0 = offen / 100 = geschlossen
  - 0 = geschlossen / 100 = offen
- Debug-Ausgaben zur Analyse
- Anzeige der letzten Aktion und Tastdauer

---

## Voraussetzungen

- Symcon ab Version 9.0  
- Unterstützte Variablen:
  - Button (Boolean oder Enum → pressed/released)
  - Shutter Bewegung (String, z. B. OPEN/CLOSE/STOP)
  - Shutter Position (Integer, 0–100)

---

## Installation

### Über Module Control

[Zum Modul
](https://github.com/Burki24/ShutterButtonControl)

---

## 🔧 Konfiguration

| Einstellung | Beschreibung |
|------------|-------------|
| Button Variable | Taster (pressed / released) |
| Shutter Bewegung | Variable für OPEN / CLOSE / STOP |
| Shutter Position | Variable für Positionssteuerung |
| Richtung | Hoch oder Runter |
| kurzer Tastendruck (ms) | Schwelle zwischen kurz/lang |
| Positionslogik | Zuordnung von 0 und 100 |

---

## Funktionsweise

### Kurzer Tastendruck

- Setzt die Zielposition des Rollladens
- Wert abhängig von der Positionslogik

---

### Langer Tastendruck

- Startet die Bewegung (OPEN oder CLOSE)
- Beim Loslassen:
  - STOP (falls unterstützt)

---

## Hinweise

- Die verwendeten Werte (`OPEN`, `CLOSE`, `STOP`) sind gerätespezifisch  
- Nicht alle Geräte unterstützen `STOP`  
- Positionswerte können je nach System invertiert sein  
- Debug-Ausgaben helfen bei der Analyse  

---

## Entwicklung & SDK

Dieses Modul basiert auf dem offiziellen  
[Symcon PHP SDK](https://www.symcon.de/service/dokumentation/entwicklerbereich/sdk-tools/sdk-php/)

---

## Lizenz

Dieses Projekt steht unter der MIT License.
