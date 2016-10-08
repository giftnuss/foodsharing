<?php 
class CoreXhr extends Control
{
	public function __construct()
	{
		$this->model = new Model();
		$this->view = new View();

		parent::__construct();
	}

	protected function fail_permissions()
	{
		return array(
				'status' => 1,
				'script' => 'pulseError("Du hast leider nicht die notwendigen Berechtigungen für diesen Vorgang.");'
		);
	}

	protected function success()
	{
		return array(
			'status' => 1
		);
	}
	
}
