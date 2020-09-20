<?php

namespace Foodsharing\Utility;

use Carbon\Carbon;
use Symfony\Contracts\Translation\TranslatorInterface;

final class TimeHelper
{
	private $translator;

	public function __construct(TranslatorInterface $translator)
	{
		$this->translator = $translator;
	}

	// given a unix time it provides a human readable full date format.
	// parameter $extendWithAbsoluteDate == true adds the date between "today/tomorrow" and the time while false leaves it empty.
	public function niceDate(?int $unixTimeStamp, bool $extendWithAbsoluteDate = false): string
	{
		if ($unixTimeStamp === null) {
			return '- -';
		}

		$date = Carbon::createFromTimestamp($unixTimeStamp);

		if ($date->isToday()) {
			$dateString = $this->translator->trans('date.today');
		} elseif ($date->isTomorrow()) {
			$dateString = $this->translator->trans('date.tomorrow');
		} elseif ($date->isYesterday()) {
			$dateString = $this->translator->trans('date.yesterday');
		} else {
			$dateString = '';
			$extendWithAbsoluteDate = true;
		}

		if ($extendWithAbsoluteDate) {
			$dateString .= $this->getDow(intval(date('w', $unixTimeStamp))) . ', '
				. (int)date('d', $unixTimeStamp) . '. '
				. $this->translator->trans('month.short.' . date('n', $unixTimeStamp));

			$year = date('Y', $unixTimeStamp);
			if ($year != date('Y')) {
				$dateString .= ' ' . $year;
			}
		}

		return $dateString . ', ' . $this->translator->trans('date.time', [
			'{time}' => date('H:i', $unixTimeStamp),
		]);
	}

	public function niceDateShort(int $ts): string
	{
		if (date('Y-m-d', $ts) === date('Y-m-d')) {
			return $this->translator->trans('date.Today') . ' ' . date('H:i', $ts);
		}

		return date('j.m.Y. H:i', $ts);
	}

	public function month(int $ts): string
	{
		return $this->translator->trans('month.' . intval(date('m', $ts)));
	}

	public function getDow(int $day): string
	{
		return [
			1 => $this->translator->trans('date.monday'),
			2 => $this->translator->trans('date.tuesday'),
			3 => $this->translator->trans('date.wednesday'),
			4 => $this->translator->trans('date.thursday'),
			5 => $this->translator->trans('date.friday'),
			6 => $this->translator->trans('date.saturday'),
			0 => $this->translator->trans('date.sunday'),
		][$day];
	}
}
