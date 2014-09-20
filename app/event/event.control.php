<?php
class EventControl extends Control
{	
	public function __construct()
	{
		
		$this->model = new EventModel();
		$this->view = new EventView();
		
		parent::__construct();
		
	}
	
	public function index()
	{
		if(!isset($_GET['sub']) && isset($_GET['id']) && ($event = $this->model->getEvent($_GET['id'])))
		{
			addBread('Termine','?page=event');
			addBread($event['name']);
			
			$status = $this->model->getInviteStatus($event['id'],fsid());
			
			addContent($this->view->eventTop($event,$status),CNT_TOP);
			addContent($this->view->event($event));
			
			if($event['online'] == 0 && $event['location'] != false)
			{
				addContent($this->view->location($event['location']),CNT_RIGHT);
			}
			elseif ($event['online'] == 1)
			{
				addContent($this->view->locationMumble(),CNT_RIGHT);
			}
		
			if($event['invites'])
			{
				addContent($this->view->invites($event['invites']),CNT_LEFT);
			}
			addContent(v_field($this->wallposts('event', $event['id']),'Pinnwand'));
		}
		else if(!isset($_GET['sub']))
		{
			go('/?page=dashboard');
		}
		
	}
	
	public function edit()
	{
		if($event = $this->model->getEvent($_GET['id']))
		{
			if($event['fs_id'] == fsId() || isOrgaTeam() || isBotFor($event['bezirk_id']))
			{
				addBread('Termine','?page=event');
				addBread('Neuer Termin');
					
				if($this->isSubmitted())
				{
					if($data = $this->validateEvent())
					{
						if($this->model->updateEvent($_GET['id'],$data))
						{
							if(isset($_POST['delinvites']) && $_POST['delinvites'] == 1)
							{
								$this->model->deleteInvites($_GET['id']);
							}
							if($data['invite'])
							{
								$this->model->inviteBezirk($data['bezirk_id'],$_GET['id'],$data['invitesubs']);
							}
							info('Event wurde erfolgreich geändert!');
							go('?page=event&id='.(int)$_GET['id']);
						}
					}
				}
					
				$bezirke = $this->model->getBezirke();
				
				if($event['location_id'] > 0)
				{
					if($loc = $this->model->getLocation($event['location_id']))
					{
						$event['location_name'] = $loc['name'];
						$event['lat'] = $loc['lat'];
						$event['lon'] = $loc['lon'];
						$event['plz'] = $loc['zip'];
						$event['ort'] = $loc['city'];
						$event['anschrift'] = $loc['street'];
					}
				}
				
				setEditData($event);
					
				addContent($this->view->eventForm($bezirke));
			}
			else
			{
				go('?page=event');
			}
		}
	}
	
	public function add()
	{
		addBread('Termine','?page=event');
		addBread('Neuer Termin');
		
		if($this->isSubmitted())
		{
			if($data = $this->validateEvent())
			{
				if($id = $this->model->addEvent($data))
				{
					if($data['invite'])
					{
						$this->model->inviteBezirk($data['bezirk_id'],$id,$data['invitesubs']);
					}
					info('Event wurde erfolgreich eingetragen!');
					go('?page=event&id='.(int)$id);
				}
			}
		}
		else
		{
			$bezirke = $this->model->getBezirke();
			
			addContent($this->view->eventForm($bezirke));
		}
	}
	
	public function validateEvent()
	{
		$out = array(
			'name' => '',
			'description' => '',
			'online_type' => 0,
			'location_id' => 0,
			'start' => date('Y-m-d').' 15:00:00',
			'end' => date('Y-m-d').' 16:00:00',
			'public' => 0,
			'bezirk_id' => 0,
			'invite' => false,
			'online' => 0,
			'invitesubs' => false
		);
	
		if(isset($_POST['public']) && $_POST['public'] == 1)
		{
			$out['public'] = 1;
		}
		elseif ($bid = $this->getPostInt('bezirk_id'))
		{
			$out['bezirk_id'] = (int)$bid;
			if(isset($_POST['invite']) && $_POST['invite'] == 1)
			{
				$out['invite'] = true;
				if(isset($_POST['invitesubs']) && $_POST['invitesubs'] == 1)
				{
					$out['invitesubs'] = true;
				}
			}
		}
		
		if($start_date = $this->getPostDate('date'))
		{
			if($start_time = $this->getPostTime('time_start'))
			{
				if($end_time = $this->getPostTime('time_end'))
				{
					$out['start'] = date('Y-m-d',$start_date).' '.preZero($start_time['hour']).':'.preZero($start_time['min']).':00';
					$out['end'] = date('Y-m-d',$start_date).' '.preZero($end_time['hour']).':'.preZero($end_time['min']).':00';
						
					if((int)$this->getPostInt('addend') == 1 && ($ed = $this->getPostDate('dateend')))
					{
						$out['end'] = date('Y-m-d',$ed).' '.preZero($end_time['hour']).':'.preZero($end_time['min']).':00';
					}
				}
	
			}
		}
	
		if($name = $this->getPostString('name'))
		{
			$out['name'] = $name;
		}
			
		if($description = $this->getPostString('description'))
		{
			$out['description'] = $description;
		}
			
		$out['online_type'] = $this->getPostInt('online_type');		
		
		if($out['online_type'] == 1)
		{
			$out['online'] = 0;
			
			$lat = $this->getPost('lat');
			$lon = $this->getPost('lon');
				
			$id = $this->model->getLocationIdByLatLon($lat,$lon);
	
			if(!$id)
			{
				$id = $this->model->addLocation(
						$this->getPostString('location_name'),
						$lat,
						$lon,
						$this->getPostString('anschrift'),
						$this->getPostString('plz'),
						$this->getPostString('ort')
				);
			}
				
			$out['location_id'] = $id;
		}
		else
		{
			$out['online'] = 1;
			$out['location_id'] = 0;
		}
		return $out;
	}
}