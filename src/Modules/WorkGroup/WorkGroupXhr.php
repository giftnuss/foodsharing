<?php

namespace Foodsharing\Modules\WorkGroup;

use Foodsharing\Lib\Xhr\XhrDialog;
use Foodsharing\Lib\Xhr\XhrResponses;
use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Core\DBConstants\Region\ApplyType;

class WorkGroupXhr extends Control
{
	private WorkGroupGateway $workGroupGateway;
	private XhrResponses $responses;

	public function __construct(
		WorkGroupView $view,
		WorkGroupGateway $workGroupGateway
	) {
		$this->view = $view;
		$this->workGroupGateway = $workGroupGateway;
		$this->responses = new XhrResponses();

		parent::__construct();
	}

	public function apply(): array
	{
		$group = $this->workGroupGateway->getGroup($_GET['id']);
		if (!$group) {
			return $this->responses->fail_generic();
		}

		$dialog = new XhrDialog();

		$dialog->addContent($this->view->applyForm($group));

		$dialog->setTitle($this->translator->trans('group.apply.title', ['{group}' => $group['name']]));
		$dialog->addButton($this->translator->trans('group.apply.send'),
			'ajreq(\'applysend\','
			. '{d:\'' . $dialog->getId() . '\''
			. ',id:' . (int)$group['id']
			. ',f: $(\'#apply-form\').serialize()'
			. '})'
		);

		$dialog->setResizeable(false);
		$dialog->addOpt('width', 450);

		return $dialog->xhrout();
	}

	public function addtogroup(): array
	{
		if (!$this->session->may('fs')) {
			return $this->responses->fail_generic();
		}

		$group = $this->workGroupGateway->getGroup($_GET['id']);
		if (!$group || $group['apply_type'] != ApplyType::OPEN) {
			return $this->responses->fail_generic();
		}

		$this->workGroupGateway->addToGroup($_GET['id'], $this->session->id());

		$url = urlencode('/?page=bezirk&bid=' . (int)$_GET['id'] . '&sub=wall');

		return [
			'status' => 1,
			'script' => 'goTo("/?page=relogin&url=' . $url . '");',
		];
	}

	public function applysend(): array
	{
		if (!isset($_GET['f'])) {
			return $this->responses->fail_generic();
		}

		$output = [];
		parse_str($_GET['f'], $output);
		if (empty($output)) {
			return $this->responses->fail_generic();
		}

		$groupId = $_GET['id'];
		$fsId = $this->session->id();
		$groupmail = $this->workGroupGateway->getGroupMail($groupId);
		$group = $this->workGroupGateway->getGroup($groupId);
		$fs = $this->workGroupGateway->getFsWithMail($fsId);
		if (!$groupmail || !$group || !$fs) {
			return $this->responses->fail_generic();
		}

		$motivation = strip_tags($output['motivation']);
		$fahig = strip_tags($output['faehigkeit']);
		$erfahrung = strip_tags($output['erfahrung']);
		$zeit = strip_tags($output['zeit']);
		$zeit = substr($zeit, 0, 300);

		// TODO translator
		$content = [
			'Motivation:' . "\n===========\n" . trim($motivation),
			'Fähigkeiten:' . "\n============\n" . trim($fahig),
			'Erfahrung:' . "\n==========\n" . trim($erfahrung),
			'Zeit:' . "\n=====\n" . trim($zeit),
		];

		$this->workGroupGateway->groupApply($groupId, $fsId, implode("\n\n", $content));

		$this->emailHelper->libmail(
			[
				'email' => $fs['email'],
				'email_name' => $fs['name'],
			],
			$groupmail,
			$this->translator->trans('group.apply.title', ['{group}' => $group['name']]),
			nl2br($this->translator->trans('group.apply.summary', [
				'{name}' => $fs['name'],
				'{group}' => $group['name'],
			]) . "\n\n" . implode("\n\n", $content))
		);

		return [
			'status' => 1,
			'script' => 'pulseInfo("' . $this->translator->trans('group.apply.sent') . '");'
				. '$("#' . preg_replace('/[^a-z0-9\-]/', '', $_GET['d']) . '").dialog("close");',
		];
	}

	/** Contact group via email.
	 * @return array|string
	 */
	public function sendtogroup()
	{
		if (!$this->session->may()) {
			return XhrResponses::PERMISSION_DENIED;
		}

		$group = $this->workGroupGateway->getGroup($_GET['id']);
		if (!$group || empty($group['email'])) {
			return $this->responses->fail_generic();
		}

		$message = $_GET['msg'];
		if (empty($message)) {
			return $this->responses->fail_generic();
		}

		$userMail = $this->session->user('email');
		$recipients = [$group['email'], $userMail];

		$this->emailHelper->tplMail('general/workgroup_contact', $recipients, [
			'gruppenname' => $group['name'],
			'message' => $message,
			'username' => $this->session->user('name'),
			'userprofile' => BASE_URL . '/profile/' . $this->session->id(),
		], $userMail);

		return [
			'status' => 1,
			'script' => 'pulseInfo("' . $this->translator->trans('group.contact.sent') . '");',
		];
	}

	public function contactgroup(): array
	{
		$group = $this->workGroupGateway->getGroup($_GET['id']);
		if (!$group || empty($group['email'])) {
			return $this->responses->fail_generic();
		}

		$dialog = new XhrDialog();
		$dialog->setTitle($this->translator->trans('group.contact.title', ['{group}' => $group['name']]));

		$dialog->addContent($this->view->contactgroup($group));

		$dialog->addAbortButton();
		$dialog->addButton($this->translator->trans('group.contact.send'),
			'if ($(\'#message\').val() != \'\') {'
				. 'ajreq(\'sendtogroup\','
				. '{id:' . (int)$_GET['id']
				. ',msg:$(\'#message\').val()'
				. '});'
				. '$(\'#' . $dialog->getId() . '\').dialog(\'close\');'
			. '} else {'
				. 'pulseInfo(\'' . $this->translator->trans('group.contact.empty') . '\');'
			. '}'
		);
		$dialog->addOpt('width', 500);
		$ret = $dialog->xhrout();
		$ret['script'] .= '$("#message").css("width", "95%"); $("#message").autosize();';

		return $ret;
	}
}
