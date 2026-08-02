<?php

/**
 * Mailkonten für Contao Open Source CMS
 *
 * @author    Frank Hoppe
 * @license   LGPL-3.0-or-later
 */

/**
 * Palette erweitern
 */
$GLOBALS['TL_DCA']['tl_settings']['palettes']['default'] .= ';{mailkonten_legend:hide},mailkonten_cron,mailkonten_admin,mailkonten_absender,mailkonten_betreff';

/**
 * Felder
 *
 * Kein Feld ist Pflicht. Die Einstellungsseite von Contao speichert immer die
 * gesamte Palette; ein Pflichtfeld dieser Erweiterung hätte sonst auch das
 * Speichern völlig unbeteiligter Einstellungen blockiert, solange es leer ist.
 */

// Schalter für den stündlichen Cronjob
$GLOBALS['TL_DCA']['tl_settings']['fields']['mailkonten_cron'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['mailkonten_cron'],
	'inputType'               => 'checkbox',
	'eval'                    => array
	(
		'tl_class'            => 'w50 m12',
	),
);

// Empfänger der Checkup-Übersicht
$GLOBALS['TL_DCA']['tl_settings']['fields']['mailkonten_admin'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['mailkonten_admin'],
	'inputType'               => 'text',
	'eval'                    => array
	(
		'rgxp'                => 'email',
		'maxlength'           => 255,
		'tl_class'            => 'w50',
	),
);

// Absendername der Checkup-Übersicht
$GLOBALS['TL_DCA']['tl_settings']['fields']['mailkonten_absender'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['mailkonten_absender'],
	'inputType'               => 'text',
	'eval'                    => array
	(
		'maxlength'           => 255,
		'tl_class'            => 'w50',
	),
);

// Betreff der Checkup-Übersicht
$GLOBALS['TL_DCA']['tl_settings']['fields']['mailkonten_betreff'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['mailkonten_betreff'],
	'inputType'               => 'text',
	'eval'                    => array
	(
		'maxlength'           => 255,
		'tl_class'            => 'w50',
	),
);
