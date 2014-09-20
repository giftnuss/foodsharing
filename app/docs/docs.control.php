<?php
class DocsControl extends Control
{	
	public function __construct()
	{
		
		$this->model = new DocsModel();
		$this->view = new DocsView();
		
		parent::__construct();
		
	}
	
	public function index()
	{
		
	}
	
	public function doc()
	{
		
		addContent('<div id="userlist"></div>',CNT_RIGHT);
		addContent('<div id="firepad"></div>',CNT_MAIN);
		addScript('https://cdn.firebase.com/v0/firebase.js');
		addScript('/js/firepad/examples/codemirror/lib/codemirror.js');
		addScript('/js/firepad/examples/firepad.js');
		addScript('/js/firepad/examples/firepad-userlist.js');
		//addScript('js/firepad/examples/example-helper.js');
		//addScript('js/firepad/examples/example-helper.js');
		addCss('/js/firepad/examples/codemirror/lib/codemirror.css');
		addCss('/js/firepad/examples/firepad.css');
		
		addContent('
			<div id="userlist"></div>
  			<div id="firepad"></div>');
	}
}