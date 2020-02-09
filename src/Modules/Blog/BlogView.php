<?php

namespace Foodsharing\Modules\Blog;

use Foodsharing\Modules\Core\View;

class BlogView extends View
{
	public function listArticle($data)
	{
		$rows = [];
		foreach ($data as $d) {
			$row_tmp = [];

			if ($this->session->isAdminFor($d['bezirk_id']) || $this->session->isOrgaTeam()) {
				$row_tmp[] = ['cnt' => $this->v_utils->v_activeSwitcher('blog_entry', $d['id'], $d['active'])];
			} else {
				$row_tmp[] = ['cnt' => $this->translationHelper->s('status_' . $d['active'])];
			}
			$row_tmp[] = ['cnt' => '<span style="display:none;">a' . $d['time_ts'] . '</span><a class="linkrow ui-corner-all" href="/?page=blog&sub=edit&id=' . $d['id'] . '">' . date('d.m.Y', $d['time_ts']) . '</a>'];
			$row_tmp[] = ['cnt' => '<a class="linkrow ui-corner-all" href="/?page=blog&sub=edit&id=' . $d['id'] . '">' . $d['name'] . '</a>'];
			$row_tmp[] = ['cnt' => $this->v_utils->v_toolbar(['id' => $d['id'], 'types' => ['edit', 'delete'], 'confirmMsg' => $this->translationHelper->sv('delete_sure', $d['name'])])];

			$rows[] = $row_tmp;
		}

		$theads = [];

		$theads[] = ['name' => $this->translationHelper->s('status'), 'sort' => false, 'width' => 140];
		$theads[] = ['name' => $this->translationHelper->s('date'), 'width' => 80];
		$theads[] = ['name' => $this->translationHelper->s('name')];
		$theads[] = ['name' => $this->translationHelper->s('actions'), 'sort' => false, 'width' => 50];

		$table = $this->v_utils->v_tablesorter($theads, $rows);

		return $this->v_utils->v_field($table, $this->translationHelper->s('article'));
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
		$bezirkchoose = '';
		if ($add) {
			$title = $this->translationHelper->s('neu_blog_entry');
		} else {
			$title = $this->translationHelper->s('edit_article');
			global $g_data;
			$this->pageHelper->addContent($this->v_utils->v_field(
				$this->v_utils->v_activeSwitcher('blog_entry', $_GET['id'], $g_data['active']),
				'Status',
				['class' => 'ui-padding']
			), CNT_LEFT);
		}
		if (is_array($bezirke) && count($bezirke) > 1) {
			$bezirkchoose = $this->v_utils->v_form_select('bezirk_id', ['values' => $bezirke]);
		} elseif (is_array($bezirke)) {
			$bezirk = end($bezirke);
			$title = 'Neuer Artikel für ' . $bezirk['name'];
			$bezirkchoose = $this->v_utils->v_form_hidden('bezirk_id', $bezirk['id']);
		}

		return $this->v_utils->v_form('test', [
			$this->v_utils->v_field(
				$bezirkchoose .
				$this->v_utils->v_form_text('name') . $this->v_utils->v_form_textarea('teaser', ['style' => 'height:75px;']) .
				$this->v_utils->v_form_picture('picture', ['resize' => [250, 528], 'crop' => [(250 / 135), (528 / 170)]]),

				$title,
				['class' => 'ui-padding']
			),
			$this->v_utils->v_field($this->v_utils->v_form_tinymce('body', ['nowrapper' => true, 'public_content' => true]), 'Inhalt')
		]);
	}
}
