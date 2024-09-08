<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (C) 2005-2022 Leo Feyer
 *
 * @package   Fernschach-Verwaltung
 * @author    Frank Hoppe
 * @license   GNU/LGPL
 * @copyright Frank Hoppe 2022
 */

/**
 * Paletten
 */
$GLOBALS['TL_DCA']['tl_settings']['palettes']['default'] .= ';{mailkonten_legend:hide},mailkonten_absender,mailkonten_betreff,mailkonten_admin,mailkonten_cron';

/**
 * Felder
 */

// E-Mail-Adresse Admin
$GLOBALS['TL_DCA']['tl_settings']['fields']['mailkonten_absender'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['mailkonten_absender'],
	'inputType'               => 'text',
	'eval'                    => array
	(
		'mandatory'           => true, 
		'tl_class'            => 'w50', 
	),
);

// E-Mail-Adresse Admin
$GLOBALS['TL_DCA']['tl_settings']['fields']['mailkonten_betreff'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['mailkonten_betreff'],
	'inputType'               => 'text',
	'eval'                    => array
	(
		'mandatory'           => true, 
		'tl_class'            => 'w50', 
	),
);

// E-Mail-Adresse Admin
$GLOBALS['TL_DCA']['tl_settings']['fields']['mailkonten_admin'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['mailkonten_admin'],
	'inputType'               => 'text',
	'eval'                    => array
	(
		'rgxp'                => 'email', 
		'mandatory'           => true, 
		'tl_class'            => 'w50', 
	),
);

// E-Mail-Adresse Admin
$GLOBALS['TL_DCA']['tl_settings']['fields']['mailkonten_cron'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['mailkonten_cron'],
	'inputType'               => 'checkbox',
	'eval'                    => array
	(
		'tl_class'            => 'w50 m12', 
	),
);
