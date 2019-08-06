<?php

namespace Foodsharing\Modules\FairTeiler;

use Foodsharing\Helpers\IdentificationHelper;
use Foodsharing\Lib\Db\Db;
use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Core\DBConstants\Region\Type;
use Foodsharing\Modules\Foodsaver\FoodsaverGateway;
use Foodsharing\Modules\Region\RegionGateway;
use Foodsharing\Services\SanitizerService;
use Symfony\Component\HttpFoundation\Request;

class FairTeilerControl extends Control
{
	private $regionId;
	private $region;
	private $fairteiler;
	private $follower;
	private $regions;

	private $gateway;
	private $regionGateway;
	private $foodsaverGateway;
	private $sanitizerService;
	private $identificationHelper;

	public function __construct(
		FairTeilerView $view,
		FairTeilerGateway $gateway,
		RegionGateway $regionGateway,
		FoodsaverGateway $foodsaverGateway,
		Db $model,
		SanitizerService $sanitizerService,
		IdentificationHelper $identificationHelper
	) {
		$this->view = $view;
		$this->gateway = $gateway;
		$this->regionGateway = $regionGateway;
		$this->foodsaverGateway = $foodsaverGateway;
		$this->model = $model;
		$this->sanitizerService = $sanitizerService;
		$this->identificationHelper = $identificationHelper;

		parent::__construct();
	}

	private function handleFollowUnfollow($ftid, $fsid, $follow, $infotype)
	{
		if (is_null($follow)) {
			return false;
		}

		if ($follow == 1 && in_array($infotype, [1, 2])) {
			$this->gateway->follow($ftid, $fsid, $infotype);
		} else {
			$this->gateway->unfollow($ftid, $fsid);
		}
	}

	private function setup(Request $request)
	{
		if ($request->query->has('uri') && $ftid = $this->uriInt(2)) {
			$this->routeHelper->go('/?page=fairteiler&sub=ft&id=' . $ftid);
		}

		/*
		 * allowed only for logged in users
		 */
		if (!$this->session->may() && $request->query->has('sub') && $request->query->get('sub') != 'ft') {
			$this->routeHelper->goLogin();
		}

		$this->fairteiler = false;
		$this->follower = false;
		$this->regions = $this->getRealRegions();
		if ($ftid = $request->query->get('id')) {
			$this->fairteiler = $this->gateway->getFairteiler($ftid);

			if (!$this->fairteiler) {
				$this->routeHelper->go('/?page=fairteiler');
			}
			$regionId = $this->fairteiler['bezirk_id'];
		}

		if (isset($regionId) || $regionId = $request->query->get('bid')) {
			if ($region = $this->regionGateway->getRegion($regionId)) {
				$this->regionId = $regionId;
				$this->region = $region;
				if ((int)$region['mailbox_id'] > 0) {
					$this->region['urlname'] = $this->model->getVal('name', 'mailbox', $region['mailbox_id']);
				} else {
					$this->region['urlname'] = $this->identificationHelper->id($this->region['name']);
				}
			}
		} else {
			$this->regionId = 0;
			$this->region = null;
		}

		if ($ftid) {
			$follow = $request->query->get('follow');
			$infotype = $request->query->get('infotype', 2);
			if ($this->handleFollowUnfollow($ftid, $this->session->id(), $follow, $infotype)) {
				$url = explode('&follow=', $this->routeHelper->getSelf());
				$this->routeHelper->go($url[0]);
			}

			if (!isset($this->regions[$this->fairteiler['bezirk_id']])) {
				$this->regions[] = $this->regionGateway->getRegion($this->fairteiler['bezirk_id']);
			}

			$this->follower = $this->gateway->getFollower($ftid);

			$this->view->setFairteiler($this->fairteiler, $this->follower);

			$this->fairteiler['urlname'] = str_replace(' ', '_', $this->fairteiler['name']);
			$this->fairteiler['urlname'] = $this->identificationHelper->id($this->fairteiler['urlname']);
			$this->fairteiler['urlname'] = str_replace('_', '-', $this->fairteiler['urlname']);

			$this->pageHelper->addHidden('
				<a href="#ft-fbshare" id="ft-public-link" target="_blank">&nbsp;</a>
				<input type="hidden" name="ft-name" id="ft-name" value="' . $this->fairteiler['name'] . '" />
				<input type="hidden" name="ft-id" id="ft-id" value="' . $this->fairteiler['id'] . '" />
				<input type="hidden" name="ft-urlname" id="ft-urlname" value="' . $this->fairteiler['urlname'] . '" />
				<input type="hidden" name="ft-bezirk" id="ft-bezirk" value="' . $this->region['urlname'] . '" />
				<input type="hidden" name="ft-publicurl" id="ft-publicurl" value="' . BASE_URL . '/' . $this->region['urlname'] . '/fairteiler/' . $this->fairteiler['id'] . '_' . $this->fairteiler['urlname'] . '" />
				');

			if ($request->query->has('delete') && ($this->session->isAdminFor($this->regionId) || $this->session->isOrgaTeam())) {
				$this->delete();
			}
		}
		$this->view->setBezirke($this->regions);
		$this->view->setBezirk($this->region);
	}

	public function getRealRegions(): array
	{
		$regions = $this->session->getRegions();

		return array_filter($regions, [$this, 'isRealRegion']);
	}

	private function isRealRegion($region): bool
	{
		return \in_array(
			$region['type'],
			[
				Type::CITY,
				Type::DISTRICT,
				Type::REGION,
				Type::PART_OF_TOWN,
			],
			false
		);
	}

	public function index(Request $request)
	{
		$this->setup($request);
		$this->pageHelper->addBread($this->translationHelper->s('your_fairteiler'), '/?page=fairteiler');
		if ($this->regionId > 0) {
			$this->pageHelper->addBread($this->region['name'], '/?page=fairteiler&bid=' . $this->regionId);
		}
		if (!$request->query->has('sub')) {
			$items = array();
			if ($regions = $this->session->getRegions()) {
				foreach ($regions as $r) {
					$items[] = array('name' => $r['name'], 'href' => '/?page=fairteiler&bid=' . $r['id']);
				}
			}

			if ($this->regionId === 0) {
				$regionIds = $this->regionGateway->listIdsForFoodsaverWithDescendants($this->session->id());
			} else {
				$regionIds = $this->regionGateway->listIdsForDescendantsAndSelf($this->regionId);
			}

			if ($fairteiler = $this->gateway->listFairteilerNested($regionIds)) {
				$this->pageHelper->addContent($this->view->listFairteiler($fairteiler));
			} else {
				$this->pageHelper->addContent($this->v_utils->v_info($this->translationHelper->s('no_fairteiler_available')));
			}
			$this->pageHelper->addContent($this->view->ftOptions($this->regionId), CNT_RIGHT);
		}
	}

	public function edit(Request $request)
	{
		if (!$this->mayEdit()) {
			$this->routeHelper->go('/?page=fairteiler&sub=ft&id=' . $this->fairteiler['id']);
		}
		$this->pageHelper->addBread($this->fairteiler['name'], '/?page=fairteiler&sub=ft&bid=' . $this->regionId . '&id=' . $this->fairteiler['id']);
		$this->pageHelper->addBread($this->translationHelper->s('edit'));
		if ($request->request->get('form_submit') == 'fairteiler') {
			if ($this->handleEditFt($request)) {
				$this->flashMessageHelper->info($this->translationHelper->s('fairteiler_edit_success'));
				$this->routeHelper->go($this->routeHelper->getSelf());
			} else {
				$this->flashMessageHelper->error($this->translationHelper->s('fairteiler_edit_fail'));
			}
		}

		$data = $this->fairteiler;

		$items = array(
			array('name' => $this->translationHelper->s('back'), 'href' => '/?page=fairteiler&sub=ft&bid=' . $this->regionId . '&id=' . $this->fairteiler['id'])
		);

		if ($this->session->isAdminFor($this->regionId) || $this->session->isOrgaTeam()) {
			$items[] = array('name' => $this->translationHelper->s('delete'), 'click' => 'if(confirm(\'' . $this->translationHelper->sv('delete_sure', $this->fairteiler['name']) . '\')){goTo(\'/?page=fairteiler&sub=ft&bid=' . $this->regionId . '&id=' . $this->fairteiler['id'] . '&delete=1\');}return false;');
		}

		$data['bfoodsaver'] = $this->follower['verantwortlich'];

		foreach ($data['bfoodsaver'] as $key => $fs) {
			$data['bfoodsaver'][$key]['name'] = $fs['name'] . ' ' . $fs['nachname'];
		}

		$data['bfoodsaver_values'] = $this->foodsaverGateway->getFsAutocomplete($this->session->getRegions());

		$this->pageHelper->addContent($this->view->options($items), CNT_RIGHT);

		$this->pageHelper->addContent($this->view->fairteilerForm($data));
	}

	private function accept()
	{
		$this->gateway->acceptFairteiler($this->fairteiler['id']);
		$this->flashMessageHelper->info('Fair-Teiler ist jetzt aktiv');
		$this->routeHelper->go('/?page=fairteiler&sub=ft&id=' . $this->fairteiler['id']);
	}

	private function delete()
	{
		if ($this->gateway->deleteFairteiler($this->fairteiler['id'])) {
			$this->flashMessageHelper->info($this->translationHelper->s('delete_success'));
			$this->routeHelper->go('/?page=fairteiler&bid=' . $this->regionId);
		}
	}

	public function check(Request $request)
	{
		if ($ft = $this->fairteiler) {
			if ($this->session->isAdminFor($ft['bezirk_id']) || $this->session->isOrgaTeam()) {
				if ($request->query->has('agree')) {
					if ($request->query->get('agree')) {
						$this->accept();
					} else {
						$this->delete();
					}
				}
				$this->pageHelper->addContent($this->view->checkFairteiler($ft));
				$this->pageHelper->addContent($this->view->menu(array(
					array('href' => '/?page=fairteiler&sub=check&id=' . (int)$ft['id'] . '&agree=1', 'name' => 'Fair-Teiler freischalten'),
					array('click' => 'if(confirm(\'Achtung! Wenn Du den Fair-Teiler löschst, kannst Du dies nicht mehr rückgängig machen. Fortfahren?\')){goTo(this.href);}else{return false;}', 'href' => '/?page=fairteiler&sub=check&id=' . (int)$ft['id'] . '&agree=0', 'name' => 'Fair-Teiler ablehnen')
				), array('title' => 'Optionen')), CNT_RIGHT);
			} else {
				$this->routeHelper->goPage('fairteiler');
			}
		} else {
			$this->routeHelper->goPage('fairteiler');
		}
	}

	public function ft(Request $request)
	{
		$this->pageHelper->addBread($this->fairteiler['name']);
		$this->pageHelper->addTitle($this->fairteiler['name']);
		$this->pageHelper->addContent(
			$this->view->fairteilerHead() . '
			<div>
				' . $this->v_utils->v_info('Beachte, dass Deine Beiträge auf der Fair-Teiler-Pinnwand öffentlich einsehbar sind.', 'Hinweis!') . '
			</div>
			<div class="ui-widget ui-widget-content ui-corner-all margin-bottom">
				' . $this->wallposts('fairteiler', $this->fairteiler['id']) . '
			</div>'
		);

		if ($this->session->may()) {
			$items = array();

			if ($this->mayEdit()) {
				$items[] = array('name' => $this->translationHelper->s('edit'), 'href' => '/?page=fairteiler&bid=' . $this->regionId . '&sub=edit&id=' . $this->fairteiler['id']);
			}

			if ($this->isFollower()) {
				$items[] = array('name' => $this->translationHelper->s('no_more_follow'), 'href' => $this->routeHelper->getSelf() . '&follow=0');
			} else {
				$items[] = array('name' => $this->translationHelper->s('follow'), 'click' => 'u_follow();return false;');
				$this->pageHelper->addHidden($this->view->followHidden());
			}

			$this->pageHelper->addContent($this->view->options($items), CNT_LEFT);
			$this->pageHelper->addContent($this->view->follower(), CNT_LEFT);
		}

		$this->pageHelper->addContent($this->view->desc(), CNT_RIGHT);
		$this->pageHelper->addContent($this->view->address(), CNT_RIGHT);
	}

	public function addFt(Request $request)
	{
		$this->pageHelper->addBread($this->translationHelper->s('add_fairteiler'));

		if ($request->request->get('form_submit') == 'fairteiler') {
			if ($this->handleAddFt($request)) {
				if ($this->session->isAdminFor($this->regionId) || $this->session->isOrgaTeam()) {
					$this->flashMessageHelper->info($this->translationHelper->s('fairteiler_add_success'));
				} else {
					$this->flashMessageHelper->info($this->translationHelper->s('fairteiler_prepare_success'));
				}
				$this->routeHelper->go('/?page=fairteiler&bid=' . (int)$this->regionId);
			} else {
				$this->flashMessageHelper->error($this->translationHelper->s('fairteiler_add_fail'));
			}
		}

		$this->pageHelper->addContent($this->view->fairteilerForm());
		$this->pageHelper->addContent($this->v_utils->v_menu(array(
			array('name' => $this->translationHelper->s('back'), 'href' => '/?page=fairteiler&bid=' . (int)$this->regionId . '')
		), $this->translationHelper->s('options')), CNT_RIGHT);
	}

	private function prepareInput(Request $request)
	{
		$data = [
			'name' => $request->request->get('name'),
			'desc' => $request->request->get('desc'),
			'anschrift' => strip_tags($request->request->get('anschrift')),
			'plz' => preg_replace('[^0-9]', '', $request->request->get('plz')),
			'ort' => strip_tags($request->request->get('ort')),
			'picture' => strip_tags($request->request->get('picture')),
			'bezirk_id' => (int)$request->request->getDigits('bezirk_id'),
			'lat' => $request->request->filter('lat', null, FILTER_SANITIZE_NUMBER_FLOAT, ['flags' => FILTER_FLAG_ALLOW_FRACTION]),
			'lon' => $request->request->filter('lon', null, FILTER_SANITIZE_NUMBER_FLOAT, ['flags' => FILTER_FLAG_ALLOW_FRACTION])
		];

		return $data;
	}

	private function validateInput($data)
	{
		return $data['lat'] && $data['lon'] && $data['bezirk_id'];
	}

	private function handleEditFt(Request $request)
	{
		if ($this->mayEdit()) {
			$data = $this->prepareInput($request);
			if ($this->validateInput($data)) {
				$responsible = $this->sanitizerService->tagSelectIds($request->request->get('bfoodsaver'));
				$this->gateway->updateVerantwortliche($this->fairteiler['id'], $responsible);

				return $this->gateway->updateFairteiler($this->fairteiler['id'], $data);
			}

			return false;
		}
	}

	private function handleAddFt(Request $request)
	{
		$data = $this->prepareInput($request);
		if ($this->validateInput($data)) {
			$status = 0;
			if ($this->session->isAdminFor($this->regionId) || $this->session->isOrgaTeam()) {
				$status = 1;
			}
			$data['status'] = $status;

			return $this->gateway->addFairteiler($this->session->id(), $data);
		}

		return false;
	}

	private function isFollower()
	{
		return isset($this->follower['all'][$this->session->id()]);
	}

	private function mayEdit(): bool
	{
		return $this->session->isAdminFor($this->regionId) ||
			$this->session->isOrgaTeam() ||
			(
				isset($this->follower['all'][$this->session->id()]) &&
				$this->follower['all'][$this->session->id()] == 'verantwortlich'
			);
	}
}
