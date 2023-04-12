<?php

namespace App\Clients;


use App\Jobs\DaZhongDetailJob;
use App\Jobs\TestJob;
use App\Models\Category;
use App\Models\HospitalInfo;
use App\Models\Product;
use Campo\UserAgent;
use Carbon\Carbon;
use Illuminate\Bus\Batch;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use PHPHtmlParser\Dom;

class DaZhongClient extends BaseClient
{

    public function getProductDetailApiCurl($query, $proxy = null)
    {
        $curl = curl_init();

        $options = [
            CURLOPT_URL => 'https://mapi.dianping.com/dzbook/prepayproductdetail.json2?' . $query,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Connection: keep-alive',
                'sec-ch-ua: "Chromium";v="94", "Microsoft Edge";v="94", ";Not A Brand";v="99"',
                'sec-ch-ua-mobile: ?0',
                'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/94.0.4606.61 Safari/537.36 Edg/94.0.992.31',
                'sec-ch-ua-platform: "macOS"',
                'Accept: */*',
                'Origin: https://www.dianping.com',
                'Sec-Fetch-Site: same-site',
                'Sec-Fetch-Mode: cors',
                'Sec-Fetch-Dest: empty',
                'Referer: https://www.dianping.com/',
                'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6',
                'Cookie: _hc.v=875208b5-2930-7808-4d4c-d9f669c627ea.1681205216; _lxsdk=1811901b903c8-04038c9409d822-11473c11-384000-1811901b903c8; _lxsdk_cuid=1811901b903c8-04038c9409d822-11473c11-384000-1811901b903c8; _lxsdk_s=1877462ae98-fd1-5f-17d%7C%7C1; aburl=1; cy=4; cye=guangzhou; dper=fec56066b8820f35d610008edcbe2a4f41bb24466fedd7ef5dd9b4a1015c6d4d4eca85cbad17d1388ff5f27350c119d39f0983f3e6874ad0d9b28562dd57d6a1; fspop=test; ll=7fd06e815b796be3df069dec7836c3df; m_flash2=1; pvhistory="6L+U5ZuePjo8L3N1Z2dlc3QvZ2V0SnNvbkRhdGE/ZGV2aWNlX3N5c3RlbT1BTkRST0lEJnlvZGFSZWFkeT1oNT46PDE2ODEyMDU3NTQ1NTZdX1s="; qruuid=630359ed-9527-4d9b-9a24-0c7d0899da17; WEBDFPID=yux665w8259w5wu6yv6zv081y8x990328123uz6x8xx979581301767v-1996565215624-1681205215624AOMUCQU75613c134b6a252faa6802015be905513004; s_ViewType=10'
            ),
        ];
        curl_setopt_array($curl, $options);
        if ($proxy)
            curl_setopt($curl, CURLOPT_PROXY, "$proxy");

        $response = curl_exec($curl);

        curl_close($curl);
        return $response;
    }


    public function getProductDetailApi($data = null)
    {
        $data = array_merge([
            "platform" => "android",
            "channel" => "dp",
            "clienttype" => "m",
            "productid" => "3827144",
            "shopid" => "97497914",
            "shopuuid" => "l7LpxQ7ByObBbXls",
            "cityid" => "4",
            "token" => "",
        ], $data);
        $query = http_build_query($data);

        $retryCount = 30;
        $break = false;
        $proxy = null;
        while ($retryCount > 0 && !$break) {
            try {
                $proxy = ProxyClient::getProxy();
                $content = $this->getProductDetailApiCurl($query , $proxy);
                $result = json_decode($content, true);
            } catch (\Exception $exception) {
                $result = null;
                $content = $exception->getMessage();
            }
            if (!data_get($result, 'data.productItems.0.name')) {
                if ($proxy)
                    ProxyClient::deleteProxy($proxy);
                else
                    $break = true;

                Log::info('>>>大众.getProductDetailApi', [
                    'retry' => $retryCount,
                    'proxy' => $proxy,
                    'content' => $content,
                ]);
                $retryCount--;
            } else {
                Log::info('>>>大众.getProductDetailApi.OK');
                return $result;
            }
        }
        throw new \Exception("大众.getProductDetailApi 失败");
    }

    public function searchApi($data)
    {
        $result = self::getProductDetailApi($data);
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
            $price = str_replace("￥", '', $price);

            $originPrice = @$item->find('.lineThrough')->innerText ?? "";
            $originPrice = str_replace("￥", '', $originPrice);

            $sale = @$item->find('.tuanSale .sold')->innerText ?? "0";
            $sale = str_replace("已售", '', $sale);

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

    public function listParse($list)
    {
        $hospitalId = $this->hospital->id;
        $type = $this->type;
        $date = $this->date;
        $batch = Bus::batch([])
            ->finally(function (Batch $batch) use ($type, $date, $hospitalId) {
                $data = Cache::get($batch->id, []);
                Log::debug("finally", [
                    $hospitalId,
                    $date,
                    $type,
                    $batch->id,
                    count($data)
                ]);
                HospitalInfo::storeProducts($hospitalId, $data, $date, $type);
                Cache::forget($batch->id);
            })
            ->onQueue('da_zhong_detail');
        foreach ($list as $item) {
            $href = $item->getAttribute('href');
            if (!$href) continue;
            $query_str = parse_url($href, PHP_URL_QUERY);
            parse_str($query_str, $query_params);
            if (!isset($query_params['productid'])) continue;

            $productid = $query_params["productid"];
            Log::debug("job {$productid}");
            $job = new DaZhongDetailJob($hospitalId,
                [
                    "productid" => $productid,
                    "shopid" => $query_params["shopid"],
                    "shopuuid" => $query_params["shopuuid"],
                ]);

            $batch->add([
                $job
            ]);
        }
        $batch->dispatch();
    }

    public function getHospitalHomeApi()
    {
        $retryCount = 30;
        $break = false;
        while ($retryCount > 0 && !$break) {

            $proxy = null;
            $config = [
                'headers' => [
                    "Connection" => 'keep-alive',
                    "Cache-Control" => 'max-age=0',
                    "sec-ch-ua" => '"Chromium";v="94", "Microsoft Edge";v="94", ";Not A Brand";v="99"',
                    "sec-ch-ua-mobile" => '?0',
                    "sec-ch-ua-platform" => '"macOS"',
                    "Upgrade-Insecure-Requests" => '1',
                    "Accept" => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
                    "Sec-Fetch-Site" => 'none',
                    "Sec-Fetch-Mode" => 'navigate',
                    "Sec-Fetch-User" => '?1',
                    "Sec-Fetch-Dest" => 'document',
                    "Accept-Language" => 'zh-CN,zh;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6',
                    "Cookie" => 'dper=e195f4431767b32d312692082052cc03e1caa7c47a85556521aa31b08761989c4e3fc05cbb005cdd66a7883d8ffb7113dd456fd2a4c0486c3c3ebccf5562368f'
                ]
            ];
            try {
                if ($proxy = ProxyClient::getProxy())
                    $config['proxy'] = "http://$proxy";
                $response = $this->get($this->hospital->dz_url, $config);
                $body = $response->getBody()->getContents();
            } catch (\Exception $exception) {
                $body = null;
            }

            if (!$body || preg_match("/验证中心/", $body)) {
                Log::info('大众 Api', [
                    '$retryCount' => $retryCount,
                    'hospital' => $this->hospital->name,
                    'result' => $body,
                ]);

                if ($proxy)
                    ProxyClient::deleteProxy($proxy);
                else
                    $break = true;
                $retryCount--;
            } else {
                return $body;
            }
        }

        Log::info('大众.拉取数据错误,进入验证', [
            'name' => $this->hospital->name
        ]);
        throw new \Exception('拉取数据错误,进入验证', 500);
    }

    public function search()
    {
        $t1 = microtime(true);
        Log::info('1.>>>>大众.拉取', [
            'name' => $this->hospital->name
        ]);

        $body = $this->getHospitalHomeApi();
        if (!$body) return null;

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
