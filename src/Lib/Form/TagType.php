<?php

namespace Foodsharing\Lib\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class TagType extends AbstractType
{
	public function getParent()
	{
		return TextType::class;
	}
}
