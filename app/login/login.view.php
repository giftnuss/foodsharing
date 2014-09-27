<?php
class LoginView extends View
{
	public function loginForm()
	{
		return 	v_form_text('email_adress').
				v_form_passwd('password') .
				v_form_hidden('ismob', '0') .
				'<p>
					<a id="forgotpasswordlink" href="/?page=login&sub=passwordReset">Passwort vergessen?</a>
				</p>';
							
	}
	
	public function join()
	{
		return '
				
		<div id="joinform">
				<div class="left corner-all">
					<ul class="linklist join">
						<li class="step0 active"><a class="corner-all" href="#" onclick="if(!$(this).parent().hasClass(\'hidden\')){join.step(0);}return false;">'.s('i_am_a').'<i class="fa fa-hand-o-right"></i></a></li>
						<li class="step1 hidden"><a class="corner-all" href="#" onclick="if(!$(this).parent().hasClass(\'hidden\')){join.step(1);}return false;">'.s('login_info').'</a></li>
						<li class="step2 hidden"><a class="corner-all" href="#" onclick="if(!$(this).parent().hasClass(\'hidden\')){join.step(2);}return false;">'.s('contact_info').'</a></li>
						<li class="step3 hidden"><a class="corner-all" href="#" onclick="if(!$(this).parent().hasClass(\'hidden\')){join.step(3);}return false;">'.s('legal_stuff').'</a></li>
					</ul>
					<div class="bottom">
						
					</div>
				</div>
				<div class="right">
					<div class="step step0">
						<div class="content">
							<h3>'.s('i_am_a').'</h3>
							<div class="iamchooser">
								<a onmouseover="$(\'.humanshow\').show();" onmouseout="$(\'.humanshow\').hide();" class="active" href="#human" onclick="$(this).addClass(\'active\').next().removeClass(\'active\');$(\'#join_iam\').val(\'human\');">
									<i class="mega-octicon octicon-person"></i>
									<span>'.s('peer').'</span>
								</a>
								<a onmouseover="$(\'.orgshow\').show();" onmouseout="$(\'.orgshow\').hide();" href="#org" onclick="$(this).addClass(\'active\').prev().removeClass(\'active\');$(\'#join_iam\').val(\'org\');">
									<i class="mega-octicon octicon-home"></i>
									<span>'.s('organisation').'</span>
								</a>
								<br />
								<span class="peer"></span>
							</div>
							<input type="hidden" name="iam" id="join_iam" value="human" />
							
							<div style="display:none;" class="humanshow msg-inside success">
									<i class="fa fa-info-circle"></i> Melde Dich als Foodsharer an um als Privatperson Lebensmittel zu teilen und zu retten
							</div>
							<div style="display:none;" class="orgshow msg-inside success">
									<i class="fa fa-info-circle"></i> Melde Dich als Soziale-Organisation, Hausprojekt oder Sonstiges an um größere Mengen an Lebensmitteln zu Spenden oder zu erhalten
							</div>
						</div>
						<div class="bottom">
							<a class="button" href="" onclick="join.step(1);return false;">'.s('next').'</a>
						</div>
					</div>
					<div style="display:none;" class="step step1">
						<div class="content">
							<h3>'.s('login_info').'</h3>
							<div class="form">
								<div class="element-wrapper">
									<input placeholder="'.s('login_name').'" type="text" value="" id="login_name" name="login_name" class="input text value" />
								</div>
								<div class="element-wrapper">
									<input placeholder="'.s('login_email').'" type="text" value="" id="login_email" name="login_email" class="input text value" />
								</div>
								<div class="element-wrapper">
									<input placeholder="'.s('login_passwd1').'" type="password" value="" id="login_passwd1" name="login_passwd1" class="input text value" />
								</div>
								<div class="element-wrapper">
									<input placeholder="'.s('login_passwd2').'" type="password" value="" id="login_passwd2" name="login_passwd1" class="input text value" />
								</div>
							</div>
							<div class="avatar">
								<a class="container corner-all" href="#">
									<span class="mega-octicon octicon-device-camera"></span>		
								</a><br />
								<a href="#" class="button">'.s('select_picture').'</a>
							</div>
							<div style="clear:both;"></div>
						</div>
						<div class="bottom">
							<a class="button" href="" onclick="join.step(0);return false;">'.s('prev').'</a> <a class="button" href="" onclick="join.step(2);return false;">'.s('next').'</a>
						</div>
					</div>
					<div style="display:none;" class="step step2">
						<div class="content">
							<h3>'.s('i_am_a').'</h3>
							<div class="element-wrapper">
								<input placeholder="'.s('login_phone').'" type="text" value="" id="login_phone" name="login_phone" class="input text value" />
								'.v_info(s('login_phone_info')).'
							</div>
							<div class="element-wrapper">
								<input placeholder="'.s('login_location').'" type="text" value="" id="login_location" name="login_location" class="input text value" />
								<div class="corner-all" id="join_mapview"></div>
								<form class="join_geo_data" style="display:none;">
									<input type="hidden" name="lat" id="join_lat" value="" />
									<input type="hidden" name="lng" id="join_lon" value="" />
									<input type="hidden" name="route" id="join_str" value="" />
									<input type="hidden" name="street_number" id="join_jsnr" value="" />
									<input type="hidden" name="postal_code" id="join_plz" value="" />
									<input type="hidden" name="locality" id="join_ort" value="" />
									<input type="hidden" name="country_short" id="join_country" value="" />
								</form>
							</div>
						</div>
						<div class="bottom">
							<a class="button" href="" onclick="join.step(1);return false;">'.s('prev').'</a> <a class="button" href="" onclick="join.step(3);return false;">'.s('next').'</a>
						</div>
					</div>
					<div style="display:none;" class="step step3">
						<div class="content">
							'.v_input_wrapper('Datenschutzerklärung', '<textarea readonly="readonly">Bla bla bla</textarea>').'	
							'.v_input_wrapper('Rechtsvereinbarung', '<textarea readonly="readonly">Bli bla blubb</textarea>').'	
							<label><input type="checkbox" name="join_legal" value="1" /> '.s('have_read_the_legal_stuff').'</label>
						</div>
						<div class="bottom">
							<a class="button" href="" onclick="join.step(2);return false;">'.s('prev').'</a> <a class="button" href="" onclick="join.finish();return false;">'.s('finish').'</a>
						</div>
					</div>
				</div>
				<div style="clear:both;"></div>
		</div>';
	}
}