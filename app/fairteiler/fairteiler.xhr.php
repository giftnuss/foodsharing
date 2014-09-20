<?php 
class FairteilerXhr extends Control
{
	
	public function __construct()
	{
		$this->model = new FairteilerModel();
		$this->view = new FairteilerView();

		parent::__construct();
	}
	
	public function load()
	{
		if(($id = (int)$_GET['id']) > 0)
		{
			if($fairteiler = $this->model->getFairteiler($id))
			{
				$fairteiler['updates'] = false;
				if($updates = $this->model->getLastUpdates($id))
				{
					$fairteiler['updates'] = $updates;
				}
				
				return array(
					'status' => 1,
					'html' => $this->view->publicFairteilerMap($fairteiler),
					'name' => $fairteiler['name']
				);
			}
		}
	}
	
	public function infofollower()
	{
		if($ft = $this->model->getFairteiler($_GET['fid']))
		{
			if($follower = $this->model->getEmailFollower($_GET['fid']))
			{
				$post = $this->model->getLastFtPost($_GET['fid']);
				
				$body = nl2br($post['body']);
				// http://lebensmittelretten.local/freiwillige/images/wallpost/medium_531d9a4e5788d.png
				
				if(!empty($post['attach']))
				{
					$attach = json_decode($post['attach'],true);
					if(isset($attach['image']) && !empty($attach['image']))
					{
						foreach ($attach['image'] as $img)
						{
							$body .= '
							<div>
								<img src="http://www.'.DEFAULT_HOST.'/images/wallpost/medium_'.$img['file'].'" />
							</div>';
						}
					}
				}
				
				foreach ($follower as $f)
				{
					tplMail(18, $f['email'],array(
						'link' => 'http://www.lebensmittelretten.de/?page=fairteiler&sub=ft&id='.(int)$_GET['fid'],
						'name' => $f['name'],
						'anrede' => genderWord($f['geschlecht'], 'Lieber', 'Liebe', 'Liebe/r'),
						'fairteiler' => $ft['name'],
						'post' => $body
					));
				}
			}
			
			if($follower = $this->model->getInfoFollower($_GET['fid']))
			{
				$this->model->addGlocke($follower, 'Updates im '.$ft['name'].' Fair-Teiler','Fair-Teiler Update','?page=fairteiler&sub=ft&id='.(int)$_GET['fid']);
			}
		}
		
		return array(
			'status' => 1,
			'script' => 'u_fbshare('.(int)$_GET['pid'].');'
		);
	}
}