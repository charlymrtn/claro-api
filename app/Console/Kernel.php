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
        // Comando de pruebas unitarias
        Commands\TestappCommand::class,
        // Comando de pruebas de sistema
        Commands\HealthcheckCommand::class,
        // Servidor de sockets
        Commands\EglobalSocketServerMockCommand::class,
        // BBVA Socket Proxy
        Commands\EglobalSocketProxyCommand::class,
        // Comando para otimizar reemplazando el anterior de laravel 5.5
        Commands\OptimizeCommand::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')
        //          ->hourly();
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        require base_path('routes/console.php');
    }
}
