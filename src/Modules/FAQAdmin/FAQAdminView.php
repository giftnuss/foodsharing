<?php

namespace Foodsharing\Modules\FAQAdmin;

use Foodsharing\Modules\Core\View;

class FAQAdminView extends View
{
	public function faq_form($categories)
	{
		return $this->v_utils->v_form('faq', [
			$this->v_utils->v_field(
				$this->v_utils->v_form_select('faq_kategorie_id', ['add' => true, 'required' => true, 'values' => $categories]) .
				$this->v_utils->v_form_textarea('name', ['style' => 'height:75px;', 'required' => true]),

				$this->translationHelper->s('neu_faq'),
				['class' => 'ui-padding']
			),
			$this->v_utils->v_field($this->v_utils->v_form_tinymce('answer', ['nowrapper' => true]), $this->translationHelper->s('answer'))
		]);
	}
}
