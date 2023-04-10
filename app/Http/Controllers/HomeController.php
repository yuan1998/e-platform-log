<?php

namespace App\Http\Controllers;

use App\Jobs\TestJob;
use Carbon\Carbon;
use Illuminate\Bus\Batch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

class HomeController extends Controller
{
    public function index()
    {
        return view('welcome');
    }

    public function test()
    {

        $batch = Bus::batch([])
            ->finally(function (Batch $batch) {
                $data = Cache::get($batch->id,[]);
                Cache::forget($batch->id);
                foreach ($data as $item) {
                    Log::debug("finally",[$item['id']]);
                }
                // The batch has finished executing...
            })->onQueue('my-queue');

        for ($i = 1; $i <= 3; $i++) {
            $batch->add([(new TestJob($i))->delay(Carbon::now()->addSeconds($i * 10))]);
        }

        $batch->dispatch();

//        dd(123);
    }
}
