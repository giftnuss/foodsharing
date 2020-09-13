<?php

namespace Foodsharing\Utility;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Legal\LegalControl;
use Foodsharing\Modules\Legal\LegalGateway;
use Symfony\Contracts\Translation\TranslatorInterface;

final class RouteHelper
{
	private Session $session;
	private TranslatorInterface $translator;
	private LegalGateway $legalGateway;

	public function __construct(
		Session $session,
		TranslatorInterface $translator,
		LegalGateway $legalGateway
	) {
		$this->session = $session;
		$this->translator = $translator;
		$this->legalGateway = $legalGateway;
	}

	public function go(string $url)
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
		$this->go('/?page=login&ref=' . urlencode($this->getSelf()));
	}

	public function goPage(string $page = ''): void
	{
		if (empty($page)) {
			$page = $this->getPage();
			if (isset($_GET['bid'])) {
				$page .= '&bid=' . (int)$_GET['bid'];
			}
		}
		$this->go('/?page=' . $page);
	}

	public function getSelf()
	{
		// TODO revert all this when index.php is the only entry point left
		// xhr.php and xhrapp.php currently modify REQUEST_URI to make symfony's routing happy.
		// see those two files for the reason why
		// because we don't want to use these technically-invalid URIs anywhere (form URLs for example),
		// fix them if needed

		// Example of an URI affected by the hack:
		// '/xhrapp.php/xhrapp.php?app=groups&m=apply&id=1'
		// only one /xhrapp.php should be there

		$originalRequestUri = $_SERVER['REQUEST_URI'];

		$fixedRequestUri = preg_replace('/^\/xhr.php\/xhr.php/', '/xhr.php', $originalRequestUri, 1);
		$fixedRequestUri = preg_replace('/^\/xhrapp.php\/xhrapp.php/', '/xhrapp.php', $fixedRequestUri, 1);

		return $fixedRequestUri;
	}

	public function getPage(): string
	{
		return $this->getGet('page') ?: 'index';
	}

	public function getSubPage(): string
	{
		return $this->getGet('sub') ?: 'index';
	}

	private function getGet(string $name): string
	{
		return $_GET[$name] ?? false;
	}

	public function pageLink(string $page, ?string $title = null): array
	{
		return ['href' => '/?page=' . $page, 'name' => $title ?? $this->translator->trans('bread.backToOverview')];
	}

	public function autolink(string $str, array $attributes = []): string
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
		) ?: '';
		$str = substr($str, 1);
		// adds http:// if not existing
		return preg_replace('`href=\"www`', 'href="http://www', $str) ?: '';
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
