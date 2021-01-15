<?php

namespace Foodsharing\Modules\Report;

use Foodsharing\Lib\Xhr\XhrDialog;
use Foodsharing\Modules\Bell\BellGateway;
use Foodsharing\Modules\Bell\DTO\Bell;
use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Core\DBConstants\Region\WorkgroupFunction;
use Foodsharing\Modules\Foodsaver\FoodsaverGateway;
use Foodsharing\Modules\Group\GroupFunctionGateway;
use Foodsharing\Modules\Mailbox\MailboxGateway;
use Foodsharing\Modules\Region\RegionGateway;
use Foodsharing\Permissions\ReportPermissions;
use Foodsharing\Utility\Sanitizer;
use Foodsharing\Utility\TimeHelper;

class ReportXhr extends Control
{
	private $foodsaver;
	private $reportGateway;
	private $foodsaverGateway;
	private $sanitizerService;
	private $timeHelper;
	private $reportPermissions;
	private $bellGateway;
	private $regionGateway;
	private $mailboxGateway;
	private $groupFunctionGateway;

	public function __construct(
		ReportGateway $reportGateway,
		ReportView $view,
		FoodsaverGateway $foodsaverGateway,
		Sanitizer $sanitizerService,
		TimeHelper $timeHelper,
		ReportPermissions $reportPermissions,
		BellGateway $bellGateway,
		RegionGateway $regionGateway,
		MailboxGateway $mailboxGateway,
		GroupFunctionGateway $groupFunctionGateway
	) {
		$this->view = $view;
		$this->reportGateway = $reportGateway;
		$this->foodsaverGateway = $foodsaverGateway;
		$this->sanitizerService = $sanitizerService;
		$this->timeHelper = $timeHelper;
		$this->reportPermissions = $reportPermissions;
		$this->bellGateway = $bellGateway;
		$this->regionGateway = $regionGateway;
		$this->mailboxGateway = $mailboxGateway;
		$this->groupFunctionGateway = $groupFunctionGateway;

		parent::__construct();

		if (isset($_GET['fsid'])) {
			$this->foodsaver = $this->foodsaverGateway->getFoodsaver($_GET['fsid']);
			$this->view->setFoodsaver($this->foodsaver);
		}
	}

	public function loadReport(): ?array
	{
		if ($this->reportPermissions->mayHandleReports() && $report = $this->reportGateway->getReport($_GET['id'])) {
			$reason = explode('=>', $report['tvalue']);

			$dialog = new XhrDialog();
			$dialog->setTitle('Meldung über ' . $report['fs_name'] . ' ' . $report['fs_nachname']);

			$content = $this->v_utils->v_input_wrapper('Report ID', $report['id']);
			$content .= $this->v_utils->v_input_wrapper('Zeitpunkt', $this->timeHelper->niceDate($report['time_ts']));

			if (isset($report['betrieb'])) {
				$content .= $this->v_utils->v_input_wrapper('Zugeordneter Betrieb', '<a href="/?page=fsbetrieb&id=' . $report['betrieb']['id'] . '">' . htmlspecialchars($report['betrieb']['name']) . '</a>');
			}

			if (\is_array($reason)) {
				$out = '<ul>';
				foreach ($reason as $r) {
					$out .= '<li>' . htmlspecialchars(trim($r)) . '</li>';
				}
				$out .= '</ul>';

				$content .= $this->v_utils->v_input_wrapper('Grund', $out);
			}

			if (!empty($report['msg'])) {
				$content .= $this->v_utils->v_input_wrapper('Beschreibung', $this->sanitizerService->plainToHtml($report['msg']));
			}

			$content .= $this->v_utils->v_input_wrapper('Gemeldet von', '<a href="/profile/' . (int)$report['rp_id'] . '">' . htmlspecialchars($report['rp_name'] . ' ' . $report['rp_nachname']) . '</a>');
			$dialog->addContent($content);
			$dialog->addOpt('width', '$(window).width()*0.9');

			$dialog->addButton('Alle Meldungen über ' . $report['fs_name'], 'goTo(\'/?page=report&sub=foodsaver&id=' . $report['fs_id'] . '\');');

			if ($report['committed'] === 0) {
				$dialog->addButton('Meldung zugestellt', 'ajreq(\'comReport\',{\'id\':' . (int)$_GET['id'] . '});');
			}
			$dialog->addButton('Löschen', 'if(confirm(\'Diese Meldung wirklich löschen?\')){ajreq(\'delReport\',{id:' . $report['id'] . '});$(\'#' . $dialog->getId() . '\').dialog(\'close\');}');

			return $dialog->xhrout();
		}

		return null;
	}

	public function comReport(): ?array
	{
		if ($this->reportPermissions->mayHandleReports()) {
			$this->reportGateway->confirmReport($_GET['id']);
			$this->flashMessageHelper->info('Meldung wurde bestätigt!');

			return [
				'status' => 1,
				'script' => 'reload();'
			];
		}

		return null;
	}

	public function delReport(): ?array
	{
		if ($this->reportPermissions->mayHandleReports()) {
			$this->reportGateway->delReport($_GET['id']);
			$this->flashMessageHelper->success('Meldung wurde gelöscht!');

			return [
				'status' => 1,
				'script' => 'reload();'
			];
		}

		return null;
	}

	// TODO : Modernisieren
	public function reportDialog(): array
	{
		// Only show Dialog when a local report group exists

		$dialog = new XhrDialog();
		$dialog->setTitle($this->foodsaver['name'] . ' melden');

		global $g_data;
		$g_data['reportreason'] = 0;
		$dialog->addContent($this->view->reportDialog());
		$storeId = 0;
		if (!isset($_GET['bid']) || (int)$_GET['bid'] === 0) {
			if ($stores = $this->reportGateway->getFoodsaverBetriebe($_GET['fsid'])) {
				$dialog->addContent($this->view->betriebList($stores));
			}
		} else {
			$storeId = $_GET['bid'];
		}

		$dialog->addContent($this->v_utils->v_form_textarea('reportmessage', [
			'desc' => $this->translator->trans('reports.info'),
		]));
		$dialog->addContent($this->v_utils->v_form_hidden('reportfsid', (int)$_GET['fsid']));
		$dialog->addContent($this->v_utils->v_form_hidden('reportbid', $storeId));
		$dialog->addOpt('width', '$(window).width()*0.9', false);
		$dialog->addAbortButton();

		$dialog->addJs('
			$("#betrieb_id").on("change", function(){
				$("#reportbid").val($(this).val());
			});
			$("#reportreason").on("change", function(){
			var value = $(this).val();
			$("#reportreason ~ select").hide();
			$("#reportreason ~ div.cb").hide();
			$("#reportreason_" + value).show();
			$("#reportreason_" + value + "_sub").show();
		});
		$("#reportreason ~ select").hide();
		$("#reportreason ~ div.cb").hide();');

		$dialog->addJs('$("#reportmessage").css("width","$(window).width()*0.6");');
		$dialog->addButton('Meldung senden', '

		if ($("#reportreason").val() == 0) {
			pulseError("Gib Bitte einen Grund für die Meldung an!");
		} else {
			var reason = $("#reportreason option:selected").text();

			if ($("select#reportreason_" + $("#reportreason").val()).length > 0 && $("select#reportreason_" + $("#reportreason").val()).val() != 0) {
				reason += " => " + $("select#reportreason_" + $("#reportreason").val() + " option:selected").text();
			}

			if ($("#reportreason_" + $("#reportreason").val() + " input:checked").length > 0) {
				$("#reportreason_" + $("#reportreason").val() + " input:checked").each(function(){
					reason += " => " + $(this).parent().text();
				});
			}

			if ($("select#reportreason_" + $("#reportreason").val() + "_sub").length > 0 && $("select#reportreason_" + $("#reportreason").val() + "_sub").val() != 0) {
				reason += " => " + $("select#reportreason_" + $("#reportreason").val() + "_sub" + " option:selected").text();
			}

			ajreq("betriebReport", {
				app: "report",
				bid: $("#reportbid").val(),
				fsid: $("#reportfsid").val(),
				reason_id: $("#reportreason").val(),
				reason: reason,
				msg: $("#reportmessage").val()
			});
		}
		');
		$dialog->noOverflow();

		return $dialog->xhrout();

		//return ['status' => 0];
	}

	public function betriebReport(): array
	{
		$reason_id = 1;
		if ($_GET['reason_id'] === 2) {
			$reason_id = 2;
		}
		$this->reportGateway->addBetriebReport($_GET['fsid'], $this->session->id(), $reason_id, $_GET['reason'], $_GET['msg'], (int)$_GET['bid']);

		$reportedFs = $this->foodsaverGateway->getFoodsaverBasics($_GET['fsid']);
		$bellData = Bell::create(
			'new_report_title',
			'report_reason',
			'far fa-life-ring fa-fw',
			['href' => '/?page=report&bid=' . $reportedFs['bezirk_id']],
			[
					'name' => $reportedFs['name'] . ' ' . $reportedFs['nachname'],
					'reason' => $_GET['reason']
			],
			'new-report-' . $reportedFs['id'],
			true
			);

		$regionReportGroupId = $this->groupFunctionGateway->getRegionFunctionGroupId($reportedFs['bezirk_id'], WorkgroupFunction::REPORT);
		if ($regionReportGroupId) {
			$reportBellRecipients = $this->foodsaverGateway->getAdminsOrAmbassadors($regionReportGroupId);
			if (!in_array($reportedFs['id'], $reportBellRecipients)) {
				$this->bellGateway->addBell($reportBellRecipients, $bellData);
			} else {
				$regionArbitrationGroupId = $this->groupFunctionGateway->getRegionFunctionGroupId($reportedFs['bezirk_id'], WorkgroupFunction::ARBITRATION);
				$reportBellRecipients = $this->foodsaverGateway->getAdminsOrAmbassadors($regionArbitrationGroupId);
				$this->bellGateway->addBell($reportBellRecipients, $bellData);
			}
		}

		return [
			'status' => 1,
			'script' => '
				$(".xhrDialog").dialog("close");
				$(".xhrDialog").dialog("destroy");
				$(".xhrDialog").remove();

				pulseInfo("Danke Dir! Die Meldung wird an die verantwortlichen Personen weitergeleitet.");
				$("#reportmessage").val("");
				$("#reportreason ~ select").hide();
				$("#reportreason ~ div.cb").hide();'
		];
	}
}
