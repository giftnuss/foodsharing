<?php

namespace Foodsharing\Modules\BusinessCard;

use Foodsharing\Modules\Core\View;

class BusinessCardView extends View
{
	public function top()
	{
		return $this->topbar('Deine foodsharing Visitenkarte', 'Hier easy generieren, ausdrucken und ausschneiden...', '<img src="/img/bcard.png" />');
	}

	public function optionForm($selectedData)
	{
		$this->pageHelper->addJs('
			$("#optionen-form .input-wrapper:last").hide();
			
			$("#opt").on("change", function(){
				$("#optionen-form").trigger("submit");
			});
				
			$("#optionen-form").on("submit", function(ev){
				ev.preventDefault();
				if($("#opt").val() == "")
				{
					pulseError(\'' . $this->sanitizerService->jsSafe($this->translationHelper->s('should_choose_option')) . '\');
				}
				else
				{
					goTo("/?page=bcard&sub=makeCard&opt=" + $("#opt").val());
				}
				
			});		
		');

		return $this->v_utils->v_quickform($this->translationHelper->s('options'), [
				$this->v_utils->v_form_select('opt', ['desc' => $this->translationHelper->s('opt_desc'), 'values' => $selectedData])
			], ['submit' => 'Visitenkarten erstellen']);
	}
}
