<?php

namespace Foodsharing\Modules\Activity;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Activity\DTO\ActivityFilter;
use Foodsharing\Modules\Activity\DTO\ActivityFilterCategory;
use Foodsharing\Modules\Activity\DTO\ImageActivityFilter;
use Foodsharing\Modules\Core\DBConstants\Region\Type;
use Foodsharing\Modules\Mailbox\MailboxGateway;
use Foodsharing\Utility\ImageHelper;
use Symfony\Contracts\Translation\TranslatorInterface;

class ActivityTransactions
{
	private ActivityGateway $activityGateway;
	private MailboxGateway $mailboxGateway;
	private ImageHelper $imageHelper;
	private TranslatorInterface $translator;
	private Session $session;

	public function __construct(
		ActivityGateway $activityGateway,
		MailboxGateway $mailboxGateway,
		ImageHelper $imageHelper,
		TranslatorInterface $translator,
		Session $session
	) {
		$this->activityGateway = $activityGateway;
		$this->mailboxGateway = $mailboxGateway;
		$this->imageHelper = $imageHelper;
		$this->translator = $translator;
		$this->session = $session;
	}

	/**
	 * Returns all activity filters for the logged in user sorted into categories.
	 *
	 * @return array ActivityFilterCategory[]
	 */
	public function getFilters(): array
	{
		// list of currently excluded activities
		$excluded = $this->session->getOption('activity-listings') ?: [];

		// regions and groups
		$regionOptions = [];
		$groupOptions = [];
		if ($bezirke = $this->session->getRegions()) {
			foreach ($bezirke as $b) {
				$option = ActivityFilter::create($b['name'], $b['id'],
					!isset($excluded['bezirk-' . $b['id']])
				);
				if ($b['type'] == Type::WORKING_GROUP) {
					$groupOptions[] = $option;
				} else {
					$regionOptions[] = $option;
				}
			}
		}

		// mailboxes
		$mailboxOptions = [];
		if ($boxes = $this->mailboxGateway->getBoxes(
			$this->session->isAmbassador(),
			$this->session->id(),
			$this->session->may('bieb'))
		) {
			$mailboxOptions = array_map(function ($b) use ($excluded) {
				return ActivityFilter::create(
					$b['name'] . '@' . PLATFORM_MAILBOX_HOST, $b['id'],
					!isset($excluded['mailbox-' . $b['id']])
				);
			}, $boxes);
		}

		// buddy walls
		$buddyOptions = [];
		if ($buddyIds = $this->session->get('buddy-ids')) {
			$buddies = $this->activityGateway->fetchAllBuddies((array)$buddyIds);
			$buddyOptions = array_map(function ($b) use ($excluded) {
				return ImageActivityFilter::create(
					$b['name'], $b['id'], !isset($excluded['buddywall-' . $b['id']]),
					$this->imageHelper->img($b['photo'])
				);
			}, $buddies);
		}

		return [
			ActivityFilterCategory::create('bezirk', $this->translator->trans('search.mygroups'),
				$this->translator->trans('terminology.groups'), $groupOptions),
			ActivityFilterCategory::create('bezirk', $this->translator->trans('search.myregions'),
				$this->translator->trans('terminology.regions'), $regionOptions),
			ActivityFilterCategory::create('mailbox', $this->translator->trans('terminology.mailboxes'),
				$this->translator->trans('terminology.mailboxes'), $mailboxOptions),
			ActivityFilterCategory::create('buddywall', $this->translator->trans('search.mybuddies'),
				$this->translator->trans('terminology.buddies'), $buddyOptions)
		];
	}

	/**
	 * Sets the deactivated activities for the logged in user.
	 *
	 * @param array $excluded a list of activities to be deactivated. List entries should be objects with
	 * 'index' and 'id' entries
	 */
	public function setExcludedFilters(array $excluded): void
	{
		$list = [];
		foreach ($excluded as $o) {
			if (isset($o['index'], $o['id']) && (int)$o['id'] > 0) {
				$list[$o['index'] . '-' . $o['id']] = [
					'index' => $o['index'],
					'id' => $o['id']
				];
			}
		}

		$this->session->setOption('activity-listings', $list);
	}
}
