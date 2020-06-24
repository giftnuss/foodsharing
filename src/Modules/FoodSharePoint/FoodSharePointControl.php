<?php

namespace Foodsharing\Modules\FoodSharePoint;

use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Core\DBConstants\Info\InfoType;
use Foodsharing\Modules\Core\DBConstants\Region\Type;
use Foodsharing\Modules\Foodsaver\FoodsaverGateway;
use Foodsharing\Modules\Mailbox\MailboxGateway;
use Foodsharing\Modules\Region\RegionGateway;
use Foodsharing\Permissions\FoodSharePointPermissions;
use Foodsharing\Utility\IdentificationHelper;
use Foodsharing\Utility\Sanitizer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;

class FoodSharePointControl extends Control
{
	private $regionId;
	private $region;
	private $foodSharePoint;
	private $follower;
	private $regions;

	private $foodSharePointGateway;
	private $regionGateway;
	private $foodsaverGateway;
	private $mailboxGateway;
	private $sanitizerService;
	private $translator;
	private $identificationHelper;
	private $foodSharePointPermissions;

	public function __construct(
		FoodSharePointView $view,
		FoodSharePointGateway $foodSharePointGateway,
		RegionGateway $regionGateway,
		FoodsaverGateway $foodsaverGateway,
		MailboxGateway $mailboxGateway,
		Sanitizer $sanitizerService,
		TranslatorInterface $translator,
		IdentificationHelper $identificationHelper,
		FoodSharePointPermissions $foodSharePointPermissions
	) {
		$this->view = $view;
		$this->foodSharePointGateway = $foodSharePointGateway;
		$this->regionGateway = $regionGateway;
		$this->foodsaverGateway = $foodsaverGateway;
		$this->mailboxGateway = $mailboxGateway;
		$this->sanitizerService = $sanitizerService;
		$this->translator = $translator;
		$this->identificationHelper = $identificationHelper;
		$this->foodSharePointPermissions = $foodSharePointPermissions;

		parent::__construct();
	}

	public function index(Request $request): void
	{
		$this->setup($request);
		$this->pageHelper->addBread($this->translator->trans('fsp.yours'), '/?page=fairteiler');
		if ($this->regionId > 0) {
			$this->pageHelper->addBread($this->region['name'], '/?page=fairteiler&bid=' . $this->regionId);
		}
		if (!$request->query->has('sub')) {
			$this->regions = $this->session->getRegions();

			if ($this->regionId === 0) {
				if ($this->session->id()) {
					$regionIds = $this->regionGateway->listIdsForFoodsaverWithDescendants($this->session->id());
				} else {
					$regionIds = [];
				}
			} else {
				$regionIds = $this->regionGateway->listIdsForDescendantsAndSelf($this->regionId);
			}

			if ($this->foodSharePoint = $this->foodSharePointGateway->listFoodSharePointsNested($regionIds)) {
				$this->pageHelper->addContent($this->view->listFoodSharePoints($this->foodSharePoint));
			} else {
				$this->pageHelper->addContent(
					$this->v_utils->v_info($this->translator->trans('fsp.none'))
				);
			}
			$this->pageHelper->addContent($this->view->foodSharePointOptions($this->regionId), CNT_RIGHT);
		}
	}

	private function setup(Request $request): void
	{
		if ($request->query->has('uri') && $foodSharePointId = $this->uriInt(2)) {
			$this->routeHelper->go('/?page=fairteiler&sub=ft&id=' . $foodSharePointId);
		}

		// allowed only for logged in users
		if (!$this->session->may()
			&& $request->query->has('sub')
			&& $request->query->get('sub') !== 'ft') {
			$this->routeHelper->goLogin();
		}

		$this->foodSharePoint = false;
		$this->follower = false;
		$this->regions = $this->getRealRegions();
		if ($foodSharePointId = $request->query->get('id')) {
			$this->foodSharePoint = $this->foodSharePointGateway->getFoodSharePoint($foodSharePointId);

			if (!$this->foodSharePoint) {
				$this->routeHelper->go('/?page=fairteiler');
			}
			$regionId = $this->foodSharePoint['bezirk_id'];
		}

		if ((isset($regionId) || $regionId = $request->query->get('bid'))
				&& $region = $this->regionGateway->getRegion($regionId)) {
			$this->regionId = $regionId;
			$this->region = $region;
			if ((int)$region['mailbox_id'] > 0) {
				$this->region['urlname'] = $this->mailboxGateway->getMailboxname($region['mailbox_id']);
			} else {
				$this->region['urlname'] = $this->identificationHelper->id($this->region['name']);
			}
		} else {
			$this->regionId = 0;
			$this->region = null;
		}

		if ($foodSharePointId) {
			$follow = $request->query->get('follow');
			$infoType = $request->query->get('infotype', InfoType::BELL);
			if ($this->handleFollowUnfollow($foodSharePointId, $this->session->id() ?? 0, $follow, $infoType)) {
				$url = explode('&follow=', $this->routeHelper->getSelf());
				$this->routeHelper->go($url[0]);
			}

			if (!isset($this->regions[$this->foodSharePoint['bezirk_id']])) {
				$this->regions[] = $this->regionGateway->getRegion($this->foodSharePoint['bezirk_id']);
			}

			$this->follower = $this->foodSharePointGateway->getFollower($foodSharePointId);

			$this->view->setFoodSharePoint($this->foodSharePoint, $this->follower);

			$this->foodSharePoint['urlname'] = str_replace(' ', '_', $this->foodSharePoint['name']);
			$this->foodSharePoint['urlname'] = $this->identificationHelper->id($this->foodSharePoint['urlname']);
			$this->foodSharePoint['urlname'] = str_replace('_', '-', $this->foodSharePoint['urlname']);

			$this->pageHelper->addHidden(
				'
				<a href="#ft-fbshare" id="ft-public-link" target="_blank">&nbsp;</a>
				<input type="hidden" name="ft-name" id="ft-name" value="' . $this->foodSharePoint['name'] . '" />
				<input type="hidden" name="ft-id" id="ft-id" value="' . $this->foodSharePoint['id'] . '" />
				<input type="hidden" name="ft-urlname" id="ft-urlname" value="' . $this->foodSharePoint['urlname'] . '" />
				<input type="hidden" name="ft-bezirk" id="ft-bezirk" value="' . $this->region['urlname'] . '" />
				<input type="hidden" name="ft-publicurl" id="ft-publicurl" value="' . BASE_URL . '/' . $this->region['urlname'] . '/fairteiler/' . $this->foodSharePoint['id'] . '_' . $this->foodSharePoint['urlname'] . '" />
				'
			);

			if ($request->query->has('delete') && $this->foodSharePointPermissions->mayDeleteFoodSharePointOfRegion($this->regionId)) {
				$this->delete();
			}
		}
		$this->view->setRegions($this->regions);
		$this->view->setRegion($this->region);
	}

	private function handleFollowUnfollow(int $foodSharePointId, int $foodSharerId, $follow, int $infoType): bool
	{
		if ($follow === null) {
			return false;
		}

		if ($follow == 1 && in_array($infoType, [InfoType::EMAIL, InfoType::BELL], true)) {
			$this->foodSharePointGateway->follow($foodSharerId, $foodSharePointId, $infoType);
		} else {
			$this->foodSharePointGateway->unfollow($foodSharerId, $foodSharePointId);
		}

		return true;
	}

	public function getRealRegions(): array
	{
		return array_filter($this->session->getRegions(), function ($r) { return Type::isAccessibleRegion($r['type']); });
	}

	public function edit(Request $request): void
	{
		if (!$this->foodSharePointPermissions->mayEdit($this->regionId, $this->follower)) {
			$this->routeHelper->go('/?page=fairteiler&sub=ft&id=' . $this->foodSharePoint['id']);
		}
		$this->pageHelper->addBread(
			$this->foodSharePoint['name'],
			'/?page=fairteiler&sub=ft&bid=' . $this->regionId . '&id=' . $this->foodSharePoint['id']
		);
		$this->pageHelper->addBread($this->translator->trans('fsp.edit'));
		if ($request->request->get('form_submit') === 'fairteiler') {
			if ($this->handleEditFsp($request)) {
				$this->flashMessageHelper->info($this->translator->trans('fsp.editSuccess'));
				$this->routeHelper->go($this->routeHelper->getSelf());
			} else {
				$this->flashMessageHelper->error($this->translator->trans('error_unexpected'));
			}
		}

		$data = $this->foodSharePoint;

		$items = [
			[
				'name' => $this->translator->trans('back'),
				'href' => '/?page=fairteiler&sub=ft&bid=' . $this->regionId . '&id=' . $this->foodSharePoint['id'],
			],
		];

		if ($this->foodSharePointPermissions->mayDeleteFoodSharePointOfRegion($this->regionId)) {
			$items[] = [
				'name' => $this->translator->trans('fsp.delete'),
				'click' => 'if(confirm(\''
					. $this->translator->trans('fsp.deleteConfirm')
					. '\')){goTo(\'/?page=fairteiler&sub=ft&bid=' . $this->regionId . '&id=' . $this->foodSharePoint['id'] . '&delete=1\');}return false;',
			];
		}

		$data['bfoodsaver'] = $this->follower['fsp_manager'];

		foreach ($data['bfoodsaver'] as $key => $fs) {
			$data['bfoodsaver'][$key]['name'] = $fs['name'] . ' ' . $fs['nachname'];
		}

		$data['bfoodsaver_values'] = $this->foodsaverGateway->getFsAutocomplete($this->session->getRegions());

		$this->pageHelper->addContent($this->view->options($items), CNT_RIGHT);

		$this->pageHelper->addContent($this->view->foodSharePointForm($data));
	}

	public function check(Request $request): void
	{
		if ($foodSharePoint = $this->foodSharePoint) {
			if ($this->foodSharePointPermissions->mayApproveFoodSharePointCreation($foodSharePoint['bezirk_id'])) {
				if ($request->query->has('agree')) {
					if ($request->query->get('agree')) {
						$this->accept();
					} else {
						$this->delete();
					}
				}
				$this->pageHelper->addContent($this->view->checkFoodSharePoint($foodSharePoint));
				$this->pageHelper->addContent(
					$this->view->menu(
						[
							[
								'href' => '/?page=fairteiler&sub=check&id=' . (int)$foodSharePoint['id'] . '&agree=1',
								'name' => $this->translator->trans('fsp.accept'),
							],
							[
								'click' => 'if(confirm(\''
									. $this->translator->trans('fsp.rejectConfirm')
									. '\')){goTo(this.href);}else{return false;}',
								'href' => '/?page=fairteiler&sub=check&id=' . (int)$foodSharePoint['id'] . '&agree=0',
								'name' => $this->translator->trans('fsp.reject'),
							],
						],
						['title' => $this->translator->trans('options')]
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
		$this->foodSharePointGateway->acceptFoodSharePoint($this->foodSharePoint['id']);
		$this->flashMessageHelper->info($this->translator->trans('fsp.acceptSuccess'));
		$this->routeHelper->go('/?page=fairteiler&sub=ft&id=' . $this->foodSharePoint['id']);
	}

	private function delete(): void
	{
		if ($this->foodSharePointGateway->deleteFoodSharePoint($this->foodSharePoint['id'])) {
			$this->flashMessageHelper->info($this->translator->trans('fsp.deleteSuccess'));
			$this->routeHelper->go('/?page=fairteiler&bid=' . $this->regionId);
		}
	}

	public function ft(Request $request): void
	{
		$this->pageHelper->addBread($this->foodSharePoint['name']);
		$this->pageHelper->addTitle($this->foodSharePoint['name']);
		$this->pageHelper->addContent(
			$this->view->foodSharePointHead() . '
			<div>'
				. $this->v_utils->v_info(
					$this->translator->trans('fsp.publicwall'),
					$this->translator->trans('notice')
				) . '
			</div>
			<div class="ui-widget ui-widget-content ui-corner-all margin-bottom">
				' . $this->wallposts('fairteiler', $this->foodSharePoint['id']) . '
			</div>'
		);

		if ($this->foodSharePointPermissions->mayFollow()) {
			$items = [];

			if ($this->foodSharePointPermissions->mayEdit($this->regionId, $this->follower)) {
				$items[] = [
					'name' => $this->translator->trans('fsp.edit'),
					'href' => '/?page=fairteiler&bid=' . $this->regionId . '&sub=edit&id=' . $this->foodSharePoint['id'],
				];
			}

			if ($this->isFollower()) {
				if ($this->foodSharePointPermissions->mayUnfollow($this->foodSharePoint['id'])) {
					$items[] = [
						'name' => $this->translator->trans('fsp.unfollow'),
						'href' => $this->routeHelper->getSelf() . '&follow=0',
					];
				}
			} else {
				$items[] = [
					'name' => $this->translator->trans('fsp.follow'),
					'click' => 'u_follow();return false;'
				];
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
		$this->pageHelper->addBread($this->translator->trans('fsp.add'));

		if ($request->request->get('form_submit') === 'fairteiler') {
			if ($this->handleAdd($request)) {
				if ($this->foodSharePointPermissions->mayAdd($this->regionId)) {
					$this->flashMessageHelper->info($this->translator->trans('fsp.addSuccess'));
				} else {
					$this->flashMessageHelper->info($this->translator->trans('fsp.suggestSuccess'));
				}
				$this->routeHelper->go('/?page=fairteiler&bid=' . (int)$this->regionId);
			} else {
				$this->flashMessageHelper->error($this->translator->trans('fsp.addError'));
			}
		}

		$this->pageHelper->addContent($this->view->foodSharePointForm());
		$this->pageHelper->addContent(
			$this->v_utils->v_menu(
				[
					[
						'name' => $this->translator->trans('back'),
						'href' => '/?page=fairteiler&bid=' . (int)$this->regionId . '',
					],
				],
				$this->translator->trans('options')
			),
			CNT_RIGHT
		);
	}

	private function handleEditFsp(Request $request): bool
	{
		if ($this->foodSharePointPermissions->mayEdit($this->regionId, $this->follower)) {
			$data = $this->prepareInput($request);
			if ($this->validateInput($data)) {
				$fspManager = $this->sanitizerService->tagSelectIds($request->request->get('fspmanagers'));
				$this->foodSharePointGateway->updateFSPManagers($this->foodSharePoint['id'], $fspManager);

				return $this->foodSharePointGateway->updateFoodSharePoint($this->foodSharePoint['id'], $data);
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

	private function handleAdd(Request $request): int
	{
		$data = $this->prepareInput($request);
		if ($this->validateInput($data)) {
			$status = 0;
			if ($this->foodSharePointPermissions->mayAdd($this->regionId)) {
				$status = 1;
			}
			$data['status'] = $status;

			return $this->foodSharePointGateway->addFoodSharePoint($this->session->id(), $data);
		}

		return 0;
	}

	private function isFollower(): bool
	{
		return isset($this->follower['all'][$this->session->id()]);
	}
}
