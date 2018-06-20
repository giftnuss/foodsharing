<?php

namespace Foodsharing\Modules\Login;

use Foodsharing\Lib\Session\S;
use Foodsharing\Modules\Core\View;

class LoginView extends View
{
	public function join($email = '', $pass = '', $datenschutz, $rechtsvereinbarung)
	{
		$params = array(
			'i_am_a' => $this->func->s('i_am_a'),
			'login_info' => $this->func->s('login_info'),
			'login_name' => $this->func->s('login_name'),
			'login_surname' => $this->func->s('login_surname'),
			'login_email' => $this->func->s('login_email'),
			'login_passwd1' => $this->func->s('login_passwd1'),
			'login_passwd2' => $this->func->s('login_passwd2'),
			'login_phone' => $this->func->s('login_phone'),
			'login_phone_info' => $this->func->s('login_phone_info'),
			'login_location' => $this->func->s('login_location'),
			'select_your_gender' => $this->func->s('select_your_gender'),
			'woman' => $this->func->s('woman'),
			'man' => $this->func->s('man'),
			'other' => $this->func->s('other'),
			'geb_datum' => $this->func->s('geb_datum'),
			'select_picture' => $this->func->s('select_picture'),
			'date_min' => date('Y-m-d', strtotime('-120 years')),
			'date_max' => date('Y-m-d', strtotime('-18 years')),
			'contact_info' => $this->func->s('contact_info'),
			'legal_stuff' => $this->func->s('legal_stuff'),
			'next' => $this->func->s('next'),
			'prev' => $this->func->s('prev'),
			'peer' => $this->func->s('peer'),
			'finish' => $this->func->s('finish'),
			'check_mail' => $this->v_utils->v_success($this->func->s('check_mail')),
			'join_success' => $this->func->s('join_success'),
			'have_read_the_legal_stuff1' => $this->func->s('have_read_the_legal_stuff1'),
			'have_read_the_legal_stuff2' => $this->func->s('have_read_the_legal_stuff2'),
			'datenschutz' => $datenschutz,
			'rechtsvereinbarung' => $rechtsvereinbarung,
			'pass' => $pass,
			'email' => $email
		);

		return $this->twig->render('pages/Register/page.twig', $params);
	}

	public function passwordRequest()
	{
		if (!S::may()) {
			$mail = '';

			if (isset($_GET['m']) && $this->func->validEmail($_GET['m'])) {
				$mail = $_GET['m'];
			}

			$params = array(
				'email' => $this->func->s('login_email'),
				'action' => $_SERVER['REQUEST_URI']
			);

			return $this->twig->render('pages/ForgotPassword/page.twig', $params);
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
