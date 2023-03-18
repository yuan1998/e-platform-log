<?php

namespace App\Clients;


use App\Models\Category;
use App\Models\HospitalInfo;
use App\Models\Product;
use Campo\UserAgent;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use PHPHtmlParser\Dom;

class DaZhongClient extends BaseClient
{

    public $ua;

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

        Log::info('2.2   >>>>>>>>大众.拉取:获取商品信息');
        $response = $this->get('https://mapi.dianping.com/dzbook/prepayproductdetail.json2', [
            'query' => $data,
            'headers' => [
                "Connection" => 'keep-alive',
                "Cache-Control" => 'max-age=0',
                "sec-ch-ua" => '"Chromium";v="94", "Microsoft Edge";v="94", ";Not A Brand";v="99"',
                "sec-ch-ua-mobile" => '?0',
                "sec-ch-ua-platform" => '"macOS"',
                "Upgrade-Insecure-Requests" => '1',
                "User-Agent" => $this->ua,
                "Accept" => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
                "Sec-Fetch-Site" => 'none',
                "Sec-Fetch-Mode" => 'navigate',
                "Sec-Fetch-User" => '?1',
                "Sec-Fetch-Dest" => 'document',
                "Accept-Language" => 'zh-CN,zh;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6',
                "Cookie" => 'dper=e195f4431767b32d312692082052cc03e1caa7c47a85556521aa31b08761989c4e3fc05cbb005cdd66a7883d8ffb7113dd456fd2a4c0486c3c3ebccf5562368f'
            ]
        ]);
        Log::info('2.3   >>>>>>>>大众.拉取:获取商品信息');
        $content = $response->getBody()->getContents();
        $result = json_decode($content, true);
        Log::info('2.4   >>>>>>>>大众.拉取:获取商品信息');
        $title = data_get($result, 'data.productItems.0.name');
        if (!$title) {
            Log::info('>>>>>>>>>>>>>大众.获取商品详情出错', [
                'name' => $this->hospital->name,
                'content' => $content,
                'data' => $data
            ]);
            return null;
        }
        Log::info('2.5   >>>>>>>>大众.拉取:获取商品信息');
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
        Log::info('2.6   >>>>>>>>大众.拉取:获取商品信息');
        return $r;

    }

    public function searchMobile()
    {
        $url = str_replace('www', 'm', $this->hospital->dz_url);
        $response = $this->get($url, [
            'headers' => [
                "Connection" => 'keep-alive',
                "Cache-Control" => 'max-age=0',
                "sec-ch-ua" => '"Chromium";v="94", "Microsoft Edge";v="94", ";Not A Brand";v="99"',
                "sec-ch-ua-mobile" => '?0',
                "sec-ch-ua-platform" => '"macOS"',
                "Upgrade-Insecure-Requests" => '1',
                "User-Agent" => $this->ua,
                "Accept" => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
                "Sec-Fetch-Site" => 'none',
                "Sec-Fetch-Mode" => 'navigate',
                "Sec-Fetch-User" => '?1',
                "Sec-Fetch-Dest" => 'document',
                "Accept-Language" => 'zh-CN,zh;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6',
                "Cookie" => 'dper=e195f4431767b32d312692082052cc03e1caa7c47a85556521aa31b08761989c4e3fc05cbb005cdd66a7883d8ffb7113dd456fd2a4c0486c3c3ebccf5562368f'

            ]
        ]);
        $body = $response->getBody()->getContents();

        $dom = new Dom;
        $dom->loadStr($body);
        $list = $dom->find('#newTuan .tuanItem');
        $result = [];

        $count = $list->count();
        Log::info('2.1>>>>大众.拉取移动端:获取商品数量', [
            'name' => $this->hospital->name,
            'count' => $count,
        ]);
        if (!$count) return $result;

        foreach ($list as $item) {
            $id = $item->getAttribute('data-id');
            if (!$id) continue;
            $title = $item->find('.tuanTitle')->innerText;
            $price = @$item->find('.tuanPrice')->innerText ?? "";
            $price = str_replace("￥" , '' ,$price);

            $originPrice = @$item->find('.lineThrough')->innerText ?? "";
            $originPrice = str_replace("￥" , '' ,$originPrice);

            $sale = @$item->find('.tuanSale .sold')->innerText ?? "0";
            $sale = str_replace("已售" , '' ,$sale);

            $r = [
                'origin_id' => $id,
                'name' => $title,
                "hospital_id" => $this->hospital->id,
                "platform_type" => HospitalInfo::DAZHONG_ID,
                "price" => $originPrice,
                "online_price" => $price,
                "sell" => $sale,
                "status" => Product::ONLINE_STATUS,
            ];

            if ($c_id = Category::validateKeyword($title)) {
                $r["category_id"] = $c_id;
            }

            $result[] = $r;
        }

        return $result;
    }

    public function listParse($list) {
        $result =[];
        foreach ($list as $item) {
            $href = $item->getAttribute('href');
            if (!$href) continue;
            $query_str = parse_url($href, PHP_URL_QUERY);
            parse_str($query_str, $query_params);
            if (!isset($query_params['productid'])) continue;
            Log::info('2.1   >>>>>>>>大众.拉取:获取商品信息', [$query_params["productid"]]);
            $response = $this->searchApi([
                "productid" => $query_params["productid"],
                "shopid" => $query_params["shopid"],
                "shopuuid" => $query_params["shopuuid"]
            ]);
            Log::info('2.7   >>>>>>>>大众.拉取:获取商品信息');
            if (!$response) continue;
            Log::info('2.8   >>>>>>>>大众.拉取:获取商品信息');
            $result[] = $response;
        }
        return $result;
    }

    public function search()
    {
        $t1 = microtime(true);
        $this->ua = UserAgent::random([
            'device_type' => 'Desktop',
        ]);
        Log::info('1.>>>>大众.拉取', [
            'name' => $this->hospital->name
        ]);
        $response = $this->get($this->hospital->dz_url, [
            'headers' => [
                "Connection" => 'keep-alive',
                "Cache-Control" => 'max-age=0',
                "sec-ch-ua" => '"Chromium";v="94", "Microsoft Edge";v="94", ";Not A Brand";v="99"',
                "sec-ch-ua-mobile" => '?0',
                "sec-ch-ua-platform" => '"macOS"',
                "Upgrade-Insecure-Requests" => '1',
                "User-Agent" => $this->ua,
                "Accept" => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
                "Sec-Fetch-Site" => 'none',
                "Sec-Fetch-Mode" => 'navigate',
                "Sec-Fetch-User" => '?1',
                "Sec-Fetch-Dest" => 'document',
                "Accept-Language" => 'zh-CN,zh;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6',
                "Cookie" => 'dper=e195f4431767b32d312692082052cc03e1caa7c47a85556521aa31b08761989c4e3fc05cbb005cdd66a7883d8ffb7113dd456fd2a4c0486c3c3ebccf5562368f'
            ]
        ]);
        $body = $response->getBody()->getContents();
        if (preg_match("/验证中心/", $body)) {
            Log::info('大众.拉取数据错误,进入验证', [
                'name' => $this->hospital->name
            ]);
            throw new \Exception('拉取数据错误,进入验证', 500);
        }

        $dom = new Dom;
        $dom->loadStr($body);
        $list = $dom->find('#sales .group a.item,#sales .group .item a');

        $count = $list->count();
        Log::info('2.>>>>大众.拉取:获取商品数量', [
            'name' => $this->hospital->name,
            'count' => $count,
        ]);
        $result = ($count === 0) ? $this->searchMobile() : $this->listParse($list);

        $t2 = microtime(true);
        Log::info('3.>>>>大众.拉取:结束', [
            'name' => $this->hospital->name,
            'time' => '耗时' . round($t2 - $t1, 3) . '秒',
        ]);
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
