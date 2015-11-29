<?php 
require_once 'config.inc.php';
require_once 'lib/func.inc.php';

require_once 'lib/db.class.php';
require_once 'lang/DE/de.php';
require_once 'lib/Manual.class.php';

/** Checks the validity of an API token
	@param $fs Foodsaver ID
	@param $key API token
	@return True or False depending on validity
 */
function check_api_token($fs, $key)
{
	global $db;
	$res = $db->qOne('SELECT COUNT(foodsaver_id) FROM '.PREFIX.'apitoken WHERE foodsaver_id = '.(int)$fs.' AND token="'.$db->safe($key).'"');
	return ($res == 1);
}

function api_generate_calendar($fs, $options)
{
	global $db;
	/* from https://gist.github.com/jakebellacera/635416 */
	header('Content-type: text/calendar; charset=utf-8');
	header('Content-Disposition: attachment; filename=calendar.ics');
	function dateToCal($timestamp) {
		return date('Ymd\THis\Z', $timestamp);
	}
	function dateToLocalCal($timestamp) {
		return date('Ymd\THis', $timestamp);
	}
	function escapeString($string) {
		return preg_replace('/([\,;])/','\\\$1', $string);
	}
	$fetches = $db->q('SELECT b.id, b.name, b.str, b.hsnr, b.plz, b.stadt, a.confirmed, UNIX_TIMESTAMP(a.`date`) AS date_ts FROM '.PREFIX.'abholer a INNER JOIN '.PREFIX.'betrieb b ON a.betrieb_id = b.id WHERE a.foodsaver_id = '.(int)$fs.' AND a.`date` > NOW() - INTERVAL 1 DAY');

	echo "BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:-//Foodsharing.de//NONSGML v1.0//EN\r\nCALSCALE:GREGORIAN\r\n";
	foreach($fetches as $f)
	{
		$datestart = dateToLocalCal($f['date_ts']);
		$dateend = dateToLocalCal($f['date_ts'] + 30 * 60);
		$uid = $f['id'].$f['date_ts'].'@foodsharing.de';
		$address = $f['str'].' '.$f['hsnr'].', '.$f['plz'].' '.$f['stadt'];
		$summary = $f['name'].' Abholung';
		if(!$f['confirmed'])
		{
			$summary .= ' (unbestätigt)';
		}
		$description = 'Foodsharing Abholung bei '.$f['name'];
		$uri = BASE_URL.'/?page=fsbetrieb&id='.$f['id'];
	// 3. Echo out the ics file's contents
		echo "BEGIN:VEVENT\r\nDTEND:";
		echo $dateend."\r\nUID:";
		echo $uid."\r\nDTSTAMP:";
		echo dateToCal(time())."\r\nLOCATION:";
		echo escapeString($address)."\r\nDESCRIPTION:";
		echo escapeString($description)."\r\nURL;VALUE=URI:";
		echo escapeString($uri)."\r\nSUMMARY:";
		echo escapeString($summary)."\r\nDTSTART:";
		echo $datestart."\r\nEND:VEVENT\r\n";
	}
	echo "END:VCALENDAR\r\n";
}

$db = new ManualDb();

$action = $_GET['f'];
$fs = $_GET['fs'];
$key = $_GET['key'];
$opts = $_GET['opts'];

if(!check_api_token($fs, $key)) {
	http_response_code(403);
	echo "Invalid access token!";
}
else
{
	switch($action)
	{
	case 'cal':
		api_generate_calendar($fs, $opts);
		break;
	}
}
