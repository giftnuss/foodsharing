<?php

namespace Foodsharing\Modules\Event;

use Foodsharing\Modules\Core\DBConstants\Region\Type;
use Foodsharing\Modules\Core\View;

class EventView extends View
{
	public function eventForm($bezirke)
	{
		global $g_data;

		$g_data = array_merge([
			'online_type' => 1,
			'invite' => 1,
			'invitesubs' => 1
		], $g_data);

		$start_time = ['hour' => 15, 'min' => 0];
		$end_time = ['hour' => 16, 'min' => 0];

		if (isset($g_data['start'])) {
			$start_time = ['hour' => (int)date('H', $g_data['start_ts']), 'min' => (int)date('i', $g_data['start_ts'])];
			$g_data['date'] = $g_data['start'];
		}

		if (isset($g_data['end'])) {
			$end_time = ['hour' => (int)date('H', $g_data['end_ts']), 'min' => (int)date('i', $g_data['end_ts'])];
			$g_data['dateend'] = $g_data['end'];
		}

		$title = $this->translationHelper->s('new_event');
		$this->pageHelper->addStyle('
			label.addend{
				display:inline-block;
				margin-left:15px;
				cursor:pointer;
			}
		');
		$this->pageHelper->addJs('
			$("#online_type").on("change", function(){
				if($(this).val() == 0)
				{
					$("#location_name-wrapper").removeClass("required");
					$("#anschrift-wrapper").removeClass("required");
					$("#plz-wrapper").removeClass("required");
					$("#ort-wrapper").removeClass("required");
					$("#location_name-wrapper").next().hide();
					$("#location_name-wrapper, #anschrift-wrapper, #plz-wrapper, #ort-wrapper").hide();
				}
				else
				{
					$("#location_name-wrapper").addClass("required");
					$("#anschrift-wrapper").addClass("required");
					$("#plz-wrapper").addClass("required");
					$("#ort-wrapper").addClass("required");
					$("#location_name-wrapper").next().show();
					$("#location_name-wrapper, #anschrift-wrapper, #plz-wrapper, #ort-wrapper").show();
				}
			});
			
			var dateend_wrapper = document.getElementById("dateend-wrapper");	
			
			$("#date").after(\'<label class="addend"><input type="checkbox" name="addend" id="addend" value="1" /> Das Event geht über mehrere Tage</label>\');
               
            dateend_wrapper.style.display = "none";

            var dateend = new Date(document.getElementById("dateend").value.split(" ")[0]);
			var datestart = new Date(document.getElementById("date").value.split(" ")[0]);
			datestart.setDate(datestart.getDate() + 1);
			
   			if(dateend >= datestart)
			{
                document.getElementById("addend").checked = true;
                dateend_wrapper.style.display = "block";
			}
			
			dateend_wrapper.classList.remove("required");
			
			$("#addend").on("change", function(){
				if($("#addend:checked").length > 0)
				{
					dateend_wrapper.style.display = "block";
					dateend_wrapper.classList.add("required");
				}
				else
				{
					dateend_wrapper.style.display = "none";
					dateend_wrapper.classList.remove("required");
				}
			});
	
			');

		$bez = '';
		$ag = '';
		$sid = 0;
		if (isset($g_data['bezirk_id'])) {
			$sid = (int)$g_data['bezirk_id'];
		} elseif (isset($_GET['bid'])) {
			$sid = (int)$_GET['bid'];
		}
		if (is_array($bezirke)) {
			foreach ($bezirke as $b) {
				$sel = '';

				if ($b['id'] == $sid) {
					$sel = ' selected="selected"';
				}

				if ($b['type'] == Type::WORKING_GROUP) {
					$ag .= '<option value="' . $b['id'] . '"' . $sel . '>' . $b['name'] . '</option>';
				} else {
					$bez .= '<option value="' . $b['id'] . '"' . $sel . '>' . $b['name'] . '</option>';
				}
			}
		}

		if (!empty($ag)) {
			$ag = '<optgroup label="Deine Arbeitsgruppen">' . $ag . '</optgroup>';
		}
		if (!empty($bez)) {
			$bez = '<optgroup label="Deine Bezirke">' . $bez . '</optgroup>';
		}

		$this->pageHelper->addJs('
			$("#public").on("change", function(){
				if($("#public:checked").length > 0)
				{
					$("#input-wrapper").hide();
				}
				else
				{
					$("#input-wrapper").show();
				}
			});		
		');

		$delinvites = '';
		if (isset($_GET['id'])) {
			$delinvites = '<br /><label><input type="checkbox" name="delinvites" id="delinvites" value="1" /> Vorhandene Einladungen löschen?</label>';
		}

		$bezirkchoose = $this->v_utils->v_input_wrapper('Für welchen Bezirk oder welche AG ist das Event?', '
			<select class="input select value" name="bezirk_id" id="bezirk_id">
				' . $ag . '
				' . $bez . '
			</select>
			<p style="padding-top:10px;">
				<label><input type="checkbox" name="invite" id="invite" checked="checked" value="' . $g_data['invite'] . '" /> Alle Foodsaver aus der Gruppe/dem Bezirk zum Termin einladen?</label><br />
				<label><input type="checkbox" name="invitesubs" id="invitesubs" checked="checked" value="' . $g_data['invitesubs'] . '" /> Alle untergeordneten Gruppen/Bezirke einschließen?</label>
				' . $delinvites . '
			</p>
		');

		$public_el = '';

		if ($this->session->isOrgaTeam()) {
			$chk = '';
			if (isset($g_data['public']) && $g_data['public'] == 1) {
				$chk = ' checked="checked"';
				$this->pageHelper->addJs('$("#input-wrapper").hide();');
			}
			$public_el = $this->v_utils->v_input_wrapper('Ist die Veranstaltung öffentlich?', '<label><input id="public" type="checkbox" name="public" value="1"' . $chk . ' /> Ja die Veranstaltung ist Öffentlich</label>');
		}

		foreach (['anschrift', 'plz', 'ort', 'lat', 'lon'] as $i) {
			if (isset($g_data[$i])) {
				$latLonOptions[$i] = $g_data[$i];
			} else {
				$latLonOptions[$i] = '';
			}
		}
		if (isset($g_data['lat'], $g_data['lon'])) {
			$latLonOptions['location'] = ['lat' => $g_data['lat'], 'lon' => $g_data['lon']];
		} else {
			$latLonOptions['location'] = ['lat' => 0, 'lon' => 0];
		}

		return $this->v_utils->v_field($this->v_utils->v_form('eventsss', [
			$public_el,
			$bezirkchoose,
			$this->v_utils->v_form_text('name', ['required' => true]),
			$this->v_utils->v_form_date('date', ['required' => true]),
			$this->v_utils->v_form_date('dateend', ['required' => true]),
			$this->v_utils->v_input_wrapper('Uhrzeit Beginn', $this->v_utils->v_form_time('time_start', $start_time)),
			$this->v_utils->v_input_wrapper('Uhrzeit Ende', $this->v_utils->v_form_time('time_end', $end_time)),
			$this->v_utils->v_form_textarea('description', ['desc' => $this->translationHelper->s('desc_desc'), 'required' => true]),
			$this->v_utils->v_form_select('online_type', ['values' => [
				['id' => 1, 'name' => $this->translationHelper->s('offline')],
				['id' => 0, 'name' => $this->translationHelper->s('online')]
			]]),
			$this->v_utils->v_form_text('location_name', ['required' => true]),
			$this->latLonPicker('latLng', $latLonOptions),
			$this->v_utils->v_info($this->translationHelper->s('saveEventInfo'))
		], ['submit' => $this->translationHelper->s('save')]), $title, ['class' => 'ui-padding']);
	}

	public function statusMenu(array $event, int $user_status): string
	{
		$menu = [];

		if ($event['fs_id'] == $this->session->id() || $this->session->isOrgaTeam()) {
			$menu[] = [
				'name' => 'Event bearbeiten',
				'href' => '/?page=event&sub=edit&id=' . (int)$event['id']
			];
		}

		if ($user_status !== -1) {
			if ($user_status !== InvitationStatus::WONT_JOIN) {
				$menu[] = [
					'name' => 'Ich kann doch nicht',
					'click' => 'ajreq(\'ustat\',{id:' . (int)$event['id'] . ',s:3});return false;'
				];
			}

			if ($user_status === InvitationStatus::INVITED) {
				$menu[] = [
					'name' => 'Einladung annehmen',
					'click' => 'ajreq(\'ustat\',{id:' . (int)$event['id'] . ',s:1});return false;'
				];
			}

			if ($user_status !== InvitationStatus::INVITED && $user_status !== InvitationStatus::ACCEPTED) {
				$menu[] = [
					'name' => 'Ich kann doch',
					'click' => 'ajreq(\'ustat\',{id:' . (int)$event['id'] . ',s:1});return false;'
				];
			}

			if ($user_status !== InvitationStatus::MAYBE) {
				$menu[] = [
					'name' => 'Ich kann vielleicht',
					'click' => 'ajreq(\'ustat\',{id:' . (int)$event['id'] . ',s:2});return false;'
				];
			}
		} else {
			$menu[] = [
				'name' => 'Ich werde teilnehmen',
				'click' => 'ajreq(\'ustatadd\',{id:' . (int)$event['id'] . ',s:1});return false;'
			];
			$menu[] = [
				'name' => 'Ich werde vielleicht teilnehmen',
				'click' => 'ajreq(\'ustatadd\',{id:' . (int)$event['id'] . ',s:2});return false;'
			];
		}

		return $this->v_utils->v_field($this->menu($menu), $this->translationHelper->s('event_options'), [], 'fas fa-cog');
	}

	public function eventTop(array $event): string
	{
		if (date('Y-m-d', $event['start_ts']) != date('Y-m-d', $event['end_ts'])) {
			$end = ' ' . $this->translationHelper->s('to') . ' ' . $this->timeHelper->niceDate($event['end_ts']);
		} else {
			$end = ' ' . $this->translationHelper->s('to') . ' ' . $this->ts_time($event['end_ts']);
		}

		return '
		<div class="event welcome ui-padding margin-bottom ui-corner-all">
			<div class="welcome_profile_image">
				<span class="calendar">
					<span class="month">' . $this->translationHelper->s('month_' . (int)date('m', $event['start_ts'])) . '</span>
					<span class="day">' . date('d', $event['start_ts']) . '</span>
				</span>
				<div class="clear"></div>
			</div>
			<div class="welcome_profile_name">
				<div class="user_display_name">
					' . $event['name'] . '
				</div>
				<div class="welcome_quick_link">
					<ul>
						<li>' . $this->timeHelper->niceDate($event['start_ts']) . $end . '</li>
					</ul>
					<div class="clear"></div>
				</div>
			</div>
			<div class="clear"></div>
		</div>';
	}

	private function ts_time($ts): string
	{
		return date('H:i', $ts) . ' Uhr';
	}

	public function invites(array $invites): string
	{
		$out = '';

		if (!empty($invites['accepted'])) {
			$avatars = $this->placeFsAvatars($invites['accepted'], 60);
			$out .= $this->v_utils->v_field($avatars, '' . count($invites['accepted']) . ' sind dabei');
		}

		if (!empty($invites['maybe'])) {
			$avatars = $this->placeFsAvatars($invites['maybe'], 54);
			$out .= $this->v_utils->v_field($avatars, '' . count($invites['maybe']) . ' kommen vielleicht');
		}

		if (!empty($invites['invited'])) {
			$avatars = $this->placeFsAvatars($invites['invited'], 54);
			$out .= $this->v_utils->v_field($avatars, '' . count($invites['invited']) . ' Einladungen');
		}

		return $out;
	}

	private function placeFsAvatars(array $foodsavers, int $maxNumberOfAvatars): string
	{
		if (!empty($foodsavers)) {
			$out = '<ul class="fsicons">';

			if (count($foodsavers) > $maxNumberOfAvatars) {
				shuffle($foodsavers);
				$foodsaverDisplayed = array_slice($foodsavers, 0, $maxNumberOfAvatars);
			} else {
				$foodsaverDisplayed = $foodsavers;
			}

			foreach ($foodsaverDisplayed as $fs) {
				$out .= '
				<li>
					<a title="' . $fs['name'] . '" style="background-image:url(' . $this->imageService->img($fs['photo']) . ');" href="/profile/' . (int)$fs['id'] . '"><span></span></a>	
				</li>';
			}
			if (count($foodsavers) > $maxNumberOfAvatars) {
				$out .= '<li class="row">...und ' . (count($foodsavers) - $maxNumberOfAvatars) . ' weitere</li></ul>';
			}

			return $out;
		}

		return '';
	}

	public function event(array $event): string
	{
		return $this->v_utils->v_field(
			'<p>' . nl2br($this->routeHelper->autolink($event['description'])) . '</p>', 'Beschreibung', ['class' => 'ui-padding']);
	}
}
