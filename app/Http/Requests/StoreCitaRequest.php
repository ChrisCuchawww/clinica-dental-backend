<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCitaRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'paciente_id'       => 'required|exists:pacientes,id',
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
            'paciente_id.required'    => 'El paciente es obligatorio.',
            'paciente_id.exists'      => 'El paciente no existe.',
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
