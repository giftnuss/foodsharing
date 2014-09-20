<?php
ini_set('max_execution_time', 999);
ini_set('memory_limit', '512M');

require_once 'config.inc.php';
require_once 'lib/db.class.php';
require_once 'gen/Foodsaver.class.php';
require_once 'lib/init.class.php';
require_once 'lib/func.inc.php';
require_once 'lib/bot.php';

$db = new FsIntit('localhost', 'root', '', 'foodsaver');

$db->makeBundeslandBezirk();

echo '<br />ready';