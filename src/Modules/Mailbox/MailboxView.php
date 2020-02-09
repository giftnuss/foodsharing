<?php

namespace Foodsharing\Modules\Mailbox;

use Foodsharing\Modules\Core\DBConstants\Mailbox\MailboxFolder;
use Foodsharing\Modules\Core\View;

class MailboxView extends View
{
	public function folder($boxes)
	{
		$children = [];
		$lat_js = '';
		foreach ($boxes as $i => $b) {
			$expand = '';

			if ($i == count($boxes) - 1) {
				$expand = '"expand":true,';
				$lat_js = 'ajreq("loadmails",{mb:' . (int)$b['id'] . ',folder:"inbox",type:"' . $b['type'] . '"});mb_setMailbox(' . (int)$b['id'] . ');
						$("#mbh-folder").val("inbox");$("#mbh-mailbox").val(' . (int)$b['id'] . ');$("#mbh-type").val("' . $b['type'] . '");';
			}

			$children[] = '{title: "' . $b['name'] . '@' . PLATFORM_MAILBOX_HOST . '", isFolder: true, icon:"mailbox.png",' . $expand . '
                    children: [
                        {title: "' . $this->translationHelper->s('inbox') . '",ident:' . $b['id'] . ',folder:"inbox",icon:"inbox.png",type:"' . $b['type'] . '"},
                        {title: "' . $this->translationHelper->s('sent') . '",ident:' . $b['id'] . ',folder:"sent",icon:"sent.png",type:"' . $b['type'] . '"},
                        {title: "' . $this->translationHelper->s('trash') . '",ident:' . $b['id'] . ',folder:"trash",icon:"trash.png",type:"' . $b['type'] . '"}
                    ]
                }';
		}

		$this->pageHelper->addJs('
			$("#mailfolder").dynatree({
            onActivate: function(node) {

				if(node.data.ident != undefined)
				{
					ajreq("loadmails",{mb:node.data.ident,folder:node.data.folder,type:node.data.type});
					mb_setMailbox(node.data.ident);
					$("#mbh-mailbox").val(node.data.ident);
					$("#mbh-folder").val(node.data.folder);
					$("#mbh-type").val(node.data.type);
				}


            },
			imagePath: "/img/icon-mail/",
            persist: false,
            children: [
                ' . implode(',', $children) . '
            ]
        });
         ' . $lat_js . '
		');

		return $this->v_utils->v_field('<div id="mailfolder"></div><input type="hidden" id="mbh-mailbox" value="" /><input type="hidden" id="mbh-folder" value="" /><input type="hidden" id="mbh-type" value="" />', 'Mailboxen');
	}

	public function manageMemberBox($box)
	{
		return $this->v_utils->v_quickform($box['name'] . '@' . PLATFORM_MAILBOX_HOST, [
			$this->v_utils->v_form_tagselect('foodsaver_' . $box['id'], ['label' => $this->translationHelper->s('mailbox_member'), 'xhr' => 'Recip']),
			$this->v_utils->v_input_wrapper($this->translationHelper->s('email_name'), '<input type="text" value="' . $box['email_name'] . '" name="email_name" class="input text value">'),
			$this->v_utils->v_form_hidden('mbid', $box['id'])
		], ['submit' => $this->translationHelper->s('save')]);
	}

	public function mailboxform()
	{
		return $this->v_utils->v_quickform($this->translationHelper->s('new_mailbox'), [
			$this->v_utils->v_form_text('name', ['desc' => $this->translationHelper->s('mailbox_name_desc')])
		], ['submit' => $this->translationHelper->s('save')]);
	}

	public function manageOpt()
	{
		return $this->v_utils->v_menu([
			['name' => $this->translationHelper->s('new_mailbox'), 'href' => '/?page=mailbox&a=newbox']
		], $this->translationHelper->s('options'));
	}

	public function options()
	{
		return $this->v_utils->v_menu([
			['name' => $this->translationHelper->s('refresh'), 'click' => 'mb_refresh();return false;'],
			['name' => $this->translationHelper->s('new_message'), 'click' => 'mb_new_message();return false;']
		], $this->translationHelper->s('options'));
	}

	public function noMessage()
	{
		return '
			<tr class="message">
				<td colspan="4" align="center"><div class="ui-padding">' . $this->v_utils->v_info($this->translationHelper->s('no_message')) . '</div></td>
			</tr>
		';
	}

	/**
	 * Converts an array with a mail sender/recipient from the database to a string.
	 */
	private function createMailAddressString(array $mailAddress): string
	{
		if (isset($mailAddress['personal'])) {
			return $mailAddress['personal'];
		} elseif (isset($mailAddress['host'])) {
			return $mailAddress['mailbox'] . '@' . $mailAddress['host'];
		} else {
			return $mailAddress['mailbox'];
		}
	}

	/**
	 * Removes leading and trailing quotation marks and replaces escaped quotation marks.
	 */
	private function fixQuotation(string $json): string
	{
		$trimmed = trim($json, '"');

		return str_replace('\"', '"', $trimmed);
	}

	public function listMessages(array $messages, int $folder, string $currentMailboxName)
	{
		$out = '';

		foreach ($messages as $m) {
			// fix wrong quotation that can occur in some data sets
			$m['sender'] = $this->fixQuotation($m['sender']);
			$m['to'] = $this->fixQuotation($m['to']);

			// create from/to text depending on the folder
			$fromToAddresses = [];
			switch ($folder) {
				case MailboxFolder::FOLDER_INBOX:
					$fromToAddresses = [json_decode($m['sender'], true)];
					break;
				case MailboxFolder::FOLDER_SENT:
					$fromToAddresses = json_decode($m['to'], true);
					break;
				case MailboxFolder::FOLDER_TRASH:
					$from = json_decode($m['sender'], true); // returns null or the input string if parsing fails
					if (!is_null($from) && is_array($from)) {
						if ($this->createMailAddressString($from) == $currentMailboxName) {
							// mail was sent
							$fromToAddresses = json_decode($m['to'], true);
						} else {
							// mail was received
							$fromToAddresses = [$from];
						}
					}
					break;
			}

			// safety check: if json_decode fails it might return null or a string
			if (!is_null($fromToAddresses) && is_array($fromToAddresses)) {
				$mappedAddresses = array_map(function ($a) {
					return $this->createMailAddressString($a);
				}, array_filter($fromToAddresses));

				$fromToText = implode(', ', $mappedAddresses);
			} else {
				$fromToText = '';
			}

			$attach_class = 'none';
			if (!empty($m['attach'])) {
				$attach_class = 'check';
			}

			$status = 'read-0';
			if ($m['answer'] == 1) {
				$status = 'answer-1';
			} elseif ($m['read'] == 1) {
				$status = 'read-1';
			}

			$out .= '
				<tr id="message-' . $m['id'] . '" class="message ' . $status . '">
					<td class="subject"><span class="status ' . $status . '">&nbsp;</span> ' . $m['subject'] . '</td>
					<td class="from"><a href="#" onclick="return false;" title="' . $fromToText . '">' . $fromToText . '</a></td>

					<td class="date">' . $this->timeHelper->niceDateShort($m['time_ts']) . '</td>
					<td class="attachment"><span class="status a-' . $attach_class . '">&nbsp;</span></td>
				</tr>
			';
		}

		return $out;
	}

	public function message($mail)
	{
		$mail['body'] = trim($mail['body']);
		$von = json_decode($mail['sender'], true);

		$von_str = $von['mailbox'] . '@' . $von['host'];
		if (isset($von['personal'])) {
			$von_str = $von['personal'];
		}

		$an = json_decode($mail['to'], true);
		$an_str = [];
		if (is_array($an)) {
			foreach ($an as $a) {
				$an_str[] = $a['mailbox'] . '@' . $a['host'];
			}
		}

		$attach = '';
		if (is_array($mail['attach']) && count($mail['attach']) > 0) {
			$attach = '
				<div id="mailattch">
					<ul class="attach">';
			foreach ($mail['attach'] as $i => $a) {
				$attach .= '<li><a class="ui-corner-all" href="/?page=mailbox&a=dlattach&mid=' . (int)$mail['id'] . '&i=' . (int)$i . '">' . $a['origname'] . '</a></li>';
			}
			$attach .= '</ul></div>';
		}

		if ($mail['time_ts'] > 1391338283) {
			$body = '<iframe sandbox="" style="width:100%;height:100%;border:0;margin:0;padding:0;" frameborder="0" src="/xhrapp.php?app=mailbox&m=fmail&id=' . $mail['id'] . '"></iframe>';
		} else {
			$body = nl2br($mail['body']);
		}

		$fullToString = implode(', ', $an_str);
		$foldButton = '';
		$shortToString = $fullToString;
		if (strlen($fullToString) > 100) {
			$shortToString = substr($fullToString, 0, 100) . ' ...';
			$foldButton = '<a onclick="mb_foldRecipients(\'' . $fullToString . '\', \'' . $shortToString . '\');return false;" href="#"><i class="fas fa-sort-down fa-lg" id="mail-fold-icon"></i></a>';
		}

		return '
			<div class="popbox">
				<div class="message-top">
					<div class="buttonbar">
						<a href="#" onclick="mb_moveto(' . MailboxFolder::FOLDER_TRASH . ');return false;" class="button">' . $this->translationHelper->s('move_to_trash') . '</a> <a href="#" onclick="mb_answer();return false;" class="button">' . $this->translationHelper->s('answer') . '</a>
					</div>
					<table class="header">
						<tr>
							<td class="label">' . $this->translationHelper->s('von') . '</td>
							<td class="data"><a onclick="mb_mailto(\'' . $von['mailbox'] . '@' . $von['host'] . '\');return false;" href="#" title="' . $von['mailbox'] . '@' . $von['host'] . '">' . $von_str . '</a></td>
						</tr>
						<tr>
							<td class="label">' . $this->translationHelper->s('an') . ' ' . $foldButton . '</td>
							<td class="data" id="mail-to-list" data-folded="true">' . $shortToString . '</td>
						</tr>
						<tr>
							<td class="label">' . $this->translationHelper->s('date') . '</td>
							<td class="data">' . $this->timeHelper->niceDate($mail['time_ts']) . '</td>
						</tr>
					</table>
				</div>
				<div class="mailbox-body">
					' . $body . '

				</div>
				<div class="mailbox-body-loader" style="display:none;"></div>
				' . $attach . '
				<input type="hidden" name="mb-hidden-id" id="mb-hidden-id" value="' . $mail['id'] . '" />
				<input type="hidden" name="mb-hidden-subject" id="mb-hidden-subject" value="' . $mail['subject'] . '" />
				<input type="hidden" name="mb-hidden-email" id="mb-hidden-email" value="' . $von['mailbox'] . '@' . $von['host'] . '" />

				<textarea id="mailbox-body-plain" style="display:none;">' . $this->mailAnswer($mail['body'], $mail['time_ts']) . '</textarea>
			</div>';
	}

	private function mailAnswer($plain, $ts, $sign = '')
	{
		return PHP_EOL . PHP_EOL . PHP_EOL .
			'-- ' .
			PHP_EOL .
			'foodsharing.de - verwenden statt verschwenden' .
			PHP_EOL . PHP_EOL . PHP_EOL . PHP_EOL .
			'----------- ' . $this->translationHelper->sv('message_from', date('j.m.Y H:i', $ts)) . ' Uhr -----------' .
			PHP_EOL . PHP_EOL .
			PHP_EOL . '> ' . str_replace(["\r", "\n"], ['', PHP_EOL . '> '], $plain);
	}

	public function folderlist($mailboxes, $mailadresses)
	{
		$this->pageHelper->addJs('
		setAutocompleteAddresses(' . json_encode($mailadresses) . ');
		$("#message-body").dialog({
			autoOpen:false,
			width:980,
			modal:true,
			resizable:false,
			draggable:false,
			open: function (event, ui) {
		    	$("#message-body").css("overflow", "hidden"); //this line does the actual hiding
		  	}
		});');
		$this->pageHelper->addHidden('
		<div id="message-body">

		</div>
		');

		/*
		 * [id] => 1
			[name] => deutschland
			[type] => bot
		 */
		if (count($mailboxes) == 1) {
			$von = $mailboxes[0]['email_name'] . ' (' . $mailboxes[0]['name'] . '@' . PLATFORM_MAILBOX_HOST . ')<input type="hidden" id="h-edit-von" value="' . $mailboxes[0]['id'] . '" />';
		} else {
			$von = '
			<select class="von-select ui-corner-all" id="edit-von">';
			foreach ($mailboxes as $m) {
				$von .= '
				<option class="mb-' . $m['id'] . '" value="' . $m['id'] . '">' . $m['email_name'] . ' (' . $m['name'] . '@' . PLATFORM_MAILBOX_HOST . ')</option>';
			}
			$von .= '
			</select>';
		}
		$this->pageHelper->addJs('
		$("#message-editor").dialog({
			autoOpen:false,
			width:980,
			modal:true,
			resizable:false,
			draggable:false,
			open: function (event, ui) {
		    	$("#message-editor").css("overflow", "hidden");
				$("#message-editor").dialog("option",{
					height: ($( window ).height()-40)
				});
				var height = ($("#message-editor").height()-100);
				if(height > 50)
				{
					$(".edit-body").css({
							"height" : height+"px"
					});
				}
				u_addTypeHead();

		  	}
		});
		$("#etattach").on("change", function(){
			if(this.files[0].size < 1310720)
			{
				$("#etattach-button").button( "option", "disabled", true );
				setTimeout(function(){
					$("#et-file-list").append("<li>"+$("#etattach-info").text()+"</li>");
				},10);
				$(".et-filebox form").trigger("submit");
			}
			else
			{
				pulseError("' . $this->translationHelper->s('file_to_big') . '");
			}
		});
		');

		$this->pageHelper->addHidden('
		<div id="message-editor">
			<div class="popbox">
				<div class="message-top">
					<table class="header">
						<tr>
							<td class="label">' . $this->translationHelper->s('von') . '</td>
							<td class="data">' . $von . '</td>
						</tr>
						<tr>
							<td class="label">' . $this->translationHelper->s('an') . '</td>
							<td class="data"><input type="text" name="an[]" class="edit-an" value="" /></td>
						</tr>
						<tr id="mail-subject">
							<td class="label">' . $this->translationHelper->s('subject') . '</td>
							<td class="data"><input class="data ui-corner-all" type="text" name="subject" id="edit-subject" value="" /></td>
						</tr>
					</table>
				</div>
				<table class="edit-table">
					<tr>
						<td class="et-left"><textarea class="edit-body" id="edit-body"></textarea></td>
						<td class="et-right">
						<div class="buttonbar">
						<a href="#" onclick="mb_send_message();return false;" class="button">' . $this->translationHelper->s('send') . '</a> <a onclick="$(\'#message-editor\').dialog(\'close\');return false;" href="#" class="button">' . $this->translationHelper->s('abort') . '</a>
					</div>
								<div class="wrapper">
									<div class="et-filebox">
										<form method="post" target="et-upload" action="/xhrapp.php?app=mailbox&m=attach" enctype="multipart/form-data">
											' . $this->v_utils->v_form_file('et-attach', ['btlabel' => $this->translationHelper->s('attach_file')]) . '
										</form>
									</div>

									<iframe width="1" height="1" frameborder="0" name="et-upload"></iframe>
									<ul id="et-file-list">

									</ul>
								</div>
						</td>
					</tr>
				</table>
				<input type="hidden" name="edit-reply" id="edit-reply" value="0" />
			</div>
		</div>
		');

		return $this->v_utils->v_field('
			<table id="messagelist" class="records-table">
				<thead>
					<tr>
						<td class="subject"><a href="#">Betreff</a></td>
						<td class="from"><a href="#">Von</a></td>
						<td class="date"><a href="#">Datum</a></td>
						<td class="attachment"><span class="attachment">&nbsp;</span></td>
					</tr>
				</thead>
				<tbody>

				</tbody>
			</table>
		', 'E-Mails');
	}
}
