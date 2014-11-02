<?php
/**
 * Mail class to retrieve mails to send and hadly it asynchron
 * 
 * @author ra
 */
class SocketMail
{
	private $data;
	
	public function __construct()
	{	
		$this->data = new SocketData();
		
		$this->data->setType('email');
		
		$this->data->set('recipients', array());
		$this->data->set('attachments', array());
		$this->data->set('from', array(DEFAULT_EMAIL,DEFAULT_EMAIL_NAME));
		$this->data->set('body', '');
		$this->data->set('html', false);
		$this->data->set('subject', '');
		$this->data->set('identifier', '');
	}
	
	public function addRecipient($email,$name = null)
	{
		$this->data->append('recipients', array($email,$name));
	}
	
	public function setFrom($email,$name = null)
	{
		$this->data->set('from', array($email,$name));
	}
	
	public function setBody($body)
	{
		$body = str_replace(array('<br>','<br />','<br/>','<p>','</p>','</ p>'),"\n",$body);
		$body = strip_tags($body);
		$this->data->set('body', $body);
	}
	
	public function setSubject($subject)
	{
		$this->data->set('subject', strip_tags($subject));
	}
	
	public function setHtmlBody($html)
	{
		$this->data->set('html',$html);
	}
	
	public function addAttachment($file,$name = null)
	{
		if($name == null) 
		{
			$name = explode('/',$file);
			$name = end($name);
		}
		
		if(file_exists($file))
		{
			$this->data->append('attachments',array($file,$name));
		}
	}
	
	public function clearRecipients()
	{
		return $this->data->set('recipients',array());
	}
	
	public function setIdentifier($identifier)
	{
		$this->data->set('identifier',$identifier);
	}
	
	public function toArray()
	{		
		return $this->data->toArray();
	}
}