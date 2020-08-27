<?php

namespace Foodsharing\Modules\Voting;

use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Region\RegionGateway;
use Foodsharing\Permissions\VotingPermissions;
use Symfony\Contracts\Translation\TranslatorInterface;

class VotingControl extends Control
{
	private VotingGateway $votingGateway;
	private VotingPermissions $votingPermissions;
	private RegionGateway $regionGateway;
	private TranslatorInterface $translator;

	public function __construct(
		VotingView $view,
		VotingGateway $votingGateway,
		VotingPermissions $votingPermissions,
		RegionGateway $regionGateway,
		TranslatorInterface $translator
	) {
		$this->view = $view;
		$this->votingGateway = $votingGateway;
		$this->votingPermissions = $votingPermissions;
		$this->regionGateway = $regionGateway;
		$this->translator = $translator;
		parent::__construct();
	}

	public function index()
	{
		if (isset($_GET['id']) && ($poll = $this->votingGateway->getPoll($_GET['id'], false))
			&& $this->votingPermissions->maySeePoll($poll)) {
			$region = $this->regionGateway->getRegion($poll->regionId);
			$this->pageHelper->addBread($region['name'], '/?page=bezirk&bid=' . $region['id']);
			$this->pageHelper->addBread($this->translator->trans('terminology.polls'), '/?page=bezirk&bid=' . $region['id'] . '&sub=polls');
			$this->pageHelper->addBread($poll->name);
			$this->pageHelper->addTitle($poll->name);

			if ($this->votingPermissions->maySeeResults($poll)) {
				$poll = $this->votingGateway->getPoll($poll->id, true);
			}

			$mayVote = $this->votingPermissions->mayVote($poll);
			$this->pageHelper->addContent($this->view->pollOverview($poll, $region, $mayVote,
				$mayVote ? null : $this->votingGateway->hasUserVoted($poll->id, $this->session->id()))
			);
		} elseif (isset($_GET['sub']) && $_GET['sub'] === 'new' && isset($_GET['bid']) && ($region = $this->regionGateway->getRegion($_GET['bid']))
			&& $this->votingPermissions->mayCreatePoll($region['id'])) {
			$this->pageHelper->addBread($region['name'], '/?page=bezirk&bid=' . $region['id']);
			$this->pageHelper->addBread($this->translator->trans('terminology.polls'), '/?page=bezirk&bid=' . $region['id'] . '&sub=polls');
			$this->pageHelper->addBread($this->translator->trans('polls.new_poll'));
			$this->pageHelper->addTitle($this->translator->trans('polls.new_poll'));

			$this->pageHelper->addContent($this->view->newPollForm($region));
		} else {
			$this->flashMessageHelper->info($this->translator->trans('poll.not_available'));
			$this->routeHelper->go('/?page=dashboard');
		}
	}
}
