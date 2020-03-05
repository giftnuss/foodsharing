<?php

namespace Foodsharing\Lib\Xhr;

use Foodsharing\Helpers\TranslationHelper;
use Foodsharing\Helpers\WeightHelper;
use Foodsharing\Lib\Session;
use Foodsharing\Lib\View\Utils;
use Foodsharing\Services\ImageService;

class ViewUtils
{
	/**
	 * @var Utils
	 */
	private $viewUtils;
	private $session;
	private $imageService;
	private $translationHelper;
	private $weightHelper;

	public function __construct(
		Utils $viewUtils,
		Session $session,
		ImageService $imageService,
		TranslationHelper $translationHelper,
		WeightHelper $weightHelper
	) {
		$this->viewUtils = $viewUtils;
		$this->weightHelper = $weightHelper;
		$this->session = $session;
		$this->imageService = $imageService;
		$this->translationHelper = $translationHelper;
	}

	public function childBezirke($childs, $parent_id)
	{
		$out = '
	<select class="select childChanger" id="xv-childbezirk-' . (int)$parent_id . '" onchange="u_printChildBezirke(this);">
		<option value="-1:0" class="xv-childs-0">Bitte auswählen...</option>';
		foreach ($childs as $c) {
			$out .= '
		<option value="' . $c['id'] . ':' . (int)$c['type'] . '" class="xv-childs-' . $c['id'] . '">' . $c['name'] . '</option>';
		}
		$out .= '
	</select>';

		return $out;
	}
}
