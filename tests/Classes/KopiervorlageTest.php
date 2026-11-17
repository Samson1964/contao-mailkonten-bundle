<?php

declare(strict_types=1);

/**
 * Mailkonten für Contao Open Source CMS
 *
 * @author    Frank Hoppe
 * @license   LGPL-3.0-or-later
 */

namespace Schachbulle\ContaoMailkontenBundle\Tests\Classes;

use Contao\StringUtil;
use PHPUnit\Framework\TestCase;
use Schachbulle\ContaoMailkontenBundle\Classes\Kopiervorlage;

/**
 * Prüft den Aufbau der Kopiervorlage mit den Zugangsdaten.
 */
class KopiervorlageTest extends TestCase
{
	/**
	 * Überspringt die Tests, wenn Contao nicht erreichbar ist.
	 *
	 * Die Vorlage löst die Wizard-Felder mit StringUtil::deserialize() auf.
	 *
	 * @return void
	 */
	protected function setUp(): void
	{
		if (!class_exists(StringUtil::class)) {
			$this->markTestSkipped('Contao steht nicht zur Verfügung (kein Composer-Autoloader).');
		}
	}

	/**
	 * Ein leerer Datensatz ergibt keinen Text.
	 *
	 * @return void
	 */
	public function testLeererDatensatz(): void
	{
		$this->assertSame('', Kopiervorlage::text(array()));
	}

	/**
	 * Ohne POP3/IMAP stehen keine Zugangsdaten in der Vorlage.
	 *
	 * Das Passwort-Feld kann auch bei abgeschaltetem POP3 noch Altdaten
	 * enthalten; es darf dann nicht herausgegeben werden.
	 *
	 * @return void
	 */
	public function testOhnePop3KeineZugangsdaten(): void
	{
		$text = Kopiervorlage::text(array('email' => 'a@example.org', 'pop3' => '', 'passwort' => 'geheim'));

		$this->assertSame('E-Mail-Konto: a@example.org', $text);
		$this->assertStringNotContainsString('geheim', $text);
	}

	/**
	 * Mit POP3/IMAP erscheinen Benutzer, Passwort, Server und Ports.
	 *
	 * @return void
	 */
	public function testZugangsdaten(): void
	{
		$text = Kopiervorlage::text(array(
			'email'       => 'a@example.org',
			'pop3'        => '1',
			'passwort'    => 'geheim',
			'imap_server' => 'sslin.de',
			'imap_port'   => 993,
			'pop3_server' => 'sslin.de',
			'pop3_port'   => 995,
			'smtp_server' => 'sslout.de',
			'smtp_port'   => 465,
		));

		$this->assertStringContainsString('Benutzername: a@example.org', $text);
		$this->assertStringContainsString('Passwort: geheim', $text);
		$this->assertStringContainsString('Posteingang (IMAP): sslin.de:993 (SSL)', $text);
		$this->assertStringContainsString('Posteingang (POP3): sslin.de:995 (SSL)', $text);
		$this->assertStringContainsString('Postausgang (SMTP): sslout.de:465 (SSL)', $text);
	}

	/**
	 * Fehlt ein Servername, steht ein Hinweis statt eines nackten Doppelpunkts.
	 *
	 * @return void
	 */
	public function testFehlenderServer(): void
	{
		$text = Kopiervorlage::text(array('email' => 'a@example.org', 'pop3' => '1', 'imap_server' => '', 'imap_port' => 993));

		$this->assertStringContainsString('Posteingang (IMAP): nicht eingetragen (SSL)', $text);
	}

	/**
	 * Weiterleitungen, Aliasse und Auto-Responder werden aufgeführt.
	 *
	 * @return void
	 */
	public function testWeiterleitungenAliasseAutoResponder(): void
	{
		$text = Kopiervorlage::text(array(
			'email'          => 'a@example.org',
			'forward'        => '1',
			'forwarder'      => serialize(array(
				array('forwarder_email' => 'b@example.org', 'forwarder_inhaber' => 'Frau Muster'),
				array('forwarder_email' => 'c@example.org', 'forwarder_inhaber' => ''),
			)),
			'alias'          => '1',
			'aliase'         => serialize(array(array('aliase_email' => 'info@example.org'))),
			'auto_responder' => '1',
		));

		$this->assertStringContainsString("Weiterleitungen:\n  - b@example.org (Frau Muster)\n  - c@example.org", $text);
		$this->assertStringContainsString("Alias-Adressen:\n  - info@example.org", $text);
		$this->assertStringContainsString('Auto-Responder: aktiv', $text);
	}

	/**
	 * Ein ausgeschalteter Bereich zeigt keine alten Zeilen.
	 *
	 * @return void
	 */
	public function testAusgeschalteteWeiterleitung(): void
	{
		$text = Kopiervorlage::text(array(
			'email'     => 'a@example.org',
			'forward'   => '',
			'forwarder' => serialize(array(array('forwarder_email' => 'b@example.org'))),
		));

		$this->assertStringNotContainsString('Weiterleitungen', $text);
	}

	/**
	 * Leere und angefangene Wizard-Zeilen sowie NULL-Felder stören nicht.
	 *
	 * @return void
	 */
	public function testLeereZeilenUndNull(): void
	{
		$text = Kopiervorlage::text(array(
			'email'     => 'a@example.org',
			'forward'   => '1',
			'forwarder' => serialize(array(array('forwarder_email' => ''), array('forwarder_inhaber' => 'ohne Adresse'))),
			'alias'     => '1',
			'aliase'    => null,
		));

		$this->assertSame('E-Mail-Konto: a@example.org', $text);
	}
}
