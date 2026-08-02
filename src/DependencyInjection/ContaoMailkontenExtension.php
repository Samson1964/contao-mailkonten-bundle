<?php

declare(strict_types=1);

/**
 * Mailkonten für Contao Open Source CMS
 *
 * @author    Frank Hoppe
 * @license   LGPL-3.0-or-later
 */

namespace Schachbulle\ContaoMailkontenBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * Lädt die Dienste dieser Erweiterung in den Symfony-Container.
 */
class ContaoMailkontenExtension extends Extension
{
	/**
	 * Liest src/Resources/config/services.yml ein.
	 *
	 * @param array            $mergedConfig Aufbereitete Konfiguration aus der
	 *                                       Anwendung; diese Erweiterung besitzt
	 *                                       keine eigenen Konfigurationsschlüssel
	 *                                       und wertet den Wert daher nicht aus
	 * @param ContainerBuilder $container    Container, in den die Dienste
	 *                                       eingetragen werden
	 *
	 * @return void
	 */
	public function load(array $mergedConfig, ContainerBuilder $container)
	{
		$loader = new YamlFileLoader(
			$container,
			new FileLocator(__DIR__.'/../Resources/config')
		);

		$loader->load('services.yml');
	}
}
