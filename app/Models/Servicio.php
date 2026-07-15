<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Servicio extends Model
{
    protected $fillable = [
        'nombre',
        'precio',
        'duracion',
        'categoria',
        'descripcion',
        'activo',
    ];

    protected $casts = [
        'precio' => 'decimal:2',
        'duracion' => 'integer',
        'activo' => 'boolean',
    ];

    public function citas()
    {
        return $this->hasMany(Cita::class);
    }

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

  
    public function tieneCitasActivas(): bool
    {
        return $this->citas()
            ->whereIn('estado', ['pendiente', 'aprobada'])
            ->exists();
    }
}
