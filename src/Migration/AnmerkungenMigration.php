<?php

declare(strict_types=1);

/**
 * Mailkonten für Contao Open Source CMS
 *
 * @author    Frank Hoppe
 * @license   LGPL-3.0-or-later
 */

namespace Schachbulle\ContaoMailkontenBundle\Migration;

use Contao\CoreBundle\Migration\AbstractMigration;
use Contao\CoreBundle\Migration\MigrationResult;
use Doctrine\DBAL\Connection;

/**
 * Führt das aufgelöste Feld „Anmerkungen“ in das Feld „Info“ über.
 *
 * Bis Version 2.1.0 gab es neben der kurzen Info zwei getrennte Felder: die
 * knappe Info oben im Formular und die ausführlichen Anmerkungen weiter unten.
 * Seit 2.1.0 gibt es nur noch die Info, jetzt als mehrzeiliges Textfeld — die
 * Anmerkungen entfallen. Diese Migration läuft vor dem Schema-Abgleich, der
 * die Spalte „anmerkungen“ anschließend entfernt, und hängt ihren Inhalt an
 * die Info an; sonst wären die Anmerkungen unwiederbringlich verloren.
 */
class AnmerkungenMigration extends AbstractMigration
{
	/**
	 * Konstruktor mit Dependency Injection.
	 *
	 * @param Connection $connection Die Doctrine-Verbindung zur Contao-Datenbank
	 */
	public function __construct(private readonly Connection $connection)
	{
	}

	/**
	 * Prüft, ob noch Anmerkungen zu übernehmen sind.
	 *
	 * Maßgeblich ist der Datenbestand, nicht das bloße Vorhandensein der Spalte
	 * „anmerkungen“: Ist sie leer, kann der normale Schema-Abgleich sie
	 * gefahrlos entfernen, ohne dass diese Migration eingreifen muss.
	 *
	 * @return bool True, wenn mindestens ein Konto eine nicht-leere Anmerkung hat
	 */
	public function shouldRun(): bool
	{
		$schemaManager = $this->connection->createSchemaManager();

		if (!$schemaManager->tablesExist(['tl_mailkonten']))
		{
			return false;
		}

		$columns = $schemaManager->listTableColumns('tl_mailkonten');

		if (!isset($columns['anmerkungen'], $columns['info']))
		{
			return false;
		}

		return (bool) $this->connection->fetchOne(
			"SELECT COUNT(*) FROM tl_mailkonten WHERE anmerkungen IS NOT NULL AND anmerkungen <> ''"
		);
	}

	/**
	 * Hängt die Anmerkungen an die Info an und leert das Feld anschließend.
	 *
	 * Die Spalte „info“ ist zu diesem Zeitpunkt noch die alte, kurze
	 * varchar(255)-Spalte — der Schema-Abgleich, der sie auf das neue text-Feld
	 * erweitert, läuft erst im Anschluss an diese Migration. Ohne die
	 * vorgezogene Erweiterung hier würden lange Anmerkungen beim Anhängen auf
	 * 255 Zeichen abgeschnitten.
	 *
	 * Das Leeren von anmerkungen ist kein Aufräumen nebenbei, sondern nötig:
	 * shouldRun() erkennt eine übernommene Anmerkung nur daran, dass die Spalte
	 * wieder leer ist. Ohne das Leeren bliebe shouldRun() dauerhaft wahr und
	 * contao:migrate würde dieselbe Anmerkung bei jedem Durchlauf erneut an die
	 * Info anhängen, bis die eingebaute Schutzgrenze nach 19 Durchläufen abbricht.
	 *
	 * @return MigrationResult Das Ergebnis mit einer Meldung für die Konsole
	 */
	public function run(): MigrationResult
	{
		$this->connection->executeStatement('ALTER TABLE tl_mailkonten CHANGE info info TEXT NULL');

		$count = $this->connection->executeStatement(
			"UPDATE tl_mailkonten
			SET info = TRIM(CONCAT(info, IF(info <> '', '\n\n', ''), anmerkungen)), anmerkungen = ''
			WHERE anmerkungen IS NOT NULL AND anmerkungen <> ''"
		);

		return $this->createResult(
			true,
			\sprintf('%d Anmerkung(en) in das Info-Feld übernommen.', $count)
		);
	}
}
