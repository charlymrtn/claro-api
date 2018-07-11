<?php

namespace app\Http\Controllers\API\v1;

use Log;
use Exception;
use Validator;
use Webpatser\Uuid\Uuid;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Classes\Pagos\Parametros\PeticionCargo;
use App\Classes\Pagos\Base\Contacto;
use App\Classes\Pagos\Base\Pedido;
use App\Classes\Pagos\Base\Direccion;
use App\Classes\Pagos\Base\Telefono;
use App\Classes\Pagos\Medios\TarjetaCredito;
use App\Classes\Pagos\Procesos\Cargo;

class CargoController extends Controller
{

    protected $oCargo;

    /**
     * Crea nueva instancia.
     *
     * @return void
     */
    public function __construct(Cargo $cargo)
    {
        $this->oCargo = $cargo;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $oRequest)
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $oRequest)
    {

         // Valida datos de entrada
        $oValidator = Validator::make($oRequest->toArray(), [
            'comercio_uuid' => 'required|string',
            'descripcion' => 'max:250',
            'prueba' => 'boolean',
            'monto' => 'required',
            'parcialidades' => 'numeric|min:0|max:48',
            // TARJETA 'tarjeta' => 'required|array',
                'tarjeta.pan' => 'required|numeric',
                'tarjeta.nombre' => 'required|min:3|max:60',
                'tarjeta.cvv2' => 'required|numeric',
                'tarjeta.expiracion_mes' => 'required|numeric',
                'tarjeta.expiracion_anio' => 'required|numeric',
                'tarjeta.inicio_mes' => 'numeric',
                'tarjeta.inicio_anio' => 'numeric',
                'tarjeta.nombres' => 'required_without:tarjeta.nombre|min:3|max:30',
                'tarjeta.apellido_paterno' => 'required_without:tarjeta.nombre|min:3|max:30',
                'tarjeta.apellido_materno' => 'required_without:tarjeta.nombre|min:3|max:30',
                'tarjeta.direccion' => 'array',
            // PEDIDO 'pedido' => 'required|array',
                'pedido.id' => 'max:48',
                'pedido.direccion_envio' => 'array',
                'pedido.articulos' => 'numeric',
            // CLIENTE 'cliente' => 'required|array',
                'cliente.id' => 'string',
                'cliente.nombre' => 'min:3|max:30',
                'cliente.apellido_paterno' => 'min:3|max:30',
                'cliente.apellido_materno' => 'min:3|max:30',
                'cliente.email' => 'email',
                'cliente.telefono' => 'string',
                'cliente.direccion' => 'array',
                'cliente.creacion' => 'date',
        ]);
        if ($oValidator->fails()) {
            $sCode = '400';
            Log::error('Error de validación de parámetros: ' . json_encode($oValidator->errors()));
            return ejsend_fail(['code' => $sCode, 'type' => 'Parámetros', 'message' => 'Error en parámetros de entrada.'], $sCode, ['errors' => $oValidator->errors()]);
        }

        // Formatea y encapsula datos en PeticionCargo
        try {
            $oPeticionCargo = new PeticionCargo([
                'comercio_uuid' => $oRequest->input('comercio_uuid'),
                'prueba' => $oRequest->input('prueba', true),
                'descripcion' => $oRequest->input('descripcion', ''),
                'monto' => $oRequest->input('monto', '0.00'),
                'puntos' => $oRequest->input('puntos', 0),
                'parcialidades' => $oRequest->input('parcialidades', 0),
                'diferido' => $oRequest->input('diferido', 0),
                'tarjeta' => new TarjetaCredito([
                    'pan' => $oRequest->input('tarjeta.pan'),
                    'nombre' => $oRequest->input('tarjeta.nombre'),
                    'cvv2' => $oRequest->input('tarjeta.cvv2'),
                    'expiracion_mes' => $oRequest->input('tarjeta.expiracion_mes'),
                    'expiracion_anio' => $oRequest->input('tarjeta.expiracion_anio'),
                ]),
                'pedido' => new Pedido([
                    'id' => $oRequest->input('pedido.id', 1),
                    'articulos' => $oRequest->input('pedido.articulos', 1),
                    'peso' => $oRequest->input('pedido.peso', 0),
                    'total' => $oRequest->input('pedido.total', $oRequest->input('monto', '0.00')),
                    'direccion_envio' => new Direccion([
                        'pais' => $oRequest->input('pedido.direccion.pais', 'MEX'),
                        'estado' => $oRequest->input('pedido.direccion.estado', 'CMX'),
                        'ciudad' => $oRequest->input('pedido.direccion.ciudad', 'CDMX'),
                        'municipio' => $oRequest->input('pedido.direccion.municipio', 'Delegación'),
                        'linea1' => $oRequest->input('pedido.direccion.linea1', ''),
                        'linea2' => $oRequest->input('pedido.direccion.linea2', ''),
                        'linea3' => $oRequest->input('pedido.direccion.linea3', ''),
                        'cp' => $oRequest->input('pedido.direccion.cp', '0000'),
                        'telefono' => new Telefono([
                            'tipo' => $oRequest->input('pedido.direccion.telefono.tipo', 'desconocido'),
                            'codigo_pais' => $oRequest->input('pedido.direccion.telefono.codigo_pais', '52'),
                            'codigo_area' => $oRequest->input('pedido.direccion.telefono.codigo_area', '55'),
                            'numero' => $oRequest->input('pedido.direccion.telefono', '0000000000'),
                            'extension' => $oRequest->input('pedido.direccion.extension', null),
                        ]),
                    ]),
                ]),
                'direccion_cargo' => new Direccion([
                    'pais' => $oRequest->input('pedido.direccion.pais', 'MEX'),
                    'estado' => $oRequest->input('pedido.direccion.estado', 'CMX'),
                    'ciudad' => $oRequest->input('pedido.direccion.ciudad', 'CDMX'),
                    'municipio' => $oRequest->input('pedido.direccion.municipio', 'Delegación'),
                    'linea1' => $oRequest->input('pedido.direccion.linea1', ''),
                    'linea2' => $oRequest->input('pedido.direccion.linea2', ''),
                    'linea3' => $oRequest->input('pedido.direccion.linea3', ''),
                    'cp' => $oRequest->input('pedido.direccion.cp', '0000'),
                    'telefono' => new Telefono([
                        'tipo' => $oRequest->input('pedido.direccion.telefono.tipo', 'desconocido'),
                        'codigo_pais' => $oRequest->input('pedido.direccion.telefono.codigo_pais', '52'),
                        'codigo_area' => $oRequest->input('pedido.direccion.telefono.codigo_area', '55'),
                        'numero' => $oRequest->input('pedido.direccion.telefono', '0000000000'),
                        'extension' => $oRequest->input('pedido.direccion.extension', null),
                    ]),
                ]),
                'cliente' => new Contacto([
                    'id' => $oRequest->input('cliente.id', 0),
                    'nombre' => $oRequest->input('cliente.nombre'),
                    'apellido_paterno' => $oRequest->input('cliente.apellido_paterno'),
                    'apellido_materno' => $oRequest->input('cliente.apellido_materno'),
                    'genero' => $oRequest->input('cliente.genero', 'Desconocido'),
                    'email' => $oRequest->input('cliente.email'),
                    'telefono' => new Telefono([
                        'tipo' => $oRequest->input('cliente.telefono.tipo', 'desconocido'),
                        'codigo_pais' => $oRequest->input('cliente.telefono.codigo_pais', '52'),
                        'codigo_area' => $oRequest->input('cliente.telefono.codigo_area', '55'),
                        'numero' => $oRequest->input('cliente.telefono', '0000000000'),
                        'extension' => $oRequest->input('cliente.extension', null),
                    ]),
                    'nacimiento' => $oRequest->input('cliente.nacimiento', null),
                    'creacion' => $oRequest->input('cliente.creacion', null),
                    'cliente.direccion' => new Direccion([
                        'pais' => $oRequest->input('pedido.direccion.pais', 'MEX'),
                        'estado' => $oRequest->input('pedido.direccion.estado', 'CMX'),
                        'ciudad' => $oRequest->input('pedido.direccion.ciudad', 'CDMX'),
                        'municipio' => $oRequest->input('pedido.direccion.municipio', 'Delegación'),
                        'linea1' => $oRequest->input('pedido.direccion.linea1', ''),
                        'linea2' => $oRequest->input('pedido.direccion.linea2', ''),
                        'linea3' => $oRequest->input('pedido.direccion.linea3', ''),
                        'cp' => $oRequest->input('pedido.direccion.cp', '0000'),
                        'telefono' => new Telefono([
                            'tipo' => $oRequest->input('pedido.direccion.telefono.tipo', 'desconocido'),
                            'codigo_pais' => $oRequest->input('pedido.direccion.telefono.codigo_pais', '52'),
                            'codigo_area' => $oRequest->input('pedido.direccion.telefono.codigo_area', '55'),
                            'numero' => $oRequest->input('pedido.direccion.telefono', '0000000000'),
                            'extension' => $oRequest->input('pedido.direccion.extension', null),
                        ]),
                    ]),
                ]),
            ]);
        } catch (\Exception $e) {
            if (empty($e->getCode())) {
                $sCode = '400';
            } else {
                $sCode = $e->getCode();
            }
            Log::error('Error on ' . __METHOD__ . ' line ' . $e->getLine() . ':' . $e->getMessage());
            return ejsend_fail(['code' => $sCode, 'type' => 'Parámetros', 'message' => 'Error en parámetros de entrada.'], $sCode, ['errors' => $e->getMessage()]);
        }

        // Envía petición a proceso de cargo
        return $this->oCargo->carga($oPeticionCargo);
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show(int $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit(int $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, int $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(int $id)
    {
        //
    }
}
