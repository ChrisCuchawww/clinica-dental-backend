<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterPacienteRequest;
use App\Models\Paciente;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // Registro de paciente
    public function register(RegisterPacienteRequest $request)
{
    $user = User::create([
        'name'     => $request->nombre,
        'email'    => $request->correo,
        'password' => Hash::make($request->password),
        'rol'      => 'paciente',  
    ]);

    $paciente = Paciente::create([
        'user_id'  => $user->id,
        'nombre'   => $request->nombre,
        'telefono' => $request->telefono,
        'correo'   => $request->correo,
    ]);

    $token = $user->createToken('auth_token')->plainTextToken;

    return response()->json([
        'token'    => $token,
        'user'     => [
            'id'     => $user->id,
            'nombre' => $user->name,
            'email'  => $user->email,
            'rol'    => $user->rol,
        ],
        'paciente' => [
            'id'       => $paciente->id,
            'nombre'   => $paciente->nombre,
            'telefono' => $paciente->telefono,
        ],
    ], 201);
}

    // Login (admin y paciente)
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'Credenciales incorrectas.'
            ], 401);
        }

        $user  = Auth::user();
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = [
            'token' => $token,
            'user'  => [
                'id'     => $user->id,
                'nombre' => $user->name,
                'email'  => $user->email,
                'rol'    => $user->rol,
            ],
        ];

        // Si es paciente, mandamos su info también
        if ($user->rol === 'paciente') {
            $response['paciente'] = $user->paciente;
        }

        return response()->json($response);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Sesión cerrada.']);
    }

    public function me(Request $request)
    {
        $user = $request->user();
        $response = [
            'id'     => $user->id,
            'nombre' => $user->name,
            'email'  => $user->email,
            'rol'    => $user->rol,
        ];

        if ($user->rol === 'paciente') {
            $response['paciente'] = $user->paciente;
        }

        return response()->json($response);
    }
}
