<?php

namespace Foodsharing\Modules\Region;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;

class ForumPostForm extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
			->add('body', TextAreaType::class, ['label' => 'forum.placeholder_answer'])
			->add('subscribe', ChoiceType::class, ['label' => 'forum.subscribe_thread', 'choices' => [
				'yes' => 1,
				'no' => 0
			], 'expanded' => true])
			->add('thread', HiddenType::class)
		;
	}
}
