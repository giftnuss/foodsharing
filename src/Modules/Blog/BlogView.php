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
	private $blogPermissions;

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

	public function listArticle($data)
	{
		$rows = [];
		foreach ($data as $article) {
			$blogId = intval($article['id']);

			if ($this->blogPermissions->mayPublish($article['bezirk_id'])) {
				$active = $this->v_utils->v_activeSwitcher('blog_entry', $blogId, boolval($article['active']));
			} else {
				$active = $this->translator->trans('blog.status.' . $article['active']);
			}

			$link = '<a class="linkrow ui-corner-all" href="/?page=blog&sub=edit&id=' . $blogId . '">';

			// No idea what that stray `a` is doing there, perhaps it is used for sorting?
			$when = '<span style="display: none;">' . 'a' . $article['time_ts'] . '</span>';
			$when .= $link . date('d.m.Y', $article['time_ts']) . '</a>';

			$name = $link . $article['name'] . '</a>';

			$actions = $this->v_utils->v_toolbar([
				'id' => $blogId,
				'types' => ['edit', 'delete'],
				'confirmMsg' => $this->translator->trans('blog.confirmDelete', ['{name}' => $article['name']]),
			]);

			$rows[] = [
				['cnt' => $active],
				['cnt' => $when],
				['cnt' => $name],
				['cnt' => $actions],
			];
		}

		$theads = [
			['name' => $this->translator->trans('blog.table.status'), 'sort' => false, 'width' => 140],
			['name' => $this->translator->trans('blog.table.date'), 'width' => 80],
			['name' => $this->translator->trans('blog.table.name')],
			['name' => $this->translator->trans('blog.table.actions'), 'sort' => false, 'width' => 50],
		];

		$table = $this->v_utils->v_tablesorter($theads, $rows);

		return $this->v_utils->v_field($table, $this->translator->trans('blog.article'));
	}

	public function newsPost($news)
	{
		return $this->v_utils->v_field(
			'<div class="news-post full"><h2><a href="/?page=blog&sub=read&id='
			. $news['id'] . '">' . $news['name']
			. '</a></h2><p class="small"><span class="time">'
			. $this->timeHelper->niceDate($news['time_ts'])
			. '</span><span class="name"> von '
			. $news['fs_name']
			. '</span></p>'
			. $this->getImage($news, 'crop_0_528_')
			. '<p>'
			. $this->sanitizerService->purifyHtml($news['body'])
			. '</p><div style="clear:both;"></div></div>'
		);
	}

	public function newsListItem($news)
	{
		return '<div class="news-post"><h2><a href="/?page=blog&sub=read&id=' . $news['id'] . '">' . $news['name'] . '</a></h2><p class="small"><span class="time">' . $this->timeHelper->niceDate(
				$news['time_ts']
			) . '</span><span class="name"> von ' . $news['fs_name'] . '</span></p>' . $this->getImage(
				$news
			) . '<p>' . $this->routeHelper->autolink(
				$news['teaser']
			) . '</p><p><a class="button" href="/?page=blog&sub=read&id=' . $news['id'] . '">weiterlesen</a></p><div style="clear:both;"></div></div>';
	}

	private function getImage($news, $prefix = 'crop_1_528_')
	{
		if (!empty($news['picture'])) {
			return '<a href="/?page=blog&sub=read&id=' . $news['id'] . '"><img class="corner-all" src="/images/' . str_replace('/', '/' . $prefix, $news['picture']) . '" /></a>';
		}

		return '';
	}

	public function pager($page)
	{
		$links = '';
		if ($page > 1) {
			$links .= '<a class="button" href="/?page=blog&p=' . ($page - 1) . '"><i class="fas fa-arrow-circle-left"></i></a>';
		}

		$links .= '<a class="button" href="/?page=blog&p=' . ($page + 1) . '"><i class="fas fa-arrow-circle-right"></i></a>';

		return '<p class="pager">' . $links . '</p>';
	}

	public function blog_entry_form($bezirke, $add = false)
	{
		if ($add) {
			$title = $this->translator->trans('blog.new');
		} else {
			$title = $this->translator->trans('blog.edit');
			global $g_data;
			$this->pageHelper->addContent($this->v_utils->v_field(
				$this->v_utils->v_activeSwitcher('blog_entry', intval($_GET['id']), boolval($g_data['active'])),
				$this->translator->trans('blog.table.status'),
				['class' => 'ui-padding']
			), CNT_LEFT);
		}

		$bezirkchoose = '';
		if (is_array($bezirke)) {
			if (count($bezirke) > 1) {
				$bezirkchoose = $this->v_utils->v_form_select('bezirk_id', ['values' => $bezirke]);
			} else {
				// Automatically select this region
				$bezirk = end($bezirke);
				$title = $this->translator->trans('blog.newTitle', ['{region}' => $bezirk['name']]);
				$bezirkchoose = $this->v_utils->v_form_hidden('bezirk_id', $bezirk['id']);
			}
		}

		return $this->v_utils->v_form('test', [
			$this->v_utils->v_field(
				$bezirkchoose
				. $this->v_utils->v_form_text('name')
				. $this->v_utils->v_form_textarea('teaser', [
					'style' => 'height:75px;',
				])
				. $this->v_utils->v_form_picture('picture', [
					'resize' => [250, 528],
					'crop' => [(250 / 135), (528 / 170)],
				]),
				$title,
				['class' => 'ui-padding']
			),
			$this->v_utils->v_field($this->v_utils->v_form_tinymce('body', [
				'nowrapper' => true,
				'public_content' => true,
			]), $this->translator->trans('blog.content'))
		]);
	}
}
