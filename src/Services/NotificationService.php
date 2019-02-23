<?php

namespace Foodsharing\Services;

use Foodsharing\Lib\Func;
use Foodsharing\Modules\Bell\BellGateway;
use Foodsharing\Modules\FairTeiler\FairTeilerGateway;

final class NotificationService
{
	private $bellGateway;
	private $fairteilerGateway;
	private $func;
	private $sanitizerService;

	public function __construct(
		BellGateway $bellGateway,
		FairTeilerGateway $fairTeilerGateway,
		Func $func,
		SanitizerService $sanitizerService
	) {
		$this->bellGateway = $bellGateway;
		$this->fairteilerGateway = $fairTeilerGateway;
		$this->func = $func;
		$this->sanitizerService = $sanitizerService;
	}

	public function newFairteilerPost(int $fairteilerId)
	{
		if ($ft = $this->fairteilerGateway->getFairteiler($fairteilerId)) {
			$post = $this->fairteilerGateway->getLastFtPost($fairteilerId);
			if ($followers = $this->fairteilerGateway->getEmailFollower($fairteilerId)) {
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

				foreach ($followers as $f) {
					$this->func->tplMail(18, $f['email'], array(
						'link' => BASE_URL . '/?page=fairteiler&sub=ft&id=' . (int)$fairteilerId,
						'name' => $f['name'],
						'anrede' => $this->func->genderWord($f['geschlecht'], 'Lieber', 'Liebe', 'Liebe/r'),
						'fairteiler' => $ft['name'],
						'post' => $body
					));
				}
			}

			if ($followers = $this->fairteilerGateway->getInfoFollowerIds($fairteilerId)) {
				$followersWithoutPostAuthor = array_diff($followers, [$post['fs_id']]);
				$this->bellGateway->addBell(
					$followersWithoutPostAuthor,
					'ft_update_title',
					'ft_update',
					'img img-recycle yellow',
					array('href' => '/?page=fairteiler&sub=ft&id=' . (int)$fairteilerId),
					array('name' => $ft['name'], 'user' => $post['fs_name'], 'teaser' => $this->sanitizerService->tt($post['body'], 100)),
					'fairteiler-' . (int)$fairteilerId
				);
			}
		}
	}
}
