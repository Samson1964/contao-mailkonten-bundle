<?php

declare(strict_types=1);

/**
 * Mailkonten für Contao Open Source CMS
 *
 * @author    Frank Hoppe
 * @license   LGPL-3.0-or-later
 */

namespace Schachbulle\ContaoMailkontenBundle\Tests\Classes;

use PHPUnit\Framework\TestCase;
use Schachbulle\ContaoMailkontenBundle\Classes\Serversuche;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

/**
 * Prüft die Ermittlung von Servern und Ports aus der Autokonfiguration.
 *
 * Es wird kein Netz benutzt: Die Antworten kommen von einem MockHttpClient,
 * die Mailserver-Abfrage von einer festen Funktion.
 */
class ServersucheTest extends TestCase
{
	/**
	 * Skipt die Tests, wenn der HTTP-Client nicht installiert ist (Lauf ohne Composer-Autoloader).
	 *
	 * @return void
	 */
	protected function setUp(): void
	{
		if (!class_exists(MockHttpClient::class)) {
			$this->markTestSkipped('symfony/http-client steht nicht zur Verfügung (kein Composer-Autoloader).');
		}
	}

	/**
	 * Beispiel-Datei im Format config-v1.1 mit mehreren Varianten je Dienst.
	 *
	 * @return string
	 */
	private static function xml(): string
	{
		return '<?xml version="1.0"?>
<clientConfig version="1.1"><emailProvider id="example.org">
<incomingServer type="imap"><hostname>imap-starttls.%EMAILDOMAIN%</hostname><port>143</port><socketType>STARTTLS</socketType></incomingServer>
<incomingServer type="imap"><hostname>imap.%EMAILDOMAIN%</hostname><port>993</port><socketType>SSL</socketType></incomingServer>
<incomingServer type="pop3"><hostname>pop.example.org</hostname><port>995</port><socketType>SSL</socketType></incomingServer>
<outgoingServer type="smtp"><hostname>smtp.example.org</hostname><port>587</port><socketType>STARTTLS</socketType></outgoingServer>
<outgoingServer type="smtp"><hostname>smtp.example.org</hostname><port>465</port><socketType>SSL</socketType></outgoingServer>
</emailProvider></clientConfig>';
	}

	/**
	 * Je Dienst wird die SSL-Variante gewählt, Platzhalter werden ersetzt.
	 *
	 * @return void
	 */
	public function testAuswertenWaehltSsl(): void
	{
		$ergebnis = Serversuche::auswerten(self::xml(), 'a@example.org');

		$this->assertSame(array(
			'imap_server' => 'imap.example.org',
			'imap_port'   => 993,
			'pop3_server' => 'pop.example.org',
			'pop3_port'   => 995,
			'smtp_server' => 'smtp.example.org',
			'smtp_port'   => 465,
		), $ergebnis['werte']);
		$this->assertSame(array(), $ergebnis['hinweise']);
	}

	/**
	 * Gibt es nur STARTTLS, wird es gewählt und der Benutzer gewarnt.
	 *
	 * @return void
	 */
	public function testAuswertenWarntBeiStarttls(): void
	{
		$xml = '<clientConfig><emailProvider><incomingServer type="imap"><hostname>imap.example.org</hostname><port>143</port><socketType>STARTTLS</socketType></incomingServer></emailProvider></clientConfig>';

		$ergebnis = Serversuche::auswerten($xml, 'a@example.org');

		$this->assertSame(143, $ergebnis['werte']['imap_port']);
		$this->assertCount(1, $ergebnis['hinweise']);
		$this->assertStringContainsString('STARTTLS', $ergebnis['hinweise'][0]);
	}

	/**
	 * Kaputtes XML und Dateien ohne Server ergeben null statt eines Fehlers.
	 *
	 * @return void
	 */
	public function testAuswertenOhneBrauchbaresErgebnis(): void
	{
		$this->assertNull(Serversuche::auswerten('<html><body>404', 'a@example.org'));
		$this->assertNull(Serversuche::auswerten('<clientConfig></clientConfig>', 'a@example.org'));
	}

	/**
	 * Unbrauchbare Servernamen und Ports werden verworfen.
	 *
	 * Die Werte landen in Formularfeldern und später in Verbindungsaufbauten;
	 * ein Servername mit Leerzeichen oder Sonderzeichen darf nicht durchkommen.
	 *
	 * @return void
	 */
	public function testAuswertenVerwirftUnbrauchbares(): void
	{
		$xml = '<clientConfig><emailProvider>
<incomingServer type="imap"><hostname>evil host;rm</hostname><port>993</port><socketType>SSL</socketType></incomingServer>
<incomingServer type="pop3"><hostname>pop.example.org</hostname><port>99999</port><socketType>SSL</socketType></incomingServer>
</emailProvider></clientConfig>';

		$this->assertNull(Serversuche::auswerten($xml, 'a@example.org'));
	}

	/**
	 * Externe Entitäten im XML werden nicht nachgeladen.
	 *
	 * @return void
	 */
	public function testAuswertenLaedtKeineEntitaeten(): void
	{
		$xml = '<?xml version="1.0"?><!DOCTYPE x [<!ENTITY e SYSTEM "file:///etc/hostname">]><clientConfig><emailProvider><incomingServer type="imap"><hostname>&e;</hostname><port>993</port><socketType>SSL</socketType></incomingServer></emailProvider></clientConfig>';

		$this->assertNull(Serversuche::auswerten($xml, 'a@example.org'));
	}

	/**
	 * Die Domain wird geprüft, kleingeschrieben und aus Adressen mit mehreren @ richtig gezogen.
	 *
	 * @return void
	 */
	public function testDomain(): void
	{
		$this->assertSame('example.org', Serversuche::domain(' Frank@Example.ORG '));
		$this->assertSame('example.org', Serversuche::domain('"a@b"@example.org'));
		$this->assertNull(Serversuche::domain('keine-adresse'));
		$this->assertNull(Serversuche::domain('a@localhost'));
		$this->assertNull(Serversuche::domain('a@127.0.0.1'));
		$this->assertNull(Serversuche::domain('a@exa mple.org'));
		$this->assertNull(Serversuche::domain('@example.org'));
	}

	/**
	 * Die Basisdomain eines Mailservers wird auf zwei Namensteile gekürzt.
	 *
	 * @return void
	 */
	public function testBasisDomain(): void
	{
		$this->assertSame('example.org', Serversuche::basisDomain('mx01.mail.Example.org.'));
		$this->assertNull(Serversuche::basisDomain('localhost'));
		$this->assertNull(Serversuche::basisDomain(''));
	}

	/**
	 * Die Kandidatenliste ist geordnet und nimmt die Basisdomain des Mailservers auf.
	 *
	 * @return void
	 */
	public function testKandidaten(): void
	{
		$suche = new Serversuche(new MockHttpClient(), static function (): array { return array('mx.hoster.net', 'mx2.hoster.net'); });

		$this->assertSame(array(
			'Autokonfiguration der Domain'                      => 'https://autoconfig.example.org/mail/config-v1.1.xml',
			'well-known-Pfad der Domain'                        => 'https://example.org/.well-known/autoconfig/mail/config-v1.1.xml',
			'Thunderbird-ISP-Datenbank'                         => 'https://autoconfig.thunderbird.net/v1.1/example.org',
			'Thunderbird-ISP-Datenbank (Mailserver hoster.net)' => 'https://autoconfig.thunderbird.net/v1.1/hoster.net',
		), $suche->kandidaten('example.org'));
	}

	/**
	 * Die erste Quelle mit brauchbarem Ergebnis gewinnt; Fehlschläge davor werden übergangen.
	 *
	 * @return void
	 */
	public function testErmittleUebergehtFehlschlaege(): void
	{
		$abgefragt = array();
		$client = new MockHttpClient(function (string $methode, string $url) use (&$abgefragt) {
			$abgefragt[] = $url;

			if (false !== strpos($url, 'autoconfig.example.org')) {
				throw new \Symfony\Component\HttpClient\Exception\TransportException('Host unbekannt');
			}

			if (false !== strpos($url, '.well-known')) {
				return new MockResponse('nicht da', array('http_code' => 404));
			}

			return new MockResponse(self::xml());
		});

		$suche = new Serversuche($client, static function (): array { return array(); });
		$ergebnis = $suche->ermittle('a@example.org');

		$this->assertTrue($ergebnis['ok']);
		$this->assertSame('imap.example.org', $ergebnis['werte']['imap_server']);
		$this->assertStringContainsString('Thunderbird-ISP-Datenbank', $ergebnis['meldung']);
		$this->assertCount(3, $abgefragt);

		// Der Teil vor dem @ darf nirgends hingehen
		foreach ($abgefragt as $url) {
			$this->assertStringNotContainsString('a@', $url);
			$this->assertStringNotContainsString('emailaddress', $url);
		}
	}

	/**
	 * Ohne jede Quelle kommt eine verständliche Meldung statt eines Fehlers.
	 *
	 * @return void
	 */
	public function testErmittleOhneQuelle(): void
	{
		$suche = new Serversuche(
			new MockHttpClient(static function () { return new MockResponse('', array('http_code' => 404)); }),
			static function (): array { return array(); }
		);

		$ergebnis = $suche->ermittle('a@example.org');

		$this->assertFalse($ergebnis['ok']);
		$this->assertArrayNotHasKey('werte', $ergebnis);
		$this->assertStringContainsString('example.org', $ergebnis['meldung']);
	}

	/**
	 * Eine unbrauchbare Adresse wird abgewiesen, ohne dass eine Abfrage hinausgeht.
	 *
	 * @return void
	 */
	public function testErmittleUnbrauchbareAdresse(): void
	{
		$aufrufe = 0;
		$suche = new Serversuche(
			new MockHttpClient(static function () use (&$aufrufe) { ++$aufrufe; return new MockResponse(''); }),
			static function (): array { return array(); }
		);

		$ergebnis = $suche->ermittle('kein-at-zeichen');

		$this->assertFalse($ergebnis['ok']);
		$this->assertSame(0, $aufrufe);
	}
}
