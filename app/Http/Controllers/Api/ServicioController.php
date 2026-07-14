<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreServicioRequest;
use App\Http\Requests\UpdateServicioRequest;
use App\Http\Resources\ServicioResource;
use App\Models\Servicio;

class ServicioController extends Controller
{
    public function index()
    {
        $servicios = Servicio::orderBy('categoria')->get();
        return ServicioResource::collection($servicios);
    }

    public function store(StoreServicioRequest $request)
    {
        $servicio = Servicio::create($request->validated());
        return response()->json(new ServicioResource($servicio), 201);
    }

    public function show(Servicio $servicio)
    {
        return new ServicioResource($servicio);
    }

    public function update(UpdateServicioRequest $request, Servicio $servicio)
    {
        $data = $request->validated();

        // Si se intenta desactivar el servicio, verificar que no tenga citas activas
        if (array_key_exists('activo', $data) && $data['activo'] === false && $servicio->activo === true) {
            if ($servicio->tieneCitasActivas()) {
                return response()->json([
                    'message' => 'No se puede desactivar este servicio porque tiene citas activas asociadas.',
                    'errors' => [
                        'activo' => ['El servicio tiene citas activas y no puede desactivarse.'],
                    ],
                ], 422);
            }
        }

        $servicio->update($data);
        return new ServicioResource($servicio);
    }

    public function destroy(Servicio $servicio)
    {
        if ($servicio->tieneCitasActivas()) {
            return response()->json([
                'message' => 'No se puede desactivar este servicio porque tiene citas activas asociadas.',
                'errors' => [
                    'activo' => ['El servicio tiene citas activas y no puede desactivarse.'],
                ],
            ], 422);
        }

        $servicio->update(['activo' => false]); // soft disable
        return response()->json(['message' => 'Servicio desactivado.']);
    }
}
