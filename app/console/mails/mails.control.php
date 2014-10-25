<?php
class MailsControl extends ConsoleControl
{		
	static $smtp;
	
	public function __construct()
	{		
		MailsControl::$smtp = false;
	}
	
	/**
	 * Method to start socket server the server will listen for mail jobs
	 */
	private function socketServer()
	{
		$server = new SocketServer();
		
		$server->addHandler('email',$this,'handleEmail');
		
		$server->start();
	}
	
	/**
	 * default method starts the server
	 */
	public function index()
	{
		$this->socketServer();
	}
	
	/**
	 * public method to stop socket server friendly
	 * accessable over commandline with: php run.php mails stop
	 */
	public function stop()
	{
		$client = new SocketClient();
		
		$client->serverSignal('close');
	}
	
	public static function handleEmail($data)
	{
		$data = $data->getData();
		info('send email ...');
		$email = new fEmail();
		$email->setFromEmail($data['from'][0],$data['from'][1]);
		$email->setSubject($data['subject']);
		$email->setHTMLBody($data['html']);
		$email->setBody($data['body']);
		
		if(!empty($data['attachments']))
		{
			foreach ($data['attachments'] as $a)
			{
				$file = new fFile($a[0]);
				
				// only files smaller 10 MB
				if($file->getSize() < 1310720)
				{
					$email->addAttachment($file,$a[1]);
				}
			}
		}
		
		foreach ($data['recipients'] as $r)
		{
			$email->addRecipient($r[0],$r[1]);
		}
		
		if(MailsControl::$smtp === false)
		{
			MailsControl::smtpReconnect();
		}
		
		$max_try = 5;
		$sended = false;
		while(!$sended)
		{
			$max_try--;
			try {
				$email->send(MailsControl::$smtp);
				success('OK');
				
				// remove atachements from temp folder
				if(!empty($data['attachments']))
				{
					foreach ($data['attachments'] as $a)
					{
						@unlink($a[0]);
					}
				}
				
				return true;
				$sended = true;
				break;
			} 
			catch (Exception $e) {
				MailsControl::smtpReconnect();
				error('email sending error: ' . $e->getMessage());
			}
			
			if($max_try == 0)
			{
				return false;
				break;
			}
		}
		
		return true;
	}
	
	/**
	 * checks current status and renew the connection to smtp server
	 */
	public static function smtpReconnect()
	{
		try
		{
			if(MailsControl::$smtp !== false)
			{
				@MailsControl::$smtp->close();
			}
	
			MailsControl::$smtp = new fSMTP(SMTP_HOST,SMTP_PORT);
			MailsControl::$smtp->authenticate(SMTP_USER, SMTP_PASS);
	
			return true;
		}
		catch (Exception $e)
		{
			error($e->getMessage());
			return false;
		}
	
		return true;
	}
}
