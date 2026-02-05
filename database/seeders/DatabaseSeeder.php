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
        $this->call(RoleSeeder::class);

        $engDept = \App\Models\Department::create(['name' => 'Engineering']);
        $purchDept = \App\Models\Department::create(['name' => 'Purchasing']);
        $mgmtDept = \App\Models\Department::create(['name' => 'Management']);

        // Admin
        \App\Models\User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'department_id' => null,
        ]);

        // Engineer
        \App\Models\User::factory()->create([
            'name' => 'John Engineer',
            'email' => 'engineer@example.com',
            'password' => bcrypt('password'),
            'role' => 'engineer',
            'department_id' => $engDept->id,
        ]);

        // Chief
        \App\Models\User::factory()->create([
            'name' => 'Jane Chief',
            'email' => 'chief@example.com',
            'password' => bcrypt('password'),
            'role' => 'chief',
            'department_id' => $engDept->id,
        ]);

        // Manager
        \App\Models\User::factory()->create([
            'name' => 'Mr. Manager',
            'email' => 'manager@example.com',
            'password' => bcrypt('password'),
            'role' => 'manager',
            'department_id' => $mgmtDept->id,
        ]);

        // Purchasing
        \App\Models\User::factory()->create([
            'name' => 'Peter Purchasing',
            'email' => 'purchasing@example.com',
            'password' => bcrypt('password'),
            'role' => 'purchasing',
            'department_id' => $purchDept->id,
        ]);

        $this->call(UnitSeeder::class);
        $this->call(RequestFormSeeder::class);
    }
}
