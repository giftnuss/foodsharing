<?php

namespace Foodsharing\Modules\BusinessCard;

use Foodsharing\Modules\Core\View;

class BusinessCardView extends View
{
	public function top()
	{
		return $this->topbar($this->translator->trans('bcard.card'),
			$this->translator->trans('bcard.claim'),
			'<img src="/img/bcard.png" />'
		);
	}

	public function optionForm($selectedData)
	{
		$this->pageHelper->addJs('
			$("#optionen-form .input-wrapper:last").hide();
			
			$("#opt").on("change", function () {
				$("#optionen-form").trigger("submit");
			});
				
			$("#optionen-form").on("submit", function (ev) {
				ev.preventDefault();
				if ($("#opt").val() == "") {
					pulseError(\'' . $this->translator->trans('bcard.choose') . '\');
				} else {
					goTo("/?page=bcard&sub=makeCard&opt=" + $("#opt").val());
				}
				
			});');

		return $this->v_utils->v_quickform($this->translator->trans('bcard.actions'), [
			$this->v_utils->v_form_select('opt', ['desc' => $this->translator->trans('bcard.desc'), 'values' => $selectedData]),
		], ['submit' => $this->translator->trans('bcard.generate')]);
	}
}
