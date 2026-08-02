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
use Schachbulle\ContaoMailkontenBundle\Classes\Export;

/**
 * Prüft den Aufbau der Textdatei des Listenexports.
 *
 * Die Datenbank wird nicht angefasst — geprüft wird nur die Umwandlung der
 * bereits geladenen Datensätze in Text.
 */
class ExportTest extends TestCase
{
	/**
	 * Überspringt die Tests, wenn Contao nicht erreichbar ist.
	 *
	 * Der Export löst die serialisierten Felder des MultiColumnWizards mit
	 * StringUtil::deserialize() auf. Ohne Composer-Autoloader — also beim Lauf
	 * mit einem außerhalb installierten PHPUnit — steht die Klasse nicht zur
	 * Verfügung.
	 *
	 * @return void
	 */
	protected function setUp(): void
	{
		if (!class_exists(StringUtil::class))
		{
			$this->markTestSkipped('Contao steht nicht zur Verfügung (kein Composer-Autoloader).');
		}
	}

	/**
	 * Ein Konto ohne Zusatzfunktionen ergibt zwei Zeilen.
	 *
	 * @return void
	 */
	public function testEinfachesKonto(): void
	{
		$text = Export::formatiere(array(array
		(
			'email'  => 'a@example.org',
			'info'   => 'Geschäftsstelle',
			'tstamp' => 0,
		)));

		$this->assertStringContainsString('Konto: a@example.org (Letzte Änderung: unbekannt)', $text);
		$this->assertStringContainsString('    Info: Geschäftsstelle', $text);
		$this->assertStringNotContainsString('POP3/IMAP', $text);
		$this->assertStringEndsWith("\r\n", $text);
	}

	/**
	 * Weiterleitungen und Aliasse werden aufgelöst.
	 *
	 * @return void
	 */
	public function testWeiterleitungenUndAliasse(): void
	{
		$text = Export::formatiere(array(array
		(
			'email'   => 'a@example.org',
			'forward' => '1',
			'forwarder' => serialize(array
			(
				array('forwarder_email' => 'b@example.org'),
				array('forwarder_email' => 'c@example.org'),
			)),
			'alias'  => '1',
			'aliase' => serialize(array(array('aliase_email' => 'info@example.org'))),
		)));

		$this->assertStringContainsString('    Weiterleitungen: Ja', $text);
		$this->assertStringContainsString('        Adresse: b@example.org', $text);
		$this->assertStringContainsString('        Adresse: c@example.org', $text);
		$this->assertStringContainsString('    Aliasse: Ja', $text);
		$this->assertStringContainsString('        Adresse: info@example.org', $text);
	}

	/**
	 * Ein leeres Wizard-Feld bricht den Export nicht ab.
	 *
	 * Contao speichert einen leeren MultiColumnWizard als NULL. Die frühere
	 * Fassung reichte diesen Wert an unserialize() weiter, bekam false zurück
	 * und lief damit in ein foreach über einen Nicht-Wert.
	 *
	 * @return void
	 */
	public function testLeeresWizardFeld(): void
	{
		$text = Export::formatiere(array(array
		(
			'email'     => 'a@example.org',
			'forward'   => '1',
			'forwarder' => null,
		)));

		$this->assertStringContainsString('    Weiterleitungen: Ja', $text);
	}

	/**
	 * Die Spam-Nummer wird über die Sprachdatei aufgelöst.
	 *
	 * @return void
	 */
	public function testSpamOptionWirdAufgeloest(): void
	{
		$GLOBALS['TL_LANG']['tl_mailkonten']['spam_options'] = array('2' => 'Markieren');

		$text = Export::formatiere(array(array
		(
			'email'           => 'a@example.org',
			'pop3'            => '1',
			'mailbox_groesse' => 2000,
			'auslastung'      => 50,
			'spam'            => '2',
			'leerung'         => '1',
		)));

		$this->assertStringContainsString('        Spam-Filter: Markieren', $text);
		$this->assertStringContainsString('        Mailbox-Größe: 2000 MB (50% belegt)', $text);
		$this->assertStringContainsString('        Leerung: Ja', $text);

		unset($GLOBALS['TL_LANG']['tl_mailkonten']['spam_options']);
	}

	/**
	 * Ohne Konten kommt eine leere Datei heraus.
	 *
	 * @return void
	 */
	public function testKeineKonten(): void
	{
		$this->assertSame('', Export::formatiere(array()));
	}
}
