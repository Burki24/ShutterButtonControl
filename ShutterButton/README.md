# ShutterButton

## 1. Funktionsumfang

Das Modul wertet eine Tastervariable aus und steuert abhängig von der Druckdauer einen Rollladen:

- **Kurzer Tastendruck:** Die konfigurierte Endposition wird über die Positionsvariable angefahren.
- **Langer Tastendruck:** Die Bewegung wird über `OPEN` oder `CLOSE` gestartet. Beim Loslassen sendet das Modul `STOP`.
- Die letzte erkannte Aktion und Druckdauer werden als Statusvariablen angezeigt.

## 2. Voraussetzungen

- Symcon ab Version 9.0
- Tastervariable als Boolean, Integer/Float mit 0 und 1 oder String mit gebräuchlichen Zuständen wie `pressed`/`released` beziehungsweise `on`/`off`
- Bewegungsvariable vom Typ String mit einer ausführbaren Aktion
- Positionsvariable vom Typ Integer oder Float mit einer ausführbaren Aktion

## 3. Software-Installation

Über das Module Control folgende Repository-URL hinzufügen:

```text
https://github.com/Burki24/ShutterButtonControl
```

## 4. Einrichten der Instanz

Unter **Instanz hinzufügen** das Modul **ShutterButton** auswählen.

| Einstellung | Beschreibung |
| --- | --- |
| Tastervariable | Liefert den gedrückten und losgelassenen Zustand |
| Bewegungsvariable | Erwartet `OPEN`, `CLOSE` und `STOP` |
| Positionsvariable | Erwartet einen Zielwert zwischen 0 und 100 |
| Richtung | Bestimmt, ob dieser Taster öffnet oder schließt |
| Positionslogik | Ordnet offen/geschlossen den Werten 0 und 100 zu |
| Grenze langer Tastendruck | Zeit, ab der die kontinuierliche Bewegung startet |

## 5. Statusvariablen und Darstellungen

| Ident | Typ | Beschreibung |
| --- | --- | --- |
| `last_duration_ms` | Integer | Dauer des letzten vollständigen Tastendrucks in Millisekunden |
| `last_action` | String | Letzte Aktion: kurzer oder langer Tastendruck |

Das Modul erstellt keine eigenen Variablenprofile. Beide Variablen verwenden native Symcon-Darstellungen aus dem zentralen `VariablePresentationHelper`.

## 6. Visualisierung

Die Statusvariablen können bei Bedarf in der Symcon-Visualisierung angezeigt werden. Die Rollladensteuerung selbst erfolgt über den angebundenen physischen oder virtuellen Taster.

## 7. PHP-Befehlsreferenz

Das Modul besitzt keine zusätzliche öffentliche PHP-API. Die Verarbeitung erfolgt automatisch über die konfigurierte Tastervariable.
