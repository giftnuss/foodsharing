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
			. $this->getImage($news, 'crop_0_528_')
			. '<p>'
			. $this->sanitizerService->purifyHtml($news['body'])
			. '</p><div style="clear:both;"></div></div>'
		);
	}

	public function newsListItem(array $news): string
	{
		return '<div class="news-post"><h2><a href="/?page=blog&sub=read&id=' . $news['id'] . '">' . $news['name'] . '</a></h2><p class="small"><span class="time">' . $this->timeHelper->niceDate(
				$news['time_ts']
			) . '</span><span class="name"> von ' . $news['fs_name'] . '</span></p>' . $this->getImage(
				$news
			) . '<p>' . $this->routeHelper->autolink(
				$news['teaser']
			) . '</p><p><a class="button" href="/?page=blog&sub=read&id=' . $news['id'] . '">weiterlesen</a></p><div style="clear:both;"></div></div>';
	}

	private function getImage(array $news, string $prefix = 'crop_1_528_'): string
	{
		if (empty($news['picture'])) {
			return '';
		}
		$src = '/images/' . str_replace('/', '/' . $prefix, $news['picture']);

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

	public function blog_entry_form(array $regions, bool $add = false): string
	{
		if (count($regions) < 1) {
			// TODO this is not supposed to happen, handle better
			return '';
		}

		if ($add) {
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

		return $this->v_utils->v_form('test', [
			$this->v_utils->v_field(
				$this->v_utils->v_info($this->translator->trans('blog.publish-info'))
				. $bezirkchoose
				. $this->v_utils->v_form_text('name')
				. $this->v_utils->v_form_textarea('teaser', [
					'style' => 'height:75px;',
				])
				. $this->v_form_picture('picture', [250, 528], [(250 / 135), (528 / 170)]),
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

	/**
	 * @deprecated Use modern frontend code instead
	 */
	private function v_form_picture(string $id, array $resize, array $crop): string
	{
		$id = $this->identificationHelper->id($id);

		$this->pageHelper->addJs('
			$("#' . $id . '-link").fancybox({
				minWidth: 600,
				scrolling: "auto",
				closeClick: false,
				helpers: {
					overlay: {closeClick: false}
				}
			});

			$("#' . $id . '-opener").button().on("click", function () {
				$("#' . $id . '-link").trigger("click");
			});
		');

		$this->pageHelper->addHidden('
		<div id="' . $id . '-fancy">
			<div class="popbox">
				<h3>' . $this->translator->trans('picture_upload_widget.picture_upload') . '</h3>
				<p class="subtitle">' . $this->translator->trans('picture_upload_widget.choose_picture') . '</p>

				<form id="' . $id . '-form" method="post" enctype="multipart/form-data" target="' . $id . '-iframe" action="/xhr.php?f=uploadPicture&id=' . $id . '&crop=1">

					<input type="file" name="uploadpic" onchange="showLoader();$(\'#' . $id . '-form\')[0].submit();" />

					<input type="hidden" id="' . $id . '-action" name="action" value="uploadPicture" />
					<input type="hidden" id="' . $id . '-id" name="id" value="' . $id . '" />

					<input type="hidden" id="' . $id . '-x" name="x" value="0" />
					<input type="hidden" id="' . $id . '-y" name="y" value="0" />
					<input type="hidden" id="' . $id . '-w" name="w" value="0" />
					<input type="hidden" id="' . $id . '-h" name="h" value="0" />

					<input type="hidden" id="' . $id . '-ratio" name="ratio" value="' . json_encode($crop) . '" />
					<input type="hidden" id="' . $id . '-ratio-i" name="ratio-i" value="0" />
					<input type="hidden" id="' . $id . '-ratio-val" name="ratio-val" value="[]" />
					<input type="hidden" id="' . $id . '-resize" name="resize" value="' . json_encode($resize) . '" />
				</form>

				<div id="' . $id . '-crop"></div>

				<iframe src="" id="' . $id . '-iframe" name="' . $id . '-iframe" style="width: 1px; height: 1px; visibility: hidden;"></iframe>
			</div>
		</div>');

		$thumb = '';

		$pic = $this->dataHelper->getValue($id);
		if (!empty($pic)) {
			$thumb = '<img src="images/' . str_replace('/', '/thumb_', $pic) . '" />';
		}
		$out = '
			<input type="hidden" name="' . $id . '" id="' . $id . '" value="' . $pic . '" /><div id="' . $id . '-preview">' . $thumb . '</div>
			<span id="' . $id . '-opener">' . $this->translator->trans('upload.image') . '</span><span style="display: none;"><a href="#' . $id . '-fancy" id="' . $id . '-link">&nbsp;</a></span>';

		return $this->v_utils->v_input_wrapper($this->translator->trans($id), $out);
	}
}
