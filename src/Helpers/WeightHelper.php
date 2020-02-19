<?php

namespace Foodsharing\Helpers;

class WeightHelper
{
	public function mapIdToKilos(int $weightId)
	{
		$weightArray = $this->createWeightBaseArray();

		return isset($weightArray[$weightId]) ? $weightArray[$weightId]['value'] : 1.5;
	}

	/* todo Depending on future stats calculations this maybe needs to follow fs_abholmengen / fs_fetchweight (if still needed) */
	private function createWeightBaseArray(): array
	{
		return [
			1 => ['value' => 2, 'name' => '1-3 kg'],
			2 => ['value' => 4, 'name' => '3-5 kg'],
			3 => ['value' => 7.5, 'name' => '5-10 kg'],
			4 => ['value' => 15, 'name' => '10-20 kg'],
			5 => ['value' => 25, 'name' => '20-30 kg'],
			6 => ['value' => 45, 'name' => '40-50 kg'],
			7 => ['value' => 64, 'name' => 'mehr als 50 kg']
		];
	}

	public function getWeightListEntries(): array
	{
		$outArray = [];
		foreach ($this->createWeightBaseArray() as $weightKey => $weightSubArray) {
			array_push($outArray, ['id' => $weightKey, 'name' => $weightSubArray['name']]);
		}

		return $outArray;
	}

	public function getFetchWeightName($weightId)
	{
		$weightArray = $this->createWeightBaseArray();

		return isset($weightArray[$weightId]) ? $weightArray[$weightId]['name'] : null;
	}
}
