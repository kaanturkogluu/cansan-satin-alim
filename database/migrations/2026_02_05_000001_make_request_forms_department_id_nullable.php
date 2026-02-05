<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Kullanıcıda departman atanmamış olabileceği için department_id nullable yapılıyor.
     */
    public function up(): void
    {
        Schema::table('request_forms', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
        });

        DB::statement('ALTER TABLE request_forms MODIFY department_id BIGINT UNSIGNED NULL');

        Schema::table('request_forms', function (Blueprint $table) {
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('request_forms', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
        });

        DB::statement('ALTER TABLE request_forms MODIFY department_id BIGINT UNSIGNED NOT NULL');

        Schema::table('request_forms', function (Blueprint $table) {
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');
        });
    }
};
