<?php

namespace Foodsharing\Lib;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;

/**
 * This class is for handling connections to our WebSocket server.
 *
 * Because it has to handle many connections at the same time, the server written in JavaScript/Node.js and not in PHP.
 * For historical reasons, the docker container containing our WebSocket server has been called "chat". Its API is
 * provided by chat/src/RestController.ts.
 */
class WebSocketConnection
{
	private $guzzle;

	public function __construct(Client $guzzle)
	{
		$this->guzzle = $guzzle;
	}

	public function sendSock(int $fsid, string $app, string $method, array $options): void
	{
		$url = SOCK_URL . 'user/' . $fsid . '/' . $app . '/' . $method;
		$this->guzzle->post($url, [RequestOptions::JSON => $options]);
	}

	public function sendSockMulti(array $fsids, string $app, string $method, array $options): void
	{
		$url = SOCK_URL . 'user/' . join('-', $fsids) . '/' . $app . '/' . $method;
		$this->guzzle->post($url, [RequestOptions::JSON => $options]);

	}

	public function isUserOnline(int $fsid): bool
	{
		$userIsOnline = $this->guzzle->get(SOCK_URL . 'user/' . $fsid . '/is-online')->getBody()->getContents();

		if ($userIsOnline === 'true') {
			return true;
		}

		return false;
	}
}
