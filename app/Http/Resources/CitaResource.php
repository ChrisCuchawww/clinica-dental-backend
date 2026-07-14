<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CitaResource extends JsonResource
{
    public function toArray($request)
{
    return [
        'id'            => $this->id,
        'paciente'      => new PacienteResource($this->whenLoaded('paciente')),
        'servicios'     => ServicioResource::collection($this->whenLoaded('servicios')),
        'fecha'         => $this->fecha->format('Y-m-d'),
        'hora'          => $this->hora,
        'hora_fin'      => $this->hora_fin,
        'estado'        => $this->estado,
        'notas'         => $this->notas,
        'monto_pagado'  => $this->monto_pagado,
    ];
}
}
