<?php
class SlaveDb
{
	static $mysqli = false;
	private $jobs;

	public function __construct()
	{
		$this->jobs = array();
	}

	public function addJob($job)
	{
		$this->jobs[] = $job->toArray();
	}

	public function send()
	{
		SlaveDb::connect();
		SlaveDb::queue($this->jobs);
		SlaveDb::disconnect();
	}

	public static function connect()
	{		
		if(SlaveDb::$mysqli === false)
		{
			SlaveDb::$mysqli = new mysqli();
			SlaveDb::$mysqli->connect(DB_SLAVE_HOST, DB_SLAVE_USER, DB_SLAVE_PASS, DB_SLAVE_DB);
			SlaveDb::$mysqli->query("SET NAMES 'utf8'");
			SlaveDb::$mysqli->query("SET CHARACTER SET 'utf8'");
		}
	}

	public static function disconnect()
	{
		@SlaveDb::$mysqli->close();
		SlaveDb::$mysqli = false;
	}

	public static function insert($sql)
	{
		if($res = SlaveDb::sql($sql))
		{
			return SlaveDb::$mysqli->insert_id;
		}
		else
		{
			return false;
		}
	}

	public static function safe($string)
	{
		return SlaveDb::$mysqli->escape_string($string);
	}

	public static function strVal($string)
	{
		return '"'.SlaveDb::safe($string).'"';
	}

	public static function sql($query)
	{
		return SlaveDb::$mysqli->query($query);
	}

	/**
	 * public static method to add an message qeue entry fpr slave server
	 *
	 * @param array $list ($type, $data, $files = false, $identifier = false, $status = 0)
	 */
	public static function queue($list)
	{

		$values = array();

		foreach ($list as $l)
		{
			$type = $l['type'];
			$data = $l['data'];
				
			$identifier = 'NULL';
			if(isset($l['identifier']))
			{
				$identifier = SlaveDb::strVal($l['identifier']);
			}
				
			$status = 0;
			if(isset($l['status']))
			{
				$status = (int)$l['status'];
			}
				
			$files = 'NULL';
			if (isset($l['files']) && is_array($l['files']))
			{
				$files = SlaveDb::strVal(serialize($l['files']));
			}
				
			$values[] = '('.(int)fsId().','.(int)$status.',NOW(),'.(int)$type.','.SlaveDb::strVal(serialize($data)).','.$files.','.$identifier.')';
		}



		return SlaveDb::insert('INSERT INTO `'.PREFIX.'queue`(`sender_id`, `status`, `time`, `type`, `data`, `files`,`identifier`) VALUES '.implode(',',$values));
	}
}