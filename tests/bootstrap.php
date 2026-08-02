<?php

declare(strict_types=1);

/**
 * Mailkonten für Contao Open Source CMS
 *
 * @author    Frank Hoppe
 * @license   LGPL-3.0-or-later
 */

/*
 * Bootstrap für den Betrieb ohne eigenes vendor-Verzeichnis.
 *
 * Das Bundle wird ohne vendor/ ausgeliefert, die Tests laufen daher mit einem
 * außerhalb installierten PHPUnit. Ist trotzdem ein Composer-Autoloader
 * vorhanden, wird dieser bevorzugt — dann stehen auch die Contao-Klassen zur
 * Verfügung und es läuft kein Test übersprungen durch.
 */

$strComposer = __DIR__ . '/../vendor/autoload.php';

if (file_exists($strComposer))
{
	require $strComposer;

	return;
}

spl_autoload_register(static function (string $strClass): void {
	$arrMap = array
	(
		'Schachbulle\\ContaoMailkontenBundle\\Tests\\' => __DIR__ . '/',
		'Schachbulle\\ContaoMailkontenBundle\\' => __DIR__ . '/../src/',
	);

	foreach ($arrMap as $strPrefix => $strDir)
	{
		if (0 !== strpos($strClass, $strPrefix))
		{
			continue;
		}

		$strFile = $strDir . str_replace('\\', '/', substr($strClass, \strlen($strPrefix))) . '.php';

		if (file_exists($strFile))
		{
			require $strFile;
		}

		return;
	}
});
