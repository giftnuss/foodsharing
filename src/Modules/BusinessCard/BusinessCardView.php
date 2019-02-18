<?php

namespace Foodsharing\Modules\BusinessCard;

use Foodsharing\Modules\Core\View;

class BusinessCardView extends View
{
	public function top()
	{
		return $this->topbar('Deine foodsharing Visitenkarte', 'hier easy generieren, ausdrucken und ausschneiden...', '<img src="/img/bcard.png" />');
	}

	public function optionform($seldata)
	{
		$this->func->addJs('
			$("#optionen-form .input-wrapper:last").hide();
			
			$("#opt").on("change", function(){
				$("#optionen-form").trigger("submit");
			});
				
			$("#optionen-form").on("submit", function(ev){
				ev.preventDefault();
				if($("#opt").val() == "")
				{
					pulseError(\'' . $this->sanitizerService->jsSafe($this->func->s('should_choose_option')) . '\');
				}
				else
				{
					ajreq("makeCard",{opt:$("#opt").val()});
				}
				
			});		
		');

		return $this->v_utils->v_quickform($this->func->s('options'), array(
				$this->v_utils->v_form_select('opt', array('desc' => $this->func->s('opt_desc'), 'values' => $seldata))
			), array('submit' => 'Visitenkarten erstellen')) . '
				
		<div class="input-wrapper" id="dlbox" style="display:none;">
			<a href="#" target="_blank" class="button">' . $this->func->s('download_card') . '</a>		
		</div>';
	}
}
