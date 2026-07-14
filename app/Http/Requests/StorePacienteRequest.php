<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePacienteRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $pacienteId = $this->route('paciente')?->id;
        $esEdicion  = $this->isMethod('PUT') || $this->isMethod('PATCH');

        return [
            'nombre'                => 'required|string|max:100',
            'telefono'              => 'required|string|max:20',
            'correo'                => 'nullable|email|max:100',
            'notas'                 => 'nullable|string',
            'password'              => $esEdicion ? 'nullable|string|min:8' : 'required|string|min:8',
            'password_confirmation' => $esEdicion ? 'nullable|same:password' : 'required|same:password',
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required'               => 'El nombre es obligatorio.',
            'telefono.required'             => 'El teléfono es obligatorio.',
            'correo.email'                  => 'El correo no tiene un formato válido.',
            'password.required'             => 'La contraseña es obligatoria.',
            'password.min'                  => 'La contraseña debe tener al menos 8 caracteres.',
            'password_confirmation.required'=> 'Debes confirmar la contraseña.',
            'password_confirmation.same'    => 'Las contraseñas no coinciden.',
        ];
    }
}
