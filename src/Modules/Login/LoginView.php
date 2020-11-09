<?php

namespace Foodsharing\Modules\Login;

use Foodsharing\Modules\Core\View;

class LoginView extends View
{
	public function passwordRequest(): string
	{
		if ($this->session->may()) {
			return '';
		}

		$params = [
			'email' => $this->translator->trans('register.login_email'),
			'action' => $_SERVER['REQUEST_URI'],
		];

		return $this->twig->render('pages/ForgotPassword/ForgotPasswordForm.twig', $params);
	}

	public function newPasswordForm(string $key): string
	{
		$key = preg_replace('/[^0-9a-zA-Z]/', '', $key);
		$out = $this->v_utils->v_info($this->translator->trans('register.change-password'));
		$out .= '
		<form name="newPass" method="post" class="contact-form">
			<input type="hidden" name="k" value="' . $key . '" />
			' . $this->v_utils->v_form_passwd('pass1') . '
			' . $this->v_utils->v_form_passwd('pass2') . '
			<div class="input-wrapper">
				<input class="button" type="submit" value="' . $this->translator->trans('button.save') . '" />
			</div>
		</form>';

		return $this->v_utils->v_field(
			$out,
			$this->translator->trans('register.set-password'),
			['class' => 'ui-padding']
		);
	}

	public function loginForm()
	{
		return $this->vueComponent('login-form', 'LoginForm');
	}
}
