<?php

namespace Foodsharing\Helpers;

final class TranslationHelper
{
	public function getTranslations(): array
	{
		global $g_lang;

		return $g_lang;
	}
}
