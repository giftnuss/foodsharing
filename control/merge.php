<?php
if(isAdmin() && isset($_GET['fsid']))
{
	$db->dbLoginAs($_GET['fsid']);
	if(isset($_GET['rurl']) && strpos($_SERVER['HTTP_REFERER'], getSelf()) === false)
	{
		go($_SERVER['HTTP_REFERER']);
	}
}