<?php 
require_once 'config.inc.php';
require_once 'lib/Session.php';
require_once 'lang/DE/de.php';
require_once 'lib/func.inc.php';
require_once 'lib/db.class.php';
require_once 'lib/Foodsaver.class.php';
require_once 'lib/Manual.class.php';
require_once 'lib/handle.inc.php';
require_once 'lib/xhr.inc.php';
require_once 'lib/xhr.view.inc.php';
require_once 'lib/view.inc.php';

S::init();

$action = $_GET['f'];

//if(loggedIn())
if(true)
{
	$db->updateActivity();
	if(isset($_GET['f']))
	{
		$func = 'xhr_'.$action;
		if(function_exists($func))
		{
			echo $func($_GET);
		}
		elseif(isOrgaTeam())
		{
			echo(
'function '.$func.'($data)
{
	global $db;
	return $db->update(\'
		UPDATE `'.str_replace('xhr_update_', '', $func).'`
		SET 	');
			$where = 'WHERE 	`id` = \'.$this->intval().\'';
			if(isset($_GET['id']))
			{
				$where = 'WHERE 	`id` = \'.$this->intval(data[\'id\']).\' ';
				unset($_GET['id']);
			}
			foreach ($_GET as $field => $value)
			{
				echo '`'.$field.'` = $db->strval(\'.$data[\''.$field.'\'].\'), 
			';
			}
			echo '
				'.$where.'
		\');
}';
		}
	}
}