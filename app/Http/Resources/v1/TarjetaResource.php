<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Resources\Json\JsonResource;

class TarjetaResource extends JsonResource
{

    /**
     * Regresa arreglo con mapa de etiquetas.
     *
     * @return array
     */
    public static function labelMap(): array
    {
        return [
            'uuid' => 'token',
            'cliente_uuid' => 'cliente_id',
            'created_at' => 'creacion',
            'updated_at' => 'actualizacion'
        ];
    }

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        return  array_replace_keys(
            array_replace(
                array_except(parent::toArray($request), [
                    'nombres',
                    'apellido_paterno',
                    'apellido_materno',
                    'deleted_at',
                    'inicio_mes',
                    'inicio_anio',
                ]),
                [
                    'created_at' => $this->created_at->toRfc3339String(),
                    'updated_at' => $this->updated_at->toRfc3339String(),
                ]
            ),
            $this->labelMap()
        );
    }
}
