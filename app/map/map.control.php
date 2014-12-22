<?php
class MapControl extends Control
{	
	public function __construct()
	{
		$this->model = new MapModel();
		$this->view = new MapView();
		
		parent::__construct();
	}
	
	public function index()
	{		
		addTitle(s('map'));
		$this->setTemplate('map');
		
		$center = $this->model->getValues(array('lat','lon'), 'foodsaver', fsId());

		
		
		addContent($this->view->mapControl(),CNT_TOP);
		
		
		$jsarr = '';
		if(isset($_GET['load']) && $_GET['load'] == 'baskets')
		{
			$jsarr = '["baskets"]';
		}
		else if(isset($_GET['load']) && $_GET['load'] == 'fairteiler')
		{
			$jsarr = '["fairteiler"]';
		}
		/*
		else
		{
			if(S::may('fs'))
			{
				$jsarr = '["betriebe"]';
			}
			else
			{
				addStyle('#map-control-wrapper > .ui-widget-content{height:93px;}');
				$jsarr = '["baskets"]';
			}
		}
		*/
		
		if(!S::may('fs'))
		{
			addStyle('#map-control-wrapper > .ui-widget-content{height:93px;}');
		}
		
		addContent(
		//$this->view->map($center)
			$this->view->lMap($center)
		);

		if(S::may('fs') && isset($_GET['bid']) && ($betrieb = $this->model->getBetrieb($_GET['bid'])))
		{
			$center = array(
					'lat' => $betrieb['lat'],
					'lon' => $betrieb['lon']
			);
			addJs('
				u_loadDialog("xhr.php?f=bBubble&id='.(int)$_GET['bid'].'");
			');
		}
		
		if(!$center)
		{
			addJs('u_init_map();');
		}
		else
		{
			addJs('u_init_map('.$center['lat'].','.$center['lon'].',6);');
		}
		
		addJs('map.initMarker('.$jsarr.');');
		
		
		if(isMob())
		{
			//addStyle('.leaflet-bottom{display:none;} .ui-dialog .ui-dialog-content{padding:0 !important;}#map-control-wrapper{right:-115px !important}');
		}
		
	}
}
