<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePacienteRequest;
use App\Http\Resources\PacienteResource;
use App\Http\Resources\CitaResource;
use App\Models\Paciente;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\CambiarPasswordRequest;

class PacienteController extends Controller
{
    public function index()
    {
        $pacientes = Paciente::with(['citas' => function ($q) {
            $q->where('estado', 'completada')
              ->where('fecha', '<=', now()->toDateString())
              ->orderBy('fecha', 'desc');
        }])->withCount('citas')
           ->orderBy('activo', 'desc')
           ->orderBy('nombre')
           ->get();

        return PacienteResource::collection($pacientes);
    }

    public function store(StorePacienteRequest $request)
    {
        $data = $request->validated();

        $user = User::create([
            'name'     => $data['nombre'],
            'email'    => $data['correo'] ?? $data['telefono'] . '@paciente.local',
            'password' => Hash::make($data['password']),
            'rol'      => 'paciente',
        ]);

        $paciente = Paciente::create([
            'user_id'  => $user->id,
            'nombre'   => $data['nombre'],
            'telefono' => $data['telefono'],
            'correo'   => $data['correo'] ?? null,
            'notas'    => $data['notas'] ?? null,
        ]);

        return response()->json(new PacienteResource($paciente), 201);
    }

    public function show(Paciente $paciente)
    {
        $paciente->load('citas.servicios');
        return new PacienteResource($paciente);
    }

    public function update(StorePacienteRequest $request, Paciente $paciente)
    {
        $data = $request->validated();
        unset($data['password_confirmation']);

        if ($paciente->user_id) {
            $user = User::find($paciente->user_id);
            if ($user) {
                $userUpdate = [
                    'name'  => $data['nombre'],
                    'email' => $data['correo'] ?? $user->email,
                ];
                if (!empty($data['password'])) {
                    $userUpdate['password'] = Hash::make($data['password']);
                }
                $user->update($userUpdate);
            }
        }

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $paciente->update($data);
        return new PacienteResource($paciente);
    }

    public function cambiarEstado(Paciente $paciente)
    {
        if ($paciente->activo) {
            if ($paciente->tieneCitasActivas()) {
                return response()->json([
                    'message' => 'No se puede desactivar: el paciente tiene citas pendientes o aprobadas.',
                ], 422);
            }
            $paciente->activo = false;
        } else {
            $paciente->activo = true;
        }

        $paciente->save();
        return new PacienteResource($paciente);
    }

    public function historial(Paciente $paciente)
    {
        $citas = $paciente->citas()
            ->with('servicios')
            ->orderBy('fecha', 'desc')
            ->get();

        return CitaResource::collection($citas);
    }

    public function miPerfil(Request $request)
    {
        $paciente = Paciente::where('user_id', $request->user()->id)->first();

        if (!$paciente) {
            return response()->json(['message' => 'Perfil no encontrado.'], 404);
        }

        $paciente->load('citas.servicios');
        return new PacienteResource($paciente);
    }

    public function cambiarPassword(CambiarPasswordRequest $request)
    {
        $user = $request->user();

        if (!Hash::check($request->password_actual, $user->password)) {
            return response()->json([
                'message' => 'La contraseña actual es incorrecta.',
                'errors' => ['password_actual' => ['La contraseña actual es incorrecta.']],
            ], 422);
        }

        $user->update([
            'password' => Hash::make($request->password_nueva),
        ]);

        return response()->json(['message' => 'Contraseña actualizada correctamente.']);
    }
}
