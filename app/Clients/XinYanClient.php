<?php

namespace App\Clients;


use App\Models\Category;
use App\Models\HospitalInfo;
use App\Models\Product;
use Carbon\Carbon;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class XinYanClient extends BaseClient
{

    public function getHospitalHomeApi($id, $page)
    {
        $retryCount = 30;
        $break = false;
        while ($retryCount >= 0 && !$break) {
            $data = [
                "hospital_id" => $id,
                "is_home" => "0",
                "limit" => 20,
                "menu1_id" => "",
                "page" => $page,
                "uid" => "",
            ];
            $config = [
                'query' => $data,
                'headers' => [
                    'authority' => 'm.soyoung.com',
                    'sec-ch-ua' => '"Chromium";v="94", "Microsoft Edge";v="94", ";Not A Brand";v="99"',
                    'accept' => 'application/json',
                    'x-requested-with' => 'XMLHttpRequest',
                    'sec-ch-ua-mobile' => '?1',
                    'sec-ch-ua-platform' => '"Android"',
                    'sec-fetch-site' => 'same-origin',
                    'sec-fetch-mode' => 'cors',
                    'sec-fetch-dest' => 'empty',
                    'referer' => 'https://m.soyoung.com/y/hospital/26728',
                    'accept-language' => 'zh-CN,zh;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6',
                ]
            ];
            $proxy = null;
            try {
                if ($retryCount && $retryCount < 30)
                    if ($proxy = ProxyClient::getProxy())
                        $config['proxy'] = "http://$proxy";
                $response = $this->get('https://m.soyoung.com/hospital/product', $config);
                $body = $response->getBody()->getContents();
                $result = json_decode($body, true);
            } catch (Exception $exception) {
                $body = $exception->getMessage();
                $result = null;
            }
            if (data_get($result, 'status') !== 200) {
                Log::info('新氧 Api', [
                    '$retryCount' => $retryCount,
                    'hospital' => $this->hospital->name,
                    'data' => $data,
                    'result' => $body,
                ]);

                if ($proxy)
                    ProxyClient::deleteProxy($proxy);
                else
                    $break = true;
                $retryCount--;
            } else {
                Log::info('新氧 Api OK');
                return $result;
            }
        }
        throw new Exception("Oops! Request XinYan 'getHospitalHomeApi' Api is Error ,pls concat admin.");
    }

    public function searchApi($page = 1)
    {
        $id = $this->hospital->origin_id;
        $result = $this->getHospitalHomeApi($id, $page);
        if (!$result) return null;

        $total = data_get($result, 'data.total', 0);
        $rows = data_get($result, 'data.list');

        $result = collect($rows)->map(function ($row) {
            $title = $row['title'];
            $result = [
                'origin_id' => $row['pid'],
                'name' => $title,
                "hospital_id" => $this->hospital->id,
                "platform_type" => HospitalInfo::XINYAN_ID,
                "price" => $row['price_origin_online'],
                "online_price" => $row['price_online'],
                "sell" => $row['order_cnt'],
                "created_at" => Carbon::parse($row['create_date'])->toDateTimeString(),
                "status" => Product::ONLINE_STATUS,
            ];
            if ($id = Category::validateKeyword($title)) {
                $result["category_id"] = $id;
            }

            return $result;
        });

        return [
            'last_page' => ceil($total / 20),
            'rows' => $result
        ];
    }

    /**
     * @throws GuzzleException
     * @throws Exception
     */
    public function search($page = 1): Collection
    {
        $this->get('https://m.soyoung.com/y/hospital/26728');

        $last_page = 0;
        $rows = collect();
        do {
            $result = $this->searchApi($page);
            if (!$last_page) {
                $last_page = $result['last_page'];
            }
            $rows = $rows->merge($result['rows']);
            $page++;
        } while ($page <= $last_page);

        return $rows->unique('origin_id');
    }

    public static function test()
    {
        HospitalInfo::pullAll();
    }
}
