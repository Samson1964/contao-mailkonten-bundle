<?php
/*
 * ====================================================================================
 * Klasse email_check
 * Greift auf E-Mail-Konten zu und prüft diese
 * ====================================================================================
 */

/**
 * Contao Open Source CMS, Copyright (C) 2005-2024 Leo Feyer
 */
namespace Schachbulle\ContaoMailkontenBundle\Email;

use Contao\Controller;

/**
 * Contao-System initialisieren
 */
define('TL_MODE', 'FE');
define('TL_SCRIPT', 'bundles/contaomailkonten/email_check.php');
require($_SERVER['DOCUMENT_ROOT'].'/../system/initialize.php');

class email_check
{

	public function __construct()
	{
	}

	public function run()
	{

		$records = \Database::getInstance()->prepare('SELECT * FROM tl_mailkonten WHERE published=?')
		                                   ->execute(1);

		$content = '';
		$konten = array();
		if($records->numRows)
		{
			while($records->next())
			{
				if($records->checkup)
				{
					$imap = new \Schachbulle\ContaoMailkontenBundle\Classes\IMAP($records->imap_server,$records->imap_port,$records->email,$records->passwort);

					//$emails = $imap->getEmails();
					$bodies = [];
					$content .= $records->email.' | '.$imap->getNumber().' E-Mails | ';
					// Postfachgröße ermitteln
					$quota = $imap->getQuota();
					if($quota)
					{
						$content .= $quota['usage'].' kB belegt | '.$quota['limit'].' kB Quota | '.(sprintf('%01.2f', $quota['usage']/$quota['limit']*100)).'% Auslastung<br>'."\n";
					}
					else
					{
						$content .= 'Postfachgröße nicht ermittelbar<br>'."\n";
					}
					
					// Daten sichern
					$konten[] = array
					(
						'email'   => $records->email, // E-Mail-Adresse
						'count'   => $imap->getNumber(), // Anzahl der E-Mails
						'usage'   => sprintf('%01.2f', $quota['usage']/1024), // Belegter Speicher in kB in MB umgerechnet
						'quota'   => sprintf('%01.2f', $quota['limit']/1024), // Gesamtspeicher in kB in MB umgerechnet
						'percent' => sprintf('%01.2f', $quota['usage']/$quota['limit']*100), // Belegter Speicherin %
					);
					
					//foreach($emails as $email)
					//{
					//	$bodies[] = $imap->getEmailBody($email->uid);
					//}
					$imap->close();

					//print_r($bodies);
				}
			}
			
			if(isset($GLOBALS['TL_CONFIG']['mailkonten_admin']) && $GLOBALS['TL_CONFIG']['mailkonten_admin'])
			{
				$content = '';
				$content .= '<table border="1" cellspacing="1">';
				$content .= '<tr>';
				$content .= '<th>Konto</th>';
				$content .= '<th>E-Mails</th>';
				$content .= '<th>MB belegt</th>';
				$content .= '<th>MB Limit</th>';
				$content .= '<th>% belegt</th>';
				$content .= '</tr>';
				foreach($konten as $item)
				{
					$content .= '<tr>';
					$content .= '<td>'.$item['email'].'</td>';
					$content .= '<td>'.$item['count'].'</td>';
					$content .= '<td>'.$item['usage'].'</td>';
					$content .= '<td>'.$item['quota'].'</td>';
					$content .= '<td style="'.($item['percent'] > 90 ? 'color:red; font-weight:bold;' : '').'">'.$item['percent'].'</td>';
					$content .= '</tr>';
				}
				$content .= '</table>';
				// Email versenden
				$objEmail = new \Email();
				$objEmail->from = 'webmaster@schachbund.de';
				$objEmail->fromName = 'Mailkonten';
				$objEmail->subject = 'Mailkonten-Checkup';
				$objEmail->html = $content;
				$objEmail->sendTo(array($GLOBALS['TL_CONFIG']['mailkonten_admin'])); 
			}
		}

		//$objEmail = new email_check();
		//$objEmail->run();
		//// open IMAP connection
		//$mail = imap_open('{sslin.de:993/imap/ssl/novalidate-cert}', 'website@schachbund.de', 'y3wwqx6kzX');
		//
		//// grab a list of all the mail headers
		//$headers = imap_headers($mail);
		//echo "<pre>";
		//print_r($headers);
		//echo "</pre>";
		//
		//// grab a header object for the last message in the mailbox
		//$last = imap_num_msg($mail);
		////$header = imap_header($mail, $last);
		//
		//// grab the body for the same message
		//$body = imap_body($mail, $last);
		//
		//// close the connection
		//imap_close($mail);
	}
}

/**
 * Controller instanzieren
 */
$email = new email_check();
$email->run();
