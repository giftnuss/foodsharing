<?php
require_once './lib/PushNotification.php';

class PushControl extends ConsoleControl
{		
	private $model;
	
	public function __construct()
	{
		$this->model = new PushModel();
	}
	
	public function index()
	{
		while(true)
		{
			$this->runQueue();
			usleep(500000);
		}
	}
	
	private function runQueue()
	{
		if($res = $this->model->sql('
		SELECT
			id,
			title,
			message,
			data,
			id_gcm,
			id_apn
		FROM
			fs_pushqueue
	
		WHERE
			`status` = 0
	
			'))
		{
			$send = array();
			while($row = $res->fetch_assoc())
			{
				$data = unserialize($row['data']);
				if($row['id_gcm'] != '')
				{
					$send[$row['id']] = $row['id'];
					$this->sendGcmNotification(array($row['id_gcm']), array(
						'title' => $row['title'],
						'message' => $row['message'],
						'd' => $data
					));
				}
	
				if($row['id_apn'] != '')
				{
					$send[$row['id']] = $row['id'];
					$this->sendIosNotification(
					$row['id_gcm'],
					array(
						'title' => $row['title'],
						'message' => $row['message']
					),
						$data
					);
				}
					
				info(count($send). 'send...');
					
				if(count($send) > 0)
				{
					$this->model->update('UPDATE fs_pushqueue SET `status` = 1 WHERE id IN('.implode(',', $send).')');
				}
			}
		}
	}
	
	private function sendGcmNotification( $registrationIdsArray, $messageData )
	{
		$apiKey = 'AIzaSyCgLZcUaDR17RMxq5JBdb-IPXWR0KhOf4o';
		$headers = array("Content-Type:" . "application/json", "Authorization:" . "key=" . $apiKey);
		$data = array(
				'data' => $messageData,
				'registration_ids' => $registrationIdsArray
		);
	
		$ch = curl_init();
	
		curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );
		curl_setopt( $ch, CURLOPT_URL, "https://android.googleapis.com/gcm/send" );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 0 );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode($data) );
	
		$response = curl_exec($ch);
		curl_close($ch);
	
		return $response;
	}
	
	private function sendIosNotification($deviceToken, $messageData, $data)
	{
	
		// set enovirnment and cretificate path
		$push = new PushNotification('production','/var/www/lmr-prod/ck.pem');
		// set device token
		$push->setDeviceToken($deviceToken);
		// Set pass phrase if any
		$push->setPassPhrase('FcY9Rkvk');
		// Set badge
		$push->setBadge(1);
		// Set message body
		$push->setMessageBody($messageData['title']);
	
		$push->setData($data);
	
		$push->sendNotification();
	
	}
}
