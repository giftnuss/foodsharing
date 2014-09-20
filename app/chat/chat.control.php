<?php
class ChatControl extends Control
{	
	public function __construct()
	{
		
		$this->model = new ChatModel();
		$this->view = new ChatView();
		
		parent::__construct();
		
	}
	
	public function index()
	{
		addBread('Nachrichten');
		addContent('
		<div id="main_container">
			<a href="javascript:void(0)" onclick="javascript:chatWith('.(int)$_GET['id'].')">Chat With Jane Doe</a>
		</div>	
		');
	}
}