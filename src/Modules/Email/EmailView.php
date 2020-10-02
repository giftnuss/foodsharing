<?php

namespace Foodsharing\Modules\Email;

use Foodsharing\Modules\Core\View;

class EmailView extends View
{
	public function v_email_compose(array $possibleSenders, string $recipientSelect): string
	{
		$metadata = $recipientSelect
			. $this->v_utils->v_form_select('mailbox_id', ['values' => $possibleSenders, 'required' => true])
			. $this->v_utils->v_form_text('subject', ['required' => true])
			. $this->v_utils->v_form_file('attachement');

		$fields = [
			$this->v_utils->v_field($metadata,
				$this->translator->trans('recipients.bread'),
				['class' => 'ui-padding']
			),
			$this->v_utils->v_field(
				$this->v_utils->v_form_tinymce('message', ['nowrapper' => true, 'type' => 'email']),
				$this->translator->trans('recipients.body')
			),
		];

		return $this->v_utils->v_form(
			$this->translator->trans('recipients.bread'),
			$fields,
			['submit' => $this->translator->trans('recipients.prepare')]
		);
	}

	public function v_email_statusbox(array $recipients, array $mail): string
	{
		$out = '';

		$id = $this->identificationHelper->id('mailtosend');

		$this->pageHelper->addJs('
			$("#' . $id . '-link").fancybox({
				minWidth: 600,
				scrolling: "auto",
				closeClick: false,
				helpers: {
					overlay: {closeClick: false}
				}
			});

			$("#' . $id . '-link").trigger("click");

			$("#' . $id . '-continue").button().on("click", function () {
				' . $id . '_continue_xhr();
				return false;
			});

			$("#' . $id . '-abort").button().on("click", function () {
				showLoader();
				$.ajax({
					url: "/xhr.php?f=abortEmail",
					data: {id:' . (int)$mail['id'] . '},
					complete: function () {
						hideLoader();
						closeBox();
					}
				});
			});'
		);

		$this->pageHelper->addJsFunc('
			function ' . $id . '_continue_xhr () {
				showLoader();
				$.ajax({
					dataType: "json",
					url: "/xhr.php?f=continueMail&id=' . (int)$mail['id'] . '",
					success: function (data) {
						$("#' . $id . '-continue").hide();
						if (data.status == 1) {
							$("#' . $id . '-comment").html(data.comment);
							$("#' . $id . '-left").html(data.left);
							' . $id . '_continue_xhr();
						} else if (data.status == 2) {
							$("#' . $id . '-comment").html(data.comment);
							hideLoader();
						} else {
							alert("' . $this->translator->trans('recipients.permission') . '");
						}
					}
				});
			}'
		);

		$style = '';
		if (count($recipients) > 50) {
			$style = ' style="height: 100px; overflow: auto; font-size: 10px; background-color: #fff; color: #333; padding: 5px;"';
		}

		$this->pageHelper->addHidden('
			<a id="' . $id . '-link" href="#' . $id . '">&nbsp;</a>
			<div class="popbox" id="' . $id . '">
				<h3>' . $this->translator->trans('recipients.send') . '</h3>
				<p class="subtitle">' . $this->translator->trans('recipients.pending', [
					'{count}' => '<span id="' . $id . '-left">' . $mail['anz'] . '</span>',
				]) . '</p>

				<div id="' . $id . '-comment">'
					. $this->v_utils->v_input_wrapper($this->translator->trans('recipients.recipients'), '<div' . $style . '>' . implode(', ', $recipients) . '</div>')
					. $this->v_utils->v_input_wrapper($this->translator->trans('mailbox.subject'), $mail['name'])
					. $this->v_utils->v_input_wrapper($this->translator->trans('recipients.body'), nl2br($mail['message'])) . '

				</div>
				<a id="' . $id . '-continue" href="#">' . $this->translator->trans('recipients.continue') . '</a> '
				. '<a id="' . $id . '-abort" href="#">' . $this->translator->trans('recipients.abort') . '</a>
			</div>'
		);

		return $out;
	}

	public function v_email_test(): string
	{
		$this->pageHelper->addStyle('#testemail {width: 91%;}');
		$button = '<a class="button" href="#" onclick="trySendTestEmail(); return false;">'
				. $this->translator->trans('recipients.testmail')
				. '</a>';

		return $this->v_utils->v_field(
			$this->v_utils->v_form_text('testemail') . $this->v_utils->v_input_wrapper('', $button),
			$this->translator->trans('recipients.test'),
			['class' => 'ui-padding']
		);
	}

	public function v_email_variables(): string
	{
		$this->pageHelper->addJs("$('#rightmenu').menu();");

		return $this->v_utils->v_field(
			'<div class="ui-padding">' . $this->translator->trans('recipients.variable-info') . '</div>',
			$this->translator->trans('recipients.variables')
		);
	}

	public function v_email_history(array $sentMails): string
	{
		$out = '
	<div id="dialog-confirm" title="' . $this->translator->trans('recipients.confirm') . '" style="display: none;">
		<p>
			<span class="ui-icon ui-icon-alert" style="float: left; margin: 0 7px 20px 0;"></span>'
			. $this->translator->trans('recipients.doublecheck') .
		'</p>
	</div>
	<h3 class="head ui-widget-header ui-corner-top">' . $this->translator->trans('recipients.history') . '</h3>
	<div class="ui-widget ui-widget-content ui-corner-bottom margin-bottom">
	<ul id="rightmenu">';
		$i = 0;
		$divs = '';
		foreach ($sentMails as $m) {
			++$i;
			$out .= '
		<li>
			<a href="#" onclick="$(\'#right-' . $i . '\').dialog(\'open\'); return false;">'
				. date('d.m.', strtotime($m['zeit'])) . ' ' . $m['name']
			. '</a>
		</li>';

			$divs .= '<div id="right-' . $i . '" style="display: none;">' . nl2br($m['message']) . '</div>';
			$this->pageHelper->addJs(
				'$("#right-' . $i . '").dialog({autoOpen: false, title: "'
				. $this->sanitizerService->jsSafe($m['name'], '"')
				. '", modal: true});'
			);
		}

		$out .= '</ul></div>' . $divs;

		return $out;
	}

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
