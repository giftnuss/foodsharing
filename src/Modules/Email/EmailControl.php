<?php

namespace Foodsharing\Modules\Email;

use DOMDocument;
use Exception;
use Flourish\fImage;
use Foodsharing\Lib\Db\Db;
use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Foodsaver\FoodsaverGateway;
use Foodsharing\Modules\Mailbox\MailboxModel;
use Foodsharing\Modules\Region\RegionGateway;
use Foodsharing\Modules\Store\StoreGateway;

class EmailControl extends Control
{
	private $mbmodel;
	private $storeGateway;
	private $foodsaverGateway;
	private $emailGateway;
	private $regionGateway;

	public function __construct(Db $model, MailboxModel $mbmodel, StoreGateway $storeGateway, FoodsaverGateway $foodsaverGateway, EmailGateway $emailGateway, RegionGateway $regionGateway)
	{
		$this->model = $model;
		$this->mbmodel = $mbmodel;
		$this->storeGateway = $storeGateway;
		$this->foodsaverGateway = $foodsaverGateway;
		$this->emailGateway = $emailGateway;
		$this->regionGateway = $regionGateway;

		parent::__construct();

		if (!$this->session->may('orga')) {
			$this->func->go('/');
		}
	}

	public function index()
	{
		$this->handleEmail();
		$this->func->addBread($this->func->s('mailinglist'), '/?page=email');

		if ($emailstosend = $this->emailGateway->getEmailsToSend($this->session->id())) {
			$this->func->addContent($this->v_email_statusbox($emailstosend));
		}

		$recip = '';
		if ($this->session->isOrgaTeam()) {
			$recip = $this->v_utils->v_form_recip_chooser();
		} elseif ($this->session->isAmbassador()) {
			$recip = $this->v_utils->v_form_recip_chooser_mini();
		}
		global $g_data;
		if (!isset($g_data['message'])) {
			$g_data['message'] = '<p><strong>{ANREDE} {NAME}</strong><br /><br /><br />';
		}

		$boxes = $this->mbmodel->getBoxes();
		foreach ($boxes as $key => $b) {
			$boxes[$key]['name'] = $b['name'] . '@' . NOREPLY_EMAIL_HOST;
		}
		$this->func->addContent($this->v_utils->v_form('Nachrichten Verteiler', array(
			$this->v_utils->v_field(
				$recip .
				$this->v_utils->v_form_select('mailbox_id', array('values' => $boxes, 'required' => true)) .
				$this->v_utils->v_form_text('subject', array('required' => true)) .
				$this->v_utils->v_form_file('attachement'),

				$this->func->s('mailing_list'),
				array('class' => 'ui-padding')
			),
			$this->v_utils->v_field($this->v_utils->v_form_tinymce('message', array('nowrapper' => true, 'type' => 'email')), $this->func->s('message'))
		), array('submit' => 'Zum Senden Vorbereiten')));

		$this->func->addStyle('#testemail{width:91%;}');

		$g_data['testemail'] = $this->model->getVal('email', 'foodsaver', $this->session->id());

		$this->func->addContent($this->v_utils->v_field($this->v_utils->v_form_text('testemail') . $this->v_utils->v_input_wrapper('', '<a class="button" href="#" onclick="ajreq(\'testmail\',{email:$(\'#testemail\').val(),subject:$(\'#subject\').val(),message:$(\'#message\').tinymce().getContent()},\'post\');return false;">Test-Mail senden</a>'), 'Newsletter Testen', array('class' => 'ui-padding')), CNT_RIGHT);

		$this->func->addJs("$('#rightmenu').menu();");
		$this->func->addContent($this->v_utils->v_field('<div class="ui-padding">' . $this->func->s('personal_styling_desc') . '</div>', $this->func->s('personal_styling')), CNT_RIGHT);

		$this->func->addContent('
	<div id="dialog-confirm" title="E-Mail senden?" style="display:none">
	<p><span class="ui-icon ui-icon-alert" style="float: left; margin: 0 7px 20px 0;"></span>' . $this->func->s('shure') . '</p>
	</div>
	<h3 class="head ui-widget-header ui-corner-top">von Dir gesendete Mails</h3>
	<div class="ui-widget ui-widget-content ui-corner-bottom margin-bottom">
	<ul id="rightmenu">', CNT_RIGHT);

		$i = 0;
		$divs = '';
		if ($mails = $this->emailGateway->getSendMails($this->session->id())) {
			foreach ($mails as $m) {
				++$i;
				$this->func->addContent('<li><a href="#" onclick="$(\'#right-' . $i . '\').dialog(\'open\');return false;">' . date('d.m.', strtotime($m['zeit'])) . ' ' . $m['name'] . '</a></li>', CNT_RIGHT);
				$divs .= '<div id="right-' . $i . '" style="display:none;">' . nl2br($m['message']) . '</div>';
				$this->func->addJs('$("#right-' . $i . '").dialog({autoOpen:false,title:"' . $this->sanitizerService->jsSafe($m['name'], '"') . '",modal:true});');
			}
		}
		$this->func->addContent('</ul></div>' . $divs, CNT_RIGHT);
	}

	private function handleEmail()
	{
		if ($this->func->submitted()) {
			$betreff = $_POST['subject'];
			$nachricht = $_POST['message'];
			$mailbox_id = $_POST['mailbox_id'];

			$nachricht = $this->handleImages($nachricht);

			$data = $this->func->getPostData();

			$foodsaver = array();

			if ($this->session->isAmbassador() || $this->session->isOrgaTeam()) {
				if ($data['recip_choose'] == 'bezirk') {
					$region_ids = $this->regionGateway->listIdsForDescendantsAndSelf($this->session->getCurrentBezirkId());
					$foodsaver = $this->foodsaverGateway->getEmailAdressen($region_ids);
				} elseif ($data['recip_choose'] == 'botschafter') {
					$foodsaver = $this->foodsaverGateway->getAllBotschafter();
				} elseif ($data['recip_choose'] == 'orgateam') {
					$foodsaver = $this->foodsaverGateway->getOrgateam();
				}
			}
			if ($this->session->isOrgaTeam()) {
				if ($data['recip_choose'] == 'all') {
					$foodsaver = $this->foodsaverGateway->getAllEmailFoodsaver();
				} elseif ($data['recip_choose'] == 'newsletter') {
					$foodsaver = $this->foodsaverGateway->getAllEmailFoodsaver(true);
				} elseif ($data['recip_choose'] == 'newsletter_all') {
					$foodsaver = $this->foodsaverGateway->getAllEmailFoodsaver(true, false);
				} elseif ($data['recip_choose'] == 'newsletter_only_foodsharer') {
					$foodsaver = $this->model->q('
						SELECT 	`id`,`email`
						FROM `fs_foodsaver`
						WHERE newsletter = 1 AND rolle = 0 AND `active` = 1 AND deleted_at IS NULL
					');
				} elseif ($data['recip_choose'] == 'all_no_botschafter') {
					$foodsaver = $this->foodsaverGateway->getAllFoodsaverNoBotschafter();
				} elseif ($data['recip_choose'] == 'storemanagers') {
					$foodsaver = $this->storeGateway->getAllStoreManagers();
				} elseif ($data['recip_choose'] == 'storemanagers_and_ambs') {
					$foodsaver1 = $this->storeGateway->getAllStoreManagers();
					$foodsaver2 = $this->foodsaverGateway->getAllBotschafter();
					$tmp = array_merge($foodsaver1, $foodsaver2);
					$foodsaver = array();
					foreach ($tmp as $t) {
						$foodsaver[$t['id']] = $t;
					}
				} elseif ($data['recip_choose'] == 'manual') {
					$foodsaver = $data['recip_choosemanual'];
					str_replace(array("\r"), '', $foodsaver);
					$foodsaver = explode("\n", $foodsaver);

					$bezirk = $this->func->getBezirk();

					$count = 0;
					foreach ($foodsaver as $i => $fs) {
						$arr = explode(';', $fs);

						foreach ($arr as $y => $a) {
							$arr[$y] = trim($a);
						}

						$name = '';
						$email = $arr[0];

						if (isset($arr[1])) {
							$name = $arr[1];
						}

						if ($this->func->validEmail($email)) {
							$this->func->libmail($bezirk, $email, $betreff, str_replace('{NAME}', $name, $nachricht));
							++$count;
						} else {
							unset($foodsaver[$i]);
						}
					}

					$this->func->info('Die E-Mail wurde erfolgreich an ' . $count . ' E-Mail-Adressen gesendet');

					$foodsaver = array();
				} elseif ($data['recip_choose'] == 'filialbez') {
					// TODO can probably be removed
					$foodsaver = $this->storeGateway->getEmailBiepBez($data['recip_choose-choose']);
				} elseif (isset($data['recip_choose-choose'])) {
					if ($data['recip_choose'] == 'choosebot') {
						$foodsaver = $this->foodsaverGateway->getEmailBotFromBezirkList($data['recip_choose-choose']);
					} else {
						$foodsaver = $this->foodsaverGateway->getEmailFoodSaverFromBezirkList($data['recip_choose-choose']);
					}
				}
			} else {
				$region_ids = $this->regionGateway->listIdsForDescendantsAndSelf($this->session->getCurrentBezirkId());
				$foodsaver = $this->foodsaverGateway->getEmailAdressen($region_ids);
			}

			if (!empty($foodsaver)) {
				$attach = $this->handleAttach('attachement');

				$out = array();
				foreach ($foodsaver as $fs) {
					$out[$fs['id']] = $fs;
				}
				$foodsaver = array();
				foreach ($out as $o) {
					$foodsaver[] = $o;
				}
				$this->emailGateway->initEmail($this->session->id(), $mailbox_id, $foodsaver, $nachricht, $betreff, $attach);
				$this->func->goPage();
			} elseif ($data['recip_choose'] != 'manual') {
				$this->func->error('In den ausgew&auml;hlten Bezirken gibt es noch keine Foodsaver');
			}
		}
	}

	private function handleAttach($name)
	{
		if (isset($_FILES[$name]) && $_FILES[$name]['size'] > 0) {
			$datei = $_FILES[$name]['tmp_name'];
			$size = $_FILES[$name]['size'];
			$datein = $_FILES[$name]['name'];
			$datein = strtolower($datein);
			$datein = str_replace('.jpeg', '.jpg', $datein);
			$dateiendung = strtolower(substr($datein, strlen($datein) - 4, 4));

			$new_name = bin2hex(random_bytes(16)) . $dateiendung;
			move_uploaded_file($datei, './data/attach/' . $new_name);

			return array(
				'name' => $datein,
				'path' => './data/attach/' . $new_name,
				'uname' => $new_name,
				'mime' => mime_content_type('./data/attach/' . $new_name),
				'size' => $size
			);
		}

		return false;
	}

	private function v_email_statusbox($mail)
	{
		$out = '';

		$recip = $this->model->qCol('
			SELECT 	CONCAT(fs.name," ",fs.nachname)
			FROM 	`fs_email_status` e,
					`fs_foodsaver` fs
			WHERE 	e.foodsaver_id = fs.id
			AND 	e.email_id = ' . $mail['id'] . '
		');

		$id = $this->func->id('mailtosend');

		$this->func->addJs('
			$("#' . $id . '-link").fancybox({
				minWidth : 600,
				scrolling :"auto",
				closeClick : false,
				helpers : {
				  overlay : {closeClick: false}
				}
			});

			$("#' . $id . '-link").trigger("click");

			$("#' . $id . '-continue").button().on("click", function(){
				' . $id . '_continue_xhr();
				return false;
			});

			$("#' . $id . '-abort").button().on("click", function(){
				showLoader();
				$.ajax({
					url:"/xhr.php?f=abortEmail",
					data:{id:' . (int)$mail['id'] . '},
					complete:function(){hideLoader();closeBox();}
				});
			});


		');

		$this->func->addJsFunc('
		function ' . $id . '_continue_xhr()
		{
				showLoader();
				$.ajax({
						dataType:"json",
						url:"/xhr.php?f=continueMail&id=' . (int)$mail['id'] . '",
						success : function(data){
							$("#' . $id . '-continue").hide();
							if(data.status == 1)
							{
								$("#' . $id . '-comment").html(data.comment);
								$("#' . $id . '-left").html(data.left);
								' . $id . '_continue_xhr();
							}
							else if(data.status == 2)
							{
								$("#' . $id . '-comment").html(data.comment);
								hideLoader();
							}
							else
							{
								alert("Du hast nich nie nötigen Rechte E-Mails zu versenden");
							}
						}
				});
			}');

		$style = '';
		if (count($recip) > 50) {
			$style = ' style="height:100px;overflow:auto;font-size:10px;background-color:#fff;color:#333;padding:5px;"';
		}

		$this->func->addHidden('
				<a id="' . $id . '-link" href="#' . $id . '">&nbsp;</a>
				<div class="popbox" id="' . $id . '">
					<h3>E-Mail senden</h3>
					<p class="subtitle">Es sind noch <span id="' . $id . '-left">' . $mail['anz'] . '</span> E-Mails zu versenden</p>

					<div id="' . $id . '-comment">
						' . $this->v_utils->v_input_wrapper('Empfänger', '<div' . $style . '>' . implode(', ', $recip) . '</div>') . '
						' . $this->v_utils->v_input_wrapper($this->func->s('subject'), $mail['name']) . '
						' . $this->v_utils->v_input_wrapper($this->func->s('message'), nl2br($mail['message'])) . '

					</div>
					<a id="' . $id . '-continue" href="#">Mit dem Senden weitermachen</a> <a id="' . $id . '-abort" href="#">Senden Abbrechen</a>
				</div>');

		return $out;
	}

	private function handleImages($body)
	{
		if (strpos($body, '<') === false) {
			return $body;
		}

		$doc = new DOMDocument();
		$doc->loadHTML($body);
		$tags = $doc->getElementsByTagName('img');

		try {
			foreach ($tags as $tag) {
				$src = $tag->getAttribute('src');
				$wwith = $tag->getAttribute('width');
				$hheight = $tag->getAttribute('height');
				$iname = $tag->getAttribute('name');

				if (!empty($wwith) || !empty($hheight)) {
					$old_filepath = '';

					$file = explode('/', $src);
					$filename = end($file);

					if (strpos($src, 'images/upload/') !== false) {
						$old_filepath = explode('images/upload', $src);
						$old_filepath = end($old_filepath);
					} elseif (!empty($iname) && strpos($iname, '/') !== false) {
						$old_filepath = $iname;
					}

					$file = 'images/upload' . $old_filepath;

					if (file_exists($file) && !is_dir($file)) {
						$ffile = explode('/', $old_filepath);
						$filename = end($ffile);

						$new_path = 'images/newsletter/';
						$new_filename = $filename;
						$y = 1;

						while (file_exists($new_path . $new_filename)) {
							$new_filename = $y . '-' . $filename;
							++$y;
						}
						copy($file, $new_path . $new_filename);
						$fimage = new fImage($new_path . $new_filename);
						if (!empty($src) && $width = $tag->getAttribute('width')) {
							$fimage->resize($width, 0);
						} elseif (!empty($src) && $height = $tag->getAttribute('height')) {
							$fimage->resize(0, $height);
						}
						$fimage->saveChanges();
						$tag->setAttribute('src', BASE_URL . '/' . $new_path . $new_filename);
						$tag->setAttribute('name', $old_filepath);
						$tag->removeAttribute('width');
						$tag->removeAttribute('height');
					}
				} elseif (substr($src, 0, 7) != 'http://' && substr($src, 0, 8) != 'https://') {
					$tag->setAttribute('src', BASE_URL . '/' . $src);
				}
			}

			$html = $doc->saveHTML();
			$html = explode('<body>', $html);
			$html = end($html);
			$html = explode('</body>', $html);
			$html = $html[0];

			return $html;
		} catch (Exception $e) {
			if ($_SESSION['client']['group']['admin'] === true && $this->session->mayGroup('admin')) {
				echo $e->getMessage();
				die();
			}

			return $body;
		}
	}
}
