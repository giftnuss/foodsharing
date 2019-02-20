<?php

namespace Foodsharing\Modules\Blog;

use Foodsharing\Modules\Core\View;

class BlogView extends View
{
	public function listArticle($data)
	{
		$rows = array();
		foreach ($data as $d) {
			$row_tmp = array();

			if ($this->session->isOrgaTeam() || $this->session->isAdminFor($d['bezirk_id'])) {
				$row_tmp[] = array('cnt' => $this->v_utils->v_activeSwitcher('blog_entry', $d['id'], $d['active']));
			} else {
				$row_tmp[] = array('cnt' => $this->func->s('status_' . $d['active']));
			}
			$row_tmp[] = array('cnt' => '<span style="display:none;">a' . $d['time_ts'] . '</span><a class="linkrow ui-corner-all" href="/?page=blog&sub=edit&id=' . $d['id'] . '">' . date('d.m.Y', $d['time_ts']) . '</a>');
			$row_tmp[] = array('cnt' => '<a class="linkrow ui-corner-all" href="/?page=blog&sub=edit&id=' . $d['id'] . '">' . $d['name'] . '</a>');
			$row_tmp[] = array('cnt' => $this->v_utils->v_toolbar(array('id' => $d['id'], 'types' => array('edit', 'delete'), 'confirmMsg' => $this->func->sv('delete_sure', $d['name']))));

			$rows[] = $row_tmp;
		}

		$theads = array();

		$theads[] = array('name' => $this->func->s('status'), 'sort' => false, 'width' => 140);
		$theads[] = array('name' => $this->func->s('date'), 'width' => 80);
		$theads[] = array('name' => $this->func->s('name'));
		$theads[] = array('name' => $this->func->s('actions'), 'sort' => false, 'width' => 50);

		$table = $this->v_utils->v_tablesorter($theads, $rows);

		return $this->v_utils->v_field($table, $this->func->s('article'));
	}

	public function newsPost($news)
	{
		return $this->v_utils->v_field('<div class="news-post full"><h2><a href="/?page=blog&sub=read&id=' . $news['id'] . '">' . $news['name'] . '</a></h2><p class="small"><span class="time">' . $this->func->niceDate($news['time_ts']) . '</span><span class="name"> von ' . $news['fs_name'] . '</span></p>' . $this->getImage($news, 'crop_0_528_') . '<p>' . $this->func->autolink($news['body']) . '</p><div style="clear:both;"></div></div>');
	}

	public function newsListItem($news)
	{
		return '<div class="news-post"><h2><a href="/?page=blog&sub=read&id=' . $news['id'] . '">' . $news['name'] . '</a></h2><p class="small"><span class="time">' . $this->func->niceDate($news['time_ts']) . '</span><span class="name"> von ' . $news['fs_name'] . '</span></p>' . $this->getImage($news) . '<p>' . $this->func->autolink($news['teaser']) . '</p><p><a class="button" href="/?page=blog&sub=read&id=' . $news['id'] . '">weiterlesen</a></p><div style="clear:both;"></div></div>';
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
			$title = $this->func->s('neu_blog_entry');
		} else {
			$title = $this->func->s('edit_article');
			global $g_data;
			$this->func->addContent($this->v_utils->v_field(
				$this->v_utils->v_activeSwitcher('blog_entry', $_GET['id'], $g_data['active']),
				'Status',
				array('class' => 'ui-padding')
			), CNT_LEFT);
		}
		if (is_array($bezirke) && count($bezirke) > 1) {
			$bezirkchoose = $this->v_utils->v_form_select('bezirk_id', array('values' => $bezirke));
		} elseif (is_array($bezirke)) {
			$bezirk = end($bezirke);
			$title = 'Neuer Artikel für ' . $bezirk['name'];
			$bezirkchoose = $this->v_utils->v_form_hidden('bezirk_id', $bezirk['id']);
		}

		return $this->v_utils->v_form('test', array(
			$this->v_utils->v_field(
				$bezirkchoose .
				$this->v_utils->v_form_text('name') . $this->v_utils->v_form_textarea('teaser', array('style' => 'height:75px;')) .
				$this->v_utils->v_form_picture('picture', array('resize' => array(250, 528), 'crop' => array((250 / 135), (528 / 170)))),

				$title,
				array('class' => 'ui-padding')
			),
			$this->v_utils->v_field($this->v_utils->v_form_tinymce('body', array('nowrapper' => true, 'public_content' => true)), 'Inhalt')
		));
	}
}
