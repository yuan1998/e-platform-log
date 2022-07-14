<?php

namespace App\Clients;

use App\Models\HospitalInfo;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;

class BaseClient
{

    public $client;
    public $hospital;

    public function __construct(HospitalInfo $hospitalInfo)
    {
        $this->hospital = $hospitalInfo;
    }


    public function getClient(): Client
    {
        if (!$this->client) {
            $jar = new \GuzzleHttp\Cookie\CookieJar();

            $this->client = new Client([
                'cookies' => $jar,
                'headers' => [
                    'user-agent' => 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/94.0.4606.61 Mobile Safari/537.36 Edg/94.0.992.31',
                    'x-requested-with' => 'XMLHttpRequest'
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


}
