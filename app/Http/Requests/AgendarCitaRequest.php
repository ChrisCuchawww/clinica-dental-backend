<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AgendarCitaRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'servicios_ids'     => 'required|array|min:1',
            'servicios_ids.*'   => 'exists:servicios,id',
            'fecha'             => 'required|date|after_or_equal:today',
            'hora'              => 'required|date_format:H:i',
            'notas'             => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'servicios_ids.required'  => 'Selecciona al menos un servicio.',
            'servicios_ids.array'     => 'Los servicios deben ser una lista.',
            'servicios_ids.*.exists'  => 'Uno de los servicios seleccionados no existe.',
            'fecha.required'          => 'La fecha es obligatoria.',
            'fecha.after_or_equal'    => 'La fecha no puede ser en el pasado.',
            'hora.required'           => 'La hora es obligatoria.',
            'hora.date_format'        => 'La hora debe tener formato HH:MM.',
        ];
    }
}
