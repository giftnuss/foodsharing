<?php

namespace Foodsharing\Modules\FairTeiler;

use Foodsharing\Lib\Session\S;
use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Core\Model;
use Foodsharing\Modules\Region\RegionGateway;

class FairTeilerControl extends Control
{
	private $bezirk_id;
	private $bezirk;
	private $fairteiler;
	private $follower;
	private $bezirke;

	private $gateway;
	private $regionGateway;

	public function __construct(FairTeilerView $view, FairTeilerGateway $gateway, RegionGateway $regionGateway, Model $model)
	{
		$this->view = $view;
		$this->gateway = $gateway;
		$this->regionGateway = $regionGateway;
		$this->model = $model;

		parent::__construct();

		if (isset($_GET['uri']) && ($ftid = $this->uriInt(2))) {
			$this->func->go('/?page=fairteiler&sub=ft&id=' . $ftid);
		}

		/*
		 * allowed only for logged in users
		 */
		if (!S::may() && isset($_GET['sub']) && $_GET['sub'] != 'ft') {
			$this->func->goLogin();
		}

		if (isset($_GET['bid']) && (int)$_GET['bid'] > 0) {
			if ($bezirk = $this->model->getBezirk($_GET['bid'])) {
				$this->bezirk_id = (int)$_GET['bid'];
				$this->bezirk = $bezirk;
				if ((int)$bezirk['mailbox_id'] > 0) {
					$this->bezirk['urlname'] = $this->model->getVal('name', 'mailbox', $bezirk['mailbox_id']);
				} else {
					$this->bezirk['urlname'] = $this->func->id($this->bezirk['name']);
				}
			}
		} else {
			$this->bezirk_id = 0;
			$this->bezirk = false;
		}

		$this->fairteiler = false;
		$this->follower = false;
		if (isset($_GET['id'])) {
			$this->fairteiler = $this->gateway->getFairteiler($_GET['id']);

			if (!$this->fairteiler) {
				$this->func->go('/?page=fairteiler');
			}
			if (isset($_GET['follow'])) {
				if ($_GET['follow'] == 1) {
					$info = 2;
					if (isset($_GET['infotype']) && $_GET['infotype'] == 1) {
						$info = 1;
					}
					$this->gateway->follow($_GET['id'], $this->func->fsId(), $info);
				} else {
					$this->gateway->unfollow($_GET['id'], $this->func->fsId());
				}
				$url = explode('&follow=', $this->func->getSelf());
				$this->func->go($url[0]);
			}

			$this->bezirke = $this->model->getRealBezirke();
			if (!isset($this->bezirke[$this->fairteiler['bezirk_id']])) {
				$this->bezirke[] = $this->model->getBezirk($this->fairteiler['bezirk_id']);
			}
			$this->view->setBezirke($this->bezirke);

			$this->view->setBezirk($this->bezirk);

			$this->follower = $this->gateway->getFollower($_GET['id']);

			$this->view->setFairteiler($this->fairteiler, $this->follower);

			$this->fairteiler['urlname'] = str_replace(' ', '_', $this->fairteiler['name']);
			$this->fairteiler['urlname'] = $this->func->id($this->fairteiler['urlname']);
			$this->fairteiler['urlname'] = str_replace('_', '-', $this->fairteiler['urlname']);

			$this->func->addHidden('
				<a href="#ft-fbshare" id="ft-public-link" target="_blank">&nbsp;</a>
				<input type="hidden" name="ft-name" id="ft-name" value="' . $this->fairteiler['name'] . '" />
				<input type="hidden" name="ft-id" id="ft-id" value="' . $this->fairteiler['id'] . '" />
				<input type="hidden" name="ft-urlname" id="ft-urlname" value="' . $this->fairteiler['urlname'] . '" />
				<input type="hidden" name="ft-bezirk" id="ft-bezirk" value="' . $this->bezirk['urlname'] . '" />
				<input type="hidden" name="ft-publicurl" id="ft-publicurl" value="http://www.' . DEFAULT_HOST . '/' . $this->bezirk['urlname'] . '/fairteiler/' . $this->fairteiler['id'] . '_' . $this->fairteiler['urlname'] . '" />
				');

			if (isset($_GET['delete']) && ($this->func->isOrgaTeam() || $this->func->isBotFor($this->bezirk_id))) {
				if ($this->gateway->deleteFairteiler($this->fairteiler['id'])) {
					$this->func->info($this->func->s('delete_success'));
					$this->func->go('/?page=fairteiler&bid=' . $this->bezirk_id);
				}
			}
		}
	}

	public function index()
	{
		$this->func->addBread($this->func->s('your_fairteiler'), '/?page=fairteiler');
		if ($this->bezirk_id > 0) {
			$this->func->addBread($this->bezirk['name'], '/?page=fairteiler&bid=' . $this->bezirk_id);
		}
		if (!isset($_GET['sub'])) {
			$items = array();
			if ($bezirke = $this->model->getBezirke()) {
				foreach ($bezirke as $b) {
					$items[] = array('name' => $b['name'], 'href' => '/?page=fairteiler&bid=' . $b['id']);
				}
			}

			if ($this->bezirk_id === 0) {
				$bezirk_ids = $this->regionGateway->listIdsForFoodsaverWithDescendants($this->func->fsId());
			} else {
				$bezirk_ids = $this->regionGateway->listIdsForDescendantsAndSelf($this->bezirk_id);
			}

			if ($fairteiler = $this->gateway->listFairteiler($bezirk_ids)) {
				$this->func->addContent($this->view->listFairteiler($fairteiler));
			} else {
				$this->func->addContent($this->v_utils->v_info($this->func->s('no_fairteiler_available')));
			}
			$this->func->addContent($this->view->ftOptions($this->bezirk_id), CNT_RIGHT);
		}
	}

	public function edit()
	{
		$this->func->addBread($this->fairteiler['name'], '/?page=fairteiler&sub=ft&bid=' . $this->bezirk_id . '&id=' . $this->fairteiler['id']);
		$this->func->addBread($this->func->s('edit'));
		if (isset($_POST['form_submit']) && $_POST['form_submit'] == 'fairteiler') {
			if ($this->handleEditFt()) {
				$this->func->info($this->func->s('fairteiler_edit_success'));
				$this->func->go($this->func->getSelf());
			} else {
				$this->func->error($this->func->s('fairteiler_edit_fail'));
			}
		}

		$data = $this->fairteiler;

		$items = array(
			array('name' => $this->func->s('back'), 'href' => '/?page=fairteiler&sub=ft&bid=' . $this->bezirk_id . '&id=' . $this->fairteiler['id'])
		);

		if ($this->func->isOrgaTeam() || $this->func->isBotFor($this->bezirk_id)) {
			$items[] = array('name' => $this->func->s('delete'), 'click' => 'if(confirm(\'' . $this->func->sv('delete_sure', $this->fairteiler['name']) . '\')){goTo(\'/?page=fairteiler&sub=ft&bid=' . $this->bezirk_id . '&id=' . $this->fairteiler['id'] . '&delete=1\');}return false;');
		}

		$data['bfoodsaver'] = $this->follower['verantwortlich'];

		foreach ($data['bfoodsaver'] as $key => $fs) {
			$data['bfoodsaver'][$key]['name'] = $fs['name'] . ' ' . $fs['nachname'];
		}

		$data['bfoodsaver_values'] = $this->model->getFsAutocomplete($this->model->getBezirke());

		$this->func->addContent($this->view->options($items), CNT_RIGHT);

		$this->func->addContent($this->view->fairteilerForm($data));
	}

	public function check()
	{
		if ($ft = $this->gateway->getFairteiler($_GET['id'])) {
			if ($this->func->isOrgaTeam() || $this->func->isBotFor($ft['bezirk_id'])) {
				if (isset($_GET['agree'])) {
					if ($_GET['agree'] == 1) {
						$this->gateway->acceptFairteiler($_GET['id']);
						$this->func->info('Fair-Teiler ist jetzt aktiv');
						$this->func->go('/?page=fairteiler&sub=ft&id=' . (int)$_GET['id']);
					} elseif ($_GET['agree'] == 0) {
						$this->gateway->deleteFairteiler($_GET['id']);
						$this->func->info('Fair-Teiler wurde gelöscht');
						$this->func->go('/?page=fairteiler');
					}
				}

				$this->func->addContent($this->view->checkFairteiler($ft));
				$this->func->addContent($this->view->menu(array(
					array('href' => '/?page=fairteiler&sub=check&id=' . (int)$ft['id'] . '&agree=1', 'name' => 'Fair-Teiler freischalten'),
					array('click' => 'if(confirm(\'Achtung! Wenn Du den Fair-Teiler löschst kannst Du dies nicht mehr rückgängig machen. Fortfahren?\')){goTo(this.href);}else{return false;}', 'href' => '/?page=fairteiler&sub=check&id=' . (int)$ft['id'] . '&agree=0', 'name' => 'Fair-Teiler ablehnen')
				), array('title' => 'Optionen')), CNT_RIGHT);
			} else {
				$this->func->goPage('fairteiler');
			}
		} else {
			$this->func->goPage('fairteiler');
		}
	}

	public function ft()
	{
		$this->func->addBread($this->fairteiler['name']);
		$this->func->addTitle($this->fairteiler['name']);
		$this->func->addContent(
			$this->view->fairteilerHead() . '
			<div>
				' . $this->v_utils->v_info('Beachte, dass Deine Beiträge auf der Fair-Teiler Pinnwand öffentlich einsehbar sind.', 'Hinweis!') . '
			</div>
			<div class="ui-widget ui-widget-content ui-corner-all margin-bottom">
				' . $this->wallposts('fairteiler', $this->fairteiler['id']) . '
			</div>'
		);

		if (S::may()) {
			$items = array();

			if ($this->mayEdit()) {
				$items[] = array('name' => $this->func->s('edit'), 'href' => '/?page=fairteiler&bid=' . $this->bezirk_id . '&sub=edit&id=' . $this->fairteiler['id']);
			}

			if ($this->isFollower()) {
				$items[] = array('name' => $this->func->s('no_more_follow'), 'href' => $this->func->getSelf() . '&follow=0');
			} else {
				$items[] = array('name' => $this->func->s('follow'), 'click' => 'u_follow();return false;');
				$this->func->addHidden($this->view->followHidden());
			}

			$this->func->addContent($this->view->options($items), CNT_LEFT);
			$this->func->addContent($this->view->follower(), CNT_LEFT);
		} else {
			$this->func->addContent($this->view->loginToFollow(), CNT_LEFT);
		}

		$this->func->addContent($this->view->desc(), CNT_RIGHT);
		$this->func->addContent($this->view->address(), CNT_RIGHT);
	}

	public function addFt()
	{
		$this->func->addBread($this->func->s('add_fairteiler'));

		if (isset($_POST['form_submit']) && $_POST['form_submit'] == 'fairteiler') {
			if ($this->handleAddFt()) {
				if ($this->func->isBotFor($this->bezirk_id) || $this->func->isOrgaTeam()) {
					$this->func->info($this->func->s('fairteiler_add_success'));
				} else {
					$this->func->info($this->func->s('fairteiler_prepare_success'));
				}
				$this->func->go('/?page=fairteiler&bid=' . (int)$this->bezirk_id);
			} else {
				$this->func->error($this->func->s('fairteiler_add_fail'));
			}
		}

		$this->func->addContent($this->view->fairteilerForm());
		$this->func->addContent($this->v_utils->v_menu(array(
			array('name' => $this->func->s('back'), 'href' => '/?page=fairteiler&bid=' . (int)$this->bezirk_id . '')
		), $this->func->s('options')), CNT_RIGHT);
	}

	private function handleEditFt()
	{
		if ($this->mayEdit()) {
			$name = strip_tags($_POST['name']);
			$desc = strip_tags($_POST['desc']);
			$anschrift = strip_tags($_POST['anschrift']);
			$plz = preg_replace('[^0-9]', '', $_POST['plz']);
			$ort = strip_tags($_POST['ort']);
			$picture = strip_tags($_POST['picture']);
			$bezirk_id = (int)$_POST['bezirk_id'];
			if (!empty($_POST['lat']) && !empty($_POST['lon']) && $bezirk_id > 0) {
				$lat = $_POST['lat'];
				$lon = $_POST['lon'];

				$this->func->handleTagselect('bfoodsaver');

				global $g_data;
				$this->gateway->updateVerantwortliche($this->fairteiler['id'], $g_data['bfoodsaver']);

				return $this->gateway->updateFairteiler($this->fairteiler['id'], $bezirk_id, $name, $desc, $anschrift, $plz, $ort, $lat, $lon, $picture);
			} else {
				return false;
			}
		}
	}

	public function handleAddFt()
	{
		$name = strip_tags($_POST['name']);
		$desc = strip_tags($_POST['desc']);
		$anschrift = strip_tags($_POST['anschrift']);
		$plz = preg_replace('[^0-9]', '', $_POST['plz']);
		$ort = strip_tags($_POST['ort']);
		$picture = strip_tags($_POST['picture']);
		$bezirk_id = (int)$_POST['bezirk_id'];
		if (!empty($_POST['lat']) && !empty($_POST['lon']) && $bezirk_id > 0) {
			$lat = $_POST['lat'];
			$lon = $_POST['lon'];

			$status = 0;
			if ($this->func->isBotFor($this->bezirk_id) || $this->func->isOrgaTeam()) {
				$status = 1;
			}

			return $this->gateway->addFairteiler($this->func->fsId(), $bezirk_id, $name, $desc, $anschrift, $plz, $ort, $lat, $lon, $picture, $status);
		} else {
			return false;
		}
	}

	private function isFollower()
	{
		if (isset($this->follower['all'][$this->func->fsId()])) {
			return true;
		} else {
			return false;
		}
	}

	public function mayEdit()
	{
		if (
			$this->func->isBotFor($this->bezirk_id) ||
			$this->func->isOrgaTeam() ||
			(
				isset($this->follower['all'][$this->func->fsId()]) &&
				$this->follower['all'][$this->func->fsId()] == 'verantwortlich'
			)
		) {
			return true;
		}

		return false;
	}
}
