<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Classes\Sistema\HealthCheck;

class HealthcheckCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'healthcheck';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verifica la instalación e integridad del sistema.';

    /**
     * @var HealthCheck Objeto HealthCheck para pruebas del sistema
     */
    protected $oHealthCheck;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(HealthCheck $oHealthCheck)
    {
        parent::__construct();
        // Asigna objetos
        $this->oHealthCheck = $oHealthCheck;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // Inicia pruebas
        $this->info(" Realizando pruebas de integridad de la aplicación...");

        // Revisa sistemas
        try {
            // Obtiene pruebas a realizar
            $aPruebas = $this->oHealthCheck->getPruebasDisponibles();
            // Barra de progreso
            $oProgressBar = $this->output->createProgressBar(count($aPruebas));
            $oProgressBar->setFormat("   %current%/%max% [%bar%] %percent:3s%% %message%");
            $oProgressBar->setMessage("Iniciando pruebas...");
            $oProgressBar->start();
            // Realiza pruebas
            $aResultados = [];
            foreach($aPruebas as $aPrueba) {
                // Revisa base de datos
                $oProgressBar->setMessage("Revisando " . $aPrueba['titulo']);
                // Realiza prueba
                $aResultado = $this->oHealthCheck->{$aPrueba['metodo']}();
                $aResultados[] = [
                    $aPrueba['titulo'],
                    $aResultado['resultado'] ? '<info>OK</info>' : '<fg=red;options=reverse>Error</>',
                    $aResultado['mensaje'],
                ];
                // Avanza barra de progreso
                $oProgressBar->advance();
            }
            $oProgressBar->setMessage("Pruebas terminadas");
            $oProgressBar->finish();
            $this->info("");
            $this->table(['Sistema', 'Resultado', 'Mensaje'], $aResultados);
        } catch (\Exception $e) {
            $this->error("Error al validar el sistema:" . $e->getMessage());
            return 2;
        }
        // Termina
        return 0;
    }
}