<?php

namespace App\Classes\Pagos\Procesadores\Prosa;

use Log;
use Exception;
use GuzzleHttp\Client;

/*
 * Clase para el manejo de la API de Feenicia (Venta manual)
 */

class VentaManualService
{

    protected $bPrueba = false;

    /**
     * Variables internas
     */
    protected $merchantId;
    protected $reqIv;
    protected $reqKey;
    protected $reqSignIv;
    protected $reqSignKey;
    protected $respIv;
    protected $respKey;
    protected $respSignIv;
    protected $respSignKey;
    protected $affilation;
    protected $userId;
    protected $password;
    protected $host1;
    protected $host2;

    public function __construct(bool $prueba = true)
    {
        // Define si es prueba
        $this->bPrueba = $prueba;

        // Datos de prueba
        if ($prueba){
            $this->merchantId = "0000000000000012";
            $this->reqIv = "b35d01d060a5799cf0777a084437fa16";
            $this->reqKey = "7f24e5aa156cc44ae90f4dda9b3e04f1";
            $this->reqSignIv = "a77a225cf5b51821c709a13eb923208e";
            $this->reqSignKey = "f334a13790a9cf8e38a3cfd7962e7b2e";
            $this->respIv = "15d3e5bccbafdd42e2d4b092d198019a";
            $this->respKey = "95df48f17abcbdac56c9a74863eb8acf";
            $this->respSignIv = "251bbca2b91e954e133385ec2eef035d";
            $this->respSignKey = "2da544cd92b462acf8f8c91ee8d5fa6a";
            $this->affilation = "9165713";
            $this->userId = "jmosco1";
            $this->password = "1Qazxsw2..";
            $this->client = new Client;
            $this->host1 = "http://54.203.245.137:10080/atna";
            $this->host2 = "http://54.203.245.137";
        } else {
            $this->merchantId = "0000000000001447";
            $this->reqIv = "f44cbc421a5b393b47be205f81316ae9";
            $this->reqKey = "194ccc64f9e71707ded0c2ab7e2985d7";
            $this->reqSignIv = "0d6d0c7e180b06a84a219bbee21c35ae";
            $this->reqSignKey = "d51ba497efa121c2341391b042d0d2ae";
            $this->respIv = "ca4b81633e89c43f0c936032ba9eaa3b";
            $this->respKey = "96fed2bc91753d8729fb8bb89b705ed7";
            $this->respSignIv = "55419b35d53067f83aab9b29f5f59e3d";
            $this->respSignKey = "831cb1e24b5e3d0c16d748c50f4e5ae3";
            $this->affilation = "7372820";
            $this->userId = "CLAROSHOPCOM";
            //todo: este dato no viene en el correo
            $this->password = "1Qazxsw2..";
            $this->client = new Client;
            $this->host1 = "https://www.feenicia.com/atena-swa-services-0.1";
            $this->host2 = "https://www.feenicia.com";
        }
    }

    /**
     * Genera la firma a enviar en la cabecera (x-requested-with)
     *
     * @param string $req String de JSON a cifrar
     *
     * @return string
     */
    public function generarFirma(string $req)
    {
        // Hashea a sha256
        $hash = hash('sha256', $req);

        // Decodifica a cadena binaria las llaves correspondientes que estan codificadas hexadecimalmente
        $reqSignKey = hex2bin($this->reqSignKey);
        $reqSignIv = hex2bin($this->reqSignIv);

        // Cifra en AES el hash anterior y lo decodifica hexadecimalmente
        $aes = bin2hex(openssl_encrypt($hash, 'AES-128-CBC', $reqSignKey, OPENSSL_RAW_DATA, $reqSignIv));

        // Retorna la firma con el merchant concatenado
        return $this->merchantId."_".$aes;
    }

    /**
     * Cifra algun string en AES-128-CBC
     *
     * @param string $word
     *
     * @return string
     */
    public function cifrarAES($word)
    {
        // Decodifica a cadena binaria las llaves correspondientes que estan codificadas hexadecimalmente
        $reqKey = hex2bin($this->reqKey);
        $reqIv = hex2bin($this->reqIv);

        // Cifra en AES el string y lo decodifica hexadecimalmente
        $aes = bin2hex(openssl_encrypt($word, 'AES-128-CBC', $reqKey, OPENSSL_RAW_DATA, $reqIv));

        //Retorna el cifrado
        return $aes;
    }

    /**
     * Genera orden de venta
     *
     * @param float $amount Monto total de la venta
     * @param array $items Arreglo de los productos de la venta
     * @param bool $test Variable que indica si en una prueba al método
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function generateOrderSale(float $amount, array $productos, bool $test = false)
    {
        // Genera json con datos de parametros recibidos y otras llaves
        $request = json_encode([
            "amount" => $amount,
            "items" => $productos,
            "merchant" => $this->merchantId,
            "userId" => $this->userId,
        ]);
        // Declara ruta de api
        $url = ($test) ? "http://jsonplaceholder.typicode.com/posts" : $this->host2."/receipt/order/create";

        // Obtiene Firma (x-requested-with)
        $firma = $this->generarFirma($request);

        // Envia petición
        $res = $this->client->request('POST', $url, [
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'x-requested-with' => $firma,
            ],
            'body' => $request,
        ]);

        // Retorna la respuesta de la peticion en JSON
        return response()->json(json_decode($res->getBody()));
    }

    /**
     * Genera venta manual
     *
     * @param float $amount Monto de la venta
     * @param int $transactionDate Fecha Unix en milisegundos de cuando se genero la orden de venta
     * @param int $orderId Id que se obtiene de la función generateOrderSale()
     * @param int $pan Numero de la tarjeta
     * @param string $cardHoldName Nombre de propietario de tarjeta
     * @param string $cvv2 CVV de la tarjeta
     * @param string $expDate Fecha de expiración de la tarjeta (YYMM)
     * @param bool $test Variable que indica si en una prueba al método
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function generateManualSale(float $amount, int $transactionDate, int $orderId, int $pan, string $cardHoldName, string $cvv2, string $expDate, bool $test = false)
    {
        // Genera json con datos de parametros recibidos y otras llaves
        $request = json_encode([
            "affiliation" => $this->affilation,
            "amount" => $amount,
            "transactionDate" => $transactionDate,
            "orderId" => $orderId,
            "tip" => 0,
            "pan" => $this->cifrarAES($pan),
            "cardholderName" => $this->cifrarAES($cardHoldName),
            "cvv2" => $this->cifrarAES($cvv2),
            "expDate" => $this->cifrarAES($expDate),
        ]);

        // Declara ruta de api
        $url = ($test) ? "http://jsonplaceholder.typicode.com/posts" : $this->host1."/sale/manual";

        // Obtiene Firma (x-requested-with)
        $firma = $this->generarFirma($request);

        // Envia petición
        $res = $this->client->request('POST', $url, [
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'x-requested-with' => $firma,
            ],
            'body' => $request,
        ]);

        // Retorna la respuesta de la peticion en JSON
        return response()->json(json_decode($res->getBody()));
    }

    /**
     * Guarda Venta
     *
     * @param string $orderId Orden de venta obtenida de la func generateOrderSale()
     * @param int $transactionId Número único para identificar la transacción en SERTI obtenida de la func generateManualSale()
     * @param string $authNum Número de autorización por el banco obtenida de la func generateManualSale()
     * @param string $transactionDate Fecha de cuando se accede a este servicio
     * @param string $panTermination Terminacion 4 digitos de la tarjeta
     * @param bool $test Variable que indica si en una prueba al método
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function generateSaveSale(string $orderId, int $transactionId, string $authNum, string $transactionDate, string $panTermination, bool $test = false)
    {
        // Genera json con datos de parametros recibidos y otras llaves
        $request = json_encode([
            "orderId" => $orderId,
            "transactionId" => $transactionId,
            "authnum" => $authNum,
            "transactionDate" => $transactionDate,
            "panTermination" => $panTermination,
            "affiliation" => $this->affilation,
            "merchant" => $this->merchantId,
        ]);
        // Declara ruta de api
        $url = ($test) ? "http://jsonplaceholder.typicode.com/posts" : $this->host2."/receipt/signature/save";

        // Obtiene Firma (x-requested-with)
        $firma = $this->generarFirma($request);

        // Envia petición
        $res = $this->client->request('POST', $url, [
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'x-requested-with' => $firma,
            ],
            'body' => $request,
        ]);

        // Retorna la respuesta de la peticion en JSON
        return response()->json(json_decode($res->getBody()));
    }

    /**
     * Crea recibo de cobro
     *
     * @param string $orderId Orden de venta obtenida de la func generateOrderSale()
     * @param int $transactionId Número único para identificar la transacción en SERTI obtenida de la func generateManualSale()
     * @param float $total Total de la venta
     * @param float $propina Propina
     * @param bool $test Variable que indica si en una prueba al método
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function createReceipt(string $orderId, int $transactionId, float $total, float $propina, bool $test = false)
    {
        // Genera json con datos de parametros recibidos
        $request = json_encode([
            "OrderId" => $orderId,
            "TransactionId" => $transactionId,
            "Total" => $total,
            "LegalEntityName" => null,
            "MerchantStreetNumColony" => null,
            "MerchantCityStateZipCode" => null,
            "AffiliationId" => null,
            "LastDigitsCard" => null,
            "Base64ImgSignature" => null,
            "AuthNumber" => null,
            "OperationId" => null,
            "ControlNumber" => null,
            "NameInCard" => null,
            "DescriptionCard" => null,
            // todo: Esta fecha me imagino es del momento que se crea el recibo, pero esta bien la sintaxis?
            "ReceiptDateTime" => "0001-01-01T00:00:00",
            "AID" => null,
            "ARQC" => null,
            "MensajeComercio" => null,
            "ClientLogoBase64Data" => null,
            "ClientLogoDataType" => null,
            "SendUrlByMail" => false,
            "Propina" => $propina,
            "strMerchantId" => null,
        ]);

        // Declara ruta de api
        $url = ($test) ? "http://jsonplaceholder.typicode.com/posts" : $this->host2."/receipt/receipt/CreateReceipt";

        // Obtiene Firma (x-requested-with)
        $firma = $this->generarFirma($request);

        // Envia petición
        $res = $this->client->request('POST', $url, [
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'x-requested-with' => $firma,
            ],
            'body' => $request,
        ]);

        // Retorna la respuesta de la peticion en JSON
        return response()->json(json_decode($res->getBody()));
    }

    /**
     * Envia recibo de cobro a cliente
     *
     * @param string $receipId Identificador con el que SERTI creo el recibo de cobro obtenido de la func createReceipt()
     * @param string $email Correo de cliente
     * @param bool $test Variable que indica si en una prueba al método
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendReceipt(string $receipId, string $email, bool $test = false)
    {
        // Genera json con datos de parametros recibidos
        $request = json_encode([
            "receiptId" => $receipId,
            "Email" => [$this->cifrarAES($email)],
        ]);

        // Declara ruta de api
        $url = ($test) ? "http://jsonplaceholder.typicode.com/posts" : $this->host2."/receipt/receipt/SendReceipt";

        // Obtiene Firma (x-requested-with)
        $firma = $this->generarFirma($request);

        // Envia petición
        $res = $this->client->request('POST', $url, [
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'x-requested-with' => $firma,
            ],
            'body' => $request,
        ]);

        // Retorna la respuesta de la peticion en JSON
        return response()->json(json_decode($res->getBody()));
    }

    /**
     * Genera venta
     *
     * @param float $amount Monto total de la venta
     * @param array $productos Arreglo de los productos de la venta
     * @param int $pan Numero de la tarjeta
     * @param string $cardHoldName Nombre de propietario de tarjeta
     * @param string $cvv CVV de la tarjeta
     * @param string $expDate Fecha de expiración de la tarjeta (YYMM)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendTransaction(float $amount, array $productos, int $pan, string $cardHoldName, string $cvv, string $expDate){

        date_default_timezone_set("America/Mexico_City");

        $panTermination = substr(((string) $pan), -4);
        $milliSecDate = (int) (round(microtime(true) * 1000));
        $date = date('Y-m-d H:i');

        try {
            // Generacion de orden de venta *******************************************************
            $ordenVenta = $this->generateOrderSale($amount, $productos);
            $response = $ordenVenta->getData();
            $respCode = $response->responseCode;
            //dump($response);
            // Verifica si el responseCode sea exitoso
            if ($respCode != "00") {
                Log::error('Error en response de la orden de venta. Response: '.json_encode($response));

                return response()->json([
                    "status" => "fail",
                    "data" => [
                        "message" => "No se puede generar la orden de venta.",
                        "response_code" => $respCode,
                        "prueba" => $this->bPrueba,
                    ],
                ]);
            }
            // Si es correcto el response del servicio, se obtiene variables a ocupar en otros servicios
            $orderId = $ordenVenta->getData()->orderId;
        } catch (Exception $e) {
            Log::error('Error on '.__METHOD__.' line '.__LINE__.':'.$e->getMessage());

            return response()->json([
                "status" => "fail",
                "data" => [
                    "message" => "No se puede generar la orden de venta. Error: ".$e->getMessage(),
                    "response_code" => "Error interno claro pagos",
                    "prueba" => $this->bPrueba,
                ],
            ]);
        }

        try {
            // Generacion de venta *******************************************************
            $venta = $this->generateManualSale($amount, $milliSecDate, $orderId, $pan, $cardHoldName, $cvv, $expDate);
            $response = $venta->getData();
            $respCode = $response->responseCode;
            //dump($response);
            // Verifica si el responseCode sea exitoso
            if ($respCode != "00") {
                Log::error('Error en response de la realización de la venta. Response: '.json_encode($response));

                return response()->json([
                    "status" => "fail",
                    "data" => [
                        "message" => "No se puede realizar la venta.",
                        "response_code" => $respCode,
                        "prueba" => $this->bPrueba,
                    ],
                ]);
            }
            // Si es correcto el response del servicio, se obtiene variables a ocupar en otros servicios
            $transactionId = $response->transactionId;
            $authNum = $response->authnum;
        } catch (Exception $e) {
            Log::error('Error on '.__METHOD__.' line '.__LINE__.':'.$e->getMessage());

            return response()->json([
                "status" => "fail",
                "data" => [
                    "message" => "No se puede realizar la venta. Error: ".$e->getMessage(),
                    "response_code" => "Error interno claro pagos",
                    "prueba" => $this->bPrueba,
                ],
            ]);
        }

        try {
            // Generacion de guardado de venta *******************************************************
            $saveSale = $this->generateSaveSale($orderId, $transactionId, $authNum, $date, $panTermination);
            $response = $saveSale->getData();
            $respCode = $response->responseCode;
            //dump($response);
            // Verifica si el responseCode sea exitoso
            if ($respCode != "00") {
                Log::error('Error en response del guardado de la venta. Response: '.json_encode($response));

                return response()->json([
                    "status" => "fail",
                    "data" => [
                        "message" => "No se puede guardar la venta.",
                        "response_code" => $respCode,
                        "prueba" => $this->bPrueba,
                    ],
                ]);
            }
        } catch (Exception $e) {
            Log::error('Error on '.__METHOD__.' line '.__LINE__.':'.$e->getMessage());

            return response()->json([
                "status" => "fail",
                "data" => [
                    "message" => "No se puede guardar la venta. Error: ".$e->getMessage(),
                    "response_code" => "Error interno claro pagos",
                    "prueba" => $this->bPrueba,
                ],
            ]);
        }

        // Venta realizada corectamente
        $importantData = [
            "orderId" => $orderId,
            "authNum" => $authNum,
            "transactionId" => $transactionId,
        ];
        Log::info('Venta realizada correctamente. : '.json_encode($importantData));

        return response()->json([
            "status" => "success",
            "data" => [
                "message" => "Venta generada correctamente",
                "response_code" => $respCode,
                "importantData" => $importantData,
                "prueba" => $this->bPrueba,
            ],
        ]);
    }


    /**
     * Funcion principal que consulta todos los servicios para una venta manual
     *
     * @param float $amount Monto total de la venta
     * @param array $productos Arreglo de los productos de la venta
     * @param int $pan Numero de la tarjeta
     * @param string $cardHoldName Nombre de propietario de tarjeta
     * @param string $cvv CVV de la tarjeta
     * @param string $expDate Fecha de expiración de la tarjeta (YYMM)
     * @param string $email Correo de cliente
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function saleMain(float $amount, array $productos, int $pan, string $cardHoldName, string $cvv, string $expDate, string $email){

        date_default_timezone_set("America/Mexico_City");

        $panTermination = substr(((string) $pan), -4);
        $milliSecDate = (int) (round(microtime(true) * 1000));
        $date = date('Y-m-d H:i');

        try {
            // Generacion de orden de venta *******************************************************
            $ordenVenta = $this->generateOrderSale($amount, $productos);
            $response = $ordenVenta->getData();
            $respCode = $response->responseCode;
            //dump($response);
            // Verifica si el responseCode sea exitoso
            if ($respCode != "00") {
                Log::error('Error en response de la orden de venta. Response: '.json_encode($response));

                return response()->json([
                    "status" => "fail",
                    "data" => [
                        "message" => "No se puede generar la orden de venta.",
                        "response_code" => $respCode,
                        "prueba" => $this->bPrueba,
                    ],
                ]);
            }
            // Si es correcto el response del servicio, se obtiene variables a ocupar en otros servicios
            $orderId = $ordenVenta->getData()->orderId;
        } catch (Exception $e) {
            Log::error('Error on '.__METHOD__.' line '.__LINE__.':'.$e->getMessage());

            return response()->json([
                "status" => "fail",
                "data" => [
                    "message" => "No se puede generar la orden de venta. Error: ".$e->getMessage(),
                    "response_code" => "Error interno claro pagos",
                    "prueba" => $this->bPrueba,
                ],
            ]);
        }

        try {
            // Generacion de venta *******************************************************
            $venta = $this->generateManualSale($amount, $milliSecDate, $orderId, $pan, $cardHoldName, $cvv, $expDate);
            $response = $venta->getData();
            $respCode = $response->responseCode;
            //dump($response);
            // Verifica si el responseCode sea exitoso
            if ($respCode != "00") {
                Log::error('Error en response de la realización de la venta. Response: '.json_encode($response));

                return response()->json([
                    "status" => "fail",
                    "data" => [
                        "message" => "No se puede realizar la venta.",
                        "response_code" => $respCode,
                        "prueba" => $this->bPrueba,
                    ],
                ]);
            }
            // Si es correcto el response del servicio, se obtiene variables a ocupar en otros servicios
            $transactionId = $response->transactionId;
            $authNum = $response->authnum;
        } catch (Exception $e) {
            Log::error('Error on '.__METHOD__.' line '.__LINE__.':'.$e->getMessage());

            return response()->json([
                "status" => "fail",
                "data" => [
                    "message" => "No se puede realizar la venta. Error: ".$e->getMessage(),
                    "response_code" => "Error interno claro pagos",
                    "prueba" => $this->bPrueba,
                ],
            ]);
        }

        try {
            // Generacion de guardado de venta *******************************************************
            $saveSale = $this->generateSaveSale($orderId, $transactionId, $authNum, $date, $panTermination);
            $response = $saveSale->getData();
            $respCode = $response->responseCode;
            //dump($response);
            // Verifica si el responseCode sea exitoso
            if ($respCode != "00") {
                Log::error('Error en response del guardado de la venta. Response: '.json_encode($response));

                return response()->json([
                    "status" => "fail",
                    "data" => [
                        "message" => "No se puede guardar la venta.",
                        "response_code" => $respCode,
                        "prueba" => $this->bPrueba,
                    ],
                ]);
            }
        } catch (Exception $e) {
            Log::error('Error on '.__METHOD__.' line '.__LINE__.':'.$e->getMessage());

            return response()->json([
                "status" => "fail",
                "data" => [
                    "message" => "No se puede guardar la venta. Error: ".$e->getMessage(),
                    "response_code" => "Error interno claro pagos",
                    "prueba" => $this->bPrueba,
                ],
            ]);
        }

        try {
            // Creacion de recibo de pago *******************************************************
            $createReceipt = $this->createReceipt($orderId, $transactionId, $amount,0.0);
            $response = $createReceipt->getData();
            $respCode = $response->responseCode;
            //dump($response);
            // Verifica si el responseCode sea exitoso
            if ($respCode != "00") {
                Log::error('Error en response al crear el recibo de la venta. Response: '.json_encode($response));

                return response()->json([
                    "status" => "fail",
                    "data" => [
                        "message" => "No se puede crear el recibo de la venta.",
                        "response_code" => $respCode,
                        "prueba" => $this->bPrueba,
                    ],
                ]);
            }
            // Si es correcto el response del servicio, se obtiene variables a ocupar en otros servicios
            $receipId = $response->receiptId;
        } catch (Exception $e) {
            Log::error('Error on '.__METHOD__.' line '.__LINE__.':'.$e->getMessage());

            return response()->json([
                "status" => "fail",
                "data" => [
                    "message" => "No se puede crear el recibo de la venta. Error: ".$e->getMessage(),
                    "response_code" => "Error interno claro pagos",
                    "prueba" => $this->bPrueba,
                ],
            ]);
        }

        try {
            // Envio de recibo de pago a Cliente *******************************************************
            $sendReceipt = $this->sendReceipt($receipId, $email);
            $response = $sendReceipt->getData();
            $respCode = $response->responseCode;
            // dump($response);
            // Verifica si el responseCode sea exitoso
            if ($respCode != "00") {
                Log::error('Error en response al enviar el recibo de la venta. Response: '.json_encode($response));

                return response()->json([
                    "status" => "fail",
                    "data" => [
                        "message" => "No se puede enviar el recibo de la venta.",
                        "response_code" => $respCode,
                        "prueba" => $this->bPrueba,
                    ],
                ]);
            }

            // Venta realizada corectamente
            $importantData = [
                "orderId" => $orderId,
                "authNum" => $authNum,
                "transactionId" => $transactionId,
            ];
            Log::info('Venta realizada correctamente. : '.json_encode($importantData));

            return response()->json([
                "status" => "success",
                "data" => [
                    "message" => "Venta generada correctamente",
                    "response_code" => $respCode,
                    "importantData" => $importantData,
                    "prueba" => $this->bPrueba,
                ],
            ]);
        } catch (Exception $e) {
            Log::error('Error on '.__METHOD__.' line '.__LINE__.':'.$e->getMessage());

            return response()->json([
                "status" => "fail",
                "data" => [
                    "message" => "No se puede enviar el recibo de la venta. Error: ".$e->getMessage(),
                    "response_code" => "Error interno claro pagos",
                    "prueba" => $this->bPrueba,
                ],
            ]);
        }
    }

    /**
     * Reembolso de una venta
     *
     * @param float $amount
     * @param string $orderId
     * @param string $pan
     * @param string $cardHoldName
     * @param string $cvv
     * @param string $expDate
     * @param string $authNum
     * @param string $transactionId
     * @param bool $test Variable que indica si en una prueba al método
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refund(float $amount, string $orderId, string $pan, string $cardHoldName, string $cvv, string $expDate, string $authNum, string $transactionId, bool $test = false)
    {
        date_default_timezone_set("America/Mexico_City");
        $milliSecDate = (int) (round(microtime(true) * 1000));

        try {
            // Genera json con datos de parametros recibidos y otras llaves
            $request = json_encode([
                "affiliation" => $this->affilation,
                "amount" => $amount,
                "transactionDate" => $milliSecDate,
                "orderId" => $orderId,
                "pan" => $this->cifrarAES($pan),
                "cardholderName" => $this->cifrarAES($cardHoldName),
                //"cvv2" => $this->cifrarAES($cvv),
                "expDate" => $this->cifrarAES($expDate),
                "authnum" => $authNum,
                "transactionId" => $transactionId,
            ]);

            // Declara ruta de api
            $url = ($test) ? "http://jsonplaceholder.typicode.com/posts" : $this->host1."/refund";

            // Obtiene Firma (x-requested-with)
            $firma = $this->generarFirma($request);

            // Envia petición
            $res = $this->client->request('POST', $url, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'x-requested-with' => $firma,
                ],
                'body' => $request,
            ]);

            // Obtiene response
            $response = json_decode($res->getBody());
            $respCode = $response->responseCode;

            // Verifica si el responseCode sea exitoso
            if ($respCode != "00") {
                Log::error('Error en response al hacer Refund. Response: '.json_encode($response));

                return response()->json([
                    "status" => "fail",
                    "data" => [
                        "message" => "No se puede realizar el Refund.",
                        "response_code" => $respCode,
                    ],
                ]);
            } else {
                Log::info('Refund realizado correctamente. : '.json_encode($response));

                return response()->json([
                    "status" => "success",
                    "data" => [
                        "message" => "Refund realizado correctamente",
                        "response_code" => $respCode,
                    ],
                ]);
            }
        } catch (Exception $e) {
            Log::error('Error on '.__METHOD__.' line '.__LINE__.':'.$e->getMessage());

            return response()->json([
                "status" => "fail",
                "data" => [
                    "message" => "No se puede realizar el refund. Error: ".$e->getMessage(),
                    "response_code" => "Error interno claro pagos",
                ],
            ]);
        }
    }

    /**
     * Reversal de una venta
     *
     * @param float $amount
     * @param string $orderId
     * @param string $pan
     * @param string $cvv
     * @param string $expDate
     * @param string $cardHoldName
     * @param string $authNum
     * @param string $transactionId
     * @param bool $test Variable que indica si en una prueba al método
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function reversal(float $amount, string $orderId, string $pan, string $cvv, string $expDate, string $cardHoldName, string $authNum, string $transactionId, bool $test = false)
    {
        date_default_timezone_set("America/Mexico_City");
        $milliSecDate = (int) (round(microtime(true) * 1000));
        try {
            // Genera json con datos de parametros recibidos y otras llaves
            $request = json_encode([
                "affiliation" => $this->affilation,
                "amount" => $amount,
                "transactionDate" => $milliSecDate,
                "orderId" => $orderId,
                "pan" => $this->cifrarAES($pan),
                //"cvv2" => $this->cifrarAES($cvv),
                "expDate" => $this->cifrarAES($expDate),
                "cardholderName" => $this->cifrarAES($cardHoldName),
                "authnum" => $authNum,
                "transactionId" => $transactionId,
            ]);

            // Declara ruta de api
            $url = ($test) ? "http://jsonplaceholder.typicode.com/posts" : $this->host1."/reversal";

            // Obtiene Firma (x-requested-with)
            $firma = $this->generarFirma($request);

            // Envia petición
            $res = $this->client->request('POST', $url, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'x-requested-with' => $firma,
                ],
                'body' => $request,
            ]);

            // Obtiene response
            $response = json_decode($res->getBody());
            $respCode = $response->responseCode;

            // Verifica si el responseCode sea exitoso
            if ($respCode != "00") {
                Log::error('Error en response al hacer Reversal. Response: '.json_encode($response));

                return response()->json([
                    "status" => "fail",
                    "data" => [
                        "message" => "No se puede realizar el Reversal.",
                        "response_code" => $respCode,
                    ],
                ]);
            } else {
                Log::info('Reversal realizado correctamente. : '.json_encode($response));

                return response()->json([
                    "status" => "success",
                    "data" => [
                        "message" => "Reversal realizado correctamente",
                        "response_code" => $respCode,
                    ],
                ]);
            }
        } catch (Exception $e) {
            Log::error('Error on '.__METHOD__.' line '.__LINE__.':'.$e->getMessage());

            return response()->json([
                "status" => "fail",
                "data" => [
                    "message" => "No se puede realizar el Reversal. Error: ".$e->getMessage(),
                    "response_code" => "Error interno claro pagos",
                ],
            ]);
        }
    }

    /**
     * Cancelación de una venta
     *
     * @param string $authNum
     * @param string $transactionId
     * @param string $pan
     * @param string $cvv
     * @param string $expDate
     * @param float $amount
     * @param string $cardHoldName
     * @param string $orderId
     * @param bool $test Variable que indica si en una prueba al método
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancel(string $authNum, string $transactionId, string $pan, string $cvv, string $expDate, float $amount, string $cardHoldName, string $orderId, bool $test = false)
    {
        try {
            date_default_timezone_set("America/Mexico_City");
            $milliSecDate = (int) (round(microtime(true) * 1000));

            // Genera json con datos de parametros recibidos y otras llaves
            $request = json_encode([
                "affiliation" => $this->affilation,
                "authnum" => $authNum,
                "transactionId" => $transactionId,
                "pan" => $this->cifrarAES($pan),
                //"cvv2" => $this->cifrarAES($cvv),
                "expDate" => $this->cifrarAES($expDate),
                "amount" => $amount,
                "cardholderName" => $this->cifrarAES($cardHoldName),
                "orderId" => $orderId,
                "transactionDate" => $milliSecDate,
            ]);

            // Declara ruta de api
            $url = ($test) ? "http://jsonplaceholder.typicode.com/posts" : $this->host1."/cancel/manual";

            // Obtiene Firma (x-requested-with)
            $firma = $this->generarFirma($request);

            // Envia petición
            $res = $this->client->request('POST', $url, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'x-requested-with' => $firma,
                ],
                'body' => $request,
            ]);

            // Obtiene response
            $response = json_decode($res->getBody());
            $respCode = $response->responseCode;

            // Verifica si el responseCode sea exitoso
            if ($respCode != "00") {
                Log::error('Error en response al hacer Cancel. Response: '.json_encode($response));

                return response()->json([
                    "status" => "fail",
                    "data" => [
                        "message" => "No se puede realizar el Cancel.",
                        "response_code" => $respCode,
                    ],
                ]);
            } else {
                Log::info('Cancel realizado correctamente. : '.json_encode($response));

                return response()->json([
                    "status" => "success",
                    "data" => [
                        "message" => "Cancel realizado correctamente",
                        "response_code" => $respCode,
                    ],
                ]);
            }
        } catch (Exception $e) {
            Log::error('Error on '.__METHOD__.' line '.__LINE__.':'.$e->getMessage());

            return response()->json([
                "status" => "fail",
                "data" => [
                    "message" => "No se puede realizar el Cancel. Error: ".$e->getMessage(),
                    "response_code" => "Error interno claro pagos",
                ],
            ]);
        }
    }
}
