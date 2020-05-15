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

	private function peopleChooser($id, $option = [])
	{
		$this->pageHelper->addJs('
			var date = new Date();
			tstring = ""+date.getYear() + ""+date.getMonth() + ""+date.getDate() + ""+date.getHours();
			var localsource = [];
			$.ajax({
				url: "/api/search/legacyindex",
				dataType: "json",
				success: function(json){

					if(json.length > 0 && json[0] != undefined && json[0].key != undefined && json[0].key == "buddies")
					{

						for(y=0;y<json[0].result.length;y++)
						{
							localsource.push({id:json[0].result[y].id,value:`${json[0].result[y].name} (${json[0].result[y].id})`});
						}

					}
				},
				complete: function(){
					$("#' . $id . ' input.tag").tagedit({
						autocompleteOptions: {
							delay: 300,
							source: function(request, response) {
					            /* Remote results only if string > 3: */

								if(request.term.length > 3)
								{
									$.ajax({
						                url: "/api/search/user",
										data: {q:request.term},
						                dataType: "json",
						                success: function(data) {
											response(data);
											// following doesn\'t work somehow => ignoring
											// local = [];
											// term = request.term.toLowerCase();
											// for(i=0;i<localsource.length;i++)
											// {
											// 	if(localsource[i].value.indexOf(term) > 0)
											// 	{
											// 		local.push(localsource[i]);
											// 	}
											// }
											// response(merge(local,data,"id"));
						                }
						            });
								}
								else
								{
									response(localsource);
								}

					        },
							minLength: 1
						},
						allowEdit: false,
						allowAdd: false,
						animSpeed:1
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

		$content .= $this->v_utils->v_input_wrapper(false, '<a class="button" id="compose_submit" href="#">' . $this->translationHelper->s('send') . '</a>');

		return '<div id="compose">' . $this->v_utils->v_field($content, $this->translationHelper->s('new_message'), ['class' => 'ui-padding']) . '</div>';
	}

	public function conversationList(array $conversations, array $profiles): string
	{
		$list = '';

		if (!empty($conversations)) {
			foreach ($conversations as $c) {
				if (!$c->lastMessage) {
					/* only show conversations with a message */
					continue;
				}
				$pics = '';
				$title = '';

				if (!empty($c->members)) {
					$pictureWidth = 50;
					$size = 'med';

					if (count($c->members) > 2) {
						$pictureWidth = 25;
						$size = 'mini';
//						shuffle($c->members);
					}

					foreach ($c->members as $m) {
						if ($m == $this->session->id()) {
							continue;
						}
						$pics .= '<img src="' . $this->imageService->img($profiles[$m]->avatar, $size) . '" width="' . $pictureWidth . '" />';
						$title .= ', ' . $profiles[$m]->name;
					}

					if ($c->title === null) {
						$title = substr($title, 2);
					} else {
						$title = $c->title;
					}

					$list .= '<li id="convlist-' . $c->id . '" class="unread-' . (int)$c->hasUnreadMessages . '"><a href="#" onclick="msg.loadConversation(' . $c->id . ');return false;"><span class="pics">' . $pics . '</span><span class="names">' . $this->sanitizerService->plainToHtml($title) . '</span><span class="msg">' . $this->sanitizerService->plainToHtml($c->lastMessage->body) . '</span><span class="time">' . $this->timeHelper->niceDate($c->lastMessage->sentAt->getTimestamp()) . '</span><span class="clear"></span></a></li>';
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
