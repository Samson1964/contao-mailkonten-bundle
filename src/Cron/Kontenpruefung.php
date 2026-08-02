<?php

declare(strict_types=1);

/**
 * Mailkonten für Contao Open Source CMS
 *
 * @author    Frank Hoppe
 * @license   LGPL-3.0-or-later
 */

namespace Schachbulle\ContaoMailkontenBundle\Cron;

use Contao\Config;
use Contao\CoreBundle\Monolog\ContaoContext;
use Contao\Database;
use Contao\Email;
use Contao\System;
use Psr\Log\LogLevel;
use Schachbulle\ContaoMailkontenBundle\Classes\IMAP;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email as MimeEmail;

/**
 * Stündlicher Cronjob: prüft die E-Mail-Konten und hält sie in Betrieb.
 *
 * Ersetzt das Skript Resources/public/email_check.php, das früher vom Hoster
 * als Cronjob über eine Adresse im Web aufgerufen wurde. Dieses Skript band
 * Contao über `system/initialize.php` ein — einen Weg, den es seit Contao 4.5
 * nicht mehr gibt; unter Contao 4.13 lief es also gar nicht mehr. Schlimmer
 * noch: Die Datei lag im öffentlichen Verzeichnis und war für jeden im Netz
 * aufrufbar, der die Adresse kannte. Ein Aufruf genügte, um sämtliche
 * Postfächer anzumelden und Mails zu verschicken.
 *
 * Der Cronjob erledigt zwei Dinge, jedes mit eigenem Abstand:
 *
 *   - **Checkup** (täglich je Konto): meldet sich am Postfach an und notiert
 *     Anzahl der E-Mails und Speicherbelegung. Danach geht eine Übersicht an
 *     den in den Einstellungen hinterlegten Administrator.
 *   - **Ping** (monatlich je Konto): verschickt eine Mail über den SMTP-Server
 *     des Kontos, damit der Anbieter das Konto nicht als ungenutzt löscht.
 *
 * Die Abstände sind nötig, weil Contao den Cronjob stündlich anstößt. Ohne sie
 * bekäme der Administrator vierundzwanzig Übersichten am Tag und jedes Konto
 * ebenso viele Ping-Mails. Wann ein Konto zuletzt an der Reihe war, steht in
 * den Feldern checkup_date und ping_date.
 *
 * Eingeschaltet wird der Cronjob unter System -> Einstellungen im Bereich
 * „Mailkonten-Verwaltung“.
 */
final class Kontenpruefung
{
	/**
	 * Mindestabstand zwischen zwei Checkups desselben Kontos in Sekunden.
	 *
	 * Etwas weniger als ein voller Tag, damit der Checkup nicht Tag für Tag
	 * später stattfindet und irgendwann eine Stunde ganz ausfällt.
	 */
	private const INTERVALL_CHECKUP = 82800;

	/**
	 * Mindestabstand zwischen zwei Ping-Mails desselben Kontos in Sekunden.
	 *
	 * Anbieter löschen ungenutzte Postfächer üblicherweise nach mehreren
	 * Monaten. Einmal im Monat ist damit reichlich Sicherheitsabstand.
	 */
	private const INTERVALL_PING = 2592000;

	/**
	 * Einstiegspunkt des Cronjobs.
	 *
	 * @return void Seiteneffekte: meldet sich an fremden Postfächern an,
	 *              verschickt E-Mails, schreibt die Zeitstempel checkup_date
	 *              und ping_date zurück und protokolliert Fehlschläge. Wirft
	 *              bewusst keine Ausnahme — ein nicht erreichbarer Mailserver
	 *              darf die übrigen Cronjobs nicht mitreißen
	 */
	public function __invoke(): void
	{
		if (!Config::get('mailkonten_cron'))
		{
			return;
		}

		if (!IMAP::verfuegbar())
		{
			self::protokolliere('Mailkonten: Der Checkup wurde übersprungen, weil die PHP-Erweiterung "imap" fehlt.');

			return;
		}

		$jetzt = time();

		$objKonten = Database::getInstance()
			->prepare("SELECT * FROM tl_mailkonten WHERE published='1' AND checkup='1' ORDER BY email ASC")
			->execute();

		$ergebnisse = array();

		while ($objKonten->next())
		{
			$konto = $objKonten->row();

			if ($jetzt - (int) $konto['checkup_date'] < self::INTERVALL_CHECKUP)
			{
				continue;
			}

			$ergebnis = $this->pruefe($konto);

			if (null !== $ergebnis)
			{
				$ergebnisse[] = $ergebnis;
			}

			// Der Zeitstempel wird auch nach einem Fehlschlag gesetzt. Sonst
			// liefe der Cronjob Stunde für Stunde in dieselbe Zeitüberschreitung
			Database::getInstance()
				->prepare('UPDATE tl_mailkonten SET checkup_date=? WHERE id=?')
				->execute($jetzt, $konto['id']);

			if ($jetzt - (int) $konto['ping_date'] >= self::INTERVALL_PING)
			{
				$this->ping($konto);

				Database::getInstance()
					->prepare('UPDATE tl_mailkonten SET ping_date=? WHERE id=?')
					->execute($jetzt, $konto['id']);
			}
		}

		if ($ergebnisse)
		{
			$this->versendeUebersicht($ergebnisse);
		}
	}

	/**
	 * Meldet sich an einem Postfach an und liest dessen Kennzahlen.
	 *
	 * @param array $konto Datensatz aus tl_mailkonten
	 *
	 * @return array|null Zeile für die Übersicht mit den Schlüsseln email,
	 *                    count, usage, quota und percent, oder null wenn das
	 *                    Postfach nicht erreichbar war. Der Fehler steht dann
	 *                    im Systemprotokoll
	 */
	private function pruefe(array $konto): ?array
	{
		try
		{
			$postfach = new IMAP(
				(string) $konto['imap_server'],
				(int) $konto['imap_port'],
				(string) $konto['email'],
				(string) $konto['passwort']
			);
		}
		catch (\Throwable $e)
		{
			self::protokolliere('Mailkonten: Checkup für '.$konto['email'].' fehlgeschlagen: '.$e->getMessage());

			return null;
		}

		$anzahl = $postfach->getNumber();
		$quota = $postfach->getQuota();
		$postfach->close();

		return self::kennzahlen((string) $konto['email'], $anzahl, $quota);
	}

	/**
	 * Rechnet die Rohwerte des Postfachs in die Zeilen der Übersicht um.
	 *
	 * Bewusst ohne Postfach- und Datenbankzugriff, damit die Umrechnung für
	 * sich getestet werden kann.
	 *
	 * @param string     $email  E-Mail-Adresse des Kontos
	 * @param int        $anzahl Anzahl der Nachrichten im Posteingang
	 * @param array|null $quota  Speicherbelegung in KB mit den Schlüsseln usage
	 *                           und limit, oder null wenn der Server keine
	 *                           Quota kennt
	 *
	 * @return array Zeile der Übersicht. Ohne Quota stehen in usage, quota und
	 *               percent jeweils null — die Übersicht zeigt dort „-“ statt
	 *               einer erfundenen Null. Ein limit von 0 (unbegrenzt) führt
	 *               ebenfalls nicht zur Division durch null
	 */
	public static function kennzahlen(string $email, int $anzahl, ?array $quota): array
	{
		$zeile = array
		(
			'email'   => $email,
			'count'   => $anzahl,
			'usage'   => null,
			'quota'   => null,
			'percent' => null,
		);

		if (null === $quota)
		{
			return $zeile;
		}

		// Die imap-Erweiterung liefert Kilobyte, angezeigt werden Megabyte
		$zeile['usage'] = round($quota['usage'] / 1024, 2);
		$zeile['quota'] = round($quota['limit'] / 1024, 2);

		if ($quota['limit'] > 0)
		{
			$zeile['percent'] = round($quota['usage'] / $quota['limit'] * 100, 2);
		}

		return $zeile;
	}

	/**
	 * Baut die HTML-Tabelle der Übersichtsmail.
	 *
	 * Die Formatangaben stehen an den Zellen selbst und nicht in einem
	 * Stilbereich im Kopf, weil viele E-Mail-Programme diesen verwerfen.
	 *
	 * @param array $zeilen Ergebniszeilen aus kennzahlen()
	 *
	 * @return string Fertige HTML-Tabelle. Konten über 90 Prozent Belegung
	 *                stehen rot und fett
	 */
	public static function tabelle(array $zeilen): string
	{
		$html = '<table border="1" cellspacing="0" cellpadding="4">'
			.'<tr><th>Konto</th><th>E-Mails</th><th>MB belegt</th><th>MB Limit</th><th>% belegt</th></tr>';

		foreach ($zeilen as $zeile)
		{
			$warnung = null !== $zeile['percent'] && $zeile['percent'] > 90;

			$html .= '<tr>'
				.'<td>'.htmlspecialchars((string) $zeile['email'], ENT_QUOTES).'</td>'
				.'<td>'.(int) $zeile['count'].'</td>'
				.'<td>'.self::zahl($zeile['usage']).'</td>'
				.'<td>'.self::zahl($zeile['quota']).'</td>'
				.'<td'.($warnung ? ' style="color:red; font-weight:bold;"' : '').'>'.self::zahl($zeile['percent']).'</td>'
				.'</tr>';
		}

		return $html.'</table>';
	}

	/**
	 * Stellt eine Zahl der Übersicht dar.
	 *
	 * @param float|null $wert Umgerechneter Wert oder null, wenn der Server
	 *                         keine Auskunft gegeben hat
	 *
	 * @return string Zahl mit zwei Nachkommastellen, oder „-“ bei null
	 */
	private static function zahl(?float $wert): string
	{
		return null === $wert ? '-' : number_format($wert, 2, ',', '.');
	}

	/**
	 * Verschickt die Übersicht an den Administrator.
	 *
	 * Ist unter System -> Einstellungen keine Administratoradresse hinterlegt,
	 * passiert nichts — die Kennzahlen stehen dann nur im Protokoll der
	 * einzelnen Fehlschläge.
	 *
	 * @param array $zeilen Ergebniszeilen aus kennzahlen(), nie leer
	 *
	 * @return void
	 */
	private function versendeUebersicht(array $zeilen): void
	{
		$empfaenger = trim((string) Config::get('mailkonten_admin'));

		if ('' === $empfaenger)
		{
			return;
		}

		$mail = new Email();
		$mail->from = (string) Config::get('adminEmail');
		$mail->fromName = (string) (Config::get('mailkonten_absender') ?: 'Mailkonten');
		$mail->subject = (string) (Config::get('mailkonten_betreff') ?: 'Mailkonten-Checkup');
		$mail->html = self::tabelle($zeilen);

		try
		{
			$mail->sendTo($empfaenger);
		}
		catch (\Throwable $e)
		{
			self::protokolliere('Mailkonten: Die Übersicht konnte nicht an '.$empfaenger.' verschickt werden: '.$e->getMessage());
		}
	}

	/**
	 * Verschickt eine Ping-Mail über den SMTP-Server des Kontos.
	 *
	 * Der Sinn liegt allein in der Anmeldung: Wer sich regelmäßig am
	 * Postausgangsserver anmeldet, gilt dem Anbieter als aktiv. Absender und
	 * Empfänger ist deshalb das Konto selbst — die Mail landet im eigenen
	 * Posteingang und geht niemanden sonst etwas an. Früher standen hier fest
	 * verdrahtet Adresse und Name des Schachbund-Webmasters, was in einer
	 * wiederverwendbaren Erweiterung nichts zu suchen hat.
	 *
	 * Verschickt wird über Symfony Mailer, den Contao ohnehin mitbringt. Die
	 * frühere Fassung setzte dafür PHPMailer voraus; die Abhängigkeit ist damit
	 * entfallen.
	 *
	 * @param array $konto Datensatz aus tl_mailkonten
	 *
	 * @return void Fehler werden protokolliert und nicht geworfen
	 */
	private function ping(array $konto): void
	{
		$server = trim((string) $konto['smtp_server']);
		$port = (int) $konto['smtp_port'];

		if ('' === $server || $port <= 0)
		{
			return;
		}

		// Port 465 spricht von Beginn an verschlüsselt (smtps), alle anderen
		// handeln die Verschlüsselung über STARTTLS aus
		$dsn = sprintf(
			'%s://%s:%s@%s:%d',
			465 === $port ? 'smtps' : 'smtp',
			rawurlencode((string) $konto['email']),
			rawurlencode((string) $konto['passwort']),
			$server,
			$port
		);

		$nachricht = (new MimeEmail())
			->from((string) $konto['email'])
			->to((string) $konto['email'])
			->subject('Ping-Mail')
			->text('Automatische Ping-Mail der Mailkonten-Verwaltung, um eine Kontolöschung durch den Anbieter zu verhindern.');

		try
		{
			$mailer = new Mailer(Transport::fromDsn($dsn));
			$mailer->send($nachricht);
		}
		catch (\Throwable $e)
		{
			self::protokolliere('Mailkonten: Ping-Mail für '.$konto['email'].' fehlgeschlagen: '.$e->getMessage());
		}
	}

	/**
	 * Schreibt eine Meldung ins Contao-Systemprotokoll.
	 *
	 * @param string $meldung Klartext der Meldung
	 *
	 * @return void
	 */
	private static function protokolliere(string $meldung): void
	{
		System::getContainer()->get('monolog.logger.contao.error')->log(
			LogLevel::ERROR,
			$meldung,
			array('contao' => new ContaoContext(__METHOD__, ContaoContext::ERROR))
		);
	}
}
