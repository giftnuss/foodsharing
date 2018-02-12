<?php

namespace Foodsharing\Modules\Report;

use Foodsharing\Lib\Xhr\XhrDialog;
use Foodsharing\Modules\Core\Control;

class ReportXhr extends Control
{
	private $foodsaver;

	public function __construct()
	{
		$this->model = new ReportModel();
		$this->view = new ReportView();

		parent::__construct();

		if (isset($_GET['fsid'])) {
			$this->foodsaver = $this->model->getOne_foodsaver($_GET['fsid']);
			$this->view->setFoodsaver($this->foodsaver);
		}
	}

	public function loadreport()
	{
		if ($this->func->mayHandleReports()) {
			if ($report = $this->model->getReport($_GET['id'])) {
				$reason = explode('=>', $report['tvalue']);

				$dialog = new XhrDialog();
				$dialog->setTitle('Meldung über ' . $report['fs_name'] . ' ' . $report['fs_nachname']);

				$content = $this->v_utils->v_input_wrapper('Zeitpunkt', $this->func->niceDate($report['time_ts']));

				if (isset($report['betrieb'])) {
					$content .= $this->v_utils->v_input_wrapper('Zugeordneter Betrieb', '<a href="/?page=fsbetrieb&id=' . $report['betrieb']['id'] . '">' . $report['betrieb']['name'] . '</a>');
				}

				if (is_array($reason)) {
					$out = '<ul>';
					foreach ($reason as $r) {
						$out .= '<li>' . trim($r) . '</li>';
					}
					$out .= '</ul>';

					$content .= $this->v_utils->v_input_wrapper('Grund', $out);
				}

				if (!empty($report['msg'])) {
					$content .= $this->v_utils->v_input_wrapper('Beschreibung', nl2br($report['msg']));
				}

				$content .= $this->v_utils->v_input_wrapper('Gemeldet von', '<a href="#" onclick="profile(' . (int)$report['rp_id'] . ');">' . $report['rp_name'] . ' ' . $report['rp_nachname'] . '</a>');
				$dialog->addContent($content);
				$dialog->addOpt('width', '600px');

				$dialog->addButton('Alle Meldungen über ' . $report['fs_name'], 'goTo(\'/?page=report&sub=foodsaver&id=' . $report['fs_id'] . '\');');

				if ($report['committed'] == 0) {
					$dialog->addButton('Report bestätigen', 'ajreq(\'comreport\',{\'id\':' . (int)$_GET['id'] . '});');
				}
				$dialog->addButton('Löschen', 'if(confirm(\'Diese Meldung wirklich löschen?\')){ajreq(\'delreport\',{id:' . $report['id'] . '});$(\'#' . $dialog->getId() . '\').dialog(\'close\');}');

				return $dialog->xhrout();
			}
		}
	}

	public function comreport()
	{
		if ($this->func->mayHandleReports()) {
			$this->model->confirmReport($_GET['id']);
			$this->func->info('Meldung wurde bestätigt!');

			return array(
				'status' => 1,
				'script' => 'reload();'
			);
		}
	}

	public function delreport()
	{
		if ($this->func->mayHandleReports()) {
			$this->model->delReport($_GET['id']);
			$this->func->info('Meldung wurde gelöscht!');

			return array(
				'status' => 1,
				'script' => 'reload();'
			);
		}
	}

	public function reportDialog()
	{
		$dialog = new XhrDialog();
		$dialog->setTitle($this->foodsaver['name'] . ' melden');

		global $g_data;
		$g_data['reportreason'] = 0;
		$dialog->addContent($this->view->reportDialog());
		$bid = 0;
		if (!isset($_GET['bid']) || (int)$_GET['bid'] == 0) {
			if ($betriebe = $this->model->getFoodsaverBetriebe($_GET['fsid'])) {
				$dialog->addContent($this->view->betriebList($betriebe));
			}
		} else {
			$bid = $_GET['bid'];
		}

		$dialog->addContent($this->v_utils->v_form_textarea('reportmessage', array('desc' => $this->func->s('reportmessage_desc'))));
		$dialog->addContent($this->v_utils->v_form_hidden('reportfsid', (int)$_GET['fsid']));
		$dialog->addContent($this->v_utils->v_form_hidden('reportbid', (int)$bid));

		$dialog->addOpt('width', '600', false);
		$dialog->addAbortButton();

		$dialog->addJs('
			$("#betrieb_id").change(function(){
				$("#reportbid").val($(this).val());
			});
			$("#reportreason").change(function(){
			var value = $(this).val();
			$("#reportreason ~ select").hide();
			$("#reportreason ~ div.cb").hide();
			$("#reportreason_" + value).show();
			$("#reportreason_" + value + "_sub").show();
		});	
		$("#reportreason ~ select").hide();
		$("#reportreason ~ div.cb").hide();');

		$dialog->addJs('$("#reportmessage").css("width","555px");');
		$dialog->addButton('Meldung senden', '
				
		if($("#reportreason").val() == 0)
		{
			pulseError("Gib Bitte einen Grund für die Meldung an!");		
		}
		else
		{
				
			var reason = $("#reportreason option:selected").text();
			
			if($("select#reportreason_" + $("#reportreason").val()).length > 0 && $("select#reportreason_" + $("#reportreason").val()).val() != 0)
			{
				reason += " => " + $("select#reportreason_" + $("#reportreason").val() + " option:selected").text();
			}
			if($("#reportreason_" + $("#reportreason").val() + " input:checked").length > 0 )
			{
				$("#reportreason_" + $("#reportreason").val() + " input:checked").each(function(){
					reason += " => " + $(this).parent().text();
					
				});
			}
			if($("select#reportreason_" + $("#reportreason").val() + "_sub").length > 0 && $("select#reportreason_" + $("#reportreason").val() + "_sub").val() != 0)
			{
				reason += " => " + $("select#reportreason_" + $("#reportreason").val() + "_sub" + " option:selected").text();
			}
			ajreq("betriebreport",{
				app:"report",
				bid: $("#reportbid").val(),
				fsid: $("#reportfsid").val(),
				reason_id: $("#reportreason").val(),
				reason: reason,
				msg: $("#reportmessage").val()
			});	
		}
		');

		return $dialog->xhrout();
	}

	public function betriebreport()
	{
		$reason_id = 1;
		if ($_GET['reason_id'] == 2) {
			$reason_id = 2;
		}
		$this->model->addBetriebReport($_GET['fsid'], $reason_id, $_GET['reason'], $_GET['msg'], (int)$_GET['bid']);

		return array(
			'status' => 1,
			'script' => '
				$(".xhrDialog").dialog("close");
				$(".xhrDialog").dialog("destroy");
				$(".xhrDialog").remove();
				
				pulseInfo("Danke Dir! Die Meldung wird an die verantwortlichen Personen weitergeleitet.");
				$("#reportmessage").val("");
				$("#reportreason ~ select").hide();
				$("#reportreason ~ div.cb").hide();'
		);
	}
}
