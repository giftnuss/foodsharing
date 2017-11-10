<?php

namespace Foodsharing\Lib\View;

class vCore
{
	public static $ids = array();

	public function id($id)
	{
		$tmp_id = $id;
		$i = 0;
		while (isset(self::$ids[$tmp_id])) {
			++$i;
			$tmp_id = $id . '_' . $i;
		}
		self::$ids[$tmp_id] = true;

		return $tmp_id;
	}
}
