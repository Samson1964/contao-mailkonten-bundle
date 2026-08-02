<?php

declare(strict_types=1);

/**
 * Mailkonten für Contao Open Source CMS
 *
 * @author    Frank Hoppe
 * @license   LGPL-3.0-or-later
 */

namespace Schachbulle\ContaoMailkontenBundle\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Schachbulle\ContaoMailkontenBundle\ContaoMailkontenBundle;

/**
 * Meldet das Bundle bei der Managed Edition von Contao an.
 */
class Plugin implements BundlePluginInterface
{
	/**
	 * Gibt die Ladereihenfolge des Bundles an.
	 *
	 * Das Bundle wird nach dem Contao-Kern geladen, damit dessen DCA-Dateien
	 * (insbesondere tl_settings) bereits stehen, wenn diese Erweiterung sie um
	 * eigene Felder ergänzt.
	 *
	 * Der MultiColumnWizard wird hier bewusst **nicht** aufgeführt: Er wird nur
	 * beim Aufbau der Backend-Formulare gebraucht, nicht beim Bundle-Start.
	 *
	 * @param ParserInterface $parser Vom Manager-Plugin übergebener Parser für
	 *                                zusätzliche Konfigurationsdateien; hier
	 *                                nicht benötigt
	 *
	 * @return array Liste mit der einen Bundle-Konfiguration dieser Erweiterung
	 */
	public function getBundles(ParserInterface $parser)
	{
		return array
		(
			BundleConfig::create(ContaoMailkontenBundle::class)
				->setLoadAfter(array(ContaoCoreBundle::class)),
		);
	}
}
