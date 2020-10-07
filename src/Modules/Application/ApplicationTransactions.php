<?php

namespace Foodsharing\Modules\Application;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Bell\BellGateway;
use Foodsharing\Modules\Bell\DTO\Bell;

class ApplicationTransactions
{
	private ApplicationGateway $applicationGateway;
	private BellGateway $bellGateway;
	private Session $session;

	public function __construct(
		ApplicationGateway $applicationGateway,
		BellGateway $bellGateway,
		Session $session
	) {
		$this->applicationGateway = $applicationGateway;
		$this->bellGateway = $bellGateway;
		$this->session = $session;
	}

	public function acceptApplication(array $group, int $userId): void
	{
		$this->applicationGateway->acceptApplication($group['id'], $userId);

		$bellData = Bell::create('workgroup_request_accept_title', 'workgroup_request_accept', 'fas fa-user-check', [
			'href' => '/?page=bezirk&bid=' . $group['id']
		], [
			'name' => $group['name']
		], 'workgroup-arequest-' . $userId);
		$this->bellGateway->addBell($userId, $bellData);
	}
}
