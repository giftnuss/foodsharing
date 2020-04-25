<?php

namespace Foodsharing\Lib;

/**
 * This class is for handling connections to our WebSocket server.
 *
 * Because it has to handle many connections at the same time, the server written in JavaScript/Node.js and not in PHP.
 * For historical reasons, the docker container containing our WebSocket server has been called "chat". You can find a
 * brief description of its API in chat/src/server.ts.
 */
class WebSocketConnection
{
	public function sendSock(int $fsid, string $app, string $method, array $options): void
	{
		$query = http_build_query([
			'u' => $fsid, // user id
			'a' => $app, // app
			'm' => $method, // method
			'o' => json_encode($options) // options
		]);
		file_get_contents(SOCK_URL . '?' . $query);
	}

	public function sendSockMulti(array $fsids, string $app, string $method, array $options): void
	{
		$query = http_build_query([
			'us' => join(',', $fsids), // user ids
			'a' => $app, // app
			'm' => $method, // method
			'o' => json_encode($options) // options
		]);
		file_get_contents(SOCK_URL . '?' . $query);
	}

	public function isUserOnline(int $fsid): bool
	{
		$userIsOnline = file_get_contents(SOCK_URL . 'is-connected?u=' . $fsid);

		if ($userIsOnline === 'true') {
			return true;
		}

		return false;
	}
}
