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

		if($records->numRows)
		{
			while($records->next())
			{
				if($records->checkup)
				{
					$imap = new \Schachbulle\ContaoMailkontenBundle\Classes\IMAP($records->imap_server,$records->imap_port,$records->email,$records->passwort);

					//$emails = $imap->getEmails();
					$bodies = [];
					echo $records->email.' | '.$imap->getNumber().' E-Mails | ';
					// Postfachgröße ermitteln
					$quota = $imap->getQuota();
					if($quota)
					{
						echo $quota['usage'].' kB belegt | '.$quota['limit'].' kB Quota | '.($quota['usage']/$quota['limit']*100).'% Auslastung<br>'."\n";
					}
					else
					{
						echo 'Postfachgröße nicht ermittelbar<br>'."\n";
					}
					
					//foreach($emails as $email)
					//{
					//	$bodies[] = $imap->getEmailBody($email->uid);
					//}
					$imap->close();

					//print_r($bodies);
				}
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
