<?php

declare(strict_types=1);

/**
 * Mailkonten für Contao Open Source CMS
 *
 * @author    Frank Hoppe
 * @license   LGPL-3.0-or-later
 */

namespace Schachbulle\ContaoMailkontenBundle\Classes;

use Contao\StringUtil;

/**
 * Baut die Kopiervorlage mit den Zugangsdaten eines Kontos.
 *
 * Wer einem Vereinsmitglied sein neues Postfach einrichtet, muss Benutzername,
 * Passwort, Servernamen, Ports und die eingerichteten Weiterleitungen
 * weitergeben. Die Vorlage stellt das als fertigen Text zusammen, den man im
 * Formular mit einem Klick in die Zwischenablage legt.
 */
final class Kopiervorlage
{
	/**
	 * Setzt den Text der Vorlage zusammen.
	 *
	 * Gearbeitet wird mit dem gespeicherten Datensatz, nicht mit dem, was gerade
	 * ungespeichert im Formular steht. Die Zugangsdaten erscheinen nur, wenn
	 * für das Konto POP3/IMAP eingeschaltet ist; Weiterleitungen, Aliasse und
	 * Auto-Responder stehen unabhängig davon darin, sofern sie gepflegt sind.
	 *
	 * Bewusst ohne Datenbank- und Sprachdateizugriff, damit der Aufbau für sich
	 * getestet werden kann.
	 *
	 * @param array $konto Datensatz aus tl_mailkonten
	 *
	 * @return string Der fertige Text mit Zeilenumbrüchen (LF). Enthält mindestens die
	 *                Adresse; bei einem leeren Datensatz ein leerer String
	 */
	public static function text(array $konto): string
	{
		$email = trim((string) ($konto['email'] ?? ''));

		if ('' === $email) {
			return '';
		}

		$zeilen = array('E-Mail-Konto: '.$email);

		if (!empty($konto['pop3'])) {
			$zeilen[] = 'Benutzername: '.$email;
			$zeilen[] = 'Passwort: '.(string) ($konto['passwort'] ?? '');
			$zeilen[] = '';
			$zeilen[] = 'Posteingang (IMAP): '.self::server($konto, 'imap').' (SSL)';
			$zeilen[] = 'Posteingang (POP3): '.self::server($konto, 'pop3').' (SSL)';
			$zeilen[] = 'Postausgang (SMTP): '.self::server($konto, 'smtp').' (SSL)';
		}

		$weiterleitungen = self::eintraege($konto['forward'] ?? '', $konto['forwarder'] ?? null, 'forwarder_email', 'forwarder_inhaber');

		if ($weiterleitungen) {
			$zeilen[] = '';
			$zeilen[] = 'Weiterleitungen:';

			foreach ($weiterleitungen as $zeile) {
				$zeilen[] = '  - '.$zeile;
			}
		}

		$aliasse = self::eintraege($konto['alias'] ?? '', $konto['aliase'] ?? null, 'aliase_email', '');

		if ($aliasse) {
			$zeilen[] = '';
			$zeilen[] = 'Alias-Adressen:';

			foreach ($aliasse as $zeile) {
				$zeilen[] = '  - '.$zeile;
			}
		}

		if (!empty($konto['auto_responder'])) {
			$zeilen[] = '';
			$zeilen[] = 'Auto-Responder: aktiv';
		}

		return implode("\n", $zeilen);
	}

	/**
	 * Formuliert Servername und Port eines Dienstes.
	 *
	 * @param array  $konto Datensatz aus tl_mailkonten
	 * @param string $dienst „imap“, „pop3“ oder „smtp“
	 *
	 * @return string „server:port“, oder „nicht eingetragen“, wenn kein Servername gepflegt ist
	 */
	private static function server(array $konto, string $dienst): string
	{
		$server = trim((string) ($konto[$dienst.'_server'] ?? ''));

		if ('' === $server) {
			return 'nicht eingetragen';
		}

		$port = (int) ($konto[$dienst.'_port'] ?? 0);

		return $port > 0 ? $server.':'.$port : $server;
	}

	/**
	 * Liest die Zeilen eines MultiColumnWizards als Klartextliste.
	 *
	 * @param mixed  $aktiv      Schalter des Bereichs („forward“ bzw. „alias“); ist er aus, bleibt die Liste leer,
	 *                           auch wenn noch alte Zeilen gespeichert sind
	 * @param mixed  $daten      Serialisierter Inhalt des Wizard-Feldes
	 * @param string $adresse    Spalte mit der E-Mail-Adresse
	 * @param string $inhaber    Spalte mit dem Besitzer; leer, wenn die Tabelle keine solche Spalte hat
	 *
	 * @return array Textzeilen, je Eintrag eine; Zeilen ohne Adresse (im Wizard bleibt oft eine
	 *               angefangene Zeile stehen) werden übergangen
	 */
	private static function eintraege($aktiv, $daten, string $adresse, string $inhaber): array
	{
		if (empty($aktiv)) {
			return array();
		}

		$liste = array();

		foreach (StringUtil::deserialize($daten, true) as $zeile) {
			$mail = trim((string) ($zeile[$adresse] ?? ''));

			if ('' === $mail) {
				continue;
			}

			$besitzer = '' !== $inhaber ? trim((string) ($zeile[$inhaber] ?? '')) : '';
			$liste[] = '' !== $besitzer ? $mail.' ('.$besitzer.')' : $mail;
		}

		return $liste;
	}
}
