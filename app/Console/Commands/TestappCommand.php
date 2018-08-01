<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TestappCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'testapp';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Corre las pruebas completas de la aplicación y genera el reporte de cobertura.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // Barra de progreso
        $oProgressBar = $this->output->createProgressBar(5);

        // Inicia pruebas
        $this->info(" Iniciando pruebas de la aplicación...");

        // Corre pruebas unitarias y genera reporte
        system('vendor/bin/phpunit');
        $oProgressBar->advance();
        $this->info(" Pruebas unitarias terminadas.");

        // Termina
        $oProgressBar->finish();
        $this->info(" Pruebas de la aplicación terminadas y reporte generado.");
        return 0;
    }
}