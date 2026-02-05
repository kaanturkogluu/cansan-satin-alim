<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('request_forms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('department_id')->constrained()->onDelete('cascade'); // To know which dept it belongs to easily
            $table->string('request_no')->unique(); // e.g., REQ-2024001
            $table->string('title'); // Talep İçeriği (Short summary)
            $table->text('description')->nullable(); // Açıklama
            $table->enum('status', ['pending_chief', 'pending_manager', 'pending_purchasing', 'approved', 'rejected'])->default('pending_chief');
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('request_forms');
    }
};
