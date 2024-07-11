# Mailkonten Changelog

## Version 1.5.0 (2024-07-11)

* Add: tl_mailkonten neue Felder smtp_server,smtp_port,pop3_server,pop3_port,imap_server,imap_port für den Zugriff auf die Konten
* Fix: Warning: Undefined array key "auslastung_options" in dca/tl_mailkonten.php (line 233)
* Add: Public-Klasse email_check für die Abfrage der Mailkonten
* Add: IMAP-Klasse für den Zugriff auf E-Mail-Konten

## Version 1.4.0 (2024-04-18)

* Add: codefog/contao-haste
* Change: Haste-Toggler statt des normalen Togglers
* Add: Kompatibilität PHP 8

## Version 1.3.2 (2022-03-25)

* Fix: Kopieren eines Datensatz erzeugt Fehler. Feld email ist unique, deshalb Option markAsCopy für das Feld aktiviert.
* Change: Checkbox für gelöschte Konten hinzufügen -> published-Feld nutze ich dafür
* Change: Auslastungsoptionen verfeinert von 10er auf 5er Schritte

## Version 1.3.1 (2022-03-11)

* Change: MCW: Datumsfelder kleiner machen, E-Mail-Felder größer. Insbesondere bei Weiterleitungen.
* Add: Änderungsdatum exportieren
* Fix: Export-Ausgabe enthält unten einen HTML-Teil
* Add: Nummern Spam im Export auflösen
* Add: Mailinglisten-URL verlinken

## Version 1.3.0 (2022-03-10)

* Fix: Übersetzungen
* Change: published-Feld default auf true setzen
* Add: Toggle-Funktion für published
* Add: Aktualisierungsdatum ausgeben in Übersicht
* Add: MCW Weiterleitungen: Feld für Inhaber hinzufügen
* Add: Kurzer Infotext zur E-Mail (direkt neben email-Feld)
* Add: Kontenübersicht: mehr Informationen anzeigen
* Add: Export der Konten in eine Textdatei (mit Option: welche Felder)

## Version 1.2.0 (2022-03-10)

* Fix: Inhaber-Feld mit rgxp=email
* Change: Umbau des Formulars

## Version 1.1.1 (2022-03-10)

* Add: Konto-Art Weiterleitung/Mailingliste
* Fix: Anzeigefehler im MCW beim Datum: 01.01.1970
* Add: Inhaber-Feld

## Version 1.1.0 (2022-03-10)

* Add: Abhängigkeit menatwork/contao-multicolumnwizard-bundle
* Add: MCW für Alias-Adressen, Weiterleitungen und Bearbeitungsgeschichte

## Version 1.0.0 (2020-12-22)

* Fix: BE-Modul wurde nicht angezeigt
* Fix: Verbesserungen BE-Formular
* Fix: BE-Übersicht korrigiert

## Version 0.0.3 (2020-12-18)

* Fix: PHP-Fehler dca/tl_mailkonten.php

## Version 0.0.2 (2020-12-18)

* Add: Übersetzungen und Optionen für BE-Formular
* Delete: tl_mailkonten.published überflüssig

## Version 0.0.1 (2020-12-18)

* Initiale Version
