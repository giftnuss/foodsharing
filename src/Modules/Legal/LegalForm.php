<?php

namespace Foodsharing\Modules\Legal;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;

class LegalForm extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
			->add('privacy_policy_date', HiddenType::class)
			->add('privacy_notice_date', HiddenType::class)
			->add('privacy_policy', CheckboxType::class, ['label' => 'legal.agree_privacy_policy', 'required' => true])
			->add('privacy_notice', ChoiceType::class, ['label' => 'legal.agree_privacy_notice',
				'choices' => ['legal.privacy_notice_agree.select' => 0, 'legal.privacy_notice_agree.acknowledge' => 1, 'legal.privacy_notice_agree.not_acknowledge' => 2], ]);
	}
}
