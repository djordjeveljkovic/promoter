<?php

namespace Database\Seeders;

use App\Models\TicketCommission;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin
        // Admin
        User::create([
            'name' => 'SUPREME',
            'email' => 'superadmin@blank.com',
            'password' => Hash::make('supremeAdminXOXO'),
            'role' => 'admin',
        ]);
        User::create([
            'name' => 'REFEST Festival',
            'email' => 'refestrs@gmail.com',
            'password' => Hash::make('Refestcar123***'),
            'role' => 'admin',
        ]);
    }
}
