<?php

namespace App\Clients;


class ProxyClient
{

    public static function getProxy()
    {
        if (config('proxy-pool.enable_proxy')) {
            $data = self::getProxyApi();
            return data_get($data, 'proxy');
        }
        return null;
    }

    public static function getProxyApi()
    {
        try {
            $curl = curl_init();
            $url = config('proxy-pool.proxy_server_url');
            curl_setopt_array($curl, array(
                CURLOPT_URL => "$url/get",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
            ));

            $response = curl_exec($curl);

            curl_close($curl);
            return json_decode($response, true);
        } catch (\Exception $exception) {
            return null;
        }
    }

    public static function deleteProxy($proxy)
    {
        try {
            $curl = curl_init();
            $url = config('proxy-pool.proxy_server_url');
            curl_setopt_array($curl, array(
                CURLOPT_URL => "$url/delete/?proxy=$proxy",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
            ));

            $response = curl_exec($curl);

            curl_close($curl);
        } catch (\Exception $exception) {
        }
    }
}
