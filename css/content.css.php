<?php 
header("Content-type: text/css", true);
$cnt = file_get_contents('../../css/style.css');
$cnt = str_replace(array('/*replace-a*/','/*replace-b*/','../images'), array('/* ',' */','../../images'), $cnt);
echo $cnt.'/* */

body
{
	background-color:#F8F1E9;
}';
