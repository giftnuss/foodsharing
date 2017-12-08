<?php

class LookupControl extends ConsoleControl
{
	private $model;

	public function __construct()
	{
		$this->model = new LookupModel();
		parent::__construct();
	}

	private function loadFile()
	{
		global $argv;
		$filename = $argv[3];
		if (!file_exists($filename)) {
			error('Could not load file ' . $filename);
			die();
		}

		info('Loading emails from ' . $filename);
		$csv = array_map('str_getcsv', file($filename));

		return $csv;
	}

	public function lookup()
	{
		$csv = $this->loadFile();
		foreach ($csv as $row) {
			$email = $row[0];
			$fs = $this->model->getFoodsaverByEmail($email);
			echo $fs['id'] . ',' . $fs['last_login'] . ',' . implode(',', $row) . "\n";
		}
	}

	public function deleteOldUsers()
	{
		$csv = $this->loadFile();
		foreach ($csv as $row) {
			$email = $row[0];
			$fs = $this->model->getFoodsaverByEmail($email);
			if (empty($fs)) {
				continue;
			}
			$date = new DateTime($fs['last_login']);
			$olderThan = new DateTime();
			$olderThan->sub(new DateInterval('P6M'));
			if ($date < $olderThan) {
				info('Deleted user ' . $fs['id']);
				$this->model->del_foodsaver($fs['id']);
			}
		}
	}
}
