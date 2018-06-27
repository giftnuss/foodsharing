<?php

namespace Foodsharing\Modules\Login;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;

class LoginForm extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options): void
	{
		$builder
			->add('email_address', EmailType::class, ['label' => 'login.email_address'])
			->add('password', PasswordType::class, ['label' => 'login.password']);
	}
}
