<?php

namespace Foodsharing\Modules\Legal;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

class LegalForm extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
			->add('privacyPolicyAcknowledged', CheckboxType::class, ['label' => 'legal.agree_privacy_policy', 'required' => true])
			->add('privacyNoticeAcknowledged', ChoiceType::class, ['label' => 'legal.agree_privacy_notice',
				'choices' => [
					'legal.privacy_notice_agree.select' => [
						'legal.privacy_notice_agree.acknowledge' => true,
						'legal.privacy_notice_agree.not_acknowledge' => false
					]
				]
			]);
	}
}
