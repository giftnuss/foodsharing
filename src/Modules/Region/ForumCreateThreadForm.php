<?php

namespace Foodsharing\Modules\Region;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class ForumCreateThreadForm extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
			->add('title', TextType::class, ['label' => 'forum.thread_title'])
			->add('body', TextAreaType::class, ['label' => 'forum.post_body'])
		;
	}
}
