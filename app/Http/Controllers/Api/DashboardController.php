<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cita;
use App\Models\Paciente;
use App\Models\Servicio;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $hoy = today();

        return response()->json([
            'citas_hoy'         => Cita::whereDate('fecha', $hoy)->count(),
            'citas_pendientes'  => Cita::where('estado', 'pendiente')->count(),
            'total_pacientes'   => Paciente::count(),
            'ganancias_hoy'     => Cita::whereDate('fecha', $hoy)->where('estado', 'completada')->sum('monto_pagado'),
            'ganancias_semana'  => Cita::whereBetween('fecha', [now()->startOfWeek(), now()->endOfWeek()])->where('estado', 'completada')->sum('monto_pagado'),
            'ganancias_mes'     => Cita::whereMonth('fecha', $hoy->month)->whereYear('fecha', $hoy->year)->where('estado', 'completada')->sum('monto_pagado'),
            'citas_de_hoy'      => Cita::with(['paciente', 'servicios'])->whereDate('fecha', $hoy)->orderBy('hora')->get(),
        ]);
    }

    public function ganancias(Request $request)
    {
        $request->validate([
            'desde' => 'required|date',
            'hasta' => 'required|date|after_or_equal:desde',
        ]);

        $ganancias = Cita::whereBetween('fecha', [$request->desde, $request->hasta])
            ->where('estado', 'completada')
            ->selectRaw('fecha, SUM(monto_pagado) as total')
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get();

        return response()->json(['ganancias' => $ganancias]);
    }
}
