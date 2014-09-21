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
		$this->setTemplate('map');
		
		$center = $this->model->getValues(array('lat','lon'), 'foodsaver', fsId());

		addContent(
			//$this->view->map($center)
			$this->view->lMap($center)
		);
		
		addContent($this->view->mapControl(),CNT_TOP);
		
		
		if(isset($_GET['load']) && $_GET['load'] == 'baskets')
		{
			addJs('loadMarker(["baskets"]);');
		}
		else
		{
			if(S::may('fs'))
			{
				addJs('loadMarker(["betriebe"]);');
			}
			else
			{
				addJs('loadMarker(["baskets"]);');
			}
		}
		
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
		
		if(isMob())
		{
			//addStyle('.leaflet-bottom{display:none;} .ui-dialog .ui-dialog-content{padding:0 !important;}#map-control-wrapper{right:-115px !important}');
		}
		
	}
}