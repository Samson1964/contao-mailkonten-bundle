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
		'sql' => array
		(
			'keys' => array
			(
				'id'             => 'primary',
				'email'          => 'index',
				'email'          => 'unique'
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
			'fields'                  => array('email', 'mailbox_groesse', 'art', 'leerung', 'auslastung', 'anmerkungen'),
			'showColumns'             => true
		),
		'global_operations' => array
		(
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
		'default'                     => '{mail_legend},email,inhaber,art,spam,auslastung,mailbox_groesse,passwort,leerung,auto_responder;{adresse_legend:hide},aliase,forwarder;{history_legend:hide},history;{adresse2_legend:hide},alias_adressen,weiterleitungen;{info_legend:hide},anmerkungen'
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
			'sql'                     => "int(10) unsigned NOT NULL default '0'"
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
				'tl_class'            => 'w50'
			)
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
				'rgxp'                => 'email',
				'tl_class'            => 'w50'
			),
			'sql'                     => "varchar(255) NOT NULL default ''"
		),
		'art' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_mailkonten']['art'],
			'exclude'                 => true,
			'filter'                  => true,
			'inputType'               => 'select',
			'options'                 => $GLOBALS['TL_LANG']['tl_mailkonten']['art_options'],
			'eval'                    => array
			(
				'includeBlankOption'  => true, 
				'tl_class'            => 'w50 clr'
			),
			'sql'                     => "char(1) NOT NULL default ''"
		),
		'spam' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_mailkonten']['spam'],
			'exclude'                 => true,
			'filter'                  => true,
			'inputType'               => 'select',
			'options'                 => $GLOBALS['TL_LANG']['tl_mailkonten']['spam_options'],
			'eval'                    => array
			(
				'includeBlankOption'  => true, 
				'tl_class'            => 'w50'
			),
			'sql'                     => "char(1) NOT NULL default ''"
		),
		'auslastung' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_mailkonten']['auslastung'],
			'exclude'                 => true,
			'filter'                  => true,
			'inputType'               => 'select',
			'options'                 => $GLOBALS['TL_LANG']['tl_mailkonten']['auslastung_options'],
			'eval'                    => array
			(
				'includeBlankOption'  => true, 
				'tl_class'            => 'w50'
			),
			'sql'                     => "int(3) unsigned NOT NULL default '0'"
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
							'style'             => 'width:100%;'
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
							'style'             => 'width:90%;'
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
							'style'             => 'width:100%;'
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
							'style'             => 'width:90%;'
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
		'leerung' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_mailkonten']['leerung'],
			'inputType'               => 'checkbox',
			'exclude'                 => true,
			'default'                 => false,
			'filter'                  => true,
			'eval'                    => array
			(
				'tl_class'            => 'w50 clr',
				'isBoolean'           => true
			),
			'sql'                     => "char(1) NOT NULL default ''"
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

}