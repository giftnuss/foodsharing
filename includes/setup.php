<?php

require __DIR__ . '/../vendor/autoload.php';

/* Intermediate global instantiation. Should be done via DI at some point. */
$g_func = new \Foodsharing\Lib\Func();
$g_view_utils = new \Foodsharing\Lib\View\Utils();
