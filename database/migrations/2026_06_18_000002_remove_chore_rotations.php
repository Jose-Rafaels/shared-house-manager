<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chores', function (Blueprint $table) {
            $table->dropColumn('rotation_start_date');
        });

        Schema::dropIfExists('chore_rotations');
    }

    public function down(): void
    {
        Schema::create('chore_rotations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chore_id')->constrained()->cascadeOnDelete();
            $table->foreignId('member_id')->constrained('members')->restrictOnDelete();
            $table->unsignedInteger('sort_order');
            $table->timestamps();
            $table->unique(['chore_id', 'member_id']);
            $table->unique(['chore_id', 'sort_order']);
        });

        Schema::table('chores', function (Blueprint $table) {
            $table->date('rotation_start_date')->nullable();
        });
    }
};
