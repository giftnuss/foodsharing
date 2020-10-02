<?php

namespace Foodsharing\Modules\Email;

use Foodsharing\Modules\Core\View;

class EmailView extends View
{
	public function v_form_recip_chooser(bool $allOptions): string
	{
		$id = 'recip_choose';

		$out = '<select class="select" name="' . $id . '" id="' . $id . '">';
		if ($allOptions) {
			$out .= '
			<option value="all">' . $this->translator->trans('recipients.all') . '</option>
			<option value="newsletter">' . $this->translator->trans('recipients.newsletter') . '</option>
			<option value="newsletter_all">' . $this->translator->trans('recipients.newsletter_all') . '</option>';
		}

		// Always include these two:
		$out .= '
			<option value="orgateam">' . $this->translator->trans('recipients.orgateam') . '</option>
			<option value="botschafter">' . $this->translator->trans('recipients.botschafter') . '</option>
		';

		if ($allOptions) {
			$out .= '
			<option value="storemanagers">' . $this->translator->trans('recipients.storemanagers') . '</option>
			<option value="storemanagers_and_ambs">' . $this->translator->trans('recipients.storemanagers_and_ambs') . '</option>
			<option value="all_no_botschafter">' . $this->translator->trans('recipients.all_no_botschafter') . '</option>
			<option value="newsletter_only_foodsharer">' . $this->translator->trans('recipients.newsletter_only_foodsharer') . '</option>
			<option value="choose">' . $this->translator->trans('recipients.choose') . '</option>
			<option value="manual">' . $this->translator->trans('recipients.manual') . '</option>';
		}

		$out .= '</select>';

		if ($allOptions) {
			$out .= '
				<div id="' . $id . '-hidden" style="display: none;"></div>
				<div id="' . $id . 'manual-wrapper" style="display: none;">
					' . $this->v_utils->v_form_textarea($id . 'manual') . '
				</div>
				<div id="' . $id . '-tree-wrapper" style="display: none;">'
					. $this->v_utils->v_info($this->translator->trans('recipients.region-hint'))
					. '<div id="' . $id . '-tree"></div>
				</div>
			';

			$this->pageHelper->addJs('
			$(\'#' . $id . '\').on("change", function () {
				if ($(this).val() == "choose") {
					$("#' . $id . '-tree-wrapper").show();
					$("#' . $id . 'manual-wrapper").hide();
				} else if ($(this).val() == "manual") {
					$("#' . $id . 'manual-wrapper").show();
					$("#' . $id . '-tree-wrapper").hide();
				} else {
					$("#' . $id . 'manual-wrapper").hide();
					$("#' . $id . '-tree-wrapper").hide();
				}
			});

			$("#' . $id . '-tree").dynatree({
			onSelect: function (select, node) {
				$("#' . $id . '-hidden").html("");
				$.map(node.tree.getSelectedNodes(), function (node) {
					$("#' . $id . '-hidden").append(\'<input type="hidden" name="' . $id . '-choose[]" value="\'+node.data.ident+\'" />\');
				});
			},
			persist: false,
			checkbox: true,
			selectMode: 3,
			clickFolderMode: 3,
			activeVisible: true,
			initAjax: {
				url: "/xhr.php?f=bezirkTree",
				data: {p: "0"}
			},
			onLazyRead: function (node) {
				node.appendAjax({
					url: "/xhr.php?f=bezirkTree",
					data: {"p": node.data.ident},
					dataType: "json",
					success: function (node) {},
					error: function (node, XMLHttpRequest, textStatus, errorThrown) {},
					cache: false
				});
			}
		});');
		}

		return $this->v_utils->v_input_wrapper($this->translator->trans('recipients.recipients'), $out);
	}
}
