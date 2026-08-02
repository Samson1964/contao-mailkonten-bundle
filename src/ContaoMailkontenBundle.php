<?php

declare(strict_types=1);

/**
 * Mailkonten für Contao Open Source CMS
 *
 * @author    Frank Hoppe
 * @license   LGPL-3.0-or-later
 */

namespace Schachbulle\ContaoMailkontenBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Symfony-Bundle-Klasse der Mailkonten-Erweiterung.
 *
 * Enthält bewusst keine eigene Logik. Sie meldet das Bundle lediglich beim
 * Kernel an, damit Symfony das Verzeichnis src/Resources findet und Contao die
 * DCA- und Sprachdateien darunter einliest.
 */
class ContaoMailkontenBundle extends Bundle
{
}
