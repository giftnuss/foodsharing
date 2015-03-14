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

	function fsidIsRegistered($fsid)
	{
		return $this->qOne("SELECT COUNT(*) FROM fs_event_registration WHERE foodsaver_id = '$fsid'") != false;
	}

	function setValid($email)
	{
		return $this->update("UPDATE fs_event_registration SET emailvalid = 1 WHERE `email` = '".$this->safe($email)."'");
	}

	function prepare($fields, &$cols, &$vals)
	{
		$cols = array_keys($fields);
		$vals = array_values($fields);
		$vals = array_map(function($v) { if(is_array($v)) { return "'".$this->safe(implode(',', $v))."'"; } else { return "'".$this->safe($v)."'"; }}, $vals);
		$cols = array_map(function($v) { return '`'.$v.'`'; }, $cols);
	}

	function register($fields)
	{
		$cols = array();
		$vals = array();
		$this->prepare($fields, $cols, $vals);
		$this->insert("INSERT INTO fs_event_registration (".implode(',', $cols).") VALUES (".implode(',', $vals).")");
		return true;
	}

	function edit($fields, $email)
	{
		$cols = array();
		$vals = array();
		$this->prepare($fields, $cols, $vals);
		$data = array_combine($cols, $vals);
		$update = array();
		array_walk($data, function(&$v, $k) { $v = "$k = $v"; });
		$this->update("UPDATE fs_event_registration SET ".implode(',', $data)." WHERE `email` = '".$this->safe($email)."'");
	}



	function getRegistrations($fields, $singleMail = False)
	{
		$cols = array_keys($fields);
		$cols[] = 'emailvalid';
		$cols = array_map(function($v) { return '`'.$v.'`'; }, $cols);
		$where = "";
		if($singleMail) {
			$where = " WHERE `email` = '".$this->safe($singleMail)."'";
		}


		return $this->q("SELECT ".implode(',', $cols)." FROM fs_event_registration$where");
	}
}
