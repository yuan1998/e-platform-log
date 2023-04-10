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

class TestJob implements ShouldQueue
{
    use Batchable,Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 120;
    public $id;
//    public $delay = 10000;

    /**
     * @param $id
     */
    public function __construct($id)
    {
        $this->id = $id;
    }

    public function handle()
    {
        try {
            $data = Cache::get($this->batchId,[]);
            $data[] = [
                'id' =>$this->id
            ];
            Cache::put($this->batchId,$data);
        } catch (Exception $exception) {
            $statusCode = $exception->getCode();
            Log::info('发生错误', [
                'code' => $statusCode,
                'msg' => $exception->getMessage(),

            ]);
            if ($statusCode === 500) {
                throw new Exception("错误");
            }
        }

    }


}
