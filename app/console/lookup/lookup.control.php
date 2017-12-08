<?php

class LookupControl extends ConsoleControl
{
	private $model;

	public function __construct()
	{
		$this->model = new LookupModel();
		parent::__construct();
	}

	public function lookupFile()
	{
		global $argv;
		$filename = $argv[3];
		if (!file_exists($filename)) {
			error('Could not load file ' . $filename);
			die();
		}

		info('Loading emails from ' . $filename);
		$csv = array_map('str_getcsv', file($filename));
		foreach ($csv as $row) {
			$email = $row[0];
			$fs = $this->model->getFoodsaverByEmail($email);
			echo $fs['id'] . ',' . $fs['last_login'] . ',' . implode(',', $row) . "\n";
		}
	}
}
