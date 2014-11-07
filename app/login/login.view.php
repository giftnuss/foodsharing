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
	
	public function join($email='',$pass='')
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
									<input placeholder="'.s('login_name').'" type="text" value="" id="login_name" name="login_name" class="input text value" /><input placeholder="'.s('login_surname').'" type="text" value="" id="login_surname" name="login_surname" class="input text value" />
								</div>
								<div class="element-wrapper">
									<input placeholder="'.s('login_email').'" type="text" value="'.$email.'" id="login_email" name="login_email" class="input text value" />
								</div>
								<div class="element-wrapper">
									<select id="login_gender" name="c[]" class="input select value">
										<option selected="selected" value="0">'.s('select_your_gender').'</option>
										<option value="2">'.s('woman').'</option>
										<option value="1">'.s('man').'</option>
										<option value="0">'.s('other').'</option>
									</select>
								</div>
								<div class="element-wrapper">
									<input placeholder="'.s('login_passwd1').'" type="password" value="'.$pass.'" id="login_passwd1" name="login_passwd1" class="input text value" />
								</div>
								<div class="element-wrapper">
									<input placeholder="'.s('login_passwd2').'" type="password" value="" id="login_passwd2" name="login_passwd1" class="input text value" />
								</div>
								
							</div>
							<div class="avatar">
								<form action="/xhrapp.php?app=login&m=photoupload" id="join_photoform" target="join_upload_frame" method="post" enctype="multipart/form-data">
									<input type="hidden" name="action" value="upload" />
									<a onclick="$(\'#join_photo\').trigger(\'click\');return false;" class="container corner-all" href="#">
										<span class="mega-octicon octicon-device-camera"></span>
										<span class="fa fa-circle-o-notch fa-spin"></span>		
									</a><br />
									<a onclick="$(\'#join_photo\').trigger(\'click\');return false;" href="#" class="button">'.s('select_picture').'</a><span class="filewrapper"><input onchange="join.startUpload();" type="file" name="photo" id="join_photo" /></span>
								</form>
								<iframe frameborder="0" style="width:10px;height:10px;" name="join_upload_frame" src="/empty.html"></iframe>
								<input type="hidden" name="c[]" value="" id="join_avatar" />
								<input type="hidden" name="c[]" value="0" id="join_avatar_error" />
							</div>
							<div style="clear:both;"></div>
						</div>
						<div class="bottom">
							<a class="button" href="" onclick="join.step(0);return false;">'.s('prev').'</a> <a class="button" href="" onclick="join.step(2);return false;">'.s('next').'</a>
						</div>
					</div>
					<div style="display:none;" class="step step2">
						<div class="content">
							<h3>'.s('contact_info').'</h3>
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
									<input type="hidden" name="street_number" id="join_hsnr" value="" />
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
							<h3>'.s('legal_stuff').'</h3>
							<div class="element-wrapper">
								<h4>Datenschutzerklärung</h4>
								<textarea readonly="readonly">Die personenbezogenen Informationen, die Du uns mitteilst, werden von foodsharing e.V., Marsiliusstr 36, 50937 Köln, gespeichert und verarbeitet. Dieser ist die verantwortliche	Stelle im Sinne des BDSG. Wir speichern und verarbeiten deine persönlichen Daten ausschließlich zur Organisation von Foodsharing. Eine Weitergabe deiner Daten an Dritte erfolgt nur mit deiner Einwilligung. Du kannst jederzeit Auskunft über die zu deiner Person gespeicherten Daten erhalten und der Verarbeitung deiner Daten widersprechen. Hierzu genügt eine	formlose Mitteilung an foodsharing e.V. Marsiliusstr 36, 50937 Köln oder per Mail an botschafter@foodsharing.de.</textarea>
							</div>
							<div class="element-wrapper">
								<h4>Rechtsvereinbarung</h4>
								<textarea readonly="readonly">Die Foodsaver erklären gegenüber dem Foodsharing e.V. das Folgende: Ich werde im Rahmen von Foodsharing e.V. als Foodsaver tätig. Ich werde bei Lebensmittelspendern Lebensmittel abholen und diese an Dritte weiterverschenken. Ich verzichte gegenüber dem Foodsharing e.V. und gegenüber dem Lebensmittelspender auf die Geltendmachung jeglichen Schadensersatzes, auch deren Lieferanten gegenüber. Jede Haftung des Lebensmittelspenders, auch für Fahrlässigkeit jeden Grades, ist ausgeschlossen. Ich verpflichte mich, die Lebensmittelspenden ausschließlich unentgeltlich weiterzugeben und vor der Weitergabe nach bestem Wissen und Gewissen auf ihre Unbedenklichkeit zu überprüfen. Die Verhaltensanweisungen im Ratgebers des Foodsharing e.V., insbesondere zu verderblichen Lebensmitteln, habe ich zur Kenntnis genommen und werde sie befolgen. Mir ist bekannt, dass der Foodsharing e.V. selbst nicht Vertragspartner der Lebensmittelspenden wird und keine Haftung dafür übernimmt. Alles was abgeholt wird, darf ausschließlich nicht kommerziell weitergegeben werden. Die Foodsaver behalten, wenn sie wollen, so viele Lebensmittel für sich wie sie essen bzw. an private Kontakte fairteilen können. Alles andere wird auf foodsharing.de eingestellt bzw. an Suppenküchen, Tafeln, Bahnhofsmissionen, gemeinnützige Vereine etc. verteilt. Das oberste Ziel ist es alle noch genießbaren abgeholten Lebensmittel vor der Vernichtung zu bewahren und sie dem menschlichen Verzehr zuzuführen. Alle Lebensmittelspenderbetriebe, Vereine, Bauernhöfe etc., die Essen abgeben, werden von jeglicher Haftung für die Genießbarkeit bzw. gesundheitliche Unbedenklichkeit der Ware entbunden, die Foodsaver tragen damit die volle Verantwortung für die Lebensmittel die sie abholen und müssen selbst entscheiden, ob diese für den Verzehr bzw. die Weitergabe noch geeignet sind. Die Lebensmittelspender erklären sich bereit, Kühlware und leicht verderblichere Lebensmittel soweit nach eigenem Ermessen möglich bis zur Abholung durch die Foodsaver weiter sachgerecht zu lagern und andernfalls die Foodsaver auf Ausnahmen, z.B. nicht ausreichende Kühlung infolge von Platzmangel, aufmerksam zu machen. Als Foodsaver garantiere ich, mich verantwortlich und fachgerecht um die Entsorgung der nicht mehr genießbaren Lebensmittel, aber auch Verpackungen, Kartons etc. zu kümmern. Außerdem verpflichten sich die Foodsaver den Ort, an dem die Ware entgegengenommen bzw. getrennt wird, mindestens so sauber zu hinterlassen, wie er vorgefunden wurde. Die Lebensmittel werden zu den Zeiten abgeholt, zu denen es der Lebensmittelspender wünscht. Normalerweise sind dies feste Zeiten, allerdings stehen die Foodsaver auch bereit um außerterminlich Lebensmittel abzuholen. Jede Person, Verein oder Gruppe kann Lebensmittel abholen, solange sie die in dieser Vereinbarung festgelegten Regeln beachten. Die Foodsaver handeln ehrenamtlich aus sozialen, ethischen und ökologischen Gründen, um die Lebensmittelverschwendung und damit den Hunger, die Ressourcenverschwendung, den Klimawandel usw. zu minimieren. Die Foodsaver sind eine effiziente, lokale und zeitnahe Ergänzung zu anderen gemeinnützigen Organisationen wie den Tafeln. Das Ziel ist es auch kleinen Lebensmittelspendern wie Bäckereien, Bioläden, Restaurants etc. durch die Kooperation mit den Foodsavern zu ermöglichen, dass überhaupt keine Lebensmittel, die noch genießbar sind, weggeworfen werden müssen. Ziel ist es, eine Abholquote von 100% zu erreichen um diese zu gewährleisten, sind alle Foodsaver immer gut vernetzt und bei unerwartetem Ausfall wie z.B. durch Krankheit etc. dazu verpflichtet, sich um eine(n) Ersatzfoodsaver der/die am besten schon mal bei dem Lebensmittelspenderbetrieb abgeholt hat, zu kümmern. Das Suchen nach einem Ersatz sollte spätestens 18 Stunden vor dem Abholtermin via Telefon und Email beginnen. Jeder Lebensmittelspenderbetrieb, der keine Lebensmittel mehr wegwirft, bekommt einen 14cm radiusgroßen Sticker mit der Aufschrift: “Wir machen mit foodsharing.de bei uns kommen keine Lebensmittel in die Tonne”; außerdem wird in dem Betrieb angeboten, Flyer und Plakate aufzuhängen/auszuhändigen und den Betrieb auch öffentlich auf foodsharing.de zu erwähnen. </textarea>
							</div>
							<label><input id="join_legal1" type="checkbox" name="join_legal1" value="1" /> '.s('have_read_the_legal_stuff1').'</label><br />
							<label><input id="join_legal2" type="checkbox" name="join_legal2" value="1" /> '.s('have_read_the_legal_stuff2').'</label>
						</div>
						<div class="bottom">
							<a class="button" href="" onclick="join.step(2);return false;">'.s('prev').'</a> <a class="button" href="" onclick="join.finish();return false;">'.s('finish').'</a>
						</div>
					</div>
				</div>
				<div style="clear:both;"></div>
		</div>
		<div id="joinloader" style="display:none;"><span class="fa fa-circle-o-notch fa-spin"></span></div>
		<div id="joinready" style="display:none">'.v_success(s('check_mail'),s('join_success')).'</div>';
	}
	
	public function passwordRequest()
	{
		if(!S::may())
		{
			$mail = '';
			if(isset($_GET['m']) && validEmail($_GET['m']))
			{
				$mail = $_GET['m'];
			}
			
			$cnt = v_info('Bitte trage hier Deine E-Mail Adresse ein mit der Du auf Lebensmittelretten.de angemeldet bist.');
			
			$cnt .= '
			<form name="passReset" method="post" class="contact-form" action="'.$_SERVER['REQUEST_URI'].'">
					'.v_form_text('email',array('value' => $mail)).'
					'.v_form_submit(s('send'), 'submitted').'
			</form>';
			
			return v_field($cnt,'Passwort zurücksetzen',array('class' => 'ui-padding'));
		}
		else
		{
			return v_field(v_info('Du bist angemeldet als '.S::user('name'),'Du bist angemeldet'),array('class' => 'ui-padding'));
		}
		/*
		'
		<div class="post">
			<p>
				
			</p>
			<form name="passReset" method="post" class="contact-form" action="'.$_SERVER['REQUEST_URI'].'">
				<table>
					<tbody>
					<tr>
						<td class="label">Deine E-Mail Adresse:</td>
						<td><p><input type="text" class="input-text-1" name="email" value="'.$mail.'" /></p></td>
					</tr>
	
					<tr><td colspan="2" class="comment-spacer-1"></td></tr>
					<tr>
						<td></td>
						<td><p class="show-all"><a onclick="document.forms.passReset.submit();return false;" class="btn-1 btn-1-color-default" href="#"><span>Absenden</span></a></p></td>
					</tr>
				</tbody></table>
			</form>
		</div>';*/
	}
	
	public function newPasswordForm($key)
	{
		$key = preg_replace('/[^0-9a-zA-Z]/', '', $key);
		
		$cnt = v_info('Jetzt kannst Du Dein Passwort ändern.');
		
		$cnt .= '
			<form name="newPass" method="post" class="contact-form">
				<input type="hidden" name="k" value="'.$key.'" />
				'.v_form_passwd('pass1').'
				'.v_form_passwd('pass2').'
				'.v_form_submit(s('save'), 'submitted').'
			</form>';
		
		return v_field($cnt,'Neues Passwort setzen',array('class' => 'ui-padding'));
		
	}
}