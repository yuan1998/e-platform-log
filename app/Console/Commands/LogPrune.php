<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class LogPrune extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'log:prune';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean log files';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        $logPath = storage_path('logs/');
        $files = File::glob($logPath . '*.log');

        foreach ($files as $file) {
            $lastModified = File::lastModified($file);
            $daysOld = floor((time() - $lastModified) / (60 * 60 * 24));
            if ($daysOld > 5) {
                File::delete($file);
            } else {
                $fileSize = File::size($file);
                if ($fileSize > 10006555) {
                    File::delete($file);
                }
            }
        }
        $this->info("Clean complete");
    }
}
