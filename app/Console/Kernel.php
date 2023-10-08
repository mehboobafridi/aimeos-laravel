<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Http\Controllers\OrdersController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();
        $schedule->call(function () {

            try {
                $OrdersController = new OrdersController();
                $request = new Request(); // Create a new Request object
                $OrdersController->RequestReport($request);
                
                Log::info('RequestReport() executed successfully');
            } catch (\Throwable $e) {
                Log::error('RequestReport() failed: ' . $e->getMessage());
            }
        })->everyTwoHours();


        $schedule->call(function () {

            try {
                $OrdersController = new OrdersController();
                $request = new Request(); // Create a new Request object
                $OrdersController->DownloadOrders($request);

                Log::info('DownloadOrders() executer successfully');
            } catch (\Throwable $e) {
                Log::error('DownloadOrders() failed: ' . $e->getMessage());
            }
        })->everyTenMinutes();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
