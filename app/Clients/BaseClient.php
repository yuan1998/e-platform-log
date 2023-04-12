<?php

namespace App\Clients;

use App\Models\HospitalInfo;
use Campo\UserAgent;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;

class BaseClient
{

    public $client;
    public $hospital;
    public $type;
    public $date;


    public function __construct(HospitalInfo $hospitalInfo)
    {
        $this->hospital = $hospitalInfo;
    }

    public function fillData($type, $date)
    {
        $this->type = $type;
        $this->date = $date;
        return $this;
    }

    public function getClient(): Client
    {
        if (!$this->client) {
            $jar = new \GuzzleHttp\Cookie\CookieJar();

            $ua = UserAgent::random([
                'device_type' => 'Desktop',
            ]);

            $this->client = new Client([
                'cookies' => $jar,
                'timeout' => 30,
                'read_timeout' => 30,
                'connect_timeout' => 30,
                'verify' => false,
                'headers' => [
                    'user-agent' => $ua
                ]
            ]);
        }

        return $this->client;
    }


    /**
     * @throws GuzzleException
     */
    public function post($url, $config = []): ResponseInterface
    {
        $client = $this->getClient();
        return $client->post($url, $config);
    }

    /**
     * @throws GuzzleException
     */
    public function get($url, $config = []): ResponseInterface
    {
        $client = $this->getClient();
        return $client->get($url, $config);
    }

    public  static function make(){
        return new static();
    }

}
