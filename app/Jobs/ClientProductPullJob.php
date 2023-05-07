<?php

namespace App\Jobs;

use App\Models\HospitalInfo;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ClientProductPullJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $id;
    public $timeout = 300;
    public $tries = 5;
    public $backoff = 10;

    public $hospitalInfo;
    public $date;
    public $type;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(HospitalInfo $hospitalInfo, $date, $type)
    {
        $this->hospitalInfo = $hospitalInfo;
        $this->date = $date;
        $this->type = $type;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->hospitalInfo) {
            try {
                Log::info('开始拉取', ['name' => $this->hospitalInfo->name, 'type' => $this->type]);
                 $this->hospitalInfo->getProducts($this->type, $this->date);
            } catch (\GuzzleHttp\Exception\ClientException $exception) {
                $response = $exception->getResponse();
                $statusCode = $response->getStatusCode();
                Log::error('ClientProductPullJob 发生错误', [
                    'code' => $statusCode,
                    'msg' => $exception->getMessage(),
                    'trace' => $exception->getTraceAsString()
                ]);
                if ($statusCode === 403) {
                    throw new Exception("请求错误");
                }
            } catch (\Exception $exception) {
                $statusCode = $exception->getCode();
                Log::error('ClientProductPullJob 发生错误', [
                    'code' => $statusCode,
                    'msg' => $exception->getMessage(),
                    'trace' => $exception->getTraceAsString()
                ]);
                if ($statusCode === 500) {
                    throw new Exception("错误");
                }
            }
            sleep(rand(20,50))
        }
    }

}
