<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class OptimizeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'optimize';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Optimiza la aplicación.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // Inicia pruebas
        $this->info(" Optimizando la aplicación...");

        // Optimiza sistemas
        try {
            // Barra de progreso
            $oProgressBar = $this->output->createProgressBar(1);
            $oProgressBar->setFormat("   %current%/%max% [%bar%] %percent:3s%% %message%");
            $oProgressBar->setMessage("Iniciando optimizaciones...");
            $oProgressBar->start();
            // Optimizaciones
            $oProgressBar->advance();
            // Finaliza
            $oProgressBar->setMessage("Optimización terminada");
            $oProgressBar->finish();
            $this->info("");
        } catch (\Exception $e) {
            $this->error("Error al optimizar el sistema:" . $e->getMessage());
            return 2;
        }
        // Termina
        return 0;
    }
}