<?php

namespace App\Jobs;

use App\Models\HospitalInfo;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

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
        if ($this->hospitalInfo)
            $this->hospitalInfo->getProducts($this->type, $this->date);


    }
}
