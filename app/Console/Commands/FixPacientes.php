<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Paciente;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class FixPacientes extends Command
{
    protected $signature = 'fix:pacientes';
    protected $description = 'Crea usuarios para pacientes sin user_id';

    public function handle()
    {
        $pacientes = Paciente::whereNull('user_id')->get();

        foreach ($pacientes as $p) {
            $email = $p->correo ?? $p->telefono . '@paciente.local';

            $user = User::create([
                'name'     => $p->nombre,
                'email'    => $email,
                'password' => Hash::make('Password123'),
                'rol'      => 'paciente',
            ]);

            $p->update(['user_id' => $user->id]);

            $this->info("Creado: {$p->nombre} | {$email}");
        }

        $this->info('Listo!');
    }
}
