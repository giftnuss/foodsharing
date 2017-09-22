<?php

$db->logout();
$_SESSION['login'] = false;
$_SESSION = array();
S::destroy();
header('Location: /');
exit();
