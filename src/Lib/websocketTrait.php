<?php


namespace Foodsharing\Lib;


trait websocketTrait
{
    public function sendSock($fsid, $app, $method, $options)
    {
        $query = http_build_query(array(
            'u' => $fsid, // user id
            'a' => $app, // app
            'm' => $method, // method
            'o' => json_encode($options) // options
        ));
        file_get_contents(SOCK_URL . '?' . $query);
    }

    public function sendSockMulti($fsids, $app, $method, $options)
    {
        $query = http_build_query(array(
            'us' => join(',', $fsids), // user ids
            'a' => $app, // app
            'm' => $method, // method
            'o' => json_encode($options) // options
        ));
        file_get_contents(SOCK_URL . '?' . $query);
    }
}