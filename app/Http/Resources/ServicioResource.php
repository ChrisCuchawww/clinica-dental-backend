<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ServicioResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'          => $this->id,
            'nombre'      => $this->nombre,
            'categoria'   => $this->categoria,
            'descripcion' => $this->descripcion,
            'precio'      => $this->precio,
            'duracion'    => $this->duracion, // en minutos
            'activo'      => $this->activo,
        ];
    }
}
