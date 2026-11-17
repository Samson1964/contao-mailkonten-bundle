<?php

/**
 * Mailkonten für Contao Open Source CMS
 *
 * @author    Frank Hoppe
 * @license   LGPL-3.0-or-later
 */

use Contao\System;
use Schachbulle\ContaoMailkontenBundle\Classes\Export;
use Schachbulle\ContaoMailkontenBundle\Classes\Serverabruf;
use Schachbulle\ContaoMailkontenBundle\Classes\Tree;

/**
 * Backend-Modul
 *
 * Neben der Tabelle sind drei eigene Modulschlüssel eingetragen. Contao ruft
 * die hinterlegte Methode auf, sobald in der Adresszeile „key=export“,
 * „key=tree“ bzw. „key=server“ steht — dahinter stecken der Knopf „Export“
 * über der Liste, das Baum-Symbol in der Zeile eines Kontos und der Knopf
 * „Server abrufen“ im Formular.
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
	'server'         => array(Serverabruf::class, 'abrufen'),
);

/**
 * Skript für die Knöpfe „Server abrufen“ und „Kopieren“ im Formular.
 *
 * Es steht hier und nicht in der DCA-Datei: Contao 5 speichert die DCA
 * zwischen, dabei gingen Nebenwirkungen wie dieser Eintrag verloren. Das
 * Skript arbeitet mit Ereignisweitergabe am Dokument, weil die Unterpalette
 * POP3/IMAP erst nach dem Anhaken per Ajax nachgeladen wird.
 *
 * Geladen wird nur im Backend. Beim Bauen des Containers und auf der Konsole
 * gibt es noch keine Anfrage — dann bleibt der Eintrag aus.
 */
$container = System::getContainer();
$request = null !== $container && $container->has('request_stack') ? $container->get('request_stack')->getCurrentRequest() : null;

if (null !== $request && $container->get('contao.routing.scope_matcher')->isBackendRequest($request))
{
	$GLOBALS['TL_JAVASCRIPT'][] = 'bundles/contaomailkonten/js/mailkonten.js';
}

unset($container, $request);
