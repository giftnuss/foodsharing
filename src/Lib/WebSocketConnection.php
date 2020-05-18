<?php

namespace Foodsharing\Lib;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use function Sentry\captureException;

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

	private function post($url, $options): void
	{
		try {
			$this->guzzle->post($url, $options);
		} catch (\Exception $e) {
			captureException($e);
		}
	}

	public function sendSock(int $fsid, string $app, string $method, array $options): void
	{
		$url = SOCK_URL . 'users/' . $fsid . '/' . $app . '/' . $method;
		$this->post($url, [RequestOptions::JSON => $options]);
	}

	public function sendSockMulti(array $fsids, string $app, string $method, array $options): void
	{
		$url = SOCK_URL . 'users/' . join(',', $fsids) . '/' . $app . '/' . $method;
		$this->post($url, [RequestOptions::JSON => $options]);
	}

	public function isUserOnline(int $fsid): bool
	{
		try {
			$userIsOnline = $this->guzzle->get(SOCK_URL . 'users/' . $fsid . '/is-online')->getBody()->getContents();

			if ($userIsOnline === 'true') {
				return true;
			}
		} catch (\Exception $e) {
			captureException($e);
		}

		return false;
	}
}
