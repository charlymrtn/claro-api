<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\v1\TarjetaResource;
use App\Http\Resources\v1\SuscripcionResource;

class ClienteResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return  array_replace_keys(
            array_replace(
                array_except(parent::toArray($request), ['deleted_at']),
                [
                    'created_at' => $this->created_at->toRfc3339String(),
                    'updated_at' => $this->updated_at->toRfc3339String(),
                    'tarjetas' => TarjetaResource::collection($this->whenLoaded('tarjetas')),
                    'suscripciones' => SuscripcionResource::collection($this->whenLoaded('suscripciones')),
                ]
            ),
            [
                'uuid' => 'id',
                'created_at' => 'creacion',
                'updated_at' => 'actualizacion'
            ]
        );
    }
}
