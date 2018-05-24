<?php

namespace Foodsharing\Modules\Legal;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;

class LegalForm extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
			->add('privacy_policy_date', HiddenType::class)
			->add('privacy_policy', CheckboxType::class, ['label' => 'legal.agree_privacy_policy', 'required' => true]);
	}
}
