<?php

namespace Foodsharing\Lib;

use GuzzleHttp\Client;

class BigBlueButton
{
	const DEFAULT_CLIENT = 'meet.example.org';
	private Client $client;
	private string $url;
	private string $secret;
	private string $dialin;

	public function __construct(string $url, string $secret, string $dialin, Client $client)
	{
		$this->client = $client;
		$this->url = $url;
		$this->secret = $secret;
		$this->dialin = $dialin;
	}

	public function isEnabled(): bool
	{
		return $this->client != self::DEFAULT_CLIENT;
	}

	public function createRoom($roomName, $roomKey)
	{
		$url = $this->createRoomURL($roomName, $roomKey);
		try {
			$res = $this->client->get($url)->getBody()->getContents();
			$res = new \SimpleXMLElement($res);

			if (!$res->returncode == 'SUCCESS') {
				return null;
			}

			return [
				'dialin' => (string)$res->dialNumber,
				'id' => (string)$res->voiceBridge
			];
		} catch (\Exception $e) {
			return null;
		}
	}

	private function createRoomURL($roomName, $roomKey)
	{
		$params = [
			'name' => $roomName,
			'meetingID' => 'fs-' . $roomKey,
			'attendeePW' => 'ap',
			'moderatorPW' => 'mp',
			'dialNumber' => $this->dialin
		];

		return $this->buildUrl('create', $params);
	}

	public function joinURL($roomKey, $username, $isModerator = false)
	{
		$params = [
			'fullName' => $username,
			'meetingID' => 'fs-' . $roomKey,
			'password' => $isModerator ? 'mp' : 'ap',
			'joinViaHtml5' => 'true',
			'redirect' => 'true'
		];

		return $this->buildUrl('join', $params);
	}

	private function buildUrl($method, $params)
	{
		$p = http_build_query($params);

		return 'https://' . $this->url . '/bigbluebutton/api/' . $method . '?' . $p . '&checksum=' . sha1($method . $p . $this->secret);
	}
}
