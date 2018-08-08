<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Models\Medios\Tarjeta;
use App\Http\Resources\v1\TarjetaResource;

class TarjetaCollectionResource extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $this->collection->transform(function (Tarjeta $cliente) {
            return (new TarjetaResource($cliente));
        });

        return [
            'data' => $this->getCollection(),
            // Metadatos
            'registros_por_pagina' => $this->perPage(),
            'pagina_actual' => $this->currentPage(),
            'desde' => $this->firstItem(),
            'hasta' => $this->lastItem(),
            'total' => $this->total(),
            'total_pagina' => $this->count(),
            'ultima_pagina' => $this->lastPage(),
        ];
    }
}
