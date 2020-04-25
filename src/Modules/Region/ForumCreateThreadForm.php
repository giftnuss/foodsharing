<?php

namespace Foodsharing\Modules\Region;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ForumCreateThreadForm extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
			->add('title', TextType::class, ['label' => 'forum.thread.title'])
			->add('body', TextareaType::class, ['label' => 'forum.post.body'])
		;
		if ($options['postActiveWithoutModeration']) {
			$builder
				->add('sendMail', ChoiceType::class, ['label' => 'forum.inform_per_email',
					'choices' => [
						'yes' => true,
						'no' => false
					],
					'expanded' => true])
			;
		}
	}

	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults([
			'postActiveWithoutModeration' => true,
		]);
		$resolver->setAllowedTypes('postActiveWithoutModeration', 'bool');
	}
}
