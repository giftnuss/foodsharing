<?php

define('MSG_TYPE_EMAIL',1);
define('MSG_TYPE_IMAGE',2);
define('MSG_TYPE_PUSH_ANDROID',3);
define('MSG_TYPE_PUSH_IOS',4);

class SlaveMail implements SlaveInterface
{
	private $recipients;
	private $files;
	private $mailObj;
	private $queue;
	private $identifier;
	
	public function __construct()
	{		
		$this->mailObj = new fEmail();
		$this->files = array();
		$this->queue = array();
		$this->identifier = false;
		
	}
	
	public function addRecipient($email,$name = null)
	{
		$this->mailObj->addRecipient($email,$name);
	}
	
	public function setFrom($email,$name = null)
	{
		$this->mailObj->setFromEmail($email,$name);
	}
	
	public function setBody($body)
	{
		$this->mailObj->setBody($body);
	}
	
	public function setSubject($subject)
	{
		$this->mailObj->setSubject($subject);
	}
	
	public function setHtmlBody($html)
	{
		$this->mailObj->setHTMLBody($html);
	}
	
	public function addAttachment($file,$name = false)
	{
		if($name === false)
		{
			$name = explode('/',$file);
			$name = end($name);
		}
		
		if(file_exists($file))
		{
			$this->files[] = array(
				'name' => $name,
				'path' => $file,
				'md5' => md5_file($file)
			);
		}
	}
	
	public function clearRecipients()
	{
		return $this->mailObj->clearRecipients();
	}
	
	public function toArray()
	{
		if(empty($this->files))
		{
			$this->files = false;
		}
		
		return array(
			'type' => MSG_TYPE_EMAIL,
			'data' => array('fEmail' => serialize($this->mailObj)),
			'files' => $this->files,
			'identifier' => $this->identifier
		);
	}
	
	public function setIdentifier($identifier)
	{
		$this->identifier = $identifier;
	}
}