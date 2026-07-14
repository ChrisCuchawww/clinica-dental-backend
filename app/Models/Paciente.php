<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Paciente extends Model
{
    protected $fillable = [
        'user_id',
        'nombre',
        'telefono',
        'correo',
        'notas',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    protected $attributes = [
        'activo' => true,
    ];

    public function citas()
    {
        return $this->hasMany(Cita::class);
    }

    public function citasAnteriores()
    {
        return $this->hasMany(Cita::class)
            ->where('estado', 'completada')
            ->orderBy('fecha', 'desc');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function expediente()
    {
        return $this->hasOne(Expediente::class);
    }

    /**
     * Determina si el paciente tiene citas activas (pendientes o aprobadas).
     * Un paciente con citas activas no puede desactivarse.
     */
    public function tieneCitasActivas(): bool
    {
        return $this->citas()
            ->whereIn('estado', ['pendiente', 'aprobada'])
            ->exists();
    }
}
