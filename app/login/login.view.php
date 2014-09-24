<?php
class LoginView extends View
{
	public function loginForm()
	{
		return 	v_form_text('email_adress').
				v_form_passwd('password') .
				v_form_hidden('ismob', '0') .
				'<p>
					<a href="/?page=login&sub=passwordReset">Passwort vergessen?</a>
				</p>';
							
	}
}