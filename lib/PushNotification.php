<?php

/**
 * PHP Apple push notification service integration
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

class PushNotification {
    
    
    private $sandbox = "ssl://gateway.sandbox.push.apple.com:2195";
    private $production  = "ssl://gateway.push.apple.com:2195";
    private $messagebody;
    private $devicetoken;
    private $badgecount;
    private $passphrase;
    private $certificatepath;
    private $envoirnment;
    private $payload;
    
    
    public function __construct($envoirnment,$certificatepath) {
        
    	$this->payload = array();
    	
        if($envoirnment!="sandbox" && $envoirnment!= "production") {
            throw new Exception("Invalid envoirnment specified");
        } else {
            $this->envoirnment = $envoirnment;
        }
        
        if(!file_exists($certificatepath)) {
            throw new Exception("Certificate does not exsists at the path specifed");
        }
        else {
            $this->certificatepath = $certificatepath;
        }
    }
    
    /**
     * Function setPassPhrase
     * Set passphrase for the certificate
     *
     * @param string $passphrase Pass phrase for the cretificate
     *
     */
    
    public function setPassPhrase($passphrase) {
        $this->passphrase = $passphrase;
    }
    
     /**
     * Function setMessageBody
     * Set message body which needs to be sent
     *
     * @param string $message Message to be sent
     *
     */
    public function setMessageBody($message) {
        $this->messagebody = $message;
    }
    
    /**
     * Function setBadge
     * Set application badge
     *
     * @param int $badge Badge number
     *
     */
    public function setBadge($badge) {
        $this->badgecount = (int)$badge;
    }
    
    /**
     * Function setDeviceToken
     * Set device token to whihc notification should be sent
     *
     * @param string $token Device token
     *
     */
    public function setDeviceToken($token) {
        $this->devicetoken = $token;
    }
    
    
    public function setData($data)
    {
    	$this->payload = $data;
    }
    
    /**
     * Function sendNotification
     * Send notification
     *
     */
    public function sendNotification() {
        
        $ctx = stream_context_create();
        // Set option to send certificate
        stream_context_set_option($ctx, 'ssl', 'local_cert', $this->certificatepath);
        // check if passphrase is set?
        if(!empty($this->passphrase)) {
            stream_context_set_option($ctx, 'ssl', 'passphrase', $this->passphrase);
        }
        $url = "";
        // Check for envoirnment and set $url variable
        if($this->envoirnment == "sandbox") {
            $url = $this->sandbox;
        }
        else $url = $this->production;
        // Create socket clinet
        $sp = stream_socket_client($url, $err, $errstr, 60, (STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT), $ctx);
        if (!$sp)
        throw new Exception("Could not connect tp apple gateway.");
        
        // Populate payload body
        $payload = $this->payload;
        $payload['aps'] = array('alert' => $this->messagebody, 'badge' => $this->badgecount, 'sound' => 'default');
        $output = json_encode($payload);
        
        $msg = chr(0) . pack('n', 32) . pack('H*', $this->devicetoken) . pack('n', strlen($output)) . $output;
        // Send it to the gateway server
        $result = fwrite($sp, $msg, strlen($msg));
        if (!$result) {
            return false;    
        }
        else return true;
    }
}
?>