# Mailkonten für Contao

Verwaltet die E-Mail-Konten einer Domain samt ihrer Weiterleitungen, Aliasse und
Mailinglisten und hält fest, wer für welches Konto zuständig ist. Für Konten mit
Zugangsdaten prüft die Erweiterung zusätzlich regelmäßig den Füllstand des
Postfachs.

Läuft unter **Contao 4.13 und Contao 5** mit **PHP 7.4 bis 8.3**.

## Installation

Über den Contao Manager das Paket `schachbulle/contao-mailkonten-bundle` suchen
und installieren, oder auf der Konsole:

```bash
composer require schachbulle/contao-mailkonten-bundle
```

Anschließend die Datenbank aktualisieren (Contao Manager oder
`vendor/bin/contao-console contao:migrate`).

Für den Checkup wird zusätzlich die PHP-Erweiterung `imap` gebraucht. Sie ist
bis PHP 8.3 im Standardumfang enthalten; ab PHP 8.4 muss sie über PECL
nachinstalliert werden. Fehlt sie, bleibt der übrige Funktionsumfang nutzbar —
der Checkup meldet dann einmalig im Systemprotokoll, dass er übersprungen wurde.

## Backend-Modul

Das Modul **Mailkonten** findet sich im Backend unter *Inhalte*. Je Konto lassen
sich vier Bereiche einschalten, die dann eigene Felder aufklappen:

| Bereich | Inhalt |
| --- | --- |
| POP3/IMAP | Inhaber, Passwort, Postfachgröße, Auslastung, Spam-Behandlung sowie die Server und Ports für SMTP, POP3 und IMAP |
| Weiterleitung | Zieladressen mit Inhaber, Einrichtungsdatum und Grund |
| Alias | Alias-Adressen mit Einrichtungsdatum und Grund |
| Mailingliste | Adresse der Listenverwaltung, deren Passwort und die eingetragenen Adressen |

Dazu kommen eine Bearbeitungsgeschichte, ein freies Anmerkungsfeld und die
beiden Schalter **Aktiv** und **Gelöscht**.

### Export der Liste

Der Knopf **Export** über der Liste schreibt alle angezeigten Konten in eine
Textdatei. Suche und Filter der laufenden Ansicht werden übernommen, die
Seitenaufteilung dagegen nicht — die Datei enthält also immer alle Treffer.

### Baum eines Kontos

Das Baum-Symbol in der Zeile eines Kontos schreibt dieses eine Konto samt aller
Weiterleitungen in eine Textdatei. Führt eine Weiterleitung auf ein weiteres
Konto, das selbst weiterleitet, wird auch dieses aufgelöst — so ist zu sehen, wo
eine Nachricht am Ende wirklich ankommt:

```text
Konto: geschaeftsstelle@example.org
===================================
├─ vorstand@example.org
│ ├─ praesident@example.org
├─ pressestelle@example.org
```

Aufgelöst wird bis zur fünften Ebene. Berücksichtigt werden nur aktive Konten
mit eingeschalteter Weiterleitung; Konten, die sich gegenseitig weiterleiten,
brechen den Zweig ab, statt ihn endlos zu wiederholen.

## Checkup und Ping-Mail

Ist bei einem Konto **Checkup** eingeschaltet und sind Zugangsdaten hinterlegt,
kümmert sich ein Cronjob um zwei Dinge:

* **Täglich** meldet er sich per IMAP am Postfach an, zählt die Nachrichten und
  liest die Speicherbelegung aus. Danach geht eine Übersicht aller geprüften
  Konten an den Administrator; Konten über 90 Prozent Belegung stehen darin rot.
* **Monatlich** verschickt er über den SMTP-Server des Kontos eine Ping-Mail an
  das Konto selbst. Damit gilt das Konto beim Anbieter als genutzt und wird
  nicht wegen Untätigkeit gelöscht.

Wann ein Konto zuletzt an der Reihe war, steht in den Feldern `checkup_date` und
`ping_date`. Contao stößt den Cronjob stündlich an; die beiden Abstände sorgen
dafür, dass daraus nicht vierundzwanzig Übersichten am Tag werden.

Ein nicht erreichbares Postfach bricht den Lauf nicht ab — der Grund landet im
Systemprotokoll, und die übrigen Konten werden weiter geprüft.

### Einstellungen

Unter *System -> Einstellungen* im Bereich **Mailkonten-Verwaltung**:

| Einstellung | Bedeutung |
| --- | --- |
| Cronjob | Schaltet die stündliche Prüfung ein. Ohne diesen Haken passiert nichts. |
| E-Mail-Adresse Admin | Empfänger der Übersicht. Ohne Eintrag wird keine Übersicht verschickt. |
| Absendername | Absendername der Übersicht, ersatzweise „Mailkonten“ |
| Betreff | Betreff der Übersicht, ersatzweise „Mailkonten-Checkup“ |

Damit der Cronjob überhaupt läuft, muss Contaos eigener Cronjob eingerichtet
sein — entweder über den Aufruf von `contao:cron` durch den Hoster oder über den
Aufruf beim Seitenaufruf im Frontend.

Das Feld **Backup** je Konto ist vorgesehen, aber noch nicht umgesetzt; es wird
derzeit von keiner Funktion ausgewertet.

## Hinweise zum Betrieb

* Die Passwörter der Konten stehen im Klartext in der Datenbank. Anders ließen
  sich Postfach und SMTP-Server nicht ansprechen. Der Zugriff auf das
  Backend-Modul sollte deshalb auf wenige Personen beschränkt bleiben.
* Die Oberfläche gibt es nur auf Deutsch. In einem englischen Backend bleiben
  die Beschriftungen leer.

## Tests

```bash
vendor/bin/phpunit
```

Die Erweiterung wird ohne `vendor/`-Verzeichnis ausgeliefert. Läuft PHPUnit von
außerhalb, registriert `tests/bootstrap.php` die Namensräume selbst; Tests, die
Contao-Klassen brauchen, werden dann übersprungen.

## Entwickler

**Frank Hoppe**
