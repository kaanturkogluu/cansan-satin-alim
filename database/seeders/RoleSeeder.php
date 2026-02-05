<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['name' => 'Yönetici', 'slug' => 'admin', 'sort_order' => 0],
            ['name' => 'Mühendis', 'slug' => 'engineer', 'sort_order' => 10],
            ['name' => 'Şef', 'slug' => 'chief', 'sort_order' => 20],
            ['name' => 'Müdür', 'slug' => 'manager', 'sort_order' => 30],
            ['name' => 'Satın Alma', 'slug' => 'purchasing', 'sort_order' => 40],
        ];

        foreach ($roles as $r) {
            Role::firstOrCreate(
                ['slug' => $r['slug']],
                ['name' => $r['name'], 'sort_order' => $r['sort_order']]
            );
        }
    }
}
