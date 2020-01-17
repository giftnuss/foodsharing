<?php

namespace Foodsharing\Modules\Mailbox;

use Foodsharing\Modules\Core\Control;
use Foodsharing\Permissions\MailboxPermissions;
use Foodsharing\Services\SanitizerService;

class MailboxControl extends Control
{
	private $sanitizerService;
	private $mailboxGateway;
	private $mailboxPermissions;

	public function __construct(
		MailboxView $view,
		SanitizerService $sanitizerService,
		MailboxGateway $mailboxGateway,
		MailboxPermissions $mailboxPermissions
	) {
		$this->view = $view;
		$this->sanitizerService = $sanitizerService;
		$this->mailboxGateway = $mailboxGateway;
		$this->mailboxPermissions = $mailboxPermissions;

		parent::__construct();

		if (!$this->session->may()) {
			$this->routeHelper->goLogin();
		}

		if (!$this->mailboxPermissions->mayHaveMailbox()) {
			$this->pageHelper->addContent($this->v_utils->v_info($this->translationHelper->s('bieb_quiz_required')));
		}
	}

	public function dlattach()
	{
		if (isset($_GET['mid'], $_GET['i'])) {
			if ($m = $this->mailboxGateway->getAttachmentFileInfo($_GET['mid'])) {
				if ($this->mailboxPermissions->mayMailbox($m['mailbox_id'])) {
					if ($attach = json_decode($m['attach'], true)) {
						if (isset($attach[(int)$_GET['i']])) {
							$file = 'data/mailattach/' . $attach[(int)$_GET['i']]['filename'];

							$Dateiname = $attach[(int)$_GET['i']]['origname'];
							$size = filesize($file);

							$mime = $attach[(int)$_GET['i']]['mime'];
							if ($mime) {
								header('Content-Type: ' . $mime);
							}
							header('Content-Disposition: attachment; filename="' . $Dateiname . '"');
							header("Content-Length: $size");
							readfile($file);
							exit();
						}
					}
				}
			}
		}

		$this->routeHelper->goPage('mailbox');
	}

	public function index()
	{
		$this->pageHelper->addBread('Mailboxen');

		if ($boxes = $this->mailboxGateway->getBoxes($this->session->isAmbassador(), $this->session->id(), $this->session->may('bieb'))) {
			if (isset($_GET['show']) && (int)$_GET['show']) {
				if ($this->mailboxPermissions->mayMessage($_GET['show'])) {
					$this->pageHelper->addJs('ajreq("loadMail",{id:' . (int)$_GET['show'] . '});');
				}
			}

			$mailadresses = $this->mailboxGateway->getMailAdresses($this->session->id());

			$this->pageHelper->addContent($this->view->folder($boxes), CNT_LEFT);
			$this->pageHelper->addContent($this->view->folderlist($boxes, $mailadresses));
			$this->pageHelper->addContent($this->view->options(), CNT_LEFT);
		}

		if (isset($_GET['mailto']) && $this->emailHelper->validEmail($_GET['mailto'])) {
			$this->pageHelper->addJs('mb_mailto("' . $_GET['mailto'] . '");');
		}
	}

	public function newbox()
	{
		$this->pageHelper->addBread('Mailbox Manager', '/?page=mailbox&a=manage');
		$this->pageHelper->addBread('Neue Mailbox');

		if ($this->mailboxPermissions->mayAddMailboxes()) {
			if (isset($_POST['name'])) {
				if ($mailbox = $this->mailboxGateway->filterName($_POST['name'])) {
					if ($this->mailboxGateway->addMailbox($mailbox, 1)) {
						$this->flashMessageHelper->info($this->translationHelper->s('mailbox_add_success'));
						$this->routeHelper->go('/?page=mailbox&a=manage');
					} else {
						$this->flashMessageHelper->error($this->translationHelper->s('mailbox_already_exists'));
					}
				}
			}
			$this->pageHelper->addContent($this->view->manageOpt(), CNT_LEFT);
			$this->pageHelper->addContent($this->view->mailboxform());
		}
	}

	public function manage()
	{
		$this->pageHelper->addBread('Mailbox Manager');
		if ($this->mailboxPermissions->mayManageMailboxes()) {
			if (isset($_POST['mbid'])) {
				global $g_data;

				$index = 'foodsaver_' . (int)$_POST['mbid'];

				$this->sanitizerService->handleTagSelect($index);

				if ($this->mailboxGateway->updateMember($_POST['mbid'], $g_data[$index])) {
					$this->flashMessageHelper->info($this->translationHelper->s('edit_success'));
					$this->routeHelper->go('/?page=mailbox&a=manage');
				}
			}

			if ($boxes = $this->mailboxGateway->getMemberBoxes()) {
				$this->pageHelper->addJs('

				');
				foreach ($boxes as $b) {
					global $g_data;
					$g_data['foodsaver_' . $b['id']] = $b['member'];
					$this->pageHelper->addContent($this->view->manageMemberBox($b));
				}
			}

			$this->pageHelper->addContent($this->view->manageOpt(), CNT_LEFT);
		} else {
			$this->flashMessageHelper->error($this->translationHelper->s('access_error'));
			$this->routeHelper->goPage('dashboard');
		}
	}
}
