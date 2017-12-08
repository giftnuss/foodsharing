<?php

class LookupModel extends ConsoleModel
{
	public function getFoodsaverByEmail($email)
	{
		return $this->qRow("SELECT * FROM fs_foodsaver WHERE email = '" . $this->safe($email) . "'");
	}
}
