<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Maria Santos', 'Jose Reyes', 'Ana Cruz', 'Paolo Garcia', 'Isabel Mendoza'] as $index => $name) {
            Customer::firstOrCreate(['email' => 'customer'.($index + 1).'@example.test'], [
                'full_name' => $name, 'phone' => 'DEMO-000-000'.($index + 1),
            ]);
        }
    }
}
