<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HacerAdminSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('users')
            ->where('email', 'admin@gmail.com')
            ->update(['rol' => 'admin']);
    }
}
