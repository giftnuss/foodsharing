<?php 
class BellXhr extends Control
{
	
	public function __construct()
	{
		$this->model = new BellModel();
		$this->view = new BellView();

		parent::__construct();
	}
	
	/**
	 * ajax call to refresh infobar messages
	 */
	public function infobar()
	{
		S::set('badge-info',0);
		S::noWrite();
		
		$xhr = new Xhr();
		$bells = $this->model->listBells(20);
		
		// additionall add bell for betrieb verantwortliche
		if(isset($_SESSION['client']['verantwortlich']))
		{
			$ids = array();
			foreach ($_SESSION['client']['verantwortlich']as $v)
			{
				$ids[] = (int)$v['betrieb_id'];
			}
			if(!empty($ids))
			{
				if($betrieb_bells = $this->model->getBetriebBells($ids))
				{
					$bbells = array();
					
					foreach ($betrieb_bells as $b)
					{
						$bbells[]= array(
							'id'=> 'b-'.$b['id'],
							'name' => 'betrieb_fetch_title',
							'body' => 'betrieb_fetch',
							'vars' => array(
								'betrieb' => $b['name'],
								'count' => $b['count']
							),
							'attr' => array(
								'href' => '?page=fsbetrieb&id='.$b['id']
							),
							'icon' => 'img img-store brown',
							'time' => $b['date'],
							'time_ts' => $b['date_ts'],
							'seen' => 0,
							'closeable' => 0
						);
					}
					if($bells)
					{
						$bells = array_merge($bbells,$bells);
					}
					else
					{
						$bells = $bbells;
					}
				}
			}
		}
		
		
		
		$xhr->addData('html', $this->view->bellList($bells));
		
		$xhr->send();
	}
	
	/**
	 * ajax call to delete an bell
	 */
	public function delbell()
	{
		$this->model->delbell($_GET['id']);
	}
}