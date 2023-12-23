<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('po:remindexpired')->daily()->runInBackground();
        $schedule->command('vendorevaluation:reminder')->daily()->runInBackground();
        $schedule->command('pomanualattachment:reminder')->daily()->runInBackground();
        $schedule->command('assetnumber:reminder')->daily()->runInBackground();
        $schedule->command('sap:refreshprtable')->everyTenMinutes()->runInBackground();
        $schedule->command('sap:refreshpotable')->everyTenMinutes()->runInBackground();
        $schedule->command('sap:refreshpoelogtable')->daily()->runInBackground();
        $schedule->command('barangjasait:reminder')->daily()->runInBackground();

        $schedule->call(function () {
            info('cron is activated every hour');
        })->hourly()->runInBackground();
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
