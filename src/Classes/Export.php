<?php

declare(strict_types=1);

/**
 * Mailkonten für Contao Open Source CMS
 *
 * @author    Frank Hoppe
 * @license   LGPL-3.0-or-later
 */

namespace Schachbulle\ContaoMailkontenBundle\Classes;

use Contao\Controller;
use Contao\CoreBundle\Exception\ResponseException;
use Contao\Database;
use Contao\DataContainer;
use Contao\Input;
use Contao\StringUtil;
use Contao\System;
use Symfony\Component\HttpFoundation\Response;

/**
 * Export aller angezeigten E-Mail-Konten in eine Textdatei.
 *
 * Die Klasse hängt als Modulschlüssel „export“ am Backend-Modul (siehe
 * Resources/contao/config/config.php). Contao ruft exportText() auf, sobald im
 * Backend der Knopf „Export“ gedrückt wird.
 *
 * Exportiert wird genau das, was die Liste gerade zeigt: Suche und Filter der
 * laufenden Backend-Sitzung werden übernommen, die Seitenaufteilung dagegen
 * nicht — eine Textdatei mit nur 30 von 300 Konten wäre nutzlos.
 */
final class Export
{
	/**
	 * Baut die Textdatei und schickt sie als Download an den Browser.
	 *
	 * Der Download wird als Ausnahme geworfen statt mit `exit` erzwungen.
	 * Contao fängt die ResponseException ab und liefert die Antwort unverändert
	 * aus. Der frühere Weg über `exit` hing davon ab, dass kein Ausgabepuffer
	 * dazwischenstand — genau daher stammte der HTML-Rest am Dateiende, der in
	 * Fassung 1.3.1 als Fehler auftauchte.
	 *
	 * @param DataContainer $dc Data Container der Liste; liefert den
	 *                          Tabellennamen für Suche und Filter
	 *
	 * @return string Immer der leere String. Der eigentliche Rückgabeweg ist
	 *                die geworfene Ausnahme; der leere String greift nur, wenn
	 *                die Methode ohne den Schlüssel „export“ aufgerufen wurde
	 *
	 * @throws ResponseException Trägt die fertige Textdatei als Download
	 */
	public function exportText(DataContainer $dc): string
	{
		if ('export' !== Input::get('key'))
		{
			return '';
		}

		$inhalt = self::formatiere(self::ladeKonten($dc->table));
		$dateiname = 'Mailkonten_'.date('Ymd-His').'.txt';

		$response = new Response($inhalt);
		$response->headers->set('Content-Type', 'text/plain; charset=utf-8');
		$response->headers->set('Content-Disposition', 'attachment; filename="'.$dateiname.'"');
		$response->headers->set('Cache-Control', 'must-revalidate, post-check=0, pre-check=0');
		$response->headers->set('Pragma', 'public');
		$response->headers->set('Expires', '0');

		throw new ResponseException($response);
	}

	/**
	 * Liest die Konten aus der Datenbank, eingeschränkt wie die Backend-Liste.
	 *
	 * Suchbegriff und Filter stehen in der Backend-Sitzung. Contao legt sie im
	 * Sitzungsbehälter „contao_backend“ ab; die frühere Fassung griff über
	 * `$dc->Session` darauf zu, was es seit Contao 4 nicht mehr gibt und unter
	 * PHP 8 mit einem Fehler abbrach.
	 *
	 * **Sicherheit:** Feldnamen aus der Sitzung werden gegen die DCA-Felder
	 * geprüft und maskiert, Werte grundsätzlich gebunden. Vorher wanderten
	 * beide unverändert in den SQL-Text — wer den Filter der Backend-Adresszeile
	 * manipulierte, konnte damit beliebiges SQL einschleusen.
	 *
	 * @param string $tabelle Tabellenname, üblicherweise „tl_mailkonten“
	 *
	 * @return array Liste der Konten als assoziative Arrays, sortiert nach
	 *               E-Mail-Adresse; leer, wenn kein Konto passt
	 */
	public static function ladeKonten(string $tabelle): array
	{
		Controller::loadDataContainer($tabelle);

		$bedingungen = array();
		$werte = array();

		// Suchbegriff der laufenden Ansicht übernehmen
		$suche = self::sitzungswert('search', $tabelle);

		if (!empty($suche['field']) && isset($suche['value']) && '' !== (string) $suche['value'])
		{
			$feld = self::pruefeFeld($tabelle, (string) $suche['field']);

			if (null !== $feld)
			{
				$bedingungen[] = 'CAST('.$feld.' AS CHAR) REGEXP ?';
				$werte[] = self::pruefeRegexp((string) $suche['value']);
			}
		}

		// Filter der laufenden Ansicht übernehmen. „limit“ ist die
		// Seitenaufteilung und wird bewusst übergangen
		foreach (self::sitzungswert('filter', $tabelle) as $name => $wert)
		{
			if ('limit' === $name || \is_array($wert))
			{
				continue;
			}

			$feld = self::pruefeFeld($tabelle, (string) $name);

			if (null !== $feld)
			{
				$bedingungen[] = $feld.'=?';
				$werte[] = $wert;
			}
		}

		$sql = 'SELECT * FROM '.Database::quoteIdentifier($tabelle);

		if ($bedingungen)
		{
			$sql .= ' WHERE '.implode(' AND ', $bedingungen);
		}

		$sql .= ' ORDER BY email ASC';

		$objKonten = Database::getInstance()->prepare($sql)->execute(...$werte);

		$konten = array();

		while ($objKonten->next())
		{
			$konten[] = $objKonten->row();
		}

		return $konten;
	}

	/**
	 * Setzt die Konten zu einer Textdatei zusammen.
	 *
	 * Bewusst ohne Datenbank- und Sitzungszugriff, damit der Aufbau der Datei
	 * für sich getestet werden kann.
	 *
	 * @param array $konten Datensätze aus ladeKonten()
	 *
	 * @return string Fertiger Dateiinhalt mit CRLF als Zeilenende (die Datei
	 *                landet erfahrungsgemäß im Windows-Editor). Leerer String,
	 *                wenn keine Konten übergeben wurden
	 */
	public static function formatiere(array $konten): string
	{
		$zeilen = array();

		foreach ($konten as $konto)
		{
			$zeitstempel = (int) ($konto['tstamp'] ?? 0);

			$zeilen[] = 'Konto: '.($konto['email'] ?? '').' (Letzte Änderung: '.($zeitstempel ? date('d.m.Y H:i', $zeitstempel) : 'unbekannt').')';
			$zeilen[] = '    Info: '.($konto['info'] ?? '');

			if (!empty($konto['pop3']))
			{
				$zeilen[] = '    POP3/IMAP: Ja';
				$zeilen[] = '        Inhaber: '.($konto['inhaber'] ?? '');
				$zeilen[] = '        Mailbox-Größe: '.($konto['mailbox_groesse'] ?? 0).' MB ('.($konto['auslastung'] ?? 0).'% belegt)';
				$zeilen[] = '        Spam-Filter: '.self::spamText((string) ($konto['spam'] ?? ''));
				$zeilen[] = '        Leerung: '.(!empty($konto['leerung']) ? 'Ja' : 'Nein');
			}

			if (!empty($konto['forward']))
			{
				$zeilen[] = '    Weiterleitungen: Ja';

				foreach (StringUtil::deserialize($konto['forwarder'] ?? null, true) as $eintrag)
				{
					$zeilen[] = '        Adresse: '.($eintrag['forwarder_email'] ?? '');
				}
			}

			if (!empty($konto['alias']))
			{
				$zeilen[] = '    Aliasse: Ja';

				foreach (StringUtil::deserialize($konto['aliase'] ?? null, true) as $eintrag)
				{
					$zeilen[] = '        Adresse: '.($eintrag['aliase_email'] ?? '');
				}
			}

			if (!empty($konto['mailinglist']))
			{
				$zeilen[] = '    Mailingliste: Ja';

				if (!empty($konto['url']))
				{
					$zeilen[] = '        Listenverwaltung: '.$konto['url'];
				}
			}

			$zeilen[] = '';
		}

		return $zeilen ? implode("\r\n", $zeilen)."\r\n" : '';
	}

	/**
	 * Übersetzt den gespeicherten Spam-Schlüssel in seinen Klartext.
	 *
	 * @param string $schluessel Wert des Feldes spam, „1“ bis „4“
	 *
	 * @return string Bezeichnung aus der Sprachdatei, etwa „Markieren“. Ist der
	 *                Schlüssel unbekannt oder leer, kommt „-“ zurück
	 */
	private static function spamText(string $schluessel): string
	{
		if ('' === $schluessel)
		{
			return '-';
		}

		return (string) ($GLOBALS['TL_LANG']['tl_mailkonten']['spam_options'][$schluessel] ?? $schluessel);
	}

	/**
	 * Liest einen Eintrag der Backend-Sitzung für eine bestimmte Tabelle.
	 *
	 * @param string $schluessel „search“ oder „filter“
	 * @param string $tabelle    Tabellenname, unter dem Contao die Werte ablegt
	 *
	 * @return array Gespeicherte Werte; leeres Array, wenn nichts hinterlegt
	 *               ist oder gar keine Sitzung läuft (etwa auf der Konsole)
	 */
	private static function sitzungswert(string $schluessel, string $tabelle): array
	{
		$request = System::getContainer()->get('request_stack')->getCurrentRequest();

		if (null === $request || !$request->hasSession())
		{
			return array();
		}

		$daten = $request->getSession()->getBag('contao_backend')->get($schluessel);

		return \is_array($daten[$tabelle] ?? null) ? $daten[$tabelle] : array();
	}

	/**
	 * Prüft einen Feldnamen gegen das DCA und maskiert ihn für SQL.
	 *
	 * @param string $tabelle Tabellenname, dessen DCA geladen ist
	 * @param string $feld    Feldname aus der Sitzung, also aus fremder Hand
	 *
	 * @return string|null Maskierter Feldname zur Verwendung im SQL-Text, oder
	 *                     null wenn die Tabelle kein solches Feld hat
	 */
	private static function pruefeFeld(string $tabelle, string $feld): ?string
	{
		if (!isset($GLOBALS['TL_DCA'][$tabelle]['fields'][$feld]))
		{
			return null;
		}

		return Database::quoteIdentifier($feld);
	}

	/**
	 * Sorgt dafür, dass ein Suchbegriff als regulärer Ausdruck brauchbar ist.
	 *
	 * MySQL bricht mit einem Fehler ab, wenn REGEXP ein ungültiges Muster
	 * bekommt — etwa bei der Suche nach einer einzelnen öffnenden Klammer.
	 * Contao löst das in der Listenansicht genauso: erst probeweise ausführen,
	 * bei einem Fehler den Begriff maskieren und damit wörtlich suchen.
	 *
	 * @param string $begriff Eingetippter Suchbegriff
	 *
	 * @return string Der Begriff selbst oder seine maskierte Fassung
	 */
	private static function pruefeRegexp(string $begriff): string
	{
		try
		{
			Database::getInstance()->prepare("SELECT '' REGEXP ?")->execute($begriff);
		}
		catch (\Exception $e)
		{
			return preg_quote($begriff);
		}

		return $begriff;
	}
}
