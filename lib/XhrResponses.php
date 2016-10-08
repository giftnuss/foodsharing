<?php
class XhrResponses
{
	public function fail_permissions()
	{
		return array(
			'status' => 0,
			'msg' => array(
				'text' => 'Du hast leider nicht die notwendigen Berechtigungen für diesen Vorgang.',
				'type' => 'error'
			)
		);
	}

	public function fail_generic()
	{
		return array( 'status' => 0 );
	}

	public function success()
	{
		return array(
			'status' => 1
		);
	}
}
