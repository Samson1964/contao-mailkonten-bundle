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
			'fields'                  => array('name', 'vorname', 'klasse', 'nr', 'ausbdat', 'prue_datum'),
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
			'toggle' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_mailkonten']['toggle'],
				'icon'                => 'visible.gif',
				'attributes'          => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleVisibility(this,%s)"',
				'button_callback'     => array('tl_mailkonten', 'toggleIcon')
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
		'default'                     => '{mail_legend},email,mailbox_groesse,passwort,art,leerung,spam,auslastung;{adresse_legend:hide},auto_responder,alias_adressen,weiterleitungen,anmerkungen;{publish_legend},published'
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
			'search'                  => true,
			'sorting'                 => true,
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
			'search'                  => true,
			'sorting'                 => true,
			'filter'                  => false,
			'eval'                    => array
			(
				'mandatory'           => false,
				'tl_class'            => 'w50'
			)
			'sql'                     => "int(10) unsigned NOT NULL default '0'"
		),
		'email' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_mailkonten']['email'],
			'inputType'               => 'text',
			'exclude'                 => true,
			'search'                  => true,
			'sorting'                 => true,
			'filter'                  => false,
			'sql'                     => "varchar(50) NOT NULL default ''",
			'eval'                    => array
			(
				'rgxp'                => 'email',
				'tl_class'            => 'w50'
			)
		),
		'art' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_mailkonten']['art'],
			'exclude'                 => true,
			'inputType'               => 'select',
			'options'                 => $GLOBALS['TL_LANG']['tl_mailkonten']['art_options'],
			'eval'                    => array
			(
				'includeBlankOption'  => true, 
				'tl_class'            => 'w50'
			),
			'sql'                     => "char(1) NOT NULL default ''"
		),
		'spam' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_mailkonten']['spam'],
			'exclude'                 => true,
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
			'inputType'               => 'select',
			'options'                 => $GLOBALS['TL_LANG']['tl_mailkonten']['auslastung_options'],
			'eval'                    => array
			(
				'includeBlankOption'  => true, 
				'tl_class'            => 'w50'
			),
			'sql'                     => "int(3) unsigned NOT NULL default '0'"
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
			'sql'                     => "char(1) NOT NULL default ''"
		),
	),
);

class tl_mailkonten extends \Backend
{

	public function toggleIcon($row, $href, $label, $title, $icon, $attributes)
	{
		$this->import('BackendUser', 'User');

		if (strlen($this->Input->get('tid')))
		{
			$this->toggleVisibility($this->Input->get('tid'), ($this->Input->get('state') == 0));
			$this->redirect($this->getReferer());
		}

		// Check permissions AFTER checking the tid, so hacking attempts are logged
		if (!$this->User->isAdmin && !$this->User->hasAccess('tl_mailkonten::published', 'alexf'))
		{
			return '';
		}

		$href .= '&amp;id='.$this->Input->get('id').'&amp;tid='.$row['id'].'&amp;state='.$row[''];

		if (!$row['published'])
		{
			$icon = 'invisible.gif';
		}

		return '<a href="'.$this->addToUrl($href).'" title="'.specialchars($title).'"'.$attributes.'>'.$this->generateImage($icon, $label).'</a> ';
	}

	public function toggleVisibility($intId, $blnPublished)
	{
		// Check permissions to publish
		if (!$this->User->isAdmin && !$this->User->hasAccess('tl_mailkonten::published', 'alexf'))
		{
			$this->log('Kein Zugriffsrecht für Aktivierung Datensatz ID "'.$intId.'"', 'tl_mailkonten toggleVisibility', TL_ERROR);
			$this->redirect('contao/main.php?act=error');
		}
		
		$this->createInitialVersion('tl_mailkonten', $intId);
		
		// Trigger the save_callback
		if (is_array($GLOBALS['TL_DCA']['tl_mailkonten']['fields']['published']['save_callback']))
		{
			foreach ($GLOBALS['TL_DCA']['tl_mailkonten']['fields']['published']['save_callback'] as $callback)
			{
				$this->import($callback[0]);
				$blnPublished = $this->$callback[0]->$callback[1]($blnPublished, $this);
			}
		}
		
		// Update the database
		$this->Database->prepare("UPDATE tl_mailkonten SET tstamp=". time() .", published='" . ($blnPublished ? '' : '1') . "' WHERE id=?")
					   ->execute($intId);
		$this->createNewVersion('tl_mailkonten', $intId);
	}

}