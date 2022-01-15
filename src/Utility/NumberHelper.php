<?php

namespace Foodsharing\Utility;

use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Helper class providing functions for formating numbers.
 */
final class NumberHelper
{
	private TranslatorInterface $translator;

	public function __construct(TranslatorInterface $translator)
	{
		$this->translator = $translator;
	}

	/**
	 * Formats a number based on the current language selection.
	 *
	 * Examples (in english or german):
	 *  - format_number(1)               == "1" or "1"
	 *  - format_number(1.0001)          == "1.00" or "1,00"
	 *  - format_number(1.23456)         == "1.23" or "1,23"
	 *  - format_number(123)             == "123" or "123"
	 *  - format_number(12345)           == "12,345" or "12.345"
	 *  - format_number(12345.67)        == "12,345" or "12.345"
	 *  - format_number(1234567)         == "1.23 mio." or "1,23 Mio."
	 *  - format_number(12345678, false) == "12,345,678" or "12.345.678"
	 *  - format_number(123456789)       == "123 mio." or "123 Mio."
	 *  - format_number(1e10)            == "10.0 bn." or "10.0 Mrd."
	 *
	 * @param float $num The number to be formatted
	 * @param bool $abbreviate_large Whether to abbreviate large numbers
	 *			(>= 1 Mio.) as millions / billions
	 * @param int $min_significant_figures The number of required significant figures.
	 * 			This only applies if the number is not a whole number.
	 *
	 * @return string The formatted number
	 */
	public function format_number(float $num, bool $abbreviate_large = true, int $min_significant_figures = 3): string
	{
		$d_sep = $this->translator->trans('numbers.decimal_separator');
		$t_sep = $this->translator->trans('numbers.thousands_separator');
		if ($abbreviate_large && $num >= 1e6) {
			$num /= 1e6;
			$unit = $this->translator->trans('numbers.million_abbreviation');
			if ($num >= 1e3) {
				$num /= 1e3;
				$unit = $this->translator->trans('numbers.billion_abbreviation');
			}
			$digits = (int)floor(log10($num)) + 1;

			return number_format($num, $min_significant_figures - $digits, $d_sep, $t_sep) . $unit;
		}

		$digits = floor(log10($num)) + 1;
		$decimals = $min_significant_figures - $digits;
		if (abs(round($num) - $num) < PHP_FLOAT_EPSILON) { // Cut trailing zeros if the number is a whole number
			$decimals = 0;
		}

		return number_format($num, $decimals, $d_sep, $t_sep);
	}

	public function format_distance(float $distance)
	{
		$distance = round($distance, 1);
		if ($distance < 1) {
			return $this->format_number($distance * 1000) . ' m';
		}

		return $this->format_number($distance, true, 2) . ' km';
	}
}
