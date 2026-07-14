<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateServicioRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'nombre'      => 'sometimes|required|string|max:100',
            'precio'      => 'sometimes|required|numeric|min:0',
            'duracion'    => 'sometimes|required|integer|min:15',
            'categoria'   => 'sometimes|nullable|string|max:50',
            'descripcion' => 'sometimes|nullable|string',
            'activo'      => 'sometimes|boolean',
        ];
    }
}
