<?php

namespace DataProvider\Datastore;

use Exception;
use Predis\Client;

class Redis
{
    static private $instance;

    static private $redis;

    private function __construct()
    {
        try {
            $redis = new Client([
                'host' => '127.0.0.1' // docker container name, app_redis
            ]);
        } catch (Exception $exception) {
            echo 'Not connected';
        }

        self::$redis = $redis;
    }

    static public function get_redis()
    {
        if (!self::$instance instanceof self) {
            self::$instance = new self();
        }
        return self::$redis;
    }
}