<?php

namespace Foodsharing\Lib\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TageditType extends AbstractType
{
	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults(['entry_type' => TagType::class,
			'entry_options' => ['attr' => ['class' => 'tag'], 'label' => false, 'required' => false],
			'allow_add' => true,
			'allow_delete' => true,
			'required' => false,
		]);
	}

	public function getParent()
	{
		return CollectionType::class;
	}
}
