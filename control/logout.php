<?php
ini_set('session.save_handler', 'redis');
ini_set('session.save_path', 'tcp://'.REDIS_HOST.':'.REDIS_PORT);
$db->logout();
$_SESSION['login'] = false;
$_SESSION = array();
session_destroy();
S::destroy();
header('Location: /');
exit();
