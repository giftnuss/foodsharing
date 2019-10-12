<?php

namespace Foodsharing\Modules\FairTeiler;

use Foodsharing\Helpers\IdentificationHelper;
use Foodsharing\Lib\Db\Db;
use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Core\DBConstants\Region\Type;
use Foodsharing\Modules\Foodsaver\FoodsaverGateway;
use Foodsharing\Modules\Region\RegionGateway;
use Foodsharing\Permissions\FoodSharePointPermissions;
use Foodsharing\Services\SanitizerService;
use Symfony\Component\HttpFoundation\Request;

class FairTeilerControl extends Control
{
	private $regionId;
	private $region;
	private $fairteiler;
	private $follower;
	private $regions;

	private $foodSharePointGateway;
	private $regionGateway;
	private $foodsaverGateway;
	private $sanitizerService;
	private $identificationHelper;
	private $foodSharePointPermissions;

	public function __construct(
		FairTeilerView $view,
		FairTeilerGateway $foodSharePointGateway,
		RegionGateway $regionGateway,
		FoodsaverGateway $foodsaverGateway,
		Db $model,
		SanitizerService $sanitizerService,
		IdentificationHelper $identificationHelper,
		FoodSharePointPermissions $foodSharePointPermissions
	) {
		$this->view = $view;
		$this->foodSharePointGateway = $foodSharePointGateway;
		$this->regionGateway = $regionGateway;
		$this->foodsaverGateway = $foodsaverGateway;
		$this->model = $model;
		$this->sanitizerService = $sanitizerService;
		$this->identificationHelper = $identificationHelper;
		$this->foodSharePointPermissions = $foodSharePointPermissions;

		parent::__construct();
	}

	private function setup(Request $request): void
	{
		if ($request->query->has('uri') && $foodSharePointId = $this->uriInt(2)) {
			$this->routeHelper->go('/?page=fairteiler&sub=ft&id=' . $foodSharePointId);
		}

		if (!$this->foodSharePointPermissions->maySeeFoodSharePointLists()
			&& $request->query->has('sub')
			&& $request->query->get('sub') !== 'ft') {
			$this->routeHelper->goLogin();
		}

		$this->fairteiler = false;
		$this->follower = false;
		$this->regions = $this->getRealRegions();
		if ($foodSharePointId = $request->query->get('id')) {
			$this->fairteiler = $this->foodSharePointGateway->getFairteiler($foodSharePointId);

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

		if ($foodSharePointId) {
			$follow = $request->query->get('follow');
			$infoType = $request->query->get('infotype', 2);
			if ($this->handleFollowUnfollow($foodSharePointId, $this->session->id() ?? 0, $follow, $infoType)) {
				$url = explode('&follow=', $this->routeHelper->getSelf());
				$this->routeHelper->go($url[0]);
			}

			if (!isset($this->regions[$this->fairteiler['bezirk_id']])) {
				$this->regions[] = $this->regionGateway->getRegion($this->fairteiler['bezirk_id']);
			}

			$this->follower = $this->foodSharePointGateway->getFollower($foodSharePointId);

			$this->view->setFairteiler($this->fairteiler, $this->follower);

			$this->fairteiler['urlname'] = str_replace(' ', '_', $this->fairteiler['name']);
			$this->fairteiler['urlname'] = $this->identificationHelper->id($this->fairteiler['urlname']);
			$this->fairteiler['urlname'] = str_replace('_', '-', $this->fairteiler['urlname']);

			$this->pageHelper->addHidden(
				'
				<a href="#ft-fbshare" id="ft-public-link" target="_blank">&nbsp;</a>
				<input type="hidden" name="ft-name" id="ft-name" value="' . $this->fairteiler['name'] . '" />
				<input type="hidden" name="ft-id" id="ft-id" value="' . $this->fairteiler['id'] . '" />
				<input type="hidden" name="ft-urlname" id="ft-urlname" value="' . $this->fairteiler['urlname'] . '" />
				<input type="hidden" name="ft-bezirk" id="ft-bezirk" value="' . $this->region['urlname'] . '" />
				<input type="hidden" name="ft-publicurl" id="ft-publicurl" value="' . BASE_URL . '/' . $this->region['urlname'] . '/fairteiler/' . $this->fairteiler['id'] . '_' . $this->fairteiler['urlname'] . '" />
				'
			);

			if ($request->query->has('delete') && $this->foodSharePointPermissions->mayDeleteFoodSharePointOfRegion($this->regionId)) {
				$this->delete();
			}
		}
		$this->view->setBezirke($this->regions);
		$this->view->setBezirk($this->region);
	}

	private function handleFollowUnfollow($foodSharePointId, int $foodSharerId, $follow, $infoType): bool
	{
		if ($follow === null) {
			return false;
		}

		if ($follow === 1 && in_array($infoType, [1, 2], true)) {
			$this->foodSharePointGateway->follow($foodSharePointId, $foodSharerId, $infoType);
		} else {
			$this->foodSharePointGateway->unfollow($foodSharePointId, $foodSharerId);
		}

		return true;
	}

	public function getRealRegions(): array
	{
		return array_filter($this->session->getRegions(), [$this, 'isRealRegion']);
	}

	private function isRealRegion(array $region): bool
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

	public function index(Request $request): void
	{
		$this->setup($request);
		$this->pageHelper->addBread($this->translationHelper->s('your_fairteiler'), '/?page=fairteiler');
		if ($this->regionId > 0) {
			$this->pageHelper->addBread($this->region['name'], '/?page=fairteiler&bid=' . $this->regionId);
		}
		if (!$request->query->has('sub')) {
			$items = array();
			if ($this->regions = $this->session->getRegions()) {
				foreach ($this->regions as $r) {
					$items[] = ['name' => $r['name'], 'href' => '/?page=fairteiler&bid=' . $r['id']];
				}
			}

			if ($this->regionId === 0) {
				$regionIds = $this->regionGateway->listIdsForFoodsaverWithDescendants($this->session->id());
			} else {
				$regionIds = $this->regionGateway->listIdsForDescendantsAndSelf($this->regionId);
			}

			if ($this->fairteiler = $this->foodSharePointGateway->listFairteilerNested($regionIds)) {
				$this->pageHelper->addContent($this->view->listFairteiler($this->fairteiler));
			} else {
				$this->pageHelper->addContent(
					$this->v_utils->v_info($this->translationHelper->s('no_fairteiler_available'))
				);
			}
			$this->pageHelper->addContent($this->view->ftOptions($this->regionId), CNT_RIGHT);
		}
	}

	public function edit(Request $request): void
	{
		if (!$this->foodSharePointPermissions->mayEdit($this->regionId, $this->follower)) {
			$this->routeHelper->go('/?page=fairteiler&sub=ft&id=' . $this->fairteiler['id']);
		}
		$this->pageHelper->addBread(
			$this->fairteiler['name'],
			'/?page=fairteiler&sub=ft&bid=' . $this->regionId . '&id=' . $this->fairteiler['id']
		);
		$this->pageHelper->addBread($this->translationHelper->s('edit'));
		if ($request->request->get('form_submit') === 'fairteiler') {
			if ($this->handleEditFt($request)) {
				$this->flashMessageHelper->info($this->translationHelper->s('fairteiler_edit_success'));
				$this->routeHelper->go($this->routeHelper->getSelf());
			} else {
				$this->flashMessageHelper->error($this->translationHelper->s('fairteiler_edit_fail'));
			}
		}

		$data = $this->fairteiler;

		$items = [
			[
				'name' => $this->translationHelper->s('back'),
				'href' => '/?page=fairteiler&sub=ft&bid=' . $this->regionId . '&id=' . $this->fairteiler['id'],
			],
		];

		if ($this->foodSharePointPermissions->mayDeleteFoodSharePointOfRegion($this->regionId)) {
			$items[] = [
				'name' => $this->translationHelper->s('delete'),
				'click' => 'if(confirm(\'' . $this->translationHelper->sv(
						'delete_sure',
						$this->fairteiler['name']
					) . '\')){goTo(\'/?page=fairteiler&sub=ft&bid=' . $this->regionId . '&id=' . $this->fairteiler['id'] . '&delete=1\');}return false;',
			];
		}

		$data['bfoodsaver'] = $this->follower['verantwortlich'];

		foreach ($data['bfoodsaver'] as $key => $fs) {
			$data['bfoodsaver'][$key]['name'] = $fs['name'] . ' ' . $fs['nachname'];
		}

		$data['bfoodsaver_values'] = $this->foodsaverGateway->getFsAutocomplete($this->session->getRegions());

		$this->pageHelper->addContent($this->view->options($items), CNT_RIGHT);

		$this->pageHelper->addContent($this->view->fairteilerForm($data));
	}

	public function check(Request $request): void
	{
		if ($ft = $this->fairteiler) {
			if ($this->foodSharePointPermissions->mayApproveFoodSharePointCreation($ft['bezirk_id'])) {
				if ($request->query->has('agree')) {
					if ($request->query->get('agree')) {
						$this->accept();
					} else {
						$this->delete();
					}
				}
				$this->pageHelper->addContent($this->view->checkFairteiler($ft));
				$this->pageHelper->addContent(
					$this->view->menu(
						[
							[
								'href' => '/?page=fairteiler&sub=check&id=' . (int)$ft['id'] . '&agree=1',
								'name' => 'Fair-Teiler freischalten',
							],
							[
								'click' => 'if(confirm(\'Achtung! Wenn Du den Fair-Teiler löschst, kannst Du dies nicht mehr rückgängig machen. Fortfahren?\')){goTo(this.href);}else{return false;}',
								'href' => '/?page=fairteiler&sub=check&id=' . (int)$ft['id'] . '&agree=0',
								'name' => 'Fair-Teiler ablehnen',
							],
						],
						['title' => 'Optionen']
					),
					CNT_RIGHT
				);
			} else {
				$this->routeHelper->goPage('fairteiler');
			}
		} else {
			$this->routeHelper->goPage('fairteiler');
		}
	}

	private function accept(): void
	{
		$this->foodSharePointGateway->acceptFairteiler($this->fairteiler['id']);
		$this->flashMessageHelper->info('Fair-Teiler ist jetzt aktiv');
		$this->routeHelper->go('/?page=fairteiler&sub=ft&id=' . $this->fairteiler['id']);
	}

	private function delete(): void
	{
		if ($this->foodSharePointGateway->deleteFairteiler($this->fairteiler['id'])) {
			$this->flashMessageHelper->info($this->translationHelper->s('delete_success'));
			$this->routeHelper->go('/?page=fairteiler&bid=' . $this->regionId);
		}
	}

	public function ft(Request $request): void
	{
		$this->pageHelper->addBread($this->fairteiler['name']);
		$this->pageHelper->addTitle($this->fairteiler['name']);
		$this->pageHelper->addContent(
			$this->view->fairteilerHead() . '
			<div>
				' . $this->v_utils->v_info(
				'Beachte, dass Deine Beiträge auf der Fair-Teiler-Pinnwand öffentlich einsehbar sind.',
				'Hinweis!'
			) . '
			</div>
			<div class="ui-widget ui-widget-content ui-corner-all margin-bottom">
				' . $this->wallposts('fairteiler', $this->fairteiler['id']) . '
			</div>'
		);

		if ($this->foodSharePointPermissions->mayFollow()) {
			$items = array();

			if ($this->foodSharePointPermissions->mayEdit($this->regionId, $this->follower)) {
				$items[] = [
					'name' => $this->translationHelper->s('edit'),
					'href' => '/?page=fairteiler&bid=' . $this->regionId . '&sub=edit&id=' . $this->fairteiler['id'],
				];
			}

			if ($this->isFollower()) {
				$items[] = [
					'name' => $this->translationHelper->s('no_more_follow'),
					'href' => $this->routeHelper->getSelf() . '&follow=0',
				];
			} else {
				$items[] = ['name' => $this->translationHelper->s('follow'), 'click' => 'u_follow();return false;'];
				$this->pageHelper->addHidden($this->view->followHidden());
			}

			$this->pageHelper->addContent($this->view->options($items), CNT_LEFT);
			$this->pageHelper->addContent($this->view->follower(), CNT_LEFT);
		}

		$this->pageHelper->addContent($this->view->desc(), CNT_RIGHT);
		$this->pageHelper->addContent($this->view->address(), CNT_RIGHT);
	}

	public function add(Request $request): void
	{
		$this->pageHelper->addBread($this->translationHelper->s('add_fairteiler'));

		if ($request->request->get('form_submit') === 'fairteiler') {
			if ($this->handelAdd($request)) {
				if ($this->foodSharePointPermissions->mayAdd($this->regionId)) {
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
		$this->pageHelper->addContent(
			$this->v_utils->v_menu(
				[
					[
						'name' => $this->translationHelper->s('back'),
						'href' => '/?page=fairteiler&bid=' . (int)$this->regionId . '',
					],
				],
				$this->translationHelper->s('options')
			),
			CNT_RIGHT
		);
	}

	private function handleEditFt(Request $request): bool
	{
		if ($this->foodSharePointPermissions->mayEdit($this->regionId, $this->follower)) {
			$data = $this->prepareInput($request);
			if ($this->validateInput($data)) {
				$responsible = $this->sanitizerService->tagSelectIds($request->request->get('bfoodsaver'));
				$this->foodSharePointGateway->updateVerantwortliche($this->fairteiler['id'], $responsible);

				return $this->foodSharePointGateway->updateFairteiler($this->fairteiler['id'], $data);
			}

			return false;
		}

		return false;
	}

	private function prepareInput(Request $request): array
	{
		return [
			'name' => $request->request->get('name'),
			'desc' => $request->request->get('desc'),
			'anschrift' => strip_tags($request->request->get('anschrift')),
			'plz' => preg_replace('[^0-9]', '', $request->request->get('plz')),
			'ort' => strip_tags($request->request->get('ort')),
			'picture' => strip_tags($request->request->get('picture')),
			'bezirk_id' => (int)$request->request->getDigits('bezirk_id'),
			'lat' => $request->request->filter(
				'lat',
				null,
				FILTER_SANITIZE_NUMBER_FLOAT,
				['flags' => FILTER_FLAG_ALLOW_FRACTION]
			),
			'lon' => $request->request->filter(
				'lon',
				null,
				FILTER_SANITIZE_NUMBER_FLOAT,
				['flags' => FILTER_FLAG_ALLOW_FRACTION]
			),
		];
	}

	private function validateInput(array $data): bool
	{
		return $data['lat'] && $data['lon'] && $data['bezirk_id'];
	}

	private function handelAdd(Request $request): int
	{
		$data = $this->prepareInput($request);
		if ($this->validateInput($data)) {
			$status = 0;
			if ($this->foodSharePointPermissions->mayAdd($this->regionId)) {
				$status = 1;
			}
			$data['status'] = $status;

			return $this->foodSharePointGateway->addFairteiler($this->session->id(), $data);
		}

		return 0;
	}

	private function isFollower(): bool
	{
		return isset($this->follower['all'][$this->session->id()]);
	}
}
