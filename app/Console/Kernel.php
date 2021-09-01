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
        Commands\DeleteExpiredActivations::class,
        Commands\DailyQuote::class,
        Commands\CheckDomains::class,
        Commands\AddNewDomain::class,
        Commands\WebsitePicker::class,
        Commands\WebsitePicker23::class,
        Commands\WebsitePicker24::class,
        Commands\WebsitePicker31::class,
        Commands\WebsitePicker34::class,
        Commands\WebsitePicker35::class,
        Commands\WebsitePicker40::class,
        Commands\WebsitePicker42::class,
        Commands\WebsitePicker44::class,
        Commands\WebsitePicker48::class,
        Commands\WebsitePicker49::class,
        Commands\WebsitePicker55::class,
        Commands\WebsitePicker56::class,
        Commands\WebsitePicker62::class,
        Commands\WebsitePicker63::class,
        Commands\WebsitePicker64::class,
        Commands\WebsitePicker65::class,
        Commands\WebsitePicker68::class,
        Commands\WebsitePicker72::class,
        Commands\WebsitePicker73::class,
        Commands\WebsitePicker74::class,
        Commands\WebsitePicker85::class,
        Commands\WebsitePicker88::class,
        Commands\WebsitePicker91::class,
        Commands\WebsitePicker99::class,
        Commands\WebsitePicker100::class,

    ];

    /**
     * Define the application's command schedule.
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     *
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();
        $schedule->command('quote:everyMinute')->everyMinute();
        $schedule->command('quote:addNewDomain')->everyMinute();
        $schedule->command('quote:checkDomains')->everyMinute();
        $schedule->command('quote:websitePicker')->everyMinute();
        $schedule->command('activations:clean')->daily();
        $schedule->command('quote:websitePicker23')->everyMinute();
        $schedule->command('quote:websitePicker24')->everyMinute();
        $schedule->command('quote:websitePicker31')->everyMinute();
        $schedule->command('quote:websitePicker34')->everyMinute();
        $schedule->command('quote:websitePicker35')->everyMinute();
        $schedule->command('quote:websitePicker40')->everyMinute();
        $schedule->command('quote:websitePicker42')->everyMinute();
        $schedule->command('quote:websitePicker44')->everyMinute();
        $schedule->command('quote:websitePicker48')->everyMinute();
        $schedule->command('quote:websitePicker49')->everyMinute();
        $schedule->command('quote:websitePicker55')->everyMinute();
        $schedule->command('quote:websitePicker56')->everyMinute();
        $schedule->command('quote:websitePicker62')->everyMinute();
        $schedule->command('quote:websitePicker63')->everyMinute();
        $schedule->command('quote:websitePicker64')->everyMinute();
        $schedule->command('quote:websitePicker65')->everyMinute();
        $schedule->command('quote:websitePicker68')->everyMinute();
        $schedule->command('quote:websitePicker72')->everyMinute();
        $schedule->command('quote:websitePicker73')->everyMinute();
        $schedule->command('quote:websitePicker74')->everyMinute();
        $schedule->command('quote:websitePicker85')->everyMinute();
        $schedule->command('quote:websitePicker88')->everyMinute();
        $schedule->command('quote:websitePicker91')->everyMinute();
        $schedule->command('quote:websitePicker99')->everyMinute();
        $schedule->command('quote:websitePicker100')->everyMinute();
   
    
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}
