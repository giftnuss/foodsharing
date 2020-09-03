<?php

namespace Foodsharing\Modules\Message;

use Foodsharing\Modules\Core\View;

final class MessageView extends View
{
	public function leftMenu(): string
	{
		return $this->menu([
			[
				'name' => $this->translator->trans('chat.new_message'),
				'click' => 'msg.compose(); return false;',
			]
		]);
	}

	private function peopleChooser($id, $option = [])
	{
		$this->pageHelper->addJs('
			var date = new Date();
			tstring = ""+date.getYear() + ""+date.getMonth() + ""+date.getDate() + ""+date.getHours();
			var localsource = [];
			$.ajax({
				url: "/api/search/legacyindex",
				dataType: "json",
				success: function (json) {
					if (json.length > 0 && json[0] != undefined && json[0].key == "buddies") {
						for (y = 0; y < json[0].result.length; y++) {
							localsource.push({
								id: json[0].result[y].id,
								value:`${json[0].result[y].name} (${json[0].result[y].id})`
							});
						}
					}
				},
				complete: function () {
					$("#' . $id . ' input.tag").tagedit({
						allowEdit: false,
						allowAdd: false,
						animSpeed: 1,
						autocompleteOptions: {
							delay: 300,
							minLength: 1,
							source: function (request, response) {
					            /* Remote results only if string > 3: */
								if (request.term.length > 3) {
									$.ajax({
						                url: "/api/search/user",
										data: {q: request.term},
						                dataType: "json",
						                success: function (data) {
											response(data);
						                }
						            });
								} else {
									response(localsource);
								}
					        }
						}
					});
				}
			});
			var localsource = [];
		');

		$input = '<input type="text" name="' . $id . '[]" value="" class="tag input text value" />';

		return $this->v_utils->v_input_wrapper($this->translationHelper->s($id), '<div id="' . $id . '">' . $input . '</div>', $id, $option);
	}

	public function compose(): string
	{
		$content = $this->peopleChooser('compose_recipients');

		$content .= $this->v_utils->v_form_textarea('compose_body');

		$content .= $this->v_utils->v_input_wrapper(false,
			'<a class="button" id="compose_submit" href="#">' . $this->translator->trans('button.send') . '</a>');

		return '<div id="compose">' . $this->v_utils->v_field(
			$content,
			$this->translator->trans('chat.new_message'),
			['class' => 'ui-padding']
		) . '</div>';
	}

	/**
	 * @param Conversation[] $conversations
	 */
	public function conversationList(array $conversations, array $profiles): string
	{
		if (empty($conversations)) {
			return '<li class="noconv">'
				. $this->v_utils->v_info($this->translator->trans('chat.empty'))
				. '</li>';
		}

		$list = '';

		foreach ($conversations as $c) {
			if (!$c->lastMessage) {
				// only show conversations with a message
				continue;
			}
			if (empty($c->members)) {
				continue;
			}

			$pics = '';
			$title = '';

			$pictureWidth = 50;
			$size = 'med';

			if (count($c->members) > 2) {
				$pictureWidth = 25;
				$size = 'mini';
			}

			foreach ($c->members as $m) {
				if ($m == $this->session->id()) {
					continue;
				}
				$pics .= '<img src="' . $this->imageService->img($profiles[$m]->avatar, $size) . '" width="' . $pictureWidth . '" />';
				$title .= ', ' . $profiles[$m]->name;
			}

			$title = $c->title ?? substr($title, 2);
			$msg = $this->sanitizerService->plainToHtml($c->lastMessage->body);
			$time = $this->timeHelper->niceDate($c->lastMessage->sentAt->getTimestamp());

			$list .= '<li id="convlist-' . $c->id . '" class="unread-' . intval($c->hasUnreadMessages) . '">'
				. '<a href="#" onclick="msg.loadConversation(' . $c->id . '); return false;">'
				. '<span class="pics">' . $pics . '</span>'
				. '<span class="names">' . $this->sanitizerService->plainToHtml($title) . '</span>'
				. '<span class="msg">' . $msg . '</span>'
				. '<span class="time">' . $time . '</span>'
				. '<span class="clear"></span>'
				. '</a></li>';
		}

		return $list;
	}

	public function conversationListWrapper(string $list): string
	{
		return $this->v_utils->v_field(
			'<div id="conversation-list"><ul class="linklist conversation-list">' . $list . '</ul></div>',
			$this->translator->trans('chat.conversations'),
			[],
			'fas fa-comments'
		);
	}

	public function conversation(): string
	{
		$out = '<div id="msg-conversation" class="corner-all">
			<ul></ul>
			<div class="loader" style="display:none;">
				<i class="fas fa-sync fa-spin"></i>
			</div>
		</div>';

		$out .= '<div id="msg-control">
			<form>'
			. $this->v_utils->v_form_textarea('msg_answer', [
				'style' => 'width: 88%;',
				'nolabel' => true,
				'placeholder' => $this->translator->trans('chat.placeholder'),
			]) . '<input id="conv_submit" type="submit" class="button" name="submit" value="&#xf0a9;" />
			</form>
		</div>';

		return '<div id="msg-conversation-wrapper" style="display: none;">'
			. $this->v_utils->v_field($out, '', ['class' => 'ui-padding'], 'fas fa-comment', 'msg-conversation-title')
			. '</div>';
	}
}
