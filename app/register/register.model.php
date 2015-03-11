<?php
class RegisterModel extends Model
{
	function isIpBlock($ip)
	{
		return $this->qOne("SELECT COUNT(*) FROM fs_event_registration WHERE ip = '".$this->safe($ip)."' AND signup_date > CURRENT_TIMESTAMP - INTERVAL 1 MINUTE") != false;
	}

	function alreadyRegistered($email)
	{
		return $this->qOne("SELECT COUNT(*) FROM fs_event_registration WHERE email = '".$this->safe($email)."'") != false;
	}

	function register($fields)
	{
		$cols = array_keys($fields);
		$vals = array_values($fields);
		$vals = array_map(function($v) { if(is_array($v)) { return "'".$this->safe(implode(',', $v))."'"; } else { return "'".$this->safe($v)."'"; }}, $vals);
		$cols = array_map(function($v) { return '`'.$v.'`'; }, $cols);

		$this->insert("INSERT INTO fs_event_registration (".implode(',', $cols).") VALUES (".implode(',', $vals).")");
		return true;
	}
}
