<?php

namespace Schachbulle\ContaoMailkontenBundle\Classes;

class IMAP
{
	private $server = '';
	private $username = '';
	private $password = '';
	private $port = 143;
	private $type = 'IMAP';
	private $ssl = true;
	private $mailbox = '';
 
	/**
	 * @var resource $stream
	 */
	private $stream = NULL;
 
	public function __construct($hostname,$port,$username,$passwort,$typ = 'IMAP')
	{
		$this->server = $hostname;
		$this->port = $port;
		$this->type = $typ;
		$this->ssl = '/ssl';
		$this->username = $username;
		$this->password = $passwort;

		$this->mailbox = sprintf("{%s:%d/%s%s/novalidate-cert}INBOX", $this->server, $this->port, $this->type, $this->ssl);
		$this->stream = imap_open($this->mailbox, $this->username, $this->password);

	}

	/**
	 * Anzahl der E-Mails zurückgeben
	 */
	public function getNumber()
	{
		$anzahl = imap_status($this->stream, $this->mailbox, SA_ALL);
		if($anzahl) 
		{
			return $anzahl->messages;
		  //echo "Nachrichten:   " . $status->messages    . "<br />\n";
		  //echo "Neueste:     " . $status->recent      . "<br />\n";
		  //echo "Ungelesen:     " . $status->unseen      . "<br />\n";
		  //echo "UIDnext:    " . $status->uidnext     . "<br />\n";
		  //echo "UIDvalidity:" . $status->uidvalidity . "<br />\n";
		} 
		else 
		{
			echo "imap_status schlug fehl: " . imap_last_error() . "\n";
		}
	}

	/**
	 * Auslastung des Postfachs zurückgeben
	 * Geliefert wird folgendes Array:
	 * Array
	 * (
	 *     [usage] => 7374
	 *     [limit] => 2048000
	 *     [STORAGE] => Array
	 *         (
	 *             [usage] => 7374
	 *             [limit] => 2048000
	 *         )
	 * 
	 * )
	 */
	public function getQuota()
	{
		$quota = imap_get_quotaroot($this->stream, 'INBOX');
		if(is_array($quota))
		{
			return $quota;
		}
		return false;
	}

	public function getEmails($onlyUnread = false)
	{
		$MC = imap_check($this->stream);
		$emails = imap_fetch_overview($this->stream, "1:{$MC->Nmsgs}", 0);
 
		if($onlyUnread) // nur ungelesene E-Mails
		{
			foreach($emails as $key=>$email)
			{
				if($email->seen) // Email wurde bereits gelesen
				{
					unset($emails[$key]); // Email aus Liste entfernen
				}
			}
		}
 
		return $emails;
	}
 
	public function getEmailBody($uid)
	{
		$body = @imap_fetchbody($this->stream, $uid, 1);
		return imap_qprint($body);
	}
 
	/**
	 * @param string $uid
	 * @return bool|string
	 */
	public function getAttachments($uid)
	{
		$structure = imap_fetchstructure($this->stream, $uid);
 
		foreach($structure->parts as $key=>$part)
		{
			if(isset($part->disposition) && $part->disposition === 'attachment')
			{
				$attachment = imap_fetchbody($this->stream, $uid, $key+1, FT_INTERNAL);
				return str_replace('=0A=', '', $attachment); // Zeilenumbrueche
			}
		}
 
		return false;
	}
 
	public function close()
	{
		if($this->stream !== false)
		{
			imap_close($this->stream);
		}
	}
}


