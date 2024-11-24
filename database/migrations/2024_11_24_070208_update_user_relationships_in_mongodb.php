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
      // Update finders table
      Schema::table('finders', function (Blueprint $table) {
        if (!Schema::hasColumn('finders', 'user_id')) {
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
        }
        if (!Schema::hasColumn('finders', 'finder_id')) {
            $table->foreignId('finder_id')->nullable()->constrained('finders')->onDelete('set null');
        }
    });

     // Update land_experts table
     Schema::table('land_experts', function (Blueprint $table) {
        if (!Schema::hasColumn('land_experts', 'user_id')) {
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
        }
    
    });


  // Update surveyors table
  Schema::table('surveyors', function (Blueprint $table) {
    if (!Schema::hasColumn('surveyors', 'user_id')) {
        $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
    }
  
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop foreign keys and columns if necessary (optional)
        Schema::table('finders', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['finder_id']);
        });

        Schema::table('land_experts', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropUnique(['license_number']);
            $table->dropUnique(['certification_id']);
        });

        Schema::table('surveyors', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropUnique(['license_number']);
            $table->dropUnique(['certification_id']);
        });
    }
};
