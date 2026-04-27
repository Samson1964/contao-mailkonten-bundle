<?php

namespace Schachbulle\ContaoMailkontenBundle\Classes;

/**
 * Class dsb_trainerlizenzExport
  */
class Tree extends \Backend
{

	/**
	 * Funktion exportTrainer_XLS
	 * @param object
	 * @return string
	 */

	public function exportKonto(\DataContainer $dc)
	{
		if ($this->Input->get('key') != 'tree')
		{
			return '';
		}

		// Mailkonto laden
		$objKonto = \Database::getInstance()->prepare('SELECT * FROM tl_mailkonten WHERE id = ?')
		                                    ->execute(\Input::get('id'));

		// Mailkonto verarbeiten
		if($objKonto->numRows)
		{
			$dateiname = 'Mailkonto_'.$objKonto->email.'_'.date('Ymd-His').'.txt';

			//// Redirect output to a client’s web browser (Xls)
			header('Content-Type: text/plain');
			header('Content-Disposition: attachment;filename="'.$dateiname.'"');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Pragma: public');
			header('Expires: 0');

			// Daten schreiben
			// Dateihandle für Direktstream öffnen
			$fp = fopen("php://output",'w');
			fputs($fp,'Konto: '.$objKonto->email."\r\n");
			fputs($fp, str_repeat('=', (7 + strlen($objKonto->email)))."\r\n");
			
			// Weiterleitungen im aktuellen Konto auslesen
			$weiterleitungen = (array)unserialize($objKonto->forwarder);
			foreach($weiterleitungen as $weiterleitung)
			{
				fputs($fp, '├─ '.$weiterleitung['forwarder_email']."\r\n");
				$forwarder1 = self::LadeWeiterleitungen($weiterleitung['forwarder_email']);
				foreach($forwarder1 as $forward1)
				{
					fputs($fp, '│ ├─ '.$forward1."\r\n");
					$forwarder2 = self::LadeWeiterleitungen($forward1);
					foreach($forwarder2 as $forward2)
					{
						fputs($fp, '│ │ ├─ '.$forward2."\r\n");
						$forwarder3 = self::LadeWeiterleitungen($forward2);
						foreach($forwarder3 as $forward3)
						{
							fputs($fp, '│ │ │ ├─ '.$forward3."\r\n");
							$forwarder4 = self::LadeWeiterleitungen($forward3);
							foreach($forwarder4 as $forward4)
							{
								fputs($fp, '│ │ │ │ ├─ '.$forward4."\r\n");
								$forwarder5 = self::LadeWeiterleitungen($forward4);
							}
						}
					}
				}
			}
			fclose($fp);
			exit;
		}

		return '';
	}

	public function LadeWeiterleitungen($adresse)
	{
		$objKonto = \Database::getInstance()->prepare('SELECT * FROM tl_mailkonten WHERE email = ? AND published = ?')
		                                    ->execute($adresse, '1');
		$weiterleitungen = (array)unserialize($objKonto->forwarder);
		$arr = array();
		foreach($weiterleitungen as $weiterleitung)
		{
			if(isset($weiterleitung['forwarder_email'])) $arr[] = $weiterleitung['forwarder_email'];
		}
		return $arr;
	}
	
	public function Weiterleitung($adresse, $ebene, $fp)
	{
		static $ebene;
		// Untergeordnetes Mailkonto vorhanden?
		$objKonto = \Database::getInstance()->prepare('SELECT * FROM tl_mailkonten WHERE email = ? AND published = ?')
		                                    ->execute($adresse, '1');

		$weiterleitungen = (array)unserialize($objKonto->forwarder);

		if(count($weiterleitungen) > 0)
		{
			foreach($weiterleitungen as $weiterleitung)
			{
				if(isset($weiterleitung['forwarder_email']))
				{
					fputs($fp, $ebene.' - '.str_repeat('│ ', $ebene).'├─ '.$weiterleitung['forwarder_email']."\r\n");
					self::Weiterleitung($weiterleitung['forwarder_email'], $ebene++, $fp); // Weiterleitungen auf nächster Ebene suchen
				}
			}
		}

		$ebene--;
	}
}
