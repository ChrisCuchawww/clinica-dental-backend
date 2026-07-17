<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PacienteController;
use App\Http\Controllers\Api\CitaController;
use App\Http\Controllers\Api\ServicioController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EstadisticasController;

//Publicas
Route::post('/login',    [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::get('/servicios', [ServicioController::class, 'index']);

//Cualquier usuario autenticado (paciente y admin)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me',      [AuthController::class, 'me']);
});

//Paciente o Admin
Route::middleware(['auth:sanctum', 'check.rol:paciente,admin'])->group(function () {
    Route::get('/citas/horarios-disponibles', [CitaController::class, 'horariosDisponibles']);
});

//Paciente autenticado
Route::middleware(['auth:sanctum', 'check.rol:paciente'])->group(function () {
    Route::get('/mi-perfil', [PacienteController::class, 'miPerfil']);
    Route::post('/mi-perfil/password', [PacienteController::class, 'cambiarPassword']);
    Route::get('/mis-citas', [CitaController::class, 'misCitas']);
    Route::post('/mis-citas', [CitaController::class, 'agendarCita']);
    Route::patch('/mis-citas/{cita}/cancelar', [CitaController::class, 'cancelarMiCita']);
});

//Admin
Route::middleware(['auth:sanctum', 'check.rol:admin'])->group(function () {
    Route::get('/dashboard',           [DashboardController::class, 'index']);
    Route::get('/dashboard/ganancias', [DashboardController::class, 'ganancias']);

    Route::apiResource('pacientes', PacienteController::class)->except(['destroy']);
    Route::patch('pacientes/{paciente}/estado',   [PacienteController::class, 'cambiarEstado']);
    Route::get('pacientes/{paciente}/historial',  [PacienteController::class, 'historial']);

    Route::apiResource('citas', CitaController::class);
    Route::patch('citas/{cita}/aprobar',   [CitaController::class, 'aprobar']);
    Route::patch('citas/{cita}/cancelar',  [CitaController::class, 'cancelar']);
    Route::patch('citas/{cita}/completar', [CitaController::class, 'completar']);

    Route::apiResource('servicios', ServicioController::class)->except(['index']);

    Route::get('/estadisticas', [EstadisticasController::class, 'index']);
});
