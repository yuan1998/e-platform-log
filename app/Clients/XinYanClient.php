<?php

namespace App\Clients;


use App\Models\HospitalInfo;
use App\Models\Product;
use Carbon\Carbon;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class XinYanClient extends BaseClient
{

    public function searchApi($page = 1)
    {
        $id = $this->hospital->origin_id;

        $data = [
            "hospital_id" => $id,
            "is_home" => "0",
            "limit" => 20,
            "menu1_id" => "",
            "page" => $page,
            "uid" => "",
        ];

        $response = $this->get('https://m.soyoung.com/hospital/product', [
            'query' => $data,
            'headers' => [
                'authority' => 'm.soyoung.com',
                'sec-ch-ua' => '"Chromium";v="94", "Microsoft Edge";v="94", ";Not A Brand";v="99"',
                'accept' => 'application/json',
                'x-requested-with' => 'XMLHttpRequest',
                'sec-ch-ua-mobile' => '?1',
                'user-agent' => 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/94.0.4606.61 Mobile Safari/537.36 Edg/94.0.992.31',
                'sec-ch-ua-platform' => '"Android"',
                'sec-fetch-site' => 'same-origin',
                'sec-fetch-mode' => 'cors',
                'sec-fetch-dest' => 'empty',
                'referer' => 'https://m.soyoung.com/y/hospital/26728',
                'accept-language' => 'zh-CN,zh;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6',
            ]
        ]);

        $body = $response->getBody()->getContents();
        $result = json_decode($body, true);


        if (data_get($result, 'status') !== 200) {
            Log::info('Debug 新氧 Api Result', [
                'result' => $body,
                'hospital' => $this->hospital,
                'data' => $data,
            ]);
            throw new Exception("Oops! Request Api is Error ,pls concat admin.");
        }
        $total = data_get($result, 'data.total', 0);
        $rows = data_get($result, 'data.list');

        $result = collect($rows)->map(function ($row) {
            return [
                'origin_id' => $row['pid'],
                'name' => $row['title'],
                "hospital_id" => $this->hospital->id,
                "platform_type" => $this->hospital->platform_type,
                "price" => $row['price_origin_online'],
                "online_price" => $row['price_online'],
                "sell" => $row['order_cnt'],
                "created_at" => $row['create_date'],
                "status" => Product::ONLINE_STATUS,
            ];
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
            sleep(1);
        } while ($page <= $last_page);

        return $rows->unique('origin_id');
    }

    public static function test()
    {
        HospitalInfo::pullAll();
    }


}
