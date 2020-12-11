<?php

namespace Foodsharing\Modules\Store\DTO;

class CreateStoreData
{
	public string $name;
	public int $regionId;

	public string $lat;
	public string $lon;
	public string $str;
	public string $hsnr = '';
	public string $zip;
	public string $city;

	public \DateTime $createdAt;
	public \DateTime $updatedAt;
}
