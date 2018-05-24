<?php

namespace Foodsharing\Modules\WorkGroup;

use Foodsharing\Lib\Form\PictureUploadType;
use Foodsharing\Lib\Form\TageditType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class WorkGroupForm extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
			->add('name', TextType::class, ['label' => 'group.name'])
			->add('description', TextAreaType::class, ['label' => 'group.description'])
			->add('photo', PictureUploadType::class, ['label' => 'group.photo'])
			->add('applyType', ChoiceType::class, ['label' => 'group.application_requirements.requirements', 'choices' => [
				'group.application_requirements.nobody' => 0,
				'group.application_requirements.requires_properties' => 1,
				'group.application_requirements.everybody' => 2,
				'group.application_requirements.open' => 3
			]])
			->add('bananaCount', IntegerType::class, ['label' => 'group.application_requirements.banana_count'])
			->add('fetchCount', IntegerType::class, ['label' => 'group.application_requirements.fetch_count'])
			->add('weekNum', IntegerType::class, ['label' => 'group.application_requirements.member_since_weeks'])
			->add('members', TageditType::class, ['label' => 'group.members'])
			->add('administrators', TageditType::class, ['label' => 'group.administrators']);
	}
}
