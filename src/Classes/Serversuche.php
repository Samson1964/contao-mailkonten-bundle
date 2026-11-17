<?php

declare(strict_types=1);

/**
 * Mailkonten für Contao Open Source CMS
 *
 * @author    Frank Hoppe
 * @license   LGPL-3.0-or-later
 */

namespace Schachbulle\ContaoMailkontenBundle\Classes;

use Symfony\Component\HttpClient\NoPrivateNetworkHttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Ermittelt Server und Ports eines E-Mail-Kontos über die Autokonfiguration.
 *
 * Mailprogramme wie Thunderbird finden die Servereinstellungen zu einer
 * Adresse selbst: Sie fragen eine kleine XML-Datei ab, die der Mailanbieter
 * bereithält (Format „config-v1.1“). Dieselbe Abfrage macht der Knopf „Server
 * abrufen“ im Formular, damit die Servernamen und Ports nicht von Hand
 * nachgeschlagen und abgetippt werden müssen.
 *
 * Es werden der Reihe nach drei Quellen befragt:
 *   1. der Autokonfigurations-Server der Domain selbst,
 *   2. dessen Standardpfad unter /.well-known/ auf der Domain,
 *   3. die öffentliche ISP-Datenbank von Thunderbird. Sie kennt die großen
 *      Anbieter und wird zusätzlich mit der Domain des Mailservers (MX) der
 *      Adresse befragt, denn eine eigene Domain, deren Post bei einem
 *      Massen-Hoster liegt, taucht dort selbst nicht auf.
 *
 * An die Server geht ausschließlich der Domainname, niemals der Teil der
 * Adresse vor dem @-Zeichen.
 */
class Serversuche
{
	/**
	 * Wartezeit je Abfrage in Sekunden.
	 *
	 * Die Abfrage läuft, während jemand im Backend auf den Knopf geklickt hat.
	 * Drei Quellen mit je einer langen Wartezeit würden das Formular einfrieren.
	 */
	private const ZEITLIMIT = 4;

	/**
	 * Adresse der ISP-Datenbank von Thunderbird; die Domain wird angehängt.
	 */
	private const ISPDB = 'https://autoconfig.thunderbird.net/v1.1/';

	/**
	 * HTTP-Client für die Abfragen.
	 *
	 * @var HttpClientInterface
	 */
	private $client;

	/**
	 * Funktion zur Abfrage der Mailserver (MX) einer Domain.
	 *
	 * @var callable
	 */
	private $mxAufloeser;

	/**
	 * Konstruktor.
	 *
	 * @param HttpClientInterface $client      HTTP-Client für die Abfragen
	 * @param callable|null       $mxAufloeser Liefert zu einer Domain die Liste ihrer Mailserver-Hostnamen,
	 *                                         nach Priorität sortiert. Ohne Angabe wird per DNS gefragt; die
	 *                                         Tests reichen hier eine feste Antwort herein
	 */
	public function __construct(HttpClientInterface $client, ?callable $mxAufloeser = null)
	{
		$this->client = $client;
		$this->mxAufloeser = $mxAufloeser ?? static function (string $domain): array
		{
			$hosts = array();
			$gewichte = array();

			if (!@getmxrr($domain, $hosts, $gewichte)) {
				return array();
			}

			array_multisort($gewichte, $hosts);

			return $hosts;
		};
	}

	/**
	 * Erzeugt die Serversuche mit einem gegen interne Adressen abgesicherten Client.
	 *
	 * Die Domain stammt aus einer im Backend eingetippten Adresse. Ohne
	 * Absicherung ließe sich der Server damit dazu bringen, Adressen im eigenen
	 * Netz abzufragen (SSRF). Der Dekorator verweigert Anfragen, die bei
	 * privaten oder internen IP-Adressen landen.
	 *
	 * Als Fabrik für die services.yml gedacht; die Tests erzeugen die Klasse
	 * direkt mit einem MockHttpClient.
	 *
	 * @param HttpClientInterface $client Der Standard-HTTP-Client von Contao
	 *
	 * @return self
	 */
	public static function mitSchutz(HttpClientInterface $client): self
	{
		return new self(new NoPrivateNetworkHttpClient($client));
	}

	/**
	 * Ermittelt Server und Ports zu einer E-Mail-Adresse.
	 *
	 * @param string $email Eingetippte E-Mail-Adresse; nur die Domain wird ausgewertet
	 *
	 * @return array Immer mit den Schlüsseln „ok“ (bool) und „meldung“ (Klartext für den Benutzer).
	 *               Bei Erfolg zusätzlich „werte“: ein Array, dessen Schlüssel den DCA-Feldern
	 *               entsprechen (smtp_server, smtp_port, imap_server, imap_port, pop3_server,
	 *               pop3_port) — nur die Felder, die die Quelle kennt
	 */
	public function ermittle(string $email): array
	{
		$domain = self::domain($email);

		if (null === $domain) {
			return array('ok' => false, 'meldung' => 'Bitte zuerst eine gültige E-Mail-Adresse eintragen.');
		}

		foreach ($this->kandidaten($domain) as $quelle => $url) {
			$xml = $this->lade($url);

			if (null === $xml) {
				continue;
			}

			$ergebnis = self::auswerten($xml, $email);

			if (null !== $ergebnis) {
				return array(
					'ok'      => true,
					'werte'   => $ergebnis['werte'],
					'meldung' => 'Server übernommen (Quelle: '.$quelle.').'.($ergebnis['hinweise'] ? ' '.implode(' ', $ergebnis['hinweise']) : ''),
				);
			}
		}

		return array('ok' => false, 'meldung' => 'Für '.$domain.' sind keine Servereinstellungen abrufbar. Bitte von Hand eintragen.');
	}

	/**
	 * Zieht die Domain aus einer E-Mail-Adresse.
	 *
	 * Internationale Domainnamen werden in ihre ASCII-Form umgewandelt, damit
	 * sie als Teil einer Internetadresse taugen. Die Prüfung ist bewusst
	 * streng: Die Domain landet später in einer URL.
	 *
	 * @param string $email Eingetippte Adresse
	 *
	 * @return string|null Kleingeschriebene Domain, oder null wenn die Adresse unbrauchbar ist
	 */
	public static function domain(string $email): ?string
	{
		$email = trim($email);
		$position = strrpos($email, '@');

		if (false === $position || $position < 1) {
			return null;
		}

		$domain = strtolower(substr($email, $position + 1));

		if (\function_exists('idn_to_ascii')) {
			$ascii = idn_to_ascii($domain);
			$domain = false === $ascii ? $domain : $ascii;
		}

		if (!preg_match('/^(?=.{4,253}$)([a-z0-9]([a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z]{2,63}$/', $domain)) {
			return null;
		}

		return $domain;
	}

	/**
	 * Stellt die Adressen zusammen, unter denen die Konfiguration liegen kann.
	 *
	 * Die Liste ist geordnet: Was der Mailanbieter der Domain selbst
	 * bereithält, hat Vorrang vor der allgemeinen Datenbank.
	 *
	 * @param string $domain Geprüfte Domain, siehe domain()
	 *
	 * @return array Zuordnung Quellenbezeichnung => Adresse. Die Bezeichnung erscheint in der
	 *               Rückmeldung an den Benutzer
	 */
	public function kandidaten(string $domain): array
	{
		$liste = array(
			'Autokonfiguration der Domain' => 'https://autoconfig.'.$domain.'/mail/config-v1.1.xml',
			'well-known-Pfad der Domain'   => 'https://'.$domain.'/.well-known/autoconfig/mail/config-v1.1.xml',
			'Thunderbird-ISP-Datenbank'    => self::ISPDB.$domain,
		);

		// Eine eigene Domain mit Postfächern bei einem großen Hoster hat selbst
		// keinen Eintrag; der Mailserver verrät aber, wer der Hoster ist
		$bereits = array($domain => true);

		foreach (\array_slice(($this->mxAufloeser)($domain), 0, 2) as $host) {
			$basis = self::basisDomain((string) $host);

			if (null === $basis || isset($bereits[$basis])) {
				continue;
			}

			$bereits[$basis] = true;
			$liste['Thunderbird-ISP-Datenbank (Mailserver '.$basis.')'] = self::ISPDB.$basis;
		}

		return $liste;
	}

	/**
	 * Kürzt einen Hostnamen auf die letzten beiden Namensteile.
	 *
	 * Aus „mx01.mail.example.org“ wird „example.org“. Das ist eine Näherung:
	 * Bei Endungen wie „co.uk“ trifft sie daneben, die Abfrage läuft dann
	 * ergebnislos ins Leere, ohne Schaden anzurichten.
	 *
	 * @param string $host Hostname, gegebenenfalls mit Punkt am Ende
	 *
	 * @return string|null Die Basisdomain, oder null wenn der Hostname unbrauchbar ist
	 */
	public static function basisDomain(string $host): ?string
	{
		$teile = explode('.', strtolower(trim($host, ". \t")));

		if (\count($teile) < 2) {
			return null;
		}

		$basis = implode('.', \array_slice($teile, -2));

		return preg_match('/^[a-z0-9-]+\.[a-z]{2,63}$/', $basis) ? $basis : null;
	}

	/**
	 * Lädt eine Konfigurationsdatei.
	 *
	 * Jeder Fehler — Zeitüberschreitung, unbekannter Host, Statuscode ungleich
	 * 200, abgewiesene interne Adresse — heißt nur „diese Quelle gibt nichts
	 * her“ und lässt die Suche bei der nächsten weitermachen.
	 *
	 * @param string $url Adresse der Datei
	 *
	 * @return string|null Der Inhalt, oder null wenn nichts Brauchbares kam
	 */
	private function lade(string $url): ?string
	{
		// Der Schutz-Dekorator löst Hostnamen per dns_get_record() auf; für einen
		// Host ohne Eintrag (autoconfig.<domain> fehlt meist) gibt PHP dabei eine
		// Warnung aus. Sie würde im Debug-Modus die JSON-Antwort zerschießen,
		// ist hier aber nur das erwartete „diese Quelle gibt es nicht“
		set_error_handler(static function (): bool { return true; }, E_WARNING);

		try {
			$antwort = $this->client->request('GET', $url, array(
				'timeout'       => self::ZEITLIMIT,
				'max_duration'  => self::ZEITLIMIT * 2,
				'max_redirects' => 3,
				'headers'       => array('Accept' => 'application/xml, text/xml'),
			));

			if (200 !== $antwort->getStatusCode()) {
				return null;
			}

			$inhalt = $antwort->getContent();
		} catch (\Throwable $e) {
			return null;
		} finally {
			restore_error_handler();
		}

		return '' === trim($inhalt) ? null : $inhalt;
	}

	/**
	 * Liest eine Autokonfigurations-Datei aus und wählt je Dienst die beste Einstellung.
	 *
	 * Die Datei darf mehrere Server je Dienst nennen (etwa IMAP mit SSL und mit
	 * STARTTLS). Gewählt wird die verschlüsselte Variante von Anfang an (SSL),
	 * dann STARTTLS, zuletzt unverschlüsselt — der Kontocheck dieses Bundles
	 * spricht nur SSL, STARTTLS wird deshalb gemeldet.
	 *
	 * Bewusst ohne Netz- und Datenbankzugriff, damit sie für sich getestet werden kann.
	 *
	 * @param string $xml   Inhalt der Datei
	 * @param string $email Adresse, für die gefragt wurde; füllt die Platzhalter %EMAILADDRESS%,
	 *                      %EMAILLOCALPART% und %EMAILDOMAIN% im Servernamen
	 *
	 * @return array|null Array mit „werte“ (Schlüssel wie die DCA-Felder) und „hinweise“ (Liste von
	 *                    Klartextsätzen), oder null wenn die Datei kaputt ist oder keinen Server nennt
	 */
	public static function auswerten(string $xml, string $email): ?array
	{
		$vorher = libxml_use_internal_errors(true);
		$dokument = new \DOMDocument();
		// LIBXML_NONET: keine fremden Adressen über Entitäten nachladen
		$geladen = $dokument->loadXML($xml, LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING);
		libxml_clear_errors();
		libxml_use_internal_errors($vorher);

		if (!$geladen) {
			return null;
		}

		[$lokal, $domain] = array_pad(explode('@', $email, 2), 2, '');
		$ersatz = array(
			'%EMAILADDRESS%'   => $email,
			'%EMAILLOCALPART%' => $lokal,
			'%EMAILDOMAIN%'    => $domain,
		);

		// Rang der Verschlüsselung: kleiner ist besser
		$rang = array('SSL' => 0, 'STARTTLS' => 1, 'plain' => 2);
		$beste = array();

		foreach (array('incomingServer', 'outgoingServer') as $element) {
			foreach ($dokument->getElementsByTagName($element) as $server) {
				$typ = strtolower((string) $server->getAttribute('type'));
				$host = trim(strtr(self::text($server, 'hostname'), $ersatz));
				$port = (int) self::text($server, 'port');
				$sicherheit = self::text($server, 'socketType');
				$sicherheit = isset($rang[$sicherheit]) ? $sicherheit : 'plain';

				if (!\in_array($typ, array('imap', 'pop3', 'smtp'), true) || '' === $host || $port < 1 || $port > 65535) {
					continue;
				}

				if (!preg_match('/^[A-Za-z0-9.-]{1,253}$/', $host)) {
					continue;
				}

				if (!isset($beste[$typ]) || $rang[$sicherheit] < $rang[$beste[$typ]['sicherheit']]) {
					$beste[$typ] = array('host' => $host, 'port' => $port, 'sicherheit' => $sicherheit);
				}
			}
		}

		if (!$beste) {
			return null;
		}

		$werte = array();
		$hinweise = array();

		foreach ($beste as $typ => $eintrag) {
			$werte[$typ.'_server'] = $eintrag['host'];
			$werte[$typ.'_port'] = $eintrag['port'];

			if ('SSL' !== $eintrag['sicherheit'] && 'smtp' !== $typ) {
				$hinweise[] = 'Achtung: '.strtoupper($typ).' bietet keine SSL-Verbindung von Anfang an ('.('plain' === $eintrag['sicherheit'] ? 'unverschlüsselt' : 'STARTTLS').'); der Kontocheck dieses Bundes spricht nur SSL.';
			}
		}

		return array('werte' => $werte, 'hinweise' => $hinweise);
	}

	/**
	 * Liest den Text eines Kindelements.
	 *
	 * @param \DOMElement $eltern Das Element, in dem gesucht wird
	 * @param string      $name   Name des Kindelements, etwa „hostname“
	 *
	 * @return string Der Text ohne Randleerzeichen; leer, wenn es das Element nicht gibt
	 */
	private static function text(\DOMElement $eltern, string $name): string
	{
		$knoten = $eltern->getElementsByTagName($name)->item(0);

		return null === $knoten ? '' : trim($knoten->textContent);
	}
}
