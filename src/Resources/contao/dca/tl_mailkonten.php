<?php

/**
 * Mailkonten für Contao Open Source CMS
 *
 * @author    Frank Hoppe
 * @license   LGPL-3.0-or-later
 */

use Contao\Backend;
use Contao\DataContainer;
use Contao\DC_Table;
use Contao\StringUtil;

/**
 * Tabelle tl_mailkonten
 */
$GLOBALS['TL_DCA']['tl_mailkonten'] = array
(
	// Konfiguration
	'config' => array
	(
		'dataContainer'               => DC_Table::class,
		'enableVersioning'            => true,
		'switchToEdit'                => true,
		'markAsCopy'                  => 'email',
		'sql' => array
		(
			// Hier standen früher zwei Einträge für „email“ untereinander,
			// „index“ und „unique“. PHP behält bei doppelten Schlüsseln nur den
			// letzten — der Index war also von Anfang an wirkungslos. Ein
			// eindeutiger Schlüssel wird ohnehin indiziert
			'keys' => array
			(
				'id'                  => 'primary',
				'email'               => 'unique',
			)
		)
	),

	// Listenansicht
	'list' => array
	(
		'sorting' => array
		(
			'mode'                    => DataContainer::MODE_SORTABLE,
			'fields'                  => array('email'),
			'flag'                    => DataContainer::SORT_ASC,
			'panelLayout'             => 'filter;search,sort,limit',
		),
		'label' => array
		(
			// „alias“ gehört in die Spaltenliste, weil das label_callback die
			// Alias-Adressen in die vierte Spalte schreibt. Als die Spalte in
			// Fassung 1.5.5 entfernt wurde, landeten die Aliasse in der
			// Info-Spalte und die Info war nirgends mehr zu sehen
			'fields'                  => array('email', 'pop3', 'forward', 'alias', 'info', 'inhaber'),
			'showColumns'             => true,
			'label_callback'          => array('tl_mailkonten', 'getRecord')
		),
		'global_operations' => array
		(
			'export' => array
			(
				// Zeigte früher auf tl_lizenzverwaltung — ein Überbleibsel der
				// Erweiterung, aus der der Export übernommen wurde. Der Knopf
				// hatte deshalb gar keine Beschriftung
				'label'               => &$GLOBALS['TL_LANG']['tl_mailkonten']['export'],
				'href'                => 'key=export',
				'icon'                => 'bundles/contaomailkonten/images/export.png',
				'attributes'          => 'onclick="Backend.getScrollOffset();"'
			),
			'all' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['MSC']['all'],
				'href'                => 'act=select',
				'class'               => 'header_edit_all',
				'attributes'          => 'onclick="Backend.getScrollOffset();"'
			)
		),
		'operations' => array
		(
			'edit' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_mailkonten']['edit'],
				'href'                => 'act=edit',
				'icon'                => 'edit.svg'
			),
			'copy' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_mailkonten']['copy'],
				'href'                => 'act=copy',
				'icon'                => 'copy.svg',
			),
			'delete' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_mailkonten']['delete'],
				'href'                => 'act=delete',
				'icon'                => 'delete.svg',
				'attributes'          => 'onclick="if(!confirm(\'' . ($GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? '') . '\'))return false;Backend.getScrollOffset()"'
			),
			// Kern-Toggle statt haste_ajax_operation. Damit entfällt die
			// Abhängigkeit codefog/contao-haste, die es für Contao 5 nicht gibt
			'toggle' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_mailkonten']['toggle'],
				'href'                => 'act=toggle&amp;field=published',
				'icon'                => 'visible.svg',
				'attributes'          => 'onclick="Backend.getScrollOffset()"',
				'showInHeader'        => true,
			),
			'tree' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_mailkonten']['tree'],
				'href'                => 'key=tree',
				'icon'                => 'bundles/contaomailkonten/images/tree.png',
			),
			'show' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_mailkonten']['show'],
				'href'                => 'act=show',
				'icon'                => 'show.svg'
			)
		)
	),

	// Paletten
	'palettes' => array
	(
		'__selector__'                => array('pop3', 'forward', 'alias', 'mailinglist'),
		'default'                     => '{mail_legend},email,info;{pop3_legend},pop3;{forward_legend},forward;{alias_legend:hide},alias;{responder_legend:hide},auto_responder;{mailingliste_legend},mailinglist;{history_legend:hide},history;{info_legend:hide},anmerkungen;{publish_legend},published,deleted'
	),

	// Unterpaletten
	'subpalettes' => array
	(
		'pop3'                        => 'inhaber,passwort,mailbox_groesse,auslastung,spam,leerung,smtp_server,smtp_port,pop3_server,pop3_port,imap_server,imap_port,backup,checkup',
		'forward'                     => 'forwarder,weiterleitungen',
		'alias'                       => 'aliase,alias_adressen',
		'mailinglist'                 => 'url,urlLinked,mlPasswort,mailingliste',
	),

	// Felder der Tabelle tl_mailkonten
	'fields' => array
	(
		'id' => array
		(
			'sql'                     => "int(10) unsigned NOT NULL auto_increment"
		),
		'tstamp' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_mailkonten']['tstamp'],
			'flag'                    => DataContainer::SORT_DAY_ASC,
			'sorting'                 => true,
			'sql'                     => "int(10) unsigned NOT NULL default '0'",
		),
		'email' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_mailkonten']['email'],
			'inputType'               => 'text',
			'exclude'                 => true,
			'search'                  => true,
			'sorting'                 => true,
			'filter'                  => true,
			'sql'                     => "varchar(255) NOT NULL default ''",
			'eval'                    => array
			(
				'rgxp'                => 'email',
				'mandatory'           => true,
				'maxlength'           => 255,
				'tl_class'            => 'w50'
			)
		),
		'info' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_mailkonten']['info'],
			'inputType'               => 'text',
			'exclude'                 => true,
			'search'                  => true,
			'sorting'                 => true,
			'filter'                  => true,
			'sql'                     => "varchar(255) NOT NULL default ''",
			'eval'                    => array
			(
				'mandatory'           => false,
				'maxlength'           => 255,
				'tl_class'            => 'w50'
			)
		),
		'pop3' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_mailkonten']['pop3'],
			'inputType'               => 'checkbox',
			'exclude'                 => true,
			'filter'                  => true,
			'eval'                    => array
			(
				'submitOnChange'      => true,
				'isBoolean'           => true,
				'tl_class'            => 'clr'
			),
			'sql'                     => "char(1) NOT NULL default ''"
		),
		'inhaber' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_mailkonten']['inhaber'],
			'inputType'               => 'text',
			'exclude'                 => true,
			'search'                  => true,
			'sorting'                 => true,
			'filter'                  => true,
			'eval'                    => array
			(
				'maxlength'           => 255,
				'tl_class'            => 'w50'
			),
			'sql'                     => "varchar(255) NOT NULL default ''"
		),
		'passwort' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_mailkonten']['passwort'],
			'inputType'               => 'text',
			'exclude'                 => true,
			'search'                  => false,
			'sorting'                 => false,
			'filter'                  => false,
			'sql'                     => "varchar(64) NOT NULL default ''",
			'eval'                    => array
			(
				'mandatory'           => false,
				'maxlength'           => 64,
				'tl_class'            => 'w50'
			)
		),
		'mailbox_groesse' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_mailkonten']['mailbox_groesse'],
			'inputType'               => 'text',
			'exclude'                 => true,
			'search'                  => false,
			'sorting'                 => true,
			'filter'                  => false,
			'eval'                    => array
			(
				'mandatory'           => false,
				'rgxp'                => 'natural',
				'tl_class'            => 'w50'
			),
			'sql'                     => "int(10) unsigned NOT NULL default '0'"
		),
		'auslastung' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_mailkonten']['auslastung'],
			'exclude'                 => true,
			'filter'                  => true,
			'inputType'               => 'select',
			'options'                 => &$GLOBALS['TL_LANG']['tl_mailkonten']['auslastung_options'],
			'eval'                    => array
			(
				'includeBlankOption'  => true,
				'tl_class'            => 'w50'
			),
			'sql'                     => "int(3) unsigned NOT NULL default '0'"
		),
		'spam' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_mailkonten']['spam'],
			'exclude'                 => true,
			'filter'                  => true,
			'inputType'               => 'select',
			'options'                 => &$GLOBALS['TL_LANG']['tl_mailkonten']['spam_options'],
			'eval'                    => array
			(
				'includeBlankOption'  => true,
				'tl_class'            => 'w50'
			),
			'sql'                     => "char(1) NOT NULL default ''"
		),
		'leerung' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_mailkonten']['leerung'],
			'inputType'               => 'checkbox',
			'exclude'                 => true,
			'default'                 => '',
			'filter'                  => true,
			'eval'                    => array
			(
				'tl_class'            => 'w50 m12',
				'isBoolean'           => true
			),
			'sql'                     => "char(1) NOT NULL default ''"
		),
		'smtp_server' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_mailkonten']['smtp_server'],
			'inputType'               => 'text',
			'exclude'                 => true,
			'search'                  => false,
			'sorting'                 => false,
			'filter'                  => false,
			'default'                 => 'sslout.de',
			'eval'                    => array
			(
				'mandatory'           => false,
				'maxlength'           => 64,
				'tl_class'            => 'w50'
			),
			'sql'                     => "varchar(64) NOT NULL default 'sslout.de'"
		),
		'smtp_port' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_mailkonten']['smtp_port'],
			'inputType'               => 'text',
			'exclude'                 => true,
			'search'                  => false,
			'sorting'                 => false,
			'filter'                  => false,
			'default'                 => 465,
			'eval'                    => array
			(
				'mandatory'           => false,
				'rgxp'                => 'natural',
				'tl_class'            => 'w50'
			),
			'sql'                     => "int(5) unsigned NOT NULL default '465'"
		),
		'pop3_server' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_mailkonten']['pop3_server'],
			'inputType'               => 'text',
			'exclude'                 => true,
			'search'                  => false,
			'sorting'                 => false,
			'filter'                  => false,
			'default'                 => 'sslin.de',
			'eval'                    => array
			(
				'mandatory'           => false,
				'maxlength'           => 64,
				'tl_class'            => 'w50'
			),
			'sql'                     => "varchar(64) NOT NULL default 'sslin.de'"
		),
		'pop3_port' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_mailkonten']['pop3_port'],
			'inputType'               => 'text',
			'exclude'                 => true,
			'search'                  => false,
			'sorting'                 => false,
			'filter'                  => false,
			'default'                 => 995,
			'eval'                    => array
			(
				'mandatory'           => false,
				'rgxp'                => 'natural',
				'tl_class'            => 'w50'
			),
			'sql'                     => "int(5) unsigned NOT NULL default '995'"
		),
		'imap_server' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_mailkonten']['imap_server'],
			'inputType'               => 'text',
			'exclude'                 => true,
			'search'                  => false,
			'sorting'                 => false,
			'filter'                  => false,
			'default'                 => 'sslin.de',
			'eval'                    => array
			(
				'mandatory'           => false,
				'maxlength'           => 64,
				'tl_class'            => 'w50'
			),
			'sql'                     => "varchar(64) NOT NULL default 'sslin.de'"
		),
		'imap_port' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_mailkonten']['imap_port'],
			'inputType'               => 'text',
			'exclude'                 => true,
			'search'                  => false,
			'sorting'                 => false,
			'filter'                  => false,
			'default'                 => 993,
			'eval'                    => array
			(
				'mandatory'           => false,
				'rgxp'                => 'natural',
				'tl_class'            => 'w50'
			),
			'sql'                     => "int(5) unsigned NOT NULL default '993'"
		),
		'backup' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_mailkonten']['backup'],
			'inputType'               => 'checkbox',
			'exclude'                 => true,
			'default'                 => '',
			'filter'                  => true,
			'eval'                    => array
			(
				'tl_class'            => 'w50 m12',
				'isBoolean'           => true
			),
			'sql'                     => "char(1) NOT NULL default ''"
		),
		// Zeitpunkt des letzten Backups; wird noch von keiner Funktion geschrieben
		'backup_date' => array
		(
			'sql'                     => "int(10) unsigned NOT NULL default '0'",
		),
		'checkup' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_mailkonten']['checkup'],
			'inputType'               => 'checkbox',
			'exclude'                 => true,
			'default'                 => '',
			'filter'                  => true,
			'eval'                    => array
			(
				'tl_class'            => 'w50 m12',
				'isBoolean'           => true
			),
			'sql'                     => "char(1) NOT NULL default ''"
		),
		// Zeitpunkt des letzten Checkups, gesetzt von Cron\Kontenpruefung
		'checkup_date' => array
		(
			'sql'                     => "int(10) unsigned NOT NULL default '0'",
		),
		// Zeitpunkt der letzten Ping-Mail, gesetzt von Cron\Kontenpruefung
		'ping_date' => array
		(
			'sql'                     => "int(10) unsigned NOT NULL default '0'",
		),
		'alias' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_mailkonten']['alias'],
			'inputType'               => 'checkbox',
			'exclude'                 => true,
			'filter'                  => true,
			'eval'                    => array
			(
				'submitOnChange'      => true,
				'isBoolean'           => true,
				'tl_class'            => 'clr'
			),
			'sql'                     => "char(1) NOT NULL default ''"
		),
		'aliase' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_mailkonten']['aliase'],
			'exclude'                 => true,
			'inputType'               => 'multiColumnWizard',
			'eval'                    => array
			(
				'tl_class'            => 'clr',
				'buttonPos'           => 'top',
				'columnFields'        => array
				(
					'aliase_email' => array
					(
						'label'                 => &$GLOBALS['TL_LANG']['tl_mailkonten']['aliase_email'],
						'exclude'               => true,
						'inputType'             => 'text',
						'eval'                  => array
						(
							'valign'            => 'middle',
							'style'             => 'width:400px;'
						)
					),
					'aliase_date' => array
					(
						'label'                 => &$GLOBALS['TL_LANG']['tl_mailkonten']['aliase_date'],
						'exclude'               => true,
						'inputType'             => 'text',
						'eval'                  => array
						(
							'rgxp'              => 'date',
							'mandatory'         => false,
							'doNotCopy'         => true,
							'datepicker'        => true,
							'tl_class'          => 'wizard',
							'style'             => 'width:150px;'
						),
						'load_callback' => array
						(
							array('tl_mailkonten', 'loadDate')
						),
					),
					'aliase_info' => array
					(
						'label'                 => &$GLOBALS['TL_LANG']['tl_mailkonten']['aliase_info'],
						'exclude'               => true,
						'inputType'             => 'text',
						'eval'                  => array
						(
							'style'             => 'width:100%;'
						)
					),
				)
			),
			'sql'                   => "blob NULL"
		),
		'alias_adressen' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_mailkonten']['alias_adressen'],
			'inputType'               => 'textarea',
			'exclude'                 => true,
			'search'                  => true,
			'sorting'                 => false,
			'filter'                  => false,
			'eval'                    => array
			(
				'tl_class'            => 'long',
				'cols'                => 80,
				'rows'                => 5,
				'style'               => 'height: 80px'
			),
			'sql'                     => "text NULL"
		),
		'forward' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_mailkonten']['forward'],
			'inputType'               => 'checkbox',
			'exclude'                 => true,
			'filter'                  => true,
			'eval'                    => array
			(
				'submitOnChange'      => true,
				'isBoolean'           => true,
				'tl_class'            => 'clr'
			),
			'sql'                     => "char(1) NOT NULL default ''"
		),
		'forwarder' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_mailkonten']['forwarder'],
			'exclude'                 => true,
			'inputType'               => 'multiColumnWizard',
			'eval'                    => array
			(
				'tl_class'            => 'clr',
				'buttonPos'           => 'top',
				'columnFields'        => array
				(
					'forwarder_email' => array
					(
						'label'                 => &$GLOBALS['TL_LANG']['tl_mailkonten']['forwarder_email'],
						'exclude'               => true,
						'inputType'             => 'text',
						'eval'                  => array
						(
							'valign'            => 'middle',
							'style'             => 'width:400px;'
						)
					),
					'forwarder_inhaber' => array
					(
						'label'                 => &$GLOBALS['TL_LANG']['tl_mailkonten']['forwarder_inhaber'],
						'exclude'               => true,
						'inputType'             => 'text',
						'eval'                  => array
						(
							'valign'            => 'middle',
							'style'             => 'width:240px;'
						)
					),
					'forwarder_date' => array
					(
						'label'                 => &$GLOBALS['TL_LANG']['tl_mailkonten']['forwarder_date'],
						'exclude'               => true,
						'inputType'             => 'text',
						'eval'                  => array
						(
							'rgxp'              => 'date',
							'mandatory'         => false,
							'doNotCopy'         => true,
							'datepicker'        => true,
							'tl_class'          => 'wizard',
							'style'             => 'width:150px;'
						),
						'load_callback' => array
						(
							array('tl_mailkonten', 'loadDate')
						),
					),
					'forwarder_info' => array
					(
						'label'                 => &$GLOBALS['TL_LANG']['tl_mailkonten']['forwarder_info'],
						'exclude'               => true,
						'inputType'             => 'text',
						'eval'                  => array
						(
							'style'             => 'width:100%;'
						)
					),
				)
			),
			'sql'                   => "blob NULL"
		),
		'weiterleitungen' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_mailkonten']['weiterleitungen'],
			'inputType'               => 'textarea',
			'exclude'                 => true,
			'search'                  => true,
			'sorting'                 => false,
			'filter'                  => false,
			'eval'                    => array
			(
				'tl_class'            => 'long',
				'cols'                => 80,
				'rows'                => 5,
				'style'               => 'height: 80px'
			),
			'sql'                     => "text NULL"
		),
		'history' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_mailkonten']['history'],
			'exclude'                 => true,
			'inputType'               => 'multiColumnWizard',
			'eval'                    => array
			(
				'tl_class'            => 'clr',
				'buttonPos'           => 'top',
				'columnFields'        => array
				(
					'history_date' => array
					(
						'label'                 => &$GLOBALS['TL_LANG']['tl_mailkonten']['history_date'],
						'exclude'               => true,
						'inputType'             => 'text',
						'eval'                  => array
						(
							'rgxp'              => 'date',
							'mandatory'         => false,
							'doNotCopy'         => true,
							'datepicker'        => true,
							'tl_class'          => 'wizard',
							'style'             => 'width:150px;'
						),
						'load_callback' => array
						(
							array('tl_mailkonten', 'loadDate')
						),
					),
					'history_info' => array
					(
						'label'                 => &$GLOBALS['TL_LANG']['tl_mailkonten']['history_info'],
						'exclude'               => true,
						'inputType'             => 'textarea',
						'eval'                  => array()
					),
				)
			),
			'sql'                   => "blob NULL"
		),
		'mailinglist' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_mailkonten']['mailinglist'],
			'inputType'               => 'checkbox',
			'exclude'                 => true,
			'filter'                  => true,
			'eval'                    => array
			(
				'submitOnChange'      => true,
				'isBoolean'           => true,
				'tl_class'            => 'clr'
			),
			'sql'                     => "char(1) NOT NULL default ''"
		),
		'url' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_mailkonten']['url'],
			'inputType'               => 'text',
			'exclude'                 => true,
			'search'                  => false,
			'sorting'                 => false,
			'filter'                  => false,
			'sql'                     => "varchar(255) NOT NULL default ''",
			'eval'                    => array
			(
				'rgxp'                => 'url',
				'mandatory'           => false,
				'maxlength'           => 255,
				'tl_class'            => 'w50'
			)
		),
		// Gibt die Adresse der Listenverwaltung als anklickbaren Link aus
		'urlLinked' => array
		(
			'input_field_callback'    => array('tl_mailkonten', 'getURL'),
		),
		'mlPasswort' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_mailkonten']['mlPasswort'],
			'inputType'               => 'text',
			'exclude'                 => true,
			'search'                  => false,
			'sorting'                 => false,
			'filter'                  => false,
			'sql'                     => "varchar(64) NOT NULL default ''",
			'eval'                    => array
			(
				'mandatory'           => false,
				'maxlength'           => 64,
				'tl_class'            => 'w50'
			)
		),
		'mailingliste' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_mailkonten']['mailingliste'],
			'exclude'                 => true,
			'inputType'               => 'multiColumnWizard',
			'eval'                    => array
			(
				'tl_class'            => 'clr',
				'buttonPos'           => 'top',
				'columnFields'        => array
				(
					'mailingliste_email' => array
					(
						'label'                 => &$GLOBALS['TL_LANG']['tl_mailkonten']['mailingliste_email'],
						'exclude'               => true,
						'inputType'             => 'text',
						'eval'                  => array
						(
							'valign'            => 'middle',
							'style'             => 'width:100%;'
						)
					),
					'mailingliste_inhaber' => array
					(
						'label'                 => &$GLOBALS['TL_LANG']['tl_mailkonten']['mailingliste_inhaber'],
						'exclude'               => true,
						'inputType'             => 'text',
						'eval'                  => array
						(
							'valign'            => 'middle',
							'style'             => 'width:100%;'
						)
					),
					'mailingliste_date' => array
					(
						'label'                 => &$GLOBALS['TL_LANG']['tl_mailkonten']['mailingliste_date'],
						'exclude'               => true,
						'inputType'             => 'text',
						'eval'                  => array
						(
							'rgxp'              => 'date',
							'mandatory'         => false,
							'doNotCopy'         => true,
							'datepicker'        => true,
							'tl_class'          => 'wizard',
							'style'             => 'width:90%;'
						),
						'load_callback' => array
						(
							array('tl_mailkonten', 'loadDate')
						),
					),
					'mailingliste_info' => array
					(
						'label'                 => &$GLOBALS['TL_LANG']['tl_mailkonten']['mailingliste_info'],
						'exclude'               => true,
						'inputType'             => 'text',
						'eval'                  => array
						(
							'style'             => 'width:100%;'
						)
					),
				)
			),
			'sql'                   => "blob NULL"
		),
		'anmerkungen' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_mailkonten']['anmerkungen'],
			'inputType'               => 'textarea',
			'exclude'                 => true,
			'search'                  => true,
			'sorting'                 => false,
			'filter'                  => false,
			'eval'                    => array
			(
				'tl_class'            => 'long',
				'cols'                => 80,
				'rows'                => 5,
				'style'               => 'height: 80px'
			),
			'sql'                     => "text NULL"
		),
		'auto_responder' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_mailkonten']['auto_responder'],
			'inputType'               => 'checkbox',
			'exclude'                 => true,
			'default'                 => '',
			'filter'                  => true,
			'eval'                    => array
			(
				'tl_class'            => 'w50',
				'isBoolean'           => true
			),
			'sql'                     => "char(1) NOT NULL default ''"
		),
		// Vorbelegung war „1“ — jedes neue Konto galt damit in der Datenbank
		// als gelöscht, sobald es an der Paletten-Vorbelegung vorbei angelegt
		// wurde (etwa beim Import)
		'deleted' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_mailkonten']['deleted'],
			'inputType'               => 'checkbox',
			'exclude'                 => true,
			'default'                 => '',
			'filter'                  => true,
			'eval'                    => array
			(
				'tl_class'            => 'w50',
				'isBoolean'           => true
			),
			'sql'                     => "char(1) NOT NULL default ''"
		),
		'published' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_mailkonten']['published'],
			'inputType'               => 'checkbox',
			'exclude'                 => true,
			'default'                 => '1',
			'filter'                  => true,
			// Erlaubt der Kern-Toggle-Operation, dieses Feld über die
			// Adresszeile umzuschalten. Ohne den Schalter lehnt Contao 4.13
			// „act=toggle“ mit einer AccessDeniedException ab
			'toggle'                  => true,
			'eval'                    => array
			(
				'tl_class'            => 'w50',
				'isBoolean'           => true
			),
			'sql'                     => "char(1) NOT NULL default '1'"
		),
	),
);

/**
 * Rückrufe der Tabelle tl_mailkonten.
 *
 * Die Klasse wird von Contao über System::importStatic() erzeugt und trägt
 * deshalb bewusst den Namen der Tabelle statt eines Namensraums.
 */
class tl_mailkonten extends Backend
{
	/**
	 * Setzt ein Datum im MultiColumnWizard auf Mitternacht.
	 *
	 * Der Datumswähler des Wizards liefert nur ein Datum ohne Uhrzeit. Wird der
	 * Zeitstempel mit einer Uhrzeit gespeichert, springt die Anzeige je nach
	 * Zeitzone auf den Vortag. Deshalb wird beim Laden auf 00:00 Uhr gerundet.
	 *
	 * @param mixed $value Gespeicherter Zeitstempel; kommt aus der Datenbank
	 *                     regelmäßig als Zeichenkette an
	 *
	 * @return int|string Zeitstempel um Mitternacht. Bei einem leeren Wert
	 *                    kommt der leere String zurück und nicht die 0 — sonst
	 *                    zeigte das Feld für jedes ungepflegte Datum den
	 *                    01.01.1970 an
	 */
	public function loadDate($value)
	{
		if (!$value)
		{
			return '';
		}

		// (int) ist nötig, weil date() unter PHP 8 keine Zeichenkette mehr als
		// Zeitstempel annimmt
		return strtotime(date('Y-m-d', (int) $value) . ' 00:00:00');
	}

	/**
	 * Füllt die Sammelspalten der Listenansicht.
	 *
	 * Die Spalten POP3/IMAP, Weiterleitung und Alias zeigen nicht den nackten
	 * Ja-Nein-Wert, sondern die dahinterliegenden Angaben: Postfachgröße samt
	 * Auslastung sowie die Ziel- und Alias-Adressen. Lange Adressen werden
	 * gekürzt und stehen vollständig im Tooltip.
	 *
	 * @param array         $row   Datensatz des Kontos
	 * @param string        $label Vorbereitete Beschriftung; bei showColumns
	 *                             ohne Bedeutung
	 * @param DataContainer $dc    Data Container der Liste
	 * @param array         $args  Spaltenwerte in der Reihenfolge von
	 *                             list.label.fields
	 *
	 * @return array Die Spaltenwerte mit den ersetzten Sammelspalten. Der
	 *               Rückgabewert muss ein Array sein, weil die Liste mit
	 *               showColumns arbeitet
	 */
	public function getRecord($row, $label, DataContainer $dc, $args)
	{
		// POP3/IMAP: Postfachgröße und Auslastung
		$args[1] = !empty($row['pop3']) ? $row['mailbox_groesse'] . ' MB (' . $row['auslastung'] . '%)' : '-';

		// Weiterleitungen: Zieladressen
		$args[2] = !empty($row['forward']) ? self::adressliste($row['forwarder'], 'forwarder_email') : '-';

		// Aliasse: Alias-Adressen
		$args[3] = !empty($row['alias']) ? self::adressliste($row['aliase'], 'aliase_email') : '-';

		return $args;
	}

	/**
	 * Baut aus den Zeilen eines MultiColumnWizards eine Adressliste.
	 *
	 * @param mixed  $daten      Serialisierter Inhalt des Wizard-Feldes
	 * @param string $schluessel Name der Spalte mit der E-Mail-Adresse
	 *
	 * @return string Untereinander stehende, gekürzte Adressen mit der
	 *                vollständigen Adresse im Tooltip. Ist nichts gepflegt,
	 *                kommt „-“ zurück
	 */
	private static function adressliste($daten, string $schluessel): string
	{
		$adressen = array();

		// deserialize() statt unserialize(): Contao speichert leere Wizards als
		// NULL, worauf unserialize() unter PHP 8 mit einer Warnung und false
		// antwortet — das anschließende foreach brach den Seitenaufbau ab
		foreach (StringUtil::deserialize($daten, true) as $zeile)
		{
			if (empty($zeile[$schluessel]))
			{
				continue;
			}

			// Erst kürzen, dann maskieren: andernfalls könnte die Kürzung
			// mitten in einer HTML-Entität wie &amp; landen
			$adresse = (string) $zeile[$schluessel];

			$adressen[] = '<span title="' . StringUtil::specialchars($adresse) . '">'
				. StringUtil::specialchars(StringUtil::substr($adresse, 16)) . '</span>';
		}

		return $adressen ? implode('<br>', $adressen) : '-';
	}

	/**
	 * Gibt die Adresse der Listenverwaltung als anklickbaren Link aus.
	 *
	 * Das Feld url enthält nur den Text der Adresse. Damit man nicht jedes Mal
	 * kopieren und einfügen muss, steht darunter derselbe Wert als Link.
	 *
	 * @param DataContainer $dc Data Container des Formulars; liefert über
	 *                          activeRecord den bearbeiteten Datensatz
	 *
	 * @return string HTML des Feldes. Ist keine Adresse gepflegt, kommt ein
	 *                leeres Platzhalter-Feld zurück. Die frühere Fassung baute
	 *                das Platzhalter-HTML zwar zusammen, wies es aber keiner
	 *                Variablen zu und gab anschließend eine undefinierte
	 *                Variable zurück — unter PHP 8 eine Warnung bei jedem
	 *                Aufruf des Formulars ohne Adresse
	 */
	public function getURL(DataContainer $dc)
	{
		$url = $dc->activeRecord->url ?? '';

		if (!$url)
		{
			return '<div class="w50 widget"></div>';
		}

		$url = StringUtil::specialchars((string) $url);

		return '<h3><label for="ctrl_urlLinked">' . ($GLOBALS['TL_LANG']['tl_mailkonten']['urlLinked'][0] ?? '') . '</label></h3>
			<div class="w50 widget">
			<div class="tl_text" style="border:0;"><span>&raquo; </span><a href="' . $url . '" target="_blank" rel="noreferrer noopener">Listenverwaltung aufrufen</a></div>
			<p class="tl_help tl_tip" title="" style="margin-left:7px;">' . ($GLOBALS['TL_LANG']['tl_mailkonten']['urlLinked'][1] ?? '') . '</p>
			</div>';
	}
}
