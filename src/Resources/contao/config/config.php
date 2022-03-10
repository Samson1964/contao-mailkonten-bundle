<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (C) 2005-2013 Leo Feyer
 *
 * @package   bdf
 * @author    Frank Hoppe
 * @license   GNU/LGPL
 * @copyright Frank Hoppe 2014
 */

$GLOBALS['BE_MOD']['content']['mailkonten'] = array
(
	'tables'         => array('tl_mailkonten'),
	'export'         => array('Schachbulle\ContaoMailkontenBundle\Classes\Export', 'exportText'),
	'icon'           => 'bundles/contaomailkonten/images/icon.png'
);
