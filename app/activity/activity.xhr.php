<?php 
class ActivityXhr extends Control
{
	
	public function __construct()
	{
		$this->model = new ActivityModel();
		$this->view = new ActivityView();

		parent::__construct();
	}
	
	public function loadmore()
	{
		$xhr = new Xhr();
		
		/*
		 * get FOrum updates
		*/
		
		$updates = array();
		if($up = $this->model->loadForumUpdates($_GET['page']))
		{
			$updates = $up;
				
			if($up = $this->model->loadBetriebUpdates($_GET['page']))
			{
				$updates = array_merge($updates,$up);
			}
			if($up = $this->model->loadMailboxUpdates($_GET['page']))
			{
				$updates = array_merge($updates,$up);
			}
		}
		
		$xhr->addData('updates', $updates);
		
		$xhr->send();
	}
	
	public function load()
	{
		/*
		 * get Forum updates
		 */
		
		if(isset($_GET['options']))
		{
			$options = array();
			foreach ($_GET['options'] as $o)
			{
				if(isset($o['index']) && isset($o['id']) && (int)$o['id'] > 0)
				{
					$options[$o['index'].'-'.$o['id']] = array(
						'index' => $o['index'],
						'id' => $o['id']
					);
				}
			}
			
			if(empty($options))
			{
				$options = false;
			}
			
			S::setOption('activity-listings', $options, $this->model);
		}
		
		$page = 0;
		$hidden_ids = array(
			'bezirk' => array(),
			'store' => array(),
			'mailbox' => array()
		);
		
		if($sesOptions = S::option('activity-listings'))
		{
			foreach ($sesOptions as $o)
			{
				if(isset($hidden_ids[$o['index']]))
				{
					$hidden_ids[$o['index']][$o['id']] = true;
				}
			}
		}
		
		$xhr = new Xhr();
		$updates = array();
		if($up = $this->model->loadForumUpdates($page,$hidden_ids['bezirk']))
		{			
			$updates = $up;
			
			if($up = $this->model->loadBetriebUpdates())
			{
				$updates = array_merge($updates,$up);
			}
			if($up = $this->model->loadMailboxUpdates($hidden_ids['mailbox']))
			{
				$updates = array_merge($updates,$up);
			}
		}
		
		$listings = array(
			'groups' => array(),
			'regions' => array(),
			'mailboxes' => array(),
			'stores' => array()
		);
		
		$option = array();
		
		if($list = S::option('activity-listings'))
		{
			$option = $list;
		}
		
		/*
		 * listings bezirke
		 */
		if($bezirke = $this->model->getBezirke())
		{
			foreach ($bezirke as $b)
			{
				$checked = true;
				if(isset($option['bezirk-' . $b['id']]))
				{
					$checked = false;
				}
				$dat = array(
					'id' => $b['id'],
					'name' => $b['name'],
					'checked' => $checked
				);
				if($b['type'] == 7)
				{
					$listings['groups'][] = $dat;
				}
				else
				{
					$listings['regions'][] = $dat;
				}
			}
		}
		
		/*
		 * listings mailboxes
		*/
		
		
		
		
		$xhr->addData('updates', $updates);
		$xhr->addData('listings', array(
			0 => array(
				'name' => s('groups'),
				'index' => 'bezirk',
				'items' => $listings['groups']
			),
			1 => array(
				'name' => s('regions'),
				'index' => 'bezirk',
				'items' => $listings['regions']
			),
			2 => array(
				'name' => s('stores'),
				'index' => 'betrieb',
				'items' => $listings['stores']
			)/*,
			2 => array(
				'name' => s('mailboxes'),
				'index' => 'mailbox',
				'items' => $listings['mailboxes']
			)*/
		));
		$xhr->addData('user', array(
			'id' => fsId(),
			'name' => S::user('name'),
			'avatar' => img(S::user('photo'))
		));
		
		$xhr->send();
	}
}