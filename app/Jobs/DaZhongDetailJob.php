<?php

namespace App\Jobs;

use App\Clients\DaZhongClient;
use App\Models\HospitalInfo;
use Exception;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class DaZhongDetailJob implements ShouldQueue
{
    use Batchable,Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $timeout = 120;

    public $hospitalId;
    public $hospitalName;
    public $productId;
    public $shopId;
    public $shopUuid;

    /**
     * @param $hospitalId
     * @param $hospitalName
     * @param $productId
     * @param $shopId
     * @param $shopUuid
     */
    public function __construct($hospitalId, $hospitalName, $productId, $shopId, $shopUuid)
    {
        $this->hospitalId = $hospitalId;
        $this->hospitalName = $hospitalName;
        $this->productId = $productId;
        $this->shopId = $shopId;
        $this->shopUuid = $shopUuid;
    }

    public function handle() {
        try {
            Log::info('2.1   >>>>>>>>大众.拉取:获取商品信息', [$this->productId]);
            $data = Cache::get($this->batchId,[]);
            $data[] = DaZhongClient::searchApi([
                "productid" => $this->productId,
                "shopid" => $this->shopId,
                "shopuuid" => $this->shopUuid
            ],$this->hospitalId,$this->hospitalName);
            Cache::put($this->batchId , $data);
        }catch (Exception $exception) {
            $statusCode = $exception->getCode();

            Log::info('DaZhongDetailJob 发生错误123', [
                'code' => $statusCode,
                'msg' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString()
            ]);
            if ($statusCode === 500) {
                throw new Exception("错误");
            }
        }
        sleep(rand(1,5));

    }


}
