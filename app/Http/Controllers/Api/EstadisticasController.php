<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cita;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class EstadisticasController extends Controller
{
    public function index(Request $request)
    {
        $periodo = $request->query('periodo', 'mes');
        $fecha   = $request->query('fecha', now()->format('Y-m'));

        [$inicio, $fin, $inicioAnterior, $finAnterior] = $this->rangos($periodo, $fecha);

        $citasQ = Cita::whereBetween('fecha', [$inicio, $fin]);

        $ingresos       = (clone $citasQ)->where('estado', 'completada')->sum('monto_pagado');
        $totalCitas     = (clone $citasQ)->count();
        $pacientesIds   = (clone $citasQ)->where('estado', 'completada')->pluck('paciente_id')->unique();
        $pacientesCount = $pacientesIds->count();
        $ticketPromedio = $pacientesCount > 0 ? round($ingresos / $pacientesCount) : 0;

        $citasQAnt    = Cita::whereBetween('fecha', [$inicioAnterior, $finAnterior]);
        $ingresosAnt  = (clone $citasQAnt)->where('estado', 'completada')->sum('monto_pagado');
        $citasAnt     = (clone $citasQAnt)->count();
        $pacientesAnt = (clone $citasQAnt)->where('estado', 'completada')->pluck('paciente_id')->unique()->count();

        $ingresosPorDia = Cita::whereBetween('fecha', [$inicio, $fin])
            ->where('estado', 'completada')
            ->selectRaw('fecha, SUM(monto_pagado) as total')
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get()
            ->map(fn($r) => ['fecha' => $r->fecha, 'total' => (float) $r->total]);

        $porEstado = (clone $citasQ)
            ->selectRaw('estado, COUNT(*) as total')
            ->groupBy('estado')
            ->pluck('total', 'estado');

        $serviciosTop = DB::table('cita_servicio')
            ->join('citas', 'citas.id', '=', 'cita_servicio.cita_id')
            ->join('servicios', 'servicios.id', '=', 'cita_servicio.servicio_id')
            ->whereBetween('citas.fecha', [$inicio, $fin])
            ->selectRaw('servicios.nombre as nombre, COUNT(*) as citas, SUM(citas.monto_pagado) as ingresos')
            ->groupBy('servicios.id', 'servicios.nombre')
            ->orderByDesc('citas')
            ->take(5)
            ->get()
            ->map(fn($r) => [
                'nombre'   => $r->nombre ?? '—',
                'citas'    => (int) $r->citas,
                'ingresos' => (float) $r->ingresos,
            ]);

        $tablaServicios = DB::table('cita_servicio')
            ->join('citas', 'citas.id', '=', 'cita_servicio.cita_id')
            ->join('servicios', 'servicios.id', '=', 'cita_servicio.servicio_id')
            ->whereBetween('citas.fecha', [$inicio, $fin])
            ->selectRaw('servicios.nombre as nombre, COUNT(*) as citas, COUNT(DISTINCT citas.paciente_id) as pacientes, SUM(citas.monto_pagado) as ingresos')
            ->groupBy('servicios.id', 'servicios.nombre')
            ->orderByDesc('ingresos')
            ->get()
            ->map(fn($r) => [
                'nombre'    => $r->nombre ?? '—',
                'citas'     => (int) $r->citas,
                'pacientes' => (int) $r->pacientes,
                'ingresos'  => (float) $r->ingresos,
            ]);

        $todosAntesIds = Cita::where('fecha', '<', $inicio)
            ->where('estado', 'completada')
            ->pluck('paciente_id')
            ->unique();

        $nuevos      = $pacientesIds->diff($todosAntesIds)->count();
        $recurrentes = $pacientesIds->intersect($todosAntesIds)->count();

        return response()->json([
            'resumen' => [
                'ingresos'            => (float) $ingresos,
                'ingresos_anterior'   => (float) $ingresosAnt,
                'citas'               => (int) $totalCitas,
                'citas_anterior'      => (int) $citasAnt,
                'pacientes_atendidos' => (int) $pacientesCount,
                'pacientes_anterior'  => (int) $pacientesAnt,
                'ticket_promedio'     => (float) $ticketPromedio,
            ],
            'ingresos_por_dia'                => $ingresosPorDia,
            'citas_por_estado'                => $porEstado,
            'servicios_top'                   => $serviciosTop,
            'tabla_servicios'                 => $tablaServicios,
            'pacientes_nuevos_vs_recurrentes' => [
                'nuevos'      => $nuevos,
                'recurrentes' => $recurrentes,
            ],
        ]);
    }

    private function rangos(string $periodo, string $fecha): array
    {
        return match ($periodo) {
            'dia' => [
                Carbon::parse($fecha)->startOfDay(),
                Carbon::parse($fecha)->endOfDay(),
                Carbon::parse($fecha)->subDay()->startOfDay(),
                Carbon::parse($fecha)->subDay()->endOfDay(),
            ],
            'semana' => [
                Carbon::parse($fecha)->startOfWeek(),
                Carbon::parse($fecha)->endOfWeek(),
                Carbon::parse($fecha)->subWeek()->startOfWeek(),
                Carbon::parse($fecha)->subWeek()->endOfWeek(),
            ],
            'anio' => [
                Carbon::createFromDate((int) $fecha, 1, 1)->startOfYear(),
                Carbon::createFromDate((int) $fecha, 1, 1)->endOfYear(),
                Carbon::createFromDate((int) $fecha, 1, 1)->subYear()->startOfYear(),
                Carbon::createFromDate((int) $fecha, 1, 1)->subYear()->endOfYear(),
            ],
            default => [
                Carbon::parse($fecha)->startOfMonth(),
                Carbon::parse($fecha)->endOfMonth(),
                Carbon::parse($fecha)->subMonth()->startOfMonth(),
                Carbon::parse($fecha)->subMonth()->endOfMonth(),
            ],
        };
    }
}
