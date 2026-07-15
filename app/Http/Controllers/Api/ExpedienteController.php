<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Expediente;
use App\Models\Paciente;
use Illuminate\Http\Request;

class ExpedienteController extends Controller
{
    public function show(Paciente $paciente)
    {
        $expediente = $paciente->expediente;

        if (!$expediente) {
            return response()->json(['message' => 'No tiene expediente aún.'], 404);
        }

        return response()->json($expediente);
    }

    public function upsert(Request $request, Paciente $paciente)
    {
        $request->validate([
            'alergias'              => 'nullable|string',
            'enfermedades_cronicas' => 'nullable|string',
            'medicamentos_actuales' => 'nullable|string',
            'antecedentes_dentales' => 'nullable|string',
            'observaciones'         => 'nullable|string',
        ]);

        $expediente = Expediente::updateOrCreate(
            ['paciente_id' => $paciente->id],
            $request->only([
                'alergias',
                'enfermedades_cronicas',
                'medicamentos_actuales',
                'antecedentes_dentales',
                'observaciones',
            ])
        );

        return response()->json($expediente);
    }
}
