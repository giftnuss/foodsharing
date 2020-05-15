<?php

namespace Foodsharing\Helpers;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Legal\LegalControl;
use Foodsharing\Modules\Legal\LegalGateway;

final class RouteHelper
{
	private $translationHelper;
	private $legalGateway;
	private $session;

	public function __construct(Session $session, TranslationHelper $translationHelper, LegalGateway $legalGateway)
	{
		$this->translationHelper = $translationHelper;
		$this->legalGateway = $legalGateway;
		$this->session = $session;
	}

	public function go(string $url): void
	{
		header('Location: ' . $url);
		exit();
	}

	public function goSelf(): void
	{
		$this->go($this->getSelf());
	}

	public function goLogin(): void
	{
		$this->go('/?page=login&ref=' . urlencode($_SERVER['REQUEST_URI']));
	}

	public function goPage($page = false): void
	{
		if (!$page) {
			$page = $this->getPage();
			if (isset($_GET['bid'])) {
				$page .= '&bid=' . (int)$_GET['bid'];
			}
		}
		$this->go('/?page=' . $page);
	}

	public function getSelf()
	{
		return $_SERVER['REQUEST_URI'];
	}

	public function getPage()
	{
		$page = $this->getGet('page');
		if (!$page) {
			$page = 'index';
		}

		return $page;
	}

	public function getSubPage()
	{
		$sub_page = $this->getGet('sub');
		if (!$sub_page) {
			$sub_page = 'index';
		}

		return $sub_page;
	}

	private function getGet(string $name)
	{
		return $_GET[$name] ?? false;
	}

	public function pageLink($page, $id, $action = '')
	{
		if (!empty($action)) {
			$action = '&a=' . $action;
		}

		return ['href' => '/?page=' . $page . $action, 'name' => $this->translationHelper->s($id)];
	}

	public function autolink(string $str, array $attributes = [])
	{
		$attributes['target'] = '_blank';
		$attrs = '';
		foreach ($attributes as $attribute => $value) {
			$attrs .= " {$attribute}=\"{$value}\"";
		}
		$str = ' ' . $str;
		$str = preg_replace(
			'`([^"=\'>])(((http|https|ftp)://|www.)[^\s<]+[^\s<\.)])`i',
			'$1<a href="$2"' . $attrs . '>$2</a>',
			$str
		);
		$str = substr($str, 1);
		// adds http:// if not existing
		return preg_replace('`href=\"www`', 'href="http://www', $str);
	}

	public function getLegalControlIfNecessary(): ?string
	{
		if ($this->session->may() && !$this->onSettingsOrLogoutPage() && !$this->legalRequirementsMetByUser()) {
			return LegalControl::class;
		}

		return null;
	}

	private function legalRequirementsMetByUser(): bool
	{
		return $this->usersPrivacyPolicyUpToDate() && $this->usersPrivacyNoticeUpToDate();
	}

	private function usersPrivacyPolicyUpToDate(): bool
	{
		$privacyPolicyVersion = $this->legalGateway->getPpVersion();

		return $privacyPolicyVersion && $privacyPolicyVersion == $this->session->user('privacy_policy_accepted_date');
	}

	private function usersPrivacyNoticeUpToDate(): bool
	{
		if ($this->session->user('rolle') < 2) {
			return true;
		}
		$privacyNoticeVersion = $this->legalGateway->getPnVersion();

		return $privacyNoticeVersion && $privacyNoticeVersion == $this->session->user('privacy_notice_accepted_date');
	}

	private function onSettingsOrLogoutPage(): bool
	{
		return in_array($this->getPage(), ['settings', 'logout']);
	}
}
