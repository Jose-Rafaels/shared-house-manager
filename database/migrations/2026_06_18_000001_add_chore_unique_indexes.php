<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chore_assignments', function (Blueprint $table) {
            $table->unique(['chore_id', 'assigned_for_date']);
        });

        Schema::table('chore_rotations', function (Blueprint $table) {
            $table->unique(['chore_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::table('chore_assignments', function (Blueprint $table) {
            $table->dropUnique(['chore_id', 'assigned_for_date']);
        });

        Schema::table('chore_rotations', function (Blueprint $table) {
            $table->dropUnique(['chore_id', 'sort_order']);
        });
    }
};
