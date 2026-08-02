<?php

/**
 * Mailkonten für Contao Open Source CMS
 *
 * @author    Frank Hoppe
 * @license   LGPL-3.0-or-later
 */

/**
 * Legenden
 */
$GLOBALS['TL_LANG']['tl_settings']['mailkonten_legend'] = 'Mailkonten-Verwaltung';

/**
 * Felder
 */
$GLOBALS['TL_LANG']['tl_settings']['mailkonten_cron'] = array('Cronjob', 'Konten mit eingeschaltetem Checkup stündlich prüfen. Das eigentliche Prüfen eines Kontos erfolgt einmal täglich, die Ping-Mail einmal im Monat.');
$GLOBALS['TL_LANG']['tl_settings']['mailkonten_admin'] = array('E-Mail-Adresse Admin', 'E-Mail-Adresse des Administrators, an den die Übersicht des Checkups gesendet wird. Ohne Eintrag wird keine Übersicht verschickt.');
$GLOBALS['TL_LANG']['tl_settings']['mailkonten_absender'] = array('Absendername', 'Name des Absenders der Übersicht. Ohne Eintrag wird "Mailkonten" verwendet.');
$GLOBALS['TL_LANG']['tl_settings']['mailkonten_betreff'] = array('Betreff', 'Betreff der Übersicht. Ohne Eintrag wird "Mailkonten-Checkup" verwendet.');
