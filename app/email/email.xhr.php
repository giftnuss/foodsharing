<?php 

class EmailXhr extends Control
{
	public function __construct()
	{
		$this->model = new EmailModel();
		$this->view = new EmailView();

		parent::__construct();
	}
	
	public function testmail()
	{
		if(!validEmail($_POST['email']))
		{
			return array(
				'status' => 1,
				'script' => 'pulseError("Mit der E-Mail Adresse stimmt etwas nicht!");'
			);
		}
		else
		{
			libmail(false, $_POST['email'], $_POST['subject'], $_POST['message']);
			return array(
				'status' => 1,
				'script' => 'pulseInfo("E-Mail wurde versendet!");'
			);
		}
	}
	
}

