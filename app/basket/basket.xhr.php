<?php 
class BasketXhr extends Control
{
	
	private $status;
	
	public function __construct()
	{
		$this->model = new BasketModel();
		$this->view = new BasketView();
		
		$this->status = array(
			'ungelesen'	=> 0,
			'gelesen' => 1,
			'abgeholt' => 2,
			'abgeleht' => 3,
			'nicht_gekommen' => 4,
			'angeklickt' => 10	
		);

		parent::__construct();
		
		/*
		 * allowed method for not logged in users
		 */
		$allowed = array(
			'bubble' => true,
			'login' => true
		);
		
		if(!S::may() && !isset($allowed[$_GET['m']]))
		{
			return $this->appout(array(
					'status' => 2
			));
		}
		
	}
	
	public function getnear()
	{
		if(S::may())
		{
			
		}
	}
	
	public function newbasket()
	{
		$dia = new XhrDialog();
		$dia->setTitle('Essenskorb anbieten');
		
		$dia->addPictureField('picture');
		
		$foodsaver = $this->model->getValues(array('telefon','handy'), 'foodsaver', fsid());
		
		$dia->addContent($this->view->basketForm($foodsaver));
				
		$dia->addJs('
				
		$("#tel-wrapper").hide();
		$("#handy-wrapper").hide();
		
		$("input.input.cb-contact_type[value=\'2\']").change(function(){
			if(this.checked)
			{
				$("#tel-wrapper").show();
				$("#handy-wrapper").show();	
			}
			else
			{
				$("#tel-wrapper").hide();
				$("#handy-wrapper").hide();
			}
		});
				
		$(".cb-food_art[value=3]").click(function(){
			if(this.checked)
			{
				$(".cb-food_art[value=2]")[0].checked = true;
			}
		});
		');
		
		$dia->noOverflow();
		
		$dia->addOpt('width', 550);
		
		$dia->addButton('Essenskorb veröffentlichen', 'ajreq(\'publish\',{appost:0,app:\'basket\',data:$(\'#'.$dia->getId().' .input\').serialize(),description:$(\'#description\').val(),picture:$(\'#'.$dia->getId().'-picture-filename\').val(),weight:$(\'#weight\').val()});');
		
		$dia->addJsAfter('
			$("#'.$dia->getId().'").resize(function(){
				alert("resize");
			});	
		');
		
		return $dia->xhrout();
	}
	
	public function publish()
	{
		$data = false;
		
		parse_str($_GET['data'], $data);
		
		$desc = strip_tags($data['description']);
		
		$desc = trim($desc);
		
		if(empty($desc))
		{
			return array(
				'status' => 1,
				'script' => 'pulseInfo("Bitte gib eine Beschreibung Ein!");'		
			);
		}
		
		$pic = '';
		$weight = floatval($data['weight']);
		
		if(isset($data['filename']))
		{
			$pic = preg_replace('/[^a-z0-9\.]/', '', $data['filename']);
			if(!empty($pic) && file_exists('tmp/' . $pic))
			{
				$this->resizePic($pic);
			}
		}
		
		$lat = 0;
		$lon = 0;
		
		$location_type = 0;
		
		if($location_type == 0)
		{
			$fs = $this->model->getValues(array('lat','lon'), 'foodsaver', fsid());
			$lat = $fs['lat'];
			$lon = $fs['lon'];
		}
		
		$contact_type = 1;
		$tel = array(
			'tel' => '',
			'handy' => ''		
		);
		
		if(isset($data['contact_type']) && is_array($data['contact_type']))
		{
			$contact_type = implode(':', $data['contact_type']);
			if(in_array(2, $data['contact_type']))
			{
				$tel = array(
					'tel' => preg_replace('/[^0-9\-\/]/', '', $data['tel']),
					'handy' => preg_replace('/[^0-9\-\/]/', '', $data['handy'])
				);
			}
		}
		
		if(!empty($desc) && ($id = $this->model->addBasket($desc,$pic,$tel,$contact_type,$weight,$location_type,$lat,$lon,S::user('bezirk_id'))))
		{
			if(isset($data['food_type']) && is_array($data['food_type']))
			{
				$types = array();
				foreach ($data['food_type'] as $ft)
				{
					if((int)$ft > 0)
					{
						$types[] = (int)$ft;
					}
				}
				
				$this->model->addTypes($id,$types);
			}
			
			if(isset($data['food_art']) && is_array($data['food_art']))
			{
				$arts = array();
				foreach ($data['food_art'] as $ft)
				{
					if((int)$ft > 0)
					{
						$arts[] = (int)$ft;
					}
				}
				
				$this->model->addArt($id,$arts);
			}
			
			return array(
				'status' => 1,
				'script' => '
					$("#msgbar-basket").hide();
					pulseInfo("Danke Dir! Der Essenskorb wurde veröffentlicht!");
					$(".xhrDialog").dialog("close");
					$(".xhrDialog").dialog("destroy");
					$(".xhrDialog").remove();'
			);
		}
		
		return array(
				'status' => 1,
				'script' => 'pulseError("Es gab einen Fehler, der Essenskorb konnte nicht veröffentlicht werden.");'
		);
		
	}
	
	public function resizePic($pic)
	{
		copy('tmp/' . $pic, 'images/basket/' . $pic);
		
		$img = new fImage('images/basket/' . $pic);
		$img->resize(800, 800);
		$img->saveChanges();
		
		
		copy('images/basket/' . $pic, 'images/basket/medium-' . $pic);
		
		$img = new fImage('images/basket/medium-' . $pic);
		$img->resize(450, 450);
		$img->saveChanges();
		
		
		copy('images/basket/medium-' . $pic, 'images/basket/thumb-' . $pic);
		
		$img = new fImage('images/basket/thumb-' . $pic);
		$img->cropToRatio(1, 1);
		$img->resize(200, 200);
		$img->saveChanges();
		
		
		copy('images/basket/thumb-' . $pic, 'images/basket/75x75-' . $pic);
		
		$img = new fImage('images/basket/75x75-' . $pic);
		$img->cropToRatio(1, 1);
		$img->resize(75, 75);
		$img->saveChanges();
		
		
		copy('images/basket/75x75-' . $pic, 'images/basket/50x50-' . $pic);
		
		$img = new fImage('images/basket/50x50-' . $pic);
		$img->cropToRatio(1, 1);
		$img->resize(50, 50);
		$img->saveChanges();
	}
	
	public function bubble()
	{
		if(($basket = $this->model->getBasket($_GET['id'])))
		{
			if($basket['fsf_id'] == 0)
			{
				$dia = new XhrDialog();
				
				/*
				 * What see the user if not logged in?
				 */
				if(!S::may())
				{
					$dia->setTitle('Essenskorb');
					$dia->addContent($this->view->bubbleNoUser($basket));
					$dia->addButton('Einloggen zum anfragen',"ajreq('login',{app:'login'});");
				}
				else
				{
					$dia->setTitle('Essenskorb von '.$basket['fs_name']);
					$dia->addContent($this->view->bubble($basket));
					$dia->addButton('Essenskorb anfragen', 'ajreq(\'request\',{app:\'basket\',id:'.(int)$basket['id'].'});');
				}
				
				$modal = false;
				if(isset($_GET['modal']))
				{
					$modal = true;
				}
				$dia->addOpt('modal', 'false',$modal);
				$dia->addOpt('resizeable', 'false',false);
				
				
				$dia->addOpt('width', 400);
				$dia->noOverflow();
				
				$return = $dia->xhrout();
				
				return $return;
			}
			else
			{
				return $this->fsBubble($basket);
			}
		}
		else
		{
			return array(
				'status' => 1,
				'script' => 'pulseError("Essenskorb konnte nicht geladen werden");'		
			);
		}
	}
	
	public function fsBubble($basket)
	{
		$dia = new XhrDialog();
		
		$dia->setTitle('Essenskorb von foodsharing.de');
		
		$dia->addContent($this->view->fsBubble($basket));
		$modal = false;
		if(isset($_GET['modal']))
		{
			$modal = true;
		}
		$dia->addOpt('modal', 'false',$modal);
		$dia->addOpt('resizeable', 'false',false);
		
		//$dia->addButton('Essenskorb anfragen auf foodsharing.de', 'ajreq(\'request\',{app:\'basket\',id:'.(int)$basket['id'].'});');
		
		$dia->addOpt('width', 400);
		$dia->noOverflow();
		
		$dia->addJs('$(".fsbutton").button();');
		
		$return = $dia->xhrout();
		
		return $return;
	}
	
	public function request()
	{
		if($basket = $this->model->getBasket($_GET['id']))
		{
			$this->model->setStatus($_GET['id'], 10);
			$dia = new XhrDialog();
			$dia->setTitle('Essenskorb von '.$basket['fs_name'].'');
			$dia->addOpt('width', 300);
			$dia->noOverflow();
			$dia->addContent($this->view->contactTitle($basket));
			
			$contact_type = array(1);
			
			if(!empty($basket['contact_type']))
			{
				$contact_type = explode(':', $basket['contact_type']);
			}
			
			if(in_array(2, $contact_type))
			{
				$dia->addContent($this->view->contactNumber($basket));
			}
			if(in_array(1, $contact_type))
			{
				$dia->addContent($this->view->contactMsg($basket));
				$dia->addButton('Anfrage absenden', 'ajreq(\'sendreqmessage\',{appost:0,app:\'basket\',id:'.(int)$_GET['id'].',msg:$(\'#contactmessage\').val()});');
			}
			
			return $dia->xhrout();
		}
	}
	
	public function sendreqmessage()
	{
		if($fs_id = $this->model->getVal('foodsaver_id', 'basket', $_GET['id']))
		{
			$msg = strip_tags($_GET['msg']);
			$msg = trim($msg);
			if(!empty($msg))
			{
				$this->model->message($fs_id, fsId(), $msg,0);
				$this->mailMessage(fsId(), $fs_id, $msg,22);
				$this->model->setStatus($_GET['id'], 0);
				
				$this->pushMessage($fs_id,$msg,'Dein Essenskorb wurde angefragt');
				
				return array(
					'status' => 1,
					'script' => 'if($(".xhrDialog").length > 0){$(".xhrDialog").dialog("close");}pulseInfo("Anfrage wurde versendet.");'		
				);
			}
			else
			{
				return array(
					'status' => 1,
					'script' => 'pulseError("Du hast keine Nachricht eingegeben");'
				);
			}
		}
		
		return array(
			'status' => 1,
			'script' => 'pulseError("Es ist ein Fehler aufgetreten");'
		);
	}
	
	public function infobar()
	{
		S::noWrite();
		
		$xhr = new Xhr();
	
		$out = '';
		if($updates = $this->model->listUpdates())
		{
			$out = $this->view->listUpdates($updates);
		}
		
		if($baskets = $this->model->listMyBaskets())
		{
			$out .= $this->view->listMyBaskets($baskets);
		}
		
		$xhr->addData('html', $out);
		
		$xhr->send();
	}
	
	public function update()
	{
		$count = $this->model->getUpdateCount();
		if((int)$count > 0)
		{
			return array(
				'status' => 1,
				'script' => '
					$("#msgBar-badge .bar-basket").text("'.$count.'").css({ opacity: 1 });
					$("#msgbar-basket ul li.loading").remove();
					$("#msgbar-basket ul").prepend(\'<li class="loading">&nbsp;</li>\');
				'		
			);
		}
		else
		{
			return array(
				'status' => 1,
				'script' => '$("#msgBar-badge .bar-basket").text("0").css({ opacity: 0 });'		
			);
		}
	}
	
	public function answer()
	{
		if($id = $this->model->getVal('foodsaver_id','basket',$_GET['id']))
		{
			if($id == fsid())
			{
				$this->model->setStatus($_GET['id'], 1, $_GET['fid']);
				return array(
					'status' => 1,
					'script'=> 'chat('.$_GET['fid'].');$("#msgbar-basket").hide();ajreq("update",{app:"basket"});'		
				);
			}
		}
	}
	
	public function removeRequest()
	{
		if($request = $this->model->getRequest($_GET['id'],$_GET['fid']))
		{
			global $g_data;
			$g_data['fetchstate'] = 3;
			/*
			 * Array
				(
				    [time_ts] => 1402149037
				    [fs_name] => Luisa
				    [fs_photo] => 530c93a86a9f8.jpg
				    [fs_id] => 3542
				    [id] => 20
				)
			 */
			
			$dia = new XhrDialog();
			$dia->addOpt('width', '400');
			$dia->noOverflow();
			$dia->setTitle('Essenskorbanfrage von '.$request['fs_name'].' abschließen');
			$dia->addContent( 
				'<div>
					<img src="'.img($request['fs_photo']).'" style="float:left;margin-right:10px;">
					<p>Anfragezeitpunkt: '.niceDate($request['time_ts']).'</p>
					<div style="clear:both;"></div>
				</div>'	
				. v_form_radio('fetchstate',array(
				'values' => array(
					array('id' => 3, 'name' => 'Ja, '.genderWord($request['fs_gender'], 'Er', 'Sie', 'Er/Sie').' hat den Korb angeholt'),
					array('id' => 5, 'name' => 'Nein, '.genderWord($request['fs_gender'], 'Er', 'Sie', 'Er/Sie').' ist leider nicht wie verabredet erschienen'),
					array('id' => 5, 'name' => 'Die Lebensmittel wurden von jemand anderes angeholt.'),
				)		
			)));
			$dia->addAbortButton();
			$dia->addButton('Weiter', 'ajreq(\'finishRequest\',{app:\'basket\',id:'.(int)$_GET['id'].',fid:'.(int)$_GET['fid'].',sk:$(\'#fetchstate-wrapper input:checked\').val()});');
			
			return $dia->xhrout();
		}
	}
	
	public function removeBasket()
	{
		$this->model->removeBasket($_GET['id']);
		
		return array(
			'status' => 1,
			'script' => '$(".basket-'.(int)$_GET['id'].'").remove();pulseInfo("Essenskorb ist jetzt nicht mehr aktiv!");'		
		);
	}
	
	public function syncFoodsharing()
	{
		$plzs = array('90','80','70','60','50','40','30','20','10','00');
		$baskets = $this->model->getTodayFsBaskets();
		$sql = array();
		$delids = array();
		foreach ($plzs as $plz)
		{
			sleep(1);
			$json = file_get_contents('http://foodsharing.de/api/foodcarts.json?app_id='.API_ID.'&zipcode='.$plz.'&_limit=1&status=1&release_date='.date('Y-m-d'));
			
			
			$main = json_decode($json,true);
			
			if($main['result'] == 'SUCCESS')
			{
				$json = file_get_contents('http://foodsharing.de/api/foodcarts.json?app_id='.API_ID.'&zipcode='.$plz.'&_limit=' . $main['overall_count'] . '&status=1&release_date='.date('Y-m-d'));
				
				$data = json_decode($json,true);
				
				if($data['result'] == 'SUCCESS')
				{
					foreach ($data['foodcarts'] as $d)
					{
						if(isset($d['Foodcart']) && !isset($baskets[$d['Foodcart']['id']]))
						{
							unset($baskets[$d['Foodcart']['id']]);
							$desc = '';
							$img = '';
							foreach ($d['Item'] as $item)
							{
								$desc .= $item['name']."\n";
								if(!empty($item['picture']))
								{
									$img =  $item['picture'];
								}
							}
							
							$desc .= $d['Foodcart']['description'];
							
							$delids[] = (int)$d['Foodcart']['id'];
							$sql[(int)$d['Foodcart']['id']] = '(1,'.$this->model->floatval($d['Foodcart']['lat']).','.$this->model->floatval($d['Foodcart']['lng']).','.$this->model->strval($desc).','.(int)$d['Foodcart']['id'].','.$this->model->dateval($d['Foodcart']['created']).','.$this->model->strval($img).')';
						}
					}
				}
			}
			else
			{
				return false;
			}
		}
		if(!empty($sql))
		{
			$this->model->del('
				DELETE FROM '.PREFIX.'basket
				WHERE `fs_id` IN('.implode(',', $delids).')		
			');
			$this->model->insert('
					INSERT INTO `'.PREFIX.'basket`
					(`status`,`lat`,`lon`,`description`,`fs_id`,`time`,`picture`)
					VALUES
					'.implode(',', $sql).'
					');
		
			if(!empty($baskets))
			{
				$this->model->del('DELETE FROM `'.PREFIX.'basket` WHERE `fs_id` IN('.implode(',', $baskets).')');
			}
		}
		//
		
	}
	
	public function finishRequest()
	{
		if($request = $this->model->getRequest($_GET['id'],$_GET['fid']))
		{
			if(isset($_GET['sk']) && (int)$_GET['sk'] > 0)
			{
				$this->model->setStatus($_GET['id'], $_GET['sk'],$_GET['fid']);
				return array(
					'status' => 1,
					'script' => '
						$(".msg-'.(int)$_GET['id'].'-'.(int)$_GET['fid'].'").remove();
						pulseInfo("Danke Dir! Der Vorgang ist abgeschlossen.");
						$(".xhrDialog").dialog("close");
						$(".xhrDialog").dialog("destroy");
						$(".xhrDialog").remove();
						'
				);
			}
		}
		
		return array(
				'status' => 1,
				'script' => 'pulseError("Es ist ein Fehler aufgetreten");'
		);
	}
}
