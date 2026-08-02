<?php

declare(strict_types=1);

/**
 * Mailkonten für Contao Open Source CMS
 *
 * @author    Frank Hoppe
 * @license   LGPL-3.0-or-later
 */

namespace Schachbulle\ContaoMailkontenBundle\Classes;

/**
 * Schlanker Zugriff auf ein IMAP- oder POP3-Postfach.
 *
 * Die Klasse kapselt die Funktionen der PHP-Erweiterung imap. Gebraucht wird
 * sie vom stündlichen Cronjob (Cron\Kontenpruefung), der für jedes Konto die
 * Anzahl der E-Mails und die Speicherbelegung ermittelt.
 *
 * Wichtig zur Erweiterung selbst: ext-imap gehört seit PHP 8.4 nicht mehr zum
 * Standardumfang und muss dort über PECL nachinstalliert werden. Deshalb ist
 * sie in der composer.json nur empfohlen und nicht vorausgesetzt — ob sie
 * vorhanden ist, prüft `verfuegbar()` vor dem ersten Verbindungsaufbau.
 */
class IMAP
{
	/**
	 * Verbindungskennung von imap_open().
	 *
	 * Ab PHP 8.1 ist das ein Objekt der Klasse IMAP\Connection, davor eine
	 * Ressource. Deshalb steht hier keine Typangabe.
	 *
	 * @var resource|object|null
	 */
	private $stream;

	/**
	 * @var string Vollständige Mailbox-Angabe in geschweiften Klammern
	 */
	private $mailbox;

	/**
	 * Öffnet die Verbindung zum Postfach.
	 *
	 * Der Verbindungsaufbau erfolgt bewusst schon im Konstruktor: Ein Objekt
	 * dieser Klasse ist damit entweder benutzbar oder es existiert gar nicht.
	 * Die frühere Fassung merkte sich bei einem Fehlschlag ein `false` im
	 * Stream und lief dann bei jedem Aufruf in einen TypeError, weil die
	 * imap-Funktionen unter PHP 8 keine `false`-Kennung mehr annehmen.
	 *
	 * Die Verbindung läuft immer über SSL und mit `novalidate-cert`, weil bei
	 * Massen-Hostern regelmäßig Zertifikate stehen, die auf den Hostnamen des
	 * Providers und nicht auf den der Domain ausgestellt sind.
	 *
	 * @param string $server   Hostname des Mailservers, z. B. „sslin.de“
	 * @param int    $port     Port, üblicherweise 993 (IMAP) oder 995 (POP3)
	 * @param string $benutzer Kontoname, meist die vollständige E-Mail-Adresse
	 * @param string $passwort Passwort des Kontos im Klartext
	 * @param string $typ      „IMAP“ oder „POP3“; bestimmt das Protokoll in der
	 *                         Mailbox-Angabe
	 *
	 * @throws \RuntimeException Wenn ext-imap fehlt oder die Anmeldung
	 *                           scheitert (falsches Passwort, Server nicht
	 *                           erreichbar, Konto gelöscht). Die Meldung
	 *                           enthält den letzten IMAP-Fehler im Klartext
	 */
	public function __construct(string $server, int $port, string $benutzer, string $passwort, string $typ = 'IMAP')
	{
		if (!self::verfuegbar())
		{
			throw new \RuntimeException('Die PHP-Erweiterung "imap" steht nicht zur Verfügung.');
		}

		$this->mailbox = sprintf('{%s:%d/%s/ssl/novalidate-cert}INBOX', $server, $port, strtolower($typ));

		// Der Klammeraffe unterdrückt die Warnung von imap_open. Der Fehler
		// wird eine Zeile später sauber als Ausnahme weitergereicht
		$stream = @imap_open($this->mailbox, $benutzer, $passwort);

		if (false === $stream)
		{
			$fehler = imap_last_error() ?: 'unbekannter Fehler';

			// Ohne dieses Aufräumen meldet PHP die Fehler am Skriptende noch
			// einmal als „Unhandled IMAP errors“ auf der Konsole
			imap_errors();

			throw new \RuntimeException('Verbindung zu '.$server.' als '.$benutzer.' fehlgeschlagen: '.$fehler);
		}

		$this->stream = $stream;
	}

	/**
	 * Prüft, ob die PHP-Erweiterung imap geladen ist.
	 *
	 * @return bool true, wenn Postfächer abgefragt werden können
	 */
	public static function verfuegbar(): bool
	{
		return \function_exists('imap_open');
	}

	/**
	 * Gibt die Anzahl der E-Mails im Posteingang zurück.
	 *
	 * @return int Anzahl der Nachrichten; 0, wenn der Server keine Auskunft gibt
	 */
	public function getNumber(): int
	{
		if (null === $this->stream)
		{
			return 0;
		}

		$status = @imap_status($this->stream, $this->mailbox, SA_MESSAGES);

		return false === $status ? 0 : (int) $status->messages;
	}

	/**
	 * Ermittelt die Speicherbelegung des Postfachs.
	 *
	 * imap_get_quotaroot() liefert je nach Server und PHP-Fassung entweder
	 * direkt die Schlüssel usage und limit oder einen Unterbau STORAGE mit
	 * denselben Schlüsseln. Beide Formen werden hier auf ein einheitliches
	 * Ergebnis gebracht. Die Werte sind Kilobyte.
	 *
	 * @return array|null Array mit den Schlüsseln „usage“ und „limit“ in KB,
	 *                    oder null, wenn der Server keine Quota kennt (etwa bei
	 *                    unbegrenzten Postfächern) oder die Abfrage scheitert
	 */
	public function getQuota(): ?array
	{
		if (null === $this->stream)
		{
			return null;
		}

		$quota = @imap_get_quotaroot($this->stream, 'INBOX');

		if (!\is_array($quota))
		{
			return null;
		}

		if (isset($quota['STORAGE']) && \is_array($quota['STORAGE']))
		{
			$quota = $quota['STORAGE'];
		}

		if (!isset($quota['usage'], $quota['limit']))
		{
			return null;
		}

		return array
		(
			'usage' => (int) $quota['usage'],
			'limit' => (int) $quota['limit'],
		);
	}

	/**
	 * Liest die Kopfdaten aller E-Mails im Posteingang.
	 *
	 * @param bool $nurUngelesen true blendet bereits gelesene Nachrichten aus
	 *
	 * @return array Liste von Objekten, wie sie imap_fetch_overview() liefert;
	 *               leer, wenn das Postfach leer ist
	 */
	public function getEmails(bool $nurUngelesen = false): array
	{
		if (null === $this->stream)
		{
			return array();
		}

		$anzahl = $this->getNumber();

		if (0 === $anzahl)
		{
			return array();
		}

		$emails = @imap_fetch_overview($this->stream, '1:'.$anzahl, 0);

		if (!\is_array($emails))
		{
			return array();
		}

		if ($nurUngelesen)
		{
			$emails = array_filter($emails, static function ($email) { return empty($email->seen); });
		}

		return array_values($emails);
	}

	/**
	 * Gibt den Textkörper einer E-Mail zurück.
	 *
	 * Geliefert wird nur der erste Abschnitt der Nachricht. Bei mehrteiligen
	 * Nachrichten (Text und HTML) ist das üblicherweise die reine Textfassung.
	 *
	 * @param int $uid Laufende Nummer der Nachricht im Postfach, nicht die UID
	 *
	 * @return string Textkörper; leerer String, wenn die Nachricht nicht
	 *                gelesen werden kann
	 */
	public function getEmailBody(int $uid): string
	{
		if (null === $this->stream)
		{
			return '';
		}

		$body = @imap_fetchbody($this->stream, $uid, '1');

		if (false === $body || '' === $body)
		{
			return '';
		}

		return (string) imap_qprint($body);
	}

	/**
	 * Gibt den ersten Dateianhang einer E-Mail zurück.
	 *
	 * @param int $uid Laufende Nummer der Nachricht im Postfach
	 *
	 * @return string|null Inhalt des Anhangs, oder null wenn die Nachricht
	 *                     keinen Anhang hat
	 */
	public function getAttachments(int $uid): ?string
	{
		if (null === $this->stream)
		{
			return null;
		}

		$struktur = @imap_fetchstructure($this->stream, $uid);

		if (!\is_object($struktur) || !isset($struktur->parts) || !\is_array($struktur->parts))
		{
			return null;
		}

		foreach ($struktur->parts as $key => $teil)
		{
			if (isset($teil->disposition) && 'attachment' === strtolower((string) $teil->disposition))
			{
				$anhang = (string) imap_fetchbody($this->stream, $uid, (string) ($key + 1), FT_INTERNAL);

				// Weiche Zeilenumbrüche der Quoted-Printable-Kodierung entfernen
				return str_replace('=0A=', '', $anhang);
			}
		}

		return null;
	}

	/**
	 * Schließt die Verbindung zum Postfach.
	 *
	 * Mehrfaches Aufrufen ist unschädlich. Die Methode räumt zusätzlich die
	 * Fehlerliste der imap-Erweiterung ab, damit PHP am Skriptende keine
	 * gesammelten Warnungen ausgibt.
	 *
	 * @return void
	 */
	public function close(): void
	{
		if (null !== $this->stream)
		{
			@imap_close($this->stream);
			$this->stream = null;
		}

		if (self::verfuegbar())
		{
			imap_errors();
		}
	}

	/**
	 * Sorgt dafür, dass eine vergessene Verbindung dennoch geschlossen wird.
	 */
	public function __destruct()
	{
		$this->close();
	}
}
