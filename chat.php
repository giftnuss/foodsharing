<?php
require_once 'config.inc.php';
function __autoload($class_name)
{
	$first = substr($class_name,0,1);

	$folder = 'flourish';
	switch ($first)
	{
		case 'f' : $folder = 'flourish'; break;
		case 'v' : $folder = 'views'; break;
	}

	$file = $_SERVER['DOCUMENT_ROOT'] . '/lib/' . $folder . '/' . $class_name . '.php';

	if (file_exists($file)) {
		include $file;
		return;
	}
	else
	{
		debug('file not loadable: '.$file);
	}
}
require_once 'lib/Session.php';

S::init();

global $mysqli;
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_DB);

$mysqli->query("SET NAMES 'utf8'");
$mysqli->query("SET CHARACTER SET 'utf8'");
if(isset($_SESSION['client']['id']))
{
	if ($_GET['action'] == "chatheartbeat") { chatHeartbeat(); } 
	if ($_GET['action'] == "sendchat") { sendChat(); } 
	if ($_GET['action'] == "closechat") { closeChat(); } 
	if ($_GET['action'] == "startchatsession") { startChatSession(); } 
	
	if (!isset($_SESSION['chatHistory'])) {
		$_SESSION['chatHistory'] = array();	
	}
	
	if (!isset($_SESSION['openChatBoxes'])) {
		$_SESSION['openChatBoxes'] = array();	
	}
}
function chatHeartbeat() {
	global $mysqli;
	
	$items = '';

	$chatBoxes = array();
	if($res = $mysqli->query("SELECT fs.id AS `from`,fs.name,fs.photo,c.msg AS message,c.time AS sent FROM fs_message c,fs_foodsaver fs	WHERE c.sender_id = fs.id AND(c.recip_id = '".(int)$_SESSION['client']['id']."' AND recd = 0) ORDER BY sent ASC"))
	{
		while ($chat = $res->fetch_assoc()) {
			if (!isset($_SESSION['openChatBoxes'][$chat['from']]) && isset($_SESSION['chatHistory'][$chat['from']])) {
				$items = $_SESSION['chatHistory'][$chat['from']];
			}
			$chat['message'] = sanitize($chat['message']);

			$items .= <<<EOD
{"s":"0","f":"{$chat['from']}","m":"{$chat['message']}","n":"{$chat['name']}","p":"{$chat['photo']}","t":"{$chat['sent']}"},
EOD;
			if (!isset($_SESSION['chatHistory'][$chat['from']])) {
				$_SESSION['chatHistory'][$chat['from']] = '';
			}
	
			$_SESSION['chatHistory'][$chat['from']] .= <<<EOD
{"s":"0","f":"{$chat['from']}","m":"{$chat['message']}","n":"{$chat['name']}","p":"{$chat['photo']}"},
EOD;
			unset($_SESSION['tsChatBoxes'][$chat['from']]);
			$_SESSION['openChatBoxes'][$chat['from']] = $chat['sent'];
		}
	}
	if (!empty($_SESSION['openChatBoxes'])) {
	foreach ($_SESSION['openChatBoxes'] as $chatbox => $time) {
		if (!isset($_SESSION['tsChatBoxes'][$chatbox])) {
			$now = time()-strtotime($time);
			$time = date('c', strtotime($time));
			
			$message = $time.'';
			if ($now > 180) {
				$items .= <<<EOD
{"s":"2","f":"$chatbox","m":"{$message}"},
EOD;

	if (!isset($_SESSION['chatHistory'][$chatbox])) {
		$_SESSION['chatHistory'][$chatbox] = '';
	}

	$_SESSION['chatHistory'][$chatbox] .= <<<EOD
{"s":"2","f":"$chatbox","m":"{$message}"},
EOD;
			$_SESSION['tsChatBoxes'][$chatbox] = 1;
		}
		}
	}
};
	
	$query = $mysqli->query("SELECT * FROM fs_message WHERE recip_id = ".(int)$_SESSION['client']['id']." and recd = 0");
	$query = $query->fetch_assoc();
	if($query) {  // only update if there is something to update.
		$query = $mysqli->query("UPDATE fs_message SET recd = 1, unread = 0 WHERE recip_id = ".(int)$_SESSION['client']['id']." and recd = 0");
	}

	if ($items != '') {
		$items = substr($items, 0, -1);
	}
header('Content-type: application/json');
?>{"items":[<?php echo $items;?>]}<?php
			exit(0);
}

function chatBoxSession($chatbox) {
	
	$items = '';
	
	if (isset($_SESSION['chatHistory'][$chatbox])) {
		$items = $_SESSION['chatHistory'][$chatbox];
	}

	return $items;
}

function startChatSession() {
	$items = '';
	if (!empty($_SESSION['openChatBoxes'])) {
		foreach ($_SESSION['openChatBoxes'] as $chatbox => $void) {
			$items .= chatBoxSession($chatbox);
		}
	}


	if ($items != '') {
		$items = substr($items, 0, -1);
	}

header('Content-type: application/json');
?>{"photo":"<?php echo $_SESSION['client']['photo'];?>","username": "<?php echo $_SESSION['client']['id'];?>","items": [<?php echo $items;?>]}<?php
	exit(0);
}

function closeChat() {

	unset($_SESSION['openChatBoxes'][$_POST['chatbox']]);
	
	echo "1";
	exit(0);
}

function sanitize($text) {
	$text = htmlspecialchars($text, ENT_QUOTES);
	$text = str_replace("\n\r","\n",$text);
	$text = str_replace("\r\n","\n",$text);
	$text = str_replace("\n","<br />",$text);
	return $text;
}
