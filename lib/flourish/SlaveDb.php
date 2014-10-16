<?php
class SlaveDb
{
	static $mysqli = false;
	static $memcache = false;
	static $connected = false;
	static $unique;
	static $counter;
	
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
		if(SlaveDb::$memcache === false)
		{
			SlaveDb::$memcache = new Memcached();
			SlaveDb::$memcache->addServer(MEM_SLAVE_HOST,MEM_SLAVE_PORT);
			
			SlaveDb::$counter = 0;
			SlaveDb::$unique = uniqid();
			
			if(SlaveDb::$memcache->get('slavejobcounter') === false)
			{
				SlaveDb::$memcache->set('slavejobcounter',0);
			}
		}
	}

	public static function disconnect()
	{
		/*
		@SlaveDb::$mysqli->close();
		SlaveDb::$mysqli = false;
		*/
	}
	
	public static function memJob($item)
	{
		SlaveDb::$counter++;
		SlaveDb::$memcache->add('slavejob:' . SlaveDb::$unique . ':' . SlaveDb::$counter, serialize($item));
	}
	
	/**
	 * public static method to add an message qeue entry fpr slave server
	 *
	 * @param array $list ($type, $data, $files = false, $identifier = false, $status = 0)
	 */
	public static function queue($list)
	{
		foreach ($list as $l)
		{
			$l['sender_id'] = (int)fsId();
			SlaveDb::memJob($l);
		}
		
		SlaveDb::$memcache->increment( 'slavejobcounter', count($list));

	}
}