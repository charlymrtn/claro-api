<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\v1\ClienteResource;
use App\Http\Resources\v1\PlanResource;

class SuscripcionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        // Define cambios
        $aCambios = [
            'created_at' => $this->created_at->toRfc3339String(),
            'updated_at' => $this->updated_at->toRfc3339String(),
            'plan' => PlanResource::collection($this->whenLoaded('plan')),
            'cliente' => ClienteResource::collection($this->whenLoaded('cliente')),
        ];
        foreach ($this->dates() as $sCampoDate) {
            if (!empty($this->$sCampoDate)) {
                $aCambios[$sCampoDate] = $this->$sCampoDate->toRfc3339String();
            }
        }
        return  array_replace_keys(
            array_replace(
                array_except(parent::toArray($request), ['deleted_at']),
                $aCambios
            ),
            [
                'uuid' => 'id',
                'created_at' => 'creacion',
                'updated_at' => 'actualizacion'
            ]
        );
    }
}
