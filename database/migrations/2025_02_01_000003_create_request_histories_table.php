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
        Schema::create('request_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_form_id')->constrained('request_forms')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Who performed the action
            $table->string('action'); // created, approved, rejected
            $table->text('note')->nullable(); // Rejection reason duplicate or additional notes
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('request_histories');
    }
};
