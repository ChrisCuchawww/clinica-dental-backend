<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterPacienteRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'nombre'    => 'required|string|max:100',
            'telefono'  => 'required|string|max:20',
            'correo'    => 'required|email|unique:users,email',
            'password'  => 'required|string|min:8|confirmed',
        ];
    }

    public function messages(): array
    {
        return [
            'correo.required'   => 'El correo es obligatorio.',
            'correo.unique'     => 'Este correo ya está registrado.',
            'password.min'      => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed'=> 'Las contraseñas no coinciden.',
        ];
    }
}
