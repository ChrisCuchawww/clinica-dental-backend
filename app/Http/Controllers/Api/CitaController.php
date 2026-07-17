<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCitaRequest;
use App\Http\Requests\AgendarCitaRequest;
use App\Http\Resources\CitaResource;
use App\Models\Cita;
use App\Models\Paciente;
use App\Models\Servicio;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CitaController extends Controller
{
    public function index(Request $request)
    {
        $query = Cita::with(['paciente', 'servicios']);

        if ($request->fecha) {
            $query->whereDate('fecha', $request->fecha);
        }

        if ($request->estado) {
            $query->where('estado', $request->estado);
        }

        $citas = $query->orderBy('fecha')->orderBy('hora')->get();
        return CitaResource::collection($citas);
    }

    public function store(StoreCitaRequest $request)
    {
        $duracionTotal = (int) Servicio::whereIn('id', $request->servicios_ids)->sum('duracion');

        $disponible = $this->verificarDisponibilidad($request->fecha, $request->hora, $duracionTotal);

        if (!$disponible) {
            return response()->json([
                'message' => 'El horario seleccionado no está disponible.'
            ], 422);
        }

        $cita = Cita::create($request->only(['paciente_id', 'fecha', 'hora', 'notas']));
        $cita->servicios()->sync($request->servicios_ids);
        $cita->load(['paciente', 'servicios']);

        return response()->json(new CitaResource($cita), 201);
    }

    public function show(Cita $cita)
    {
        $cita->load(['paciente', 'servicios']);
        return new CitaResource($cita);
    }

    public function update(StoreCitaRequest $request, Cita $cita)
    {
        $cita->update($request->only(['paciente_id', 'fecha', 'hora', 'notas']));
        $cita->servicios()->sync($request->servicios_ids);
        $cita->load(['paciente', 'servicios']);
        return new CitaResource($cita);
    }

    public function destroy(Cita $cita)
    {
        $cita->delete();
        return response()->json(['message' => 'Cita eliminada.']);
    }

    public function aprobar(Cita $cita)
    {
        $cita->update(['estado' => 'aprobada']);
        return response()->json(['message' => 'Cita aprobada.']);
    }

    public function cancelar(Cita $cita)
    {
        $cita->update(['estado' => 'cancelada']);
        return response()->json(['message' => 'Cita cancelada.']);
    }

    public function completar(Request $request, Cita $cita)
    {
        $request->validate([
            'monto_pagado' => 'required|numeric|min:0',
        ]);

        $cita->update([
            'estado'       => 'completada',
            'monto_pagado' => $request->monto_pagado,
        ]);

        return response()->json(['message' => 'Cita completada y pago registrado.']);
    }

   public function horariosDisponibles(Request $request)
{
    if ($request->has('servicios_ids') && is_string($request->servicios_ids)) {
        $request->merge([
            'servicios_ids' => array_filter(explode(',', $request->servicios_ids)),
        ]);
    }

    $request->validate([
        'fecha'           => 'required|date',
        'servicios_ids'   => 'required|array|min:1',
        'servicios_ids.*' => 'exists:servicios,id',
    ]);

    $duracionTotal = (int) Servicio::whereIn('id', $request->servicios_ids)->sum('duracion');
    $horariosOcupados = $this->getHorariosOcupados($request->fecha, $duracionTotal);

    $esHoy = Carbon::parse($request->fecha)->isSameDay(Carbon::now());
    $ahora = Carbon::now();

    $horariosDisponibles = [];
    $inicio = Carbon::parse('09:00');
    $fin    = Carbon::parse('18:00');

    while ($inicio->lessThan($fin)) {
        $hora = $inicio->format('H:i');
        $finBloque = $inicio->copy()->addMinutes($duracionTotal);

        $horarioCompleto = Carbon::parse($request->fecha . ' ' . $hora);
        $yaPaso = $esHoy && $horarioCompleto->lessThan($ahora);

        if (!in_array($hora, $horariosOcupados) && $finBloque->lessThanOrEqualTo($fin) && !$yaPaso) {
            $horariosDisponibles[] = $hora;
        }
        $inicio->addMinutes(30);
    }

    return response()->json(['horarios' => $horariosDisponibles]);
}

   private function verificarDisponibilidad(string $fecha, string $hora, int $duracionNueva): bool
{
    \Log::info('verificarDisponibilidad', [
        'fecha_recibida' => $fecha,
        'hora_recibida' => $hora,
        'duracion' => $duracionNueva,
    ]);

    $horaInicio = Carbon::parse($hora);
    $horaFin    = $horaInicio->copy()->addMinutes($duracionNueva);

    $citasDelDia = Cita::with('servicios')
        ->whereDate('fecha', $fecha)
        ->whereNotIn('estado', ['cancelada'])
        ->get();

    \Log::info('citas encontradas', ['count' => $citasDelDia->count(), 'citas' => $citasDelDia->toArray()]);

    $traslape = $citasDelDia->filter(function ($cita) use ($horaInicio, $horaFin) {
        $citaInicio = Carbon::parse($cita->hora);
        $duracionCita = (int) $cita->servicios->sum('duracion');
        $citaFin = $citaInicio->copy()->addMinutes($duracionCita);

        return $horaInicio->lt($citaFin) && $horaFin->gt($citaInicio);
    });

    return $traslape->isEmpty();
}
    private function getHorariosOcupados(string $fecha, int $duracionNueva): array
    {
        $citas = Cita::with('servicios')
            ->whereDate('fecha', $fecha)
            ->whereNotIn('estado', ['cancelada'])
            ->get();

        $ocupados = [];

        foreach ($citas as $cita) {
            $inicio = Carbon::parse($cita->hora);
            $duracionCita = (int) $cita->servicios->sum('duracion');
            $fin = $inicio->copy()->addMinutes($duracionCita);

            $slot = $inicio->copy();
            while ($slot->lt($fin)) {
                $ocupados[] = $slot->format('H:i');
                $slot->addMinutes(30);
            }
        }

        return array_unique($ocupados);
    }

    public function misCitas(Request $request)
    {
        $paciente = $request->user()->paciente;
        $citas = $paciente->citas()
            ->with('servicios')
            ->orderBy('fecha', 'desc')
            ->get();
        return CitaResource::collection($citas);
    }

    public function agendarCita(AgendarCitaRequest $request)
    {
        $paciente = Paciente::where('user_id', $request->user()->id)->first();

        if (!$paciente) {
            return response()->json(['message' => 'Perfil de paciente no encontrado.'], 404);
        }

        $duracionTotal = (int) Servicio::whereIn('id', $request->servicios_ids)->sum('duracion');

        $disponible = $this->verificarDisponibilidad($request->fecha, $request->hora, $duracionTotal);

        if (!$disponible) {
            return response()->json([
                'message' => 'El horario seleccionado no está disponible.'
            ], 422);
        }

        $cita = Cita::create([
            'paciente_id' => $paciente->id,
            'fecha'       => $request->fecha,
            'hora'        => $request->hora,
            'notas'       => $request->notas,
        ]);

        $cita->servicios()->sync($request->servicios_ids);
        $cita->load(['paciente', 'servicios']);

        return response()->json(new CitaResource($cita), 201);
    }

    public function cancelarMiCita(Request $request, Cita $cita)
    {
        $paciente = Paciente::where('user_id', $request->user()->id)->first();

        if (!$paciente || $cita->paciente_id !== $paciente->id) {
            return response()->json(['message' => 'No autorizado para cancelar esta cita.'], 403);
        }

        if (!in_array($cita->estado, ['pendiente', 'aprobada'])) {
            return response()->json(['message' => 'Esta cita ya no se puede cancelar.'], 422);
        }

        $cita->update(['estado' => 'cancelada']);

        return response()->json(['message' => 'Cita cancelada correctamente.']);
    }
}
