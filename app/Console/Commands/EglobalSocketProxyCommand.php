<?php

namespace App\Console\Commands;

use App;
use Illuminate\Console\Command;
use App\Classes\Pagos\Procesadores\Eglobal\SocketServerProxy;
use Ratchet\Server\IoServer;
use React\EventLoop\Factory as LoopFactory;

class EglobalSocketProxyCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'eglobalproxyserver';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Inicia servidor proxy de eGlobal';

    /*
     * @var array $aConfig Configuración de servicio
     */
    protected $aConfig;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        // Carga configuración de servidores dependiendo del ambiente
        $this->sEnv = App::environment();
        $this->aConfig = config('claropagos.' . $this->sEnv . '.procesadores_pago.eglobal');
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // Inicia pruebas
        $this->info(" Iniciando socket server...");

        // Revisa sistemas
        try {
            // Crea servidor
            $oLoop = LoopFactory::create();
            $server = IoServer::factory(new SocketServerProxy($oLoop), $this->aConfig['proxy']['puerto']);
            // Agrega timer
            $server->loop->addPeriodicTimer($this->aConfig['keepalive'] / 3, function () use ($server) {
                $server->app->keepalive();
            });
            // Corre servidor
            $server->run();
        } catch (\Exception $e) {
            $this->error("Error en el server:" . $e->getMessage());
            return 2;
        }
        // Termina
        return 0;
    }
}
