<?php

/**
* Contao Open Source CMS
*
* Copyright (c) 2005-2014 Leo Feyer
*
 */


/**
 * Table tl_mailkonten 
 */
$GLOBALS['TL_DCA']['tl_mailkonten'] = array
(
	// Config
	'config' => array
	(
		'dataContainer'               => 'Table',
		'enableVersioning'            => true,
		'switchToEdit'                => true,
		'markAsCopy'                  => 'email',
		'sql' => array
		(
			'keys' => array
			(
				'id'             => 'primary',
				'email'          => 'index',
				'email'          => 'unique',
			)
		)
	),
	// List
	'list' => array
	(
		'sorting' => array
		(
			'mode'                    => 2,
			'fields'                  => array('email'),
			'flag'                    => 11,
			'panelLayout'             => 'filter;search,sort,limit',
		),
		'label' => array
		(
			'fields'                  => array('email', 'pop3', 'forward', 'alias', 'mailinglist', 'info', 'tstamp'),
			'showColumns'             => true,
			'label_callback'          => array('tl_mailkonten','getRecord')
		),
		'global_operations' => array
		(
			'export' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['export'],
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
				'icon'                => 'edit.gif'
			),
			'copy' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_mailkonten']['copy'],
				'href'                => 'act=copy',
				'icon'                => 'copy.gif',
			),
			'delete' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_mailkonten']['delete'],
				'href'                => 'act=delete',
				'icon'                => 'delete.gif',
				'attributes'          => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"'
			),
			'toggle' => array
			(
				'label'                => &$GLOBALS['TL_LANG']['tl_mailkonten']['toggle'],
				'attributes'           => 'onclick="Backend.getScrollOffset()"',
				'haste_ajax_operation' => array
				(
					'field'            => 'published',
					'options'          => array
					(
						array('value' => '', 'icon' => 'invisible.svg'),
						array('value' => '1', 'icon' => 'visible.svg'),
					),
				),
			),
			'show' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_mailkonten']['show'],
				'href'                => 'act=show',
				'icon'                => 'show.gif'
			)
		)
	),
	// Palettes
	'palettes' => array
	(
		'__selector__'                => array('pop3', 'forward', 'alias', 'mailinglist'),
		'default'                     => '{mail_legend},email,info;{pop3_legend},pop3;{forward_legend},forward;{alias_legend:hide},alias;{responder_legend:hide},auto_responder;{mailingliste_legend},mailinglist;{history_legend:hide},history;{info_legend:hide},anmerkungen;{publish_legend},published'
	),

	// Subpalettes
	'subpalettes' => array
	(
		'pop3'                        => 'inhaber,passwort,mailbox_groesse,auslastung,spam,leerung,smtp_server,smtp_port,pop3_server,pop3_port,imap_server,imap_port,backup,checkup',
		'forward'                     => 'forwarder,weiterleitungen',
		'alias'                       => 'aliase,alias_adressen',
		'mailinglist'                 => 'url,urlLinked,mlPasswort,mailingliste',
	),

	// Base fields in table tl_mailkonten
	'fields' => array
	(
		'id' => array
		(
			'sql'                     => "int(10) unsigned NOT NULL auto_increment"
		),
		'tstamp' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_mailkonten']['tstamp'],
			'flag'                    => 5,
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
			'filter'                  => true,
			'eval'                    => array
			(
				'submitOnChange'      => true,
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
			'default'                 => false,
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
				'tl_class'            => 'w50'
			),
			'sql'                     => "int(5) unsigned NOT NULL default '993'"
		),
		'backup' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_mailkonten']['backup'],
			'inputType'               => 'checkbox',
			'exclude'                 => true,
			'default'                 => false,
			'filter'                  => true,
			'eval'                    => array
			(
				'tl_class'            => 'w50 m12',
				'isBoolean'           => true
			),
			'sql'                     => "char(1) NOT NULL default ''"
		),
		'checkup' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_mailkonten']['checkup'],
			'inputType'               => 'checkbox',
			'exclude'                 => true,
			'default'                 => false,
			'filter'                  => true,
			'eval'                    => array
			(
				'tl_class'            => 'w50 m12',
				'isBoolean'           => true
			),
			'sql'                     => "char(1) NOT NULL default ''"
		),
		'alias' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_mailkonten']['alias'],
			'inputType'               => 'checkbox',
			'filter'                  => true,
			'eval'                    => array
			(
				'submitOnChange'      => true,
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
			'filter'                  => true,
			'eval'                    => array
			(
				'submitOnChange'      => true,
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
						'eval'                  => array
						(
						)
					),
				)
			),
			'sql'                   => "blob NULL"
		),
		'mailinglist' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_mailkonten']['mailinglist'],
			'inputType'               => 'checkbox',
			'filter'                  => true,
			'eval'                    => array
			(
				'submitOnChange'      => true,
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
				'mandatory'           => false,
				'tl_class'            => 'w50'
			)
		),
		// Gibt einen Link zum LiMS-Leitfaden aus
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
			'default'                 => false,
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
			'default'                 => true,
			'filter'                  => true,
			'eval'                    => array
			(
				'tl_class'            => 'w50',
				'isBoolean'           => true
			),
			'sql'                     => "char(1) NOT NULL default '1'"
		),
	),
);

class tl_mailkonten extends \Backend
{

	/**
	 * Set the timestamp to 00:00:00 (see #26)
	 *
	 * @param integer $value
	 *
	 * @return integer
	 */
	public function loadDate($value)
	{
		if($value) return strtotime(date('Y-m-d', $value) . ' 00:00:00');
		return '';
	}

	/**
	 * Add an image to each record
	 * @param array
	 * @param string
	 * @param DataContainer
	 * @param array
	 * @return string
	 */
	public function getRecord($row, $label, \DataContainer $dc, $args)
	{
		// POP3/IMAP ergänzen
		if($row['pop3'])
		{
			$args[1] = $row['mailbox_groesse'].' MB ('.$row['auslastung'].'%)';
		}
		else
		{
			$args[1] = '-';
		}

		// Weiterleitungen ergänzen
		if($row['forward'])
		{
			$forwarder = unserialize($row['forwarder']);
			$daten = array();
			foreach($forwarder as $item)
			{
				$daten[] = '<span title="'.$item['forwarder_email'].'">'.substr($item['forwarder_email'], 0, 16).'</span>';
			}
			$args[2] = implode('<br>', $daten);
		}
		else
		{
			$args[2] = '-';
		}

		// Alias ergänzen
		if($row['alias'])
		{
			$aliase = unserialize($row['aliase']);
			$daten = array();
			foreach($aliase as $item)
			{
				$daten[] = '<span title="'.$item['aliase_email'].'">'.substr($item['aliase_email'], 0, 16).'</span>';
			}
			$args[3] = implode('<br>', $daten);
		}
		else
		{
			$args[3] = '-';
		}

		return $args;

	}

	public function getURL(\DataContainer $dc)
	{
		if($dc->activeRecord->url)
		{
			$text = 
			'<h3><label for="ctrl_urlLinked">'.$GLOBALS['TL_LANG']['tl_mailkonten']['urlLinked'][0].'</label></h3>
			<div class="w50 widget">
			<div class="tl_text" style="border:0;"><span>&raquo; </span><a style="" href="'.$dc->activeRecord->url.'" target="_blank">Listenverwaltung aufrufen</a></div>
			<p class="tl_help tl_tip" title="" style="margin-left:7px;">'.$GLOBALS['TL_LANG']['tl_mailkonten']['urlLinked'][1].'</p>
			</div>';
		}
		else
		{
			'<div class="w50 widget"></div>';
		}

		return $text;
	}

}