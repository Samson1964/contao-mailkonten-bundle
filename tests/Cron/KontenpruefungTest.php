<?php

declare(strict_types=1);

/**
 * Mailkonten für Contao Open Source CMS
 *
 * @author    Frank Hoppe
 * @license   LGPL-3.0-or-later
 */

namespace Schachbulle\ContaoMailkontenBundle\Tests\Cron;

use PHPUnit\Framework\TestCase;
use Schachbulle\ContaoMailkontenBundle\Cron\Kontenpruefung;

/**
 * Prüft die Umrechnung der Postfachwerte und den Aufbau der Übersichtstabelle.
 */
class KontenpruefungTest extends TestCase
{
	/**
	 * Kilobyte werden in Megabyte umgerechnet und die Auslastung ermittelt.
	 *
	 * @return void
	 */
	public function testKennzahlenRechnetInMegabyteUm(): void
	{
		$zeile = Kontenpruefung::kennzahlen('a@example.org', 12, array('usage' => 1024000, 'limit' => 2048000));

		$this->assertSame('a@example.org', $zeile['email']);
		$this->assertSame(12, $zeile['count']);
		$this->assertSame(1000.0, $zeile['usage']);
		$this->assertSame(2000.0, $zeile['quota']);
		$this->assertSame(50.0, $zeile['percent']);
	}

	/**
	 * Ohne Quota bleiben die Speicherwerte leer.
	 *
	 * Manche Server geben zur Postfachgröße gar keine Auskunft. Eine erfundene
	 * Null wäre irreführend — die Übersicht zeigt dort später einen Strich.
	 *
	 * @return void
	 */
	public function testKennzahlenOhneQuota(): void
	{
		$zeile = Kontenpruefung::kennzahlen('a@example.org', 3, null);

		$this->assertSame(3, $zeile['count']);
		$this->assertNull($zeile['usage']);
		$this->assertNull($zeile['quota']);
		$this->assertNull($zeile['percent']);
	}

	/**
	 * Ein unbegrenztes Postfach führt nicht zur Division durch null.
	 *
	 * Unter PHP 8 ist die Division durch null keine Warnung mehr, sondern ein
	 * DivisionByZeroError — der Cronjob wäre damit abgebrochen.
	 *
	 * @return void
	 */
	public function testKennzahlenBeiUnbegrenztemPostfach(): void
	{
		$zeile = Kontenpruefung::kennzahlen('a@example.org', 3, array('usage' => 500, 'limit' => 0));

		$this->assertSame(0.49, $zeile['usage']);
		$this->assertSame(0.0, $zeile['quota']);
		$this->assertNull($zeile['percent']);
	}

	/**
	 * Die Übersicht enthält für jedes Konto eine Zeile.
	 *
	 * @return void
	 */
	public function testTabelleEnthaeltAlleKonten(): void
	{
		$html = Kontenpruefung::tabelle(array
		(
			Kontenpruefung::kennzahlen('a@example.org', 1, array('usage' => 100, 'limit' => 1000)),
			Kontenpruefung::kennzahlen('b@example.org', 2, null),
		));

		$this->assertStringContainsString('<td>a@example.org</td>', $html);
		$this->assertStringContainsString('<td>b@example.org</td>', $html);

		// Kopfzeile plus zwei Datenzeilen
		$this->assertSame(3, substr_count($html, '<tr>'));

		// Ohne Quota steht ein Strich statt einer Zahl
		$this->assertStringContainsString('<td>-</td>', $html);
	}

	/**
	 * Ein volles Postfach wird hervorgehoben.
	 *
	 * @return void
	 */
	public function testTabelleHebtVollesPostfachHervor(): void
	{
		$voll = Kontenpruefung::tabelle(array(Kontenpruefung::kennzahlen('a@example.org', 1, array('usage' => 950, 'limit' => 1000))));
		$leer = Kontenpruefung::tabelle(array(Kontenpruefung::kennzahlen('a@example.org', 1, array('usage' => 100, 'limit' => 1000))));

		$this->assertStringContainsString('color:red', $voll);
		$this->assertStringNotContainsString('color:red', $leer);
	}

	/**
	 * Eine Adresse mit Sonderzeichen wird maskiert.
	 *
	 * Die Adressen stammen aus dem Backend-Formular und sind damit zwar keine
	 * offene Flanke, in HTML gehören spitze Klammern aber trotzdem maskiert.
	 *
	 * @return void
	 */
	public function testTabelleMaskiertSonderzeichen(): void
	{
		$html = Kontenpruefung::tabelle(array(Kontenpruefung::kennzahlen('<b>a</b>@example.org', 1, null)));

		$this->assertStringNotContainsString('<b>', $html);
		$this->assertStringContainsString('&lt;b&gt;', $html);
	}
}
