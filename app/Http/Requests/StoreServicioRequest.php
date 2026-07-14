<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreServicioRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'nombre'      => 'required|string|max:100',
            'precio'      => 'required|numeric|min:0',
            'duracion'    => 'required|integer|min:15',
            'categoria'   => 'nullable|string|max:50',
            'descripcion' => 'nullable|string',
            'activo'      => 'boolean',
        ];
    }
}
