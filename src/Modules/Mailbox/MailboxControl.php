<?php

namespace Foodsharing\Modules\Mailbox;

use Foodsharing\Modules\Core\Control;
use Foodsharing\Services\SanitizerService;

class MailboxControl extends Control
{
	private $sanitizerService;

	public function __construct(MailboxModel $model, MailboxView $view, SanitizerService $sanitizerService)
	{
		$this->model = $model;
		$this->view = $view;
		$this->sanitizerService = $sanitizerService;

		parent::__construct();
	}

	public function dlattach()
	{
		if (isset($_GET['mid'], $_GET['i'])) {
			if ($m = $this->model->getValues(array('mailbox_id', 'attach'), 'mailbox_message', $_GET['mid'])) {
				if ($this->model->mayMailbox($m['mailbox_id'])) {
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

		$this->func->goPage('mailbox');
	}

	public function index()
	{
		$this->pageCompositionHelper->addBread('Mailboxen');

		if ($boxes = $this->model->getBoxes()) {
			if (isset($_GET['show']) && (int)$_GET['show']) {
				if ($this->model->mayMessage($_GET['show'])) {
					$this->pageCompositionHelper->addJs('ajreq("loadMail",{id:' . (int)$_GET['show'] . '});');
				}
			}

			$mailadresses = $this->model->getMailAdresses();

			$this->pageCompositionHelper->addContent($this->view->folder($boxes), CNT_LEFT);
			$this->pageCompositionHelper->addContent($this->view->folderlist($boxes, $mailadresses));
			$this->pageCompositionHelper->addContent($this->view->options(), CNT_LEFT);
		}

		if (isset($_GET['mailto']) && $this->func->validEmail($_GET['mailto'])) {
			$this->pageCompositionHelper->addJs('mb_mailto("' . $_GET['mailto'] . '");');
		}
	}

	public function newbox()
	{
		$this->pageCompositionHelper->addBread('Mailbox Manager', '/?page=mailbox&a=manage');
		$this->pageCompositionHelper->addBread('Neue Mailbox');

		if ($this->session->isOrgaTeam()) {
			if (isset($_POST['name'])) {
				if ($mailbox = $this->model->filterName($_POST['name'])) {
					if ($this->model->addMailbox($mailbox, 1)) {
						$this->func->info($this->func->s('mailbox_add_success'));
						$this->func->go('/?page=mailbox&a=manage');
					} else {
						$this->func->error($this->func->s('mailbox_already_exists'));
					}
				}
			}
			$this->pageCompositionHelper->addContent($this->view->manageOpt(), CNT_LEFT);
			$this->pageCompositionHelper->addContent($this->view->mailboxform());
		}
	}

	public function manage()
	{
		$this->pageCompositionHelper->addBread('Mailbox Manager');
		if ($this->session->isOrgaTeam()) {
			if (isset($_POST['mbid'])) {
				global $g_data;

				$index = 'foodsaver_' . (int)$_POST['mbid'];

				$this->sanitizerService->handleTagselect($index);

				if ($this->model->updateMember($_POST['mbid'], $g_data[$index])) {
					$this->func->info($this->func->s('edit_success'));
					$this->func->go('/?page=mailbox&a=manage');
				}
			}

			if ($boxes = $this->model->getMemberBoxes()) {
				$this->pageCompositionHelper->addJs('
							
				');
				foreach ($boxes as $b) {
					global $g_data;
					$g_data['foodsaver_' . $b['id']] = $b['member'];
					$this->pageCompositionHelper->addContent($this->view->manageMemberBox($b));
				}
			}

			$this->pageCompositionHelper->addContent($this->view->manageOpt(), CNT_LEFT);
		}
	}
}
