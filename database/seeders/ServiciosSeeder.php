<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServiciosSeeder extends Seeder
{
    public function run(): void
    {
        $servicios = [
            ['nombre' => 'Limpieza Dental', 'precio' => 450.00, 'duracion' => 45, 'categoria' => 'Preventivo', 'descripcion' => 'Eliminación de sarro, placa bacteriana y pulido dental. Incluye revisión y diagnóstico general de tu salud bucal.', 'activo' => true],
            ['nombre' => 'Resina Dental', 'precio' => 700.00, 'duracion' => 40, 'categoria' => 'Restauración', 'descripcion' => 'Obturación estética con resina compuesta del color de tu diente. Tratamiento rápido y duradero por pieza.', 'activo' => true],
            ['nombre' => 'Extracción Dental', 'precio' => 600.00, 'duracion' => 30, 'categoria' => 'Cirugía', 'descripcion' => 'Extracción de piezas dentales con anestesia local. Incluye seguimiento postoperatorio y receta médica.', 'activo' => true],
            ['nombre' => 'Pegado de Dientes', 'precio' => 500.00, 'duracion' => 30, 'categoria' => 'Restauración', 'descripcion' => 'Recolocación y cementado de piezas dentales fracturadas o desprendidas con materiales de alta resistencia.', 'activo' => true],
            ['nombre' => 'Brackets', 'precio' => 8000.00, 'duracion' => 60, 'categoria' => 'Ortodoncia', 'descripcion' => 'Colocación de brackets metálicos o estéticos para corrección de la alineación dental. Incluye valoración inicial y plan de tratamiento.', 'activo' => true],
            ['nombre' => 'Dentadura Completa', 'precio' => 6000.00, 'duracion' => 60, 'categoria' => 'Prótesis', 'descripcion' => 'Elaboración de dentadura completa superior e inferior para pacientes con ausencia total de piezas dentales.', 'activo' => true],
            ['nombre' => 'Dentadura Removible', 'precio' => 3500.00, 'duracion' => 45, 'categoria' => 'Prótesis', 'descripcion' => 'Prótesis parcial removible para reponer una o varias piezas dentales. Cómoda, estética y de fácil mantenimiento.', 'activo' => true],
            ['nombre' => 'Prótesis Fija', 'precio' => 4500.00, 'duracion' => 60, 'categoria' => 'Prótesis', 'descripcion' => 'Corona o puente fijo sobre diente natural o implante. Material de porcelana o zirconio para máxima estética y durabilidad.', 'activo' => true],
            ['nombre' => 'Extracción Tercer Molar', 'precio' => 1200.00, 'duracion' => 60, 'categoria' => 'Cirugía', 'descripcion' => 'Extracción quirúrgica de muelas del juicio con anestesia local. Incluye valoración previa, cirugía y seguimiento postoperatorio.', 'activo' => true],
        ];

        DB::table('servicios')->insert(array_map(function ($s) {
            return array_merge($s, ['created_at' => now(), 'updated_at' => now()]);
        }, $servicios));
    }
}
