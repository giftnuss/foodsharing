<?php

namespace Foodsharing\Lib;

use Foodsharing\Modules\Region\RegionGateway;

final class Func
{
	private $regionGateway;

	/**
	 * @var Session
	 */
	private $session;

	public function __construct(
		RegionGateway $regionGateway
	) {
		$this->regionGateway = $regionGateway;
	}

	/**
	 * @required
	 */
	public function setSession(Session $session)
	{
		$this->session = $session;
	}

	public function s($id)
	{
		global $g_lang;

		if (isset($g_lang[$id])) {
			return $g_lang[$id];
		}

		return $id;
	}

	public function preZero($i)
	{
		if ($i < 10) {
			return '0' . $i;
		}

		return $i;
	}

	public function isMob(): bool
	{
		return isset($_SESSION['mob']) && $_SESSION['mob'] == 1;
	}

	public function submitted(): bool
	{
		return isset($_POST) && !empty($_POST);
	}

	public function info($msg, $title = false)
	{
		$t = '';
		if ($title !== false) {
			$t = '<strong>' . $title . '</strong> ';
		}
		$_SESSION['msg']['info'][] = $msg;
	}

	public function error($msg, $title = false)
	{
		$t = '';
		if ($title !== false) {
			$t = '<strong>' . $title . '</strong> ';
		}
		$_SESSION['msg']['error'][] = $t . $msg;
	}

	public function getBezirk()
	{
		return $this->regionGateway->getBezirk($this->session->getCurrentBezirkId());
	}
}
