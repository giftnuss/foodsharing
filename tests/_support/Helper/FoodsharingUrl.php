<?php
namespace Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

class FoodsharingUrl extends \Codeception\Module
{
	public function StoreUrl($id)
	{
		return '/?page=fsbetrieb&id='.(int)$id;
	}

}
