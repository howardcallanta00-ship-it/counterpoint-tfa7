<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Miguel Ramos', 'Sofia Aquino', 'Carlo Bautista', 'Lara Villanueva', 'Rafael Dela Cruz'] as $name) {
            User::firstOrCreate(['username' => 'demo.'.strtolower(explode(' ', $name)[0])], ['full_name' => $name]);
        }
    }
}
