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
	
	public function getfetchhistory()
	{
		if(S::may() && ($this->model->isVerantwortlich($_GET['bid']) || S::may('orga')))
		{
			if($history = $this->model->getFetchHistory($_GET['bid'],$_GET['from'],$_GET['to']))
			{
				//print_r($history);
				//die();
				return array(
					'status' => 1,
					'script' => '
					$("daterange_from").datepicker("close");
					$("daterange_to").datepicker("close");
						
					$("#daterange_content").html(\''.jsSafe($this->view->fetchlist($history)).'\');
						'
				);
			}
		}
	}
	
	public function fetchhistory()
	{
		if(S::may() && ($this->model->isVerantwortlich($_GET['bid']) || S::may('orga')))
		{
			$dia = new XhrDialog();
			$dia->setTitle('Abholungs-History');
			
			$id = 'daterange';
			
			$dia->addContent($this->view->fetchHistory());
			
			$dia->addJsAfter('

					$( "#'.$id.'_from" ).datepicker({
						changeMonth: true,
						maxDate: "0",
						
						onClose: function( selectedDate ) {
							$( "#'.$id.'_to" ).datepicker( "option", "minDate", selectedDate );
						}
					});
					$( "#'.$id.'_to" ).datepicker({
						changeMonth: true,
						maxDate: "0",
						autoOpen: true,
						onClose: function( selectedDate ) {
							$( "#'.$id.'_from" ).datepicker( "option", "maxDate", selectedDate );
						}
					});
					
					$( "#'.$id.'_from" ).datepicker("show");
					
					
					$(window).resize(function(){
						$("#'.$dia->getId().'").dialog("option",{
							height:($(window).height()-40)
						});
					});
					
					$("#daterange_submit").click(function(ev){
						ev.preventDefault();
					
						var date = $( "#'.$id.'_from" ).datepicker("getDate");
						
						var from = "";
						var to = "";
						
						if(date !== null)
						{
							from = date.getFullYear() + "-" + preZero((date.getMonth()+1)) + "-" + preZero(date.getDate());
							date = $( "#'.$id.'_to" ).datepicker("getDate");
						
							if(date === null)
							{
								to = from;
							}
							else
							{
								to = date.getFullYear() + "-" + preZero((date.getMonth()+1)) + "-" + preZero(date.getDate());
							}
					
							ajreq("getfetchhistory",{app:"betrieb",from:from,to:to,bid:'.(int)$_GET['bid'].'});
						}
						else
						{
							alert("Du musst erst ein Datum ausw&auml;hlen ;)");
						}
					});
					
			');
			
			$dia->addOpt('width','500px');
			$dia->addOpt('height','($(window).height()-40)',false);
			
			
			return $dia->xhrout();
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