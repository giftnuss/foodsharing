<?php

namespace Foodsharing\Modules\Report;

use Foodsharing\Lib\Xhr\XhrDialog;
use Foodsharing\Modules\Bell\BellGateway;
use Foodsharing\Modules\Core\Control;
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
			$dialog->setTitle($this->translator->trans('profile.report.xhr.reporting') . ' ' . $report['fs_name'] . ' ' . $report['fs_nachname']);

			$content = $this->v_utils->v_input_wrapper($this->translator->trans('profile.report.xhr.Report ID'), $report['id']);
			$content .= $this->v_utils->v_input_wrapper($this->translator->trans('reports.time'), $this->timeHelper->niceDate($report['time_ts']));

			if (isset($report['betrieb'])) {
				$content .= $this->v_utils->v_input_wrapper($this->translator->trans('reports.store'), '<a href="/?page=fsbetrieb&id=' . $report['betrieb']['id'] . '">' . htmlspecialchars($report['betrieb']['name']) . '</a>');
			}

			if (\is_array($reason)) {
				$out = '<ul>';
				foreach ($reason as $r) {
					$out .= '<li>' . htmlspecialchars(trim($r)) . '</li>';
				}
				$out .= '</ul>';

				$content .= $this->v_utils->v_input_wrapper($this->translator->trans('reports.reason'), $out);
			}

			if (!empty($report['msg'])) {
				$content .= $this->v_utils->v_input_wrapper($this->translator->trans('basket.description'), $this->sanitizerService->plainToHtml($report['msg']));
			}

			$content .= $this->v_utils->v_input_wrapper($this->translator->trans('profile.report.xhr.reportee'), '<a href="/profile/' . (int)$report['rp_id'] . '">' . htmlspecialchars($report['rp_name'] . ' ' . $report['rp_nachname']) . '</a>');
			$dialog->addContent($content);
			$dialog->addOpt('width', '$(window).width()*0.9');

			$dialog->addButton($this->translator->trans('profile.report.xhr.allofthem') . ' ' . $report['fs_name'], 'goTo(\'/?page=report&sub=foodsaver&id=' . $report['fs_id'] . '\');');

			if ($report['committed'] === 0) {
				$dialog->addButton($this->translator->trans('profile.report.xhr.delivered'), 'ajreq(\'comReport\',{\'id\':' . (int)$_GET['id'] . '});');
			}
			$dialog->addButton($this->translator->trans('button.delete'), 'if(confirm(' . $this->translator->trans('profile.report.xhr.plsconfirm') . ')){ajreq(\'delReport\',{id:' . $report['id'] . '});$(\'#' . $dialog->getId() . '\').dialog(\'close\');}');

			return $dialog->xhrout();
		}

		return null;
	}

	public function comReport(): ?array
	{
		if ($this->reportPermissions->mayHandleReports()) {
			$this->reportGateway->confirmReport($_GET['id']);
			$this->flashMessageHelper->info($this->translator->trans('profile.report.xhr.confirmed'));

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
			$this->flashMessageHelper->success($this->translator->trans('profile.report.xhr.xxx'));

			return [
				'status' => 1,
				'script' => 'reload();'
			];
		}

		return null;
	}
}
