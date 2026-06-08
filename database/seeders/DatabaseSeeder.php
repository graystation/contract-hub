<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin user
        User::create([
            'name'              => '管理者',
            'email'             => 'koizumi.office.llc.2022@gmail.com',
            'password'          => Hash::make('Hub#2026!Kz@'),
            'email_verified_at' => now(),
        ]);
    }
}
