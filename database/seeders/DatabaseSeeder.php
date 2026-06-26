<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Jeremiah Quintano',
            'email' => 'jeremiahquintano17@gmail.com',
            'username' => 'admin1'
        ]);

        User::factory()->create([
            'name' => 'Greian Baldonado',
            'email' => 'greianbaldonado17@gmail.com',
            'username' => 'admin2'
        ]);
    }
}
