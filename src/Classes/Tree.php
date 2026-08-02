<?php

declare(strict_types=1);

/**
 * Mailkonten für Contao Open Source CMS
 *
 * @author    Frank Hoppe
 * @license   LGPL-3.0-or-later
 */

namespace Schachbulle\ContaoMailkontenBundle\Classes;

use Contao\CoreBundle\Exception\ResponseException;
use Contao\Database;
use Contao\DataContainer;
use Contao\Input;
use Contao\StringUtil;
use Symfony\Component\HttpFoundation\Response;

/**
 * Export eines einzelnen Kontos samt aller Weiterleitungen als Baum.
 *
 * Die Klasse hängt als Modulschlüssel „tree“ am Backend-Modul (siehe
 * Resources/contao/config/config.php) und wird über das Baum-Symbol in der
 * Zeile eines Kontos aufgerufen.
 *
 * Leitet ein Konto an eine Adresse weiter, die selbst ein Konto mit
 * Weiterleitungen ist, wird auch diese aufgelöst — so lässt sich nachvollziehen,
 * wo eine Nachricht am Ende wirklich ankommt.
 */
final class Tree
{
	/**
	 * Größte Verschachtelungstiefe der Auflösung.
	 *
	 * Fünf Ebenen reichen für die Verhältnisse beim Schachbund und begrenzen
	 * zugleich den Aufwand: Jede Ebene bedeutet eine Datenbankabfrage je
	 * gefundener Adresse.
	 */
	private const MAX_TIEFE = 5;

	/**
	 * Baut die Baumdarstellung und schickt sie als Download an den Browser.
	 *
	 * Wie beim Listenexport wird die Antwort als Ausnahme geworfen, statt die
	 * Ausgabe mit `exit` abzuwürgen.
	 *
	 * @param DataContainer $dc Data Container der Liste; wird nicht ausgewertet,
	 *                          gehört aber zur von Contao erwarteten Signatur
	 *
	 * @return string Immer der leere String; siehe exportText() im Listenexport
	 *
	 * @throws ResponseException Trägt die fertige Textdatei als Download
	 */
	public function exportKonto(DataContainer $dc): string
	{
		if ('tree' !== Input::get('key'))
		{
			return '';
		}

		$objKonto = Database::getInstance()
			->prepare('SELECT * FROM tl_mailkonten WHERE id=?')
			->limit(1)
			->execute(Input::get('id'));

		if (!$objKonto->numRows)
		{
			return '';
		}

		$zeilen = self::baum(
			(string) $objKonto->email,
			$objKonto->forward ? StringUtil::deserialize($objKonto->forwarder, true) : array()
		);

		$dateiname = 'Mailkonto_'.$objKonto->email.'_'.date('Ymd-His').'.txt';

		$response = new Response(implode("\r\n", $zeilen)."\r\n");
		$response->headers->set('Content-Type', 'text/plain; charset=utf-8');
		$response->headers->set('Content-Disposition', 'attachment; filename="'.$dateiname.'"');
		$response->headers->set('Cache-Control', 'must-revalidate, post-check=0, pre-check=0');
		$response->headers->set('Pragma', 'public');
		$response->headers->set('Expires', '0');

		throw new ResponseException($response);
	}

	/**
	 * Setzt die Zeilen des Baumes zusammen.
	 *
	 * @param string        $email           Adresse des Kontos, das die Wurzel bildet
	 * @param array         $weiterleitungen Einträge des MultiColumnWizards des
	 *                                       Kontos, jeweils mit dem Schlüssel
	 *                                       forwarder_email
	 * @param callable|null $aufloeser       Ermittelt zu einer Adresse deren
	 *                                       Weiterleitungen. Ohne Angabe wird in
	 *                                       der Datenbank nachgesehen; die Tests
	 *                                       reichen hier eine eigene Zuordnung
	 *                                       herein und kommen so ohne Contao aus
	 *
	 * @return array Liste der Textzeilen ohne Zeilenende. Die erste Zeile ist
	 *               die Adresse, die zweite die Unterstreichung
	 */
	public static function baum(string $email, array $weiterleitungen, ?callable $aufloeser = null): array
	{
		$aufloeser = $aufloeser ?? static function (string $adresse): array { return self::ladeWeiterleitungen($adresse); };

		$zeilen = array
		(
			'Konto: '.$email,
			str_repeat('=', 7 + \strlen($email)),
		);

		foreach ($weiterleitungen as $weiterleitung)
		{
			if (empty($weiterleitung['forwarder_email']))
			{
				continue;
			}

			// Die Wurzel gilt bereits als besucht, damit ein Konto, das auf sich
			// selbst zurückleitet, den Baum nicht künstlich aufbläht
			$zeilen = array_merge(
				$zeilen,
				self::zweig((string) $weiterleitung['forwarder_email'], 1, array($email), $aufloeser)
			);
		}

		return $zeilen;
	}

	/**
	 * Erzeugt die Zeilen eines Astes und steigt rekursiv weiter hinab.
	 *
	 * Ersetzt die früheren fünf ineinander geschachtelten Schleifen. Deren
	 * letzte Ebene las die Weiterleitungen zwar noch aus, gab sie aber nicht
	 * mehr aus — die Arbeit war schlicht verloren.
	 *
	 * @param string   $adresse   Adresse, die auf dieser Ebene ausgegeben wird
	 * @param int      $tiefe     Aktuelle Ebene, beginnend bei 1
	 * @param array    $besucht   Adressen des bisherigen Weges; verhindert, dass
	 *                            sich gegenseitig weiterleitende Konten den Baum
	 *                            mit Wiederholungen füllen
	 * @param callable $aufloeser Ermittelt zu einer Adresse deren Weiterleitungen
	 *
	 * @return array Zeilen dieses Astes samt aller darunterliegenden
	 */
	private static function zweig(string $adresse, int $tiefe, array $besucht, callable $aufloeser): array
	{
		$zeilen = array(str_repeat('│ ', $tiefe - 1).'├─ '.$adresse);

		if ($tiefe >= self::MAX_TIEFE || \in_array($adresse, $besucht, true))
		{
			return $zeilen;
		}

		$besucht[] = $adresse;

		foreach ($aufloeser($adresse) as $weiterleitung)
		{
			$zeilen = array_merge($zeilen, self::zweig($weiterleitung, $tiefe + 1, $besucht, $aufloeser));
		}

		return $zeilen;
	}

	/**
	 * Sucht das Konto zu einer Adresse und liest dessen Weiterleitungen.
	 *
	 * Berücksichtigt werden nur aktive Konten mit eingeschalteter
	 * Weiterleitungsfunktion — ein abgeschaltetes Konto leitet nichts weiter.
	 *
	 * @param string $adresse E-Mail-Adresse, zu der ein Konto gesucht wird
	 *
	 * @return array Liste der Zieladressen; leer, wenn es zu der Adresse kein
	 *               aktives Konto mit Weiterleitungen gibt
	 */
	private static function ladeWeiterleitungen(string $adresse): array
	{
		$objKonto = Database::getInstance()
			->prepare("SELECT forwarder FROM tl_mailkonten WHERE email=? AND forward='1' AND published='1'")
			->limit(1)
			->execute($adresse);

		if (!$objKonto->numRows)
		{
			return array();
		}

		$adressen = array();

		foreach (StringUtil::deserialize($objKonto->forwarder, true) as $weiterleitung)
		{
			if (!empty($weiterleitung['forwarder_email']))
			{
				$adressen[] = (string) $weiterleitung['forwarder_email'];
			}
		}

		return $adressen;
	}
}
