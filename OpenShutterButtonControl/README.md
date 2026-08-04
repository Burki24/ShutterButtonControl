# OpenShutterButtonControl

## 1. Funktionsumfang

Das Modul wertet eine Tastervariable aus und steuert abhängig von der Druckdauer einen Rollladen:

- **Kurzer Tastendruck:** Die konfigurierte Endposition wird über die Positionsvariable angefahren.
- **Langer Tastendruck:** Die Bewegung wird über `OPEN` oder `CLOSE` gestartet. Beim Loslassen sendet das Modul `STOP`.
- Die letzte erkannte Aktion und Druckdauer werden als Statusvariablen angezeigt.
- Die Anbindung ist herstellerunabhängig, sofern die verwendeten Variablen die dokumentierten Werte und Aktionen bereitstellen.
- Eine laufende Langfahrt wird bei Loslassen, Änderung der Instanzkonfiguration oder Löschen der Instanz mit `STOP` beendet.
- Die Initialisierung der Fremdvariablen erfolgt erst, wenn der Symcon-Kernel vollständig betriebsbereit ist.

## 2. Voraussetzungen

- Symcon ab Version 9.0
- Tastervariable als Boolean, Integer/Float mit 0 und 1 oder String mit gebräuchlichen Zuständen wie `pressed`/`released` beziehungsweise `on`/`off`
- Bewegungsvariable vom Typ String mit einer ausführbaren Aktion
- Positionsvariable vom Typ Integer oder Float mit einer ausführbaren Aktion

## 3. Software-Installation

Über das Module Control folgende Repository-URL hinzufügen:

```text
https://github.com/Burki24/OpenShutterButtonControl
```

## 4. Einrichten der Instanz

Unter **Instanz hinzufügen** das Modul **OpenShutterButtonControl** auswählen.

| Einstellung | Beschreibung |
| --- | --- |
| Tastervariable | Liefert den gedrückten und losgelassenen Zustand |
| Bewegungsvariable | Erwartet `OPEN`, `CLOSE` und `STOP` |
| Positionsvariable | Erwartet einen Zielwert zwischen 0 und 100 |
| Richtung | Bestimmt, ob dieser Taster öffnet oder schließt |
| Positionslogik | Ordnet offen/geschlossen den Werten 0 und 100 zu |
| Grenze langer Tastendruck | Zeit zwischen 100 und 5000 ms, ab der die kontinuierliche Bewegung startet |

Das Konfigurationsformular lässt für Bewegungs- und Positionsvariable nur passende Variablentypen mit vorhandener Aktion zu. Die gleichen Voraussetzungen werden zusätzlich bei jeder Initialisierung im Modulcode geprüft. Alle drei ausgewählten Variablen werden als Symcon-Objektreferenzen registriert.

## 5. Statusvariablen und Darstellungen

| Ident | Typ | Beschreibung |
| --- | --- | --- |
| `last_duration_ms` | Integer | Dauer des letzten vollständigen Tastendrucks in Millisekunden |
| `last_action` | String | Letzte Aktion: kurzer oder langer Tastendruck |

Das Modul erstellt keine eigenen Variablenprofile. Beide permanent benötigten Statusvariablen werden direkt in `Create()` registriert und verwenden native Symcon-Darstellungen aus dem zentralen `VariablePresentationHelper`.

## 6. Statushinweise

Die Instanz zeigt ihren aktuellen Zustand direkt in der Symcon-Konsole an. Bei einem Konfigurationsfehler wird die Tasterüberwachung nicht registriert beziehungsweise sicher beendet, bis wieder eine gültige Konfiguration vorliegt.

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

## 7. Visualisierung

Die Statusvariablen können bei Bedarf in der Symcon-Visualisierung angezeigt werden. Die Rollladensteuerung selbst erfolgt über den angebundenen physischen oder virtuellen Taster. Bei einer unvollständigen oder ungültigen Konfiguration zeigt die Instanz einen konkreten Statushinweis für die betroffene Variable oder Eigenschaft an.

## 8. PHP-Befehlsreferenz

Das Modul besitzt keine zusätzliche öffentliche PHP-API. Die Verarbeitung erfolgt automatisch über die konfigurierte Tastervariable.
