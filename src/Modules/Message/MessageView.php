<?php

namespace Foodsharing\Modules\Message;

use Foodsharing\Modules\Core\View;

final class MessageView extends View
{
	public function leftMenu(): string
	{
		return $this->menu([
			['name' => $this->translationHelper->s('new_message'), 'click' => 'msg.compose();return false;']
		]);
	}

	public function compose(): string
	{
		$content = $this->peopleChooser('compose_recipients');

		$content .= $this->v_utils->v_form_textarea('compose_body');

		$content .= $this->v_utils->v_input_wrapper(false, '<a class="button" id="compose_submit" href="#">' . $this->translationHelper->s('send') . '</a>');

		return '<div id="compose">' . $this->v_utils->v_field($content, $this->translationHelper->s('new_message'), ['class' => 'ui-padding']) . '</div>';
	}

	public function conversationList(array $conversations): string
	{
		$list = '';

		if (!empty($conversations)) {
			foreach ($conversations as $c) {
				$pics = '';
				$title = '';

				if (!empty($c['member'])) {
					$pictureWidth = 50;
					$size = 'med';

					if (count($c['member']) > 2) {
						$pictureWidth = 25;
						$size = 'mini';
						shuffle($c['member']);
					}

					foreach ($c['member'] as $m) {
						if ($m['id'] == $this->session->id()) {
							continue;
						}
						$pics .= '<img src="' . $this->imageService->img($m['photo'], $size) . '" width="' . $pictureWidth . '" />';
						$title .= ', ' . $m['name'];
					}

					if ($c['name'] === null) {
						$title = substr($title, 2);
					} else {
						$title = $c['name'];
					}

					$list .= '<li id="convlist-' . $c['id'] . '" class="unread-' . (int)$c['unread'] . '"><a href="#" onclick="msg.loadConversation(' . $c['id'] . ');return false;"><span class="pics">' . $pics . '</span><span class="names">' . $title . '</span><span class="msg">' . $c['last_message'] . '</span><span class="time">' . $this->timeHelper->niceDate($c['last_ts']) . '</span><span class="clear"></span></a></li>';
				}
			}
		} else {
			$list = '<li class="noconv">' . $this->v_utils->v_info($this->translationHelper->s('no_conversations')) . '</li>';
		}

		return $list;
	}

	public function conversationListWrapper(string $list): string
	{
		return $this->v_utils->v_field('<div id="conversation-list"><ul class="linklist conversation-list">' . $list . '</ul></div>', $this->translationHelper->s('conversations'), [], 'fas fa-comments');
	}

	public function conversation(): string
	{
		$out = '
			<div id="msg-conversation" class="corner-all"><ul></ul><div class="loader" style="display:none;"><i class="fas fa-sync fa-spin"></i></div></div>
		';

		$out .= '
			<div id="msg-control">
				<form>
					' . $this->v_utils->v_form_textarea('msg_answer', ['style' => 'width: 88%;', 'nolabel' => true, 'placeholder' => $this->translationHelper->s('write_something')]) . '<input id="conv_submit" type="submit" class="button" name="submit" value="&#xf0a9;" />
				</form>
			</div>';

		return '<div id="msg-conversation-wrapper" style="display:none;">' . $this->v_utils->v_field($out, '', ['class' => 'ui-padding'], 'fas fa-comment', 'msg-conversation-title') . '</div>';
	}
}
