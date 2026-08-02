<?php

/**
 * Mailkonten für Contao Open Source CMS
 *
 * @author    Frank Hoppe
 * @license   LGPL-3.0-or-later
 */

use Schachbulle\ContaoMailkontenBundle\Classes\Export;
use Schachbulle\ContaoMailkontenBundle\Classes\Tree;

/**
 * Backend-Modul
 *
 * Neben der Tabelle sind zwei eigene Modulschlüssel eingetragen. Contao ruft
 * die hinterlegte Methode auf, sobald in der Adresszeile „key=export“ bzw.
 * „key=tree“ steht — dahinter stecken der Knopf „Export“ über der Liste und
 * das Baum-Symbol in der Zeile eines Kontos.
 *
 * Der frühere Schlüssel „icon“ ist entfallen: Er verwies auf eine Bilddatei,
 * die es im Bundle gar nicht gibt, und wird von Contao 4 und 5 ohnehin nicht
 * mehr ausgewertet. Das Symbol der Backend-Navigation kommt aus dem Stylesheet.
 */
$GLOBALS['BE_MOD']['content']['mailkonten'] = array
(
	'tables'         => array('tl_mailkonten'),
	'export'         => array(Export::class, 'exportText'),
	'tree'           => array(Tree::class, 'exportKonto'),
);
