<?php

namespace Schachbulle\ContaoMailkontenBundle\Classes;

/**
 * Class dsb_trainerlizenzExport
  */
class Export extends \Backend
{

	/**
	 * Funktion exportTrainer_XLS
	 * @param object
	 * @return string
	 */

	public function exportText(\DataContainer $dc)
	{
		if ($this->Input->get('key') != 'export')
		{
			return '';
		}

		$arrExport = self::getRecords($dc); // Lizenzen auslesen

		$dateiname = 'Mailkonten_'.date('Ymd-His').'.txt';

		//// Redirect output to a client’s web browser (Xls)
		header('Content-Type: text/plain');
		header('Content-Disposition: attachment;filename="'.$dateiname.'"');
		// Daten schreiben
		foreach($arrExport as $item)
		{
			echo 'Konto: '.$item['email']."\r\n";
			echo '    Info: '.$item['info']."\r\n";
			if($item['pop3'])
			{
				echo '    POP3/IMAP: Ja'."\r\n";
				echo '        Inhaber: '.$item['inhaber']."\r\n";
				//echo '        Passwort: '.$item['passwort']."\r\n";
				echo '        Mailbox-Größe: '.$item['mailbox_groesse'].' MB ('.$item['auslastung'].'% belegt)'."\r\n";
				echo '        Spam-Filter: '.$item['spam']."\r\n";
				echo '        Leerung: '.$item['leerung']."\r\n";
			}
			if($item['forward'])
			{
				echo '    Weiterleitungen: Ja'."\r\n";
				$arr = unserialize($item['forwarder']);
				foreach($arr as $data)
				{
					echo '        Adresse: '.$data['forwarder_email']."\r\n";
				}
			}
			if($item['alias'])
			{
				echo '    Aliasse: Ja'."\r\n";
				$arr = unserialize($item['aliase']);
				foreach($arr as $data)
				{
					echo '        Adresse: '.$data['aliase_email']."\r\n";
				}
			}
			if($item['mailinglist'])
			{
				echo '    Mailingliste: Ja'."\r\n";
			}
			echo "\r\n";
		}
		//print_r($arrExport);
		
		//header('Cache-Control: max-age=0');
		//// If you're serving to IE 9, then the following may be needed
		//header('Cache-Control: max-age=1');
        //
		//// If you're serving to IE over SSL, then the following may be needed
		//header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
		//header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
		//header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
		//header('Pragma: public'); // HTTP/1.0

	}

	public function getRecords(\DataContainer $dc)
	{
		// Liest die Datensätze der Lizenzverwaltung in ein Array

		// Suchbegriff in aktueller Ansicht laden
		$search = $dc->Session->get('search');
		$search = $search[$dc->table]; // Das Array enthält field und value
		//if($search['field']) $sql = " WHERE ".$search['field']." LIKE '%%".$search['value']."%%'"; // findet auch Umlaute, Suche nach "ba" findet auch "bä"
		if($search['field'] && $search['value']) $sql = " WHERE LOWER(CAST(".$search['field']." AS CHAR)) REGEXP LOWER('".$search['value']."')"; // Contao-Standard, ohne Umlaute, Suche nach "ba" findet nicht "bä"
		else $sql = '';

		// Filter in aktueller Ansicht laden. Beispiel mit Spezialfilter (tli_filter):
		//
		// [filter] => Array
		//       (
		//           [tl_lizenzverwaltungFilter] => Array
		//               (
		//                   [tli_filter] => V2
		//               )
		// 
		//           [tl_lizenzverwaltung] => Array
		//               (
		//                   [limit] => 0,30
		//                   [geschlecht] => w
		//               )
		// 
		//       )
		$filter = $dc->Session->get('filter');
		$filter = $filter[$dc->table]; // Das Array enthält limit (Wert meistens = 0,30) und alle Feldnamen mit den Werten
		foreach($filter as $key => $value)
		{
			if($key != 'limit')
			{
				($sql) ? $sql .= ' AND' : $sql = ' WHERE';
				$sql .= " ".$key." = '".$value."'";
			}
		}

		$sql = "SELECT * FROM tl_mailkonten".$sql.' ORDER BY email ASC';
		//echo $sql."\r\n\r\n";
		//log_message('Excel-Export mit: '.$sql, 'lizenzverwaltung.log');
		// Datensätze laden
		$records = \Database::getInstance()->prepare($sql)
		                                   ->execute();

		// Datensätze umwandeln
		$arrExport = array();
		if($records->numRows)
		{
			while($records->next()) 
			{
				$arrExport[] = array
				(
					'email'           => $records->email,
					'info'            => $records->info,
					'pop3'            => $records->pop3,
					'inhaber'         => $records->inhaber,
					'passwort'        => $records->passwort,
					'mailbox_groesse' => $records->mailbox_groesse,
					'auslastung'      => $records->auslastung,
					'spam'            => $records->spam,
					'leerung'         => $records->leerung,
					'forward'         => $records->forward,
					'forwarder'       => $records->forwarder,
					'alias'           => $records->alias,
					'aliase'          => $records->aliase,
					'auto_responder'  => $records->auto_responder,
					'alias'           => $records->alias,
					'mailinglist'     => $records->mailinglist,
					'url'             => $records->url,
					'mlPasswort'      => $records->mlPasswort,
					'mailingliste'    => $records->mailingliste,
					'history'         => $records->history,
					'anmerkungen'     => $records->anmerkungen,
					'published'       => $records->published,
				);
			}
		}
		return $arrExport;
	}

}
