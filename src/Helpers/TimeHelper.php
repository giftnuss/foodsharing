<?php

namespace Foodsharing\Helpers;

use Flourish\fDate;

final class TimeHelper
{
	private $translationHelper;

	public function __construct(TranslationHelper $translationHelper)
	{
		$this->translationHelper = $translationHelper;
	}

	// given a unix time it provides a human readable full date format.
	// parameter $extendWithAbsoluteDate == true adds the date between "today/tomorrow" and the time while false leaves it empty.
	public function niceDate(?int $unixTimeStamp, bool $extendWithAbsoluteDate = false): string
	{
		if ($unixTimeStamp === null) {
			return '- -';
		}

		$date = new fDate($unixTimeStamp);

		if ($date->eq('today')) {
			$dateString = $this->translationHelper->s('today') . ', ';
		} elseif ($date->eq('tomorrow')) {
			$dateString = $this->translationHelper->s('tomorrow') . ', ';
		} elseif ($date->eq('-1 day')) {
			$dateString = $this->translationHelper->s('yesterday') . ', ';
		} else {
			$dateString = '';
			$extendWithAbsoluteDate = true;
		}

		if ($extendWithAbsoluteDate) {
			$days = $this->getDow();
			$dateString = $dateString . $days[date('w', $unixTimeStamp)] . ', ' . (int)date(
					'd',
					$unixTimeStamp
				) . '. ' . $this->translationHelper->s('smonth_' . date('n', $unixTimeStamp));
			$year = date('Y', $unixTimeStamp);
			if ($year != date('Y')) {
				$dateString = $dateString . ' ' . $year;
			}
			$dateString .= ', ';
		}

		return $dateString . date('H:i', $unixTimeStamp) . ' ' . $this->translationHelper->s('clock');
	}

	public function niceDateShort($ts)
	{
		if (date('Y-m-d', $ts) === date('Y-m-d')) {
			return $this->translationHelper->s('today') . ' ' . date('H:i', $ts);
		}

		return date('j.m.Y. H:i', $ts);
	}

	public function getDow(): array
	{
		return [
			1 => $this->translationHelper->s('monday'),
			2 => $this->translationHelper->s('tuesday'),
			3 => $this->translationHelper->s('wednesday'),
			4 => $this->translationHelper->s('thursday'),
			5 => $this->translationHelper->s('friday'),
			6 => $this->translationHelper->s('saturday'),
			0 => $this->translationHelper->s('sunday'),
		];
	}
}
