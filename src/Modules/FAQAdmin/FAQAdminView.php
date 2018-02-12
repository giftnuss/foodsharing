<?php
/**
 * Created by IntelliJ IDEA.
 * User: matthias
 * Date: 11.02.18
 * Time: 17:16.
 */

namespace Foodsharing\Modules\FAQAdmin;

use Foodsharing\Modules\Core\View;

class FAQAdminView extends View
{
	public function faq_form($categories)
	{
		return $this->v_utils->v_form('faq', array(
			$this->v_utils->v_field(
				$this->v_utils->v_form_select('faq_kategorie_id', array('add' => true, 'required' => true, 'values' => $categories)) .
				$this->v_utils->v_form_textarea('name', array('style' => 'height:75px;', 'required' => true)),

				$this->func->s('neu_faq'),
				array('class' => 'ui-padding')
			),
			$this->v_utils->v_field($this->v_utils->v_form_tinymce('answer', array('nowrapper' => true)), $this->func->s('answer'))
		));
	}
}
