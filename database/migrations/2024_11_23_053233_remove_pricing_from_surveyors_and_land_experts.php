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
       // Remove the pricing column from surveyors table
       Schema::table('surveyors', function (Blueprint $table) {
        $table->dropColumn('pricing');
    });

    // Remove the pricing column from land_experts table
        Schema::table('land_experts', function (Blueprint $table) {
            $table->dropColumn('pricing');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Add the pricing column back to surveyors table
        Schema::table('surveyors', function (Blueprint $table) {
            $table->decimal('pricing', 8, 2)->after('certification_id');
        });

        // Add the pricing column back to land_experts table
        Schema::table('land_experts', function (Blueprint $table) {
            $table->decimal('pricing', 8, 2)->after('certification_id');
        });
    }
};
