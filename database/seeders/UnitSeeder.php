<?php

namespace Database\Seeders;

use App\Models\Unit;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    public function run(): void
    {
        $units = [
            ['name' => 'Adet', 'symbol' => 'adet'],
            ['name' => 'Kilogram', 'symbol' => 'kg'],
            ['name' => 'Gram', 'symbol' => 'g'],
            ['name' => 'Litre', 'symbol' => 'L'],
            ['name' => 'Metre', 'symbol' => 'm'],
            ['name' => 'Metrekare', 'symbol' => 'm²'],
            ['name' => 'Paket', 'symbol' => 'paket'],
            ['name' => 'Kutu', 'symbol' => 'kutu'],
            ['name' => 'Rulo', 'symbol' => 'rulo'],
            ['name' => 'Takım', 'symbol' => 'takım'],
        ];

        foreach ($units as $u) {
            Unit::firstOrCreate(
                ['name' => $u['name']],
                ['symbol' => $u['symbol']]
            );
        }
    }
}
