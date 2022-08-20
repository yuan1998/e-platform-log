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

    public $timeout = 0;

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
                Log::info('发生错误', [
                    'code' => $statusCode,
                    'msg' => $exception->getMessage(),
                ]);
                if ($statusCode === 403) {
                    $this->release(60 * 50);
                }
            } catch (\Exception $exception) {
                $statusCode = $exception->getCode();
                Log::info('发生错误', [
                    'code' => $statusCode,
                    'msg' => $exception->getMessage(),
                ]);
                if ($statusCode === 500) {
                    $this->release(60 * 3);
                }
            }

        }


    }

    /**
     * The job failed to process.
     *
     * @param Exception $exception
     * @return void
     */
    public function failed(Exception $exception)
    {
        Log::info('发生错误', [
            'code' => $exception->getCode(),
            'msg' => $exception->getMessage(),
            'hospital' => $this->hospitalInfo,
            'type' => $this->type,
        ]);

        // Send user notification of failure, etc...
    }
}
