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
use Schachbulle\ContaoMailkontenBundle\Classes\Tree;

/**
 * Prüft den Aufbau der Baumdarstellung von Weiterleitungen.
 *
 * Die Auflösung der Weiterleitungen wird als Funktion hereingereicht, damit die
 * Tests ohne Datenbank auskommen.
 */
class TreeTest extends TestCase
{
	/**
	 * Ein Konto ohne Weiterleitungen ergibt nur Kopf und Unterstreichung.
	 *
	 * @return void
	 */
	public function testKontoOhneWeiterleitungen(): void
	{
		$zeilen = Tree::baum('a@example.org', array(), static function (): array { return array(); });

		$this->assertCount(2, $zeilen);
		$this->assertSame('Konto: a@example.org', $zeilen[0]);

		// Die Unterstreichung ist so lang wie die Kopfzeile
		$this->assertSame(str_repeat('=', \strlen($zeilen[0])), $zeilen[1]);
	}

	/**
	 * Weiterleitungen der zweiten Ebene werden eingerückt ausgegeben.
	 *
	 * @return void
	 */
	public function testWeiterleitungenWerdenAufgeloest(): void
	{
		$zuordnung = array
		(
			'b@example.org' => array('c@example.org'),
			'c@example.org' => array('d@example.org'),
		);

		$zeilen = Tree::baum(
			'a@example.org',
			array(array('forwarder_email' => 'b@example.org')),
			static function (string $adresse) use ($zuordnung): array { return $zuordnung[$adresse] ?? array(); }
		);

		$this->assertSame('├─ b@example.org', $zeilen[2]);
		$this->assertSame('│ ├─ c@example.org', $zeilen[3]);
		$this->assertSame('│ │ ├─ d@example.org', $zeilen[4]);
		$this->assertCount(5, $zeilen);
	}

	/**
	 * Leere Zeilen des MultiColumnWizards werden übersprungen.
	 *
	 * Im Wizard bleibt regelmäßig eine angefangene Zeile ohne Adresse stehen.
	 *
	 * @return void
	 */
	public function testLeereZeilenWerdenUebersprungen(): void
	{
		$zeilen = Tree::baum(
			'a@example.org',
			array(
				array('forwarder_email' => ''),
				array('forwarder_info' => 'ohne Adresse'),
				array('forwarder_email' => 'b@example.org'),
			),
			static function (): array { return array(); }
		);

		$this->assertCount(3, $zeilen);
		$this->assertSame('├─ b@example.org', $zeilen[2]);
	}

	/**
	 * Gegenseitige Weiterleitungen führen nicht zur Endlosschleife.
	 *
	 * @return void
	 */
	public function testKreisverkehrWirdAbgebrochen(): void
	{
		$zuordnung = array
		(
			'b@example.org' => array('c@example.org'),
			'c@example.org' => array('b@example.org'),
		);

		$zeilen = Tree::baum(
			'a@example.org',
			array(array('forwarder_email' => 'b@example.org')),
			static function (string $adresse) use ($zuordnung): array { return $zuordnung[$adresse] ?? array(); }
		);

		// b, c und noch einmal b — dann ist der Weg bereits besucht
		$this->assertSame('├─ b@example.org', $zeilen[2]);
		$this->assertSame('│ ├─ c@example.org', $zeilen[3]);
		$this->assertSame('│ │ ├─ b@example.org', $zeilen[4]);
		$this->assertCount(5, $zeilen);
	}

	/**
	 * Tiefer als fünf Ebenen wird nicht aufgelöst.
	 *
	 * @return void
	 */
	public function testTiefeIstBegrenzt(): void
	{
		// Jede Adresse leitet an die nächste weiter, ohne Ende
		$aufloeser = static function (string $adresse): array
		{
			return array('nachfolger' . (((int) filter_var($adresse, FILTER_SANITIZE_NUMBER_INT)) + 1));
		};

		$zeilen = Tree::baum(
			'a@example.org',
			array(array('forwarder_email' => 'nachfolger1')),
			$aufloeser
		);

		// Kopf, Unterstreichung und fünf Ebenen
		$this->assertCount(7, $zeilen);
		$this->assertSame('│ │ │ │ ├─ nachfolger5', $zeilen[6]);
	}
}
