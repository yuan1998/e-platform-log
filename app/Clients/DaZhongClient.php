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
    public $ua;

    public static function searchApi($data = [], $hospitalId, $hospitalName)
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

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://mapi.dianping.com/dzbook/prepayproductdetail.json2?$query",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_NOSIGNAL => false,
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
                'Cookie: edper=SwhkYbM0IrbKx839Pt96PUCasaw035D-8DDN1pKW_yunyIvci4esbN07uGEyZLBCXbyJNnqqzU-wkpvlAkxSpg; mpmerchant_portal_shopid=97902493; _hc.v=4d56775b-7b3e-77f9-9e5a-9a1eaaa33d7b.1678790762; _lxsdk_cuid=186f375a013c8-08d0b94389c4e1-7c342e3c-76aa0-186f375a013c8; _lxsdk=186f375a013c8-08d0b94389c4e1-7c342e3c-76aa0-186f375a013c8; WEBDFPID=z038zy603u695970y21x2u4uuzz21784813u624z53u97958uz430uw7-1994481818745-1679121817811AOMMCWO75613c134b6a252faa6802015be905511934; cityid=4; default_ab=citylist%3AA%3A1%7CshopList%3AC%3A5%7Cugcdetail%3AA%3A1; pvhistory="6L+U5ZuePjo8L3N1Z2dlc3QvZ2V0SnNvbkRhdGE/ZGV2aWNlX3N5c3RlbT1BTkRST0lEJnlvZGFSZWFkeT1oNT46PDE2NzkxMjIwMzUyOTldX1s="; cy=4; cye=guangzhou; s_ViewType=10; dper=3ff84894edbae22cc586e54a326da06b60ada5a7d99dc16db09bfcc89f2fa40e0d2c5f9b65087ff8e2d447d89bf78926de57cfae1f035689aecc3cecf47703b4; ll=7fd06e815b796be3df069dec7836c3df; s_ViewType=10'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

//        $response = $this->get('https://mapi.dianping.com/dzbook/prepayproductdetail.json2', [
//            'query' => $data,
//            'headers' => [
//                "User-Agent" => $this->ua,
//                'Connection' => 'keep-alive',
//                'sec-ch-ua' => '"Chromium";v="94", "Microsoft Edge";v="94", ";Not A Brand";v="99"',
//                'sec-ch-ua-mobile' => '?0',
//                'sec-ch-ua-platform' => '"macOS"',
//                'Accept' => '*/*',
//                'Origin' => 'https://www.dianping.com',
//                'Sec-Fetch-Site' => 'same-site',
//                'Sec-Fetch-Mode' => 'cors',
//                'Sec-Fetch-Dest' => 'empty',
//                'Referer' => 'https://www.dianping.com/',
//                'Accept-Language' => 'zh-CN,zh;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6',
//                'Cookie' => 'edper=SwhkYbM0IrbKx839Pt96PUCasaw035D-8DDN1pKW_yunyIvci4esbN07uGEyZLBCXbyJNnqqzU-wkpvlAkxSpg; mpmerchant_portal_shopid=97902493; _hc.v=4d56775b-7b3e-77f9-9e5a-9a1eaaa33d7b.1678790762; _lxsdk_cuid=186f375a013c8-08d0b94389c4e1-7c342e3c-76aa0-186f375a013c8; _lxsdk=186f375a013c8-08d0b94389c4e1-7c342e3c-76aa0-186f375a013c8; WEBDFPID=z038zy603u695970y21x2u4uuzz21784813u624z53u97958uz430uw7-1994481818745-1679121817811AOMMCWO75613c134b6a252faa6802015be905511934; cityid=4; default_ab=citylist%3AA%3A1%7CshopList%3AC%3A5%7Cugcdetail%3AA%3A1; pvhistory="6L+U5ZuePjo8L3N1Z2dlc3QvZ2V0SnNvbkRhdGE/ZGV2aWNlX3N5c3RlbT1BTkRST0lEJnlvZGFSZWFkeT1oNT46PDE2NzkxMjIwMzUyOTldX1s="; cy=4; cye=guangzhou; s_ViewType=10; dper=3ff84894edbae22cc586e54a326da06b60ada5a7d99dc16db09bfcc89f2fa40e0d2c5f9b65087ff8e2d447d89bf78926de57cfae1f035689aecc3cecf47703b4; ll=7fd06e815b796be3df069dec7836c3df; s_ViewType=10'
//            ]
//        ]);
//        $content = $response->getBody()->getContents();
        $result = json_decode($response, true);
        $title = data_get($result, 'data.productItems.0.name');
        if (!$title) {
            Log::info('>>>>>>>>>>>>>大众.获取商品详情出错', [
                'name' => $hospitalName,
                'content' => $response,
                'data' => $data
            ]);
            return null;
        }
        $r = [
            'origin_id' => data_get($result, 'data.productItems.0.id'),
            'name' => $title,
            "hospital_id" => $hospitalId,
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

            Log::debug("job {$query_params["productid"]}");
            $job = new DaZhongDetailJob($hospitalId, $this->hospital->name, $query_params["productid"], $query_params["shopid"], $query_params["shopuuid"]);

            $batch->add([
                $job
            ]);
        }
        $batch->dispatch();

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
