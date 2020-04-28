<?php

namespace Foodsharing\Modules\Register;

use Foodsharing\Modules\Core\View;

class RegisterView extends View
{
	public function registerForm()
	{
		return $this->vueComponent('register-form', 'RegisterForm');
	}
}
