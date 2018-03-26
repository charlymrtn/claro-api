<?php

namespace App\Console\Commands;

use App;
use Illuminate\Console\Command;
use App\Classes\Pagos\Procesadores\Eglobal\SocketServerMock;
use Ratchet\Server\IoServer;
use React\EventLoop\Factory as LoopFactory;

class EglobalSocketServerMockCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'eglobalmockserver';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Inicia mock de servidor de sockets de eglobal.';

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
        $this->info(" Iniciando mock de servidor de sockets de eglobal...");

        // Revisa sistemas
        try {
            // Crea servidor
            $oLoop = LoopFactory::create();
            $server = IoServer::factory(new SocketServerMock($oLoop), $this->aConfig['puerto']);
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
