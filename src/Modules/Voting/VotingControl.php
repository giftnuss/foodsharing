<?php

namespace Foodsharing\Modules\Voting;

use Exception;
use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Region\RegionGateway;
use Foodsharing\Permissions\VotingPermissions;

class VotingControl extends Control
{
	private VotingGateway $votingGateway;
	private VotingPermissions $votingPermissions;
	private VotingTransactions $votingTransactions;
	private RegionGateway $regionGateway;

	public function __construct(
		VotingView $view,
		VotingGateway $votingGateway,
		VotingPermissions $votingPermissions,
		VotingTransactions $votingTransactions,
		RegionGateway $regionGateway
	) {
		$this->view = $view;
		$this->votingGateway = $votingGateway;
		$this->votingPermissions = $votingPermissions;
		$this->votingTransactions = $votingTransactions;
		$this->regionGateway = $regionGateway;
		parent::__construct();
	}

	public function index()
	{
		try {
			if (isset($_GET['id']) && ($poll = $this->votingTransactions->getPoll($_GET['id'], true))
				&& $this->votingPermissions->maySeePoll($poll)) {
				$region = $this->regionGateway->getRegion($poll->regionId);
				$this->pageHelper->addBread($region['name'], '/?page=bezirk&bid=' . $region['id']);
				$this->pageHelper->addBread($this->translator->trans('terminology.polls'), '/?page=bezirk&bid=' . $region['id'] . '&sub=polls');
				$this->pageHelper->addBread($poll->name);
				$this->pageHelper->addTitle($poll->name);

				$mayVote = $this->votingPermissions->mayVote($poll);
				try {
					$voteDateTime = $this->votingGateway->getVoteDatetime($poll->id, $this->session->id());
				} catch (Exception $e) {
					$voteDateTime = null;
				}
				$mayEdit = $this->votingPermissions->mayEditPoll($poll);
				$this->pageHelper->addContent($this->view->pollOverview($poll, $region, $mayVote,
					$mayVote ? null : $voteDateTime, $mayEdit)
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
		} catch (Exception $e) {
			$this->flashMessageHelper->info($this->translator->trans('poll.not_available'));
			$this->routeHelper->go('/?page=dashboard');
		}
	}
}
