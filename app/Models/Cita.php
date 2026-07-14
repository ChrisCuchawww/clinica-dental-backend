<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Cita extends Model
{
    protected $fillable = [
        'paciente_id',
        'fecha',
        'hora',
        'estado',
        'notas',
        'monto_pagado',
    ];

    protected $casts = [
        'fecha' => 'date',
        'monto_pagado' => 'decimal:2',
    ];

    public function paciente()
    {
        return $this->belongsTo(Paciente::class);
    }

    // Relación muchos-a-muchos con servicios
    public function servicios()
    {
        return $this->belongsToMany(Servicio::class, 'cita_servicio')->withTimestamps();
    }

    // Duración total sumando todos los servicios de la cita
    public function getDuracionTotalAttribute()
    {
        return $this->servicios->sum('duracion');
    }

    // Hora de fin calculada según la duración total
    public function getHoraFinAttribute()
    {
        return Carbon::parse($this->hora)
            ->addMinutes($this->duracion_total)
            ->format('H:i');
    }

    public function scopePendientes($query)
    {
        return $query->where('estado', 'pendiente');
    }

    public function scopeDeHoy($query)
    {
        return $query->whereDate('fecha', today());
    }

    protected $attributes = [
        'estado' => 'pendiente',
    ];
}
