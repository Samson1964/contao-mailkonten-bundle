<?php 

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2014 Leo Feyer
 *
 */

/**
 * Backend-Modul Übersetzungen
 */

// Standardfunktionen
$GLOBALS['TL_LANG']['tl_mailkonten']['new'] = array('Neuer Eintrag', 'Neuer Eintrag');
$GLOBALS['TL_LANG']['tl_mailkonten']['edit'] = array('Eintrag %s bearbeiten', 'Eintrag %s bearbeiten');
$GLOBALS['TL_LANG']['tl_mailkonten']['copy'] = array('Eintrag %s kopieren', 'Eintrag %s kopieren');
$GLOBALS['TL_LANG']['tl_mailkonten']['delete'] = array('Eintrag %s löschen', 'Eintrag %s löschen');
$GLOBALS['TL_LANG']['tl_mailkonten']['toggle'] = array('Eintrag %s aktivieren/deaktivieren', 'Eintrag %s aktivieren/deaktivieren');
$GLOBALS['TL_LANG']['tl_mailkonten']['show'] = array('Details zum Eintrag %s anzeigen', 'Details zum Eintrag %s anzeigen');

// Formularfelder
$GLOBALS['TL_LANG']['tl_mailkonten']['mail_legend'] = 'Postfach';
$GLOBALS['TL_LANG']['tl_mailkonten']['email'] = array('E-Mail', 'E-Mail-Adresse');
$GLOBALS['TL_LANG']['tl_mailkonten']['mailbox_groesse'] = array('Größe', 'Größe des Postfachs in MB');
$GLOBALS['TL_LANG']['tl_mailkonten']['art'] = array('Art', 'Art des Postfachs');
$GLOBALS['TL_LANG']['tl_mailkonten']['spam'] = array('Spam', 'Was wird mit Spam gemacht?');
$GLOBALS['TL_LANG']['tl_mailkonten']['auto_responder'] = array('Auto-Responder', 'Auto-Responder aktiv');
$GLOBALS['TL_LANG']['tl_mailkonten']['leerung'] = array('Leerung', 'Leerung vom Inhaber des Postfachs');
$GLOBALS['TL_LANG']['tl_mailkonten']['auslastung'] = array('Auslastung', 'Auslastung des Postfachs');
$GLOBALS['TL_LANG']['tl_mailkonten']['passwort'] = array('Passwort', 'Passwort des Postfaches');

$GLOBALS['TL_LANG']['tl_mailkonten']['adresse_legend'] = 'Informationen';
$GLOBALS['TL_LANG']['tl_mailkonten']['alias_adressen'] = array('Alias-Adressen', 'Alias-Adressen');
$GLOBALS['TL_LANG']['tl_mailkonten']['weiterleitungen'] = array('Weiterleitungen', 'Weiterleitungen');
$GLOBALS['TL_LANG']['tl_mailkonten']['anmerkungen'] = array('Anmerkungen', 'Anmerkungen');

$GLOBALS['TL_LANG']['tl_mailkonten']['art_options'] = array
(
	'1' => 'POP3',
	'2' => 'Exchange',
	'3' => 'Weiterleitung',
	'4' => 'POP3/Weiterleitung',
	'5' => 'Mailingliste',
	'6' => 'IMAP'
);

$GLOBALS['TL_LANG']['tl_mailkonten']['spam_options'] = array
(
	'1' => 'Zustellen',
	'2' => 'Markieren',
	'3' => 'Löschen',
	'4' => 'Ablehnen'
);

$GLOBALS['TL_LANG']['tl_mailkonten']['auslastung_options'] = array
(
	'1'   => '0%',
	'10'  => '10%',
	'20'  => '20%',
	'30'  => '30%',
	'40'  => '40%',
	'50'  => '50%',
	'60'  => '60%',
	'70'  => '70%',
	'80'  => '80%',
	'90'  => '90%',
	'100' => '100%',
);
