# Mailkonten Changelog

## Version 2.0.0 (2026-08-02)

Portierung auf Contao 4.13/5 und PHP 8.3. Die Erweiterung setzt jetzt
mindestens PHP 7.4 und Contao 4.13 voraus.

### Abhängigkeiten

* Change: `php` auf `^7.4 || ^8.0` und `contao/core-bundle` auf `^4.13 || ^5.0` angehoben
* Delete: `codefog/contao-haste` entfernt – der Toggler kommt jetzt aus dem Contao-Kern
* Delete: `phpmailer/phpmailer` entfernt – die Ping-Mail läuft über Symfony Mailer, den Contao ohnehin mitbringt
* Delete: `doctrine/doctrine-cache-bundle` aus `require-dev` entfernt
* Change: `menatwork/contao-multicolumnwizard-bundle` auf `^3.4` festgelegt statt `*`
* Add: `ext-imap` als Empfehlung eingetragen; seit PHP 8.4 ist sie nicht mehr im Standardumfang

### Cronjob statt öffentlichem Skript

* Delete: `src/Resources/public/email_check.php` entfernt. Die Datei band Contao über
  `system/initialize.php` ein – einen Weg, den es seit Contao 4.5 nicht mehr gibt. Sie lag
  zudem im öffentlichen Verzeichnis: Ein Aufruf der Adresse genügte, um sämtliche Postfächer
  anzumelden und Mails zu verschicken
* Add: `Cron\Kontenpruefung` als Contao-Cronjob (stündlich, Dienst-Kennzeichnung `contao.cronjob`)
* Add: Der Checkup eines Kontos läuft einmal täglich, die Ping-Mail einmal im Monat.
  Ohne diese Abstände hätte der stündliche Cronjob vierundzwanzig Übersichten am Tag
  und ebenso viele Ping-Mails je Konto verschickt
* Add: `checkup_date` und `ping_date` werden jetzt tatsächlich geschrieben – die Felder
  gab es seit 1.5.3, gefüllt hat sie nie jemand
* Change: Absender und Empfänger der Ping-Mail sind das Konto selbst. Vorher standen dort
  fest verdrahtet Adresse und Name des Schachbund-Webmasters
* Fix: Ein nicht erreichbares Postfach bricht den Lauf nicht mehr ab, sondern landet im
  Systemprotokoll; die übrigen Konten werden weiter geprüft

### PHP-8-Fehler

* Fix: `Export::getRecords()` griff über `$dc->Session` auf die Sitzung zu – das gibt es
  seit Contao 4 nicht mehr, der Export brach mit einem Fehler ab. Suche und Filter kommen
  jetzt aus dem Sitzungsbehälter `contao_backend`
* Fix: `unserialize()` auf leeren Wizard-Feldern (Contao speichert diese als NULL) lieferte
  `false` und brach das anschließende `foreach` ab. Überall durch `StringUtil::deserialize()`
  ersetzt
* Fix: `tl_mailkonten::getURL()` gab bei einem Konto ohne Adresse der Listenverwaltung eine
  undefinierte Variable zurück – unter PHP 8 eine Warnung bei jedem Aufruf des Formulars
* Fix: `IMAP::__construct()` merkte sich bei einer fehlgeschlagenen Anmeldung ein `false`
  im Stream; jeder folgende Aufruf lief damit in einen TypeError. Der Fehlschlag wird jetzt
  als Ausnahme gemeldet
* Fix: Division durch null in der Auslastungsrechnung, wenn ein Postfach kein Limit hat.
  Unter PHP 8 ist das ein `DivisionByZeroError` und kein Hinweis mehr
* Fix: `imap_status()` und `imap_fetch_overview()` gegen `false`-Rückgaben abgesichert

### Contao-5-Kompatibilität

* Change: Toggle-Operation von `haste_ajax_operation` auf die Kern-Operation
  `act=toggle&field=published` umgestellt, `published` mit `'toggle' => true` gekennzeichnet
* Change: `dataContainer` auf `DC_Table::class` umgestellt, `mode` und `flag` auf die
  Konstanten von `DataContainer`
* Change: Operations-Symbole von `.gif` auf `.svg` umgestellt – Contao 5 liefert nur noch SVG
* Change: `_instanceof`-Block aus der `services.yml` entfernt. Keine Klasse dieser Erweiterung
  setzt `FrameworkAwareInterface` oder `ContainerAwareInterface` um, und letzteres gibt es
  seit Symfony 7 nicht mehr – der Block hätte den Containerbau verhindert
* Change: Globale Klassen (`\Backend`, `\Database`, `\Input`, `\Email`, `\DataContainer`)
  durch die Klassen aus dem Namensraum `Contao` ersetzt
* Delete: Schlüssel `icon` aus der Backend-Modul-Anmeldung entfernt. Er verwies auf eine
  Bilddatei, die es im Bundle gar nicht gibt, und wird seit Contao 4 nicht mehr ausgewertet

### Sicherheit

* Fix: SQL-Injection im Listenexport. Feldname und Wert aus Suche und Filter wanderten
  unverändert in den SQL-Text; wer den Filter in der Backend-Adresszeile veränderte, konnte
  beliebiges SQL einschleusen. Feldnamen werden jetzt gegen das DCA geprüft und maskiert,
  Werte grundsätzlich gebunden
* Fix: Die Adressen in der Listenansicht werden vor der Ausgabe maskiert

### Korrektheit und Code-Qualität

* Fix: Die Spalte **Info** der Listenansicht zeigte seit 1.5.5 die Alias-Adressen, weil beim
  Entfernen der Alias-Spalte das `label_callback` nicht mitgezogen wurde. Die Alias-Spalte ist
  wieder da, die Info damit auch
* Fix: Beschriftung des Export-Knopfes verwies auf `tl_lizenzverwaltung` – ein Überbleibsel
  der Erweiterung, aus der der Export übernommen wurde. Der Knopf trug deshalb gar keine Beschriftung
* Fix: Doppelter Schlüssel `email` in den SQL-Schlüsseln des DCA. PHP behielt nur den letzten,
  der Index war von Anfang an wirkungslos
* Fix: Vorbelegung von `tl_mailkonten.deleted` von `'1'` auf `''` geändert. Jedes neue Konto
  galt in der Datenbank als gelöscht
* Fix: Die fünfte Ebene des Baum-Exports las die Weiterleitungen zwar noch aus, gab sie aber
  nicht mehr aus. Die fünf verschachtelten Schleifen sind jetzt eine Rekursion mit Schutz
  gegen sich gegenseitig weiterleitende Konten
* Fix: Pflichtfelder in `tl_settings` entfernt. Solange sie leer waren, ließ sich die gesamte
  Einstellungsseite von Contao nicht speichern
* Change: Export und Baum liefern ihre Datei jetzt als `ResponseException` statt mit `exit`.
  Damit ist der HTML-Rest am Dateiende endgültig ausgeschlossen
* Change: Zeichensatz der Datei `Classes/IMAP.php` bereinigt – die Kommentare waren nicht UTF-8
* Change: `declare(strict_types=1)` in allen Klassendateien, deutsche Kommentarblöcke an jeder Methode
* Delete: Nicht verwendete Sprachschlüssel `art` und `art_options` entfernt; die Spam-Optionen
  von `default.php` nach `tl_mailkonten.php` verschoben, wo sie hingehören
* Add: `tests/` mit 16 Unit-Tests für Textexport, Baumaufbau und die Umrechnung der Postfachwerte

## Version 1.6.2 (2026-07-29)

* Fix: Warning: Undefined array key "deleteConfirm" bei contao:migrate -> Lesezugriffe auf $GLOBALS['TL_LANG'] in den DCA-Dateien mit `?? null` bzw. `?? array()` abgesichert, da der DcaLoader die Sprachdateien noch nicht geladen hat
* Change: Beschreibung, Keywords und Homepage in der composer.json ergänzt, damit Packagist das Paket verständlich darstellt und über die Suche auffindbar macht

## Version 1.6.1 (2026-04-27)

* Fix: Baum-Export von Weiterleitungen -> abgeschaltete Weiterleitungsfunktion wurde nicht beachtet

## Version 1.6.0 (2026-04-27)

* Add: Ausgabe von Konten als Baumstruktur (inkl. verlinkter Konten über Weiterleitungen) -> Ausgabe bis Ebene 5

## Version 1.5.5 (2025-01-28)

* Fix: tl_mailkonten Funktion loadDate gibt immer 01.01.1970 zurück -> Rückgabe bei 0 auf '' statt 0 geändert
* Add: tl_mailkonten.deleted -> Löschmarkierung
* Change: Auflistung Datensätze -> alias, mailinglist, tstamp entfernt, inhaber hinzugefügt

## Version 1.5.4 (2025-01-04)

* Fix: date(): Argument #2 ($timestamp) must be of type ?int, string given in /src/Resources/contao/dca/tl_mailkonten.php (line 800)

## Version 1.5.3 (2025-01-04)

* Change: Klasse email_check -> Abfrage der E-Mail-Konten alphabetisch aufwärts
* Add: tl_mailkonten.checkup_date und backup_date, um den letzten Zugriff auf diese Funktionen zu dokumentieren
* Add: tl_mailkonten.ping_date, um das letzte Versenden der Ping-Mail zu dokumentieren
* Fix: date(): Argument #2 ($timestamp) must be of type ?int, string given in /src/Resources/contao/dca/tl_mailkonten.php (line 788)

## Version 1.5.2 (2024-09-08)

* Add: tl_settings ausgebaut
* Add: "phpmailer/phpmailer": "*" in composer.json

## Version 1.5.1 (2024-07-20)

* Change: Klasse email_check ausgebaut
* Add: tl_settings.mailkonten_admin

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
