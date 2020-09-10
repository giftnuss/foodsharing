<?php

namespace Foodsharing\Modules\Activity;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Activity\DTO\ActivityCategory;
use Foodsharing\Modules\Activity\DTO\ActivityImageOption;
use Foodsharing\Modules\Activity\DTO\ActivityOption;
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
	 * Returns all activity options for the logged in user sorted into categories.
	 *
	 * @return array ActivityCategory[]
	 */
	public function getOptions(): array
	{
		// list of currently unchecked options
		$uncheckedOptions = [];
		if ($list = $this->session->getOption('activity-listings')) {
			$uncheckedOptions = $list;
		}

		// regions and groups
		$regionOptions = [];
		$groupOptions = [];
		if ($bezirke = $this->session->getRegions()) {
			foreach ($bezirke as $b) {
				$option = ActivityOption::create($b['name'], $b['id'],
					!isset($uncheckedOptions['bezirk-' . $b['id']])
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
			$mailboxOptions = array_map(function ($b) use ($uncheckedOptions) {
				return ActivityOption::create(
					$b['name'] . '@' . PLATFORM_MAILBOX_HOST, $b['id'],
					!isset($uncheckedOptions['mailbox-' . $b['id']])
				);
			}, $boxes);
		}

		// buddy walls
		$buddyOptions = [];
		if ($buddyIds = $this->session->get('buddy-ids')) {
			$buddies = $this->activityGateway->fetchAllBuddies((array)$buddyIds);
			$buddyOptions = array_map(function ($b) use ($uncheckedOptions) {
				return ActivityImageOption::create(
					$b['name'], $b['id'], !isset($uncheckedOptions['buddywall-' . $b['id']]),
					$this->imageHelper->img($b['photo'])
				);
			}, $buddies);
		}

		return [
			ActivityCategory::create('bezirk', $this->translator->trans('search.mygroups'), $groupOptions),
			ActivityCategory::create('bezirk', $this->translator->trans('search.myregions'), $regionOptions),
			ActivityCategory::create('mailbox', $this->translator->trans('terminology.mailboxes'), $mailboxOptions),
			ActivityCategory::create('buddywall', $this->translator->trans('search.mybuddies'), $buddyOptions)
		];
	}

	/**
	 * Sets the deactivated activities for the logged in user.
	 *
	 * @param array $options a list of activities to be deactivated, objects with 'index' and 'id' entries
	 */
	public function setOptions(array $options): void
	{
		$list = [];
		foreach ($options as $o) {
			if ((int)$o['id'] > 0 && isset($o['index'], $o['id'])) {
				$list[$o['index'] . '-' . $o['id']] = [
					'index' => $o['index'],
					'id' => $o['id']
				];
			}
		}

		$this->session->setOption('activity-listings', empty($list) ? false : $list);
	}
}
