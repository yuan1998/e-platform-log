<?php

namespace App\Clients;


use App\Models\Category;
use App\Models\HospitalInfo;
use App\Models\Product;
use Illuminate\Support\Arr;
use PHPHtmlParser\Dom;

class DaZhongClient extends BaseClient
{

    public function searchApi($data = [])
    {
        $data = array_merge([
            "platform" => "pc",
            "channel" => "dp",
            "clienttype" => "web",
            "productid" => "3827144",
            "shopid" => "97497914",
            "shopuuid" => "l7LpxQ7ByObBbXls",
            "cityid" => "1",
        ], $data);

        $response = $this->get('https://mapi.dianping.com/dzbook/prepayproductdetail.json2', [
            'query' => $data,
            'headers' => [
                "Connection" => 'keep-alive',
                "Cache-Control" => 'max-age=0',
                "sec-ch-ua" => '"Chromium";v="94", "Microsoft Edge";v="94", ";Not A Brand";v="99"',
                "sec-ch-ua-mobile" => '?0',
                "sec-ch-ua-platform" => '"macOS"',
                "Upgrade-Insecure-Requests" => '1',
                "User-Agent" => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/94.0.4606.61 Safari/537.36 Edg/94.0.992.31',
                "Accept" => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
                "Sec-Fetch-Site" => 'none',
                "Sec-Fetch-Mode" => 'navigate',
                "Sec-Fetch-User" => '?1',
                "Sec-Fetch-Dest" => 'document',
                "Accept-Language" => 'zh-CN,zh;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6',
            ]
        ]);
        $content = $response->getBody()->getContents();
        $result = json_decode($content, true);

        $title = data_get($result, 'data.productItems.0.name');
        if (!$title) return null;

        $r = [
            'origin_id' => data_get($result, 'data.productItems.0.id'),
            'name' => $title,
            "hospital_id" => $this->hospital->id,
            "platform_type" => HospitalInfo::DAZHONG_ID,
            "price" => data_get($result, 'data.productItems.0.originalPrice'),
            "online_price" => data_get($result, 'data.productItems.0.price'),
            "sell" => data_get($result, 'data.saleCount'),
            "status" => Product::ONLINE_STATUS,
        ];

        if ($id = Category::validateKeyword($title)) {
            $r["category_id"] = $id;
        }

        return $r;

    }


    public function search()
    {
        $response = $this->get('https://www.dianping.com/shop/l7LpxQ7ByObBbXls', [
            'headers' => [
                "Connection" => 'keep-alive',
                "Cache-Control" => 'max-age=0',
                "sec-ch-ua" => '"Chromium";v="94", "Microsoft Edge";v="94", ";Not A Brand";v="99"',
                "sec-ch-ua-mobile" => '?0',
                "sec-ch-ua-platform" => '"macOS"',
                "Upgrade-Insecure-Requests" => '1',
                "User-Agent" => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/94.0.4606.61 Safari/537.36 Edg/94.0.992.31',
                "Accept" => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
                "Sec-Fetch-Site" => 'none',
                "Sec-Fetch-Mode" => 'navigate',
                "Sec-Fetch-User" => '?1',
                "Sec-Fetch-Dest" => 'document',
                "Accept-Language" => 'zh-CN,zh;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6',
            ]
        ]);
        $body = $response->getBody()->getContents();
        $dom = new Dom;
        $dom->loadStr($body);
        $list = $dom->find('#sales .group a.item,#sales .group .item a');
        $result = [];
        foreach ($list as $item) {
            $href = $item->getAttribute('href');
            if (!$href) continue;
            $query_str = parse_url($href, PHP_URL_QUERY);
            parse_str($query_str, $query_params);
            if (!isset($query_params['productid'])) continue;
            $response = $this->searchApi([
                "productid" => $query_params["productid"],
                "shopid" => $query_params["shopid"],
                "shopuuid" => $query_params["shopuuid"]
            ]);
            if (!$response) continue;

            $result[] = $response;
        }
        return $result;

    }


    public static function test()
    {
        $info = HospitalInfo::query()
            ->first();

        $client = new static($info);
        $client->search();
    }

}