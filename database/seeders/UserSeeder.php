<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['name' => 'Admin User', 'email' => 'admin@fundsync.com', 'position' => '1001', 'password' => Hash::make('password')],
            ['name' => 'Maria Santos', 'email' => 'maria.santos@fundsync.com', 'position' => '1002', 'password' => Hash::make('password')],
            ['name' => 'Juan Dela Cruz', 'email' => 'juan.delacruz@fundsync.com', 'position' => '1003', 'password' => Hash::make('password')],
            ['name' => 'Ana Reyes', 'email' => 'ana.reyes@fundsync.com', 'position' => '1004', 'password' => Hash::make('password')],
            ['name' => 'Jose Garcia', 'email' => 'jose.garcia@fundsync.com', 'position' => '1005', 'password' => Hash::make('password')],
            ['name' => 'Rosa Mendoza', 'email' => 'rosa.mendoza@fundsync.com', 'position' => '1006', 'password' => Hash::make('password')],
            ['name' => 'Pedro Bautista', 'email' => 'pedro.bautista@fundsync.com', 'position' => '1007', 'password' => Hash::make('password')],
            ['name' => 'Luisa Torres', 'email' => 'luisa.torres@fundsync.com', 'position' => '1008', 'password' => Hash::make('password')],
            ['name' => 'Carlos Ramos', 'email' => 'carlos.ramos@fundsync.com', 'position' => '1009', 'password' => Hash::make('password')],
            ['name' => 'Elena Flores', 'email' => 'elena.flores@fundsync.com', 'position' => '1010', 'password' => Hash::make('password')],
        ];

        foreach ($users as $user) {
            DB::table('users')->updateOrInsert(
                ['email' => $user['email']],
                array_merge($user, [
                    'email_verified_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}
