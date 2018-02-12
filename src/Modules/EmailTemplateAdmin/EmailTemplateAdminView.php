<?php
/**
 * Created by IntelliJ IDEA.
 * User: matthias
 * Date: 11.02.18
 * Time: 19:01.
 */

namespace Foodsharing\Modules\EmailTemplateAdmin;

use Foodsharing\Modules\Core\View;

class EmailTemplateAdminView extends View
{
	public function message_tpl_form()
	{
		global $g_data;
		$g_data['language_id'] = 1;

		return $this->v_utils->v_form('E-Mail Vorlage', array(
			$this->v_utils->v_field(
				$this->v_utils->v_form_select('language_id') .
				$this->v_utils->v_form_text('name', array('required' => true)) .
				$this->v_utils->v_form_text('subject', array('required' => array())) .
				$this->v_utils->v_form_file('attachement'),
				'E-Mail-Vorlage',
				array('class' => 'ui-padding')
			),
			$this->v_utils->v_field($this->v_utils->v_form_tinymce('body', array('nowrapper' => true)), $this->func->s('message'))
		), array('submit' => 'Speichern'));
	}
}
