<?php

namespace Foodsharing\Lib\Xhr;

/**
 * class give you methods to send data to the client for nice outputs.
 *
 * @author raphael
 */
class Xhr
{
	// display simple popup messages after the request
	private array $messages;

	// additional script that is executed if the request success
	private string $script;

	// status code 1 = will execute the response
	private int $status;

	// data that will be sent to the client and is available under global ajax object (ajax.data)
	private array $data;

	public function __construct()
	{
		$this->messages = [];
		$this->script = '';
		$this->status = 1;
		$this->data = [];
	}

	/**
	 * Method to set the status code returned to the client.
	 *
	 * @param int $code
	 *
	 * @deprecated Only SettingsXhr is still using this, do not add new usage!
	 */
	public function setStatus($code): void
	{
		$this->status = (int)$code;
	}

	/**
	 * Add data accessible by client side js code.
	 *
	 * example:
	 *    $xhr->addData('tree_id', 1);
	 *    is accessible in global Javascript with:
	 *    alert(ajax.data.tree_id); => output is "1".
	 *
	 * @deprecated Only ActivityXhr BasketXhr and BellXhr are still using this, do not add new usage!
	 */
	public function addData(string $key, $value): void
	{
		$this->data[$key] = $value;
	}

	/**
	 * Method to add an simple pop up message to the cloent, there are 3 types of messages info, error and success can be added as 2nd parameter, default is info.
	 *
	 * @deprecated Only StoreXhr and TeamXhr are still using this, do not add new usage!
	 */
	public function addMessage(string $msg, string $type = 'info'): void
	{
		$this->messages[] = [
			'type' => $type,
			'text' => $msg
		];
	}

	/**
	 * Method to programmatically add javascript code that will be executed on client side.
	 *
	 * @deprecated Only StoreXhr and TeamXhr are still using this, do not add new usage!
	 */
	public function addScript(string $js): void
	{
		$this->script .= "\n" . $js;
	}

	/**
	 * Method to send everything to the client in the expected format.
	 */
	public function send()
	{
		header('content-type: application/json; charset=utf-8');
		$out = [
			'status' => $this->status,
			'data' => $this->data,
			'script' => $this->script
		];

		if (!empty($this->messages)) {
			$out['msg'] = $this->messages;
		}

		echo json_encode($out, JSON_PARTIAL_OUTPUT_ON_ERROR);
		exit();
	}
}
