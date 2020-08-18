<?php

namespace Foodsharing\Modules\FoodSharePoint;

use Foodsharing\Modules\Bell\BellGateway;
use Foodsharing\Modules\Bell\DTO\Bell;
use Foodsharing\Utility\EmailHelper;
use Foodsharing\Utility\Sanitizer;
use Foodsharing\Utility\TranslationHelper;

class FoodSharePointTransactions
{
	private FoodSharePointGateway $foodSharePointGateway;
	private BellGateway $bellGateway;
	private EmailHelper $emailHelper;
	private TranslationHelper $translationHelper;
	private Sanitizer $sanitizer;

	public function __construct(
		FoodSharePointGateway $foodSharePointGateway,
		BellGateway $bellGateway,
		EmailHelper $emailHelper,
		TranslationHelper $translationHelper,
		Sanitizer $sanitizer
	) {
		$this->foodSharePointGateway = $foodSharePointGateway;
		$this->bellGateway = $bellGateway;
		$this->emailHelper = $emailHelper;
		$this->translationHelper = $translationHelper;
		$this->sanitizer = $sanitizer;
	}

	public function sendNewFoodSharePointPostNotifications(int $foodSharePointId): void
	{
		if ($foodSharePoint = $this->foodSharePointGateway->getFoodSharePoint($foodSharePointId)) {
			$post = $this->foodSharePointGateway->getLastFoodSharePointPost($foodSharePointId);
			if ($followers = $this->foodSharePointGateway->getEmailFollower($foodSharePointId)) {
				$body = nl2br($post['body']);

				if (!empty($post['attach'])) {
					$attach = json_decode($post['attach'], true);
					if (isset($attach['image']) && !empty($attach['image'])) {
						foreach ($attach['image'] as $img) {
							$body .= '
							<div>
								<img src="' . BASE_URL . '/images/wallpost/medium_' . $img['file'] . '" />
							</div>';
						}
					}
				}

				$followersWithoutPostAuthor = array_filter($followers, function ($x) use ($post) {
					return $x['id'] !== $post['fs_id'];
				});
				foreach ($followersWithoutPostAuthor as $f) {
					$this->emailHelper->tplMail('foodSharePoint/new_message', $f['email'], [
						'link' => BASE_URL . '/?page=fairteiler&sub=ft&id=' . (int)$foodSharePointId,
						'name' => $f['name'],
						'anrede' => $this->translationHelper->genderWord($f['geschlecht'], 'Lieber', 'Liebe', 'Liebe/r'),
						'fairteiler' => $foodSharePoint['name'],
						'post' => $body
					]);
				}
			}

			if ($followers = $this->foodSharePointGateway->getInfoFollowerIds($foodSharePointId)) {
				$followersWithoutPostAuthor = array_diff($followers, [$post['fs_id']]);
				$bellData = Bell::create(
					'ft_update_title',
					'ft_update',
					'fas fa-recycle',
					['href' => '/?page=fairteiler&sub=ft&id=' . $foodSharePointId],
					['name' => $foodSharePoint['name'], 'user' => $post['fs_name'], 'teaser' => $this->sanitizer->tt($post['body'], 100)],
					'fairteiler-' . $foodSharePointId
				);
				$this->bellGateway->addBell($followersWithoutPostAuthor, $bellData);
			}
		}
	}
}
