<?php

if(
		(isset($_GET['newapp']) && !empty($_GET['newapp']))
		||
		(isset($argv[1]) && !empty($argv[1]))		
)
{
	$arg = '';
	if(isset($_GET['newapp']))
	{
		$arg = $_GET['newapp'];
	}
	else
	{
		$arg = $argv[1];
	}
	
	
	$appname = preg_replace('/[^0-9a-z]/', '', strtolower($arg));
	
	if(!empty($appname))
	{
		$path = 'app/'.$appname;
		if(mkdir($path))
		{
			file_put_contents($path.'/'.$appname.'.control.php', gen_control($appname));
			file_put_contents($path.'/'.$appname.'.model.php', gen_model($appname));
			file_put_contents($path.'/'.$appname.'.script.js', gen_script($appname));
			file_put_contents($path.'/'.$appname.'.style.css', gen_style($appname));
			file_put_contents($path.'/'.$appname.'.view.php', gen_view($appname));
			file_put_contents($path.'/'.$appname.'.xhr.php', gen_xhr($appname));
			file_put_contents($path.'/'.$appname.'.php', gen_loader($appname));
			file_put_contents($path.'/'.$appname.'.lang.php', gen_lang($appname));
			echo 'OK';
			
		}
	}
}


function gen_control($appname)
{
	return '<?php
class '.ucfirst($appname).'Control extends Control
{	
	public function __construct()
	{
		
		$this->model = new '.ucfirst($appname).'Model();
		$this->view = new '.ucfirst($appname).'View();
		
		parent::__construct();
		
	}
	
	public function index()
	{
		
	}
}';
}

function gen_model($appname)
{
	return '<?php
class '.ucfirst($appname).'Model extends Model
{
	
}';
}

function gen_script($appname)
{
	return '';
}

function gen_style($appname)
{
	return '';
}

function gen_view($appname)
{
	return '<?php
class '.ucfirst($appname).'View extends View
{
	
}';
}

function gen_xhr($appname)
{
	return '<?php 
class '.ucfirst($appname).'Xhr extends Control
{
	
	public function __construct()
	{
		$this->model = new '.ucfirst($appname).'Model();
		$this->view = new '.ucfirst($appname).'View();

		parent::__construct();
	}
}';
}

function gen_loader($appname)
{
	return '<?php
loadApp(\''.$appname.'\');';
}

function gen_lang($appname)
{
	return '<?php
global $g_lang;

';
}