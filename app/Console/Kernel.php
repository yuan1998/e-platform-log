<?php

namespace App\Console;

use App\Models\HospitalInfo;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();

        $schedule->call(function () {
            Log::info('定期运行');
            $date = Carbon::yesterday()->toDateString();
            HospitalInfo::pullAll(null, true, $date);
        })->dailyAt("00:05");
//        $schedule->call(function () {
//            Log::info('定期运行 : 11:50');
//            $date = Carbon::today()->toDateString();
//            HospitalInfo::pullAll(null, true, $date);
//        })->dailyAt("11:50");
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
