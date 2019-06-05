<?php

namespace Foodsharing\Modules\WorkGroup;

use Foodsharing\Lib\Xhr\XhrDialog;
use Foodsharing\Lib\Xhr\XhrResponses;
use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Core\DBConstants\Region\ApplyType;

class WorkGroupXhr extends Control
{
	private $workGroupGateway;
	private $responses;

	public function __construct(
			WorkGroupView $view,
			WorkGroupGateway $workGroupGateway
	) {
		$this->view = $view;
		$this->workGroupGateway = $workGroupGateway;
		$this->responses = new XhrResponses();

		parent::__construct();
	}

	public function apply()
	{
		$group = $this->workGroupGateway->getGroup($_GET['id']);
		if ($group) {
			$dialog = new XhrDialog();

			$dialog->addContent($this->view->applyForm($group));

			$dialog->setTitle('Bewerbung ' . $group['name']);
			$dialog->addButton('Bewerbung absenden',
				'ajreq(\'applysend\',{d:\'' . $dialog->getId() . '\',id:' . (int)$group['id'] . ', f:$(\'#apply-form\').serialize()})'
			);

			$dialog->setResizeable(false);
			$dialog->addOpt('width', 450);

			return $dialog->xhrout();
		}

		return $this->responses->fail_generic();
	}

	public function addtogroup()
	{
		if ($this->session->may('fs')) {
			$group = $this->workGroupGateway->getGroup($_GET['id']);
			if ($group && $group['apply_type'] == ApplyType::OPEN) {
				$this->workGroupGateway->addToGroup($_GET['id'], $this->session->id());

				return array(
					'status' => 1,
					'script' => 'goTo("/?page=bezirk&bid=' . (int)$_GET['id'] . '&sub=wall");'
				);
			}
		}

		return $this->responses->fail_generic();
	}

	public function applysend()
	{
		if (isset($_GET['f'])) {
			$output = array();
			parse_str($_GET['f'], $output);
			if (!empty($output)) {
				$groupId = $_GET['id'];
				$groupmail = $this->workGroupGateway->getGroupMail($groupId);
				if ($groupmail) {
					$group = $this->workGroupGateway->getGroup($groupId);
					if ($group) {
						$fsId = $this->session->id();
						$fs = $this->workGroupGateway->getFsWithMail($fsId);
						if ($fs) {
							$motivation = strip_tags($output['motivation']);
							$fahig = strip_tags($output['faehigkeit']);
							$erfahrung = strip_tags($output['erfahrung']);
							$zeit = strip_tags($output['zeit']);
							$zeit = substr($zeit, 0, 300);

							$content = array(
								'Motivation:' . "\n===========\n" . trim($motivation),
								'Fähigkeiten:' . "\n============\n" . trim($fahig),
								'Erfahrung:' . "\n==========\n" . trim($erfahrung),
								'Zeit:' . "\n=====\n" . trim($zeit)
							);

							$this->workGroupGateway->groupApply($groupId, $fsId, implode("\n\n", $content));

							$this->emailHelper->libmail(array(
								'email' => $fs['email'],
								'email_name' => $fs['name']
							), $groupmail, 'Bewerbung für ' . $group['name'], nl2br($fs['name'] . ' möchte gerne in der Arbeitsgruppe ' . $group['name'] . ' mitmachen.' . "\n\n" . implode("\n\n", $content)));

							return array(
								'status' => 1,
								'script' => 'pulseInfo("Bewerbung wurde abgeschickt!");$("#' . preg_replace('/[^a-z0-9\-]/', '', $_GET['d']) . '").dialog("close");'
							);
						}
					}
				}
			}
		}

		return $this->responses->fail_generic();
	}

	/*
	 * CONTACT GROUP VIA EMAIL
	 */

	public function sendtogroup()
	{
		if (!$this->session->id()) {
			return XhrResponses::PERMISSION_DENIED;
		}

		$group = $this->workGroupGateway->getGroup($_GET['id']);
		if ($group && !empty($group['email'])) {
			$message = $_GET['msg'];

			if (!empty($message)) {
				$userMail = $this->session->user('email');
				$recipients = array($group['email'], $userMail);

				$this->emailHelper->tplMail('general/workgroup_contact', $recipients, array(
					'gruppenname' => $group['name'],
					'message' => $message,
					'username' => $this->session->user('name'),
					'userprofile' => BASE_URL . '/profile/' . $this->session->id()
						), $userMail);

				return array(
					'status' => 1,
					'script' => 'pulseInfo("Nachricht wurde versendet!");'
				);
			}
		}

		return $this->responses->fail_generic();
	}

	public function contactgroup()
	{
		$group = $this->workGroupGateway->getGroup($_GET['id']);
		if ($group && !empty($group['email'])) {
			$dialog = new XhrDialog();
			$dialog->setTitle($group['name'] . ' kontaktieren');

			$dialog->addContent($this->view->contactgroup($group));

			$dialog->addAbortButton();
			$dialog->addButton('Nachricht senden', 'if($(\'#message\').val()!=\'\'){ajreq(\'sendtogroup\',{id:' . (int)$_GET['id'] . ',msg:$(\'#message\').val()});$(\'#' . $dialog->getId() . '\').dialog(\'close\');}else{pulseInfo(\'Schreib erst mal was ;)\');}');
			$dialog->addOpt('width', 500);
			$ret = $dialog->xhrout();
			$ret['script'] .= '$("#message").css("width","95%");$("#message").autosize();';

			return $ret;
		}

		return $this->responses->fail_generic();
	}
}
