<?php

namespace Foodsharing\Lib\Xhr;

use Foodsharing\Lib\Session;
use Foodsharing\Lib\View\Utils;
use Foodsharing\Utility\ImageHelper;
use Foodsharing\Utility\TranslationHelper;
use Foodsharing\Utility\WeightHelper;

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
		ImageHelper $imageService,
		TranslationHelper $translationHelper,
		WeightHelper $weightHelper
	) {
		$this->viewUtils = $viewUtils;
		$this->weightHelper = $weightHelper;
		$this->session = $session;
		$this->imageService = $imageService;
		$this->translationHelper = $translationHelper;
	}
}
