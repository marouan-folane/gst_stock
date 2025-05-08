<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

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
        // Check for expiring products daily at 8am
        $schedule->command('products:check-expiring')->dailyAt('08:00');
        
        // Check for low stock products daily at 9am
        $schedule->command('products:check-low-stock')->dailyAt('09:00');
        
        // Check for sensible products and low stock products every 30 minutes
        $schedule->command('check:sensible-products --force')
                ->everyThirtyMinutes();
        
        // Send stock email alerts every 30 minutes using SMTP
        $schedule->command('alerts:send-emails')
                ->everyThirtyMinutes();
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

    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\CheckExpiringProducts::class,
        Commands\CheckLowStockProducts::class,
        Commands\TestTwilioNotification::class,
        Commands\CheckSensibleProducts::class,
        Commands\SendStockEmailAlerts::class,
    ];
}
