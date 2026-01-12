<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Service;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Admin
        User::create([
            'name' => 'Administrateur',
            'email' => 'admin@ledantec.sn',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'service_id' => Service::where('code', 'DG')->first()->id,
        ]);

        // Chef Service Chirurgie
        User::create([
            'name' => 'Dr. Fall',
            'email' => 'fall@ledantec.sn',
            'password' => Hash::make('password'),
            'role' => 'service',
            'service_id' => Service::where('code', 'CHIR')->first()->id,
        ]);

        // Controleur Budgetaire / Finance
        User::create([
            'name' => 'M. Diop',
            'email' => 'finance@ledantec.sn',
            'password' => Hash::make('password'),
            'role' => 'controleur',
            'service_id' => Service::where('code', 'FIN')->first()->id,
        ]);
    }
}
