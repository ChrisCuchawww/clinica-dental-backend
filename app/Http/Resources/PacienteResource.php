<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PacienteResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'           => $this->id,
            'nombre'       => $this->nombre,
            'telefono'     => $this->telefono,
            'correo'       => $this->correo,
            'notas'        => $this->notas,
            'activo'       => (bool) $this->activo,
            'citas_count'  => $this->citas_count ?? $this->whenLoaded('citas', fn() => $this->citas->count(), 0),
            'created_at'   => $this->created_at,
            'ultima_visita' => $this->whenLoaded('citas', function () {
                return $this->citas
                    ->where('estado', 'completada')
                    ->where('fecha', '<=', now()->toDateString())
                    ->sortByDesc('fecha')
                    ->first()
                    ?->fecha;
            }),
        ];
    }
}
