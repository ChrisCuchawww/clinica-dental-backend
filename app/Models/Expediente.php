<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expediente extends Model
{
    protected $fillable = [
        'paciente_id',
        'alergias',
        'enfermedades_cronicas',
        'medicamentos_actuales',
        'antecedentes_dentales',
        'observaciones',
        ];

    public function paciente()
    {
        return $this->belongsTo(Paciente::class);
    }
}
