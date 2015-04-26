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
		$cols = array_merge(array('on_place'), array_keys($fields));
		$cols[] = 'emailvalid';
		$cols[] = 'admin_comment';
		$cols[] = 'id';
		$cols = array_map(function($v) { return '`'.$v.'`'; }, $cols);
		$where = "";
		if($singleMail) {
			$where = " WHERE `email` = '".$this->safe($singleMail)."'";
		}

		return $this->q("SELECT ".implode(',', $cols)." FROM fs_event_registration$where");
	}
	
	function getIDbyEmail($email)
	{
	    return $this->qOne("SELECT id FROM fs_event_registration WHERE `email` = '".$this->safe($email)."'");
	}
	
	function updateSeen($seen)
	{
	    $ids = implode(',', array_keys($seen));
	    return $this->update("UPDATE fs_event_registration SET on_place = 1 WHERE id IN ($ids)");
	}
	
	function updateComments($comments)
	{
	    foreach($comments as $id=>$comment)
	    {
	        $this->update("UPDATE fs_event_registration SET admin_comment = '".$this->safe($comment)."' WHERE id = ".intval($id));
	    }
	}

	function listWorkshops() {
		return $this->q("SELECT w.`id`, w.`name`, UNIX_TIMESTAMP(w.`start`) as start, w.`duration`, w.`name_en`, w.`allowed_attendants`, COUNT(rc.`id`) AS registrations, COALESCE(SUM(rc.`confirmed`), 0) AS attendants FROM fs_event_workshops w LEFT JOIN fs_event_workshop_registration rc ON w.id = rc.wid GROUP BY w.`id` ORDER BY `start`, `name`");
	}

	function updateWorkshopWish($uid, $wid, $wish) {
	    if(!$wid)
	    {
	        $wid = "0";
	    } else
	    {
	        $wid = intval($wid);
	    }
		$this->insert("INSERT INTO fs_event_workshop_registration (`uid`, `wid`, `wish`) VALUES ($uid, $wid, $wish) ON DUPLICATE KEY UPDATE `wid` = $wid, `confirmed` = 0");
	}
	
	function getWorkshopWishes($uid) {
	    return $this->q("SELECT `wish`, `wid` FROM fs_event_workshop_registration WHERE `uid` = $uid ORDER BY `wish`");
	}

	/* set confirmed state (uid, wid) pairs in the array
		ex: ((5, 3), (3, 4), (43, 3))
	 */
	function setConfirmedWorkshopWish($uw_pairs, $confirm) {
		$val = ($confirm ? 1 : 0);
		$where_arr = array_reduce($uw_pairs, function($c, $i) { $c[] = "(`uid` = ".$i[0]." AND `wid` = ".$i[1].")"; return $c; }, array());
		$where = implode(' OR ', $where_arr);

		$this->update("UPDATE fs_event_workshop_registration SET `confirmed` = $val WHERE $where");
	}
	
	function setConfirmedWorkshop($uid, $workshop, $confirm) {
	    $val = $confirm ? 1 : 0;
	    $this->update("UPDATE fs_event_workshop_registration SET `confirmed` = $val WHERE `uid` = ".intval($uid)." AND `wid` = ".intval($workshop));
	}

	function listWorkshopWishes() {
		$res = $this->q(
			"SELECT e.`name`, e.`id` as uid,
			GROUP_CONCAT(w.`wid` ORDER BY w.`wish`) as wids,
		GROUP_CONCAT(w.`confirmed` ORDER BY w.`wish`) as confirmed
		FROM fs_event_workshop_registration w
		LEFT JOIN fs_event_registration e ON w.uid = e.id
		GROUP BY w.uid
        ORDER BY e.id"
	);
		if($res) {
		    return $res;
		} else {
		    return array();
		}
	}


}
