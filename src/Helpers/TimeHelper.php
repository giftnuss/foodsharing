<?php

namespace Foodsharing\Helpers;

use Flourish\fDate;
use Foodsharing\Lib\Func;

final class TimeHelper
{
	private $func;

	public function __construct(Func $func)
	{
		$this->func = $func;
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
			$dateString = $this->func->s('today') . ', ';
		} elseif ($date->eq('tomorrow')) {
			$dateString = $this->func->s('tomorrow') . ', ';
		} elseif ($date->eq('-1 day')) {
			$dateString = $this->func->s('yesterday') . ', ';
		} else {
			$dateString = '';
			$extendWithAbsoluteDate = true;
		}

		if ($extendWithAbsoluteDate) {
			$days = $this->getDow();
			$dateString = $dateString . $days[date('w', $unixTimeStamp)] . ', ' . (int)date(
					'd',
					$unixTimeStamp
				) . '. ' . $this->func->s('smonth_' . date('n', $unixTimeStamp));
			$year = date('Y', $unixTimeStamp);
			if ($year != date('Y')) {
				$dateString = $dateString . ' ' . $year;
			}
			$dateString .= ', ';
		}

		return $dateString . date('H:i', $unixTimeStamp) . ' ' . $this->func->s('clock');
	}

	public function niceDateShort($ts)
	{
		if (date('Y-m-d', $ts) === date('Y-m-d')) {
			return $this->func->s('today') . ' ' . date('H:i', $ts);
		}

		return date('j.m.Y. H:i', $ts);
	}

	public function getDow(): array
	{
		return [
			1 => $this->func->s('monday'),
			2 => $this->func->s('tuesday'),
			3 => $this->func->s('wednesday'),
			4 => $this->func->s('thursday'),
			5 => $this->func->s('friday'),
			6 => $this->func->s('saturday'),
			0 => $this->func->s('sunday'),
		];
	}
}
