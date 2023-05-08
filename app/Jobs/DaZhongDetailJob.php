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
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300;
    public $tries = 5;
    public $backoff = 10;

    public $hospitalId;
    public $productData;

    /**
     * @param $hospitalId
     * @param $productData
     */
    public function __construct($hospitalId, $productData)
    {
        $this->hospitalId = $hospitalId;
        $this->productData = $productData;
    }

    public function handle()
    {
        try {
            $hospital = HospitalInfo::find($this->hospitalId);
            $productId = $this->productData['productid'];
            if (!$hospital || !$productId) return;
            Log::info('2.1   >>>>>>>>大众.拉取:获取商品信息', [$productId]);
            $data = Cache::get($this->batchId, []);

            $client = new DaZhongClient($hospital);
            $result = $client->searchApi($this->productData);
            if ($result) {
                $data[] = $result;
                Cache::put($this->batchId, $data);
            }
        } catch (Exception $exception) {
            $statusCode = $exception->getCode();

            Log::info('DaZhongDetailJob 发生错误123', [
                'code' => $statusCode,
                'msg' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString()
            ]);
            if ($statusCode === 500) {
                throw new Exception("DaZhongDetailJob 错误");
            }
        }
        sleep(rand(20,30));
    }


}
