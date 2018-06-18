<?php

namespace Foodsharing\Modules\Login;

use Foodsharing\Lib\Session\S;
use Foodsharing\Modules\Core\View;

class LoginView extends View
{
	public function loginForm()
	{
		return $this->v_utils->v_form_text('email_adress') .
			$this->v_utils->v_form_passwd('password') .
			$this->v_utils->v_form_hidden('ismob', '0') .
			'<p>
					<a id="forgotpasswordlink" href="/?page=login&sub=passwordReset">Passwort vergessen?</a>
				</p>';
	}

	public function join($email = '', $pass = '', $datenschutz, $rechtsvereinbarung)
	{
		return '
		<div id="joinform">
				<div class="left corner-all">
					<ul class="linklist join">
						<li class="step0 active"><a class="corner-all" href="#" onclick="if(!$(this).parent().hasClass(\'hidden\')){join.step(0);}return false;">' . $this->func->s('i_am_a') . '<i class="fa fa-hand-o-right"></i></a></li>
						<li class="step1 hidden"><a class="corner-all" href="#" onclick="if(!$(this).parent().hasClass(\'hidden\')){join.step(1);}return false;">' . $this->func->s('login_info') . '</a></li>
						<li class="step2 hidden"><a class="corner-all" href="#" onclick="if(!$(this).parent().hasClass(\'hidden\')){join.step(2);}return false;">' . $this->func->s('contact_info') . '</a></li>
						<li class="step3 hidden"><a class="corner-all" href="#" onclick="if(!$(this).parent().hasClass(\'hidden\')){join.step(3);}return false;">' . $this->func->s('legal_stuff') . '</a></li>
					</ul>
					<div class="bottom">
					</div>
				</div>
				<div class="right">
					<div class="step step0">
						<div class="content">
							<h3>' . $this->func->s('i_am_a') . '</h3>
							<div class="iamchooser">
								<a onmouseover="$(\'.humanshow\').show();" onmouseout="$(\'.humanshow\').hide();" class="active" href="#human" onclick="$(this).addClass(\'active\').next().removeClass(\'active\');$(\'#join_iam\').val(\'human\');">
									<i class="mega-octicon octicon-person"></i>
									<span>' . $this->func->s('peer') . '</span>
								</a>
								<br />
								<span class="peer"></span>
							</div>
							<input type="hidden" name="iam" id="join_iam" value="human" />
							<div style="display:none;" class="humanshow msg-inside success">
									<i class="fa fa-info-circle"></i>Melde Dich als Foodsharer an, um als Privatperson Lebensmittel zu teilen und zu retten.
							</div>
						</div>
						<div class="bottom">
							<a class="button" href="" onclick="join.step(1);return false;">' . $this->func->s('next') . '</a>
						</div>
					</div>
					<div style="display:none;" class="step step1">
						<div class="content">
							<h3>' . $this->func->s('login_info') . '</h3>
							<div class="form">
								<div class="element-wrapper">
									<input placeholder="' . $this->func->s('login_name') . '" type="text" value="" id="login_name" name="login_name" class="input text value" /><input placeholder="' . $this->func->s('login_surname') . '" type="text" value="" id="login_surname" name="login_surname" class="input text value" />
								</div>
								<div class="element-wrapper">
									<input placeholder="' . $this->func->s('login_email') . '" type="text" value="' . $email . '" id="login_email" name="login_email" class="input text value" />
								</div>
								<div class="element-wrapper">
									<select id="login_gender" name="c[]" class="input select value">
										<option selected="selected" value="0">' . $this->func->s('select_your_gender') . '</option>
										<option value="2">' . $this->func->s('woman') . '</option>
										<option value="1">' . $this->func->s('man') . '</option>
										<option value="0">' . $this->func->s('other') . '</option>
									</select>
								</div>
								<div class="element-wrapper">
									<input placeholder="' . $this->func->s('login_passwd1') . '" type="password" value="' . $pass . '" id="login_passwd1" name="login_passwd1" class="input text value" />
								</div>
								<div class="element-wrapper">
									<input placeholder="' . $this->func->s('login_passwd2') . '" type="password" value="" id="login_passwd2" name="login_passwd1" class="input text value" />
								</div>
								<div class="element-wrapper">
									<input placeholder="yyyy-mm-dd" type="date" id="birthdate" min="' . date('Y-m-d', strtotime('-120 years')) . '" max="' . date('Y-m-d', strtotime('-18 years')) . '" required />
									<label for="birthdate">' . $this->func->s('geb_datum') . '</label>
								</div>
							</div>
							<div class="avatar">
								<form action="/xhrapp.php?app=login&m=photoupload" id="join_photoform" target="join_upload_frame" method="post" enctype="multipart/form-data">
									<input type="hidden" name="action" value="upload" />
									<a onclick="$(\'#join_photo\').trigger(\'click\');return false;" class="container corner-all" href="#">
										<span class="mega-octicon octicon-device-camera"></span>
										<span class="fa fa-circle-o-notch fa-spin"></span>
									</a><br />
									<a onclick="$(\'#join_photo\').trigger(\'click\');return false;" href="#" class="button">' . $this->func->s('select_picture') . '</a><span class="filewrapper"><input onchange="join.startUpload();" type="file" name="photo" id="join_photo" /></span>
								</form>
								<iframe frameborder="0" style="width:10px;height:10px;" name="join_upload_frame" src="/empty.html"></iframe>
								<input type="hidden" name="c[]" value="" id="join_avatar" />
								<input type="hidden" name="c[]" value="0" id="join_avatar_error" />
							</div>
							<div style="clear:both;"></div>
						</div>
						<div class="bottom">
							<a class="button" href="" onclick="join.step(0);return false;">' . $this->func->s('prev') . '</a> <a class="button" href="" onclick="join.step(2);return false;">' . $this->func->s('next') . '</a>
						</div>
					</div>
					<div style="display:none;" class="step step2">
						<div class="content">
							<h3>' . $this->func->s('contact_info') . '</h3>
							<div class="element-wrapper">
								<input placeholder="' . $this->func->s('login_phone') . '" type="text" value="" id="login_phone" name="login_phone" class="input text value" />
								' . $this->v_utils->v_info($this->func->s('login_phone_info')) . '
							</div>
							<div class="element-wrapper">
								<input placeholder="' . $this->func->s('login_location') . '" type="text" value="" id="login_location" name="login_location" class="input text value" />
								<div class="corner-all" id="join_mapview"></div>
								<form class="join_geo_data" style="display:none;">
									<input type="hidden" name="lat" id="join_lat" value="" />
									<input type="hidden" name="lng" id="join_lon" value="" />
									<input type="hidden" name="route" id="join_str" value="" />
									<input type="hidden" name="street_number" id="join_hsnr" value="" />
									<input type="hidden" name="postal_code" id="join_plz" value="" />
									<input type="hidden" name="locality" id="join_ort" value="" />
									<input type="hidden" name="country_short" id="join_country" value="" />
								</form>
							</div>
						</div>
						<div class="bottom">
							<a class="button" href="" onclick="join.step(1);return false;">' . $this->func->s('prev') . '</a> <a class="button" href="" onclick="join.step(3);return false;">' . $this->func->s('next') . '</a>
						</div>
					</div>
					<div style="display:none;" class="step step3">
						<div class="content">
							<h3>' . $this->func->s('legal_stuff') . '</h3>
							<div class="element-wrapper">
								<h4>' . $datenschutz['title'] . '</h4>
								
								<textarea readonly="readonly">' . $datenschutz['body'] . '</textarea>
							</div>
							<div class="element-wrapper">
								<h4>' . $rechtsvereinbarung['title'] . '</h4>
								<a href="https://wiki.foodsharing.de/Rechtsvereinbarung" target="_blank">Hier klicken</a> um unsere <a href="https://wiki.foodsharing.de/Rechtsvereinbarung" target="_blank">Rechtsvereinbarung</a> in einem neuen Fenster zu &ouml;ffen
							</div>
							<label><input id="join_legal1" type="checkbox" name="join_legal1" value="1" /> ' . $this->func->s('have_read_the_legal_stuff1') . '</label><br />
							<label><input id="join_legal2" type="checkbox" name="join_legal2" value="1" /> ' . $this->func->s('have_read_the_legal_stuff2') . '</label><br />
							<label><input id="newsletter" type="checkbox" name="newsletter" value="1" /> Ich möchte ca. 1x im Monat den foodsharing Newsletter erhalten</label>
						</div>
						<div class="bottom">
							<a class="button" href="" onclick="join.step(2);return false;">' . $this->func->s('prev') . '</a> <a class="button" href="" onclick="join.finish();return false;">' . $this->func->s('finish') . '</a>
						</div>
					</div>
				</div>
				<div style="clear:both;"></div>
		</div>
		<div id="joinloader" style="display:none;"><span class="fa fa-circle-o-notch fa-spin"></span></div>
		<div id="joinready" style="display:none">' . $this->v_utils->v_success($this->func->s('check_mail'), $this->func->s('join_success')) . '</div>';
	}

	public function passwordRequest()
	{
		if (!S::may()) {
			$mail = '';
			if (isset($_GET['m']) && $this->func->validEmail($_GET['m'])) {
				$mail = $_GET['m'];
			}

			$cnt = $this->v_utils->v_info('Bitte trage hier Deine E-Mail-Adresse ein, mit welcher Du auf foodsharing.de angemeldet bist!');

			$cnt .= '
			<form name="passReset" method="post" class="contact-form" action="' . $_SERVER['REQUEST_URI'] . '">
					' . $this->v_utils->v_form_text('email', array('value' => $mail)) . '
					' . $this->v_utils->v_form_submit($this->func->s('send'), 'submitted') . '
			</form>';

			return $this->v_utils->v_field($cnt, 'Passwort zurücksetzen', array('class' => 'ui-padding'));
		} else {
			return $this->v_utils->v_field($this->v_utils->v_info('Du bist angemeldet als ' . S::user('name'), 'Du bist angemeldet'), array('class' => 'ui-padding'));
		}
	}

	public function newPasswordForm($key)
	{
		$key = preg_replace('/[^0-9a-zA-Z]/', '', $key);
		$cnt = $this->v_utils->v_info('Jetzt kannst Du Dein Passwort ändern.');
		$cnt .= '
			<form name="newPass" method="post" class="contact-form">
				<input type="hidden" name="k" value="' . $key . '" />
				' . $this->v_utils->v_form_passwd('pass1') . '
				' . $this->v_utils->v_form_passwd('pass2') . '
				' . $this->v_utils->v_form_submit($this->func->s('save'), 'submitted') . '
			</form>';

		return $this->v_utils->v_field($cnt, 'Neues Passwort setzen', array('class' => 'ui-padding'));
	}
}
