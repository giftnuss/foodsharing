<?php 
class BetriebXhr extends Control
{
	
	public function __construct()
	{
		$this->model = new BetriebModel();
		$this->view = new BetriebView();

		parent::__construct();
	}
	
	public function savedate()
	{
		if(strtotime($_GET['time']) > 0 && $_GET['fetchercount'] > 0)
		{
			$fetchercount = (int)$_GET['fetchercount'];
			$time = $_GET['time'];
			if($fetchercount > 8)
			{
				$fetchercount = 8;
			}
			
			if($this->model->addFetchDate($_GET['bid'],$time,$fetchercount))
			{
				info('Abholtermin wurde eingetragen!');
				return array(
					'status' => 1,
					'script' => 'reload();'		
				);
			}
			
		}
	}
	
	public function deldate()
	{
		if(isset($_GET['id']) && isset($_GET['time']) && strtotime($_GET['time']) > 0)
		{
			$this->model->deldate($_GET['id'],$_GET['time']);
			
			info('Abholtermin wurde gelöscht.');
			
			return array(
				'status' => 1,
				'script' => 'reload();'		
			);
		}
	}
	
	public function adddate()
	{
		$dia = new XhrDialog();
		$dia->setTitle('Abholtermin eintragen');
		$dia->addContent($this->view->dateForm());
		$dia->addOpt('width', 280);
		$dia->setResizeable(false);
		$dia->addAbortButton();
		$dia->addButton('Speichern', 'saveDate();');
		
		$dia->addJs('
				
			function saveDate()
			{
				var date = $("#datepicker").datepicker( "getDate" );
				
				date = date.getFullYear() + "-" +
				    ("00" + (date.getMonth()+1)).slice(-2) + "-" +
				    ("00" + date.getDate()).slice(-2) + " " + 
				    ("00" + $("select[name=\'time[hour]\']").val()).slice(-2) + ":" + 
				    ("00" + $("select[name=\'time[min]\']").val()).slice(-2) + ":00";
				
				if($("#fetchercount").val() > 0)
				{
					ajreq("savedate",{
						app:"betrieb",
						time:date,
						fetchercount:$("#fetchercount").val(),
						bid:'.(int)$_GET['id'].'
					});
				}
				else
				{
					pulseError("Du musst noch die Anzahl der Abholer/innen auswählen");
				}
			}
				
			$("#datepicker").datepicker({
				minDate: new Date()
			});	
		');
		
		return $dia->xhrout();
	}
}