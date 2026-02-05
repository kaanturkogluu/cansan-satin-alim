<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY role VARCHAR(50) NOT NULL DEFAULT 'engineer'");
        } else {
            DB::statement('ALTER TABLE users ALTER COLUMN role TYPE VARCHAR(50)');
        }
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE users MODIFY role ENUM('admin','engineer','chief','manager','purchasing') NOT NULL DEFAULT 'engineer'");
    }
};
