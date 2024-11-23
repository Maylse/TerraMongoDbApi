<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('consultation_logs', function (Blueprint $table) {
             $table->id();
            $table->foreignId('consultation_request_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('status');
            $table->text('response_message')->nullable();
            $table->text('message')->nullable();        // Added field
            $table->date('date')->nullable();           // Added field
            $table->time('time')->nullable();           // Added field
            $table->string('location')->nullable();     // Added field
            $table->decimal('rate', 8, 2)->nullable();  // Added field (decimal for monetary values)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consultation_logs');
    }
};
