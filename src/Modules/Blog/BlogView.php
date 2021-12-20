<?php

namespace Foodsharing\Modules\Blog;

use Foodsharing\Lib\Session;
use Foodsharing\Lib\View\Utils;
use Foodsharing\Modules\Core\View;
use Foodsharing\Permissions\BlogPermissions;
use Foodsharing\Utility\DataHelper;
use Foodsharing\Utility\IdentificationHelper;
use Foodsharing\Utility\ImageHelper;
use Foodsharing\Utility\PageHelper;
use Foodsharing\Utility\RouteHelper;
use Foodsharing\Utility\Sanitizer;
use Foodsharing\Utility\TimeHelper;
use Foodsharing\Utility\TranslationHelper;
use Symfony\Contracts\Translation\TranslatorInterface;

class BlogView extends View
{
	private BlogPermissions $blogPermissions;

	// picture size on the blog post page and the upload form
	private const PICTURE_FULL_WIDTH = 528;
	private const PICTURE_FULL_HEIGHT = 285;
	// picture size in the blog list
	private const PICTURE_PREVIEW_WIDTH = 500;
	private const PICTURE_PREVIEW_HEIGHT = 161;

	public function __construct(
		\Twig\Environment $twig,
		Session $session,
		Utils $viewUtils,
		BlogPermissions $blogPermissions,
		DataHelper $dataHelper,
		IdentificationHelper $identificationHelper,
		ImageHelper $imageService,
		PageHelper $pageHelper,
		RouteHelper $routeHelper,
		Sanitizer $sanitizerService,
		TimeHelper $timeHelper,
		TranslationHelper $translationHelper,
		TranslatorInterface $translator
	) {
		parent::__construct(
			$twig,
			$session,
			$viewUtils,
			$dataHelper,
			$identificationHelper,
			$imageService,
			$pageHelper,
			$routeHelper,
			$sanitizerService,
			$timeHelper,
			$translationHelper,
			$translator
		);
		$this->blogPermissions = $blogPermissions;
	}

	public function blogpostOverview(array $data): string
	{
		return $this->vueComponent('vue-blog-overview', 'BlogOverview', [
			'mayAdministrateBlog' => $this->blogPermissions->mayAdministrateBlog(),
			'managedRegions' => $this->session->getMyAmbassadorRegionIds(),
			'blogList' => $data,
		]);
	}

	public function newsPost(array $news): string
	{
		return $this->v_utils->v_field(
			'<div class="news-post full"><h2><a href="/?page=blog&sub=read&id='
			. $news['id'] . '">' . $news['name']
			. '</a></h2><p class="small"><span class="time">'
			. $this->timeHelper->niceDate($news['time_ts'])
			. '</span><span class="name"> von '
			. $news['fs_name']
			. '</span></p>'
			. $this->getImage($news, null, 'crop_0_528_')
			. '<p>'
			. $this->sanitizerService->purifyHtml($news['body'])
			. '</p><div class="clear"></div></div>'
		);
	}

	public function newsListItem(array $news): string
	{
		return '<div class="news-post"><h2><a href="/?page=blog&sub=read&id=' . $news['id'] . '">' . $news['name'] . '</a></h2><p class="small"><span class="time">' . $this->timeHelper->niceDate(
				$news['time_ts']
			) . '</span><span class="name"> von ' . $news['fs_name'] . '</span></p>' . $this->getImage(
				$news, [self::PICTURE_PREVIEW_WIDTH, self::PICTURE_PREVIEW_HEIGHT]
			) . '<p>' . $this->routeHelper->autolink(
				$news['teaser']
			) . '</p><p><a class="button" href="/?page=blog&sub=read&id=' . $news['id'] . '">weiterlesen</a></p><div class="clear"></div></div>';
	}

	private function getImage(array $news, array $size = null, string $prefix = 'crop_1_528_'): string
	{
		if (empty($news['picture'])) {
			return '';
		}

		if (strpos($news['picture'], '/api/uploads/') === 0) {
			// path for pictures uploaded with the new API
			$src = $news['picture'];
			if (!empty($size)) {
				$src .= '?w=' . $size[0] . '&h=' . $size[1];
			}
		} else {
			// backward compatible path for old pictures
			$src = '/images/' . str_replace('/', '/' . $prefix, $news['picture']);
		}

		return '<a href="/?page=blog&sub=read&id=' . $news['id'] . '">'
			. '<img class="corner-all" src="' . $src . '" />'
			. '</a>';
	}

	public function pager(int $page): string
	{
		$links = '';
		if ($page > 1) {
			$links .= '<a class="button" href="/?page=blog&p=' . ($page - 1) . '"><i class="fas fa-arrow-circle-left"></i></a>';
		}

		$links .= '<a class="button" href="/?page=blog&p=' . ($page + 1) . '"><i class="fas fa-arrow-circle-right"></i></a>';

		return '<p class="pager">' . $links . '</p>';
	}

	public function blog_entry_form(array $regions, array $data = null): string
	{
		if (count($regions) < 1) {
			// TODO this is not supposed to happen, handle better
			return '';
		}

		if (is_null($data)) {
			$title = $this->translator->trans('blog.new');
		} else {
			$title = $this->translator->trans('blog.edit');
		}

		$bezirkchoose = '';
		if (count($regions) === 1) {
			// Automatically select this region
			$bezirk = end($regions);
			$title = $this->translator->trans('blog.newTitle', ['{region}' => $bezirk['name']]);
			$bezirkchoose = $this->v_utils->v_form_hidden('bezirk_id', $bezirk['id']);
		} else {
			$bezirkchoose = $this->v_utils->v_form_select('bezirk_id', ['values' => $regions]);
		}

		// create picture upload component
		$initialValue = '';
		if (!is_null($data) && !empty($data['picture'])) {
			if (strpos($data['picture'], '/api/uploads/') === 0) {
				// path for pictures uploaded with the new API
				$initialValue = $data['picture'];
			} else {
				// backward compatible path for old pictures
				$initialValue = 'images/' . $data['picture'];
			}
		}
		$uploadForm = $this->vueComponent('image-upload', 'file-upload-v-form', [
			'inputName' => 'picture',
			'isImage' => true,
			'initialValue' => $initialValue,
			'imgHeight' => self::PICTURE_FULL_WIDTH,
			'imgWidth' => self::PICTURE_FULL_HEIGHT
		]);

		$this->dataHelper->setEditData($data);

		return $this->v_utils->v_form('test', [
			$this->v_utils->v_field(
				$this->v_utils->v_info($this->translator->trans('blog.publish-info'))
				. $bezirkchoose
				. $this->v_utils->v_form_text('name')
				. $this->v_utils->v_form_textarea('teaser', [
					'style' => 'height:75px;',
				])
				. $uploadForm,
				$title,
				['class' => 'ui-padding']
			),
			$this->v_utils->v_field($this->v_utils->v_form_tinymce('body', [
				'nowrapper' => true,
				'public_content' => true,
				'label' => $this->translator->trans('blog.content'),
			]), $this->translator->trans('blog.content'))
		]);
	}
}
